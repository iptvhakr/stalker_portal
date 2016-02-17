<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Imagine\Image\Box;

class AudioClubController extends \Controller\BaseStalkerController {

    protected $allStatus = array();

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->allStatus = array(
            array('id' => 1, 'title' => $this->setLocalization('Unpublished')),
            array('id' => 2, 'title' => $this->setLocalization('Published'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/audio-albums');
        }
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function audio_albums() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $allGenre = $this->db->getAllFromTable('audio_genres');
        $allGenre = $this->getUCArray($this->setLocalization($allGenre, 'name'), 'name');
        $this->app['allAudioGenres'] = $allGenre;
        $this->app['allAudioYears'] = $this->setLocalization($this->db->getAllFromTable('audio_years'), 'name');

        $locale = substr($this->app["language"], 0, 2);
        $this->app['allCountries'] = ($locale != 'ru' ? array_map(function($row) use ($locale){ $row['name'] = $row['name_en']; return $row; }, $this->db->getAllFromTable('countries')): $this->db->getAllFromTable('countries'));

        $this->app['allLanguages'] = $this->db->getAllFromTable('audio_languages');
        $this->app['allStatus'] = $this->allStatus;
        
        $attribute = $this->getDropdownAttributeAudioClub();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->audio_albums_list_json();
        
        $this->app['allAudioAlbums'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function add_audio_albums() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $this->app['curr_cover_dir'] = '';
        $form = $this->buildAudioAlbumForm();

        if ($this->saveAudioAlbumData($form)) {
            return $this->app->redirect('audio-albums');
        }
        $this->app['form'] = $form->createView();
        $this->app['audioAlbumEdit'] = FALSE;

        $attribute = $this->getDropdownAttributeAudioComposition();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $this->app['allAlbumComposition'] = array();
        $this->app['totalRecords'] = 0;
        $this->app['recordsFiltered'] = 0;
        $this->app['audioAlbumID'] = -1;
        $this->app['allLanguages'] = $this->db->getAllFromTable('audio_languages');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Albums'), $this->app['controller_alias'] . '/audio-albums');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Add audio album'));
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function edit_audio_albums() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if ($this->method == 'POST' && !empty($this->postData['form']['id'])) {
            $id = $this->postData['form']['id'];
        } else if ($this->method == 'GET' && !empty($this->data['id'])) {
            $id = $this->data['id'];
        } else {
            return $this->app->redirect('add-audio-albums');
        }

        $audio_album = $this->db->getAudioAlbum($id);
        $this->audio_album = (is_array($audio_album) && count($audio_album) > 0) ? $audio_album : array();
        $this->audio_album['genre_ids'] = $this->db->getGenreForAlbum($id, 'genre_id');
        
        $this->app['curr_cover_dir'] = (!empty($this->audio_album['cover']) && strpos($this->audio_album['cover'], 'new') === FALSE)? 'misc/audio_covers/'.ceil($this->audio_album['id']/100).'/': 'misc/audio_covers/new/';
        $form = $this->buildAudioAlbumForm($this->audio_album);

        if ($this->saveAudioAlbumData($form, TRUE)) {
            return $this->app->redirect('audio-albums');
        }
        $this->app['form'] = $form->createView();
        $this->app['audioAlbumEdit'] = TRUE;
        $this->app['audioAlbumID'] = $id;
        $this->app['allLanguages'] = $this->db->getAllFromTable('audio_languages');
        $attribute = $this->getDropdownAttributeAudioComposition();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;
        
        $list = $this->audio_albums_composition_list_json(array('album_id' => $id));
        
        $this->app['allAlbumComposition'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        $this->app['albumName'] = $this->audio_album['name'];
        $this->app['breadcrumbs']->addItem($this->setLocalization('Albums'), $this->app['controller_alias'] . '/audio-albums');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Edit audio album'));
        return $this->app['twig']->render("AudioClub_add_audio_albums.twig");
    }
    
    public function audio_artists() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $this->app['dropdownAttribute'] = $this->getShortDropdownAttribute();
        $list = $this->audio_artists_list_json();
        
        $this->app['allAudioArtists'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function audio_genres() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getShortDropdownAttribute();
        $list = $this->audio_genres_list_json();
        
        $this->app['allAudioGenres'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
       
    public function audio_languages() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getShortDropdownAttribute();
        $list = $this->audio_languages_list_json();
        
        $this->app['allAudioLanguages'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function audio_years() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getShortDropdownAttribute();
        $list = $this->audio_years_list_json();
        
        $this->app['allAudioYears'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
    public function audio_logs() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function audio_albums_list_json($param = array()) {
        
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
                
        $filds_for_select = array(
            "id" => "`audio_albums`.`id` as `id`",
            "name" => "CONCAT_WS(' - ', `audio_performers`.`name`, `audio_albums`.`name`) as `name`",
            "tracks_count" => "(SELECT COUNT(*) FROM `audio_compositions` WHERE `album_id` = `audio_albums`.`id`) as `tracks_count`",
            "ganre_name" => "'' as `ganre_name`",
            "year" => "`audio_years`.`name` as `year`",
            "country" => "`countries`.`name" . (substr($this->app["language"], 0, 2) != 'ru' ? "_en": "" ) . "` as `country`",
            "language" => "0 as `language`",
            "status" => "`audio_albums`.`status` as `status`"
        );
        $error = $this->setLocalization('Error');
        $param = (!empty($this->data) ? $this->data : array());

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $like_filter = array();
        $filter = $this->getAudioFilters($like_filter);
        
        if (empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = $like_filter;
        } elseif (!empty($query_param['like']) && !empty($like_filter)) {
            $query_param['like'] = array_merge($query_param['like'], $like_filter);
        }
        
        $query_param['where'] = array_merge($query_param['where'], $filter);

        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'audio_albums.id as id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        $response['recordsTotal'] = $this->db->getTotalRowsAudioAlbumsList();
        $response["recordsFiltered"] = $this->db->getTotalRowsAudioAlbumsList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $response['data'] = $this->db->getAudioAlbumsList($query_param);
        $response['data'] = $this->setLocalization($response['data'], 'year');
        while (list($key, $row) = each($response['data'])){
            $response['data'][$key]['RowOrder'] = "dTRow_" . $row['id'];
        }        
        $this->getAlbumsGenreNames($response['data']);
        $this->getAlbumsLanguages($response['data']);
        
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }
    
    public function remove_audio_albums() {

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['albumsid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeAudioAlbum';
        $data['id'] = $this->postData['albumsid'];
        $data['album'] = $this->db->deleteAudioAlbum(array('id' => $this->postData['albumsid']));
        $data['genre'] = $this->db->deleteAudioGenre(array('album_id' => $this->postData['albumsid']));
        $data['compositions'] = $this->db->deleteAudioCompositions(array('album_id' => $this->postData['albumsid']));
        
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function audio_genres_list_json($param = array()){
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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsAudioGenresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsAudioGenresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
           $query_param['select'][] = 'id';
        }
        
        $response['data'] = $this->db->getAudioGenresList($query_param);
                
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
        
    }
    
    public function add_audio_genres() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addAudioGenre';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioGenresList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $data['id'] = $this->db->insertAudioGenres(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_audio_genres() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editAudioGenre';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioGenresList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $this->db->updateAudioGenres(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
        } else {
            $data['nothing_to_do'] = TRUE;
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_audio_genres() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['genresid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeAudioGenre';
        $data['id'] = $this->postData['genresid'];
        $this->db->deleteAudioGenres(array('id' => $this->postData['genresid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_audio_genres_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkAudioGenre';
        $error = $this->setLocalization('Name already used');
        if ($this->db->getAudioGenresList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function audio_artists_list_json($param = array()){
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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsAudioArtistList();
        $response["recordsFiltered"] = $this->db->getTotalRowsAudioArtistList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
           $query_param['select'][] = 'id';
        }
        
        $response['data'] = $this->db->getAudioArtistList($query_param);
                
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
        
    }
    
    public function add_audio_artists() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addAudioArtist';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioArtistList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $data['id'] = $this->db->insertAudioArtist(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_audio_artists() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editAudioArtist';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioArtistList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $this->db->updateAudioArtist(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
        } else {
            $data['nothing_to_do'] = TRUE;
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_audio_artists() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['artistsid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeAudioArtist';
        $data['id'] = $this->postData['artistsid'];
        $this->db->deleteAudioArtist(array('id' => $this->postData['artistsid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_audio_artists_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkAudioArtist';
        $error = $this->setLocalization('Name already used');
        if ($this->db->getAudioArtistList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function audio_languages_list_json($param = array()){
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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsAudioLanguageList();
        $response["recordsFiltered"] = $this->db->getTotalRowsAudioLanguageList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
           $query_param['select'][] = 'id';
        }
        
        $response['data'] = $this->db->getAudioLanguageList($query_param);
                
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
        
    }
    
    public function add_audio_languages() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addAudioLanguage';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioLanguageList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $data['id'] = $this->db->insertAudioLanguage(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_audio_languages() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editAudioLanguage';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioLanguageList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $this->db->updateAudioLanguage(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
        } else {
            $data['nothing_to_do'] = TRUE;
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_audio_languages() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['languagesid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeAudioLanguage';
        $data['id'] = $this->postData['languagesid'];
        $this->db->deleteAudioLanguage(array('id' => $this->postData['languagesid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_audio_languages_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkAudioLanguage';
        $error = $this->setLocalization('Name already used');
        if ($this->db->getAudioLanguageList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function audio_years_list_json($param = array()){
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

        $query_param = $this->prepareDataTableParams($param, array('operations', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }
        
        $response['recordsTotal'] = $this->db->getTotalRowsAudioYearList();
        $response["recordsFiltered"] = $this->db->getTotalRowsAudioYearList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        
        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
           $query_param['select'][] = 'id';
        }
        
        $response['data'] = $this->db->getAudioYearList($query_param);
                
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;
        
        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
        
    }
    
    public function add_audio_years() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addAudioYear';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioYearList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $data['id'] = $this->db->insertAudioYear(array('name' => $this->postData['name']));
            $data['name'] = $this->postData['name'];
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_audio_years() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editAudioYear';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getAudioYearList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')));
        if (empty($check)) {
            $this->db->updateAudioYear(array('name' => $this->postData['name']), array('id' => $this->postData['id']));
            $error = '';
            $data['id'] = $this->postData['id'];
            $data['name'] = $this->postData['name'];
        } else {
            $data['nothing_to_do'] = TRUE;
            $error = '';
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_audio_years() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['yearsid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeAudioYear';
        $data['id'] = $this->postData['yearsid'];
        $this->db->deleteAudioYear(array('id' => $this->postData['yearsid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function check_audio_years_name() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['name'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkAudioYear';
        $error = $this->setLocalization('Name already used');
        if ($this->db->getAudioYearList(array('where' => array('name' => $this->postData['name']), 'order' => array('name' => 'ASC')))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function edit_audio_cover() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->data['cover'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $error = $this->setLocalization('No data');
        
        if (!empty($_FILES)) {
            reset($_FILES);
            list($key, $tmp) = each($_FILES);
            
            $uploaded = $this->request->files->get($key)->getPathname();
                        
            $filename = $this->data['cover'];
            $path = realpath(PROJECT_PATH . "/../misc/audio_covers/");
            $web_path = 'misc/audio_covers/';
            if (strpos($filename, 'new') !== FALSE) {
                
                $filename .= ($filename == 'new') ? rand(0, 100000) . "." . $this->request->files->get($key)->getClientOriginalExtension(): '';
                $path .= '/new/';
                $web_path .= 'new';
            } else {
                $id = explode('.', $filename);
                $path .= "/" . ceil($id[0] / 100) . "/";
                $web_path .= ceil($id[0] / 100);
            }
            if (!is_dir($path)) {
                mkdir($path, 0755);
            }

            $this->app['imagine']->open($uploaded)->resize(new Box(240, 240))->save($path . $filename);
            $data['path'] = $web_path;
            $data['name'] = $filename;
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function delete_cover() {
        if (!$this->isAjax || $this->method != 'POST' || (empty($this->postData['cover_id']) && empty($this->postData['file_name']))) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'deleteCover';
        $error = $this->setLocalization('Failed');
        $album = array();
        if (!empty($this->postData['cover_id'])) {
            $album = $this->db->getAudioAlbumsList(array(
                'select' => array('audio_albums.id as id', 'audio_albums.cover as cover'),
                'where' => array( 'audio_albums.id'=> $this->postData['cover_id']),
                'order' => array('audio_albums.id'=>'ASC')
            ));
        }

        $file_name = (count($album) != 0 && !empty($album[0]['cover']) ? $album[0]['cover']: (!empty($this->postData['file_name']) ? $this->postData['file_name']: ''));

        if (count($album) != 0 && !empty($album[0]['cover'])){
            $path = realpath(PROJECT_PATH . "/../misc/audio_covers/").'/' . ceil($album[0]['id']/100).'/';
        } else {
            $path = realpath(PROJECT_PATH . "/../misc/audio_covers/").'/new/';
        }

        if (!empty($file_name)) {
            if (!empty($this->postData['cover_id'])) {
                $this->db->updateCover($this->postData['cover_id'], '');
            }
            try{
                unlink($path . $file_name);
                $data['msg'] = $this->setLocalization('Deleted');
                $error = '';
            } catch (\Exception $e){
                $error = $this->setLocalization('image file has not been deleted') . ', ';
                $error .= $this->setLocalization('image name') . ' - "' . $file_name . '", ';
                $error .= $this->setLocalization('file can be deleted manually from screenshot directory');
                $data['msg'] = $error;
            }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function audio_albums_composition_list_json($param = array()) {
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }
        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setCompositionModal'
        );
        $filds_for_select = array(
            "id" => 'audio_compositions.id as `id`', 
            "number" => 'audio_compositions.number as `number`', 
            "name" => 'audio_compositions.name as `name`', 
            "url" => 'audio_compositions.url as `url`', 
            "language" => 'audio_languages.name as `language`', 
            "duration" => '0 as `duration`', 
            "tasks" => '0 as `tasks`', 
            "complaints" => '0 as `complaints`',
            "status" => 'audio_compositions.status as `status`',
            "language_id" => 'audio_languages.id as `language_id`'
        );
        $error = $this->setLocalization('Error');
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if(!empty($param['album_id'])) {
            $query_param['where']['album_id'] = $param['album_id'];
        } else {
            $query_param['where']['album_id'] = -1;
        }
        
        if(!empty($param['trackid'])) {
            $query_param['where']['audio_compositions.id'] = $param['trackid'];
        }
        
        if (empty($query_param['select'])) {
            $query_param['select'] = array_values($filds_for_select);
        } else {
            $query_param['select'][] = 'audio_compositions.id as id';
        }
        $this->cleanQueryParams($query_param, array_keys($filds_for_select), $filds_for_select);
        
        $response['recordsTotal'] = $this->db->getTotalRowsAlbumsCompositionList(array('album_id'=>$query_param['where']['album_id']));
        $response["recordsFiltered"] = $this->db->getTotalRowsAlbumsCompositionList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        $query_param['order']['number'] = 'ASC';
        $response['data'] = $this->db->getAlbumsCompositionList($query_param);

        while (list($key, $row) = each($response['data'])){
            $response['data'][$key]['RowOrder'] = "dTRow_" . $row['id'];
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
    
    public function audio_track_reorder() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $matches = array();
        $data = array();
        $data['action'] = 'reorder';
        $error = 'error';
        if (preg_match("/(\d+)/i", $this->postData['id'], $matches)){
            $params = array(
                'select' => array(
                    "id" => 'audio_compositions.id as `id`', 
                    "number" => 'audio_compositions.number as `number`', 
                    "album_id" => 'audio_compositions.album_id as `album_id`', 
                    ),
                'where' => array(),
                'like' => array(),
                'order' => array()
            );
            $id = $matches[0];
            $curr_pos = $this->postData['fromPosition'];
            $new_pos = $this->postData['toPosition'];
            
            $params['where']['audio_compositions.id'] = $id;
            $curr_track = $this->db->getAlbumsCompositionList($params);
            
            $params['where'] = array();
            $params['where']['number'] = $new_pos;
            $params['where']['album_id'] = $curr_track[0]['album_id'];
            
            $target_track = $this->db->getAlbumsCompositionList($params);
            
            $curr_track[0]['number'] = $new_pos;
            $target_track[0]['number'] = $curr_pos;
            
            if ($this->db->updateAlbumsComposition($curr_track[0], $curr_track[0]['id']) && $this->db->updateAlbumsComposition($target_track[0], $target_track[0]['id'])) {
                $error = '';
            }
            
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function audio_tracks_manage() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        $data = array();
        $data['action'] = 'audioTracksManage';
        $track = array($this->postData);
        $error = $this->setLocalization('error');
        if (empty($this->postData['id'])) {
            $params = array(
                    'select' => array(
                        "max" => 'if(max(audio_compositions.`number`), max(audio_compositions.`number`) + 1, 1) as `number`'
                        ),
                    'where' => array(
                        "audio_compositions.`album_id`" => $track[0]['album_id']
                        ),
                    'like' => array(),
                    'order' => array()
                );
            $max_num = $this->db->getAlbumsCompositionList($params);
            $operation = 'insertAlbumsComposition';
            $track[0]['added'] = 'NOW()';
            $track[0]['number'] = (!empty($max_num[0]['number']) ? $max_num[0]['number']: 1);
        } else {
            $operation = 'updateAlbumsComposition';
            $track['id'] = $this->postData['id'];
        }
        unset($track[0]['id']);

        if (!empty($this->postData['url']) && preg_match('/^(\w+\s)?\w+\:\/\/.*$/i', $this->postData['url'])) {
            if ($result = call_user_func_array(array($this->db, $operation), $track)) {
                $error = '';
            }
        } else {
            $data['msg'] = $this->setLocalization('Invalid format links');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function remove_audio_album_track() {
        
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['trackid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'audioTracksManage';
        $data['id'] = $this->postData['trackid'];
        $this->db->deleteAudioCompositions(array('id' => $this->postData['trackid']));
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_audio_album_track(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['trackid']) || !array_key_exists('trackstatus', $this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'audioTracksManage';
        $data['id'] = $this->postData['trackid'];
        $this->db->updateAlbumsComposition(array('status' => (int)(!((bool) $this->postData['trackstatus']))), $this->postData['trackid']);
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    public function toggle_audio_albums(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['albumsid']) || !array_key_exists('albumsstatus', $this->postData)) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageAudioAlbum';
        $data['id'] = $this->postData['albumsid'];
        $this->db->updateAudioAlbum(array('status' => (int)(!((bool) $this->postData['albumsstatus']))), $this->postData['albumsid']);
        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }
    
    //------------------------ service method ----------------------------------

    private function getAudioFilters(&$like_filter) {
        $return = array();

        if (!empty($this->data['filters'])){
            if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
                $return['status'] = $this->data['filters']['status_id'] - 1;
            }
                       
            if (array_key_exists('year', $this->data['filters']) && (!is_numeric($this->data['filters']['year']) || $this->data['filters']['year'] != 0)) {
                $return['year'] = $this->data['filters']['year'];
            }
            if (array_key_exists('ganre_name', $this->data['filters']) && !is_numeric($this->data['filters']['ganre_name'])) {
                $like_filter['ganre_name'] = "%" . $this->data['filters']['ganre_name'] . "%";
            }
            
            if (array_key_exists('country', $this->data['filters']) && !is_numeric($this->data['filters']['country'])) {
                $like_filter['country'] = "%" . $this->data['filters']['country'] . "%";
            }
            
            if (array_key_exists('language', $this->data['filters']) && !is_numeric($this->data['filters']['language'])) {
                $like_filter['language'] = "%" . $this->data['filters']['language'] . "%";
            }
            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $return;
    }
    
    private function getDropdownAttributeAudioClub(){
        return array(
            array('name'=>'name',           'title'=>$this->setLocalization('Title'),   'checked' => TRUE),
            array('name'=>'tracks_count',   'title'=>$this->setLocalization('Tracks'),  'checked' => TRUE),
            array('name'=>'ganre_name',     'title'=>$this->setLocalization('Genre'),   'checked' => TRUE),
            array('name'=>'year',           'title'=>$this->setLocalization('Year'),    'checked' => TRUE),
            array('name'=>'country',        'title'=>$this->setLocalization('Country'), 'checked' => TRUE),
            array('name'=>'language',       'title'=>$this->setLocalization('Language'),'checked' => TRUE),
            array('name'=>'status',         'title'=>$this->setLocalization('Status'),  'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),'checked' => TRUE)
        );
    }
    
    private function getAlbumsGenreNames(&$data) {
        reset($data);
        while(list($key, $row) = each($data)){
            $tmp = $this->setLocalization($this->db->getGenreForAlbum($row['id'], 'name'));
            $data[$key]['ganre_name'] = !empty($tmp) && is_array($tmp) ? implode(', ', $tmp) : '';
        }
    }
    
    private function getAlbumsLanguages(&$data) {
        reset($data);
        while(list($key, $row) = each($data)){
            $data[$key]['language'] = implode(', ', $this->db->getLanguagesForAlbum($row['id'], 'name'));
        }
    }
    
    private function buildAudioAlbumForm(&$data = array(), $edit = FALSE) {
        $query_param = array(
            'select' => array("*"),
            'where' => array(),
            'like' => array(),
            'order' => array()
        );
        
        $tmp = $this->db->getAudioArtistList($query_param);
        $all_performers = array();
        if (!empty($tmp)) {
            $all_performers = array_combine($this->getFieldFromArray($tmp, 'id'), $this->getFieldFromArray($tmp, 'name'));
        }
        
        $tmp = $this->db->getAudioGenresList($query_param);
        $all_genres = array();
        if (!empty($tmp)) {
            $tmp = $this->getUCArray($tmp, 'name');
            $all_genres = array_combine($this->getFieldFromArray($tmp, 'id'), $this->getFieldFromArray($tmp, 'name'));
        }
        
        $tmp = $this->db->getAudioYearList($query_param);
        $all_years = array();
        if (!empty($tmp)) {
            $all_years = array_combine($this->getFieldFromArray($tmp, 'id'), $this->getFieldFromArray($tmp, 'name'));
        }

        if (!empty($this->app['locale']) && substr($this->app['locale'], 0, 2) == 'ru') {
            $query_param['select'][] = '`name` as `name`';
            $query_param['order']['name'] = 'ASC';
        } else {
            $query_param['select'][] = '`name_en` as `name`';
            $query_param['order']['name_en'] = 'ASC';
        }

        $tmp = $this->db->getAudioCountryList($query_param);
        $all_countries = array();
        if (!empty($tmp)) {
            $all_countries = array_combine($this->getFieldFromArray($tmp, 'id'), $this->getFieldFromArray($tmp, 'name'));
        }
        $builder = $this->app['form.factory'];
        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('performer_id', 'choice', array(
                    'choices' => $all_performers,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($all_performers))))
                        )
                )
                ->add('name', 'text', array('constraints' => array(new Assert\NotBlank())))
                ->add('genre_ids', 'choice', array(
                    'choices' => $all_genres,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($all_genres), 'multiple' => TRUE))),
                    'multiple' => TRUE
                        )
                )
                ->add('year_id', 'choice', array(
                    'choices' => $all_years,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($all_years))))
                        )
                )
                ->add('cover', 'hidden')
                ->add('country_id', 'choice', array(
                    'choices' => $all_countries,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($all_countries))))
                        )
                )
                ->add('save', 'submit');
//                ->add('reset', 'reset');
        return $form->getForm();
    }

    private function saveAudioAlbumData(&$form, $edit = FALSE) {
        
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            $action = (isset($this->audio_album) && $edit ? 'updateAudioAlbum' : 'insertAudioAlbum');

            if ($form->isValid()) {
                if ($action == 'insertAudioAlbum') {
                    $data['added'] = 'NOW()';
                }
                $album_tracks = (!empty($this->postData['album_tracks'])? implode("', '", json_decode($this->postData['album_tracks'])): '');
                $genre_ids = !empty($data['genre_ids']) && is_array($data['genre_ids'])? $data['genre_ids']: array();
                $data = array_intersect_key($data, array_flip($this->getFieldFromArray($this->db->getTableFields('audio_albums'), 'Field')));
                $param = array();
                $param[] = $data;
                if ($edit && !empty($data['id'])) {
                    $param[] = $data['id'];
                    unset($param[0]['id']);
                }
                
                
                if ($return_val = call_user_func_array(array($this->db, $action), $param)) {
                    if ($action == 'updateAudioAlbum') {
                        $album_id = $data['id'];
                        $this->db->deleteAudioGenre(array('album_id' => $data['id']));
                    } else {
                        $album_id = $return_val;
                        $this->db->updateAlbumsComposition(array('album_id' => $album_id), array("`id` in ('$album_tracks') and 1" => '1'));
                    }
                    if (!empty($data['cover'])) {
                        if (strpos($data['cover'], 'new') !== FALSE) {
                            $filename = explode('.', $data['cover']);
                            $path = realpath(PROJECT_PATH . "/../misc/audio_covers/");
                            $old_path = $path . '/new/';
                            $path .= "/" . ceil($album_id / 100) . "/";

                            if (!is_dir($path)) {
                                mkdir($path, 0755);
                            }
                            
                            if (@rename($old_path . $data['cover'], $path . "$album_id.$filename[1]")){
                                $this->db->updateAudioAlbum(array('cover' => "$album_id.$filename[1]"), $album_id);
                            }
                        }
                    }
                    
                    if (!empty($genre_ids)) {
                        $genres_data = array();
                        foreach ($genre_ids as $genre_id){
                            $genres_data[] = array(
                                'album_id' => $album_id,
                                'genre_id' => $genre_id
                            );
                        }
                        if (!empty($genres_data)){
                            $this->db->insertAudioGenre($genres_data);
                        }
                    }
                    
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function getDropdownAttributeAudioComposition(){
        return array(
            array('name'=>'number',         'title'=>$this->setLocalization('Order'),   'checked' => TRUE),
            array('name'=>'name',           'title'=>$this->setLocalization('Title'),   'checked' => TRUE),
            array('name'=>'url',            'title'=>$this->setLocalization('URL'),     'checked' => TRUE),
            array('name'=>'language',       'title'=>$this->setLocalization('Language'),'checked' => TRUE),
            array('name'=>'status',         'title'=>$this->setLocalization('Status'),  'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),'checked' => TRUE)
        );
    }

    private function getShortDropdownAttribute(){
        return array(
            array('name'=>'name',           'title'=>$this->setLocalization('Title'),       'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),   'checked' => TRUE)
        );
    }
}