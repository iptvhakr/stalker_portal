<?php

namespace Stalker\Lib\RESTAPI\v1;

abstract class APIRequest
{
    abstract function getAction();
    abstract function getResource();
}

?>