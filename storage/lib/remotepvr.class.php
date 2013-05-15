<?php

class RemotePvr extends Storage
{
    public function __construct(){
        parent::__construct();
    }

    public function checkMedia($name){
        $result = array();

        $result['series'] = array();
        $result['series_file'] = array();
        $result['files']  = array();

        if (is_file(RECORDS_DIR.$name)){
            $result['files'][] = array('name' => $name, 'md5' => '');
        }else{
            throw new IOException('File '.RECORDS_DIR.$name.' not exist on '.$this->storage_name);
        }

        return $result;
    }

    public function createLink($media_file, $media_id){
        $this->user->checkHome();

        preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $media_file, $arr);

        $ext = $arr[2];

        $from = RECORDS_DIR.$media_file;

        $to = NFS_HOME_PATH.$this->user->getMac().'/'.$media_id.'.'.$ext;

        $link_result = @symlink($from, $to);

        if (!$link_result){
            throw new IOException('Could not create link '.$from.' to '.$to.' on '.$this->storage_name);
        }

        if (!is_readable($to)){
            throw new IOException('File '.$to.' is not readable on '.$this->storage_name);
        }

        return true;
    }
}

?>