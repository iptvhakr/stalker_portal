<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class EventsController extends \Controller\BaseStalkerController {
    
    protected $formEvent = array();
    protected $hiddenEvent = array();
    protected $sendedStatus = array();
    protected $receivingStatus = array();


    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->formEvent = array(
            array("id" => "send_msg",           "title" => $this->setlocalization('Sending a message')),
            array("id" => "reboot",             "title" => $this->setlocalization('Reboot')),
            array("id" => "reload_portal",      "title" => $this->setlocalization('Restart the portal')),
            array("id" => "update_channels",    "title" => $this->setlocalization('Update channel list')),
            array("id" => "play_channel",       "title" => $this->setlocalization('Playback channel')),
            array("id" => "mount_all_storages", "title" => $this->setlocalization('Mount all storages')),
            array("id" => "cut_off",            "title" => $this->setlocalization('Turn off')),
            array("id" => "update_image",       "title" => $this->setlocalization('Image update'))
        );
        $this->hiddenEvent = array(
            array("id" => "update_epg",                 "title" => $this->setlocalization('EPG update')),
            array("id" => "update_subscription",        "title" => $this->setlocalization('Subscribe update')),
            array("id" => "update_modules",             "title" => $this->setlocalization('Modules update')),
            array("id" => "cut_on",                     "title" => $this->setlocalization('Turn on')),
            array("id" => "show_menu",                  "title" => $this->setlocalization('Show menu')),
            array("id" => "additional_services_status", "title" => $this->setlocalization('Status additional service'))
        );

        $this->sendedStatus = array(
            array("id" => 1 , "title" => $this->setlocalization('Not delivered')),
            array("id" => 2 , "title" => $this->setlocalization('Delivered'))
        );

        $this->receivingStatus = array(
            array("id" => 1 , "title" => $this->setlocalization('Not received')),
            array("id" => 2 , "title" => $this->setlocalization('Received'))
        );
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
            } elseif (!empty($this->data['filters']['mac'])) {
                $param['mac'] = $this->data['filters']['mac'];
            }

            $currentUser = $this->db->getUser($param);
            $this->app['currentUser'] = array(
                'name' => $currentUser['fname'],
                'mac' => (!empty($this->data['filters']['mac'])? $this->data['filters']['mac']: $currentUser['mac']),
                'uid' => (!empty($this->data['uid'])? $this->data['uid']: $currentUser['id'])
            );
        }
        $this->app['eventList'] = $list['data'];
        $this->app['formEvent'] = $this->formEvent;
        $this->app['allEvent'] = array_merge($this->formEvent, $this->hiddenEvent);
        $this->app['sendedStatus'] = $this->sendedStatus;
        $this->app['receivingStatus'] = $this->receivingStatus;
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['consoleGroup'] = $this->db->getConsoleGroup();
        
        if (!empty($this->app['currentUser'])) {
            $this->app['breadcrumbs']->addItem($this->setlocalization('Users events') . " {$this->app['currentUser']['name']} ({$this->app['currentUser']['mac']})");
        }

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
            'ended' => "events.`ended` as `ended`",
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
        $allevents = $this->formEvent;
        $allevents = array_combine($this->getFieldFromArray($allevents, 'id'), $this->getFieldFromArray($allevents, 'title'));
        
        $hiddenevents = $this->hiddenEvent;
        $hiddenevents = array_combine($this->getFieldFromArray($hiddenevents, 'id'), $this->getFieldFromArray($hiddenevents, 'title'));
        
        $events = array_merge($allevents, $hiddenevents);

        $response['data'] = array_map(function($row) use ($events){
            $row['event'] = $events[$row['event']];
            $row['mac'] = (!empty($row['mac']) ? $row['mac']: 'no_mac_address');
            $row['addtime'] = (int)  strtotime($row['addtime']);
            $row['eventtime'] = (int)  strtotime($row['eventtime']);
            return $row;
        }, $response['data']);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function add_event(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['user_list_type']) || empty($this->postData['event'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addEvent';
        $data['msg'] = $this->setlocalization('Added') . ' ' . $this->setlocalization('for'). ' ';
        $error = $this->setlocalization('Error. Event has not been added.');
        
        $_SERVER['TARGET'] = 'ADM';
        $event = new \SysEvent();
        $event->setTtl($this->postData['ttl']);
        $get_list_func_name = 'get_userlist_' . str_replace('to_', '', $this->postData['user_list_type']);
        $set_event_func_name = 'set_event_' . str_replace('to_', '', $this->postData['event']);
        $user_list = $this->$get_list_func_name($event);
//        $event->setUserListById($user_list);
        if ($this->$set_event_func_name($event, $user_list)){
            $data['msg'] .= count($user_list). ' ' . $this->setlocalization('users');
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function upload_list_addresses() {
        if (!$this->isAjax || $this->method != 'POST' || empty($_FILES)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'addAddressList';
        $data['msg'] = $this->setlocalization('Added');
        $data['fname'] = '';
        $error = $this->setlocalization('Error. The file does not contain valid MAC-addresses.');
        
        list($key, $tmp) = each($_FILES);
        $file_data = file_get_contents($tmp['tmp_name']);
        $list = array();
        
        preg_match_all('/([0-9a-fA-F]{2}:){5}([0-9a-fA-F]{2})/', $file_data, $list);
        if (!empty($list) && !empty($list[0])) {
            $file_name = tempnam(sys_get_temp_dir(), 'MAC');
            $data['fname'] = basename($file_name);
            $file_data = implode(';', $list[0]);
            file_put_contents($file_name, $file_data);
            $data['msg'] .= count($list[0]) . ' ' . $this->setlocalization('addresses');
            $error = '';
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function clean_events() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['uid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'cleanEvents';
        $data['msg'] = $this->setlocalization('Deleted') . ' ' . $this->db->deleteEventsByUID($this->postData['uid']) . ' ' . $this->setlocalization('events');
        $error = '';
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getEventsFilters() {
        $return = array();

        if (!empty($this->data['filters']) && !empty($this->data['filters']['event'])) {
            $return['event'] = $this->data['filters']['event'];
        }

        if (!empty($this->data['filters']) && !empty($this->data['filters']['sended'])) {
            $return['sended'] = (int)$this->data['filters']['sended'] - 1;
        }
        
        if (!empty($this->data['filters']) && !empty($this->data['filters']['ended'])) {
            $return['ended'] = (int)$this->data['filters']['ended'] - 1;
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
            $user_list = \Middleware::getAllUsersId();
        }else{
            $event->setUserListByMac('online');
            $user_list = \Middleware::getOnlineUsersId();
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
