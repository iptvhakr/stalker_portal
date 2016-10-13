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

        $check_register = $this->db->getRegisterRowsList(array('where' => array('A.id' => $this->app['user_id'])), 'ALL');
        if (empty($check_register)) {
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
        } elseif (!empty($this->data['id']) || !empty($this->postData['id'])) {
            $data = $this->db->getRegisterList(array(
                'select' => array('E_A_R.id as id', 'E_A_R.name as name', 'E_A_R.phone as phone', 'E_A_R.email as email', 'E_A_R.region as region', 'E_A_R.admin_id as admin_id'),
                'where' => array('E_A_R.id' => (!empty($this->data['id'])? $this->data['id']: $this->postData['id']))
            ));
            $data = !empty($data) && is_array($data) ? $data[0] : array('admin_id' => $this->app['user_id']);
        } else {
            $data = array('admin_id' => $this->app['user_id']);
        }

        $data['accept_terms'] = !empty($data['accept_terms']) && ($data['accept_terms'] == 'on' || (int)$data['accept_terms'] == 1);

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

        $form = $this->buildCompanyForm($data);

        if ($this->saveCompanyData($form)){
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/company-list');
        }

        $this->app['form'] = $form->createView();

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of companies'), $this->app['controller_alias'] . '/company-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Company add'));

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

        $form = $this->buildCompanyForm($data);

        if ($this->saveCompanyData($form)){
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/company-list');
        }

        $this->app['form'] = $form->createView();

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of companies'), $this->app['controller_alias'] . '/company-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Company edit'));

        return $this->app['twig']->render("ExternalAdvertising_company_add.twig");
    }

    public function settings(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $check_register = $this->db->getRegisterRowsList(array('where' => array('A.id' => $this->app['user_id'])), 'ALL');
        if (empty($check_register)) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/register');
        }

        if ($this->method == 'POST' && array_key_exists('form', $this->postData)) {
            $data = $this->postData['form'];
        } else {
            if (!empty($this->data['id']) || !empty($this->postData['id'])) {
                $id = !empty($this->data['id']) ? $this->data['id'] : $this->postData['id'];
            } else {
                $registration = $this->db->getRegisterList(array('select' => array('E_A_R.id as owner', 'E_A_R.admin_id', 'A.reseller_id'), 'where' => array('A.id' => $this->app['user_id'])));
                $registration = end($registration);
                $id = $registration['owner'];
            }

            $data = $this->db->getSourceList(array(
                'select' => array('E_A_S.id', 'E_A_S.owner', 'E_A_S.source'),
                'where' => array('E_A_S.owner' => $id),
                'joined' => $this->getJoinedSettingsTables()
            ));
            if (!empty($data)) {
                $sources = $this->getFieldFromArray($data, 'source');
                $ids = $this->getFieldFromArray($data, 'id');
                $data = array(
                    'owner' => $id,
                    'source' => !empty($sources) ? array_combine($ids, $sources) : array(''),
                    'new_source' => array('')
                );
            } else {
                $data = array('owner' => $id, 'new_source' => array(''));
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

    public function request_new_source(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['owner'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'messageBlock';
        $error = $this->setLocalization('Failed');

        $registration = $this->db->getRegisterList(array('select' => array('E_A_R.name as name', 'E_A_R.email as email', 'E_A_R.phone as phone', 'E_A_R.region as region'), 'where' => array('E_A_R.id' => $this->postData['owner'])));

        $registration = array_filter(array_pop($registration));

        if (count($registration) != 4) {
            $data['msg'] = '<div class="col-md-12">'.
                '<span class="col-md-12 txt-default">'. $this->setLocalization('The registration data are incomplete. To edit data please go to the link: ').
                    '<a href="' . $this->workURL . '/' .$this->app['controller_alias'] . '/register?id=' . $this->postData['owner'] . '">' .
                        $this->setLocalization('edit register data') .
                    '</a>' .
                '</span>'.
            '</div>';
            $data['button_block'] = FALSE;
            $data['data_empty'] = TRUE;
            $error = '';
        } else {
            try{
                $registration['requery'] = TRUE;
                if (call_user_func_array(array('\Stalker\Lib\Core\Advertising', 'registration'), $registration)) {
                    $data['button_block'] = TRUE;
                    $data['msg'] = $this->setLocalization('Requested');
                    $error = '';
                }
            } catch (\Exception $e) {
                $data['msg'] = $e->getMessage();
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function buildRegisterForm(&$data = array(), $show = FALSE) {

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];

        $regions = array(
            'first_region' => 'First region',
            'second_region' => 'Second region',
            'third_region' => 'Third region',
            'fourth_region' => 'Fourth region',
            'fifth_region' => 'Fifth region',
        );

        $form = $builder->createBuilder('form', $data)
            ->add('id', 'hidden')
            ->add('admin_id', 'hidden')
            ->add('submit_type', 'hidden')
            ->add('name', 'text', array(
                    'attr' => array('readonly' => $show, 'disabled' => $show))
            )
            ->add('phone', 'text', array(
                    'attr' => array('readonly' => $show, 'disabled' => $show))
            )
            ->add('email', 'text', array(
                    'attr' => array('readonly' => $show, 'disabled' => $show))
            )
            ->add('region', 'choice', array(
                    'choices' => $regions,
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'data' => (empty($data['region']) ? '': $data['region']),
                )
            )
            ->add('accept_terms', 'checkbox', array(
                    'required' => TRUE,
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                )
            )
            ->add('save', 'submit')
            ->add('skip', 'submit');
        return $form->getForm();
    }

    private function saveRegisterData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($data['accept_terms'] && $form->isValid()) {
                $curr_fields = $this->db->getTableFields('ext_adv_register');
                $curr_fields = $this->getFieldFromArray($curr_fields, 'Field');
                $curr_fields = array_flip($curr_fields);

                $data = array_intersect_key($data, $curr_fields);
                $data['updated'] = 'NOW()';

                if (!empty($data['id'])) {
                    $operation = 'update';
                    $id = $data['id'];
                    $params = array($data, $id);
                }else {
                    $operation = 'insert';
                    $data['added'] = 'NOW()';
                    $params = array($data);
                }
                unset($data['id']);

                $result = call_user_func_array(array($this->db, $operation.'RegisterData'), $params);
                if (is_numeric($result)) {
                    return TRUE;
                }
            } elseif (!$data['accept_terms']) {
                $form->get('accept_terms')->addError(new FormError($this->setLocalization('You need accept Terms of Service.')));
            }
        }
        return FALSE;
    }

    private function buildSettingsForm(&$data = array(), $show = FALSE){

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];

        $form = $builder->createBuilder('form', $data)
            ->add('owner', 'hidden');
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
                    'owner' => $data['owner'],
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
            'select' => array('E_A_S.id as id', 'E_A_S.source as source'),
            'where' => array('A.id' => $this->app['user_id']),
            'joined' => $this->getJoinedSettingsTables()
        ));

        $sources = array_combine($this->getFieldFromArray($sources, 'id'), $this->getFieldFromArray($sources, 'source'));
        $platforms = array(
            'settopbox' => 'Set-Top Box',
            'ios' => 'iOS',
            'android' => 'Android',
            'smarttv' => 'SmartTV'
        );

        $choise_labels = array(
            $this->setLocalization('Run applications'),
            $this->setLocalization('Before starting the movie - Video Club'),
            $this->setLocalization('During the movie playback - Video Club')
        );

        if (!empty($data['old_skin_pos'])) {
            $data['old_skin_pos'] = $this->prepareBitMaskField($data['old_skin_pos']);
        } else {
            $data['old_skin_pos'] = array_fill(0, 3, 0);
        }

        if (!empty($data['smart_skin_pos'])) {
            $data['smart_skin_pos'] = $this->prepareBitMaskField($data['smart_skin_pos']);
        } else {
            $data['smart_skin_pos'] = array_fill(0, 3, 0);
        }

        if (array_key_exists('status', $data)) {
            settype($data['status'], 'bool');
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
                    'data' => (empty($data['platform']) ? '': $data['platform']),
                )
            )
            ->add('status', 'checkbox', array(
                    'label' => ' ',
                    'required' => FALSE,
                    'label_attr' => array('class'=> 'label-success'),
                    'attr' => array('readonly' => $show, 'disabled' => $show, 'class' => 'form-control'),
                )
            )
            ->add('old_skin_pos', 'collection', array(
                'entry_type'   => 'checkbox',
                'entry_options'  => array(
                    'attr'      => array('class' => 'form-control', 'style'=> 'display: inline-block; height: auto; width: auto;'),
                    'label' => $choise_labels
                )
            ))
            ->add('smart_skin_pos', 'collection', array(
                'entry_type'   => 'checkbox',
                'entry_options'  => array(
                    'attr'      => array('class' => 'form-control', 'style'=> 'display: inline-block; height: auto; width: auto;'),
                    'label' => $choise_labels
                )
            ))
            ->add('save', 'submit');
        return $form->getForm();
    }

    private function saveCompanyData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                reset($data);
                while(list($key, $field) = each($data)){
                    if (is_array($field)) {
                        $data[$key] = implode('', array_map(function($val){return (int)$val; }, $field));
                    }
                }
                $curr_fields = $this->db->getTableFields('ext_adv_companies');
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

    private function getJoinedSettingsTables(){
        return array(
            'ext_adv_register as E_A_R' => array('left_key' => 'E_A_S.owner', 'right_key' => 'E_A_R.id', 'type' => 'LEFT'),
            'administrators as A' => array( 'left_key' => 'E_A_R.admin_id', 'right_key' => 'A.id', 'type' => 'LEFT')
        );
    }

    private function getJoinedCompanyTables(){
        return array_merge(array(
            'ext_adv_sources as E_A_S' => array('left_key' => 'E_A_C.source', 'right_key' => 'E_A_S.id', 'type' => 'LEFT'),
        ), $this->getJoinedSettingsTables());
    }

    private function getCompanyFields(){
        return array(
            "id" => "E_A_C.`id` as `id`",
            "name" => "E_A_C.`name` as `name`",
            "platform" => "E_A_C.`platform` as `platform`",
            "status" => "E_A_C.`status` as `status`",
            "old_skin_pos" => "E_A_C.`old_skin_pos` as `old_skin_pos`",
            "smart_skin_pos" => "E_A_C.`smart_skin_pos` as `smart_skin_pos`"
        );
    }

    private function prepareBitMaskField($data){
        if (is_string($data)){
            $data =  str_split($data);
        }
        return array_replace(array_fill(0, 3, FALSE), array_map(function($val){
            return $val == (is_numeric($val) ?  1 : 'on');
        }, $data));
    }
}