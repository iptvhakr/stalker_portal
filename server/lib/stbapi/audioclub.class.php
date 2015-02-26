<?php

namespace Stalker\Lib\StbApi;

interface Audioclub
{
    public function createLink();

    public function getCategories();

    public function getOrderedList();

    public function getTrackList();

    public function getUserPlaylists();

    public function createPlaylist();

    public function addTrackToPlaylist();

    public function removeFromPlaylist();

    public function deletePlaylist();
}