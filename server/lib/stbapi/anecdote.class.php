<?php

namespace Stalker\Lib\StbApi;

interface Anecdote
{
    public function getByPage();

    public function setVote();

    public function getBookmark();

    public function setBookmark();
}