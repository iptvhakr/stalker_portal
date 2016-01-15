<?php

namespace Model;

class StatisticsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getVideoStatTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->{"getVideoStat{$func_alias}List"}($params, TRUE);
    }

    public function getVideoStatAllList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("video")
                        ->where($param['where'])
                        ->where(array("accessed" => 1))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function getVideoStatGenreList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("genre");
        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getVideoStatDailyList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("daily_played_video")
                        ->where($param['where'])->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getNoActiveAbonentTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->{"getNoActiveAbonent{$func_alias}List"}($params, TRUE);
    }
    
    public function getNoActiveAbonentTvList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("users")
                        ->where($param['where'])
                        ->where(array('NOT `users`.`time_last_play_tv`'=>NULL))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getNoActiveAbonentVideoList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("users")
                        ->where($param['where'])
                        ->where(array('NOT `users`.`time_last_play_video`'=>NULL))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getDailyClaimsTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getDailyClaimsList($params, TRUE);
    }
    
    public function getDailyClaimsList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("daily_media_claims")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getClaimsLogsTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getClaimsLogsList($params, TRUE);
    }
    
    public function getClaimsLogsList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("`media_claims_log` as M_C_L")
                            ->join('`itv` as I', 'M_C_L.`media_id`', 'I.`id` and M_C_L.`media_type` = "itv"', "LEFT")
                            ->join('`karaoke` as K', 'M_C_L.`media_id`', 'K.`id` and M_C_L.`media_type` = "karaoke"', "LEFT")
                            ->join('`video` as V', 'M_C_L.`media_id`', 'V.`id` and M_C_L.`media_type` = "vclub"', "LEFT")
                            ->join('`users` as U', 'M_C_L.`uid`', 'U.`id`', "LEFT")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getTvArchiveTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array('count(`ch_id`) as `counter`'),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getTvArchiveList($params, TRUE);
    }
    
    public function getTvArchiveList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("`played_tv_archive`")
                        ->join('itv', 'itv.id', 'played_tv_archive.ch_id', 'INNER')
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->groupby('ch_id')
                        ->orderby('counter', 'DESC');

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        return $this->mysqlInstance->get()->all();
    }
    
    public function getTimeShiftTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array('count(`ch_id`) as `counter`'),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getTimeShiftList($params, TRUE);
    }
    
    public function getTimeShiftList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("`played_timeshift`")
                        ->join('itv', 'itv.id', 'played_timeshift.ch_id', 'INNER')
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->groupby('ch_id')
                        ->orderby('counter', 'DESC');

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getAbonentStatTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->{"getAbonentStat{$func_alias}List"}($params, TRUE);
    }

    public function getAbonentStatTvList($param, $counter = FALSE) {
        if ($counter) {
            $param['select'][] = "count(`played_itv`.`id`) as `counter`";
        }

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("users")
                        ->join("played_itv", "users.id", "played_itv.uid", "LEFT")
                        ->where($param['where'])
                        ->where(array("NOT played_itv.playtime" => NULL))
                        ->like($param['like'], 'OR')
                        ->groupby(array("users.id"))
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getAbonentStatVideoList($param, $counter = FALSE) {
        if ($counter) {
            $param['select'][] = "count(`played_video`.`id`) as `counter`";
        }

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("users")
                        ->join("played_video", "users.id", "played_video.uid", "LEFT")
                        ->where($param['where'])->like($param['like'], 'OR')
                        ->where(array('NOT played_video.playtime'=>NULL))
                        ->groupby(array("users.id"))
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getAbonentStatAnecList($param, $counter = FALSE) {
        if ($counter) {
            $param['select'][] = "`readed_anec`.`mac` as `mac`";
        }
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("readed_anec")->where($param['where'])->like($param['like'], 'OR')
                        ->where(array('NOT readed'=>NULL))
                        ->groupby(array("mac"))
                        ->orderby($param['order']);
        
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getTvTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array('count(`played_itv`.id) as `counter`'),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getTvList($params, TRUE);
    }
    
    public function getTvList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("`played_itv`")
                        ->join('itv', 'itv.id', 'played_itv.itv_id', 'LEFT')
                        ->where($param['where'])
                        ->where(array(' itv.id IS NOT ' => NULL))
                        ->like($param['like'], 'OR')
                        ->groupby('itv_id');
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function getTVLocale() {
        return $this->mysqlInstance
                    ->select(array('UPPER(SUBSTRING(`user_locale`, 1, 2)) as `title`', '`user_locale` as `id`'))
                    ->from('played_itv')
                    ->groupby('user_locale')
                    ->orderby('user_locale')
                    ->get()
                    ->all();
    }
    
    public function getModeratorsStatRowsList($incoming = array(), $all = FALSE) {
        /*$incoming['select'] = '*';*/
        if ($all) {
            $incoming['like'] = array();    
        }
        return $this->getModeratorsStatList($incoming, TRUE);
    }

    public function getModeratorsStatList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from($param['from']);
        if (array_key_exists('joined', $param)) {
            foreach ($param['joined'] as $table => $keys) {
                $this->mysqlInstance->join($table, $keys['left_key'], $keys['right_key'], $keys['type']);
            }
        }
        $this->mysqlInstance->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['groupby'])) {
            $this->mysqlInstance->groupby($param['groupby']);
        }

        if (!empty($param['limit']['limit']) && !$counter) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->count()->get()->all();
            if (count($result) > 1) {
                return count($result);
            } else if (!empty ($result[0])){
                list($key, $data) = each($result[0]);
            } else {
                return 0;
            }
            return $data;
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function getAdmins($id = FALSE) {

        $this->mysqlInstance->from('administrators');
        if ($id !== FALSE) {
            $this->mysqlInstance->where(array('id'=>$id));
        }
        return $this->mysqlInstance->orderby('login')->get()->all();
    }
    
    public function getArhiveIDs($table) {
        return $this->mysqlInstance->select(array('id', 'CONCAT_WS(" - ", `year`, `month`) as `title`'))->from($table)->orderby('year, month')->get()->all();
    }

    public function getMinDateFromTable($table, $date_field){
        if (empty($table) || empty($date_field)) {
            return 0;
        }
        $result = $this->mysqlInstance->query("SELECT MIN($date_field) as min_date FROM $table")->get();
        if ($result = strtotime($result['min_date'])) {
            return $result;
        }
        return 0;
    }

    public function truncateTable($table_name){
        $this->mysqlInstance->query("TRUNCATE TABLE $table_name");
    }

    public function updateDailyClaims($values, $in){
        if (!empty($in)) {
            reset($in);
            list($key, $val) = each($in);
            $this->mysqlInstance->in($key, $val);
        } else {
            return 0;
        }
        return $this->mysqlInstance->update('`daily_media_claims`', $values)->total_rows();
    }

    public function updateMediaClaims($values, $in, $where){
        if (!empty($in)) {
            reset($in);
            list($key, $val) = each($in);
            $this->mysqlInstance->in($key, $val);
        } else {
            return 0;
        }
        return $this->mysqlInstance->update('`media_claims`', $values, $where)->total_rows();
    }

    public function deleteClaimsLogs($in){
        if (!empty($in)) {
            reset($in);
            list($key, $val) = each($in);
            $this->mysqlInstance->in($key, $val);
        } else {
            return 0;
        }
        return $this->mysqlInstance->delete('media_claims_log', array())->total_rows();
    }

    public function cleanDailyClaims(){
        return $this->mysqlInstance->delete('daily_media_claims', array(
            'vclub_sound' => 0,
            'vclub_video' => 0,
            'itv_sound' => 0,
            'itv_video' => 0,
            'karaoke_sound' => 0,
            'karaoke_video' => 0,
            'no_epg' => 0,
            'wrong_epg' => 0
        ))->total_rows();
    }

    public function cleanMediaClaims(){
        return $this->mysqlInstance->delete('media_claims', array(
            'sound_counter' => 0,
            'video_counter' => 0,
            'no_epg' => 0,
            'wrong_epg' => 0
        ))->total_rows();
    }
}