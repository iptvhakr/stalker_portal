<?php

namespace Stalker\Lib\Utils;

class FormatJSON extends Format
{
    public function __construct($array){
        $this->formatted = json_encode($array);
    }
}