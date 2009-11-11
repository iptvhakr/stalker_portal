<?php

function get_data(){
    $where = '';
    
    $page  = 0;
    $selected_item = 0;
    $cur_page      = 0;
    $stb = Stb::getInstance();
    $uid = $stb->id;
    
    $abc_ru = array('*','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','0-9');
    $abc_en = array('*','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','W','Z', '0-9');
    
    $month_arr = array(
        1  => 'января',
        2  => 'февраля',
        3  => 'марта',
        4  => 'апреля',
        5  => 'мая',
        6  => 'июня',
        7  => 'июля',
        8  => 'августа',
        9  => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря'
    );
    
    $day_of_week_arr = array(
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        7 => 'Воскресенье'
    );
    
    if (LANG == 'RU'){
        $abc = $abc_ru;
    }elseif(LANG == 'EN'){
        $abc = $abc_en;
    }
    
    $datetime = date("Y-m-d H:i:s"); 
    
    $type     = $_REQUEST['type'];
    $action   = @$_REQUEST['action'];
    $num      = @$_REQUEST['num'];
    $search   = @urldecode($_REQUEST['s']);
    
    if (mb_check_encoding($search,'windows-1251')){
        $search = iconv("WINDOWS-1251","UTF-8", $search);
    }
    
    $l        = @$_REQUEST['l'];
    $genre_id = @$_REQUEST['genre_id'];
    $year_range = @$_REQUEST['year_range'];
    $param    = @$_REQUEST['param'];
    
    $db = Database::getInstance(DB_NAME);
    
    if (@$_REQUEST['p']){
        $page = @$_REQUEST['p']-1;
    }
    
    $page_offset = $page*MAX_PAGE_ITEMS;
    
    if ($type == 'vod'){
        if(isset($l)){
            $where .= "where name like '".$l."%'";
        }
        
        if (intval($_REQUEST['cat_num']) == 0 || intval($_REQUEST['cat_num']) == 101){
            $genre_field = 'genre';
        }else{
            $genre_field = 'cat_genre';
        }
        
        if (isset($genre_id) && $where != ''){
            $where .= ' and ('.$genre_field.'_id_1='.$genre_id.' or '.$genre_field.'_id_2='.$genre_id.' or '.$genre_field.'_id_3='.$genre_id.' or '.$genre_field.'_id_4='.$genre_id.')';
        }else if(isset($genre_id) && $where == ''){
            $where .= ' where ('.$genre_field.'_id_1='.$genre_id.' or '.$genre_field.'_id_2='.$genre_id.' or '.$genre_field.'_id_3='.$genre_id.' or '.$genre_field.'_id_4='.$genre_id.')';
        }
    }

    $table_map = array(
        'vod'           => 'video',
        'audio_club'    => 'audio',
        'itv'           => 'itv',
        'fav_itv'       => 'fav_itv',
        'save_fav'      => 'fav_itv',
        'iradio'        => 'iradio',
        'set_id'        => 'last_id',
        'epg'           => 'epg',
        'get_id'        => 'last_id',
        'log'           => 'user_log',
        'get_profile'   => 'users',
        'create_link'   => '',
        'week'          => '',
        'karaoke'       => 'karaoke',
        'watchdog'      => '',
        'event_confirm' => '',
        'save_fav_itv_status' => '',
        'radio'         => 'radio',
        'add_to_playlist'   => 'playlist',
        'del_from_playlist' => 'playlist',
        'del_vclub_fav'     => 'fav_vclub',
        'add_vclub_fav'     => 'fav_vclub',
        'all_vclub_genres'  => 'genre',
        'played_itv'        => 'played_itv',
        'played_video'      => 'video',
        'video_records'     => 'video_records',
        'start_rec'         => 'users_rec',
        'stop_rec'          => 'users_rec',
        'del_my_video_rec'  => 'users_rec',
        'stream_error'      => 'stream_error',
        'my_video_records'  => 'users_rec',
        'anec'              => 'anec',
        'get_anec_bookmark_page' => 'anec_bookmark',
        'make_anec_bookmark'     => 'anec_bookmark',
        'video_clip'  => 'video_clips'
    );
    
    $table = $table_map[$type];
    
    if ($type == 'stream_error'){
        $ch_id = @$_REQUEST['data'];
        $sql = "insert into stream_error (`ch_id`, `mac`, `error_time`) value ($ch_id, '$stb->mac', NOW())";
        $db->executeQuery($sql);
        return 0;
    }
    
    if ($type == 'set_id'){
        $last_id=$_REQUEST['id'];
        //increment_counter($last_id, 'itv');
        /*$sql = "select * from last_id where ident='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $ip = $rs->getValueByName(0, 'id');
        if($ip){
            $sql = "update last_id set last_id='".$last_id."' where ident='".$stb->mac."'";
        }else{
            $sql = "insert into last_id (ident, last_id) values ('".$stb->mac."', '".$last_id."')";
        }*/
        
        $sql = "insert into last_id (ident, last_id) values ('".$stb->mac."', '".$last_id."')
                on duplicate key update last_id='".$last_id."'";
        
        $rs = $db->executeQuery($sql);
        return 0;
    }
    
    if($type == 'watchdog'){
        $ip = $stb->ip;
        
        $update = "update users set keep_alive=NOW(), ip='$ip', now_playing_type=".intval($_REQUEST['data']['cur_play_type'])."  where mac='$stb->mac'";
        $rs = $db->executeQuery($update);
        
        $events = Event::getAllNotEndedEvents($stb->id);
        
        $messages = count($events);
                
        $res['data'] = array();
        $res['data']['msgs'] = $messages;
        
        if ($messages>0){
            if ($events[0]['sended'] == 0){
                
                Event::setSended($events[0]['id']);
                
                if($events[0]['need_confirm'] == 0){
                    Event::setEnded($events[0]['id']);
                }
            }
            
            if ($events[0]['id'] != @$_REQUEST['data']['event_active_id']){
                $res['data']['id'] = $events[0]['id'];
                $res['data']['event'] = $events[0]['event'];
                $res['data']['need_confirm'] = $events[0]['need_confirm'];
                $res['data']['msg'] = $events[0]['msg'];
                $res['data']['reboot_after_ok'] = $events[0]['reboot_after_ok'];
            }
        }
        
        /**
         * @todo вынести в events
         */
        $res['data']['additional_services_on'] = $stb->additional_services_on;
        
        $cur_weather = new Curweather();
        $res['data']['cur_weather'] = $cur_weather->getData();
        
        $sql = "select * from updated_places where uid=$stb->id";
        $rs  = $db->executeQuery($sql);
        $res['data']['updated'] = array();
        $res['data']['updated']['anec'] = intval($rs->getValueByName(0, 'anec'));
        $res['data']['updated']['vclub'] = intval($rs->getValueByName(0, 'vclub'));
        
        return $res;
        exit;
    }
    
    if($type == 'event_confirm'){
        $id = intval($_REQUEST['data']);

        Event::setConfirmed($id);
        
        $res['data'] = 'ok';
        return $res;
        exit;
    }
    
    if ($type == 'create_link'){
        
        if(is_array($_REQUEST['data'])){
            $data_req = @$_REQUEST['data']['cmd'];
        }else{
            $data_req = $_REQUEST['data'];
        }
        
        if (is_array($_REQUEST['data'])){
            $series = @$_REQUEST['data']['series'];
        }else{
            $series = 0;
        }
        
        if ($action == 'vclub'){
            
            preg_match("/auto \/media\/(\d+).mpg$/", $data_req, $tmp_arr);
            
            $media_id = $tmp_arr[1];
            
            $master = new VideoMaster();
            
            try {
                $res['data'] = $master->play($media_id, $series);
            }catch (Exception $exception){
                echo $exception->getMessage();
            }
            
            var_dump($res);
            
        }elseif($action == 'karaoke'){
            preg_match("/auto \/media\/(\d+).mpg$/", $data_req, $tmp_arr);
            $media_id = $tmp_arr[1];

            $master = new KaraokeMaster();
            try {
                $res['data'] = $master->play($media_id);
            }catch (Exception $exception){
                echo $exception->getMessage();
            }
            
            var_dump($res);
            
        }
        return $res;
        exit;
    }
    
    if ($type == 'log' && $stb->mac && $action){
        if ($param == 'undefined'){
            $param = '';
        }
        $type = 0;
        if (@$_REQUEST['data']){
            $type = $_REQUEST['data'];
        }
        $sql = "insert into user_log (mac, action, param, time, type) values ('$stb->mac', '$action', '$param', '$datetime', '$type')";
        $rs = $db->executeQuery($sql);
        
        $_sql = '';
        $storage = '';
        $hd = 0;
        if ($action == 'play'){
            $_sql .= ', now_playing_start=NOW() ';

            switch ($type){
                case 1: // TV
                    $ch_name = '';
                    $sql = "select * from itv where cmd='$param' and status=1";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $ch_name = $rs->getValueByName(0, 'name');
                    }else{
                        $ch_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$ch_name'";
                    break;
                case 2: // Video Club
                    $video_name = '';
                    
                    preg_match("/auto \/media\/([\S\s]+)\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
                    
                    $storage  = $tmp_arr[1];
                    $media_id = $tmp_arr[2];
                    
                    $sql = "select * from video where id=$media_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $video_name = $rs->getValueByName(0, 'name');
                        $hd         = $rs->getValueByName(0, 'hd');
                    }else{
                        $video_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$video_name'";
                    $_sql .= ", storage_name='$storage', hd_content=$hd";
                    break;
                case 3: // Karaoke
                    $karaoke_name = '';
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $karaoke_id = $tmp_arr[1];
                    
                    $sql = "select * from karaoke where id=$karaoke_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $karaoke_name = $rs->getValueByName(0, 'name');
                    }else{
                        $karaoke_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$karaoke_name'";
                    break;
                case 4: // Audio Club
                    $audio_name = '';
                    
                   // preg_match("/auto \/media\/(\d+).mp3$/", $param, $tmp_arr);
                    preg_match("/(\d+).mp3$/", $param, $tmp_arr);
                    $audio_id = $tmp_arr[1];
                    
                    $sql = "select * from audio where id=$audio_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $audio_name = $rs->getValueByName(0, 'name');
                    }else{
                        $audio_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$audio_name'";
                    break;
                case 5: // Radio
                    $ch_name = '';
                    $sql = "select * from radio where cmd='$param' and status=1";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $ch_name = $rs->getValueByName(0, 'name');
                    }else{
                        $ch_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$ch_name'";
                    break;
                case 6: // My Records
                    $my_record_name = '';
                    
                    preg_match("/\/(\d+).mpg/", $param, $tmp_arr);
                    $my_record_id = $tmp_arr[1];
                    
                    $sql = "select t_start,itv.name from users_rec, itv where users_rec.ch_id=itv.id and users_rec.id=$my_record_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $my_record_name = $rs->getValueByName(0, 't_start').' '.$rs->getValueByName(0, 'name');
                    }else{
                        $my_record_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$my_record_name'";
                    break;
                case 7: // Shared Records
                    $shared_record_name = '';
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $shared_record_id = $tmp_arr[1];
                    
                    $sql = "select * from video_records where id=$shared_record_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $shared_record_name = $rs->getValueByName(0, 'descr');
                    }else{
                        $shared_record_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$shared_record_name'";
                    break;
                case 8: // Video clips
                    $video_name = '';
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $media_id = $tmp_arr[1];
                    
                    $sql = "select * from video_clips where id=$media_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $video_name = $rs->getValueByName(0, 'name');
                    }else{
                        $video_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$video_name'";
                    break;
                default:
                    $_sql .= ", now_playing_content='unknown media'";
            }
        }
        
        if ($action == 'infoportal'){
            $_sql .= ', now_playing_start=NOW() ';

            $info_arr = array(
                20 => 'city_info',
                21 => 'anec_page',
                22 => 'weather_page',
                23 => 'game_page',
                24 => 'horoscope_page',
                25 => 'course_page'
            );
            if (@$info_arr[$type]){
                $info_name = $info_arr[$type];
            }else{
                $info_name = 'unknown';
            }
            $_sql .= ", now_playing_content='$info_name'";
        }
        
        if ($action == 'stop' || $action == 'close_infoportal'){
            $_sql .= ", now_playing_content=''";
            $_sql .= ", storage_name='$storage', hd_content=$hd";
            $type = 0;
        }
        
        if ($action == 'pause'){
            $sql = "insert into vclub_paused (`uid`, `mac`, `pause_time`) value ('$stb->id', '$stb->mac', NOW())";
            $db->executeQuery($sql);
        }
        if ($action == 'continue' || $action == 'stop' || $action == 'set_pos()' || $action == 'play'){
            $sql = "delete from vclub_paused where mac='$stb->mac'";
            $db->executeQuery($sql);
        }
        
        if ($action == 'readed_anec'){
            $sql = "insert into readed_anec (mac,readed) value ('$stb->mac', NOW())";
            $db->executeQuery($sql);
            return 1;
            exit;
        }
        
        if ($action == 'loading_fail'){
            $sql = "insert into loading_fail (mac, added) value ('$stb->mac', NOW())";
            $db->executeQuery($sql);
            return 1;
            exit;
        }
        
        $update = "update users set last_active=NOW(), keep_alive=NOW(), now_playing_type='$type' $_sql where mac='$stb->mac'";
        //echo $update;
        $rs = $db->executeQuery($update);
        
        return 1;
        exit;
    }
    
    if ($type == 'make_anec_bookmark'){
        $anec_id = $_REQUEST['data'];
        
        $sql = "select * from anec_bookmark where uid=$stb->id";
        $rs = $db->executeQuery($sql);
        if ($rs->getRowCount() == 1){
            $sql = "update anec_bookmark set anec_id=$anec_id where uid=$stb->id";
        }else{
            $sql = "insert into anec_bookmark (uid, anec_id) value ($stb->id, $anec_id)";
        }
        $db->executeQuery($sql);
        return 1;
        exit;
    }
    
    if ($type == 'get_anec_bookmark_page'){
        $sql = "select * from anec_bookmark where uid=$stb->id";
        $rs = $db->executeQuery($sql);
        if ($rs->getRowCount() == 1){
            $anec_id = $rs->getValueByName(0, 'anec_id');
            $sql = "select count(*) as count from anec where id>=$anec_id order by added desc";
            $rs = $db->executeQuery($sql);
            $page = $rs->getValueByName(0, 'count');
        }else{
            $anec_id = 0;
            $page = 0;
        }
        $data['data'] = $page;
        return $data;
        exit;
    }
    
    if ($type == 'get_media_cats'){
        $arr = array('all'=>0);
        $arr2 = array(0=>'Все');
        $sql = "select * from media_category";
        $rs = $db->executeQuery($sql);
        while(@$rs->next()){
            $arr[$rs->getCurrentValueByName('category_alias')] = $rs->getCurrentValueByName('id');
            $arr2[$rs->getCurrentValueByName('id')] = $rs->getCurrentValueByName('category_name');
        }
        $data['data'] = $arr;
        $data['data2'] = $arr2;
        return $data;
        exit;
    }
    
    if ($type == 'vote_anec'){
        $anec_id = intval($_REQUEST['data']);
        $voted_anec_id = 0;
        
        $sql = "select * from anec_rating where uid=$stb->id and anec_id=$anec_id";
        $rs = $db->executeQuery($sql);
        
        if (intval($rs->getRowCount()) == 0){
            $sql = "insert into anec_rating (uid, anec_id) value ($stb->id, $anec_id)";
            $db->executeQuery($sql);
            $voted_anec_id = $anec_id;
        }
        $data['data'] = $voted_anec_id;
        return $data;
        exit;
    }
    
    if ($type == 'day_weather'){
        //$weather = new Weather();
        $weather = new Gismeteo();
        $data['data'] = $weather->getData();
        return $data;
        exit;
    }
    
    if ($type == 'horoscope'){
        $horoscope = new Horoscope();
        $data['data'] = $horoscope->getData();
        return $data;
        exit;
    }
    
    if ($type == 'course'){
        $course = new Course();
        $data['data'] = $course->getData();
        return $data;
        exit;
    }
    
    if ($type == 'all_recipe_cats'){
        $sql = 'select * from recipe_cats order by num';
        $rs = $db->executeQuery($sql);
        $data['data'] = $rs->getAllValues();
        return $data;
        exit;
    }
    
    if ($type == 'add_recipes_fav'){
        $new_id = intval($_REQUEST['data']);
        $sql = 'select * from fav_recipes where uid='.$stb->id;
        $rs = $db->executeQuery($sql);
        $fav_recipes_arr = @unserialize($rs->getValueByName(0, 'fav_recipes'));
        if (!@in_array($new_id, $fav_recipes_arr)){
            if (!is_array($fav_recipes_arr)){
                $fav_recipes_arr = array();
            }
            $fav_recipes_arr[] = $new_id;
            $fav_recipes_str = serialize($fav_recipes_arr);
            if (@$rs->getRowCount() == 0){
                $sql = "insert into fav_recipes (uid, fav_recipes, addtime) values ($uid, '$fav_recipes_str', NOW())";
            }else{
                $sql = "update fav_recipes set fav_recipes='$fav_recipes_str', edittime=NOW() where uid=".$stb->id;
            }
            $rs = $db->executeQuery($sql);
        }
        exit;
    }
    
    if ($type == 'del_recipes_fav'){
        $del_id = intval($_REQUEST['data']);
        $sql = 'select * from fav_recipes where uid='.$stb->id;
        $rs = $db->executeQuery($sql);
        $fav_recipes_arr = @unserialize($rs->getValueByName(0, 'fav_recipes'));
        if (is_array($fav_recipes_arr)){
            if (in_array($del_id, $fav_recipes_arr)){
                unset($fav_recipes_arr[array_search($del_id, $fav_recipes_arr)]);
                $fav_recipes_str = serialize($fav_recipes_arr);
                $sql = "update fav_recipes set fav_recipes='$fav_recipes_str', edittime=NOW() where uid=".$stb->id;
                $rs = $db->executeQuery($sql);
            }
        }
        $data['data'] = 'ok';
        return $data;
        exit;
    }
    
    if ($type == 'vclub_vote'){
        $data = $_REQUEST['data'];
        $media_id = $data['media_id'];
        $type     = $data['type'];
        $vote     = $data['vote'];
        if ($vote == 'good'){
            $good = 1;
            $bad = 0;
        }else{
            $good = 0;
            $bad = 1;
        }
        
        $sql = "insert into vclub_vote (media_id, uid, vote_type, good, bad, added) values ($media_id, $stb->id, '$type', $good, $bad, NOW())";
        $db->executeQuery($sql);
        
        $sql = "update video set vote_".$type."_good=vote_".$type."_good+$good, vote_".$type."_bad=vote_".$type."_bad+$bad where id=$media_id";
        $db->executeQuery($sql);
        
        return 1;
        exit;
    }
    
    if ($type == 'played_video'){
        $video_id = $_REQUEST['data']['video_id'];
        $storage_id = $_REQUEST['data']['storage_id'];
        $day = date("j");
        
        if ($day <= 15){
            $field_name = 'count_first_0_5';
        }else{
            $field_name = 'count_second_0_5';
        }
        
        $sql = "update video set $field_name=$field_name+1, count=count+1, last_played=NOW() where id=$video_id";
        $db->executeQuery($sql);
        
        $sql = "insert into played_video (video_id, uid, storage, playtime) values ( $video_id, $uid, $storage_id, NOW())";
        $db->executeQuery($sql);
        
        $sql = "update users set time_last_play_video=NOW() where id=$uid";
        $db->executeQuery($sql);
        
        $now=date("Y-m-d");
        $sql = "select * from daily_played_video where date='$now'";
        $rs = $db->executeQuery($sql);
        $row_count = $rs->getRowCount();

        if ($row_count > 0){
            $sql = "update daily_played_video set count=count+1, date=NOW() where id=".$rs->getValueByName(0, 'id');
        }else{
            $sql = "insert into daily_played_video (`count`, `date`) value (1, NOW())";
        }
        $db->executeQuery($sql);
        
        $sql = "select * from stb_played_video where uid=$stb->id and video_id=$video_id";
        $rs = $db->executeQuery($sql);
        
        if ($rs->getRowCount() > 0){
            $sql = "update stb_played_video set playtime=NOW() where uid=$stb->id and video_id=$video_id";
        }else{
            $sql = "insert into stb_played_video (uid, video_id, playtime) values ($stb->id, $video_id, NOW())";
        }
        
        $db->executeQuery($sql);
        
        return 1;
        exit;
    }
    
    if ($type == 'played_itv'){
        $itv_id = $_REQUEST['data'];
        $day = date("j");
        
        $sql = "insert into played_itv (itv_id, uid, playtime) values ( $itv_id, $uid, NOW())";
        $db->executeQuery($sql);
        
        $sql = "update users set time_last_play_tv=NOW() where id=$uid";
        $db->executeQuery($sql);
        
        $sql = "insert into last_id (ident, last_id) values ('".$stb->mac."', '".$itv_id."')
                on duplicate key update last_id='".$itv_id."'";
        
        $db->executeQuery($sql);
        
        return 1;
    }
    
    if ($type == 'preload_images'){
        $dir = PORTAL_PATH.'/client/i/';
        $files = array();
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_file($dir.$file)){
                        $files[] = 'i/'.$file;
                    }
                }
                closedir($dh);
            }
        }
        
        $data['data'] = $files;
        return $data;
    }
    
    if ($type == 'get_profile'){
        
        $params = @$_REQUEST['data'];
        
        if (@$params['hd'] == 1){
            $hd = 1;
        }else{
            $hd = 0;
        }
        
        $ver = $params['ver'];
        
        $sql = "select * from users_rec where uid=$uid and ended=0";
        $rs = $db->executeQuery($sql);
        $my_rec_ch = $rs->getValuesByName('ch_id');
        
        $sql = " select * from users where mac='$stb->mac'";
        $rs = $db->executeQuery($sql);
        $uid = @$rs->getValueByName(0, 'id');
        
        if(!$uid){
            $sql = "insert into users (mac) values ('$stb->mac')";
            $db->executeQuery($sql);
            
            $uid = $rs->getLastInsertId();
            $stb->setId($uid);
            
            $sql = "insert into updated_places (uid) values ($uid)";
            $db->executeQuery($sql);
        }
        
        $name = substr($stb->mac, 12, 16);
        
        $sql_up = "update users set last_start=NOW(), keep_alive=NOW(), version='".$ver."', hd=$hd, name='".$name."' where mac='".$stb->mac."'";
        
        $db->executeQuery($sql_up);
        
        $sql = "select * from users where mac='$stb->mac'";
        $rs = $db->executeQuery($sql);
        
        $master = new Master();
            
        $arr['id'] = $uid;
        $arr['parent_password'] = $rs->getValueByName(0, 'parent_password');
        $arr['status'] = $rs->getValueByName(0, 'status');
        $arr['bright'] = $rs->getValueByName(0, 'bright');
        $arr['video_out'] = $rs->getValueByName(0, 'video_out');
        $arr['fav_itv_on'] = $rs->getValueByName(0, 'fav_itv_on');
        $arr['volume'] = $rs->getValueByName(0, 'volume');
        $arr['my_rec_ch'] = $my_rec_ch;
        $arr['storages'] = $master->getStoragesForStb();
        $arr['additional_services_on'] = $rs->getValueByName(0, 'additional_services_on');
        $arr['image_version'] = $rs->getValueByName(0, 'image_version');
        $arr['last_itv_id'] = intval($db->executeQuery("select * from last_id where ident='".$stb->mac."'")->getValueByName(0, 'last_id'));
                
        $master->checkAllHomeDirs();
        
        $cur_weather = new Curweather();
        $arr['cur_weather'] = $cur_weather->getData();
        
        $sql = "select * from updated_places where uid=$stb->id";
        $rs  = $db->executeQuery($sql);
        $arr['updated'] = array();
        $arr['updated']['anec'] = intval($rs->getValueByName(0, 'anec'));
        $arr['updated']['vclub'] = intval($rs->getValueByName(0, 'vclub'));        

        $data['data'] = $arr;
        return $data;
    }
    
    if ($type == 'set_parent_password'){
        $data = $_REQUEST['data'];
        $sql = "update users set parent_password='".$data."' where mac='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $data['data'] = 'ok';
        return $data;
    }
    
    if ($type == 'set_bright'){
        $data = $_REQUEST['data'];
        $sql = "update users set bright='".$data."' where mac='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $data['data'] = 'ok';
        return $data;
    }
    
    if ($type == 'set_video_out'){
        $data = $_REQUEST['data'];
        $sql = "update users set video_out='".$data."' where mac='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $data['data'] = 'ok';
        return $data;
    }
    
    if($type == 'set_volume'){
        $data_req = intval($_REQUEST['data']);
        if($data_req>=0 && $data_req<=100){
            $volume = $data_req;
        }else{
            $volume = 100;
        }
        $sql = "update users set volume='".$volume."' where mac='".$stb->mac."'";
        //echo $sql;
        $rs = $db->executeQuery($sql);
        $data['data'] = 'ok';
        return $data;
    }
    
    if ($type == 'save_fav_itv_status'){
        $fav_itv_on = intval($_REQUEST['data']);
        $sql = "update users set fav_itv_on='".$fav_itv_on."' where mac='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $data['data'] = 'ok';
        return $data;
    }
    
    if ($type == 'add_to_playlist'){
        $new_id = $_REQUEST['data'];
        $sql = "select * from playlist where uid=$uid";
        $rs = $db->executeQuery($sql);
        $records = @$rs->getRowCount();
        $tracks = @$rs->getValueByName(0, 'tracks');
        $tracks = @unserialize($tracks);
        if (!in_array($new_id, $tracks)){
            if (!is_array($tracks)){
                $tracks = array();
            }
            $tracks[] = $new_id;
            $tracks = serialize($tracks);
            if ($records == 0){
                $sql = "insert into playlist (uid, tracks, addtime) values ($uid, '$tracks', NOW())";
            }else{
                $sql = "update playlist set tracks='$tracks', edittime=NOW() where uid=$uid";
            }
            $rs = $db->executeQuery($sql);
        }
        exit;
    }
    
    if ($type == 'del_from_playlist'){
        $del_id = $_REQUEST['data'];
        $sql = "select * from playlist where uid=$uid";
        $rs = $db->executeQuery($sql);
        $records = @$rs->getRowCount();
        $tracks = @$rs->getValueByName(0, 'tracks');
        $tracks = @unserialize($tracks);
        if (is_array($tracks)){
            if (in_array($del_id, $tracks)){
                unset($tracks[array_search($del_id, $tracks)]);
                $tracks = serialize($tracks);
                $sql = "update playlist set tracks='$tracks', edittime=NOW() where uid=$uid";
                $rs = $db->executeQuery($sql);
            }
        }
        exit;
    }
    
    if ($type == 'add_vclub_fav'){
        $new_id = $_REQUEST['data'];
        $sql = "select * from fav_vclub where uid=$uid";
        $rs = $db->executeQuery($sql);
        $records = @$rs->getRowCount();
        $fav_video = @$rs->getValueByName(0, 'fav_video');
        $fav_video = @unserialize($fav_video);
        if (!@in_array($new_id, $fav_video)){
            if (!is_array($fav_video)){
                $fav_video = array();
            }
            $fav_video[] = $new_id;
            $fav_video = serialize($fav_video);
            if ($records == 0){
                $sql = "insert into fav_vclub (uid, fav_video, addtime) values ($uid, '$fav_video', NOW())";
            }else{
                $sql = "update fav_vclub set fav_video='$fav_video', edittime=NOW() where uid=$uid";
            }
            $rs = $db->executeQuery($sql);
        }
        exit;
    }
    
    if ($type == 'del_vclub_fav'){
        $del_id = $_REQUEST['data'];
        $sql = "select * from fav_vclub where uid=$uid";
        $rs = $db->executeQuery($sql);
        $records = @$rs->getRowCount();
        $fav_video = @$rs->getValueByName(0, 'fav_video');
        $fav_video = @unserialize($fav_video);
        if (is_array($fav_video)){
            if (in_array($del_id, $fav_video)){
                unset($fav_video[array_search($del_id, $fav_video)]);
                $fav_video = serialize($fav_video);
                $sql = "update fav_vclub set fav_video='$fav_video', edittime=NOW() where uid=$uid";
                $rs = $db->executeQuery($sql);
            }
        }
        $data['data'] = 'ok';
        return $data;
    }
    
    if ($type == 'vclub_news'){
        $sql = "select * from vclub_news order by id desc limit 2";
        $rs = $db->executeQuery($sql);
        while(@$rs->next()){
            $arr[] = array(
                'msg'   => $rs->getCurrentValueByName('msg'),
                );
        }
        $data['data'] = $arr;
        return $data;
    }
    
    if ($type == 'vclub_not_ended'){
        $video_id   = $_REQUEST['data']['video_id'];
        $series     = $_REQUEST['data']['series'];
        $end_time   = $_REQUEST['data']['end_time'];
        
        
        $sql = "select * from vclub_not_ended where uid=$stb->id and video_id=$video_id";
        $rs = $db->executeQuery($sql);
        if ($rs->getRowCount() > 0){        
            $sql = "update vclub_not_ended set series=$series, end_time=$end_time, added=NOW() where uid=$stb->id and video_id=$video_id";
        }else{
            $sql = "insert into vclub_not_ended (uid, video_id, series, end_time, added) values ($stb->id, $video_id, $series, $end_time, NOW())";
        }
        $rs = $db->executeQuery($sql);
        return 1;
    }
    
    if ($type == 'epg_info'){
        $data = array();
        
        $now_datetime = date("Y-m-d H:i:s");
        $day_begin_datetime = date("Y-m-d 00:00:00");
        
        $all_ch_str = join(',', get_all_subscription_and_base_ch($stb->id));
        
        $sql = 'select *,MAX(UNIX_TIMESTAMP(time)) as start_timestamp, MAX(time) as time from epg where ch_id in ('.$all_ch_str.') and time>="'.$day_begin_datetime.'" and time<="'.$now_datetime.'" group by ch_id';
        
        $rs  = $db->executeQuery($sql);
        $cur_program_arr = $rs->getAllValues();
        
        foreach ($cur_program_arr as $cur_program){
            $period_end = date("Y-m-d H:i:s", ($cur_program['start_timestamp'] + 9*3600));
            $sql = 'select *,UNIX_TIMESTAMP(time) as start_timestamp, TIME_FORMAT(time,"%H:%i") as time from epg where ch_id='.$cur_program['ch_id'].' and time>="'.$cur_program['time'].'" and time<="'.$period_end.'" order by time';
            $rs  = $db->executeQuery($sql);
            $data['data'][$cur_program['ch_id']] = $rs->getAllValues();
        }
        return $data;
    }
    
    if ($type == 'week') {
    	$data['data'] = get_week();
    	return $data;
    }
    
    if ($type == 'vod' && $action == 'year_bar'){
        $years = slice_year($page);
        $data['data']['years'] = $years;
        $data['data']['total_year_pages'] = ceil(count(get_years())/10);
    	return $data;
    }
    
    if ($type == 'get_id'){
        $sql = "select * from last_id where ident='".$stb->mac."'";
        $rs = $db->executeQuery($sql);
        $last_id = $rs->getValueByName(0, 'last_id'); 
        $data['last_id'] = $last_id;
        return $data;
    }
    
    if ($type == 'fav_itv'){
        
        $itv_ch = array();
        
        $sql = "select * from fav_itv where uid='".$uid."'";
        $rs = $db->executeQuery($sql);
        $itv_ch = $rs->getValueByName(0, 'fav_ch'); 
        
        if ($itv_ch){
            $itv_ch = unserialize(base64_decode($itv_ch));
        }
        //var_dump($itv_ch);
        if (is_array($itv_ch) && count($itv_ch) > 0){
            $fav_str = join(",", $itv_ch);
            $sql = "select * from itv where itv.id in ($fav_str) and status=1 order by field(itv.id,$fav_str)";
            $rs = $db->executeQuery($sql);
            $itv_ch_e = $rs->getValuesByName('id'); 
        }else{
            $itv_ch_e = array();
        }
        
        $data['data'] = $itv_ch_e;
        
        return $data;
    }
    
    if($type == 'save_fav'){
        
        
        $data = $_REQUEST['data'];
        if ($data == NULL){
            $data = array();
        }
        if (is_array($data)){
            $data_str = base64_encode(serialize($data));
        
            $sql = "select * from fav_itv where uid='".$uid."'";
            $rs = $db->executeQuery($sql);
            $id = $rs->getValueByName(0, 'id');
            
            if($id){
                $sql = "update fav_itv set fav_ch='".$data_str."', addtime=NOW() where uid='".$uid."'";
            }else{
                $sql = "insert into fav_itv (uid, fav_ch, addtime) values ('".$uid."', '".$data_str."', NOW())";
            }
            
            $rs = $db->executeQuery($sql);
        }
        return 1;
        exit;
    }
    
    if ($type == 'updated_place_confirm'){
        $col = $_REQUEST['data'];
        
        $sql = "update updated_places set $col=0 where uid=".$stb->id;
        $rs = $db->executeQuery($sql);
        
        return 1;
        exit;
    }
    
    if ($type == 'mastermind_log'){
        $data = $_REQUEST['data'];
        $points = 1;
        $tries = $data['tries'];
        $total_time = $data['total_time'];
        
        if ($tries <= 7 && $total_time < 600){
            $points = 3;
        }else if ($tries <= 10 && $total_time < 600){
            $points = 2;
        }
        
        $sql = "insert into mastermind_wins (uid, tries, total_time, points, added) values ('$stb->id', $tries, $total_time, $points, NOW())";
        $rs = $db->executeQuery($sql); 
        
        return 1;
        exit;
    }
    
    if ($type == 'start_rec'){
        
        $vtrack = '';
        $atrack = '';
        
        $rec_ch_id = $_REQUEST['data'];
        
        $sql = "select * from itv where id=$rec_ch_id";
        $rs = $db->executeQuery($sql);
        $cmd = $rs->getValueByName(0, 'cmd');
        
        preg_match("/vtrack:(\d+)/", $cmd, $vtrack_arr);
        preg_match("/atrack:(\d+)/", $cmd, $atrack_arr);
        
        if ($vtrack_arr[1]){
            $vtrack = $vtrack_arr[1];
        }
        
        if ($atrack_arr[1]){
            $atrack = $atrack_arr[1];
        }
        
        $end_record_ts = time()+MAX_USER_REC_LENGTH;
        $end_record = date("Y-m-d H:i:s", $end_record_ts);
        
        $sql = "insert into users_rec (ch_id, uid, t_start, atrack, vtrack, end_record) value ($rec_ch_id, $uid, NOW(), $atrack, $vtrack, '$end_record')";
        $rs = $db->executeQuery($sql);
        $u_rec_id = $rs->getLastInsertId();
        
        $sql = "select * from rec_files where ch_id=$rec_ch_id and ended=0";
        $rs = $db->executeQuery($sql);
        if ($rs->getRowCount() == 0){
            
            $sql = "select * from itv where id=$rec_ch_id";
            $rs = $db->executeQuery($sql);
            $cmd = $rs->getValueByName(0, 'cmd');
            
            $sql = "insert into rec_files (ch_id, t_start, atrack, vtrack) value ($rec_ch_id, NOW(), $atrack, $vtrack)";
            $rs = $db->executeQuery($sql);
            $rec_f_id = $rs->getLastInsertId();
            $rec_f_name = $rec_f_id.'.mpg';
            
            $sql = "update users_rec set file_id=$rec_f_id where id=$u_rec_id";
            $rs = $db->executeQuery($sql);
            
            _log("start -- sh /media/raid0/storage/-save-/rtp_start.sh $rec_f_name $cmd");
            
            $result = `sh /media/raid0/storage/-save-/rtp_start.sh $rec_f_name $cmd`;
        }else{
            $rec_f_id = $rs->getValueByName(0, 'id');
            
            $sql = "update users_rec set file_id=$rec_f_id where id=$u_rec_id";
            $rs = $db->executeQuery($sql);
        }
        exit;
    }
    
    if ($type == 'stop_rec'){

        $rec_ch_id = $_REQUEST['data'];
        
        $sql = "select * from rec_files where ch_id=$rec_ch_id and ended=0";
        $rs = $db->executeQuery($sql);
        $rec_f_id = $rs->getValueByName(0, 'id');
        
        $sql = "update users_rec set t_stop=NOW(), ended=1, file_id=$rec_f_id, length=(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(t_start)) where uid=$uid and ch_id=$rec_ch_id and ended=0";
        $rs = $db->executeQuery($sql);
        
        $sql = "select * from users_rec where ch_id=$rec_ch_id and ended=0";
        $rs = $db->executeQuery($sql);
        
        if ($rs->getRowCount() == 0){
            
            $rec_f_name = $rec_f_id.'.mpg';
            
            $sql = "update rec_files set t_stop=NOW(), ended=1, length=(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(t_start)) where ch_id=$rec_ch_id and ended=0";
            $rs = $db->executeQuery($sql);
            
            $sql = "select * from itv where id=$rec_ch_id";
            $rs = $db->executeQuery($sql);
            $cmd = $rs->getValueByName(0, 'cmd');
            
            _log("stop -- sh /media/raid0/storage/-save-/rtp_stop.sh $rec_f_name $cmd");
            
            $result = `sh /media/raid0/storage/-save-/rtp_stop.sh $rec_f_name $cmd`;
        }
        return 1;
        exit;
    }
    
    if ($type == 'del_my_video_rec'){

        $u_rec_id = $_REQUEST['data'];
        
        $sql = "select * from users_rec where id=".$u_rec_id;
        $rs = $db->executeQuery($sql);
        $file_id = $rs->getValueByName(0, 'file_id');
        
        $sql = "delete from users_rec where id=".$u_rec_id;
        $rs = $db->executeQuery($sql);
        
        $sql = "select * from users_rec where file_id=".$file_id;
        $rs = $db->executeQuery($sql);
        
        if ($rs->getRowCount() == 0){
            $sql = "delete from rec_files where id=".$file_id;
            $rs = $db->executeQuery($sql);
            unlink(MY_VIDEO_RECORDS_STORAGE_DIR.$file_id.'.mpg');
        }
        return 1;
        exit;
    }
    
    if($type == 'all_vclub_genres'){
        $genres = array();
        $cat_id = @intval($_REQUEST['data']);
        
        if ($cat_id != 0 && $cat_id != 101){
            $sql = "select category_alias from media_category where id=$cat_id";
            $rs = $db->executeQuery($sql);
            $category_alias = $rs->getValueByName(0, 'category_alias');
            
            $sql = "select * from cat_genre where category_alias='".$category_alias."' order by title";
        }else{
            $sql = "select * from genre order by title";
        }
        
        $rs = $db->executeQuery($sql);
        while(@$rs->next()){
            $genres[] = array(
                                 'id'    => $rs->getCurrentValueByName('id'),
                                 'title' => $rs->getCurrentValueByName('title')
                             );
        }
        $data['data'] = $genres;
        return $data;
        exit;
    }
    
    if($type != 'epg' && $action != 'by_year' && $type != 'log' && $type != 'radio' && $type != 'video_records' && $type != 'my_video_records'){
        
    	if ($type == 'itv'){
    	    $order = '';
    	    
    	    if (!check_moderator($stb->mac)){
               if ($where){
                    $where .= ' and status=1';
               }else{
                    $where .= '  status=1';
               }
            }
    	    
    	    $fav_arr = get_fav_ids($uid);
    	    if (isset($_REQUEST['data']) && $_REQUEST['data']){
                $fav = $_REQUEST['data'];
                if ($fav_arr){
                    $fav_str = join(",", $fav_arr);
                    //echo $fav_str;
                    //$where .= " and itv.id in ($fav_str)";
                    if ($where){
                        $where .= " and itv.id in ($fav_str)";
                    }else{
                        $where .= "  itv.id in ($fav_str)";
                    }
                    if ($action != 'tv_by_channel'){
                        $order = " order by field(itv.id,$fav_str)";
                    }else{
                        $order = " order by itv.name";
                    }
                }else{
                    //$where .= " and itv.id=0";
                    if ($where){
                        $where .= " and itv.id=0";
                    }else{
                        $where .= "  itv.id=0";
                    }
                }
                
            }else{
                if($action == 'tv_by_number' || $action == 'all'){
                    $order = " order by itv.number";
                }elseif($action == 'tv_by_channel'){
                    $order = " order by itv.name";
                }elseif($action == 'tv_by_theme'){
                    $order = " order by itv.number";
                }else{
                    $order = '';
                }
            }
            if ($where){
                $where .= " and hd<=".$stb->hd;
            }else{
                $where .= "  hd<=".$stb->hd;
            }
            //$where .= " and hd<=".$stb->hd;

            if (ENABLE_SUBSCRIPTION){
                $all_subscription_and_base_ch = get_all_subscription_and_base_ch($uid);
                if ($action == 'all'){
                    if (is_array($all_subscription_and_base_ch) && count($all_subscription_and_base_ch) > 0){
                        $all_subscription_str = join(",", $all_subscription_and_base_ch);
                        $where .= " and id in ($all_subscription_str)";
                    }
                }
            }
            
    	    $query = 'select * from '.$table.' where '.$where;
    	    //echo $query;
    	}
    	
    	if ($type == 'audio_club'){
    	    if (isset($_REQUEST['playlist'])){
                $palylist_ids = get_playlist_ids();
                if (count($palylist_ids) > 0){
                    $palylist_str = join(",", $palylist_ids);
                    $where .= " where status=1 ";
                    $where .= " and audio.id in ($palylist_str) ";
                    $order = " order by field(audio.id,$palylist_str)";
                }else{
                    $where = " and audio.id=0 ";                    
                }
            }else{
                $order = " order by audio.name";
            }
            
    	    $query = 'select * from '.$table.' '.$where;
    	}
    	
    	if ($type == 'vod'){
    	    $fav_arr = get_fav_video_ids();
    	    if (isset($_REQUEST['data']) && $_REQUEST['data']){
    	        $fav = $_REQUEST['data'];
    	        //$fav_arr = get_fav_video_ids();
    	        if ($fav_arr){
                    $fav_str = join(",", $fav_arr);
                    if ($where == ''){
                        $where .= " where video.id in ($fav_str)";
                    }else{
                        $where .= " and video.id in ($fav_str)";
                    }
                    $order = " order by field(video.id,$fav_str)";
                }else{
                    $where .= " where video.id=0";
                }
    	    }else{
                $order = " order by video.name";
    	    }
    	    //$query = 'select * from '.$table.', genre '.$where;
    	    $query = '';
    	    //echo '$where: '.$where;
    	}
    	
    	if ($type == 'karaoke'){
    	    $query = 'select * from '.$table.',karaoke_genre '.$where.'';
    	}
        //echo $query;
        if ($query){
    	    $rs=$db->executeQuery($query);
    	    $total_items = $rs->getRowCount();    
    	    $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
        }
    }

    // Default filed names
    $id   = 'id';
    $name = 'name';
    $cmd  = 'cmd';
    $desc = 'descr';
    $cost = 'm_cost';

    //$where = '';

    switch ($type){
        case 'itv':
           //echo 'mod:'.check_moderator($stb->mac);
           /*if (!check_moderator($stb->mac)){
               if ($where){
                    $where .= ' and status=1';
               }else{
                    $where .= '  status=1';
               }
            }*/
            
            if ($action == 'top' && $num){
                $sql = 'select * from itv where '.$where.' order by count desc limit '.$num;
            }
            elseif ($action == 'all'){
                if (!isset($_REQUEST['data'])){
                    $where .= " and censored=0 ";
                }
                
                $sql = 'select * from itv  where  '.$where.' '.$order;
            }elseif ($action == 'saved'){
                
                $sql = 'select itv.id,itv.name,itv.descr,saved_ch.add_time from saved_ch left join itv on saved_ch.ch_id=itv.id where u_id='.$u_id.'  group by saved_ch.ch_id';
            }elseif ($action == 'tv_by_number'){
                
                if ($where){
                    $where .= ' and itv.tv_genre_id=tv_genre.id';
                }else{
                    $where .= ' itv.tv_genre_id=tv_genre.id ';
                }
                
                if ($_REQUEST['p']==0){
                    $sql = "select *,itv.number as itv_number from last_id,itv where last_id.last_id=itv.id and ident='".$stb->mac."'";
                    $rs = $db->executeQuery($sql);
                    $last_id = intval($rs->getValueByName(0, 'last_id'));
                    $last_ch = intval($rs->getValueByName(0, 'itv_number'));
                    //echo '$last_id: '.$last_id.'; ';
                    $where2 = '';
                    if (@$_REQUEST['data'] == 1){
                        if (in_array($last_id, $fav_arr)){
                            //$fav_str = $fav_str;
                            $ch_tmp_idx = array_search($last_id, $fav_arr);
                            if ($ch_tmp_idx>=0){
                                //echo '$idx: '.array_search($last_id, $fav_arr);
                                $fav_arr_2 = array_slice($fav_arr,0,array_search($last_id, $fav_arr)+1);
                                $fav_str = join(",", $fav_arr_2);
                                $where2 .= " and itv.id in ($fav_str)";
                            }
                        }
                    }else{
                        $where2 = "and itv.number<=$last_ch";
                    }
                    
                    //$sql = "select * from itv, tv_genre where $where and itv.number<=$last_id $order";
                    $sql = "select * from itv, tv_genre where $where $where2 $order";
                    //echo $sql;
                    $rs=$db->executeQuery($sql);
                    $ch_idx = intval($rs->getRowCount());
                    $cur_page = ceil($ch_idx/MAX_PAGE_ITEMS);
                    $selected_item = $ch_idx - floor($ch_idx/MAX_PAGE_ITEMS)*MAX_PAGE_ITEMS;
                    if ($selected_item == 0){
                        $selected_item = 10;
                    }
                    $page_offset = ($cur_page-1)*MAX_PAGE_ITEMS;
                    if ($page_offset < 0){
                        $page_offset = 0;
                    }
                }
                
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where;
                //echo $sql;
                $rs=$db->executeQuery($sql);
        	    $total_items = $rs->getRowCount();   
        	    $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
        	    
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where.' '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                $desc = 'genres_name';
            }elseif ($action == 'tv_by_channel'){
                
                if ($where){
                    $where .= ' and itv.tv_genre_id=tv_genre.id';
                }else{
                    $where .= ' itv.tv_genre_id=tv_genre.id';
                }
                
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where;
                
                $rs=$db->executeQuery($sql);
        	    $total_items = $rs->getRowCount();   
        	    $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where.' '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                $desc = 'genres_name';
            }elseif($action == 'tv_by_theme'){
                
                if ($where){
                    $where .= ' and itv.tv_genre_id=tv_genre.id and tv_genre.id='.$genre_id;
                }else{
                    $where .= ' itv.tv_genre_id=tv_genre.id and tv_genre.id='.$genre_id;
                }
                
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where;
                $rs=$db->executeQuery($sql);
        	    $total_items = $rs->getRowCount();   
        	    $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql = 'select itv.*, tv_genre.title as genres_name from itv, tv_genre where '.$where.' '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                $desc = 'genres_name';
            }else{
                $sql = 'select * from itv '.$where.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            }
            $cost = 'cost';
            $number = 'number';
            
            break;
        case 'iradio':
            $sql = 'select * from iradio '.$where.' order by name limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            $cost = 'add_time';
            break;
        case 'vod':
            
            if (isset($l)){
                $where .= ' where name like "'.$l.'%"';
            }
            /*if (isset($genre_id) && $where != ''){
                $where .= ' and genre.id='.$genre_id;
            }else if(isset($genre_id) && $where == ''){
                $where .= ' where genre.id='.$genre_id;
            }*/
            
            if (isset($_REQUEST['s'])){
                //$s = urldecode($_REQUEST['s']);
                //$s = $_REQUEST['s'];
                //var_dump($_SERVER);
                //var_dump($_REQUEST);
                //var_dump(mb_check_encoding($s,'windows-1251'));
                //var_dump(mb_check_encoding($s,'utf-8'));
                //$s = iconv("WINDOWS-1251","UTF-8", $s);
                $where .= ' where (name like "%'.$search.'%" or o_name like "%'.$search.'%" or actors like "%'.$search.'%" or director like "%'.$search.'%" or year like "%'.$search.'%")';
            }
            
            $letters = '';
            if (isset($action) && !isset($_REQUEST['vclub_by_add_time'])){
                switch ($action){
                    case 1:
                        $letters = '';
                        break;
                    case (count($abc)):
                        $letters = ' like "0%" or name like "1%" or name like "2%" or name like "3%" or name like "4%" or name like "5%" or name like "6%" or name like "7%" or name like "8%" or name like "9%")';
                        break;
                    default:
                        $letters = ' like "'.$abc[$action-1].'%")';
                }
                
                if($letters){
                    if ($where != ''){
                        $where .= ' and (name '.$letters;
                    }else if($where == ''){
                        $where .= ' where (name '.$letters;
                    }
                }
                
                if (isset($year_range) && $year_range>=0){
                    $year_d = get_years();
                    if($where != ''){
                        $where .= ' and year='.$year_d[$year_range];
                    }else{
                        $where .= ' where year='.$year_d[$year_range];
                    }
                }
            }
            
            if (isset($_REQUEST['vclub_by_add_time'])){
                $range = intval(@$_REQUEST['action']);
                //echo $_REQUEST['action'];
                $timestamp = time();
                $from_timestamp = '';
                switch ($range){
                    case 1: // 1 day 
                        $from_timestamp = $timestamp - 60*60*24;
                        break;
                    case 2: // 2 day
                        $from_timestamp = $timestamp - 60*60*24*2;
                        break;
                    case 3: // 3 day
                        $from_timestamp = $timestamp - 60*60*24*3;
                        break;
                    case 4: // 4 day
                        $from_timestamp = $timestamp - 60*60*24*4;
                        break;
                    case 5: // 5 day
                        $from_timestamp = $timestamp - 60*60*24*5;
                        break;
                    case 6: // 6 day
                        $from_timestamp = $timestamp - 60*60*24*6;
                        break;
                    case 7: // 7 day
                        $from_timestamp = $timestamp - 60*60*24*7;
                        break;
                    case 8: // 2 week
                        $from_timestamp = $timestamp - 60*60*24*14;
                        break;
                    case 9: // 3 week
                        $from_timestamp = $timestamp - 60*60*24*21;
                        break;
                    case 10: // 1 month
                        $from_timestamp = $timestamp - 60*60*24*31;
                        break;
                }
                //echo '$from_timestamp: '.$from_timestamp;
                $mysql_time = date("Y-m-d H:i:s", $from_timestamp);
                if ($where != ''){
                    $where .= " and added>'$mysql_time' ";
                }else if($where == ''){
                    $where .= " where added>'$mysql_time' ";
                }
            }
            
            if ($where != ''){
                $where .= ' and status=1';
            }else if($where == ''){
                $where .= ' where status=1';
            }
            
            if (@$_COOKIE['parent_password'] != get_user_param($stb->mac, 'parent_password')){
                //$where .= ' and genre_id<>14 and genre_id<>12';
                //$where .= ' and censored<>1';
            }
            if (check_moderator($stb->mac)){
                //$where .= ' and accessed=0';
                $where_accessed = '';
            }else{
                $where .= ' and accessed=1';
                $where_accessed = ' and accessed=1';
            }
            
            if ($stb->hd){
                $where .=  ' and disable_for_hd_devices=0';
            }
            
            //$where .= ' and category_id='.$_REQUEST['cat_num'];
            if (intval($_REQUEST['cat_num']) > 0 && intval($_REQUEST['cat_num']) != 101){
                $where .= ' and category_id='.$_REQUEST['cat_num'];
                $where_cat_num = ' and category_id='.$_REQUEST['cat_num'];
            }else{
                $where_cat_num = '';
            }
            
            if (@$_REQUEST['hd']){
                $where .=  " and hd=1";
                $where_cat_num .= " and hd=1";
            }else{
                $where .=  " and hd<=".$stb->hd;
                $where_cat_num .= " and hd<=".$stb->hd;
            }
            
            //echo $where;
            $sql  = 'select video.* from '.$table.' '.$where.'  '.$order.'';
            if (isset($_REQUEST['vclub_by_top'])){
                $sql  = 'select video.*, (count_first_0_5+count_second_0_5) as count_sum from video where status=1 '.$where_cat_num.' order by count_sum desc limit 0,10';
            }elseif (isset($_REQUEST['get_vclub_not_ended'])){
                $sql  = 'select video.*, vclub_not_ended.* ,vclub_not_ended.series as cur_series from video,vclub_not_ended where video.id=vclub_not_ended.video_id and vclub_not_ended.uid='.$stb->id.' and status=1 '.$where_accessed.' limit 0,10';
            }
            //echo '$sql: '.$sql;
            $rs=$db->executeQuery($sql);
	        $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
            $sql  = 'select video.* from '.$table.' '.$where.'  '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            if (isset($_REQUEST['vclub_by_top'])){
                $sql  = 'select video   .*, (count_first_0_5+count_second_0_5) as count_sum from video where status=1 '.$where_cat_num.' order by count_sum desc limit 0,10';
            }elseif (isset($_REQUEST['get_vclub_not_ended'])){
                $sql  = 'select video.*, video.id as id,vclub_not_ended.series as cur_series,vclub_not_ended.end_time as end_time from video,vclub_not_ended where video.id=vclub_not_ended.video_id and vclub_not_ended.uid='.$stb->id.' and status=1 '.$where_accessed.' order by vclub_not_ended.added desc limit 0,10';
            }
            $id   = 'id';
            $name = 'name';
            $cmd  = 'path';
            $desc = 'description';
            $genre_name = 'genre_name';
            $director = 'director';
            $actors = 'actors';
            $cost = 'time';
            $time = 'time';
            $year = 'year';
            
            break;
        case 'video_clip':
            
            if (isset($l)){
                $where .= ' where name like "'.$l.'%"';
            }

            $letters = '';
            if (isset($action) && !isset($_REQUEST['video_clips_by_add_time'])){
                if (isset($_REQUEST['video_clips_singer_bar'])){
                    $field = 'singer';
                    $order = ' group by singer';
                }else{
                    $field = 'name';
                }
                switch ($action){
                    case 1:
                        $letters = '';
                        break;
                    case (count($abc)):
                        $letters = ' like "0%" or '.$field.' like "1%" or '.$field.' like "2%" or '.$field.' like "3%" or '.$field.' like "4%" or '.$field.' like "5%" or '.$field.' like "6%" or '.$field.' like "7%" or '.$field.' like "8%" or '.$field.' like "9%")';
                        break;
                    default:
                        $letters = ' like "'.$abc[$action-1].'%")';
                }
                
                if($letters){
                    if ($where != ''){
                        $where .= " and ($field $letters";
                    }else if($where == ''){
                        $where .= " where ($field $letters";
                    }
                }
            }
            
            if (isset($_REQUEST['singer'])){
                if ($where != ''){
                    $where .= " and singer='{$_REQUEST['singer']}'";
                }else if($where == ''){
                    $where .= " where singer='{$_REQUEST['singer']}'";
                }
            }
            
            if (isset($_REQUEST['video_clips_by_add_time'])){
                $range = intval(@$_REQUEST['action']);
                $timestamp = time();
                $from_timestamp = '';
                switch ($range){
                    case 1: // 1 day 
                        $from_timestamp = $timestamp - 60*60*24;
                        break;
                    case 2: // 2 day
                        $from_timestamp = $timestamp - 60*60*24*2;
                        break;
                    case 3: // 3 day
                        $from_timestamp = $timestamp - 60*60*24*3;
                        break;
                    case 4: // 4 day
                        $from_timestamp = $timestamp - 60*60*24*4;
                        break;
                    case 5: // 5 day
                        $from_timestamp = $timestamp - 60*60*24*5;
                        break;
                    case 6: // 6 day
                        $from_timestamp = $timestamp - 60*60*24*6;
                        break;
                    case 7: // 7 day
                        $from_timestamp = $timestamp - 60*60*24*7;
                        break;
                    case 8: // 2 week
                        $from_timestamp = $timestamp - 60*60*24*14;
                        break;
                    case 9: // 3 week
                        $from_timestamp = $timestamp - 60*60*24*21;
                        break;
                    case 10: // 1 month
                        $from_timestamp = $timestamp - 60*60*24*31;
                        break;
                }
                
                $mysql_time = date("Y-m-d H:i:s", $from_timestamp);
                if ($where != ''){
                    $where .= " and added>'$mysql_time' ";
                }else if($where == ''){
                    $where .= " where added>'$mysql_time' ";
                }
            }
            
            if ($where != ''){
                $where .= ' and status=1';
            }else if($where == ''){
                $where .= ' where status=1';
            }
            
            if (!check_moderator($stb->mac)){
                $where .= ' and accessed=1';
            }
            if (isset($_REQUEST['video_clips_singer_bar'])){
                $sql  = 'select video_clips.* from '.$table.' '.$where.'  '.$order.'';
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
    	        $sql  = 'select video_clips.* from '.$table.' '.$where.'  '.$order.'';
            }else{
                $sql  = 'select video_clips.* from '.$table.' '.$where.'  '.$order.'';
                if (isset($_REQUEST['video_clips_by_top'])){
                    $sql  = 'select video_clips.*, (count_first_0_5+count_second_0_5) as count_sum from video_clips order by count_sum desc limit 0,10';
                }
                
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql  = 'select video_clips.* from '.$table.' '.$where.'  '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                if (isset($_REQUEST['video_clips_by_top'])){
                    $sql  = 'select video_clips.*, (count_first_0_5+count_second_0_5) as count_sum from video_clips order by count_sum desc limit 0,10';
                }
            }
            
            
            $id   = 'id';
            $name = 'name';
            $singer = 'singer';
            $cmd  = 'path';
            $desc = 'description';
            $genre_name = 'genre_name';
            $director = 'director';
            $actors = 'actors';
            $cost = 'time';
            $time = 'time';
            $year = 'year';
            
            break;
        case 'video_records':
            $sql = 'select * from video_records where status=1 and accessed=1';
            $rs=$db->executeQuery($sql);
	        $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
	        $sql = 'select * from video_records where status=1 and accessed=1 order by addtime desc limit '.($page*3).', 3';
            $id   = 'id';
            $descr= 'descr';
            $cmd  = 'cmd';
            $addtime = 'addtime';
	        break;
        case 'my_video_records':
            $sql = "select * from users_rec where uid=$uid";
            $rs=$db->executeQuery($sql);
	        $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
	        $sql = 'select users_rec.*, itv.name as ch_name, (UNIX_TIMESTAMP(users_rec.t_start) - UNIX_TIMESTAMP(rec_files.t_start)) as files_t_start from users_rec, itv, rec_files where users_rec.ch_id=itv.id and users_rec.file_id=rec_files.id and uid='.$uid.' order by t_start desc limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            $id   = 'id';
	        break;
        case 'karaoke':
            if (isset($genre_id) && $where != '' && $genre_id >0){
                $where .= ' and karaoke_genre.id='.$genre_id;
            }else if(isset($genre_id) && $where == '' && $genre_id >0){
                $where .= ' where karaoke_genre.id='.$genre_id;
            }
            $letters = '';
            
            if(isset($_REQUEST['karaoke_by_name'])){
                $field = 'name';
            }else{
                $field = 'singer';
            }
            
            if (isset($action) && (isset($_REQUEST['karaoke_singer_bar']) || isset($_REQUEST['karaoke_by_name']))){
                switch ($action){
                    case 1:
                        $letters = '';
                        break;
                    case (count($abc)):
                        $letters = ' like "0%" or name like "1%" or name like "2%" or name like "3%" or name like "4%" or name like "5%" or name like "6%" or name like "7%" or name like "8%" or name like "9%")';
                        break;
                    default:
                        $letters = ' like "'.$abc[$action-1].'%")';
                }
                if($letters){
                    if ($where != ''){
                        $where .= ' and ('.$field.' '.$letters;
                    }else if($where == ''){
                        $where .= ' where ('.$field.' '.$letters;
                    }
                }
            }
            
            if ($where != ''){
                $where .= ' and status=1';
            }else if($where == ''){
                $where .= ' where status=1';
            }
            
            if (!check_moderator($stb->mac)){
                $where .= ' and accessed=1';
            }
            
            if (isset($_REQUEST['singer'])){
                $where .= " and singer='{$_REQUEST['singer']}'";
            }
            
            if (isset($_REQUEST['karaoke_singer_bar'])){
                $sql  = 'select karaoke.* from '.$table.' '.$where.' group by singer order by singer';
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql  = 'select karaoke.* from '.$table.' '.$where.' group by singer order by singer limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                break;
            }else{
                $sql  = 'select karaoke.* from '.$table.' '.$where.' order by name';
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql  = 'select karaoke.* from '.$table.' '.$where.' order by name limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            }
            $id   = 'id';
            $name = 'name';
            $cmd  = 'path';
            $desc = 'description';
            $genre_name = 'genre_name';
            $singer = 'singer';
            $author = 'author';
            $cost = 'time';
            $time = 'time';
            $year = 'year';
            
            break;
        case 'audio_club':
            $letters = '';
            
            if(isset($_REQUEST['audio_club_by_name'])){
                $field = 'audio.name';
            }else{
                $field = 'singer.singer';
                $table = 'singer';
            }
            if (isset($_REQUEST['audio_club_singer_bar'])){
                $lang_field = 'singer.lang';
            }else{
                $lang_field = 'audio.lang';
            }
            
            if (isset($_REQUEST['action']) && !isset($_REQUEST['audio_club_by_singer'])){
                    
                if (@$_REQUEST['abc'] == 0){
                    switch ($_REQUEST['action']){
                        case 1:
                            $letters = '';
                            if ($where != ''){
                                $where .= " and ($lang_field=0 or $lang_field=2)";
                            }else if($where == ''){
                                $where .= " where ($lang_field=0 or $lang_field=2)";
                            }
                            break;
                        case 2:
                            $letters = ' like "а%" or '.$field.' like "б%") ';
                            break;
                        case 3:
                            $letters = ' like "в%" or '.$field.' like "г%") ';
                            break;
                        case 4:
                            $letters = ' like "д%" or '.$field.' like "е%" or '.$field.' like "ё%" or '.$field.' like "ж%" or '.$field.' like "з%")';
                            break;
                        case 5:
                            $letters = ' like "и%" or '.$field.' like "й%" or '.$field.' like "к%" or '.$field.' like "л%" or '.$field.' like "м%")';
                            break;
                        case 6:
                            $letters = ' like "н%" or '.$field.' like "о%" or '.$field.' like "п%")';
                            break;
                        case 7:
                            $letters = ' like "р%" or '.$field.' like "с%" or '.$field.' like "т%")';
                            break;
                        case 8:
                            $letters = ' like "у%" or '.$field.' like "ф%" or '.$field.' like "х%" or '.$field.' like "ц%" or '.$field.' like "ч%")';
                            break;
                        case 9:
                            $letters = ' like "ш%" or '.$field.' like "щ%" or '.$field.' like "ъ%" or '.$field.' like "ы%" or '.$field.' like "ь%" or '.$field.' like "э%" or '.$field.' like "ю%" or '.$field.' like "я%")';
                            break;
                        case 10:
                            $letters = ' like "0%" or '.$field.' like "1%" or '.$field.' like "2%" or '.$field.' like "3%" or '.$field.' like "4%" or '.$field.' like "5%" or '.$field.' like "6%" or '.$field.' like "7%" or '.$field.' like "8%" or '.$field.' like "9%")';
                            break;
                    }
                }else if (@$_REQUEST['abc'] == 1){
                    switch ($action){
                        case 1:
                            $letters = '';
                            if ($where != ''){
                                $where .= " and ($lang_field=1 or $lang_field=2)";
                            }else if($where == ''){
                                $where .= " where ($lang_field=1 or $lang_field=2)";
                            }
                            break;
                        case 2:
                            $letters = ' like "a%" or '.$field.' like "b%" or '.$field.' like "c%") ';
                            break;
                        case 3:
                            $letters = ' like "d%" or '.$field.' like "e%" or '.$field.' like "f%" or '.$field.' like "g%") ';
                            break;
                        case 4:
                            $letters = ' like "h%" or '.$field.' like "i%" or '.$field.' like "j%")';
                            break;
                        case 5:
                            $letters = ' like "k%" or '.$field.' like "l%" or '.$field.' like "m%")';
                            break;
                        case 6:
                            $letters = ' like "n%" or '.$field.' like "o%" or '.$field.' like "p%")';
                            break;
                        case 7:
                            $letters = ' like "q%" or '.$field.' like "r%" or '.$field.' like "s%")';
                            break;
                        case 8:
                            $letters = ' like "t%" or '.$field.' like "u%" or '.$field.' like "v%")';
                            break;
                        case 9:
                            $letters = ' like "w%" or '.$field.' like "x%" or '.$field.' like "y%" or '.$field.' like "z%")';
                            break;
                        case 10:
                            $letters = ' like "0%" or '.$field.' like "1%" or '.$field.' like "2%" or '.$field.' like "3%" or '.$field.' like "4%" or '.$field.' like "5%" or '.$field.' like "6%" or '.$field.' like "7%" or '.$field.' like "8%" or '.$field.' like "9%")';
                            break;
                    }
                }
                if($letters){
                    if ($where != ''){
                        $where .= ' and ('.$field.' '.$letters;
                    }else if($where == ''){
                        $where .= ' where ('.$field.' '.$letters;
                    }
                }
            }
            
            if (isset($_REQUEST['singer'])){
                if ($where != ''){
                    $where .= " and audio.singer_id='{$_REQUEST['singer']}'";
                }else{
                    $where .= " where audio.singer_id='{$_REQUEST['singer']}'";
                }
            }
            
            if (isset($_REQUEST['audio_club_singer_bar'])){
                $sql  = 'select singer.* from '.$table.' '.$where.' order by singer';
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql  = 'select singer.* from singer '.$where.' order by singer limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                break;
            }else{
                if ($where == ''){
                    $where = ' where audio.singer_id=singer.id';
                }else{
                    $where .= ' and audio.singer_id=singer.id';
                }
                $sql  = 'select audio.*, singer.singer from audio, singer '.$where.' and status=1';
                //echo $sql;
                $rs=$db->executeQuery($sql);
    	        $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
                $sql  = 'select audio.*, singer.singer, album.name as album_name from audio, singer left join album on  audio.album_id=album.id '.$where.' and status=1 group by audio.id '.$order.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
                //echo $sql;
            }
            
            $id   = 'id';
            $name = 'name';
            $singer = 'singer';
            $time = 'time';
            $year = 'year';
            
            break;
        case 'radio':
            if (isset($_REQUEST['radio_by_name'])){
                $sql = 'select * from radio where status=1 order by name';
                $rs=$db->executeQuery($sql);
                $total_items = $rs->getRowCount();
    	        $total_pages=ceil($total_items/MAX_PAGE_ITEMS);
    	        $sql = 'select * from radio where status=1 order by name limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            }
            
            $id     = 'id';
            $number = 'number';
            $name   = 'name';
            $cmd    = 'cmd';
            break;
        case 'web':
            $sql = 'select * from web order by name limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            break;
        case 'game':
            $sql = 'select * from game order by name limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            break;
        case 'epg':
            if ($_REQUEST['data'] == 0){
                $time = time();
                $year = date("Y",$time);
                $month = date("n",$time);
                $day = date("j",$time);
                $hour = date("G",$time);
                
                $time_from = date("Y-m-d H:00:00", mktime (0,0,0,$month,$day,$year));
                $time_to = date("Y-m-d H:00:00", mktime (23,59,59,$month,$day,$year));
            }else{
                $time_from = $_REQUEST['data']." 00:00:00";
                $time_to = $_REQUEST['data']." 23:59:59";
                
            }
            
            if ($_REQUEST['data'] == 0 || $_REQUEST['data'] == date("Y-m-d")){
                $epg = new Epg();
                $epg->getCurProgram($action);
                $cur_page       = $epg->cur_program_page;
                $selected_item  = $epg->cur_program_row;
                $cur_program_id = $epg->cur_program_id;

                if ($_REQUEST['p']==0){
                    $page_offset = ($cur_page-1)*MAX_PAGE_ITEMS;
                    if ($page_offset < 0){
                        $page_offset = 0;
                    }
                }
            }else{
                $cur_program_id = 0;
            }
            
            $sql = 'select * from epg where ch_id='.$action.' and time>="'.$time_from.'" and time<"'.$time_to.'" order by time';
            //echo $sql;
            $rs=$db->executeQuery($sql);
	        $total_items = $rs->getRowCount();
	        $total_pages = ceil($total_items/MAX_PAGE_ITEMS);
            $sql = 'select * from epg where ch_id='.$action.' and time>="'.$time_from.'" and time<"'.$time_to.'" order by time limit '.$page_offset.', '.MAX_PAGE_ITEMS;
            $cmd  = 'time';
            $cost = 'id';
            break;
        
        case 'main_city_info':
            
            $sql = 'select * from main_city_info';
            $rs=$db->executeQuery($sql);
            $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/5);
	        $page_offset = $page*5;
	        $sql = 'select * from main_city_info order by num limit '.$page_offset.', 5';
            //echo $sql;
            $id     = 'id';
            $number = 'number';
            $name   = 'title';
            break;
        
        case 'other_city_info':
            
            $sql = 'select * from other_city_info';
            $rs=$db->executeQuery($sql);
            $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/5);
	        $page_offset = $page*5;
	        $sql = 'select * from other_city_info order by num limit '.$page_offset.', 5';
            //echo $sql;
            $id     = 'id';
            $number = 'number';
            $name   = 'title';
            break;
        case 'help_city_info':
            $sql = 'select * from help_city_info';
            $rs=$db->executeQuery($sql);
            $total_items = $rs->getRowCount();
	        $total_pages=ceil($total_items/5);
	        $page_offset = $page*5;
	        $sql = 'select * from help_city_info order by num limit '.$page_offset.', 5';
	        $id     = 'id';
            $number = 'number';
            $name   = 'title';
            break;
        case 'mastermind_rating':
            $sql = 'select * from mastermind_wins group by uid';
            $rs=$db->executeQuery($sql);
            $total_items = $rs->getRowCount();
            $total_pages=ceil($total_items/10);
            if ($_REQUEST['p'] == 0){
                $sql = 'select SUM(points) as sum_points from mastermind_wins where uid='.$stb->id;
                $rs=$db->executeQuery($sql);
                $uid_points = intval($rs->getValueByName(0, 'sum_points'));
                if ($uid_points>0){
                    $sql = 'select SUM(points) as sum_points,uid,MIN(tries) as min_tries, MIN(total_time) as min_time from mastermind_wins group by uid order by sum_points desc,min_tries,min_time';
                    $rs=$db->executeQuery($sql);
                    $n = 1;
                    while (@$rs->next()) {
                    	if ($rs->getCurrentValueByName('uid') != $stb->id){
                    	    $n++;
                    	}else{
                    	    break;
                    	}
                    }
                    
                    $page = ceil($n/10)-1;
                    $cur_page = $page+1;
                }else{
                    $page = 0;
                    $cur_page = $page+1;
                }
            }
            $page_offset = $page*10;
            $place = $page*10;
            $sql = 'select uid, name, count(uid) as games, MIN(tries) as min_tries, MIN(total_time) as min_time, SUM(points) as sum_points from mastermind_wins,users where mastermind_wins.uid=users.id group by uid order by sum_points desc, min_tries,min_time limit '.$page_offset.', 10';
            break;
        case 'recipes_by_cat':
            
            $req = $_REQUEST['data'];
            $cat_id = intval($req['cat_id']);
            $fav = intval($req['fav']);;
            
            $order = ' order by name ';
            
            if ($cat_id > 0){
                $where = "where (recipe_cat_id_1=$cat_id or recipe_cat_id_2=$cat_id or recipe_cat_id_3=$cat_id or recipe_cat_id_4=$cat_id) ";
            }else{
                $where = '';
            }
            
            if ($search){
                if ($where){
                    $where .= ' and (name like "%'.$search.'%" or descr like "%'.$search.'%" or ingredients like "%'.$search.'%") ';
                }else{
                    $where .= ' where (name like "%'.$search.'%" or descr like "%'.$search.'%" or ingredients like "%'.$search.'%") ';
                }
            }
            $fav_recipes_arr = array();
            
            
            $sql = 'select * from fav_recipes where uid='.$stb->id;
            $rs=$db->executeQuery($sql);
            if ($rs->getRowCount() > 0){
                $fav_recipes = $rs->getValueByName(0, 'fav_recipes'); 
                
                if ($fav_recipes){
                    $fav_recipes_arr = unserialize($fav_recipes);
                }
            }
            
            if ($fav){
                if (is_array($fav_recipes_arr) && count($fav_recipes_arr) > 0){
                    $fav_recipes_str = join(",", $fav_recipes_arr);
                    if ($where){
                        $where .= ' and recipes.id in ('.$fav_recipes_str.')';
                        $order = ' order by field(recipes.id,'.$fav_recipes_str.')';
                    }else{
                        $where .= ' where recipes.id in ('.$fav_recipes_str.')';
                        $order = ' order by field(recipes.id,'.$fav_recipes_str.')';
                    }
                }else{
                    if ($where){
                        $where .= ' and recipes.id=0';
                    }else{
                        $where .= ' where recipes.id=0';
                    }
                }
            }
            
            $sql = 'select * from recipes '.$where;
            //echo $sql;
            $rs=$db->executeQuery($sql);
            $total_items = $rs->getRowCount();
            $total_pages = ceil($total_items/10);
            $page_offset = $page*10;
            
	        $sql = 'select * from recipes '.$where.' '.$order.' limit '.$page_offset.', 10';
	        //echo $sql;
	        $id     = 'id';
            $number = 'number';
            $name   = 'title';
            
            break;
        case 'anec':
            $sql = "select * from anec";
            $rs = $db->executeQuery($sql);
            $total_items = $total_pages = $rs->getRowCount();
            $page_offset = $page;
            $sql = 'select *, DATE(added) as added from anec order by id desc limit '.$page_offset.',1';
            //echo $sql;
            /*if ($_REQUEST['data']){
                $dir    = $_REQUEST['data']['dir'];
                $cur_id = intval($_REQUEST['data']['cur_id']);
                if ($dir>0){
                    $where = "id>$cur_id";
                    $desc = '';
                }else{
                    $where = "id<$cur_id";
                    $desc = 'desc';
                }
                $sql = "select * from anec where $where order by id $desc limit 0,1";
            }else{
                $sql = "select * from anec order by id desc limit 0,1";
            }*/
            
            $rs = $db->executeQuery($sql);
            $id = 'id';
            break;
        default:
            $sql = 'select * from '.$type.' limit '.$page_offset.', '.MAX_PAGE_ITEMS;
    }
    $rs=$db->executeQuery($sql);
    $data['total_items'] = @$total_items;
    $data['max_page_items'] = MAX_PAGE_ITEMS;
    $data['selected_item'] = $selected_item;
    $data['cur_page']      = $cur_page;
    $arr = array();
    
    while(@$rs->next()){
        /*if($type != 'epg' && $type != 'vod' && $type != 'itv' && $type != 'karaoke' && $type != 'audio_club' && $type != 'radio' && $type != 'video_records' && $type != 'my_video_records' && $type != 'video_clip' && $type != 'main_city_info' && $type != 'main_city_info'){
            $arr[] = array(
                'id'   => $rs->getCurrentValueByName($id),
                'name' => $rs->getCurrentValueByName($name),
                'cmd'  => $rs->getCurrentValueByName($cmd),
                'desc' => nl2br($rs->getCurrentValueByName($desc)),
                'cost' => $rs->getCurrentValueByName($cost)
            );
        }else */
        if($type == 'itv'){
            $itv_id = $rs->getCurrentValueByName($id);
            $cur_epg = get_cur_program($itv_id);
            if (@$_REQUEST['data'] == 1){
                $num = array_search($itv_id, $fav_arr)+1;
            }else{
                $num = $rs->getCurrentValueByName($number);
            }
            if (@in_array($itv_id,$fav_arr)){
                $mark = '&bull;';
            }else{
                $mark = '';
            }
            if (ENABLE_SUBSCRIPTION){
                if (@in_array($itv_id, $all_subscription_and_base_ch)){
                    $open = 1;
                    $cmd_str  = $rs->getCurrentValueByName($cmd);
                }else{
                    $open = 0;
                    $cmd_str  = 'wtf?';
                }
            }else{
                $open = 1;
                $cmd_str  = $rs->getCurrentValueByName($cmd);
            }
            $arr[] = array(
                'id'         => $itv_id,
                'number'     => $num,
                'name'       => $rs->getCurrentValueByName($name),
                'censored'   => $rs->getCurrentValueByName('censored'),
                'cmd'        => $cmd_str,
                'desc'       => nl2br($rs->getCurrentValueByName($desc)),
                'my_ch'      => $mark,
                'open'       => $open,
                'cost'       => $rs->getCurrentValueByName($cost),
                'time_start' => $cur_epg[0],
                'cur_prog'   => $cur_epg[1]
            );
        }else if($type == 'vod'){
            
            $video_id = $rs->getCurrentValueByName($id);
            $genres_str = implode(", ", get_video_genres($video_id));
            
            $vod_descr =   'Жанр: '.$genres_str."\n".
                           'Год: '.$rs->getCurrentValueByName($year)."\n".
                           'Длительность: '.$rs->getCurrentValueByName($time)."\n".
                           'Режиссер: '.$rs->getCurrentValueByName($director)."\n".
                           'В ролях: '.$rs->getCurrentValueByName($actors)."\n\n".
                           $rs->getCurrentValueByName($desc);
            $p_name = $rs->getCurrentValueByName($name);
            $o_name = $rs->getCurrentValueByName('o_name');
            if ($o_name){
                $full_name = $p_name.' / '.$o_name;
            }else{
                $full_name = $p_name;
            }
            
            if (@in_array($video_id,$fav_arr)){
                $mark = '&bull;';
            }else{
                $mark = '';
            }
            $cur_series = 0;
            $end_time   = 0;
            if (isset($_REQUEST['get_vclub_not_ended'])){
                $cur_series = $rs->getCurrentValueByName('cur_series');
                $end_time   = $rs->getCurrentValueByName('end_time');
            }
            
            $arr[] = array(
                'id'          => $video_id,
                'name'        => $full_name,
                'cmd'         => $rs->getCurrentValueByName($cmd),
                'genre_name'  => $genres_str,
                'censored'    => $rs->getCurrentValueByName('censored'),
                'my_video'    => $mark,
                'year'        => $rs->getCurrentValueByName($year),
                'desc'        => nl2br($vod_descr),
                'cost'        => $rs->getCurrentValueByName($cost),
                'time'        => $rs->getCurrentValueByName($time),
                'director'    => $rs->getCurrentValueByName($director),
                'series'      => unserialize($rs->getCurrentValueByName('series')),
                'screenshots' => get_video_screenshots($rs->getCurrentValueByName($id)),
                'cur_series'  => $cur_series,
                'end_time'    => $end_time
            );
        }else if ($type == 'video_clip'){
            if (isset($_REQUEST['video_clips_singer_bar'])){
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName('id'),
                    'singer' => $rs->getCurrentValueByName('singer'),
                );
            }else{
                $arr[] = array(
                    'id'   => $rs->getCurrentValueByName($id),
                    'name' => $rs->getCurrentValueByName($name),
                    'singer' => $rs->getCurrentValueByName($singer),
                    //'desc' => nl2br($rs->getCurrentValueByName($desc)),
                    'cost' => $rs->getCurrentValueByName($cost)
                );
            }
        }else if ($type == 'video_records'){
            $rec_id = $rs->getCurrentValueByName($id);
            $arr[] = array(
                    'id'    => $rec_id,
                    'descr' => $rs->getCurrentValueByName($descr),
                    'cmd'   => $rec_id.'.mpg'
                );
            $data['max_page_items'] = 3;
        }else if ($type == 'my_video_records'){
            $rec_id = $rs->getCurrentValueByName($id);
            $arr[] = array(
                    'id'            => $rec_id,
                    't_start'       => datetime2human($rs->getCurrentValueByName('t_start')),
                    'ch_name'       => $rs->getCurrentValueByName('ch_name'),
                    'ch_id'         => $rs->getCurrentValueByName('ch_id'),
                    'file_id'       => $rs->getCurrentValueByName('file_id'),
                    'length'        => sec2hhmmss($rs->getCurrentValueByName('length')),
                    'length_s'      => $rs->getCurrentValueByName('length'),
                    'atrack'        => $rs->getCurrentValueByName('atrack'),
                    'vtrack'        => $rs->getCurrentValueByName('vtrack'),
                    'files_t_start' => $rs->getCurrentValueByName('files_t_start'),
                    'ended'         => $rs->getCurrentValueByName('ended'),
                );
        }else if($type == 'karaoke'){
            if (isset($_REQUEST['karaoke_singer_bar'])){
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName('id'),
                    'singer' => $rs->getCurrentValueByName('singer'),
                );
            }else{
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName($id),
                    'name'   => $rs->getCurrentValueByName($name),
                    'cmd'    => $rs->getCurrentValueByName($cmd),
                    'cost'   => $rs->getCurrentValueByName($cost),
                    'time'   => $rs->getCurrentValueByName($time),
                    'singer' => $rs->getCurrentValueByName($singer),
                    'author' => $rs->getCurrentValueByName($author),
                );
            }
        }else if($type == 'audio_club'){
            if (isset($_REQUEST['audio_club_singer_bar'])){
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName('id'),
                    'singer' => $rs->getCurrentValueByName('singer'),
                );
            }else{
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName($id),
                    'name'   => $rs->getCurrentValueByName($name),
                    'time'   => $rs->getCurrentValueByName($time),
                    'singer' => $rs->getCurrentValueByName($singer),
                    'album'  => $rs->getCurrentValueByName('album_name'),
                    'time'   => $rs->getCurrentValueByName('time'),
                );
            }
        }else if($type == 'radio'){
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName($id),
                    'number' => $rs->getCurrentValueByName($number),
                    'name'   => $rs->getCurrentValueByName($name),
                    'cmd'    => $rs->getCurrentValueByName($cmd),
                );
        }else if($type == 'main_city_info' || $type == 'other_city_info' || $type == 'help_city_info'){
            $data['max_page_items'] = 5;
                $arr[] = array(
                    'id'     => $rs->getCurrentValueByName($id),
                    'title'  => $rs->getCurrentValueByName('title'),
                    'number' => $rs->getCurrentValueByName('number')
                );
        }else if($type == 'mastermind_rating'){
            $selected = 0;
            $place ++;
            if ($rs->getCurrentValueByName('uid') == $stb->id){
                $selected = 1;
            }
            $arr[] = array(
                'place'      => $place,
                'selected'   => $selected,
                'name'       => $rs->getCurrentValueByName('name'),
                'games'      => $rs->getCurrentValueByName('games'),
                'min_tries'  => $rs->getCurrentValueByName('min_tries'),
                'min_time'   => $rs->getCurrentValueByName('min_time'),
                'sum_points' => $rs->getCurrentValueByName('sum_points'),
            );
        }else if($type == 'anec'){
            $data['max_page_items'] = 1;
            $anec_id = $rs->getCurrentValueByName($id);
                $arr[] = array(
                    'id'        => $anec_id,
                    'anec_body' => nl2br($rs->getCurrentValueByName('anec_body')),
                    'added'     => $rs->getCurrentValueByName('added'),
                    'rating'    => get_anec_rating($anec_id),
                    'voted'     => get_anec_voted($anec_id, $stb->id)
                );
        }else if($type == 'recipes_by_cat'){
            $fav = 0;
            $id = $rs->getCurrentValueByName('id');

            if (in_array($id,$fav_recipes_arr)){
                $fav = 1;
            }
            
            $arr[] = array(
                'id'           => $id,
                'name'         => $rs->getCurrentValueByName('name'),
                'descr'        => $rs->getCurrentValueByName('descr'),
                'ingredients'  => $rs->getCurrentValueByName('ingredients'),
                'fav'          => $fav
            );
        }else if($type == 'epg'){
            $arr[] = array(
                'id'   => $rs->getCurrentValueByName($id),
                'name' => $rs->getCurrentValueByName($name),
                'cmd'  => time_mysql2epg($rs->getCurrentValueByName($cmd)),
                'desc' => $rs->getCurrentValueByName($desc),
                'cost' => $rs->getCurrentValueByName($cost),
                'sel'  => 0,
                't_st' => $rs->getCurrentValueByName($cmd),
            );
        }else{
            $arr[] = array(
                'id'   => $rs->getCurrentValueByName($id),
                'name' => $rs->getCurrentValueByName($name),
                'cmd'  => $rs->getCurrentValueByName($cmd),
                'desc' => nl2br($rs->getCurrentValueByName($desc)),
                'cost' => $rs->getCurrentValueByName($cost)
            );
        }
        
    }
    //var_dump($data);
    if ($type == 'epg'){
        for ($i=0; $i<count($arr); $i++){
            if ($arr[$i]['id'] == $cur_program_id){
                $arr[$i]['sel'] = 1;
            }else{
                $arr[$i]['sel'] = 0;
            }
        }
    }
    $data['data'] = $arr;
    
    return $data;
}

