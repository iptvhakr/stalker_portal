<?php
/**
 * Main ITV class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Itv extends AjaxResponse implements \Stalker\Lib\StbApi\Itv
{
    public static $instance = null;
    
    private $all_user_channels_ids;
    private $dvb_channels = null;
    private $include_censored = true;
    private $fav_itv = null;
    private $censored_channels = null;
    private static $channels_cache = array();
    private static $links_cache = array();

    /**
     * @static
     * @return Itv
     */
    public static function getInstance(){
        if (self::$instance == null)
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
        $streamer_id = 0;
        $link_id = 0;
        $load = 0;

        preg_match("/\/ch\/(\d+)(.*)/", $_REQUEST['cmd'], $tmp_arr);

        if (empty($tmp_arr)){
            $error = 'nothing_to_play';
        }

        $extra = $tmp_arr[2];

        $link_id = intval($tmp_arr[1]);
        $link = \Itv::getLinkById($link_id);

        $channel = Itv::getById($link['ch_id']);
        $ch_id = $channel['id'];

        try{

            if (!empty($link)){
                $link_id = $link['id'];

                if ($link['status'] == 0 || Config::getSafe('force_ch_link_check', false) || !empty($_REQUEST['for_pvr']) && strpos($link['url'], '.m3u8')){

                    $alternative_links = self::getUrlsForChannel($ch_id, !empty($_REQUEST['for_pvr']));

                    if (empty($alternative_links)){
                        throw new ItvLinkException('nothing_to_play');
                    }else{
                        $link    = $alternative_links[0];
                        $link_id = $link['id'];
                    }
                }

            }else{
                $link_id = null;
            }

            $real_channel = $this->getRealChannelByChannelId($ch_id, $link_id);
            $cmd = $real_channel['cmd'];
            $streamer_id = empty($real_channel['streamer_id']) ? 0 : (int) $real_channel['streamer_id'];
            $link_id     = empty($real_channel['link_id']) ? 0 : (int) $real_channel['link_id'];
            $load        = empty($real_channel['load']) ? 0 : (int) $real_channel['load'];

        }catch(ItvLinkException $e){
            $error = $e->getMessage();
            echo $e->getTraceAsString();
        }catch(Exception $e){
            $error = 'link_fault';
            echo $e->getTraceAsString();
        }

        $res = array(
            'id'          => $ch_id,
            'cmd'         => empty($error) ? $cmd.$extra : '',
            'streamer_id' => $streamer_id,
            'link_id'     => $link_id,
            'load'        => $load,
            'error'       => empty($error) ? '' : $error
        );

        var_dump($res);

        return $res;
    }

    public function getUrlByChannelId($ch_id, $link_id = null){

        $channel = $this->getRealChannelByChannelId($ch_id, $link_id);

        return (empty($channel['cmd']) ? false : $channel['cmd']);
    }

    public function getRealChannelByChannelId($ch_id, $link_id = null){

        $ch_id = intval($ch_id);

        $channel = Itv::getById($ch_id);
        $channel['link_id'] = $link_id;

        if ($link_id != null){
            $link = Itv::getLinkById($link_id);

            if (!empty($link)){
                $channel['cmd'] = $link['url'];
                $channel['use_http_tmp_link']  = $link['use_http_tmp_link'];
                $channel['wowza_tmp_link']     = $link['wowza_tmp_link'];
                $channel['nginx_secure_link']  = $link['nginx_secure_link'];
                $channel['use_load_balancing'] = $link['use_load_balancing'];
            }
        }

        if (empty($link_id) || empty($link)){
            throw new ItvChannelTemporaryUnavailable();
        }

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

        if ($channel['use_load_balancing']){

            try{
                $streamers = StreamServer::getForLink($link_id);
            }catch (Exception $e){
                throw new ItvLinkException($e->getCode());
            }

            if ($streamers){
                $new_addr = $streamers[0]['address'];
                $channel['load'] = $streamers[0]['load'];

                if ($channel['load'] >= 1){
                    throw new ItvLinkException('limit');
                }

                $channel['streamer_id'] = $streamers[0]['id'];
                $channel['cmd'] = preg_replace('/:\/\/([^\/]*)/', '://'.$new_addr ,$channel['cmd']);
            }else{
                throw new ItvLinkException('nothing_to_play');
            }
        }

        if ($channel['use_http_tmp_link']){

            if ($channel['wowza_tmp_link']){
                $key = $this->createTemporaryLink("1");

                if (!$key){
                    throw new ItvLinkException('link_fault');
                }else{
                    if (Config::getSafe('use_named_wowza_token', false)){
                        $channel['cmd'] = $channel['cmd'].(strpos($channel['cmd'], '?') ? '&' : '?').'token='.$key;
                    }else{
                        $channel['cmd'] = $channel['cmd'].'?'.$key;
                    }
                }
            }else if (!empty($link) && $link['flussonic_tmp_link']){
                $key = $this->createTemporaryLink($this->stb->id);

                if (!$key){
                    throw new ItvLinkException('link_fault');
                }else{
                    $channel['cmd'] = $channel['cmd'].(strpos($channel['cmd'], '?') ? '&' : '?').'token='.$key;
                }
            }else if ($channel['nginx_secure_link']){ // http://wiki.nginx.org/HttpSecureLinkModule

                $channel['cmd'] = self::getNginxSecureLink($channel['cmd']);

            }else{

                if (strpos($channel['cmd'], 'rtp://') !== false || strpos($channel['cmd'], 'udp://') !== false){
                    return $channel;
                }

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

                        if (preg_match("/(\w+)\s+http:/", $channel['cmd'], $match)){
                            $solution = $match[1];
                        }else{
                            $solution = 'ffrt';
                        }

                        $channel['cmd'] = $solution.' http://'.$streamer.'/ch/'.$link_result
                            .(empty($tmp_url_arr[4]) ? '' : $tmp_url_arr[4])
                            .(empty($tmp_url_arr[5]) ? '' : $tmp_url_arr[5]);
                    }
                }
            }
        }

        if (!empty($channel['streamer_id'])){
            $cache = Cache::getInstance();
            $cache->set($this->stb->id.'_playback',
                array('type' => 'tv-channel', 'id' => $channel['id'], 'link_id' => $channel['link_id'],
                      'streamer_id' => $channel['streamer_id']), 0, 10);

        }else{
            $cache = Cache::getInstance();
            $cache->del($this->stb->id.'_playback');
        }

        return $channel;
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

        $key = md5($url.microtime(1).uniqid());

        $cache = Cache::getInstance();

        $result = $cache->set($key, $url, 0, Config::getSafe('tv_tmp_link_ttl', 5));

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
                                            'itv_id'      => $itv_id,
                                            'uid'         => $this->stb->id,
                                            'playtime'    => 'NOW()',
                                            'user_locale' => $this->stb->getParam('locale')
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
            $this->db->insert('last_id', array('last_id' => $id, 'ident' => $this->stb->mac, 'uid' => $this->stb->id));
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
        }else{
            $fav_ch = explode(",", $fav_ch);
        }

        if (is_array($fav_ch)){
            return $this->saveFav(array_unique($fav_ch), $uid);
        }
        
        return true;
    }

    public function saveFav(array $fav_array, $uid){

        if (empty($uid)){
            return false;
        }

        $fav_ch_str  = base64_encode(serialize($fav_array));

        if ($this->fav_itv === null){
            $this->fav_itv = $fav_itv_arr = $this->db
                ->from('fav_itv')
                ->where(array('uid' => intval($uid)))
                ->use_caching(array('fav_itv.uid='.intval($uid)))
                ->get()
                ->first();
        }else{
            $fav_itv_arr = $this->fav_itv;
        }

        if (empty($fav_itv_arr)){
            return $this->db
                ->use_caching(array('fav_itv.uid='.intval($uid)))
                ->insert('fav_itv',
                array(
                    'uid'     => (int) $uid,
                    'fav_ch'  => $fav_ch_str,
                    'addtime' => 'NOW()'
                ))->insert_id();
        }else{
            return $this->db
                ->use_caching(array('fav_itv.uid='.intval($uid)))
                ->update('fav_itv',
                array(
                    'fav_ch'  => $fav_ch_str,
                    'addtime' => 'NOW()'
                ),
                array('uid' => (int) $uid))->result();
        }
    }

    public function getFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        if ($this->fav_itv === null){
            $this->fav_itv = $fav_itv_ids_arr = $this->db
                ->from('fav_itv')
                ->where(array('uid' => intval($uid)))
                ->use_caching(array('fav_itv.uid='.intval($uid)))
                ->get()
                ->first();
        }else{
            $fav_itv_ids_arr = $this->fav_itv;
        }

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

    /**
     * @param bool $include_censored
     * @param bool $include_unsubscribed
     * @return Mysql $query
     */
    public function getChannels($include_censored = false, $include_unsubscribed = false){

        $all_user_channels_ids = $this->getAllUserChannelsIds();

        if (Config::getSafe('enable_tariff_plans', false)){
            $user = User::getInstance(Stb::getInstance()->id);
            $options = $user->getServicesByType('option');
            if ($options && array_search('show_unsubscribed_tv_channels', $options) !== false){
                $show_unsubscribed_tv_channels_option = true;
            }else{
                $show_unsubscribed_tv_channels_option = false;
            }
        }else{
            $show_unsubscribed_tv_channels_option = Config::getSafe('show_unsubscribed_tv_channels', false);
        }

        if (!$include_censored){
            $censored_origin = Mysql::getInstance()->from('itv')->where(array('censored' => 1))->get()->all('id');
            $censored_list = $this->getCensoredList();
            $censored_exclude_list = $this->getCensoredExcludeList();

            $censored_real = array_values(array_diff(array_merge($censored_origin, $censored_list), $censored_exclude_list));
        }

        /** @var Mysql $query  */
        $query = $this->db->from('itv');

        $this->include_censored = $include_censored;
        
        if (!$include_censored){
            $query->not_in('id', $censored_real);
        }
                        
        if (!$this->stb->isModerator()){
            $query->where(array('status' => 1));
        }

        if (!$include_unsubscribed || !$show_unsubscribed_tv_channels_option && (!Config::getSafe('show_unsubscribed_tv_channels', false) || Config::getSafe('show_unsubscribed_tv_channels', false) && in_array($this->stb->mac, Config::getSafe('hide_unsubscribed_for_macs', array())))){
            $query->in('id', $all_user_channels_ids);
        }

        return $query;
    }

    public static function getFilteredUserChannelsIds(){

        $user_agent = Stb::getInstance()->getUserAgent();

        $all_links = Mysql::getInstance()->from('ch_links')->get()->all();

        $disable_channel_filter_for_macs = Config::getSafe('disable_channel_filter_for_macs', array());

        $mac = Stb::getInstance()->mac;

        $user_links = array_filter($all_links, function($link) use ($user_agent, $disable_channel_filter_for_macs, $mac){
            return $link['user_agent_filter'] == '' || in_array($mac, $disable_channel_filter_for_macs) || preg_match("/".$link['user_agent_filter']."/", $user_agent);
        });

        $user_ch_ids = array_map(function($link){
            return $link['ch_id'];
        }, $user_links);

        $user_ch_ids = array_unique($user_ch_ids);

        return $user_ch_ids;
    }

    public function getAllChannels(){
        
        $result = $this->getChannels(true, true)
                    ->orderby('number');
                    //->get()
                    //->all();
        $this->include_censored = false;
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
        
    }
    
    public function getAllFavChannels(){
        $fav_ids = $this->getFav();
        
        $fav_str = implode(",", $fav_ids);
        
        if (empty($fav_str)){
            $fav_str = 'null';
        }

        $fav_channels = $this->getChannels(true, true)->in('id', $fav_ids)->orderby('field(id,' . $fav_str . ')');

        $this->include_censored = false;

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

        $user_genres = $this->getChannels(true)->groupby('tv_genre_id')->get()->all('tv_genre_id');

        /** @var Mysql $genres_query  */
        $genres_query = $this->db->from('tv_genre');

        if (!Config::getSafe('show_empty_tv_category', true)){
            $genres_query->in('id', $user_genres);
        }

        $genres = $genres_query->orderby('number', 'ASC')->get()->all();

        if (in_array('dvb', stb::getAvailableModulesByUid($this->stb->id))
            && in_array(Stb::getInstance()->getParam('stb_type'), array('MAG270', 'MAG275'))){
            array_unshift($genres, array('id' => 'dvb', 'title' => _('DVB')));
        }

        if (Config::getSafe('show_pvr_filter_in_genres_list', false)
            && in_array(Stb::getInstance()->getParam('stb_type'), explode(',', Config::get('allowed_stb_types_for_local_recording')))
        ){
            array_unshift($genres, array('id' => 'pvr', 'title' => _('Channels with PVR')));
        }

        array_unshift($genres, array('id' => '*', 'title' => $this->all_title));

        $genres = array_map(function($item){
            $item['alias'] = strtolower($item['title']);
            $item['title'] = _($item['title']);
            return $item;
        }, $genres);
        
        return $genres;
    }
    
    private function getOffset($where = array(), $where_or = array()){
        
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

        if (empty($_REQUEST['genre']) || $_REQUEST['genre'] == '*' || $_REQUEST['genre'] == 'dvb'){
            $dvb_channels = $this->getDvbChannels();
        }else{
            $dvb_channels = array();
        }

        $tv_number = $this->db->from('itv')->where(array('id' => $last_id))->get()->first('number');

        if (empty($tv_number) && !empty($dvb_channels)){
            foreach ($dvb_channels as $channel){
                if ($channel['id'] == $last_id){
                    $tv_number = $channel['number'];
                    break;
                }
            }
        }
        
        $ch_idx = 0;

        if (Config::getSafe('enable_tariff_plans', false)){
            $user = User::getInstance(Stb::getInstance()->id);
            $options = $user->getServicesByType('option');
            if ($options && array_search('show_unsubscribed_tv_channels', $options) !== false){
                $show_unsubscribed_tv_channels_option = true;
            }else{
                $show_unsubscribed_tv_channels_option = false;
            }
        }else{
            $show_unsubscribed_tv_channels_option = Config::getSafe('show_unsubscribed_tv_channels', false);
        }
        
        if(@$_REQUEST['fav']){
            
            if (in_array($last_id, $fav)){
                
                $ch_tmp_idx = array_search($last_id, $fav);
                
                if ($ch_tmp_idx >= 0){
                    $fav = array_slice($fav, 0, $ch_tmp_idx+1);
                }

                $query = $this->db->from('itv')->where($where)->in('itv.id', $fav);

                if (!empty($where_or)){
                    $query->where($where_or, 'OR ');
                }

                if (!$show_unsubscribed_tv_channels_option && (!Config::getSafe('show_unsubscribed_tv_channels', false) || Config::getSafe('show_unsubscribed_tv_channels', false) && in_array($this->stb->mac, Config::getSafe('hide_unsubscribed_for_macs', array())))){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $ch_idx = $query->get()->count();

                if (!empty($dvb_channels)){
                    $flipped_fav = array_flip($fav);
                    foreach ($dvb_channels as $channel){
                        if (isset($flipped_fav[$channel['id']])){
                            $ch_idx++;
                        }
                    }
                }
            }

        }else{

            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name'){

                $query = $this->db->from('itv')->where($where)->orderby('name');

                if (!empty($where_or)){
                    $query->where($where_or, 'OR ');
                }

                if (!$show_unsubscribed_tv_channels_option && (!Config::getSafe('show_unsubscribed_tv_channels', false) || Config::getSafe('show_unsubscribed_tv_channels', false) && in_array($this->stb->mac, Config::getSafe('hide_unsubscribed_for_macs', array())))){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $all_channels = $query->get()->all();

                $all_channels_map = array();

                foreach ($all_channels as $channel){
                    $all_channels_map[$channel['name'].'-'.$channel['id']] = $channel;
                }

                $dvb_channels_name_map = array();

                foreach ($dvb_channels as $channel){
                    $dvb_channels_name_map[$channel['name'].'-'.$channel['id']] = $channel;
                }

                $all_channels_map = array_merge($all_channels_map, $dvb_channels_name_map);

                ksort($all_channels_map);

                $ch_idx = 0;

                foreach ($all_channels_map as $key => $channel){

                    $ch_idx++;

                    if ($channel['id'] == $last_id){
                        break;
                    }
                }

            }else{

                $query = $this->db->from('itv')->where($where)->where(array('number<=' => $tv_number));

                if (!empty($where_or)){
                    $query->where($where_or, 'OR ');
                }

                if (!$show_unsubscribed_tv_channels_option && (!Config::getSafe('show_unsubscribed_tv_channels', false) || Config::getSafe('show_unsubscribed_tv_channels', false) && in_array($this->stb->mac, Config::getSafe('hide_unsubscribed_for_macs', array())))){
                    $query->in('itv.id', $all_user_channels_ids);
                }

                $all_ids = $query->get()->all('id');

                if (!empty($dvb_channels) && $dvb_channels[0]['number'] < $tv_number){

                    $dvb_channels_ids = array();

                    foreach ($dvb_channels as $channel){
                        if ($channel['number'] <= $tv_number){
                            $dvb_channels_ids[] = $channel['id'];
                        }
                    }

                    $all_ids = array_merge($all_ids, $dvb_channels_ids);
                }

                if (array_search($last_id, $all_ids) !== false){
                    $ch_idx = count($all_ids);
                }else{
                    $ch_idx = 1;
                }
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
        
        if (!empty($_REQUEST['genre']) && $_REQUEST['genre'] !== '*' && $_REQUEST['genre'] !== 'pvr'){
            
            $genre = intval($_REQUEST['genre']);
            
            $where['tv_genre_id'] = $genre;
        }elseif(!empty($_REQUEST['genre']) && $_REQUEST['genre'] == 'pvr'){
            $where_or = array(
                'allow_pvr' => 1,
                'allow_local_pvr' => 1
            );
        }

        if ((empty($_REQUEST['genre']) || $_REQUEST['genre'] == '*') && !Config::getSafe('show_adult_tv_channels_in_common_list', true)){
            $where['tv_genre_id!='] = (int) Mysql::getInstance()->from('tv_genre')->where(array('title' => 'for adults'))->get()->first('id');
        }
        
        $offset = $this->getOffset($where, isset($where_or) ? $where_or : array());

        $this->db
            ->from('itv')
            ->where($where)
            ->limit(self::max_page_items, $offset);

        if (\Config::getSafe('enable_numbering_in_order', false) && \Config::getSafe('order_itv_channel_as_adding', false) &&
            (empty($_REQUEST['sortby']) || (!empty($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'number'))) {
            $this->db
                ->select(array('itv.*'))
                ->join('service_in_package', 'itv.id', 'service_in_package.service_id', 'LEFT')
                ->orderby('service_in_package.id', 'DESC')
                ->groupby('service_in_package.service_id');
        }

        if (isset($where_or)){
            $this->db->where($where_or, 'OR ');
        }

        return $this->db;
    }
    
    public function getOrderedList(){
        $fav = $this->getFav();
        $all_user_channels_ids = $this->getAllUserChannelsIds();
        $dvb_channels = $this->getDvbChannels();

        $fav_str = implode(",", $fav);

        if (empty($fav_str)){
            $fav_str = 'null';
        }

        if (Config::getSafe('enable_tariff_plans', false)){
            $user = User::getInstance(Stb::getInstance()->id);
            $options = $user->getServicesByType('option');
            if ($options && array_search('show_unsubscribed_tv_channels', $options) !== false){
                $show_unsubscribed_tv_channels_option = true;
            }else{
                $show_unsubscribed_tv_channels_option = false;
            }
        }else{
            $show_unsubscribed_tv_channels_option = Config::getSafe('show_unsubscribed_tv_channels', false);
        }

        $result = $this->getData();

        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name'){

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

        if (!$show_unsubscribed_tv_channels_option && (!Config::getSafe('show_unsubscribed_tv_channels', false) || Config::getSafe('show_unsubscribed_tv_channels', false) && in_array($this->stb->mac, Config::getSafe('hide_unsubscribed_for_macs', array())))){
            $result = $result->in('itv.id', $all_user_channels_ids);
        }

        if (@$_REQUEST['sortby'] == 'name'){

            $iptv_channels = $result->nolimit()->get()->all();

            $iptv_channels_map = array();
            foreach ($iptv_channels as $channel){
                $iptv_channels_map[$channel['name'].'-'.$channel['id']] = $channel;
            }

            $dvb_channels_name_map = array();

            foreach ($dvb_channels as $channel){
                $dvb_channels_name_map[$channel['name'].'-'.$channel['id']] = $channel;
            }

            $all_channels_map = array_merge($iptv_channels_map, $dvb_channels_name_map);

            ksort($all_channels_map);

            $page_channels = array_slice(array_values($all_channels_map), $this->page * self::max_page_items, self::max_page_items);

            $this->setResponse('total_items', count($all_channels_map));
            $this->setResponse('cur_page', $this->cur_page);
            $this->setResponse('selected_item', $this->selected_item);
            $this->setResponse('data', $page_channels);

        }else{
            $this->setResponseData($result);
        }

        return $this->getResponse('prepareData');
    }

    public function prepareData(){

        $fav           = $this->getFav();
        $censored_list = $this->getCensoredList();
        $censored_exclude_list = $this->getCensoredExcludeList();
        $dvb_channels = $this->getDvbChannels();

        //var_dump('!!!!!!!!!!!!!!!!', $censored_list, $censored_exclude_list, $this->include_censored);

        $epg = new Epg();

        $quality = $this->stb->getParam('tv_quality');

        $total_iptv_channels = (int) $this->response['total_items'];

        if (!empty($_REQUEST['fav'])){
            $dvb_channels = array_values(array_filter($dvb_channels, function($channel) use ($fav){
                return in_array($channel['id'], $fav);
            }));
        }

        if (@$_REQUEST['sortby'] != 'name' && (empty($_REQUEST['genre']) || $_REQUEST['genre'] == '*' || $_REQUEST['genre'] == 'dvb')){
            $this->response['total_items'] += count($dvb_channels);
        }

        if (((count($this->response['data']) < self::max_page_items) && !empty($dvb_channels) || !isset($_REQUEST['p'])) && @$_REQUEST['sortby'] != 'name' && (empty($_REQUEST['genre']) || $_REQUEST['genre'] == '*' || $_REQUEST['genre'] == 'dvb')){

            $total_iptv_pages = ceil($total_iptv_channels/self::max_page_items);

            if ($this->page == $total_iptv_pages-1){
                $dvb_part_length = self::max_page_items - $total_iptv_channels % self::max_page_items;
            }else{
                $dvb_part_length = self::max_page_items;
            }

            if (!empty($_REQUEST['genre']) && $_REQUEST['genre'] == 'dvb'){
                $dvb_part_offset = $this->page * self::max_page_items;
            }elseif($this->page + 1 > $total_iptv_pages){
                $diff_items = $total_iptv_channels % self::max_page_items;
                $dvb_part_offset = ($this->page - $total_iptv_pages) * self::max_page_items + ($diff_items > 0 ? self::max_page_items - $diff_items : 0);
            }else{
                $dvb_part_offset = 0;
            }

            if (isset($_REQUEST['p'])){
                $dvb_channels = array_splice($dvb_channels, $dvb_part_offset, $dvb_part_length);
            }

            $this->response['data'] = array_merge($this->response['data'], $dvb_channels);

            if (!empty($_REQUEST['fav'])){

                $ordered_list = array();
                $channels_map = array();

                foreach ($this->response['data'] as $channel){
                    $channels_map[$channel['id']] = $channel;
                }

                foreach ($fav as $ch_id){
                    if (!empty($channels_map[$ch_id]))
                    $ordered_list[] = $channels_map[$ch_id];
                }

                $this->response['data'] = $ordered_list;
            }
        }

        $length = count($this->response['data']);

        $enable_numbering_in_order = Config::getSafe('enable_numbering_in_order', false);

        $excluded = 0;

        $ch_ids = array();

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

            unset($this->response['data'][$i]['descr']);
            unset($this->response['data'][$i]['monitoring_url']);

            if ($this->response['data'][$i]['lock'] == 1 && !$this->include_censored){
                array_splice($this->response['data'], $i, 1);
                $length--;
                $i--;
                $excluded++;
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

            if (@$_REQUEST['fav'] || $enable_numbering_in_order){
                $this->response['data'][$i]['number'] = strval(($i+1) + (self::max_page_items * ($this->page)) + ((!empty($_REQUEST['fav']) || $enable_numbering_in_order) ? $excluded : 0));
            }
            
            $this->response['data'][$i]['genres_str'] = '';
            
            $this->response['data'][$i]['epg'] = empty($next_five_epg) ? array() : $next_five_epg;
            
            $this->response['data'][$i]['open'] = 1;

            if($this->response['data'][$i]['use_http_tmp_link'] || Config::getSafe('force_ch_link_check', false)){
                $this->response['data'][$i]['cmd'] = 'ffrt http://'.Config::get('stream_proxy').'/ch/'.$this->response['data'][$i]['id'];
            }

            if($this->response['data'][$i]['enable_wowza_load_balancing']){
                $this->response['data'][$i]['use_http_tmp_link'] = 1;
                $this->response['data'][$i]['cmd'] = 'udp://ch/'.$this->response['data'][$i]['id'];
            }
            
            if (Config::get('enable_subscription') && (empty($this->response['data'][$i]['type']) || $this->response['data'][$i]['type'] != 'dvb')){
                
                if (in_array($this->response['data'][$i]['id'], $this->getAllUserChannelsIds()) || $this->stb->isModerator()){
                //if (in_array($this->response['data'][$i]['id'], $this->getAllUserChannelsIds())){
                    $this->response['data'][$i]['open'] = 1;
                }else{
                    $this->response['data'][$i]['open'] = 0;
                    $this->response['data'][$i]['cmd'] = 'udp://wtf?';
                }
            }

            if ($this->response['data'][$i]['status'] == 0 && $this->stb->isModerator()){
                $this->response['data'][$i]['only_for_moderator'] = 1;
            }

            $ch_ids[] = $this->response['data'][$i]['id'];

            $this->response['data'][$i]['mc_cmd'] = empty($this->response['data'][$i]['mc_cmd']) ? '' : '1';
            $this->response['data'][$i]['allow_pvr'] = $this->response['data'][$i]['allow_pvr']==0 ? '' : '1';
            $this->response['data'][$i]['allow_local_pvr'] = $this->response['data'][$i]['allow_local_pvr']==0 ? '' : '1';

            $this->response['data'][$i]['pvr'] = (int) (Config::getSafe('show_tv_channel_pvr_icon', true)
                && ($this->response['data'][$i]['allow_pvr'] || $this->response['data'][$i]['allow_local_pvr'])
            );
        }

        $cur_programs = $epg->getCurProgramsMap($ch_ids);

        $urls_map = $this->getUrlsMapForChannels($ch_ids);

        for ($i = 0; $i < count($this->response['data']); $i++){

            $cur_program = isset($cur_programs[$this->response['data'][$i]['id']]) ? $cur_programs[$this->response['data'][$i]['id']] : null;

            if (!empty($cur_program)){
                $cur_playing = $cur_program['t_time'].' '.$cur_program['name'];
            }else{
                $cur_playing = $this->no_ch_info;
            }

            $this->response['data'][$i]['cur_playing'] = $cur_playing;

            if (empty($this->response['data'][$i]['type']) || $this->response['data'][$i]['type'] != 'dvb'){
                $this->response['data'][$i]['cmds']               = isset($urls_map[$this->response['data'][$i]['id']]) ? $urls_map[$this->response['data'][$i]['id']] : array();
                $this->response['data'][$i]['cmd']                = empty($this->response['data'][$i]['cmds'][0]['url']) ? '' : $this->response['data'][$i]['cmds'][0]['url'];
                $this->response['data'][$i]['use_http_tmp_link']  = empty($this->response['data'][$i]['cmds'][0]['use_http_tmp_link']) ? 0 : $this->response['data'][$i]['cmds'][0]['use_http_tmp_link'];
                $this->response['data'][$i]['wowza_tmp_link']     = empty($this->response['data'][$i]['cmds'][0]['wowza_tmp_link']) ? 0 : $this->response['data'][$i]['cmds'][0]['wowza_tmp_link'];
                $this->response['data'][$i]['use_load_balancing'] = empty($this->response['data'][$i]['cmds'][0]['use_load_balancing']) ? 0 : $this->response['data'][$i]['cmds'][0]['use_load_balancing'];
            }

            if (empty($this->response['data'][$i]['cmds']) || $this->response['data'][$i]['enable_monitoring'] && $this->response['data'][$i]['monitoring_status'] == 0){
                $this->response['data'][$i]['open'] = 0;
                $this->response['data'][$i]['error'] = 'limit';
                $this->response['data'][$i]['cmd'] = 'udp://wtf?';
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

        $period = empty($_REQUEST['period']) ? 3 : (int) $_REQUEST['period'];
        
        $response = array('data' => $epg->getEpgInfo($period));
        return $response;
    }
    
    public function getAllUserChannelsIds(){
        
        if ($this->all_user_channels_ids === null){
            $this->all_user_channels_ids = $this->getAllUserChannelsIdsByUid($this->stb->id);
        }
        
        return $this->all_user_channels_ids;
    }

    public function getAllUserChannelsIdsByUid($uid){

        if (Config::getSafe('enable_tariff_plans', false) && !Config::getSafe('enable_tv_subscription_for_tariff_plans', false)){

            $user = User::getInstance($uid);
            $subscription = $user->getServicesByType('tv');

            if (empty($subscription)){
                $subscription = array();
            }

            $channel_ids = $subscription;
        }else{
            $channel_ids = array_unique(array_merge(ItvSubscription::getSubscriptionChannelsIds($uid), ItvSubscription::getBonusChannelsIds($uid), $this->getBaseChannelsIds()));
        }

        $filtered_channels = self::getFilteredUserChannelsIds();

        if (!empty($_COOKIE['ext_channels']) && in_array('ext_channels', stb::getAvailableModulesByUid($this->stb->id))){
            $ext_channels = explode(',', $_COOKIE['ext_channels']);
            $ext_channels = Mysql::getInstance()->from('itv')->where(array('bonus_ch' => 1))->in('id', $ext_channels)->get()->all('id');
            $channel_ids = array_merge($channel_ids, $ext_channels);
        }

        if ($channel_ids == 'all'){
            $channel_ids = $filtered_channels;
        }else{
            $channel_ids = array_intersect($channel_ids, $filtered_channels);
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

        return Mysql::getInstance()
            ->from('itv')
            ->where(array('id' => $id))
            ->use_caching(array('itv.id='.intval($id)))
            ->get()
            ->first('name');
    }
    
    public static function getChannelById($id){
        
        return self::getById($id);
    }

    public static function getLinkById($id){

        if (isset(self::$links_cache[strval($id)])){
            return self::$links_cache[strval($id)];
        }else{
            $link = self::$links_cache[strval($id)] =  Mysql::getInstance()
                ->from('ch_links')
                ->where(array('id' => $id))
                ->use_caching(array('ch_links.id='.intval($id)))
                ->get()
                ->first();
        }

        return $link;
    }
    
    public function getShortEpg(){
        
        $ch_id = intval($_REQUEST['ch_id']);

        $channel = Itv::getById($ch_id);

        if (empty($channel['xmltv_id'])){
            return array();
        }
        
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

    public static function invalidateCacheForChannel($id){
        if (isset(self::$channels_cache[strval($id)])){
            unset(self::$channels_cache[strval($id)]);
        }
    }

    public static function getById($id){
        if (isset(self::$channels_cache[strval($id)])){
            return self::$channels_cache[strval($id)];
        }else{
            $channel = self::$channels_cache[strval($id)] = Mysql::getInstance()
                ->from('itv')
                ->where(array('id' => intval($id)))
                ->use_caching(array('itv.id='.intval($id)))
                ->get()
                ->first();
        }

        return $channel;
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

        if ($this->censored_channels === null){
            $this->censored_channels = $list = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first();
        }else{
            $list = $this->censored_channels;
        }

        if (isset($list['exclude'])){
            $list = $list['exclude'];
        }else{
            $list = array();
        }

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

        if ($this->censored_channels === null){
            $this->censored_channels = $item = Mysql::getInstance()->from('censored_channels')->where(array('uid' => $this->stb->id))->get()->first();
        }else{
            $item = $this->censored_channels;
        }

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

        if ($this->censored_channels === null){
            $this->censored_channels = $list = Mysql::getInstance()
                ->from('censored_channels')
                ->where(array('uid' => intval($this->stb->id)))
                ->use_caching(array('censored_channels.uid='.intval($this->stb->id)))
                ->get()
                ->first();
        }else{
            $list = $this->censored_channels;
        }

        if (isset($list['list'])){
            $list = $list['list'];
        }else{
            $list = array();
        }

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

        if ($this->censored_channels === null){
            $this->censored_channels = $item = Mysql::getInstance()
                ->from('censored_channels')
                ->where(array('uid' => intval($this->stb->id)))
                ->use_caching(array('censored_channels.uid='.intval($this->stb->id)))
                ->get()
                ->first();
        }else{
            $item = $this->censored_channels;
        }

        $data = array(
            "list" => System::base64_encode(serialize($list)),
            "uid"  => $this->stb->id
        );

        if (empty($item)){

            return Mysql::getInstance()->insert('censored_channels', $data)->insert_id();

        }else{

            return Mysql::getInstance()->use_caching()->update('censored_channels', $data, array('uid' => intval($this->stb->id)));

        }
    }

    public function getRawAllUserChannels($uid = null){

        if ($uid){
            $user_channels = $this->getAllUserChannelsIdsByUid($uid);
            return Mysql::getInstance()->from('itv')->where(array('status' => 1))->in('id', $user_channels)->orderby('number');
        }


        return Mysql::getInstance()->from('itv')->where(array('status' => 1))->orderby('number');
    }

    public static function getLogoPathsById($id){

        $channel = Itv::getById($id);

        if (empty($channel['logo'])){
            return null;
        }

        return array(
            realpath(PROJECT_PATH."/../misc/logos/120/".$channel['logo']),
            realpath(PROJECT_PATH."/../misc/logos/160/".$channel['logo']),
            realpath(PROJECT_PATH."/../misc/logos/240/".$channel['logo']),
            realpath(PROJECT_PATH."/../misc/logos/320/".$channel['logo'])
        );
    }

    public static function getLogoUriById($id, $resolution = 320){

        $channel = Itv::getById($id);

        if (empty($channel['logo'])){
            //return Config::get('portal_url').'misc/logos/dummy.png';
            return "";
        }

        return Config::get('portal_url').'misc/logos/'.$resolution.'/'.$channel['logo'];
    }

    public static function delLogoById($id){

        $paths = self::getLogoPathsById($id);

        foreach ($paths as $path){
            if ($path){
                unlink($path);
            }
        }

        return Mysql::getInstance()->update('itv', array('logo' => ''), array('id' => $id))->result();
    }

    public static function getServices(){
        /*Mysql::$debug=true;*/
        return Mysql::getInstance()->select('id, CONCAT_WS(". ", cast(number as char), name) as name')->from('itv')->orderby('number')->get()->all();
    }

    public function getUrlsMapForChannels($ch_ids){

        $user_agent = Stb::getInstance()->getUserAgent();

        $channel_links = Mysql::getInstance()
            ->from('ch_links')
            ->where(array('status' => 1))
            ->in('ch_id', $ch_ids)
            ->orderby('priority, rand()')
            ->get()
            ->all();

        $disable_channel_filter_for_macs = Config::getSafe('disable_channel_filter_for_macs', array());

        $mac = Stb::getInstance()->mac;

        $user_channel_links = array_map(function($link){

            if ($link['use_http_tmp_link'] == 1 || $link['use_load_balancing'] == 1 || Config::getSafe('force_ch_link_check', false)){
                if (preg_match("/(\w+)\s+http:/", $link['url'], $match)){
                    $solution = $match[1];
                }else{
                    $solution = 'ffrt';
                }

                $link['url'] = $solution.' http://'.Config::get('stream_proxy').'/ch/'.$link['id'];
            }

            unset($link['monitoring_url']);

            return $link;

        }, $channel_links);

        // group by channel id
        $grouped_links = array();

        foreach ($user_channel_links as $link){
            if (!isset($grouped_links[$link['ch_id']])){
                $grouped_links[$link['ch_id']] = array();
            }

            $grouped_links[$link['ch_id']][] = $link;
        }

        foreach ($grouped_links as $ch_id => $links){

            $user_channel_links = array_filter($links, function($link) use ($user_agent, $disable_channel_filter_for_macs, $mac){
                return in_array($mac, $disable_channel_filter_for_macs) || $link['user_agent_filter'] != '' && preg_match("/".$link['user_agent_filter']."/", $user_agent);
            });

            if (empty($user_channel_links)){
                $user_channel_links = array_filter($links, function($link) use ($user_agent){
                    return $link['user_agent_filter'] == '';
                });
            }

            $grouped_links[$ch_id] = array_values($user_channel_links);
        }

        return $grouped_links;
    }

    public static function getUrlsForChannel($ch_id, $for_pvr = false){

        $user_agent = Stb::getInstance()->getUserAgent();

        $channel_links = Mysql::getInstance()
            ->from('ch_links')
            ->where(array('ch_id' => $ch_id, 'status' => 1))
            ->orderby('priority, rand()')
            ->get()
            ->all();

        $disable_channel_filter_for_macs = Config::getSafe('disable_channel_filter_for_macs', array());

        $mac = Stb::getInstance()->mac;

        $user_channel_links = array_filter($channel_links, function($link) use ($user_agent, $disable_channel_filter_for_macs, $mac){
            return in_array($mac, $disable_channel_filter_for_macs) || $link['user_agent_filter'] != '' && preg_match("/".$link['user_agent_filter']."/", $user_agent);
        });

        if (empty($user_channel_links)){
            $user_channel_links = array_filter($channel_links, function($link) use ($user_agent){
                return $link['user_agent_filter'] == '';
            });
        }

        if ($for_pvr){
            $user_channel_links = array_filter($user_channel_links, function($link){
                return strpos($link['url'], '.m3u8') === false;
            });
        }

        $user_channel_links = array_map(function($link){

            if ($link['use_http_tmp_link'] == 1 || $link['use_load_balancing'] == 1 || Config::getSafe('force_ch_link_check', false)){
                if (preg_match("/(\w+)\s+http:/", $link['url'], $match)){
                    $solution = $match[1];
                }else{
                    $solution = 'ffrt';
                }

                $link['url'] = $solution.' http://'.Config::get('stream_proxy').'/ch/'.$link['id'];
            }

            unset($link['monitoring_url']);

            return $link;

        }, $user_channel_links);

        return array_values($user_channel_links);
    }

    public function saveDvbChannels(){

        $channels = json_decode($_REQUEST['channels'], true);

        if ($channels === null){
            return false;
        }

        $dvb_channels = Mysql::getInstance()->from('dvb_channels')->where(array('uid' => $this->stb->id))->get()->first('channels');

        if (empty($dvb_channels)){
            return Mysql::getInstance()->insert('dvb_channels',
                array(
                     'uid'      => $this->stb->id,
                     'channels' => json_encode($channels),
                     'modified' => 'NOW()'
                )
            )->insert_id();
        }else{
            return Mysql::getInstance()->update('dvb_channels',
                array(
                     'channels' => json_encode($channels),
                     'modified' => 'NOW()'
                ),
                array('uid' => $this->stb->id)
            )->result();
        }
    }

    public function getDvbChannels(){

        $stb_type = Stb::getInstance()->getParam('stb_type');

        if ($stb_type != 'MAG270' && $stb_type != 'MAG275'){
            return array();
        }

        if ($this->dvb_channels !== null){
            return $this->dvb_channels;
        }

        if (!in_array('dvb', stb::getAvailableModulesByUid($this->stb->id))){
            $this->dvb_channels = array();
            return $this->dvb_channels;
        }

        $dvb_channels = Mysql::getInstance()->from('dvb_channels')->where(array('uid' => $this->stb->id))->get()->first('channels');

        if (empty($dvb_channels)){
            $this->dvb_channels = array();
            return $this->dvb_channels;
        }

        $dvb_channels = json_decode($dvb_channels, true);

        if (!$dvb_channels){
            $this->dvb_channels = array();
            return $this->dvb_channels;
        }

        $ch_number = (int) $this->getChannels(true)->orderby('number', 'desc')->get()->first('number');

        $this->dvb_channels = array_map(function($channel) use (&$ch_number){

            $ch_number++;
            $channel['type'] = 'dvb';
            $channel['cmd']  = 'dvb dvb://'. $channel['id'];
            $channel['cmds'] = array($channel['cmd']);
            $channel['name'] = $channel['name'] . ' (DVB)';
            $channel['status'] = 1;
            $channel['number'] = (string) $ch_number;
            $channel['dvb_id'] = $channel['id'];
            $channel['id'] = (int) str_replace(array('T', 'C', '_'), '', $channel['id']);
            $channel['scrambled'] = $channel['scrambled'] == 'true' ? 1 : 0;
            $channel['tv_genre_id'] = 'dvb';

            unset($channel['isRadio']);
            unset($channel['symrate']);
            unset($channel['channel_number']);

            return $channel;
        }, $dvb_channels);

        return $this->dvb_channels;
    }

    public static function setChannelLinkStatus($link_id, $status){

        if (strpos($link_id, 's') === 0){ // stream balanser monitoring

            $balanser_link_id = substr($link_id, 1);

            Mysql::getInstance()->update('ch_link_on_streamer', array('monitoring_status' => $status), array('id' => $balanser_link_id));

            $balanser_link = Mysql::getInstance()->from('ch_link_on_streamer')->where(array('id' => $balanser_link_id))->get()->first();

            if (empty($balanser_link)){
                return false;
            }

            $link = Mysql::getInstance()->from('ch_links')->where(array('id' => $balanser_link['link_id']))->get()->first();

            if (empty($link)){
                return false;
            }

            if ($status == 0){

                $other_good_balanser_links = Mysql::getInstance()
                    ->from('ch_link_on_streamer')
                    ->where(array(
                        'link_id' => $link['id'],
                        'id!='    => $balanser_link_id,
                        'monitoring_status' => 1
                    ))
                    ->get()
                    ->all();

                if (empty($other_good_balanser_links)){
                    Mysql::getInstance()->update('ch_links', array('status' => $status), array('id' => $link['id']));
                }

            }else{
                Mysql::getInstance()->update('ch_links', array('status' => $status), array('id' => $link['id']));
            }

            $ch_id = $link['ch_id'];

        }else{
            Mysql::getInstance()->update('ch_links', array('status' => $status), array('id' => $link_id));

            $ch_id = (int) Mysql::getInstance()->from('ch_links')->where(array('id' => $link_id))->get()->first('ch_id');
        }

        $channel = Mysql::getInstance()->from('itv')->where(array('id' => $ch_id))->get()->first();

        if (empty($channel)){
            return false;
        }

        $good_links = Mysql::getInstance()->from('ch_links')->where(array('ch_id' => $ch_id, 'status' => 1))->get()->all();

        if (!empty($good_links) && $channel['monitoring_status'] == 0){
            Mysql::getInstance()->update('itv', array('monitoring_status' => 1), array('id' => $ch_id));

            if (Config::exist('administrator_email')){

                $message = sprintf(_("Channel %s set to active because at least one of its URLs became available."), $channel['number'].' '.$channel['name']);

                mail(Config::get('administrator_email'), 'Channels monitoring report: channel enabled', $message, "Content-type: text/html; charset=UTF-8\r\n");
            }

        }else if (empty($good_links) && $channel['monitoring_status'] == 1){
            Mysql::getInstance()->update('itv', array('monitoring_status' => 0), array('id' => $ch_id));

            if (Config::exist('administrator_email')){

                $message = sprintf(_('Channel %s set to inactive because all its URLs are not available.'), $channel['number'].' '.$channel['name']);

                mail(Config::get('administrator_email'), 'Channels monitoring report: channel disabled', $message, "Content-type: text/html; charset=UTF-8\r\n");
            }
        }

        return Mysql::getInstance()->update('itv', array('monitoring_status_updated' => 'NOW()'), array('id' => $ch_id))->result();
    }

    public function getLinksForMonitoring($status=FALSE){

        $result = Mysql::getInstance()
            ->select('ch_links.*, itv.name as ch_name')
            ->from('ch_links')
            ->join('itv', 'itv.id', 'ch_links.ch_id', 'INNER')
            ->where(array(
                'ch_links.enable_monitoring' => 1,
                'ch_links.enable_balancer_monitoring' => 0
            ));

        if ($status) {
            $result->where(array('ch_links.status'=> (int) ($status=='up')));
        }

        $monitoring_links = $result->orderby('ch_id')
            ->get()
            ->all();

        $result = Mysql::getInstance()
            ->select('ch_links.*, streamer_id, ch_link_on_streamer.id as streamer_link_id, itv.name as ch_name')
            ->from('ch_links')
            ->join('ch_link_on_streamer', 'link_id', 'ch_links.id', 'INNER')
            ->join('itv', 'itv.id', 'ch_links.ch_id', 'INNER')
            ->where(array(
                'ch_links.enable_monitoring' => 1,
                'ch_links.enable_balancer_monitoring' => 1,
                'ch_links.use_load_balancing' => 1
            ));
        if ($status) {
            $result->where(array('ch_links.status'=> (int) ($status=='up')));
        }
        $balanser_monitoring_links_raw = $result->orderby('ch_id')
            ->get()
            ->all();

        $servers_map = StreamServer::getIdMap();

        $balanser_monitoring_links = array();

        foreach ($balanser_monitoring_links_raw as $link){
            if (empty($servers_map[$link['streamer_id']])){
                continue;
            }

            if ($link['use_http_tmp_link'] == 1 && $link['wowza_tmp_link'] == 0){

                $colon_pos = strpos($servers_map[$link['streamer_id']]['address'], ":");

                if ($colon_pos === false){
                    $address = $servers_map[$link['streamer_id']]['address'];
                }else{
                    $address = substr($servers_map[$link['streamer_id']]['address'], 0, $colon_pos);
                }

                $link['url']            = preg_replace('/:\/\/([^\/:]*)/', '://'.$address, $link['url']);
                $link['monitoring_url'] = preg_replace('/:\/\/([^\/:]*)/', '://'.$address, $link['monitoring_url']);
            }else{
                $link['url']            = preg_replace('/:\/\/([^\/]*)/', '://'.$servers_map[$link['streamer_id']]['address'], $link['url']);
                $link['monitoring_url'] = preg_replace('/:\/\/([^\/]*)/', '://'.$servers_map[$link['streamer_id']]['address'], $link['monitoring_url']);
            }

            $link['id'] = 's'.$link['streamer_link_id'];

            $balanser_monitoring_links[] = $link;
        }

        $monitoring_links = array_merge($monitoring_links, $balanser_monitoring_links);

        $monitoring_links = array_map(function($cmd){

            $cmd['monitoring_url'] = trim($cmd['monitoring_url']);

            if (!empty($cmd['monitoring_url']) && preg_match("/(\S+:\/\/\S+)/", $cmd['monitoring_url'], $match)){
                $cmd['url'] = $match[1];
            }else if (preg_match("/(\S+:\/\/\S+)/", $cmd['url'], $match)){
                $cmd['url'] = $match[1];
            }

            if ($cmd['flussonic_tmp_link']){
                $cmd['type'] = 'flussonic_health';
            }elseif($cmd['nginx_secure_link']){
                try{
                    $cmd['type'] = 'nginx_secure_link';
                    $cmd['url'] = Itv::getNginxSecureLink($cmd['url']);
                } catch( ItvLinkException $e){
                    return false;
                }
            }else{
                $cmd['type'] = 'stream';
            }

            return $cmd;
        }, $monitoring_links);

        return array_values(array_filter($monitoring_links));
    }

    public static function getNginxSecureLink($cmd){

        if (preg_match("/:\/\/([^\/]+)\/?(\S*)/", $cmd, $match)){

            $nginx_secure_link_order = Config::get('nginx_secure_link_order');
            $nginx_secure_link_field = array(
                '$secure_link_expires'=>'',
                '$uri'=>'',
                '$remote_addr'=>'',
                '$secret'=>''
            );
            $path   = '/'.$match[2];

            $expire = time() + Config::getSafe('nginx_secure_link_ttl', 5);

            if (strpos($nginx_secure_link_order, '$secret') !== FALSE) {
                $nginx_secure_link_field['$secret'] = Config::get('nginx_secure_link_secret');
            }

            if (strpos($nginx_secure_link_order, '$uri') !== FALSE) {
                $nginx_secure_link_field['$uri'] = str_replace('/playlist.m3u8', '', $path);
            }

            if (strpos($nginx_secure_link_order, '$secure_link_expires') !== FALSE) {
                $nginx_secure_link_field['$secure_link_expires'] = $expire;
            }

            if (strpos($nginx_secure_link_order, '$remote_addr') !== FALSE) {
                if (!empty($_SERVER['REMOTE_ADDR'])) {
                    $nginx_secure_link_field['$remote_addr'] = $_SERVER['REMOTE_ADDR'];
                } else {
                    throw new ItvLinkException('link_fault');
                }
            }

            $hash = base64_encode(md5(strtr($nginx_secure_link_order, $nginx_secure_link_field), true));

            $hash = strtr($hash, '+/', '-_');
            $hash = str_replace('=', '', $hash);

            $new_path = $path.(strpos($cmd, '?') ? '&' : '?').'st='.$hash.'&e='.$expire;

            return str_replace($match[1].$path, $match[1].$new_path, $cmd);

        }else{
            throw new ItvLinkException('link_fault');
        }
    }
}

class ItvLinkException extends Exception{}

class ItvChannelTemporaryUnavailable extends Exception{
    protected $message = 'temporary_unavailable';
}
?>