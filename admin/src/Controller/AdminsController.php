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

        $list = $this->admins_list_json();
        
        $this->app['allAdmins'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $this->app['allAdminGroups'] = $this->db->getAllFromTable('admin_groups');

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function admins_groups(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $list = $this->admins_groups_list_json();
        
        $this->app['allGroups'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
       
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
        
        $group_name = $this->db->getAdminGropsList(array('select'=>'*','where' => array('id' => $gid), 'like'=>'','order' => array('name' => 'ASC'), 'limit' => array('limit' => 1, 'offset'=>'')));
        
        $this->app['adminGropName'] = $group_name[0]['name'];
        $this->app['adminGropID'] = $this->data['id'];
        $permissionMap = $this->setlocalization($permissionMap, 'description');
        $this->app['permissionMap'] = $permissionMap;
        
        $this->app['breadcrumbs']->addItem($this->setlocalization('Permissions for group administrators') . ": '{$group_name[0]['name']}'");

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    //----------------------- ajax method --------------------------------------
    
    public function admins_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setAdminsModal'
        );

               
        $filds_for_select = $this->getAdminsFields();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = "A_G.`id` as `gid`";
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (!empty($param['id'])) {
            $query_param['where']['A.`id`'] = $param['id'];
        }

        $response['recordsTotal'] = $this->db->getAdminsTotalRows();
        $response["recordsFiltered"] = $this->db->getAdminsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getAdminsList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
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
        $data['action'] = 'checkAdminsLogin';
        $error = $this->setLocalization('Login is already used');
        if ($this->db->getAdminsList(array('where' => array('login' => $this->postData['login']), 'order' => array('login' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Login is already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Login is available');
            $error = '';
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
        $data['action'] = 'manageAdminsList';
        $item = array($this->postData);

        $error = 'error';
        if (empty($this->postData['id'])) {
            $operation = 'insertAdmin';
        } else {
            $operation = 'updateAdmin';
            $item['id'] = $this->postData['id'];
        }
        if (empty($item[0]['pass']) || $item[0]['pass'] != $item[0]['re_pass']) {
            unset($item[0]['pass']);
        } else {
            $item[0]['pass'] = md5($item[0]['pass']);
        }
        unset($item[0]['id']);
        unset($item[0]['re_pass']);

        if ($result = call_user_func_array(array($this->db, $operation), array($item))) {
            $error = '';    
        }
        
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_admin() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageAdminsList';
        $data['id'] = $this->postData['id'];        
        $error = '';    
        $this->db->deleteAdmin(array('id' => $this->postData['id']));
        
        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function admins_groups_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setAdminsGroupsModal'
        );
        
        $filds_for_select = $this->getAdminGroupsFields();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
//            $query_param['select'][] = "A_G.`id` as `id`";
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (!empty($param['id'])) {
            $query_param['where']['A_G.`id`'] = $param['id'];
        }

        $response['recordsTotal'] = $this->db->getAdminGropsTotalRows();
        $response["recordsFiltered"] = $this->db->getAdminGropsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getAdminGropsList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
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
        $data['action'] = 'checkAdminGroupsName';
        $error = $this->setLocalization('Group name is already used');
        if ($this->db->getAdminGropsList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')))) {
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
        $data['action'] = 'manageAdminGroupsList';
        $item = array($this->postData);

        $error = 'error';
        if (empty($this->postData['id'])) {
            $operation = 'insertAdminsGroup';
        } else {
            $operation = 'updateAdminsGroup';
            $item['id'] = $this->postData['id'];
        }

        unset($item[0]['id']);

        if ($result = call_user_func_array(array($this->db, $operation), array($item))) {
            $error = '';    
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
        $data['action'] = 'manageAdminGroupsList';
        $data['id'] = $this->postData['id'];        
        $error = '';    
        $this->db->deleteAdminsGroup(array('id' => $this->postData['id']));
        $this->db->deleteAdminGroupPermissions($this->postData['id']);
        
        $response = $this->generateAjaxResponse($data);
        
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function save_admins_group_permissions(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'managePermissions';
        $data['msg'] = $this->setlocalization('Failed');
        $error = 'Ошибка';
        
        $write_data = array();
        $adminGropID = $this->postData['adminGropID'];
        unset($this->postData['adminGropID']);
        
        $baseMap = $this->db->getAdminGroupPermissions();
        $baseMap = $this->getJoinedNameArray($baseMap, 'controller_name', 'action_name');
        $baseMap = array_map(function($val){
            unset($val['id']);
            return $val;
        }, $baseMap);
        
        
        foreach ($this->postData as $controller => $row) {
            $index = $row['index'];
            unset($row['index']);
            $index['view_access'] = (int)((bool)array_sum($this->getFieldFromArray($row, 'view_access')));
            $index['edit_access'] = (int)((bool)array_sum($this->getFieldFromArray($row, 'view_access')));
            $index['action_access'] = (int)((bool)array_sum($this->getFieldFromArray($row, 'view_access')));
            $row['index'] = $index;
            
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
            $data['msg'] = $error = $this->setlocalization('Saved');
        }
        
        $response = $this->generateAjaxResponse($data);
        
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------
    
    private function getAdminsDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setlocalization('ID'),        'checked' => TRUE),
            array('name' => 'login',        'title' => $this->setlocalization('Login'),     'checked' => TRUE),
            array('name' => 'group_name',   'title' => $this->setlocalization('Group'),    'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setlocalization('Operations'),  'checked' => TRUE)
        );
    }
    
    private function getAdminGroupsDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setlocalization('ID'),       'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setlocalization('Title'),    'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setlocalization('Operations'),'checked' => TRUE)
        );
    }
    
    private function getAdminsFields(){
        return array(
            "id" => "A.`id` as `id`",
            "login" => "A.`login` as `login`",
            "group_name" => "A_G.`name` as `group_name`",
            "gid" => "A_G.`id` as `gid`"
        );
    }
    
    private function getAdminGroupsFields(){
        return array(
            'id' => 'A_G.`id` as `id`', 
            'name' => 'A_G.`name` as `name`'
        );
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
}