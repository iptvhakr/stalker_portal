<?php

namespace Model;

class TvChannelsModel extends \Model\BaseStalkerModel {

        private $broadcasting_keys = array(
            'cmd' => array(''),
            'user_agent_filter' => array(''),
            'priority' => array(''),
            'use_http_tmp_link' => array(FALSE),
            'wowza_tmp_link' => array(''),
            'nginx_secure_link' => array(''),
            'flussonic_tmp_link' => array(''),
            'enable_monitoring' => array(FALSE),
            'enable_balancer_monitoring' => array(''),
            'monitoring_url' => array(''),
            'use_load_balancing' => array(FALSE),
            'stream_server' => array('')
        );
    
    public function __construct() {
        parent::__construct();
    }

    public function getLastModifiedId() {
        return $this->mysqlInstance->from('itv')->where(array('modified!=' => ''))->orderby('modified', 'DESC')->limit(1, 0)->get()->first('id');
    }

    public function getAllChannels($filter = '') {
        $where = '';
        if (!empty($filter)) {
            $where = "where " . (is_string($filter)? $filter: (is_array($filter)? implode(' and ', $filter): ''));
        }
        return $this->mysqlInstance->query("select itv.*, tv_genre.title as genres_name, media_claims.media_type, media_claims.media_id, "
                                                . "media_claims.sound_counter, media_claims.video_counter, media_claims.no_epg, "
                                                . "media_claims.wrong_epg "
                                            . "from itv "
                                                . "left join media_claims on itv.id=media_claims.media_id and media_claims.media_type='itv' "
                                                . "left join tv_genre on itv.tv_genre_id=tv_genre.id "
                                            . " $where "
                                            . "group by itv.id "
                                            . "order by number")->all();
    }

    public function getAllGenres() {
        return $this->mysqlInstance->from('tv_genre')->get()->all();
    }
    
    public function getChannelById($id){
        return $this->mysqlInstance->from('itv')->where(array('id' => intval($id)))->get()->first();
    }

    public function getChannelLinksById($id){
        $links = $this->mysqlInstance->select(' *, `url` as `cmd` ')->from('ch_links')->where(array('ch_id' => (int) $id))->orderby('priority')->get()->all();
        $map = array();

        foreach ($links as $link){
            $map[$link['id']] = $link;
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
                foreach ($this->broadcasting_keys as $key => $value) {
                    if (!isset($data[$key][$cmd_key])) {
                        $data[$key][$cmd_key] = $value;
                    }
                }
            }
        }
        $cmd_val = FALSE;
        if (is_array($data['cmd']) && !empty($data['cmd'])) {
            $cmd_val = array_values($data['cmd']);
        }
        return $this->mysqlInstance->insert('itv', array(
                            'name' => $data['name'],
                            'number' => $data['number'],
                            'use_http_tmp_link' => (int)(!empty($data['use_http_tmp_link']) && $data['use_http_tmp_link'] != 'off'),
                            'wowza_tmp_link' => (int)(!empty($data['wowza_tmp_link']) && $data['wowza_tmp_link'] != 'off'),
                            'nginx_secure_link' => (int)(!empty($data['nginx_secure_link']) && $data['nginx_secure_link'] != 'off'),
                            'wowza_dvr' => (int)(!empty($data['wowza_dvr']) && $data['wowza_dvr'] != 'off'),
                            'censored' => (int)(!empty($data['censored']) && $data['censored'] != 'off'),
                            'base_ch' => (int)(!empty($data['base_ch']) && $data['base_ch'] != 'off'),
                            'bonus_ch' => (int)(!empty($data['bonus_ch']) && $data['bonus_ch'] != 'off'),
                            'hd' => (int)(!empty($data['hd']) && $data['hd'] != 'off'),
                            'cost' => (!empty($data['hd']) && is_numeric($data['hd'])? $data['hd']: 0),
                            'cmd' => ($cmd_val !== FALSE ? $cmd_val[0] : ""),
                            'cmd_1' => (!empty($data['cmd_1'])? $data['cmd_1']: ''),
                            'cmd_2' => (!empty($data['cmd_2'])? $data['cmd_2']: ''),
                            'cmd_3' => (!empty($data['cmd_3'])? $data['cmd_3']: ''),
                            'mc_cmd' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off' && !empty($data['mc_cmd'])? $data['mc_cmd']: ''),
                            'enable_wowza_load_balancing' => (int)(!empty($data['enable_wowza_load_balancing']) && $data['enable_wowza_load_balancing'] != 'off'),
                            'enable_tv_archive' => (int)(!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off'),
                            'allow_pvr' => (int)(!empty($data['allow_pvr']) && $data['allow_pvr'] != 'off'),
                            'allow_local_pvr' => (int)(!empty($data['allow_local_pvr']) && $data['allow_local_pvr'] != 'off'),
                            'allow_local_timeshift' => (int)(!empty($data['allow_local_timeshift']) && $data['allow_local_timeshift'] != 'off'),
                            'enable_monitoring' => (int)(!empty($data['enable_monitoring']) && $data['enable_monitoring'] != 'off'),
                            'descr' => (!empty($data['descr'])? $data['descr']: ''),
                            'tv_genre_id' => (!empty($data['tv_genre_id'])? $data['tv_genre_id']: 0),
                            'status' => 1,
                            'xmltv_id' => (!empty($data['xmltv_id'])? $data['xmltv_id']: ''),
                            'service_id' => (!empty($data['service_id'])? trim($data['service_id']): ''),
                            'volume_correction' => (!empty($data['volume_correction'])? intval($data['volume_correction']): 0),
                            'correct_time' => (!empty($data['correct_time'])? intval($data['correct_time']): 0),
                            'modified' => 'NOW()',
                            'tv_archive_duration' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off' && !empty($data['tv_archive_duration'])? intval($data['tv_archive_duration']): 0)
                        ))->insert_id();
    }
    
