<?php

namespace Stalker\Lib\Utils;

class FormatXML extends Format
{
    private $xml;

    public function __construct($array){
        $this->xml = new \SimpleXMLElement('<response/>');

        // todo: items...
        array_walk_recursive($array, array($this, 'addNode'));
        $this->formatted = $this->xml->asXML();
    }

    private function addNode($value, $name){
        $this->xml->addChild($name, $value);
    }
}