<?php

class RemotePvr extends AjaxResponse implements \Stalker\Lib\StbApi\RemotePvr
{
    public function __construct(){
        parent::__construct();
    }

    public function createLink(){

        preg_match("/\/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);

        $media_id = $tmp_arr[1];

        $item = self::getById($media_id);

        if ($item['local']){
            return array(
                'cmd' => $item['file'],
                'local' => 1
            );
        }

        $res = $this->getLinkByRecId($media_id);

        var_dump($res);

        return $res;
    }

    private function getLinkByRecId($rec_id){

        $item = self::getById($rec_id);

        $master = new StreamRecorder();

        try {
            $res = $master->play($rec_id, 0, false, $item['storage_name']);
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }

        $res['local'] = 0;

        if (!empty($res['cmd'])){
            preg_match("/\.(\w*)$/", $res['cmd'], $ext_arr);
            $res['to_file'] = System::transliterate($item['id'].'_'.Itv::getChannelNameById($item['ch_id']).'_'.$item['program']);
            $res['to_file'] .= '.'.$ext_arr[1];
        }

        if (!empty($_REQUEST['download'])){
            $downloads = new Downloads();
            $res['cmd'] = $downloads->createDownloadLink('pvr', $rec_id, Stb::getInstance()->id);
        }

        return $res;
    }

    public function getUrlByRecId($rec_id){

        $link = $this->getLinkByRecId($rec_id);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
        }

        return $link['cmd'];
    }

    public static function getById($id){
        //return Mysql::getInstance()->from('users_rec')->where(array('id' => intval($id)))->get()->first();
        return Mysql::getInstance()
            ->select('users_rec.*, rec_files.storage_name as storage_name, itv.name as ch_name')
            ->from('users_rec')
            ->join('rec_files', 'users_rec.file_id', 'rec_files.id', 'LEFT')
            ->join('itv', 'users_rec.ch_id', 'itv.id', 'LEFT')
            ->where(array('users_rec.id' => intval($id)))
            ->get()
            ->first();
    }

    /**
     * @return Mysql $this
     */
    public function prepareQuery(){
        return Mysql::getInstance()
            ->select('users_rec.*, itv.name as ch_name, UNIX_TIMESTAMP(t_start) as t_start_ts')
            ->from('users_rec')
            ->join('itv', 'itv.id', 'users_rec.ch_id', 'LEFT')
            ->orderby('t_start', 'DESC')
            ->orderby('t_stop', 'DESC');
    }

    public function getOrderedList(){

        $result = $this->prepareQuery()
            ->where(array('uid' => $this->stb->id))
            ->limit(self::max_page_items, $this->page * self::max_page_items);

        $this->setResponseData($result);

        $recorder = new StreamRecorder();
        $rest_length = $recorder->checkTotalUserRecordsLength($this->stb->id);
        
        $this->response['records_rest_length'] = $rest_length;

        return $this->getResponse('prepareData');
    }

    public function prepareData(){

        for ($i = 0; $i < count($this->response['data']); $i++){

            $this->response['data'][$i]['length']  = System::convertTimeLengthToHuman($this->response['data'][$i]['length']);

            $this->response['data'][$i]['t_start'] = System::convertDatetimeToHuman($this->response['data'][$i]['t_start_ts']);

            if ($this->response['data'][$i]['local']){
                $this->response['data'][$i]['cmd'] = 'auto '.$this->response['data'][$i]['file'];
            }else{
                $this->response['data'][$i]['cmd'] = 'auto /media/'.$this->response['data'][$i]['id'].'.mpg';
            }

            if (!empty($this->response['data'][$i]['program'])){
                $this->response['data'][$i]['ch_name'] .= ' — '.$this->response['data'][$i]['program'];
            }

            $this->response['data'][$i]['name'] = $this->response['data'][$i]['ch_name'];

            //$this->response['data'][$i]['open'] = !$this->response['data'][$i]['ended'];
            $this->response['data'][$i]['open'] = !$this->response['data'][$i]['ended'];

            $this->response['data'][$i]['started'] = intval($this->response['data'][$i]['started']);
            $this->response['data'][$i]['ended']   = intval($this->response['data'][$i]['ended']);

            if ($this->response['data'][$i]['started'] && !$this->response['data'][$i]['ended']){
                $this->response['data'][$i]['length'] = _('recording');
            }elseif (!$this->response['data'][$i]['started'] && !$this->response['data'][$i]['ended']){
                $this->response['data'][$i]['length'] = _('scheduled');
            }
        }

        return $this->response;
    }