function time_mysql2epg($datetime){
    preg_match("/(\d+):(\d+)/", $datetime, $arr);
    return $arr[0];
}

function datetime2human($datetime){
    $ts = datetime2timestamp($datetime);
    return date("H:i d.m.y", $ts);
}

function get_cur_program($ch_id){
    $db = Database::getInstance(DB_NAME);
    
    $time  = time();
    $year  = date("Y",$time);
    $month = date("n",$time);
    $day   = date("j",$time);
    $hour  = date("G",$time);
    $time_from = mktime (0,0,0,$month,$day,$year);
    $time_from = date("Y-m-d H:00:00", $time_from);
    
    $time_to = mktime (23,59,59,$month,$day,$year);
    $time_to = date("Y-m-d H:59:59", $time_to);
    $time_now = date("Y-m-d H:00:00");

    $sql = 'select * from epg where ch_id='.$ch_id.' and time>="'.$time_from.'" and time<"'.$time_to.'" order by time';
    
    $rs=$db->executeQuery($sql);
    $cur_program = '';
    $time_start = '';
    $arr = array();
    while(@$rs->next()){
        $time_start = $rs->getCurrentValueByName('time');
        $program = $rs->getCurrentValueByName('name');
        $timestamp_start = datetime2timestamp($time_start);
        $arr[] = array(
            'time_start'      => $time_start,
            'timestamp_start' => $timestamp_start,
            'program'         => $program
        );
    }
    $result = array('-', '-');
    if (@$arr){
        for ($i=0; $i<count($arr); $i++){
            if ($arr[$i]['timestamp_start'] <= time()){
                if ($i>0){
                }
            }
            else{
                return array(time_mysql2epg($arr[$i-1]['time_start']),$arr[$i-1]['program']);
            }
        }
    }
    return $result;
}

