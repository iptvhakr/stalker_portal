<?php

class Recorder extends Storage
{
    /**
     * Start stream recording
     *
     * @param string $url multicast address
     * @param int $rec_id
     * @return string record filename
     */
    public function start($url, $rec_id){
        
        $this->stop($rec_id);

        $filename = $rec_id.'_'.date("YmdHis").'.mpg';

        preg_match('/:\/\/([\d\.]+):(\d+)/', $url, $arr);

        $ip   = $arr[1];
        $port = $arr[2];

        /*if (strpos($url, 'rtp://') !== false){
            //exec('nohup dumprtp '.$ip.' '.$port.' > '.RECORDS_DIR.$filename.' 2>error.log & echo $!', $out);
            exec('nohup dumprtp '.$ip.' '.$port.' > '.RECORDS_DIR.$filename.' 2>/dev/null & echo $!', $out);
        }elseif(strpos($url, 'udp://') !== false){
            //exec('nohup udpdump '.$ip.' '.$port.' > '.RECORDS_DIR.$filename.' 2>&1 & echo $!', $out);
            exec('nohup udpdump '.$ip.' '.$port.' > '.RECORDS_DIR.$filename.' 2>/dev/null & echo $!', $out);
        }else{
            throw new DomainException('Not supported protocol');
        }*/

        if (strpos($url, 'rtp://') !== false || strpos($url, 'udp://') !== false){
            exec('nohup python '.PROJECT_PATH.'/dumpstream -a'.$ip.' -p'.$port.' > '.RECORDS_DIR.$filename.' 2>/dev/null & echo $!', $out);
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
            posix_kill($pid, 15);
            throw new IOException('PID file is not created');
        }

        return $filename;
    }

    /**
     * Stop stream recording
     *
     * @param int $rec_id
     * @return bool
     */
    public function stop($rec_id){
        
        $pid_file = $this->getRecPidFile($rec_id);

        if (!is_file($pid_file)){
            return true;
        }

        $pid = intval(file_get_contents($pid_file));

        if (posix_kill($pid, 0)){
            $kill_result = posix_kill($pid, 15);

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

    /**
     * Delete recorded file
     *
     * @param string $filename
     * @return bool
     */
    public function delete($filename){
        
        return @unlink(RECORDS_DIR.$filename);
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

?>