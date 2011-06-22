<?php

class TvArchiveTasks
{
    private $tasks_api_url;

    public function __construct($tasks_api_url){
        $this->tasks_api_url = $tasks_api_url;
    }

    public function add($task){

        if (!key_exists('id', $task)){
            return false;
        }

        $cached = $this->getCacheFile();

        $tasks = array_map(function($item) use ($task){
            if ($item['id'] == $task['id']){
                return $task;
            }
            return $item;
        }, $cached);

        if (count($cached) != count($tasks)){
            $tasks[] = $task;
        }

        return $this->saveToCache($tasks);
    }

    public function del($ch_id){

        $cached = $this->getCacheFile();

        $tasks = array_filter($cached, function($item) use ($ch_id){
            return $item['ch_id'] != $ch_id;
        });

        return $this->saveToCache($tasks);
    }

    public function sync(){

        $content = file_get_contents($this->tasks_api_url);

        if ($content === false){
            return $this->getFromCache();
        }

        $content = json_decode($content, true);

        if ($content === null || !key_exists('results', $content)){
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