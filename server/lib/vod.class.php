<?php
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

        preg_match("/\/media\/(\d+).mpg(.*)/", $_REQUEST['cmd'], $tmp_arr);

        $media_id = $tmp_arr[1];
        $params = $tmp_arr[2];

        $forced_storage = $_REQUEST['forced_storage'];
        $disable_ad     = $_REQUEST['disable_ad'];

        $link = $this->getLinkByVideoId($media_id, intval($_REQUEST['series']), $forced_storage);

        $link['cmd'] = $link['cmd'] . $params;

        if (Config::get('enable_tariff_plans')){
            $user = User::getInstance(Stb::getInstance()->id);

            $options = $user->getServicesByType('option');

            if ($options && array_search('disable_vclub_ad', $options) !== false){
                $disable_ad = true;
            }
        }

        $moderator_w_disables_ad = Mysql::getInstance()
            ->from('moderators')
            ->where(array(
                'status'           => 1,
                'mac'              => Stb::getInstance()->mac,
                'disable_vclub_ad' => 1
            ))
            ->get()
            ->first();

        if (!empty($moderator_w_disables_ad)){
            $disable_ad = true;
        }

        $vclub_ad = new VclubAdvertising();

        if (!$disable_ad && empty($link['error'])){

            $video = Video::getById($media_id);

            $picked_ad = $vclub_ad->getOneWeightedRandom($video['category_id']);

            if (!empty($picked_ad)){
                $link = array(
                    array(
                        'id'    => 0,
                        'ad_id' => $picked_ad['id'],
                        'ad_must_watch' => $picked_ad['must_watch'],
                        'type'  => 'ad',
                        'cmd'   => $picked_ad['url']
                    ),
                    $link
                );
            }
        }

        var_dump($link);

        return $link;
    }

    public function getLinkByVideoId($video_id, $series = 0, $forced_storage = "")
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

            if (!empty($video['rtsp_url'])){
                return array(
                    'id'  => $video_id,
                    'cmd' => $video['rtsp_url']
                );
            }
        }

        $master = new VideoMaster();

        try {
            $res = $master->play($video_id, intval($series), true, $forced_storage);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }

        return $res;
    }

    public function getUrlByVideoId($video_id, $series = 0, $forced_storage = "")
    {

        $video = Video::getById($video_id);

        if (empty($video)) {
            throw new Exception("Video not found");
        }

        if (!empty($video['rtsp_url'])) {
            return $video['rtsp_url'];
        }

        $link = $this->getLinkByVideoId($video_id, $series, $forced_storage);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
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

        //$where = array('status' => 1);
        $where = array();

        if (@$_REQUEST['hd']) {
            $where['hd'] = 1;
        } else {
            $where['hd<='] = 1;
        }

        /*if (!$this->stb->hd && Config::get('vclub_mag100_filter')){
            $where['for_sd_stb'] = 1;
        }*/

        if (!$this->stb->isModerator()) {
            $where['accessed'] = 1;

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

        if (!empty($_REQUEST['category']) && $_REQUEST['category'] == 'coming_soon'){
            $ids = Mysql::getInstance()->from('moderator_tasks')->where(array('ended' => 0, 'media_type' => 2))->get()->all('media_id');
        }elseif (@$_REQUEST['category'] && @$_REQUEST['category'] !== '*') {
            $where['category_id'] = intval($_REQUEST['category']);
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

    public function getOrderedList()
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

        return $this->getResponse('prepareData');
    }

    public function prepareData()
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

            if ($this->response['data'][$i]['hd']) {
                $this->response['data'][$i]['sd'] = 0;
            } else {
                $this->response['data'][$i]['sd'] = 1;
            }

            /// TRANSLATORS: "%2$s" - original video name, "%1$s" - video name.
            $this->response['data'][$i]['name'] = sprintf(_('video_name_format'), $this->response['data'][$i]['name'], $this->response['data'][$i]['o_name']);

            $this->response['data'][$i]['hd'] = intval($this->response['data'][$i]['hd']);

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

            //$this->response['data'][$i]['screenshot_uri'] = $this->getImgUri($this->response['data'][$i]['screenshot_id']);

            //var_dump('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', $this->response['data'][$i]['screenshots']);

            if ($this->response['data'][$i]['screenshots'] === null) {
                $this->response['data'][$i]['screenshots'] = '0';
            }

            $screenshots = explode(",", $this->response['data'][$i]['screenshots']);

            $this->response['data'][$i]['screenshot_uri'] = $this->getImgUri($screenshots[0]);

            $this->response['data'][$i]['genres_str'] = $this->getGenresStrByItem($this->response['data'][$i]);

            if (!empty($this->response['data'][$i]['rtsp_url']) && $this->response['data'][$i]['for_rent'] == 0) {
                $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['rtsp_url'];
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

        $categories = $this->db
            ->select('id, category_name as title, category_alias as alias')
            ->from("media_category")
            ->get()
            ->all();

        array_unshift($categories, array('id' => '*', 'title' => $this->all_title, 'alias' => '*'));

        $categories = array_map(function($item)
        {
            $item['title'] = _($item['title']);
            return $item;
        }, $categories);


        if (Config::getSafe('enable_coming_soon_section', false)){
            $categories[] = array(
                'id'    => 'coming_soon',
                'title' => _('coming soon'),
                'alias' => 'coming_soon'
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
            return _($item);
        }, $this->db->from('cat_genre')->in('id', array($item['cat_genre_id_1'], $item['cat_genre_id_2'], $item['cat_genre_id_3'], $item['cat_genre_id_4']))->get()->all('title')));
    }

    public function setClaim()
    {

        return $this->setClaimGlobal('vclub');
    }
}

class VodLinkException extends Exception
{
}

?>