function get_week(){
    $ru_days = array('ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС');
    $ru_month = array('янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сент', 'окт', 'ноя', 'дек');
    $cur_num_day = date('w')-1;
    $cur_num_week = date('');
    
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    
    for ($i=0; $i<=6; $i++){
        $w_day   = date("d", mktime (0, 0, 0, $month, $day-$cur_num_day+$i, $year));
        $w_month = date("n", mktime (0, 0, 0, $month, $day-$cur_num_day+$i, $year))-1;
        $arr[$i]['f_human'] = $ru_days[$i].' '.$w_day.$ru_month[$w_month];
        $arr[$i]['f_mysql'] = date("Y-m-d", mktime (0, 0, 0, $month, $day-$cur_num_day+$i, $year));
        $arr[$i]['today'] = 0;
        if ($cur_num_day == $i){
            $arr[$i]['today'] = 1;
        }
    }
    return $arr;
}

function get_video_screenshots($id){
    $db = Database::getInstance(DB_NAME);
    
    $sql = 'select * from screenshots where media_id='.$id;
    $rs=$db->executeQuery($sql);
    $arr = array();
    while(@$rs->next()){
        $arr[] = array(
                    'uri' => get_img_uri($rs->getCurrentValueByName('id'))
                    );
    }
    return $arr;
}

