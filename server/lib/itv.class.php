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
    private $include_censored = true;

    /**
     * @static
     * @return Itv
     */
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

    /**
     * nginx.conf
     *
     * location / {
     *
     *   rewrite ^/ch/(.*) /stalker_portal/server/api/chk_tmp_tv_link.php?key=$1 last;
     *
     *   proxy_set_header Host tv.infomir.com.ua;
     *   proxy_set_header X-Real-IP $remote_addr;
     *   proxy_pass http://tv.infomir.com.ua:88/;
     *}
     *
     *location ~* ^/get/(.*?)/(.*) {
     *   internal;
     *
     *   set $upstream_uri       $2;
     *   set $upstream_host      $1;
     *
     *   set $upstream_url http://$upstream_host/$upstream_uri;
     *
     *   proxy_set_header Host $upstream_host;
     *   proxy_set_header X-Real-IP $remote_addr;
     *   proxy_pass $upstream_url;
     *}
     * 
     * @return array
     */
    public function createLink(){

        $cmd = '';

        preg_match("/\/ch\/(\d+)$/", $_REQUEST['cmd'], $tmp_arr);

        if (empty($tmp_arr)){
            $error = 'nothing_to_play';
        }

        $ch_id = intval($tmp_arr[1]);

        try{
            $cmd = $this->getUrlByChannelId($ch_id);
        }catch(ItvLinkException $e){
            $error = $e->getMessage();
            echo $e->getTraceAsString();
        }catch(Exception $e){
            $error = 'link_fault';
            echo $e->getTraceAsString();
        }

        $res = array(
            'id'    => $ch_id,
            'cmd'   => empty($error) ? $cmd : '',
            'error' => empty($error) ? '' : $error
        );

        var_dump($res);

        return $res;
    }

    public function getUrlByChannelId($ch_id){

        $ch_id = intval($ch_id);

        $channel = self::getChannelById($ch_id);

        if (empty($channel)){
            throw new ItvLinkException('nothing_to_play');
        }

        if ($channel['enable_wowza_load_balancing']){

            $balancer_addr = $this->getWowzaBalancer($channel['cmd']);

            $edge = $this->getWowzaEdge('http://'.$balancer_addr.'/loadbalancer');

            if (!$edge){
                throw new ItvLinkException('nothing_to_play');
            }else{

                $cmd = preg_replace("/".preg_replace('/:.*/', '', $balancer_addr)."/", $edge, $channel['cmd']);

                if ($cmd){
                    $channel['cmd'] = $cmd;
                }
            }
        }

        if ($channel['use_http_tmp_link']){

            if ($channel['wowza_tmp_link']){
                $key = $this->createTemporaryLink("1");

                if (!$key){
                    throw new ItvLinkException('link_fault');
                }else{
                    $cmd = $channel['cmd'].'?'.$key;
                }
            }else{

                if (Config::getSafe('stream_proxy', '') != ''){
                    preg_match("/http:\/\/([^\/]*)[\/]?([^\s]*)?(\s*)?(.*)?$/", $channel['cmd'], $tmp_url_arr);
                }else{
                    preg_match("/http:\/\/([^\/]*)\/([^\/]*)[\/]?([^\s]*)?(\s*)?(.*)?$/", $channel['cmd'], $tmp_url_arr);
                }

                if (empty($tmp_url_arr)){
                    throw new ItvLinkException('nothing_to_play');
                }else{
                    if (count($tmp_url_arr) == 6){
                        $streamer = $tmp_url_arr[1];
                        $redirect_host = $tmp_url_arr[2];
                        $redirect_uri  = $tmp_url_arr[3];
                    }else{
                        $streamer = Config::get('stream_proxy');
                        $redirect_host = $tmp_url_arr[1];
                        $redirect_uri  = $tmp_url_arr[2];
                    }

                    $redirect_url = '/get/'.$redirect_host.'/'.$redirect_uri;

                    $link_result = $this->createTemporaryLink($redirect_url);

                    if (!$link_result){
                        throw new ItvLinkException('link_fault');
                    }else{
                        $cmd = 'ffrt http://'.$streamer.'/ch/'.$link_result.' '.$tmp_url_arr[4];
                    }
                }
            }
        }else{
            return $channel['cmd'];
        }

        return (empty($cmd) ? false : $cmd);
    }

    private function getWowzaBalancer($url){

        if (preg_match('/:\/\/([^\/]*)\//', $url, $tmp)){
            
            return $tmp[1];
        }

        return false;
    }

    private function getWowzaEdge($balancer_addr){

        $a = microtime(1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $balancer_addr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responce = curl_exec($ch);

        if ($responce === false){
            return false;
        }

        $responce = trim($responce);

        var_dump($balancer_addr,'load', microtime(1) - $a);

        return substr($responce, strlen('redirect='));
    }

    private function createTemporaryLink($url){

        $key = md5($url.time().uniqid());

        $cache = Cache::getInstance();

        $result = $cache->set($key, $url, 0, 5);

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
            $fav_ch = '';
        }

        $fav_ch = explode(",", $fav_ch);
        
        if (is_array($fav_ch)){
            /*$fav_ch_str = base64_encode(serialize($fav_ch));
            
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
            }*/

            return $this->saveFav($fav_ch, $uid);
        }
        
        return true;
    }

    public function saveFav(array $fav_array, $uid){

        if (empty($uid)){
            return false;
        }

        $fav_ch_str  = base64_encode(serialize($fav_array));

        $fav_itv_arr = $this->db->from('fav_itv')->where(array('uid' => $uid))->get()->first();

        if (empty($fav_itv_arr)){
            return $this->db->insert('fav_itv',
                array(
                    'uid'     => $uid,
                    'fav_ch'  => $fav_ch_str,
                    'addtime' => 'NOW()'
                ))->insert_id();
        }else{
            return $this->db->update('fav_itv',
                array(
                    'fav_ch'  => $fav_ch_str,
                    'addtime' => 'NOW()'
                ),
                array('uid' => $uid))->result();
        }
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

        $all_user_channels_ids = $this->getAllUserChannelsIds();

        $query = $this->db->from('itv');

        $this->include_censored = $include_censored;
        
        if (!$include_censored){
            $query->where(array('censored' => 0));
        }
                        
        if (!$this->stb->isModerator()){
            $query->where(array('status' => 1));
        }

        if (Config::get('enable_tv_quality_filter')){
            //$query->where(array('quality' => $this->stb->getParam('tv_quality')));
        }

        if (Config::get('enable_tariff_plans')){
            $query->in('id', $all_user_channels_ids);
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

        $genres = array_map(function($item){$item['title'] = _($item['title']); return $item;}, $genres);
        
        return $genres;
    }
    
    private function getOffset($where = array()){
        
        if (!$this->load_last_page){
            return $this->page * self::max_page_items;
        }
        
        $fav = $this->getFav();
        $all_user_channels_ids = $this->getAllUserChannelsIds();
        
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

                $query = $this->db->from('itv')->where($where)->in('itv.id', $fav);

                if (Config::get('enable_tariff_plans')){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $ch_idx = $query->get()->count();
            }

/*            $query = $this->db->from('itv')->where($where)->in('itv.id', $fav);

            if (Config::get('enable_tariff_plans')){
                $query->in('itv.id', $all_user_channels_ids);
            }

            $ch_idx = $query->get()->count();*/
        }else{

            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name'){

                $query = $this->db->from('itv')->where($where)->orderby('name');

                if (Config::get('enable_tariff_plans')){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $chs = $query->get()->all();

                foreach ($chs as $ch){
                    $ch_idx++;
                    if ($ch['id'] == $last_id){
                        break;
                    }
                }

            }else{
                $query = $this->db->from('itv')->where($where)->where(array('number<=' => $tv_number));

                if (Config::get('enable_tariff_plans')){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $ch_idx = $query->get()->count();
            }
        }
        
        if ($ch_idx > 0){
            $this->cur_page = ceil($ch_idx/self::max_page_items);
            $this->page = $this->cur_page-1;
            $this->selected_item = $ch_idx - ($this->cur_page-1)*self::max_page_items;
        }
        
        $page_offset = ($this->cur_page-1)*self::max_page_items;
        
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

        if (Config::get('enable_tv_quality_filter')){
            $quality = empty($_REQUEST['quality']) ? $this->stb->getParam('tv_quality') : $_REQUEST['quality'];
            $this->stb->setParam('tv_quality', $quality);
            //$where['quality'] = $quality;
        }
        
        if (@$_REQUEST['genre'] && @$_REQUEST['genre'] !== '*'){
            
            $genre = intval($_REQUEST['genre']);
            
            $where['tv_genre_id'] = $genre;
        }
        
        $offset = $this->getOffset($where);
        
        return $this->db
                        ->from('itv')
                        ->where($where)
                        ->limit(self::max_page_items, $offset);
    }
    
    public function getOrderedList(){
        $fav = $this->getFav();
        $all_user_channels_ids = $this->getAllUserChannelsIds();

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

        if (Config::get('enable_tariff_plans') && !Config::getSafe('show_unsubscribed_tv_channels', false)){
            $result = $result->in('itv.id', $all_user_channels_ids);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){

        $fav           = $this->getFav();
        $censored_list = $this->getCensoredList();
        $censored_exclude_list = $this->getCensoredExcludeList();

        //var_dump('!!!!!!!!!!!!!!!!', $censored_list, $censored_exclude_list, $this->include_censored);

        $epg = new Epg();

        //$qualities = array('high' => 1, 'medium' => 2, 'low' => 3);

        $quality = $this->stb->getParam('tv_quality');

        $length = count($this->response['data']);

        for ($i = 0; $i < $length; $i++){

            if (Config::get('enable_tv_quality_filter')){

                if ($quality == 'low'){
                    if ($this->response['data'][$i]['cmd_3']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_3'];
                        $this->response['data'][$i]['quality_low'] = 1;
                    }else if ($this->response['data'][$i]['cmd_2']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_2'];
                        $this->response['data'][$i]['quality_medium'] = 1;
                    }else{
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_1'];
                        $this->response['data'][$i]['quality_high'] = 1;
                    }
                }else if ($quality == 'medium'){
                    if ($this->response['data'][$i]['cmd_2']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_2'];
                        $this->response['data'][$i]['quality_medium'] = 1;
                    }else if ($this->response['data'][$i]['cmd_3']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_3'];
                        $this->response['data'][$i]['quality_low'] = 1;
                    }else{
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_1'];
                        $this->response['data'][$i]['quality_high'] = 1;
                    }
                }else{
                    if ($this->response['data'][$i]['cmd_1']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_1'];
                        $this->response['data'][$i]['quality_high'] = 1;
                    }else if ($this->response['data'][$i]['cmd_2']){
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_2'];
                        $this->response['data'][$i]['quality_medium'] = 1;
                    }else{
                        $this->response['data'][$i]['cmd']     = $this->response['data'][$i]['cmd_3'];
                        $this->response['data'][$i]['quality_low'] = 1;
                    }
                }
            }
            
            if ($this->response['data'][$i]['censored'] && !in_array($this->response['data'][$i]['id'], $censored_exclude_list)){
                $this->response['data'][$i]['lock'] = 1;
            }else{
                $this->response['data'][$i]['lock'] = 0;
            }

            if (in_array($this->response['data'][$i]['id'], $censored_list)){
                $this->response['data'][$i]['lock'] = 1;
            }

            if ($this->response['data'][$i]['lock'] == 1 && !$this->include_censored){
                //unset($this->response['data'][$i]);
                array_splice($this->response['data'], $i, 1);
                continue;
            }
            
            if (in_array($this->response['data'][$i]['id'], $fav)){
                $this->response['data'][$i]['fav'] = 1;
            }else{
                $this->response['data'][$i]['fav'] = 0;
            }

            if ($this->response['data'][$i]['enable_tv_archive']){
                $this->response['data'][$i]['archive'] = 1;
            }else{
                $this->response['data'][$i]['archive'] = 0;
            }
            
            if (@$_REQUEST['fav']){
                $this->response['data'][$i]['number'] = strval(($i+1) + (self::max_page_items * ($this->page)));
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
                $this->response['data'][$i]['cmd'] = 'ffrt http://'.Config::get('stream_proxy').'/ch/'.$this->response['data'][$i]['id'];
            }

            if($this->response['data'][$i]['enable_wowza_load_balancing']){
                $this->response['data'][$i]['use_http_tmp_link'] = 1;
                $this->response['data'][$i]['cmd'] = 'udp://ch/'.$this->response['data'][$i]['id'];
            }
            
            if (Config::get('enable_subscription')){
                
                if (in_array($this->response['data'][$i]['id'], $this->getAllUserChannelsIds()) || $this->stb->isModerator()){
                //if (in_array($this->response['data'][$i]['id'], $this->getAllUserChannelsIds())){
                    $this->response['data'][$i]['open'] = 1;
                }else{
                    $this->response['data'][$i]['open'] = 0;
                    $this->response['data'][$i]['cmd'] = 'udp://wtf?';
                }
            }

            $this->response['data'][$i]['mc_cmd'] = empty($this->response['data'][$i]['mc_cmd']) ? '' : '1';
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
        
        if ($this->all_user_channels_ids === null){
            //$this->all_user_channels_ids = array_unique(array_merge(ItvSubscription::getSubscriptionChannelsIds($this->stb->id), ItvSubscription::getBonusChannelsIds($this->stb->id), $this->getBaseChannelsIds()));
            $this->all_user_channels_ids = $this->getAllUserChannelsIdsByUid($this->stb->id);
        }
        
        return $this->all_user_channels_ids;
    }

    public function getAllUserChannelsIdsByUid($uid){

        if (Config::getSafe('enable_tariff_plans', false)){

            $user = User::getInstance(Stb::getInstance()->id);
            $subscription = $user->getServicesByType('tv');

            if (empty($subscription)){
                $subscription = array();
            }

            //var_dump($subscription);

            //$channel_ids = array_unique(array_merge($subscription, $this->getBaseChannelsIds()));
            $channel_ids = $subscription;
        }else{
            $channel_ids = array_unique(array_merge(ItvSubscription::getSubscriptionChannelsIds($uid), ItvSubscription::getBonusChannelsIds($uid), $this->getBaseChannelsIds()));
        }

        return $channel_ids;
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

    public function getByIds($ids = array()){

        //Mysql::$debug = true;

        $result = Mysql::getInstance()->from('itv');

        if (!empty($ids)){
            $result = $result->in('id', $ids);
        }

        $result = $result->get()->all();

        return $result;
    }

    public function getById($id){
        return Mysql::getInstance()->from('itv')->where(array('id' => intval($id)))->get()->first();
    }

    public function addToCensored(){

        $ch_id = intval($_REQUEST['ch_id']);

        $censored_list = $this->getCensoredList();

        array_push($censored_list, $ch_id);

        return $this->setCensoredList(array_unique($censored_list));
    }

    public function delFromCensored(){

        $ch_id = intval($_REQUEST['ch_id']);

        $censored_list = $this->getCensoredList();

        $idx = array_search($ch_id, $censored_list);

        if ($idx === false){

            $exclude_list = $this->getCensoredExcludeList();
            array_push($exclude_list, $ch_id);
            
            return $this->setCensoredExcludeList(array_unique($exclude_list));
        }

        unset($censored_list[$idx]);

        return $this->setCensoredList($censored_list);
    }

    private function getCensoredExcludeList(){
        
        $list = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first('exclude');

        if (empty($list)){
            return array();
        }

        $list = unserialize(System::base64_decode($list));

        if ($list === false){
            return array();
        }

        return $list;
    }

    private function setCensoredExcludeList($list){
        
        $item = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first();

        $data = array(
            "exclude" => System::base64_encode(serialize($list)),
            "uid"     => $this->stb->id
        );

        if (empty($item)){

            return Mysql::getInstance()->insert('censored_channels', $data)->insert_id();

        }else{

            return Mysql::getInstance()->update('censored_channels', $data, array('uid' => $this->stb->id));

        }
    }

    private function getCensoredList(){

        $list = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first('list');

        if (empty($list)){
            return array();
        }

        $list = unserialize(System::base64_decode($list));

        if ($list === false){
            return array();
        }

        return $list;
    }

    private function setCensoredList($list){

        $item = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first();

        $data = array(
            "list" => System::base64_encode(serialize($list)),
            "uid"  => $this->stb->id
        );

        if (empty($item)){

            return Mysql::getInstance()->insert('censored_channels', $data)->insert_id();

        }else{

            return Mysql::getInstance()->update('censored_channels', $data, array('uid' => $this->stb->id));

        }
    }

    public function getRawAllUserChannels($uid = null){

        if ($uid){
            $user_channels = $this->getAllUserChannelsIdsByUid($uid);
            return Mysql::getInstance()->from('itv')->where(array('status' => 1))->in('id', $user_channels)->orderby('number');
        }


        return Mysql::getInstance()->from('itv')->where(array('status' => 1))->orderby('number');
    }

    public static function getLogoPathById($id){

        $channel = Itv::getById($id);

        if (empty($channel['logo'])){
            return null;
        }

        return realpath(PROJECT_PATH."/../misc/logos/".$channel['logo']);
    }

    public static function getLogoUriById($id){

        $channel = Itv::getById($id);

        if (empty($channel['logo'])){
            return Config::get('portal_url').'misc/logos/dummy.png';
        }

        return Config::get('portal_url').'misc/logos/'.$channel['logo'];
    }

    public static function delLogoById($id){
        $path = self::getLogoPathById($id);

        $result = unlink($path);

        if (!$result){
            return false;
        }

        Mysql::getInstance()->update('itv', array('logo' => ''), array('id' => $id));

        return $result;
    }

    public static function getServices(){
        Mysql::$debug=true;
        return Mysql::getInstance()->select('id, CONCAT_WS(". ", cast(number as char), name) as name')->from('itv')->orderby('number')->get()->all();
    }
}

class ItvLinkException extends Exception{}
?>