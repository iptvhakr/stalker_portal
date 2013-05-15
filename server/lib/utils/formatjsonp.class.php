<?php

namespace Stalker\Lib\Utils;

class FormatJSONP extends Format
{
    private $callback;

    public function __construct($array){

        if (empty($this->callback)){
            $this->callback = 'response_callback';
        }

        $this->formatted = $this->callback.'('.json_encode($array).')';
    }

    public function setCallback($callback){
        $this->callback = $callback;
    }
}