<?php

class WowzaTvArchive extends TvArchive
{
    public function __construct(){

        parent::__construct();
    }

    protected function getAllActiveStorages(){

        $storages = array();

        $data = $this->db->from('storages')->where(array('status' => 1, 'for_records' => 1, 'wowza_server' => 1))->get()->all();

        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
            $storages[$storage['storage_name']]['load'] = $this->getStorageLoad($storage);
            $storages[$storage['storage_name']]['storage_ip'] = $storage['storage_ip'].":8086";
        }

        $storages = $this->sortByLoad($storages);

        return $storages;
    }
}

?>