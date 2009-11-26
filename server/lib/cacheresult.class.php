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
    
    public function as_array($return = false){
        
        if (!$return){
            
            return $this;
        }
        
        return $this->data;
    }
    
    public function all(){
        
        return $this->as_array(true);
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
}

?>