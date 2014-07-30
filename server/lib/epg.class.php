<?php
/**
 * Epg from XMLTV
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Epg implements \Stalker\Lib\StbApi\Epg
{
    private $db;
    private $cleaned_epg = array();
    private $day_begin_datetime;
    private $now_datetime;
    private $cur_program_id;
    private $cur_program_idx;
    private $cur_program_page;
    private $cur_program_row;
    //private $correction_time = 0; // minutes
    private $settings = array();
    private $real_ids = array();
    private $corrections = null;
    private $channels_updated = array();
    private $new_ids = array();

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

        $event = new SysEvent();
        $event->setUserListByMac('online');
        $event->sendUpdateEpg();

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
            $etag = '';
            $headers = get_headers($setting['uri'], 1);

            if ($headers === false){
                return "\n"._("Source")." ".$setting['uri']." "._("unavailable")."\n";
            }

            if (preg_match("/301 Moved Permanently/", $headers[0]) && !empty($headers['Location'])){

                $setting['uri'] = $headers['Location'];

                return $this->updateEpgBySetting($setting, $force);

            }elseif (!preg_match("/200 OK/", $headers[0])){
                return "\n"._("Source")." ".$setting['uri']." "._("unavailable")."\n";
            }

            if (!empty($headers['ETag'])){
                $etag = $headers['ETag'];
            }else if (!empty($headers['Last-Modified'])){
                $etag = $headers['Last-Modified'];
            }else{
                $etag = time();
            }
        }else{
            $etag = md5_file($setting['uri']);
        }

        if ($setting['etag'] == $etag && !$force){
            return _("Source")." ".$setting['uri']." "._("not changed")."\n";
        }

        if (preg_match("/\.gz$/", $setting['uri'])){

            $handle = gzopen($setting['uri'], 'r');

            $tmpfname = tempnam("/tmp", "xmltv");
            $fp = fopen($tmpfname, "w");

            while (!gzeof($handle)){
                $contents = gzread($handle, 1000000);
                fwrite($fp, $contents);
            }
            gzclose($handle);

            $xml = simplexml_load_file($tmpfname);

            unlink($tmpfname);

        }else{
            $xml = simplexml_load_file($setting['uri']);
        }

        $ids_arr = $this->getITVids();

        $data_arr = array();

        $start_time = microtime(1);

        $total_need_to_delete = array();

        foreach ($xml->programme as $programme){

            $itv_id_arr = @$ids_arr[$setting['id_prefix'].strval($programme->attributes()->channel)];

            if ($itv_id_arr){

                $start = strtotime(strval($programme->attributes()->start));
                $stop  = strtotime(strval($programme->attributes()->stop));

                $title = strval($programme->title);
                $descr = strval($programme->desc);

                $category = array();
                $director = array();
                $actor    = array();

                if (!empty($programme->category)){

                    foreach ($programme->category as $_category){
                        $category[] = strval($_category);
                    }
                }

                $category = implode(', ', $category);

                if (!empty($programme->credits->director)){

                    foreach ($programme->credits->director as $_director){
                        $director[] = strval($_director);
                    }
                }

                $director = implode(', ', $director);

                if (!empty($programme->credits->actor)){
                    foreach ($programme->credits->actor as $_actor){
                        $actor[] = strval($_actor);
                    }
                }

                $actor = implode(', ', $actor);

                foreach ($itv_id_arr as $itv_id){
                    $correction_time = $this->getCorrectionTimeByChannelId($itv_id);
                    $start_ts = $start + $correction_time * 60;
                    $need_to_delete = $this->getProgramIdsForClean($start_ts, $itv_id);

                    if ($need_to_delete){
                        $total_need_to_delete = array_merge($total_need_to_delete, $need_to_delete);
                    }
                }

                foreach ($itv_id_arr as $itv_id){

                    $correction_time = $this->getCorrectionTimeByChannelId($itv_id);

                    $start_ts = $start + $correction_time * 60;
                    $mysql_start = date("Y-m-d H:i:s", $start_ts);

                    $stop_ts = $stop + $correction_time * 60;
                    $mysql_stop  = date("Y-m-d H:i:s", $stop_ts);

                    $duration = $stop_ts - $start_ts;

                    $real_id = $itv_id.'_'.$start_ts;

                    if (isset($this->real_ids[$real_id])){
                        continue;
                    }

                    $this->real_ids[$real_id] = true;

                    $data_arr[] = array(
                                            'ch_id'    => $itv_id,
                                            'time'     => $mysql_start,
                                            'time_to'  => $mysql_stop,
                                            'duration' => $duration,
                                            'real_id'  => $real_id,
                                            'name'     => $title,
                                            'descr'    => $descr,
                                            'category' => $category,
                                            'director' => $director,
                                            'actor'    => $actor
                                            );

                    $this->channels_updated[$itv_id] = 1;
                }
            }
        }

        $xml = null;

        $total_need_to_delete = array_diff($total_need_to_delete, $this->new_ids);

        if (!empty($total_need_to_delete)){
            Mysql::getInstance()->query('delete from epg where id in ('.implode(', ', array_unique($total_need_to_delete)).')');
            Mysql::getInstance()->query('OPTIMIZE TABLE epg');
        }

        if (!empty($data_arr)){
            $result = $this->db->insert('epg', $data_arr);

            $real_ids = array_map(function($item){
                return $item['real_id'];
            }, $data_arr);

            $this->new_ids = array_merge(Mysql::getInstance()->from('epg')->in('real_id', $real_ids)->get()->all('id'), $this->new_ids);
        }else{
            $result = true;
        }

        $setting['etag'] = $etag;
        $this->setSettings($setting);

        //$str = sprintf(_("Updated %d channels from %d, %d errors"), $done, $total, $err)." \n";
        $str = "\n";
        if (!$result){
            $str .= "<b>"._("Errors").": </b> 1\n";
        }
        $str .= "<b>".sprintf(_("Successful: %s channels"), count($this->channels_updated))."</b>\n";
        $str .= "<b>".sprintf(_("Deleted: %s records"), count($total_need_to_delete))."</b>\n";
        $str .= "<b>"._("Queries").": </b>".Mysql::get_num_queries()."\n";
        $str .= "<b>"._("Exec time").": </b>".round(microtime(1) - $start_time, 2).'s';

        $this->channels_updated = array();

        return $str;
    }

    private function getCorrectionTimeByChannelId($ch_id){

        if ($this->corrections === null){
            $this->corrections = array();
            $channels = Mysql::getInstance()->from('itv')->get()->all();

            foreach ($channels as $channel){
                $this->corrections[$channel['id']] = $channel['correct_time'];
            }
        }

        return empty($this->corrections[$ch_id]) ? 0 : (int) $this->corrections[$ch_id];
    }

    /**
     * Return all EPG setting records.
     *
     * @return array
     */
    private function getSettings(){

        return $this->db->from('epg_setting')->where(array('status' => 1))->get()->all();
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
            if (!array_key_exists($channel['xmltv_id'], $ids)){
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
     * @return array|null
     */
    private function getProgramIdsForClean($date, $itv_id){

        $real_date = $date;

        $real_from = date("Y-m-d H:i:s", $date);

        $date = date("Y-m-d", $date);

        $from = $date." 00:00:00";
        $to   = $date." 23:59:59";

        if (!array_key_exists($itv_id, $this->cleaned_epg)){
            $this->cleaned_epg[$itv_id] = array();
        }

        if (!array_key_exists($date, $this->cleaned_epg[$itv_id]) || $this->cleaned_epg[$itv_id][$date] > $real_date){
            $this->cleaned_epg[$itv_id] = array($date => $real_date);

            $need_to_delete = Mysql::getInstance()
                ->from('epg')
                ->where(array(
                    'ch_id'  => $itv_id,
                    'time>=' => $real_from,
                    'time<'  => $to
                ))
                ->get()
                ->all('id');

            return $need_to_delete;
        }

        return null;
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

        /*$ch_id = intval($ch_id);

        $program = $this->db
                            ->from('epg')
                            ->where(array(
                                'ch_id'    => $ch_id,
                                'time<='   => 'NOW()',
                                //'time_to>' => 'NOW()'
                            ))
                            ->where(array(
                                'time_to'  => 0,
                                'time_to>' => 'NOW()'
                            ), 'OR ')
                            ->orderby('time', 'DESC')
                            ->get()
                            ->first();

        return $program;*/

        return $this->getProgramByChannelAndTime($ch_id);
    }

    public function getProgramByChannelAndTime($ch_id, $datetime = 'NOW()'){

        $ch_id = intval($ch_id);

        $program = $this->db
            ->select('*, TIME_FORMAT(epg.time,"%H:%i") as t_time')
            ->from('epg')
            ->where(array(
                'ch_id'    => $ch_id,
                'time<='   => $datetime
            ))
            ->where(array(
                'time_to'  => 0,
                'time_to>' => $datetime
            ), 'OR ')
            ->orderby('time', 'DESC')
            ->get()
            ->first();

        return $program;
    }

    public function getAllProgramForCh(){

        $ch_id = intval($_REQUEST['ch_id']);

        $channel = Itv::getById($ch_id);

        if (empty($channel)){
            return array();
        }

        return $this->db
            ->select('UNIX_TIMESTAMP(time) as start_timestamp, UNIX_TIMESTAMP(time_to) as stop_timestamp, name')
            ->from('epg')
            ->where(array(
                'ch_id'    => $ch_id,
                'time>'    => date('Y-m-d H:i:s', strtotime('-'.$channel['tv_archive_duration'].' hours'))
            ))
            ->orderby('time')
            ->get()
            ->all();

    }

    /**
     * Return current program and $num_programs next.
     *
     * @param int $ch_id
     * @param int $num_programs
     * @return array
     */
    public function getCurProgramAndFewNext($ch_id, $num_programs){

        $cur_program = $this->getCurProgram($ch_id);

        if (empty($cur_program['id'])){
            return array();
        }

        $epg = $this->db->from('epg')
            /// Mysql time format, see https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
            ->select('epg.*, UNIX_TIMESTAMP(epg.time) as start_timestamp, UNIX_TIMESTAMP(epg.time_to) as stop_timestamp, TIME_FORMAT(epg.time,"'._('%H:%i').'") as t_time, TIME_FORMAT(epg.time_to,"%H:%i") as t_time_to')
            ->where(
                array(
                    'epg.ch_id' => $ch_id,
                    'epg.time>='  => $cur_program['time']
                ))
            ->orderby('epg.time')
            ->limit($num_programs)
            ->get()
            ->all();

        $reminder = new TvReminder();
        $reminders = $reminder->getAllActiveForMac(Stb::getInstance()->mac);

        $tv_archive = new TvArchive();
        $archived_recs = $tv_archive->getAllTasksAssoc();

        for ($i = 0; $i < count($epg); $i++){

            if (array_key_exists($epg[$i]['real_id'], $reminders)){
                $epg[$i]['mark_memo'] = 1;
            }else{
                $epg[$i]['mark_memo'] = 0;
            }

            if (array_key_exists($epg[$i]['ch_id'], $archived_recs) &&
                $epg[$i]['start_timestamp'] > $archived_recs[$epg[$i]['ch_id']]['start_timestamp'] &&
                $epg[$i]['stop_timestamp'] < time()){

                $epg[$i]['mark_archive'] = 1;
            }else{
                $epg[$i]['mark_archive'] = 0;
            }
        }

        return $epg;
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
     * @param int $hours
     * @return array
     */
    public function getEpgInfo($hours){

        return $this->getEpgForChannelsOnPeriod(array(), '', date("Y-m-d H:i:s", (time() + ($hours+2)*3600)));
    }

    public function getEpgForChannelsOnPeriod($channels_ids = array(), $from ='', $to = '', $limit = 0, $offset = 0){

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

            $program = $db
                         ->from('epg')
                         ->select('epg.*, UNIX_TIMESTAMP(epg.time) as start_timestamp, UNIX_TIMESTAMP(epg.time_to) as stop_timestamp, TIME_FORMAT(epg.time,"'._('%H:%i').'") as t_time, TIME_FORMAT(epg.time_to,"'._('%H:%i').'") as t_time_to')
                         ->where(array(
                             'epg.ch_id'     =>  $ch_id,
                             'epg.time_to>'  =>  $from,
                             'epg.time<'     =>  $to,
                         ))
                         ->orderby('epg.time');

            if ($limit){
                $program = $program->limit($limit, $offset);
            }

            $result[$ch_id] = $program->get()->all();
        }

        //$week_day_arr = System::word('week_arr');
        $week_day_arr = array(_('SUNDAY'),_('MONDAY'),_('TUESDAY'),_('WEDNESDAY'),_('THURSDAY'),_('FRIDAY'),_('SATURDAY'));

        $now_ts = time();

        $recorder = new StreamRecorder();
        $user_rec_ids = $recorder->getDeferredRecordIdsForUser(Stb::getInstance()->id);

        $tv_archive = new TvArchive();
        $archived_recs = $tv_archive->getAllTasksAssoc();

        $reminder = new TvReminder();
        $reminders = $reminder->getAllActiveForMac(Stb::getInstance()->mac);

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

                /*if ($epg[$i]['start_timestamp'] < $now_ts){
                    $epg[$i]['mark_memo'] = null;
                }*/

                if (array_key_exists($epg[$i]['real_id'], $user_rec_ids)){
                    $epg[$i]['mark_rec'] = 1;
                    $epg[$i]['rec_id']   = $user_rec_ids[$epg[$i]['real_id']];
                }else{
                    $epg[$i]['mark_rec'] = 0;
                }

                if (array_key_exists($epg[$i]['real_id'], $reminders)){
                    $epg[$i]['mark_memo'] = 1;
                }else{
                    $epg[$i]['mark_memo'] = 0;
                }

                if (array_key_exists($epg[$i]['ch_id'], $archived_recs)){

                    if ($epg[$i]['start_timestamp'] > time() - $archived_recs[$epg[$i]['ch_id']]['parts_number'] * 3600 &&
                        $epg[$i]['stop_timestamp'] < time()){
    
                        $epg[$i]['mark_archive'] = 1;
                    }else{
                        $epg[$i]['mark_archive'] = 0;
                    }
                }else{
                    $epg[$i]['mark_archive'] = 0;
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
        $default_page = false;

        $page_items = 10;

        $all_user_ids = Itv::getInstance()->getAllUserChannelsIds();

        $dvb_channels = Itv::getInstance()->getDvbChannels();
        $dvb_ch_idx = null;

        $channel = Itv::getChannelById($ch_id);

        if (empty($channel)){
            foreach ($dvb_channels as $dvb_channel){
                if ($dvb_channel['id'] == $ch_id){
                    $channel = $dvb_channel;
                    break;
                }
            }

            for ($i = 0; $i < count($dvb_channels); $i++){
                if ($dvb_channels[$i]['id'] == $ch_id){
                    $channel = $dvb_channels[$i];
                    $dvb_ch_idx = $i;
                }
            }

            if ($dvb_ch_idx != null){
                $dvb_ch_idx++;
            }
        }

        $total_channels = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->get()
                                   ->count();

        $total_iptv_channels = $total_channels;

        $total_channels += count($dvb_channels);

        $ch_idx = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->where(array('number<=' => $channel['number']))
                                   ->get()
                                   ->count();

        $ch_idx += $dvb_ch_idx;

        if ($ch_idx === false){
            $ch_idx = 0;
        }

        if ($page == 0){

            $default_page = true;

            $page = ceil($ch_idx/$page_items);

            if ($page == 0){
                $page == 1;
            }
        }

        $ch_idx = $ch_idx - ($page-1)*$page_items;

        $user_channels = Itv::getInstance()
                                   ->getChannels()
                                   ->orderby('number')
                                   ->in('id', $all_user_ids)
                                   ->limit($page_items, ($page-1)*$page_items)
                                   ->get()
                                   ->all();

        $total_iptv_pages = ceil($total_iptv_channels/$page_items);

        if (count($user_channels) < $page_items){

            if ($page == $total_iptv_pages){
                $dvb_part_length = $page_items - $total_iptv_channels % $page_items;
            }else{
                $dvb_part_length = $page_items;
            }

            if ($page > $total_iptv_pages){
                $dvb_part_offset = ($page - $total_iptv_pages - 1) * $page_items + ($page_items - ($total_iptv_channels) % $page_items);
            }else{
                $dvb_part_offset = 0;
            }

            if (isset($_REQUEST['p'])){
                $dvb_channels = array_splice($dvb_channels, $dvb_part_offset, $dvb_part_length);
            }

            $user_channels = array_merge($user_channels, $dvb_channels);
        }

        $display_channels_ids = array();

        for ($i=0; $i<count($user_channels); $i++){
            if (Config::getSafe('enable_numbering_in_order', false)){
                $user_channels[$i]['number'] = (string) (($i+1)+($page-1)*10);
            }
            $display_channels_ids[] = $user_channels[$i]['id'];
        }

        $raw_epg = $this->getEpgForChannelsOnPeriod($display_channels_ids, $from, $to);

        $result = array();

        foreach ($raw_epg as $id => $epg){

            $channel = $user_channels[array_search($id, $display_channels_ids)];

            $result[] = array(
                              'ch_id'   => $id,
                              //'name'  => Itv::getChannelNameById($id),
                              'name'    => $channel['name'],
                              'number'  => $channel['number'],
                              'ch_type' => isset($channel['type']) && $channel['type'] == 'dvb' ? 'dvb' : 'iptv',
                              'dvb_id'  => isset($channel['type']) && $channel['type'] == 'dvb' ? $channel['dvb_id'] : null,
                              'epg_container' => 1,
                              'epg'     => $epg);
        }

        $time_marks = array();

        $from_ts = strtotime($from);
        $to_ts   = strtotime($to);

        /// Time format. See: http://ua2.php.net/manual/en/function.date.php
        $time_marks[] = date(_("H:i"), $from_ts);
        $time_marks[] = date(_("H:i"), $from_ts+1800);
        $time_marks[] = date(_("H:i"), $from_ts+2*1800);
        $time_marks[] = date(_("H:i"), $from_ts+3*1800);

        if (!$default_page){
            //$ch_idx = 0;
            //$page = 0;
        }

        if (!in_array($ch_id, $display_channels_ids)){
            $ch_idx = 0;
            $page   = 0;
        }else{
            $ch_idx = array_search($ch_id, $display_channels_ids) + 1;
        }

        //var_dump($display_channels_ids, $ch_id, $ch_idx);

        return array('total_items'    => $total_channels,
                     'max_page_items' => $page_items,
                     'cur_page'       => $page, // $page?
                     'selected_item'  => $ch_idx,
                     'time_marks'     => $time_marks,
                     'from_ts'        => $from_ts,
                     'to_ts'          => $to_ts,
                     'data'           => $result);
    }

    public function getWeek(){

        $cur_num_day = date('N')-1;

        $week_short_arr = array(_('Sun'),_('Mon'),_('Tue'),_('Wed'),_('Thu'),_('Fri'),_('Sat'));

        array_push($week_short_arr, array_shift($week_short_arr));

        $month_arr = array(_('JANUARY'),_('FEBRUARY'),_('MARCH'),_('APRIL'),_('MAY'),_('JUNE'),_('JULY'),_('AUGUST'),_('SEPTEMBER'),_('OCTOBER'),_('NOVEMBER'),_('DECEMBER'));

        $year  = date("Y");
        $month = date("m");
        $day   = date("d");

        $week_days = array();

        $epg_history_weeks = Config::getSafe('epg_history_weeks', 1);

        for ($i=0; $i<=13+$epg_history_weeks*7; $i++){
            $w_day   = date("d", mktime (0, 0, 0, $month, $day-$cur_num_day-$epg_history_weeks*7+$i, $year));
            $w_month = date("n", mktime (0, 0, 0, $month, $day-$cur_num_day-$epg_history_weeks*7+$i, $year))-1;
            $week_days[$i]['f_human'] = $week_short_arr[$i % 7].' '.$w_day.' '.$month_arr[$w_month];
            $week_days[$i]['f_mysql'] = date("Y-m-d", mktime (0, 0, 0, $month, $day-$cur_num_day-$epg_history_weeks*7+$i, $year));
            if ($week_days[$i]['f_mysql'] === date("Y-m-d")){
                $week_days[$i]['today'] = 1;
            }else{
                $week_days[$i]['today'] = 0;
            }
        }

        return $week_days;
    }

    public static function getById($id){

        return Mysql::getInstance()->from('epg')->where(array('id' => $id))->get()->first();
    }

    public static function getByRealId($real_id){
        return Mysql::getInstance()->from('epg')->where(array('real_id' => $real_id))->get()->first();
    }

    public function getSimpleDataTable(){

        $ch_id = intval($_REQUEST['ch_id']);
        $date  = $_REQUEST['date'];
        $page  = intval($_REQUEST['p']);

        $default_page = false;

        $page_items = 10;

        $from = $date.' 00:00:00';
        $to   = $date.' 23:59:59';

        //$epg = $this->getEpgForChannelsOnPeriod(array($ch_id), $from, $to);


        $program = Mysql::getInstance()
                     ->from('epg')
                     ->select('epg.*, UNIX_TIMESTAMP(epg.time) as start_timestamp, UNIX_TIMESTAMP(epg.time_to) as stop_timestamp, TIME_FORMAT(epg.time,"'._('%H:%i').'") as t_time, TIME_FORMAT(epg.time_to,"'._('%H:%i').'") as t_time_to')
                     ->where(array(
                         'epg.ch_id'       =>  $ch_id,
                         'epg.time>='      =>  $from,
                         'epg.time<='      =>  $to
                     ))
                     ->orderby('epg.time')
                     ->get()
                     ->all();


        $total_items = count($program);

        $ch_idx = Mysql::getInstance()
                     ->from('epg')
                     ->count()
                     ->where(array(
                         'epg.ch_id'     =>  $ch_id,
                         'epg.time>='    =>  $from,
                         'epg.time<'     =>  'NOW()',
                     ))
                     ->get()
                     ->counter();

        //var_dump($ch_idx, date('Y-m-d'));

        if ($page == 0){

            $default_page = true;

            $page = ceil($ch_idx/$page_items);

            if ($page == 0){
                $page = 1;
            }

            if ($date != date('Y-m-d')){
                $page = 1;
                $default_page = false;
            }
        }

        $program = array_slice($program, ($page-1)*$page_items, $page_items);

        $now = time();

        $recorder = new StreamRecorder();
        $user_rec_ids = $recorder->getDeferredRecordIdsForUser(Stb::getInstance()->id);

        $tv_archive = new TvArchive();
        $archived_recs = $tv_archive->getAllTasksAssoc();

        $reminder = new TvReminder();
        $reminders = $reminder->getAllActiveForMac(Stb::getInstance()->mac);

        //var_dump($reminders);

        for ($i=0; $i<count($program); $i++){
            if ($program[$i]['stop_timestamp'] < $now){
                $program[$i]['open'] = 0;
            }else{
                $program[$i]['open'] = 1;
            }

            /*if ($program[$i]['start_timestamp'] < $now){
                $program[$i]['mark_memo'] = null;
            }*/
            //var_dump($reminders);
            if (array_key_exists($program[$i]['real_id'], $reminders)){
                $program[$i]['mark_memo'] = 1;
            }else{
                $program[$i]['mark_memo'] = 0;
            }

            //if (in_array($program[$i]['id'], $user_rec_ids)){
            if (array_key_exists($program[$i]['real_id'], $user_rec_ids)){
                $program[$i]['mark_rec'] = 1;
                $program[$i]['rec_id']   = $user_rec_ids[$program[$i]['real_id']];
            }else{
                $program[$i]['mark_rec'] = 0;
            }

            if (array_key_exists($program[$i]['ch_id'], $archived_recs)){

                if (($program[$i]['start_timestamp'] > time() - $archived_recs[$program[$i]['ch_id']]['parts_number'] * 3600 &&
                    $program[$i]['stop_timestamp'] < time())){
                    
                    $program[$i]['mark_archive'] = 1;
                }else{
                    $program[$i]['mark_archive'] = 0;
                }
            }else{
                $program[$i]['mark_archive'] = 0;
            }
        }

        if ($default_page){
            $cur_page = $page;
            $selected_item = $ch_idx - ($page-1)*$page_items;
        }else{
            $cur_page = 0;
            $selected_item = 0;
        }

        return array(
                        'cur_page'       => $cur_page,
                        'selected_item'  => $selected_item,
                        'total_items'    => $total_items,
                        'max_page_items' => $page_items,
                        'data'           => $program
                    );
    }
}
?>