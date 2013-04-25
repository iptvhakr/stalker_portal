<?php

namespace Stalker\Lib\StbApi;

interface Watchdog
{
    public function getEvents();

    public function confirmEvent();
}