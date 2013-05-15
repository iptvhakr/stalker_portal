<?php

class Logger
{
    private $file_prefix = '';
    private $save_path   = '/var/log/stalkerd/';

    public function __construct(){}

    public function access($log_msg){
        $this->write($this->save_path.$this->file_prefix."access.log", $log_msg);
    }

    public function error($log_msg){
        $this->write($this->save_path.$this->file_prefix."error.log", $log_msg);
    }

    private function write($file, $text){
        file_put_contents($file, $text, FILE_APPEND | LOCK_EX);
    }

    public function setPrefix($prefix){
        $this->file_prefix = $prefix;
    }
}

?>