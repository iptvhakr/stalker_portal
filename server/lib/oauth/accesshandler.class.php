<?php

namespace Stalker\Lib\OAuth;

abstract class AccessHandler
{
    public function checkUserAuth($username, $password, $mac = null, $serial_number = null, OAuthRequest $request){}

    public function generateUniqueToken(\User $user){}

    public function isValidClient($client_id, $client_secret){}

    public function getAdditionalParams(\User $user){}

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

    /**
     * @param \User $user
     * @return string|null
     */
    public function getSecretKey(\User $user){}
}