function get_user_param($mac, $param){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from users where mac='$mac'";
    $rs = $db->executeQuery($sql);
    $result = $rs->getValueByName(0, $param);
    return $result;
}

function check_moderator($mac){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from moderators where mac='$mac' and status=1";
    $rs = $db->executeQuery($sql);
    $result = $rs->getValueByName(0, 'id');
    return $result;
}

/*function increment_counter($id, $table){
    $db = Database::getInstance(DB_NAME);
    
    $mac = $_COOKIE['mac'];
    
    $stb->mac = '';
    if (@$_COOKIE['mac']){
        $stb->mac = $_COOKIE['mac'];
    }
    
    if ($mac){
        $query = "select * from moderators where mac='$mac'";
        $rs = $db->executeQuery($query);
        $mid = @$rs->getValueByName(0, 'id');
        
        //$sql= "insert into start_play_$table (media_id, uid, starttime) value ($id, $uid, NOW())";
        //echo '$sql: '.$sql;
        //$rs = $db->executeQuery($sql);
        
        //if (!$mid){
            //$sql = "select * from $table where id=$id";
            //$rs = $db->executeQuery($sql);
            //$cur_count = $rs->getValueByName(0, 'count');
            //$sql = "update $table set count=".($cur_count+1)." where id=$id";
            //$rs = $db->executeQuery($sql);
        }
    }
}*/

