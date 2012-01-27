<?php

class RESTResponse
{

    protected $body = array('status' => 'OK', 'results' => '');
    private $request;

    public function __construct(){
        ob_start();
    }

    public function setError($text){
        $this->body['error']  = $text;
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

    public function setRequest($request){
        $this->request = $request;
    }

    public function sendAuthRequest(){
        header('WWW-Authenticate: Basic realm="Stalker API"');
        header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
        $this->setError("401 Unauthorized request");
        $this->send();
        exit;
    }

    public function send(){
        header("Content-Type: application/json");
        $this->setOutput();
        $response = json_encode($this->body);
        echo $response;
        ob_end_flush();
        
        $logger = new Logger();
        $logger->setPrefix("api_");

        // format: ip - login - [date] method "query" - "data" response_bytes;
        $logger->access(sprintf("%s - %s - [%s] %s \"%s\" - \"%s\" %d\n",
            empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
            @$_SERVER['PHP_AUTH_USER'],
            date("r"),
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            http_build_query($this->request->getData()),
            strlen($response)
        ));

        if (!empty($this->body['error'])){
            // format: ip - login - [date] method "query" - "data": error message;
            $logger->error(sprintf("%s - %s - [%s] %s \"%s\" - \"%s\": %s\n",
                empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
                @$_SERVER['PHP_AUTH_USER'],
                date("r"),
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'],
                http_build_query($this->request->getData()),
                $this->body['error']
            ));
        }
    }
}

?>