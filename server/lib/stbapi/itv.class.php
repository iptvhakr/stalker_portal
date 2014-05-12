<?php

namespace Stalker\Lib\StbApi;

interface Itv
{
    public function createLink();

    public function setLastId();

    public function setPlayed();

    public function setFav();

    public function getAllFavChannels();

    public function setClaim();

    public function setFavStatus();

    public function addToCensored();

    public function delFromCensored();

    public function getShortEpg();

    public function getGenres();

    public function getEpgInfo();

    public function getOrderedList();

    public function getAllChannels();

    public function getFavIds();

    public function saveDvbChannels();
}