<?php

namespace Model;

class TvChannelsModel extends \Model\BaseStalkerModel {

        private $broadcasting_keys = array(
            'user_agent_filter' => '',
            'priority' => '',
            'use_http_tmp_link' => FALSE,
            'wowza_tmp_link' => '',
            'nginx_secure_link' => '',
            'flussonic_tmp_link' => '',
            'enable_monitoring' => FALSE,
            'enable_balancer_monitoring' => '',
            'monitoring_url' => '',
            'use_load_balancing' => FALSE,
            'stream_server' => ''
        );
    
    public function __construct() {
        parent::__construct();
    }

    public function getLastModifiedId() {
        return $this->mysqlInstance->from('itv')->where(array('modified!=' => ''))->orderby('modified', 'DESC')->limit(1, 0)->get()->first('id');
    }

    public function getTotalRowsAllChannels($where = array(), $like = array()) {
        $params = array(
            'where' => $where
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAllChannels($params, TRUE);
    }

    public function getAllChannels($param = array(), $counter = FALSE) {

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from("itv")
            ->join('media_claims', 'itv.id', 'media_claims.media_id and media_claims.media_type="itv"', 'LEFT')
            ->join('tv_genre', 'itv.tv_genre_id', 'tv_genre.id', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($where)) {
            $this->mysqlInstance->where($where);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], ' OR ');
        }

        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function getAllGenres() {
        return $this->mysqlInstance->from('tv_genre')->get()->all();
    }
    
    public function getChannelById($id){
        return $this->mysqlInstance->from('itv')->where(array('id' => intval($id)))->get()->first();
    }

    public function getChannelLinksById($id){
        $links = $this->mysqlInstance->select(' *, `url` as `cmd`, `status` as `monitoring_status`')->from('ch_links')->where(array('ch_id' => (int) $id))->orderby('priority')->get()->all();
        $map = array();

        foreach ($links as $link){
            $map[] = $link;
        }

        return $map;
    }
    
    public function getStreamersIdMapForLink($link_id){

        $streamers = $this->mysqlInstance->from('ch_link_on_streamer')->where(array('link_id' => $link_id))->get()->all();
        
        $map = array();
        
        foreach ($streamers as $streamer){
            $map[$streamer['streamer_id']] = $streamer;
        }

        return $map;
    }
    
    public function getAllStreamServer(){
        return $this->mysqlInstance->from('streaming_servers')->orderby('name')->get()->all();
    }
    
