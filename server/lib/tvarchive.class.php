<?php

class TvArchive extends Master
{

    public function __construct(){

        $this->media_type = 'tv_archive';
        $this->db_table   = 'tv_archive';

        parent::__construct();
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

        $task_id = Mysql::getInstance()->insert('tv_archive', array('ch_id' => $ch_id, 'storage_name' => $less_loaded))->insert_id();

        if (!$task_id){
            return false;
        }

        $channel = Itv::getChannelById($ch_id);

        $task = array(
            'id'    => $task_id,
            'ch_id' => $channel['id'],
            'cmd'   => $channel['cmd']
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

    public function getAllTasks($storage_name){

        return Mysql::getInstance()
            ->select('tv_archive.id as id, itv.id as ch_id, itv.cmd as cmd')
            ->from('tv_archive')
            ->join('itv', 'itv.id', 'tv_archive.ch_id', 'LEFT')
            ->where(array('storage_name' => $storage_name))
            ->get()
            ->all();
    }

    public function updateStartTime($ch_id, $time){

        return Mysql::getInstance()->update('tv_archive', array('start_time' => date("Y-m-d H:i:s", $time)), array('ch_id' => intval($ch_id)));
    }

    public function updateEndTime($ch_id, $time){

        return Mysql::getInstance()->update('tv_archive', array('end_time' => date("Y-m-d H:i:s", $time)), array('ch_id' => intval($ch_id)));
    }
}

?>