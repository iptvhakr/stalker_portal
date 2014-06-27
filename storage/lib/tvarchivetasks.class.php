<?php

class TvArchiveTasks
{
    private static $tasks_api_url;

    public function __construct(){}

    public function setApiUrl($tasks_api_url){
        self::$tasks_api_url = $tasks_api_url;
    }

    public function add($task){

        if (!array_key_exists('id', $task)){
            return false;
        }

        $cached = $this->getFromCache();

        $need_to_add = true;

        $tasks = array_map(function($item) use ($task, &$need_to_add){
            if ($item['id'] == $task['id']){
                $need_to_add = false;
                return $task;
            }
            return $item;
        }, $cached);

        if ($need_to_add){
            $tasks[] = $task;
        }

        return $this->saveToCache($tasks);
    }

    public function del($ch_id){

        $cached = $this->getFromCache();

        $tasks = array_values(array_filter($cached, function($item) use ($ch_id){
            return $item['ch_id'] != $ch_id;
        }));

        return $this->saveToCache($tasks);
    }

    public function sync(){

        $content = file_get_contents(self::$tasks_api_url);

        if ($content === false){
            return $this->getFromCache();
        }

        $content = json_decode($content, true);

        if ($content === null || !array_key_exists('results', $content)){
            return false;
        }

        $content = $content['results'];

        $this->saveToCache($content);

        return $content;
    }

    public function getAll(){

        return $this->sync();
    }

    private function getCacheFile(){
        return realpath(dirname(__FILE__).'/../').'/.tasks';
    }

    private function getFromCache(){

        $tasks = file_get_contents($this->getCacheFile());

        if ($tasks === false){
            return null;
        }

        return json_decode($tasks, true);
    }

    private function saveToCache($tasks){
        
        return file_put_contents($this->getCacheFile(), json_encode($tasks));
    }
}

?>