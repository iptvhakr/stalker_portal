<?php

class AdminPanelEvents extends SysEvent {

    private $postData = array();
    private $user_list = array();

    public function __construct($postData = array()){
        parent::__construct();
        $this->postData = $postData;
    }

    public function setPostData($postData){
        $this->postData = $postData;
        return $this;
    }

    public function get_userlist_all() {
        if (array_key_exists('event', $this->postData) && $this->postData['event'] == 'send_msg' || $this->postData['event'] == 'send_msg_with_video') {
            $this->user_list = \Middleware::getAllUsersId();
        } else {
            $this->user_list = \Middleware::getOnlineUsersId();
        }
        return $this;
    }

    public function get_userlist_by_user_list() {
        if (array_key_exists('user_list_type', $this->postData) && $this->postData['user_list_type'] == 'by_user_list' && !empty($this->postData['file_name'])) {
            $file_name = sys_get_temp_dir() . "/" . $this->postData['file_name'];
            if (is_writable($file_name)) {
                $file_data = explode(';', file_get_contents($file_name));
                foreach ($file_data as $mac) {
                    $uid = \Middleware::getUidByMac($mac);
                    if ($uid) {
                        $this->user_list[] = $uid;
                    }
                }
            }
            @unlink($file_name);
        }

        return $this;
    }

    public function get_userlist_by_group() {
        if (array_key_exists('group_id', $this->postData) && intval($this->postData['group_id']) > 0) {
            $this->user_list = Mysql::getInstance()->from('stb_in_group')->where(array('stb_group_id' => $this->postData['group_id']))->get()->all('uid');
        }
        return $this;
    }

    public function get_userlist_by_pattern() {
        if (!empty($this->postData['pattern'])) {
            $param = array();
            if ($this->postData['pattern'] == 'MAG100') {
                $param['hd'] = 0;
            } else {
                $param['stb_type'] = $this->postData['pattern'];
            }
            $this->user_list = \Middleware::getUidsByPattern($param);
        }
        return $this;
    }

    public function get_userlist_by_filter() {

        if (!empty($this->postData['filter_set'])) {

            $filter_set = \Filters::getInstance();
            $filter_set->setResellerID(array_key_exists('reseller', $this->postData) ? $this->postData['reseller'] : 0);
            $filter_set->initData('users', 'id');

            $curr_filter_set = \Mysql::getInstance()->from('filter_set')->where(array('id' => $this->postData['filter_set']))->get()->first();
            if (!empty($curr_filter_set) && $unserialize_data = @unserialize($curr_filter_set['filter_set'])) {
                $filter_data = array();
                foreach ($unserialize_data as $row) {
                    $filter_data[$row[0]] = $row;
                }
                $filters_with_cond = array_filter(array_map(function ($row) use ($filter_data) {
                    if (array_key_exists($row['text_id'], $filter_data)) {
                        $value = (($row['text_id'] == 'status') || ($row['text_id'] == 'state') ? (int)($filter_data[$row['text_id']][2] - 1 > 0) : $filter_data[$row['text_id']][2]);
                        return array(
                            $row['method'],
                            $filter_data[$row['text_id']][1],
                            $value
                        );
                    }
                }, $filter_set->getFilters()));

                $filter_set->setFilters($filters_with_cond);
                $this->user_list = $filter_set->getData();
            }
        }
        return $this;
    }

    public function get_userlist_single() {
        $this->user_list = array(\Middleware::getUidByMac($this->postData['mac']));
        return $this;
    }

    public function cleanAndSetUsers(){
        $all_users = \Mysql::getInstance()->from('users')->get()->all('id');
        if (!empty($all_users)) {
            $this->user_list = array_intersect($this->user_list, $all_users);
        }
        $this->setUserListById($this->user_list);
        return $this;
    }

    public function set_event_send_msg() {
        if (array_key_exists('msg', $this->postData) && array_key_exists('header', $this->postData)) {
            if (!empty($this->postData['need_reboot'])) {
                $this->sendMsgAndReboot($this->postData['msg'], $this->postData['header']);
            } else {
                $this->sendMsg($this->postData['msg'], $this->postData['header']);
            }
            return TRUE;
        }
        return FALSE;
    }

    public function set_event_reboot() {
        $this->sendReboot();
        return TRUE;
    }

    public function set_event_reload_portal() {
        $this->sendReloadPortal();
        return TRUE;
    }

    public function set_event_update_channels() {
        $this->sendUpdateChannels();
        return TRUE;
    }

    public function set_event_play_channel() {
        if (array_key_exists('channel', $this->postData)) {
            $this->sendPlayChannel($this->postData['channel']);
            return TRUE;
        }
        return FALSE;
    }

    public function set_event_play_radio_channel() {
        if (array_key_exists('channel', $this->postData)) {
            $this->sendPlayRadioChannel($this->postData['channel']);
            return TRUE;
        }
        return FALSE;
    }

    public function set_event_mount_all_storages() {
        $this->sendMountAllStorages();
        return TRUE;
    }

    public function set_event_cut_off() {
        if (!is_array($this->user_list)) {
            $this->user_list = array($this->user_list);
        }
        \Mysql::getInstance()->where( "id in (" . implode(",", $this->user_list) . ")")->update('users',  array("status" => 1, "last_change_status" => "NOW()" ));
        $this->sendCutOff();
        return TRUE;
    }

    public function set_event_update_image() {
        $this->sendUpdateImage();
        return TRUE;
    }

    public function set_event_send_msg_with_video() {
        if (array_key_exists('msg', $this->postData) && array_key_exists('header', $this->postData) && !empty($this->postData['video_url'])) {
            $this->sendMsgWithVideo($this->postData['msg'], $this->postData['video_url'], $this->postData['header']);
            return TRUE;
        }
        return FALSE;
    }

    public function getUserList(){
        return $this->user_list;
    }
}