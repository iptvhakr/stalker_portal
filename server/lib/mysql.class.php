<?php
/**
 * Mysql driver.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Mysql
{
    private $link;
    
    private $charset = 'utf8';
    private $num_queries = 0;
    private $cache_hits = 0;
    
    private $cache;
    
    private $allow_caching = true;

    private $select  = array();
    private $from    = array();
    private $where   = array();
    private $join    = array();
    private $orderby = array();
    private $groupby = array();
    private $limit   = false;
    private $offset  = false;
    
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Mysql();
        }
        return self::$instance;
    }
    
    private function __construct(){
        
        $this->link = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
        
        if (QUERY_CACHE){
            $this->cache = Cache::getInstance();
        }
        
        if ($this->link){
            mysql_select_db(DB_NAME);
        }
        
        $this->set_charset($this->charset);
    }
    
    private function set_charset($charset){

        if (!mysql_set_charset($charset)){
            throw new Exception('Error: '.mysql_error($this->link));
        }
    }
    
    public function select($sql = '*'){
        
        if(is_string($sql)){
            $sql = explode(',', $sql);
        }
        
        foreach ($sql as $val){
            if (($val = trim($val)) === '') continue;
            
            $this->select[] = $val;
        }
        return $this;
    }
    
    public function from($tables){
        
        if (is_string($tables)){
            $tables = array($tables);
        }
        
        foreach ($tables as $table){
            
            if(($table = trim($table)) === '') continue;
            
            $this->from[] = trim($table);
        }
        
        return $this;
    }
    
    public function join($table, $key, $value, $type){
        
        $join = array();
        
        if (!empty($type)){
            
            $type = strtoupper(trim($type));
            
            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER'))){
                $type = '';
            }else{
                $type .= ' '; 
            }
        }
        
        $cond = array();
        $keys  = is_array($key) ? $key : array($key => $value);
        
        foreach ($keys as $key => $value){
            
            $cond[] = $key.'='.$value;
            
        }
        
        $join['tables'][] = $table;
        
        $join['conditions'] = '('.trim(implode(' ', $cond)).')';
        $join['type'] = $type;
        
        $this->join[] = $join;
        
        return $this;
    }
    
    public function where($key, $type = 'AND ', $value = null, $quote = true){
        
        if (empty($key)){
            return $this;
        }
        
        if (!is_array($key)){
            $keys = array($key => $value);
        }else{
            $keys = $key;
        }
        
        $where = array();
        
        foreach ($keys as $key => $value){
            
            //$prefix = (count($this->where) == 0) ? '' : $type;
            $prefix = (count($where) == 0) ? '' : $type;
            
            if ($quote === -1){
                $value = '';
            }else{
                
                if ($value === null){
                    if (!$this->has_operator($key)){
                        $key .= ' IS';
                    }
                    
                    $value = ' NULL';
                }else{
                    if (!$this->has_operator($key) && !empty($key)){
                        $key = $key.'=';
                    }else{
                        preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $key, $matches);
                        if(isset($matches[1]) && isset($matches[2])){
                            $key = trim($matches[1]).''.trim($matches[2]);
                        }
                    }
                    
                    $value = $this->escape($value);
                }
            }
            
            //$this->where[] = $prefix.$key.$value;
            
            
            /*if (empty($where)){
                $where .= ' AND ('.$prefix.$key.$value.'';
            }*/
            
            $where[] = $prefix.$key.$value;
        }
        
        $where_str = '('.implode(' ', $where).')';
        
        
        if (count($this->where) != 0){
            //$where = "AND '('.$where.')";
            
            $where_str = ' AND '.$where_str;
            
        }
        
        /*$this->where[] = '('.$where.')';*/
        $this->where[] = $where_str;
        
        return $this;
    }
    
    public function in($field, $values, $not = false){
        
        $escaped_values = array();
        
        foreach ($values as $value){
            if (is_numeric($value)){
                $escaped_values []= $value;
            }else{
                $escaped_values []= "'".$this->escape_str($value)."'";
            }
        }
        if (!empty($values)){
            $values = implode(",", $escaped_values);
        }else{
            $values = 'null';
        }
        
        $where = $field.' '.($not === true ? 'not ' : '').'in ('.$values.')';
        
        $this->where($where, 'and ', '', -1);
        
        return $this;
    }
    
    /*public function like($field, $match = ''){
        
        if (empty($field)){
            return $this;
        }
        
        $fields = is_array($field) ? $field : array($field => $match);
        
        foreach ($fields as $field => $match_item){
            
            $matches = is_array($match_item) ? $match_item : array($match_item);
            
            foreach ($matches as $match){
                
                $match = $this->escape_str($match);
                
                $prefix = (count($this->where) == 0) ? '' : ' AND';
                    
                //$match = '%'.str_replace('%', '\\%', $match).'%';
                    
                $this->where[] = $prefix.' '.$field.' LIKE \''.$match.'\'';
            }
        }

        return $this;
    }*/
    
    public function like($fields, $type = 'AND '){
        
        if (empty($fields)){
            return $this;
        }
        
        $like = array();
        
        foreach ($fields as $field => $match){
            
            $prefix = (count($like) == 0) ? '' : $type;
            
            $like[] = $prefix.' '.$field.' LIKE \''.$match.'\'';
        }
        
        $where_str = '('.implode(' ', $like).')';
        
        if (count($this->where) != 0){
            $where_str = ' AND '.$where_str;
        }
        
        $this->where[] = $where_str;

        return $this;
    }
    
    public function limit($limit, $offset = null){
        
        $this->limit = intval($limit);
        
        if ($offset !== null || !is_int($this->offset)){
            $this->offset = intval($offset);
        }
        
        return $this;
    }
    
    public function nolimit(){
        
        $this->limit = false;
        
        $this->offset = false;
        
        return $this;
    }
    
    public function orderby($orderby, $direction = null){
        
        if (!is_array($orderby)){
            $orderby = array($orderby => $direction);
        }
        
        foreach ($orderby as $column => $direction){
            
            $direction = strtoupper(trim($direction));
            
            if (!in_array($direction, array('ASC', 'DESC', 'RAND()', 'RANDOM()', 'NULL'))){
                $direction = 'ASC';
            }
            
            $this->orderby[] = $column.' '.$direction;
        }
        
        return $this;
    }
    
    public function groupby($by){
        
        if (!is_array($by)){
			$by = explode(',', strval($by));
		}
		
		foreach ($by as $val){
		    $val = trim($val);
		    
		    if ($val != ''){
				$this->groupby[] = $val;
			}
		}
		
		return $this;
    }
    
    private function compile_select($database){
        
        $sql = 'SELECT ';
        $sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';
        
        if (count($database['from']) > 0)
		{
			$sql .= "\nFROM (";
			$sql .= implode(', ', $database['from']).")";
		}

		if (count($database['join']) > 0)
		{
			foreach($database['join'] AS $join)
			{
				$sql .= "\n".$join['type'].'JOIN '.implode(', ', $join['tables']).' ON '.$join['conditions'];
			}
		}

		if (count($database['where']) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		/*if (count($database['having']) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}*/

		if (count($database['orderby']) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit']))
		{
			$sql .= "\n";
			$sql .= 'LIMIT '.$database['offset'].', '.$database['limit'];
		}

		return $sql;
    }
    
    public function insert($table, $keys){
        
        
        $fields = array();
        $values = array();
        
        if (key_exists(0, $keys)){
            
            $fields = array_keys($keys[0]);
            
            foreach ($keys as $data){
                $value_arr = array();
                
                foreach ($data as $field => $value){
                    $value_arr[] = $this->escape($value);
                }
                
                $values[] = '('.implode(', ', $value_arr).')';
                
            }
            
            $value_str = implode(', ', $values);
            
        }else{
            
            foreach ($keys as $field => $value){
                $fields[] = $field;
                $values[] = $this->escape($value);
            }
            
            $value_str = '('.implode(', ', $values).')';
            
        }
        
        $sql = 'INSERT INTO '.$table.' ('.implode(', ', $fields).') value '.$value_str;
        
        $result = $this->query($sql);
        
        $this->reset_write();
        
        return $result;
    }
    
    public function update($table, $values, $where){
        
        $this->from[] = $table;
        
        foreach ($where as $key => $value){
            $this->where(array($key => $value), 'AND ');
        }
        
        foreach ($values as $key => $val){
            $valstr[] = $key.'='.$this->escape($val);
        }
        
        $sql = 'UPDATE '.$table.' SET '.implode(', ', $valstr).' WHERE '.implode(' ', $this->where);
        
        $result = $this->query($sql);
        
        $this->reset_write();
        
        return $result;
    }
    
    public function delete($table, $where){
        
        foreach ($where as $key => $value){
            $this->where(array($key => $value), 'AND ');
        }
        
        $sql = 'DELETE FROM '.$table.' WHERE '.implode(' ', $this->where);
        
        $result = $this->query($sql);
        
        $this->reset_write();
        
        return $result;
    }
    
    public function get($table = ''){
        
        if ($table != ''){
            $this->from($table);
        }
        
        $sql = $this->compile_select(get_object_vars($this));
        
        $result = $this->query($sql);
        
        $this->reset_select();
        
        return $result;
    }
    
    public function query($sql){
        
        echo $sql."\n";
        
        if (QUERY_CACHE && $this->allow_caching){
            
            $tags = $this->get_tags(get_object_vars($this));
            
            
            if(!preg_match('/INSERT|UPDATE|REPLACE|SET|DELETE|TRUNCATE/i', $sql)){                
                
                $key = $this->get_cache_key($sql);
                
                if(($result = $this->cache->get($key)) === false){
                    $this->num_queries++;
                    
                    $result = new MysqlResult(mysql_query($sql, $this->link), $sql, $this->link);
                    
                    $this->cache->set($key, $result->as_array(true), $tags);
                    
                    return $result;
                }else{
                    $this->cache_hits++;
                    
                    $result = new CacheResult($result, $sql);
                    
                    return $result;
                }
                
            }else{
                
                $this->cache->setInvalidTags($tags);
                
            }
        }
        
        $this->enable_caching();
        
        $this->num_queries++;
        
        return new MysqlResult(mysql_query($sql, $this->link), $sql, $this->link);
    }
    
    private function reset_select(){
        
        $this->select   = array();
		$this->from     = array();
		$this->join     = array();
		$this->where    = array();
		$this->orderby  = array();
		$this->groupby  = array();
		$this->limit    = false;
		$this->offset   = false;
    }
    
    private function reset_write(){
        
        $this->set   = array();
		$this->from  = array();
		$this->where = array();
    }
    
    private function escape_str($str){
        
        return mysql_real_escape_string($str, $this->link); 
    }
    
    private function escape($value){
        if(is_int($value)){
            
            return $value;
        //}elseif (!in_array(strtoupper(trim($value)), array('NOW()', 'CURDATE()', 'CURTIME()')) && !strpos($value, '+')){
        }elseif (!in_array(strtoupper(trim($value)), array('NOW()', 'CURDATE()', 'CURTIME()'))){
            
            $value = "'".$this->escape_str($value)."'";
        }else{
            
            $this->disable_caching();
        }
        
        return $value;
    }
    
    public function has_operator($str){
		return (bool) preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?\b|BETWEEN/i', trim($str));
	}
	
	public function get_num_queries(){
	    return $this->num_queries;
	}
	
	public function get_cache_hits(){
	    return $this->cache_hits;
	}
	
	private function get_cache_key($sql){
		return sha1(serialize($sql));
	}
	
	private function get_tags($database){
	    
	    $tags = array();
	    
	    if (count($database['from']) > 0)
		{
			$tags = array_merge($tags, $database['from']);
		}

		if (count($database['where']) > 0)
		{
		    if(count($database['from']) == 1){
		        $where = array();
		        foreach ($database['where'] as $str){
		            $where[] = $database['from'][0].'.'.$str;
		        }
		    }else{
		        $where = $database['where'];
		    }
		    
			$tags = array_merge($tags, $where);
		}
		
		return $tags;
	}
	
	private function enable_caching(){
	    $this->allow_caching = true;
	}
	
	private function disable_caching(){
	    $this->allow_caching = false;
	}
}
?>