<?php

namespace Stalker\Lib\RESTAPI\v2;
use Stalker\Lib\HTTP\HTTPResponse;
use Stalker\Lib\Utils as Utils;

class RESTApiResponse extends HTTPResponse
{
    protected $body = array("status" => "OK", "results" => "");
    private   $request;

    public function __construct(){
        ob_start();
    }

    public function setError(\Exception $exception){

        if ($exception instanceof RESTRequestException){
            $this->status = $exception->getCode();
        }else{
            $this->status = self::STATUS_500;
        }

        $this->body['error']  = $exception->getMessage();
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

    public function setRequest(RESTApiRequest $request){
        $this->request = $request;
        $this->setContentType($request->getAcceptType());
    }

    public function send(){

        if (!empty($this->status)){
            header($_SERVER["SERVER_PROTOCOL"]." ".$this->status);
        }

        if (!empty($this->content_type)){
            header("Content-type: ".$this->content_type."; charset=utf-8");
        }

        $this->setOutput();
        echo $this->getFormattedOutput();
    }

    private function getFormattedOutput(){
        switch ($this->content_type){
            case 'application/json':
                $format = new Utils\FormatJSON($this->body);
                break;
            case 'application/javascript':
                $format = new Utils\FormatJSONP($this->body);
                break;
            case 'text/xml':
                $format = new Utils\FormatXML($this->body);
                break;
            case 'text/plain':
                $format = new Utils\FormatTEXT($this->body);
                break;
            case 'audio/x-mpegurl':
                $format = new Utils\FormatM3U($this->body['results']);
                break;
            default:
                $format = new Utils\FormatTEXT($this->body);
        }

        return $format->getOutput();
    }
}
