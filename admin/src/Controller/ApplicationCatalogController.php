<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class ApplicationCatalogController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $tos = $this->db->getTOS();
        if (empty($tos)) {
            return $this->app['twig']->render('ApplicationCatalog_index.twig');
        } elseif (empty($tos[0]['accepted'])) {
            $this->app['tos'] = $tos[0];
            return $this->app['twig']->render('ApplicationCatalog_tos.twig');
        }

        $attribute = $this->getApplicationListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->application_list_json();
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render('ApplicationCatalog_application_list.twig');
    }

    public function accept_tos() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->app['userlogin'] === 'admin' && !empty($this->postData['accepted'])){
            $this->db->setAcceptedTOS();
        }

        return $this->app->redirect($this->workURL . '/application-catalog');
    }

    public function application_detail(){

    }

    public function application_install(){

    }

    public function application_update(){

    }

    public function application_delete(){

    }

    public function application_toggle_state(){

    }

    public function application_version_edit(){

    }

    //----------------------- ajax method --------------------------------------

    public function application_list_json(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $apps_list = new \AppsManager();
        $response['data'] = $apps_list->getList();

        $response['recordsTotal'] = $response['recordsFiltered'] = count($response['data']);

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = '';

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function application_get_data_from_repo(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['apps']['url'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'buildSaveForm';
        $response['data'] = array();
        $response['error'] = '';
        try{
            $repo =  new \GitHub($this->postData['apps']['url']);
            $response['data'] = $repo->getFileContent('package.json');
        } catch(\GitHubError $e){
            $response['error'] = $this->setLocalization($e->getMessage());
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function application_add(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['apps'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';
        $postData = $this->postData['apps'];
        if (!empty($postData['url'])) {
            $app = $this->db->getApplicationByURL($postData['url']);
            if (empty($app) && $this->db->insertApplication($postData)) {
                $response['error'] = $error = '';
            } else {
                $response['error'] = $error = $this->setLocalization('Perhaps the application is already installed. You can update it if the new version is available or uninstall and install again');
            }
        } else {
            $response['error'] = $error = $this->setLocalization('URL of application is not defined');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function getApplicationListDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',               'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Name'),          'checked' => TRUE),
            /*array('name' => 'publisher',        'title' => $this->setLocalization('Publisher'),     'checked' => TRUE),*/
            array('name' => 'url',              'title' => $this->setLocalization('URL'),           'checked' => TRUE),
            array('name' => 'current_version',  'title' => $this->setLocalization('Current version'),'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('State'),         'checked' => TRUE),
            array('name' => 'operations',       'title' => $this->setLocalization('Operations'),    'checked' => TRUE)
        );
        return $attribute;
    }
}
