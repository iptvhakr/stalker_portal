<?php

class TvArchive extends Master implements \Stalker\Lib\StbApi\TvArchive
{

    public function __construct(){

        $this->media_type = 'tv_archive';
        $this->db_table   = 'tv_archive';

        parent::__construct();
    }

    /**
     * Return link for program
     *
     * @return array
     */
    public function createLink(){

        $res = array(
            'id'         => 0,
            'cmd'        => '',
            'storage_id' => '',
            'load'       => '0',
            'error'      => ''
        );
        
        preg_match("/\/media\/(\d+).mpg/", $_REQUEST['cmd'], $tmp_arr);

        $program_id = $tmp_arr[1];

        $program = Epg::getById($program_id);

        try{
            $task = $this->getLessLoadedTaskByChId($program['ch_id']);
        }catch (StorageSessionLimitException $e){
            $res['error']        = 'limit';
            $res['storage_name'] = $e->getStorageName();
            return $res;
        }

        $overlap = Config::getSafe('tv_archive_playback_overlap', 0) * 60;

        $overlap_start = Config::getSafe('tv_archive_playback_overlap_start', 0) * 60;

        $tz = new DateTimeZone(Stb::$server_timezone);

        $date = new DateTime(date('r', strtotime($program['time']) - $overlap_start));
        $date->setTimeZone($tz);

        $date_to = new DateTime(date('r', strtotime($program['time_to'])));
        $date_to->setTimeZone($tz);

        $start_timestamp = $date->getTimestamp();
        $stop_timestamp  = $date_to->getTimestamp() + $overlap;

        $channel = Itv::getChannelById($program['ch_id']);

        $filename = $date->format("Ymd-H");

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }

        $storage = Master::getStorageByName($task['storage_name']);

        if ($storage['wowza_server']){
            $storage['storage_ip'] = empty($storage['archive_stream_server']) ? $storage['storage_ip'] : $storage['archive_stream_server'];
        }

        $res['storage_id'] = $storage['id'];

        $position = date("i", $start_timestamp) * 60;

        if ($channel['flussonic_dvr']){

            if (preg_match("/:\/\/([^\/]*)\/([^\/]*).*(mpegts|m3u8)$/", $channel['mc_cmd'], $match)){

                if ($match[3] == 'mpegts'){
                    $res['cmd'] = 'http://'.$storage['storage_ip'].'/'.$match[2].'/archive/'.$start_timestamp.'/'.($stop_timestamp - $start_timestamp).'/mpegts';
                }else{
                    $res['cmd'] = preg_replace('/:\/\/([^\/]*)/', '://'.$storage['storage_ip'], $channel['mc_cmd']);
                    $res['cmd'] = preg_replace('/\.m3u8/', '-' . $start_timestamp
                        . '-' . ($stop_timestamp - $start_timestamp) . '.m3u8' ,$res['cmd']);
                }

                $res['download_cmd'] = 'http://'.$storage['storage_ip'].'/'.$match[2].'/archive-'.$start_timestamp.'-'.($stop_timestamp - $start_timestamp).'.ts';

            }else{
                $res['error'] = 'link_fault';
            }

        }else{
            $res['cmd'] = $res['download_cmd'] = Config::getSafe('tv_archive_player_solution', 'ffmpeg').' http://' . $storage['storage_ip'] . ':' . $storage['apache_port']
                . '/stalker_portal/storage/get.php?filename=' . $filename
                . '&ch_id=' . $program['ch_id']
                . '&token='.$this->createTemporaryToken()
                . '&start=' . $position
                . '&duration=' . ($stop_timestamp - $start_timestamp)
                . '&osd_title=' . urlencode($channel['name'].' — '.$program['name'])
                . '&real_id=' . $program['real_id'];
        }

        $res['to_file'] = date("Ymd-H", $start_timestamp).'_'.System::transliterate($channel['name'].'_'.$program['name']).'.mpg';

