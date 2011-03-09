<?php
/**
 * Main ITV class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Itv extends AjaxResponse
{
    public static $instance = NULL;
    
    private $all_user_channels_ids;
    
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

        $cmd = '';

        preg_match("/\/ch\/(\d+)$/", $_REQUEST['cmd'], $tmp_arr);

        if (empty($tmp_arr)){
            $error = 'nothing_to_play';
        }

        $media_id = intval($tmp_arr[1]);

        $channel = self::getChannelById($media_id);

        var_dump($media_id, $channel);

        if (empty($channel)){
            $error = 'nothing_to_play';
        }

        preg_match("/http:\/\/(.*?)\/(.*)$/", $channel['cmd'], $tmp_url_arr);

        if (empty($tmp_url_arr)){
            $error = 'nothing_to_play';
        }else{
            $redirect_host = $tmp_url_arr[1];
            $redirect_uri  = $tmp_url_arr[2];
            $redirect_url = '/get/'.$redirect_host.'/'.$redirect_uri;

            $link_result = $this->createTemporaryLink($redirect_url);

            var_dump($redirect_url, $link_result);

            if (!$link_result){
                $error = 'link_fault';
            }else{
                $cmd = 'ffrt http://'.STREAM_PROXY.'/ch/'.$link_result;
            }
        }

        $res = array(
            'id'    => $media_id,
            'cmd'   => empty($error) ? $cmd : '',
            'error' => empty($error) ? '' : $error
        );

        var_dump($res);

        return $res;
    }

    private function createTemporaryLink($url){

        $key = md5($url.time());

        $cache = Cache::getInstance();

        $result = $cache->set($key, $url, 0, 15);

        if ($result){
            return $key;
        }else{
            return $result;
        }
    }

    public static function checkTemporaryLink($key){

        return Cache::getInstance()->get($key);
    }
    
    public function setPlayed(){
        $itv_id = intval($_REQUEST['itv_id']);
        
        $this->db->insert('played_itv', array(
                                            'itv_id'   => $itv_id,
                                            'uid'      => $this->stb->id,
                                            'playtime' => 'NOW()'
                                        ));
        
        $this->db->update('users',
                          array('time_last_play_tv' => 'NOW()'),
                          array('id' => $this->stb->id));
        
        $this->setLastId($itv_id);
        
        return true;
    }
    
    public function getLastId(){
        
        $last_id_arr = $this->db->from('last_id')
                                ->where(array('ident' => $this->stb->mac))
                                ->get()
                                ->first();
        
        if(!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            return $last_id_arr['last_id'];
        }
        
        return 0;
    }
    
    public function setLastId($id = 0){
        
        if (!$id){
            $id = intval($_REQUEST['id']);
        }
        
        $last_id_arr = $this->db->from('last_id')
                                ->where(array('ident' => $this->stb->mac))
                                ->get()
                                ->first();
        
        if (!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            $this->db->update('last_id', array('last_id' => $id), array('ident' => $this->stb->mac));
        }else{
            $this->db->insert('last_id', array('last_id' => $id, 'ident' => $this->stb->mac));
        }
        
        return true;
    }
    
    public function setFav($uid = null){
        
        if (!$uid){
            $uid = $this->stb->id;
        }
        
        $fav_ch = @$_REQUEST['fav_ch'];
        
        if (empty($fav_ch)){
            $fav_ch = array();
        }
        
        if (is_array($fav_ch)){
            $fav_ch_str = base64_encode(serialize($fav_ch));
            
            $fav_itv_arr = $this->db->from('fav_itv')->where(array('uid' => $uid))->get()->first();
            
            if (empty($fav_itv_arr)){
                $this->db->insert('fav_itv',
                                   array(
                                        'uid'     => $uid,
                                        'fav_ch'  => $fav_ch_str,
                                        'addtime' => 'NOW()'
                                   ));
            }else{
                $this->db->update('fav_itv',
                                   array(
                                        'fav_ch'  => $fav_ch_str,
                                        'addtime' => 'NOW()'
                                   ),
                                   array('uid' => $uid));
            }
        }
        
        return true;
    }
    
    public function getFav($uid = null){
        
        if (!$uid){
            $uid = $this->stb->id;
        }
        
        $fav_itv_ids_arr = $this->db->from('fav_itv')->where(array('uid' => $uid))->get()->first();
        
        if (!empty($fav_itv_ids_arr)){
            $fav_ch = unserialize(base64_decode($fav_itv_ids_arr['fav_ch']));
            
            if (is_array($fav_ch)){
                return $fav_ch;
            }
        }
        
        return array();
    }
    
    public function getListByNumber(){
        
        $page = intval($_REQUEST['p']);
        
        $this->db->from('itv')
                 ->where(array('status' => 1));
    }
    
    public function getChannels($include_censored = false){
        
        $query = $this->db->from('itv');
        
        if (!$include_censored){
            $query->where(array('censored' => 0));
        }
                        
        if (!$this->stb->isModerator()){
            $query->where(array('status' => 1));
        }
        
        return $query;
    }
    
    public function getAllChannels(){
        
        $result = $this->getChannels()
                    ->orderby('number');
                    //->get()
                    //->all();
                    
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
        
    }
    
    public function getAllFavChannels(){
        
        $fav_ids = $this->getFav();
        
        $fav_str = implode(",", $fav_ids);
        
        if (empty($fav_str)){
            $fav_str = 'null';
        }
        
        $fav_channels = $this->getChannels()
                                            ->in('id' , $fav_ids)
                                            ->orderby('field(id,'.$fav_str.')');
                                            //->get()
                                            //->all();
        
        /*for ($i=0; $i<count($fav_channels); $i++){
            $fav_channels[$i]['number'] = $i+1;
        }
        
        return $fav_channels;   */
        
        $this->setResponseData($fav_channels);
        
        return $this->getResponse('prepareData');
    }
    
    public function getFavIds(){
        
        $fav = $this->getFav();
        $fav_str = implode(",", $fav);
        
        if (empty($fav_str)){
            $fav_str = 'null';
        }
        
        $fav_ids = $this->db
                            ->from('itv')
                            ->in('id', $fav)
                            ->where(array('status' => 1))
                            ->orderby('field(id,'.$fav_str.')')
                            ->get()
                            ->all('id');
                            
        return $fav_ids;
    }
    
    public function getGenres(){
        
        $genres = $this->db->from('tv_genre')->get()->all();
        
        array_unshift($genres, array('id' => '*', 'title' => $this->all_title));
        
        return $genres;
    }
    
    private function getOffset($where = array()){
        
        if (!$this->load_last_page){
            return $this->page * MAX_PAGE_ITEMS;
        }
        
        $fav = $this->getFav();
        
        if (!empty($_REQUEST['from_ch_id']) && intval($_REQUEST['from_ch_id'])>0){
            $last_id = intval($_REQUEST['from_ch_id']);
        }else{
            $last_id = $this->getLastId();
        }
        
        $tv_number = $this->db->from('itv')->where(array('id' => $last_id))->get()->first('number');
        
        $ch_idx = 0;
        
        if(@$_REQUEST['fav']){
            
            if (in_array($last_id, $fav)){
                
                $ch_tmp_idx = array_search($last_id, $fav);
                
                if ($ch_tmp_idx >= 0){
                    $fav = array_slice($fav, 0, $ch_tmp_idx+1);
                }
            }
            
            $ch_idx = $this->db->from('itv')->where($where)->in('itv.id', $fav)->get()->count();
        }else{

            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name'){
                //$ch_idx = $this->db->from('itv')->where($where)->where(array('number<=' => $tv_number))->get()->count();
                $chs = $this->db->from('itv')->where($where)->orderby('name')->get()->all();

                foreach ($chs as $ch){
                    $ch_idx++;
                    if ($ch['id'] == $last_id){
                        break;
                    }
                }

            }else{
                $ch_idx = $this->db->from('itv')->where($where)->where(array('number<=' => $tv_number))->get()->count();
            }
        }
        
        if ($ch_idx > 0){
            $this->cur_page = ceil($ch_idx/MAX_PAGE_ITEMS);
            $this->page = $this->cur_page-1;
            $this->selected_item = $ch_idx - ($this->cur_page-1)*MAX_PAGE_ITEMS;
        }
        
        $page_offset = ($this->cur_page-1)*MAX_PAGE_ITEMS;
        
        if ($page_offset < 0){
            $page_offset = 0;
        }
        
        return $page_offset;
    }
    
    private function getData(){
        
        $where = array();
        
        if (!$this->stb->isModerator()){
            $where['status'] = 1;
        }
        
        if (@$_REQUEST['hd']){
            $where['hd'] = 1;
        }else{
            $where['hd<='] = 1;
        }
        
        if (@$_REQUEST['genre'] && @$_REQUEST['genre'] !== '*'){
            
            $genre = intval($_REQUEST['genre']);
            
            $where['tv_genre_id'] = $genre;
        }
        
        $offset = $this->getOffset($where);
        
        return $this->db
                        ->from('itv')
                        ->where($where)
                        ->limit(MAX_PAGE_ITEMS, $offset);
    }
    
    public function getOrderedList(){
        $fav = $this->getFav();
        $fav_str = implode(",", $fav);
        
        if (empty($fav_str)){
            $fav_str = 'null';
        }
        
        $result = $this->getData();
        
        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];
            
            if ($sortby == 'name'){
                $result = $result->orderby('name');
            }elseif ($sortby == 'number'){
                $result = $result->orderby('number');
            }elseif ($sortby == 'fav'){
                $result = $result->orderby('field(id,'.$fav_str.')');
            }
            
        }else{
            $result = $result->orderby('number');
        }
        
        if (@$_REQUEST['fav']){
            $result = $result->in('itv.id', $fav);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        $fav = $this->getFav();
        
        $epg = new Epg();
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
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
            
            if (@$_REQUEST['fav']){
                $this->response['data'][$i]['number'] = strval(($i+1) + (MAX_PAGE_ITEMS * ($this->page)));
            }
            
            $this->response['data'][$i]['genres_str'] = $this->getGenreById($this->response['data'][$i]['id']);
            
            $next_five_epg = $epg->getCurProgramAndFiveNext($this->response['data'][$i]['id']);
            
            $cur_playing = $this->no_ch_info;
            
            if (!empty($next_five_epg)){
                $cur_playing = $next_five_epg[0]['t_time'].' '.$next_five_epg[0]['name'];
            }
            
            $this->response['data'][$i]['cur_playing'] = $cur_playing;
            
            $this->response['data'][$i]['epg'] = $next_five_epg;
            
            $this->response['data'][$i]['open'] = 1;

            if($this->response['data'][$i]['use_http_tmp_link']){
                $this->response['data'][$i]['cmd'] = 'rtp udp://ch/'.$this->response['data'][$i]['id'];
            }
            
            if (ENABLE_SUBSCRIPTION){
                
                if (in_array($this->response['data'][$i]['id'], $this->getAllUserChannelsIds())){
                    $this->response['data'][$i]['open'] = 1;
                }else{
                    $this->response['data'][$i]['open'] = 0;
                    $this->response['data'][$i]['cmd'] = 'udp://wtf?';
                }
            }
        }

        return $this->response;
    }
    
    private function getGenreById($id){
        
        $genre = $this->db->from('tv_genre')->where(array('id' => $id))->get()->first();
        
        if (empty($genre)){
            return '';
        }
        
        return $genre['title'];
    }
    
    public function getEpgInfo(){
        $epg = new Epg();
        
        $response = array('data' => $epg->getEpgInfo());
        return $response;
    }
    
    public function getAllUserChannelsIds(){
        
        if (empty($this->all_user_channels_ids)){
        
            $this->all_user_channels_ids = array_unique(array_merge($this->getSubscriptionChannelsIds(), $this->getBonusChannelsIds(), $this->getBaseChannelsIds()));
        }
        
        return $this->all_user_channels_ids;
    }

    public function getSubscriptionChannelsIds(){
        
        $db = clone $this->db;
        
        $sub_ch = $db->from('itv_subscription')->where(array('uid' => $this->stb->id))->get()->first('sub_ch');
        
        if (empty($sub_ch)){
            return array();
        }
        
        $sub_ch_arr = unserialize(base64_decode($sub_ch));
        
        if (!is_array($sub_ch_arr)){
            return array();
        }
        
        return $sub_ch_arr;
    }
    
    public function getBonusChannelsIds(){
        
        $db = clone $this->db;
        
        $bonus_ch = $db->from('itv_subscription')->where(array('uid' => $this->stb->id))->get()->first('bonus_ch');
        
        if (empty($bonus_ch)){
            return array();
        }
        
        $bonus_ch_arr = unserialize(base64_decode($bonus_ch));
        
        if (!is_array($bonus_ch_arr)){
            return array();
        }
        
        return $bonus_ch_arr;
    }

    public function getBaseChannelsIds(){
        
        $db = clone $this->db;
        
        return $db->from('itv')->where(array('base_ch' => 1))->get()->all('id');
    }
    
    public function setClaim(){
        
        return $this->setClaimGlobal('itv');
        
    }
    
    public function setFavStatus(){
        
        return $this->db->update('users',
                                 array(
                                     'fav_itv_on' => @intval($_REQUEST['fav_itv_on'])
                                 ),
                                 array(
                                     'id' => $this->stb->id
                                 ));
    }
    
    public function getChannelsByIds($ids){
        
        return $this->getChannels(true)->in('id', $ids)->get()->all();
    }
    
    public static function getChannelNameById($id){
        
        return Mysql::getInstance()->from('itv')->where(array('id' => $id))->get()->first('name');
    }
    
    public static function getChannelById($id){
        
        return Mysql::getInstance()->from('itv')->where(array('id' => $id))->get()->first();
    }
    
    public function getShortEpg(){
        
        $ch_id = intval($_REQUEST['ch_id']);
        
        $epg = new Epg();
        
        return $epg->getCurProgramAndFiveNext($ch_id);
    }
}
?>