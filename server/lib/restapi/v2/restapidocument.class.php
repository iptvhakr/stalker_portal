<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiDocument extends RESTApiResource
{
    public function __construct(){
        $this->controllers = new RESTApiControllersStorage();
    }
}
