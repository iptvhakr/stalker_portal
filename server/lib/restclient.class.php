<?php

class RESTClient
{
    private $resource;
    private $rest_server;
    private $method;
    private $ids;
    private $data;
    public static $from;
    private static $auth_login;
    private static $auth_password;
    private static $access_token;
    private $timeout;

    public function __construct($rest_server){
        $this->rest_server = $rest_server;
        $this->timeout     = (int) Config::getSafe('rest_client_timeout', 3);
        $this->connection_timeout = (int) Config::getSafe('rest_client_connection_timeout', 1);
    }

    public function get(){

        $this->method = "GET";
        return $this->execute();
    }

    public function create($data = array()){

        $this->method = "POST";
        $this->data = $data;
        return $this->execute();
    }

    public function update($data = array()){

        $this->method = "PUT";
        $this->data = $data;
        return $this->execute();
    }

    public function delete(){

        $this->method = "DELETE";
        return $this->execute();
    }

    public function resource($resource){
        $this->resource = $resource;
        return $this;
    }

    public function ids($ids){

        if (!is_array($ids)){
            $ids = array($ids);
        }

        $this->ids = $ids;

        return $this;
    }

    public function setAuthParams($login, $password){
        if (!empty($login) && !empty($password)){
            self::$auth_login    = $login;
            self::$auth_password = $password;
        }
    }

    public static function setAccessToken($token){
        self::$access_token = $token;
    }

    private function execute(){

        $url = $this->rest_server  . $this->resource . '/' . ((!empty($this->ids)) ? implode(',', $this->ids) : '');

        //$stream_params = array('method' => $this->method);

        $headers = array();

        $headers[] = "Connection: close";
        $headers[] = "X-From: ".self::$from;

        if (!empty(self::$access_token)){
            $headers[] = "Authorization: Bearer ".self::$access_token;
        }elseif (!empty(self::$auth_login) && !empty(self::$auth_password)){
            $headers[] = "Authorization: Basic ".base64_encode(self::$auth_login.":".self::$auth_password);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  $this->method);
        curl_setopt($ch, CURLOPT_TIMEOUT,        $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connection_timeout);

        if ($this->method == 'POST' || $this->method == 'PUT'){

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }

        $json_result = curl_exec($ch);

        $this->reset();

        if ($json_result === false){
            if (curl_errno($ch) == 28){
                throw new RESTClientConnectionTimeout('Connection timeout. url: '.$url.'; Error: '.curl_error($ch));
            }else{
                throw new RESTClientConnectionFailure('Error get contents from url: '.$url.'; Error: '.curl_error($ch));
            }
        }

        $result = json_decode($json_result, true);

        //var_dump($json_result);

        if (!empty($result['output'])){
            echo $result['output'];
        }

        if ($result === null){
            throw new RESTClientUnknownFormat("Result cannot be decoded. Result: ".$json_result);
        }

        if ($result['status'] != 'OK'){

            $error = !empty($result['error']) ? $result['error'] : "No description of the error. Result: ".$json_result;

            throw new RESTClientRemoteError($error);
        }

        return $result['results'];
    }

    private function reset(){
        $this->ids  = array();
        $this->data = array();
        $this->resource    = '';
        $this->method      = '';
    }
}

class RESTClientException extends Exception{}
class RESTClientConnectionTimeout extends RESTClientException{}
class RESTClientConnectionFailure extends RESTClientException{}
class RESTClientUnknownFormat extends RESTClientException{}
class RESTClientConnectException extends RESTClientException{}
class RESTClientRemoteError extends RESTClientException{}

?>