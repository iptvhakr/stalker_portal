<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class InfoportalController extends \Controller\BaseStalkerController {

    protected $allServices = array();
    
    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allServices = array(
            array('id' => 'main', 'title' => $this->setlocalization('Emergency services')),
            array('id' => 'help', 'title' => $this->setlocalization('Reference services')),
            array('id' => 'other', 'title' => $this->setlocalization('Other services'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/phone-book');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function phone_book() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->app['allServices'] = $this->allServices;
        
        
        $list = $this->phone_book_list_json();
        
        $this->app['allPhone'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        $attribute = $this->getPhoneBoockDropdownAttribute();
        
        if (empty($this->data['filters']['service'])) {
            if (empty($this->data['filters'])) {
                $this->data['filters'] = array('service' => 'main');
            } else {
                $this->data['filters']['service'] = 'main';
            }
            $this->checkDropdownAttribute($attribute);
        } else {
            $this->checkDropdownAttribute($attribute, "-filters-{$this->data['filters']['service']}");
        }
        $this->app['filters'] = $this->data['filters'];
        
        $this->app['dropdownAttribute'] = $attribute;
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function humor() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $attribute = $this->getHumorDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->humor_list_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function phone_book_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setPhoneBookModal'
        );
        
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $like_filter = array();
        
        $filters = $this->getInfoportalFilters($like_filter);
        
        $table_prefix = (!empty($filters['service']) ? $filters['service']: 'main');
        $table_prefix = (!empty($this->postData['phoneboocksource']) ? $this->postData['phoneboocksource']: $table_prefix);
        
        unset($filters['service']);
        
        if (empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = $like_filter;
        } elseif (!empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = array_merge($query_param['like'], $like_filter);
        }
        
        $query_param['where'] = array_merge($query_param['where'], $filters);

        
        if (empty($query_param['select'])) {
            $query_param['select'] = "*";
        } else {
            $query_param['select'][] = 'id';
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsPhoneBoockList($table_prefix);
        $response["recordsFiltered"] = $this->db->getTotalRowsPhoneBoockList($table_prefix, $query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        if (array_key_exists('id', $param)) {
            $query_param['where']['id'] = $param['id'];
        }
        
        $response['data'] = $this->db->getPhoneBoockList($table_prefix, $query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function humor_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setHumorModal'
        );
        
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = "*";
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsHumorList();
        $response["recordsFiltered"] = $this->db->getTotalRowsHumorList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        if (array_key_exists('id', $param)) {
            $query_param['where']['id'] = $param['id'];
        }
        
        $response['data'] = $this->db->getHumorList($query_param);
        $response['data'] = array_map(function($row){
            $row['added'] = (int) strtotime($row['added']);
            return $row;
        }, $response['data']);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
            
    public function save_phone_book_item() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'managePhoneBook';
        $item = array($this->postData);

        $error = 'error';
        if (count($item) != 0 && !empty($item[0]['num']) && ((int)$item[0]['num']) > 0) {
            if (empty($this->postData['id'])) {
                $operation = 'insertPhoneBoock';
            } else {
                $operation = 'updatePhoneBoock';
                $item['id'] = $this->postData['id'];
            }
            unset($item[0]['id']);
            unset($item[0]['phoneboocksource']);

            if ($result = call_user_func_array(array($this->db, $operation), array($this->postData['phoneboocksource'], $item))) {
                $error = '';
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_phone_book_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'managePhoneBook';
        $data['id'] = $this->postData['id'];        
        $error = '';    
        $this->db->deletePhoneBoock($this->postData['phoneboocksource'], array('id' => $this->postData['id']));
        
        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function save_humor_item() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'manageHumor';
        $item = array($this->postData);

        $error = 'error';
        if (empty($this->postData['id'])) {
            $operation = 'insertHumor';
            $item[0]['added'] = "NOW()";
        } else {
            $operation = 'updateHumor';
            $item['id'] = $this->postData['id'];
        }
        unset($item[0]['id']);

        if ($result = call_user_func_array(array($this->db, $operation), $item)) {
            $error = '';    
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_humor_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageHumor';
        $data['id'] = $this->postData['id'];        
        $error = '';    
        $this->db->deleteHumor(array('id' => $this->postData['id']));
        
        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getInfoportalFilters(&$like_filter) {
        $return = array();

        if (!empty($this->data['filters'])) {
            if (array_key_exists('service', $this->data['filters']) && !empty($this->data['filters']['service'])) {
                $return['service'] = $this->data['filters']['service'];
            } else {
                $return['service'] = 'main';
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }

    private function getPhoneBoockDropdownAttribute() {
        return array(
            array('name'=>'num',        'title'=>$this->setlocalization('Order'),       'checked' => TRUE),
            array('name'=>'title',      'title'=>$this->setlocalization('Title'),       'checked' => TRUE),
            array('name'=>'number',     'title'=>$this->setlocalization('Phone number'),'checked' => TRUE),
            array('name'=>'operations', 'title'=>$this->setlocalization('Operations'),  'checked' => TRUE)
        );
    }
    
    private function getHumorDropdownAttribute() {
        return array(
            array('name'=>'id',         'title'=>$this->setlocalization('Order'),       'checked' => TRUE),
            array('name'=>'added',      'title'=>$this->setlocalization('Date'),        'checked' => TRUE),
            array('name'=>'anec_body',  'title'=>$this->setlocalization('Text'),        'checked' => TRUE),
            array('name'=>'operations', 'title'=>$this->setlocalization('Operations'),  'checked' => TRUE)
        );
    }
    
}
