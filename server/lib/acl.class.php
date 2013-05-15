<?php
/**
 * Access Control List class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Acl
{
    
    private $default_actions = array('view', 'edit', 'add', 'delete');
    
    public function __construct($user){
        
    }
    
    public function addRole($name, $parent){
        
    }
    
    public function addResource($name){
        
    }
    
    public function allow($role, $resource, $right){
        
    }
    
    public function disallow($role, $resource, $right){
        
    }
    
    public function isAllowed($role, $resource, $action){
        
    }
}

?>