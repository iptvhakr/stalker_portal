<?php

namespace Stalker\Lib\StbApi;

interface Weather
{
    public function getCurrent();

    public function getForecast();
}