<?php

namespace Stalker\Lib\StbApi;

interface Advertising{

    public static function registration($full_name, $email, $phone, $region);

    public function getAd();

}