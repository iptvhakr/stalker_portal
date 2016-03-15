<?php

namespace Stalker\Lib\SOAPApi\v1;

use Stalker\Lib\Core\Config;

class SoapApiServer
{
    private $server;
    private $handler = 'Stalker\Lib\SOAPApi\v1\SoapApiHandler';

    public function __construct(){
        ini_set("soap.wsdl_cache_enabled", "0");
        //$this->server = new \SoapServer(Config::get('wsdl_uri'), array('cache_wsdl' => WSDL_CACHE_NONE));
    }

    public function handleRequest(){
        $this->server = new \SoapServer(Config::get('wsdl_uri'), array('cache_wsdl' => WSDL_CACHE_NONE));
        $this->server->setClass($this->handler);
        $this->server->handle();
    }

    public function output($doc = true, $wsdl = false, $phpclient = false){
        require_once(PROJECT_PATH . '/lib/php-wsdl-2.3/class.phpwsdl.php');

        $soap = \PhpWsdl::CreateInstance(
            'API', // PhpWsdl will determine a good namespace
            null, // Change this to your SOAP endpoint URI (or keep it NULL and PhpWsdl will determine it)
            null, // Change this to a folder with write access
            Array( // All files with WSDL definitions in comments
                PROJECT_PATH . '/lib/soapapi/v1/soapapihandler.class.php'
            ),
            null, // The name of the class that serves the webservice will be determined by PhpWsdl
            null, // This demo contains all method definitions in comments
            null, // This demo contains all complex types in comments
            false, // Don't send WSDL right now
            false); // Don't start the SOAP server right now

        // Disable caching for demonstration
        ini_set('soap.wsdl_cache_enabled', 0); // Disable caching in PHP
        \PhpWsdl::$CacheTime = 0; // Disable caching in PhpWsdl

        if ($wsdl){
            $soap->ForceOutputWsdl = true;
        }else if ($phpclient){
            $soap->ForceOutputPhp = true;
        }else{
            $this->ForceOutputHtml = true;
        }
        //$soap->Optimize = false;
        $soap->RunServer();
    }

    public function outputWsdl(){
        $this->output(false, true);
    }

    public function outputPhpClient(){
        $this->output(false, false, true);
    }

    public function outputDocs(){
        $this->output(true);
    }
}