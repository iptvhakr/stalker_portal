<?php

abstract class AccessHandler
{
    public function checkUserAuth($username, $password){}

    public function generateUniqueToken($username){}

    public function isValidClient($client_id, $client_secret){}

    public function getAdditionalParams($username){}
}
?>