<?php

class Recorder extends Storage
{
    /**
     * Start stream recording
     *
     * @param string $url multicast address
     * @param int $rec_id
     * @param int $start_delay
     * @param int $duration
     * @return string record filename
     * @throws Exception
     */
    public function start($url, $rec_id, $start_delay, $duration){
        
        $this->stop($rec_id);

        $filename = intval($rec_id).'_'.date("YmdHis").'.mpg';

        if (!preg_match('/:\/\//', $url, $arr)){
            throw new Exception('URL wrong format');
        }

        $ip   = $arr[1];
        $port = $arr[2];

        if (strpos($url, 'rtp://') !== false ||
            strpos($url, 'udp://') !== false ||
            strpos($url, 'http://') !== false)
        {
            if (defined("ASTRA_RECORDER") && ASTRA_RECORDER){
                exec('astra '.PROJECT_PATH.'/dumpstream.lua'
                    .' -A '.$url
                    .' -s '.$start_delay
                    .' -l '.$duration
                    .' -c '.API_URL.'stream_recorder/'.$rec_id
                    .' -o '.RECORDS_DIR.$filename
                    .' > /dev/null 2>&1 & echo $!'
                    , $out);
            }else{
                exec('nohup python '.PROJECT_PATH.'/dumpstream'
                    .' -a'.$ip
                    .' -p'.$port
                    .' -s'.$start_delay
                    .' -l'.$duration
                    .' -c'.API_URL.'stream_recorder/'.$rec_id
                    .' -o'.RECORDS_DIR.$filename
                    .' > /dev/null 2>&1 & echo $!'
                    , $out);
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

        if (!file_put_contents($this->getRecPidFile($rec_id), $pid)){
            posix_kill($pid, defined("ASTRA_RECORDER") && ASTRA_RECORDER ? 1 : 15);
            throw new IOException('PID file is not created');
        }

        return $filename;
    }

    /**
     * Stop stream recording
     *
     * @param int $rec_id
     * @return bool
     * @throws IOException
     */
    public function stop($rec_id){
        
        $pid_file = $this->getRecPidFile($rec_id);

        if (!is_file($pid_file)){
            return true;
        }

        $pid = intval(file_get_contents($pid_file));

        if (posix_kill($pid, 0)){
            $kill_result = posix_kill($pid, defined("ASTRA_RECORDER") && ASTRA_RECORDER ? 1 : 15);

            if (!$kill_result){
                throw new IOException('Kill pid "'.$pid.'" failed on '.$this->storage_name.': '.posix_strerror(posix_get_last_error()));
            }

            unlink($pid_file);
            return $kill_result;

        }else{
            unlink($pid_file);
            return true;
        }
    }

    public function updateStopTime($rec_id, $stop_time){

        $pid_file = $this->getRecPidFile($rec_id);

        if (!is_file($pid_file)){
            return true;
        }

        $pid = intval(file_get_contents($pid_file));

        if (posix_kill($pid, 0)){

            $kill_result = posix_kill($pid, 14);

            if (!$kill_result){
                throw new IOException('Send signal to pid "'.$pid.'" failed on '.$this->storage_name.': '.posix_strerror(posix_get_last_error()));
            }

            return $kill_result;

        }else{
            return true;
        }
    }

    /**
     * Delete recorded file
     *
     * @param string $filename
     * @return bool
     */
    public function delete($filename){
        
        return @unlink(RECORDS_DIR.basename($filename));
    }
    
    /**
     * Construct pid filename
     *
     * @param int $rec_id
     * @return string
     */
    private function getRecPidFile($rec_id){

        return '/tmp/rec_'.$this->storage_name.'_'.$rec_id.'.pid';
    }
}
