<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class UsersController extends \Controller\BaseStalkerController {

    protected $allStatus = array();
    private $allState = array(array('id' => 2, 'title' => 'Offline'), array('id' => 1, 'title' => 'Online'));
    private $watchdog = 0;
    private $userFields = array(
        'users.id as id', "mac", "ip", "login", "ls", "fname", "reseller.id as reseller_id",
        "status", 'tariff_plan.name as tariff_plan_name',
        "DATE_FORMAT(last_change_status,'%d.%m.%Y') as last_change_status",
        "concat (users.fname) as fname",
        "UNIX_TIMESTAMP(`keep_alive`) as last_active",
        "DATE_FORMAT(`expire_billing_date`,'%d.%m.%Y') as `expire_billing_date`",
        "account_balance"
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
        $this->allStatus = array(
            array('id' => 1, 'title' => $this->setlocalization('on')),
            array('id' => 2, 'title' => $this->setlocalization('off'))
        );
        if (empty($this->app['reseller'])) {
            $this->userFields[] = "reseller.name as reseller_name";
        }
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/users-list');
        }
        
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
        $this->app['recordsFiltered'] = $users['recordsFiltered'];
        $this->app['consoleGroup'] = $this->db->getConsoleGroup();
        
        $attribute = $this->getUsersListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $this->app['enableBilling'] = TRUE;
        }

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_groups() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $empty_reseller = $this->setLocalization('Empty');
        $data = $this->db->getConsoleGroup();
        if (empty($this->app['reseller'])) {
            $this->app['allGroups'] = array_map(function ($row) use ($empty_reseller) {
                if (empty($row['reseller_name'])) {
                    $row['reseller_name'] = $empty_reseller;
                }
                if (empty($row['reseller_id'])) {
                    $row['reseller_id'] = '-';
                }
                $row['operations'] = '';
                return $row;
            }, $data);
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        } else {
            $this->app['allGroups'] = $data;
        }

        $this->app['dropdownAttribute'] = $this->getUsersConsolesGroupsDropdownAttribute();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->users_consoles_logs_json();
        
        if (!empty($this->data['id'])) {
            $this->app['user'] = $this->db->getUsersList(array('select'=>array('`users`.`name`', '`users`.`fname`', '`users`.`mac`'), array('where' => array('`users`.id' => $this->data['id']))));
        }
        
        $this->app['logList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $user_name = '';
        if (!empty($this->app['user'])) {
            $user_name = " {$this->app['user'][0]['name']} {$this->app['user'][0]['fname']} ({$this->app['user'][0]['mac']})";
            $this->app['breadcrumbs']->addItem($this->setLocalization("Log of user") . $user_name);
        }

        $this->app['currentUser'] = $user_name;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_report() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->users_consoles_report_json();
        $this->app['consoleReport'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['now_time'] = strftime('%d.%m.%Y')  . " " . strftime('%T');
        $this->app['breadcrumbs']->addItem($this->setLocalization("STB statuses report") . " " . $this->app['now_time']);
    
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_users() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $this->app['tarifPlanFlag'] = \Config::getSafe('enable_tariff_plans', false);
        $form = $this->buildUserForm();

        if ($this->saveUsersData($form)) {
            return $this->app->redirect('users-list');
        }
        $this->app['form'] = $form->createView();
        $this->app['userEdit'] = FALSE;

        if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
            $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();    
        }
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $this->app['enableBilling'] = TRUE;
        }
        $this->app['breadcrumbs']->addItem($this->setLocalization('Users list'), $this->app['controller_alias'] . '/users-list');
        $this->app['breadcrumbs']->addItem($this->setlocalization('Add user'));
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
        if (empty($this->user)) {
            return $this->app->redirect('add-users');
        }
        $this->app['tarifPlanFlag'] = \Config::getSafe('enable_tariff_plans', false);
        if (!empty($this->user['expire_billing_date']) && preg_match("/(19|20)\d\d([- \/\.])(0[1-9]|1[012])[- \/\.](0[1-9]|[12][0-9]|3[01])/im", $this->user['expire_billing_date'], $match)) {
            $this->user['expire_billing_date'] = implode('-', array_reverse(explode($match[2], $this->user['expire_billing_date'])));
        } elseif (((int) str_replace(array('-', '.'), '', $this->user['expire_billing_date'])) == 0) {
            $this->user['expire_billing_date'] = '';
        } else {
            $this->user['expire_billing_date'] = str_replace('.', '-', $this->user['expire_billing_date']);
        }

        $this->user['version'] = preg_replace("/(\r\n|\n\r|\r|\n|\s){2,}/i", "$1", stripcslashes($this->user['version']));
        $form = $this->buildUserForm($this->user, TRUE);

        if ($this->saveUsersData($form, TRUE)) {
            return $this->app->redirect('edit-users?id='.$id);
        }
        $this->app['form'] = $form->createView();
        $this->app['userEdit'] = TRUE;
        $this->app['userID'] = $id;
        
        $users_tarif_plans = array_map(function($val){
            $val['optional'] = (int)$val['optional'];
            $val['subscribed'] = (int)$val['subscribed'];
            return $val;
        }, $this->db->getTarifPlanByUserID($id));
        
        $this->app['userTPs'] = $users_tarif_plans;

        $this->app['state'] = (int) $this->user['state'];

        if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
            $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();    
        }
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $this->app['enableBilling'] = TRUE;
        }

        $this->app['userName'] = $this->user['mac'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Users list'), $this->app['controller_alias'] . '/users-list');
        $this->app['breadcrumbs']->addItem($this->setlocalization('Edit user'));
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
        $tmp = $this->db->getConsoleGroup(array('Sg.id' => $id));
        $this->app['consoleGroup'] = $tmp[0];
        $this->app['groupid'] = $id;
        $list = $this->users_groups_consoles_list_json();
        $this->app['consoleGroupList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        $this->app['breadcrumbs']->addItem($this->setLocalization('User groups'), $this->app['controller_alias'] . '/users-consoles-groups');
        $this->app['breadcrumbs']->addItem($this->setLocalization("STB of group") . " '{$this->app['consoleGroup']['name']}'");
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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_', 'reseller_name'));
        if (($search = array_search('state', $query_param['select'])) != FALSE) {
            unset($query_param['select'][$search]);
            unset($query_param['where']['state']);
            unset($query_param['like']['state']);
        }

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getUsersFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $query_param['select'] = array_merge($query_param['select'], array_diff($this->userFields, $query_param['select']));
        $response['recordsTotal'] = $this->db->getTotalRowsUresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsUresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        }
        
        if (!empty($query_param['order']) && !empty($query_param['order']['state'])) {
            $query_param['order']['`keep_alive`'] = $query_param['order']['state'];
            unset($query_param['order']['state']);
        }
        $reseller_empty_name = $this->setLocalization('Empty');
        $response['data'] = array_map(function($val) use ($reseller_empty_name) {
            $val['last_active'] = (int)$val['last_active']; 
            $val['last_change_status'] = (int) strtotime($val['last_change_status']);
            $val['last_change_status'] = $val['last_change_status'] > 0 ? $val['last_change_status']: 0;
            $val['expire_billing_date'] = (int) strtotime($val['expire_billing_date']);
            $val['expire_billing_date'] = $val['expire_billing_date'] > 0 ? $val['expire_billing_date']: 0;
            $val['reseller_id'] = !empty($val['reseller_id']) ? $val['reseller_id']: '-';
            $val['reseller_name'] = !empty($val['reseller_name']) ? $val['reseller_name']: $reseller_empty_name;
            return $val;
        }, $this->db->getUsersList($query_param));

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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'toggleUserStatus';
        $error = $this->setlocalization('Failed');

        $event = new \SysEvent();
        $event->setUserListById($this->postData['userid']);
        if ($this->db->toggleUserStatus($this->postData['userid'], (int) (!$this->postData['userstatus']))) {
            $error = '';
            if ($this->postData['userstatus'] == 1) {
                $event->sendCutOn();
            } else {
                $event->sendCutOff();
            }
            $data['title'] = ($this->postData['userstatus'] ? 'Отключить' : 'Включить');
            $data['status'] = ($this->postData['userstatus'] ? '<span class="">' . $this->setLocalization("on") . '</span>' : '<span class="">' . $this->setLocalization("off") . '</span>');
            $data['userstatus'] = (int) !$this->postData['userstatus'];
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_user() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
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

    public function reset_users_settings_password() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'resetUsersParentPassword';
        $error = '';
        $data['newpass'] = '0000';
        $this->db->updateUserById(array('settings_password' => '0000'), $this->postData['userid']);

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function reset_user_fav_tv() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'resetUserFavTv';
        $this->db->updateUserFavItv(array('fav_ch' => ''), $id = $this->postData['userid']);
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function add_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addConsoleGroup';
        $error = $this->setlocalization('Failed');
        $check = $this->db->getConsoleGroup(array('Sg.name' => $this->postData['name']));
        if (empty($check)) {
            $data['id'] = $this->db->insertConsoleGroup(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $data['reseller_id'] = $this->admin->getResellerID();
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editConsoleGroup';
        $error = $this->setlocalization('Failed');
        $check = $this->db->getConsoleGroup(array('Sg.name' => $this->postData['name']));
        if (empty($check)) {
            $this->db->updateConsoleGroup(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
            $data['reseller_id'] = $this->admin->getResellerID();
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['consolegroupid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeConsoleGroup';
        $data['id'] = $this->postData['consolegroupid'];
        $this->db->deleteConsoleGroup(array('id' => $this->postData['consolegroupid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_login() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkLogin';
        $error = $this->setlocalization('Name already used');
        if ($this->db->checkLogin(trim($this->postData['name']))) {
            $data['chk_rezult'] = $this->setlocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setlocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_console_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkConsoleName';
        $error = $this->setlocalization('Name already used');
        if ($this->db->checkConsoleName(trim($this->postData['name']))) {
            $data['chk_rezult'] = $this->setlocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setlocalization('Name is available');
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
                $query_param['limit']['limit'] = 50;
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeConsoleItem';
        $data['stb_in_group_id'] = $this->postData['consoleid'];
        $this->db->deleteConsoleItem(array('id' => $this->postData['consoleid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function add_console_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['groupid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addConsoleItem';
        $error = $this->setlocalization('Failed');
        $mac = \Middleware::normalizeMac($this->postData['name']);
        if (!empty($mac)) {
            $check_in_group = $this->db->getConsoleGroupList(array('where' => array('mac' => $mac), 'order' => 'mac', 'limit' => array('limit' => 1)));

            $check_in_users = $this->db->getUsersList(array('select' => array("*", "users.id as uid"), 'where' => array('mac' => $mac), 'order' => 'mac'));
            if ((count($check_in_group) == 0 || (int)$check_in_group[0]['stb_group_id'] == 0) && !empty($check_in_users)) {
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkConsoleItem';
        $error = $this->setlocalization($this->setlocalization('Name already used'));
        $mac = \Middleware::normalizeMac($this->postData['mac']);
        $data['jjj'] = $check_in_group = $this->db->getConsoleGroupList(array('where' => array('mac' => $mac), 'order' => 'mac', 'limit' => array('limit' => 1)));
        $check_in_users = $this->db->getUsersList(array('select' => array("*", "users.id as uid"), 'where' => array('mac' => $mac), 'order' => 'mac'));

        if (count($check_in_group) != 0 && (int)$check_in_group[0]['stb_group_id'] != 0) {
            $group_name = $check_in_group[0]['name'];
            $data['chk_rezult'] = $this->setLocalization('This user is already connected to the group') ." '$group_name'";
            $error = $this->setLocalization('This user is already connected to the group') . " '$group_name'";
        } elseif (empty($check_in_users)) {
            $data['chk_rezult'] = $error = $this->setLocalization("User with this MAC-address is not defined");
        } else {
            $data['chk_rezult'] = $this->setlocalization('The user can be connected to the group');
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
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (!empty($this->data['id'])) {
            $query_param['where']['users.`id`'] = $this->data['id'];
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsLogList($query_param['where']);
        $response["recordsFiltered"] = $this->db->getTotalRowsLogList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        }

        $response['data'] = $this->db->getLogList($query_param);

        $this->setLogObjects($response['data']);
        if (!empty($deleted_params['order'])) {
            $this->orderByDeletedParams($response['data'], $deleted_params['order']);
        }
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $response['data'] = array_map(function($row){
            $row['time'] = (int) strtotime($row['time']); 
            return $row;
        }, $response['data']);

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
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        $response['data'] = $this->db->getUsersList($query_param, TRUE);
        
        $response['data'] = array_map(function($row){
            $row['last_change_status'] = (int) strtotime($row['last_change_status']); 
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

    public function set_expire_billing_date(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['userid']) || empty($this->postData['setaction'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'setExpireBillingDate';
        $error = 'Error';
        if ($this->postData['setaction'] == 'set' && !empty($this->postData['expire_date'])){
            $date = $this->postData['expire_date'];
        } elseif ($this->postData['setaction'] == 'unset') {
            $date = 0;
        }
        if (isset($date)) {
            if (!empty($date) && preg_match("/(0[1-9]|[12][0-9]|3[01])([- \/\.])(0[1-9]|1[012])[- \/\.](19|20)\d\d/im", $date, $match)) {
                $date = implode('-', array_reverse(explode($match[2], $date)));
            }
            $this->db->updateUserById(array('expire_billing_date' => $date), $this->postData['userid']);
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_user_to_reseller(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageUserReseller';
        $user_id = $this->postData['id'];
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_reseller = $this->db->getReseller(array('select'=>array('*'), 'where'=>array('id' => $target_id)), TRUE);
        } else{
            $count_reseller = 1;
        }

        if (!empty($count_reseller) && $source_id !== $target_id) {
            $this->db->updateResellerMemberByID('users', $user_id, $target_id);
            $data['msg'] = $this->setLocalization('Moved');
        } else {
            $error = $data['msg'] = empty($count_reseller) ? $this->setLocalization('Not found reseller for moving') : $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_user_group_to_reseller(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['consolegroupid']) || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editConsoleGroup';
        $console_group_id = $this->postData['consolegroupid'];
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_reseller = $this->db->getReseller(array('select'=>array('*'), 'where'=>array('id' => $target_id)), TRUE);
        } else{
            $count_reseller = 1;
        }

        if (!empty($count_reseller) && $source_id !== $target_id) {
            $this->db->updateResellerMemberByID('stb_groups', $console_group_id, $target_id);
            $data['msg'] = $this->setLocalization('Moved');
            $check = $this->db->getConsoleGroup(array('Sg.id' => $this->postData['consolegroupid']));
            $data['id'] = $this->postData['consolegroupid'];
            $data['name'] = $check[0]['name'];
            $data['reseller_id'] = $check[0]['reseller_id'];

        } else {
            $error = $data['msg'] = empty($count_reseller) ? $this->setLocalization('Not found reseller for moving') : $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
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
        if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
            $return['status'] = $this->data['filters']['status_id'] - 1;
        }
        if (array_key_exists('state_id', $this->data['filters']) && $this->data['filters']['state_id'] != 0) {
            $return['keep_alive' . ($this->data['filters']['state_id'] - 1 ? "<" : ">")] = "$now_time";
        }
        if (array_key_exists('interval_from', $this->data['filters']) && $this->data['filters']['interval_from']!= 0) {
            $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_from']);
            $return['UNIX_TIMESTAMP(last_active)>='] = $date->getTimestamp();
        }
        if (array_key_exists('interval_to', $this->data['filters']) && $this->data['filters']['interval_to']!= 0) {
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
        $additional_services = $status = array(
            0 => $this->setLocalization('on'),
            1 => $this->setLocalization('off')
        );
/*
        $additional_services = array(
            0 => 'Выключены',
            1 => 'Включены'
        );*/

        $stb_groups = new \StbGroup();

        $all_groups = $stb_groups->getAll();
        $group_keys = $this->getFieldFromArray($all_groups, 'id');
        $group_names = $this->getFieldFromArray($all_groups, 'name');

        if (is_array($group_keys) && is_array($group_names) && count($group_keys) == count($group_names) && count($group_keys) > 0) {
            $all_groups = array_combine($group_keys, $group_names);
        } else {
            $all_groups = array(NULL);
        }

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

        if (is_array($plan_keys) && is_array($plan_names) && count($plan_keys) == count($plan_names)) {
            $tariff_plans = array_combine($plan_keys, $plan_names);
        } else {
            $tariff_plans = array();
        }

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $resellers = array_merge($resellers, $this->db->getAllFromTable('reseller'));
            $resellers = array_combine($this->getFieldFromArray($resellers, 'id'), $this->getFieldFromArray($resellers, 'name'));

            if (empty($data['reseller_id'])) {
                $data['reseller_id'] = '-';
            }
        }

        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('fname', 'text', array('required' => FALSE))
                ->add('login', 'text', $this->getAddUserFormParam($edit))
                ->add('password', 'password', array('required' => FALSE))
                ->add('phone', 'text', array('required' => FALSE))
                ->add('ls', 'text', array('required' => FALSE))
                ->add('group_id', 'choice', array(
                        'choices' => $all_groups,
                        'data' => (!empty($data['group_id'])? $data['group_id']: NULL) ,
                        'required' => FALSE
                        )
                )
                ->add('mac', 'text', ($edit?array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE):array('required' => FALSE)))
                ->add('status', 'choice', array(
                    'choices' => $status,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($status)))),
                    'required' => FALSE
                        )
                )
                ->add('comment', 'textarea', array('required' => FALSE))
                ->add('save', 'submit');
//                ->add('reset', 'reset');
        if (!empty($data['id'])) {
            $form->add('ip', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('parent_password', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('settings_password', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('fav_itv', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('version', 'textarea', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('account_balance', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE));
        }
        if ($this->app['tarifPlanFlag']){
            $form->add('tariff_plan_id', 'choice', array(
                    'choices' => $tariff_plans,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($tariff_plans)))),
                    'required' => FALSE
                        )
                );
        } else {
            $form->add('additional_services_on', 'choice', array(
                    'choices' => $additional_services,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($additional_services)))),
                    'required' => FALSE
                        )
                );
        }

        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $form->add('expire_billing_date', 'text', array('required' => FALSE));
        }
        if (empty($this->app['reseller'])) {
            $form->add('reseller_id', 'choice', array(
                    'choices' => $resellers,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($resellers)))),
                    'required' => FALSE
                )
            );
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
                $id = $data['id'];

                if (empty($member)) {
                    $stb_groups->addMember(array('mac' => \Middleware::normalizeMac($data['mac']), 'uid' => \Middleware::getUidByMac($data['mac']), 'stb_group_id' => $data['group_id']));
                } else {
                    $stb_groups->setMember(array('stb_group_id' => $data['group_id']), $member['id']);
                }

                $curr_fields = $this->db->getTableFields('users');
                $curr_fields = $this->getFieldFromArray($curr_fields, 'Field');
                $curr_fields = array_flip($curr_fields);

                $data = array_intersect_key($data, $curr_fields);
                $match = array();
                if (!empty($data['expire_billing_date']) && preg_match("/(0[1-9]|[12][0-9]|3[01])([- \/\.])(0[1-9]|1[012])[- \/\.](19|20)\d\d/im", $data['expire_billing_date'], $match)) {
                    $data['expire_billing_date'] = implode('-', array_reverse(explode($match[2], $data['expire_billing_date'])));
                } else {
                    $data['expire_billing_date'] = 0;
                }
                if ($data['reseller_id'] == '-') {
                    $data['reseller_id'] = NULL;
                }
                if (!empty($this->user) && array_key_exists('status', $this->user) && ((int) $this->user['status'] != (int)$data['status'])) {
                    $data['last_change_status'] = FALSE;
                    $event = new \SysEvent();
                    $event->setUserListById($data['id']);
                    if ((int)$data['status'] == 0) {
                        $event->sendCutOn();
                    } else {
                        $event->sendCutOff();
                    }
                } else {
                    unset($data['last_change_status']);
                }

                unset($data['version']);

                $result = call_user_func_array(array($this->db, $action), array($data, $data['id']));

                if (!empty($this->postData['tariff_plan_packages'])) {
                    $this->changeUserPlanPackages($id, $this->postData['tariff_plan_packages']);
                }
                return TRUE;
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
                    if (!empty($tmp_arr[1])) {
                        $media = $this->db->getVideo(array('id' => $tmp_arr[1]));
                        $data[$key]['object'] = $media['name'];
                    } else {
                        $data[$key]['type'] = $this->logObjectsTypes['unknown'];
                        $data[$key]['object'] = '';
                    }
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

    private function changeUserPlanPackages($user_id, $tariff_plan_packages){
        $users_tarif_plans = array_map(function($val) {
            $val['optional'] = (int) $val['optional'];
            $val['subscribed'] = (int) $val['subscribed'];
            return $val;
        }, $this->db->getTarifPlanByUserID($user_id));
        
        $user = \User::getInstance($user_id);
        foreach ($users_tarif_plans as $row) {
            if (array_key_exists($row['package_id'], $tariff_plan_packages) && $tariff_plan_packages[$row['package_id']] == 'on') {
                $user->subscribeToPackage($row['package_id'], null, true);
            } else {
                $user->unsubscribeFromPackage($row['package_id'], null, true);
            }
        }
    }
    
    private function getUsersListDropdownAttribute() {
        $attribute = array(
            array('name'=>'mac',                'title'=>'MAC',                                 'checked' => TRUE),
            array('name'=>'ip',                 'title'=>'IP',                                  'checked' => TRUE),
            array('name'=>'login',              'title'=>$this->setLocalization('Login'),       'checked' => TRUE),
            array('name'=>'ls',                 'title'=>$this->setLocalization('Account'),     'checked' => TRUE),
            array('name'=>'fname',              'title'=>$this->setLocalization('Name'),        'checked' => TRUE),
            array('name'=>'last_change_status', 'title'=>$this->setLocalization('Last modified'),'checked' => TRUE),
            array('name'=>'state',              'title'=>$this->setLocalization('State'),       'checked' => TRUE),
            array('name'=>'status',             'title'=>$this->setLocalization('Status'),      'checked' => TRUE)
            );
        if (empty($this->app['reseller'])) {
            $attribute[] = array('name'=>'reseller_name',      'title'=>$this->setLocalization('Reseller'),    'checked' => TRUE);
        }
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $attribute[] = array('name'=>'expire_billing_date', 'title'=>$this->setLocalization('Expire billing date'),'checked' => TRUE);
        }
        $attribute[] = array('name'=>'operations',         'title'=>$this->setLocalization('Operations'),  'checked' => TRUE);

        return $attribute;
    }

    private function getUsersConsolesGroupsDropdownAttribute() {
        $attribute = array(
            array('name'=>'name',               'title'=>$this->setLocalization('Name'),            'checked' => TRUE),
            array('name'=>'users_count',        'title'=>$this->setLocalization('Quantity of users'),'checked' => TRUE)
        );
        if (empty($this->app['reseller'])) {
            $attribute[] = array('name'=>'reseller_name',      'title'=>$this->setLocalization('Reseller'),    'checked' => TRUE);
        }
        $attribute[] = array('name'=>'operations',         'title'=>$this->setLocalization('Operations'),  'checked' => TRUE);
        return $attribute;
    }
}
