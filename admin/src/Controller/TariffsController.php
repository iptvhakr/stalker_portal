<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Symfony\Component\Form\FormError;

class TariffsController extends \Controller\BaseStalkerController {

    private $allPackageTypes = array(
            array("id" => "tv", "title" => "tv"), 
            array("id" => "video", "title" => "video"), 
            array("id" => "radio", "title" => "radio"), 
            array("id" => "module", "title" => "module"), 
            array("id" => "option", "title" => "option")
        );
    protected $allServices = array();
    protected $allServiceTypes = array();

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allServiceTypes = array(
            array("id" => 'periodic', "title" => $this->setLocalization("permanent")),
            array("id" => 'single', "title" => $this->setLocalization("once-only"))
        );
        $this->allServices = array(
            array("id" => '1', "title" => $this->setLocalization("Complete")),
            array("id" =>  '2', "title" => $this->setLocalization("Optional"))
        );

        $this->allInitiatorRoles = array(
            array("id" =>   'user',     "title" => $this->setLocalization("User")),
            array("id" =>   'admin',    "title" => $this->setLocalization("Administrator")),
            array("id" =>   'api',      "title" => $this->setLocalization("API"))
        );

        $this->allPackageStates = array(
            array("id" =>   '1',    "title" => $this->setLocalization("off")),
            array("id" =>   '2',    "title" => $this->setLocalization("on"))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/tariff-plans');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app->render($this->getTemplateName(__METHOD__));
    }

    public function service_packages() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getServicePackagesDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->service_packages_list_json();

        $this->app['allPackageTypes'] = $this->setLocalization($this->allPackageTypes, 'title');
        $this->app['allServices'] = $this->setLocalization($this->allServices, 'title');

        $this->app['allTariffsPackages'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_service_package() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $form = $this->buildServicePackageForm();

        if ($this->saveServicePackageData($form)) {
            return $this->app->redirect($this->workURL . '/tariffs/service-packages');
        }
        $this->app['form'] = $form->createView();
        $this->app['servicePackageEdit'] = FALSE;
        $this->app['breadcrumbs']->addItem($this->setLocalization('Service packages'), $this->app['controller_alias'] . '/service-packages');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add package'));
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function edit_service_package() {
        ob_start();
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-service-package');
        }
        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );

        $query_param['where']['services_package.id'] = $id;
        $query_param['order'] = 'services_package.id';

        $package = $this->db->getTariffsList($query_param);
        $this->package = (is_array($package) && count($package) > 0) ? $package[0] : array();
        $services = array_flip($this->db->getPackageById($id));
        $func = 'get_' . $this->package['type'] . "_services";
        $all_services = $this->$func();
        $all_services = array_combine($this->getFieldFromArray($all_services, 'id'), $this->getFieldFromArray($all_services, 'name'));
        $this->package['services'] = array_intersect_key($all_services, $services);
        $this->package['services_json'] = '';
//        $this->package['services_json'] = json_encode($this->package['services']);
        $this->package['disabled_services'] = array_diff_key($all_services, $services);
//        $this->package['disabled_services_json'] = json_encode($this->package['disabled_services']);
        $this->package['disabled_services_json'] = '';
        $form = $this->buildServicePackageForm($this->package);

        if ($this->saveServicePackageData($form, TRUE)) {
            return $this->app->redirect($this->workURL . '/tariffs/service-packages');
        }

