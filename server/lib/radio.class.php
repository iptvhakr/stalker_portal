<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Config;

/**
 * Main Radio class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Radio extends AjaxResponse implements \Stalker\Lib\StbApi\Radio
{
    public $fav_radio = FALSE;

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

    public static function getById($id){
        return Mysql::getInstance()->from('radio')->where(array('status' => 1, 'id' => $id))->get()->first();
    }
    
    private function getData(){
        
        $offset = $this->page * self::max_page_items;
        
        $where = array();
        
        if (!$this->stb->isModerator()){
            $where['status'] = 1;
        }
        $this->db->from('radio')->where($where);
        if (empty($_REQUEST['all'])) {
            $this->db->limit(self::max_page_items, $offset);
        }
        return $this->db;
    }
    
    public function getOrderedList(){

        $user = User::getInstance($this->stb->id);
        $all_user_radio_ids = $user->getServicesByType('radio');
        if ($all_user_radio_ids === null){
            $all_user_radio_ids = array();
        }

        if ($this->getFav($this->stb->id) !== FALSE) {
            $fav_str = implode(",", $this->fav_radio);
        } else {
            $fav_str = 'null';
        }
        $result = $this->getData();

        if (@$_REQUEST['search']){
            $search = trim($_REQUEST['search']);
            $result = $result->like(array('name' => "%$search%"));
        }

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
            $result = $result->in('radio.id', ($this->fav_radio !== FALSE ? $this->fav_radio: array()));
        }

        $result = $result->orderby('number');

        if (Config::get('enable_tariff_plans') && $all_user_radio_ids != 'all'){
            $result = $result->in('radio.id', $all_user_radio_ids);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        if (is_array($this->response['data'])) {
            $fav_ids = $this->getFavIds();
            $counter = 1;
            $delimiter = self::max_page_items;
            $this->response['data'] = array_map(function($row) use ($fav_ids, &$counter, $delimiter){
                $row['fav'] = ((int)in_array($row['id'], $fav_ids));

                if ($row['enable_monitoring'] == 1){
                    $row['error'] = (int) $row['monitoring_status'] == 1 ? '': 'link_fault';
                    $row['open'] = (int) $row['monitoring_status'] == 1;
                }else{
                    $row['error'] = '';
                    $row['open'] = 1;
                }

                $row['radio'] = TRUE;
                $row['page'] = ceil($counter/$delimiter);
                $counter++;
                return $row;
            }, $this->response['data']);

            if (array_key_exists('fav', $_REQUEST) && ( (int) $_REQUEST['fav']) == 1 ) {
                reset($this->response['data']);
                while(list($key, $row) = each($this->response['data'])){
                    $this->response['data'][$key]['number'] = (string) ($key + 1);
                }
            }
        }
        return $this->response;
    }

    public function getRawAllUserChannels($uid = null){

        if ($uid){
            if (Config::getSafe('enable_tariff_plans', false)){

                $user = User::getInstance(Stb::getInstance()->id);
                $user_channels = $user->getServicesByType('radio');

                if ($user_channels == 'all'){
                    return Mysql::getInstance()->from('radio')->where(array('status' => 1))->orderby('number');
                }else{
                    return Mysql::getInstance()->from('radio')->where(array('status' => 1))->in('id', $user_channels)->orderby('number');
                }
            }
        }

        return Mysql::getInstance()->from('radio')->where(array('status' => 1))->orderby('number');
    }

    public static function getServices(){
        return Mysql::getInstance()->select('id, name')->from('radio')->get()->all();
    }

    public function setFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        $fav_radio = @$_REQUEST['fav_radio'];

        if (empty($fav_radio)){
            $fav_radio = array();
        }else{
            $fav_radio = explode(",", $fav_radio);
        }

        if (is_array($fav_radio)){
            return $this->saveFav(array_unique($fav_radio), $uid);
        }

        return true;
    }

    public function getAllFavRadio(){
        if ($this->getFav() !== FALSE && !empty($this->fav_radio)) {
            $fav_str = implode(",", $this->fav_radio);
        } else {
            $fav_str = 'null';
        }
        $fav_radios = $this->db
            ->from('radio')
            ->in('id', ($this->fav_radio !== FALSE? $this->fav_radio: array()))
            ->where(array('status' => 1))
            ->orderby('field(id,'.$fav_str.')');
        $this->setResponseData($fav_radios);

        return $this->getResponse('prepareData');
    }

    public function setFavStatus(){}

    public function getFavIds(){

        if ($this->getFav() !== FALSE && !empty($this->fav_radio)) {
            $fav_str = implode(",", $this->fav_radio);
        } else {
            $fav_str = 'null';
        }

        $fav_ids = $this->db
            ->from('radio')
            ->in('id', ($this->fav_radio !== FALSE? $this->fav_radio: array()))
            ->where(array('status' => 1))
            ->orderby('field(id,'.$fav_str.')')
            ->get()
            ->all('id');

        return $fav_ids;
    }

    public function getFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        if ($this->fav_radio === FALSE) {
            $fav_radio_ids_arr = $this->db
                ->select('fav_radio')
                ->from('fav_radio')
                ->where(array('uid' => intval($uid)))
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->get()
                ->first('fav_radio');

            if (!empty($fav_radio_ids_arr)) {
                $this->fav_radio = (is_string($fav_radio_ids_arr) ? unserialize($fav_radio_ids_arr): FALSE);
            }
        }

        return $this->fav_radio;
    }

    public function saveFav(array $fav_array, $uid){

        if (empty($uid)){
            return false;
        }

        $fav_ch_str  = serialize($fav_array);

        if (empty($this->fav_radio)) {
            $this->getFav($uid);
        }

        if ($this->fav_radio === FALSE){
            return $this->db
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->insert('fav_radio',
                    array(
                        'uid'     => (int) $uid,
                        'fav_radio'  => $fav_ch_str,
                        'addtime' => 'NOW()'
                    ))->insert_id();
        }else{
            return $this->db
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->update('fav_radio',
                    array(
                        'fav_radio'  => $fav_ch_str
                    ),
                    array('uid' => (int) $uid))->result();
        }
    }

    public function getChannelById(){
        $number = @$_REQUEST['number'];
        $result = Mysql::getInstance()->from('radio')->where(array('status' => 1, 'number' => $number));
        $this->setResponseData($result);

        return $this->getResponse('prepareData');
    }

    public function getLinksForMonitoring($status=FALSE){
        $result = Mysql::getInstance()
            ->select("id, name as ch_name, cmd as url, 'stream' as type, monitoring_status as status, enable_monitoring")
            ->from('radio')
            ->where(array('enable_monitoring' => 1));

        if ($status) {
            $result->where(array('monitoring_status'=> (int) ($status=='up')));
        }

        $monitoring_links = $result->orderby('id')
            ->get()
            ->all();

        $monitoring_links = array_map(function ($row) {
            if (!empty($row['url']) && preg_match("/(\S+:\/\/\S+)/", $row['url'], $match)) {
                $row['url'] = $match[1];
            }

            return $row;
        }, $monitoring_links);

        return $monitoring_links;
    }

    public static function setChannelLinkStatus($link_id, $status){

        if (empty($link_id) || !is_numeric($link_id)) {
            return false;
        }

        $channel = Mysql::getInstance()->from('radio')->where(array('id' => $link_id))->get()->first();

        if (empty($channel)) {
            return false;
        }

        if ((int)$status != (int)$channel['monitoring_status']) {

            if ((int)$status == 0) {

                if (Config::exist('administrator_email')) {

                    $message = sprintf(_("Radio-channel %s set to active because its URL became available."), $channel['number'] . ' ' . $channel['name']);

                    mail(Config::get('administrator_email'), 'Radio-channels monitoring report: channel enabled', $message, "Content-type: text/html; charset=UTF-8\r\n");
                }

            } else {

                if (Config::exist('administrator_email')) {

                    $message = sprintf(_('Radio-channel %s set to inactive because its URL are not available.'), $channel['number'] . ' ' . $channel['name']);

                    mail(Config::get('administrator_email'), 'Radio-channels monitoring report: channel disabled', $message, "Content-type: text/html; charset=UTF-8\r\n");
                }
            }
            Mysql::getInstance()->update('radio', array('monitoring_status' => $status), array('id' => $link_id))->result();
        }

        return Mysql::getInstance()->update('radio', array('monitoring_status_updated' => 'NOW()'), array('id' => $link_id))->result();
    }
}

?>