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
            return $this->app->redirect($this->app['controller_alias'] . '/verta-media-company-list');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function verta_media_company_list() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $check_register = $this->db->getRegisterRowsList(array('where' => array('A.id' => $this->app['user_id'])), 'ALL');
        if (empty($check_register)) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/verta-media-register');
        }

        if (empty($this->data['filters'])) {
            $this->data['filters'] = array();
        }

        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function verta_media_register(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && array_key_exists('form', $this->postData)) {
            $data = $this->postData['form'];
        } elseif (!empty($this->data['id']) || ! empty($this->postData['id'])) {
            $data = $this->db->getRegisterList(array(
                'where' => array('E_A_R.id' => (!empty($this->data['id'])? $this->data['id']: $this->postData['id']))
            ));
            $data = !empty($data) && is_array($data) ? $data[0] : array('admin_id' => $this->app['user_id']);
        } else {
            $data = array('admin_id' => $this->app['user_id']);
        }

        $data['accept_terms'] = !empty($data['accept_terms']) && ($data['accept_terms'] == 'on' || (int)$data['accept_terms'] == 1);

        $form = $this->buildRegisterForm($data);

        if ($this->saveRegisterData($form)) {
            if (!empty($data['submit_type']) && $data['submit_type'] == 'skip') {
                return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/verta-media-settings');
            }
            $this->app['breadcrumbs']->addItem($this->setLocalization('Congratulations!'));
            return $this->app['twig']->render('ExternalAdvertising_verta_media_register_confirm.twig');
        }
        $this->app['form'] = $form->createView();
        $this->app['breadcrumbs']->addItem($this->setLocalization('Register'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function verta_media_company_detail() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of companies'), $this->app['controller_alias'] . '/verta-media-company-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Certificate request'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function verta_media_settings(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $check_register = $this->db->getRegisterRowsList(array('where' => array('A.id' => $this->app['user_id'])), 'ALL');
        if (empty($check_register)) {
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/verta-media-register');
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
            return $this->app->redirect($this->workURL . '/' .$this->app['controller_alias'] . '/verta-media-settings');
        }
        $this->app['form'] = $form->createView();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function verta_media_company_list_json($local_use = FALSE){
        if (!$this->isAjax && !$local_use) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        $data['data'] = array();
        $error = $this->setLocalization('Failed');
        $error = '';

        $data['recordsTotal'] = 0;
        $data["recordsFiltered"] = 0;

        $response = $this->generateAjaxResponse($data, $error);

        return $local_use ? $response: new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function toggle_company_state(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $error = $this->setLocalization('Failed');

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function buildRegisterForm(&$data = array(), $show = FALSE) {

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];

        $regions = array(
            '1' => 'First region',
            '2' => 'Second region',
            '3' => 'Third region',
            '4' => 'Fourth region',
            '5' => 'Fifth region',
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
    private function getDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',       'title' => $this->setLocalization('ID'),        'checked' => TRUE),
            array('name' => 'title',    'title' => $this->setLocalization('Title'),     'checked' => TRUE),
            array('name' => 'platform', 'title' => $this->setLocalization('Platform'),  'checked' => TRUE),
            array('name' => 'status',   'title' => $this->setLocalization('Status'),    'checked' => TRUE)
        );
        return $attribute;
    }

    private function getJoinedSettingsTables(){
        return array(
            'ext_adv_register as E_A_R' => array('left_key' => 'E_A_S.owner', 'right_key' => 'E_A_R.admin_id', 'type' => 'LEFT'),
            'administrators as A' => array( 'left_key' => 'E_A_R.admin_id', 'right_key' => 'A.id', 'type' => 'LEFT')
        );
    }

}