    public function startRecDeferred(){

        $response = array();

        try{
            $response['data'] = $this->startRecDeferredById($_REQUEST['program_id']);
        }catch (nPVRException $e){
            $response['error'] = _($e->getMessage());
        }

        return $response;
    }

    public function startRecDeferredById($program_id){

        $recorder = new StreamRecorder();

        return $recorder->startDeferred($program_id);
    }

    public function stopRecDeferred(){

        $rec_id   = intval($_REQUEST['data']);
        $duration = intval($_REQUEST['duration']);

        $recorder = new StreamRecorder();

        return $recorder->stopDeferred($rec_id, $duration);
    }

    public function startRecNow(){

        $response = array();

        try{
            $user_rec_id = $this->startRecNowByChannelId(intval($_REQUEST['ch_id']));
            if ($user_rec_id){
                $response['data'] = $this->getRecordingChIds(true);
            }
        }catch (nPVRException $e){
            $response['error'] = _($e->getMessage());
        }


        return $response;
    }

    public function startRecNowByChannelId($ch_id){
        $channel = Mysql::getInstance()->from('itv')->where(array('id' => $ch_id))->get()->first();

        if (empty($channel)){
            throw new nPVRChannelNotFoundError();
        }

        $recorder = new StreamRecorder();

        $user_rec_id = $recorder->startNow($channel);

        return $user_rec_id;
    }

    public function setInternalId(){
        $rec_id = (int) $_REQUEST['rec_id'];
        $internal_id = $_REQUEST['internal_id'];

        return Mysql::getInstance()->update('users_rec',
            array(
                'internal_id' => $internal_id,
                'started' => 1
            ),
            array('id' => $rec_id)
        );
    }

    public function startDeferredRecordOnStb(){

        $program_id  = $_REQUEST['program_real_id'];
        $file        = $_REQUEST['file'];
        $internal_id = $_REQUEST['internal_id'];
        $ch_id       = (int) $_REQUEST['ch_id'];
        $start_ts    = (int) $_REQUEST['start_ts'];
        $stop_ts     = (int) $_REQUEST['stop_ts'];

        $recorder = new StreamRecorder();

        if ($program_id != 0){
            $rec_exist = Mysql::getInstance()->from('users_rec')->where(array('program_real_id' => $program_id, 'uid' => $this->stb->id))->get()->first();

            if ($rec_exist){
                return $rec_exist['id'];
            }

            $rec_id = $recorder->startDeferred($program_id, true, array(
                'time'    => date("Y-m-d H:i:s", $start_ts),
                'time_to' => date("Y-m-d H:i:s", $stop_ts)
            ));
        }else{
            $program = array(
                'id'      => 0,
                'real_id' => '',
                'ch_id'   => $ch_id,
                'time'    => date("Y-m-d H:i:s", $start_ts),
                'time_to' => date("Y-m-d H:i:s", $stop_ts),
            );

            $rec_id = $recorder->startDeferred($program_id, true, $program);
        }

        if ($rec_id){
            Mysql::getInstance()->update('users_rec',
                array(
                    'file' => $file,
                    'internal_id' => $internal_id

                ),
                array('id' => $rec_id));
        }

        return $rec_id;
    }

    public function startRecordOnStb(){

        $ch_id = intval($_REQUEST['ch_id']);
        $file  = $_REQUEST['file'];
        $start_ts  = (int) $_REQUEST['start_ts'];
        $stop_ts   = (int) $_REQUEST['stop_ts'];
        $internal_id = $_REQUEST['internal_id'];

        $channel = Mysql::getInstance()->from('itv')->where(array('id' => $ch_id))->get()->first();

        if (empty($channel)){
            return false;
        }

        $recorder = new StreamRecorder();

        $rec_id = $recorder->startNow($channel, true);

        if ($rec_id){
            Mysql::getInstance()->update('users_rec',
                array(
                    'file' => $file,
                    't_start' => date("Y-m-d H:i:s", $start_ts),
                    't_stop'  => date("Y-m-d H:i:s", $stop_ts),
                    'length'  => $stop_ts - $start_ts,
                    'internal_id' => $internal_id

                ),
                array('id' => $rec_id));
        }

        return $rec_id;
    }

