<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Imagine\Image\Box;

class VideoClubController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->logoHost = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/') . "misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
        
        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Unpublished')),
            array('id' => 2, 'title' => $this->setLocalization('Published')),
            array('id' => 3, 'title' => $this->setLocalization('Scheduled'))
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
        
        $allYears = $this->db->getAllFromTable('video', 'year', 'year');

        $list = $this->video_list_json();
        
        $this->app['allYears'] = array_filter(array_map(function($val){
            if ((int)$val['year'] >= 1895) {
                return array('id'=>$val['year'], 'title'=>$val['year']);
            }
            return FALSE;
        }, $allYears));
        
        $this->app['allGenre'] =  $this->prepareNewGenresListIds($this->db->getVideoCategories());
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
            $this->app['curr_cover_dir'] = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/') . "screenshots/" . ceil(intval(str_replace('.jpg', '', $data['cover_id'])) / 100);
        } else {
            $this->app['curr_cover_dir'] = '';
        }

        $this->app['videoEdit'] = FALSE;

        $this->app['breadcrumbs']->addItem($this->setLocalization('Movie list'), $this->app['controller_alias'] . '/video-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add movie'));

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
        $screenshot = $this->db->getScreenshotData(array('media_id' => $this->oneVideo['id']));
        if(!empty($screenshot)){
            $this->oneVideo['cover_id'] = $screenshot[0]['id'];
            $this->app['curr_cover_dir'] = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/') . "screenshots/" .  ceil(intval($this->oneVideo['cover_id']) / 100);
            $this->app['cover_ext'] = '.' . end(explode('.', $screenshot[0]['name']));
        } else {
            $this->app['curr_cover_dir'] = '';
            $this->app['cover_ext'] = '';
        }

        $this->prepareFormVideoCategories();
        $this->prepareOneVideo();
        $form = $this->buildForm($this->oneVideo);
        
        if ($this->saveVideoData($form, TRUE)) {
            return $this->app->redirect('video-list');
        }

        $this->app['form'] = $form->createView();
        $this->app['videoEdit'] = TRUE;
        $this->app['videoName'] = $this->oneVideo['name'];

        $this->app['breadcrumbs']->addItem($this->setLocalization('Movie list'), $this->app['controller_alias'] . '/video-list');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit movie'));

        return $this->app['twig']->render('VideoClub_add_video.twig');
    }
    
    public function video_schedule() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $allTasks = $this->video_schedule_list_json();
        $this->app['allTasks'] = $allTasks['data'];
        $this->app['recordsFiltered'] = $allTasks['recordsFiltered'];
        $this->app['totalRecords'] = $allTasks['recordsTotal'];

        $attribute = $this->getVideoScheduleDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function video_advertise() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $allAds= $this->video_advertise_list_json();
        $this->app['ads'] = $allAds['data'];
        $this->app['recordsFiltered'] = $allAds['recordsFiltered'];
        $this->app['totalRecords'] = $allAds['recordsTotal'];

        $attribute = $this->getVideoAdvertiseDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

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
        $this->app['breadcrumbs']->addItem($this->setLocalization('Advertising'), $this->app['controller_alias'] . '/video-advertise');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add commercial'));
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
        $this->app['adsTitle'] = $this->ad['title'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Advertising'), $this->app['controller_alias'] . '/video-advertise');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit commercial'));
        return $this->app['twig']->render('VideoClub_add_video_ads.twig');
    }
    
    public function video_moderators_addresses() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $allModerators= $this->video_moderators_addresses_list_json();
        $this->app['ads'] = $allModerators['data'];
        $this->app['Moderators'] = $allModerators['data'];
        $this->app['recordsFiltered'] = $allModerators['recordsFiltered'];
        $this->app['totalRecords'] = $allModerators['recordsTotal'];

        $attribute = $this->getVideoModeratorsAddressesDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;


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
        $this->app['active_alias'] = 'video-moderators-addresses';
        $this->app['breadcrumbs']->addItem($this->setLocalization('Moderators'), $this->app['controller_alias'] . '/video-moderators-addresses');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add moderator'));
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
        $this->mod = $this->db->getModerators(array('where' => array('id' => $id)));
        $this->mod['disable_vclub_ad'] = (bool)$this->mod['disable_vclub_ad'];
        $form = $this->buildModForm($this->mod);
        
        if ($this->saveVideoModData($form)) {
            return $this->app->redirect('video-moderators-addresses');
        }       
        
        $this->app['form'] = $form->createView();
        $this->app['modEdit'] = TRUE;
        $this->app['modName'] = $this->mod['name'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Moderators'), $this->app['controller_alias'] . '/video-moderators-addresses');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit moderator'));
        return $this->app['twig']->render('VideoClub_add_video_moderators.twig');
    }

    public function video_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $logs = $this->video_logs_json();
        $this->app['totalRecords'] = $logs['recordsTotal'];
        $this->app['recordsFiltered'] = $logs['recordsFiltered'];
        $this->app['allVideoLogs'] = $logs['data'];

        $attribute = $this->getVideoLogsDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $param = (!empty($this->data)? $this->data: array());

        if (!empty($param['video_id'])) {
            $video = $this->db->getVideoByParam(array('id' => $param['video_id']));
            $this->app['breadcrumbs']->addItem($video['name']);
        }


        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function video_categories(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getVideoCategoriesDropdownAttribute();
        $list = $this->video_categories_list_json();

        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function video_genres(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getVideoGenresDropdownAttribute();
        $list = $this->video_genres_list_json();

        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $allCategories = $this->db->getCategoriesGenres();

        if (isset($allCategories) && is_array($allCategories) && count($allCategories) > 0) {
            $this->app['allCategories'] = $this->setLocalization($allCategories, 'category_name');
        } else {
            $this->app['allCategories'] = array();
        }

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
            "count" => "`video`.`count` as `count`",
            "counter" => "(`video`.count_second_0_5 + `video`.count_first_0_5) as `counter`",
            "year" => "`video`.`year` as `year`",
            "added" => "CAST(`video`.`added` as CHAR) as `added`",
            "complaints" => "media_claims.sound_counter + media_claims.video_counter as `complaints`",
            "accessed" => "`video`.`accessed` as `accessed`"
        );
        $error = $this->setLocalization("Error");
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
            $query_param['order']['id'] = 'DESC';
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsVideoList();
        $response["recordsFiltered"] = $this->db->getTotalRowsVideoList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getVideoList($query_param);

        $tmp_allTasks = $this->db->getAllModeratorTasks(($this->app['userlogin'] != 'admin' ? $this->app['user_id']: FALSE));
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

        if (!empty($response['data'])) {
            $cat_genres = $this->db->getVideoCategories();
            $cat_genres = $this->setLocalization($cat_genres, 'title');
            $cat_genres = array_combine($this->getFieldFromArray($cat_genres, 'id'), $this->getFieldFromArray($cat_genres, 'title'));
            reset($response['data']);
            while (list($key, $row) = each($response['data'])){
                $response['data'][$key]['RowOrder'] = "dTRow_" . $row['id'];
                $response['data'][$key]['cat_genre'] = array();
                if (!empty($row['cat_genre_id_1'])) {
                    $response['data'][$key]['cat_genre'][] = $this->mb_ucfirst($cat_genres[$row['cat_genre_id_1']]);
                }
                if (!empty($row['cat_genre_id_2'])) {
                    $response['data'][$key]['cat_genre'][] = $this->mb_ucfirst($cat_genres[$row['cat_genre_id_2']]);
                }
                if (!empty($row['cat_genre_id_3'])) {
                    $response['data'][$key]['cat_genre'][] = $this->mb_ucfirst($cat_genres[$row['cat_genre_id_3']]);
                }
                if (!empty($row['cat_genre_id_4'])) {
                    $response['data'][$key]['cat_genre'][] = $this->mb_ucfirst($cat_genres[$row['cat_genre_id_4']]);
                }
                $response['data'][$key]['cat_genre'] = implode(', ', $response['data'][$key]['cat_genre']);
                $response['data'][$key]['added'] = (int) strtotime($response['data'][$key]['added']) * ($this->isAjax? 1000 : 1);
                $response['data'][$key]['task_date_on'] = ((int)$response['data'][$key]['task_date_on']) * ($this->isAjax? 1000 : 1);
                $response['data'][$key]['accessed'] = !empty($response['data'][$key]['accessed']) ? (int)$response['data'][$key]['accessed']: 0;
                $response['data'][$key]['series'] = count(unserialize($row['series']));
                if (!array_key_exists('tasks', $response['data'][$key]) || !is_array($response['data'][$key]['tasks'])) {
                    $response['data'][$key]['tasks'] = array();
                }
                if (array_key_exists($row['id'], $allTasks)) {
                    $response['data'][$key]['tasks'] = $allTasks[$row['id']];
                }
                $response['data'][$key]['on_storages'] = (int) $this->check_video_status($row['id']);
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videoinfo';
        $data['title'] = $this->setLocalization('Information about the video source');
        $data['base_info'] = $this->setLocalization('information not available');
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
                sort($data_s['files']);
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videoremove';
        
        $error = $this->setLocalization('Information not available');
        if ($this->db->videoLogWrite($video, 'video deleted')) {
            $result = $this->db->removeVideoById($media_id);
            if (is_numeric($result)) {
                $error = '';
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function disable_video() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || (!is_numeric($this->postData['videoid']))) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $media_id = intval($this->postData['videoid']);
        $video = $this->db->getVideoById($media_id);
        $data = array();
        $data['action'] = 'videodisable';
        $data['title'] = $this->setLocalization('Publish');
        
        $error = $this->setLocalization('Information not available');;
        if ($this->db->videoLogWrite($video, 'Unpublished')) {

            $this->db->deleteVideoTask(array("video_id" => $media_id));
            $result = $this->db->disableVideoById($media_id);
            if (is_numeric($result)) {
                $this->db->toggleDisableForHDDevices($video, 0);
                $error = '';
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function enable_video() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['videoid']) || 
            (!is_numeric($this->postData['videoid'])) || empty($this->postData['video_on_date'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'videoenable';
        $data['title'] = $this->setLocalization('Unpublished');
        $error = $this->setLocalization('Information not available');
        
        $media_id = intval($this->postData['videoid']);
        $date_on = date("Y-m-d", strtotime($this->postData['video_on_date']));
        
        if ($date_on == date("Y-m-d")) {
            $error = !((bool) $this->db->deleteVideoTask(array("video_id" => $media_id)));
            $video = $this->db->getVideoById($media_id);
            
            if ($this->db->videoLogWrite($video, 'Published')) {
                $result = $this->db->enableVideoById($media_id);
                if (is_numeric($result)) {
                    $this->db->toggleDisableForHDDevices($video, 1);
                    $error = '';
                    if ($result === 0) {
                        $data['nothing_to_do'] = TRUE;
                    }
                }
            }
            $data['status'] = "<span class='txt-success'>" . $this->setLocalization('Published') . "<span>";
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
            $data['status'] = "<span class='txt-info'>" . $this->setLocalization('Scheduled') . ' ' . $this->setLocalization('on') . ' ' . "$data_in[date_on]</span>";
//            $data['video_on_date'] = $date_on;
            $data = array_merge($data_in, $data);
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function get_md5() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['storage_name']) || empty($this->postData['media_name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'setMD5';
        $error = $this->setLocalization('Information not available');

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
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function create_tasks(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'createtasks';
        $error = $this->setLocalization('Information not available');

        if (empty($this->postData['sendData']['id']) || empty($this->postData['sendData']['to_usr']) || empty($this->postData['sendData']['comment'])) {
            $error = $this->setLocalization('Not all fields are filled');
        } else {
            $data_in = $this->postData['sendData'];
            $data_in['task_id'] = $this->db->setModeratorTask($data_in);
            $data_in['uid'] = $_SESSION['uid'];
            $video = $this->db->getVideoById($data_in['id']);
            if ($data_in['task_id'] && $this->db->setModeratorHistory($data_in)){
                $this->db->videoLogWrite($video, serialize(array('task'=>$data_in['task_id'], 'event'=>'task open')), $data_in['to_usr']);
                $error = '';
                $data['task_id'] = $data_in['task_id'];
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkName';
        $error = $this->setLocalization('Name is busy');
        if ($this->db->checkName($this->postData)) {
            $data['chk_rezult'] = $this->setLocalization('Name is busy');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_moderator_mac() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['mac'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkModMac';
        $error = $this->setLocalization("Address is busy");
        if (preg_match('/([0-9a-fA-F]{2}([:]|$)){6}$/', trim($this->postData['mac']))) {
            $params = array('mac' => trim($this->postData['mac']));
            if (!empty($this->postData['id'])) {
                $params['id<>'] = $this->postData['id'];
            }
            if ($this->db->checkModMac($params)) {
                $data['chk_rezult'] = $this->setLocalization("Address is busy");
            } else {
                $data['chk_rezult'] = $this->setLocalization("Address is available");
                $error = '';
            }
        } else {
            $data['chk_rezult'] = $this->setLocalization("Error: Not valid mac address");
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function edit_cover() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if (empty($this->data['id']) || (!is_numeric($this->data['id']) && strpos($this->data['id'], 'new') === FALSE)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        } 
        
        $data = array();
        $data['action'] = 'editCover';
        $error = $this->setLocalization("Information not available");

        if (!empty($_FILES)){
            list($f_key, $tmp) = each($_FILES);
            if (is_uploaded_file($tmp['tmp_name']) && preg_match("/jpeg|jpg|png/",$tmp['type'])){

                if ($this->data['id'] != 'new' && is_numeric($this->data['id'])) {
                    $screenshot = $this->db->getScreenshotData(array('id' => $this->data['id']));
                    if (!empty($screenshot)) {
                        $file_info = pathinfo($screenshot[0]['name']);
                        $this->db->removeScreenshotData($this->data['id']);
                        $img_path = $this->getCoverFolder($this->data['id']);
                        $img_path = str_replace(str_replace('/admin', '', $this->baseDir), "", $img_path);
                        @unlink($this->baseDir. rtrim(\Config::getSafe('portal_url', '/stalker_portal/'), "/") . $img_path.'/'.$this->data['id'].'.'.$file_info['extension']);
                    }
                }

                $s_data = array(
                    'name' => $tmp['name'],
                    'size' => $tmp['size'],
                    'type' => $tmp['type']
                );

                $upload_id = $this->db->saveScreenshotData($s_data);
                $img_path = $this->getCoverFolder($upload_id);
                umask(0);
                try{
                    $uploaded = $this->request->files->get($f_key)->getPathname();
                    $ext = end(explode('.', $s_data['name']));
                    $this->app['imagine']->open($uploaded)->resize(new Box(240, 320))->save($img_path."/$upload_id.$ext");
                    chmod($img_path."/$upload_id.$ext", 0644);
                    $error = '';
                } catch (\ImagickException $e) {
                    $error = sprintf(_('Error during file moving from %s to %s'), $tmp['tmp_name'], $img_path."/$upload_id.$ext");
                }
            }
        }
        $img_path = str_replace(str_replace('/admin', '', $this->baseDir), "", $img_path);
        $response = $this->generateAjaxResponse(array('pic' => $this->baseHost . rtrim(\Config::getSafe('portal_url', '/stalker_portal/')) . $img_path."/$upload_id.$ext"), $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function delete_cover() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['cover_id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteCover';
        $error = $this->setLocalization('Failed');
        $img_path = $this->getCoverFolder($this->postData['cover_id']);
        $screenshot = $this->db->getScreenshotData(array('id' => $this->postData['cover_id']));
        $ext = !empty($screenshot[0]['name']) ? end(explode('.', $screenshot[0]['name'])): '';
        $filename = $img_path . '/' . $this->postData['cover_id'] . ".$ext";

        if ($this->db->removeScreenshotData($this->postData['cover_id']) && is_file($filename)) {
            try{
                unlink($img_path . '/' . $this->postData['cover_id'] . ".$ext");
                $error = '';
                $data['msg'] = $this->setLocalization('Deleted');
            } catch (\Exception $e){
                $error = $this->setLocalization('image file has not been deleted') . ', ';
                $error .= $this->setLocalization('image name') . ' - "' . $this->postData['cover_id'] . ".$ext" . '", ';
                $error .= $this->setLocalization('file can be deleted manually from screenshot directory');
                $data['msg'] = $error;
            }
        } else {
            $data['msg'] = $error = $this->setLocalization("No information about") . ' - "' . $this->postData['cover_id'] . ".$ext\" " . $this->setLocalization('or file is not exists');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function update_rating_kinopoisk() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['data'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'updateRatingKinopoisk';
        $error = $this->setLocalization('No data');
        
        try {
            $data['result'] = \Vclubinfo::getRatingByName($this->postData['data']);
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
        $error = $this->setLocalization('No data');
        
        try {
            $data['result'] = \Vclubinfo::getInfoByName($this->postData['data']);
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'getKinopoiskInfoById';
        $error = $this->setLocalization('No data');
        
        try {
            $data['result'] = \Vclubinfo::getInfoById($this->postData['data']);
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
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'getImage';
        $error = $this->setLocalization('No data');

        if (strpos($this->data['url'], 'http://') === 0 && (strpos($this->data['url'], 'kinopoisk.ru/') || strpos($this->data['url'], 'image.tmdb.org/'))){
            $img = file_get_contents($this->data['url']);
            if (!empty($img)) {
                echo $img;
                exit;
            }
        }
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function video_schedule_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filds_for_select = $this->getVideoScheduleFields();

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        foreach($query_param['order'] as $key => $val){
            if ($search = array_search($key, $filds_for_select )){
                $new_key = str_replace(" as $search", '', $key);
                unset($query_param['order'][$key]);
                $query_param['order'][$new_key] = $val;
            }
        }

        if (!isset($query_param['like'])) {
            $query_param['like'] = array();
        }

        $response['recordsTotal'] = $this->db->getTotalRowsAllVideoTasks();
        $response["recordsFiltered"] = $this->db->getTotalRowsAllVideoTasks($query_param['where'], $query_param['like']);
        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $allTasks = $this->db->getAllVideoTasks($query_param);

        $scheduled_on = $this->setLocalization('scheduled') . ' ' .$this->setLocalization('on');

        if (is_array($allTasks)) {
            reset($allTasks);
            while (list($num, $row) = each($allTasks)) {
                $allTasks[$num]['date_on'] = strtotime($row['date_on']);
                if ($allTasks[$num]['date_on'] < 0) {
                    $allTasks[$num]['date_on'] = 0;
                }
                $allTasks[$num]['task_added'] = strtotime($row['task_added']);
                if ($allTasks[$num]['task_added'] < 0) {
                    $allTasks[$num]['task_added'] = 0;
                }
                $allTasks[$num]['tasks'] = "<span data-task-state=1>$scheduled_on " . strftime('%d-%m-%Y', $allTasks[$num]['date_on']) . "</span>";
            }
            $response["data"] = $allTasks;
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

    public function video_advertise_list_json() {

        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $ad = new \VclubAdvertising();
        $self = $this;
        $response["data"] = array_map(function($row) use ($self){
            if (!is_numeric($row['must_watch'])) {
                $row['must_watch'] = $self->setLocalization($row['must_watch']);
            }
            settype($row['status'], 'int');
            return $row;
        }, $ad->getAllWithStatForMonth());

        $response["recordsFiltered"] = $response["recordsTotal"] = (string) count($response["data"]);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        $error = "";

        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function remove_tasks() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['taskid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'removeTasks';
        $error = $this->setLocalization('Failed');

        $result = $this->db->deleteVideoTask(array('id'=>$this->postData['taskid']));
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_video_ads() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['adsid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'removeAds';
        $error = $this->setLocalization('Failed');
        $ad = new \VclubAdvertising();
        
        $result = $ad->delById($this->postData['adsid']);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_video_ads_status() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['adsid']) || !isset($this->postData['adsstatus'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'toggleVideoAdsStatus';
        $error = $this->setLocalization('Failed');
        $ad = new \VclubAdvertising();
        
        if ($ad->updateById((int) $this->postData['adsid'], array('status' => (int) $this->postData['adsstatus'], 'denied_categories' => $ad->getDeniedVclubCategoriesForAd((int) $this->postData['adsid'])))) {
            $error = '';
            $data['title'] = ($this->postData['adsstatus'] ? $this->setLocalization('Unpublish'): $this->setLocalization('Publish'));
            $data['status'] = '<span data-filter="status" >' .($this->postData['adsstatus'] ?  $this->setLocalization('Published') : $this->setLocalization('Not published')) . '</span>';
            $data['adsstatus'] = (int)!$this->postData['adsstatus'];
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function video_moderators_addresses_list_json() {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $error = $this->setLocalization("Error");
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filds_for_select = $this->getVideoModeratorsAddressesFields();

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        foreach($query_param['order'] as $key => $val){
            if ($search = array_search($key, $filds_for_select )){
                $new_key = str_replace(" as $search", '', $key);
                unset($query_param['order'][$key]);
                $query_param['order'][$new_key] = $val;
            }
        }

        if (!isset($query_param['like'])) {
            $query_param['like'] = array();
        }

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['recordsTotal'] = $this->db->getTotalRowsModerators();
        $response['recordsFiltered'] = $this->db->getTotalRowsModerators($query_param['where'], $query_param['like']);

        $allModerators = $this->db->getModerators($query_param);
        if (is_array($allModerators)) {
            $response["data"] = array_map(function ($row) {
                settype($row['status'], 'int');
                settype($row['disable_vclub_ad'], 'int');
                return $row;
            }, $allModerators);
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

    public function remove_video_moderators() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['modid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'manageList';
        $error = '';
        $this->db->deleteModeratorsById($this->postData['modid']);
        
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_video_moderators_status() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['modid']) || !isset($this->postData['modstatus'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }
        
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'manageList';
        $error = $this->setLocalization('Failed');
        
        if ($this->db->updateModeratorsById((int) $this->postData['modid'], array('status' => (int) $this->postData['modstatus']))) {
            $error = '';
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
            '`video`.`name`'=>'IF(ISNULL(`video`.`name`), `video_log`.`video_name`, `video`.`name`) as `video_name`',
            'action'=>'`action`'
        );
        
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        
        $param = (!empty($this->data)? $this->data: array());
        
        $query_param = $this->prepareDataTableParams($param);

        if (!array_key_exists('where', $query_param)) {
            $query_param['where'] = array();
        }

        if($this->app['userlogin'] == 'admin') {
            $query_param['where']["moderator_id"] = $this->app['user_id'];
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
            $query_param['limit']['limit'] = 50;
        }
        $response['data'] = $this->db->getVideoLog($query_param);
        $response['data'] = array_map(function($row){
            $row['actiontime'] = (int)  strtotime($row['actiontime']);
            return $row;
        }, $response['data']);
        $response['data'] = $this->setLocalization($response['data'], 'action');

        $this->setLinksForVideoLog($response['data']);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw']: 1;
        
        
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function video_categories_list_json(){

        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        $error = $this->setLocalization('Error');
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_', 'localized_title', 'RowOrder'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filds_for_select = $this->getVideoCategoryFields();

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $response['recordsTotal'] = $this->db->getTotalRowsCategoriesGenresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsCategoriesGenresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
            $query_param['select'][] = 'id';
        }

        $query_param['order']['num'] = 'ASC';

        $self = $this;
        $response['data'] = array_map(function($row) use ($self){
            $row['localized_title'] = $self->setLocalization($row['category_name']);
            $row['RowOrder'] = "dTRow_" . $row['id'];
            return $row;
        }, $this->db->getCategoriesGenres($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function video_categories_reorder() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $matches = array();
        $data = array();
        $data['action'] = 'reorder';
        $data['msg'] = $error = $this->setLocalization('error');
        if (preg_match("/(\d+)/i", $this->postData['id'], $matches) && preg_match("/(\d+)/i", $this->postData['target_id'], $matches_1)){
            if ($this->db->mowingCategoriesRows($matches[1], $this->postData['fromPosition'], $this->postData['toPosition'], $this->postData['direction'])){
                $error = '';
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function add_video_categories(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['category_name']) || empty($this->postData['num'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addVideoCategory';
        $error = $this->setLocalization('Failed');

        $category_alias  = $this->transliterate($this->postData['category_name']);

        $check = $this->db->getCategoriesGenres(array(
            'where' => array(
                'category_name' => $this->postData['category_name'],
                'category_alias' => $category_alias,
                'num' => $this->postData['num']
            )));

        if (empty($check)) {
            $data['id']  = $this->db->insertCategoriesGenres(array('category_name' => $this->postData['category_name'], 'num' => $this->postData['num'], 'category_alias' => $category_alias));
            $data['category_name'] = $this->postData['category_name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_video_categories(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['category_name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editVideoCategory';
        $error = $this->setLocalization('Failed');

        $check = $this->db->getCategoriesGenres(array(
            'select'=>array('*'),
            'where' => array(
                'category_name' => $this->postData['category_name'],
                'num' => $this->postData['num'],
                'id<>' => $this->postData['id']
            ),
            'order' => array('category_name' => 'ASC'),
            'like' => array()
        ));
        if (empty($check)) {
            $this->db->updateCategoriesGenres(array(
                'category_name' => $this->postData['category_name'],
                'num' => $this->postData['num']
            ), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['category_name'] = $this->postData['category_name'];
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_video_categories(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['categoriesid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeVideoCategory';
        $data['id'] = $this->postData['categoriesid'];
        $this->db->mowingCategoriesRows($this->postData['categoriesid'], $this->postData['curr_pos'], 1000000, 'forward');
        $this->db->deleteCategoriesGenres(array('id' => $this->postData['categoriesid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_video_categories_name(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['category_name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkVideoCategory';
        $error = $this->setLocalization('Name already used');

        $add = (array_key_exists('edit', $this->postData) && (strtolower((string)$this->postData['edit']) == 'false' || $this->postData['edit'] === FALSE));

        if ($this->db->getCategoriesGenres(array('where' => array(' BINARY category_name' => $this->postData['category_name']))) ||
            ($add ? $this->db->getCategoriesGenres(array('where' => array(' BINARY category_alias' => $this->transliterate($this->postData['category_name'])))): 0)) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));

    }

    public function video_genres_list_json(){

        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'openModalBox'
        );

        $error = $this->setLocalization('Error');
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', '_', 'localized_title', 'category','RowOrder'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (!empty($this->postData['id'])) {
            $query_param['where']['cat_genre.id'] = $this->postData['id'];
        }

        $filter = $this->getVideoListFilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);

        $filds_for_select = $this->getVideoCategoryGenresFields();

        $query_param['select'] = array_values($filds_for_select);

        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);

        $response['recordsTotal'] = $this->db->getTotalRowsVideoCatGenresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsVideoCatGenresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (empty($query_param['select'])) {
            $query_param['select'][] = '*';
        }
        $query_param['select'][] = 'cat_genre.id as id';
        $query_param['select'][] = 'media_category.id as category_id';

        if (!in_array('category_name', $query_param['select'])){
            $query_param['select'][] = 'category_name';
        }

        $query_param['order']['title'] = 'ASC';

        $self = $this;
        $response['data'] = array_map(function($row) use ($self){
            $row['localized_title'] = $self->setLocalization($row['title']);
            $row['category'] = $self->setLocalization($row['category_name']);
            $row['RowOrder'] = "dTRow_" . $row['id'];
            return $row;
        }, $this->db->getVideoCatGenres($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function save_video_genres(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['title']) || empty($this->postData['category_alias'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageGenre';
        $error = $this->setLocalization('Failed');

        $where = array(
            'cat_genre.title' => $this->postData['title'],
            'cat_genre.category_alias' => $this->postData['category_alias']
        );
        $operation_params = array(
            'data' => array_filter($this->postData)
        );

        if (!empty($this->postData['id'])) {
            $operation = 'update';
            $where['cat_genre.id<>'] = $this->postData['id'];
            $operation_params['where'] = array('cat_genre.id' => $operation_params['data']['id']);
            unset($operation_params['data']['id']);
        } else {
            $operation = 'insert';
        }

        $check = $this->db->getVideoCatGenres(array('where' => $where));

        if (empty($check)) {
            $error = '';
            $data['msg'] = $this->setLocalization(($operation == 'insert') ? 'Inserted' : 'Updated') . ' ' . call_user_func(array($this->db, $operation."VideoCatGenres"), $operation_params);
        } else {
            $error = $this->setLocalization('In this category already exists such a genre');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_video_genres(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['genresid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteGenre';
        $error = $this->setLocalization('Failed');
        if ($result = $this->db->deleteVideoCatGenres(array('id' => $this->postData['genresid']))) {
            $error = '';
            $data['msg'] = $this->setLocalization('Deleted') . ' ' . $result;
        }

        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function getVideoListFilters()
    {
        $filters = array();
        if (array_key_exists('filters', $this->data)) {

            if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
                if ($this->data['filters']['status_id'] != 3) {
                    $filters['`accessed`'] = $this->data['filters']['status_id'] - 1;
                } else {
                    $filters["not isnull(`video_on_tasks`.`id`) and '1'"] = '1';
                }
            }

            if (array_key_exists('year', $this->data['filters']) && $this->data['filters']['year'] != 0) {
                $filters["`year`"] = $this->data['filters']['year'];
            }

            if (array_key_exists('genre_id', $this->data['filters']) && $this->data['filters']['genre_id'] != 0) {
                $genre_id = $this->data['filters']['genre_id'];
                $filters["(`cat_genre_id_1` in ($genre_id) OR `cat_genre_id_2` in ($genre_id) OR `cat_genre_id_3` in ($genre_id) OR `cat_genre_id_4` in ($genre_id)) AND 1"] = "1";
            }

            if (array_key_exists('category_id', $this->data['filters']) && $this->data['filters']['category_id'] != 0) {
                $filters["media_category.id"] = $this->data['filters']['category_id'];
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();

        }

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

        $cat_genres = array(''=>'');
        foreach ($this->app['catGenres'] as $row) {
            $cat_genres[$row['id']] = $row['category_name'];
        }
        
        $cat_video = array();
        foreach ($this->app['videoCategories'] as $row) {
            $cat_video[$row['id']] = $row['title'].$row['id'];
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
                /*+*/->add('o_name', 'text', array('required' => FALSE))
                /*кинопосик ИД*/
                /*+*/->add('kinopoisk_id', 'text', array('constraints' => array(new Assert\Type(array('type' => 'numeric'))), 'required' => FALSE))
                /*+*/->add('rating_kinopoisk', 'text', array('constraints' => array(new Assert\Type(array('type' => 'numeric'))), 'required' => FALSE))
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
                                    new Assert\Choice(array('choices' => array_keys($genres), 'multiple' => TRUE))
                                ),
                            'multiple' => TRUE
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
                                    new Assert\NotBlank()
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
            'all' => $this->setLocalization('All'),
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
                                                new Assert\NotBlank(),
                                                new Assert\Regex('/([0-9a-fA-F]{2}([:]|$)){6}$/')
                                            ),
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
    
    private function saveVideoData(&$form, $edit = FALSE) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                if (empty($data['id'])) {
                    $is_repeating_name = $this->db->checkName($data);
                    $operation = 'insertVideo';
                } elseif (isset($this->oneVideo)) {
                    $check_name = (bool) $this->db->checkName(array('id<>'=>$data['id'], 'name' => $data['name']));
                    $is_repeating_name = empty($this->oneVideo['name']) || $check_name;
                    $operation = 'updateVideo';
                }
                if (!$is_repeating_name) {
                    $series = array();
                    if ($data['protocol'] == 'custom') {
                        if (empty($data['rtsp_url'])) {
                            $error_local = array();
                            $error_local['rtsp_url'] = ($is_repeating_name ? $this->setLocalization('If the protocol') . " - '$data[protocol]', " . $this->setLocalization('then this field must be filled') : '');
                            $this->app['error_local'] = $error_local;
                            return FALSE;
                        } elseif(!preg_match('/^(\w+\s)?\w+\:\/\/.*$/i', $data['rtsp_url'])) {
                            $error_local = array();
                            $error_local['rtsp_url'] = ($is_repeating_name ? $this->setLocalization('Invalid format links') : '');
                            $this->app['error_local'] = $error_local;
                            return FALSE;
                        } else {
                            if (preg_match("/s\d+e(\d+).*$/i", $data['rtsp_url'], $tmp_arr)) {
                                $series = range(1, (int)$tmp_arr[1], 1);
                            }
                        }
                    }
                    $data['trans_name'] = $this->transliterate(@urldecode($data['name']));
                    if ($data['hd']) {
                        $data['trans_name'] .= '_HD';
                    }

                    $db_data = array(
                        'name' => trim($data['name']),
                        'series' => serialize($series),
                        'o_name' => trim($data['o_name']),
                        'censored' => $data['censored'],
                        'hd' => $data['hd'],
                        'for_sd_stb' => $data['for_sd_stb'],
                        'protocol' => $data['protocol'],
                        'rtsp_url' => trim($data['rtsp_url']),
                        'time' => @$data['duration'],
                        'description' => $data['description'],
                        'genre_id_1' => (array_key_exists(0, $data['genres']) ? $data['genres'][0] : 0),
                        'genre_id_2' => (array_key_exists(1, $data['genres']) ? $data['genres'][1] : 0),
                        'genre_id_3' => (array_key_exists(2, $data['genres']) ? $data['genres'][2] : 0),
                        'genre_id_4' => (array_key_exists(3, $data['genres']) ? $data['genres'][3] : 0),
                        'cat_genre_id_1' => (array_key_exists(0, $data['cat_genre_id']) ? $data['cat_genre_id'][0] : 0),
                        'cat_genre_id_2' => (array_key_exists(1, $data['cat_genre_id']) ? $data['cat_genre_id'][1] : 0),
                        'cat_genre_id_3' => (array_key_exists(2, $data['cat_genre_id']) ? $data['cat_genre_id'][2] : 0),
                        'cat_genre_id_4' => (array_key_exists(3, $data['cat_genre_id']) ? $data['cat_genre_id'][3] : 0),
                        'category_id' => $data['category_id'],
                        'director' => $data['director'],
                        'actors' => $data['actors'],
                        'status' => (int)!empty($data['rtsp_url']),
                        'year' => $data['year'],
                        'volume_correction' => (int)$data['volume_correction'],
                        'kinopoisk_id' => $data['kinopoisk_id'],
                        'rating_kinopoisk' => $data['rating_kinopoisk'],
                        'rating_count_kinopoisk' => $data['rating_count_kinopoisk'],
                        'rating_imdb' => $data['rating_imdb'],
                        'rating_count_imdb' => $data['rating_count_imdb'],
                        'age' => $data['age'],
                        'rating_mpaa' => $data['rating_mpaa'],
                        'high_quality' => $data['high_quality'],
                        'low_quality' => $data['low_quality'],
                        'comments' => $data['comments'],
                        'country' => $data['country']
                    );
                    if ($operation == 'insertVideo') {
                        $this->createMediaStorage($data['trans_name'], $data['year']);
                        $db_data['path'] = $data['trans_name'] . (!empty($data['year']) ? "_$data[year]": '');
                        $db_data['added'] = 'NOW()';
                        $id = $this->db->$operation($db_data);
                        $db_data['id'] = $id;
                        $this->db->videoLogWrite($db_data, 'added');
                    } else {
                        $id = $data['id'];
                        $this->db->$operation($db_data, $id);
                        $db_data['id'] = $id;
                        $this->db->videoLogWrite($db_data, 'edited');
                    }
                    $cover_id = (!empty($data['cover_big']) ? $this->getExternalImage($data['cover_big']) : (!empty($data['cover_id']) ? $data['cover_id'] : FALSE));
                    if ($cover_id !== FALSE) {
                        $this->db->updateScreenshotData($id, $cover_id);
                    }
//                    $this->db->cleanScreenshotData();
                    return TRUE;
                } else {
                    $error_local = array();
                    $error_local['name'] = ($is_repeating_name ? $this->setLocalization('This name already exists') : '');
                    $this->app['error_local'] = $error_local;
                    return FALSE;
                }
            }
        }
        return FALSE;
    }

    private function prepareFormVideoCategories(){
        $videoGenres = $this->db->getVideoGenres();
        $this->app['videoGenres'] = $this->getUCArray($this->setLocalization($videoGenres, 'title'), 'title');
        
        $catGenres = $this->db->getCategoriesGenres();
        $this->app['catGenres'] = $this->getUCArray($this->setLocalization($catGenres, 'category_name'), 'category_name');
        
        $videoCategories = $this->db->getVideoCategories();
        $this->app['videoCategories'] = $this->getUCArray($this->setLocalization($videoCategories, 'title'), 'title');
        
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
        
        $cover_blob = @file_get_contents($url);

        if ($cover_blob !== FALSE) {
            try {
                $s_data = array();
                $s_data['name'] = substr($url, strrpos($url, '/') + 1);
                $s_data['size'] = 0;
                $s_data['type'] = '';
                $cover_id = $this->db->saveScreenshotData($s_data);
                
                $ext = end(explode('.', $s_data['name']));
                $img_path = $this->getCoverFolder($cover_id);
                umask(0);
                
                $this->app['imagine']->load($cover_blob)->resize(new Box(240, 320))->save($img_path."/$cover_id.$ext");
                chmod($img_path."/$cover_id.$ext", 0644);
                
                $tmp = getimagesize($img_path."/$cover_id.$ext");
                $s_data['type'] = $tmp['mime'];
                $s_data['size'] = filesize($img_path."/$cover_id.$ext");
                
                $this->db->updateScreenshotData($s_data, $cover_id);
                
            } catch (\ImagickException $e){
                $error = $this->setLocalization('Error: could not save cover image') . '. ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = $this->setLocalization('Error: could not save cover image') . '. ' . $e->getMessage();
            }
        }
        return (isset($cover_id) ? $cover_id: FALSE);
    }
    
    private function createMediaStorage($trans_name, $additional = '') {

        $existed = $this->db->getVideoByParam(array('path' => $trans_name));

        if (!empty($existed)) {
            $error = $this->setLocalization('Error: The folder with that name already exists');
        } else {
            $_SERVER['TARGET'] = 'ADM';
            $master = new \VideoMaster();
            try {
                $master->createMediaDir($trans_name, $additional);
            } catch (\MasterException $e) {
                //var_dump($e->getMessage(), $e->getStorageName()); exit;
                $moderator_storages = $master->getModeratorStorages();
                if (!empty($moderator_storages[$e->getStorageName()])) {
                    $error = $this->setLocalization('Error creating the folder on moderator storage');
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
        reset($data);
        while(list($key, $row) = each($data)){
            $data[$key]['video_name'] = "<a href='$this->workURL/" . $this->app['controller_alias'] . "/edit-video?id=$row[video_id]'>$row[video_name]</a>";
            if ($action = @unserialize($row['action'])) {
                $data[$key]['action'] = strtr($action_link_template, array("{action[task]}" => $action['task'], "{action[event]}" => $this->mb_ucfirst($this->setLocalization($action['event']))));
            } else {
                $matches = array();
                $c = preg_match_all("/task\=(\d*)[^\>]*\>([^\<]*)\</i", stripcslashes($row['action']), $matches);
                if (count($matches) >= 2 && !empty($matches[1][0]) && !empty($matches[2][0])) {
                    $data[$key]['action'] = strtr($action_link_template, array("{action[task]}" => $matches[1][0], "{action[event]}" => $this->mb_ucfirst($this->setLocalization($matches[2][0]))));
                } 
            }
        }
    }
    
    private function getVideoListDropdownAttribute(){
        return array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),            'checked' => TRUE),
            array('name' => 'path',         'title' => $this->setLocalization('Catalogue'),     'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'o_name',       'title' => $this->setLocalization('Original title'),'checked' => FALSE),
            array('name' => 'time',         'title' => $this->setLocalization('Length, min'),   'checked' => TRUE),
            array('name' => 'series',       'title' => $this->setLocalization('Episodes'),      'checked' => TRUE),
            array('name' => 'cat_genre',    'title' => $this->setLocalization('Genre'),         'checked' => TRUE),
            array('name' => 'year',         'title' => $this->setLocalization('Year'),          'checked' => TRUE),
            array('name' => 'added',        'title' => $this->setLocalization('Date'),          'checked' => TRUE),
            array('name' => 'tasks',        'title' => $this->setLocalization('Tasks'),         'checked' => TRUE),
            array('name' => 'count',        'title' => $this->setLocalization('Views lifetime'),'checked' => FALSE),
            array('name' => 'counter',      'title' => $this->setLocalization('Views last month'),'checked' => FALSE),
            array('name' => 'complaints',   'title' => $this->setLocalization('Complaints'),    'checked' => TRUE),
            array('name' => 'accessed',       'title' => $this->setLocalization('Status'),        'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),    'checked' => TRUE)
        );
        
    }

    private function getVideoScheduleDropdownAttribute(){
        return array(
            array('name' => 'task_added',   'title' => $this->setLocalization('Date'),          'checked' => TRUE),
            array('name' => 'name',         'title' => $this->setLocalization('Title'),         'checked' => TRUE),
            array('name' => 'o_name',       'title' => $this->setLocalization('Original title'),'checked' => TRUE),
            array('name' => 'time',         'title' => $this->setLocalization('Length, min'),   'checked' => TRUE),
            array('name' => 'tasks',        'title' => $this->setLocalization('Tasks'),         'checked' => TRUE),
            array('name' => 'year',         'title' => $this->setLocalization('Year'),          'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),    'checked' => TRUE)
        );
    }

    private function getVideoAdvertiseDropdownAttribute(){
        return array(
            array('name' => 'title',        'title' => $this->setLocalization("Title"),                     'checked' => TRUE),
            array('name' => 'url',          'title' => $this->setLocalization("Address"),                   'checked' => TRUE),
            array('name' => 'weight',       'title' => $this->setLocalization("Weight"),                    'checked' => TRUE),
            array('name' => 'started',      'title' => $this->setLocalization("Views started"),             'checked' => TRUE),
            array('name' => 'ended',        'title' => $this->setLocalization("Views counted"),             'checked' => TRUE),
            array('name' => 'must_watch',   'title' => $this->setLocalization("Necessary to view")." (%)",  'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization("Status"),                    'checked' => TRUE),
            array('name' => 'operations',   'title' => $this->setLocalization("Operations"),                'checked' => TRUE)
        );
    }

    private function prepareNewGenresListIds($all_genre_list = array()){
        $all_genre_list = $this->setLocalization($all_genre_list, 'title');
        $return_list = array();
        foreach($all_genre_list as $row){
            $row["title"] = $this->mb_ucfirst($row["title"]);
            if (array_key_exists($row['title'], $return_list)) {
                $return_list["$row[title]"]['id'] .= ",$row[id]";
            } else {
                $return_list["$row[title]"] = array('id'=>$row['id'], 'title' => $row["title"]);
            }
        }
        ksort($return_list);
        return array_combine(range(0, count($return_list)-1), array_values($return_list));
    }

    private function getVideoCategoriesDropdownAttribute(){
        return array(
            array('name'=>'num',                'title'=>$this->setLocalization('Number'),          'checked' => TRUE),
            array('name'=>'category_name',      'title'=>$this->setLocalization('Title'),           'checked' => TRUE),
            array('name'=>'localized_title',    'title'=>$this->setLocalization('Localized title'), 'checked' => TRUE),
            array('name'=>'genre_in_category',  'title'=>$this->setLocalization('Genres in category'), 'checked' => TRUE),
            array('name'=>'movie_in_category',  'title'=>$this->setLocalization('Movies in category'), 'checked' => TRUE),
            array('name'=>'operations',         'title'=>$this->setLocalization('Operation'),       'checked' => TRUE)
        );
    }

    private function getVideoGenresDropdownAttribute(){
        return array(
            array('name'=>'title',          'title'=>$this->setLocalization('Title'),           'checked' => TRUE),
            array('name'=>'localized_title','title'=>$this->setLocalization('Localized title'), 'checked' => TRUE),
            array('name'=>'category',       'title'=>$this->setLocalization('Category'),        'checked' => TRUE),
            array('name'=>'movie_in_genre', 'title'=>$this->setLocalization('Movies in genre'), 'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),       'checked' => TRUE)
        );
    }

    private function check_video_status($id){

        $video = \Video::getById($id);

        if (!empty($video['rtsp_url'])){
            return 2;
        }

        return $video['status'];
    }

    private function getVideoScheduleFields(){
        return array(
            'task_added' => 'video_on_tasks.added as `task_added`',
            'name' => 'video.name as `name`',
            'o_name' => 'video.o_name as `o_name`',
            'time' => 'video.time as `time`',
            'tasks' => '"" as `tasks`',
            'year' => 'video.year as `year`',
            'task_id' => 'video_on_tasks.id as `task_id`',
            'video_id' => 'video_on_tasks.video_id as `video_id`',
            'date_on' => 'DATE_FORMAT(video_on_tasks.date_on, "%Y-%m-%d %H:%i:%s") as `date_on`',
            'id' => 'video_on_tasks.id as `id`'
        );
    }

    private function getVideoModeratorsAddressesDropdownAttribute(){
        return array(
            array('name'=>'name',           'title'=>$this->setLocalization('Name'),                    'checked' => TRUE),
            array('name'=>'mac',            'title'=>$this->setLocalization('MAC address'),             'checked' => TRUE),
            array('name'=>'disable_vclub_ad','title'=>$this->setLocalization('Advertising is disabled'),'checked' => TRUE),
            array('name'=>'status',         'title'=>$this->setLocalization('Status'),                  'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),               'checked' => TRUE)
        );
    }

    private function getVideoModeratorsAddressesFields(){
        return array(
            'id' => 'moderators.id as `id`',
            'name' => 'moderators.name as `name`',
            'mac' => 'moderators.mac as `mac`',
            'disable_vclub_ad' => 'moderators.disable_vclub_ad as `disable_vclub_ad`',
            'status' => 'moderators.status as `status`'
        );
    }

    private function getVideoLogsDropdownAttribute(){
        return array(
            array('name'=>'video_id',   'title'=>$this->setLocalization('ID'),          'checked' => TRUE),
            array('name'=>'actiontime', 'title'=>$this->setLocalization('Date'),        'checked' => TRUE),
            array('name'=>'video_name', 'title'=>$this->setLocalization('Title'),       'checked' => TRUE),
            array('name'=>'login',      'title'=>$this->setLocalization('Moderator'),   'checked' => TRUE),
            array('name'=>'action',     'title'=>$this->setLocalization('Status'),      'checked' => TRUE)
        );
    }

    private function getVideoCategoryFields(){
        return array(
            'num' => '`media_category`.`num` as `num`',
            'category_name' => '`media_category`.`category_name` as `category_name`',
            'genre_in_category' => 'CAST((SELECT  COUNT(*) FROM `cat_genre` WHERE `cat_genre`.`category_alias` = `media_category`.`category_alias`) as CHAR) as `genre_in_category`',
            'movie_in_category' => 'CAST((SELECT  COUNT(*) FROM `video` WHERE `video`.`category_id` = `media_category`.`id`) as CHAR) as `movie_in_category`'
        );
    }

    private function getVideoCategoryGenresFields(){
        return array(
            'title' => '`cat_genre`.`title` as `title`',
            'category' => '`media_category`.`category_name` as `category`',
            'movie_in_genre' => 'CAST((SELECT  COUNT(*) FROM `video` WHERE `video`.`category_id` = `media_category`.`id` AND (`cat_genre`.`id` = `video`.`cat_genre_id_1` || `cat_genre`.`id` = `video`.`cat_genre_id_2` || `cat_genre`.`id` = `video`.`cat_genre_id_3` || `cat_genre`.`id` = `video`.`cat_genre_id_4`)) as CHAR) as `movie_in_genre`',
        );
    }
}
