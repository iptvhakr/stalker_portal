<?php

namespace Stalker\Lib\StbApi;

interface TvArchive
{
    public function createLink();

    public function getLinkForChannel();

    public function getNextPartUrl();
}