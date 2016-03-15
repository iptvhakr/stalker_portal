<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Stb;

class RESTRequest extends APIRequest
{
    private $action;
    private $resource;
    private $identifiers;
    private $data;
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

        parse_str(file_get_contents("php://input"), $this->data);
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

    public function getAccept(){
        return empty($_SERVER['HTTP_ACCEPT']) ? 'application/json' : $_SERVER['HTTP_ACCEPT'];
    }

    public static function useMacIdentifiers(){
        return self::$use_mac_identifiers = true;
    }

    public function getConvertedIdentifiers(){

        if (self::$use_mac_identifiers || (!empty($this->identifiers[0]) && strlen($this->identifiers[0]) >= 12)){
            //var_dump($this->identifiers);
            return Stb::getUidByMacs($this->identifiers);
        }else{
            return Stb::getUidByLs($this->identifiers);
        }
    }

    public function getPut(){
        return $this->data;
    }

    public function getData($key = ''){

        if (!empty($key)){
            if (!array_key_exists($key, $this->data)){
                return null;
            }
            return $this->data[$key];
        }

        return $this->data;
    }
}

class RESTRequestException extends \Exception {}
