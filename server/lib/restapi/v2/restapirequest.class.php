<?php

namespace Stalker\Lib\RESTAPI\v2;

use Stalker\Lib\HTTP\HTTPRequest as HTTPRequest;
use Stalker\Lib\OAuth;
    
class RESTApiRequest extends HTTPRequest
{

    protected $resource;
    protected $action;
    protected $authorization;

    /*public function __construct(){

        parent::__construct();
    }*/

    public function init(){

        if (empty($this->method)){
            throw new RESTBadRequest("Empty request method");
        }

        if ($this->method == 'OPTIONS'){
            exit;
        }

        if (!in_array($this->method, array('GET', 'POST', 'PUT', 'DELETE'))){
            throw new RESTNotAllowedMethod("Method not allowed");
        }

        if (!in_array($this->accept_type, array('application/json', 'application/javascript', 'text/xml', 'text/html'))){
            throw new RESTNotAcceptable("Not acceptable type");
        }

        $this->parseAction();
        $this->parseResource();
        /*$this->parseAuthorization();*/
    }

    private function parseResource(){

        if (empty($_GET['_resource'])){
            throw new RESTBadRequest("Empty resource");
        }

        $requested_uri = $_GET['_resource'];

        $params = explode ("/", $requested_uri);

        if (empty($params[count($params)-1])){
            unset($params[count($params)-1]);
        }

        if (count($params) == 0){
            throw new RESTBadRequest("Empty resource");
        }

        $this->resource = $params;
    }

    private function parseAction(){
        
        $methods_map = array('get' => 'get', 'post' => 'create', 'put' => 'update', 'delete' => 'delete');

        $this->action = $methods_map[strtolower($this->method)];
    }

    //private function parseAuthorization(){

        /*if (empty($this->authorization)){
            return;
        }

        if (strpos("MAC", $this->authorization) == 0){
            $this->authorization = new OAuth\MACAccessType($this);
        }else if (strpos("Bearer", $this->authorization) == 0){
            $this->authorization = new OAuth\BearerAccessType($this);
        }*/

        /*if (preg_match('/(\S+)\s+(.*)$/i', $this->authorization, $matches)){
            $auth_info['type'] = $matches[1];

            if (preg_match('/id="([^"]+)"/i', $matches[2], $params)){
                $auth_info['id'] = $params[1];
            }

            if (preg_match('/nonce="([^"]+)"/i', $matches[2], $params)){
                $auth_info['nonce'] = $params[1];
            }

            if (preg_match('/mac="([^"]+)"/i', $matches[2], $params)){
                $auth_info['mac'] = $params[1];
            }
        }

        if (preg_match('/Bearer\s+(.*)$/i', $this->authorization, $matches)){
            $auth_info['schema'] = 'Bearer';
            $auth_info['token']  = $matches[1];
        }else if (preg_match('/MAC\s+(.*)$/i', $this->authorization, $matches)){
            $auth_info['schema'] = 'MAC';
            $auth_info['params'] = $matches[1];
        }else{
            return false;
        }*/
    //}

    public final function getAction(){
        return $this->action;
    }

    public final function getResource(){
        return $this->resource;
    }

    public final function getTarget(){
        return $this->resource[0];
    }

    /*public final function getAuthorization(){
        return $this->authorization;
    }*/

    public function getOffset(){
        return !empty($_GET['offset']) ? intval($_GET['offset']) : null;
    }

    public function getLimit(){
        return !empty($_GET['limit']) ? intval($_GET['limit']) : null;
    }

    public function getSearch(){
        return $this->getParam('q');
    }

    public function getLanguage(){
        if (empty($this->accept_language)){
            return null;
        }

        return substr($this->accept_language, 0, 2);
    }
}

class RESTException extends \Exception{}

abstract class RESTRequestException extends RESTException{}

class RESTBadRequest extends RESTRequestException{
    protected $code = "400 Bad Request";
}

class RESTUnauthorized extends RESTRequestException{
    protected $code = "401 Unauthorized";
}

class RESTForbidden extends RESTRequestException{
    protected $code = "403 Forbidden";
}

class RESTNotFound extends RESTRequestException{
    protected $code = "404 Not Found";
}

class RESTNotAllowedMethod extends RESTRequestException{
    protected $code = "405 Method Not Allowed";
}

class RESTNotAcceptable extends RESTRequestException{
    protected $code = "406 Not Acceptable";
}

class RESTServerError extends RESTRequestException{
    protected $code = "500 Internal Server Error";
}

class RESTTemporaryUnavailable extends RESTRequestException{
    protected $code = "503 Service Unavailable";
}