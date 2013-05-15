<?php

namespace Stalker\Lib\OAuth;

class BearerAccessType extends AccessTokenType
{
    protected $type = 'Bearer';

    protected function parseAuthHeader(){

        if (preg_match('/Bearer\s+(.*)$/i', $this->request->getAuthorization(), $matches)){
            $this->access_token = trim($matches[1]);
        }else{
            throw new AuthBadRequest("Wrong Bearer authorization header");
        }
    }

    public function getToken(){
        return $this->access_token;
    }

    public function checkRequest(){

        $session = $this->access_handler->getAccessSessionByToken($this->access_token);

        if (empty($session)){
            throw new AuthUnauthorized("Access token wrong or expired");
        }

        if (preg_match("/\/users\/(\d+)/", $this->request->getRequestUri(), $match)){
            if ($match[1] != $session['uid']){
                throw new AuthForbidden("Access denied");
            }
        }
    }
}

?>