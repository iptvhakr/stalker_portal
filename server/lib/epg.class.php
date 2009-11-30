<?php
/**
 * Epg from XMLTV
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Epg
{
    public $db;
    public $cleaned_epg = array();
    public $day_begin_datetime;
    public $now_datetime;
    public $cur_program_id;
    public $cur_program_idx;
    public $cur_program_page;
    public $cur_program_row;
    public $correction_time = 0; // minutes
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->day_begin_datetime = date("Y-m-d 00:00:00");
        $this->now_datetime = date("Y-m-d H:i:s");
    }
    
    public function updateEpg(){
        
        $xml = simplexml_load_file(XMLTV_URI);
        $ids_arr = $this->getITVids();
        
        $insert_data = array();
        $data_arr = array();
        
        foreach ($xml->programme as $programme){
            
            $itv_id_arr = @$ids_arr[strval($programme->attributes()->channel)];
            
            if ($itv_id_arr){
                
                $mysql_start = xmltvdatetime2datetime($programme->attributes()->start);

                $start = date("YmdHis", datetime2timestamp($mysql_start) + $this->correction_time*60);
                $mysql_start = date("Y-m-d H:i:s", datetime2timestamp($mysql_start) + $this->correction_time*60);
                
                $title = addslashes($programme->title);
                
                foreach ($itv_id_arr as $itv_id){
                    
                    $this->cleanEpgByDate($start, $itv_id);
                    
                    if (!key_exists($itv_id, $data_arr)){
                        $data_arr[$itv_id][] = array();
                    }
                    
                    $data_arr[$itv_id][] = array(
                                                'ch_id' => $itv_id,
                                                'time'  => $mysql_start,
                                                'name'  => $title
                                                );
                    
                }
            }
        }
        
        $err = 0;
        $done = 0;
        $xml_ids_done = '';
        $xml_ids_err = '';
        $total = 0;
        
        foreach ($data_arr as $itv_xml_id => $data){
            
            $result = $this->db->insert('epg', $data);
            
            if ($result->insert_id()){
                $done++;
                $xml_ids_done .= "xml_id #".$itv_xml_id."\n";
            }else{
                $err++;
                $xml_ids_err  .= "xml_id #".$itv_xml_id."\n";
            }
            
            $total++;
        }
        
        $str = "Обновлено $done каналов из $total, $err ошибок \n";
        $str .= "<b>Ошибки :</b>".$xml_ids_err."\n";
        $str .= "<b>Успешно :</b>".$xml_ids_done."\n";
        
        return $str;
    }
    
    private function getITVids(){
        
        /*$sql = "select * from itv where xmltv_id!=''";
        $rs = $this->db->executeQuery($sql);*/
        
        $valid_channels = $this->db->from('itv')->where(array('xmltv_id!=' => ''))->get()->all();
        
        $ids = array();
        
        foreach ($valid_channels as $channel){
            if (!key_exists($channel['xmltv_id'], $ids)){
                $ids[$channel['xmltv_id']] = array();
            }
            $ids[$channel['xmltv_id']][] = $channel['id'];
        }
        
        /*while(@$rs->next()){
            $xmltv_id = $rs->getCurrentValueByName('xmltv_id');
            if (!key_exists($xmltv_id,$ids)){
                $ids[$xmltv_id] = array();
            }
            $ids[$xmltv_id][] = $rs->getCurrentValueByName('id');
        }*/
        
        return $ids;
    }
    
    private function cleanEpgByDate($date, $itv_id){
        $yy = substr($date, 0,4);
        $mm = substr($date, 4,2);
        $dd = substr($date, 6,2);
        
        $from = "$yy-$mm-$dd 00:00:00";
        $to   = "$yy-$mm-$dd 23:59:59";
        
        if (!@$this->cleaned_epg[$itv_id]){
            $this->cleaned_epg[$itv_id] = array();
        }
        
        if (!@$this->cleaned_epg[$itv_id]["$yy-$mm-$dd"]){
            $this->cleaned_epg[$itv_id] = array("$yy-$mm-$dd" => 1);
            
            /*$sql = "delete from epg where ch_id=$itv_id and time>='$from' and time<'$to'";
            $this->db->executeQuery($sql);*/
            
            $this->db->delete('epg',
                              array(
                                  'ch_id'  => $itv_id,
                                  'time>=' => $from,
                                  'time<'  => $to
                              ));
        }
    }
    
    public function getCurProgramPage(){
        return $this->cur_program_page;
    }
    
    public function getCurProgramIdx(){
        return $this->cur_program_idx;
    }
    
    public function getCurProgram($ch_id){
        $ch_id = intval($ch_id);
        
        
        /*$sql = 'select * from epg where ch_id='.$ch_id.' and time>="'.$this->day_begin_datetime.'" and time<"'.$this->now_datetime.'" order by time desc';
        $rs = $this->db->executeQuery($sql);*/
        
        $result = $this->db->from('epg')
                           ->where(
                               array(
                                   'ch_id'  => $ch_id,
                                   'time>=' => $this->day_begin_datetime,
                                   'time<'  => $this->now_datetime
                               ))
                           ->orderby('time', 'desc')
                           ->get();
        
        //echo $this->cur_program_idx;
        $this->cur_program_idx = intval($result->count());
        $this->cur_program_id  = intval($result->get('id'));
        if ($this->cur_program_id > 0){
            $this->cur_program_page = ceil($this->cur_program_idx/MAX_PAGE_ITEMS);
            $this->cur_program_row = $this->cur_program_idx - floor($this->cur_program_idx/MAX_PAGE_ITEMS)*MAX_PAGE_ITEMS;
            if ($this->cur_program_row == 0){
                $this->cur_program_row = 10;
            }
        }else{
            $this->cur_program_page = 0;
            $this->cur_program_row  = 0;
        }
    }
    
}
?>