function get_fav_ids($uid = 0){
    $db  = Database::getInstance(DB_NAME);
    $stb = Stb::getInstance();
    
    $itv_ch = array();
    
    if ($uid == 0){
        $uid = $stb->id;
    }
    
    $sql = "select * from fav_itv where uid=$uid";
    $rs = $db->executeQuery($sql);
    $itv_ch = $rs->getValueByName(0, 'fav_ch'); 
    $itv_ids = array();
    if ($itv_ch){
        $itv_ids = unserialize(base64_decode($itv_ch));
    }
    if (is_array($itv_ids) && count($itv_ids) > 0){
        $fav_str = join(",", $itv_ids);
        $sql = "select * from itv where itv.id in ($fav_str) and status=1 order by field(itv.id,$fav_str)";
        $rs = $db->executeQuery($sql);
        $itv_ch_e = $rs->getValuesByName('id'); 
    }else{
        $itv_ch_e = array();
    }
    return $itv_ch_e;
}

function get_playlist_ids(){
    $db  = Database::getInstance(DB_NAME);
    $stb = Stb::getInstance();
    
    $uid = $stb->id;
    
    $sql = "select * from playlist where uid=$uid";
    $rs = $db->executeQuery($sql);
    $tracks = unserialize($rs->getValueByName(0, 'tracks'));
    $palylist_ids = array();
    if (is_array($tracks)){
        $palylist_ids = $tracks;
    }
    return $palylist_ids;
}

