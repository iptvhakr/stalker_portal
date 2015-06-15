<?php

namespace Stalker\Lib\StbApi;

interface Radio
{
    public function getOrderedList();

    public function setFav();

    public function getAllFavRadio();

    public function setFavStatus();

    public function getFavIds();

    public function getChannelById();
}