<?php

namespace Stalker\Lib\StbApi;

interface Weatherco
{
    public function getCurrent();

    public function getForecast();
}