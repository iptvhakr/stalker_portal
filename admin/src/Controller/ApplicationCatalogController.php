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

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/application-list');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function application_list() {

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

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function smart_application_list() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getSmartApplicationListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allType'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Application')),
            array('id' => 2, 'title' => $this->setLocalization('System'))
        );

        $this->app['allCategory'] = array(
            array('id' => "media",          'title' => $this->setLocalization('Media')),
            array('id' => "apps",           'title' => $this->setLocalization('Application')),
            array('id' => "games",          'title' => $this->setLocalization('Games')),
            array('id' => "notification",   'title' => $this->setLocalization('Notification'))
        );

        $this->app['allInstalled'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Not installed')),
            array('id' => 2, 'title' => $this->setLocalization('Installed'))
        );

        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Off')),
            array('id' => 2, 'title' => $this->setLocalization('On'))
        );

        $this->app['allCompatibility'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Incompatible')),
            array('id' => 2, 'title' => $this->setLocalization('Compatible'))
        );

        $this->getSmartApplicationFilters();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
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
        $this->app['breadcrumbs']->addItem($this->setLocalization('Stalker applications'), 'application-catalog/application-list');
        $this->app['breadcrumbs']->addItem(!empty($this->app['app_info']['info']['name']) ? $this->app['app_info']['info']['name'] : $this->setLocalization('Undefined'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function smart_application_detail(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['id'])) {
            return $this->app->redirect($this->workURL . '/smart-application-catalog');
        }

        $attribute = $this->getSmartApplicationDetailDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['app_info'] = $this->smart_application_version_list_json();
        $this->app['breadcrumbs']->addItem($this->setLocalization('SmartLauncher applications'), 'application-catalog/smart-application-list');
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

    public function smart_application_list_json(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => ''
        );

        $filds_for_select = $this->getSmartApplicationFields();

        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $filter = $this->getSmartApplicationFilters();

        $installed = NULL;
        if (array_key_exists('installed', $filter)) {
            $installed = (bool) $filter['installed'];
            unset($filter['installed']);
        }

        $query_param = $this->prepareDataTableParams($param, array('operations', /*'logo', 'name', 'available_version', 'conflicts', 'description',*/ 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $query_param['where'] = array_merge($query_param['where'], $filter);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $get_conflicts = FALSE;
        if (!empty($param['id'])) {
            $query_param['where']['L_A.`id`'] = $param['id'];
            $response['action'] = 'buildModalByAlias';
            $get_conflicts = TRUE;
        }

        if (!empty($query_param['like'])) {
            if (array_key_exists('description', $query_param['like'])) {
                $query_param['like']['localization'] = $query_param['like']['description'];
            } elseif (array_key_exists('name', $query_param['like'])){
                $query_param['like']['localization'] = $query_param['like']['name'];
            }
        }

        if (!array_search('L_A.`id` as `id`', $query_param['select'])) {
            $query_param['select'][] = 'L_A.`id` as `id`';
        }
        if (!array_search('L_A.`alias` as `alias`', $query_param['select'])) {
            $query_param['select'][] = 'L_A.`alias` as `alias`';
        }

        $response['recordsTotal'] = $this->db->getTotalRowsSmartApplicationList();
        $response["recordsFiltered"] = 0;//$this->db->getTotalRowsSmartApplicationList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $apps_manager = new \SmartLauncherAppsManager();

        $base_obj = $this->db->getSmartApplicationList($query_param, FALSE, TRUE);

        while($row = $base_obj->next()) {
                try {
                    $info = $apps_manager->getAppInfo($row['id']);
                    if ($installed !== NULL && $installed !== $info['installed']) {
                        continue;
                    }
                    $row['name'] = $info['name'];
                    $row['description'] = $info['description'];
                    $row['available_version'] = $info['available_version'];
                    $row['conflicts'] = $row['available_version_conflicts'] = array();
                    if ($get_conflicts) {
                        if (!empty($row['current_version'])) {
                            $row['conflicts'] = $apps_manager->getConflicts($row['id'], $row['current_version']);
                        }
                        if (!empty($row['available_version']) && (empty($row['current_version']) || $row['current_version'] != $row['available_version'])) {
                            $row['available_version_conflicts'] = $apps_manager->getConflicts($row['id'], $row['available_version']);
                        }
                    }
                    $row['icon'] = $info['icon'];
                    $row['backgroundColor'] = $info['backgroundColor'];
                    settype($row['status'], 'int');
                    $row['installed'] = $info['installed'];
                    if (count($response["data"]) <= 50) {
                        $response["data"][] = $row;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            $response["recordsFiltered"]++;
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

    public function smart_application_get_data_from_repo(){

        if (!$this->isAjax || $this->method != 'POST' || (empty($this->postData['apps']['url']) && empty($this->postData['alias']))) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = (!empty($this->postData['alias']) ? 'buildModalByAlias': 'buildSaveForm');
        $response['data'] = array();
        $response['error'] = '';
        try{
            $search_str = !empty($this->postData['apps']['url']) ? $this->postData['apps']['url']: $this->postData['alias'];
            $repo =  new \Npm();
            $response['data'] = $repo->info($search_str, (!empty($this->postData['version'])? $this->postData['version']: NULL));
            $response['data']['repository']['url'] = $search_str;

        } catch(\Exception $e){
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

    public function smart_application_add(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['apps'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';
        $postData = $this->postData['apps'];
        if (!empty($postData['url'])) {
            $app = $this->db->getSmartApplication(array('url' => $postData['url']));
            if (empty($app) && $this->db->insertSmartApplication($postData)) {
                $response['error'] = $error = '';
            } else {
                $response['error'] = $error = $this->setLocalization('Perhaps the application is already installed. You can update it if the new version is available or uninstall and install again');
            }
        } else {
            $response['error'] = $error = $this->setLocalization('Package name is not defined');
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

    public function smart_application_version_list_json(){

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
            $apps_list = new \SmartLauncherAppsManager();
            $app = $apps_list->getAppInfo($id);
            $app['conflicts'] = $apps_list->getConflicts($id, $version);
        } catch (\Exception $e){
            $response['error'] = $error = $this->setLocalization('Failed to get the list of versions of this applications') . '. ' . $e->getMessage();
            $app = FALSE;
        }
        if ($app !== FALSE) {
            $id = $app['id'];
            $response["data"] = array_values(array_filter(array_map(function($row) use ($version, $apps_list, $id){
                if ($version === FALSE || $version == $row['version']) {
                    /*$row['published'] = (int)strtotime($row['published']);*/
                    $row['published'] = $row['published'] < 0 ? 0 : $row['published'];
                    $row['conflicts'] = $apps_list->getConflicts($id, $row['version']);
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

    public function smart_application_version_save_option(){
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

            $result = $this->db->updateSmartApplication(array('options' => $option), $app_id);
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

    public function smart_application_version_install(){

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
                $apps = new \SmartLauncherAppsManager();
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
            } catch(\SmartLauncherAppsManagerException $e){
                $response['error'] = $this->setLocalization($e->getMessage());
            } catch(\SmartLauncherAppsManagerConflictException $e){
                $response['error'] = $this->setLocalization($e->getMessage());
                foreach($e->getConflicts() as $row){
                    list($app, $ver) = each($row);
                    $response['error'] .= "<br> $app $ver " . PHP_EOL;
                }
                $response['msg'] = $response['error'];
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

    public function smart_application_version_delete(){
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

            $app_db = $this->db->getSmartApplication(array('id' => $this->postData['id']));

            try{
                $apps = new \SmartLauncherAppsManager();
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

    public function smart_application_toggle_state(){

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

        try{
            $apps_list = new \SmartLauncherAppsManager();
            $conflicts = $apps_list->getConflicts($id, (!empty($postData['current_version']) ? $postData['current_version']: NULL));
            $response['conflicts'] = !empty($conflicts);
        } catch (\Exception $e){

        }

        unset($postData['id']);

        $result = $this->db->updateSmartApplication($postData, $id);
        if (is_numeric($result)) {
            $response['error'] = $error = '';
            if (!empty($postData['current_version'])) {
                $response['msg'] = $this->setLocalization('Activated. Current version') . ' ' . $postData['current_version'];
            }
            if ($result === 0) {
                $response['nothing_to_do'] = TRUE;
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

    public function smart_application_delete(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $response['action'] = 'manageList';

        if ($this->db->deleteSmartApplication($this->postData)) {
            $response['error'] = $error = '';
            $response['msg'] = $this->setLocalization('Application has been deleted');
        } else {
            $response['error'] = $error = $this->setLocalization('Failed to delete application.');
        }

        $response = $this->generateAjaxResponse($response);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function smart_application_reset_all(){
        if (!$this->isAjax) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $response = array('action' => 'manageList');
        $error = $this->setLocalization('Failed');

        try{
            $apps = new \SmartLauncherAppsManager();
            if ($apps->resetApps()){
                $error = '';
            }
        } catch (\Exception $e) {
            $response['msg'] = $error = $e->getMessage();
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

    private function getSmartApplicationListDropdownAttribute(){
        $attribute = array(
            array('name' => 'icon',             'title' => $this->setLocalization('Logo'),              'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Application'),       'checked' => TRUE),
            array('name' => 'type',             'title' => $this->setLocalization('Type'),              'checked' => TRUE),
            array('name' => 'category',         'title' => $this->setLocalization('Category'),          'checked' => TRUE),
            array('name' => 'current_version',  'title' => $this->setLocalization('Current version'),   'checked' => TRUE),
            array('name' => 'available_version','title' => $this->setLocalization('Actual version'),    'checked' => TRUE),
            /*array('name' => 'conflicts',    'title' => $this->setLocalization('Compatibility'),     'checked' => TRUE),*/
            array('name' => 'author',           'title' => $this->setLocalization('Author'),            'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('State'),             'checked' => TRUE),
            array('name' => 'description',      'title' => $this->setLocalization('Description'),       'checked' => TRUE),
            array('name' => 'operations',       'title' => $this->setLocalization('Operations'),        'checked' => TRUE)
        );
        return $attribute;
    }

    private function getSmartApplicationDetailDropdownAttribute(){
        $attribute = array(
            array('name' => 'version',          'title' => $this->setLocalization('Current version'),   'checked' => TRUE),
            array('name' => 'published',        'title' => $this->setLocalization('Date'),              'checked' => TRUE),
            array('name' => 'conflicts',        'title' => $this->setLocalization('Compatibility'),     'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('State'),             'checked' => TRUE),
            array('name' => 'operations',       'title' => $this->setLocalization('Operations'),        'checked' => TRUE)
        );
        return $attribute;
    }

    private function getSmartApplicationFields(){
        $attribute = array(
            'id' => 'L_A.`id` as `id`',
            'icon' => '"" as `icon`',
            'name' => '"" as `name`',
            'type' => 'L_A.`type` as `type`',
            'category' => 'L_A.`category` as `category`',
            'current_version' => 'L_A.`current_version` as `current_version`',
            'available_version' => '"" as `available_version`',
            'alias' => 'L_A.`alias` as `alias`',
            'conflicts' => '"" as `conflicts`',
            'author' => 'L_A.`author` as `author`',
            'status' => 'L_A.`status` as `status`',
            'localization' => 'L_A.`localization` as `localization`',
            'description' => '"" as `description`'
        );
        return $attribute; //L_A
    }

    private function getSmartApplicationFilters() {
        $return = array();

        if (!empty($this->data['filters'])){

            if (array_key_exists('type', $this->data['filters']) && $this->data['filters']['type'] != 0) {
                $return['`L_A`.`type`' . ($this->data['filters']['type'] == 1? '=': '<>')] = 'app';
            }


            if (array_key_exists('category', $this->data['filters']) && !is_numeric($this->data['filters']['category'])) {
                $return['`L_A`.`category`'] = $this->data['filters']['category'];
            }

            if (array_key_exists('installed', $this->data['filters']) && $this->data['filters']['installed']!= 0) {
                $return['installed'] = $this->data['filters']['installed'] - 1;
            }

            if (array_key_exists('status', $this->data['filters']) && $this->data['filters']['status']!= 0) {
                $return['`L_A`.`status`'] = $this->data['filters']['status'] - 1;
            }

            if (array_key_exists('conflicts', $this->data['filters']) && $this->data['filters']['conflicts']!= 0) {
                /*$return['conflicts'] = $this->data['filters']['conflicts'] - 1;*/
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }
}
