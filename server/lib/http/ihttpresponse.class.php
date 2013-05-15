<?php

namespace Stalker\Lib\HTTP;

interface IHTTPResponse
{
    public function setStatus($status);
    public function setContentType($content_type);
    public function setBody($body);
    public function send();
}
