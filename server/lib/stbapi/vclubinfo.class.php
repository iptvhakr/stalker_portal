<?php

namespace Stalker\Lib\StbApi;


interface vclubinfo {

    public static function getInfoById($id);

    public static function getInfoByName($orig_name);

    public static function getRatingByName($orig_name);

    public static function getRatingById($kinopoisk_id);

}