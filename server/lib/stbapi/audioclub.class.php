<?php

namespace Stalker\Lib\StbApi;

interface Audioclub
{
    public function createLink();

    /*public function setPlayed();

    public function getYears();

    public function getGenres();

    public function getLanguages();

    public function setFav();

    public function delFav();*/

    public function getCategories();

    public function getOrderedList();

    /*public function getPerformersList();

    public function getGenresList();

    public function getYearsList();*/

    public function getTrackList();
}