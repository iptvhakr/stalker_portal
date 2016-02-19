<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class KaraokeController extends \Controller\BaseStalkerController {

    protected $allStatus = array();
    private $allProtocols = array(array('id' => "nfs", 'title' => 'NFS'), array('id' => "http", 'title' => 'HTTP'), array('id' => "custom", 'title' => 'Custom URL'));

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allStatus = array(
            array('id' => 1, 'title' => $this->setLocalization('Unpublished')),
            array('id' => 2, 'title' => $this->setLocalization('Published'))
        );
    }
    
    // ------------------- action method ---------------------------------------
    
    public function index() {
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->app['allProtocols'] = $this->allProtocols;
        $this->app['allStatus'] = $this->allStatus;
        
        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->karaoke_list_json();
        
        $this->app['allKaraoke'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
       
    //----------------------- ajax method --------------------------------------

    public function karaoke_list_json($param = array()) {
        
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setKaraokeModal'
        );
        
        $filds_for_select = array(
            "id" => "`karaoke`.`id` as `id`",
            "name" => "`karaoke`.`name` as `name`",
            "singer" => "`karaoke`.`singer` as `singer`",
            "added" => "CAST(`karaoke`.`added` AS CHAR) as `added`",
            "protocol" => "`karaoke`.`protocol` as `protocol`",
            "rtsp_url" => "`karaoke`.`rtsp_url` as `rtsp_url`",
            "media_claims" => "CONCAT_WS(' / ', if(`media_claims`.`sound_counter`, `media_claims`.`sound_counter`, 0), if(`media_claims`.`video_counter`, `media_claims`.`video_counter`, 0)) as `media_claims`",
            "done" => "`karaoke`.`done` as `done`",
            "accessed" => "`karaoke`.`accessed` as `accessed`",
            "status" => "`karaoke`.`status` as `status`"
        );
                
        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $like_filter = array();
        $filter = $this->getKaraokeFilters($like_filter);
        
        if (empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = $like_filter;
        } elseif (!empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = array_merge($query_param['like'], $like_filter);
        }
        
        $query_param['where'] = array_merge($query_param['where'], $filter);

        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'karaoke.id as id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        if (array_key_exists('accessed', $query_param['order']) && !array_key_exists('status', $query_param['order']) ){
            $query_param['order']['status'] = 'DESC';
        }
        
        if(!array_key_exists('status', $query_param['select']) ){
            $query_param['select'][] = "`karaoke`.`status` as `status`";
        }
        
        if (array_key_exists('karaokeid', $param)) {
            $query_param['where']['karaoke.id'] = $param['karaokeid'];
        }
        
        if (empty($query_param['order'])) {
            $query_param['order']['added'] = 'DESC';
        }
        
        
        $response['recordsTotal'] = $this->db->getTotalRowsKaraokeList();
        $response["recordsFiltered"] = $this->db->getTotalRowsKaraokeList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getKaraokeList($query_param);
        
        $response['data'] = array_map(function($row){
            $row['added'] = (int) strtotime($row['added']);
            return $row;
        }, $response['data']);
        
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function save_karaoke() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'manageKaraoke';
        $karaoke = array($this->postData);
        $error = $this->setLocalization('error');
        if (empty($this->postData['id'])) {
            $operation = 'insertKaraoke';
            $karaoke[0]['added'] = 'NOW()';
            $karaoke[0]['add_by'] = $this->admin->getId();

            if ($karaoke[0]['protocol'] == 'custom'){
                $karaoke[0]['status'] = 1;
            }

        } else {
            $operation = 'updateKaraoke';
            $karaoke['id'] = $this->postData['id'];
        }
        unset($karaoke[0]['id']);

        if ((!empty($this->postData['protocol']) && $this->postData['protocol'] != 'custom') || (!empty($this->postData['rtsp_url']) && preg_match('/^(\w+\s)?\w+\:\/\/.*$/i', $this->postData['rtsp_url']))) {
            $result = call_user_func_array(array($this->db, $operation), $karaoke);
            if (is_numeric($result)) {
                $error = '';
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }
        } else {
            $data['msg'] = $this->setLocalization('Invalid format links');
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_karaoke() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['karaokeid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageKaraoke';
        $data['id'] = $this->postData['karaokeid'];
        $error = '';    
        $this->db->deleteKaraoke(array('id' => $this->postData['karaokeid']));
        
        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_karaoke_done() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['karaokeid']) || !array_key_exists('done', $this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageKaraoke';
        $data['id'] = $this->postData['karaokeid'];
        $this->db->updateKaraoke(array('done' => (int)(!((bool) $this->postData['done'])), 'done_time' => 'NOW()'), $this->postData['karaokeid']);
        $error = '';    
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_karaoke_accessed() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['karaokeid']) || !array_key_exists('accessed', $this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageKaraoke';
        $data['id'] = $this->postData['karaokeid'];
        $error = $this->setLocalization('Failed');

        $good_storages = array();
        $media_id = intval($this->postData['karaokeid']);
        if (empty($_SERVER['TARGET'])) {
            $_SERVER['TARGET'] = 'ADM';
        }

        $where = array('karaoke.id'=>$this->postData['karaokeid']);
        $item = $this->db->getKaraokeList(array('select'=> array("*", "karaoke.id as id"), "where" => $where));

        ob_start();
        if (($master = new \KaraokeMaster()) && $item[0]['protocol'] != 'custom'){
            $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, true);
            $this->db->updateKaraoke(array('status' => (int)(count($good_storages) > 0)), $this->postData['karaokeid']);
        }
        ob_end_clean();

        if (!empty($good_storages) || $item[0]['protocol'] == 'custom') {

            if ($item[0]['protocol'] == 'custom' && empty($item[0]['rtsp_url'])){
                $error = $this->setLocalization('You can not publishing record with protocol - "custom", and with empty field - URL');
            }else{

                $this->db->updateKaraoke(array('accessed' => (int)(!((bool) $this->postData['accessed'])), 'added' => 'NOW()'), $this->postData['karaokeid']);
                if ($item[0]['protocol'] != 'custom') {
                    if ((int)(!((bool)$this->postData['accessed'])) == 1) {
                        @chmod(KARAOKE_STORAGE_DIR . '/' . $this->postData['karaokeid'] . '.mpg', 0444);
                    } else {
                        @chmod(KARAOKE_STORAGE_DIR . '/' . $this->postData['karaokeid'] . '.mpg', 0666);
                    }
                }

                $error = '';
            }
        } else {
            $error = $this->setLocalization('File unavailable and cannot be published');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_karaoke_source() {

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['karaokeid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkSourceKaraoke';
        $data['id'] = $this->postData['karaokeid'];
        $data['base_info'] = $this->setLocalization('Information not available');
        $error = $this->setLocalization('Error');
        $media_id = intval($this->postData['karaokeid']);

        $karaoke_data = $this->db->getKaraokeList(array('where' => array('karaoke.id' => $media_id)));
        if (!empty($karaoke_data) && $karaoke_data[0]['protocol'] != 'custom') {
            if (empty($_SERVER['TARGET'])) {
                $_SERVER['TARGET'] = 'ADM';
            }
            ob_start();
            if ($master = new \KaraokeMaster()){
                $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, true);

                $this->db->updateKaraoke(array('status' => (int)(count($good_storages) > 0)), $this->postData['karaokeid']);

                $arr = array();

                foreach ($good_storages as $name => $val){
                    $arr[] = array(
                        'storage_name' => $name,
                        'file'         => $media_id.'.mpg',
                    );
                }
                $error = '';
                $data['base_info'] = $arr;
            }
            ob_end_clean();
        } else {
            $data['msg'] = $data['base_info'];
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------
    
    private function getDropdownAttribute() {
        return array(
            array('name'=>'id',             'title'=>$this->setLocalization('ID'),      'checked' => TRUE),
            array('name'=>'name',           'title'=>$this->setLocalization('Title'),   'checked' => TRUE),
            array('name'=>'singer',         'title'=>$this->setLocalization('Artist'),  'checked' => TRUE),
            array('name'=>'added',          'title'=>$this->setLocalization('Added'),   'checked' => TRUE),
            array('name'=>'protocol',       'title'=>$this->setLocalization('Protocol'),'checked' => TRUE),
            array('name'=>'rtsp_url',       'title'=>$this->setLocalization('URL'),     'checked' => TRUE),
            array('name'=>'media_claims',   'title'=>$this->setLocalization('Complaints'),'checked' => TRUE),
            array('name'=>'done',           'title'=>$this->setLocalization('Tasks'),   'checked' => TRUE),
            array('name'=>'accessed',       'title'=>$this->setLocalization('Conditions'),'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operations'),'checked' => TRUE)
        );
    }
    
    private function getKaraokeFilters(&$like_filter) {
        $return = array();

        if (!empty($this->data['filters'])){
            if (array_key_exists('status', $this->data['filters']) && $this->data['filters']['status']!= 0) {
                $return['`karaoke`.`accessed`'] = $this->data['filters']['status'] - 1;
            }
                       
            if (array_key_exists('protocol', $this->data['filters']) && !empty($this->data['filters']['protocol'])) {
                $return['`karaoke`.`protocol`'] = $this->data['filters']['protocol'];
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }
}
