<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class RadioController extends \Controller\BaseStalkerController {

    protected $allStatus = array();

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allStatus = array(
            array('id' => 1, 'title' => $this->setLocalization('Unpublished')),
            array('id' => 2, 'title' => $this->setlocalization('Published'))
        );

        $this->app['allMonitoringStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('monitoring off')),
            array('id' => 2, 'title' => $this->setLocalization('errors occurred')),
            array('id' => 3, 'title' => $this->setLocalization('no errors'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

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
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add radio'));
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

        settype($this->radio['enable_monitoring'], 'bool');

        $form = $this->buildRadioForm($this->radio);

        if ($this->saveRadioData($form, TRUE)) {
            return $this->app->redirect('index');
        }
        $this->app['form'] = $form->createView();
        $this->app['radioEdit'] = TRUE;
        $this->app['radioID'] = $id;
        $this->app['radioName'] = $this->radio['name'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit radio'));
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

        if (!empty($query_param['select'])) {
            $query_param['select'][] = 'monitoring_status_updated';
            $query_param['select'][] = 'enable_monitoring';
        }

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getRadioFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $response['recordsTotal'] = $this->db->getTotalRowsRadioList();
        $response["recordsFiltered"] = $this->db->getTotalRowsRadioList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (empty($query_param['order'])) {
            
        }
        
        $response['data'] = $this->db->getRadioList($query_param);

        if (is_array($response['data'])) {
            reset($response['data']);
            while (list($key, $row) = each($response['data'])) {
                if ($monitoring_status = $this->getMonitoringStatus($row)) {
                    $response['data'][$key]['monitoring_status'] = $monitoring_status;
                } else {
                    unset($response['data'][$key]);
                }
            }
        }

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function toggle_radio() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['radioid']) || !isset($this->postData['radiostatus'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'toggleRadioStatus';
        $error = $this->setlocalization('Failed');

        if ($this->db->toggleRadioStatus($this->postData['radioid'], (int) (!$this->postData['radiostatus']))) {
            $error = '';
            $data['title'] = (!$this->postData['radiostatus'] ? $this->setlocalization('Unpublish') : $this->setlocalization('Publish'));
            $data['status'] = (!$this->postData['radiostatus'] ? '<span class="txt-success">' . $this->setlocalization('Published') . '</span>' : '<span class="txt-danger">' . $this->setlocalization('Unpublished') . '</span>');
            $data['radiostatus'] = (int) !$this->postData['radiostatus'];
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_radio() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['radioid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeRadio';
        $this->db->deleteRadioById($this->postData['radioid']);
        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function radio_check_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['param'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkRadioName';
        $error = $this->setlocalization('Name already used');
        
        if ($this->db->searchOneRadioParam(array('name' => trim($this->postData['param']), 'id<>' => trim($this->postData['radioid'])))) {
            $data['chk_rezult'] = $this->setlocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setlocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function radio_check_number() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['param'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkRadioNumber';
        $error = $this->setlocalization('Number is not unique');
        if ($this->db->searchOneRadioParam(array('number' => trim($this->postData['param']), 'id<>' => trim($this->postData['radioid'])))) {
            $data['chk_rezult'] = $this->setlocalization('Number is not unique');
        } else {
            $data['chk_rezult'] = $this->setlocalization('Number is unique');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

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
                ->add('volume_correction', 'choice', array(
                            'choices' => array_combine(range(-20, 20, 1), range(-100, 100, 5)),
                            'constraints' => array(
                                new Assert\Range(array('min' => -20, 'max' => 20)), 
                                new Assert\NotBlank()),
                            'required' => TRUE,
                            'data' => (empty($data['volume_correction']) ? 0: $data['volume_correction'])
                        )
                    )
                ->add('enable_monitoring', 'checkbox', array('required' => FALSE))
                ->add('save', 'submit');
//                ->add('reset', 'reset');
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
            if (($edit && (empty($data['id']) || (!empty($radio_num) && $radio_num != $data['number'] ) || (!empty($radio_name) && $radio_name != $data['name'] ) ) ) || ( !$edit && ( !empty($radio_num) || !empty($radio_name)) ) ){
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

        if (!empty($this->data['filters'])){
            if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id']!= 0) {
                $return['status'] = $this->data['filters']['status_id'] - 1;
            }

            if (array_key_exists('monitoring_status', $this->data['filters']) && $this->data['filters']['monitoring_status'] != 0) {
                if (((int)$this->data['filters']['monitoring_status']) == 1) {
                    $return['enable_monitoring'] = 0;
                } else {
                    $return['enable_monitoring'] = 1;
                    $return['monitoring_status'] = (int) ($this->data['filters']['monitoring_status'] - 2);
                }
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }
    
    private function getDropdownAttribute() {
        return array(
            array('name'=>'id',                 'title'=>$this->setlocalization('ID'),                  'checked' => TRUE),
            array('name'=>'number',             'title'=>$this->setlocalization('Order'),               'checked' => TRUE),
            array('name'=>'name',               'title'=>$this->setlocalization('Title'),               'checked' => TRUE),
            array('name'=>'cmd',                'title'=>$this->setlocalization('URL'),                 'checked' => TRUE),
            array('name'=>'volume_correction',  'title'=>$this->setlocalization('Volume'),              'checked' => TRUE),
            array('name'=>'status',             'title'=>$this->setlocalization('Status'),              'checked' => TRUE),
            array('name'=>'monitoring_status','title' => $this->setLocalization('Monitoring status'),   'checked' => TRUE),
            array('name'=>'operations',         'title'=>$this->setlocalization('Operations'),          'checked' => TRUE),
        );
    }


    private function getMonitoringStatus($row) {
        $return = '';
        if (!$row['enable_monitoring']) {
            $return .= $this->setlocalization('monitoring off');
        } else {
            $diff = time() - strtotime($row['monitoring_status_updated']);
            if ($diff > 3600) {
                $return .= $this->setlocalization('more than an hour ago');
            } else if ($diff < 60) {
                $return .= $this->setlocalization('less than a minute ago');
            } else {
                $return .= $this->setlocalization('{{minute}} minutes ago', '', 0, array('{{minute}}' => round($diff / 60)));
            }
            $return .= '<br><span style="color: ';
            if ($row['monitoring_status'] == 1) {
                $return .= 'green;">' . $this->setLocalization('no errors');
            } else {
                $return .= 'red;">' . $this->setLocalization('errors occurred');
            }
            $return .= '</span>';
        }
        return $return;
    }

}