    public function updateITVChannel($data){
        if (!empty($data['cmd'])) {
            while(list($cmd_key, $cmd_data) = each($data['cmd'])) {
                foreach ($this->broadcasting_keys as $key => $value) {
                    if (!array_key_exists($key, $data) || !is_array($data[$key]) || !array_key_exists($cmd_key, $data[$key])) {
                        $data[$key][$cmd_key] = $value;
                    }
                }
            }
        }
        $cmd_val = FALSE;
        if (is_array($data['cmd']) && !empty($data['cmd'])) {
            $cmd_val = array_values($data['cmd']);
        }
        $this->mysqlInstance->update('itv', array(
                            'name' => $data['name'],
                            'number' => $data['number'],
                            'use_http_tmp_link' => (int)(!empty($data['use_http_tmp_link']) && $data['use_http_tmp_link'] != 'off'),
                            'wowza_tmp_link' => (int)(!empty($data['wowza_tmp_link']) && $data['wowza_tmp_link'] != 'off'),
                            'nginx_secure_link' => (int)(!empty($data['nginx_secure_link']) && $data['nginx_secure_link'] != 'off'),
                            'wowza_dvr' => (int)(!empty($data['wowza_dvr']) && $data['wowza_dvr'] != 'off'),
                            'censored' => (int)(!empty($data['censored']) && $data['censored'] != 'off'),
                            'base_ch' => (int)(!empty($data['base_ch']) && $data['base_ch'] != 'off'),
                            'bonus_ch' => (int)(!empty($data['bonus_ch']) && $data['bonus_ch'] != 'off'),
                            'hd' => (int)(!empty($data['hd']) && $data['hd'] != 'off'),
                            'cost' => (!empty($data['hd']) && is_numeric($data['hd'])? $data['hd']: 0),
                            'cmd' => ($cmd_val !== FALSE ? $cmd_val[0] : ""),
                            'cmd_1' => (!empty($data['cmd_1'])? $data['cmd_1']: ''),
                            'cmd_2' => (!empty($data['cmd_2'])? $data['cmd_2']: ''),
                            'cmd_3' => (!empty($data['cmd_3'])? $data['cmd_3']: ''),
                            'mc_cmd' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off' && !empty($data['mc_cmd'])? $data['mc_cmd']: ''),
                            'enable_wowza_load_balancing' => (int)(!empty($data['enable_wowza_load_balancing']) && $data['enable_wowza_load_balancing'] != 'off'),
                            'enable_tv_archive' => (int)(!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off'),
                            'allow_pvr' => (int)(!empty($data['allow_pvr']) && $data['allow_pvr'] != 'off'),
                            'allow_local_pvr' => (int)(!empty($data['allow_local_pvr']) && $data['allow_local_pvr'] != 'off'),
                            'allow_local_timeshift' => (int)(!empty($data['allow_local_timeshift']) && $data['allow_local_timeshift'] != 'off'),
                            'enable_monitoring' => (int)(!empty($data['enable_monitoring']) && $data['enable_monitoring'] != 'off'),
                            'descr' => (!empty($data['descr'])? $data['descr']: ''),
                            'tv_genre_id' => (!empty($data['tv_genre_id'])? $data['tv_genre_id']: 0),
                            'status' => 1,
                            'xmltv_id' => (!empty($data['xmltv_id'])? $data['xmltv_id']: ''),
                            'service_id' => (!empty($data['service_id'])? trim($data['service_id']): ''),
                            'volume_correction' => (!empty($data['volume_correction'])? intval($data['volume_correction']): 0),
                            'correct_time' => (!empty($data['correct_time'])? intval($data['correct_time']): 0),
                            'modified' => 'NOW()',
                            'tv_archive_duration' => (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] != 'off' && !empty($data['tv_archive_duration'])? intval($data['tv_archive_duration']): 0)
                        ), array('id' => intval($data['id']))
                );
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
}
