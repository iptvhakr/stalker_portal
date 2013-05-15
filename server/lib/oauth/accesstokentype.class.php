<?php

namespace Stalker\Lib\OAuth;
use Stalker\Lib\HTTP\HTTPRequest;

abstract class AccessTokenType
{
    protected $type;
    protected $request;
    protected $access_handler;
    protected $access_token;

    public final function __construct(HTTPRequest $request,AccessHandler $access_handler){
        $this->request     = $request;
        $this->access_handler = $access_handler;
        $this->parseAuthHeader();
    }

    public function getType(){
        return $this->type;
    }

    abstract protected function parseAuthHeader();

    abstract public function getToken();

    abstract public function checkRequest();

    public function getSession(){
        return $this->access_handler->getAccessSessionByToken($this->access_token);
    }
}

?>