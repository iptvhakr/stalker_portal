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
        $this->logoHost = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/') . "misc/logos";
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
        $datatables['datatable-2'] = $this->index_datatable2_list_json();
        $datatables['datatable-3'] = $this->index_datatable3_list_json();

        $this->app['datatables'] = $datatables;

        $this->app['breadcrumbs']->addItem($this->setLocalization('Dashboard'));

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
        $error = $this->setLocalization('Failed');

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
        $error = $this->setLocalization('Failed');

        $data['data'] = array();
        $row = array('category'=>'', 'number' => '');

        $row['category'] = $this->setLocalization('Users online');
        $row['number'] = '<span class="txt-success">' . $this->db->get_users('online') . '</sapn>';
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Users offline');
        $row['number'] = '<span class="txt-danger">' . $this->db->get_users('offline') . '</sapn>';
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('TV channels');
        $row['number'] = $this->db->getCountForStatistics('itv', array('status' => 1));
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Films, serials');
        $row['number'] = $this->db->getCountForStatistics('video', array('status' => 1, 'accessed' => 1));
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Audio albums');
        $row['number'] = $this->db->getCountForStatistics('audio_albums', array('status' => 1));
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Karaoke songs');
        $row['number'] = $this->db->getCountForStatistics('karaoke', array('status' => 1));
        $data['data'][] = $row;

        $row['category'] = $this->setLocalization('Installed applications');
        $row['number'] = 0;
        $data['data'][] = $row;

        $data["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $error = '';
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }
    }

    public function index_datatable2_list_json(){
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
        $data['datatableID'] = 'datatable-2';
        $data['json_action_alias'] = 'index-datatable2-list-json';
        $error = $this->setLocalization('Failed');

        $data['data'] = array();

        $storages = $this->db->getStorages();

        foreach($storages as $storage){
            $row = array('storage'=> $storage['storage_name'], 'video' => '-', 'tv_archive' => '-', 'timeshift'=>'-', 'loading' => 0);
            $records = $this->db->getStoragesRecords($row['storage']);
            $total_storage_loading = $this->db->getStoragesRecords($row['storage'], TRUE);
            $row['loading'] = (int) $storage['max_online'] ? round(($total_storage_loading*100)/$storage['max_online'], 2) . "%": '-';
            foreach ($records as $record) {
                if ($record['now_playing_type'] == 2) {
                    $row['video'] = $record['count'];
                } elseif ($record['now_playing_type'] == 11) {
                    $row['tv_archive'] = $record['count'];
                } elseif ($record['now_playing_type'] == 14) {
                    $row['timeshift'] = $record['count'];
                }
            }
            $data['data'][] = $row;
        }

        $data["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $error = '';
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }
    }

    public function index_datatable3_list_json(){
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
        $data['datatableID'] = 'datatable-3';
        $data['json_action_alias'] = 'index-datatable3-list-json';
        $error = $this->setLocalization('Failed');

        $data['data'] = array();

        $streaming_servers = $this->db->getStreamServer();

        foreach($streaming_servers as $server){
            $user_sessions = $this->db->getStreamServerStatus($server['id'], TRUE);
            $row = array(
                'server'=> $server['name'],
                'sessions' => $user_sessions,
                'loading' => ((int) $server['max_sessions'] > 0 ? round(($user_sessions * 100)/$server['max_sessions'], 2)."%" : "&infin;")
            );
            $data['data'][] = $row;
        }

        $data["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        if ($this->isAjax) {
            $error = '';
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }
    }

    public function index_datatable4_list_json(){

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
        $data['datatableID'] = 'datatable-4';
        $data['json_action_alias'] = 'index-datatable4-list-json';
        $error = $this->setLocalization('Failed');

        $data['data'] = array();

        $types = array('tv' => 1,'video' => 2, 'karaoke' => 3, 'audio' => 4, 'radio' => 5);
        $all_sessions = 0;

        foreach($types as $key=>$type){
            $data['data'][$key] = array();
            $data['data'][$key]['sessions'] = $this->db->getCurActivePlayingType($type);
            $all_sessions += $data['data'][$key]['sessions'];
        }

        $data['data'] = array_map(function($row) use ($all_sessions){
            settype($row['sessions'], 'int');
            $row['percent'] = ($all_sessions)? round(($row['sessions'] * 100)/$all_sessions,0): 0;
            return $row;
        }, $data['data']);

        $data['data']['all_sessions'] = (int)$all_sessions;

        if ($this->isAjax) {
            $error = '';
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }

    }

    public function index_datatable5_list_json(){

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
        $data['datatableID'] = 'datatable-5';
        $data['json_action_alias'] = 'index-datatable5-list-json';
        $error = $this->setLocalization('Failed');

        $data['data'] = $this->db->getUsersActivity();

        $reseller = (int) $this->app['reseller'];

        $data['data'] = array_map(function($row) use ($reseller){
            settype($row['time'], 'int');
            $row['users_online'] = @json_decode($row['users_online'], TRUE);
            $key = empty($reseller) ? 'total': $reseller;
            $row['users_online'] = (is_array($row['users_online']) && array_key_exists($key, $row['users_online'])) ? (int) $row['users_online'][$key] : 0;
            return array($row['time'], $row['users_online']);
        }, $data['data']);

        if ($this->isAjax) {
            $error = '';
            $data = $this->generateAjaxResponse($data);
            return new Response(json_encode($data), (empty($error) ? 200 : 500));
        } else {
            return $data;
        }

    }

    //------------------------ service method ----------------------------------
}
