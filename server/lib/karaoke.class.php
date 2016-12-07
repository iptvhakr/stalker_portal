<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Cache;

/**
 * Main Karaoke class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Karaoke extends AjaxResponse implements \Stalker\Lib\StbApi\Karaoke
{

    public $fav_karaoke = FALSE;

    private static $instance = NULL;
    
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
        return Mysql::getInstance()->from('karaoke')->where(array('id' => (int) $id))->get()->first();
    }
    
    public function createLink(){
        
        preg_match("/\/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);
        
        $media_id = $tmp_arr[1];

        $res = $this->getLinkByKaraokeId($media_id);
        
        var_dump($res);
        
        return $res;
    }

    public function getLinkByKaraokeId($karaoke_id){

        $master = new KaraokeMaster();

        try {
            $res = $master->play($karaoke_id);
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }

        return $res;
    }

    public function getUrlByKaraokeId($karaoke_id){

        $link = $this->getLinkByKaraokeId($karaoke_id);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
        }

        if (!empty($link['storage_id'])){
            $storage = Master::getStorageById($link['storage_id']);
            if (!empty($storage)){
                $cache = Cache::getInstance();
                $cache->set($this->stb->id.'_playback',
                    array('type' => 'karaoke', 'id' => $link['id'], 'storage' => $storage['storage_name'], 'storage_id' => $storage['id']), 0, 10);
            }
        }else{
            $cache = Cache::getInstance();
            $cache->del($this->stb->id.'_playback');
        }

        return $link['cmd'];
    }
    
    private function getData(){
        
        $offset = $this->page * self::max_page_items;
        
        $where = array('status' => 1);
        
        if (!$this->stb->isModerator()){
            $where['accessed'] = 1;
        }
        
        $like = array();
        
        if (@$_REQUEST['abc'] && @$_REQUEST['abc'] !== '*'){
            
            $letter = $_REQUEST['abc'];
            
            if (@$_REQUEST['sortby'] == 'name'){
                $like = array('karaoke.name' => $letter.'%');
            }else{
                $like = array('karaoke.singer' => $letter.'%');
            }
        }
        
        if (@$_REQUEST['search']){
            
            $letters = $_REQUEST['search'];
            
            $search['karaoke.name']   = '%'.$letters.'%';
            $search['karaoke.singer'] = '%'.$letters.'%';
        }
        
        return $this->db
                        ->from('karaoke')
                        ->where($where)
                        ->like($like)
                        ->like($search, 'OR ')
                        ->limit(self::max_page_items, $offset);
    }
    
    public function getOrderedList(){

        if ($this->getFav($this->stb->id) !== FALSE) {
            $fav_str = implode(",", $this->fav_karaoke);
        } else {
            $fav_str = 'null';
        }

        $result = $this->getData();
        
        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];
            
            if ($sortby == 'name'){
                $result = $result->orderby('karaoke.name');
            }elseif ($sortby == 'singer'){
                $result = $result->orderby('karaoke.singer');
            } elseif ($sortby == 'fav'){
                $result = $result->orderby('field(id,'.$fav_str.')');
            }
            
        }else{
            $result = $result->orderby('karaoke.singer');
        }

        if (@$_REQUEST['fav']){
            $result = $result->in('karaoke.id', ($this->fav_karaoke !== FALSE ? $this->fav_karaoke: array()));
        }

        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){

        $fav_ids = $this->getFavIds();

        for ($i = 0; $i < count($this->response['data']); $i++){

            $this->response['data'][$i]['fav'] = ((int)in_array($this->response['data'][$i]['id'], $fav_ids));

            if (empty($this->response['data'][$i]['rtsp_url'])){
                $this->response['data'][$i]['cmd'] = '/media/'.$this->response['data'][$i]['id'].'.mpg';
            }else{
                $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['rtsp_url'];
            }
        }
        
        return $this->response;
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
    
    public function setClaim(){
        
        return $this->setClaimGlobal('karaoke');
    }

    public function getRawAll(){
        return Mysql::getInstance()
            ->select('karaoke.*, karaoke_genre.title as genre')
            ->from('karaoke')
            ->join('karaoke_genre', 'karaoke.genre_id', 'karaoke_genre.id', 'LEFT')
            ->where(array('status' => 1, 'accessed' => 1));
    }

    public function setFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        $fav_karaoke = @$_REQUEST['fav_karaoke'];

        if (empty($fav_karaoke)){
            $fav_karaoke = array();
        }else{
            $fav_karaoke = explode(",", $fav_karaoke);
        }

        if (is_array($fav_karaoke)){
            return $this->saveFav(array_unique($fav_karaoke), $uid);
        }

        return true;
    }

    public function getAllFavKaraoke(){
        if ($this->getFav() !== FALSE && !empty($this->fav_karaoke)) {
            $fav_str = implode(",", $this->fav_karaoke);
        } else {
            $fav_str = 'null';
        }
        $fav_karaoke = $this->db
            ->from('karaoke')
            ->in('id', ($this->fav_karaoke !== FALSE? $this->fav_karaoke: array()))
            ->where(array('status' => 1))
            ->orderby('field(id,'.$fav_str.')');
        $this->setResponseData($fav_karaoke);

        return $this->getResponse('prepareData');
    }

    public function getFavIds(){

        if ($this->getFav() !== FALSE && !empty($this->fav_karaoke)) {
            $fav_str = implode(",", $this->fav_karaoke);
        } else {
            $fav_str = 'null';
        }

        $fav_ids = $this->db
            ->from('karaoke')
            ->in('id', ($this->fav_karaoke !== FALSE? $this->fav_karaoke: array()))
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

        if ($this->fav_karaoke === FALSE) {
            $fav_karaoke_ids_arr = $this->db
                ->select('fav_karaoke')
                ->from('fav_karaoke')
                ->where(array('uid' => intval($uid)))
                ->use_caching(array('fav_karaoke.uid='.intval($uid)))
                ->get()
                ->first('fav_karaoke');

            if (!empty($fav_karaoke_ids_arr)) {
                $this->fav_karaoke = (is_string($fav_karaoke_ids_arr) ? unserialize($fav_karaoke_ids_arr): FALSE);
            }
        }

        return $this->fav_karaoke;
    }

    public function saveFav(array $fav_array, $uid){

        if (empty($uid)){
            return false;
        }

        $fav_ch_str  = serialize($fav_array);

        if (empty($this->fav_karaoke)) {
            $this->getFav($uid);
        }

        if ($this->fav_karaoke === FALSE){
            return $this->db
                ->use_caching(array('fav_karaoke.uid='.intval($uid)))
                ->insert('fav_karaoke',
                    array(
                        'uid'     => (int) $uid,
                        'fav_karaoke'  => $fav_ch_str,
                        'addtime' => 'NOW()'
                    ))->insert_id();
        } else {
            return $this->db
                ->use_caching(array('fav_karaoke.uid='.intval($uid)))
                ->update('fav_karaoke',
                    array(
                        'fav_karaoke'  => $fav_ch_str,
                        'edittime' => 'NOW()'
                    ),
                    array('uid' => (int) $uid))->result();
        }
    }

    public function setFavStatus(){}

}

?>