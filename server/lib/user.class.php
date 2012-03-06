<?php

class User
{
    private $id;

    public function __construct($uid = 0){
        $this->id = (int) $uid;
    }

    public function getVideoFavorites(){

        $fav_video_arr = Mysql::getInstance()->from('fav_vclub')->where(array('uid' => $this->id))->get()->first();

        if (empty($fav_video_arr)){
            return array();
        }

        $fav_video = unserialize($fav_video_arr['fav_video']);

        if (!is_array($fav_video)){
            $fav_video = array();
        }

        return $fav_video;
    }
}