<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use \Cron as Cron;

class EventsController extends \Controller\BaseStalkerController {

    protected $formEvent = array();
    protected $hiddenEvent = array();
    protected $sendedStatus = array();
    protected $receivingStatus = array();
    protected $scheduleType = array();
    protected $scheduleState = array();


    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->formEvent = array(
            array("id" => "send_msg",           "title" => $this->setLocalization('Sending a message') ),
            array("id" => "reboot",             "title" => $this->setLocalization('Reboot') ),
            array("id" => "reload_portal",      "title" => $this->setLocalization('Restart the portal') ),
            array("id" => "update_channels",    "title" => $this->setLocalization('Update channel list') ),
            array("id" => "play_channel",       "title" => $this->setLocalization('Playback channel') ),
            array("id" => "play_radio_channel", "title" => $this->setLocalization('Playback radio channel') ),
            array("id" => "mount_all_storages", "title" => $this->setLocalization('Mount all storages') ),
            array("id" => "cut_off",            "title" => $this->setLocalization('Turn off') ),
            array("id" => "update_image",       "title" => $this->setLocalization('Image update') )
        );
        $this->hiddenEvent = array(
            /*array("id" => "send_msg_with_video",        "title" => $this->setLocalization('Sending a message with video') ),*/
            array("id" => "update_epg",                 "title" => $this->setLocalization('EPG update') ),
            array("id" => "update_subscription",        "title" => $this->setLocalization('Subscribe update') ),
            array("id" => "update_modules",             "title" => $this->setLocalization('Modules update') ),
            array("id" => "cut_on",                     "title" => $this->setLocalization('Turn on') ),
            array("id" => "show_menu",                  "title" => $this->setLocalization('Show menu') ),
            array("id" => "additional_services_status", "title" => $this->setLocalization('Status additional service') )
        );

        $this->sendedStatus = array(
            array("id" => 1 , "title" => $this->setLocalization('Not delivered')),
            array("id" => 2 , "title" => $this->setLocalization('Delivered'))
        );

        $this->receivingStatus = array(
            array("id" => 1 , "title" => $this->setLocalization('Not received')),
            array("id" => 2 , "title" => $this->setLocalization('Received'))
        );

        $this->scheduleType = array(
            array("id" => 1 , "title" => $this->setLocalization('One-time event')),
            array("id" => 2 , "title" => $this->setLocalization('For a period'))
        );

        $this->scheduleState = array(
            array("id" => 2 , "title" => $this->setLocalization('Scheduled')),
            array("id" => 1 , "title" => $this->setLocalization('Stopped'))
        );

        $this->repeatingInterval = array(
            array("id" => 1 , "title" => $this->setLocalization('Year')),
            array("id" => 2 , "title" => $this->setLocalization('Month')),
            array("id" => 3 , "title" => $this->setLocalization('Week')),
            array("id" => 4 , "title" => $this->setLocalization('Day'))
        );

        $this->monthNames = array(
            array("id" => 1 , "title" => $this->setLocalization('January')),
            array("id" => 2 , "title" => $this->setLocalization('February')),
            array("id" => 3 , "title" => $this->setLocalization('March')),
            array("id" => 4 , "title" => $this->setLocalization('April')),
            array("id" => 5 , "title" => $this->setLocalization('May')),
            array("id" => 6 , "title" => $this->setLocalization('June')),
            array("id" => 7 , "title" => $this->setLocalization('July')),
            array("id" => 8 , "title" => $this->setLocalization('August')),
            array("id" => 9 , "title" => $this->setLocalization('September')),
            array("id" => 10 , "title" => $this->setLocalization('October')),
            array("id" => 11 , "title" => $this->setLocalization('November')),
            array("id" => 12 , "title" => $this->setLocalization('December'))
        );

