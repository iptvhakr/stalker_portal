<?php

namespace Stalker\Lib\HTTP;

class HTTPRequest implements IHTTPRequest
{
    protected $method;
    protected $request_uri;
    protected $accept_type;
    protected $authorization;
    protected $data;
    protected $raw_data;
    protected $accept_language;

    public function __construct(){

        $this->method          = !empty($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null;
        $this->request_uri     = !empty($_SERVER["REQUEST_URI"]) ? str_replace('//', '/', $_SERVER['REQUEST_URI']) : null;
        $this->accept_type     = $this->parseAcceptType();
        $this->authorization   = !empty($_SERVER["HTTP_AUTHORIZATION"]) ? $_SERVER["HTTP_AUTHORIZATION"] : null;

        $this->raw_data        = file_get_contents("php://input");

        if (!empty($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json'){
            $this->data = json_decode($this->raw_data, true);

            if (empty($this->data)){
                $this->data = array();
            }
        }else{
            parse_str($this->raw_data, $this->data);
        }

        $this->accept_language = $this->parseAcceptLanguage();
    }

    protected function parseAcceptType(){

        $accept_type = !empty($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : null;

        if (empty($accept_type)){
            return null;
        }

        $accept_types = explode(",", $accept_type);

        return trim($accept_types[0]);
    }

    protected function parseAcceptLanguage(){

        $accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

        if (empty($accept_language)){
            return null;
        }

        $accept_languages = explode(",", $accept_language);

        return trim($accept_languages[0]);
    }

    public final function getMethod(){
        return $this->method;
    }

    public final function getRequestUri(){
        return $this->request_uri;
    }

    public final function getAcceptType(){
        return $this->accept_type;
    }

    public function getAuthorization(){
        return $this->authorization;
    }

    public function getRawData(){
        return $this->raw_data;
    }

    public final function getData($key = ''){
        
        if (!empty($key)){
            if (!array_key_exists($key, $this->data)){
                return null;
            }
            return $this->data[$key];
        }

        return $this->data;
    }

    public function getAcceptLanguage(){
        return $this->accept_language;
    }

    public function getHost(){
        $host = !empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";

        $host_parts = explode(":", $host);

        return $host_parts[0];
    }

    public function getServerPort(){

        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 443 : 80;
    }

    public function getParam($name){
        return empty($_GET[$name])? null : $_GET[$name];
    }
}

?>