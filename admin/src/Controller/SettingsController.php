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
            return $this->app->redirect($this->app['controller_alias'] . '/epg');
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
        $this->app['current_theme'] = array('name'=> $current , 'title'=> ucwords(str_replace('_', ' ', $current)) , 'preview' => $this->theme_path.$current."/preview.png");
        $themes = \Middleware::getThemes();
        $theme_path = $this->theme_path;
        if (is_array($themes)) {
            $themes = array_map(function($val) use ($theme_path) {
                $tmp = array('name'=> $val , 'title'=> ucwords(str_replace('_', ' ', $val)) , 'previews' => array($theme_path.$val."/preview.png"));
                for($i = 1; $i<=2; $i++){
                    $tmp_preview = $theme_path.$val."/preview$i.png";
                    if (is_file($tmp_preview)) {
                       $tmp['previews'][] =  $tmp_preview;
                    }
                }
                return $tmp;
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
        $error = $this->setlocalization('There is no such skin');
        $data['name'] = $data['title'] = $data['preview'] = '';
        $themes = \Middleware::getThemes();
        if (!empty($themes) && in_array($this->postData['themename'], $themes) ) {
            $this->db->setCurrentTheme($this->postData['themename']);
            $error = '';
            
            $event = new \SysEvent();
            $event->setUserListByMac('online');
            $event->sendReboot();
            
            $data['name'] = $this->postData['themename'];
            $data['title']= ucwords(str_replace('_', ' ', $this->postData['themename']));
            $data['preview'] = $this->theme_path.$this->postData['themename']."/preview.png";
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
        
        $error = $this->setlocalization("Error");
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
        $response['data'] = array_map(function($val){
            $val['enable'] = (int)$val['enable'];
            if (strtotime($val['require_image_date']) === FALSE) {
                $val['require_image_date'] = "0000-00-00";
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
        $error = $this->setlocalization('Failed');
        
        if ($result = call_user_func_array(array($this->db, $operation), $item)) {
            $error = '';    
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
            array('name'=>'id',                         'title'=>$this->setlocalization('ID'),                      'checked' => TRUE),
            array('name'=>'stb_type',                   'title'=>$this->setlocalization('STB model'),               'checked' => TRUE),
            array('name'=>'require_image_version',      'title'=>$this->setlocalization('STB API version'),         'checked' => TRUE),
            array('name'=>'require_image_date',         'title'=>$this->setlocalization('Image date'),              'checked' => TRUE),
            array('name'=>'update_type',                'title'=>$this->setlocalization('Update type'),             'checked' => TRUE),
            array('name'=>'prefix',                     'title'=>$this->setlocalization('Prefix'),                  'checked' => TRUE),
            array('name'=>'image_description_contains', 'title'=>$this->setlocalization('Image description'),       'checked' => TRUE),
            array('name'=>'image_version_contains',     'title'=>$this->setlocalization('Required STB API version'),'checked' => TRUE),
            array('name'=>'hardware_version_contains',  'title'=>$this->setlocalization('Hardware version'),        'checked' => TRUE),
            array('name'=>'enable',                     'title'=>$this->setlocalization('Automatic update'),        'checked' => TRUE),
            array('name'=>'operations',                 'title'=>$this->setlocalization('Operations'),              'checked' => TRUE)
        );
    }
}
