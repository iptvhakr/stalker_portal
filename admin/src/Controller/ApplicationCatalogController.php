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
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['id'])) {
            return $this->app->redirect($this->workURL . '/application-catalog');
        }

        $attribute = $this->getApplicationDetailDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['app_info'] = $this->application_version_list_json();
        $this->app['breadcrumbs']->addItem(!empty($this->app['app_info']['info']['name']) ? $this->app['app_info']['info']['name'] : $this->setLocalization('Undefined'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
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

        try{
            $apps_list = new \AppsManager();
            $response['data'] = $apps_list->getList();
        } catch (\Exception $e){
            $response['error'] = $error = $this->setLocalization('Failed to get the list of applications');
        }

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
            if (!array_key_exists('repository', $response['data'])) {
                $response['data']['repository']['url'] = $this->postData['apps']['url'];
            }
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
            $app = $this->db->getApplication(array('url' => $postData['url']));
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

    public function application_version_list_json(){

        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'manageList',
            'info'=> array()
        );

        $id = FALSE;

        $version = !empty($this->postData['version']) ? $this->postData['version'] : FALSE;

        if (!empty($this->data['id'])) {
            $id = $this->data['id'];
        }
        if (!empty($this->postData['id'])) {
            $id = $this->postData['id'];
            $response['action'] = 'createOptionForm';
        }

        try{
            $apps_list = new \AppsManager();
            $app = $apps_list->getAppInfo($id);
        } catch (\Exception $e){
            $response['error'] = $error = $this->setLocalization('Failed to get the list of versions of this applications') . '. ' . $e->getMessage();
            $app = FALSE;
        }

        if ($app !== FALSE) {
            $response["data"] = array_values(array_filter(array_map(function($row) use ($version){
                if ($version === FALSE || $version == $row['version']) {
                    $row['published'] = (int)strtotime($row['published']);
                    $row['published'] = $row['published'] < 0 ? 0 : $row['published'];
                    return $row;
                }
            }, $app['versions'])));
            $response['recordsTotal'] = count($response["data"]);
            $response['recordsFiltered'] = count($response["data"]);
            unset($app['versions']);
            $response['info'] = $app;
        }

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function application_version_save_option(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['apps'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';
        $postData = $this->postData['apps'];
        if (!empty($postData['id'])) {
            $app_id = $postData['id'];
            unset($postData['id']);
            $option = json_encode($postData);

            $result = $this->db->updateApplication(array('options' => $option), $app_id);
            if (is_numeric($result)) {
                $response['error'] = $error = '';
                if ($result === 0) {
                    $response['nothing_to_do'] = TRUE;
                }
            } else {
                $response['error'] = $error = $this->setLocalization('Failed to update the parameters of application launch');
            }
        } else {
            $response['error'] = $error = $this->setLocalization('Application is undefined');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function application_version_install(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';
        if (!empty($this->postData['id'])) {
            ignore_user_abort(true);
            set_time_limit(0);

            try{
                $apps = new \AppsManager();
                if (empty($this->postData['version'])) {
                    $result = $apps->installApp($this->postData['id']);
                } else {
                    $result = $apps->updateApp($this->postData['id'], $this->postData['version']);
                }
                if ($result !==FALSE ) {
                    $response['error'] = $error = '';
                    $response['installed'] = 1;
                } else {
                    $response['error'] = $error = $this->setLocalization('Error of installing the application');
                }
            } catch(\PharException $e){
                $response['error'] = $this->setLocalization($e->getMessage());
            } catch(\Exception $e){
                $response['error'] = $this->setLocalization($e->getMessage());
            }
        } else {
            $response['error'] = $error = $this->setLocalization('Application is undefined');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function application_version_delete(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';
        if (!empty($this->postData['id'])) {
            ignore_user_abort(true);
            set_time_limit(0);

            $app_db = $this->db->getApplication(array('id' => $this->postData['id']));

            try{
                $apps = new \AppsManager();
                $apps->deleteApp($this->postData['id'], $this->postData['version']);
                $response['error'] = $error = '';

                if ($app_db[0]['current_version'] == $this->postData['version']) {
                    $response['installed'] = 0;
                }

            } catch(\Exception $e){
                $response['error'] = $error = $this->setLocalization('Error of uninstalling the application.');
                $response['error'] .= ' ' . $this->setLocalization($e->getMessage());
            }
        } else {
            $response['error'] = $error = $this->setLocalization('Application is undefined');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function application_toggle_state(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'changeStatus';
        $response['field'] = 'app_status';
        $postData = $this->postData;
        $id = $postData['id'];
        $key = '';
        if (array_key_exists('status', $postData)) {
            $postData['status'] = !empty($postData['status']) && $postData['status'] != 'false' && $postData['status'] !== FALSE ? 1: 0;
            $key = 'status';
        }

        if (array_key_exists('autoupdate', $postData)) {
            $postData['autoupdate'] = !empty($postData['autoupdate']) && $postData['autoupdate'] != 'false' && $postData['autoupdate'] !== FALSE ? 1: 0;
            $response['field'] = 'app_autoupdate';
            $key = 'autoupdate';
        }

        unset($postData['id']);

        $result = $this->db->updateApplication($postData, $id);
        if (is_numeric($result)) {
            $response['error'] = $error = '';
            if (!empty($postData['current_version'])) {
                $response['msg'] = $this->setLocalization('Activated. Current version') . ' ' . $postData['current_version'];
            }
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
            $response['installed'] = !empty($postData[$key]) && $postData[$key] != 'false' && $postData[$key] !== FALSE? 1: 0;;
        } else {
            $response['error'] = $error = $this->setLocalization('Failed to activated of application.');
            if (!empty($postData['current_version'])) {
                $response['error'] = $error .= $this->setLocalization('Version') . ' ' . $postData['current_version'];
            }
            $response['installed'] = (int)!(!empty($postData[$key]) && $postData[$key] != 'false' && $postData[$key] !== FALSE? 1: 0);
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function application_delete(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';

        if ($this->db->deleteApplication($this->postData)) {
            $response['error'] = $error = '';
            $response['msg'] = $this->setLocalization('Application has been deleted');
        } else {
            $response['error'] = $error = $this->setLocalization('Failed to delete application.');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function getApplicationListDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',               'title' => $this->setLocalization('ID'),                'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Application'),       'checked' => TRUE),
            /*array('name' => 'publisher',        'title' => $this->setLocalization('Publisher'),     'checked' => TRUE),*/
            array('name' => 'url',              'title' => $this->setLocalization('URL'),               'checked' => TRUE),
            array('name' => 'current_version',  'title' => $this->setLocalization('Current version'), 'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('State'),             'checked' => TRUE),
            array('name' => 'operations',       'title' => $this->setLocalization('Operations'),        'checked' => TRUE)
        );
        return $attribute;
    }

    private function getApplicationDetailDropdownAttribute() {
        $attribute = array(
            array('name' => 'version',      'title' => $this->setLocalization('Application version'),   'checked' => TRUE),
            array('name' => 'published',    'title' => $this->setLocalization('Release date'),          'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization('State'),                 'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),            'checked' => TRUE)
        );
        return $attribute;
    }
}
