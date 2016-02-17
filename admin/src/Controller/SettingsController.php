<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class SettingsController extends \Controller\BaseStalkerController {

    private $theme_path = '../../c/template/';


    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------
    
    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/themes');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        } 
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function themes(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $current = $this->db->getCurrentTheme();
        $this->app['current_theme'] = array('name'=> $current);
        $themes = \Middleware::getThemes();
        if (is_array($themes)) {
            $themes = array_map(function($theme){
                return array(
                    'name'     => $theme['id'],
                    'title'    => $theme['name'],
                    'previews' => array($theme['preview']),
                );
            }, $themes);
        }
        $this->app['allData'] = $themes;
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function common() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $attribute = $this->getCommonDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->common_list_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    //----------------------- ajax method --------------------------------------

    public function set_current_theme(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['themename'])) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageTheme';
        $error = $this->setLocalization('There is no such skin');
        $data['name'] = $data['title'] = $data['preview'] = '';
        $themes = \Middleware::getThemes();
        if (!empty($themes) && array_key_exists($this->postData['themename'], $themes) ) {
            $this->db->setCurrentTheme($this->postData['themename']);
            $error = '';
            
            $event = new \SysEvent();
            $event->setUserListByMac('online');
            $event->sendReboot();

            $theme = $themes[$this->postData['themename']];
            
            $data['name'] = $theme['id'];
            $data['title']= $theme['name'];
            $data['preview'] = $theme['preview'];
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function common_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setCommonModal'
        );
        
        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = "*";
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsCommonList();
        $response["recordsFiltered"] = $this->db->getTotalRowsCommonList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        if (array_key_exists('id', $param)) {
            $query_param['where']['id'] = $param['id'];
        }
        
        if (empty($query_param['order'])) {
            $query_param['order']['id'] = 'asc';
        }
        $commonList = $this->db->getCommonList($query_param);
        $convert = ($this->method == 'GET');
        $response['data'] = array_map(function($val) use($convert){
            $val['enable'] = (int)$val['enable'];
            if ($convert) {
                $val['require_image_date'] = (int) strtotime($val['require_image_date']);
                if ($val['require_image_date'] < 0) {
                    $val['require_image_date'] = 0;
                }
            }
            return $val;
        }, $commonList);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function save_common_item() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'manageCommon';
        $item = array($this->postData);
        if (empty($this->postData['id'])) {
            $operation = 'insertCommon';
        } else {
            $operation = 'updateCommon';
            $item['id'] = $this->postData['id'];
        }

        unset($item[0]['id']);
        $error = $this->setLocalization('Failed');
        
        $result = call_user_func_array(array($this->db, $operation), $item);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_common_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageCommon';
        $data['id'] = $this->postData['id'];        
        $error = '';    
        $this->db->deleteCommon(array('id' => $this->postData['id']));
        
        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_common_item_status() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || !array_key_exists('enable', $this->postData)) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageCommon';
        $data['id'] = $this->postData['id'];
        $this->db->updateCommon(array('enable' => (int)(!((bool) $this->postData['enable']))), $this->postData['id']);
        $error = '';    
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getCommonDropdownAttribute(){
        return array(
            array('name'=>'id',                         'title'=>$this->setLocalization('ID'),                      'checked' => TRUE),
            array('name'=>'stb_type',                   'title'=>$this->setLocalization('STB model'),               'checked' => TRUE),
            array('name'=>'require_image_version',      'title'=>$this->setLocalization('STB API version'),         'checked' => TRUE),
            array('name'=>'require_image_date',         'title'=>$this->setLocalization('Image date'),              'checked' => TRUE),
            array('name'=>'update_type',                'title'=>$this->setLocalization('Update type'),             'checked' => TRUE),
            array('name'=>'prefix',                     'title'=>$this->setLocalization('Prefix'),                  'checked' => TRUE),
            array('name'=>'image_description_contains', 'title'=>$this->setLocalization('Image description'),       'checked' => TRUE),
            array('name'=>'image_version_contains',     'title'=>$this->setLocalization('Required STB API version'),'checked' => TRUE),
            array('name'=>'hardware_version_contains',  'title'=>$this->setLocalization('Hardware version'),        'checked' => TRUE),
            array('name'=>'enable',                     'title'=>$this->setLocalization('Automatic update'),        'checked' => TRUE),
            array('name'=>'operations',                 'title'=>$this->setLocalization('Operations'),              'checked' => TRUE)
        );
    }
}
