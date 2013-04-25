<?php

namespace Stalker\Lib\StbApi;

interface Epg
{
    public function getWeek();

    public function getAllProgramForCh();

    public function getDataTable();

    public function getSimpleDataTable();
}