        $this->app['form'] = $form->createView();
        $this->app['servicePackageEdit'] = TRUE;
        $this->app['packageName'] = $this->package['name'];
        ob_end_clean();
        $this->app['breadcrumbs']->addItem($this->setLocalization('Service packages'), $this->app['controller_alias'] . '/service-packages');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit package'));
        return $this->app['twig']->render('Tariffs_add_service_package.twig');
    }

    public function tariff_plans() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->tariff_plans_list_json();

        $this->app['allTariffsPlans'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        $attribute = $this->getTariffPlansDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_tariff_plans() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $form = $this->buildTariffPlanForm();

        if ($this->saveTariffPlanData($form)) {
            return $this->app->redirect($this->workURL . '/tariffs/tariff-plans');
        }
        $this->app['userDefault'] = $this->getDefaultPlan();
        $this->app['form'] = $form->createView();
        $this->app['servicePlanEdit'] = FALSE;
        $this->app['breadcrumbs']->addItem($this->setLocalization('Tariff plans'), $this->app['controller_alias'] . '/tariff-plans');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add tariff plan'));
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function edit_tariff_plan() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-service-package');
        }
        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );
        
        $query_param['where']['tariff_plan.id'] = $id;
        $query_param['order'] = 'tariff_plan.id';
        
        $plan = $this->db->getTariffPlansList($query_param);
        $this->plan = (is_array($plan) && count($plan) > 0) ? $plan[0] : array();
        $this->plan['packages'] = $this->db->getOptionalForPlan(array(
            'select' => array('package_id as id', 'name', 'optional'),
            'where' => array('plan_id' => $id),
            'order' => array('package_in_plan.id' => '')
        ));

        $form = $this->buildTariffPlanForm($this->plan);

        if ($this->saveTariffPlanData($form, TRUE)) {
            return $this->app->redirect($this->workURL . '/tariffs/tariff-plans');
        }
        
        
        $this->app['userDefault'] = $this->getDefaultPlan($this->plan['id']);
        $this->app['form'] = $form->createView();
        $this->app['servicePlanEdit'] = TRUE;
        $this->app['planName'] = $this->plan['name'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Tariff plans'), $this->app['controller_alias'] . '/tariff-plans');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit tariff plan'));
        return $this->app['twig']->render('Tariffs_add_tariff_plans.twig');
    }

    public function subscribe_log() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $list = $this->subscribe_log_json();

        $this->app['allLogs'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $attribute = $this->getLogsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allInitiatorRoles'] = $this->allInitiatorRoles;
        $this->app['allPackageStates'] = $this->allPackageStates;
        $this->app['allPackageNames'] = $this->db->getTariffsList( array(
            'select'=>array('id', 'name as title'),
            'where' => array(),
            'like' => array(),
            'order' =>array('id'=>'ASC')
        ));

        if (!empty($this->data['user_id'])) {
            $currentUser = $this->db->getUser(array('id' => (int) $this->data['user_id']));
            $this->app['currentUser'] = array(
                'name' => $currentUser['fname'],
                'mac' => $currentUser['mac'],
                'uid' => $currentUser['id']
            );
            $this->app['breadcrumbs']->addItem($this->setLocalization('Log of user') . " " . " {$this->app['currentUser']['name']} ({$this->app['currentUser']['mac']})");
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function service_packages_list_json() {
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

        $filds_for_select = array(
            'id' => 'services_package.`id` as `id`',
            'external_id' => 'services_package.`external_id` as `external_id`',
            'name' => 'services_package.`name` as `name`',
            'users_count' => '0 as `users_count`',
            'type' => 'services_package.`type` as `type`',
            'all_services' => 'services_package.`all_services` as `all_services`'
        );

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $filter = $this->getTariffsFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $response['recordsTotal'] = $this->db->getTotalRowsTariffsList();
        $response["recordsFiltered"] = $this->db->getTotalRowsTariffsList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        $response['data'] = $this->db->getTariffsList($query_param);
        $this->setUserCount($response['data']);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function remove_service_package() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['packageid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removePackage';
        $data['msg'] = array('Package' => $this->db->deletePackageById($this->postData['packageid']), 'Services' => $this->db->deleteServicesById($this->postData['packageid']));

        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function get_services() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['type'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateService';
        $func = 'get_' . $this->postData['type'] . '_services';
        ob_start();
        $data['services'] = $this->$func();
        reset($data['services']);
        while(list($key, $row) = each($data['services'])){
            settype($data['services'][$key]['id'], 'string');
        }
        ob_end_clean();

        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_external_id() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['externalid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkExternalId';
        $error = $this->setLocalization('ID already used');
        $param = array(
            'where' => array(
                'external_id' => trim($this->postData['externalid'])
            ),
            'order' => array('id' => '')
        );
        if (!empty($this->postData['selfid'])) {
            $param['where']['id<>'] = trim($this->postData['selfid']);
        }
        $result = $this->db->getTariffPlansList($param);

        if (!empty($result)) {
            $data['chk_rezult'] = $this->setLocalization('ID already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('ID is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function tariff_plans_list_json() {
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

        $filds_for_select = array(
            'id' => 'tariff_plan.`id` as `id`',
            'external_id' => 'tariff_plan.`external_id` as `external_id`',
            'name' => 'tariff_plan.`name` as `name`',
            'users_count' => '(SELECT COUNT(*) FROM users WHERE (users.tariff_plan_id = tariff_plan.id) || IF(tariff_plan.user_default, tariff_plan_id = 0, 0)) AS users_count',
            'user_default' => 'tariff_plan.`user_default` as `user_default`'
        );

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'id';
            $query_param['select'][] = 'tariff_plan.`user_default` as `user_default`';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $filter = $this->getTariffsFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $response['recordsTotal'] = $this->db->getTotalRowsTariffPlansList();
        $response["recordsFiltered"] = $this->db->getTotalRowsTariffPlansList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        $response['data'] = $this->db->getTariffPlansList($query_param);
//        $this->setUserCount($response['data']);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function remove_tariff_plan() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['planid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removePlan';
        $data['msg'] = array('Plan' => $this->db->deletePlanById($this->postData['planid']), 'Tariff' => $this->db->deleteTariffById($this->postData['planid']));

        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function subscribe_log_json() {
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

        $filds_for_select = array(
            'id' => 'P_S_L.`id` as `id`',
            'mac' => 'CAST(U.`mac` AS CHAR) as `mac`',
            'package' => 'S_P.`name` as `package`',
            'state' => 'P_S_L.`set_state` as `state`',
            'initiator_name' => 'IF(P_S_L.`initiator` = "admin", A.login, IF(P_S_L.`initiator` = "user" AND U.`login` <> "" AND NOT ISNULL(U.`login`) , U.`login`, P_S_L.`initiator`)) as `initiator_name`',
            'initiator' => 'P_S_L.`initiator` as `initiator`',
            'modified' => 'CAST(P_S_L.`modified` as CHAR) as `modified`',
        );

        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $filter = $this->getTariffsFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $user_id = FALSE;
        if (!empty($this->data['user_id'])) {
            $query_param['where']['user_id'] = $user_id =(int) $this->data['user_id'];
        }
        $query_param['select'][] = 'P_S_L.`user_id` as `user_id`';

        $response['recordsTotal'] = $this->db->getTotalRowsSubscribeLogList(array(), array(), $user_id);
        $response["recordsFiltered"] = $this->db->getTotalRowsSubscribeLogList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $self = $this;

        $response['data'] = array_map(function($row) use ($self){
            if ($row['initiator'] != 'admin' || $row['initiator_name'] == 'user') {
                $row['initiator_name'] = $self->setLocalization($row['initiator_name']);
            }
            $row['state'] = (int) $row['state'];
            $row['initiator'] = $self->setLocalization($row['initiator']);
            $row['modified'] = (int)  strtotime($row['modified']);
            return $row;
        }, $this->db->getSubscribeLogList($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    //------------------------ service method ----------------------------------

    private function getTariffsFilters() {
        $return = array();
        if (!empty($this->data['filters'])) {
            if (!empty($this->data['filters']['type'])) {
                $return['type'] = $this->data['filters']['type'];
            }
            if (!empty($this->data['filters']['all_services'])) {
                $return['all_services'] = (int) $this->data['filters']['all_services'] - 1;
            }

            if (!empty($this->data['filters']['state'])) {
                $return['P_S_L.`set_state`'] = ((int) $this->data['filters']['state']) - 1;
            }

            if (!empty($this->data['filters']['initiator'])) {
                $return['P_S_L.`initiator`'] = $this->data['filters']['initiator'];
            }
            if (!empty($this->data['filters']['package'])) {
                $return['S_P.`id`'] = (int) $this->data['filters']['package'];
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }

    private function setUserCount(&$data) {
        reset($data);
        while (list($key, $row) = each($data)) {
            $data[$key]['users_count'] = (int) $this->db->getUserCountForPackage($row['id']);
            $data[$key]['users_count'] += (int) $this->db->getUserCountForSubscription($row['id']);
        }
    }

    private function buildServicePackageForm(&$data = array(), $edit = FALSE) {
        $builder = $this->app['form.factory'];
        if (array_key_exists('all_services', $data)) {
            $val = $data['all_services'];
            settype($data['all_services'], 'bool');
        } else {
            $val = FALSE;
        }
        $all_services = $services = $disabled_services = array('');
        if (!empty($data)) {
            if (!empty($data["id"])) {
                $services = array_flip($this->db->getPackageById($data["id"]));
            }
            $func = 'get_' . $data['type'] . "_services";
            $all_services = $this->$func();
            $all_services = array_combine($this->getFieldFromArray($all_services, 'id'), $this->getFieldFromArray($all_services, 'name'));
            $services = array_intersect_key($all_services, $services);
            $disabled_services = array_diff_key($all_services, $services);
            if (empty($data['service_type'])) {
                $data['service_type'] = 'periodic';
            }
            $data['services_json'] = json_encode(array_keys($services));
            $data['disabled_services_json'] = json_encode(array_keys($disabled_services));
        }

        $disabled_services["0"] = '';
        $services["0"] = '';

        $allPackageTypes = array_combine($this->getFieldFromArray($this->allPackageTypes, 'id'), $this->getFieldFromArray($this->allPackageTypes, 'title'));
        $allServiceTypes = array_combine($this->getFieldFromArray($this->allServiceTypes, 'id'), $this->getFieldFromArray($this->allServiceTypes, 'title'));

        $form = $builder->createBuilder('form', $data, array('csrf_protection' => false))
                ->add('id', 'hidden')
                ->add('external_id', 'text', array(
                        'constraints' => array(
                            new Assert\NotBlank()
                        ),
                        'required' => TRUE
                    ))
                ->add('name', 'text', array(
                    'constraints' => new Assert\NotBlank(),
                        'required' => TRUE
                    ))
                ->add('description', 'textarea', array('required' => false))
                ->add('type', 'choice', array(
                    'choices' => $allPackageTypes,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($allPackageTypes))), new Assert\NotBlank()),
                    'required' => TRUE
                ))
                ->add('service_type', 'choice', array(
                    'choices' => $allServiceTypes,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($allServiceTypes)))),
                    'required' => FALSE
                ))
                ->add('price', 'text', array('required' => false))
                ->add('rent_duration', 'text', array('required' => false))
                ->add('all_services', 'checkbox', array('required' => false, 'value' => $val))
                ->add('disabled_services_json', 'hidden')
                ->add('disabled_services', 'choice', array(
                    'choices' => $disabled_services,
                    'multiple' => TRUE,
                    'required' => FALSE
                ))
                ->add('services_json', 'hidden')
                ->add('services', 'choice', array(
                    'choices' => $services,
                    'multiple' => TRUE,
                    'required' => FALSE
                ))
                ->add('save', 'submit');
//                ->add('reset', 'reset');
        return $form->getForm();
    }

    private function saveServicePackageData(&$form, $edit = FALSE) {
        if (!empty($this->method) && $this->method == 'POST') {

            $form->handleRequest($this->request);
            $data = $form->getData();
            $data['services'] = array_flip(json_decode($data['services_json']));
            $data['disabled_services'] = array_flip(json_decode($data['disabled_services_json']));
            $action = (isset($this->package) && $edit ? 'updatePackage' : 'insertPackage');
            $package_external_id = $this->db->getTariffsList(array('where' => array('external_id' => $data['external_id'], "id<>"=>($edit? $data['id']: ''))));
            $data['all_services'] = !empty($data['all_services']) ? (int) $data['all_services'] : 0;
            if (empty($data['service_type'])) {
                $data['service_type'] = 'periodic';
            }

            if ($edit && (!empty($package_external_id) && $package_external_id[0]['id'] != $data['id']) ||
                !$edit && !empty($package_external_id)) {
                $form->get('external_id')->addError(new FormError($this->setLocalization('ID already used')));
                return FALSE;
            }

            if ($form->isValid()) {
                $param[] = array_intersect_key($data, array_flip($this->getFieldFromArray($this->db->getTableFields('services_package'), 'Field')));
                if ($edit && !empty($data['id'])) {
                    $param[] = $data['id'];
                    unset($param[0]['id']);
                    if ($package_external_id == $data['external_id']) {
                        unset($param[0]['external_id']);
                    }
                    $this->db->deleteServicesById($data['id']);
                }
                if ($return_val = call_user_func_array(array($this->db, $action), $param)) {
                    if (!empty($data['services'])) {
                        foreach ($data['services'] as $id => $service) {
                            $this->db->insertServices(array(
                                'service_id' => $id,
                                'package_id' => ($action == 'updatePackage' ? $data['id'] : $return_val),
                                'type' => $data['type']
                            ));
                        }
                    }
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function get_tv_services() {
        return \Itv::getServices();
    }

    private function get_video_services() {
        return \Video::getServices();
    }

    private function get_radio_services() {
        return \Radio::getServices();
    }

    private function get_module_services() {
        return \Module::getServices();
    }

    private function get_option_services() {
        $option_services = \Config::getSafe('option_services', array());

        $result = array_map(function($item) {
            return array(
                'id' => $item,
                'name' => $item
            );
        }, $option_services);
        return $result;
    }

    private function buildTariffPlanForm(&$data = array(), $edit = FALSE){
        $builder = $this->app['form.factory'];
        if (array_key_exists('user_default', $data)) {
            $val = $data['user_default'];
            settype($data['user_default'], 'bool');
        } else {
            $val = FALSE;
        }
        $tmp = $this->db->getTariffsList(array('select' => array('id', 'name'), 'order' => array('id' => '')));
        $all_packeges = array_combine($this->getFieldFromArray($tmp, 'id'), $this->getFieldFromArray($tmp, 'name'));
        
        if (!empty($data['packages'])) {
            $data['packages_optional'] = array_combine($this->getFieldFromArray($data['packages'], 'id'), $this->getFieldFromArray($data['packages'], 'optional'));
            $data['packages'] = $this->getFieldFromArray($data['packages'], 'id');
        } else {
            $data['packages'] = $data['packages_optional'] = array();
        }
        
        $data['packages_optional'] = json_encode($data['packages_optional']);

        $form = $builder->createBuilder('form', $data, array('csrf_protection' => false))
                ->add('id', 'hidden')
                ->add('external_id', 'text', array(
                    'constraints' => array(new Assert\NotBlank()),
                    'required' => TRUE))
                ->add('name', 'text', array('constraints' => array(new Assert\NotBlank()), 'required' => TRUE))
                ->add('user_default', 'checkbox', array('required' => TRUE, 'value' => $val))
                ->add('packages', 'choice', array(
                    'choices' => $all_packeges,
                    'multiple' => TRUE,
                    'required' => FALSE))
                ->add('packages_optional', 'hidden', array('required' => FALSE))
                ->add('save', 'submit');
        return $form->getForm();
    }
    
    private function saveTariffPlanData(&$form, $edit = FALSE) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            $action = (isset($this->plan) && $edit ? 'updatePlan' : 'insertPlan');
            
            $param = array(
                'where' => array(
                    'external_id' => trim($data['external_id']),
                ),
                'order' => array('id' => '')
            );
            if (!empty($data['id'])) {
                $param['where']['id<>'] = trim($data['id']);
            }
            
            $plan = $this->db->getTariffsList($param);
            if ($edit && !empty($data['id']) && (!empty($plan['external_id']) && $plan['external_id'] != $data['external_id'])) {
                return FALSE;
            }
            
            if ($form->isValid()) {
                $param = array();
                $param[] = array_intersect_key($data, array_flip($this->getFieldFromArray($this->db->getTableFields('tariff_plan'), 'Field')));
                if ($edit && !empty($data['id'])) {
                    $param[] = $data['id'];    
                    unset($param[0]['id']);
                    if (array_key_exists('external_id', $plan) && $plan['external_id'] == $data['external_id']) {
                        unset($param[0]['external_id']);
                    }
                    $this->db->deletePackageInPlanById($data['id']);
                }
                if ($return_val = call_user_func_array(array($this->db, $action), $param)) {
                    if (!empty($data['packages_optional'])) {
                        $packages_optional = json_decode($data['packages_optional']);
                        foreach ($packages_optional as $package => $option) {
                            $this->db->insertPackageInPlan(array(
                                'plan_id' => ($action == 'updatePlan' ? $data['id'] : $return_val),
                                'package_id' => $package,
                                'optional' => $option
                            ));
                        }
                    }
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    private function getDefaultPlan($curr_id = FALSE){
        $default_plan = $this->db->getUserDefaultPlan();
        if (!empty($default_plan) && $default_plan != $curr_id) {
            return TRUE;
        }
        return FALSE;
    }
    
    private function getServicePackagesDropdownAttribute() {
        return array(
            array('name'=>'external_id',    'title'=>$this->setLocalization('External ID'), 'checked' => TRUE),
            array('name'=>'name',           'title'=>$this->setLocalization('Package'),     'checked' => TRUE),
            array('name'=>'users_count',    'title'=>$this->setLocalization('Users'),       'checked' => TRUE),
            array('name'=>'type',           'title'=>$this->setLocalization('Service'),     'checked' => TRUE),
            array('name'=>'all_services',   'title'=>$this->setLocalization('Access'),      'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operations'),  'checked' => TRUE)
        );
    }
    
    private function getTariffPlansDropdownAttribute() {
        return array(
            array('name'=>'external_id',    'title'=>$this->setLocalization('External ID'), 'checked' => TRUE),
            array('name'=>'name',           'title'=>$this->setLocalization('Tariff name'), 'checked' => TRUE),
            array('name'=>'users_count',    'title'=>$this->setLocalization('Users'),       'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operations'),  'checked' => TRUE)
        );
    }

    private function getLogsDropdownAttribute() {
        return array(
            array('name'=>'id',             'title'=>$this->setLocalization('ID'),              'checked' => TRUE),
            array('name'=>'mac',            'title'=>$this->setLocalization('MAC'),             'checked' => TRUE),
            array('name'=>'package',        'title'=>$this->setLocalization('Package name'),    'checked' => TRUE),
            array('name'=>'state',          'title'=>$this->setLocalization('State'),           'checked' => TRUE),
            array('name'=>'initiator_name', 'title'=>$this->setLocalization('Initiator'),       'checked' => TRUE),
            array('name'=>'initiator',      'title'=>$this->setLocalization('Initiator role'),  'checked' => TRUE),
            array('name'=>'modified',       'title'=>$this->setLocalization('Modified'),        'checked' => TRUE)
        );
    }
}
