<?php

namespace Stalker\Lib\OAuth;

class MACAccessType extends AccessTokenType
{
    protected $type = 'MAC';

    private $id;
    private $ts;
    private $nonce;
    private $mac;
    private $key;

    public function parseAuthHeader(){

        if (preg_match('/MAC\s+(.*)$/i', $this->request->getAuthorization(), $matches)){

            if (preg_match('/id="([^"]+)"/i', $matches[1], $params)){
                $this->id = $this->access_token = $params[1];
            }else{
                throw new AuthBadRequest("Wrong [id] param in authorization header");
            }

            if (preg_match('/ts="(\d+)"/i', $matches[1], $params)){
                $this->ts = (int) $params[1];
            }else{
                throw new AuthBadRequest("Wrong [ts] param in authorization header");
            }

            if (preg_match('/nonce="([^"]+)"/i', $matches[1], $params)){
                $this->nonce = $params[1];
            }else{
                throw new AuthBadRequest("Wrong [nonce] param in authorization header");
            }

            if (preg_match('/mac="([^"]+)"/i', $matches[1], $params)){
                $this->mac = $params[1];
            }else{
                throw new AuthBadRequest("Wrong [mac] param in authorization header");
            }

        }else{
            throw new AuthBadRequest("Wrong MAC authorization header");
        }
    }

    public function getToken(){
        return $this->id;
    }

    public function checkRequest(){

        $session = $this->access_handler->getAccessSessionByToken($this->id);

        if (empty($session)){
            throw new AuthUnauthorized("Access token wrong or expired");
        }

        $this->key = $session['secret_key'];

        if (empty($session['time_delta'])){
            $time_delta = time() - $this->ts;
            $this->access_handler->setTimeDeltaForToken($this->id, $time_delta);
        }else{
            $time_delta = (int) $session['time_delta'];
        }

        $this_delta = time() - $this->ts;

        if (($time_delta - $this_delta) > 3 || ($time_delta - $this_delta) < 3){
            throw new AuthUnauthorized("Delta time very suspicious");
        }

        if (!$this->checkMacSignature()){
            throw new AuthUnauthorized("Mac signature mismatch");
        }

        if (preg_match("/\/users\/(\d+)/", $this->request->getRequestUri(), $match)){
            if ($match[1] != $session['uid']){
                throw new AuthForbidden("Access denied");
            }
        }
    }

    private function checkMacSignature(){

        if ($this->countSignature() == $this->mac){
            return true;
        }

        return false;
    }

    private function countSignature(){

        $request = $this->getNormalizedRequestString();

        return base64_encode(hash_hmac('sha256', $request, $this->key, true));
    }

    private function getNormalizedRequestString(){

        $normalized_request  = $this->ts."\n";
        $normalized_request .= $this->nonce."\n";
        $normalized_request .= $this->request->getMethod()."\n";
        $normalized_request .= $this->request->getRequestUri()."\n";
        $normalized_request .= $this->request->getHost()."\n";
        $normalized_request .= $this->request->getServerPort()."\n";
        $normalized_request .= $this->request->getRawData()."\n";

        return $normalized_request;
    }
}

?>