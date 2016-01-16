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
                'hd'       => 0
            ));
        }

        return true;
    }

    public static function log($video_id, $text, $moderator_id = null){

        if ($moderator_id === null){
            $moderator_id = $_SESSION['uid'];
        }

        $video = self::getById($video_id);

        return Mysql::getInstance()->insert('video_log', array(
            'action'       =>  $text,
            'video_id'     => $video_id,
            'video_name'   => $video['name'],
            'moderator_id' => $moderator_id,
            'actiontime'   => 'NOW()'
        ))->insert_id();
    }

    public function getRawAll(){

        return Mysql::getInstance()->from('video')->where(array('status' => 1, 'accessed' => 1));
    }

    public function filterList($list){

        for ($i = 0; $i < count($list); $i++){
            /// xgettext:no-php-format
            $list[$i]['name']   = sprintf(_('video_name_format'), $list[$i]['name'], $list[$i]['o_name']);
            $list[$i]['genres'] = implode(', ',
                array_map(
                    function($item){
                        return _($item);
                    },
                    Mysql::getInstance()->from('cat_genre')
                        ->in('id',
                        array(
                            $list[$i]['cat_genre_id_1'],
                            $list[$i]['cat_genre_id_2'],
                            $list[$i]['cat_genre_id_3'],
                            $list[$i]['cat_genre_id_4']))
                        ->get()->all('title')
                )
            );

            $list[$i]['genres_ids'] = array();

            for ($j = 1; $j<=4; $j++){
                if ($list[$i]['cat_genre_id_'.$j] > 0)
                    $list[$i]['genres_ids'][] = (int) $list[$i]['cat_genre_id_'.$j];
            }

            $list[$i]['cover'] = $this->getCoverUrl($list[$i]['id']);
        }

        return $list;
    }

    private function getCoverUrl($video_id){

        $cover = Mysql::getInstance()->from('screenshots')->where(array('media_id' => intval($video_id)))->get()->first();

        if (empty($cover)){
            return false;
        }

        $dir_name = ceil($cover['id']/100);
        $dir_path = Config::get('screenshots_url').$dir_name;
        $ext = pathinfo($cover['name'], PATHINFO_EXTENSION);
        if (!$ext){
            $ext = 'jpg';
        }
        $dir_path .= '/'.$cover['id'].'.'.$ext;
        return $dir_path;
    }

    public function setLocale($language){
        Stb::getInstance()->initLocale($language);
    }

    public function getFavorites(){

    }

    public static function getNotEnded(){

        $raw = Mysql::getInstance()->from('vclub_not_ended')->where(array('uid' => Stb::getInstance()->id))->get()->all();

        $not_ended = array();

        foreach ($raw as $video){
            $not_ended[$video['video_id']] = $video;
        }

        return $not_ended;
    }

    public static function getServices(){

        return Mysql::getInstance()->select('id, name')->from('video')->orderby('name')->get()->all();
    }
}
