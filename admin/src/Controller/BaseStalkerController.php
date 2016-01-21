<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class BaseStalkerController {

    protected $app;
    protected $request;
    protected $baseDir;
    protected $baseHost;
    protected $workHost;
    protected $workURL;
    protected $refferer;
    protected $Uri;
    protected $method;
    protected $isAjax;
    protected $data;
    protected $postData;
    protected $db;
    protected $admin;
    protected $session;
    protected $access_level = 0;
    protected $access_levels = array(
        0 => 'denied',
        1 => 'view',
        2 => 'edit',
        3 => 'edit',
        4 => 'action',
        5 => 'all',
        6 => 'all',
        7 => 'all',
        8 => 'all',
    );

    public function __construct(Application $app, $modelName = '') {
        $this->app = $app;
        $this->request = $app['request'];
        
        if (session_id()) {
            session_write_close();
            $this->app['request']->getSession()->save();
        }
        $this->app['request']->getSession()->start();
        $this->admin = \Admin::getInstance();
        
        $this->app['userlogin'] = $this->admin->getLogin();
                
        $this->baseDir = rtrim(str_replace(array("src", "Controller"), '', __DIR__), '//');
        $this->getPathInfo();
        $this->setRequestMethod();
        $this->setAjaxFlag();
        $this->getData();
        $this->setDataTablePluginSettings();
        
        $modelName = "Model\\" . (empty($modelName) ? 'BaseStalker' : str_replace(array("\\", "Controller"), '', $modelName)) . 'Model';
        $this->db = FALSE;
        $modelName = (class_exists($modelName)? $modelName: 'Model\BaseStalkerModel');
        if (class_exists($modelName)) {
            $this->db = new $modelName;
            if (!($this->db instanceof $modelName)) {
                $this->db = FALSE;
            }
        }
        $uid = $this->admin->getId(); 
        if ($this->db !== FALSE && !empty($uid)) {
            $this->app['userTaskMsgs'] = $this->db->getCountUnreadedMsgsByUid($uid);
        }

        $this->app['user_id'] = $uid;

        $this->app['reseller'] = $this->admin->getResellerID();
        $this->db->setReseller($this->app['reseller']);
        $this->db->setAdmin($this->app['user_id'], $this->app['userlogin']);

        $this->saveFiles = $app['saveFiles'];
        $this->setSideBarMenu();
        $this->setTopBarMenu();
        
        if($this->app['userlogin'] == 'admin'){
            $this->access_level = 8;
        } else {
            $this->setAccessLevel();
        }
        if (isset($this->data['set-dropdown-attribute'])) {            
            $this->set_dropdown_attribute();
            exit;
        }
    }

    protected function getTemplateName($method_name) {
        return str_replace(array(__NAMESPACE__, '\\', '::'), array('', '', '_'), $method_name) . ".twig";
    }

    private function getPathInfo() {
        $tmp = explode('/', trim($this->request->getPathInfo(), '/'));
        $this->app['controller_alias'] = $tmp[0];
        $this->app['action_alias'] = (count($tmp) == 2) ? $tmp[1] : '';
        $this->baseHost = $this->request->getSchemeAndHttpHost();
        $this->workHost = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/');
        $this->app['workHost'] = $this->workHost;
        $this->Uri = $this->app['request']->getUri();
        $controller = (!empty($this->app['controller_alias']) ? "/" . $this->app['controller_alias'] : '');
        $action = (!empty($this->app['action_alias']) ? "/" . $this->app['action_alias'] : '');
        $workUrl = explode("?", str_replace(array($action, $controller), '', $this->Uri));
        $this->workURL = $workUrl[0];
        $this->app['breadcrumbs']->addItem('Stalker', $this->workURL);
        $this->refferer = $this->request->server->get('HTTP_REFERER');
    }

    private function setSideBarMenu() {
        $side_bar  = json_decode(str_replace(array("_(", ")"), '', file_get_contents($this->baseDir . '/json_menu/menu.json')), TRUE);     
        $this->setControllerAccessMap();
        $this->cleanSideBar($side_bar);
        $this->app['side_bar'] = $side_bar;
    }

    private function setTopBarMenu() {
        $top_bar  = json_decode(str_replace(array("_(", ")"), '', file_get_contents($this->baseDir . '/json_menu/top_menu.json')), TRUE);
        if (!empty($this->app['userlogin'])) {
            $top_bar[1]['add_params'] = '<span class="hidden-xs">"'. $this->app['userlogin'] .'"</span>';
            if (!empty($this->app['userTaskMsgs'])) {
                $top_bar[1]['action'][1]['add_params'] = '<span class="hidden-xs badge">'. $this->app['userTaskMsgs'] .'</span>';
            }
        }
        
        $this->setControllerAccessMap();
        $this->cleanSideBar($top_bar);
        $this->app['top_bar'] = $top_bar;
    }
	
    private function setRequestMethod() {
        $this->method = $this->request->getMethod();
    }

    private function setAjaxFlag() {
        $this->isAjax = $this->request->isXmlHttpRequest();
    }

    private function getData() {
        $this->data = $this->request->query->all();
        $this->postData = $this->request->request->all();
    }

    public function setLocalization($source = array(), $fieldname = '', $number = FALSE, $params = array()) {
        if (!empty($source)) {
            if (!is_array($source)) {
                $translate = '';
                if ($number === FALSE) {
                    $translate =  $this->app['translator']->trans($source, $params);
                } else {
                    $translate =  $this->app['translator']->transChoice($source, $number, $params);
                }
                return (!empty($translate) ? $translate: $source);
            } elseif (array_key_exists($fieldname, $source) && is_string($source[$fieldname])) {
                $source[$fieldname] = $this->setLocalization($source[$fieldname], $fieldname, $number, $params);
            } else {
                while (list($key, $row) = each($source)) {
                    $source[$key] = $this->setLocalization($row, $fieldname, $number, $params);
                }
            }
            return $source;
        }
        return FALSE;
    }

    public function getFieldFromArray($array, $field) {
        $return_array = array();
        if (is_array($array) && !empty($array)) {
            $tmp = array_values($array);
            if (!empty($tmp) && is_array($tmp[0]) && array_key_exists($field, $tmp[0])) {
                foreach ($array as $key => $value) {
                    $return_array[] = $value[$field];
                }
            }
        }
        return $return_array;
    }

    public function generateAjaxResponse($data = array(), $error = '') {
        $response = array();

        if (empty($error) && !empty($data)) {
            $response['success'] = TRUE;
            $response['error'] = FALSE;
        } else {
            $response['success'] = FALSE;
            $response['error'] = $error;
        }

        return array_merge($response, $data);
    }

    protected function checkAuth() {
        if (empty($this->app['controller_alias']) || ($this->app['action_alias'] != 'register' && $this->app['action_alias'] != 'login')) {
            if (!$this->admin->isAuthorized()) {
                if ($this->isAjax) {
                    $response = $this->generateAjaxResponse(array(), 'Need authorization');
                    return new Response(json_encode($response), 401);
                } else {
                    return $this->app->redirect(trim($this->workURL, '/') . '/login', 302);
                }
            }

            $parent_access = $this->getParentActionAccess();
            
            if(
                $this->access_level < 1 ||
                (!empty($this->postData) && !$this->isAjax && $this->access_level < 2) ||
                (!empty($this->postData) && $this->isAjax && $this->access_level < 4 && $parent_access === FALSE) ||
                ($parent_access !== FALSE && !$parent_access)
            ) {
                if ($this->isAjax) {
                    $response = $this->generateAjaxResponse(array('msg' => $this->setLocalization('Access denied')), 'Access denied');
                    return new Response(json_encode($response), 403);
                } else {
                    return $this->app['twig']->render("AccessDenied.twig");
                }
            } 
        }
    }

    protected function getCoverFolder($id) {

        $dir_name = ceil($id / 100);
        $dir_path = realpath(PROJECT_PATH . '/../' . \Config::getSafe('screenshots_path', 'screenshots/')) . '/' . $dir_name;
        if (!is_dir($dir_path)) {
            umask(0);
            if (!mkdir($dir_path, 0777)) {
                return -1;
            } else {
                return $dir_path;
            }
        } else {
            return $dir_path;
        }
    }

    protected function transliterate($st) {

        $st = trim($st);
        $replace = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ж' => 'g', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
            'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
            'т' => 't', 'у' => 'u', 'ф' => 'f', 'ы' => 'i', 'э' => 'e', 'А' => 'A',
            'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ж' => 'G',
            'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Ы' => 'I', 'Э' => 'E', 'ё' => "yo", 'х' => "h",
            'ц' => "ts", 'ч' => "ch", 'ш' => "sh", 'щ' => "shch", 'ъ' => '', 'ь' => '',
            'ю' => "yu", 'я' => "ya", 'Ё' => "Yo", 'Х' => "H", 'Ц' => "Ts", 'Ч' => "Ch",
            'Ш' => "Sh", 'Щ' => "Shch", 'Ъ' => '', 'Ь' => '', 'Ю' => "Yu", 'Я' => "Ya",
            ' ' => "_", '!' => "", '?' => "", ',' => "", '.' => "", '"' => "", '\'' => "",
            '\\' => "", '/' => "", ';' => "", ':' => "", '«' => "", '»' => "", '`' => "",
            '-' => "-", '—' => "-"
        );
        $st = strtr($st, $replace);

        $st = preg_replace("/[^a-z0-9_-]/i", "", $st);

        return $st;
    }

    protected function prepareDataTableParams($params = array(), $drop_columns = array()) {
        $query_param = array(
            'select' => array(),
            'like' => array(),
            'order' => array(),
            'limit' => array('offset' => 0, 'limit' => FALSE)
        );
        if (empty($params) || !is_array($params) || !array_key_exists('columns', $params)) {
            return $query_param;
        }
        if (array_key_exists('length', $params)) {
            $query_param['limit']['limit'] = $params['length'];
        }
        if (array_key_exists('start', $params)) {
            $query_param['limit']['offset'] = $params['start'];
        }
        if (!empty($params['order'])){
            foreach ($params['order'] as $val) {
                $column = $params['columns'][(int) $val['column']];

                $direct = $val['dir'];
                $col_name = !empty($column['name']) ? $column['name'] : (!empty($column['data']) ? $column['data'] : FALSE);

                if ($col_name === FALSE || in_array($col_name, $drop_columns)) {
                    continue;
                }
                if ($column['orderable']) {
                    $query_param['order'][$col_name] = $direct;
                }
            }
        }

        if (!empty($params['columns'])) {
            foreach ($params['columns'] as $key => $column) {
                $col_name = !empty($column['name']) ? $column['name'] : (!empty($column['data']) ? $column['data'] : FALSE);
                if ($col_name === FALSE || in_array($col_name, $drop_columns)) {
                    continue;
                }
                $query_param['select'][] = $col_name;
                if (!empty($column['searchable']) && $column['searchable'] == 'true' && !empty($params['search']['value']) && $params['search']['value'] != "false") {
                    $query_param['like'][$col_name] = "%" . $params['search']['value'] . "%";
                }
            }
        }

        return $query_param;
    }

    protected function cleanQueryParams(&$data, $filds_for_delete = array(), $fields_for_replace = array()) {
        reset($data);
        while (list($key, $block) = each($data)) {
            foreach ($filds_for_delete as $field) {
                if (array_key_exists($field, $block)) {
                    $new_name = str_replace(" as `$field`", '', $fields_for_replace[$field]);
                    if (array_key_exists($field, $fields_for_replace) && !is_numeric($new_name)) {
                        $data[$key][$new_name] = $data[$key][$field];
                    }
                    unset($data[$key][$field]);
                } elseif (($search = array_search($field, $block)) !== FALSE && array_search($fields_for_replace[$field], $block) === FALSE) {
                    if (array_key_exists($field, $fields_for_replace)) {
                        $data[$key][] = $fields_for_replace[$field];
                    }
                    unset($data[$key][$search]);
                }
            }
        }
    }

    protected function orderByDeletedParams(&$data, $param) {
        foreach ($param as $field => $direct) {
            $direct = strtoupper($direct) == 'ASC' ? 1 : -1;
            usort($data, function ($a, $b) use ($field, $direct) {
                return (($a[$field] >= $b[$field]) ? -1 : 1) * $direct;
            });
        }
    }

    protected function checkDisallowFields(&$data, $fields = array()) {
        $return = array();
        while (list($key, $block) = each($data)) {
            foreach ($fields as $field) {
                if (array_key_exists($field, $block)) {
                    $return[$key][$field] = $block[$field];
                    unset($data[$key][$field]);
                } elseif (($search = array_search($field, $block)) !== FALSE) {
                    $return[$key][$field] = $block[$search];
                    unset($data[$key][$search]);
                }
            }
        }
        return $return;
    }

    private function setAccessLevel() {
        $this->setControllerAccessMap();
        $controller_alias = !empty($this->app['controller_alias']) ? $this->app['controller_alias']: 'index';
        if (array_key_exists($controller_alias, $this->app['controllerAccessMap']) && $this->app['controllerAccessMap'][$controller_alias]['access']) {
            if ($this->app['action_alias'] == '' || $this->app['action_alias'] == 'index') {
                $this->access_level = $this->app['controllerAccessMap'][$controller_alias]['access'];
                return;
            } elseif (array_key_exists($this->app['action_alias'], $this->app['controllerAccessMap'][$controller_alias]['action'])) {
                $parent_access = $this->getParentActionAccess();
                $this->access_level = ($parent_access !== FALSE) ? $parent_access: $this->app['controllerAccessMap'][$controller_alias]['action'][$this->app['action_alias']]['access'];
                return;
            }
        }
        $this->access_level = 0;
    }
    
    private function setControllerAccessMap(){
        if (empty($this->app['controllerAccessMap'])) {
            $is_admin = (!empty($this->app['userlogin']) && $this->app['userlogin'] == 'admin');
            $gid = ($is_admin)?'':$this->admin->getGID(); 
            $map = array();
            $tmp_map = $this->db->getControllerAccess($gid, $this->app['reseller']);
            foreach ($tmp_map as $row) {
                if(!array_key_exists($row['controller_name'], $map)) {
                    $map[$row['controller_name']]['access'] = (!$is_admin) ? $this->getDecFromBin($row): '8';
                    if ($map[$row['controller_name']]['access'] == 0) {
                        continue;
                    }
                    $map[$row['controller_name']]['action'] = array();
                }
                if ((!empty($row['action_name']) && $row['action_name'] != 'index') || $row['controller_name'] != 'index') {
                    $map[$row['controller_name']]['action'][$row['action_name']]['access'] = (!$is_admin) ? $this->getDecFromBin($row): '8';
                }
            }
            $this->app['controllerAccessMap'] = $map;
        }
    }
    
    private function getDecFromBin($row){
        $key = $row['action_access'].$row['edit_access'].$row['view_access'];
        return bindec($row['action_access'].$row['edit_access'].$row['view_access']);
    }
    
    private function cleanSideBar(&$side_bar) {
        if (empty($this->app['controllerAccessMap'])) {
            $this->setControllerAccessMap();
        }
        $dont_remove = (!empty($this->app['userlogin']) && $this->app['userlogin'] == 'admin');
        while(list($key, $row) = each($side_bar)){
            $controller = str_replace('_', '-', $row['alias']);
            $side_bar[$key]['name'] = $row['name'] = $this->setLocalization($row['name']);
            if ($this->app['controller_alias'] == $controller) {
                    $this->app['breadcrumbs']->addItem($row['name'], $this->workURL . "/$controller");
                }
            if ((!$dont_remove && !array_key_exists($controller, $this->app['controllerAccessMap']))
                || (array_key_exists($controller, $this->app['controllerAccessMap']) && $this->app['controllerAccessMap'][$controller]['access'] == 0)) {
                unset($side_bar[$key]);
                continue;
            }
            while(list($key_a, $row_a) = each($row['action'])){
                $side_bar[$key]['action'][$key_a]['name'] = $row_a['name'] = $this->setLocalization($row_a['name']);
                $action = str_replace('_', '-', $row_a['alias']);
                if ($this->app['controller_alias'] == $controller && $this->app['action_alias'] == $action) {
                    $this->app['breadcrumbs']->addItem($row_a['name'], $this->workURL . "/$controller/$action");
                }
                if ((!$dont_remove && !array_key_exists($action, $this->app['controllerAccessMap'][$controller]['action']))
                    || (array_key_exists($action, $this->app['controllerAccessMap'][$controller]['action']) && $this->app['controllerAccessMap'][$controller]['action'][$action]['access'] == 0)) {
                    unset($side_bar[$key]['action'][$key_a]);
                }
            }
        }
    }
    
    protected function infliction_array($dest = array(), $source = array()) {
        if (is_array($dest)) {
            while(list($d_key, $d_row) = each($dest)){
                if (is_array($source)) {
                    if (array_key_exists($d_key, $source)) {
                        $dest[$d_key] = $this->infliction_array($d_row, $source[$d_key]);
                    } else {
                        continue;
                    }
                } else{
                    return $dest; 
                }
            }
        } elseif (!is_array($source)) {
            return $source;
        }
        return $dest;
    }
    
    protected function checkDropdownAttribute(&$attribute, $filters = ''){
        
        $param = array();
        $param['controller_name'] = $this->app['controller_alias'];
        $param['action_name'] = (empty($this->app['action_alias']) ? 'index': $this->app['action_alias']).$filters;
        $param['admin_id'] = $this->admin->getId();
        
        $base_attribute = $this->db->getDropdownAttribute($param);
        if (empty($base_attribute)) {
            return $attribute;
        }
        $dropdown_attributes = unserialize($base_attribute['dropdown_attributes']);
        foreach ($dropdown_attributes as $key => $value) {
            reset($attribute);
            while (list($num, $row) = each($attribute)){
                if ($row['name'] == $key) {
                    $attribute[$num]['checked'] = ($value == 'true');
                    break;
                }
            }
        }
    }

    protected function setDataTablePluginSettings(){
        $this->app['datatable_lang_file'] = "./plugins/datatables/lang/" . str_replace('utf8', 'json', $this->app['locale']);
    }

    protected function getParentActionAccess(){
        $return = FALSE;
        if ($this->app['userlogin'] !== 'admin' && $this->isAjax && preg_match("/-json$/", $this->app['action_alias'])) {
            $action_alias = preg_replace(array('/-composition/i', '/-datatable\d/i'), '', $this->app['action_alias'], 1);
            $parent_1 = str_replace('-json', '', $action_alias);
            $parent_2 = str_replace('-list-json', '', $action_alias);
            $parent_access = 0;
            if ($parent_1 == $this->app['controller_alias'] || $parent_2 == $this->app['controller_alias']) {
                $parent_access = $this->app['controllerAccessMap'][$this->app['controller_alias']]['access'];
            } elseif (array_key_exists($parent_1, $this->app['controllerAccessMap'][$this->app['controller_alias']]['action'])){
                $parent_access = $this->app['controllerAccessMap'][$this->app['controller_alias']]['action'][$parent_1]['access'];
            } elseif (array_key_exists($parent_2, $this->app['controllerAccessMap'][$this->app['controller_alias']]['action'])) {
                $parent_access = $this->app['controllerAccessMap'][$this->app['controller_alias']]['action'][$parent_2]['access'];
            }
            $return = (int) ($parent_access > 0);
        }
        return $return;
    }

    protected function mb_ucfirst($str) {
        $fc = mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8');
        return $fc.mb_substr($str, 1, mb_strlen($str), 'UTF-8');
    }

    protected function getUCArray($array = array(), $field = ''){
        reset($array);
        while(list($key, $row) = each($array)){
            if (!empty($field)) {
                $row[$field] = $this->mb_ucfirst($row[$field]);
            } else {
                $row = $this->mb_ucfirst($row);
            }
            $array[$key] = $row;
        }
        return $array;
    }
}
