<?php
/**
 * Data class for AJAX loader.
 * Call method associated with $action, from context associated with $type.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */
 
class DataLoader
{
    
    private $type;
    private $action;
    private $context;
    private $interface;
    private $method;
    
    public function __construct($type, $action){
        
        $this->type   = $type;
        $this->action = $action;
        
        try {
            $this->context = $this->getContext();
            $this->method  = $this->getMethod();
            
        }catch (Exception $e){
            error_log($e->getMessage());
        }
    }
    
    public function getResult(){

        if (empty($this->context) || empty($this->method)){
            return false;
        }

        try {
            $result = call_user_func(array($this->context, $this->method));
            return $result;
        
        }catch (Exception $e){
            error_log($e->getTraceAsString());
        }
    }
    
    private function getContext(){
     
        $class = implode(array_map(function($part){
            return ucfirst($part);
        },explode('_', $this->type)));

        $this->interface = "Stalker\\Lib\\StbApi\\".$class;

        if (!interface_exists($this->interface)){
            throw new Exception('Class "'.$class.'" not exist');
        }
        
        if (is_callable(array($class, 'getInstance'))){
            return call_user_func(array($class, 'getInstance'));
        }else{
            return new $class;
        }

    }
    
    private function getMethod(){
        
        if (empty($this->action)){
            throw new Exception('Action is empty');
        }
        
        $parts = explode('_', $this->action);
        
        
        for($i=0; $i < count($parts); $i++){

            if ($i == 0){
                continue;
            }
            
            $parts[$i] = ucfirst($parts[$i]);
        }
        
        $method = implode($parts);
        
        if (method_exists($this->interface, $method)){
            return $method;
        }else{
            throw new Exception('Method "'.$method.'" not exist in '.$this->interface);
        }
    }
}