    public function updateRecordOnStbEndTime(){

        $rec_id  = intval($_REQUEST['rec_id']);
        $stop_ts = intval($_REQUEST['stop_ts']);

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $rec_id))->get()->first();

        if (empty($user_record)){
            return false;
        }

        return Mysql::getInstance()->update(
            'users_rec',
            array(
                't_stop' => date("Y-m-d H:i:s", $stop_ts),
                'length' => $stop_ts - strtotime($user_record['t_start']),
            ),
            array('id' => $rec_id)
        )->result();
    }

    public function stopRecordOnStb(){
        $rec_id = intval($_REQUEST['rec_id']);

        $user_record = Mysql::getInstance()->from('users_rec')->where(array('id' => $rec_id))->get()->first();

        if (empty($user_record)){
            return false;
        }

        return Mysql::getInstance()->update(
            'users_rec',
            array(
                'ended'      => '1',
                'end_record' => 'NOW()',
                'length'     => time() - strtotime($user_record['t_start'])
            ),
            array('id' => $rec_id)
        )->result();
    }

    public function delRecordOnStb(){
        $rec_id = intval($_REQUEST['rec_id']);

        return Mysql::getInstance()->delete(
            'users_rec',
            array('id' => $rec_id)
        );
    }

    public function stopRec(){
        return $this->stopRecById(intval($_REQUEST['rec_id']));
    }

    public function stopRecById($rec_id){
        $recorder = new StreamRecorder();

        return $recorder->stop($rec_id);
    }

    public function getActiveRecordings(){
        return $this->getRecordingChIds();
    }

    public function getRecordingChIds($only_remote = false){

        $fields = 'id, id as real_id, ch_id, local, UNIX_TIMESTAMP(t_start) as t_start_ts, UNIX_TIMESTAMP(t_stop) as t_stop_ts, program, file, program_id, program_real_id, internal_id';

        $remote_recordings = Mysql::getInstance()
            ->select($fields)
            ->from('users_rec')
            ->where(array(
                'uid'     => $this->stb->id,
                'ended'   => 0,
                'started' => 1,
                'local'   => 0
            ))
            ->get()
            ->all();

        if ($only_remote){
            return $remote_recordings;
        }

        Mysql::getInstance()->update(
            'users_rec',
            array(
                'ended'   => 1,
                'started' => 1),
            array(
                'uid'   => $this->stb->id,
                'ended' => 0,
                'local' => 1,
                't_stop<' => 'NOW()'
            )
        );

        $local_recordings = Mysql::getInstance()
            ->select($fields)
            ->from('users_rec')
            ->where(array(
                'uid'     => $this->stb->id,
                'ended'   => 0,
                'local'   => 1
            ))
            ->get()
            ->all();

        return array_merge($remote_recordings, $local_recordings);
    }

    public function delRec(){

        return $this->delRecById(intval($_REQUEST['rec_id']));
    }

    public function delRecById($rec_id){

        $recorder = new StreamRecorder();

        return $recorder->del($rec_id);
    }

    public static function setAllowedStoragesForChannel($ch_id, $storage_names = array()){

        $current_storages = array_keys(self::getStoragesForChannel($ch_id));

        $need_to_delete = array_values(array_diff($current_storages, $storage_names));
        $need_to_add    = array_values(array_diff($storage_names, $current_storages));

        if (!empty($need_to_delete)){
            $quoted_storage_name = array_map(function($name){
                return '"'.$name.'"';
            }, $need_to_delete);
            Mysql::getInstance()->query('delete from pvr_storages where ch_id='.intval($ch_id).' and storage_name in ('.implode(', ', $quoted_storage_name).')');
        }

        if (!empty($need_to_add)){

            $need_to_add = array_map(function($task) use ($ch_id){
                return array(
                    'ch_id'        => $ch_id,
                    'storage_name' => $task
                );
            }, $need_to_add);

            Mysql::getInstance()->insert('pvr_storages', $need_to_add);
        }
    }

    public static function getStoragesForChannel($ch_id){
        $allowed_storages_raw = Mysql::getInstance()->from('pvr_storages')->where(array('ch_id' => $ch_id))->get()->all();

        $allowed_storages = array();

        if ($allowed_storages_raw){
            foreach ($allowed_storages_raw as $task){
                $allowed_storages[$task['storage_name']] = $task;
            }
        }

        return $allowed_storages;
    }
}

abstract class nPVRException extends Exception{}
abstract class nPVRUserException extends nPVRException{}
abstract class nPVRServerException extends nPVRException{}

class nPVRChannelNotFoundError extends nPVRServerException{
    public function __construct(){
        $this->message = _('Channel not found');
    }
}
class nPVRRecordingAlreadyExistError extends nPVRUserException{
    public function __construct(){
        $this->message = _('Recording for this channel already exist');
    }
}
class nPVRTotalLengthLimitError extends nPVRUserException{
    public function __construct(){
        $this->message = _('Recording duration limit is reached');
    }
}
class nPVRServerError extends nPVRServerException{
    public function __construct(){
        $this->message = _('Server error');
    }
}