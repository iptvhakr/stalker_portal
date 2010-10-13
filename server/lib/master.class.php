<?php
/**
 * Master for storages.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

abstract class Master
{
    private $storages;
    private $moderator_storages;
    private $clients;
    private $stb;
    protected $media_id;
    private $media_name;
    private $from_cache;
    private $cache_expire_h = MASTER_CACHE_EXPIRE;
    protected $db;
    protected $media_type;
    protected $media_params;
    protected $rtsp_url;
    protected $db_table;
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->stb = Stb::getInstance();
        $this->storages = $this->getAllActiveStorages();
        $this->moderator_storages = $this->getModeratorStorages();
        $this->clients = $this->getClients();
    }
    
    /**
     * Trying to create a link of media file in stb home directory
     *
     * @param int $media_id
     * @param int $series_num
     * @return array contains path to media or error
     */
    public function play($media_id, $series_num = 0, $from_cache = true){
        
        $this->initMedia($media_id);
        
        $res = array(
            'id'         => 0,
            'cmd'        => '',
            'storage_id' => '',
            'load'       => '',
            'error'      => ''
        );
        
        if (!empty($this->rtsp_url)){
            
            $res['id']  = $this->media_id;
            $res['cmd'] = $this->rtsp_url;
            
            return $res;
            
        }
        
        $good_storages = $this->getAllGoodStoragesForMedia($this->media_id, !$from_cache);
        
        $default_error = 'nothing_to_play';
        
        foreach ($good_storages as $name => $storage){
            if ($storage['load'] < 1){
                
                if ($series_num > 0){
                    
                    $file = $storage['series_file'][array_search($series_num, $storage['series'])];
                    
                }else{
                    $file = $storage['first_media'];
                }
                
                try {
                    $this->clients[$name]->createLink($this->stb->mac, $this->media_name, $file, $this->media_id, $this->media_type);
                }catch (SoapFault $exception){
                    $default_error = 'link_fault';
                    $this->parseException($exception);
                    
                    if ($this->from_cache){
                        
                        return $this->play($media_id, $series_num, false);
                    }else{
                        continue;
                    }
                }
                
                preg_match("/([\S\s]+)\.([\S]+)$/", $file, $arr);
                $ext = $arr[2];
                
                $res['cmd'] = 'auto /media/'.$name.'/'.$this->media_id.'.'.$ext;
                $res['id']   = $this->media_id;
                $res['load'] = $storage['load'];
                $res['storage_id'] = $this->storages[$name]['id'];
                $res['from_cache'] = $this->from_cache;
                return $res;
            }else{
                $this->incrementStorageDeny($name);
                $res['error'] = 'limit';
                return $res;
            }
        }
        
        if ($this->from_cache){
            
            return $this->play($media_id, $series_num, false);
        }else{
            $res['error'] = $default_error;
            return $res;
        }

    }
    
    /**
     * Wrapper for srorage method, that creates directory for media my name
     *
     * @param string $media_name
     */
    public function createMediaDir($media_name){
        foreach ($this->storages as $name => $storage){
            try {
                $this->clients[$name]->createDir($media_name);
            }catch (SoapFault $exception){
                $this->parseException($exception);
            }
        }
    }
    
    /**
     * Check stb home directory on all active storages
     *
     */
    public function checkAllHomeDirs(){
        foreach ($this->storages as $name => $storage){
            $this->checkHomeDir($name);
        }
    }
    
    /**
     * Return active storages array
     *
     * @return array active storages
     */
    public function getStoragesForStb(){
        
        //return $this->storages;
        
        $storages = array();
        
        $where = array('status' => 1);
        
        if (!$this->stb->isModerator()){
            $where['for_moderator'] = 0;
        }
        
        $data = $this->db->from('storages')->where($where)->get()->all();
        
        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
        }
        return $storages;
        
    }
    
    /**
     * Return moderators storages
     *
     * @return array storages names
     */
    private function getModeratorStorages(){
        
        $data = $this->db->from('storages')->where(array('status' => 1, 'for_moderator' => 1))->get()->all();
        
        $storages = array();
        
        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
        }
        return $storages;
    }
    
    /**
     * Set media_id and media_name properties
     *
     * @param int $media_id
     */
    private function initMedia($media_id){
        
        if (empty($this->media_id)){
            $this->media_id = $media_id;
        }
        
        if (empty($this->media_params)){
            $this->media_params = $this->getMediaParams($this->media_id);
        }
        
        if (empty($this->media_name)){
            $this->media_name = $this->getMediaName();
        }
    }
    
    /**
     * Get from database all active storages
     *
     * @return array active storages
     */
    private function getAllActiveStorages(){
        
        $storages = array();
        
        $data = $this->db->from('storages')->where(array('status' => 1))->get()->all();
        
        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
        }
        return $storages;
    }
    
    /**
     * Wrapper for srorage method, that check stb home directory
     *
     * @param string $storage_name
     */
    private function checkHomeDir($storage_name){
        
        try {
            $this->clients[$storage_name]->checkHomeDir($this->stb->mac);
        }catch (SoapFault $exception){
            $this->parseException($exception);
        }
    }
    
    /**
     * Get all good storages for media by id from cache(if they valid), or from network
     *
     * @param int $media_id
     * @return array good storages, sorted by load
     */
    private function getAllGoodStoragesForMedia($media_id, $force_net = false){
        
        $cache = array();
        
        $this->initMedia($media_id);
        
        if ($this->stb->isModerator()){
            $good_storages = $this->getAllGoodStoragesForMediaFromNet($this->media_name);
            return $good_storages;
        }
        
        if (!$force_net){
            $cache = $this->getAllGoodStoragesForMediaFromCache();
        }
        
        if (!empty($cache)){
            $good_storages = $cache;
            $this->from_cache = true;
        }else{
            $good_storages = $this->getAllGoodStoragesForMediaFromNet($this->media_name);
            $this->from_cache = false;
        }
        
        $good_storages = $this->sortByLoad($good_storages);
        
        return $good_storages;
    }
    
    /**
     * Get all good for media by id interviewing all good storages
     *
     * @param int $media_id
     * @param bool $force_moderator default = false
     * @return array good storages from net
     */
    public function getAllGoodStoragesForMediaFromNet($media_id, $force_moderator = false){
        
        $this->initMedia($media_id);
        
        $good_storages = array();
        
        if ($this->stb->isModerator() || $force_moderator){
            $storages = $this->storages;
        }else{
            $storages = array_diff($this->storages, $this->moderator_storages);
        }
        
        foreach ($storages as $name => $storage){
            
            $raw = $this->checkMediaDir($name, $this->media_name);
            
            if (count($raw['files']) > 0){
                
                $raw['first_media'] = $raw['files'][0]['name'];
                
                $this->saveSeries($raw['series']);
                
                $raw['load'] = $this->getStorageLoad($name);
                
                $good_storages[$name] = $raw;
                
            }
        }
        $this->checkMD5Sum($good_storages);
        
        if (!$this->stb->isModerator()){
            $this->setStorageCache($good_storages);
        }
        
        return $good_storages;
    }
    
    /**
     * Start md5sum for media in all storages
     *
     * @param string $media_name
     */
    public function startMD5SumInAllStorages($media_name){
        foreach ($this->storages as $name => $storage){
            try {
                $this->startMD5Sum($name, $media_name);
            }catch (Exception $exception){
                
            }
        }
    }
    
    /**
     * wrapper for srorage method, that start md5sum for media
     *
     * @param string $storage_name
     * @param string $media_name
     */
    public function startMD5Sum($storage_name, $media_name){
        try {
            $this->clients[$storage_name]->startMD5Sum($media_name);
        }catch (SoapFault $exception){
            $this->parseException($exception);
            throw new Exception($exception->getMessage());
        }
    }
    
    /**
     * Wrapper for srorage method, that abort md5sum for media
     *
     * @param string $storage_name
     * @param string $media_name
     */
    public function stopMD5Sum($storage_name, $media_name){
        try {
            $this->clients[$name]->stopMD5Sum($media_name);
        }catch (SoapFault $exception){
            $this->parseException($exception);
        }
    }
    
    /**
     * Compare md5sum from net and from cache. If they don't equal - add record to master log
     *
     * @param array $storages_from_net
     */
    private function checkMD5Sum($storages_from_net){
        $storages_from_cache = $this->getAllGoodStoragesForMediaFromCache();
        foreach ($storages_from_net as $name => $storage){
            if (key_exists($name, $storages_from_cache)){
                foreach ($storages_from_net[$name]['files'] as $net_file){
                    foreach ($storages_from_cache[$name]['files'] as $cache_file){
                        if (($cache_file['name'] == $net_file['name']) && ($cache_file['md5'] != $net_file['md5']) && !empty($net_file['md5']) && !empty($cache_file['md5'])){
                            $this->addToLog('File '.$cache_file['name'].' in '.$this->media_name.' on '.$name.' changed '.$cache_file['md5'].' => '.$net_file['md5']);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Add record to master log
     *
     * @param string $txt
     */
    private function addToLog($txt){
        /*$sql = 'insert into master_log (log_txt, added) values ("'.mysql_real_escape_string(trim($txt)).'", NOW())';
        $this->db->executeQuery($sql);*/
        
        $this->db->insert('master_log',
                          array(
                              'log_txt' => trim($txt),
                              'added'   => 'NOW()'
                          ));
        
    }
    
    /**
     * Get good storages from cache
     *
     * @return array good storages from cache
     */
    private function getAllGoodStoragesForMediaFromCache(){
        
        $cache = array();
        
        foreach ($this->getAllCacheKeys() as $key){
        
            /*$sql = 'select * from storage_cache where cache_key="'.$key.'" and status=1 and UNIX_TIMESTAMP(changed)>'.(time() - $this->cache_expire_h*3600);
            $storage_cache = $this->db->executeQuery($sql)->getAllValues();*/
            
            $storage_cache = $this->db->from('storage_cache')
                                      ->where(array(
                                          'cache_key'                => $key,
                                          'status'                   => 1,
                                          'UNIX_TIMESTAMP(changed)>' => time() - $this->cache_expire_h*3600
                                      ))
                                      ->get()
                                      ->all();
            
            if(!empty($storage_cache)){
                $storage_cache = $storage_cache[0];
                $storage_data = unserialize($storage_cache['storage_data']);
                if (is_array($storage_data) && !empty($storage_data)){
                    $cache[$storage_cache['storage_name']] = $storage_data;
                    $cache[$storage_cache['storage_name']]['load'] = $this->getStorageLoad($storage_cache['storage_name']);
                }
            }
            
        }
        return $cache;
    }
    
    /**
     * Set storage cache
     *
     * @param array $storages
     */
    private function setStorageCache($storages){
        
        $this->db->update('storage_cache',
                                  array(
                                      'status'  => 0,
                                      'changed' => '0000-00-00 00:00:00',
                                  ),
                                  array(
                                      'media_id'   => $this->media_id,
                                      'media_type' => $this->media_type,
                                  ));
        
        if (!empty($storages)){
            
            foreach ($storages as $name => $data){
                
                $storage_data = serialize($data);
                
                $cache_key = $this->getCacheKey($name);
                
                $record = $this->db->from('storage_cache')
                                   ->where(array('cache_key' => $cache_key))
                                   ->get()
                                   ->first();

                if (empty($record)){
                    
                    $this->db->insert('storage_cache',
                                      array(
                                          'cache_key'    => $cache_key,
                                          'media_type'   => $this->media_type,
                                          'media_id'     => $this->media_id,
                                          'storage_name' => $name,
                                          'storage_data' => $storage_data,
                                          'status'       => 1,
                                          'changed'      => 'NOW()'
                                      ));
                }else{
                    
                    $this->db->update('storage_cache',
                                      array(
                                          'storage_data' => $storage_data,
                                          'status'       => 1,
                                          'changed'      => 'NOW()',
                                      ),
                                      array('cache_key' => $cache_key));
                    
                }
            }
        }
        
        /*$records_in_cache = $this->db
                                      ->from('storage_cache')
                                      ->where(array(
                                         'media_type' => $this->media_type,
                                         'media_id'   => $this->media_id,
                                         'status'     => 1,
                                      ))
                                      ->get()
                                      ->all();
        
        foreach ($records_in_cache as $record){
            if (!key_exists($record['storage_name'], $storages)){
                $this->db->update('storage_cache',
                                  array('status' => 0),
                                  array('id'     => $record['id']));
            }
        }*/
        
    }
    
    /**
     * Return unique key for cache record by storage name, media type and media id
     *
     * @param string $storage_name
     * @return string unique key for cache record
     */
    private function getCacheKey($storage_name){
        return $storage_name.'_'.$this->media_type.'_'.$this->media_id;
    }
    
    /**
     * Return all keys for cache records for media
     *
     * @return array all cache keys for media
     */
    private function getAllCacheKeys(){
        $keys = array();
        foreach ($this->storages as $name => $storage){
            $keys[] = $this->getCacheKey($name);
        }
        return $keys;
    }
    
    /**
     * wrapper for srorage method, that check media directory
     *
     * @param string $storage_name
     * @param string $media_name
     * @return array content of media directory
     */
    protected function checkMediaDir($storage_name, $media_name){
        try {
            return $this->clients[$storage_name]->checkDir($media_name, $this->media_type);
        }catch (SoapFault $exception){
            $this->parseException($exception);
        }
    }
    
    /**
     * Calculates storage load
     *
     * @param string $storage_name
     * @return int storage load
     */
    protected function getStorageLoad($storage_name){
        if ($this->storages[$storage_name]['max_online'] > 0){
            return $this->getStorageOnline($storage_name) / $this->storages[$storage_name]['max_online'];
        }
        return 1;
    }
    
    /**
     * Return online sessions on storage
     *
     * @param string $storage_name
     * @return int sessions online
     */
    protected function getStorageOnline($storage_name){
        
        /*$sql = 'select count(*) as sd_online from users where now_playing_type=2 and hd_content=0 and storage_name="'.$storage_name.'" and UNIX_TIMESTAMP(keep_alive)>'.(time() - 120);
        $sd_online = $this->db->executeQuery($sql)->getValueByName(0, 'sd_online');*/
        
        $sd_online = $this->db->select('count(*) as sd_online')
                              ->from('users')
                              ->where(
                                  array(
                                      'now_playing_type' => 2,
                                      'hd_content'       => 0,
                                      'storage_name'     => $storage_name,
                                      'UNIX_TIMESTAMP(keep_alive)>' => time() - 120,
                                  ))
                              ->get()
                              ->first('sd_online');
        
        /*$sql = 'select count(*) as hd_online from users where now_playing_type=2 and hd_content=1 and storage_name="'.$storage_name.'" and UNIX_TIMESTAMP(keep_alive)>'.(time() - 120);
        $hd_online = $this->db->executeQuery($sql)->getValueByName(0, 'hd_online');*/
        
        $hd_online = $this->db->select('count(*) as hd_online')
                              ->from('users')
                              ->where(
                                  array(
                                      'now_playing_type' => 2,
                                      'hd_content'       => 1,
                                      'storage_name'     => $storage_name,
                                      'UNIX_TIMESTAMP(keep_alive)>' => time() - 120,
                                  ))
                              ->get()
                              ->first('hd_online'); 
        
        return $sd_online + 3*$hd_online;
    }
    
    /**
     * Return media name
     *
     * @return string
     */
    abstract protected function getMediaName();
    
    /**
     * Return media params from db
     *
     * @param int $media_id
     * @return array
     */
    protected function getMediaParams($media_id){
        
        $media_params = $this->db->from($this->db_table)
                                  ->where(array('id' => $media_id))
                                  ->get()
                                  ->first();
        
        if (!empty($media_params)){
            if (!empty($media_params['rtsp_url'])){
                $this->rtsp_url = $media_params['rtsp_url'];
            }
        }
        
        return $media_params;
    }
    
    /**
     * Increment counter of storage deny
     *
     * @param string $storage_name
     */
    private function incrementStorageDeny($storage_name){
        
        //$this->db->executeQuery('update storage_deny set counter=counter+1, updated=NOW() where name="'.$storage_name.'"');
        
        $storage = $this->db->from('storage_deny')->where(array('name' => $storage_name))->get()->first();
        
        if (empty($storage)){
            $this->db->insert('storage_deny',
                              array(
                                  'name'    => $storage_name,
                                  'counter' => 1,
                                  'updated' => 'NOW()'
                              ));
        }else{
            $this->db->update('storage_deny',
                              array(
                                  'counter' => $storage['counter'] + 1,
                                  'updated' => 'NOW()'
                              ),
                              array('name' => $storage_name));
        }
        
    }
    
    /**
     * Get soap clients for all good storages
     *
     * @return array soap clients for all good storages
     */
    private function getClients(){
        $clients = array();
        foreach ($this->storages as $name => $storage){
            $clients[$name] = new SoapClient('http://localhost'.PORTAL_URI.'server/storage/storage.wsdl.php?id='.$this->storages[$name]['id']);
        }
        return $clients;
    }
    
    /**
     * Sort array of good storages by load
     *
     * @param array $storages
     * @return array good storages sorted by load
     */
    private function sortByLoad($storages){
        
        if (!empty($storages)){
        
            foreach ($storages as $name => $storage) {
                $load[$name] = $storage['load'];
            }
            
            array_multisort($load, SORT_ASC, SORT_NUMERIC, $storages);
        }
        
        return $storages;
    }
    
    /**
     * Save in database information about series for media
     *
     * @param array $series_arr
     */
    protected function saveSeries($series_arr){
        return true;
    }
    
    /**
     * Parse exception, add exception message to output and to master log
     *
     * @param SoapFault $exception
     */
    private function parseException($exception){
        //trigger_error($exception->getMessage()."\n".$exception->getTraceAsString(), E_USER_ERROR);
        echo $exception->getMessage()."\n".$exception->getTraceAsString();
        $this->addToLog($exception->getMessage());
    }
}
?>