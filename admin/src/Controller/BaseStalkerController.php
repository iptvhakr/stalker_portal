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
    protected $workURL;
    protected $Uri;
    protected $method;
    protected $isAjax;
    protected $data;
    protected $postData;
    protected $db;
    protected $admin;
    protected $session;

    public function __construct(Application $app, $modelName = '') {
        $this->app = $app;
        $this->request = $app['request'];
        if (session_id()) {
            session_write_close();
            $this->app['request']->getSession()->save();
        }
        $this->app['request']->getSession()->start();
        $this->admin = \Admin::getInstance();
        $this->baseDir = rtrim(str_replace(array("src", "Controller"), '', __DIR__), '//');
        $this->getPathInfo();
        $this->setRequestMethod();
        $this->setAjaxFlag();
        $this->getData();

        $modelName = "Model\\" . (empty($modelName) ? 'BaseStalker' : str_replace(array("\\", "Controller"), '', $modelName)) . 'Model';
        $this->db = FALSE;
        if (class_exists($modelName)) {
            $this->db = new $modelName;
            if (!($this->db instanceof $modelName)) {
                $this->db = FALSE;
            }
        }

        $this->saveFiles = $app['saveFiles'];
        $this->setSideBarMenu();
    }

    protected function getTemplateName($metod_name) {
        return str_replace(array(__NAMESPACE__, '\\', '::'), array('', '', '_'), $metod_name) . ".twig";
    }

    private function getPathInfo() {
        $tmp = explode('/', trim($this->request->getPathInfo(), '/'));
        $this->app['controller_alias'] = $tmp[0];
        $this->app['action_alias'] = (count($tmp) == 2) ? $tmp[1] : '';
        $this->baseHost = $this->request->getSchemeAndHttpHost();
        $this->Uri = $this->app['request']->getUri();
        $controller = (!empty($this->app['controller_alias']) ? "/" . $this->app['controller_alias'] : '');
        $action = (!empty($this->app['action_alias']) ? "/" . $this->app['action_alias'] : '');
        $workUrl = explode("?", str_replace(array($action, $controller), '', $this->Uri));
        $this->workURL = $workUrl[0];
    }

    private function setSideBarMenu() {
        $this->app['side_bar'] = json_decode(file_get_contents($this->baseDir . '/menu.json'), TRUE);
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

    protected function setLocalization($source = array(), $fieldname = '') {
        if (!empty($source)) {
            if (!is_array($source)) {
                return _($source);
            } else {
                $fieldname = (empty($fieldname)) ? array_keys($source[0]) : array($fieldname);
                while (list($key, $row) = each($source)) {
                    foreach ($fieldname as $f_name) {
                        $source[$key][$f_name] = _($source[$key][$f_name]);
                    }
                }
            }
            return $source;
        }
        return FALSE;
    }

    public function getFieldFromArray($array, $field) {
        $return_array = array();
        $tmp = array_values($array);
        if (!empty($tmp) && is_array($tmp[0]) && array_key_exists($field, $tmp[0])) {
            foreach ($array as $key => $value) {
                $return_array[] = $value[$field];
            }
        }
        return $return_array;
    }

    public function gererateAjaxResponse($data = array(), $error = '') {
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
                    $response = $this->gererateAjaxResponse(array(), 'Need authorization');
                    return new Response(json_encode($response), 401);
                } else {
                    return $this->app->redirect($this->workURL . '/login', 302);
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

        return $query_param;
    }

    protected function cleanQueryParams(&$data, $filds_for_delete = array(), $fields_for_replace = array()) {
        reset($data);
        while (list($key, $block) = each($data)) {
            foreach ($filds_for_delete as $field) {
                if (array_key_exists($field, $block)) {
                    if (array_key_exists($field, $fields_for_replace)) {
                        $data[$key][str_replace(" as `$field`", '', $fields_for_replace[$field])] = $data[$key][$field];
                    }
                    unset($data[$key][$field]);
                } elseif (($search = array_search($field, $block)) !== FALSE) {
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

}
