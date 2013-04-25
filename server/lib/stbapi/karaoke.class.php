<?php

namespace Stalker\Lib\StbApi;

interface Karaoke
{
    public function getAbc();

    public function createLink();

    public function setClaim();

    public function getOrderedList();
}