<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class AdminsController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/admins-list');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function admins_list(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getAdminsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allAdminGroups'] = $this->db->getAdminGropsList(array('select'=>array('A_G.id as id', "A_G.name as name")));
        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        }
        $all_groups = $this->db->getAllFromTable('admin_groups ');
        if (!empty($all_groups) && is_array($all_groups)) {
            $this->app['allGroups'] = $all_groups;
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function admins_groups(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->app['reseller'])) {
            $resellers = array(array('id' => '-', 'name' => $this->setLocalization('Empty')));
            $this->app['allResellers'] = array_merge($resellers, $this->db->getAllFromTable('reseller'));
        }

        $all_groups = $this->db->getAllFromTable('admin_groups ');
        if (!empty($all_groups) && is_array($all_groups)) {
            $this->app['allGroups'] = $all_groups;
        }

        $attribute = $this->getAdminGroupsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function admins_groups_permissions(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $gid = (!empty($this->data['id']) ? $this->data['id']: FALSE);
        
        if ($gid === FALSE) {
            return $this->app->redirect('admins-groups');
        }
        $permissionMap = $this->db->getAdminGroupPermissions($gid);
        $permissionMap = $this->getJoinedNameArray($permissionMap, 'controller_name', 'action_name');

        $baseMap = $this->db->getAdminGroupPermissions();
        $baseMap = $this->getJoinedNameArray($baseMap, 'controller_name', 'action_name');
        
        $permissionMap = array_map(function($val){
            $val['is_ajax'] = (int) $val['is_ajax'];
            $val['view_access'] = (int) $val['view_access'];
            $val['edit_access'] = (int) $val['edit_access'];
            $val['action_access'] = (int) $val['action_access'];
            return $val;
        },$this->infliction_array($baseMap, $permissionMap));
        
        $group_name = $this->db->getAdminGropsList(array('select'=>'A_G.*','where' => array('A_G.id' => $gid), 'like'=>'','order' => array('name' => 'ASC'), 'limit' => array('limit' => 1, 'offset'=>'')));
        
        $this->app['adminGropName'] = $group_name[0]['name'];
        $this->app['adminGropID'] = $this->data['id'];
        $permissionMap = $this->setLocalization($permissionMap, 'description');
        $this->app['permissionMap'] = $permissionMap;

        $this->app['breadcrumbs']->addItem($this->setLocalization('Groups'), $this->app['controller_alias'] . '/admins-groups');
        $this->app['breadcrumbs']->addItem($this->setLocalization('permissions for group administrators ') . ": '{$group_name[0]['name']}'");

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function resellers_list(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getResellerDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allResellers'] = $this->db->getAllFromTable('reseller');

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------
    
    public function admins_list_json($local_uses = FALSE){
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
        if (!$local_uses) {
            $response['action'] = 'setAdminsModal';
        }

        $filds_for_select = $this->getAdminsFields();
                
        $error = "Error";
        $param = (!empty($this->data)?$this->data: $this->postData);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = "A_G.`id` as `gid`";
            if (empty($this->app['reseller'])) {
                $query_param['select'][] = "R.`id` as `reseller_id`";
            }
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (!empty($param['id'])) {
            $query_param['where']['A.`id`'] = $param['id'];
        }

        $response['recordsTotal'] = $this->db->getAdminsTotalRows();
        $response["recordsFiltered"] = $this->db->getAdminsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response["data"] = array_map(function($row){
            $row['RowOrder'] = "dTRow_" . $row['id'];
            return $row;
        }, $this->db->getAdminsList($query_param));
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax && !$local_uses) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function check_admins_login() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['login'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkData';
        $data['input_id'] = 'adm_login';
        $error = $this->setLocalization('Login is already used');

        if (preg_match('/^[A-Za-z0-9_]+$/i', $this->postData['login'])) {
            if ($this->db->getAdminsList(array('where' => array('login' => $this->postData['login']), 'order' => array('login' => 'ASC')))) {
                $data['chk_rezult'] = $this->setLocalization('Login is already used');
            } else {
                $data['chk_rezult'] = $this->setLocalization('Login is available');
                $error = '';
            }
        } else {
            $error = $data['chk_rezult'] = $this->setLocalization('Used illegal characters');
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function save_admin() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'updateTableData';
        $item = array($this->postData);
        $error = $this->setLocalization('Failed');

        if (!empty($this->postData['login']) && $this->postData['login'] == 'admin') {
            unset($item[0]['login']);
            unset($item[0]['gid']);
            $error = $this->setLocalization('Account "admin" is not editable. You may change only password.');
        }

        if (empty($item[0]['id']) && !empty($item[0]['login']) && !empty($item[0]['gid']) && !empty($item[0]['pass'])) {
            $operation = 'insertAdmin';
        } else {
            $operation = 'updateAdmin';
            $item['id'] = $this->postData['id'];
        }

        $new_pass = FALSE;

        if (empty($item[0]['pass']) || $item[0]['pass'] != $item[0]['re_pass']) {
            unset($item[0]['pass']);
        } else {
            $new_pass = $item[0]['pass'];
            $item[0]['pass'] = md5($item[0]['pass']);
        }

        unset($item[0]['id']);
        unset($item[0]['re_pass']);
        $result = $need_authorization = FALSE;

        if (((!empty($item[0]['login']) && preg_match('/^[A-Za-z0-9_]+$/i', $item[0]['login'])) || $operation != 'insertAdmin') && !empty($item[0])) {
            if (!empty($item[0]['login']) && $item[0]['login'] != $this->admin->getLogin() && !empty($item['id']) && $item['id'] == $this->admin->getId()){
                $data['msg'] = $error = $this->setLocalization('You can not change your own login');
            } elseif (($result = call_user_func_array(array($this->db, $operation), array($item))) && is_numeric($result)) {
                $error = '';
                if ($operation == 'updateAdmin') {
                    if ($new_pass && $item['id'] == $this->admin->getId()) {
                        if (\Admin::checkAuthorization($this->admin->getLogin(), $new_pass)){
                            $data['msg'] = $this->setLocalization('Your password has been changed');
                        } else {
                            $data['msg'] = $error = $this->setLocalization('Need authorization');
                            $need_authorization = TRUE;
                        }
                    }
                    $data = array_merge_recursive($data, $this->admins_list_json(TRUE));
                    $data['id'] = $item['id'];
                    $data['action'] = 'updateTableRow';
                    $data['msg'] = $this->setLocalization('Changed');
                } else {
                    $data['msg'] = $this->setLocalization('Added');
                }
                $this->cleanSideBars();
            } elseif (!empty($this->postData['login']) && $this->postData['login'] == 'admin') {
                $data['msg'] = $error;
            } else {
                $data['nothing_to_do'] = TRUE;
            }
        } else {
            $data['msg'] = $error = $this->setLocalization("Not all required fields are filled");
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : ($need_authorization ? 403: 500)));
    }
    
    public function remove_admin() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteTableRow';
        $data['id'] = $this->postData['id'];        
        $error = '';
        $result = $this->db->deleteAdmin(array('id' => $this->postData['id']));
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }
        $this->cleanSideBars();

        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function admins_groups_list_json($local_uses = FALSE){
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
        if (!$local_uses) {
            $response['action'] = 'setAdminsGroupsModal';
        }

        $filds_for_select = $this->getAdminGroupsFields();
                
        $error = $this->setLocalization("Error");
        $param = (!empty($this->data)?$this->data: $this->postData);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } elseif (empty($this->app['reseller'])) {
            $query_param['select'][] = "R.`id` as `reseller_id`";
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (!empty($param['id'])) {
            $query_param['where']['A_G.`id`'] = $param['id'];
        }

        $response['recordsTotal'] = $this->db->getAdminGropsTotalRows();
        $response["recordsFiltered"] = $this->db->getAdminGropsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (empty($this->app['reseller'])) {
            $empty_reseller_name = $this->setLocalization('Empty');
            $response["data"] = array_map(function ($row) use ($empty_reseller_name) {
                if (empty($row['reseller_name'])) {
                    $row['reseller_name'] = $empty_reseller_name;
                }
                if (empty($row['reseller_id'])) {
                    $row['reseller_id'] = '-';
                }
                $row['RowOrder'] = "dTRow_" . $row['id'];
                return $row;
            }, $this->db->getAdminGropsList($query_param));
        } else {
            $response["data"] = $this->db->getAdminGropsList($query_param);
        }
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax && !$local_uses) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function check_admins_group_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkData';
        $data['input_id'] = 'adm_name';
        $error = $this->setLocalization('Group name is already used');
        if ($this->db->getAdminGropsList(array('select'=>array('A_G.*'), 'where' => array('A_G.name' => $this->postData['name']), 'order' => array('A_G.name' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Group name is already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Group name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function save_admins_group() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'updateTableData';
        $item = array($this->postData);

        $error = $this->setLocalization('error');
        if (empty($this->postData['id'])) {
            $operation = 'insertAdminsGroup';
        } else {
            $operation = 'updateAdminsGroup';
            $item['id'] = $this->postData['id'];
        }

        unset($item[0]['id']);
        $result = call_user_func_array(array($this->db, $operation), array($item));
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
            if ($operation == 'updateAdminsGroup') {
                $data = array_merge_recursive($data, $this->admins_groups_list_json(TRUE));
                $data['id'] = $item['id'];
                $data['action'] = 'updateTableRow';
                $data['msg'] = $this->setLocalization('Changed');
            } else {
                $data['msg'] = $this->setLocalization('Added');
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_admins_group() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteTableRow';
        $data['id'] = $this->postData['id'];        
        $error = $this->setLocalization('Error');
        $admin_count = $this->db->getAdminsTotalRows(array('gid' => $data['id']));
        if (empty($admin_count)) {
            $this->db->deleteAdminsGroup(array('id' => $this->postData['id']));
            $this->db->deleteAdminGroupPermissions($this->postData['id']);
            $error = '';
        } else {
            $error = $data['msg'] = $this->setLocalization('{admin_count} administrators to be moved to another group before deleting', '', FALSE, array('{admin_count}' => $admin_count));
        }

        $response = $this->generateAjaxResponse($data);
        
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function save_admins_group_permissions(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['data'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $postData = json_decode($this->postData['data'], TRUE);

        $data = array();
        $data['action'] = 'managePermissions';
        $data['msg'] = $this->setLocalization('Failed');
        $error = 'Ошибка';
        
        $write_data = array();
        $adminGropID = $postData['adminGropID'];
        unset($postData['adminGropID']);
        
        $baseMap = $this->db->getAdminGroupPermissions();
        $baseMap = $this->getJoinedNameArray($baseMap, 'controller_name', 'action_name');
        $baseMap = array_map(function($val){
            unset($val['id']);
            return $val;
        }, $baseMap);

        foreach ($postData as $controller => $row) {
            foreach ($row as $action => $permissions) {
                $baseKey = (empty($action) || $action == 'index')? $controller: $controller . '-' . $action;
                $baseMap[$baseKey]['view_access'] = $permissions['view_access'];
                $baseMap[$baseKey]['edit_access'] = $permissions['edit_access'];
                $baseMap[$baseKey]['action_access'] = $permissions['action_access'];
                $baseMap[$baseKey]['group_id'] = $adminGropID;
                $write_data[]= $baseMap[$baseKey];
            }
        };

        $this->db->deleteAdminGroupPermissions($adminGropID);
        if ($this->db->setAdminGroupPermissions($write_data)){
            $error = ''; 
            $data['msg'] = $this->setLocalization('Saved');
            $this->cleanSideBars();
        }
        
        $response = $this->generateAjaxResponse($data, $error);
        
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function resellers_list_json($local_uses = FALSE){
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
        if (!$local_uses) {
            $response['action'] = 'setResellerModal';
        }


        $filds_for_select = $this->getResellerFields();

        $error = "Error";
        $param = (!empty($this->data)?$this->data: $this->postData);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = "(select count(*) from administrators as A where A.reseller_id = R.id) as admins_count";
            $query_param['select'][] ="(select count(*) from users as U where U.reseller_id = R.id) as users_count";
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        if (!empty($param['id'])) {
            $query_param['where']['R.`id`'] = $param['id'];
        }

        if (!empty($query_param['like'][$filds_for_select['admins_count']])) {
            unset($query_param['like'][$filds_for_select['admins_count']]);
        }
        if (!empty($query_param['like'][$filds_for_select['users_count']])) {
            unset($query_param['like'][$filds_for_select['users_count']]);
        }

        if (!empty($param['id'])) {
            $query_param['where']['id'] = $param['id'];
        }

        $response['recordsTotal'] = $this->db->getResellersTotalRows();
        $response["recordsFiltered"] = $this->db->getResellersTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (($search = array_search('users_count', $query_param['select'])) !== FALSE) {
            unset($query_param['select'][$search]);
        }
        if (($search = array_search('admins_count', $query_param['select'])) !== FALSE) {
            unset($query_param['select'][$search]);
        }

        if (empty($param['id']) && empty($query_param['like'])) {
            $response["data"][] = array(
                "id" => "-",
                "name" => $this->setLocalization("Empty"),
                "created" => 0,
                "modified" => 0,
                "admins_count" => $this->db->getResellerMember('administrators', NULL),
                "users_count" => $this->db->getResellerMember('users', NULL),
                "max_users" => "&#8734;"
            );
        }

        if (!empty($query_param['order'][$filds_for_select['admins_count']])) {
            $tmp = $query_param['order'][$filds_for_select['admins_count']];
            $query_param['order'] = array('admins_count' => $tmp);
        }
        if (!empty($query_param['order'][$filds_for_select['users_count']])) {
            $tmp = $query_param['order'][$filds_for_select['users_count']];
            $query_param['order'] = array('users_count' => $tmp);
        }

        $response["data"] = array_merge($response["data"], $this->db->getResellersList($query_param));

        $response["data"] = array_map(function($row){
            $row['created'] = (int)strtotime($row['created']);
            $row['created'] = $row['created'] < 0 ? 0 : $row['created'];
            $row['modified'] = (int)strtotime($row['modified']);
            $row['modified'] = $row['created'] < 0 ? 0 : $row['modified'];
            $row['RowOrder'] = "dTRow_" . $row['id'];
            return $row;
        }, $response["data"]);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax && !$local_uses) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function resellers_save(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $item = array($this->postData);

        $error = $this->setLocalization('Failed');
        if (empty($this->postData['id'])) {
            $operation = 'insertReseller';
        } else {
            $operation = 'updateReseller';
            $item['id'] = $this->postData['id'];
        }

        unset($item[0]['id']);

        $result = call_user_func_array(array($this->db, $operation), array($item));
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
            if ($operation == 'updateReseller') {
                $data = array_merge_recursive($data, $this->resellers_list_json(TRUE));
                $data['id'] = $item['id'];
                $data['action'] = 'updateTableRow';
                $data['msg'] = $this->setLocalization('Changed');
            } else {
                $data['msg'] = $this->setLocalization('Added');
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function resellers_delete(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteTableRow';
        $data['id'] = $this->postData['id'];
        $error = '';

        $count_members = $this->db->getResellerMember('administrators', $this->postData['id']) + $this->db->getResellerMember('users', $this->postData['id']);

        if (empty($count_members)) {
            $this->db->deleteReseller(array('id' => $this->postData['id']));
            $data['msg'] = $this->setLocalization('Deleted');
        } else {
            $error = $data['msg'] = $this->setLocalization('Found members of this reseller. Deleting not possible.');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_users_to_reseller(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        $count_members = $this->db->getResellerMember('users', $source_id);

        if (!empty($count_members) && $source_id != $target_id) {
            $this->db->updateResellerMember('users', $source_id, $target_id);
            $data['msg'] = $this->setLocalization('Moved');
        } else {
            $error = $data['msg'] = $this->setLocalization('Not found members for moving. Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_admin_to_reseller(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableRow';
        $data['id'] = $admin_id = $this->postData['id'];
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_reseller = $this->db->getResellersList(array('select'=>array('*'), 'where'=>array('id' => $target_id), 'like' => array(), 'order' => array()), TRUE);
        } else{
            $count_reseller = 1;
        }

        if (!empty($count_reseller) && $source_id !== $target_id) {
            $this->db->updateResellerMemberByID('administrators', $admin_id, $target_id);
            $data['msg'] = $this->setLocalization('Moved');
            $data = array_merge_recursive($data, $this->admins_list_json(TRUE));
            $this->cleanSideBars();
        } else {
            $error = $data['msg'] = empty($count_reseller) ? $this->setLocalization('Not found reseller for moving') : $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_admin_group_to_reseller(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $admin_id = $this->postData['id'];
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_reseller = $this->db->getResellersList(array('select'=>array('*'), 'where'=>array('id' => $target_id), 'like' => array(), 'order' => array()), TRUE);
        } else{
            $count_reseller = 1;
        }

        if (!empty($count_reseller) && $source_id !== $target_id) {
            $this->db->updateResellerMemberByID('admin_groups', $admin_id, $target_id);
            $data = array_merge_recursive($data, $this->admins_groups_list_json(TRUE));
            $data['msg'] = $this->setLocalization('Moved');
            $data['id'] = $admin_id;
            $data['action'] = 'updateTableRow';
            $this->cleanSideBars();
        } else {
            $error = $data['msg'] = empty($count_reseller) ? $this->setLocalization('Not found reseller for moving') : $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_admin_to_group(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableRow';
        $data['id'] = $admin_id = $this->postData['id'];
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_admins = $this->db->getAdminGropsTotalRows(array('A_G.id' => $target_id));
        } else{
            $count_admins = 1;
        }

        if (!empty($count_admins) && $source_id !== $target_id) {
            $result = $this->db->updateAdmin(array('id' => $admin_id, 0 => array('gid' => $target_id)));
            if (is_numeric($result)) {
                $error = '';
                $data['msg'] = $this->setLocalization('Moved');
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
                $data = array_merge_recursive($data, $this->admins_list_json(TRUE));
                $this->cleanSideBars();
            }
        } else {
            if (empty($count_admins)){
                $error = $data['msg'] = $this->setLocalization('Not found admin-group for moving');
            } else {
                $error = $data['msg'] = $this->setLocalization('Nothing to do');
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_all_admin_to_group(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['source_id']) || empty($this->postData['target_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $source_id = $this->postData['source_id'] !== '-' ? $this->postData['source_id']: NULL;
        $target_id = $this->postData['target_id'] !== '-' ? $this->postData['target_id']: NULL;
        $error = '';

        if (!empty($target_id)) {
            $count_admins = $this->db->getAdminGropsTotalRows(array('A_G.id' => $target_id));
        } else{
            $count_admins = 1;
        }

        if (!empty($count_admins) && $source_id !== $target_id) {
            $result = $this->db->updateAdmin(array('gid' => $source_id, 0 => array('gid' => $target_id)));
            if (is_numeric($result)) {
                $error = '';
                $data['msg'] = $this->setLocalization('Moved');
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
                $this->cleanSideBars();
            }
        } else {
            if (empty($count_admins)){
                $error = $data['msg'] = $this->setLocalization('Not found admin-group for moving');
            } else {
                $error = $data['msg'] = $this->setLocalization('Nothing to do');
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------  move-all-admin-to-group
    
    private function getAdminsDropdownAttribute() {
        $return = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),        'checked' => TRUE),
            array('name' => 'login',        'title' => $this->setLocalization('Login'),     'checked' => TRUE),
            array('name' => 'group_name',   'title' => $this->setLocalization('Group'),     'checked' => TRUE)
        );
        if (empty($this->app['reseller'])) {
            $return[] = array('name' => 'reseller_name','title' => $this->setLocalization('Reseller'),  'checked' => TRUE);
        }
        $return[] = array('name' => 'operations',   'title' => $this->setLocalization('Operations'),'checked' => TRUE);
        return $return;
    }
    
    private function getAdminGroupsDropdownAttribute() {
        $return = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'admin_count',  'title' => $this->setLocalization('Admins in group'),'checked' => TRUE)
        );
        if (empty($this->app['reseller'])) {
            $return[] = array('name' => 'reseller_name','title' => $this->setLocalization('Reseller'),  'checked' => TRUE);
        }
        $return[] = array('name' => 'operations',   'title' => $this->setLocalization('Operations'),'checked' => TRUE);
        return $return;
    }
    
    private function getAdminsFields(){
        $return = array(
            "id" => "A.`id` as `id`",
            "login" => "A.`login` as `login`",
            "group_name" => "A_G.`name` as `group_name`",
            "gid" => "A_G.`id` as `gid`"
        );
        if (empty($this->app['reseller'])) {
            $return['reseller_id'] = 'R.`id` as `reseller_id`';
            $return['reseller_name'] = 'R.`name` as `reseller_name`';
        }
        return $return;
    }
    
    private function getAdminGroupsFields(){
        $return = array(
            'id' => 'A_G.`id` as `id`', 
            'name' => 'A_G.`name` as `name`',
            'admin_count' => 'COUNT(A.id) as `admin_count`',
        );
        if (empty($this->app['reseller'])) {
            $return['reseller_id'] = 'R.`id` as `reseller_id`';
            $return['reseller_name'] = 'R.`name` as `reseller_name`';
        }
        return $return;
    }
    
    private function getJoinedNameArray($input= array(), $field1 = '', $field2 = '' ) {
        $output = array();
        foreach ($input as $row) {
            if (array_key_exists($field1, $row) && array_key_exists($field2, $row)) {
                $new_key = trim($row[$field1].'-'.$row[$field2], '-');
                $output[$new_key] = $row;
            }
        }
        return $output;
    }

    private function getResellerDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),                    'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Name'),                  'checked' => TRUE),
            array('name' => 'created',      'title' => $this->setLocalization('Created'),               'checked' => TRUE),
            array('name' => 'modified',     'title' => $this->setLocalization('Modified'),              'checked' => TRUE),
            array('name' => 'admins_count', 'title' => $this->setLocalization('Admins of reseller'),    'checked' => TRUE),
            array('name' => 'users_count',  'title' => $this->setLocalization('Users of reseller'),     'checked' => TRUE),
            array('name' => 'max_users',    'title' => $this->setLocalization('Maximum number of users'),'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),        'checked' => TRUE)
        );
    }

    private function getResellerFields(){
        return array(
            "id" => "R.`id` as `id`",
            "name" => "R.`name` as `name`",
            "created" => "CAST(R.`created` as CHAR) as `created`",
            "modified" => "CAST(R.`modified` as CHAR) as `modified`",
            "admins_count" => "(select count(*) from administrators as A where A.reseller_id = R.id) as admins_count",
            "users_count" => "(select count(*) from users as U where U.reseller_id = R.id) as users_count",
            "max_users" => "R.`max_users` as `max_users`"
        );
    }

    private function cleanSideBars(){
        foreach(glob($this->baseDir . '/resources/cache/sidebar/*bar') as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
    }
}