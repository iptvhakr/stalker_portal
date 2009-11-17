<?php
/**
 * Mysql class.
 * @package stalker_portal
 */

class Mysql
{
    private  $db_connect_id;
    private  $num_queries = 0;
    private  $num_rows;
    private  $last_insert_id;
    private  $max_page_items = MAX_PAGE_ITEMS;
    
    private $charset_query = array(
        "SET NAMES 'utf8'",
        "SET CHARACTER SET utf8"
    );
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Mysql();
        }
        return self::$instance;
    }

    
    private function __construct(){
        
        $this->db_connect_id = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
        
        if ($this->db_connect_id){
            mysql_select_db(DB_NAME);
        }else{
            
        }
        
        foreach ($this->charset_query as $query){
            $result = mysql_query($query);
        }
    }
    
    public function query($str){
        
        $rows = array();
        
        $this->num_queries++;
        
        $result = mysql_query($str);
        
        if (!$result){
            throw new Exception("Error: mysql_query ".mysql_error());
        }
        
        while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) {
            $rows[]=$row;	
        }
        
        $this->num_rows = count($rows);
        
        return $rows;
    }
    
    public function getData($table, $where_arr = array(), $page = 0, $end_query = ''){
        
        $where = '';
        
        if (count($where_arr) > 0){
            
            $where .= "where ";
            
            foreach ($where_arr as $field => $val){

                if (is_array($val)){

                    $val_s = implode(",", $val);
                    $where .= "$field in ($val_s) and ";
                    
                    if (empty($end_query)){
                        $end_query = "order by field($field,$val_s)";
                    }
                    
                }else{
                    $where .= "$field='".mysql_real_escape_string($val)."' and ";
                }
            }
        }
        
        $where = substr($where, 0, strlen($where)-5);
        
        if ($page >= 1){
            $page_offset = ($page-1) * $this->max_page_items;
            $end_query .= " limit $page_offset, ".$this->max_page_items;
        }
        
        $sql = "select * from $table $where $end_query";
        
        try {
            $result = $this->query($sql);
        }catch (Exception $e){
            return false;
        }
        
        return $result;
    }
    
    public function getFirstData(){
        $args = func_get_args();
        $result = call_user_func_array(array($this, 'getData'), $args);
        
        if (is_array($result) && count($result) > 0){
            return $result[0];
        }
        
        return $result;
    }
    
    public function getRowCount($table = '', $where_arr = array()){
        
        if (!$table){
            return $this->num_rows;
        }
        
        $where = '';
        
        if (count($where_arr) > 0){
            $where .= "where ";
            foreach ($where_arr as $field => $val){
                $where .= "$field = '$val' and ";
            }
        }
        
        $where = substr($where, 0, strlen($where)-5);
        $sql = "select count(*) as counter from $table $where";
        
        try {
            $arr = $this->query($sql);
            $counter = $arr[0]['counter'];
            $this->num_rows = $counter;
        }catch (Exception $e){
            return 0;
        }
        
        return $counter;
    }
    
    public function insertData($table, $add_data_arr = array()){
        
        $fields = array();
        $values = array();
        
        if (count($add_data_arr) > 0){
            foreach ($add_data_arr as $field => $val){
                $fields[] = $field;
                if (in_array(strtoupper($val), array('NOW()', 'CURDATE()', 'CURTIME()'))){
                    $values[] = $val;
                }else{
                    $values[] = "'".mysql_real_escape_string($val)."'";
                }
            }
        }else{
            return false;
        }
        
        $field_str = join(", ", $fields);
        $value_str = join(", ", $values);
        
        $sql = "insert into $table ($field_str) value ($value_str)";
        
        try {
            $last_id = $this->update($sql);
        }catch (Exception $e){
            return false;
        }
        
        return $last_id;
    }
    
    public function updateData($table, $set_data_arr = array(), $where_arr = array()){
        
        $where = '';
        $set_data = '';
        
        if (count($set_data_arr) > 0){
            foreach ($set_data_arr as $field => $val){
                if (in_array(strtoupper($val), array('NOW()', 'CURDATE()', 'CURTIME()'))){
                    $set_data .= "$field=".$val.", ";
                }else{
                    $set_data .= "$field='".mysql_escape_string($val)."', ";
                }
            }
        }else{
            return false;
        }
        
        $set_data = substr($set_data, 0, strlen($set_data)-2);
        
        if (count($where_arr) > 0){
            $where .= "where ";
            foreach ($where_arr as $field => $val){
                $where .= "$field='$val' and ";
            }
        }
        
        $where = substr($where, 0, strlen($where)-5);
        
        $sql = "update $table set $set_data $where";
        
        try {
            $this->update($sql);
        }catch (Exception $e){
            return false;
        }
        
        return true;
    }

    private function update($str){
        
        $this->num_queries++;
        $rs = mysql_query($str);
        
        if (!$rs){
            throw new Exception("Error: mysql_query ".mysql_error());
        }
        
        $this->last_insert_id = mysql_insert_id($this->db_connect_id);
        return $rs;
    }
    
    public function getQueryCounter(){
        return $this->num_queries;
    }
}
?>