<?php

namespace Stalker\Lib\OAuth;

abstract class AccessHandler
{
    public function checkUserAuth($username, $password, $mac = null, $serial_number = null, OAuthRequest $request){}

    public function generateUniqueToken($username){}

    public function isValidClient($client_id, $client_secret){}

    public function getAdditionalParams($username){}

    /**
     * @param $token
     * @return array|null
     */
    public function getAccessSessionByToken($token){}

    /**
     * @param $key
     * @return array|null
     */
    public function getAccessSessionByDeveloperApiKey($key){}

    public function getSecretKey($username){}
}
?>