    public function insertITVChannel($data){
        if (!empty($data['cmd'])) {
            while(list($cmd_key, $cmd_data) = each($data['cmd'])) {
                reset($this->broadcasting_keys);
                while (list($key, $value) = each($this->broadcasting_keys)) {
                    if (array_key_exists($key, $data) and array_key_exists($cmd_key, $data[$key])) {
                        $this->broadcasting_keys[$key] |= (is_numeric($data[$key][$cmd_key])? (int) $data[$key][$cmd_key]: (!empty($data[$key][$cmd_key]) && $data[$key][$cmd_key] !== 'off'));
                    }
                }
            }
        }
        $data = array_merge($data, $this->broadcasting_keys);
        $cmd_val = FALSE;
        if (is_array($data['cmd']) && !empty($data['cmd'])) {
            $cmd_val = array_values($data['cmd']);
        }
        return $this->mysqlInstance->insert('itv', array(
                            'name' => $data['name'],
                            'number' => $data['number'],
                            'use_http_tmp_link' => (int)(!empty($data['use_http_tmp_link']) && $data['use_http_tmp_link'] !== 'off'),
                            'wowza_tmp_link' => (int)(!empty($data['wowza_tmp_link']) && $data['wowza_tmp_link'] !== 'off'),
                            'nginx_secure_link' => (int)(!empty($data['nginx_secure_link']) && $data['nginx_secure_link'] !== 'off'),
                            'enable_tv_archive' => (int)(!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off'),
                            'wowza_dvr' => (int)(!empty($data['wowza_dvr']) && $data['wowza_dvr'] !== 'off'),
                            'flussonic_dvr' => (int)(!empty($data['flussonic_dvr']) && $data['flussonic_dvr'] !== 'off'),
                            'censored' => (int)(!empty($data['censored']) && $data['censored'] !== 'off'),
                            'base_ch' => (int)(!empty($data['base_ch']) && $data['base_ch'] !== 'off'),
                            'bonus_ch' => (int)(!empty($data['bonus_ch']) && $data['bonus_ch'] !== 'off'),
                            'hd' => (int)(!empty($data['hd']) && $data['hd'] !== 'off'),
                            'cost' => (!empty($data['hd']) && is_numeric($data['hd'])? $data['hd']: 0),
                            'cmd' => ($cmd_val !== FALSE ? $cmd_val[0] : ""),
                            'cmd_1' => (!empty($data['cmd_1'])? $data['cmd_1']: ''),
                            'cmd_2' => (!empty($data['cmd_2'])? $data['cmd_2']: ''),
                            'cmd_3' => (!empty($data['cmd_3'])? $data['cmd_3']: ''),
                            'mc_cmd' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off' && !empty($data['mc_cmd'])? $data['mc_cmd']: ''),
                            'enable_wowza_load_balancing' => (int)(!empty($data['enable_wowza_load_balancing']) && $data['enable_wowza_load_balancing'] !== 'off'),
                            'allow_pvr' => (int)(!empty($data['allow_pvr']) && $data['allow_pvr'] !== 'off'),
                            'allow_local_pvr' => (int)(!empty($data['allow_local_pvr']) && $data['allow_local_pvr'] !== 'off'),
                            'allow_local_timeshift' => (int)(!empty($data['allow_local_timeshift']) && $data['allow_local_timeshift'] !== 'off'),
                            'enable_monitoring' => (int)(!empty($data['enable_monitoring']) && $data['enable_monitoring'] !== 'off'),
                            'descr' => (!empty($data['descr'])? $data['descr']: ''),
                            'tv_genre_id' => (!empty($data['tv_genre_id'])? $data['tv_genre_id']: 0),
                            'status' => 1,
                            'xmltv_id' => (!empty($data['xmltv_id'])? $data['xmltv_id']: ''),
                            'service_id' => (!empty($data['service_id'])? trim($data['service_id']): ''),
                            'volume_correction' => (!empty($data['volume_correction'])? intval($data['volume_correction']): 0),
                            'correct_time' => (!empty($data['correct_time'])? intval($data['correct_time']): 0),
                            'modified' => 'NOW()',
                            'tv_archive_duration' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off' && !empty($data['tv_archive_duration'])? intval($data['tv_archive_duration']): 0)
                        ))->insert_id();
    }
    
    public function updateITVChannel($data){
        if (!empty($data['cmd'])) {
            while(list($cmd_key, $cmd_data) = each($data['cmd'])) {
                reset($this->broadcasting_keys);
                while (list($key, $value) = each($this->broadcasting_keys)) {
                    if (array_key_exists($key, $data) and array_key_exists($cmd_key, $data[$key])) {
                        $this->broadcasting_keys[$key] |= (bool)(is_numeric($data[$key][$cmd_key])? (int) $data[$key][$cmd_key]: (!empty($data[$key][$cmd_key]) && $data[$key][$cmd_key] !== 'off'));
                    }
                }
            }
        }
        $data = array_merge($data, $this->broadcasting_keys);
        $cmd_val = FALSE;
        if (is_array($data['cmd']) && !empty($data['cmd'])) {
            $cmd_val = array_values($data['cmd']);
        }
         $input = array(
                            'name' => $data['name'],
                            'number' => $data['number'],
                            'use_http_tmp_link' => (int)(!empty($data['use_http_tmp_link']) && $data['use_http_tmp_link'] !== 'off'),
                            'wowza_tmp_link' => (int)(!empty($data['wowza_tmp_link']) && $data['wowza_tmp_link'] !== 'off'),
                            'nginx_secure_link' => (int)(!empty($data['nginx_secure_link']) && $data['nginx_secure_link'] !== 'off'),
                            'enable_tv_archive' => (int)(!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off'),
                            'wowza_dvr' => (int)(!empty($data['wowza_dvr']) && $data['wowza_dvr'] !== 'off'),
                            'flussonic_dvr' => (int)(!empty($data['flussonic_dvr']) && $data['flussonic_dvr'] !== 'off'),
                            'censored' => (int)(!empty($data['censored']) && $data['censored'] !== 'off'),
                            'base_ch' => (int)(!empty($data['base_ch']) && $data['base_ch'] !== 'off'),
                            'bonus_ch' => (int)(!empty($data['bonus_ch']) && $data['bonus_ch'] !== 'off'),
                            'hd' => (int)(!empty($data['hd']) && $data['hd'] !== 'off'),
                            'cost' => (!empty($data['hd']) && is_numeric($data['hd'])? $data['hd']: 0),
                            'cmd' => ($cmd_val !== FALSE ? $cmd_val[0] : ""),
                            'cmd_1' => (!empty($data['cmd_1'])? $data['cmd_1']: ''),
                            'cmd_2' => (!empty($data['cmd_2'])? $data['cmd_2']: ''),
                            'cmd_3' => (!empty($data['cmd_3'])? $data['cmd_3']: ''),
                            'mc_cmd' => (!empty($data['enable_tv_archive']) || !empty($data['allow_pvr'])) && !empty($data['mc_cmd']) ? $data['mc_cmd']: '',
                            'enable_wowza_load_balancing' => (int)(!empty($data['enable_wowza_load_balancing']) && $data['enable_wowza_load_balancing'] !== 'off'),
                            'allow_pvr' => (int)(!empty($data['allow_pvr']) && $data['allow_pvr'] !== 'off'),
                            'allow_local_pvr' => (int)(!empty($data['allow_local_pvr']) && $data['allow_local_pvr'] !== 'off'),
                            'allow_local_timeshift' => (int)(!empty($data['allow_local_timeshift']) && $data['allow_local_timeshift'] !== 'off'),
                            'enable_monitoring' => (int)(!empty($data['enable_monitoring']) && $data['enable_monitoring'] !== 'off'),
                            'descr' => (!empty($data['descr'])? $data['descr']: ''),
                            'tv_genre_id' => (!empty($data['tv_genre_id'])? $data['tv_genre_id']: 0),
                            'status' => 1,
                            'xmltv_id' => (!empty($data['xmltv_id'])? $data['xmltv_id']: ''),
                            'service_id' => (!empty($data['service_id'])? trim($data['service_id']): ''),
                            'volume_correction' => (!empty($data['volume_correction'])? intval($data['volume_correction']): 0),
                            'correct_time' => (!empty($data['correct_time'])? intval($data['correct_time']): 0),
                            'modified' => 'NOW()',
                            'tv_archive_duration' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off' && !empty($data['tv_archive_duration'])? intval($data['tv_archive_duration']): 0)
                        );
        if (!$input['enable_monitoring']){
            $input['monitoring_status'] = 1;
        }
        $this->mysqlInstance->update('itv', $input, array('id' => intval($data['id'])));
        return $data['id'];
    }
    
    public function insertCHLink($link) {
        return $this->mysqlInstance->insert('ch_links', $link)->insert_id();
    }
    
    public function insertCHLinkOnStreamer($link_id, $streamer_id) {
        $this->mysqlInstance->insert('ch_link_on_streamer', array('link_id' => $link_id, 'streamer_id' => $streamer_id));
    }
    
    public function getStorages() {
        return $this->mysqlInstance->from('storages')->where(array('status' => 1, 'for_records' => 1, 'wowza_server' => 0))->get()->all();
    }
    
    public function updateLogoName($id, $logo){
        $this->mysqlInstance->update('itv', array('logo' => $logo), array('id' => $id));
    }
    
    public function getFieldFirstVal($field_name, $value){
        return $this->mysqlInstance->from('itv')->where(array($field_name => $value))->get()->all($field_name);
    }
    
    public function getUnnecessaryLinks($id, $urls){
        return $this->mysqlInstance->query("select * from ch_links where ch_id='$id' and url not in ('" . implode("','", $urls) . "')")->all('id');
    }
    
    public function deleteCHLink($links) {
        $this->mysqlInstance->query("delete from ch_links where id in (" . implode(",", $links) . ")");
    }
    
    public function deleteCHLinkOnStreamer($links) {
        $this->mysqlInstance->query("delete from ch_link_on_streamer where link_id in (" . implode(",", $links) . ")");
    }
    
    public function deleteCHLinkOnStreamerByLinkAndID($link_id, $ids) {
        $this->mysqlInstance->query("delete from ch_link_on_streamer where link_id=$link_id and streamer_id in (" . implode(",", $ids) . ")");
    }
    
    public function updateCHLink($ch_id, $links) {
        $this->mysqlInstance->update('ch_links', $links, array('ch_id' => (int) $ch_id, 'url' => $links['url']));
    }
    
    public function removeChannel($id) {
        return $this->mysqlInstance->delete('itv', array('id' => (int) $id))->total_rows();
    }
    
    public function changeChannelStatus($id, $status = 0) {
        return $this->mysqlInstance->update('itv', array('status' => (empty($status) ? 0: 1)), array('id' => (int) $id))->total_rows();
    }
    
    public function updateChannelNum($row) {
        return $this->mysqlInstance->update('itv', array('number' => $row['number']), array('id' => $row['id']))->total_rows();
    }
    
    public function updateChannelLockedStatus($row) {
        return $this->mysqlInstance->update('itv', array('locked' => $row['locked']), array('id' => $row['id']))->total_rows();
    }

    public function getEPGForChannel($id, $time_from, $time_to){
        return $this->mysqlInstance->from('epg')
            ->where(array('ch_id'  => $id,'time>=' => $time_from,'time<=' => $time_to))
            ->orderby('time')
            ->get()
            ->all();
    }

    public function deleteEPGForChannel($id, $time_from, $time_to){
        return $this->mysqlInstance->delete('epg', array('ch_id'  => $id,'time>=' => $time_from,'time<=' => $time_to))->total_rows();
    }

    public function insertEPGForChannel($data){
        return $this->mysqlInstance->insert('epg', $data)->total_rows();
    }

    public function getTotalRowsEPGList($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getEPGList($params, TRUE);
    }

    public function getEPGList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("epg_setting");
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function updateEPG($param, $where) {
        $where = (is_array($where) ? $where : array('id' => $where));
        return $this->mysqlInstance->update("epg_setting", $param, $where)->total_rows();
    }

    public function insertEPG($param) {
        return $this->mysqlInstance->insert("epg_setting", $param)->insert_id();
    }

    public function deleteEPG($param) {
        return $this->mysqlInstance->delete("epg_setting", $param)->total_rows();
    }

    public function searchOneEPGParam($param = array()){
        reset($param);
        list($key, $row) = each($param);
        return $this->mysqlInstance->from('epg_setting')->where($param)->get()->first($key);
    }

    public function updateITVChannelLogo($id, $logo_name) {
        return $this->mysqlInstance->update('itv', array('logo' => $logo_name), array('id' => $id))->total_rows();
    }

    public function getCurrentTasks(){
        return $this->mysqlInstance->select('ch_id, storage_name')->from('tv_archive')->get()->all();
    }

    public function checkChannelParams($ch_id){
        return $this->mysqlInstance->from('itv')
            ->where(array(
                'id' => $ch_id,
                'NOT ISNULL(mc_cmd) AND mc_cmd<>"" and "1"' => '1'
            ))
            ->get()->count();
    }

    public function getTotalRowsTvGenresList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('tv_genre')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function getTvGenresList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('tv_genre');

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], ( array_key_exists('offset', $param['limit']) ? $param['limit']['offset']: FALSE ) );
        }
        return $this->mysqlInstance->get()->all();
    }

    public function insertTvGenres($param){
        return $this->mysqlInstance->query("INSERT INTO `tv_genre` (`title`, `number`) SELECT '$param[title]', MAX(`number`) + 1 FROM `tv_genre`")->insert_id();
    }

    public function updateTvGenres($data, $param){
        unset($data['id']);
        return $this->mysqlInstance->update('tv_genre', $data, $param)->total_rows();
    }

    public function deleteTvGenres($param){
        return $this->mysqlInstance->delete('tv_genre', $param)->total_rows();
    }

    public function getChanelDisabledLink($id, $disabled = FALSE){
        $where = array(
            'ch_id' => $id,
            'enable_monitoring' => 1);
        if ($disabled) {
            $where['status'] = 0;
        }
        return $this->mysqlInstance->from('ch_links')->where($where)->get()->all();
    }

    public function getFirstFreeChannelNumber() {
        $min = (int) $this->mysqlInstance->query("SELECT min(`itv`.`number`) as `empty_number` FROM `itv`")->first('empty_number');
        if ($min > 1) {
            return 1;
        } else {
            return $this->mysqlInstance
                ->query("SELECT (`itv`.`number`+1) as `empty_number`
                    FROM `itv`
                    WHERE (
                        SELECT 1 FROM `itv` as `st` WHERE `st`.`number` = (`itv`.`number` + 1) LIMIT 1
                    ) IS NULL
                    ORDER BY `itv`.`number`
                    LIMIT 1")
                ->first('empty_number');
        }
    }

    public function getLastChannelNumber() {
        return $this->mysqlInstance
            ->query("SELECT max(`itv`.`number`) as `last_number` FROM `itv`")
            ->first('last_number');
    }

    public function resetMediaClaims($media_id){
        return $this->mysqlInstance->update('media_claims',
            array(
                'sound_counter' => 0,
                'video_counter' => 0,
                'no_epg'        => 0,
                'wrong_epg'     => 0
            ),
            array('media_id' => intval($media_id)))->total_rows();
    }
}
