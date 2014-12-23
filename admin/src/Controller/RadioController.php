<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class RadioController extends \Controller\BaseStalkerController {

    private $allStatus = array(array('id' => 1, 'title' => 'Выключено'), array('id' => 2, 'title' => 'Включено'));

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->radio_list_json();

        $this->app['allRadio'] = $list['data'];
        $this->app['allStatus'] = $this->allStatus;
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_radio() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $form = $this->buildRadioForm();

        if ($this->saveRadioData($form)) {
            return $this->app->redirect('index');
        }
        $this->app['form'] = $form->createView();
        $this->app['radioEdit'] = FALSE;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function edit_radio() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-radio');
        }
        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );

        $query_param['where']['radio.id'] = $id;
        $query_param['order'] = 'radio.id';
        $radio = $this->db->getRadioList($query_param);
        $this->radio = (is_array($radio) && count($radio) > 0) ? $radio[0] : array();

        $form = $this->buildRadioForm($this->radio);

        if ($this->saveRadioData($form, TRUE)) {
            return $this->app->redirect('index');
        }
        $this->app['form'] = $form->createView();
        $this->app['radioEdit'] = TRUE;
        $this->app['radioID'] = $id;
        
        return $this->app['twig']->render("Radio_add_radio.twig");
    }

    //----------------------- ajax method --------------------------------------

    public function radio_list_json($param = array()) {
        $response = array();
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

        $filter = $this->getRadioFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $response['recordsTotal'] = $this->db->getTotalRowsRadioList();
        $response["recordsFiltered"] = $this->db->getTotalRowsRadioList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getRadioList($query_param);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->gererateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function toggle_radio() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['radioid']) || !isset($this->postData['radiostatus'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'toggleRadioStatus';
        $error = 'Не удалось';

        if ($this->db->toggleRadioStatus($this->postData['radioid'], (int) (!$this->postData['radiostatus']))) {
            $error = '';
            $data['title'] = (!$this->postData['radiostatus'] ? 'Отключить' : 'Включить');
            $data['status'] = (!$this->postData['radiostatus'] ? '<span class="txt-success">Вкл.</span>' : '<span class="txt-danger">Выкл</span>');
            $data['radiostatus'] = (int) !$this->postData['radiostatus'];
        }

        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_radio() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['radioid'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeRadio';
        $this->db->deleteRadioById($this->postData['radioid']);
        $error = '';

        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function radio_check_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['param'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkRadioName';
        $error = 'Имя занято';
        
        if ($this->db->searchOneRadioParam(array('name' => trim($this->postData['param']), 'id<>' => trim($this->postData['radioid'])))) {
            $data['chk_rezult'] = 'Имя занято';
        } else {
            $data['chk_rezult'] = 'Имя свободно';
            $error = '';
        }
        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function radio_check_number() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['param'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkRadioNumber';
        $error = 'Номер не уникален';
        if ($this->db->searchOneRadioParam(array('number' => trim($this->postData['param']), 'id<>' => trim($this->postData['radioid'])))) {
            $data['chk_rezult'] = 'Номер не уникален';
        } else {
            $data['chk_rezult'] = 'Номер уникален';
            $error = '';
        }
        $response = $this->gererateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function buildRadioForm(&$data = array(), $edit = FALSE) {

        $builder = $this->app['form.factory'];
        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('number', 'text', array('constraints' => array(
                        new Assert\NotBlank(),
                        'required' => TRUE
                    ), 'required' => TRUE))
                ->add('name', 'text', array('constraints' => array(
                        new Assert\NotBlank(),
                        'required' => TRUE
                    ), 'required' => TRUE))
                ->add('cmd', 'text', array('constraints' => array(
                        new Assert\NotBlank(),
                        'required' => TRUE
                    ), 'required' => TRUE))
                ->add('volume_correction', 'text', array(
                        'constraints' => array('required' => FALSE),
                        'required' => FALSE
                        )
                )
                ->add('save', 'submit')
                ->add('reset', 'reset');
        return $form->getForm();
    }

    private function saveRadioData(&$form, $edit = FALSE) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            $action = (isset($this->radio) && $edit ? 'updateRadio' : 'insertRadio');
            $radio_num = $this->db->searchOneRadioParam(array('number' => $data['number']));
            $radio_name = $this->db->searchOneRadioParam(array('name' => $data['name']));
            $data['volume_correction'] = !empty($data['volume_correction'])? (int)str_replace('%', '', $data['volume_correction']): 0;
            if ($edit && !empty($data['id']) && (!empty($radio_num) && $radio_num != $data['number']) && (!empty($radio_name) && $radio_name != $data['name'])) {
                return FALSE;
            }
            if ($form->isValid()) {
                $data = array_intersect_key($data, array_flip($this->getFieldFromArray($this->db->getTableFields('radio'), 'Field')));
                $param = array();
                $param[] = $data;
                if ($edit && !empty($data['id'])) {
                    $param[] = $data['id'];    
                    unset($param[0]['id']);
                    if ($radio_num == $data['number']) {
                        unset($param[0]['number']);
                    }
                    if ($radio_name == $data['name']) {
                        unset($param[0]['name']);
                    }
                }
                if (call_user_func_array(array($this->db, $action), $param)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function getRadioFilters() {
        $return = array();

        if (!empty($this->data['filters']) && !empty((int) $this->data['filters']['status_id'])) {
            $return['status'] = $this->data['filters']['status_id'] - 1;
        }

        $this->app['filters'] = !empty($this->data['filters']) ? $this->data['filters'] : array();
        return $return;
    }

}
