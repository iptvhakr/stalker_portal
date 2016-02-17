<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class StoragesController extends \Controller\BaseStalkerController {

    private $allServerStatus = array();


    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allServerStatus = array(
            array('id' => 1, 'title' => $this->setLocalization('Unpublished')),
            array('id' => 2, 'title' => $this->setLocalization('Published'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/storages-list');
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function storages_list() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->storages_list_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function storages_video_search() {
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getSearchDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        
        $this->app['dropdownStorages'] = array_map(function($val){
            return array('name' => $val['storage_name'], 'title' => $val['storage_name'], 'checked' => FALSE);
        }, $this->db->getListList(array("where" => array('status' => 1))));

        $this->app['dropdownQuality'] = array(
            array('name' => 'HD', 'title' => 'HD', 'checked' => FALSE),
            array('name' => 'SD', 'title' => 'SD', 'checked' => FALSE)
        );
        
        $this->app['dropdownStatus'] = array_map(function($val){
            $val['name'] = $val['id'];
            $val['checked'] = FALSE;
            return $val;
        }, $this->allServerStatus);
        
        $list = $this->storages_video_search_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
        
    }
    
    public function storages_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getLogsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->storages_logs_json();
        
        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    //----------------------- ajax method --------------------------------------
    
    public function storages_list_json() {
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

               
        $filds_for_select = $this->getListFields();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);
        
        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $response['recordsTotal'] = $this->db->getListTotalRows();
        $response["recordsFiltered"] = $this->db->getListTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getListList($query_param);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function reset_cache(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'listMsg';
        $error = $this->setLocalization('Error');
        if ($this->postData['id'] != 'all') {
            $result = $this->db->getListList(array('select'=>array('storage_name'), 'where'=>array('id'=>$this->postData['id'])));
            $names = array('storage_name'=>$result[0]['storage_name']);
        } else {
            $names = array();
        }
        $result = $this->db->updateStorageCache(array('changed' => '0000-00-00 00:00:00'), $names);
        if (is_numeric($result)) {
            $data['msg'] = $this->setLocalization('A cache has been reset') . (!empty($names)? ' ' . $this->setLocalization('for') . ' ' .implode(', ', $names): ' ' . $this->setLocalization('for all servers'));
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function refresh_cache(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        ob_start();
        ob_implicit_flush (TRUE);
        header('Content-Type: application/json');
        ob_flush();
        sleep(1);
        $data = array();
        $data['action'] = 'listMsg';
        
        $updated_video = 0;
        $updated_karaoke = 0;

        $not_custom_video = $this->db->getNoCustomVideo();

        $data['msg'] = $this->setLocalization('Updated') . ": " . count($not_custom_video) . " - " . $this->setLocalization('movies') . "; ";
        $_SERVER['TARGET'] = 'ADM';
        
        foreach($not_custom_video as $row){
            set_time_limit(30);
            
            ob_start();
            ob_implicit_flush (FALSE);
            $master = new \VideoMaster();
            $master->getAllGoodStoragesForMediaFromNet($row, true);
            ob_end_clean();
            
            unset($master);
            $updated_video++;
        }

        $not_custom_karaoke = $this->db->getNoCustomKaraoke();
        $data['msg'] .= count($not_custom_karaoke)  . " - " . $this->setLocalization('karaoke');
                
        foreach($not_custom_karaoke as $row){
            set_time_limit(30);
            
            ob_start();
            ob_implicit_flush (FALSE);
            $master = new \KaraokeMaster();
            $master->getAllGoodStoragesForMediaFromNet($row);
            ob_end_clean();
            
            unset($master);
            $updated_karaoke++;
        }
        ob_end_clean();
        $error = '';
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_storage(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'fillModalAd';
        $result = $this->db->getListList(array('select'=>array('*'), 'where'=>array('id'=>$this->postData['id'])));
        $data['storage'] = $result[0];
        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function save_storage() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['form'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        
        $data = array();
        $data['action'] = 'listMsg';
        $storage = array($this->postData['form']);
        $error = 'error';
        if (!empty($storage[0]['storage_name']) && !empty($storage[0]['storage_ip']) && !empty($storage[0]['apache_port'])) {
            if (empty($this->postData['form']['id'])) {
                $operation = 'insertStorages';
            } else {
                $operation = 'updateStorages';
                $storage['id'] = $this->postData['form']['id'];
            }
            unset($storage[0]['id']);

            $storage[0]['flussonic_dvr'] = (int)(!empty($storage[0]['flussonic_dvr']) && $storage[0]['flussonic_dvr'] != 'off' && !empty($storage[0]['for_records']));
            $storage[0]['wowza_dvr'] = (int)(!empty($storage[0]['wowza_dvr']) && $storage[0]['wowza_dvr'] != 'off' && !empty($storage[0]['for_records']));
            $storage[0]['fake_tv_archive'] = (int)(!empty($storage[0]['fake_tv_archive']) && $storage[0]['fake_tv_archive'] != 'off' && !empty($storage[0]['for_records']));

            $result = call_user_func_array(array($this->db, $operation), $storage);
            if (is_numeric($result)) {
                $error = '';
                $data['msg'] = $this->setLocalization('Saved');
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }

        } else {
            $error = $data['msg'] = $this->setLocalization('Fill in the required fields');
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_storages_status() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || !array_key_exists('status', $this->postData)) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'listMsg';
        $data['id'] = $this->postData['id'];
        $this->db->updateStorages(array('status' => (int)(!((bool) $this->postData['status']))), $this->postData['id']);
        $error = '';    
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_storage() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'listMsg';
        $result = $this->db->deleteStorages($this->postData['id']);
        $data['msg'] = $this->setLocalization('Deleted') . " " . (!empty($result)? $result: '');

        $error = '';

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function storages_video_search_json(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        
        $filds_for_select = $this->getSearchFields();
        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);
        
        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));
        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        $like_filter = $having = $query_param['having'] = array();
        $filter = $this->getSearchFilters($like_filter, $having);

        if (empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = $like_filter;
        } elseif (!empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = array_merge($query_param['like'], $like_filter);
        }
        
        if (empty($query_param['having']) && !empty($having)) {
            $query_param['having'] = $having;
        } elseif (!empty($query_param['having']) && !empty($having)) {
            $query_param['having'] = array_merge($query_param['having'], $having);
        }
        
        $query_param['where'] = array_merge($query_param['where'], $filter);

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        if (!empty($query_param['like']["count(`storage_name`)"])) {
//            $query_param['having']["on_storages like '" . $query_param['like']["count(`storage_name`)"] . "' and '1'"] = '1';
            unset($query_param['like']["count(`storage_name`)"]);
        }

        $response['recordsTotal'] = $this->db->getTotalRowsVideoList($query_param['select']);
        $response["recordsFiltered"] = $this->db->getTotalRowsVideoList($query_param['select'], $query_param['where'], $query_param['like'], $query_param['having']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response['data'] = $this->db->getVideoList($query_param);
//        while (list($key, $row) = each($response['data'])){
//            $response['data'][$key]['RowOrder'] = "dTRow_" . $row['id'];
//        }

        $response['data'] = array_map(function($row){
            $row['last_played'] = (int) strtotime($row['last_played']);
            return $row;
        }, $response['data']);
        
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if (!empty($param['textview'])) {
            header('Content-Type: text/plain; charset=utf-8');
            $i = 1;
            foreach ($response['data'] as $row) {
                echo $i."\t".$row['path']."\t".$row['on_storages']."\r\n";
                $i++;
            }
            exit;
        }
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function storages_logs_json() {
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

               
        $filds_for_select = $this->getLogsFields();
                
        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $response['recordsTotal'] = $this->db->getLogsTotalRows();
        $response["recordsFiltered"] = $this->db->getLogsTotalRows($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        $response["data"] = $this->db->getLogsList($query_param);
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
    
    //------------------------ service method ----------------------------------
    
    private function getSearchFilters(&$like_filter, &$having){
        $return = array();

        if (!empty($this->data['filters'])) {
            
            $on_storages = (array_key_exists('on_storages', $this->data['filters']))? $this->data['filters']['on_storages'] : array();
            $not_on_storages = (array_key_exists('not_on_storages', $this->data['filters']))? $this->data['filters']['not_on_storages'] : array();
            $search = array_search('all', $on_storages);
            if ($search !== FALSE) {
                unset($on_storages[$search]);
            }

            $search = array_search('all', $not_on_storages);
            if ($search !== FALSE) {
                unset($not_on_storages[$search]);
            }
            
            if (!empty($this->data['filters']['status'][0]) && $this->data['filters']['status'][0] != 'all'){
                if (($this->data['filters']['status'][0] == 2)) {
                    $return['video.accessed'] = 1;
                } else {
                    $return['video.accessed'] = 0;
                }
            }
            
            if (array_key_exists('quality', $this->data['filters']) && strtolower($this->data['filters']["quality"][0]) != "all"){
                $return['video.hd'] = (int)(strtolower($this->data['filters']["quality"][0]) == "hd");
            } 
        
            if (isset($this->data['filters']['total_storages']) && $this->data['filters']['total_storages'] != '') {
                $having['`on_storages`'] = (int)$this->data['filters']['total_storages'];// + count($on_storages);
            }
            
            foreach ($on_storages as $value) {
                $having["`storages` like '%" . $value . "%' and '1'"] = "1";
            }
            foreach ($not_on_storages as $value) {
                $having["`storages` not like '%" . $value . "%' and '1'"] = "1";
            }
        
        }
        return $return;
    }

    private function getLogsDropdownAttribute() {
        return array(
            array('name' => 'added',    'title' => $this->setLocalization('Time'),  'checked' => TRUE),
            array('name' => 'log_txt',  'title' => $this->setLocalization('Message'),'checked' => TRUE)
        );
    }
    
    private function getLogsFields(){
        return array(
            "added" => "CAST(M_L.`added` AS CHAR) as `added`",
            "log_txt" => "M_L.`log_txt` as `log_txt`"
        );
    }
    
    private function getListDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'storage_name', 'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'storage_ip',   'title' => $this->setLocalization('IP'),            'checked' => TRUE),
            array('name' => 'nfs_home_path','title' => $this->setLocalization('Home directory'),'checked' => TRUE),
            array('name' => 'max_online',   'title' => $this->setLocalization('Maximum users'), 'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization('Status'),        'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operation'),     'checked' => TRUE)
        );
    }
    
    private function getListFields(){
        return array(
            "id" => "S.`id` as `id`",
            "storage_name" => "S.`storage_name` as `storage_name`",
            "storage_ip" => "S.`storage_ip` as `storage_ip`",
            "nfs_home_path" => "S.`nfs_home_path` as `nfs_home_path`",
            "max_online" => "S.`max_online` as `max_online`",
            "status" => "S.`status` as `status`"
        );
    }
    
    private function getSearchDropdownAttribute() {
        return array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'path',         'title' => $this->setLocalization('Catalogue'),     'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'hd',           'title' => $this->setLocalization('Video quality'), 'checked' => TRUE),
            array('name' => 'on_storages',  'title' => $this->setLocalization('Storage quantity'),'checked' => TRUE),
            array('name' => 'count',        'title' => $this->setLocalization('All views'),     'checked' => TRUE),
            array('name' => 'month_counter','title' => $this->setLocalization('Views per month'),'checked' => TRUE),
            array('name' => 'last_played',  'title' => $this->setLocalization('Last view'),     'checked' => TRUE),
            array('name' => 'accessed',     'title' => $this->setLocalization('Status'),        'checked' => TRUE)
        );
    }
    
    private function getSearchFields(){
        return array(           
            "id" => "`video`.`id` as `id`",
            "path" => "`video`.`path` as `path`",
            "name" => "`video`.`name` as `name` ",
            'hd'=>"if(`video`.`hd` = 1, 'HD', 'SD') as `hd` ",
            'on_storages'=>"count(`storage_name`) as `on_storages`",
            'count'=>"`video`.`count` as `count` ",
            'month_counter'=>"(`video`.`count_first_0_5` + `video`.`count_second_0_5`) as `month_counter`",
            'last_played'=>"cast(`video`.`last_played` as char) as `last_played` ",
            "accessed" => "`video`.`accessed` as `accessed`",
            "tasks" => "(select count(*) from `moderator_tasks` where `moderator_tasks`.`ended` = 0 and `moderator_tasks`.`media_id`= `video`.`id`) as `tasks`",
            "storages"=>"GROUP_CONCAT(`storage_name`) as `storages`"
        );
    }
}
