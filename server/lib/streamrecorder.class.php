<?php

class StreamRecorder extends Master
{
    public function __construct(){
        
        $this->media_type = 'remote_pvr';
        $this->db_table   = 'users_rec';
        
        parent::__construct();
    }

    protected function getAllActiveStorages(){
        
        $storages = array();

        $data = $this->db->from('storages')->where(array('status' => 1, 'for_records' => 1))->get()->all();

        foreach ($data as $idx => $storage){
            $storages[$storage['storage_name']] = $storage;
            $storages[$storage['storage_name']]['load'] = $this->getStorageLoad($storage);
        }

        $storages = $this->sortByLoad($storages);

        var_dump($storages);

        return $storages;
    }

    protected function getMediaName(){

        return Mysql::getInstance()->from('rec_files')->where(array('id' => $this->media_params['file_id']))->get()->first('file_name');
    }

    public function startDeferred($program_id, $on_stb = false, $program = null){

        if ($program && empty($program_id)){
            $epg = $program;
        }else{
            $epg = Mysql::getInstance()
                ->select('*, UNIX_TIMESTAMP(time) as start_ts, UNIX_TIMESTAMP(time_to) as stop_ts')
                ->from('epg')
                ->where(array('real_id' => $program_id))
                ->get()
                ->first();
        }

        $channel = Mysql::getInstance()->from('itv')->where(array('id' => intval($epg['ch_id'])))->get()->first();

        $user_rec_id = $this->createUserRecord($channel, false, $epg['time'], $on_stb, $program);

        if (!$user_rec_id){
            return false;
        }

        return $user_rec_id;
    }

    public function setStarted($file_id){

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('file_id' => $file_id))->get()->first();

        if (empty($user_record) || $user_record['ended']){
            return false;
        }

        $data = array(
            't_start' => 'NOW()',
            'started' => 1
        );

        Mysql::getInstance()->update('users_rec', $data, array('file_id' => $file_id));

        $channel = Itv::getChannelById($user_record['ch_id']);

        if (!empty($user_record['program_real_id'])){
            $event = new SysEvent();
            $event->setUserListById($user_record['uid']);
            $event->setAutoHideTimeout(300);
            User::clear();
            $user = User::getInstance((int) $user_record['uid']);
            $event->sendMsg($user->getLocalizedText('Starting recording') . ' — ' . $user_record['program'] . ' ' .  $user->getLocalizedText('on channel') . ' ' . $channel['name']);
        }

