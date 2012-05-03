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
        return $storages;
    }

    protected function getMediaName(){

        return Mysql::getInstance()->from('rec_files')->where(array('id' => $this->media_params['file_id']))->get()->first('file_name');
    }

    public function startDeferred($program_id){

        $epg = Mysql::getInstance()
            ->select('*, UNIX_TIMESTAMP(time) as start_ts, UNIX_TIMESTAMP(time_to) as stop_ts')
            ->from('epg')
            ->where(array('real_id' => $program_id))
            ->get()
            ->first();

        $channel = Mysql::getInstance()->from('itv')->where(array('id' => intval($epg['ch_id'])))->get()->first();

        $user_rec_id = $this->createUserRecord($channel, false, $epg['time']);

        if (!$user_rec_id){
            return false;
        }

        $start_rec_task = array(
            'id'     => $user_rec_id,
            'job'    => 'start',
            'time'   => $epg['start_ts']
        );

        $daemon = new RESTClient(Config::get('daemon_api_url'));
        
        try{
            $start = $daemon->resource('recorder_task')->create($start_rec_task);
        }catch (RESTClientException $e){
            $this->deleteUserRecord($user_rec_id);
            echo $e->getMessage();
            throw new Exception($e->getMessage());
        }

        if ($start){

            /*if (($epg['stop_ts'] - $epg['start_ts']) > Config::get('record_max_length') * 60){
                $epg['stop_ts'] = $epg['start_ts'] + Config::get('record_max_length') * 60;
            }*/

            $stop_rec_task = array(
                'id'     => $user_rec_id,
                'job'    => 'stop',
                'time'   => $epg['stop_ts']
            );

            $daemon = new RESTClient(Config::get('daemon_api_url'));
            $stop = $daemon->resource('recorder_task')->create($stop_rec_task);

            if (!$stop){
                $daemon = new RESTClient(Config::get('daemon_api_url'));
                $daemon->resource('recorder_task')->ids($user_rec_id)->delete();

                $this->deleteUserRecord($user_rec_id);

                return false;
            }

            return $user_rec_id;
        }

        $this->deleteUserRecord($user_rec_id);

        return false;
    }                       

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
            $event->sendMsg(_('Starting recording') . ' — ' . $user_record['program'] . ' ' .  _('on channel') . ' ' . $channel['name']);
        }

        return $file_record;
    }

    public function startNow($channel){

        $is_recording = Mysql::getInstance()->from('users_rec')->where(array('ch_id' => $channel['id'], 'uid' => $this->stb->id, 'ended' => 0, 'started' => 1))->get()->first();

        if (!empty($is_recording)){
            return false;
        }

        return $this->createUserRecord($channel);
    }

    private function createUserRecord($channel, $auto_start = true, $start_time = 0){

        $rest_length = $this->checkTotalUserRecordsLength($this->stb->id);

        if ($rest_length <= 0){
            return false;
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
            $program = $epg->getCurProgram($channel['id']);

            $data['program'] = $program['name'];
            $data['t_start'] = 'NOW()';
            $data['started'] = 1;
        }else{
            $program = $epg->getProgramByChannelAndTime($channel['id'], $start_time);

            $length = strtotime($program['time_to']) - strtotime($program['time']);

            if ($length < 0){
                //$length = 0;
                return false;
            }

            if ($rest_length - $length <= 0 ){
                return false;
            }

            $data['program']    = $program['name'];
            $data['program_id'] = $program['id'];
            $data['program_real_id'] = $program['real_id'];
            $data['t_start']    = $start_time;
            $data['length']     = $length;
            $data['t_stop']     = date("Y-m-d H:i:s", strtotime($start_time) + $length);
        }

        $user_rec_id = Mysql::getInstance()->insert('users_rec', $data)->insert_id();

        //$now_recording = Mysql::getInstance()->from('rec_files')->where(array('ch_id' => $channel['id'], 'ended' => 0))->get()->first();

        /*if ($now_recording){

            Mysql::getInstance()->update('users_rec', array('file_id' => $now_recording['id']), array('id' => $user_rec_id));
        }else{*/

        if (!$auto_start){
            return $user_rec_id;
        }else{

            if ($rest_length/60 - Config::get('record_max_length') < 0){
                $length = $rest_length;
            }else{
                $length = Config::get('record_max_length')*60;
            }
            
            $stop_rec_task = array(
                'id'     => $user_rec_id,
                'job'    => 'stop',
                'time'   => $length + time()
            );

            $t_start = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first('t_start');

            $t_stop = date("Y-m-d H:i:s", strtotime($t_start) + $length);

            Mysql::getInstance()->update('users_rec', array('length' => $length, 't_stop' => $t_stop), array('id' => $user_rec_id));

            $daemon = new RESTClient(Config::get('daemon_api_url'));
            try{
                $result = $daemon->resource('recorder_task')->create($stop_rec_task);
            }catch (RESTClientException $e){
                $this->deleteUserRecord($user_rec_id);
                echo $e->getMessage();
                throw new Exception($e->getMessage());
                //return false;
            }
        }

        return $this->createFileRecord($user_rec_id);
        //}

        //return $user_rec_id;
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

        //var_dump($this->storages);

        foreach ($this->storages as $name => $storage){

            if ($storage['load'] < 1){

                try {
                    //$file_name = $this->clients[$name]->startRecording($channel['mc_cmd'], $rec_file_id);
                    $file_name = $this->clients[$name]->resource('recorder')->create(array('url' => $channel['mc_cmd'], 'rec_id' => $rec_file_id));
                }catch (Exception $exception){
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

        Mysql::getInstance()->update('users_rec', array('file_id' => $rec_file_id, 'started' => 1), array('id' => $user_rec_id));
        
        return $user_rec_id;
    }

    public function stop($user_rec_id){

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
        $file_record = Mysql::getInstance()->from('rec_files')->where(array('id' => $user_record['file_id']))->get()->first();

        /*Mysql::getInstance()->update('users_rec',
                                     array(
                                         't_stop'  => 'NOW()',
                                         'ended'   => 1,
                                         'length'  => '(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(t_start))'
                                     ),
                                     array(
                                         'id' => $user_rec_id,
                                     ));*/

        /*$active_recordings = Mysql::getInstance()->from('users_rec')->where(array('ch_id' => $record['ch_id'], 'ended' => 0))->get()->count();
        if (empty($active_recordings) ){*/

        $daemon = new RESTClient(Config::get('daemon_api_url'));
        $daemon->resource('recorder_task')->ids($user_rec_id)->delete();

        Mysql::getInstance()->update('users_rec',
                                 array(
                                     't_stop'  => 'NOW()',
                                     'ended'   => 1,
                                     'length'  => '(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(t_start))'
                                 ),
                                 array(
                                     'id' => $user_rec_id,
                                 ));


        if ($user_record['started']){

            Mysql::getInstance()->update('rec_files',
                                         array(
                                             't_stop' => 'NOW()',
                                             'ended'  => 1,
                                             'length' => '(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(t_start))'
                                         ),
                                         array(
                                             'id' => $file_record['id'],
                                         ));

            //return $this->clients[$file_record['storage_name']]->stopRecording($file_record['id']);
            var_dump($file_record, $this->clients);
            return $this->clients[$file_record['storage_name']]->resource('recorder')->ids($file_record['id'])->update();
        }
        //}

        return true;
    }

    public function stopDeferred($user_rec_id, $duration_minutes){

        $user_record = Mysql::getInstance()->select('*, UNIX_TIMESTAMP(t_start) as start_ts')->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();

        $rest_length = $this->checkTotalUserRecordsLength($this->stb->id);

        $rest_length += $user_record['length'];

        if ($rest_length/60 - $duration_minutes <= 0){
            $duration_minutes = $rest_length/60;
        }

        //var_dump($rest_length, $duration_minutes);

        $stop_time = intval($user_record['start_ts'] + $duration_minutes*60);

        $daemon = new RESTClient(Config::get('daemon_api_url'));
        $update_result = $daemon->resource('recorder_task')->ids($user_rec_id)->update(array('job' => 'stop', 'time' => $stop_time));

        Mysql::getInstance()->update('users_rec', array('t_stop' => date("Y-m-d H:i:s", $stop_time), 'length' => $duration_minutes*60), array('id' => $user_rec_id));

        if ($update_result){
            return $stop_time;
        }

        return false;
    }

    public function stopAndUsrMsg($user_rec_id){

        $stopped = $this->stop($user_rec_id);

        if ($stopped){
            $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $user_rec_id))->get()->first();
            $channel     = Itv::getChannelById($user_record['ch_id']);

            $event = new SysEvent();
            $event->setUserListById($user_record['uid']);
            $event->sendMsg(_('Stopped recording') . ' — ' . $user_record['program'] . ' ' .  _('on channel') . ' ' . $channel['name']);
        }
        
        return $stopped;
    }

    public function checkStatus($channel){
        
    }

    public function del($rec_id){
        
        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $rec_id))->get()->first();

        //Mysql::getInstance()->delete('users_rec', array('id' => $rec_id));
        $this->deleteUserRecord($rec_id);

        if (!$user_record['ended']){
            $daemon = new RESTClient(Config::get('daemon_api_url'));
            $daemon->resource('recorder_task')->ids($rec_id)->delete();
        }

        $related_records = Mysql::getInstance()->from('users_rec')->where(array('file_id' => $user_record['file_id']))->get()->all();

        if (!empty($related_records)){
            return true;
        }

        try {
            //return $this->clients[$rec_file['storage_name']]->deleteRecords($rec_file['file_name']);

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
            /*->select('users_rec.*, UNIX_TIMESTAMP(epg.time) as start_ts, UNIX_TIMESTAMP(epg.time_to) as stop_ts')
            ->from('users_rec')
            ->join('epg', 'epg.id', 'users_rec.program_id', 'LEFT')*/
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

    public function getDeferredRecordIdsForUser($uid){

        $user_recs = Mysql::getInstance()->select('id, program_id, program_real_id')->from('users_rec')->where(array('uid' => $uid))->get()->all();

        $rec_ids = array();

        foreach ($user_recs as $record){
            $rec_ids[$record['program_real_id']] = $record['id'];
        }

        return $rec_ids;
    }

    private function getTotalUserRecordsLength($uid){

        return Mysql::getInstance()->select('SUM(length) as total_length')->from('users_rec')->where(array('uid' => $uid))->get()->first('total_length');
    }

    public function checkTotalUserRecordsLength($uid){

        //var_dump(Config::get('total_records_length')*60, $this->getTotalUserRecordsLength($uid));

        $length = (int) (Config::get('total_records_length')*60 - $this->getTotalUserRecordsLength($uid));

        if ($length < 0){
            $length = 0;
        }

        return $length;
    }
}

?>