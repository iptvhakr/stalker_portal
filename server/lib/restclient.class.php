<?php

class RESTClient
{
    private $resource;
    private $rest_server;
    private $method;
    private $ids;
    private $data;
    public static $from;
    private $auth_login;
    private $auth_password;

    public function __construct($rest_server){
        $this->rest_server = $rest_server;
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
            $this->auth_login = $login;
            $this->auth_password = $password;
        }
    }

    private function execute(){

        $url = $this->rest_server  . $this->resource . '/' . ((!empty($this->ids)) ? implode(',', $this->ids) : '');

        //$stream_params = array('method' => $this->method);

        $headers = array();

        $headers[] = "Connection: close";
        $headers[] = "X-From: ".self::$from;

        if (!empty(self::$auth_login) && !empty(self::$auth_password)){
            $headers[] = "Authorization: Basic ".base64_encode(self::$auth_login.":".self::$auth_password);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

        if ($this->method == 'POST' || $this->method == 'PUT'){

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }

        $json_result = curl_exec($ch);

        $this->reset();

        if ($json_result === false){
            throw new RESTClientConnectException('Error get contents from url: '.$url.'; Error: '.curl_error($ch));
        }

        $result = json_decode($json_result, true);

        //var_dump($json_result);

        if (!empty($result['output'])){
            echo $result['output'];
        }

        if ($result === null){
            throw new RESTClientException("Result cannot be decoded. Result: ".$json_result);
        }

        if ($result['status'] != 'OK'){

            $error = !empty($result['error']) ? $result['error'] : "No description of the error. Result: ".$json_result;

            throw new RESTClientException($error);
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
class RESTClientConnectException extends RESTClientException{}

?>