        var_dump($res);

        return $res;
    }

    private function createTemporaryToken(){

        $key = md5(mktime(1).uniqid());

        $cache = Cache::getInstance();

        $result = $cache->set($key, true, 0, 28800); // 28800 -  8 hours

        if ($result){
            return $key;
        }else{
            return $result;
        }
    }

    public static function checkTemporaryToken($token){
        return Cache::getInstance()->get($token);
    }

    private function createTemporaryTimeShiftToken($url){

        $key = md5($url.time().uniqid());

        $cache = Cache::getInstance();

        $result = $cache->set($key, $url, 0, 28800); // 28800 -  8 hours

        if ($result){
            return $key;
        }else{
            return $result;
        }
    }

    public static function checkTemporaryTimeShiftToken($key){
        return Cache::getInstance()->get($key);
    }

    public function getUrlByProgramId($program_id, $disable_overlap = false, $program = array()){

        if (empty($program)){
            $program = Epg::getById($program_id);
        }

        $task = $this->getLessLoadedTaskByChId($program['ch_id']);

        $overlap = Config::getSafe('tv_archive_playback_overlap', 0) * 60;

        $overlap_start = Config::getSafe('tv_archive_playback_overlap_start', 0) * 60;

        $tz = new DateTimeZone(Stb::$server_timezone);

        $date = new DateTime(date('r', strtotime($program['time']) - ($disable_overlap ? -$overlap : $overlap_start))); // previous part overlap compensation
        $date->setTimeZone($tz);

        $date_to = new DateTime(date('r', strtotime($program['time_to'])));
        $date_to->setTimeZone($tz);

        $start_timestamp = $date->getTimestamp();
        $stop_timestamp  = $date_to->getTimestamp() + $overlap;

        $channel = Itv::getChannelById($program['ch_id']);

        $filename = $date->format("Ymd-H");

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }

        $storage = Master::getStorageByName($task['storage_name']);

        $position = date("i", $start_timestamp) * 60;

        $channel = Itv::getChannelById($program['ch_id']);

        return 'http://' . $storage['storage_ip'] . ':' . $storage['apache_port']
            . '/stalker_portal/storage/get.php?filename=' . $filename
            . '&ch_id=' . $program['ch_id']
            . '&token='.$this->createTemporaryToken()
            . '&start=' . $position
            . '&duration=' . ($stop_timestamp - $start_timestamp)
            . '&osd_title=' . urlencode($channel['name'].' — '.$program['name'])
            . '&real_id=' . $program['real_id'];
    }

    public function getNextPartUrl(){

        $program_id = $_REQUEST['id'];

        if (!$program_id){
            return false;
        }

        $program = Epg::getByRealId($program_id);

        if (empty($program)){

            if (preg_match("/(\d+)_(\d+)/", $program_id, $match)){

                $next = Mysql::getInstance()
                    ->from('epg')
                    ->where(
                        array(
                             'ch_id' => (int) $match[1],
                             'time>' => date("Y-m-d H:i:s", (int) $match[2])
                        )
                    )
                    ->orderby('time')
                    ->limit(1)
                    ->get()
                    ->first();

            }else{
                return false;
            }
        }else{
            $next = Mysql::getInstance()->from('epg')->where(array('ch_id' => $program['ch_id'], 'time>' => $program['time']))->orderby('time')->limit(1)->get()->first();
        }

        if (empty($next)){
            return false;
        }

        try{

            if ($next['time'] != $program['time_to'] && !isset($match)){

                $program = array(
                    'name'    => '['._('Break in the program').']',
                    'ch_id'   => $next['ch_id'],
                    'time'    => $program['time_to'],
                    'time_to' => $next['time'],
                    'real_id' => $next['ch_id'].'_'.strtotime($program['time_to'])
                );

                return $this->getUrlByProgramId(0, true, $program);

            }else{
                return $this->getUrlByProgramId($next['id'], true);
            }

        }catch (StorageSessionLimitException $e){
            return false;
        }
    }

    /**
     * Return link for current channel and current time
     *
     * NGINX config:
     *
     * location /tslink/ {
     *
     *     rewrite ^/tslink/(.+)/archive/(\d+)/(.+) /stalker_portal/server/api/chk_tmp_timeshift_link.php?key=$1&file=$3 last;
     *
     *     proxy_set_header Host 192.168.1.71; # <- portal ip
     *     proxy_set_header X-Real-IP $remote_addr;
     *     proxy_pass http://192.168.1.71:88/; # <- portal ip
     * }
     *
     * location /archive/ {
     *     root /var/www/bb1;
     *     internal;
     * }
     *
     * @return string
     */
    public function getLinkForChannel(){

        $ch_id = intval($_REQUEST['ch_id']);

        $task = $this->getLessLoadedTaskByChId($ch_id, true);

        $storage = Master::getStorageByName($task['storage_name']);

        //$channel = Itv::getChannelById($ch_id);

        $tz = new DateTimeZone(Stb::$server_timezone);

        $date = new DateTime(date('r'));
        $date->setTimeZone($tz);

        $position = intval($date->format("i")) * 60 + intval($date->format("s"));

        $channel = Itv::getChannelById($ch_id);

        $filename = $date->format("Ymd-H");

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }


        if (Config::getSafe('enable_timeshift_tmp_link', false)){

            $redirect_url = '/archive/' . $ch_id . '/' . $filename;

            $link_result = $this->createTemporaryTimeShiftToken($redirect_url);

            return 'ffmpeg http://' . $storage['storage_ip']
                . '/tslink/'.$link_result
                . '/archive/'
                . $ch_id
                . '/'
                . $filename
                . ' position:' . $position
                . ' media_len:' . (intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s")));
        }else{

            return 'ffmpeg http://' . $storage['storage_ip']
                . '/archive/'
                . $ch_id
                . '/'
                . $filename
                . ' position:' . $position
                . ' media_len:' . (intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s")));
        }
    }

    /**
     * @deprecated
     * @param $ch_id
     * @return mixed
     */
    private function getTaskByChId($ch_id){
        
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();
    }

    private function getLessLoadedTaskByChId($ch_id, $ignore_session_limit = false){

        $tasks = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->all();

        $tasks_map = array();

        foreach ($tasks as $task){
            $tasks_map[$task['storage_name']] = $task;
        }

        var_dump($this->storages);

        $all_storages = array_keys($this->storages);
        $task_storages = array_keys($tasks_map);

        $intersection = array_intersect($all_storages, $task_storages);

        $intersection = array_values($intersection);

        if (empty($intersection)){
            return false;
        }

        var_dump($tasks_map, $intersection);

        if ($this->storages[$intersection[0]]['load'] >= 1 && !$ignore_session_limit){
            $this->incrementStorageDeny($intersection[0]);
            throw new StorageSessionLimitException($intersection[0]);
        }

        return $tasks_map[$intersection[0]];
    }

    protected function getAllActiveStorages(){

        $storages = array();

        $data = $this->db->from('storages')->where(array('status' => 1, 'for_records' => 1, 'wowza_server' => 0))->get()->all();

        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
            $storages[$storage['storage_name']]['load'] = $this->getStorageLoad($storage);
        }

        $storages = $this->sortByLoad($storages);

        return $storages;
    }

    protected function getMediaName(){
        return $this->media_id;
    }

    /*protected function getStorageLoad($storage){

        $total_tasks = count($this->getAllTasks());

        if ($total_tasks == 0){
            return 0;
        }

        return count($this->getAllTasks($storage['storage_name'])) / count($this->getAllTasks());
    }*/

    public function setPlayed(){

        return $this->db->insert('played_tv_archive', array(
            'ch_id'    => (int) $_REQUEST['ch_id'],
            'uid'      => $this->stb->id,
            'playtime' => 'NOW()'
        ))->insert_id();
    }

    public function updatePlayedEndTime(){

        $played_tv_archive = Mysql::getInstance()->from('played_tv_archive')->where(array('id' => (int) $_REQUEST['hist_id']))->get()->first();

        if (!empty($played_tv_archive)){

            return Mysql::getInstance()->update('played_tv_archive',
                array(
                     'length' => time() - strtotime($played_tv_archive['playtime'])
                ),
                array(
                     'id' => (int) $_REQUEST['hist_id']
                )
            );
        }

        return false;
    }

    public function setPlayedTimeshift(){
        return $this->db->insert('played_timeshift', array(
            'ch_id'    => (int) $_REQUEST['ch_id'],
            'uid'      => $this->stb->id,
            'playtime' => 'NOW()'
        ))->insert_id();
    }

    public function updatePlayedTimeshiftEndTime(){

        $played_timeshift = Mysql::getInstance()->from('played_timeshift')->where(array('id' => (int) $_REQUEST['hist_id']))->get()->first();

        if (!empty($played_timeshift)){

            return Mysql::getInstance()->update('played_timeshift',
                array(
                     'length' => time() - strtotime($played_timeshift['playtime'])
                ),
                array(
                     'id' => (int) $_REQUEST['hist_id']
                )
            );
        }

        return false;
    }

    public function createTasks($ch_id, $force_storages = array()){

        if (empty($force_storages)){
            return $this->createTask($ch_id);
        }

        $exist_tasks_raw = $this->getAllTasksForChannel($ch_id);
        $exist_tasks = array();

        foreach ($exist_tasks_raw as $task){
            $exist_tasks[$task['storage_name']] = $task;
        }

        $exist_tasks_storages = array_keys($exist_tasks);

        $need_to_delete = array_diff($exist_tasks_storages, $force_storages);
        $need_to_add    = array_diff($force_storages, $exist_tasks_storages);

        if (!empty($need_to_delete)){
            foreach ($need_to_delete as $delete_from_storage){
                $this->deleteTaskById($exist_tasks[$delete_from_storage]['id']);
            }
        }

        $result = true;

        if (!empty($need_to_add)){
            foreach ($need_to_add as $add_to_storage){
                $result = $this->createTask($ch_id, $add_to_storage) && $result;
            }
        }

        return $result;
    }

    public function createTask($ch_id, $force_storage = ''){

        if (empty($this->storages)){
            return false;
        }

        $storage_names = array_keys($this->storages);

        if (!empty($force_storage) && in_array($force_storage, $storage_names)){
            $storage_name = $force_storage;
        }else{
            $storage_name = $storage_names[0];
        }

        $exist_task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id, 'storage_name' => $storage_name))->get()->first();

        if (!empty($exist_task)){
            return true;
        }

        $task_id = Mysql::getInstance()->insert('tv_archive', array('ch_id' => $ch_id, 'storage_name' => $storage_name))->insert_id();

        if (!$task_id){
            return false;
        }

        if (!empty($force_storage) && array_key_exists($force_storage, $this->storages) && $this->storages[$force_storage]['fake_tv_archive'] == 1){
            return true;
        }

        $channel = Itv::getChannelById($ch_id);

        if ($channel['flussonic_dvr']){
            return true;
        }

        if (preg_match("/(\S+:\/\/\S+)/", $channel['mc_cmd'], $match)){
            $cmd = $match[1];
        }else{
            $cmd = $channel['mc_cmd'];
        }

        $task = array(
            'id'             => $task_id,
            'ch_id'          => $channel['id'],
            'cmd'            => $cmd,
            'parts_number'   => $channel['tv_archive_duration']
        );

        return $this->clients[$storage_name]->resource('tv_archive_recorder')->create(array('task' => $task));
    }

    public function deleteTasks($ch_id){

        $channel_tasks = $this->getAllTasksForChannel($ch_id);

        if (empty($channel_tasks)){
            return true;
        }

        $result = true;

        foreach ($channel_tasks as $task){
            $result = $this->deleteTaskById($task['id']) && $result;
        }

        return $result;
    }

    protected function deleteTaskById($task_id){

        $task = Mysql::getInstance()->from('tv_archive')->where(array('id' => $task_id))->get()->first();

        if (empty($task)){
            return true;
        }

        if (array_key_exists($task['storage_name'], $this->storages) && $this->storages[$task['storage_name']]['fake_tv_archive'] == 0 && $this->storages[$task['storage_name']]['flussonic_server'] == 0){
            $this->clients[$task['storage_name']]->resource('tv_archive_recorder')->ids($task['ch_id'])->delete();
        }

        return Mysql::getInstance()->delete('tv_archive', array('id' => $task_id));
    }

    /**
     * @deprecated
     * @param $ch_id
     * @return bool
     */
    public function deleteTask($ch_id){

        $task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();

        if (empty($task)){
            return true;
        }

        Mysql::getInstance()->delete('tv_archive', array('ch_id' => $ch_id));

        if (array_key_exists($task['storage_name'], $this->storages) && ($this->storages[$task['storage_name']]['fake_tv_archive'] == 1 || $this->storages[$task['storage_name']]['flussonic_server'] == 1)){
            return true;
        }

        return $this->clients[$task['storage_name']]->resource('tv_archive_recorder')->ids($ch_id)->delete();
    }

    public function getAllTasksForChannel($ch_id){
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->all();
    }

    public function getAllTasks($storage_name = null, $not_fake = false){

        if ($storage_name){
            $where = array('storage_name' => $storage_name);
        }else{
            $where = array();
        }

        $fake_storages = array();

        if ($not_fake){
            foreach ($this->storages as $storage_name => $storage){
                if ($storage['fake_tv_archive'] == 1){
                    $fake_storages[] = $storage_name;
                }
            }
        }

        $tasks = array();

        $raw_tasks = Mysql::getInstance()
            ->select('tv_archive.id as id, itv.id as ch_id, itv.mc_cmd as cmd, itv.tv_archive_duration as parts_number')
            ->from('tv_archive')
            ->join('itv', 'itv.id', 'tv_archive.ch_id', 'LEFT')
            ->where($where);

        if (!empty($fake_storages)){
            $raw_tasks = $raw_tasks->in('storage_name', $fake_storages, true);
        }

        $raw_tasks = $raw_tasks->get()->all();

        foreach ($raw_tasks as $task){

            if (preg_match("/(\S+:\/\/\S+)/", $task['cmd'], $match)){
                $task['cmd'] = $match[1];
            }

            $task['ch_id'] = (int) $task['ch_id'];

            $tasks[] = $task;
        }

        return $tasks;
    }

    public function getAllTasksAssoc($storage_name = null){

        $tasks = $this->getAllTasks($storage_name);

        $result = array();

        foreach ($tasks as $task){
            $result[$task['ch_id']] = $task;
        }

        return $result;
    }

    public function updateStartTime($ch_id, $time){

        return Mysql::getInstance()->update('tv_archive', array('start_time' => date("Y-m-d H:i:s", $time)), array('ch_id' => intval($ch_id)));
    }

    public function updateEndTime($ch_id, $time){

        return Mysql::getInstance()->update('tv_archive', array('end_time' => date("Y-m-d H:i:s", $time)), array('ch_id' => intval($ch_id)));
    }

    /**
     * @deprecated
     * @param int $ch_id
     * @return int
     */
    public static function getArchiveRange($ch_id = 0){
        return (int) Config::get('tv_archive_parts_number');
    }

    /**
     * @deprecated
     * @param $ch_id
     * @return mixed
     */
    public static function getTaskByChannelId($ch_id){
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();
    }

    public static function getTasksByChannelId($ch_id){
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->all();
    }

}

?>