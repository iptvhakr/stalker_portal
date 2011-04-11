<?php

class RESTResponse
{

    protected $body = array('status' => 'OK', 'results' => '');

    public function __construct(){

    }

    public function setError($text){
        $this->body['error'] = $text;
        $this->body['status'] = 'ERROR';
    }

    public function setBody($body){
        $this->body['results'] = $body;
    }

    public function send(){
        header("Content-Type: application/json");
        echo json_encode($this->body);
    }
}

?>