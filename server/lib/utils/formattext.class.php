<?php

namespace Stalker\Lib\Utils;

class FormatTEXT extends Format
{
    public function __construct($array){
        $this->formatted = '<pre>'.var_export($array, true).'</pre>';
    }
}