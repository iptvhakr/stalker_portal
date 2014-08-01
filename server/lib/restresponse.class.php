<?php

class RESTResponse
{

    protected $body = array('status' => 'OK', 'results' => '');
    /** @var  RESTRequest */
    private $request;
    private $content_type = 'application/json';

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

    public function setContentType($content_type){
        $this->content_type = $content_type;
    }

    public function send(){

        if (!empty($this->request) && strpos($this->request->getAccept(), 'text/channel-monitoring-id-url') !== false){
            if (is_array($this->body['results'])){

                $channels = array_filter($this->body['results'], function($channel){
                    return $channel['enable_monitoring'];
                });

                //$channels = $this->body['results'];

                if (preg_match("/items=(\d+)-(\d*)/", $this->request->getAccept(), $match)){
                    $start = $match[1];
                    $end   = empty($match[2]) ? count($channels) : $match[2];

                    $channels = array_slice($channels, $start-1, $end-$start+1);

                    //var_dump($start, $end, $channels);
                } elseif (preg_match("/part=(\d+)\/(\d+)/", $this->request->getAccept(), $match)){
                    $length = count($channels);
                    $start  = round((intval($match[1])-1) * ($length/intval($match[2])));
                    $end    = round((intval($match[1])) * ($length/intval($match[2])));

                    $channels = array_slice($channels, $start, $end-$start);

                    //var_dump($length, $start, $end);
                }

                $body = array_reduce($channels, function($prev, $curr){
                    return $prev.$curr['id'].' '.$curr['url'].(isset($curr['type']) ? ' '.$curr['type'] : '')."\n";
                }, '');

                header("Content-Type: text/plain");

                echo $body;
            }
            return;
        }

        header("Content-Type: ".$this->content_type);

        $this->setOutput();
        $response = json_encode($this->body);
        echo $response;
        ob_end_flush();

        if (!Config::getSafe('enable_api_log', true)){
            return;
        }
        
        $logger = new Logger();
        $logger->setPrefix("api_");

        // format: ip - login - [date] method "query" - "data" response_bytes;
        $logger->access(sprintf("%s - %s - [%s] %s \"%s\" - \"%s\" %d\n",
            empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
            @$_SERVER['PHP_AUTH_USER'],
            date("r"),
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            empty($this->request) ? 'no resource' : http_build_query($this->request->getData()),
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
                empty($this->request) ? 'no resource' : http_build_query($this->request->getData()),
                $this->body['error']
            ));
        }
    }
}

?>