<?php

namespace Stalker\Lib\StbApi;

interface Vod
{
    public function createLink();

    public function delLink();

    public function setPlayed();

    public function setClaim();

    public function getGenresByCategoryAlias();

    public function getYears();

    public function getAbc();

    public function setNotEnded();

    public function setEnded();

    public function setFav();

    public function delFav();

    public function getCategories();

    public function getOrderedList();
}