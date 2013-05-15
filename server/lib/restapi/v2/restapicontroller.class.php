<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiController extends RESTApiResource
{
    protected $name;

    public function getName(){
        return $this->name;
    }

}