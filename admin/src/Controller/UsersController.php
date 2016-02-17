<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class UsersController extends \Controller\BaseStalkerController {

    protected $allStatus = array();
    protected $mediaTypeName = array(
        0 => '--',
        1 => 'TV',
        2 => 'Video',
        3 => 'Karaoke',
        4 => 'Audio',
        5 => 'Radio',
        6 => 'My records',
        7 => 'Records',
        9 => 'ad',
        10 => 'Media browser',
        11 => 'Tv archive',
        12 => 'Records',
        14 => 'TimeShift',
        20 => 'Infoportal',
        21 => 'Infoportal',
        22 => 'Infoportal',
        23 => 'Infoportal',
        24 => 'Infoportal',
        25 => 'Infoportal'
    );
    private $allState = array(array('id' => 2, 'title' => 'Offline'), array('id' => 1, 'title' => 'Online'));
    private $watchdog = 0;
    private $userFields = array(
        'users.id as `id`', "`mac`", "`ip`", "`country`", "`login`", "`ls`", "`fname`", "reseller.id as `reseller_id`", "`theme`",
        "`status`", 'tariff_plan.name as `tariff_plan_name`',
        "DATE_FORMAT(last_change_status,'%d.%m.%Y') as `last_change_status`",
        "concat (users.fname) as `fname`",
        "UNIX_TIMESTAMP(`keep_alive`) as `last_active`",
        "`expire_billing_date` as `expire_billing_date`",
        "users.`created` as `created`",
        "`account_balance`", "`now_playing_type`", "IF(now_playing_type = 2 and storage_name, CONCAT('[', storage_name, ']', now_playing_content), now_playing_content) as `now_playing_content`"
    );
    private $logObjectsTypes = array(
        'itv' => 'IPTV каналы',
        'video' => 'Видео клуб',
        'unknown' => '',
    );
    protected $formEvent = array();
    protected $hiddenEvent = array();

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->watchdog = \Config::get('watchdog_timeout') * 2;
        $this->userFields[] = "((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`keep_alive`)) <= $this->watchdog) as `state`";
        $this->allStatus = array(
            array('id' => 1, 'title' => $this->setLocalization('on')),
            array('id' => 2, 'title' => $this->setLocalization('off'))
        );

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

        if (empty($this->app['reseller'])) {
            $this->userFields[] = "reseller.name as `reseller_name`";
        }

        $this->app['defTTL'] = array(
            'send_msg' => 7*24*3600,
            'send_msg_with_video' => 7*24*3600,
            'other' => $this->watchdog
        );
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

        $users_filter = array();
        if (!empty($this->data['filters'])) {
            $users_filter = $this->data['filters'];
        }
        if ( !empty($this->data['filter_set'])) {
            $curr_filter_set = $this->db->getFilterSet(array('id' => $this->data['filter_set']));
            if (!empty($curr_filter_set) && count($curr_filter_set) > 0 && !empty($curr_filter_set[0]['filter_set'])) {
                $curr_filter_set[0]['filter_set'] = unserialize($curr_filter_set[0]['filter_set']);
                if (!empty($curr_filter_set[0]['filter_set'])) {
                    $curr_filter_set[0]['filter_set'] = array_combine($this->getFieldFromArray($curr_filter_set[0]['filter_set'], 0), $this->getFieldFromArray($curr_filter_set[0]['filter_set'], 2));
                    $users_filter = array_replace($curr_filter_set[0]['filter_set'], $users_filter);
                }
                $this->app['filter_set'] = $curr_filter_set[0];
            }
        }

        if (!array_key_exists('state', $users_filter)) {
            $users_filter['state'] = "0";
        }
        if (!array_key_exists('status', $users_filter)) {
            $users_filter['status'] = "0";
        }
        if (!array_key_exists('stbmodel', $users_filter)) {
            $users_filter['stbmodel'] = "0";
        }

        $filter_set = \Filters::getInstance();
        $filter_set->setResellerID($this->app['reseller']);
        $filter_set->initData('users', 'id');
        $self = $this;

        $users_filter = array_filter($users_filter, function($val){ return $val !== 'without'; });

        if (!empty($users_filter)) {
            $filters = array_map(function($row) use ($users_filter, $self){
                $row['title']= $self->setLocalization($row['title']);
                if (array_key_exists($row['text_id'], $users_filter)) {
                    $row['value'] = $users_filter[$row['text_id']];
                }
                return $row;
            }, $filter_set->getFilters(array_keys($users_filter)));
        } else {
            $filters = array();
        }

        if (!empty($this->app['filters'])) {
            $users_filter = array_merge($this->app['filters'], $users_filter);
        }

        if (!empty($filters)) {
            $this->app['filters_set'] = array_map(function($row) use ($self){
                if (is_array($row['values_set'])) {
                    $row['values_set'] = array_map(function($row_in) use ($self){
                        $row_in['title']= $self->setLocalization($row_in['title']);
                        return $row_in;
                    }, $row['values_set']);
                }
                return $row;
            }, array_combine($this->getFieldFromArray($filters, 'text_id'), array_values($filters)));
        }

        reset($users_filter);

        while(list($text_id, $row) = each($users_filter)){
            if (array_key_exists($text_id, $this->app['filters_set']) && $this->app['filters_set'][$text_id]['type'] != 'STRING') {
                $value = explode("|", $row);
                if ( $this->app['filters_set'][$text_id]['type'] == 'DATETIME') {
                    $users_filter[$text_id] = array(
                        'from' => !empty($value[0]) ? $value[0] : '',
                        'to' => !empty($value[1]) ? $value[1] : ''
                    );
                } else {
                    $users_filter[$text_id] = $value;
                }
            }
        }

        $this->app['filters'] = $users_filter;

        $filters_template = array_filter(array_map(function($row) use ($users_filter, $self, $filter_set){
            if ((int)$row['default'] || ($row['type'] != 'STRING' && $row['type'] != 'DATETIME' && $row['values_set'] === FALSE)) {
                return FALSE;
            } else {
                $row['title']= $self->setLocalization($row['title']);
                $row['name'] = $row['text_id'];
                $row['checked'] = array_key_exists($row['text_id'], $users_filter);
                return $row;
            }
        }, $filter_set->getFilters()));
        if (!empty($filters_template)) {
            $this->app['filters_template'] = array_combine($this->getFieldFromArray($filters_template, 'text_id'), array_values($filters_template));
        }

        $users = $this->users_list_json();

        $this->app['allUsers'] = $users['data'];
        $this->app['allStatus'] = $this->allStatus;
        $this->app['allState'] = $this->allState;
        $this->app['totalRecords'] = $users['recordsTotal'];
        $this->app['recordsFiltered'] = $users['recordsFiltered'];
        $this->app['consoleGroup'] = $this->db->getConsoleGroup(array('select' => $this->getUsersGroupsConsolesListFields()));

        $attribute = $this->getUsersListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $this->app['enableBilling'] = TRUE;
        }

        $this->app['hide_media_info'] = \Config::getSafe('hide_media_info_for_offline_stb', false);
        $this->app['mediaTypeName'] = $this->setLocalization($this->mediaTypeName);

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        }

        $reseller_info = $this->db->getReseller(array('where'=>array('id' => $this->app['reseller'])));
        if (!empty($reseller_info[0]['max_users'])) {
            $this->app['resellerUserLimit'] = ((int)$reseller_info[0]['max_users'] - (int)$users['recordsTotal']) > 0;
        } else {
            $this->app['resellerUserLimit'] = TRUE;
        }

        if (!empty($curr_filter_set)) {
            $this->app['breadcrumbs']->addItem($this->setLocalization("Used filter") . ' "' . $curr_filter_set[0]['title'] . '"');
        }

        $this->app['messagesTemplates'] = $this->db->getAllFromTable('messages_templates', 'title');

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_groups() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        }

        $list = $this->users_consoles_groups_list_json();
        $this->app['ads'] = $list['data'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        $attribute = $this->getUsersConsolesGroupsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_consoles_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->users_consoles_logs_json();
        
        if (!empty($this->data['id'])) {
            $this->app['user'] = $this->db->getUsersList(array(
                'select' => array(
                    '`users`.`name`',
                    '`users`.`fname`',
                    '`users`.`mac`'
                ),
                'where' => array('`users`.id' => $this->data['id'])
            ));
        }
        
        $this->app['logList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $user_name = '';

        $attribute = $this->getUsersConsolesLogsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

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

        $attribute = $this->getUsersConsolesReportDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

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
        $this->app['userEdit'] = FALSE;

        $reseller_info = $this->db->getReseller(array('where'=>array('id' => $this->app['reseller'])));
        $users_total = $this->db->getTotalRowsUresList();

        if (!empty($reseller_info[0]['max_users'])) {
            $this->app['resellerUserLimit'] = ((int)$reseller_info[0]['max_users'] - (int)$users_total) > 0;
        } else {
            $this->app['resellerUserLimit'] = TRUE;
        }

        if ($this->app['resellerUserLimit']) {
            $this->app['tarifPlanFlag'] = \Config::getSafe('enable_tariff_plans', false);
            $form = $this->buildUserForm();

            if ($this->saveUsersData($form)) {
                return $this->app->redirect('users-list');
            }
            $this->app['form'] = $form->createView();

            if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
                $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();
            }
            if (\Config::getSafe('enable_internal_billing', 'false')) {
                $this->app['enableBilling'] = TRUE;
            }
        }
        $this->app['breadcrumbs']->addItem($this->setLocalization('Users list'), $this->app['controller_alias'] . '/users-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add user'));
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function edit_users() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );

        $query_param['select'] = array_merge($query_param['select'], array_diff($this->userFields, $query_param['select']));

        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $query_param['where']['users.id'] = $this->postData['form']['id'];
        } elseif ($this->method == 'GET' && !empty($this->data['id'])) {
            $query_param['where']['users.id'] = $this->data['id'];
        } elseif ($this->method == 'GET' && !empty($this->data['mac'])) {
            $query_param['where']['users.mac'] = $this->data['mac'];
        } else {
            return $this->app->redirect('add-users');
        }

        $query_param['order'] = 'users.id';
        $user = $this->db->getUsersList($query_param);
        $this->user = (is_array($user) && count($user) > 0) ? $user[0] : array();
        if (empty($this->user)) {
            return $this->app->redirect('add-users');
        }
        $this->app['tarifPlanFlag'] = \Config::getSafe('enable_tariff_plans', false);
        if (!empty($this->user['expire_billing_date']) && preg_match("/(19|20\d\d)[- \/\.](0[1-9]|1[012])[- \/\.](0[1-9]|[12][0-9]|3[01])/im", $this->user['expire_billing_date'], $match)) {
            unset($match[0]);
            $this->user['expire_billing_date'] = implode('-', array_reverse($match));
        } elseif (((int) str_replace(array('-', '.'), '', $this->user['expire_billing_date'])) == 0) {
            $this->user['expire_billing_date'] = '';
        } else {
            $this->user['expire_billing_date'] = str_replace('.', '-', $this->user['expire_billing_date']);
        }
        $this->user['version'] = preg_replace("/(\r\n|\n\r|\r|\n|\s){2,}/i", "$1", stripcslashes($this->user['version']));
        $form = $this->buildUserForm($this->user, TRUE);

        if ($this->saveUsersData($form, TRUE)) {
            return $this->app->redirect('users-list');
        }
        $this->app['form'] = $form->createView();
        $this->app['userEdit'] = TRUE;
        $this->app['userID'] = $this->user['id'];
        
        $users_tarif_plans = array_map(function($val){
            $val['optional'] = (int)$val['optional'];
            $val['subscribed'] = (int)$val['subscribed'];
            return $val;
        }, $this->db->getTarifPlanByUserID($this->user['id']));
        
        $this->app['userTPs'] = $users_tarif_plans;

        $this->app['state'] = (int) $this->user['state'];

        if (\Config::getSafe('enable_tv_subscription_for_tariff_plans', false)) {
            $this->app['channelsCost'] = "0.00"; //$this->getCostSubChannels();    
        }
        if (\Config::getSafe('enable_internal_billing', 'false')) {
            $this->app['enableBilling'] = TRUE;
        }

        $this->app['resellerUserLimit'] = TRUE;

        $this->app['userName'] = $this->user['mac'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Users list'), $this->app['controller_alias'] . '/users-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit user'));
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
        $tmp = $this->db->getConsoleGroup(array('select' => $this->getUsersGroupsConsolesListFields(), 'where' => array('Sg.id' => $id)));
        $this->app['consoleGroup'] = $tmp[0];
        $this->app['groupid'] = $id;
        $list = $this->users_groups_consoles_list_json();
        $this->app['consoleGroupList'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];

        $attribute = $this->getUsersGroupsConsolesListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['breadcrumbs']->addItem($this->setLocalization('User groups'), $this->app['controller_alias'] . '/users-consoles-groups');
        $this->app['breadcrumbs']->addItem($this->setLocalization("STB of group") . " '{$this->app['consoleGroup']['name']}'");
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function users_filter_list(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getUsersFiltersDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allAdmins'] = $this->db->getAllFromTable('administrators', 'login');

        $list = $this->users_filter_list_json();
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        if (!empty($this->data['filters'])) {
            $this->app['filters'] = $this->data['filters'];
        }

        $this->app['consoleGroup'] = $this->db->getConsoleGroup(array('select' => $this->getUsersGroupsConsolesListFields()));

        $this->app['formEvent'] = $this->formEvent;
        $this->app['allEvent'] = array_merge($this->formEvent, $this->hiddenEvent);

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

        if (($search = array_search('created', $query_param['select'])) != FALSE) {
            unset($query_param['select'][$search]);
            unset($query_param['where']['created']);
            unset($query_param['like']['created']);
        }

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getUsersFilters();

        $query_param['in'] = array();
        if (!empty($this->app['filters']) || !empty($this->data['filter_set'])) {
            $filter_set = \Filters::getInstance();
            $filter_set->setResellerID($this->app['reseller']);
            $filter_set->initData('users', 'id');
            $app_filter = array();
            if (!empty($this->app['filters'])) {
                $app_filter = $this->app['filters'];
            } elseif(!empty($this->data['filter_set'])) {
                $data_filter = $this->db->getFilterSet(array('id'=>$this->data['filter_set']));
                if (!empty($data_filter[0]) && array_key_exists('filter_set', $data_filter[0])) {
                    $data_filter = @unserialize($data_filter[0]['filter_set']);
                    if (is_array($data_filter) && count($data_filter)>0 ) {
                        $app_filter = array_combine($this->getFieldFromArray($data_filter, 0), $this->getFieldFromArray($data_filter, 2));
                    }
                }
            }

            $app_filter = array_filter($app_filter, function($val){ return $val != 'without';});
            $all_filters = $filter_set->getFilters();
            $filtered_users = $filters_with_cond = array();
            $greatest = FALSE;
            $cond = "=";
            reset($all_filters);
            while(list($key, $row) = each($all_filters)) {
                if (array_key_exists($row['text_id'], $app_filter)) {
                     if($row['type'] == 'DATETIME') {
                        if (is_string($app_filter[$row['text_id']])) {
                            $tmp = explode('|', $app_filter[$row['text_id']]);
                            $app_filter[$row['text_id']] = array(
                                'from' => !empty($tmp[0]) ? $tmp[0] : 0,
                                'to' => !empty($tmp[1]) ? $tmp[1] : (empty($tmp[0]) ? time(): 0)
                            );
                        }
                        $filters_with_cond[] = array( $row['method'], ">=", $app_filter[$row['text_id']]['from'] );
                        if (!empty($app_filter[$row['text_id']]['to'])) {
                            $filters_with_cond[] = array($row['method'], "<=", $app_filter[$row['text_id']]['to']);
                        }
                        continue;
                    } elseif ($row['type'] == 'STRING') {
                        $cond = "*=";
                    } else {
                        $cond = "=";
                        if (is_string($app_filter[$row['text_id']])) {
                            $tmp = explode('|', $app_filter[$row['text_id']]);
                            if (empty($tmp) || (is_array($tmp) && (array_search('0', $tmp, TRUE) !== FALSE || array_search('', $tmp, TRUE) !== FALSE))){
                                continue;
                            }

                            $filtered_users[$row['text_id']] = array();
                            foreach($tmp as $value){
                                $filter_set->initData('users', 'id');
                                if (($row['text_id'] == 'status') || ($row['text_id'] == 'state') ) {
                                    if ((int)$value) {
                                        $value = (int)($value - 1 > 0);
                                    } else {
                                        continue;
                                    }
                                }
                                $filter_set->setFilters($row['method'], $cond, $value);
                                $filtered_users[$row['text_id']] = array_unique(array_merge($filtered_users[$row['text_id']], $filter_set->getData()));
                            }
                            if ($greatest === FALSE || count($filtered_users[$row['text_id']]) > count($filtered_users[$greatest])) {
                                $greatest = $row['text_id'];
                            }
                        }
                         continue;
                    }
                    if (empty($app_filter[$row['text_id']]) || (is_numeric($app_filter[$row['text_id']]) && (int)$app_filter[$row['text_id']] == 0)){
                        continue;
                    }
                    $value = (($row['text_id'] == 'status' || $row['text_id'] == 'state') ? (int)($app_filter[$row['text_id']] - 1 > 0) : $app_filter[$row['text_id']]);
                    $filters_with_cond[] = array($row['method'], $cond, $value);
                }
            }
            $filter_set->initData('users', 'id');
            $filter_set->setFilters($filters_with_cond);
            $last = uniqid();
            $filtered_users[$last] = $filter_set->getData();
                                /*print_r($filtered_users);*/
                                /*exit;*/
            if ($greatest === FALSE || count($filtered_users[$last]) > count($filtered_users[$greatest])) {
                $greatest = $last;
            }

            $result = $filtered_users[$greatest];
            unset($filtered_users[$greatest]);
            foreach ($filtered_users as $value) {
                $result = array_intersect($result, $value);
            }

            $query_param['in'] = array('users.id'=>$result) ;
        }

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $query_param['select'] = array_merge($query_param['select'], array_diff($this->userFields, $query_param['select']));
        $response['recordsTotal'] = $this->db->getTotalRowsUresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsUresList($query_param['where'], $query_param['like'], $query_param['in']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        if (!empty($query_param['order'])) {
            if (!empty($query_param['order']['state'])) {
                $query_param['order']['`keep_alive`'] = $query_param['order']['state'];
                unset($query_param['order']['state']);
            } elseif (!empty($query_param['order']['last_change_status'])) {
                $query_param['order']['unix_timestamp(last_change_status)'] = $query_param['order']['last_change_status'];
                unset($query_param['order']['last_change_status']);
            } elseif (!empty($query_param['order']['ls'])) {
                $direct = strtoupper($query_param['order']['ls']);
                $order = array(
                    'ls=0' =>  $direct == 'ASC' ? 'ASC' : 'DESC',
                    '-ls' =>  $direct == 'ASC' ? 'DESC': 'ASC',
                    'ls' =>  $direct == 'ASC' ? 'ASC' : 'DESC'
                );
                unset($query_param['order']['ls']);
                $query_param['order'] = array_merge($query_param['order'], $order);
            } elseif (!empty($query_param['order']['created'])) {
                $query_param['order']['users.created'] = $query_param['order']['created'];
                unset($query_param['order']['created']);
            }
        }

        $reseller_empty_name = $this->setLocalization('Empty');

        $countries = array();
        $country_field_name  = ($this->app['lang'] == 'ru' ? 'name': 'name_en');

        foreach($this->db->getAllFromTable('countries') as $row){
            $countries[$row['iso2']] = $row[$country_field_name];
        }

        $response['data'] = array_map(function($val) use ($reseller_empty_name, $countries) {
            $val['last_active'] = (int)$val['last_active']; 
            $val['last_change_status'] = (int) strtotime($val['last_change_status']);
            $val['last_change_status'] = $val['last_change_status'] > 0 ? $val['last_change_status']: 0;
            $val['expire_billing_date'] = (int) strtotime($val['expire_billing_date']);
            $val['expire_billing_date'] = $val['expire_billing_date'] > 0 ? $val['expire_billing_date']: 0;
            $val['created'] = (int) strtotime($val['created']);
            $val['created'] = $val['created'] > 0 ? $val['created']: 0;
            $val['reseller_id'] = !empty($val['reseller_id']) ? $val['reseller_id']: '-';
            $val['reseller_name'] = !empty($val['reseller_name']) ? $val['reseller_name']: $reseller_empty_name;
            if (!empty($val['country'])) {
                $val['country_name'] = $countries[$val['country']];
                $val['country'] = strtolower($val['country']);
            } else {
                $val['country_name'] = $val['country'] = '';
            }
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
        $error = $this->setLocalization('Failed');

        $event = new \SysEvent();
        $event->setUserListById($this->postData['userid']);
        if ($this->db->toggleUserStatus($this->postData['userid'], (int) (!$this->postData['userstatus']))) {
            $error = '';
            if ($this->postData['userstatus'] == 1) {
                $event->sendCutOn();
            } else {
                $event->sendCutOff();
            }
            $data['title'] = ($this->postData['userstatus'] ? $this->setLocalization("on") : $this->setLocalization("off"));
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

        $reseller_info = $this->db->getReseller(array('where'=>array('id' => $this->app['reseller'])));
        $users_total = $this->db->getTotalRowsUresList();

        if (!empty($reseller_info[0]['max_users'])) {
            $data['add_button'] = ((int)$reseller_info[0]['max_users'] - (int)$users_total) > 0;
        } else {
            $data['add_button'] = TRUE;
        }

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

    public function users_consoles_groups_list_json() {

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
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filds_for_select = $this->getUsersGroupsConsolesListFields();

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        foreach($query_param['order'] as $key => $val){
            if ($search = array_search($key, $filds_for_select )){
                $new_key = str_replace(" as $search", '', $key);
                unset($query_param['order'][$key]);
                $query_param['order'][$new_key] = $val;
            }
        }

        if (!isset($query_param['like'])) {
            $query_param['like'] = array();
        }

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (!empty($this->app['reseller'])) {
            $query_param['where']['reseller_id'] = $this->app['reseller'];
        }

        $response['recordsTotal'] = $this->db->getTotalRowsConsoleGroup();
        $response['recordsFiltered'] = $this->db->getTotalRowsConsoleGroup($query_param['where'], $query_param['like']);

        $allGroups = $this->db->getConsoleGroup($query_param);
        if (is_array($allGroups)) {
            $empty_reseller = $this->setLocalization('Empty');
            $response["data"] = array_map(function ($row) use ($empty_reseller) {
                if (empty($row['reseller_name'])) {
                    $row['reseller_name'] = $empty_reseller;
                }
                if (empty($row['reseller_id'])) {
                    $row['reseller_id'] = '-';
                }
                $row['operations'] = '';
                return $row;
            }, $allGroups);
        }

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function add_console_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getConsoleGroup(array('where' => array('Sg.name' => $this->postData['name'])), 'COUNT');
        if (empty($check)) {
            $data['id'] = $this->db->insertConsoleGroup(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $data['reseller_id'] = ((int) $this->admin->getResellerID()? $this->admin->getResellerID(): '-');
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
        $data['action'] = 'manageList';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getConsoleGroup(array('where' => array('Sg.name' => $this->postData['name'], 'Sg.id<>' => $this->postData['id'])));
        if (empty($check)) {
            $result = $this->db->updateConsoleGroup(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            if (is_numeric($result)) {
                $error = '';
                $data['id'] = $this->postData['id'];
                $data['name'] = $this->postData['name'];
                $data['reseller_id'] = $this->admin->getResellerID();
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }
        } else {
            $data['msg'] = $error = $this->setLocalization("Name already used");
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
        $data['action'] = 'manageList';
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
        $error = $this->setLocalization('Name already used');
        $params = array(
            'login' => trim($this->postData['name'])
        );
        if (!empty($this->postData['id'])) {
            $params['id<>'] = $this->postData['id'];
        }
        if ($this->db->checkLogin($params)) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
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
        $error = $this->setLocalization('Name already used');
        if ($this->db->checkConsoleName(trim($this->postData['name']))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
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
        $error = $this->setLocalization('Failed');
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
                $data['msg'] = $error = $this->setLocalization("This user is already connected to the group") . " '$group_name'";
            } elseif (empty($check_in_users)) {
                $data['msg'] = $error = $this->setLocalization("User with this MAC-address is not defined");
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
        $error = $this->setLocalization('Name already used');
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
            $data['chk_rezult'] = $this->setLocalization('The user can be connected to the group');
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
            'uid' => "user_log.`uid` as `uid`",
            'action' => "user_log.`action` as `action`",
            'param' => "user_log.`param` as `param`"
        );

        $error = $this->setLocalization("Error");
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
        $data['action'] = 'manageList';
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
            $check = $this->db->getConsoleGroup(array('where' => array('Sg.id' => $this->postData['consolegroupid'])));
            $data['id'] = $this->postData['consolegroupid'];
            $data['name'] = $check[0]['name'];
            $data['reseller_id'] = $check[0]['reseller_id'];

        } else {
            $error = $data['msg'] = empty($count_reseller) ? $this->setLocalization('Not found reseller for moving') : $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function get_filter(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['text_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addFilter';
        $error = $this->setLocalization('Not exists');

        $filter_set = \Filters::getInstance();
        $filter_set->setResellerID($this->app['reseller']);
        $filter_set->initData('users', 'id');
        $data['filter'] = $filter_set->getFilters($this->postData['text_id']);

        if (!empty($data['filter'])) {
            $error = '';
            $data['filter']['title'] = $this->setLocalization($data['filter']['title']);
            unset($data['filter']['method']);

            if (!empty($data['filter']['values_set'])) {
                reset($data['filter']['values_set']);
                while(list($key, $row) = each($data['filter']['values_set'])){
                    $data['filter']['values_set'][$key]['title'] = $this->setLocalization($row['title']);
                }
            } elseif ($data['filter']['type'] != 'STRING' && $data['filter']['type'] != 'DATETIME' && empty($data['filter']['values_set'])) {
                $data['msg'] = $error = $this->setLocalization('No data for this filter');
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));

    }

    public function save_filter(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['filter_set'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'saveFilterData';
        $error = $this->setLocalization('Not enough data');

        $params = $this->postData['filter_set'];

        if (!empty($params['filter_set'])) {
            $params['filter_set'] = json_decode(urldecode($params['filter_set']), TRUE);
        }

        if (!empty($params['title']) && !empty($params['filter_set'])) {

            $filter_set = \Filters::getInstance();
            $filter_set->setResellerID($this->app['reseller']);
            $filter_set->initData('users', 'id');
            $app_filter = $params['filter_set'];
            $all_filters = $filter_set->getFilters();
            $filters_with_cond = array_filter(array_map(function($row) use ($app_filter) {
                if (array_key_exists($row['text_id'], $app_filter) and trim(trim($app_filter[$row['text_id']], "|")) != '') {
                    if ($row['type'] == 'STRING') {
                        $cond = "*=";
                    } elseif($row['type'] == 'DATETIME') {
                        $cond = ">=";
                    } else {
                        $cond = "=";
                    }
                    return array($row['text_id'], $cond, $app_filter[$row['text_id']]);
                }
                return FALSE;
            }, $all_filters));

            $params['filter_set'] = serialize($filters_with_cond);
            if (!empty($params['filter_set'])) {
                $current = $this->db->getFilterSet(array('id' => $params['id'], 'admin_id' => $params['admin_id']));
                if (!empty($current)) {
                    $operation = 'update';
                    $filter_data['id'] = $params['id'];
                } else {
                    $operation = 'insert';
                }
                $filter_data['params'] = $params;
                unset($params['id']);
                $filter_data['params']['for_all'] = (int)(array_key_exists('for_all', $params) && !empty($params['for_all']));

                $return_id = 0;

                $result = call_user_func_array(array($this->db, $operation."FilterSet"), $filter_data);
                if (is_numeric($result)) {
                    $error = '';
                    if ($result === 0) {
                        $data['nothing_to_do'] = TRUE;
                        $data['msg'] = $this->setLocalization('Nothing to do');
                    }
                    if ($operation == 'insert') {
                        $data['return_id'] = $result;
                    }
                }
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function users_filter_list_json(){
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

        $filds_for_select = $this->getFilterSetFields();
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        if (!empty($this->data['filters']['admin_id'])) {
            $query_param['where'] = array('A.id'=>$this->data['filters']['admin_id']);
        }

        $response['recordsTotal'] = $this->db->getTotalRowsUsersFilters();
        $response["recordsFiltered"] = $this->db->getTotalRowsUsersFilters($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        }

        $filter_set = \Filters::getInstance();
        $filter_set->setResellerID($this->app['reseller']);
        $filter_set->initData('users', 'id');

        $self = $this;

        $response['data'] = array_map(function($row) use ($filter_set, $self){
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
        }, $this->db->getUsersFiltersList($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = '';

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function remove_filter(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setLocalization('Failed');

        $result = $this->db->deleteFilter($this->postData['id']);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function toggle_filter_favorite(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || !array_key_exists('favorite', $this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setLocalization('Failed');

        $result = $this->db->toggleFilterFavorite($this->postData['id'], (int) $this->postData['favorite'] == 1 ? 0: 1);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function get_autocomplete_watching_tv(){
        if (!$this->isAjax || empty($this->data)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return new Response(json_encode($this->db->getTVChannelNames($this->data['term'])), 200);
    }

    public function get_autocomplete_watching_movie(){
        if (!$this->isAjax || empty($this->data)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return new Response(json_encode($this->db->getMovieNames($this->data['term'])), 200);
    }

    public function get_autocomplete_stbfirmware_version() {
        if (!$this->isAjax || empty($this->data)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $term = $this->data['term'];
        $str_len_offset = ceil((20 - $this->data['term']) / 2);

        $result = array_map(function($row) use ($term, $str_len_offset){
            $pos = strpos($row, $term);
            $begin = $pos !== FALSE ? $pos: 0;
            return substr($row, $begin, strlen($term) + $str_len_offset * 2);
        }, $this->db->getStbFirmwareVersion($this->data['term']));

        return new Response(json_encode($result), 200);
    }

    //------------------------ service method ----------------------------------

    private function getUsersFilters() {
        $return = array();

        if (empty($this->data['filters']) && empty($this->app['filters'])){
            $this->app['filters'] =  array('interval_from'=>'', 'interval_to'=>'');
            return $return;
        }
        $now_timestamp = time() - $this->watchdog;
        $now_time = date("Y-m-d H:i:s", $now_timestamp);
        if (array_key_exists('filters', $this->data) && is_array($this->data['filters'])) {
            if (array_key_exists('status', $this->data['filters']) && is_numeric($this->data['filters']['status']) && $this->data['filters']['status'] != 0 && $this->data['filters']['status'] != 'without') {
                $return['status'] = $this->data['filters']['status'] - 1;
            }
            if (array_key_exists('state', $this->data['filters']) && is_numeric($this->data['filters']['state']) && $this->data['filters']['state'] != 0  && $this->data['filters']['state'] != 'without') {
                $return['keep_alive' . ((int)$this->data['filters']['state'] - 1 ? "<" : ">")] = "$now_time";
            }
            if (array_key_exists('interval_from', $this->data['filters']) && $this->data['filters']['interval_from']!= 0 && $this->data['filters']['interval_from'] != 'without') {
                $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_from']);
                $return['UNIX_TIMESTAMP(last_active)>='] = $date->getTimestamp();
            }
            if (array_key_exists('interval_to', $this->data['filters']) && $this->data['filters']['interval_to']!= 0 && $this->data['filters']['interval_to'] != 'without') {
                $date = \DateTime::createFromFormat('d/m/Y', $this->data['filters']['interval_to']);
                $return['UNIX_TIMESTAMP(last_active)<='] = $date->getTimestamp();
            }

            $this->data['filters']['interval_from'] = (empty($this->data['filters']['interval_from']) || $this->data['filters']['interval_from'] == 'without') ? '' : $this->data['filters']['interval_from'];
            $this->data['filters']['interval_to'] = (empty($this->data['filters']['interval_to']) || $this->data['filters']['interval_to'] == 'without') ? '' : $this->data['filters']['interval_to'];

            if (!empty($this->app['filters'])) {
                $this->app['filters'] = array_merge($this->app['filters'], $this->data['filters']);
            } else {
                $this->app['filters'] = $this->data['filters'];
            }
        }

        return $return;
    }

    private function buildUserForm(&$data = array(), $edit = FALSE) {

        $builder = $this->app['form.factory'];
        $additional_services = array(
            0 => $this->setLocalization('off'),
            1 => $this->setLocalization('on')
        );
        $status = array(
            1 => $this->setLocalization('status off'),
            0 => $this->setLocalization('status on')
        );


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

        if ($this->app['tarifPlanFlag']) {
            $tarif_plans = $this->db->getAllTariffPlans();
            $plan_keys = $this->getFieldFromArray($tarif_plans, 'id');
            $plan_names = $this->getFieldFromArray($tarif_plans, 'name');

            if (is_array($plan_keys) && is_array($plan_names) && count($plan_keys) == count($plan_names) && count($plan_keys) > 0) {
                $tariff_plans = array_combine($plan_keys, $plan_names);
                if (!array_key_exists(0 , $tariff_plans)) {
                    $tariff_plans[0] = '---';
                }
            } else {
                $tariff_plans = array(NULL);
            }
            if (!empty($data) && is_array($data) && array_key_exists('tariff_plan_id', $data) && (int)$data['tariff_plan_id'] == 0) {
                $user_default = array_filter(array_combine($plan_keys, $this->getFieldFromArray($tarif_plans, 'user_default')));
                reset($user_default);
                list($default_id) = each($user_default);
                if (!empty($default_id) ) {
                    settype($default_id, 'int');
                    if (array_key_exists($default_id, $tariff_plans)){
                        $data['tariff_plan_id'] = $default_id;
                        $data['tariff_plan_name'] = $tariff_plans[$default_id];
                    }
                }
            }
        }

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $resellers = array_merge($resellers, $this->db->getAllFromTable('reseller'));
            $resellers = array_combine($this->getFieldFromArray($resellers, 'id'), $this->getFieldFromArray($resellers, 'name'));

            if (empty($data['reseller_id'])) {
                $data['reseller_id'] = '-';
            }
        }

        $all_themes = \Middleware::getThemes();

        $themes = array();

        foreach ($all_themes as $alias => $theme){
            $themes[$alias] = $alias;
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
                ->add('mac', 'text', ($edit ? array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE) : array('required' => FALSE)))
                ->add('status', 'choice', array(
                    'choices' => $status,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($status)))),
                    'required' => FALSE
                        )
                )
                ->add('theme', 'choice', array(
                        'choices' => $themes,
                        'constraints' => array(new Assert\Choice(array('choices' => array_keys($themes)))),
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
                    ->add('account_balance', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE))
                    ->add('video_out', 'text', array('required' => FALSE, 'read_only' => TRUE, 'disabled' => TRUE));
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
                    new Assert\NotBlank()
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
                $id = ($action == 'updateUserById' && !empty($data['id']) ? $data['id'] : $result);

                if (array_key_exists('password', $data) && !empty($id)) {
                    $password = md5(md5($data['password']).$id);
                    $this->db->updateUserById(array('password' => $password), $id);
                }

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
            array('name'=>'created',            'title'=>$this->setLocalization('Created'),     'checked' => FALSE),
            array('name'=>'now_playing_type',   'title'=>$this->setLocalization('Type'),        'checked' => FALSE),
            array('name'=>'now_playing_content','title'=>$this->setLocalization('Media'),       'checked' => FALSE),
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

    private function getUsersFiltersDropdownAttribute() {
        $attribute = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),                'checked' => TRUE),
            array('name' => 'login',        'title' => $this->setLocalization('Author'),            'checked' => TRUE),
            array('name' => 'title',        'title' => $this->setLocalization('Title'),            'checked' => TRUE),
            array('name' => 'filter_set',   'title' => $this->setLocalization('Filter conditions'), 'checked' => TRUE),
            array('name' => 'for_all',      'title' => $this->setLocalization('Visibility'),        'checked' => TRUE),
            array('name' => 'favorites',    'title' => $this->setLocalization('Favorites'),         'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),        'checked' => TRUE)
        );
        return $attribute;
    }

    private function getFilterSetFields(){
        return array(
            'id' => 'F_S.`id` as `id`',
            'login' => 'A.`login` as `login`',
            'title' => 'F_S.title as `title`',
            'filter_set' => 'F_S.filter_set as `filter_set`',
            'for_all' => 'F_S.for_all as `for_all`',
            'favorites' => 'F_S.`favorites` as `favorites`'
        );
    }

    private function getUsersGroupsConsolesListDropdownAttribute() {
        $attribute = array(
            array('name'=>'mac',        'title'=>$this->setLocalization('MAC'),         'checked' => TRUE),
            array('name'=>'uid',        'title'=>$this->setLocalization('uid'),         'checked' => TRUE)
        );
        if (!empty($this->app['reseller'])) {
            $attribute[] = array('name'=>'reseller_name', 'title'=>$this->setLocalization('Reseller'), 'checked' => TRUE);
        }
        $attribute[] = array('name'=>'operations', 'title'=>$this->setLocalization('Operations'), 'checked' => TRUE);
        return $attribute;
    }

    private function getUsersGroupsConsolesListFields(){
        return array(
            'id' => 'Sg.`id` as `id`',
            'name' => 'Sg.name as `name`',
            'users_count' => '(select count(*) from stb_in_group as Si where Si.stb_group_id = Sg.id) as `users_count`',
            'reseller_id' => 'R.id as `reseller_id`',
            'reseller_name' => 'R.name as `reseller_name`',
        );
    }

    private function getUsersConsolesLogsDropdownAttribute() {
        $attribute = array(
            array('name' => 'time',     'title' => $this->setLocalization('Time'),      'checked' => TRUE),
            array('name' => 'mac',      'title' => $this->setLocalization('MAC'),       'checked' => TRUE),
            array('name' => 'action',   'title' => $this->setLocalization('Actions'),   'checked' => TRUE),
            array('name' => 'object',   'title' => $this->setLocalization('Object'),    'checked' => TRUE),
            array('name' => 'type',     'title' => $this->setLocalization('Type'),      'checked' => TRUE),
            array('name' => 'param',    'title' => $this->setLocalization('Parameter'), 'checked' => TRUE)
        );
        return $attribute;
    }

    private function getUsersConsolesReportDropdownAttribute() {
        $attribute = array(
            array('name' => 'rank',             'title' => '#',                             'checked' => TRUE),
            array('name' => 'mac',              'title' => $this->setLocalization('MAC'),   'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('Status'),'checked' => TRUE),
            array('name' => 'last_change_status','title' => $this->setLocalization('Time'), 'checked' => TRUE)
        );
        return $attribute;
    }
}
