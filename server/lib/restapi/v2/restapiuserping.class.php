<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserPing extends RESTApiController
{

    protected $name = 'ping';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $user = \User::getInstance((int) $parent_id);
        $user->updateKeepAlive();

        return true;
    }
}