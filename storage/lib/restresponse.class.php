<?php

class RESTResponse
{

    protected $body = array('status' => 'OK', 'results' => '');

    public function __construct(){
        ob_start();
    }

    public function setError($text){
        $this->body['error'] = $text;
        $this->body['status'] = 'ERROR';
    }

    public function setBody($body){
        $this->body['results'] = $body;
    }

    private function setOutput(){
        $output = ob_get_contents();
        ob_end_clean();
        if ($output){
            $this->body['output'] = $output;
        }
    }

    public function send(){
        header("Content-Type: application/json");
        $this->setOutput();
        echo json_encode($this->body);
    }
}

?>