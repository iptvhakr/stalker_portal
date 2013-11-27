<?php

class Pvr extends AjaxResponse implements \Stalker\Lib\StbApi\Pvr
{
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
        
        return $this->db->insert('pvr',
                          array(
                              'ch_id'   => $ch_id,
                              't_start' => 'NOW()',
                              'atrack'  => $atrack,
                              'vtrack'  => $vtrack,
                              'uid'     => $this->stb->id
                          ))->insert_id();
    }
    
    public function getOrderedList(){
        
        $result = $this->db
                        ->select('pvr.*, itv.name as ch_name, UNIX_TIMESTAMP(t_start) as t_start_ts')
                        ->from('pvr')
                        ->join('itv', 'itv.id', 'pvr.ch_id', 'LEFT')
                        ->where(array('uid' => $this->stb->id))
                        ->orderby('t_stop', 'DESC')
                        ->orderby('t_start', 'DESC');
                        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            $this->response['data'][$i]['length'] = System::convertTimeLengthToHuman($this->response['data'][$i]['length']);
            
            $this->response['data'][$i]['t_start'] = System::convertDatetimeToHuman($this->response['data'][$i]['t_start_ts']);
            
            $this->response['data'][$i]['cmd'] = 'auto /media/usbdisk/'.$this->response['data'][$i]['id'].'.mpg';
            
            $this->response['data'][$i]['name'] = $this->response['data'][$i]['t_start'].' '.$this->response['data'][$i]['ch_name'];
        }
        
        return $this->response['data'];
    }
}

?>