        return true;
    }

    public function setEnded($file_id){

        $user_rec_id = Mysql::getInstance()->from('users_rec')->where(array('file_id' => $file_id))->get()->first('id');

        if (empty($user_rec_id)){
            return false;
        }

        $stopped = $this->stop($user_rec_id, false);

        if ($stopped){
            $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
            $channel     = Itv::getChannelById($user_record['ch_id']);

            $event = new SysEvent();
            $event->setUserListById($user_record['uid']);
            User::clear();
            $user = User::getInstance((int) $user_record['uid']);
            $event->sendMsg($user->getLocalizedText('Stopped recording') . ' — ' . $user_record['program'] . ' ' .  $user->getLocalizedText('on channel') . ' ' . $channel['name']);
        }

        return $stopped;
    }

    /**
     * @deprecated
     * @param $user_rec_id
     * @return bool
     */
    public function startDeferredNow($user_rec_id){

        $user_rec_id = intval($user_rec_id);

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();

        if ($user_record['ended']){

            $this->deleteUserRecord($user_rec_id);

            return false;
        }

        $data = array(
            't_start' => 'NOW()',
            'started' => 1
        );

        Mysql::getInstance()->update('users_rec', $data, array('id' => $user_rec_id));

        $file_record =  $this->createFileRecord($user_rec_id);

        if ($file_record){

            $channel = Itv::getChannelById($user_record['ch_id']);

            $event = new SysEvent();
            $event->setUserListById($user_record['uid']);
            $event->setAutoHideTimeout(300);

            $user = User::getInstance((int) $user_record['uid']);
            $event->sendMsg($user->getLocalizedText('Starting recording') . ' — ' . $user_record['program'] . ' ' .  $user->getLocalizedText('on channel') . ' ' . $channel['name']);
        }

        return $file_record;
    }

    public function startNow($channel, $on_stb = false){

        if (!$on_stb){
            $is_recording = Mysql::getInstance()->from('users_rec')->where(array('ch_id' => $channel['id'], 'uid' => $this->stb->id, 'ended' => 0, 'started' => 1))->get()->first();

            if (!empty($is_recording)){
                return false;
            }
        }

        return $this->createUserRecord($channel, true, 0, $on_stb);
    }

    private function createUserRecord($channel, $auto_start = true, $start_time = 0, $on_stb = false, $virtual_program = null){

        if (!$on_stb){
            $rest_length = $this->checkTotalUserRecordsLength($this->stb->id);

            if ($rest_length <= 0){
                return false;
            }
        }else{
            $rest_length = 0;
        }

        preg_match("/vtrack:(\d+)/", $channel['mc_cmd'], $vtrack_arr);
        preg_match("/atrack:(\d+)/", $channel['mc_cmd'], $atrack_arr);

        $vtrack = '';
        $atrack = '';

        if (count($vtrack_arr) > 0){
            $vtrack = intval($vtrack_arr[1]);
        }

        if (count($atrack_arr)){
            $atrack = intval($atrack_arr[1]);
        }

        $data = array(
             'ch_id'   => $channel['id'],
             'uid'     => $this->stb->id,
             'atrack'  => $atrack,
             'vtrack'  => $vtrack,
        );

        $epg = new Epg();

        if ($auto_start){

            if ($rest_length/60 - Config::get('record_max_length') < 0){
                $length = $rest_length;
            }else{
                $length = Config::get('record_max_length')*60;
            }

            if ($length < 0){
                return false;
            }

            $program = $epg->getCurProgram($channel['id']);

            $data['program'] = $program['name'];
            $data['t_start'] = 'NOW()';
            $data['started'] = 1;
            $data['local']   = (int) $on_stb;
            $data['length']  = $length;
            $data['t_stop']  = date("Y-m-d H:i:s", time() + $length);
        }else{
            $program = $epg->getProgramByChannelAndTime($channel['id'], $start_time);

            if ($virtual_program && is_array($virtual_program) && !array_key_exists('id', $virtual_program)){
                $program['time']    = $virtual_program['time'];
                $program['time_to'] = $virtual_program['time_to'];
                $start_time = $program['time'];
            }elseif ($virtual_program){
                $virtual_program['name'] = $program['name'];
                $program = $virtual_program;
            }

            $length = strtotime($program['time_to']) - strtotime($program['time']);

            if ($length < 0){
                return false;
            }

            if (!$on_stb && ($rest_length - $length <= 0) ){
                return false;
            }

            $data['program']    = $program['name'];
            $data['program_id'] = $program['id'];
            $data['program_real_id'] = $program['real_id'];
            $data['t_start']    = $start_time;
            $data['length']     = $length;
            $data['t_stop']     = date("Y-m-d H:i:s", strtotime($start_time) + $length);
            $data['local']      = (int) $on_stb;
        }

        $user_rec_id = Mysql::getInstance()->insert('users_rec', $data)->insert_id();

        if ($on_stb){
            return $user_rec_id;
        }else{

            $t_start = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first('t_start');

            $t_stop = date("Y-m-d H:i:s", strtotime($t_start) + $length);

            Mysql::getInstance()->update('users_rec', array('length' => $length, 't_stop' => $t_stop), array('id' => $user_rec_id));
        }

        return $this->createFileRecord($user_rec_id);
    }

    private function deleteUserRecord($user_rec_id){

        return Mysql::getInstance()->delete('users_rec', array('id' => $user_rec_id));
    }

    private function deleteFileRecord($rec_file_id){

        return Mysql::getInstance()->delete('rec_files', array('id' => $rec_file_id));
    }

    private function createFileRecord($user_rec_id){

        $user_rec = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();

        if (empty($user_rec)){
            return false;
        }

        $channel  = Mysql::getInstance()->from('itv')->where(array('id' => $user_rec['ch_id']))->get()->first();

        if (empty($channel)){
            return false;
        }

        $rec_file_id = Mysql::getInstance()->insert('rec_files',
            array(
                'ch_id'    => $user_rec['ch_id'],
                't_start'  => 'NOW()',
                'atrack'   => $user_rec['atrack'],
                'vtrack'   => $user_rec['vtrack'],
            ))->insert_id();

        $allowed_storages = array_keys(RemotePvr::getStoragesForChannel($user_rec['ch_id']));

        foreach ($this->storages as $name => $storage){

            if (!in_array($name, $allowed_storages)){
                continue;
            }

            if ($storage['load'] < 1){

                try {
                    $file_name = $this->clients[$name]
                        ->resource('recorder')
                        ->create(array(
                                      'url'         => $channel['mc_cmd'],
                                      'rec_id'      => $rec_file_id,
                                      'start_delay' => strtotime($user_rec['t_start']) - time(),
                                      'duration'    => $user_rec['length']
                                 )
                        );
                }catch (Exception $exception){

                    try{
                        //stop recording just in case
                        $this->clients[$name]->resource('recorder')->ids($rec_file_id)->update();
                    }catch(Exception $exception){
                        $this->parseException($exception);
                    }

                    $this->deleteUserRecord($user_rec_id);
                    $this->deleteFileRecord($rec_file_id);
                    $this->parseException($exception);
                }
            }

            if (!empty($file_name)){
                break;
            }
        }

        if (empty($file_name)){
            Mysql::getInstance()->update('users_rec', array('ended' => 1), array('id' => $user_rec_id));
            Mysql::getInstance()->update('rec_files', array('ended' => 1), array('id' => $rec_file_id));
            return false;
        }

        Mysql::getInstance()->update('rec_files',
            array(
                'storage_name' => $name,
                'file_name'    => $file_name
            ),
            array('id' => $rec_file_id));

        Mysql::getInstance()->update('users_rec',
            array(
                 'file_id' => $rec_file_id,
                 'started' => strtotime($user_rec['t_start']) - time() > 0 ? 0 : 1
            ),
            array('id' => $user_rec_id));
        
        return $user_rec_id;
    }

    public function stop($user_rec_id, $send_to_storage = true){

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
        $file_record = Mysql::getInstance()->from('rec_files')->where(array('id' => $user_record['file_id']))->get()->first();

        Mysql::getInstance()->update('users_rec',
                                 array(
                                     't_stop'  => 'NOW()',
                                     'ended'   => 1,
                                     'length'  => time() - strtotime($user_record['t_start'])
                                 ),
                                 array(
                                     'id' => $user_rec_id,
                                 ));

        if ($user_record['started']){

            Mysql::getInstance()->update('rec_files',
                                         array(
                                             't_stop' => 'NOW()',
                                             'ended'  => 1,
                                             'length' => time() - strtotime($file_record['t_start'])
                                         ),
                                         array(
                                             'id' => $file_record['id'],
                                         ));

            if ($send_to_storage){
                try{
                    return $this->clients[$file_record['storage_name']]->resource('recorder')->ids($file_record['id'])->update();
                }catch(Exception $exception){
                    $this->parseException($exception);
                    return false;
                }
            }
        }

        return true;
    }

    public function stopDeferred($user_rec_id, $duration_minutes){

        $user_record = Mysql::getInstance()->select('*, UNIX_TIMESTAMP(t_start) as start_ts')->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
        $file_record = Mysql::getInstance()->from('rec_files')->where(array('id' => $user_record['file_id']))->get()->first();

        $rest_length = $this->checkTotalUserRecordsLength($this->stb->id);

        $rest_length += $user_record['length'];

        if ($rest_length/60 - $duration_minutes <= 0){
            $duration_minutes = $rest_length/60;
        }

        $stop_time = intval($user_record['start_ts'] + $duration_minutes*60);

        try{
            // update stop time via SIGNAL to dumpstream process on storage
            $this->clients[$file_record['storage_name']]
                ->resource('recorder')
                ->ids($file_record['id'])
                ->update(array("stop_time" => $stop_time));
        }catch(Exception $exception){
            $this->parseException($exception);
        }

        Mysql::getInstance()->update('users_rec', array('t_stop' => date("Y-m-d H:i:s", $stop_time), 'length' => $duration_minutes*60), array('id' => $user_rec_id));

        return $stop_time;
    }

    /**
     * @deprecated
     * @param $user_rec_id
     * @return bool
     */
    public function stopAndUsrMsg($user_rec_id){

        $stopped = $this->stop($user_rec_id);

        if ($stopped){
            $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
            $channel     = Itv::getChannelById($user_record['ch_id']);

            $event = new SysEvent();
            $event->setUserListById($user_record['uid']);

            $user = User::getInstance((int) $user_record['uid']);
            $event->sendMsg($user->getLocalizedText('Stopped recording') . ' — ' . $user_record['program'] . ' ' .  $user->getLocalizedText('on channel') . ' ' . $channel['name']);
        }
        
        return $stopped;
    }

    public function checkStatus($channel){
        
    }

    public function del($rec_id){
        
        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $rec_id))->get()->first();

        $this->deleteUserRecord($rec_id);

        $related_records = Mysql::getInstance()->from('users_rec')->where(array('file_id' => $user_record['file_id']))->get()->all();

        if (!empty($related_records)){
            return true;
        }

        try {

            if (empty($user_record['file_id'])){
                return true;
            }

            $rec_file = Mysql::getInstance()->from('rec_files')->where(array('id' => $user_record['file_id']))->get()->first();

            if (!empty($rec_file)){
                return $this->clients[$rec_file['storage_name']]->resource('recorder')->ids($rec_file['file_name'])->delete();
            }
        }catch (Exception $exception){
            $this->parseException($exception);
        }

        return false;
    }

    public function getAllDeferredRecords(){
        return Mysql::getInstance()
            ->select('users_rec.*, UNIX_TIMESTAMP(t_start) as start_ts, UNIX_TIMESTAMP(t_stop) as stop_ts')
            ->from('users_rec')
            ->where(array('ended' => 0))
            ->get()
            ->all();
    }

    public function getTasks(){

        $deferred_records = $this->getAllDeferredRecords();

        $tasks = array();

        foreach ($deferred_records as $record){
            if (!$record['started']){
                $tasks[] = array('id' => $record['id'], 'job' => 'start', 'time' => $record['start_ts']);
            }
            $tasks[] = array('id' => $record['id'], 'job' => 'stop',  'time' => $record['stop_ts']);
        }

        return $tasks; 
    }

    public function getRecordingInfo($file_id){

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('file_id' => $file_id))->get()->first();

        if (empty($user_record)){
            return null;
        }

        return array(
            'id' => $user_record['id'],
            'start' => strtotime($user_record['t_start']),
            'stop'  => strtotime($user_record['t_stop'])
        );
    }

    public function getDeferredRecordIdsForUser($uid){

        $user_recs = Mysql::getInstance()->select('id, program_id, program_real_id')->from('users_rec')->where(array('uid' => $uid))->get()->all();

        $rec_ids = array();

        foreach ($user_recs as $record){
            $rec_ids[$record['program_real_id']] = $record['id'];
        }

        return $rec_ids;
    }

    private function getTotalUserRecordsLength($uid){

        return Mysql::getInstance()->select('SUM(length) as total_length')->from('users_rec')->where(array('uid' => $uid, 'local' => 0))->get()->first('total_length');
    }

    public function checkTotalUserRecordsLength($uid){

        $length = (int) (Config::get('total_records_length')*60 - $this->getTotalUserRecordsLength($uid));

        if ($length < 0){
            $length = 0;
        }

        return $length;
    }
}

?>