function get_fav_video_ids(){
    $db = Database::getInstance(DB_NAME);
    $stb = Stb::getInstance();
    
    $uid = $stb->id;
    
    $sql = "select * from fav_vclub where uid=$uid";
    //echo '$sql: '.$sql;
    $rs = $db->executeQuery($sql);
    $fav_video = unserialize($rs->getValueByName(0, 'fav_video'));
    $fav_video_ids = array();
    if (is_array($fav_video)){
        $fav_video_ids = $fav_video;
    }
    return $fav_video_ids;
}

function get_video_genres($video_id){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from video where id=$video_id";
    $rs = $db->executeQuery($sql);
    
    $genre_id = array();
    $genre_names = array();
    
    for ($i = 1; $i <= 4; $i++){
        if ($rs->getValueByName(0, 'cat_genre_id_'.$i) > 0){
            $genre_id[$i] = $rs->getValueByName(0, 'cat_genre_id_'.$i);
            
            $sql2 = "select * from cat_genre where id=".$genre_id[$i];
            $rs2 = $db->executeQuery($sql2);
            $genre_names[$i] = strtolower($rs2->getValueByName(0, 'title'));
            
        }else{
            break;
        }
    }
    
    return $genre_names;
}

function sec2hhmmss($sec){
    $hhmmss = '';
    
    $hh = floor($sec/3600);
    $mm = floor(($sec - $hh*3600)/60);
    $ss = $sec - $hh*3600 - $mm*60;
    if ($hh){
        $hhmmss .= $hh.'ч ';
    }   
    if ($mm){
        $hhmmss .= $mm.'м ';
    }
    if ($ss){
        $hhmmss .= $ss.'с ';
    }
    if (!$hhmmss){
        $hhmmss = '0с';
    }
    return $hhmmss;
}

