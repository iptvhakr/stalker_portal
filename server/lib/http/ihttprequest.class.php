<?php

namespace Stalker\Lib\HTTP;

interface IHTTPRequest
{
    public function getMethod();
    public function getRequestUri();
    public function getAcceptType();
    public function getAuthorization();
    public function getData();
    public function getAcceptLanguage();
}

?>