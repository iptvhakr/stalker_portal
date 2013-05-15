<?php

class AjaxBackend
{
    protected $body = array('js' => '', 'text' => '');

    private $content_type = 'application/json';

    public function __construct(){
        ob_start();
    }

    public function setBody($body){
        $this->body['js'] = $body;
    }

    private function setOutput(){

        $output = ob_get_contents();
        ob_end_clean();

        if ($output){
            $this->body['text'] = $output;
        }
    }

    public function send(){

        header("Content-Type: ".$this->content_type);

        $this->setOutput();
        $response = json_encode($this->body);
        echo $response;
        ob_end_flush();
    }
}