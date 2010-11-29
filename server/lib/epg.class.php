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
                                        ->select('*, TIME_FORMAT(`time`,"%H:%i") as t_time, TIME_FORMAT(`time_to`,"%H:%i") as t_time_to')
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
        
        /*$db = clone $this->db;
        
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
        
        return $result;*/
        
        return $this->getEpgForChannelsOnPeriod(array());
    }
    
    private function getEpgForChannelsOnPeriod($channels_ids = array(), $from ='', $to = ''){
        
        $db = clone $this->db;
        
        if (empty($channels_ids)){
            $channels_ids = Itv::getInstance()->getAllUserChannelsIds();
        }
        
        if (empty($from)){
            //$from = 'NOW()';
            $from = date("Y-m-d H:i:s");
        }
        
        $from_ts = strtotime($from);
        
        if (empty($to)){
            $to = date("Y-m-d H:i:s", (time() + 9*3600));
        }
        
        $to_ts = strtotime($to);
        
        $result = array();
        
        foreach ($channels_ids as $ch_id){
            
            $result[$ch_id] = $db
                                 ->from('epg')
                                 ->select('*, UNIX_TIMESTAMP(time) as start_timestamp, UNIX_TIMESTAMP(time_to) as stop_timestamp, TIME_FORMAT(time,"%H:%i") as t_time, TIME_FORMAT(time_to,"%H:%i") as t_time_to')
                                 ->where(array(
                                     'ch_id'     => $ch_id,
                                     'time_to>'  =>  $from,
                                     'time<'     =>  $to,
                                 ))
                                 ->orderby('time')
                                 ->get()
                                 ->all();
        }
        
        $week_day_arr = System::word('week_arr');
        
        foreach ($result as $ch_id => $epg){
            
            for ($i = 0; $i < count($epg); $i++){
                
                $epg[$i]['display_duration'] = $epg[$i]['duration'];
                $epg[$i]['larr'] = 0;
                $epg[$i]['rarr'] = 0;
                
                if ($epg[$i]['start_timestamp'] < $from_ts){
                    $epg[$i]['larr'] = 1;
                    $epg[$i]['display_duration'] = $epg[$i]['duration'] - ($from_ts - $epg[$i]['start_timestamp']);
                }
                
                if ($epg[$i]['stop_timestamp'] > $to_ts){
                    $epg[$i]['rarr'] = 1;
                    $epg[$i]['display_duration'] = $epg[$i]['duration'] - ($epg[$i]['stop_timestamp'] - $to_ts);
                }
                
                $epg[$i]['on_date'] = $week_day_arr[date("w", $epg[$i]['start_timestamp'])].' '.date("d.m.Y", $epg[$i]['start_timestamp']);
            }
            
            $result[$ch_id] = $epg;
        }
        
        return $result;
    }
    
    public function getDataTable(){
        
        $page  = intval($_REQUEST['p']);
        $ch_id = intval($_REQUEST['ch_id']);
        $from  = $_REQUEST['from'];
        $to    = $_REQUEST['to'];
        
        $page_items = 10;

        $all_user_ids = Itv::getInstance()->getAllUserChannelsIds();
        
        $channel = Itv::getChannelById($ch_id);
        
        $total_channels = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->get()
                                   ->count();
        
        $ch_idx = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->where(array('number<=' => $channel['number']))
                                   ->get()
                                   ->count();
        
        
        if ($ch_idx === false){
            $ch_idx = 0;
        }
        
        if ($page == 0){
            $page = ceil($ch_idx/$page_items);
            
            if ($page == 0){
                $page == 1;
            }
        }
        
        $user_channels = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->limit($page_items, ($page-1)*$page_items)
                                   ->get()
                                   ->all();
        
        $display_channels_ids = array_map(function($element){return $element['id'];}, $user_channels);
        
        $raw_epg = $this->getEpgForChannelsOnPeriod($display_channels_ids, $from, $to);
        
        $result = array();
        
        foreach ($raw_epg as $ch_id => $epg){
            
            $channel = $user_channels[array_search($ch_id, $display_channels_ids)];
            
            $result[] = array(
                              'ch_id'  => $ch_id,
                              //'name'  => Itv::getChannelNameById($ch_id), //@todo: in future for php>=5.3.0 use - array_filter($channels, function($element) use ($ch_id){return ($element['id'] == $ch_id)}),
                              'name'   => $channel['name'],
                              'number' => $channel['number'],
                              'epg_container' => 1,
                              'epg'    => $epg);
        }
        
        $time_marks = array();
        
        $from_ts = strtotime($from);
        $to_ts   = strtotime($to);
        
        $time_marks[] = date("H:i", $from_ts);
        $time_marks[] = date("H:i", $from_ts+1800);
        $time_marks[] = date("H:i", $from_ts+2*1800);
        $time_marks[] = date("H:i", $from_ts+3*1800);
        
        
        return array('total_items'    => $total_channels,
                     'max_page_items' => $page_items,
                     'cur_page'       => $page, // $page?
                     'selected_item'  => $ch_idx,
                     'time_marks'     => $time_marks,
                     'from_ts'        => $from_ts,
                     'to_ts'          => $to_ts,
                     'data'           => $result);
    }
}
?>