        $this->dayNames = array(
            array("id" => 1 , "title" => $this->setLocalization('Mon')),
            array("id" => 2 , "title" => $this->setLocalization('Tue')),
            array("id" => 3 , "title" => $this->setLocalization('Wed')),
            array("id" => 4 , "title" => $this->setLocalization('Thu')),
            array("id" => 5 , "title" => $this->setLocalization('Fri')),
            array("id" => 6 , "title" => $this->setLocalization('Sat')),
            array("id" => 7 , "title" => $this->setLocalization('Sun'))
        );
        $this->app['defTTL'] = array(
            'send_msg' => 7*24*3600,
            'send_msg_with_video' => 7*24*3600,
            'other' => \Config::get('watchdog_timeout') * 2
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

        $this->app['allFilters'] = $this->getAllFilters();

        $this->app['messagesTemplates'] = $this->db->getAllFromTable('messages_templates', 'title');

        $attribute = $this->getEventsListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        if (!empty($this->app['currentUser'])) {
            $this->app['breadcrumbs']->addItem($this->setLocalization('Users events') . " {$this->app['currentUser']['name']} ({$this->app['currentUser']['mac']})");
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

    public function event_scheduler(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['scheduleType'] = $this->scheduleType;
        $this->app['scheduleState'] = $this->scheduleState;
        $this->app['consoleGroup'] = $this->db->getConsoleGroup();
        $this->app['formEvent'] = $this->formEvent;
        $this->app['allFilters'] = $this->getAllFilters();
        $this->app['repeatingInterval'] = $this->repeatingInterval;
        $this->app['monthNames'] = $this->monthNames;
        $this->app['dayNames'] = $this->dayNames;
        $this->app['messagesTemplates'] = $this->db->getAllFromTable('messages_templates', 'title');


        $attribute = $this->getSchedulerDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->event_scheduler_list_json();
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
            'name' => "users.`fname` as `name`",
            'post_function' => "events.`post_function` as `post_function`",
            'param1' => "events.`param1` as `param1`"
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

        $self = $this;

        $response['data'] = array_map(function($row) use ($events, $self){
            $row['event'] = $events[$row['event']];
            $row['mac'] = (!empty($row['mac']) ? $row['mac']: 'no_mac_address');
            $row['addtime'] = (int)  strtotime($row['addtime']);
            if ($row['addtime'] < 0) {
                $row['addtime'] = 0;
            }
            $row['eventtime'] = (int)  strtotime($row['eventtime']);
            if ($row['eventtime'] < 0) {
                $row['eventtime'] = 0;
            }
            if (!empty($row['post_function'])) {
                $row['post_function'] = $self->setLocalization(str_replace('_', ' ', ucfirst($row['post_function'])));
            }
            if (!empty($row['param1']) && strpos($row['param1'], '://') === FALSE) {
                $row['param1'] = $self->setLocalization($row['param1']);
            }
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
        $data['msg'] = $this->setLocalization('Added') . ' ' . $this->setLocalization('for'). ' ';
        $error = $this->setLocalization('Error. Event has not been added.');

        $_SERVER['TARGET'] = 'ADM';
        $event = new \AdminPanelEvents($this->postData);
        $event->setTtl($this->postData['ttl']);
        if (!empty($this->postData['add_post_function']) && !empty($this->postData['post_function']) && !empty($this->postData['param1'])) {
            $event->setPostFunctionParam($this->postData['post_function'], $this->postData['param1']);
        }
        $get_list_func_name = 'get_userlist_' . str_replace('to_', '', $this->postData['user_list_type']);
        $set_event_func_name = 'set_event_' . str_replace('to_', '', $this->postData['event']);

        if ($event->$get_list_func_name()->cleanAndSetUsers()->$set_event_func_name()){
            $data['msg'] .= count($event->getUserList()). ' ' . $this->setLocalization('users');
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
        $data['msg'] = $this->setLocalization('Added');
        $data['fname'] = '';
        $error = $this->setLocalization('The file does not contain valid MAC-addresses.');

        list($key, $tmp) = each($_FILES);
        $file_data = file_get_contents($tmp['tmp_name']);
        $list = array();

        preg_match_all('/([0-9a-fA-F]{2}:){5}([0-9a-fA-F]{2})/', $file_data, $list);
        if (!empty($list) && !empty($list[0])) {
            $file_name = tempnam(sys_get_temp_dir(), 'MAC');
            $data['fname'] = basename($file_name);
            $file_data = implode(';', $list[0]);
            file_put_contents($file_name, $file_data);
            $data['msg'] .= count($list[0]) . ' ' . $this->setLocalization('addresses');
            $error = '';
        } else {
            $data['msg'] = $error;
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
        $data['msg'] = $this->setLocalization('Deleted') . ' ' . (is_numeric($result)? $result: $this->setLocalization($result)) . ' ' . $this->setLocalization('events');
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
        $error = $this->setLocalization('Not enough data');

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
            $data['msg'] = $this->setLocalization('Nothing to do');
            $data['nothing_to_do'] = TRUE;
            $error = '';
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
        $error = $this->setLocalization('Failed');

        if ($error = $this->db->deleteMsgTemplate($this->postData['id'])) {
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function event_scheduler_list_json(){

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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_', 'next_run', 'event_trans'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getEventsFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $filds_for_select = $this->getScheduleEventsFields();
        if (!empty($query_param['select'])) {
            $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        } else {
            $query_param['select'] = $filds_for_select;
        }

        if (!array_key_exists('event', $query_param['select'])) {
            $query_param['select']['event'] = $filds_for_select['event'];
        }

        if (!array_key_exists('last_run', $query_param['select'])) {
            $query_param['select']['last_run'] = $filds_for_select['last_run'];
        }

        if (array_key_exists('id', $this->postData)) {
            $query_param['where'] = array('S_E.id'=>$this->postData['id']);
            $response['action'] = 'fillModalForm';
        }

        $response['recordsTotal'] = $this->db->getTotalRowsScheduleEvents();
        $response["recordsFiltered"] = $this->db->getTotalRowsScheduleEvents($query_param['where'], $query_param['like']);

        $cronTab = new \CronExpression('* * * * *', new Cron\FieldFactory() );
        foreach($cronTab->getMessageParts() as $key=>$val){
            $cronTab->setMessageParts($key, $this->setLocalization($val));
        }

        $deferred = $this->setLocalization('deferred');
        $unlimited = $this->setLocalization('unlimited');
        $not_run = $this->setLocalization('do not yet running');
        $all_event = array_merge($this->formEvent, $this->hiddenEvent);
        $all_event = array_combine($this->getFieldFromArray($all_event, 'id'), $this->getFieldFromArray($all_event, 'title'));
        $all_recipients = array(
            'to_all' => $this->setLocalization('All'),
            'by_group' => $this->setLocalization('Group'),
            'to_single' => $this->setLocalization('One'),
            'by_filter' => $this->setLocalization('Filter')
        );

        $response['data'] = array_map(function($row) use ($cronTab, $deferred, $all_event, $all_recipients, $unlimited, $not_run){
            $cronTab->setCurrentTime($row['last_run']);
            $row['event_trans'] = $all_event[$row['event']];
            $row['post_function'] = array_key_exists($row['post_function'], $all_event) ? $all_event[$row['post_function']]: $row['post_function'];
            $row['date_begin'] = (int)strtotime($row['date_begin']);
            if ($row['date_begin'] < 0) {
                $row['date_begin'] = 0;
            }
            $row['date_end'] = (int)strtotime($row['date_end']);
            if ($row['date_end'] <= 0) {
                $row['date_end'] = $unlimited;
            }

            $row['last_run'] = (int)strtotime($row['last_run']);
            if ($row['last_run'] <= 0) {
                $row['last_run'] = $not_run;
            }

            $row['cron_str'] = $row['schedule'];
            $cronTab->setExpression($row['schedule'])->setMessage();
            if (!empty($row['schedule']) && (int) $row['periodic']) {
                $row['next_run'] = $cronTab->getNextRunDate()->getTimestamp();
                $row['schedule'] = implode(' ', $cronTab->getMessage());
            } else {
                $row['date_end'] = $row['next_run'] = $row['state'] ? $cronTab->getNextRunDate()->getTimestamp() : $deferred;
                $row['schedule'] = $cronTab->getMessageParts('once');
            }

            $recipient = json_decode($row['recipient'], TRUE);
            list($row['recipient'], $recipient) = each($recipient);
            $row['user_list_type'] = $row['recipient'];
            $row['recipient'] = $all_recipients[$row['recipient']];
            if (!empty($recipient) && is_array($recipient)) {
                $row = array_merge($row, (array)$recipient);
            }
            return $row;
        }, $this->db->getScheduleEvents($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = '';

        if ($this->isAjax) {
            if (!empty($response['data']) && !empty($this->postData['id'])) {
                $response['data'][0] = array_merge($response['data'][0], array_map(function($row){
                    return is_numeric($row) ? str_pad((string) $row, 2, '0', STR_PAD_LEFT) : $row;
                }, \CronForm::getInstance()->setExpression($response['data'][0]['cron_str'])->getFormData()));

                if (array_key_exists('interval', $response['data'][0])) {
                    $response['data'][0]['interval'] = str_replace('0', 'repeating_interval_', $response['data'][0]['interval']);
                }
                $response['data'][0]['type'] = "schedule_type_" . ( (int) $response['data'][0]['periodic'] + 1);
            }
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function save_schedule_event(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addEvent';
        $error = $this->setLocalization('Not enough data');

        $from_db = array_flip($this->getFieldFromArray($this->db->getTableFields('schedule_events'), 'Field'));
        $form_post = $this->postData;

        $recipient_func = "getRecipientBy" . ucfirst(strtolower(str_replace(array('by_', 'to_'), '', $form_post['user_list_type'])));
        $form_post['recipient'] = $this->$recipient_func($form_post);
        $form_post['periodic'] = (int)str_replace('schedule_type_', '', $form_post['type']) - 1;
        $form_post['state'] = 1;
        $form_post['reboot_after_ok'] = (int)(!empty($form_post['need_reboot']) && (string)$form_post['need_reboot'] != 'false' && (string)$form_post['need_reboot'] != 'off' && (string)$form_post['need_reboot'] != 'false' && (string)$form_post['need_reboot'] != '0');

        if (array_key_exists('month', $form_post)) {
            $form_post['month'] = (int)$form_post['month'];
        }
        if (array_key_exists('every_month', $form_post)) {
            $form_post['every_month'] = (int)$form_post['every_month'];
        }
        if (array_key_exists('every_day', $form_post)) {
            $form_post['every_day'] = (int)$form_post['every_day'];
        }
        if (array_key_exists('every_hour', $form_post)) {
            $form_post['every_hour'] = (int)$form_post['every_hour'];
        }
        if (array_key_exists('every_minute', $form_post)) {
            $form_post['every_minute'] = (int)$form_post['every_minute'];
        }
        if (array_key_exists('date_begin', $form_post)) {
            $date = \DateTime::createFromFormat('d/m/Y', $form_post['date_begin']);
            $form_post['date_begin'] = $date ? $date->format('Y-m-d G:i:s'): 'NOW()';
        }
        if (array_key_exists('date_end', $form_post)) {
            $date = \DateTime::createFromFormat('d/m/Y', $form_post['date_end']);
            $form_post['date_end'] = $date ? $date->format('Y-m-d G:i:s') : '';
        }

        $form_post['schedule'] = \CronForm::getInstance()->setFormData($form_post)->getExpression();

        $params = array();
        $from_db = array_combine(array_keys($from_db), array_fill(0, count($from_db), NULL));
        if (!empty($form_post['id'])) {
            $id = $form_post['id'];
            $operation = 'update';
            $params[] = array_replace($from_db, array_intersect_key($form_post, $from_db));
            $params[] = $id;
        } else {
            $operation = 'insert';
            $params[] = array_replace($from_db, array_intersect_key($form_post, $from_db));
        }
        unset($params[0]['id']);

        $result = call_user_func_array(array($this->db, $operation."ScheduleEvents"), $params);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
                $data['msg'] = $this->setLocalization('Nothing to do');
            }
        }


        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function scheduler_toggle_state(){
        if (!$this->isAjax || $this->method != 'POST' || !isset($this->postData['id']) || !isset($this->postData['state'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addEvent';
        $error = $this->setLocalization('Nothing to do');

        if ($this->db->updateScheduleEvents(array('state' => !((int)$this->postData['state'])), $this->postData['id'])){
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));

    }

    public function scheduler_remove(){
        if (!$this->isAjax || $this->method != 'POST' || !isset($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addEvent';
        $error = $this->setLocalization('Not enough data');

        if ($this->db->deleteScheduleEvents($this->postData['id'])){
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));

    }

    //------------------------ service method ----------------------------------

    private function getEventsFilters() {
        $return = array();

        if (!array_key_exists('filters', $this->data)) {
            $this->data['filters'] = array();
        }

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

        if (!empty($this->data['filters']) && !empty($this->data['filters']['date_from'])) {
            $date = \DateTime::createFromFormat("d/m/Y", $this->data['filters']['date_from']);
            $date->modify('midnight');
            $this->data['filters']['interval_from'] = $return['UNIX_TIMESTAMP(`date_begin`) >='] = $date->getTimestamp();// $date->format('Y-m-d H:i:s');
        }

        if (!empty($this->data['filters']) && !empty($this->data['filters']['date_to'])) {
            $date = \DateTime::createFromFormat("d/m/Y", $this->data['filters']['date_to']);
            $date->modify('1 second ago tomorrow');
            $this->data['filters']['interval_to'] = $return['UNIX_TIMESTAMP(`date_end`) <='] = $date->getTimestamp();// $date->format('Y-m-d H:i:s');
        }

        if (!empty($this->data['filters']) && !empty($this->data['filters']['type']) && (int)$this->data['filters']['type']) {
            $return['periodic'] = (int)$this->data['filters']['type'] - 1;
        }

        if (!empty($this->data['filters']) && !empty($this->data['filters']['state']) && (int)$this->data['filters']['state']) {
            $return['state'] = (int)$this->data['filters']['state'] - 1;
        }

        $this->app['filters'] = !empty($this->data['filters']) ? $this->data['filters'] : array();
        return $return;
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

    private function getEventsListDropdownAttribute(){

        $attribute = array(
            array('name'=>'events_id',      'title'=>$this->setLocalization('ID'),                      'checked' => TRUE),
            array('name'=>'addtime',        'title'=>$this->setLocalization('Added'),                   'checked' => TRUE),
            array('name'=>'eventtime',      'title'=>$this->setLocalization('Expiration date'),         'checked' => TRUE),
            array('name'=>'mac',            'title'=>$this->setLocalization('MAC'),                     'checked' => TRUE),
            array('name'=>'event',          'title'=>$this->setLocalization('Event'),                   'checked' => TRUE),
            array('name'=>'msg',            'title'=>$this->setLocalization('Message'),                 'checked' => TRUE),
            array('name'=>'post_function',  'title'=>$this->setLocalization('Post function'),           'checked' => TRUE),
            array('name'=>'param1',         'title'=>$this->setLocalization('Post function parameter'), 'checked' => FALSE),
            array('name'=>'sended',         'title'=>$this->setLocalization('Delivery status'),         'checked' => TRUE),
            array('name'=>'ended',          'title'=>$this->setLocalization('Receipt status'),          'checked' => TRUE)
        );

        return $attribute;
    }

    private function getSchedulerDropdownAttribute(){

        $attribute = array(
            array('name'=>'id',             'title'=>$this->setLocalization('ID'),          'checked' => TRUE),
            array('name'=>'event_trans',    'title'=>$this->setLocalization('Event'),       'checked' => TRUE),
            array('name'=>'post_function',  'title'=>$this->setLocalization('Post-function'),'checked' => TRUE),
            array('name'=>'recipient',      'title'=>$this->setLocalization('Recipient'),   'checked' => TRUE),
            array('name'=>'periodic',       'title'=>$this->setLocalization('Type'),        'checked' => TRUE),
            array('name'=>'date_begin',     'title'=>$this->setLocalization('Begin'),       'checked' => TRUE),
            array('name'=>'date_end',       'title'=>$this->setLocalization('End'),         'checked' => TRUE),
            array('name'=>'schedule',       'title'=>$this->setLocalization('Schedule'),    'checked' => TRUE),
            array('name'=>'next_run',       'title'=>$this->setLocalization('Next run'),    'checked' => TRUE),
            array('name'=>'last_run',       'title'=>$this->setLocalization('Last run'),    'checked' => TRUE),
            array('name'=>'state',          'title'=>$this->setLocalization('State'),       'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operations'),  'checked' => TRUE)
        );

        return $attribute;
    }

    private function getScheduleEventsFields(){
        return array(
            'id' => 'S_E.`id` as `id`',
            'event' => 'S_E.event as `event`',
            'header' => 'S_E.header as `header`',
            'msg' => 'S_E.msg as `msg`',
            'post_function' => 'S_E.post_function as `post_function`',
            'recipient' => 'S_E.recipient as `recipient`',
            'periodic' => 'S_E.periodic as `periodic`',
            'date_begin' => 'TIMESTAMP(S_E.date_begin) as `date_begin`',
            'date_end' => 'TIMESTAMP(S_E.date_end) as `date_end`',
            'schedule' => 'S_E.schedule as `schedule`',
            'state' => 'S_E.state as `state`',
            'reboot_after_ok' => 'S_E.reboot_after_ok as `reboot_after_ok`',
            'param1' => 'S_E.param1 as `param1`',
            'ttl' => 'S_E.ttl as `ttl`',
            'last_run' => 'S_E.last_run as `last_run`'
        );
    }

    private function getAllFilters(){

        $filter_set = \Filters::getInstance();
        $filter_set->setResellerID($this->app['reseller']);
        $filter_set->initData('users', 'id');

        $self = $this;

        return array_map(function($row) use ($filter_set, $self){
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
    }

    private function getRecipientByAll($data){
        return json_encode(array($data['user_list_type'] => ''));
    }

    private function getRecipientByGroup($data){
        return json_encode(array($data['user_list_type'] => array('group_id' => $data['group_id'])));
    }

    private function getRecipientBySingle($data){
        return json_encode(array($data['user_list_type'] => array('mac' => $data['mac'])));
    }

    private function getRecipientByFilter($data){
        return json_encode(array($data['user_list_type'] => array('filter_set' => $data['filter_set'])));
    }

}