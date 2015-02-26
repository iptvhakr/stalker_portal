<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class VideoClubController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->logoHost = $this->baseHost . "/stalker_portal/misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
        
        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => 'Отключен'),
            array('id' => 2, 'title' => 'Включен')
        );
    }
    
    // ------------------- action method ---------------------------------------

    public function index() {
        
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/video-list');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function video_list() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $allGenre = $this->db->getVideoGenres();
        
        $allYears = $this->db->getAllFromTable('video', 'year', 'year');
        
        $list = $this->video_list_json();
        
        $this->app['allYears'] = array_filter(array_map(function($val){
            if ((int)$val['year'] >= 1895) {
                return array('id'=>$val['year'], 'title'=>$val['year']);
            }
            return FALSE;
        }, $allYears));
        
        $this->app['allGenre'] =  $this->setLocalization($allGenre, 'title');
        $this->app['allVideo'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        $this->app['allModerators'] = $this->db->getAllAdmins();
        
        $attribute = $this->getVideoListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_video() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->prepareFormVideoCategories();

        $form = $this->buildForm();
        
        if ($this->saveVideoData($form)) {
            return $this->app->redirect('video-list');
        }
        $this->app['form'] = $form->createView();
        $data = $form->getData();
        if (!empty($data['cover_id'])) {
            $this->app['curr_cover_dir'] = $this->baseHost . "/stalker_portal/screenshots/" . ceil(intval(str_replace('.jpg', '', $data['cover_id'])) / 100);
        } else {
            $this->app['curr_cover_dir'] = '';
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function edit_video() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-video');
        }
        $this->oneVideo = $this->db->getVideoById($id);
        if (empty($this->oneVideo['id'])){
            $this->oneVideo['id'] = $id;
        }
        $this->oneVideo['cover_id'] = $this->db->getScreenshotData($this->oneVideo['id']);
        if(!empty($this->oneVideo['cover_id'])){
                $this->app['curr_cover_dir'] = $this->baseHost . "/stalker_portal/screenshots/" .  ceil(intval(str_replace('.jpg', '',$this->oneVideo['cover_id'])) / 100);
            } else {
                $this->app['curr_cover_dir'] = '';
            }
        
        $this->prepareFormVideoCategories();
        $this->prepareOneVideo();
        $form = $this->buildForm($this->oneVideo);
        
        if ($this->saveVideoData($form)) {
            return $this->app->redirect('video-list');
        }

        $this->app['form'] = $form->createView();
        $this->app['videoEdit'] = TRUE;
        
        $this->app['breadcrumbs']->addItem("Редактировать видео '{$this->oneVideo['name']}'");
        
        return $this->app['twig']->render('VideoClub_add_video.twig');
    }
    
    public function video_schedule() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $this->app['allTasks'] = $this->db->getAllVideoTasks();
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function video_advertise() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $ad = new \VclubAdvertising();
        $this->app['ads'] = $ad->getAllWithStatForMonth();
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_video_ads() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->ads = new \VclubAdvertising();
        
        $video_category = new \VideoCategory();
        $this->video_categories = $video_category->getAll();
        $this->getVideoCatForAds();
        
        $form = $this->buildAdsForm();
        
        if ($this->saveVideoAdsData($form)) {
            return $this->app->redirect('video-advertise');
        }       
        
        $this->app['form'] = $form->createView();
        $this->app['adsEdit'] = FALSE;
        $this->app['breadcrumbs']->addItem("Добавить рекламу");
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function edit_video_ads() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-video-ads');
        }
        
        $this->ads = new \VclubAdvertising();
        $this->ad = $this->ads->getById($id);
        $this->ad['denied_categories'] = $this->ads->getDeniedVclubCategoriesForAd($id);
        
        $video_category = new \VideoCategory();
        $this->video_categories = $video_category->getAll();
        $this->getVideoCatForAds();
        
        $form = $this->buildAdsForm($this->ad);
        
        if ($this->saveVideoAdsData($form)) {
            return $this->app->redirect('video-advertise');
        }
        
        $this->app['form'] = $form->createView();
        $this->app['adsEdit'] = TRUE;
        $this->app['breadcrumbs']->addItem("Редактировать рекламу '{$this->ad['title']}'");
        return $this->app['twig']->render('VideoClub_add_video_ads.twig');
    }
    
    public function video_moderators_addresses() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->app['Moderators'] = $this->db->getModerators();
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function add_video_moderators(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $form = $this->buildModForm();
        
        if ($this->saveVideoModData($form)) {
            return $this->app->redirect('video-moderators-addresses');
        }       
        
        $this->app['form'] = $form->createView();
        $this->app['modEdit'] = FALSE;
        $this->app['breadcrumbs']->addItem("Добавить модератора");
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function edit_video_moderators(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-video-moderators');
        }
        $this->mod = $this->db->getModerators($id);
        $this->mod['disable_vclub_ad'] = (bool)$this->mod['disable_vclub_ad'];
        $form = $this->buildModForm($this->mod);
        
        if ($this->saveVideoModData($form)) {
            return $this->app->redirect('video-moderators-addresses');
        }       
        
        $this->app['form'] = $form->createView();
        $this->app['modEdit'] = TRUE;
        $this->app['breadcrumbs']->addItem("Редактировать модератора '{$this->mod['name']}'");
        return $this->app['twig']->render('VideoClub_add_video_moderators.twig');
    }

    public function video_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $logs = $this->video_logs_json();
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['allVideoLogs'] = $logs['data'];
        
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    //----------------------- ajax method --------------------------------------
    
    public function video_list_json() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        
        $filds_for_select = array(
            "id" => "`video`.`id` as `id`",
            "path" => "`video`.`path` as `path`",
            "name" => "`video`.`name` as `name` ",
            "o_name" => "`video`.`o_name` as `o_name`",
            "time" => "`video`.`time` as `time`",
            "cat_genre" => "'' as `cat_genre`",
            "series" => "`video`.`series` as `series`",
            "tasks" => "(select count(*) from moderator_tasks where media_id = video.id) as `tasks`", //moderator_tasks.ended = 0 and 
            "task_id" => "`video_on_tasks`.`id` as `task_id`",
            "year" => "`video`.`year` as `year`",
            "added" => "CAST(`video`.`added` as CHAR) as `added`",
            "complaints" => "media_claims.sound_counter + media_claims.video_counter as `complaints`",
            "accessed" => "`video`.`accessed` as `accessed`"
        );
        $error = "Error";
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        $filter = $this->getVideoListFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'video.id as id';
        }
        
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        $query_param['select'][]= "media_claims.sound_counter as `sound_counter`";
        $query_param['select'][]= "media_claims.video_counter as `video_counter`";
        $query_param['select'][]= "`video_on_tasks`.`id` as `task_id`";
        $query_param['select'][]= "UNIX_TIMESTAMP(`video_on_tasks`.`date_on`) as `task_date_on`";
        $query_param['select'][]= "cat_genre_id_1";
        $query_param['select'][]= "cat_genre_id_2";
        $query_param['select'][]= "cat_genre_id_3";
        $query_param['select'][]= "cat_genre_id_4";
        if (empty($query_param['order'])) {
            $query_param['order']['added'] = 'DESC';
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsVideoList();
        $response["recordsFiltered"] = $this->db->getTotalRowsVideoList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getVideoList($query_param);
        if (!empty($response['data'])) {
            $cat_genres = $this->db->getVideoCategories();
            $cat_genres = $this->setLocalization($cat_genres, 'title'); 
            $cat_genres = array_combine($this->getFieldFromArray($cat_genres, 'id'), $this->getFieldFromArray($cat_genres, 'title'));
            while (list($key, $row) = each($response['data'])){
                $response['data'][$key]['RowOrder'] = "dTRow_" . $row['id'];
                $response['data'][$key]['cat_genre'] = array();
                if (!empty($row['cat_genre_id_1'])) {
                    $response['data'][$key]['cat_genre'][] = $cat_genres[$row['cat_genre_id_1']];
                }
                if (!empty($row['cat_genre_id_2'])) {
                    $response['data'][$key]['cat_genre'][] = $cat_genres[$row['cat_genre_id_2']];
                }
                if (!empty($row['cat_genre_id_3'])) {
                    $response['data'][$key]['cat_genre'][] = $cat_genres[$row['cat_genre_id_3']];
                }
                if (!empty($row['cat_genre_id_4'])) {
                    $response['data'][$key]['cat_genre'][] = $cat_genres[$row['cat_genre_id_4']];
                }
                $response['data'][$key]['cat_genre'] = implode(', ', $response['data'][$key]['cat_genre']);
                $response['data'][$key]['task_date_on'] = (int) strtotime($response['data'][$key]['task_date_on']);
                $response['data'][$key]['added'] = (int) strtotime($response['data'][$key]['added']);
            }
        }
              
        $tmp_allTasks = $this->db->getAllModeratorTasks();
        $allTasks = array();
        if (is_array($tmp_allTasks)) {
            while (list($num, $row) = each($tmp_allTasks)) {
                $row['end_time'] = (int)$row['end_time'] * ($this->isAjax? 1000 : 1);
                $row['ended'] = (int)$row['ended'];
                $row['rejected'] = (int)$row['rejected'];
                $row['expired'] = (time() - strtotime($row['start_time'])) > 864000;
                $allTasks[$row['media_id']][] = $row;
            }
        }

        if (is_array($response['data'])) {
            reset($response['data']);
            while (list($num, $row) = each($response['data'])) {
                $response['data'][$num]['task_date_on'] = (int)$response['data'][$num]['task_date_on'] * ($this->isAjax? 1000 : 1);
                $response['data'][$num]['accessed'] = (int)$response['data'][$num]['accessed'];
                $response['data'][$num]['series'] = count(unserialize($row['series']));
                if (!array_key_exists('tasks', $response['data'][$num])) {
                    $response['data'][$num]['tasks'] = array();
                }
                if (array_key_exists($row['id'], $allTasks)) {
                    if (!is_array($response['data'][$num]['tasks'])) {
                        $response['data'][$num]['tasks'] = array();    
                    }
                    $response['data'][$num]['tasks'] = $allTasks[$row['id']];
                }
            }
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
    
    public function video_info() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || (!is_numeric($this->postData['videoid']))) {
            $this->app->abort(404, 'Page not found');
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videoinfo';
        $data['title'] = 'Иформация о видео-источнике';
        $data['base_info'] = 'Информация отсутствует';
        $data['add_info'] = array();
        $error = '';
        
//        $error = 'Информация отсутствует';
        
        if (empty($video['rtsp_url'])){
            $path = $video['path'];

            if (empty($_SERVER['TARGET'])) {
                $_SERVER['TARGET'] = 'ADM';
            }
            $master = new \VideoMaster();
            $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, true);
            if (!empty($good_storages)) {
                $data['base_info'] = array();
            }
            foreach ($good_storages as $name => $data_s){
                $data['base_info'][] = array(
                    'storage_name' => $name,
                    'path'         => $path,
                    'series'       => count($data_s['series']),
                    'files'        => $data_s['files'],
                    'for_moderator' => $data_s['for_moderator'],
                );
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_video() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || (!is_numeric($this->postData['videoid']))) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videoremove';
        
        $error = 'Информация отсутствует';
        if ($this->db->videoLogWrite($video, 'video deleted')) {
            if ($this->db->removeVideoById($media_id)){
                $error='';    
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function disable_video() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || (!is_numeric($this->postData['videoid']))) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videodisable';
        $data['title'] = 'Включить';
        
        $error = 'Информация отсутствует';
        if ($this->db->videoLogWrite($video, 'off')) {
            $this->db->deleteVideoTask(array("video_id" => $media_id));
            if ($this->db->disableVideoById($media_id)){
                $this->db->toggleDisableForHDDevices($video, 0);
                $error='';    
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function enable_video() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || 
            (!is_numeric($this->postData['videoid'])) || empty($this->postData['video_on_date'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'videoenable';
        $data['title'] = 'Отключить';
        $error = 'Информация отсутствует';
        
        $media_id = intval($this->postData['videoid']);
        $date_on = date("Y-m-d", strtotime($this->postData['video_on_date']));
        
        if ($date_on == date("Y-m-d")) {
            $error = !((bool) $this->db->deleteVideoTask(array("video_id" => $media_id)));
            $video = $this->db->getVideoById($media_id);
            
            if ($this->db->videoLogWrite($video, 'on') && $this->db->enableVideoById($media_id)) {
                $this->db->toggleDisableForHDDevices($video, 1);
                $error = '';
            }
            $data['status'] = "<span class='txt-success'>Опубликовано<span>";
        } else {
            $data_in = array(
                'video_id' => $media_id,
                'date_on' => $date_on
            );
            
            $video_id = $this->db->getVideoTaskByVideoId($media_id);
            
            if (empty($video_id)) {
                $error = !((bool) $this->db->addVideoTask($data_in));
            } else {
                $this->db->updateVideoTask($data_in, array("video_id"=>$media_id));
                $error = '';
            }
            $data_in['date_on'] = strftime("%e-%m-%Y", strtotime($data_in['date_on']));
            $data['status'] = "<span class='txt-info'>Запланированно на $data_in[date_on]</span>";
//            $data['video_on_date'] = $date_on;
            $data = array_merge($data_in, $data);
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_md5() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['storage_name']) || empty($this->postData['media_name'])) {
            $this->app->abort(404, 'Page not found');
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'setMD5';
        $error = 'Информация отсутствует';
        if (\Admin::isPageActionAllowed('add_video')){
            if (empty($_SERVER['TARGET'])) {
                $_SERVER['TARGET'] = 'ADM';
            }
            $master = new \VideoMaster();
            ob_start();
            try {
                $data['data'] = $master->startMD5Sum($this->postData['storage_name'], $this->postData['media_name']);
                $error = '';
            }catch (\Exception $exception){
                $error = $exception->getMessage();
            }
            $data['md5_data'] = ob_get_contents();
            ob_end_clean();
        }else{
            $error = 'У Вас нет прав на это действие';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function create_tasks(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'createtasks';
        $error = 'Информация отсутствует';
        
        if (empty($this->postData['sendData']['id']) || empty($this->postData['sendData']['to_usr']) || empty($this->postData['sendData']['comment'])) {
            $error = 'Не все поля заполнены';
        } else {
            $data_in = $this->postData['sendData'];
            $data_in['task_id'] = $this->db->setModeratorTask($data_in);
            $data_in['uid'] = $_SESSION['uid'];
            if ($data_in['task_id'] && $this->db->setModeratorHistory($data_in)){
                $this->db->videoLogWrite($data_in['id'], serialize(array('task'=>$data_in['task_id'], 'event'=>'task open')), $data_in['to_usr']);
                $error = '';
                $data['task_id'] = $data_in['task_id'];
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkName';
        $error = 'Имя занято';
        if ($this->db->checkName(trim($this->postData['name']))) {
            $data['chk_rezult'] = 'Имя занято';
        } else {
            $data['chk_rezult'] = 'Имя свободно';
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_moderator_mac() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['mac'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkModMac';
        $error = 'Адрес занят';
        if ($this->db->checkModMac(trim($this->postData['mac']))) {
            $data['chk_rezult'] = 'Адрес занят';
        } else {
            $data['chk_rezult'] = 'Адрес свободен';
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function edit_cover() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if (empty($this->data['id']) || (!is_numeric($this->data['id']) && strpos($this->data['id'], 'new') === FALSE)) {
            $this->app->abort(404, 'Страница не найдена... Печаль...');
        } 
        
        $data = array();
        $data['action'] = 'editCover';
        $error = 'Информация отсутствует';

        if ((\Admin::isEditAllowed() || \Admin::isCreateAllowed()) && !empty($_FILES)){
            list($f_key, $tmp) = each($_FILES);
            if (is_uploaded_file($tmp['tmp_name']) && preg_match("/jpe?g/",$tmp['type'])){
                    
                if ($this->data['id'] == 'new') {
                    $s_data = array(
                        'name' => $tmp['name'],
                        'size' => $tmp['size'],
                        'type' => $tmp['type']
                    );
                            
                    $upload_id = $this->db->saveScreenshotData($s_data);
                } else {
                    $upload_id = $this->data['id'];
                }

                $img_path = $this->getCoverFolder($upload_id);
                umask(0);

                if (!rename($tmp['tmp_name'], $img_path.'/'.$upload_id.'.jpg')){
                    $error = sprintf(_('Error during file moving from %s to %s'), $tmp['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
                }else{
                    chmod($img_path.'/'.$upload_id.'.jpg', 0644);
                    $error = '';
                }
            }
        }
        $img_path = str_replace(str_replace('/admin', '', $this->baseDir), "", $img_path);
        $response = $this->generateAjaxResponse(array('pic' => $this->baseHost . "/stalker_portal" . $img_path.'/'.$upload_id), $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function update_rating_kinopoisk() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['data'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'updateRatingKinopoisk';
        $error = 'Нет данных';
        
        try {
            $data['result'] = \Kinopoisk::getRatingByName($this->postData['data']);      
            $error = '';
        } catch (\KinopoiskException $e) {
            $error = $e->getMessage();

            $logger = new \Logger();
            $logger->setPrefix("kinopoisk_");

            // format: [date] - error_message - [base64 encoded response];
            $logger->error(sprintf("[%s] - %s - \"%s\"\n", date("r"), $e->getMessage(), base64_encode($e->getResponse())));
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_kinopoisk_info_by_name() {
       
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'getKinopoiskInfoByName';
        $error = 'Нет данных';
        
        try {
            $data['result'] = \Kinopoisk::getInfoByName($this->postData['data']);      
            $error = '';
        } catch (\KinopoiskException $e) {
            $error = $e->getMessage();

            $logger = new \Logger();
            $logger->setPrefix("kinopoisk_");

            // format: [date] - error_message - [base64 encoded response];
            $logger->error(sprintf("[%s] - %s - \"%s\"\n", date("r"), $e->getMessage(), base64_encode($e->getResponse())));
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_kinopoisk_info_by_id() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['data'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'getKinopoiskInfoById';
        $error = 'Нет данных';
        
        try {
            $data['result'] = \Kinopoisk::getInfoById($this->postData['data']);      
            $error = '';
        } catch (\KinopoiskException $e) {
            $error = $e->getMessage();

            $logger = new \Logger();
            $logger->setPrefix("kinopoisk_");

            // format: [date] - error_message - [base64 encoded response];
            $logger->error(sprintf("[%s] - %s - \"%s\"\n", date("r"), $e->getMessage(), base64_encode($e->getResponse())));
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_image() {
        if ($this->method != 'GET' || empty($this->data['url'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'getImage';
        $error = 'Нет данных';

        if (strpos($this->data['url'], 'http://') === 0 && strpos($this->data['url'], 'kinopoisk.ru/')){
            $img = file_get_contents($this->data['url']);
            if (!empty($img)) {
                echo $img;
                exit;
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_tasks() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['taskid'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'removeTasks';
        $error = 'Не удалось';
        
        if ($this->db->deleteVideoTask(array('id'=>$this->postData['taskid']))) {
            $error = '';
        } 
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_video_ads() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['adsid'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'removeAds';
        $error = 'Не удалось';
        $ad = new \VclubAdvertising();
        
        if ($ad->delById($this->postData['adsid'])) {
            $error = '';
        } 
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_video_ads_status() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['adsid']) || !isset($this->postData['adsstatus'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'toggleVideoAdsStatus';
        $error = 'Не удалось';
        $ad = new \VclubAdvertising();
        
        if ($ad->updateById((int) $this->postData['adsid'], array('status' => (int) $this->postData['adsstatus']))) {
            $error = '';
            $data['title'] = ($this->postData['adsstatus'] ? 'Отключить': 'Включить');
            $data['status'] = ($this->postData['adsstatus'] ? '<span class="txt-success">Опубликовано</span>': '<span class="txt-danger">Не опубликовано</span>');
            $data['adsstatus'] = (int)!$this->postData['adsstatus'];
        } 
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_video_moderators() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['modid'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'removeMod';
        $error = '';
        $this->db->deleteModeratorsById($this->postData['modid']);
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_video_moderators_status() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['modid']) || !isset($this->postData['modstatus'])) {
            $this->app->abort(404, 'Page not found');
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'toggleVideoModStatus';
        $error = 'Не удалось';
        
        if ($this->db->updateModeratorsById((int) $this->postData['modid'], array('status' => (int) $this->postData['modstatus']))) {
            $error = '';
            $data['title'] = ($this->postData['modstatus'] ? 'Отключить': 'Включить');
            $data['status'] = ($this->postData['modstatus'] ? '<span class="txt-success">Вкл</span>': '<span class="txt-danger">Выкл</span>');
            $data['modstatus'] = (int)!$this->postData['modstatus'];
        } 
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
        
    public function video_logs_json($param = array()) {
        $response = array();
        $fields = array(
            'id'=>'`video_log`.`id` as `id`', 
            'video_id'=>'`video_log`.`video_id` as `video_id`',
            'login'=>'`administrators`.`login` as `login`', 
            'actiontime'=>'`actiontime`', 
            'video_name'=>'`video_name`', 
            'action'=>'`action`'
        );
        
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $param = (!empty($this->data)? $this->data: array());
        
        $query_param = $this->prepareDataTableParams($param);
        
        if (!\Admin::isPageActionAllowed('myvideolog')){
            $query_param['where']["moderator_id"] = $_SESSION['uid'];
        }
        
        if (!empty($this->data['video_id'])) {
            $query_param['where']['video_id'] = $this->data['video_id'];
        }
        
        $query_param['select'] = array_merge($query_param['select'], array_diff($fields, $query_param['select']));
        
        if (empty($query_param['order'])) {
            $query_param['order']['actiontime'] = 'desc';
        }
        
        $this->cleanQueryParams($query_param, array_keys($fields), $fields);
        
        $response['recordsTotal'] = $this->db->getTotalRowsVideoLog($query_param['where']);
        $response["recordsFiltered"] = $this->db->getTotalRowsVideoLog($query_param['where'], $query_param['like']);
        
        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 10;
        }
        $response['data'] = $this->db->getVideoLog($query_param);
        
        $response['data'] = array_map(function($row){
            $row['actiontime'] = (int)  strtotime($row['actiontime']);
            return $row;
        }, $response['data']);
        
        $this->setLinksForVideoLog($response['data']);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw']: 1;
        
        
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    //------------------------ service method ----------------------------------
    
    private function getVideoListFilters() {
        $filters = array();
        if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
            $filters['`accessed`'] = $this->data['filters']['status_id'] - 1;
        }
        
        if (array_key_exists('year', $this->data['filters']) && $this->data['filters']['year'] != 0) {
            $filters["`year`"] = $this->data['filters']['year'];
        }
        
        if (array_key_exists('genre_id', $this->data['filters']) && $this->data['filters']['genre_id'] != 0) {
            $genre_id = $this->data['filters']['genre_id'];
            $filters["(`genre_id_1`= '$genre_id' OR `genre_id_2` = '$genre_id' OR `genre_id_3` = '$genre_id' OR `genre_id_4` = '$genre_id') AND "] = "1=1";
        }
        
        
        $this->app['filters'] = $this->data['filters'];
        return $filters;
    }
 
    private function buildForm($data = array()) {

        $builder = $this->app['form.factory'];

        $ages = array("0+"=>"0+","6+"=>"6+","12+"=>"12+","14+"=>"14+","16+"=>"16+","18+"=>"18+", "21+"=>"21+");
        $rating_mpaa = array("G"=>"G", "PG"=>"PG", "PG-13"=>"PG-13", "R"=>"R", "NC-17"=>"NC-17");
        $protocol = array('http' => "HTTP", "custom" => "Custom URL", "nfs" => "NFS");
        $genres = array();
        foreach ($this->app['videoGenres'] as $row) {
            $genres[$row['id']] = $row['title'];
        }
        
        $cat_genres = array();
        foreach ($this->app['catGenres'] as $row) {
            $cat_genres[$row['id']] = $row['category_name'];
        }
        
        $cat_video = array();
        foreach ($this->app['videoCategories'] as $row) {
            $cat_video[$row['id']] = $row['title'];
        }
        
        $for_sd_stb = $this->getConfigOptionalyFormField('for_sd_stb', 'vclub_mag100_filter');
        $high_quality = $this->getConfigOptionalyFormField('high_quality', 'enable_video_high_quality_option');
        $low_quality = $this->getConfigOptionalyFormField('low_quality', 'enable_video_low_quality_option');
        
        $form = $builder->createBuilder('form', $data)
                /*название*/
                /*+*/->add('name', 'text', array('constraints' => array( 
                            new Assert\NotBlank()),
                            'required' => TRUE
                        )
                    )
                /*+*/->add('id', 'hidden')
                /*+*/->add('rating_count_kinopoisk', 'hidden')
                /*+*/->add('rating_imdb', 'hidden')
                /*+*/->add('rating_count_imdb', 'hidden')
                /*ориг название*/
                /*+*/->add('o_name', 'text', array('constraints' => array('required' => FALSE), 'required' => FALSE))
                /*кинопосик ИД*/
                /*+*/->add('kinopoisk_id', 'text', array('constraints' => array(new Assert\Type(array('type' => 'numeric')), 'required' => FALSE), 'required' => FALSE))
                /*+*/->add('rating_kinopoisk', 'text', array('constraints' => array(new Assert\Type(array('type' => 'numeric')), 'required' => FALSE), 'required' => FALSE))
                /*возраст рейтинг*/
                /*+*/->add('age', 'choice', array(
                            'choices' => $ages,
                            'constraints' => array(
                                        new Assert\Choice(array('choices' => $ages)),
                                        new Assert\NotBlank()
                                    ), 
                            'required' => TRUE
                        )
                    )
                /*рейтинг МРАА*/
                /*+*/->add('rating_mpaa', 'choice', array(
                            'choices' => $rating_mpaa,
                            'constraints' => array(
                                        new Assert\Choice(array('choices' => $rating_mpaa)),
                                        new Assert\NotBlank()
                                    ), 
                            'required' => TRUE
                        )
                    )
                /*протокол*/
                /*+*/->add('protocol', 'choice', array(
                            'choices' => $protocol,
                            'constraints' => array(
                                        new Assert\Choice(array('choices' => array_keys($protocol))),
                                        new Assert\NotBlank()
                                    ), 
                            'required' => TRUE
                        )
                    )
                /*урл*/
                /*+*/->add('rtsp_url', 'text')
                /*огр возраст*/
        /*+/-*/->add('censored', 'checkbox', array('required' => false))
                /*+*/->add($for_sd_stb['name'], $for_sd_stb['type'], $for_sd_stb['option'])
                /*+*/->add($high_quality['name'], $high_quality['type'], $high_quality['option'])
                /*+*/->add($low_quality['name'], $low_quality['type'], $low_quality['option'])
                /*ХД*/
                /*+*/->add('hd', 'checkbox', array('required' => false))
                /*старый жанр*/
                /*+*/->add('genres', 'choice', array(
                            'choices' => $genres,
                            'constraints' => array(
                                    new Assert\Choice(array('choices' => array_keys($genres), 'multiple' => TRUE)),
                                    new Assert\NotBlank()
                                ),
                            'multiple' => TRUE, 
                            'required' => TRUE
                        )
                    )
                /*категория*/
                /*+*/->add('category_id', 'choice', array(
                            'choices' => $cat_genres,
                            'constraints' => array(
                                    new Assert\Choice(array('choices' => array_keys($cat_genres))),
                                    new Assert\NotBlank()
                                ),
                            'required' => TRUE
                        )
                    )
                /*жанр*/
                /*+*/->add('cat_genre_id', 'choice', array(
                            'choices' => $cat_video,
                            'constraints' => array(
                                    new Assert\Choice(array('choices' => array_keys($cat_video), 'multiple' => TRUE)),
                                    new Assert\NotBlank(),
                                    'required' => TRUE
                                ),
                            'multiple' => TRUE, 
                            'required' => TRUE
                        )
                    )
                /*год*/
                /*+*/->add('year', 'text', array('required' => TRUE, 'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                            'pattern' => '/\d{4}/',
                            'match'   => TRUE))
                            )
                        )
                    )
                /*------*/->add('country', 'text', array('required' => TRUE, 'constraints' => array(new Assert\NotBlank(),new Assert\Type(array('type' => 'string')))))
                /*длительность*/
                /*+*/->add('duration', 'text', array('required' => TRUE, 'constraints' => array(new Assert\NotBlank(),new Assert\Type(array('type' => 'numeric')))))
                /*режисер*/
                /*+*/->add('director', 'text', array('required' => TRUE, 'constraints' => array(new Assert\NotBlank())))
                /*актеры*/
                /*+*/->add('actors', 'textarea', array('required' => TRUE, 'constraints' => array(new Assert\NotBlank())))
                /*описание*/
                /*+*/->add('description', 'textarea', array('required' => TRUE, 'constraints' => array(new Assert\NotBlank())))
                /*громкость*/
                /*+*/->add('volume_correction', 'choice', array(
                            'choices' => array_combine(range(-20, 20, 1), range(-100, 100, 5)),
                            'constraints' => array(
                                new Assert\Range(array('min' => -20, 'max' => 20)), 
                                new Assert\NotBlank()),
                            'required' => TRUE,
                            'data' => (empty($data['volume_correction']) ? 0: $data['volume_correction'])
                        )
                    )
                ->add('comments', 'textarea')
                /*обложка*/                
                ->add('cover_id', 'hidden')
                ->add('cover_big', 'hidden')
                ->add('save', 'submit');
//                ->add('reset', 'reset');
        return $form->getForm();
    }
    
    private function buildAdsForm($data = array()) {

        $builder = $this->app['form.factory'];
        $must_watch = array(
            'all' => 'Все',
            '90' => '90%',
            '80' => '80%',
            '70' => '70%',
            '60' => '60%',
            '50' => '50%',
            '40' => '40%',
            '30' => '30%',
            '20' => '20%',
            '10' => '10%',
            '5' => '5%',
            '0' => '0%'
            );
        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('title', 'text', array('constraints' => array( 
                            new Assert\NotBlank()),
                            'required' => TRUE
                        )
                    )   
                ->add('url', 'text', array('constraints' => array( 
                            new Assert\NotBlank()),
                            'required' => TRUE
                        )
                    )
                ->add('weight', 'text', array('constraints' => array( 
                            new Assert\NotBlank(),
                            new Assert\Type(array('type' => 'numeric'))
                            ),
                            'required' => TRUE
                        )
                    )
                ->add('denied_categories', 'choice', array(
                            'choices' => $this->video_categories,
                            'constraints' => array(
                                    new Assert\Choice(array('choices' => array_keys($this->video_categories), 'multiple' => TRUE))
                                ),
                            'multiple' => TRUE, 
                            'required' => FALSE
                        )
                    )
                ->add('must_watch', 'choice', array(
                            'choices' => $must_watch,
                            'constraints' => array(
                                    new Assert\NotBlank(),
                                    new Assert\Choice(array('choices' => array_keys($must_watch), 'multiple' => FALSE))
                                ),
                            'multiple' => FALSE, 
                            'required' => TRUE
                        )
                    )
                ->add('save', 'submit');
//                ->add('reset', 'reset');

        return $form->getForm();
    }
    
    private function buildModForm($data = array()) {

        $builder = $this->app['form.factory'];

        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('name', 'text', array('constraints' => array( 
                            new Assert\NotBlank()),
                            'required' => TRUE
                        )
                    )   
                ->add('mac', 'text', array('constraints' => array( 
                            new Assert\NotBlank()),
                            'required' => TRUE
                        )
                    )
                ->add('disable_vclub_ad', 'checkbox', array('required' => FALSE))
                ->add('save', 'submit');
//                ->add('reset', 'reset');

        return $form->getForm();
    }
    
    private function getConfigOptionalyFormField($field_name, $config_option){
        $return_opt = array(
            'name' => $field_name,
            'type' => 'hidden',
            'option' => array()
        );
        if (\Config::getSafe($config_option, false)){
            $return_opt['type'] = 'checkbox';
            $return_opt['option'] = array('required' => false);
        }
        return $return_opt;
    }
    
    private function saveVideoData(&$form) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                if (empty($data['id'])) {
                    $is_repeating_name = $this->db->checkName(trim($data['name']));
                    $operation = 'insertVideo';
                } elseif (isset($this->oneVideo)) {
                    $is_repeating_name = !((empty($this->oneVideo['name']) || $this->oneVideo['name'] != $data['name']) xor ( (bool) $this->db->checkName(trim($data['name']))));
                    $operation = 'updateVideo';
                }
                if (!$is_repeating_name) {
                    if ($data ['protocol'] == 'custom' && $data ['rtsp_url']) {
                        $error_local = array();
                        $error_local['rtsp_url'] = ($is_repeating_name ? "Если протокол - '$data[protocol]', то это поле необходимо заполнить" : '');
                        $this->app['error_local'] = $error_local;
                        return FALSE;
                    }
                    $data['trans_name'] = $this->transliterate(@urldecode($data['name']));
                    if ($data['hd']) {
                        $data['trans_name'] .= '_HD';
                    }
                    $this->createMediaStorage($data['trans_name']);
                    $db_data = array(
                        'name' => trim($data['name']),
                        'o_name' => trim($data['o_name']),
                        'censored' => $data ['censored'],
                        'hd' => $data ['hd'],
                        'for_sd_stb' => $data ['for_sd_stb'],
                        'protocol' => $data ['protocol'],
                        'rtsp_url' => $data ['rtsp_url'],
                        'time' => @$data ['duration'],
                        'description' => $data ['description'],
                        'genre_id_1' => (array_key_exists(0, $data['genres']) ? $data ['genres'][0] : 0),
                        'genre_id_2' => (array_key_exists(1, $data['genres']) ? $data ['genres'][1] : 0),
                        'genre_id_3' => (array_key_exists(2, $data['genres']) ? $data ['genres'][2] : 0),
                        'genre_id_4' => (array_key_exists(3, $data['genres']) ? $data ['genres'][3] : 0),
                        'cat_genre_id_1' => (array_key_exists(0, $data['cat_genre_id']) ? $data ['cat_genre_id'][0] : 0),
                        'cat_genre_id_2' => (array_key_exists(1, $data['cat_genre_id']) ? $data ['cat_genre_id'][1] : 0),
                        'cat_genre_id_3' => (array_key_exists(2, $data['cat_genre_id']) ? $data ['cat_genre_id'][2] : 0),
                        'cat_genre_id_4' => (array_key_exists(3, $data['cat_genre_id']) ? $data ['cat_genre_id'][3] : 0),
                        'category_id' => $data ['category_id'],
                        'director' => $data ['director'],
                        'actors' => $data ['actors'],
                        'status' => (int) !empty($data ['rtsp_url']),
                        'year' => $data['year'],
                        'volume_correction' => (int) $data['volume_correction'],
                        'kinopoisk_id' => $data['kinopoisk_id'],
                        'rating_kinopoisk' => $data['rating_kinopoisk'],
                        'rating_count_kinopoisk' => $data['rating_count_kinopoisk'],
                        'rating_imdb' => $data['rating_imdb'],
                        'rating_count_imdb' => $data['rating_count_imdb'],
                        'age' => $data['age'],
                        'rating_mpaa' => $data['rating_mpaa'],
                        'path' => $data ['trans_name'],
                        'high_quality' => $data ['high_quality'],
                        'low_quality' => $data ['low_quality'],
                        'comments' => $data['comments'],
                        'country' => $data['country']
                    );
                    if ($operation == 'insertVideo') {
                        $db_data['added'] = 'NOW()';
                        $id = $this->db->$operation($db_data);
                    } else {
                        $id = $data['id'];
                        $this->db->$operation($db_data, $id);
                    }
                    $cover_id = (!empty($data['cover_big']) ? $this->getExternalImage($data['cover_big']) : (!empty($data['cover_id']) ? $data['cover_id'] : FALSE));
                    if ($cover_id !== FALSE) {
                        $this->db->updateScreenshotData($id, $cover_id);
                    }
//                    $this->db->cleanScreenshotData();
                    return TRUE;
                } else {
                    $error_local = array();
                    $error_local['name'] = ($is_repeating_name ? 'Такое имя уже есть' : '');
                    $this->app['error_local'] = $error_local;
                    return FALSE;
                }
            }
        }
        return FALSE;
    }

    private function prepareFormVideoCategories(){
        $videoGenres = $this->db->getVideoGenres();
        $this->app['videoGenres'] = $this->setLocalization($videoGenres, 'title');
        
        $catGenres = $this->db->getCategoriesGenres();
        $this->app['catGenres'] = $this->setLocalization($catGenres, 'category_name');
        
        $videoCategories = $this->db->getVideoCategories();
        $this->app['videoCategories'] = $this->setLocalization($videoCategories, 'title'); 
        
        $this->app['videoEdit'] = FALSE;
        
        $prepared_cat_genre = array();
        foreach($this->app['videoCategories'] as $row){
            if (!array_key_exists($row['category_alias'], $prepared_cat_genre)) {
                $prepared_cat_genre[$row['category_alias']] = array();
            }
            $prepared_cat_genre[$row['category_alias']][] = $row;
        }
        
        $this->app['preparedCatGenre'] = $prepared_cat_genre;
    }
    
    private function prepareOneVideo() {
        $this->catFieldsToArray('genre_id_', "genres", 4);
        $this->catFieldsToArray('cat_genre_id_', "cat_genre_id", 4);
        if (empty($this->oneVideo['age'])) {
            $this->oneVideo['age'] = "0+";
        }
        if (empty($this->oneVideo['rating_mpaa'])) {
            $this->oneVideo['rating_mpaa'] = "G";
        }
        $this->oneVideo['duration'] = (!empty($this->oneVideo['time'])? $this->oneVideo['time']: 0);
        $this->oneVideo['cover_id'] = $this->db->getScreenshotData($this->oneVideo['id']);
        $this->getBoolVal($this->oneVideo);
    }
    
    private function catFieldsToArray($field_prefix, $array_name, $fields_count) {
        if (empty($this->oneVideo)) {
            return;
        }
        $return_array = array();
        for($i = 0; $i < $fields_count; $i++){
            if (array_key_exists($field_prefix.$i, $this->oneVideo) && !empty($this->oneVideo[$field_prefix.$i])) {
                $return_array[] = $this->oneVideo[$field_prefix.$i];
            }
        }
        $this->oneVideo[$array_name] = $return_array;
    }
    
    private function getBoolVal(&$data){
        while(list($key, $val) = each($data)){
            if(is_string($val) || is_numeric($val) || is_null($val)){
                $data[$key] = (empty($val)? FALSE: (intval($val) == 1? TRUE: $val));
            } /*elseif(is_array($val) || is_object($val)){
                $data[$key] = $this->getBoolVal($val);
            }*/
        }
    }
    
    private function getExternalImage($url) {
        $cover_id = FALSE;
        try {
            $tmpfname = tempnam("/tmp", "video_cover");
            $cover_blob = file_get_contents($url);
            file_put_contents($tmpfname, $cover_blob);
            $cover = new \Imagick($tmpfname);
            unlink($tmpfname);
        } catch (\ImagickException $e) {
            $error = _('Error: ' . $e->getMessage());
        }

        if ($cover) {

            if (!$cover->resizeImage(240, 320, \Imagick::FILTER_LANCZOS, 1)) {
                $error = _('Error: could not resize cover');
            }

            $cover_filename = substr($url, strrpos($url, '/') + 1);
            $s_data = array();
            $s_data['name'] = $cover_filename;
            $s_data['size'] = $cover->getimagesize();
            $s_data['type'] = $cover->getformat();

            $cover_id = $this->db->saveScreenshotData($s_data);

            $img_path = $this->getCoverFolder($cover_id);
            umask(0);

            if (empty($error) && !$cover->writeImage($img_path . '/' . $cover_id . '.jpg')) {
                $error = _('Error: could not save cover image');
            }

            $cover->destroy();
        }
        return $cover_id;
    }
    
    private function createMediaStorage($trans_name) {

        $existed = $this->db->getVideoByParam(array('path' => $trans_name));

        if (!empty($existed)) {
            $error = _('Error: The folder with that name already exists');
        } else {
            $_SERVER['TARGET'] = 'ADM';
            $master = new \VideoMaster();
            try {
                $master->createMediaDir($trans_name);
            } catch (\MasterException $e) {
                //var_dump($e->getMessage(), $e->getStorageName()); exit;
                $moderator_storages = $master->getModeratorStorages();
                if (!empty($moderator_storages[$e->getStorageName()])) {
                    $error = _('Error creating the folder on moderator storage');
                }
            }
        }
    }

    private function saveVideoAdsData(&$form){
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            $action = (isset($this->ad) ? 'updateById': 'add');

            if ($form->isValid()) {
                if ($action=='add'){
                    if ( $this->ads->$action($data)){
                        return TRUE;
                    }                    
                } else {
                    if ( $this->ads->$action($data['id'],$data)){
                        return TRUE;
                    }
                }
            
            }
        }
        return FALSE;
    }
    
    private function getVideoCatForAds(){
        $keys = $this->getFieldFromArray($this->video_categories, 'id');
        $values = $this->getFieldFromArray($this->video_categories, 'category_name');
        $this->video_categories = array_combine($keys, $values);
    }
    
    private function saveVideoModData(&$form){
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            $action = (isset($this->mod) ? 'updateModeratorsById': 'insertModerators');
            if (array_key_exists('disable_vclub_ad', $data)) {
                $data['disable_vclub_ad'] = (int) $data['disable_vclub_ad'];
            } else {
                $data['disable_vclub_ad'] = 0;
            }
            if ($form->isValid()) {
                if ($action=='insertModerators'){
                    if ( $this->db->$action($data)){
                        return TRUE;
                    }                    
                } else {
                    if ( $this->db->$action($data['id'],$data)){
                        return TRUE;
                    }
                }
            
            }
        }
        return FALSE;
    }
    
    private function setLinksForVideoLog(&$data){
        
        $action_link_template = "<a href='$this->workURL/tasks/task-detail-video?id={action[task]}'>{action[event]}</a>";
        $video_name_link_template = "<a href='$this->workURL/tasks/task-detail-video?id={match[0]}'>{match[1]}</a>";
        while(list($key, $row) = each($data)){
            $data[$key]['video_name'] = "<a href='$this->workURL/" . $this->app['controller_alias'] . "/edit-video?id=$row[video_id]'>$row[video_name]</a>";
            if ($action = unserialize($row['action'])) {
                $data[$key]['action'] = strtr($action_link_template, array("{action[task]}" => $action['task'], "{action[event]}" => $action['event']));
            } else {
                $matches = array();
                $c = preg_match_all("/task\=(\d*)[^\>]*\>([^\<]*)\</i", $row['action'], $matches);
                if (count($matches) >= 2 && !empty($matches[1][0]) && !empty($matches[2][0])) {
                    $data[$key]['action'] = strtr($action_link_template, array("{action[task]}" => $matches[1][0], "{action[event]}" => $matches[2][0]));
                } 
            }
        }
    }
    
    private function getVideoListDropdownAttribute(){
        return array(
            array('name' => 'id',           'title' => 'ID',                    'checked' => TRUE),
            array('name' => 'path',         'title' => 'Каталог',               'checked' => TRUE),
            array('name' => 'name',         'title' => 'Название',              'checked' => TRUE),
            array('name' => 'o_name',       'title' => 'Оригинальное название', 'checked' => FALSE),
            array('name' => 'time',         'title' => 'Длительность',          'checked' => TRUE),
            array('name' => 'series',       'title' => 'Серии',                 'checked' => TRUE),
            array('name' => 'cat_genre',    'title' => 'Жанр',                  'checked' => TRUE),
            array('name' => 'tasks',        'title' => 'Задания',               'checked' => TRUE),
            array('name' => 'year',         'title' => 'Год',                   'checked' => TRUE),
            array('name' => 'added',        'title' => 'Дата запуска',          'checked' => TRUE),
            array('name' => 'complaints',   'title' => 'Жалобы',                'checked' => TRUE),
            array('name' => 'status',       'title' => 'Статус',                'checked' => TRUE),
            array('name' => 'operations',   'title' => 'Действия',              'checked' => TRUE)
        );
        
    }
}
