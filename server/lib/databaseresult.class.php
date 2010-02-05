<?php
/**
 * Abstract class for databare result.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

abstract class DatabaseResult
{
    protected $result;
    
    protected $total_rows  = 0;
    protected $current_row = 0;
    protected $insert_id;
    protected $sql;
    
    abstract public function __construct($result, $sql, $link);
    
    abstract public function __destruct();
    
    abstract public function all($field = null);
    
    public function insert_id(){
        
        return $this->insert_id;
    }
    
    public function get($name = null){
        
        $row = $this->current();
        
        if ($name === null){
            return $row;
        }else{
            return $row[$name];
        }
	}
	
	public function first($name = null){
	    $this->current_row = 0;
	    return $this->get($name);
	}
	
	public function offsetExist($offset){
	    
	    return ($offset >=0 && $offset < $this->total_rows);
	}
	
	public function count(){
	    
        return $this->total_rows;
	}
	
	public function rewind(){
	    
	    $this->current_row = 0;
	    return $this;
	}
	
}
?>