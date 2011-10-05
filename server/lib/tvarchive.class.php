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

        $start_timestamp = strtotime($program['time']);
        $stop_timestamp  = strtotime($program['time_to']);

        $channel = Itv::getChannelById($program['ch_id']);

        if ($channel['wowza_dvr']){

            $position = $start_timestamp - (time() - Config::get('tv_archive_parts_number') * 3600);

            $res['cmd'] = $channel['mc_cmd']
                        . 'position:' . $position;
            
        }else{

            $storage = Master::getStorageByName($task['storage_name']);

            $res['storage_id'] = $storage['id'];

            $position = date("i", $start_timestamp) * 60;

            $res['cmd'] = 'ffmpeg http://' . $storage['storage_ip']
                        . '/archive/'
                        . $program['ch_id']
                        . '/'
                        . date("Ymd-H", $start_timestamp)
                        . '.mpg position:' . $position;
        }

        $res['cmd'] .= ' media_len:' . ($stop_timestamp - $start_timestamp);

        var_dump($res);

        return $res;
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

        return 'ffmpeg http://' . $storage['storage_ip']
                             . '/archive/'
                             . $ch_id
                             . '/'
                             . date("Ymd-H")
                             . '.mpg position:' . $position
                             . ' media_len:' . (intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s")));
    }

    private function getTaskByChId($ch_id){
        
        return Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();
    }

    protected function getAllActiveStorages(){

        $storages = array();

        $data = $this->db->from('storages')->where(array('status' => 1, 'for_records' => 1))->get()->all();

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

    public function createTask($ch_id){

        $storage_names = array_keys($this->storages);
        $less_loaded = $storage_names[0];

        $exist_task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();

        if (!empty($exist_task)){
            return true;
        }

        $task_id = Mysql::getInstance()->insert('tv_archive', array('ch_id' => $ch_id, 'storage_name' => $less_loaded))->insert_id();

        if (!$task_id){
            return false;
        }

        $channel = Itv::getChannelById($ch_id);

        $task = array(
            'id'             => $task_id,
            'ch_id'          => $channel['id'],
            'cmd'            => empty($channel['mc_cmd']) ?  $channel['cmd'] : $channel['mc_cmd'],
            'parts_number'   => Config::get('tv_archive_parts_number')
        );

        return $this->clients[$less_loaded]->resource('tv_archive_recorder')->create(array('task' => $task));
    }

    public function deleteTask($ch_id){

        $task = Mysql::getInstance()->from('tv_archive')->where(array('ch_id' => $ch_id))->get()->first();

        if (empty($task)){
            return true;
        }

        Mysql::getInstance()->delete('tv_archive', array('ch_id' => $ch_id));

        return $this->clients[$task['storage_name']]->resource('tv_archive_recorder')->ids($ch_id)->delete();
    }

    public function getAllTasks($storage_name = null){

        if ($storage_name){
            $where = array('storage_name' => $storage_name);
        }else{
            $where = array();
        }

        $tasks = array();

        $raw_tasks = Mysql::getInstance()
            ->select('tv_archive.id as id, itv.id as ch_id, itv.cmd as cmd, UNIX_TIMESTAMP(tv_archive.start_time) as start_timestamp, UNIX_TIMESTAMP(tv_archive.end_time) as stop_timestamp')
            ->from('tv_archive')
            ->join('itv', 'itv.id', 'tv_archive.ch_id', 'LEFT')
            ->where($where)
            ->get()
            ->all();

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
}

?>