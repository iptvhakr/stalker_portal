<?php

class TvArchiveRecorder extends Storage
{

    /**
     * Start stream recording
     *
     * @param array $task
     * @return bool
     * @throws Exception
     */
    public function start($task){

        $url = $task['cmd'];

        if (!preg_match('/:\/\//', $url, $arr)){
            throw new Exception('URL wrong format');
        }

        $ip   = $arr[1];
        $port = $arr[2];

        $task['ch_id'] = (int) $task['ch_id'];

        $pid_file = $this->getRecPidFile($task['ch_id']);

        if (file_exists($pid_file)){
            if ($this->processExist($pid_file)){
                return false;
            }else{
                unlink($pid_file);
            }
        }

        if (strpos($url, 'rtp://') !== false || strpos($url, 'udp://') !== false){

            $path = $this->getRecordsPath($task);

            if ($path && is_dir($path)){
                if (defined("ASTRA_RECORDER") && ASTRA_RECORDER){
                    exec('astra '.PROJECT_PATH.'/dumpstream.lua'
                        .' -A '.$url
                        .' -d '.$path
                        .' -n '.intval($task['parts_number'])
                        .' > /dev/null 2>&1 & echo $!'
                        , $out);
                }else{
                    exec('nohup python '.PROJECT_PATH.'/dumpstream'
                        .' -a'.$ip
                        .' -p'.$port
                        .' -d'.$path
                        .' -n'.intval($task['parts_number'])
                        .' > /dev/null 2>&1 & echo $!'
                        , $out);
                }
            }else{
                throw new Exception('Wrong archive path or permission denied for new folder');
            }

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

        $archive = new TvArchiveTasks();
        $archive->add($task);

        return true;
    }

    /**
     * Start all tasks
     *
     * @param array $tasks
     * @return bool
     */
    public function startAll($tasks){

        if (!is_array($tasks)){
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

        $archive = new TvArchiveTasks();
        $archive->del($ch_id);

        return posix_kill($pid, 9);
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
     * @return string|false
     */
    private function getRecordsPath($task){

        $dir = RECORDS_DIR.'/archive/'.$task['ch_id'].'/';

        if (!is_dir($dir)){
            umask(0);

            if (!mkdir($dir, 0777, true)){
                return false;
            }
        }

        return $dir;
    }

    private function processExist($pid_file){

        $pid = trim(file_get_contents($pid_file));

        return posix_kill($pid, 0);
    }
}

?>
