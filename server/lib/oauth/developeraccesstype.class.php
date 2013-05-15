<?php

namespace Stalker\Lib\OAuth;

class DeveloperAccessType extends AccessTokenType
{
    protected $type = 'Developer';
    private $api_key;

    protected function parseAuthHeader(){
        $this->api_key = $this->request->getParam('api_key');
    }

    public function getToken(){
        return $this->api_key;
    }

    public function checkRequest(){

        $session = $this->access_handler->getAccessSessionByDeveloperApiKey($this->api_key);

        if (empty($session)){
            throw new AuthUnauthorized("Developer api key wrong or expired");
        }

        if (preg_match("/\/users\/(\d+)/", $this->request->getRequestUri(), $match)){
            if ($match[1] != $session['uid']){
                throw new AuthForbidden("Access denied");
            }
        }

        return true;
    }

    public function getSession(){
        return $this->access_handler->getAccessSessionByDeveloperApiKey($this->api_key);
    }
}