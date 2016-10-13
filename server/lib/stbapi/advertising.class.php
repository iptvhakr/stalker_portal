<?php

namespace Stalker\Lib\StbApi;

interface Advertising{

    public static function registration($full_name, $email, $phone, $region, $additional_request = false);

    public function getAd();

}