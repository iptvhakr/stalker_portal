<?php

class Video
{

    public static function getById($id){

        $id = intval($id);

        return Mysql::getInstance()->from('video')->where(array('id' => $id))->get()->first();
    }

    public static function switchOnById($id){

        $id = intval($id);

        Mysql::getInstance()->update('video', array('accessed' => 1, 'added' => 'NOW()'), array('id' => $id));

        Mysql::getInstance()->update('updated_places', array('vclub' => 1));
        
        self::log($id, "on");
        self::disableForHDDevices($id);
    }

    public static function switchOffById($id){

        $id = intval($id);

        Mysql::getInstance()->update('video', array('accessed' => 0, 'added' => 'NOW()'), array('id' => $id));

        self::log($id, "off");
        self::enableForHDDevices($id);
    }

    private static function disableForHDDevices($id){

        $id = intval($id);

        return self::setDisableForHDDevices($id, 1);
    }

    private static function enableForHDDevices($id){

        $id = intval($id);

        return self::setDisableForHDDevices($id, 0);
    }

    private static function setDisableForHDDevices($id, $val){

        $id  = intval($id);
        $val = intval($val);

        $video = self::getById($id);

        if ($video['hd']){
            return Mysql::getInstance()->update('video', array('disable_for_hd_devices' => 1), array(
                'name'     => $video['name'],
                'o_name'   => $video['o_name'],
                'director' => $video['director'],
                'year'     => $video['year'],
                'hd'       => $val
            ));
        }

        return true;
    }

    public static function log($video_id, $text){

        return Mysql::getInstance()->insert('video_log', array(
            'action'       =>  $text,
            'video_id'     => intval($video_id),
            'moderator_id' => @intval($_SESSION['uid']),
            'actiontime'   => 'NOW()'
        ))->insert_id();
    }
}

?>