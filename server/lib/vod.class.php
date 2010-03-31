<?php
/**
 * Main VOD class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Vod extends AjaxResponse
{
    public static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){
        parent::__construct();
    }
    
    public function createLink(){
        
        preg_match("/auto \/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);
            
        $media_id = $tmp_arr[1];
        
        $master = new VideoMaster();
        
        try {
            $res = $master->play($media_id, intval($_REQUEST['series']));
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }
        
        var_dump($res);
        
        return $res;
    }
    
    public function getMediaCats(){
        
        return $this->db->get('media_category')->all();
        
    }
    
    public function setVote(){
        
        if ($_REQUEST['vote'] == 'good'){
            $good = 1;
            $bad = 0;
        }else{
            $good = 0;
            $bad = 1;
        }
        
        $type = $_REQUEST['type'];
        
        $this->db->insert('vclub_vote',
                           array(
                                'media_id'  => intval($_REQUEST['media_id']),
                                'uid'       => $this->stb->id,
                                'vote_type' => $type,
                                'good'      => $good,
                                'bad'       => $bad,
                                'added'     => 'NOW()'
                           ));
        
        //$video = $this->db->getFirstData('video', array('id' => intval($_REQUEST['media_id'])));
        $video = $this->db->from('video')->where(array('id' => intval($_REQUEST['media_id'])))->get()->first();
        
        $this->db->update('video',
                           array(
                                'vote_'.$type.'_good' => $video['vote_'.$type.'_good'] + $good,
                                'vote_'.$type.'_bad'  => $video['vote_'.$type.'_bad'] + $bad,
                           ),
                           array('id' => intval($_REQUEST['media_id'])));
        
        return true;
    }
    
    public function setPlayed(){
        
        $video_id   = intval($_REQUEST['video_id']);
        $storage_id = intval($_REQUEST['storage_id']);
        
        if ($day <= date("j")){
            $field_name = 'count_first_0_5';
        }else{
            $field_name = 'count_second_0_5';
        }
        
        //$video = $this->db->getFirstData('video', array('id' => $video_id));
        $video = $this->db->from('video')->where(array('id' => $video_id))->get()->first();
        
        $this->db->update('video',
                           array(
                                $field_name   => $video[$field_name] + 1,
                                'count'       => $video['count'] + 1,
                                'last_played' => 'NOW()'
                           ),
                           array('id' => $video_id));
        
        $this->db->insert('played_video',
                           array(
                                'video_id' => $video_id,
                                'uid'      => $this->stb->id,
                                'storage'  => $storage_id,
                                'playtime' => 'NOW()'
                           ));
        
        $this->db->update('users',
                           array('time_last_play_video' => 'NOW()'),
                           array('id' => $this->stb->id));
        
        //$today_record = $this->db->getFirstData('daily_played_video', array('date' => 'CURDATE()'));
        $today_record = $this->db->from('daily_played_video')->where(array('date' => date('Y-m-d')))->get()->first();
        
        if (empty($today_record)){
            
            $this->db->insert('daily_played_video',
                               array(
                                    'count' => 1,
                                    'date'  => date('Y-m-d')
                               ));
            
        }else{
            
            $this->db->update('daily_played_video',
                               array(
                                    'count' => $today_record['count'] + 1,
                                    'date'  => date('Y-m-d')
                               ));
            
        }
        
        /*$played_video = $this->db->getData('stb_played_video',
                            array(
                                'uid' => $this->stb->id,
                                'video_id' => $video_id
                            ));*/
        $played_video = $this->db->from('stb_played_video')
                                 ->where(array(
                                    'uid' => $this->stb->id,
                                    'video_id' => $video_id
                                 ))
                                 ->get()
                                 ->all();
        
        if (empty($played_video)){
            
            $this->db->insert('stb_played_video',
                               array(
                                    'uid'      => $this->stb->id,
                                    'video_id' => $video_id,
                                    'playtime' => 'NOW()'
                               ));
            
        }else{
            
            $this->db->update('stb_played_video',
                               array('playtime' => 'NOW()'),
                               array(
                                    'uid'      => $this->stb->id,
                                    'video_id' => $video_id
                               ));
            
        }
        
        return true;
    }
    
    public function setFav(){
        
        $new_id = intval($_REQUEST['video_id']);

        $fav_video = $this->getFav();
        
        if (!is_array($fav_video)){
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
        
        return true;
    }
    
    public function getFav(){
        
        //$fav_video_arr = $this->db->getFirstData('fav_vclub', array('uid' => $this->stb->id));
        $fav_video_arr = $this->db->from('fav_vclub')->where(array('uid' => $this->stb->id))->get()->first();
        
        if (empty($fav_video_arr)){
            return array();
        }
        
        $fav_video = unserialize($fav_video_arr['fav_video']);
        
        if (!is_array($fav_video)){
            $fav_video = array();
        }
        
        return $fav_video;
    }
    
    public function delFav(){
        
        $del_id = intval($_REQUEST['video_id']);
        
        $fav_video = $this->getFav();
        
        if (is_array($fav_video)){

            if (in_array($del_id, $fav_video)){
                
                unset($fav_video[array_search($del_id, $fav_video)]);
                
                $fav_video_s = serialize($fav_video);
                
                $this->db->update('fav_vclub',
                                   array(
                                        'fav_video' => $fav_video_s,
                                        'edittime'  => 'NOW()'
                                   ),
                                   array('uid' => $this->stb->id));
                
            }
        }
        
        return true;
    }
    
    public function setNotEnded(){
        
        $video_id   = intval($_REQUEST['video_id']);
        $series     = intval($_REQUEST['series']);
        $end_time   = intval($_REQUEST['end_time']);
        
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
        
        
        
        if (empty($not_ended)){

            $this->db->insert('vclub_not_ended',
                               array(
                                    'uid'      => $this->stb->id,
                                    'video_id' => $video_id,
                                    'series'   => $series,
                                    'end_time' => $end_time,
                                    'added'    => 'NOW()'
                               ));
            
        }else{
            
            $this->db->update('vclub_not_ended',
                               array(
                                    'series'   => $series,
                                    'end_time' => $end_time,
                                    'added'    => 'NOW()'
                               ),
                               array(
                                    'uid'      => $this->stb->id,
                                    'video_id' => $video_id
                               ));
            
        }
        
        return true;
    }
    
    private function getData(){
        
        $offset = $this->page * MAX_PAGE_ITEMS;
        
        $where = array('status' => 1);
        
        if (@$_REQUEST['hd']){
            $where['hd'] = 1;
        }else{
            $where['hd<='] = 1;
        }
        
        if (!$this->stb->isModerator()){
            $where['accessed'] = 1;
            
            if ($this->stb->hd){
                $where['disable_for_hd_devices'] = 0;
            }
        }
        
        if (@$_REQUEST['years'] && @$_REQUEST['years'] !== '*'){
            $where['year'] = $_REQUEST['years'];
        }
        
        if (@$_REQUEST['category'] && @$_REQUEST['category'] !== '*'){
            $where['category_id'] = intval($_REQUEST['category']);
        }
        
        $like = array();
        
        if (@$_REQUEST['abc'] && @$_REQUEST['abc'] !== '*'){
            
            $letter = $_REQUEST['abc'];
            
            $like = array('name' => $letter.'%');
        }
        
        $where_genre = array();
        
        if (@$_REQUEST['genre'] && @$_REQUEST['genre'] !== '*'){
            
            $genre = intval($_REQUEST['genre']);
            
            $where_genre['cat_genre_id_1'] = $genre;
            $where_genre['cat_genre_id_2'] = $genre;
            $where_genre['cat_genre_id_3'] = $genre;
            $where_genre['cat_genre_id_4'] = $genre;
        }
        
        $search = array();
        
        if (@$_REQUEST['search']){
            
            $letters = $_REQUEST['search'];
            
            $search['name']     = '%'.$letters.'%';
            $search['o_name']   = '%'.$letters.'%';
            $search['actors']   = '%'.$letters.'%';
            $search['director'] = '%'.$letters.'%';
            $search['year']     = '%'.$letters.'%';
        }
        
        return $this->db
                        ->select('video.*, screenshots.id as screenshot_id')
                        ->from('video')
                        ->join('screenshots', 'video.id', 'screenshots.media_id', 'LEFT')
                        ->where($where)
                        ->where($where_genre, 'OR ')
                        ->like($like)
                        ->like($search, 'OR ')
                        ->limit(MAX_PAGE_ITEMS, $offset);
    }
    
    public function getOrderedList(){
        $fav = $this->getFav();
        
        $result = $this->getData();
        
        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];
            
            if ($sortby == 'name'){
                $result = $result->orderby('name');
            }elseif ($sortby == 'added'){
                $result = $result->orderby('added', 'DESC');
            }elseif ($sortby == 'top'){
                $result->select('*, (count_first_0_5+count_second_0_5) as top')->orderby('top', 'DESC');
            }
            
        }else{
            $result = $result->orderby('name');
        }
        
        if (@$_REQUEST['fav']){
            //var_dump($fav);
            //$fav = implode(",", $fav);
            //var_dump($fav);
            $result = $result->in('id', $fav);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        $fav = $this->getFav();
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            if ($this->response['data'][$i]['hd']){
                $this->response['data'][$i]['sd'] = 0;
            }else{
                $this->response['data'][$i]['sd'] = 1;
            }
            
            if ($this->response['data'][$i]['censored']){
                $this->response['data'][$i]['lock'] = 1;
            }else{
                $this->response['data'][$i]['lock'] = 0;
            }
            
            if (in_array($this->response['data'][$i]['id'], $fav)){
                $this->response['data'][$i]['fav'] = 1;
            }else{
                $this->response['data'][$i]['fav'] = 0;
            }
            
            $this->response['data'][$i]['screenshot_uri'] = $this->getImgUri($this->response['data'][$i]['screenshot_id']);
            
            $this->response['data'][$i]['genres_str'] = $this->getGenresStrByItem($this->response['data'][$i]);
        }
        
        return $this->response;
    }
    
    public function getCategories(){
        
        $categories = $this->db
                        ->select('id, category_name as title, category_alias as alias')
                        ->from("media_category")
                        ->get()
                        ->all();
                        
        array_unshift($categories, array('id' => '*', 'title' => 'Все', 'alias' => '*'));
        
        return $categories;
    }
    
    public function getGenresByCategoryAlias($cat_alias = ''){
        
        if (!$cat_alias){
            $cat_alias = @$_REQUEST['cat_alias'];
        }
        
        $where = array();
        
        if ($cat_alias != '*'){
            $where['category_alias'] = $cat_alias;
        }
        
        $genres = $this->db
                        ->select('id, title')
                        ->from("cat_genre")
                        ->where($where)
                        ->get()
                        ->all();
                        
        array_unshift($genres, array('id' => '*', 'title' => '*'));
        
        return $genres;
    }
    
    public function getYears(){
        
        $where = array('year>' => '1900');
        
        if (@$_REQUEST['category'] && @$_REQUEST['category'] !== '*'){
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
    
    public function getAbc(){
        
        $abc = array();
        
        foreach ($this->abc as $item){
            $abc[] = array(
                        'id'    => $item,
                        'title' => $item
                     );
        }
        
        return $abc;
    }
    
    public function getGenresStrByItem($item){
        
        return implode(', ', $this->db->from('cat_genre')->in('id', array($item['cat_genre_id_1'], $item['cat_genre_id_2'], $item['cat_genre_id_3'], $item['cat_genre_id_4']))->get()->all('title'));
        
    }
}

?>