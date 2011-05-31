<?php

class RESTClient
{
    private $resource;
    private $rest_server;
    private $method;
    private $ids;
    private $data;
    public static $from;

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

    private function execute(){

        $url = $this->rest_server  . $this->resource . '/' . ((!empty($this->ids)) ? implode(',', $this->ids) : '');

        $stream_params = array('method' => $this->method);

        $headers  = "Connection: close\r\n";
        $headers .= "X-From: ".self::$from."\r\n";

        //if (($this->method == 'POST' || $this->method == 'PUT') && !empty($this->data)){
        if ($this->method == 'POST' || $this->method == 'PUT'){

            $data_url = http_build_query($this->data);

            $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $headers .= "Content-Length: ".strlen($data_url)."\r\n";

            $stream_params['header']  = $headers;
            $stream_params['content'] = $data_url;
        }

        $json_result = @file_get_contents($url, false, stream_context_create(array('http' => $stream_params)));

        if ($json_result === false){
            throw new RESTClientConnectException('Error get contents from url: '.$url);
        }

        $result = json_decode($json_result, true);

        var_dump($json_result);

        if (!empty($result['output'])){
            echo $result['output'];
        }

        if ($result === null){
            throw new RESTClientException("Result cannot be decoded");
        }

        if ($result['status'] != 'OK'){

            $error = !empty($result['error']) ? $result['error'] : "No description of the error";

            throw new RESTClientException($error);
        }

        return $result['results'];
    }

    private function reset(){
        
    }
}

class RESTClientException extends Exception{}
class RESTClientConnectException extends RESTClientException{}

?>