<?php

class TvReminder implements \Stalker\Lib\StbApi\TvReminder
{
    
    private $db;
    private $stb;
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->stb = Stb::getInstance();
    }
    
    public function add(){
        
        $ch_id      = @intval($_REQUEST['ch_id']);
        $program_id = $_REQUEST['program_id'];
        $fire_ts  = @intval($_REQUEST['fire_ts']);
        $program_name = $_REQUEST['program_name'];

        $memo = $this->db->from('tv_reminder')->where(array('mac' => $this->stb->mac, 'tv_program_real_id' => $program_id))->get()->first();
        
        if (!empty($memo)){
            return false;
        }

        $channel = Itv::getById($ch_id);

        if (empty($channel)){
            $dvb_channels = Itv::getInstance()->getDvbChannels();

            foreach ($dvb_channels as $dvb_channel){
                if ($dvb_channel['id'] == $ch_id){
                    $channel = $dvb_channel;
                    break;
                }
            }
        }

        if (empty($channel)){
            return false;
        }

        if (isset($channel['type']) && $channel['type'] == 'dvb'){

            $program = array(
                'id'      => 0,
                'real_id' => $program_id,
                'time'    => date("Y-m-d H:i:s", $fire_ts)
            );

        }else{
            $program = Epg::getByRealId($program_id);

            if (empty($program)){
                return false;
            }
        }
        
        $id = $this->db->insert('tv_reminder',
                                 array(
                                     'mac'           => $this->stb->mac, 
                                     'ch_id'         => $ch_id,
                                     'tv_program_id' => $program['id'],
                                     'tv_program_real_id' => $program['real_id'],
                                     'fire_time'     => $program['time'],
                                     'tv_program_name' => $program_name,
                                     'added'         => 'NOW()'
                                 ))->insert_id();
        
        if (intval($id) <= 0){
            return false;
        }
        
        return $this->getRaw()->where(array('tv_reminder.id' => $id))->get()->first();
    }
    
    private function getRaw(){
        
        return $this->db->select('UNIX_TIMESTAMP(`fire_time`) as fire_ts, TIME_FORMAT(`fire_time`,"%H:%i") as t_fire_time, itv.name as itv_name, itv.id as ch_id, epg.name as program_name, tv_program_id, tv_program_real_id')
                        ->from('tv_reminder')
                        ->join('epg', 'tv_reminder.tv_program_real_id', 'epg.real_id', 'INNER')
                        ->join('itv', 'tv_reminder.ch_id', 'itv.id', 'INNER');
    }

    public function getAllActive(){

        $memos =  $this->getRaw()->where(array('tv_reminder.mac' => $this->stb->mac, 'tv_reminder.fire_time>' => 'NOW()'))->get()->all();

        $dvb_memos = $this->db->select('UNIX_TIMESTAMP(`fire_time`) as fire_ts, TIME_FORMAT(`fire_time`,"%H:%i") as t_fire_time, ch_id, tv_program_id, tv_program_real_id, tv_program_name as program_name')
                              ->from('tv_reminder')
                              ->where(array(
                                           'tv_reminder.mac' => $this->stb->mac,
                                           'tv_reminder.fire_time>' => 'NOW()',
                                           'tv_reminder.tv_program_id' => 0
                                      ))->get()->all();

        $dvb_channels_map = array();
        $dvb_channels = Itv::getInstance()->getDvbChannels();

        foreach ($dvb_channels as $dvb_channel){
            $dvb_channels_map[$dvb_channel['id']] = $dvb_channel;
        }

        $dvb_memos = array_map(function($memo) use ($dvb_channels_map){

            if (!empty($dvb_channels_map[$memo['ch_id']])){
                $memo['itv_name'] = $dvb_channels_map[$memo['ch_id']]['name'];
            }

            return $memo;
        }, $dvb_memos);

        $memos = array_merge($memos, $dvb_memos);

        return $memos;
    }

    public function getAllActiveForMac($mac){

        $all = $this->getRaw()->where(array('tv_reminder.mac' => $mac, 'tv_reminder.fire_time>' => 'NOW()'))->get()->all();
        
        $reminders = array();

        foreach ($all as $memo){
            $reminders[$memo['tv_program_real_id']] = $memo;
        }

        return $reminders;
    }
    
    public function del(){
        
        $program_id = $_REQUEST['program_id'];
        
        return $this->db->delete('tv_reminder', array('tv_program_real_id' => $program_id, 'mac' => $this->stb->mac));
    }
}

?>