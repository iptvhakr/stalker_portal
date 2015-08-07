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
            array("id" => "send_msg",           "title" => $this->setlocalization('Sending a message') ),
            array("id" => "send_msg_with_video","title" => $this->setlocalization('Sending a message with video') ),
            array("id" => "reboot",             "title" => $this->setlocalization('Reboot') ),
            array("id" => "reload_portal",      "title" => $this->setlocalization('Restart the portal') ),
            array("id" => "update_channels",    "title" => $this->setlocalization('Update channel list') ),
            array("id" => "play_channel",       "title" => $this->setlocalization('Playback channel') ),
            array("id" => "play_radio_channel", "title" => $this->setlocalization('Playback radio channel') ),
            array("id" => "mount_all_storages", "title" => $this->setlocalization('Mount all storages') ),
            array("id" => "cut_off",            "title" => $this->setlocalization('Turn off') ),
            array("id" => "update_image",       "title" => $this->setlocalization('Image update') )
        );
        $this->hiddenEvent = array(
            array("id" => "update_epg",                 "title" => $this->setlocalization('EPG update') ),
            array("id" => "update_subscription",        "title" => $this->setlocalization('Subscribe update') ),
            array("id" => "update_modules",             "title" => $this->setlocalization('Modules update') ),
            array("id" => "cut_on",                     "title" => $this->setlocalization('Turn on') ),
            array("id" => "show_menu",                  "title" => $this->setlocalization('Show menu') ),
            array("id" => "additional_services_status", "title" => $this->setlocalization('Status additional service') )
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

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/events');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function events() {
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

        $filter_set = \Filters::getInstance();
        $filter_set->setResellerID($this->app['reseller']);
        $filter_set->initData('users', 'id');

        $self = $this;

        $this->app['allFilters'] = array_map(function($row) use ($filter_set, $self){
            if(($filter_set_data = @unserialize($row['filter_set'])) !== FALSE){
                $row['filter_set'] = '';
                foreach($filter_set_data as $data_row){
                    $filter_set_filter = $filter_set->getFilters(array($data_row[0]));
                    $row_filter_set = $self->setLocalization($filter_set_filter[0]['title']).': ';
                    if (!empty($filter_set_filter[0]['values_set']) && is_array($filter_set_filter[0]['values_set'])) {
                        foreach($filter_set_filter[0]['values_set'] as $filter_row){
                            if ($data_row[2] == $filter_row['value'] ) {
                                $row_filter_set .= $self->setLocalization($filter_row['title']).'; ';
                            }
                        }
                    } else {
                        $row_filter_set .= $data_row[2].'; ';
                    }
                    $row['filter_set'] .= $row_filter_set;
                }
            }
            settype($row['favorites'], 'int');
            settype($row['for_all'], 'int');
            return $row;
        }, $this->db->getAllFromTable('filter_set', 'title'));

        $this->app['messagesTemplates'] = $this->db->getAllFromTable('messages_templates', 'title');

        if (!empty($this->app['currentUser'])) {
            $this->app['breadcrumbs']->addItem($this->setlocalization('Users events') . " {$this->app['currentUser']['name']} ({$this->app['currentUser']['mac']})");
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function message_templates(){

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getMessagesTemplatesDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allAdmins'] = $this->db->getAllFromTable('administrators', 'login');

        $list = $this->message_templates_list_json();
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        if (!empty($this->data['filters'])) {
            $this->app['filters'] = $this->data['filters'];
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
            $query_param['limit']['limit'] = 50;
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
        $user_list = array_intersect($this->$get_list_func_name($event), $this->getFieldFromArray($this->db->getUser(array(), 'ALL'), 'id'));
        $event->setUserListById($user_list);

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
        $result = $this->postData['uid'] == 'all' ? $this->db->deleteAllEvents() : $this->db->deleteEventsByUID($this->postData['uid']);
        $data['msg'] = $this->setlocalization('Deleted') . ' ' . (is_numeric($result)? $result: $this->setLocalization($result)) . ' ' . $this->setlocalization('events');
        $error = '';
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function message_templates_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $error = $this->setLocalization("Error");
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filds_for_select = $this->getMsgTemplatesFields();
        if (!empty($query_param['select'])) {
            $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        } else {
            $query_param['select'] = $filds_for_select;
        }

        if (!empty($this->data['filters']['admin_id'])) {
            $query_param['where']['M_T.author'] = $this->data['filters']['admin_id'];
        }

        if (array_key_exists('id', $this->postData)) {
            $query_param['where'] = array('M_T.id'=>$this->postData['id']);
            $response['action'] = 'fillModalForm';
        }

        $response['recordsTotal'] = $this->db->getTotalRowsMsgTemplates();
        $response["recordsFiltered"] = $this->db->getTotalRowsMsgTemplates($query_param['where'], $query_param['like']);

        $response['data'] = array_map(function($row){
            $row['created'] = (int)strtotime($row['created']) * 1000;
            $row['edited'] = (int)strtotime($row['edited']) * 1000;
            return $row;
        }, $this->db->getMsgTemplates($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = '';

        if ($this->isAjax) {

            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function save_message_template(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['msg_tpl'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setlocalization($this->setlocalization('Not enough data'));

        $tpl_data['params'] = $this->postData['msg_tpl'];
        $tpl_data['params']['author'] = $tpl_data['params']['admin_id'];

        if (!empty($this->postData['msg_tpl']['id'])) {
            $operation = 'update';
            $tpl_data['id'] = $this->postData['msg_tpl']['id'];
        } else {
            $operation = 'insert';
            $tpl_data['params']['created'] = "NOW()";

        }
        unset($tpl_data['params']['id']);
        unset($tpl_data['params']['admin_id']);

        $return_id = 0;
        if ($return_id = call_user_func_array(array($this->db, $operation."MsgTemplate"), $tpl_data)) {
            $error = '';
            if ($operation == 'insert') {
                $data['return_id'] = $return_id;
            }
        } else {
            $data['msg'] = $error = $this->setlocalization($this->setlocalization('Nothing to do'));
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_template(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setlocalization($this->setlocalization('Failed'));

        if ($error = $this->db->deleteMsgTemplate($this->postData['id'])) {
            $error = '';
        }

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
        if ($this->postData['event'] == 'send_msg' || $this->postData['event'] == 'send_msg_with_video'){
            $user_list = \Middleware::getAllUsersId();
        }else{
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
        
        return $user_list;
    }
    
    private function get_userlist_by_group(&$event){
        $user_list = array();
        if (intval($this->postData['group_id']) > 0){
            $user_list = $this->getFieldFromArray($this->db->getConsoleInGroup(array('stb_group_id' => $this->postData['group_id'])), 'id');
        }
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
        return $user_list;
    }

    private function get_userlist_by_filter(&$event){
        $user_list = array();
        if (!empty($this->postData['filter_set'])) {

            $filter_set = \Filters::getInstance();
            $filter_set->setResellerID($this->app['reseller']);
            $filter_set->initData('users', 'id');

            $curr_filter_set = $this->db->getFilterSet(array('id' => $this->postData['filter_set']));
            if (!empty($curr_filter_set) && is_array($curr_filter_set) && count($curr_filter_set) > 0) {
                $filter_data = @unserialize($curr_filter_set[0]['filter_set']);
                $filter_data = array_combine($this->getFieldFromArray($filter_data, 0), array_values($filter_data));
                $filters_with_cond = array_filter(array_map(function($row) use ($filter_data) {
                    if (array_key_exists($row['text_id'], $filter_data)) {
                        $value = (($row['text_id'] == 'status') || ($row['text_id'] == 'state') ? (int)($filter_data[$row['text_id']][2] - 1 > 0) : $filter_data[$row['text_id']][2]);
                        return array($row['method'], $filter_data[$row['text_id']][1], $value);
                    }
                }, $filter_set->getFilters()));

                $filter_set->setFilters($filters_with_cond);
                $user_list = $filter_set->getData();
            }
        }
        return $user_list;
    }
    
    private function get_userlist_single(&$event){
        $user_list = \Middleware::getUidByMac($this->postData['mac']);
        $user_list = array($user_list);
        return $user_list;
    }
    
    private function set_event_send_msg(&$event, $user_list){
        if (!empty($this->postData['need_reboot'])) {
            $event->sendMsgAndReboot($this->postData['msg'], $this->postData['header']);
        } else {
            $event->sendMsg($this->postData['msg'], $this->postData['header']);
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

    private function set_event_play_radio_channel(&$event, $user_list){
        $event->sendPlayRadioChannel($this->postData['channel']);
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

    private function set_event_send_msg_with_video(&$event, $user_list){
        if (!empty($this->postData['video_url'])){
            $event->sendMsgWithVideo($this->postData['msg'], $this->postData['video_url'], $this->postData['header']);
        } else {
            return FALSE;
        }
        return TRUE;
    }

    private function getMessagesTemplatesDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),        'checked' => TRUE),
            array('name' => 'title',        'title' => $this->setLocalization('Title'),     'checked' => TRUE),
            array('name' => 'login',        'title' => $this->setLocalization('Author'),    'checked' => TRUE),
            array('name' => 'created',      'title' => $this->setLocalization('Created'),   'checked' => TRUE),
            array('name' => 'edited',       'title' => $this->setLocalization('Edited'),    'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),'checked' => TRUE)
        );
        return $attribute;
    }

    private function getMsgTemplatesFields(){
        return array(
            'id' => 'M_T.`id` as `id`',
            'login' => 'A.`login` as `login`',
            'admin_id' => 'A.`id` as `admin_id`',
            'title' => 'M_T.title as `title`',
            'header' => 'M_T.header as `header`',
            'body' => 'M_T.body as `body`',
            'created' => 'M_T.created as `created`',
            'edited' => 'M_T.edited as `edited`'
        );
    }
}
