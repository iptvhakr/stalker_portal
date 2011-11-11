<?php
/**
 * Cache database result.
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class CacheResult extends DatabaseResult
{
    
    private $data;
    
    public function __construct($data, $sql){
        
        $this->data       = $data;
        $this->sql        = $sql;
        $this->total_rows = count($data);
    }
    
    public function __destruct(){
        
    }
    
    public function as_array($return = false, $field = null){
        
        if (!$return){
            
            return $this;
        }

        $array = array();

	    if ($this->total_rows > 0){

            reset($this->data);

            if($field !== null){
                //while ($row = mysql_fetch_assoc($this->result)){
                foreach ($this->data as $row){
    				$array[] = $row[$field];
    			}
            }else{
                //while ($row = mysql_fetch_assoc($this->result)){
                foreach ($this->data as $row){
    				$array[] = $row;
                }
            }

		}

        return $array;
        
        //return $this->data;
    }
    
    public function all($field = null){
        
        return $this->as_array(true, $field);
    }
    
    public function seek($offset){
        
        if (!$this->offsetExist($offset)){
            return false;
        }
        
        $this->current_row = $offset;
        
        return true;
    }
    
    public function current(){
        
        return $this->data[$this->current_row];
    }

    public function counter(){
        return $this->total_rows;
    }
}

?>