<?php
/**
 * STB authorization and authentication
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Stb
{
    public $id  = 0;
    public $mac = '';
    public $ip;
    public $hd  = 0;
    public $params = array();
    private $db;
    
    static private $instance = NULL;
    
    static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Stb();
        }
        return self::$instance;
    }
    
    public function __construct(){
        $this->mac = @trim(urldecode($_COOKIE['mac']));
        if (@$_SERVER['HTTP_X_REAL_IP']){
            $this->ip = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->db = Database::getInstance(DB_NAME);
        $this->getStbParams();
    }
    
    public function setId($id){
        $this->id = $id;
        $this->params['id'] = $id;
    }
    
    public function getStbParams(){
        $sql = "select * from users where mac='$this->mac'";
        $rs = $this->db->executeQuery($sql);
        $params = @$rs->getValuesByRow(0);
        if (is_array($params)){
            $this->params = $params;
            $this->id  = $params['id'];
            $this->hd  = $params['hd'];
            $this->additional_services_on = $params['additional_services_on'];
        }
    }
    
    public function getIdByMAC($mac){
        $sql = "select * from users where mac='$mac'";
        $rs = $this->db->executeQuery($sql);
        $id = intval($rs->getValueByName(0, 'id'));
        if ($id>0){
            return $id;
        }else{
            return false;
        }
    }
    
    private function getAllMACs(){
        $sql = "select * from users";
        $rs = $this->db->executeQuery($sql);
        $arr = array();
        while(@$rs->next()){
            $arr[$rs->getCurrentValueByName('mac')] = intval($rs->getCurrentValueByName('id'));
        }
        return $arr;
    }
}
?>