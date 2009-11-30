<?php
/**
 * MySQL database result.
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class MysqlResult extends DatabaseResult
{
    
    public function __construct($result, $sql, $link){
	    
	    if (is_resource($result)){
	        
	        $this->total_rows  = mysql_num_rows($result);
	        
	    }elseif(is_bool($result)){
	        
	        if($result == false){
	            
	            throw new Exception('Error: mysql_query '.mysql_error().'; query :'.$sql);
	            
	        }else{
                
	            $this->insert_id  = mysql_insert_id($link);
	            $this->total_rows = mysql_affected_rows($link);
	        }
	    }
	    
	    $this->result = $result;
	    
	    $this->sql = $sql;
    }
    
    public function __destruct(){
        
        if (is_resource($this->result)){
            mysql_free_result($this->result);
        }
	}
	
	public function as_array($return = false, $field = null){
	    
	    if (!$return){
	        return $this;
	    }
	    
	    $array = array();
	    
	    if ($this->total_rows > 0){
	        
            mysql_data_seek($this->result, 0);
            
            if($field !== null){
                while ($row = mysql_fetch_assoc($this->result)){
    				$array[] = $row[$field];
    			}
            }else{
                while ($row = mysql_fetch_assoc($this->result)){
    				$array[] = $row;
    			}
            }

		}
		
		return $array;
	}
	
	public function all($field){
	    
	    return $this->as_array(true, $field);
	}
	
	public function seek($offset){
	    
	    if ($this->offsetExist($offset) && mysql_data_seek($this->result, $offset)){
	        
	        $this->current_row = $offset;
	        
	        return true;
	    }else{
	        return false;
	    }
	}
	
	public function current(){
	    
	    if (!$this->seek($this->current_row)){
	        return null;
	    }
	    
	    return mysql_fetch_assoc($this->result);
	}
	
}
?>