<?php

namespace Stalker\Lib\Utils;

abstract class Format implements IFormat
{
    protected $formatted;

    public function getOutput(){
        return $this->formatted;
    }
}