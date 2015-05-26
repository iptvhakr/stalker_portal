<?php

namespace Model;

class StatisticsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getVideoStatTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("video")
                        ->where($param['where'])
                        ->where(array("accessed" => 1))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }

    public function getVideoStatGenreList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])->from("genre");
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getVideoStatDailyList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("daily_played_video")
                        ->where($param['where'])->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getNoActiveAbonentTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("users")
                        ->where($param['where'])
                        ->where(array('NOT `users`.`time_last_play_tv`'=>NULL))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getNoActiveAbonentVideoList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("users")
                        ->where($param['where'])
                        ->where(array('NOT `users`.`time_last_play_video`'=>NULL))
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getDailyClaimsTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("daily_media_claims")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getClaimsLogsTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("`media_claims_log` as M_C_L")
                            ->join('`itv` as I', 'M_C_L.`media_id`', 'I.`id` and M_C_L.`media_type` = "itv"', "LEFT")
                            ->join('`karaoke` as K', 'M_C_L.`media_id`', 'K.`id` and M_C_L.`media_type` = "karaoke"', "LEFT")
                            ->join('`video` as V', 'M_C_L.`media_id`', 'V.`id` and M_C_L.`media_type` = "video"', "LEFT")
                            ->join('`users` as U', 'M_C_L.`uid`', 'U.`id`', "LEFT")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getTvArchiveTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*", 'count(`ch_id`) as `counter`'),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("`played_tv_archive`")
                        ->join('itv', 'itv.id', 'played_tv_archive.ch_id', 'INNER')
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->groupby('ch_id')
                        ->orderby('counter', 'DESC');

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        return $obj->get()->all();
    }
    
    public function getTimeShiftTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*", 'count(`ch_id`) as `counter`'),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("`played_timeshift`")
                        ->join('itv', 'itv.id', 'played_timeshift.ch_id', 'INNER')
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->groupby('ch_id')
                        ->orderby('counter', 'DESC');

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        
        return $obj->get()->all();
    }
    
    public function getAbonentStatTotalRows($func_alias, $where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
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
        
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("users")
                        ->join("played_itv", "users.id", "played_itv.uid", "LEFT")
                        ->where($param['where'])
                        ->where(array("NOT played_itv.playtime" => NULL))
                        ->like($param['like'], 'OR')
                        ->groupby(array("users.id"))
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getAbonentStatVideoList($param, $counter = FALSE) {
        if ($counter) {
            $param['select'][] = "count(`played_video`.`id`) as `counter`";
        }
        
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("users")
                        ->join("played_video", "users.id", "played_video.uid", "LEFT")
                        ->where($param['where'])->like($param['like'], 'OR')
                        ->where(array('NOT played_video.playtime'=>NULL))
                        ->groupby(array("users.id"))
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getAbonentStatAnecList($param, $counter = FALSE) {
        if ($counter) {
            $param['select'][] = "`readed_anec`.`mac` as `mac`";
        }
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("readed_anec")->where($param['where'])->like($param['like'], 'OR')
                        ->where(array('NOT readed'=>NULL))
                        ->groupby(array("mac"))
                        ->orderby($param['order']);
        
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function getTvTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*", 'count(`played_itv`.id) as `counter`'),
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("`played_itv`")
                        ->join('itv', 'itv.id', 'played_itv.itv_id', 'LEFT')
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->groupby('itv_id');
        if (!empty($param['order'])) {
            $obj = $obj->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->get()->all();
            return count($result);
        }
        
        return $obj->get()->all();
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
        $incoming['select'] = '*';
        if ($all) {
            $incoming['like'] = array();    
        }
        return $this->getModeratorsStatList($incoming, TRUE);
    }

    public function getModeratorsStatList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])->from($param['from']);
        if (array_key_exists('joined', $param)) {
            foreach ($param['joined'] as $table => $keys) {
                $obj = $obj->join($table, $keys['left_key'], $keys['right_key'], $keys['type']);
            }
        }
        $obj = $obj->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['groupby'])) {
            $obj = $obj->groupby($param['groupby']);
        }

        if (!empty($param['limit']['limit']) && !$counter) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $obj->count()->get()->all();
            if (count($result) > 1) {
                return count($result);
            } else if (!empty ($result[0])){
                list($key, $data) = each($result[0]);
            } else {
                return 0;
            }
            return $data;
        }

        return ($counter) ? $obj : $obj->get()->all();
    }
    
    public function getAdmins($id = FALSE) {
        $obj = $this->mysqlInstance->select()->from('administrators');
        if ($id !== FALSE) {
            $obj = $obj->where(array('id'=>$id));
        }
        return $obj->orderby('login')->get()->all();
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
}