<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class IndexController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->logoHost = $this->baseHost . "/stalker_portal/misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $datatables['datatable-1'] = $this->index_datatable1_list_json();

        $this->app['datatables'] = $datatables;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function set_dropdown_attribute() {

        if (!$this->isAjax || empty($this->postData)) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'dropdownAttributesAction';
        $error = $this->setLocalization('Не удалось');

        $aliases = trim(str_replace($this->workURL, '', $this->refferer), '/');
        $aliases = array_pad(explode('/', $aliases), 2, 'index');
        
        $aliases[1] = urldecode($aliases[1]);
        $filters = explode('?', $aliases[1]);
        $aliases[1] = $filters[0];
        if (count($filters) > 1 && (!empty($this->data['set-dropdown-attribute']) && $this->data['set-dropdown-attribute'] == 'with-button-filters')) {
            $filters[1] = explode("&", $filters[1]);
            $filters[1] = $filters[1][0];
            $filters[1] = str_replace(array('=', '_'), '-', $filters[1]);
            $filters[1] = preg_replace('/(\[[^\]]*\])/i', '', $filters[1]);
            $aliases[1] .= "-$filters[1]";
        }
//        print_r($filters);exit;
        $param = array();
        $param['controller_name'] = $aliases[0];
        $param['action_name'] = $aliases[1];
        $param['admin_id'] = $this->admin->getId();
        $this->db->deleteDropdownAttribute($param);

        $param['dropdown_attributes'] = serialize($this->postData);
        $id = $this->db->insertDropdownAttribute($param);
        
        if ($id && $id != 0) {
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);
        if (empty($error)) {
            header($_SERVER['SERVER_PROTOCOL'] . " 200 OK", true, 200);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($response);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }

        exit;
    }

    public function index_datatable1_list_json(){
        $data = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $data['action'] = 'datatableReload';
        $data['datatableID'] = 'datatable-1';
        $data['json_action_alias'] = 'index-datatable1-list-json';
        $error = $this->setLocalization('Не удалось');

        $data['data'] = array();
        $row = array('category'=>'', 'number' => '');

        $row['category'] = $this->setLocalization('Users online');
        $row['number'] = '<span class="txt-success">' . $this->db->get_users('online') . '</sapn>';
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Users offline');
        $row['number'] = '<span class="txt-danger">' . $this->db->get_users('offline') . '</sapn>';
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('TV channels');
        $row['number'] = $this->db->getCountForStatistics('itv');
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Films, serials');
        $row['number'] = $this->db->getCountForStatistics('video');
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Audio albums');
        $row['number'] = $this->db->getCountForStatistics('audio_albums');
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Karaoke songs');
        $row['number'] = $this->db->getCountForStatistics('karaoke');
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Installed applications');
        $row['number'] = 0;
        $data['data'][] = $row;

        $data["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }
    }

    //------------------------ service method ----------------------------------
}
