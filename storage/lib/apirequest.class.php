<?php

abstract class APIRequest
{
    abstract function getAction();
    abstract function getResource();
}

?>