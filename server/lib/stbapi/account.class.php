<?php

namespace Stalker\Lib\StbApi;

interface Account
{
    public function checkPrice();

    public function subscribeToPackage();

    public function unsubscribeFromPackage();

    public function checkVideoPrice();

    public function rentVideo();
}