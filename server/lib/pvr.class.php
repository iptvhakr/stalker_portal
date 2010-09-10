<?php

class Pvr extends AjaxResponse
{
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){
        parent::__construct();
    }
    
    public function getNewId(){
        
        $ch_id = intval($_REQUEST['ch_id']);
        
        $ch_item = $this->db->from('itv')->where(array('id' => $ch_id))->get()->first();
        
        if (empty($ch_item)){
            return 0;
        }
        
        $vtrack = '';
        $atrack = '';
        
        preg_match("/vtrack:(\d+)/", $ch_item['cmd'], $vtrack_arr);
        preg_match("/atrack:(\d+)/", $ch_item['cmd'], $atrack_arr);
        
        if ($vtrack_arr[1]){
            $vtrack = $vtrack_arr[1];
        }
        
        if ($atrack_arr[1]){
            $atrack = $atrack_arr[1];
        }
        
        return $this->db->insert('rec_files',
                          array(
                              'ch_id'   => $ch_id,
                              't_start' => 'NOW()',
                              'atrack'  => $atrack,
                              'vtrack'  => $vtrack,
                              'uid'     => $this->stb->id,
                          ))->insert_id;
    }
    
    public function getOrderedList(){
        
        $result = $this->db
                        ->select('rec_files.*, itv.name as ch_name, UNIX_TIMESTAMP(t_start) as t_start_ts')
                        ->from('rec_files')
                        ->join('itv', 'itv.id', 'rec_files.ch_id', 'LEFT')
                        ->where(array('uid' => $this->stb->id))
                        ->orderby('t_stop', 'DESC')
                        ->orderby('t_start', 'DESC');
                        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            $this->response['data'][$i]['length'] = $this->convertTimeLengthToHuman($this->response['data'][$i]['length']);
            
            $this->response['data'][$i]['t_start'] = $this->convertDatetimeToHuman($this->response['data'][$i]['t_start_ts']);
            
            $this->response['data'][$i]['cmd'] = 'auto /media/usbdisk/'.$this->response['data'][$i]['id'].'.ts';
            
            $this->response['data'][$i]['name'] = $this->response['data'][$i]['t_start'].' '.$this->response['data'][$i]['ch_name'];
        }
        
        return $this->response['data'];
    }
    
    private function convertTimeLengthToHuman($length){
        
        $hh = floor($length / 3600);
        
        $mm = floor(($length - $hh*3600)/60);
        
        $ss = $length - $hh*3600 - $mm*60;
        
        $result = '';
        
        if ($hh > 0){
            $result .= $hh.System::word('records_time_h').' ';
        }
        
        if ($mm > 0){
            $result .= $mm.System::word('records_time_m').' ';
        }
        
        if ($ss > 0){
            $result .= $ss.System::word('records_time_s').' ';
        }
        
        return $result;
    }
    
    private function convertDatetimeToHuman($datetime){
        
        $this_mm = date("m");
        $this_dd = date("d");
        $this_yy = date("Y");
        
        $human_date = '';
        
        if ($datetime > mktime(0,0,0, $this_mm, $this_dd, $this_yy)){
            $human_date = System::word('vod_today').', '.date("H:i", $datetime);
        }elseif ($added_time > mktime(0,0,0, $this_mm, $this_dd-1, $this_yy)){
            $human_date = System::word('vod_yesterday').', '.date("H:i", $datetime);
        }else{
            $human_date = date("d.m.Y H:i", $datetime);
        }
        
        return $human_date;
    }
}

?>