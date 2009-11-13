<?php
/**
 * Abstract class for all data classes (MySQL, Memcache...).
 * @package stalker_portal
 */
abstract class Data
{
    abstract function getData(){}
    
    abstract function insertData(){}
    
    abstract function updateData(){}
    
    abstract function deleteData(){}
    
/*    abstract function setData(){}*/
    
    protected function getKey($prefix, $data = array()){
        
    }
    
    public function getFirstData(){
        $result = call_user_func_array(array($this, 'getData'), func_get_args());
        
        if (is_array($result) && count($result) > 0){
            return $result[0];
        }
        
        return $result;
    }
    
}
?>