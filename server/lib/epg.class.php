<?php
/**
 * Epg from XMLTV
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Epg
{
    private $db;
    private $cleaned_epg = array();
    private $day_begin_datetime;
    private $now_datetime;
    private $cur_program_id;
    private $cur_program_idx;
    private $cur_program_page;
    private $cur_program_row;
    private $correction_time = 0; // minutes
    private $settings = array();
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->day_begin_datetime = date("Y-m-d 00:00:00");
        $this->now_datetime = date("Y-m-d H:i:s");
        
        $this->settings = $this->getSettings();
    }
    
    /**
     * Update EPG from all EPG setting records.
     *
     * @param bool $force
     * @return string
     */
    public function updateEpg($force = false){
        
        $result = '';
        
        foreach ($this->settings as $setting){
            $result .= $this->updateEpgBySetting($setting, $force);
            $result .= "\n";
        }
        
        return $result;
    }
    
    /**
     * Update EPG from one DB setting record.
     *
     * @param array $setting
     * @param bool $force
     * @return string Update result.
     */
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
        
        if (preg_match("/\.gz$/", $setting['uri'])){

            $handle = gzopen($setting['uri'], 'r');
            
            $contents = gzread($handle, 30000000);
            
            gzclose($handle);
            
            $xml = simplexml_load_string($contents);
            
        }else{
            $xml = simplexml_load_file($setting['uri']);
        }
        
        $ids_arr = $this->getITVids();
        
        $insert_data = array();
        $data_arr = array();
        
        foreach ($xml->programme as $programme){
            
            $itv_id_arr = @$ids_arr[strval($programme->attributes()->channel)];
            
            if ($itv_id_arr){
                
                $start_ts = strtotime(strval($programme->attributes()->start)) + $this->correction_time*60;
                
                $mysql_start = date("Y-m-d H:i:s", $start_ts);
                
                $stop_ts = strtotime(strval($programme->attributes()->stop)) + $this->correction_time*60;
                
                $mysql_stop  = date("Y-m-d H:i:s", $stop_ts);
                
                $duration = $stop_ts - $start_ts;

                //$title = addslashes($programme->title);
                $title = strval($programme->title);
                
                foreach ($itv_id_arr as $itv_id){
                    
                    $this->cleanEpgByDate($start_ts, $itv_id);
                    
                    $data_arr[$itv_id][] = array(
                                                'ch_id' => $itv_id,
                                                'time'  => $mysql_start,
                                                'time_to'  => $mysql_stop,
                                                'duration' => $duration,
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
    
    /**
     * Return all EPG setting records.
     *
     * @return array
     */
    private function getSettings(){
        
        return $this->db->from('epg_setting')->get()->all();
    }
    
    /**
     * Update EPG settings.
     *
     * @param array $setting
     * @return MysqlResult
     */
    private function setSettings($setting){
        
        if (isset($setting['id'])){
            
            return $this->db->update('epg_setting',
                              array(
                                'uri'     => $setting['uri'],
                                'etag'    => $setting['etag'],
                                'updated' => 'NOW()'
                              ),
                              array('id' => $setting['id']));
        }else{
            
            return $this->db->insert('epg_setting',
                              array(
                                'uri' => $setting['uri']
                              ));
        }
    }
    
    /**
     * Return array of xmltv_id=>ch_ids.
     *
     * @return array Array(xmltv_id => array(id, id, ...,id))
     */
    private function getITVids(){
        
        $valid_channels = $this->db->from('itv')->where(array('xmltv_id!=' => ''))->get()->all();
        
        $ids = array();
        
        foreach ($valid_channels as $channel){
            if (!key_exists($channel['xmltv_id'], $ids)){
                $ids[$channel['xmltv_id']] = array();
            }
            $ids[$channel['xmltv_id']][] = $channel['id'];
        }
        
        return $ids;
    }
    
    /**
     * Delete program for channel using date.
     *
     * @param string $date
     * @param int $itv_id
     * @return MysqlResult
     */
    private function cleanEpgByDate($date, $itv_id){
        
        $date = date("Y-m-d", $date);
        
        $from = $date." 00:00:00";
        $to   = $date." 23:59:59";
        
        if (!@$this->cleaned_epg[$itv_id]){
            $this->cleaned_epg[$itv_id] = array();
        }
        
        if (!@$this->cleaned_epg[$itv_id][$date]){
            $this->cleaned_epg[$itv_id] = array($date => 1);
            
            $this->db->delete('epg',
                              array(
                                  'ch_id'  => $itv_id,
                                  'time>=' => $from,
                                  'time<'  => $to
                              ));
        }
    }
    
    /**
     * Return current program page.
     *
     * @return int
     */
    public function getCurProgramPage(){
        return $this->cur_program_page;
    }
    
    /**
     * Return current program index in list.
     *
     * @return int
     */
    public function getCurProgramIdx(){
        return $this->cur_program_idx;
    }
    
    /**
     * Find current program.
     *
     * @param int $ch_id
     * @return array|null $program
     */
    public function getCurProgram($ch_id){
        
        $ch_id = intval($ch_id);
        
        /*$result = $this->db->from('epg')
                           ->where(
                               array(
                                   'ch_id'  => $ch_id,
                                   'time>=' => $this->day_begin_datetime,
                                   'time<'  => $this->now_datetime
                               ))
                           ->orderby('time', 'desc')
                           ->get();
        
        
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
        }*/
        
        $program = $this->db
                            ->from('epg')
                            ->where(array(
                                'ch_id'    => $ch_id,
                                'time<='   => 'NOW()',
                                'time_to>' => 'NOW()'
                            ))
                            ->get()
                            ->first();
        
        return $program;
    }
    
    /**
     * Return current program and $num_programs next.
     *
     * @param int $ch_id
     * @param int $num_programs
     * @return array
     */
    public function getCurProgramAndFewNext($ch_id, $num_programs){
        
        
        /*$cur_prog_id = $this->db->from('epg')
                           ->where(
                               array(
                                   'ch_id'  => $ch_id,
                                   'time>=' => $this->day_begin_datetime,
                                   'time<'  => $this->now_datetime
                               ))
                           ->orderby('time', 'desc')
                           ->get()
                           ->first('id');
        */
        
        $cur_program = $this->getCurProgram($ch_id);
                           
        if (!empty($cur_program['id'])){
            
            return $this->db->from('epg')
                                        ->select('*, TIME_FORMAT(`time`,"%H:%i") as t_time')
                                        ->where(
                                            array(
                                                'ch_id' => $ch_id,
                                                'time>='  => $cur_program['time']
                                            ))
                                        ->orderby('time')
                                        ->limit($num_programs)
                                        ->get()
                                        ->all();
            
        }
        
        return array();
    }
    
    /**
     * Return current program and 5 next.
     *
     * @param int $ch_id
     * @return array
     */
    public function getCurProgramAndFiveNext($ch_id){
        
        return $this->getCurProgramAndFewNext($ch_id, 5);
    }
    
    /**
     * Returns an array of programs on channels for next 9 hours.
     *
     * @return array
     */
    public function getEpgInfo(){
        
        //$itv = Itv::getInstance();
        
        $data = array();
        
        $now_datetime = date("Y-m-d H:i:s");
        $day_begin_datetime = date("Y-m-d 00:00:00");
        
        $db = clone $this->db;
        
        /*$cur_program_arr = $db
                              ->from('epg')
                              ->select('*, MAX(UNIX_TIMESTAMP(time)) as start_timestamp, MAX(time) as time')
                              ->in('ch_id', $itv->getAllUserChannelsIds())
                              ->where(array(
                                  'time>=' => $day_begin_datetime,
                                  'time<=' => $now_datetime
                              ))
                              ->groupby('ch_id')
                              ->get()
                              ->all();*/
        $cur_program_arr = $db
                              ->from('epg')
                              ->select('*, UNIX_TIMESTAMP(time) as start_timestamp')
                              ->in('ch_id', Itv::getInstance()->getAllUserChannelsIds())
                              ->where(array(
                                  'time<='   => 'NOW()',
                                  'time_to>' => 'NOW()'
                              ))
                              ->get()
                              ->all();
        
        $result = array();
        
        foreach ($cur_program_arr as $cur_program){
            
            $period_end = date("Y-m-d H:i:s", ($cur_program['start_timestamp'] + 9*3600));
            
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
        }
        
        return $result;
    }
    
    public function getDataTable(){
        
        $page  = $_REQUEST['p'];
        $ch_id = intval($_REQUEST['ch_id']);
        $from  = $_REQUEST['from'];
        $to    = $_REQUEST['to'];
        
        $user_channels = Itv::getInstance()->getAllUserChannelsIds();
        
        
        
    }
}
?>