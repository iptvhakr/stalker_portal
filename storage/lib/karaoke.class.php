<?php

class Karaoke extends Storage
{
    public function __construct(){
        parent::__construct();
    }

    public function checkMedia($name){
        $result = array();

        $result['series'] = array();
        $result['series_file'] = array();
        $result['files']  = array();

        //var_dump(KARAOKE_STORAGE_DIR.$name);

        if (is_file(KARAOKE_STORAGE_DIR.$name)){
            $result['files'][] = array('name' => $name, 'md5' => '');
        }

        return $result;
    }

    public function createLink($media_file, $media_id, $proto = ''){

        $this->user->checkHome();

        preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $media_file, $arr);
        $ext = $arr[2];

        $from = KARAOKE_STORAGE_DIR.'/'.$media_file;
        $to = NFS_HOME_PATH.$this->user->getMac().'/'.$media_id.'.'.$ext;

        if ($proto == 'http'){
            $link_result = @symlink($from, $to);
        }else{
            $link_result = @link($from, $to);
        }

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