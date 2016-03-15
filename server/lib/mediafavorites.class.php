<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;

class MediaFavorites implements \Stalker\Lib\StbApi\MediaFavorites
{
    public function getAll(){
        return System::base64_decode(Mysql::getInstance()->from('media_favorites')->where(array('uid' => Stb::getInstance()->id))->get()->first('favorites'));
    }

    public function save(){
        $favorites = @$_REQUEST['favorites'];

        if (empty($favorites)){
            $favorites = '""';
        }

        $favorites = System::base64_encode($favorites);

        $record = Mysql::getInstance()->from('media_favorites')->where(array('uid' => Stb::getInstance()->id))->get()->first();

        if (empty($record)){
            return Mysql::getInstance()->insert('media_favorites', array('favorites' => $favorites, 'uid' => Stb::getInstance()->id))->insert_id();
        }else{
            return Mysql::getInstance()->update('media_favorites', array('favorites' => $favorites), array('uid' => Stb::getInstance()->id))->result();
        }
    }
}