function get_my_video_length_s($id){
    $db = Database::getInstance(DB_NAME);
    
    $length_s = 0;
    
    $sql = "select * from users_rec where id=$id";
    $rs = $db->executeQuery($sql);
    $rows = $rs->getRowCount();
    if ($rows == 1){
        $ended = $rs->getValueByName(0, 'ended');
    
        if ($ended == 1){
            $length_s = $rs->getValueByName(0, 'length');
        }else{
            $t_start = $rs->getValueByName(0, 't_start');
            $length_s = time() - mysql2timestamp($t_start);
        }
    }
    return $length_s;
}

function mysql2timestamp($datetime){
    preg_match("/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/", $datetime, $arr);
    return @mktime($arr[4], $arr[5], $arr[6], $arr[2], $arr[3], $arr[1]);
}

function _log($txt){
    /*
    $fp = fopen ("/var/www/log.txt", "a");
    print_r( $txt."\n");
    fwrite($fp, $txt."\n");
    fclose($fp);
    */
}

function get_years(){
    $years = array();
    $to = date("Y");
    $from = 1944;
    
    for($i=$to; $i>=$from; $i--){
        $years[] = $i;
    }
    return $years;
}

function slice_year($page){
    $years = get_years();
    $page = $page+1;
    $from = ($page-1)*10;
    $items   = 10;
    $out = array_slice($years, $from, $items);
    return $out;
}
?>