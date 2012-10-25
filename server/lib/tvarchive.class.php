<?php

class TvArchive extends Master
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

        $task = $this->getTaskByChId($program['ch_id']);

        $overlap = Config::getSafe('tv_archive_playback_overlap', 0) * 60;

        $tz = new DateTimeZone(Stb::$server_timezone);

        $date = new DateTime(date('r', strtotime($program['time'])));
        $date->setTimeZone($tz);

        $date_to = new DateTime(date('r', strtotime($program['time_to'])));
        $date_to->setTimeZone($tz);

        $start_timestamp = $date->getTimestamp();
        $stop_timestamp  = $date_to->getTimestamp() + $overlap;

        $channel = Itv::getChannelById($program['ch_id']);

        //$filename = date("Ymd-H", $start_timestamp);
        $filename = $date->format("Ymd-H");

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }

        /*if ($channel['wowza_dvr']){

            $position = $start_timestamp - (time() - Config::get('tv_archive_parts_number') * 3600);

            $res['cmd'] = $channel['mc_cmd']
                        . 'position:' . $position;
            
        }else{*/

        $storage = Master::getStorageByName($task['storage_name']);

        if ($storage['wowza_server']){
            $storage['storage_ip'] = empty($storage['archive_stream_server']) ? $storage['storage_ip'] : $storage['archive_stream_server'];
        }

        $res['storage_id'] = $storage['id'];

        $position = date("i", $start_timestamp) * 60;

        $res['cmd'] = 'ffmpeg http://' . $storage['storage_ip']
                    . '/archive/'
                    . $program['ch_id']
                    . '/'
                    . $filename
                    . ' position:' . $position;
        /*}*/

        $res['cmd'] .= ' media_len:' . ($stop_timestamp - $start_timestamp);

        $res['download_cmd'] = 'http://' . $storage['storage_ip'] . ':88'
            . '/stalker_portal/storage/get.php?filename=' . $filename
            . '&ch_id=' . $program['ch_id']
            . '&start=' . $position
            . '&duration=' . ($stop_timestamp - $start_timestamp)
            . '&osd_title=' . urlencode($channel['name'].' — '.$program['name'])
            . '&real_id=' . $program['real_id'];

        if (!$channel['wowza_dvr']){
            $res['cmd'] = $res['download_cmd'];
        }

        $res['to_file'] = date("Ymd-H", $start_timestamp).'_'.System::transliterate($channel['name'].'_'.$program['name']).'.mpg';

        var_dump($res);

        return $res;
    }

    public function getUrlByProgramId($program_id){

        $program = Epg::getById($program_id);

        $task = $this->getTaskByChId($program['ch_id']);

        $overlap = Config::getSafe('tv_archive_playback_overlap', 0) * 60;

        if (Stb::$server_timezone){

            $tz = new DateTimeZone(Stb::$server_timezone);

            $date = new DateTime(date('r', strtotime($program['time'])));
            $date->setTimeZone($tz);

            $date_to = new DateTime(date('r', strtotime($program['time_to'])));
            $date_to->setTimeZone($tz);

            $start_timestamp = $date->getTimestamp();
            $stop_timestamp  = $date_to->getTimestamp() + $overlap;

        }else{

            $start_timestamp = strtotime($program['time']);
            $stop_timestamp  = strtotime($program['time_to']) + $overlap;
        }

        $channel = Itv::getChannelById($program['ch_id']);

        $filename = date("Ymd-H", $start_timestamp);

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }

        $storage = Master::getStorageByName($task['storage_name']);

        $position = date("i", $start_timestamp) * 60;

        $channel = Itv::getChannelById($program['ch_id']);

        return 'http://' . $storage['storage_ip'] . ':88'
            . '/stalker_portal/storage/get.php?filename=' . $filename
            . '&ch_id=' . $program['ch_id']
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
            return false;
        }

        $next = Mysql::getInstance()->from('epg')->where(array('ch_id' => $program['ch_id'], 'time>' => $program['time']))->orderby('time')->limit(1)->get()->first();

        if (empty($next)){
            return false;
        }

        return $this->getUrlByProgramId($next['id']);
    }

    /**
     * Return link for current channel and current time
     *
     * @return string
     */
    public function getLinkForChannel(){

        $ch_id = intval($_REQUEST['ch_id']);

        $task = $this->getTaskByChId($ch_id);
        $storage = Master::getStorageByName($task['storage_name']);

        //$channel = Itv::getChannelById($ch_id);

        $position = date("i") * 60 + intval(date("s"));

        $channel = Itv::getChannelById($ch_id);

        $filename = date("Ymd-H");

        if ($channel['wowza_dvr']){
            $filename .= '.mp4';
        }else{
            $filename .= '.mpg';
        }

        return 'ffmpeg http://' . $storage['storage_ip']
                             . '/archive/'
                             . $ch_id
                             . '/'
                             . $filename
                             . ' position:' . $position
                             . ' media_len:' . (intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s")));
    }

    private function getTaskByChId($ch_id){
        
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();
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

    protected function getStorageLoad($storage){

        $total_tasks = count($this->getAllTasks());

        if ($total_tasks == 0){
            return 0;
        }

        return count($this->getAllTasks($storage['storage_name'])) / count($this->getAllTasks());
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

        $exist_task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();

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

        $task = array(
            'id'             => $task_id,
            'ch_id'          => $channel['id'],
            'cmd'            => $channel['mc_cmd'],
            'parts_number'   => Config::get('tv_archive_parts_number')
        );

        return $this->clients[$storage_name]->resource('tv_archive_recorder')->create(array('task' => $task));
    }

    public function deleteTask($ch_id){

        $task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();

        if (empty($task)){
            return true;
        }

        Mysql::getInstance()->delete('tv_archive', array('ch_id' => $ch_id));

        if (array_key_exists($task['storage_name'], $this->storages) && $this->storages[$task['storage_name']]['fake_tv_archive'] == 1){
            return true;
        }

        return $this->clients[$task['storage_name']]->resource('tv_archive_recorder')->ids($ch_id)->delete();
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

        $range = $this->getArchiveRange()*3600;

        $archive_start = date("Y-m-d H:00:00", time() - $range);
        $archive_end   = date("Y-m-d H:00:00");

        $raw_tasks = Mysql::getInstance()
            ->select('tv_archive.id as id, itv.id as ch_id, itv.mc_cmd as cmd, enable_tv_archive & wowza_dvr as wowza_archive, UNIX_TIMESTAMP("'.$archive_start.'") as start_timestamp, UNIX_TIMESTAMP("'.$archive_end.'") as stop_timestamp')
            ->from('tv_archive')
            ->join('itv', 'itv.id', 'tv_archive.ch_id', 'LEFT')
            ->where($where);

        if (!empty($fake_storages)){
            $raw_tasks = $raw_tasks->in('storage_name', $fake_storages, true);
        }

        $raw_tasks = $raw_tasks->get()->all();

        foreach ($raw_tasks as $task){
            $task['parts_number'] = Config::get('tv_archive_parts_number');
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

    public static function getArchiveRange($ch_id = 0){
        return (int) Config::get('tv_archive_parts_number');
    }

    public static function getTaskByChannelId($ch_id){
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();
    }
}

?>