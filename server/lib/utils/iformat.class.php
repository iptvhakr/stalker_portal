<?php

namespace Stalker\Lib\Utils;

interface IFormat
{
    /**
     * @param array $array
     */
    public function __construct($array);

    /**
     * @return string
     */
    public function getOutput();
}
