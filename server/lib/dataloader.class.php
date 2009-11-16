<?php
/**
 * Data class for AJAX loader.
 * @package stalker_portal
 */
 
class DataLoader extends Data 
{
    protected $db;
    protected $stb;
    
    protected function __construct($action){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
        
        
        $parts = explode('_', $action);
        
        for($i=0; $i<=count($parts); $i++){

            if ($i == 0){
                continue;
            }
            
            $parts[$i] = ucfirst($parts[$i]);
        }
        
        $method = implode($parts);
        
        if (method_exists($this, $method)){
            return $this->$method();
        }else{
            throw new Exception('Method '.$method.' not exist');
        }
        
    }
    
    protected function getData($source, $where = array()){
        $args = func_get_args();
        $key  = $this->getKey($source, $args);
        $tags = $this->getTags($where);
        
        call_user_func_array(array($this->db, 'getData'), $args);
    }
    
    /**
     * Update data array in destination
     *
     * @param string $destination
     * @param array $data
     * @param array $where
     * @return bool
     */
    protected function updateData($destination, $data, $where = array()){
        
        return call_user_func_array(array($this->db, 'updateData'), $args);
    }
    
    /**
     * Insert data array in destination
     *
     * @param string $destination
     * @param array $data
     * @return int last insert id
     */
    protected function insertData($destination, $data){
        
        return call_user_func_array(array($this->db, 'insertData'), $args);
    }
    
    
}

?>