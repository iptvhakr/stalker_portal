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
    private $settings = array();
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->day_begin_datetime = date("Y-m-d 00:00:00");
        $this->now_datetime = date("Y-m-d H:i:s");
        
        $this->settings = $this->getSettings();
    }
    
    public function updateEpg($force = false){
        
        $result = '';
        
        foreach ($this->settings as $setting){
            $result .= $this->updateEpgBySetting($setting, $force);
            $result .= "\n";
        }
        
        return $result;
    }
    
    public function updateEpgBySetting($setting, $force = false){
        
        $str = "From {$setting['uri']}\n";
        
        if (strpos($setting['uri'], 'http') === 0){
            
            $headers = get_headers($setting['uri']);
            
            foreach ($headers as $header){
                
                if (preg_match('/^ETag: "(.*)"/', $header, $matches)){
                    if (!empty($matches[1])){
                        
                        $etag = $matches[1];
                        
                        break;
                    }
                }
            }
        }else{
            $etag = md5_file($setting['uri']);
        }
        
        if ($setting['etag'] == $etag && !$force){
            return 'Файл не изменился';
        }
        
        $xml = simplexml_load_file($setting['uri']);
        $ids_arr = $this->getITVids();
        
        $insert_data = array();
        $data_arr = array();
        
        foreach ($xml->programme as $programme){
            
            $itv_id_arr = @$ids_arr[strval($programme->attributes()->channel)];
            
            if ($itv_id_arr){
                
                $start_ts = strtotime(strval($programme->attributes()->start)) + $this->correction_time*60;
                
                $mysql_start = date("Y-m-d H:i:s",$start_ts);

                $title = addslashes($programme->title);
                
                foreach ($itv_id_arr as $itv_id){
                    
                    $this->cleanEpgByDate($start_ts, $itv_id);
                    
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
    
    private function getSettings(){
        
        return $this->db->from('epg_setting')->get()->all();
    }
    
    private function setSettings($setting){
        
        if (isset($setting['id'])){
            //$sql = "update epg_setting set uri='{$setting['uri']}', etag='{$setting['etag']}', updated=NOW() where id=".$setting['id'];
            $this->db->update('epg_setting',
                              array(
                                'uri'     => $setting['uri'],
                                'etag'    => $setting['etag'],
                                'updated' => 'NOW()'
                              ),
                              array('id' => $setting['id']));
        }else{
            //$sql = "insert into epg_setting (uri) values ('{$setting['uri']}')";
            $this->db->insert('epg_setting',
                              array(
                                'uri' => $setting['uri']
                              ));
        }
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
        
        $date = date("Y-m-d", $date);
        
        $from = $date." 00:00:00";
        $to   = $date." 23:59:59";
        
        if (!@$this->cleaned_epg[$itv_id]){
            $this->cleaned_epg[$itv_id] = array();
        }
        
        if (!@$this->cleaned_epg[$itv_id][$date]){
            $this->cleaned_epg[$itv_id] = array($date => 1);
            
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
    
    public function getCurProgramAndFewNext($ch_id, $num_programs){
        
        
        $cur_prog_id = $this->db->from('epg')
                           ->where(
                               array(
                                   'ch_id'  => $ch_id,
                                   'time>=' => $this->day_begin_datetime,
                                   'time<'  => $this->now_datetime
                               ))
                           ->orderby('time', 'desc')
                           ->get()
                           ->first('id');
                           
        if (!empty($cur_prog_id)){
            
            return $this->db->from('epg')
                                        ->select('*, TIME_FORMAT(`time`,"%H:%i") as t_time')
                                        ->where(
                                            array(
                                                'ch_id' => $ch_id,
                                                'id>='  => $cur_prog_id
                                            ))
                                        ->orderby('time')
                                        ->limit($num_programs)
                                        ->get()
                                        ->all();
            
        }
        
        return array();
    }
    
    public function getCurProgramAndFiveNext($ch_id){
        
        return $this->getCurProgramAndFewNext($ch_id, 5);
    }
    
    public function getEpgInfo(){
        
        $itv = Itv::getInstance();
        
        $data = array();
        
        $now_datetime = date("Y-m-d H:i:s");
        $day_begin_datetime = date("Y-m-d 00:00:00");
        
        //$all_ch_str = join(',', $itv->getAllUserChannelsIds());
        
        /*$sql = 'select *,MAX(UNIX_TIMESTAMP(time)) as start_timestamp, MAX(time) as time from epg where ch_id in ('.$all_ch_str.') and time>="'.$day_begin_datetime.'" and time<="'.$now_datetime.'" group by ch_id';
        
        $rs  = $db->executeQuery($sql);
        $cur_program_arr = $rs->getAllValues();*/
        
        $db = clone $this->db;
        
        $cur_program_arr = $db
                              ->from('epg')
                              ->select('*, MAX(UNIX_TIMESTAMP(time)) as start_timestamp, MAX(time) as time')
                              ->in('ch_id', $itv->getAllUserChannelsIds())
                              ->where(array(
                                  'time>=' => $day_begin_datetime,
                                  'time<=' => $now_datetime
                              ))
                              ->groupby('ch_id')
                              ->get()
                              ->all();
        
        $result = array();
        
        foreach ($cur_program_arr as $cur_program){
            
            $period_end = date("Y-m-d H:i:s", ($cur_program['start_timestamp'] + 9*3600));
            
            /*$sql = 'select *,UNIX_TIMESTAMP(time) as start_timestamp, TIME_FORMAT(time,"%H:%i") as t_time from epg where ch_id='.$cur_program['ch_id'].' and time>="'.$cur_program['time'].'" and time<="'.$period_end.'" order by time';
            $rs  = $db->executeQuery($sql);*/
            
            
            
            $result[$cur_program['ch_id']] = $db
                                                ->from('epg')
                                                ->select('*, UNIX_TIMESTAMP(time) as start_timestamp, TIME_FORMAT(time,"%H:%i") as t_time')
                                                ->where(array(
                                                    'ch_id'  => $cur_program['ch_id'],
                                                    'time>=' => $cur_program['time'],
                                                    'time<=' => $period_end
                                                ))
                                                ->orderby('time')
                                                ->get()
                                                ->all();
            //$data['data'][$cur_program['ch_id']] = $rs->getAllValues();
        }
        
        return $result;
        //return $data;
    }
}
?>