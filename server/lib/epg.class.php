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
    public $correction_time = -60; // min
    
    public function __construct(){
        $this->db = Database::getInstance(DB_NAME);
        $this->day_begin_datetime = date("Y-m-d 00:00:00");
        $this->now_datetime = date("Y-m-d H:i:s");
    }
    
    public function updateEpg(){
        $xml = simplexml_load_file(XMLTV_URI);
        $ids_arr = $this->getITVids();
        $init_sql = 'insert into epg (`ch_id`, `time`, `name`) value ';
        $sql_arr = array();
        //var_dump($ids_arr);
        foreach ($xml->programme as $programme){
            $itv_id_arr = @$ids_arr[strval($programme->attributes()->channel)];
            //$itv_id = array_search(strval($programme->attributes()->channel), $ids);
            
            if ($itv_id_arr){
                //$start = strval($programme->attributes()->start);
                $mysql_start = xmltvdatetime2datetime($programme->attributes()->start);

                $start = date("YmdHis", datetime2timestamp($mysql_start) + $this->correction_time*60);
                $mysql_start = date("Y-m-d H:i:s", datetime2timestamp($mysql_start) + $this->correction_time*60);
                
                $title = addslashes($programme->title);
                //var_dump($title);
                foreach ($itv_id_arr as $itv_id){
                    $this->cleanEpgByDate($start,$itv_id);
                    if (@$sql_arr[$itv_id]){
                        $sql_arr[$itv_id] .= "($itv_id, '$mysql_start', '$title'),";
                    }else{
                        $sql_arr[$itv_id] = $init_sql."($itv_id, '$mysql_start', '$title'),";
                    }
                }
             }
        }
        //var_dump($sql_arr);
        $err = 0;
        $done = 0;
        $xml_ids_done = '';
        $xml_ids_err = '';
        $total = 0;
        foreach ($sql_arr as $itv_xml_id => $sql){
            //var_dump(substr($sql, 0, strlen($sql)-1));
            if ($this->db->executeQuery(substr($sql, 0, strlen($sql)-1))){
                $done++;
                $xml_ids_done .= "xml_id #".$itv_xml_id."\n";
            }else{
                $err++;
                $xml_ids_err .= "xml_id #".$itv_xml_id."\n";
            }
            $total++;
        }
        $str = "Обновлено $done каналов из $total, $err ошибок \n";
        $str .= "<b>Ошибки :</b>".$xml_ids_err."\n";
        $str .= "<b>Успешно :</b>".$xml_ids_done."\n";
        return $str;
    }
    
    private function getITVids(){
        //$sql = "select * from itv where status=1 and xmltv_id!=''";
        $sql = "select * from itv where xmltv_id!=''";
        $rs = $this->db->executeQuery($sql);
        $ids = array();
        while(@$rs->next()){
            $xmltv_id = $rs->getCurrentValueByName('xmltv_id');
            if (!key_exists($xmltv_id,$ids)){
                $ids[$xmltv_id] = array();
            }
            $ids[$xmltv_id][] = $rs->getCurrentValueByName('id');
            //$ids[$rs->getCurrentValueByName('id')] = $rs->getCurrentValueByName('xmltv_id');
        }
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
            $sql = "delete from epg where ch_id=$itv_id and time>='$from' and time<'$to'";
            $this->db->executeQuery($sql);
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
        $sql = 'select * from epg where ch_id='.$ch_id.' and time>="'.$this->day_begin_datetime.'" and time<"'.$this->now_datetime.'" order by time desc';
        
        $rs = $this->db->executeQuery($sql);
        //echo $this->cur_program_idx;
        $this->cur_program_idx = intval($rs->getRowCount());
        $this->cur_program_id  = intval($rs->getValueByName(0, 'id'));
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