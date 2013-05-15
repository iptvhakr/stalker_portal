<?php
/**
 * Administrator class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Administrator
{
    
    private $db;
    private $acl;
    private $id;
    private $login;
    
    public function __construct(){
        
        $this->db  = Mysql::getInstance();
        
        if ($this->isAuthorized()){
            $this->acl = new Acl($this->id);
        }
    }
    
    public function checkAuthorization($login, $pass){
        
        $admin = $this->db->from('administrators')
                          ->where(array('login' => $login))
                          ->get()
                          ->first();
        
        if (!empty($admin)){
            
            if ($admin['pass'] == md5($pass)){
                
                $_SESSION['uid']     = $admin['id'];
                $_SESSION['login']   = $admin['login'];
                $_SESSION['pass']    = $admin['pass'];
                
                return true;
            }
        }
        
        return false;
    }
    
    public function isAuthorized(){
        
        if (!empty($_SESSION['login']) && !empty($_SESSION['pass'])){
            
            $admin = $this->db->from('administrators')
                              ->where(array('login' => $login))
                              ->get()
                              ->first();
            
            if (!empty($admin['pass'])) {
                
            	if ($admin['pass'] == $_SESSION['pass']){
            	    
            	    $this->id    = $admin['id'];
            	    $this->login = $admin['login'];
            	    
            	    return true;
            	}
            }
        }
        
        return false;
    }
    
    public function isAlowed($resource, $action){
        
        return $this->acl($this->login, $resource, $action);
    }
}
?>