<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Cache;
use Stalker\Lib\Core\Advertising;

/**
 * Main VOD class.
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Vod extends AjaxResponse implements \Stalker\Lib\StbApi\Vod
{
    private static $instance = NULL;

    public static function getInstance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function createLink()
    {

        if (preg_match("/\/media\/(\d+).mpg(.*)/", $_REQUEST['cmd'], $tmp_arr)){
            $file_id  = 0;
            $media_id = $tmp_arr[1];
        }elseif (preg_match("/\/media\/file_(\d+).mpg(.*)/", $_REQUEST['cmd'], $tmp_arr)){
            $file_id = $tmp_arr[1];

            $file = Video::getFileById($file_id);

            if (!empty($file)){
                $media_id = $file['video_id'];
            }else{
                $media_id = 0;
            }

            if (!empty($_REQUEST['series'])){

                $season_id = Mysql::getInstance()->from('video_season_series')
                    ->where(array(
                        'id' => $file['series_id']
                    ))
                    ->get()
                    ->first('season_id');

                $episode = Mysql::getInstance()->from('video_season_series')
                    ->where(array(
                        'season_id'     => $season_id,
                        'series_number' => intval($_REQUEST['series'])
                    ))
                    ->get()
                    ->first();

                if ($file['series_id'] != $episode['id']){
                    $file_id = Mysql::getInstance()->from('video_series_files')
                        ->where(array(
                            'series_id' => $episode['id']
                        ))
                        ->get()
                        ->first('id');
                }
            }
        }

        if ($file){
            $subtitles = Mysql::getInstance()
                ->from('video_series_files')
                ->where(array('video_id' => $file['video_id'], 'series_id' => $file['series_id'], 'file_type' => 'sub'))
                ->get()
                ->all();

            $subtitles = array_map(function ($subtitle){

                $languages = unserialize($subtitle['languages']);

                if ($languages && is_array($languages) && count($languages) > 0){
                    $lang = $languages[0];
                }else{
                    $lang = '';
                }

                return array(
                    'file' => $subtitle['url'],
                    'lang' => $lang
                );
            }, $subtitles);
        }else{
            $subtitles = array();
        }

        $params = $tmp_arr[2];

        $forced_storage = $_REQUEST['forced_storage'];
        $disable_ad     = $_REQUEST['disable_ad'];

        $link = $this->getLinkByVideoId($media_id, intval($_REQUEST['series']), $forced_storage, $file_id);

        if (!empty($link['subtitles'])){
            $subtitles = array_merge($subtitles, $link['subtitles']);
        }

        $link['subtitles'] = $subtitles;

        if ($_REQUEST['download']){

            if (preg_match('/\.(\w*)$/', $link['cmd'], $match)){
                $extension = $match[1];
            }

            $downloads = new Downloads();
            $link['cmd'] = $downloads->createDownloadLink('vclub', $media_id, Stb::getInstance()->id, intval($_REQUEST['series'])).(isset($extension) ? '&ext=.'.$extension : '');
        }else{
            $link['cmd'] = $link['cmd'] . $params;
        }

        if (Config::get('enable_tariff_plans')){
            $user = User::getInstance(Stb::getInstance()->id);

            $options = $user->getServicesByType('option');

            if ($options && array_search('disable_vclub_ad', $options) !== false){
                $disable_ad = true;
            }
        }

        $moderator = $this->db
            ->from('moderators')
            ->where(array('mac' => Stb::getInstance()->mac))
            ->use_caching()
            ->get()
            ->first();

        if (!$disable_ad) {
            $disable_ad = !empty($moderator) && $moderator['status'] == 1 && $moderator['disable_vclub_ad'] == 1 || !empty($_REQUEST['download']);
        }

        $vclub_ad = new VclubAdvertising();

        /*$advertising = new Advertising();
        $advert = $advertising->getAd(Stb::getInstance()->id);*/

        if (!$disable_ad && empty($link['error'])){

            $video = Video::getById($media_id);

            if (!empty($advert) && !empty($advert['config']['places']) && $advert['config']['places']['before_video'] == 1){

                $link = array(
                    array(
                        'id'            => 0,
                        'media_type'    => 'advert',
                        'cmd'           => $advert['ad'],
                        'is_advert'     => true,
                        'ad_tracking'   => $advert['tracking'],
                        'ad_must_watch' => 25
                    ),
                    $link
                );

            }else{

                $picked_ad = $vclub_ad->getOneWeightedRandom($video['category_id']);

            if (!empty($picked_ad)){

                $link['cmd'] = $_REQUEST['cmd'];

                    $link = array(
                        array(
                            'id'            => 0,
                            'ad_id'         => $picked_ad['id'],
                            'ad_must_watch' => $picked_ad['must_watch'],
                            'media_type'    => 'vclub_ad',
                            'cmd'           => $picked_ad['url'],
                            'subtitles'     => $subtitles
                        ),
                        $link
                    );
                }
            }

        }

        var_dump($link);

        return $link;
    }

    public function getLinkByVideoId($video_id, $series = 0, $forced_storage = "", $file_id = 0)
    {

        $video_id = intval($video_id);

        if (Config::get('enable_tariff_plans')){

            $user = User::getInstance($this->stb->id);
            $all_user_video_ids = $user->getServicesByType('video', 'single');

            if ($all_user_video_ids === null){
                $all_user_video_ids = array();
            }

            if ($all_user_video_ids != 'all'){
                $all_user_video_ids = array_flip($all_user_video_ids);
            }

            $all_user_rented_video_ids = $user->getAllRentedVideo();

            if ((array_key_exists($video_id, $all_user_video_ids) || $all_user_video_ids == 'all') && !array_key_exists($video_id, $all_user_rented_video_ids)){
                return array(
                    'id'         => $video_id,
                    'error'      => 'access_denied'
                );
            }

            $video = Video::getById($video_id);

            if (!empty($video['rtsp_url']) && !$file_id){
                return array(
                    'id'  => $video_id,
                    'cmd' => $this->changeSeriesOnCustomURL($video['rtsp_url'], $series)
                );
            }
        }

        $master = new VideoMaster();

        try {
            $res = $master->play($video_id, intval($series), true, $forced_storage, $file_id);
            $res['cmd'] = $this->changeSeriesOnCustomURL($res['cmd'], $series);

            $file = Video::getFileById($file_id);

            if ($file['tmp_link_type'] == 'flussonic'){
                $res['cmd'] .= (strpos($res['cmd'], '?') ? '&' : '?' ).'token='.Master::createTemporaryLink($this->stb->id);
            }elseif ($file['tmp_link_type'] == 'nginx'){

                $secret = Config::get('nginx_secure_link_secret');

                if(preg_match('/http(s)?:\/\/([^\/]+)\/(.+)$/', $res['cmd'], $match)){
                    $uri = '/'.$match[3];
                }else{
                    $uri = '';
                }

                $remote_addr = $this->stb->ip;
                $expire = time() + Config::getSafe('vclub_nginx_tmp_link_ttl', 7200);

                $hash = base64_encode(md5($secret.$uri.$remote_addr.$expire, true));

                $hash = strtr($hash, '+/', '-_');
                $hash = str_replace('=', '', $hash);

                $res['cmd'] .= (strpos($res['cmd'], '?') ? '&' : '?' ).'st='.$hash.'&e='.$expire;

            }elseif ($file['tmp_link_type'] == 'wowza'){
                $res['cmd'] .= (strpos($res['cmd'], '?') ? '&' : '?' ).'token='.Master::createTemporaryLink('1');
            } elseif ($file['tmp_link_type'] == 'edgecast_auth'){
                $res['cmd'] .= (strpos($res['cmd'], '?') ? '&' : '?' ). Itv::getEdgeCastAuthToken('EDGECAST_VIDEO_SECURITY_TOKEN_TTL');
            }

            array_walk($file, function(&$row, $key){
                $row = $key . ' = ' . $row;
            });

            error_log('---------------');
            error_log(implode('; ', $file));
            error_log('---------------');
            error_log($res['cmd']);
            error_log('---------------');

        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }

        return $res;
    }

    public function getLinkByFileId($file_id, $forced_storage = ""){

        $video = Video::getVideoByFileId($file_id);
        $video_id = $video['id'];
        $file = Video::getFileById($file_id);

        if (Config::get('enable_tariff_plans')){

            $user = User::getInstance($this->stb->id);
            $all_user_video_ids = $user->getServicesByType('video', 'single');

            if ($all_user_video_ids === null){
                $all_user_video_ids = array();
            }

            if ($all_user_video_ids != 'all'){
                $all_user_video_ids = array_flip($all_user_video_ids);
            }

            $all_user_rented_video_ids = $user->getAllRentedVideo();

            if ((array_key_exists($video_id, $all_user_video_ids) || $all_user_video_ids == 'all') && !array_key_exists($video_id, $all_user_rented_video_ids)){
                return array(
                    'id'         => $video_id,
                    'error'      => 'access_denied'
                );
            }

            if ($file['protocol'] == 'custom_url' && !empty($file['url'])) {
                return array(
                    'id'  => $video_id,
                    'cmd' => $file['url']
                );
            }
        }

        $master = new VideoMaster();

        try {
            $res = $master->play($video_id, 0, true, $forced_storage, $file_id);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }

        return $res;
    }

    public function getUrlByVideoId($video_id, $series = 0, $forced_storage = "", $file_id = 0)
    {

        $video = Video::getById($video_id);

        if (empty($video)) {
            throw new Exception("Video not found");
        }

error_log('tut - ' . __LINE__);
        if (!empty($video['rtsp_url']) && !$file_id) {
            return $video['rtsp_url'];
        }
        $link = $this->getLinkByVideoId($video_id, $series, $forced_storage, $file_id);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
        }

        if (!empty($link['storage_id'])){
            $storage = Master::getStorageById($link['storage_id']);
            if (!empty($storage)){
                $cache = Cache::getInstance();
                $cache->set($this->stb->id.'_playback',
                    array('type' => 'video', 'id' => $link['id'], 'storage' => $storage['storage_name'], 'storage_id' => $storage['id']), 0, 10);
            }
        }else{
            $cache = Cache::getInstance();
            $cache->del($this->stb->id.'_playback');
        }

        return $link['cmd'];
    }

    public function getUrlByFileId($file_id, $forced_storage = ""){

        $video = Video::getVideoByFileId($file_id);

        if (empty($video)) {
            throw new Exception("Video not found");
        }

        $file = Video::getFileById($file_id);

        if ($file['protocol'] == 'custom_url' && !empty($file['url'])) {
            return $file['url'];
        }

        $link = $this->getLinkByFileId($file_id, $forced_storage);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
        }

        if (!empty($link['storage_id'])){
            $storage = Master::getStorageById($link['storage_id']);
            if (!empty($storage)){
                $cache = Cache::getInstance();
                $cache->set($this->stb->id.'_playback',
                    array('type' => 'video', 'id' => $link['id'], 'storage' => $storage['storage_name']), 0, 10);
            }
        }else{
            $cache = Cache::getInstance();
            $cache->del($this->stb->id.'_playback');
        }

        return $link['cmd'];
    }

    public function delLink()
    {

        $item = $_REQUEST['item'];

        if (preg_match("/\/(\w+)$/", $item, $tmp_arr)) {

            $key = $tmp_arr[1];

            var_dump($tmp_arr, strlen($key));

            if (strlen($key) != 32) {
                return false;
            }

            return Cache::getInstance()->del($key);
        }

        return false;
    }

    public function getMediaCats()
    {

        return $this->db->get('media_category')->all();

    }

    public function setVote()
    {

        if ($_REQUEST['vote'] == 'good') {
            $good = 1;
            $bad = 0;
        } else {
            $good = 0;
            $bad = 1;
        }

        $type = $_REQUEST['type'];

        $this->db->insert('vclub_vote',
            array(
                'media_id' => intval($_REQUEST['media_id']),
                'uid' => $this->stb->id,
                'vote_type' => $type,
                'good' => $good,
                'bad' => $bad,
                'added' => 'NOW()'
            ));

        //$video = $this->db->getFirstData('video', array('id' => intval($_REQUEST['media_id'])));
        $video = $this->db->from('video')->where(array('id' => intval($_REQUEST['media_id'])))->get()->first();

        $this->db->update('video',
            array(
                'vote_' . $type . '_good' => $video['vote_' . $type . '_good'] + $good,
                'vote_' . $type . '_bad' => $video['vote_' . $type . '_bad'] + $bad,
            ),
            array('id' => intval($_REQUEST['media_id'])));

        return true;
    }

    public function setPlayed()
    {

        $video_id = intval($_REQUEST['video_id']);
        $storage_id = intval($_REQUEST['storage_id']);

        if (date("j") <= 15) {
            $field_name = 'count_first_0_5';
        } else {
            $field_name = 'count_second_0_5';
        }

        $video = $this->db->from('video')->where(array('id' => $video_id))->get()->first();

        $this->db->update('video',
            array(
                $field_name => $video[$field_name] + 1,
                'count' => $video['count'] + 1,
                'last_played' => 'NOW()'
            ),
            array('id' => $video_id));

        $this->db->insert('played_video',
            array(
                'video_id' => $video_id,
                'uid' => $this->stb->id,
                'storage' => $storage_id,
                'playtime' => 'NOW()'
            ));

        $this->db->update('users',
            array('time_last_play_video' => 'NOW()'),
            array('id' => $this->stb->id));

        $today_record = $this->db->from('daily_played_video')->where(array('date' => date('Y-m-d')))->get()->first();

        if (empty($today_record)) {

            $this->db->insert('daily_played_video',
                array(
                    'count' => 1,
                    'date' => date('Y-m-d')
                ));

        } else {

            $this->db->update('daily_played_video',
                array(
                    'count' => $today_record['count'] + 1,
                    'date' => date('Y-m-d')
                ),
                array(
                    'id' => $today_record['id']
                ));

        }

        $played_video = $this->db->from('stb_played_video')
            ->where(array(
            'uid' => $this->stb->id,
            'video_id' => $video_id
        ))
            ->get()
            ->all();

        if (empty($played_video)) {

            $this->db->insert('stb_played_video',
                array(
                    'uid' => $this->stb->id,
                    'video_id' => $video_id,
                    'playtime' => 'NOW()'
                ));

        } else {

            $this->db->update('stb_played_video',
                array('playtime' => 'NOW()'),
                array(
                    'uid' => $this->stb->id,
                    'video_id' => $video_id
                ));

        }

        if (Config::getSafe('enable_tariff_plans', false)){

            $user = User::getInstance(Stb::getInstance()->id);
            $package = $user->getPackageByVideoId($video['id']);

            if (!empty($package) && $package['service_type'] == 'single'){

                $video_rent_history = Mysql::getInstance()
                    ->from('video_rent_history')
                    ->where(array(
                        'video_id' => $video['id'],
                        'uid'      => Stb::getInstance()->id
                    ))
                    ->orderby('rent_date', 'DESC')
                    ->get()
                    ->first();

                if (!empty($video_rent_history)){
                    Mysql::getInstance()->update('video_rent_history', array('watched' => $video_rent_history['watched'] + 1), array('id' => $video_rent_history['id']));
                }
            }
        }

        return true;
    }

    public function setFav()
    {

        $new_id = intval($_REQUEST['video_id']);

        $favorites = $this->getFav();

        if ($favorites === null) {
            $favorites = array($new_id);
        } else {
            $favorites[] = $new_id;
        }

        return $this->saveFav($favorites, $this->stb->id);

        /*if ($fav_video === null){
            $this->db->insert('fav_vclub',
                               array(
                                    'uid'       => $this->stb->id,
                                    'fav_video' => serialize(array($new_id)),
                                    'addtime'   => 'NOW()'
                               ));
             return true;                      
        }
        
        if (!in_array($new_id, $fav_video)){
            
            $fav_video[] = $new_id;
            $fav_video_s = serialize($fav_video);
            
            $this->db->update('fav_vclub',
                               array(
                                    'fav_video' => $fav_video_s,
                                    'edittime'  => 'NOW()'),
                               array('uid' => $this->stb->id));
            
        }
        
        return true;*/
    }

    public function saveFav(array $fav_array, $uid)
    {

        if (empty($uid)) {
            return false;
        }

        $fav_videos_str = serialize($fav_array);

        $fav_video = $this->getFav($uid);

        //var_dump($this->stb->id, $fav_video);

        if ($fav_video === null) {
            return $this->db->insert('fav_vclub',
                array(
                    'uid' => $uid,
                    'fav_video' => $fav_videos_str,
                    'addtime' => 'NOW()'
                ))->insert_id();
        } else {
            return $this->db->update('fav_vclub',
                array(
                    'fav_video' => $fav_videos_str,
                    'edittime' => 'NOW()'),
                array('uid' => $uid))->result();
        }
    }

    public function getFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        return $this->getFavByUid($uid);

        /*$fav_video_arr = $this->db->from('fav_vclub')->where(array('uid' => $this->stb->id))->get()->first();

       if ($fav_video_arr === null){
           return null;
       }

       if (empty($fav_video_arr)){
           return array();
       }

       $fav_video = unserialize($fav_video_arr['fav_video']);

       if (!is_array($fav_video)){
           $fav_video = array();
       }

       return $fav_video;*/
    }

    public function getFavByUid($uid)
    {

        $uid = (int)$uid;

        $fav_video_arr = $this->db->from('fav_vclub')->where(array('uid' => $uid))->get()->first();

        if ($fav_video_arr === null) {
            return null;
        }

        if (empty($fav_video_arr)) {
            return array();
        }

        $fav_video = unserialize($fav_video_arr['fav_video']);

        if (!is_array($fav_video)) {
            $fav_video = array();
        }

        return $fav_video;
    }

    public function delFav()
    {

        $del_id = intval($_REQUEST['video_id']);

        $fav_video = $this->getFav();

        if (is_array($fav_video)) {

            if (in_array($del_id, $fav_video)) {

                unset($fav_video[array_search($del_id, $fav_video)]);

                $fav_video_s = serialize($fav_video);

                $this->db->update('fav_vclub',
                    array(
                        'fav_video' => $fav_video_s,
                        'edittime' => 'NOW()'
                    ),
                    array('uid' => $this->stb->id));

            }
        }

        return true;
    }

    public function setEnded()
    {
        $video_id = intval($_REQUEST['video_id']);

        $not_ended = $this->db->from('vclub_not_ended')
            ->where(array(
            'uid' => $this->stb->id,
            'video_id' => $video_id
        ))
            ->get()
            ->first();

        if (!empty($not_ended)){
            return Mysql::getInstance()->delete('vclub_not_ended', array('uid' => $this->stb->id, 'video_id' => $video_id))->result();
        }

        return true;
    }

    public function setNotEnded()
    {

        $video_id = intval($_REQUEST['video_id']);
        $series = intval($_REQUEST['series']);
        $end_time = intval($_REQUEST['end_time']);

        /*$not_ended = $this->db->getFirstData('vclub_not_ended',
        array(
             'uid' => $this->stb->id,
             'video_id' => $video_id
        ));*/
        $not_ended = $this->db->from('vclub_not_ended')
            ->where(array(
            'uid' => $this->stb->id,
            'video_id' => $video_id
        ))
            ->get()
            ->first();


        if (empty($not_ended)) {

            $this->db->insert('vclub_not_ended',
                array(
                    'uid' => $this->stb->id,
                    'video_id' => $video_id,
                    'series' => $series,
                    'end_time' => $end_time,
                    'added' => 'NOW()'
                ));

        } else {

            $this->db->update('vclub_not_ended',
                array(
                    'series' => $series,
                    'end_time' => $end_time,
                    'added' => 'NOW()'
                ),
                array(
                    'uid' => $this->stb->id,
                    'video_id' => $video_id
                ));

        }

        return true;
    }

    private function getData()
    {

        $offset = $this->page * self::max_page_items;

        $where = array();

        if (@$_REQUEST['hd']) {
            $where['hd'] = 1;
        } else {
            $where['hd<='] = 1;
        }

        if (!empty($_REQUEST['category']) && $_REQUEST['category'] == 'coming_soon'){
            $tasks_video = Mysql::getInstance()->from('moderator_tasks')->where(array('ended' => 0, 'media_type' => 2))->get()->all('media_id');
            $scheduled_video = Mysql::getInstance()->from('video_on_tasks')->get()->all('video_id');

            $ids = array_unique(array_merge($tasks_video, $scheduled_video));
        }elseif (@$_REQUEST['category'] && @$_REQUEST['category'] !== '*') {
            $where['category_id'] = intval($_REQUEST['category']);
        }

        if (!$this->stb->isModerator()) {
            if (!isset($ids)){
                $where['accessed'] = 1;
            }

            $where['status'] = 1;

            if ($this->stb->hd) {
                $where['disable_for_hd_devices'] = 0;
            }
        } else {
            $where['status>='] = 1;
        }

        if (@$_REQUEST['years'] && @$_REQUEST['years'] !== '*') {
            $where['year'] = $_REQUEST['years'];
        }

        if ((empty($_REQUEST['category']) || $_REQUEST['category'] == '*') && !Config::getSafe('show_adult_movies_in_common_list', true)){
            $where['category_id!='] = (int) Mysql::getInstance()->from('media_category')->where(array('category_alias' => 'adult'))->get()->first('id');
        }

        $like = array();

        if (@$_REQUEST['abc'] && @$_REQUEST['abc'] !== '*') {

            $letter = $_REQUEST['abc'];

            $like = array('video.name' => $letter . '%');
        }

        $where_genre = array();

        if (@$_REQUEST['genre'] && @$_REQUEST['genre'] !== '*' && $_REQUEST['category'] !== '*') {

            $genre = intval($_REQUEST['genre']);

            $where_genre['cat_genre_id_1'] = $genre;
            $where_genre['cat_genre_id_2'] = $genre;
            $where_genre['cat_genre_id_3'] = $genre;
            $where_genre['cat_genre_id_4'] = $genre;
        }

        if (@$_REQUEST['category'] == '*' && @$_REQUEST['genre'] !== '*') {

            $genre_title = $this->db->from('cat_genre')->where(array('id' => intval($_REQUEST['genre'])))->get()->first('title');

            $genres_ids = $this->db->from('cat_genre')->where(array('title' => $genre_title))->get()->all('id');
        }

        $search = array();

        if (!empty($_REQUEST['search'])) {

            $letters = $_REQUEST['search'];

            $search['video.name'] = '%' . $letters . '%';
            $search['o_name'] = '%' . $letters . '%';
            $search['actors'] = '%' . $letters . '%';
            $search['director'] = '%' . $letters . '%';
            $search['year'] = '%' . $letters . '%';
        }

        $data = $this->db
            ->select('video.*, (select group_concat(screenshots.id) from screenshots where media_id=video.id) as screenshots')
            ->from('video')
            ->where($where)
            ->where($where_genre, 'OR ');

        if (isset($ids)){
            $data->in('id', $ids);
        }

        if (!empty($genres_ids) && is_array($genres_ids)) {

            $data = $data->group_in(array(
                'cat_genre_id_1' => $genres_ids,
                'cat_genre_id_2' => $genres_ids,
                'cat_genre_id_3' => $genres_ids,
                'cat_genre_id_4' => $genres_ids,
            ), 'OR');
        }

        $data = $data->like($like)
            ->like($search, 'OR ')
        //->groupby('video.path')
            ->limit(self::max_page_items, $offset);

        return $data;
    }

    public function getOrderedList(){

        $movie_id   = isset($_REQUEST['movie_id']) ? (int) $_REQUEST['movie_id'] : 0;
        $season_id  = isset($_REQUEST['season_id']) ? (int) $_REQUEST['season_id'] : 0;
        $episode_id = isset($_REQUEST['episode_id']) ? (int) $_REQUEST['episode_id'] : 0;

        if (!$movie_id && !$season_id && !$episode_id){
            return $this->getMoviesList();
        }elseif ($movie_id && !$season_id && !$episode_id){
            $movie = Video::getById($movie_id);
            if ($movie['is_series']){
                return $this->getSeasonsList($movie_id);
            }else{
                return $this->getFilesList($movie_id);
            }
        }elseif ($movie_id && $season_id && !$episode_id){
            return $this->getEpisodesList($season_id);
        }elseif ($movie_id && $season_id && $episode_id){
            return $this->getFilesList($movie_id, $episode_id);
        }
    }

    public function getEpisodesList($season_id){

        $offset = $this->page * self::max_page_items;

        $episodes = Mysql::getInstance()
            ->select('video_season_series.*')
            ->from('video_season_series')
            ->join('video_series_files',
                array(
                    'video_season_series.id' => 'video_series_files.series_id',
                    'video_series_files.file_type' => '"video"'
                    ), null, 'LEFT')
            ->where(
                array(
                    'season_id' => $season_id,
                    'video_series_files.accessed' => 1
                ))
            ->groupby('video_season_series.id')
            ->orderby('series_number');

        $episodes->limit(self::max_page_items, $offset);

        $episodes_nums = clone $episodes;
        $episodes_nums = $episodes_nums->nolimit()->get()->all('series_number');

        //$episodes_nums = array_map('intval', $episodes_nums);

        $this->setResponseData($episodes);

        for ($i = 0; $i < count($this->response['data']); $i++) {

            $item = $this->response['data'][$i];

            $this->response['data'][$i]['name'] = _('Episode').' '.$item['series_number'];

            if ($item['series_name']){
                $this->response['data'][$i]['name'] .= '. ' . $item['series_name'];
            }elseif($item['series_original_name']){
                $this->response['data'][$i]['name'] .= '. ' . $item['series_original_name'];
            }

            $this->response['data'][$i]['is_episode'] = true;

            $this->response['data'][$i]['series'] = $episodes_nums;
        }

        if (!empty($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    public function getSeasonsList($movie_id){

        $offset = $this->page * self::max_page_items;

        $seasons = Mysql::getInstance()->from('video_season')
            ->where(array('video_id' => $movie_id))
            ->orderby('season_number');

        $seasons->limit(self::max_page_items, $offset);

        $this->setResponseData($seasons);

        for ($i = 0; $i < count($this->response['data']); $i++) {

            $item = $this->response['data'][$i];

            $this->response['data'][$i]['name'] = _('Season').' '.$item['season_number'];

            if ($item['season_name']){
                $this->response['data'][$i]['name'] .= '. ' . $item['season_name'];
            }elseif($item['season_original_name']){
                $this->response['data'][$i]['name'] .= '. ' . $item['season_original_name'];
            }

            $this->response['data'][$i]['is_season'] = true;
        }

        if (!empty($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    public function getFilesList($movie_id, $episode_id = 0){

        $offset = $this->page * self::max_page_items;

        $files = Mysql::getInstance()->from('video_series_files')
            ->where(
                array(
                    'video_id'  => $movie_id,
                    'file_type' => 'video',
                    'accessed'  => 1
                )
            );

        if ($episode_id){
            $files->where(array('series_id' => $episode_id));
        }

        $files->limit(self::max_page_items, $offset);

        $this->setResponseData($files);

        if (Config::get('enable_tariff_plans')){
            $user = User::getInstance($this->stb->id);
            $for_rent = $user->getServicesByType('video', 'single');

            if ($for_rent === null){
                $for_rent = array();
            }

            $rented_video = $user->getAllRentedVideo();

            if ($for_rent != 'all'){
                $for_rent = array_flip($for_rent);
            }else{
                $for_rent = array();
            }
        }else{
            $for_rent = array();
            $rented_video = array();
        }

        for ($i = 0; $i < count($this->response['data']); $i++) {

            $item = $this->response['data'][$i];

            $language_codes = unserialize($item['languages']);

            if (!is_array($language_codes)){
                $language_codes = array();
            }

            $languages = array_map(function($code){

                $language = Mysql::getInstance()->from('languages')->where(array('iso_639_code' => $code))->get()->first('name');

                if ($language){
                    $language = _($language);
                }else{
                    $language = $code;
                }

                return $language;

            }, $language_codes);

            $quality_map = Video::getQualityMap();

            if (isset($quality_map[$item['quality']])){
                $item['quality'] = _($quality_map[$item['quality']]['text_title']).' ('.$quality_map[$item['quality']]['num_title'].')';
            }

            if (array_key_exists($movie_id, $for_rent) || $for_rent == 'all'){
                $this->response['data'][$i]['for_rent'] = 1;

                if (array_key_exists($movie_id, $rented_video)){
                    $this->response['data'][$i]['rent_info'] = $rented_video[$movie_id];
                }else{
                    $this->response['data'][$i]['open'] = 0;
                }

            }else{
                $this->response['data'][$i]['for_rent'] = 0;
            }

            $this->response['data'][$i]['name'] = implode(', ', $languages) . ' / ' . $item['quality'];
            $this->response['data'][$i]['is_file'] = true;

            if (!empty($this->response['data'][$i]['url']) && $this->response['data'][$i]['protocol'] == 'custom' && $this->response['data'][$i]['for_rent'] == 0) {
                $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['url'];
            } else {
                $this->response['data'][$i]['cmd'] = '/media/file_' . $this->response['data'][$i]['id'] . '.mpg';
            }
        }

        if (!empty($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    public function getMoviesList()
    {
        $fav = $this->getFav();

        $ls = Stb::getInstance()->getParam('ls');

        if ($ls){
            $ids_on_ls = Mysql::getInstance()->from('users')->where(array('ls' => $ls))->get()->all('id');
        }else{
            $ids_on_ls = array($this->stb->id);
        }

        $user = User::getInstance($this->stb->id);
        $all_users_video_ids = $user->getServicesByType('video');

        $result = $this->getData();

        if (@$_REQUEST['sortby']) {
            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name' || $sortby == 'purchased') {
                $result = $result->orderby('video.name');
            } elseif ($sortby == 'added') {
                $result = $result->orderby('video.added', 'DESC');
            } elseif ($sortby == 'top') {
                $result->select('(count_first_0_5+count_second_0_5) as top')->orderby('top', 'DESC');
            } elseif ($sortby == 'last_ended') {
                $result = $result->orderby('vclub_not_ended.added', 'DESC');
            } elseif ($sortby == 'rating') {
                $result = $result->orderby('video.rating_kinopoisk', 'DESC');
            }

        } else {
            $result = $result->orderby('video.name');
        }

        if (!empty($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'purchased' && Config::get('enable_tariff_plans')) {
            $rented_video = $user->getAllRentedVideo();
            $rented_video_ids = array_keys($rented_video);
            $result = $result->in('video.id', $rented_video_ids);
        }

        if (@$_REQUEST['fav']) {
            $result = $result->in('video.id', $fav);
        }

        if (@$_REQUEST['hd']) {
            $result = $result->where(array('hd' => 1));
        }

        if (Config::get('enable_tariff_plans') && $all_users_video_ids != 'all'){
            $result = $result->in('video.id', $all_users_video_ids);
        }

        if (@$_REQUEST['not_ended']) {
            $result = $result->from('vclub_not_ended')
                ->select('vclub_not_ended.series as cur_series, vclub_not_ended.end_time as position')
                ->where('video.id=vclub_not_ended.video_id', 'AND ', null, -1)
                /*->where(array('vclub_not_ended.uid' => $this->stb->id));*/
                ->in('vclub_not_ended.uid',  $ids_on_ls);
        }

        $this->setResponseData($result);

        return $this->getResponse('prepareMoviesList');
    }

    public function prepareMoviesList()
    {

        $fav = $this->getFav();

        $not_ended = Video::getNotEnded();

        if (Config::get('enable_tariff_plans')){
            $user = User::getInstance($this->stb->id);
            $for_rent = $user->getServicesByType('video', 'single');

            if ($for_rent === null){
                $for_rent = array();
            }

            $rented_video = $user->getAllRentedVideo();

            if ($for_rent != 'all'){
                $for_rent = array_flip($for_rent);
            }else{
                $for_rent = array();
            }
        }else{
            $for_rent = array();
            $rented_video = array();
        }

        for ($i = 0; $i < count($this->response['data']); $i++) {

            $this->response['data'][$i]['is_movie'] = true;

            /// TRANSLATORS: "%2$s" - original video name, "%1$s" - video name.
            $this->response['data'][$i]['name'] = sprintf(_('video_name_format'), $this->response['data'][$i]['name'], $this->response['data'][$i]['o_name']);

            unset($this->response['data'][$i]['hd']);

            if ($this->response['data'][$i]['censored']) {
                $this->response['data'][$i]['lock'] = 1;
            } else {
                $this->response['data'][$i]['lock'] = 0;
            }

            if ($fav !== null && in_array($this->response['data'][$i]['id'], $fav)) {
                $this->response['data'][$i]['fav'] = 1;
            } else {
                $this->response['data'][$i]['fav'] = 0;
            }

            if (array_key_exists($this->response['data'][$i]['id'], $for_rent) || $for_rent == 'all'){
                $this->response['data'][$i]['for_rent'] = 1;

                if (array_key_exists($this->response['data'][$i]['id'], $rented_video)){
                    $this->response['data'][$i]['rent_info'] = $rented_video[$this->response['data'][$i]['id']];
                }else{
                    $this->response['data'][$i]['open'] = 0;
                }

            }else{
                $this->response['data'][$i]['for_rent'] = 0;
            }

            $this->response['data'][$i]['series'] = unserialize($this->response['data'][$i]['series']);

            if (!empty($this->response['data'][$i]['series'])) {
                $this->response['data'][$i]['position'] = 0;
            }

            if (!empty($not_ended[$this->response['data'][$i]['id']]) && !empty($this->response['data'][$i]['series'])){
                $this->response['data'][$i]['cur_series'] = $not_ended[$this->response['data'][$i]['id']]['series'];
            }

            if ($this->response['data'][$i]['screenshots'] === null) {
                $this->response['data'][$i]['screenshots'] = '0';
            }

            $screenshots = explode(",", $this->response['data'][$i]['screenshots']);

            $this->response['data'][$i]['screenshot_uri'] = $this->getImgUri($screenshots[0]);

            $this->response['data'][$i]['genres_str'] = $this->getGenresStrByItem($this->response['data'][$i]);

            if (!empty($this->response['data'][$i]['rtsp_url']) && $this->response['data'][$i]['for_rent'] == 0) {
                if (!empty($this->response['data'][$i]['series'])) {
                    $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['rtsp_url'] = $this->changeSeriesOnCustomURL( $this->response['data'][$i]['rtsp_url'], $this->response['data'][$i]['cur_series']);
                }else{
                    $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['rtsp_url'];
                }
            } else {
                $this->response['data'][$i]['cmd'] = '/media/' . $this->response['data'][$i]['id'] . '.mpg';
            }

            if (@$_REQUEST['sortby'] && @$_REQUEST['sortby'] == 'added') {
                $this->response['data'][$i] = array_merge($this->response['data'][$i], $this->getAddedArr($this->response['data'][$i]['added']));
            }

            if (Config::getSafe('enable_video_low_quality_option', false)){
                $this->response['data'][$i]['low_quality'] = intval($this->response['data'][$i]['low_quality']);
            }else{
                $this->response['data'][$i]['low_quality'] = 0;
            }

            $this->response['data'][$i]['has_files'] = (int) Mysql::getInstance()
                ->from('video_series_files')
                ->where(array(
                    'video_id' => $this->response['data'][$i]['id']
                ))
                ->count()
                ->get()
                ->counter();
        }

        if (!empty($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    private function getAddedArr($datetime)
    {

        $added_time = strtotime($datetime);

        $added_arr = array(
            //'str'       => '',
            //'bg_level'  => ''
        );

        $this_mm = date("m");
        $this_dd = date("d");
        $this_yy = date("Y");

        if ($added_time > mktime(0, 0, 0, $this_mm, $this_dd, $this_yy)) {
            //$added_arr['today'] = System::word('vod_today');
            $added_arr['today'] = _('today');
        } elseif ($added_time > mktime(0, 0, 0, $this_mm, $this_dd - 1, $this_yy)) {
            //$added_arr['yesterday'] = System::word('vod_yesterday');
            $added_arr['yesterday'] = _('yesterday');
        } elseif ($added_time > mktime(0, 0, 0, $this_mm, $this_dd - 7, $this_yy)) {
            //$added_arr['week_and_more'] = System::word('vod_last_week');
            $added_arr['week_and_more'] = _('last week');
        } else {
            $added_arr['week_and_more'] = $this->months[date("n", $added_time) - 1] . ' ' . date("Y", $added_time);
        }

        return $added_arr;
    }

    public function getCategories()
    {

        if (!Config::getSafe('show_empty_vclub_category', true)) {

            $user = User::getInstance($this->stb->id);
            $all_users_video_ids = $user->getServicesByType('video');

            $user_categories = Mysql::getInstance()->from('video')->select('category_id')->groupby('category_id');

            if (!$this->stb->isModerator()) {
                $user_categories->where(array(
                    'accessed' => 1,
                    'status'   => 1,
                ));

                if ($this->stb->hd) {
                    $user_categories->where(array(
                        'disable_for_hd_devices' => 0,
                    ));
                }
            } else {
                $user_categories->where(array(
                    'status>=' => 1,
                ));
            }

            if (Config::get('enable_tariff_plans') && $all_users_video_ids != 'all') {
                $user_categories->in('video.id', $all_users_video_ids);
            }

            $user_categories = $user_categories->get()->all('category_id');
        }

        $categories = $this->db
            ->select('id, category_name as title, category_alias as alias, censored')
            ->from("media_category");

        if (!Config::getSafe('show_empty_vclub_category', true) && isset($user_categories)){
            $categories->in('id', $user_categories);
        }

        $categories = $categories->get()->all();

        array_unshift($categories, array('id' => '*', 'title' => $this->all_title, 'alias' => '*'));

        $categories = array_map(function($item)
        {
            $item['title']    = _($item['title']);
            $item['censored'] = (int) $item['censored'];
            return $item;
        }, $categories);


        if (Config::getSafe('enable_coming_soon_section', false)){
            $categories[] = array(
                'id'       => 'coming_soon',
                'title'    => _('coming soon'),
                'alias'    => 'coming_soon',
                'censored' => 0
            );
        }

        return $categories;
    }

    public function getGenresByCategoryAlias($cat_alias = '')
    {

        if (!$cat_alias) {
            $cat_alias = @$_REQUEST['cat_alias'];
        }

        $where = array();

        if ($cat_alias != '*') {
            $where['category_alias'] = $cat_alias;
        }

        $genres = $this->db
            ->select('id, title')
            ->from("cat_genre")
            ->where($where)
            ->groupby('title')
            ->orderby('title')
            ->get()
            ->all();

        array_unshift($genres, array('id' => '*', 'title' => '*'));

        $genres = array_map(function($item)
        {
            $item['title'] = _($item['title']);
            return $item;
        }, $genres);

        return $genres;
    }

    public function getYears()
    {

        $where = array('year>' => '1900');

        if (@$_REQUEST['category'] && @$_REQUEST['category'] !== '*') {
            $where['category_id'] = $_REQUEST['category'];
        }

        $years = $this->db
            ->select('year as id, year as title')
            ->from('video')
            ->where($where)
            ->groupby('year')
            ->orderby('year')
            ->get()
            ->all();

        array_unshift($years, array('id' => '*', 'title' => '*'));

        return $years;
    }

    public function getAbc()
    {

        $abc = array();

        foreach ($this->abc as $item) {
            $abc[] = array(
                'id' => $item,
                'title' => $item
            );
        }

        return $abc;
    }

    public function getGenresStrByItem($item)
    {

        return implode(', ', array_map(function($item)
        {
            $item = _($item);
            $fc = mb_strtoupper(mb_substr($item, 0, 1, 'UTF-8'), 'UTF-8');
            $item = $fc.mb_substr($item, 1, mb_strlen($item), 'UTF-8');
            return $item;
        }, $this->db->from('cat_genre')->in('id', array($item['cat_genre_id_1'], $item['cat_genre_id_2'], $item['cat_genre_id_3'], $item['cat_genre_id_4']))->get()->all('title')));
    }

    public function setClaim()
    {

        return $this->setClaimGlobal('vclub');
    }

    public function changeSeriesOnCustomURL($url = '', $series = 1){
        $tmp_arr = array();
        if ($series < 1) {
            $series = 1;
        }
        if (preg_match("/(s\d+e)(\d+).*$/i", $url, $tmp_arr)){
            $search_str = $tmp_arr[1].$tmp_arr[2];
            $replace_str = $tmp_arr[1].str_pad($series, 2, '0',  STR_PAD_LEFT );
            $url = str_replace($search_str, $replace_str, $url);
        }
        return $url;
    }
}

class VodLinkException extends Exception
{
}

?>