<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ExternalAdvertisingController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);

    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/company-list');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function company_list() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $tos = $this->db->getTOS('external_ad');
        if (empty($tos) || empty($tos[0]['accepted'])) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/register');
        }

        if (empty($this->data['filters'])) {
            $this->data['filters'] = array();
        }

        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function register(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && array_key_exists('form', $this->postData)) {
            $data = $this->postData['form'];
        } else {
            $data = array();
        }

        $data['accept_terms_save'] = !empty($data['accept_terms_save']) && ($data['accept_terms_save'] == 'on' || (int)$data['accept_terms_save'] == 1);
        $data['accept_terms_skip'] = !empty($data['accept_terms_skip']) && ($data['accept_terms_skip'] == 'on' || (int)$data['accept_terms_skip'] == 1);

        $form = $this->buildRegisterForm($data);

        if ($this->saveRegisterData($form)) {
            if (!empty($data['submit_type'])) {
                if ($data['submit_type'] == 'skip') {
                    return $this->app->redirect($this->workURL . '/' . $this->app['controller_alias'] . '/settings');
                } else if ($data['submit_type'] == 'save') {
                    try {
                        \Stalker\Lib\Core\Advertising::registration($data['name'], $data['email'], $data['phone'], $data['region']);
                    } catch (\Exception $e) {

                    }
                }
                $this->app['breadcrumbs']->addItem($this->setLocalization('Congratulations!'));
                return $this->app['twig']->render('ExternalAdvertising_register_confirm.twig');
            }
        }

        $this->app['form'] = $form->createView();
        $this->app['breadcrumbs']->addItem($this->setLocalization('Register'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function company_add() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $form = $this->buildCompanyForm();

        if ($this->saveCompanyData($form)){
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/company-list');
        }

        $this->app['form'] = $form->createView();

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of campaigns'), $this->app['controller_alias'] . '/company-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Campaign add'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function company_edit() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $data = $this->postData['form'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $data = $this->company_list_json(TRUE);
            $data = !empty($data['data']) ? $data['data'][0]:array();
        }

        if (empty($data)) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/company-add');
        }

        $data[$data['platform']] = array();
        $is_positions = $this->db->getAdPositions($data['id']);
        if (!empty($is_positions)) {
            $data[$data['platform']] = array_combine(array_values($is_positions), array_fill(0, count($is_positions), TRUE));
        }

        $form = $this->buildCompanyForm($data);

        if ($this->saveCompanyData($form)){
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/company-list');
        }

        $this->app['form'] = $form->createView();

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of campaigns'), $this->app['controller_alias'] . '/company-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Campaign edit'));

        return $this->app['twig']->render("ExternalAdvertising_company_add.twig");
    }

    public function settings(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $tos = $this->db->getTOS('external_ad');
        if (empty($tos) || empty($tos[0]['accepted'])) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/register');
        }

        if ($this->method == 'POST' && array_key_exists('form', $this->postData)) {
            $data = $this->postData['form'];
        } else {
            $data = $this->db->getSourceList(array(
                'select' => array('E_A_S.id', 'E_A_S.source'),
            ));
            if (!empty($data)) {
                $sources = $this->getFieldFromArray($data, 'source');
                $ids = $this->getFieldFromArray($data, 'id');
                $data = array(
                    'source' => !empty($sources) ? array_combine($ids, $sources) : array(''),
                    'new_source' => array('')
                );
            } else {
                $data = array('new_source' => array(''));
            }
        }

        $form = $this->buildSettingsForm($data);

        if($this->saveSettingsData($form)) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/settings');
        }
        $this->app['form'] = $form->createView();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function company_list_json($local_use = FALSE){
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

        $filds_for_select = $this->getCompanyFields();
        $error = $this->setLocalization('Error');
        $param = (!empty($this->data)?$this->data: $this->postData);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        if (!empty($param['id'])) {
            $query_param['where']['E_A_C.`id`'] = $param['id'];
        }

        if (!empty($this->app['reseller'])) {
            $query_param['joined'] = $this->getJoinedCompanyTables();
        }

        $response['recordsTotal'] = $this->db->getCompanyRowsList($query_param, 'ALL');
        $response["recordsFiltered"] = $this->db->getCompanyRowsList($query_param);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = array_map(function($row){
            $row['RowOrder'] = "dTRow_" . $row['id'];
            settype($row['status'], 'int');
            return $row;
        },$this->db->getCompanyList($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax && !$local_use) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function toggle_company_state(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableRow';
        $data['id'] = $this->postData['id'];
        $data['data'] = array();
        $error = $this->setLocalization('Failed');

        $result = $this->db->updateCompanyData(array('status' => empty($this->postData['status'])), $this->postData['id']);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
            $data = array_merge_recursive($data, $this->company_list_json(TRUE));
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function delete_company(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteTableRow';
        $data['id'] = $this->postData['id'];
        $error = $this->setLocalization('Failed');

        $result = $this->db->deleteCompanyData($this->postData['id']);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function buildRegisterForm(&$data = array()) {

        $builder = $this->app['form.factory'];

        $regions = array(
            'first_region' => 'First region',
            'second_region' => 'Second region',
            'third_region' => 'Third region',
            'fourth_region' => 'Fourth region',
            'fifth_region' => 'Fifth region',
        );

        $form = $builder->createBuilder('form', $data)
            ->add('submit_type', 'hidden')
            ->add('name', 'text')
            ->add('phone', 'text')
            ->add('email', 'text')
            ->add('region', 'choice', array(
                    'choices' => $regions,
                    'data' => (empty($data['region']) ? '': $data['region']),
                )
            )
            ->add('accept_terms_save', 'checkbox', array('required'  => FALSE))
            ->add('accept_terms_skip', 'checkbox', array('required'  => FALSE))
            ->add('save', 'submit')
            ->add('skip', 'submit');
        return $form->getForm();
    }

    private function saveRegisterData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if (($data['accept_terms_save'] || $data['accept_terms_skip']) && $form->isValid()) {
                $result = call_user_func_array(array($this->db, 'setAcceptedTOS'), array('external_ad'));
                if (is_numeric($result)) {
                    return TRUE;
                }
            } elseif (!($data['accept_terms_save'] || $data['accept_terms_skip'])) {
                $form->get('accept_terms_' . $data['submit_type'])->addError(new FormError($this->setLocalization('You need accept Terms of Service.')));
            }
        }
        return FALSE;
    }

    private function buildSettingsForm(&$data = array(), $show = FALSE){

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];

        $form = $builder->createBuilder('form', $data);
        if (!empty($data['source'])) {
            $form->add('source', 'collection', array(
                'entry_type'   => 'text',
                'entry_options'  => array(
                    'attr'      => array('class' => 'form-control', 'data-validation' => 'required')
                )
            ));
        }
        $form->add('new_source', 'collection', array(
            'entry_type'   => 'text',
            'entry_options'  => array(
                'attr' => array('class' => 'form-control')
            ),
            'required' => FALSE,
            'allow_add' => TRUE
        ))
            ->add('save', 'submit');
        return $form->getForm();
    }

    private function saveSettingsData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                $curr_fields = $this->db->getTableFields('ext_adv_sources');
                $curr_fields = $this->getFieldFromArray($curr_fields, 'Field');
                $curr_fields = array_flip($curr_fields);

                $old_sources = !empty($data['source']) ? $data['source']: array();
                $new_sources = !empty($data['new_source']) ? $data['new_source']: array();

                $data = array_intersect_key($data, $curr_fields);
                $data['updated'] = 'NOW()';

                $result = 0;
                $params = array(
                    'updated' => 'NOW()'
                );

                foreach($old_sources as $source_id => $source_val) {
                    if (is_numeric($result)) {
                        $params['source'] = $source_val;
                        $result += $this->db->updateSourceData($params, $source_id);
                    } else {
                        $result = FALSE;
                        break;
                    }
                }

                $params['added'] = 'NOW()';

                foreach($new_sources as $source_val) {
                    if (!empty($source_val)) {
                        $params['source'] = $source_val;
                        if (is_numeric($result) && is_numeric($this->db->insertSourceData($params))) {
                            $result++;
                        } else {
                            $result = FALSE;
                            break;
                        }
                    }
                }

                if (is_numeric($result) && $result > 0) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function buildCompanyForm(&$data = array(), $show = FALSE){

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];

        $sources = $this->db->getSourceList(array(
            'select' => array('E_A_S.id as id', 'E_A_S.source as source')
        ));

        $sources = array_combine($this->getFieldFromArray($sources, 'id'), $this->getFieldFromArray($sources, 'source'));
        $platforms = array(
            'stb' => 'Set-Top Box',
            'ios' => 'iOS',
            'android' => 'Android',
            'smarttv' => 'SmartTV'
        );

        $this->app['platform_list'] = array(
            'stb' => array('101' => 'STANDART SKIN', '201' => 'SMART LAUNCHER SKINS'),
            'ios' => array('401' => 'iOS'),
            'android' => array('301' => 'Android'),
            'smarttv' => array('501' => 'SmartTV')
        );

        if (array_key_exists('status', $data)) {
            settype($data['status'], 'bool');
        }

        $ad_positions = $this->db->getAllFromTable('ext_adv_positions', 'position_code');
        $parts_labels = array();
        foreach($platforms as $platform=>$label) {
            if (!array_key_exists($platform, $data)) {
                $data[$platform] = array();
            }
            if (!array_key_exists($platform, $parts_labels)) {
                $parts_labels[$platform] = array();
            }
            foreach($ad_positions as $row) {
                if($row['platform'] == $platform){
                    $parts_labels[$platform][$row['position_code']] = $row['label'];
                    $data[$platform][$row['position_code']] = array_key_exists($row['position_code'], $data[$platform]) && $data[$platform][$row['position_code']];
                }
            }
        }

        $form = $builder->createBuilder('form', $data)
            ->add('id', 'hidden')
            ->add('name', 'text', array(
                'attr'      => array('class' => 'form-control', 'data-validation' => 'required'),
                'required' => TRUE
            ))
            ->add('source', 'choice', array(
                    'choices' => $sources,
                    'required' => TRUE,
                    'attr' => array('readonly' => $show, 'disabled' => $show, 'class' => 'populate placeholder', 'data-validation' => 'required'),
                    'data' => (empty($data['source']) ? '': $data['source']),
                )
            )
            ->add('platform', 'choice', array(
                    'choices' => $platforms,
                    'required' => TRUE,
                    'attr' => array('readonly' => $show, 'disabled' => $show, 'class' => 'populate placeholder', 'data-validation' => 'required'),
                    'data' => (empty($data['platform']) ? 'stb': $data['platform']),
                )
            )
            ->add('status', 'checkbox', array(
                    'label' => ' ',
                    'required' => FALSE,
                    'label_attr' => array('class'=> 'label-success'),
                    'attr' => array('readonly' => $show, 'disabled' => $show, 'class' => 'form-control'),
                )
            )
            ->add('save', 'submit')
            ->add('stb', 'collection', array(
                'type' => 'checkbox',
                'options' => array(
                    'required' => FALSE,
                    'label' => $parts_labels['stb']
                ),
                'required' => FALSE,
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => FALSE
            ))
            ->add('ios', 'collection', array(
                'type' => 'checkbox',
                'options' => array(
                    'required' => FALSE,
                    'label' => $parts_labels['ios']
                ),
                'required' => FALSE,
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => FALSE
            ))
            ->add('android', 'collection', array(
                'type' => 'checkbox',
                'options' => array(
                    'required' => FALSE,
                    'label' => $parts_labels['android']
                ),
                'required' => FALSE,
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => FALSE
            ))
            ->add('smarttv', 'collection', array(
                'type' => 'checkbox',
                'options' => array(
                    'required' => FALSE,
                    'label' => $parts_labels['smarttv']
                ),
                'required' => FALSE,
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => FALSE
            ));

        return $form->getForm();
    }

    private function saveCompanyData(&$form) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            if ($form->isValid()) {
                $get_positions = array();
                foreach( array( 'stb', 'ios', 'android', 'smarttv') as $platform){
                    if (array_key_exists($platform, $data)) {
                        $get_positions = array_merge($get_positions, array_keys($data[$platform]));
                    }
                }

                if (!empty($data['id'])) {
                    $is_positions = $this->db->getAdPositions($data['id']);
                    if (!empty($is_positions)) {
                        $del_position = array_diff($is_positions, $get_positions);
                        $get_positions = array_diff($get_positions, $is_positions);
                        if (!empty($del_position)){
                            $this->db->delAdPositions($data['id'], $del_position);
                        }
                    }
                }


                $curr_fields = $this->db->getTableFields('ext_adv_campaigns');
                $curr_fields = $this->getFieldFromArray($curr_fields, 'Field');
                $curr_fields = array_flip($curr_fields);

                $data = array_intersect_key($data, $curr_fields);
                $data['updated'] = 'NOW()';

                if (!empty($data['id'])) {
                    $operation = 'update';
                    $id = $data['id'];
                    unset($data['id']);
                    $params = array($data, $id);
                } else {
                    $operation = 'insert';
                    $data['added'] = 'NOW()';
                    $params = array($data);
                }

                $result = call_user_func_array(array($this->db, $operation.'CompanyData'), $params);

                if (is_numeric($result)) {
                    $this->db->addAdPositions($operation == 'update' ? $id: $result, $get_positions);
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function getDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),        'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Title'),     'checked' => TRUE),
            array('name' => 'platform',     'title' => $this->setLocalization('Platform'),  'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization('Status'),    'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),'checked' => TRUE)
        );
        return $attribute;
    }

    private function getJoinedCompanyTables(){
        return array(
            'ext_adv_sources as E_A_S' => array('left_key' => 'E_A_C.source', 'right_key' => 'E_A_S.id', 'type' => 'LEFT'),
        );
    }

    private function getCompanyFields(){
        return array(
            "id" => "E_A_C.`id` as `id`",
            "name" => "E_A_C.`name` as `name`",
            "platform" => "E_A_C.`platform` as `platform`",
            "status" => "E_A_C.`status` as `status`"
        );
    }
}