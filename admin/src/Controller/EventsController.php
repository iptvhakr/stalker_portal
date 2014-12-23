<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class EventsController extends \Controller\BaseStalkerController {
    
    private $allEvent = array(
            "send_msg" => "send_msg",
            "reboot" => "reboot",
            "reload_portal" => "reload_portal",
            "update_channels" => "update_channels",
            "play_channel" => "play_channel",
            "mount_all_storages" => "mount_all_storages",
            "cut_off" => "cut_off",
            "update_image" => "update_image"
        );

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------
    
    public function index() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->events_list_json();

        if(!empty($this->data['uid']) || !empty($this->data['filters']['mac'])){
            $param = array();
            if (!empty($this->data['uid'])) {
                $param['id'] = $this->data['uid'];
            }
            
            if (!empty($this->data['filters']['mac'])) {
                $param['mac'] = $this->data['filters']['mac'];
            }
            
            $currentUser = $this->db->getUser($param);
            $this->app['currentUser'] = array(
                'name' => $currentUser['fname'],
                'mac' => $currentUser['mac'],
                'uid' => $currentUser['id']
            );
        }
        $this->app['eventList'] = $list['data'];
        $this->app['allEvent'] = $this->allEvent;
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['consoleGroup'] = $this->db->getConsoleGroup();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    //----------------------- ajax method --------------------------------------
    
    public function events_list_json(){
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $filds_for_select = array(
            'events_id' => "events.`id` as `events_id`",
            'addtime' => "CAST(events.`addtime` AS CHAR) as `addtime`",
            'eventtime' => "CAST(events.`eventtime` AS CHAR) as `eventtime`",
            'mac' => "users.`mac` as `mac`",
            'event' => "events.`event` as `event`",
            'msg' => "events.`msg` as `msg`",
            'sended' => "events.`sended` as `sended`",
            'uid' => "events.`uid` as `uid`",
            'name' => "users.`fname` as `name`"
        );

        $error = "";
        
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array( '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getEventsFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'uid';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        $response['recordsTotal'] = $this->db->getTotalRowsEventsList();      
        $response["recordsFiltered"] = $this->db->getTotalRowsEventsList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getEventsList($query_param);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->gererateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function add_event(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['user_list_type']) || empty($this->postData['event'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addEvent';
        $data['msg'] = 'Добавлено ';
        $error = 'Ошибка. Событие не добавлено.';
        
        $event = new \SysEvent();
        $event->setTtl($this->postData['ttl']);
        $get_list_func_name = 'get_userlist_' . str_replace('to_', '', $this->postData['user_list_type']);
        $set_event_func_name = 'set_event_' . str_replace('to_', '', $this->postData['event']);
        $user_list = $this->$get_list_func_name($event);
        if ($this->$set_event_func_name($event, $user_list)){
            $data['msg'] .= count($user_list) . ' пользователям';
            $error = '';
        }

        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function upload_list_addresses() {
        if (!$this->isAjax || $this->method != 'POST' || empty($_FILES)) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'addAddressList';
        $data['msg'] = 'Добавлено ';
        $data['fname'] = '';
        $error = 'Ошибка. Файл не содержит действительных MAC-адресов.';
        
        list($key, $tmp) = each($_FILES);
        $file_data = file_get_contents($tmp['tmp_name']);
        $list = array();
        
        preg_match_all('/([0-9a-fA-F]{2}:){5}([0-9a-fA-F]{2})/', $file_data, $list);
        if (!empty($list) && !empty($list[0])) {
            $file_name = tempnam(sys_get_temp_dir(), 'MAC');
            $data['fname'] = basename($file_name);
            $file_data = implode(';', $list[0]);
            file_put_contents($file_name, $file_data);
            $data['msg'] .= count($list[0]) . ' адресов';
            $error = '';
        }
        
        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function clean_events() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['uid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'cleanEvents';
        $data['msg'] = 'Удалено ' . $this->db->deleteEventsByUID($this->postData['uid']) . ' событий';
        $error = '';
        
        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getEventsFilters() {
        $return = array();

        if (!empty($this->data['filters']) && !empty($this->data['filters']['event'])) {
            $return['event'] = $this->data['filters']['event'];
        }

        if (!empty($this->data['uid'])) {
            $return['uid'] = $this->data['uid'];
        }
        
        $this->app['filters'] = !empty($this->data['filters']) ? $this->data['filters'] : array();
        return $return;
    }
    
    private function get_userlist_all(&$event){
        $user_list = array();
        if ($this->postData['event'] == 'send_msg'){
            $event->setUserListByMac('all');
            $user_list = \Middleware::getOnlineUsersId();
        }else{
            $event->setUserListByMac('online');
            $user_list = \Middleware::getAllUsersId();
        }
        return $user_list;
    }
    
    private function get_userlist_by_user_list(&$event){
        $user_list = array();
        
        if ($this->postData['user_list_type'] == 'by_user_list' && !empty($this->postData['file_name'])) {
            $file_name = sys_get_temp_dir() . "/" . $this->postData['file_name'];
            if (is_writable($file_name)) {
                $file_data = explode(';', file_get_contents($file_name));
                foreach ($file_data as $mac){
                    $uid = \Middleware::getUidByMac($mac);
                    if ($uid){
                        $user_list[] = $uid;
                    }
                }
            }
            @unlink($file_name);
        }
        
        $event->setUserListById($user_list);
        return $user_list;
    }
    
    private function get_userlist_by_group(&$event){
        $user_list = array();
        if (intval($this->postData['group_id']) > 0){
            $user_list = $this->db->getConsoleInGroup(array('stb_group_id' => $this->postData['group_id']));
            $user_list = $this->getFieldFromArray($user_list, 'id');
        }
        $event->setUserListById($user_list);
        return $user_list;
    }
    
    private function get_userlist_by_pattern(&$event){
        $user_list = array();
        if (!empty($this->postData['pattern'])) {
            $param = array();
            if ($this->postData['pattern'] == 'MAG100') {
                $param['hd'] = 0;
            } else {
                $param['stb_type'] = $this->postData['pattern'];
            }
            $user_list = \Middleware::getUidsByPattern($param);
        }
        $event->setUserListById($user_list);
        return $user_list;
    }
    
    private function get_userlist_single(&$event){
        $event->setUserListByMac($this->postData['mac']);
        $user_list = \Middleware::getUidByMac($this->postData['mac']);
        $user_list = array($user_list);
        return $user_list;
    }
    
    private function set_event_send_msg(&$event, $user_list){
        if (!empty($this->postData['need_reboot'])) {
            $event->sendMsgAndReboot($this->postData['msg']);
        } else {
            $event->sendMsg($this->postData['msg']);
        }
        return TRUE;
    }
    
    private function set_event_reboot(&$event, $user_list){
        $event->sendReboot();
        return TRUE;
    }
    
    private function set_event_reload_portal(&$event, $user_list){
        $event->sendReloadPortal();
        return TRUE;
    }
    
    private function set_event_update_channels(&$event, $user_list){
        $event->sendUpdateChannels();
        return TRUE;
    }
    
    private function set_event_play_channel(&$event, $user_list){
        $event->sendPlayChannel($this->postData['channel']);
        return TRUE;
    }
    
    private function set_event_mount_all_storages(&$event, $user_list){
        $_SERVER['TARGET'] = 'ADM';
        $event->sendMountAllStorages();
        return TRUE;
    }
    
    private function set_event_cut_off(&$event, $user_list){
    if (!is_array($user_list)){
            $user_list = array($user_list);
        }
        $this->db->updateUser(array("status"=>1, "last_change_status"=>"NOW()"), "id in (".implode(",", $user_list).")");
        $event->sendCutOff();
        return TRUE;
    }
    private function set_event_update_image(&$event, $user_list){
        $event->sendUpdateImage();
        return TRUE;
    }
}
