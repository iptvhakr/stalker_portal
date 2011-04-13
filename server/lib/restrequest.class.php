<?php

class RESTRequest extends APIRequest
{
    private $action;
    private $resource;
    private $identifiers;
    private $put;
    private static $use_mac_identifiers = false;

    public function __construct(){
        $this->init();
    }

    protected function init(){

        if (empty($_SERVER['REQUEST_METHOD'])){
            throw new RESTRequestException("Empty request method");
        }

        if (empty($_GET['q'])){
            throw new RESTRequestException("Empty resource");
        }

        $requested_uri = $_GET['q'];

        //var_dump($_SERVER);

        $params = explode ("/", $requested_uri);

        if (empty($params[count($params)-1])){
            unset($params[count($params)-1]);
        }

        if (count($params) == 0){
            throw new RESTRequestException("Empty resource");
        }

        $this->resource = $params[0];

        if (count($params) > 1){
            $this->identifiers = explode(',', $params[1]);
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        $methods_map = array('get' => 'get', 'post' => 'create', 'put' => 'update', 'delete' => 'delete');

        if (empty($methods_map[$method])){
            throw new RESTRequestException("Not supported method");
        }

        $this->action = $methods_map[$method];

        parse_str(file_get_contents("php://input"),$this->put);
    }

    public function getAction(){
        return $this->action;
    }

    public function getResource(){
        return $this->resource;
    }

    public function getIdentifiers(){
        return $this->identifiers;
    }

    public static function useMacIdentifiers(){
        return self::$use_mac_identifiers = true;
    }

    public function getConvertedIdentifiers(){

        if (self::$use_mac_identifiers){
            return Stb::getUidByMacs($this->identifiers);
        }else{
            return Stb::getUidByLs($this->identifiers);
        }
    }

    public function getPut(){
        return $this->put;
    }
}

class RESTRequestException extends Exception {}

?>