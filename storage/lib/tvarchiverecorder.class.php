<?php

class TvArchiveRecorder extends Storage
{

    /**
     * Start stream recording
     *
     * @param array $task
     * @return bool
     */
    public function start($task){

        $url = $task['cmd'];

        preg_match('/:\/\/([\d\.]+):(\d+)/', $url, $arr);

        $ip   = $arr[1];
        $port = $arr[2];

        $pid_file = $this->getRecPidFile($task['ch_id']);

        if (file_exists($pid_file)){
            if ($this->processExist($pid_file)){
                return false;
            }else{
                unlink($pid_file);
            }
        }

        if (strpos($url, 'rtp://') !== false || strpos($url, 'udp://') !== false){
            //var_dump('nohup python '.PROJECT_PATH.'/dumpstream -a'.$ip.' -p'.$port.' -d'.$this->getRecordsPath($task).' -n'.$task['parts_number'].' -c'.TASKS_API_URL.$task['ch_id'].' > /dev/null 2>&1 & echo $!');
            exec('nohup python '.PROJECT_PATH.'/dumpstream -a'.$ip.' -p'.$port.' -d'.$this->getRecordsPath($task).' -n'.$task['parts_number'].' -c'.TASKS_API_URL.$task['ch_id'].' > /dev/null 2>&1 & echo $!', $out);
        }else{
            throw new DomainException('Not supported protocol');
        }

        if (intval($out[0]) == 0){
            $arr = explode(' ', $out[0]);
            $pid = intval($arr[1]);
        }else{
            $pid = intval($out[0]);
        }

        if (empty($pid)){
            throw new OutOfRangeException('Not possible to get pid');
        }

        if (!file_put_contents($pid_file, $pid)){
            posix_kill($pid, 15);
            throw new IOException('PID file is not created');
        }

        return true;
    }

    /**
     * Start all tasks
     *
     * @param array $tasks
     * @return bool
     */
    public function startAll($tasks){

        if (empty($tasks)){
            return false;
        }

        foreach($tasks as $task){
            $this->start($task);
        }

        return true;
    }

    /**
     * Stop stream recording
     *
     * @param int|string $ch_id
     * @return bool
     */
    public function stop($ch_id){
        
        $pid_file = $this->getRecPidFile($ch_id);

        if (!is_file($pid_file)){
            return true;
        }

        $pid = intval(file_get_contents($pid_file));

        unlink($pid_file);

        return posix_kill($pid, 15);
    }
    
    /**
     * Construct pid filename
     *
     * @param int $ch_id
     * @return string
     */
    private function getRecPidFile($ch_id){

        return '/tmp/rec_archive_'.STORAGE_NAME.'_'.$ch_id.'.pid';
    }

    /**
     * Return save dir for records
     *
     * @param array $task
     * @return string
     */
    private function getRecordsPath($task){

        $dir = RECORDS_DIR.'/archive/'.$task['ch_id'].'/';

        if (!is_dir($dir)){
            umask(0);
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    private function processExist($pid_file){

        $pid = trim(file_get_contents($pid_file));

        return posix_kill($pid, 0);
    }
}

?>
