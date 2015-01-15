<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class UsersController extends \Controller\BaseStalkerController {

    private $allStatus = array(array('id' => 1, 'title' => 'Выкл'), array('id' => 2, 'title' => 'Вкл'));
    private $allState = array(array('id' => 1, 'title' => 'Offline'), array('id' => 2, 'title' => 'Online'));
    private $watchdog = 0;
    private $userFields = array(
        'users.id as id', "mac", "ip", "login", "ls", "fname",
        "status", 'tariff_plan.name as tariff_plan_name',
        "DATE_FORMAT(last_change_status,'%d.%m.%Y') as last_change_status",
        "concat (users.fname) as fname"
    );
    private $logObjectsTypes = array(
        'itv' => 'IPTV каналы',
        'video' => 'Видео клуб',
        'unknown' => '',
    );

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->watchdog = \Config::get('watchdog_timeout') * 2;
        $this->userFields[] = "((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`keep_alive`)) <= $this->watchdog) as state";
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_list() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $users = $this->users_list_json();

        $this->app['allUsers'] = $users['data'];
        $this->app['allStatus'] = $this->allStatus;
        $this->app['allState'] = $this->allState;
        $this->app['totalRecords'] = $users['recordsTotal'];
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_groups() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['allGroups'] = $this->db->getConsoleGroup();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->users_consoles_logs_json();
        $this->app['logList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_report() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->users_consoles_report_json();
        $this->app['consoleReport'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_users() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $form = $this->buildUserForm();

        if ($this->saveUsersData($form)) {
            return $this->app->redirect('users-list');
        }
        $this->app['form'] = $form->createView();
        $this->app['userEdit'] = FALSE;

        if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
            $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();    
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function edit_users() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-users');
        }
        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );

        $query_param['select'] = array_merge($query_param['select'], array_diff($this->userFields, $query_param['select']));
        $query_param['where']['users.id'] = $id;
        $query_param['order'] = 'users.id';
        $user = $this->db->getUsersList($query_param);
        $this->user = (is_array($user) && count($user) > 0) ? $user[0] : array();

        $form = $this->buildUserForm($this->user);

        if ($this->saveUsersData($form)) {
            return $this->app->redirect('users-list');
        }
        $this->app['form'] = $form->createView();
        $this->app['userEdit'] = TRUE;
        $this->app['userID'] = $id;

        if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
            $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();    
        }

        return $this->app['twig']->render("Users_add_users.twig");
    }

    public function users_groups_consoles_list() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('users-consoles-groups');
        }
        $tmp = $this->db->getConsoleGroup(array('id' => $id));
        $this->app['consoleGroup'] = $tmp[0];
        $this->app['groupid'] = $id;
        $list = $this->users_groups_consoles_list_json();
        $this->app['consoleGroupList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function users_list_json($param = array()) {
        $response = array();
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', 'state', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getUsersFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $query_param['select'] = array_merge($query_param['select'], array_diff($this->userFields, $query_param['select']));
        $response['recordsTotal'] = $this->db->getTotalRowsUresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsUresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        }
        $response['data'] = $this->db->getUsersList($query_param);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function toggle_user() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid']) || !isset($this->postData['userstatus'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'toggleUserStatus';
        $error = 'Не удалось';

        $event = new \SysEvent();
        $event->setUserListById($this->postData['userid']);
        if ($this->db->toggleUserStatus($this->postData['userid'], (int) (!$this->postData['userstatus']))) {
            $error = '';
            if ($this->postData['userstatus'] == 1) {
                $event->sendCutOn();
            } else {
                $event->sendCutOff();
            }
            $data['title'] = (!$this->postData['userstatus'] ? 'Отключить' : 'Включить');
            $data['status'] = (!$this->postData['userstatus'] ? '<span class="txt-success">Вкл.</span>' : '<span class="txt-danger">Выкл</span>');
            $data['userstatus'] = (int) !$this->postData['userstatus'];
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_user() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeUser';
        $this->db->deleteUserById($this->postData['userid']);
        $this->db->deleteUserFavItv($this->postData['userid']);
        $this->db->deleteUserFavVclub($this->postData['userid']);
        $this->db->deleteUserFavMedia($this->postData['userid']);
        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function reset_users_parent_password() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'resetUsersParentPassword';
        $error = '';
        $data['newpass'] = '0000';
        $this->db->updateUserById(array('parent_password' => '0000'), $this->postData['userid']);

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function reset_user_fav_tv() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'resetUserFavTv';
        $this->db->updateUserFavItv(array('fav_ch' => ''), $id = $this->postData['userid']);
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function add_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addConsoleGroup';
        $error = 'Не удалось';
        $check = $this->db->getConsoleGroup(array('name' => $this->postData['name']));
        if (empty($check)) {
            $data['id'] = $this->db->insertConsoleGroup(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editConsoleGroup';
        $error = 'Не удалось';
        $check = $this->db->getConsoleGroup(array('name' => $this->postData['name']));
        if (empty($check)) {
            $this->db->updateConsoleGroup(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['consolegroupid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeConsoleGroup';
        $data['id'] = $this->postData['consolegroupid'];
        $this->db->deleteConsoleGroup(array('id' => $this->postData['consolegroupid']));
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_login() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkLogin';
        $error = 'Имя занято';
        if ($this->db->checkLogin(trim($this->postData['name']))) {
            $data['chk_rezult'] = 'Имя занято';
        } else {
            $data['chk_rezult'] = 'Имя свободно';
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_console_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkConsoleName';
        $error = 'Имя занято';
        if ($this->db->checkConsoleName(trim($this->postData['name']))) {
            $data['chk_rezult'] = 'Имя занято';
        } else {
            $data['chk_rezult'] = 'Имя свободно';
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function users_groups_consoles_list_json($param = array()) {
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $error = "Error";
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        if ($this->method == 'GET' && !empty($this->data['id'])) {
            $param = (!empty($this->data) ? $this->data : array());

            $query_param = $this->prepareDataTableParams($param, array('operations', 'state', '_'));

            if (!isset($query_param['where'])) {
                $query_param['where'] = array();
            }

            $query_param['where'] = array_merge($query_param['where'], array('stb_group_id' => $this->data['id']));
            $response['recordsTotal'] = $this->db->getTotalRowsConsoleGroupList($query_param['where']);
            $response["recordsFiltered"] = $this->db->getTotalRowsConsoleGroupList($query_param['where'], $query_param['like']);

            if (empty($query_param['limit']['limit'])) {
                $query_param['limit']['limit'] = 10;
            }

            $query_param['select'] = array_merge(array_diff(array('*', 'stb_in_group.id as stb_in_group_id', 'stb_groups.id as stb_groups_id'), $query_param['select']), $query_param['select']);

            $response['data'] = $this->db->getConsoleGroupList($query_param);

            $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
            $error = '';
        }

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function remove_console_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['consoleid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeConsoleItem';
        $data['stb_in_group_id'] = $this->postData['consoleid'];
        $this->db->deleteConsoleItem(array('id' => $this->postData['consoleid']));
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function add_console_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['groupid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addConsoleItem';
        $error = 'Не удалось';
        $mac = \Middleware::normalizeMac($this->postData['name']);
        if (!empty($mac)) {
            $check_in_group = $this->db->getConsoleGroupList(array('where' => array('mac' => $mac), 'order' => 'mac'));
            $check_in_users = $this->db->getUsersList(array('select' => array("*", "users.id as uid"), 'where' => array('mac' => $mac), 'order' => 'mac'));
            if (empty($check_in_group) && !empty($check_in_users)) {
                $param = array(
                    'mac' => $mac,
                    'uid' => $check_in_users[0]['uid'],
                    'stb_group_id' => $this->postData['groupid']
                );
                $result = $this->db->insertConsoleItem($param);
                if (!empty($result)) {
                    $data['stb_in_group_id'] = $result;
                    $data['uid'] = $param['uid'];
                    $data['mac'] = $param['mac'];
                    $error = '';
                }
            } elseif (!empty($check_in_group)) {
                $group_name = $check_in_group[0]['name'];
                $error = "Пользователь уже подключен к группе '$group_name'";
            } elseif (empty($check_in_users)) {
                $error = "Пользователь с таким MAC-адресом не определен";
            }
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_console_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['mac'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkConsoleItem';
        $error = 'Имя занято';
        $mac = \Middleware::normalizeMac($this->postData['mac']);
        $check_in_group = $this->db->getConsoleGroupList(array('where' => array('mac' => $mac), 'order' => 'mac'));
        $check_in_users = $this->db->getUsersList(array('select' => array("*", "users.id as uid"), 'where' => array('mac' => $mac), 'order' => 'mac'));
        if (!empty($check_in_group)) {
            $group_name = $check_in_group[0]['name'];
            $data['chk_rezult'] = "Пользователь уже подключен к группе '$group_name'";
            $error = "Пользователь уже подключен к группе '$group_name'";
        } elseif (empty($check_in_users)) {
            $data['chk_rezult'] = $error = "Пользователь с таким MAC-адресом не определен";
        } else {
            $data['chk_rezult'] = 'Пользователя можно подключить к группе';
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function users_consoles_logs_json() {
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $filds_for_select = array(
            'time' => "CAST(user_log.`time` AS CHAR) as `time`",
            'mac' => "user_log.`mac` as `mac`",
            'uid' => "users.`id` as `uid`",
            'action' => "user_log.`action` as `action`",
            'param' => "user_log.`param` as `param`"
        );

        $error = "Error";
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $deleted_params = $this->checkDisallowFields($query_param, array('object', 'type'));

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'uid';
        }
        $this->cleanQueryParams($query_param, array('time', 'mac', 'action', 'param', 'uid'), $filds_for_select);

        $response['recordsTotal'] = $this->db->getTotalRowsLogList();
        $response["recordsFiltered"] = $this->db->getTotalRowsLogList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        }

        $response['data'] = $this->db->getLogList($query_param);

        $this->setLogObjects($response['data']);
        if (!empty($deleted_params['order'])) {
            $this->orderByDeletedParams($response['data'], $deleted_params['order']);
        }
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = '';

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function users_consoles_report_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $response = array();

        $filds_for_select = array(
            'id' => 'users.id as `id`',
            'rank' => '@rank:= if(isnull(@rank), 0, @rank+1) as `rank`',
            'mac' => "users.mac as `mac`",
            'status' => "users.status as `status`",
            'last_change_status' => "CAST(users.`last_change_status` AS CHAR) as `last_change_status`"
        );

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('state', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'id';
        }
        $this->cleanQueryParams($query_param, array('id', 'rank', 'mac', 'status', 'last_change_status'), $filds_for_select);
        $query_param['where']['UNIX_TIMESTAMP(last_change_status)>='] = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
        $response['recordsTotal'] = $this->db->getTotalRowsUresList(array('UNIX_TIMESTAMP(last_change_status)>=' => mktime(0, 0, 0, date("n"), date("j"), date("Y"))));
        $response["recordsFiltered"] = $this->db->getTotalRowsUresList($query_param['where'], $query_param['like']);
        
        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        $response['data'] = $this->db->getUsersList($query_param, TRUE);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    //------------------------ service method ----------------------------------

    private function getUsersFilters() {
        $return = array();

        if (empty($this->data['filters'])){
            $this->app['filters'] =  array('interval_from'=>'', 'interval_to'=>'');
            return $return;
        }
        $now_timestamp = time() - $this->watchdog;
        $now_time = date("Y-m-d H:i:s", $now_timestamp);
        if (array_key_exists('status_id', $this->data['filters']) && !empty((int) $this->data['filters']['status_id'])) {
            $return['status'] = $this->data['filters']['status_id'] - 1;
        }
        if (array_key_exists('state_id', $this->data['filters']) && !empty((int) $this->data['filters']['state_id'])) {
            $return['keep_alive' . ($this->data['filters']['state_id'] - 1 ? "<" : ">")] = "'$now_time'";
        }
        if (array_key_exists('interval_from', $this->data['filters']) && !empty((int) $this->data['filters']['interval_from'])) {
            $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_from']);
            $return['UNIX_TIMESTAMP(last_active)>='] = $date->getTimestamp();
        }
        if (array_key_exists('interval_to', $this->data['filters']) && !empty((int) $this->data['filters']['interval_to'])) {
            $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_to']);
            $return['UNIX_TIMESTAMP(last_active)<='] = $date->getTimestamp();
        }

        $this->data['filters']['interval_from'] = (empty($this->data['filters']['interval_from']) ? '' : $this->data['filters']['interval_from']);
        $this->data['filters']['interval_to'] = (empty($this->data['filters']['interval_to']) ? '' : $this->data['filters']['interval_to']);

        $this->app['filters'] = $this->data['filters'];
        return $return;
    }

    private function buildUserForm(&$data = array(), $edit = FALSE) {

        $builder = $this->app['form.factory'];
        $status = array(
            0 => 'Выключена',
            1 => 'Включена'
        );

        $additional_services = array(
            0 => 'Выключены',
            1 => 'Включены'
        );

        $stb_groups = new \StbGroup();

        $all_groups = $stb_groups->getAll();
        $group_keys = $this->getFieldFromArray($all_groups, 'id');
        $group_names = $this->getFieldFromArray($all_groups, 'name');
        $all_groups = array_combine($group_keys, $group_names);

        if (!empty($data['id'])) {
            $tmp = $stb_groups->getMemberByUid($data['id']);
            if (!empty($tmp)) {
                $data['group_id'] = $tmp['stb_group_id'];
            }
            $tmp = $this->db->getUserFavItv($data['id']);

            if (!empty($tmp)) {
                $tmp = unserialize(base64_decode($tmp));
                $data['fav_itv'] = (is_array($tmp)) ? count($tmp) : 0;
                $data['fav_itv_on'] = ($data['fav_itv']) ? 1 : 0;
            } else {
                $data['fav_itv'] = 0;
                $data['fav_itv_on'] = 0;
            }
            $data['version'] = str_replace("; ", ";", $data['version']);
            $data['version'] = str_replace(";", ";\r\n", $data['version']);
        }

        $tarif_plans = $this->db->getAllTariffPlans();
        $plan_keys = $this->getFieldFromArray($tarif_plans, 'id');
        $plan_names = $this->getFieldFromArray($tarif_plans, 'name');
        $tariff_plans = array_combine($plan_keys, $plan_names);

        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('fname', 'text', array('required' => FALSE))
                ->add('login', 'text', $this->getAddUserFormParam($edit))
                ->add('password', 'password', array('required' => FALSE))
                ->add('phone', 'text', array('required' => FALSE))
                ->add('ls', 'text', array('required' => FALSE))
                ->add('group_id', 'choice', array(
                    'choices' => $all_groups,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($all_groups)))),
                    'required' => FALSE
                        )
                )
                ->add('tariff_plan_id', 'choice', array(
                    'choices' => $tariff_plans,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($tariff_plans)))),
                    'required' => FALSE
                        )
                )
                ->add('additional_services_on', 'choice', array(
                    'choices' => $additional_services,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($additional_services)))),
                    'required' => FALSE
                        )
                )
                ->add('status', 'choice', array(
                    'choices' => $status,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($status)))),
                    'required' => FALSE
                        )
                )
                ->add('comment', 'textarea', array('required' => FALSE))
                ->add('save', 'submit')
                ->add('reset', 'reset');
        if (!empty($data['id'])) {
            $form->add('mac', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('ip', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('parent_password', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('fav_itv', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('version', 'textarea', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE));
        }
        return $form->getForm();
    }

    private function getAddUserFormParam($edit) {
        if (!$edit) {
            return array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    'required' => TRUE
                ),
                'required' => TRUE
            );
        }
        return array('required' => FALSE);
    }

    private function saveUsersData(&$form, $edit = FALSE) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            $action = (isset($this->user) ? 'updateUserById' : 'insertUsers');
            if (array_key_exists('password', $data) && $edit && empty($data['password'])) {
                unset($data['password']);
            }
            if ($form->isValid()) {

                $stb_groups = new \StbGroup();
                $member = $stb_groups->getMemberByUid(intval($data['id']));

                if (empty($member)) {
                    $stb_groups->addMember(array('mac' => \Middleware::normalizeMac($data['mac']), 'uid' => \Middleware::getUidByMac($data['mac']), 'stb_group_id' => $data['group_id']));
                } else {
                    $stb_groups->setMember(array('stb_group_id' => $data['group_id']), $member['id']);
                }

                $curr_fields = $this->db->getTableFields('users');
                $curr_fields = $this->getFieldFromArray($curr_fields, 'Field');
                $curr_fields = array_flip($curr_fields);

                $data = array_intersect_key($data, $curr_fields);

                if ($action == 'insertUsers') {
                    if ($this->db->$action($data)) {
                        return TRUE;
                    }
                } else {
                    if ($this->db->$action($data, $data['id'])) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    private function getSubChannels($id = 0) {

        if ($id == 0) {
            return array();
        }

        $sub_ch = $this->db->getSubChannelsDB($id);
        $sub_ch = unserialize(base64_decode($sub_ch));

        if (!is_array($sub_ch)) {
            return array();
        } else {
            return $sub_ch;
        }
    }

    private function getCostSubChannels($id = 0) {
        if ($id == 0) {
            return 0;
        }
        $sub_ch = $this->getSubChannels($id);

        return number_format($this->db->returngetCostSubChannelsDB($sub_ch), 2, '.');
    }

    private function setLogObjects(&$data) {
        while (list($key, $row) = each($data)) {
            if ($row['action'] == 'play') {
                $sub_param = substr($row['param'], 0, 3);
                if ($sub_param == 'rtp') {
                    $data[$key]['type'] = $this->logObjectsTypes['itv'];
                    $chanel = $this->db->getITV(array('cmd' => $row['param']));
                    $data[$key]['object'] = $chanel['name'];
                } elseif ($sub_param == 'aut') {
                    $data[$key]['type'] = $this->logObjectsTypes['video'];
                    preg_match("/auto \/media\/(\d+)\.[a-z]*$/", $row['param'], $tmp_arr);
                    $media = $this->db->getVideo(array('id' => $tmp_arr[1]));
                    $data[$key]['object'] = $media['name'];
                } else {
                    $data[$key]['type'] = $this->logObjectsTypes['unknown'];
                    $data[$key]['object'] = '';
                }
            } else {
                $data[$key]['type'] = $this->logObjectsTypes['unknown'];
                $data[$key]['object'] = (!empty($row['param']) ? $row['param'] : "");
            }
        }
    }

}
