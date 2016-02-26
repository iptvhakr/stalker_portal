<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use M3uParser;

class TvChannelsController extends \Controller\BaseStalkerController {

    private $logoHost;
    private $logoDir;
    private $broadcasting_keys = array(
        'cmd' => array(''),
        'user_agent_filter' => array(''),
        'priority' => array(''),
        'use_http_tmp_link' => array(FALSE),
        'wowza_tmp_link' => array(''),
        'nginx_secure_link' => array(''),
        'flussonic_tmp_link' => array(''),
        'enable_monitoring' => array(FALSE),
        'monitoring_status' => array(FALSE),
        'enable_balancer_monitoring' => array(''),
        'monitoring_url' => array(''),
        'use_load_balancing' => array(FALSE),
        'stream_server' => array('')
    );

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);
        
        $this->logoHost = $this->baseHost . \Config::getSafe('portal_url', '/stalker_portal/') . "misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
        $this->app['allArchive'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Yes')),
            array('id' => 2, 'title' => $this->setLocalization('No'))
        );
        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Published')),
            array('id' => 2, 'title' => $this->setLocalization('Unpublished'))
        );

        $this->app['allMonitoringStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('monitoring off')),
            array('id' => 2, 'title' => $this->setLocalization('errors occurred')),
            array('id' => 3, 'title' => $this->setLocalization('no errors')),
            array('id' => 4, 'title' => $this->setLocalization('there are some problems'))
        );
    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/iptv-list');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function iptv_list() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $allChannels = $this->iptv_list_json();
        $this->app['allChannels'] = $this->db->getAllFromTable('itv');
        $this->app['recordsFiltered'] = $allChannels['recordsFiltered'];
        $this->app['totalRecords'] = $allChannels['recordsTotal'];
        $this->app['allGenres'] = $this->getAllGenres();

        $attribute = $this->getIptvListDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function move_channel() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $filter = array();

        if ($this->method == 'GET' && !empty($this->data['filters'])) {
            $filter = $this->getIPTVfilters();
        }

        $allChannels = $this->db->getAllChannels(array('select' => $this->getAllChannelsFields(), 'where' => $filter, 'order'=> array('number' => 'ASC')));

        if (is_array($allChannels)) {
            while (list($num, $row) = each($allChannels)) {
                $allChannels[$num]['logo'] = $this->getLogoUriById(FALSE, $row, 120);
                $allChannels[$num]['locked'] = (bool)$allChannels[$num]['locked'];
            }
            if (((int)$allChannels[0]['number']) == 0) {
                array_push($allChannels, $allChannels[0]);
                unset($allChannels[0]);
            }
        }
        $this->app['allChannels'] = $this->fillEmptyRows($allChannels);

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function add_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['not_found'] = $this->app['request']->getSession()->get('channel_error');

        if (!empty($this->app['not_found'])) {
            $this->app['request']->getSession()->remove('channel_error');
        }

        $this->app['allGenres'] = $this->getAllGenres();
        $this->app['streamServers'] = $this->db->getAllStreamServer();
        $this->app['channelEdit'] = FALSE;
        $form = $this->buildForm();

        if ($this->saveChannelData($form)) {
            return $this->app->redirect('iptv-list');
        }

        $this->app['form'] = $form->createView();
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function edit_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if ($this->method == 'GET' && (empty($this->data['id']) || !is_numeric($this->data['id']))) {
            return $this->app->redirect('add-channel');
        }

        $id = ($this->method == 'POST' && !empty($this->postData['form']['id'])) ? $this->postData['form']['id'] : $this->data['id'];

        $this->app['allGenres'] = $this->getAllGenres();
        $this->app['channelEdit'] = TRUE;
        $this->oneChannel = $this->db->getChannelById($id);
        if (empty($this->oneChannel)) {
            $this->app['request']->getSession()->set('channel_error', TRUE);
            return $this->app->redirect('add-channel');
        }
        $this->oneChannel = array_merge($this->oneChannel, $this->getStorages($id));
        $this->oneChannel['pvr_storage_names'] = array_keys(\RemotePvr::getStoragesForChannel($id));
        settype($this->oneChannel['enable_tv_archive'], 'boolean');
        settype($this->oneChannel['wowza_dvr'], 'boolean');
        settype($this->oneChannel['flussonic_dvr'], 'boolean');
        settype($this->oneChannel['allow_pvr'], 'boolean');
        settype($this->oneChannel['censored'], 'boolean');
        settype($this->oneChannel['allow_local_timeshift'], 'boolean');
        settype($this->oneChannel['allow_local_pvr'], 'boolean');
        settype($this->oneChannel['base_ch'], 'boolean');
        $this->oneChannel['logo'] = $this->getLogoUriById(FALSE, $this->oneChannel);
        $this->setChannelLinks();
        $this->app['streamServers'] = $this->streamServers;

        $this->app['error_local'] = array();

        $form = $this->buildForm($this->oneChannel);

        if ($this->saveChannelData($form)) {
            return $this->app->redirect('iptv-list');
        }

        $this->app['form'] = $form->createView();

        $this->app['breadcrumbs']->addItem("'{$this->oneChannel['name']}'");

        $this->app['editChannelName'] = $this->oneChannel['name'];

        return $this->app['twig']->render('TvChannels_add_channel.twig');
    }

    public function epg() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getEpgDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $list = $this->epg_list_json();

        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        $this->app['allLanguages'] = $this->getLanguageCodesEN();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function tv_genres(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['dropdownAttribute'] = $this->getGenresDropdownAttribute();
        $list = $this->tv_genres_list_json();

        $this->app['allData'] = $list['data'];
        $this->app['totalRecords'] = $list['recordsTotal'];
        $this->app['recordsFiltered'] = $list['recordsFiltered'];

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function m3u_import(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['allGenres'] = $this->getAllGenres();

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    //----------------------- ajax method --------------------------------------

    public function iptv_list_json(){
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

        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_', 'claims'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        $filter = $this->getIPTVfilters();

        $query_param['where'] = array_merge($query_param['where'], $filter);
        $filds_for_select = $this->getAllChannelsFields();

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

        $response['recordsTotal'] = $this->db->getTotalRowsAllChannels();
        $response["recordsFiltered"] = $this->db->getTotalRowsAllChannels($query_param['where'], $query_param['like']);
        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        $allChannels = $this->db->getAllChannels($query_param);

        $allChannels = $this->setLocalization($allChannels, 'genres_name');
        $response["data"] = array();
        if (is_array($allChannels)) {
            reset($allChannels);
            while (list($num, $row) = each($allChannels)) {
                $allChannels[$num]['logo'] = $this->getLogoUriById(FALSE, $row, 120);
                $allChannels[$num]['genres_name'] = $this->mb_ucfirst($allChannels[$num]['genres_name']);
                $allChannels[$num]['enable_tv_archive'] = (int) $allChannels[$num]['enable_tv_archive'];
                $allChannels[$num]['status'] = (int) $allChannels[$num]['status'];
                settype($allChannels[$num]['sound_counter'], 'int');
                settype($allChannels[$num]['video_counter'], 'int');
                settype($allChannels[$num]['no_epg'], 'int');
                settype($allChannels[$num]['wrong_epg'], 'int');
                if (($monitoring_status = $this->getMonitoringStatus($row)) !== FALSE) {
                    $allChannels[$num]['monitoring_status'] = $monitoring_status;
                    $response["data"][] = $allChannels[$num];
                    /*if (count($response["data"]) >= 50) {
                        break;
                    }*/
                } else {
                    unset($allChannels[$num]);
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

    public function remove_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (empty($this->data['id']) || (!is_numeric($this->data['id']))) {
            $this->app->abort(404, $this->setLocalization('Cannot find channel'));
        }

        $channel = $this->db->getChannelById($this->data['id']);
        $response = array();
        $response['rows'] = $this->db->removeChannel($this->data['id']);
        $response['action'] = 'remove';

        if (!empty($response['rows'])) {
            $this->saveFiles->removeFile($this->logoDir, $channel['logo']);
            $response['success'] = TRUE;
            $response['error'] = FALSE;
        } else {
            $response['success'] = FALSE;
            $response['error'] = TRUE;
        }

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function disable_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (empty($this->data['id']) || (!is_numeric($this->data['id']))) {
            $this->app->abort(404, $this->setLocalization('Cannot find channel'));
        }

        $rows = $this->db->changeChannelStatus($this->data['id'], 0);

        $data = array(
            'rows' => $rows,
            'action' => $this->setLocalization('Publish'),
            'status' => $this->setLocalization('Unpublished'),
            'urlactfrom' => 'disable',
            'urlactto' => 'enable');
        $error = '';
        if (empty($rows)) {
            $data['msg'] = $error = $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function enable_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (empty($this->data['id']) || (!is_numeric($this->data['id']))) {
            $this->app->abort(404, $this->setLocalization('Cannot find channel'));
        }

        $rows = $this->db->changeChannelStatus($this->data['id'], 1);

        $data = array(
            'rows' => $rows,
            'action' => $this->setLocalization('Unpublish'),
            'status' => $this->setLocalization('Published'),
            'urlactfrom' => 'enable',
            'urlactto' => 'disable'
        );

        $error = '';
        if (empty($rows)) {
            $data['msg'] = $error = $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_logo() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (empty($this->data['id']) || (!is_numeric($this->data['id']) && strpos($this->data['id'], 'new') === FALSE)) {
            $this->app->abort(404, $this->setLocalization('Cannot find channel'));
        } elseif ($this->data['id'] == 'new') {
            $this->data['id'] .= rand(0, 100000);
        }

        $this->saveFiles->handleUpload($this->logoDir, $this->data['id']);

        $error = $this->saveFiles->getError();
        $response = $this->generateAjaxResponse(array('pic' => $this->logoHost . "/320/" . $this->saveFiles->getFileName()), $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function delete_logo() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (!$this->isAjax || empty($this->postData['logo_id']) || (!is_numeric($this->postData['logo_id']) && strpos($this->postData['logo_id'], 'new') === FALSE)) {
            $this->app->abort(404, $this->setLocalization('Cannot find channel'));
        }

        $channel = $this->db->getChannelById($this->postData['logo_id']);

        if (!empty($channel) && array_key_exists('id', $channel)) {
            $this->db->updateITVChannelLogo($channel['id'], '');
            $this->saveFiles->removeFile($this->logoDir, $channel['logo']);
        } else {
            $this->saveFiles->removeFile($this->logoDir, $this->postData['logo_id']);
        }
        $error = $this->saveFiles->getError();
        $response = $this->generateAjaxResponse(array('data' => 0, 'action'=>'deleteLogo'), $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_apply() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (!$this->isAjax) {
            $this->app->abort(404, $this->setLocalization('The unexpected request'));
        }
        $senddata = array('action' => 'manageChannel');
        if (empty($this->postData['data'])) {
            $senddata['error'] = $this->setLocalization('No moved items, nothing to do');
            $senddata['nothing_to_do'] = TRUE;
        } else {
            $senddata['error'] = '';
            foreach ($this->postData['data'] as $row) {
                if (empty($row['id'])) {
                    continue;
                }
                if (!$this->db->updateChannelNum($row)) {
                    $senddata['error'] = $this->setLocalization('Failed to save, update the channel list');
                }
            }
        }
        $response = $this->generateAjaxResponse($senddata, $senddata['error']);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function toogle_lock_channel(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (!$this->isAjax) {
            $this->app->abort(404, $this->setLocalization('The unexpected request'));
        }
        if (empty($this->postData['data'])) {
            $erorr = 'nothing to do';
            $senddata = array('action' => 'canceled');
        } else {
            $erorr = '';
            $senddata = array('action' => 'applied');
            foreach ($this->postData['data'] as $row) {
                if (empty($row['id'])) {
                    continue;
                }

                $row['locked'] = (empty($row['locked']) || $row['locked'] == "false" || $row['locked'] == '0') ? 0: 1;
                if (!$this->db->updateChannelLockedStatus($row)) {
                    $erorr = $this->setLocalization('Failed to save, update the channel list');
                    $senddata = array('action' => 'canceled');
                }
            }
        }
        $response = $this->generateAjaxResponse($senddata, $erorr);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function save_channel_epg_item(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (!$this->isAjax || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('The unexpected request'));
        }

        $ch_id = (!empty($this->data['id']) ? $this->data['id']: $this->postData['id']);
        $date = (!empty($this->postData['epg_date']) ? $this->postData['epg_date']: strftime('%d-%m-%Y'));

        $senddata = array(
            'action' => 'saveEPGSuccess',
            'deleted' => 0,
            'inserted' => 0
        );

        $erorr = '';

        $date = implode('-', array_reverse(explode('-', $date)));
        $senddata['deleted'] = $this->db->deleteEPGForChannel($ch_id, $date.' 00:00:00', $date.' 23:59:59');

        $epg = (!empty($this->postData['epg_body'])? $this->postData['epg_body']: '');
        $epg = preg_split("/\n/", stripslashes(trim($epg)));

        for ($i=0; $i<count($epg); $i++){
            $curr_row = $this->get_epg_row($date, $epg, $i);
            $curr_row['ch_id'] = $ch_id;
            $curr_row['real_id'] = $ch_id . '_' . strtotime($curr_row['time']);
            $senddata['inserted'] += $this->db->insertEPGForChannel($curr_row);
        }

        $response = $this->generateAjaxResponse($senddata, $erorr);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function get_channel_epg_item(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        if (!$this->isAjax || (empty($this->data['id']) && empty($this->postData['id']))) {
            $this->app->abort(404, $this->setLocalization('The unexpected request'));
        }

        $ch_id = (!empty($this->data['id']) ? $this->data['id']: $this->postData['id']);
        $date = (!empty($this->postData['epg_date']) ? $this->postData['epg_date']: strftime('%d-%m-%Y'));

        $senddata = array(
            'action' => 'showModalBox',
            'ch_id' => $ch_id,
            'epg_date' => $date,
            'epg_body' => ''
        );
        $erorr = '';

        $date = implode('-', array_reverse(explode('-', $date)));
        $epg_data = $this->db->getEPGForChannel($ch_id, $date.' 00:00:00', $date.' 23:59:59');

        if (!empty($epg_data)) {
            $tmp = array('');
            reset($epg_data);
            while(list($key, $row) = each($epg_data)){
                preg_match("/(\d+):(\d+)/", $row['time'], $tmp);
                $senddata['epg_body'] .= $tmp[0]." ".$row['name']."\n";
            }
        }

        $response = $this->generateAjaxResponse($senddata, $erorr);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function epg_list_json(){
        if ($this->isAjax) {
            if ($no_auth = $this->checkAuth()) {
                return $no_auth;
            }
        }

        $response = array(
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'action' => 'setEPGModal'
        );

        $error = "Error";
        $param = (empty($param) ? (!empty($this->data)?$this->data: $this->postData) : $param);

        $query_param = $this->prepareDataTableParams($param, array('operations', 'RowOrder', '_'));

        if (!isset($query_param['where'])) {
            $query_param['where'] = array();
        }

        if (empty($query_param['select'])) {
            $query_param['select'] = "*";
        }

        if (!isset($query_param['like'])) {
            $query_param['like'] = array();
        }

        $response['recordsTotal'] = $this->db->getTotalRowsEPGList();
        $response["recordsFiltered"] = $this->db->getTotalRowsEPGList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }
        if (array_key_exists('id', $param)) {
            $query_param['where']['id'] = $param['id'];
        }
        $EPGList = $this->db->getEPGList($query_param);
        $response['data'] = array_map(function($val){
            $val['status'] = (int)$val['status'];
            $val['updated'] = (int) strtotime($val['updated']);
            return $val;
        }, $EPGList);
        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function save_epg_item() {

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData)) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageEPG';
        $item = array($this->postData);
        $check = array();
        if (empty($this->postData['id'])) {
            $operation = 'insertEPG';
            $check = $this->db->searchOneEPGParam(array('uri' => trim($this->postData['uri'])));
        } else {
            $operation = 'updateEPG';
            $item['id'] = $this->postData['id'];
        }
        unset($item[0]['id']);
        $error = ' ';
        if (empty($check)) {
            $result = call_user_func_array(array($this->db, $operation), $item);
            if (is_numeric($result)) {
                $error = '';
                if ($result === 0) {
                    $data['nothing_to_do'] = TRUE;
                }
            }
        } else {
            $error .= $this->setLocalization('URL is busy');
            $data['msg']=$error;
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_epg_item() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageEPG';
        $data['id'] = $this->postData['id'];
        $error = '';
        $this->db->deleteEPG(array('id' => $this->postData['id']));

        $response = $this->generateAjaxResponse($data);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function toggle_epg_item_status() {

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id']) || !array_key_exists('status', $this->postData)) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageEPG';
        $data['id'] = $this->postData['id'];
        $this->db->updateEPG(array('status' => (int)(!((bool) $this->postData['status']))), $this->postData['id']);
        $error = '';
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function epg_check_uri(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['param'])) {
            $this->app->abort(404, 'Page not found');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkRadioName';
        $error = $this->setLocalization('URL is busy');

        if ($this->db->searchOneEPGParam(array('uri' => trim($this->postData['param']), 'id<>' => trim($this->postData['epgid'])))) {
            $data['chk_rezult'] = $this->setLocalization('URL is busy');
        } else {
            $data['chk_rezult'] = $this->setLocalization('URL is free');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function update_epg(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'manageEPG';
        $data['id'] = $this->postData['id'];
        $error = '';

        $epg = new \Epg();

        $data['msg'] = nl2br($epg->updateEpg(!empty($this->postData['force'])));

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function restart_all_archives(){
        if (!$this->isAjax) {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'restartArchive';
        $data['error'] = $error = '';

        $tv_archive = new \TvArchive();
        $result = true;
        $current_tasks = $this->db->getCurrentTasks();

        $new_tasks = array();

        foreach ($current_tasks as $task) {
            $new_tasks[$task['ch_id']][] = $task['storage_name'];
        }
        foreach (array_keys($new_tasks) as $channel) {
            if ($this->db->checkChannelParams($channel)) {
                $tv_archive->deleteTasks($channel);
                $result = $tv_archive->createTasks($channel, $new_tasks[$channel]) && $result;
            } else {
                $result = FALSE;
                if (empty($data['error'])) {
                    $data['error'] = $this->setLocalization('Some channels not enough params.');
                }
            }
        }
        if (!$result) {
            $data['error'] .= ' ' . $this->setLocalization('TV Archive has NOT been restarted correctly.');
        }  else {
            $data['msg'] = $this->setLocalization('TV Archive has been restarted.');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function tv_genres_list_json(){

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

        $response['recordsTotal'] = $this->db->getTotalRowsTvGenresList();
        $response["recordsFiltered"] = $this->db->getTotalRowsTvGenresList($query_param['where'], $query_param['like']);

        if (empty($query_param['limit']['limit'])) {
            $query_param['limit']['limit'] = 50;
        } elseif ($query_param['limit']['limit'] == -1) {
            $query_param['limit']['limit'] = FALSE;
        }

        if (!empty($query_param['select']) && !in_array('id', $query_param['select'])) {
            $query_param['select'][] = 'id';
        }

        $self = $this;
        $query_param['order']['number'] = 'ASC';

        $response['data'] = array_map(function($row) use ($self){
            $row['localized_title'] = $self->setLocalization($row['title']);
            $row['RowOrder'] = "dTRow_" . $row['id'];
            return $row;
        }, $this->db->getTvGenresList($query_param));

        $response["draw"] = !empty($this->data['draw']) ? $this->data['draw'] : 1;

        $error = "";
        if ($this->isAjax) {
            $response = $this->generateAjaxResponse($response);
            return new Response(json_encode($response), (empty($error) ? 200 : 500));
        } else {
            return $response;
        }
    }

    public function add_tv_genres(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['title'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'addTvGenre';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getTvGenresList(array('where' => array('title' => $this->postData['title']), 'order' => array('title' => 'ASC')));
        if (empty($check)) {
            if ($this->db->insertTvGenres(array('title' => $this->postData['title']))) {
                $error = '';
            } else {
                $data['msg'] = $error = ' ' . $this->setLocalization('Nothing to do');
            }
        } else {
            $data['msg'] = $error = ' ' . $this->setLocalization('Name already used');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_tv_genres(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['title']) || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'editTvGenre';
        $error = $this->setLocalization('Failed');
        $check = $this->db->getTvGenresList(array(
            'select'=>array('*'),
            'where' => array(' BINARY title' => $this->postData['title'], 'id<>' => $this->postData['id']),
            'order' => array('title' => 'ASC'),
            'like' => array()
        ));
        if (empty($check)) {
            $result = $this->db->updateTvGenres(array('title' => $this->postData['title']), array('id' => $this->postData['id']));
            if ($result) {
                $error = '';
                $data['id'] = $this->postData['id'];
                $data['title'] = $this->postData['title'];
            } elseif(is_numeric($result)) {
                $error = '';
                $data['msg'] = ' ' . $this->setLocalization('Nothing to do');
                $data['nothing_to_do'] = TRUE;
            }
        } else {
            $data['msg'] = $error = ' ' . $this->setLocalization('Name already used');
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function remove_tv_genres(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['genresid'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'removeTvGenre';
        $data['id'] = $this->postData['genresid'];
        $error = $this->setLocalization('Failed');

        if ($this->db->deleteTvGenres(array('id' => $this->postData['genresid']))){
            $error = '';
        } else {
            $data['msg'] = $error = $this->setLocalization('Nothing to do');
        }

        $response = $this->generateAjaxResponse($data, '');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function check_tv_genres_name(){

        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['title'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkTvGenre';
        $error = $this->setLocalization('Name already used');
        if ($this->db->getTvGenresList(array(
            'select'=>array('*'),
            'where' => array(' BINARY title' => $this->postData['title']),
            'order' => array('title' => 'ASC'),
            'like' => array()
        ))) {
            $data['chk_rezult'] = $this->setLocalization('Name already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Name is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));

    }

    public function tv_genres_reorder() {
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $matches = array();
        $data = array();
        $data['action'] = 'reorder';
        $error = $this->setLocalization('Error');
        if (preg_match("/(\d+)/i", $this->postData['id'], $matches)){
            $params = array(
                'select' => array(
                    "id"        => 'tv_genre.id as `id`',
                    "number"    => 'tv_genre.number as `number`'
                ),
                'where' => array(),
                'like' => array(),
                'order' => array('number'=>'DESC')
            );
            $curr_pos = $this->postData['fromPosition'];
            $new_pos = $this->postData['toPosition'];

            $params['where']['tv_genre.number'] = $curr_pos;
            $curr_genre = $this->db->getTvGenresList($params);

            $params['where'] = array();
            $params['where']['tv_genre.number<='] = $new_pos;
            $target_genre = $this->db->getTvGenresList($params);

            if ($this->db->updateTvGenres($target_genre[0], array('id' => $curr_genre[0]['id'])) && $this->db->updateTvGenres($curr_genre[0], array('id' => $target_genre[0]['id']))) {
                $error = '';
                $data['msg'] = $this->setLocalization('Done');
            } else {
                $data['msg'] = $error;
            }

        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function get_m3u_data() {
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, 'Page not found...');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'loadM3UData';
        $data['data'] = array(
            'channels' => array(),
            'last_channel_number' => 0,
            'free_number_exists' => 1
        );
        $error = $this->setLocalization('Upload failed');

        $storage = new \Upload\Storage\FileSystem('/tmp', TRUE);
        $file = new \Upload\File('qqfile', $storage);

        try {
            // Success!
            $file->upload();

            $obj = new M3uParser\M3uParser();
            $m3u_data = $obj->parseFile($file->getPath() . '/' .$file->getNameWithExtension());
            @unlink($file->getPath() . '/' .$file->getNameWithExtension());

            $data['data']['last_channel_number'] = (int) $this->db->getLastChannelNumber();

            if ($data['data']['last_channel_number'] + count($m3u_data) > 9999) {
                $data['data']['free_number_exists'] = (int)(($this->db->getAllChannels(array(), 'COUNT') + count($m3u_data)) <= 9999);
            }

            foreach ($m3u_data as $entry) {
                $name = trim($entry->getName());
                if (!mb_check_encoding($name, 'UTF-8')) {
                    $name = mb_convert_encoding($name, 'UTF-8', array('CP1251'));
                }
                $data['data']['channels'][] = array(
                    'name' => $name,
                    'cmd' => trim($entry->getPath())
                );
            }
            $error = '';
        } catch (\Exception $e) {
            // Fail!
            $data['msg'] = $error = $file->getErrors();
        }

        $response = $this->generateAjaxResponse($data, $error);
        $json_string = json_encode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = $this->setLocalization('Error m3u parse. Check the file encoding. Required UTF-8 encoding.');
            $json_string = json_encode(array('msg' => $error, 'error' => $error));
        }

        return new Response($json_string, (empty($error) ? 200 : 500));
    }

    public function save_m3u_item(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'saveM3UItem';
        $error = $this->setLocalization('Error');

        $item_data = $this->postData;

        $data['item_id'] = $item_data['item_id'];

        $is_repeating_name = count($this->db->getFieldFirstVal('name', $item_data['name']));
        $is_repeating_number = count($this->db->getFieldFirstVal('number', $item_data['number']));

        if (!$is_repeating_name && !$is_repeating_number) {
            $this->dataPrepare($item_data);
            if (empty($item_data['cmd'])) {
                $data['msg'] = $error = $this->setLocalization('Requires at least one link of broadcast');
            } if (empty($item_data['name'])) {
                $data['msg'] = $error = $this->setLocalization('Field "Channel name" cannot be empty');
            } if (empty($item_data['number'])) {
                $data['msg'] = $error = $this->setLocalization('Field "Channel number" cannot be empty');
            } else {
                $item_data['cmd'] = array(0=>$item_data['cmd']);
                $item_data['priority'] = array(0=>0);
                $item_data['user_agent_filter'] = array(0=>'');
                $item_data['monitoring_url'] = array(0=>'');

                $ch_id = $this->db->insertITVChannel($item_data);
                $this->setDBLincs($ch_id, $item_data);
                $this->createTasks($ch_id, $item_data);
                $error = '';
            }
        } else {
             if ($is_repeating_number) {
                $data['msg'] = $error = $this->setLocalization('Number "%number%" is already in use', '', '', array('%number%' => $item_data['number']));
            } elseif ($is_repeating_name) {
                 $data['msg'] = $error = $this->setLocalization('Name "%name%" already exists', '', '', array('%name%' => $item_data['name']));
             }
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function epg_check_prefix(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        $data = array();
        $data['action'] = 'checkData';
        $data['input_id'] = 'form_id_prefix';
        $error = $this->setLocalization('Prefix already used');
        $params = array(
            'id_prefix' => $this->postData['prefix']
        );
        if (!empty($this->postData['epg_id'])) {
            $params['id<>'] = $this->postData['epg_id'];
        }
        $result = $this->db->searchOneEPGParam($params);

        if (!empty($this->postData['prefix']) && $result) {
            $data['chk_rezult'] = $this->setLocalization('Prefix already used');
        } else {
            $data['chk_rezult'] = $this->setLocalization('Prefix is available');
            $error = '';
        }
        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function itv_reset_claims(){
        if (!$this->isAjax || $this->method != 'POST') {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        $data['action'] = 'manageChannelTable';
        $error = $this->setLocalization('Failed');
        $result = $this->db->resetMediaClaims($this->postData['media_id']);
        if (is_numeric($result)) {
            $error = '';
            if ($result === 0) {
                $data['nothing_to_do'] = TRUE;
            }
        } else {
            $data['msg'] = $error;
        }

        $response = $this->generateAjaxResponse($data, $error);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function getLogoUriById($id = FALSE, $row = FALSE, $resolution = 320) {

        $channel = ($row === FALSE) ? $this->db->getChannelById($id) : $row;

        if (empty($channel['logo'])) {
            return "";
        }

        return \Config::get('portal_url') . 'misc/logos/' . $resolution . '/' . $channel['logo'];
    }

    private function setChannelLinks() {
        $this->channeLinks = $this->db->getChannelLinksById($this->oneChannel['id']);
        if (empty($this->channeLinks)) {
            $this->channeLinks = (!empty($this->oneChannel['cmd']) ? array($this->oneChannel['cmd']) : array(''));
        }
        $this->streamServers = $this->db->getAllStreamServer();
        while (list($key, $row) = each($this->channeLinks)) {
            foreach ($this->broadcasting_keys as $b_key => $value) {
                if (!array_key_exists($b_key, $this->oneChannel) || !is_array($this->oneChannel[$b_key])) {
                    $this->oneChannel[$b_key] = array();
                }
                if (isset($row[$b_key])) {
                    $this->oneChannel[$b_key][$key + 1] = $row[$b_key];
                } else {
                    $this->oneChannel[$b_key][$key + 1] = $value[0];
                }
                settype($this->oneChannel[$b_key][$key + 1], gettype($value[0]));
            }
            if (!empty($row['id'])) {
                $this->setLinkStreamServers($key, $row['id']);
            }
        }
    }

    private function setLinkStreamServers($num, $id) {

        $this->streamers_map[$num] = $this->db->getStreamersIdMapForLink($id);
        if (!is_array($this->oneChannel['stream_server'])) {
            $this->oneChannel['stream_server'] = array();
        }
        $server = array();
        while (list($key, $row) = each($this->streamServers)) {
            if (!empty($this->streamers_map[$num][$this->streamServers[$key]['id']])) {
                $server[] = $this->streamers_map[$num][$this->streamServers[$key]['id']]['streamer_id'];
            }
        }

        $this->oneChannel['stream_server'][$num + 1] = implode(';', $server);
    }

    private function buildForm($data = array()) {

        $builder = $this->app['form.factory'];

        $genres = array();

        foreach ($this->app['allGenres'] as $row) {
            $genres[$row['id']] = $row['title'];
        }

        $storages = $this->getStorages();

        $def_number = 0;
        $def_name = 'Channel';
        if (!empty($data['number'])){
            $def_number = $data['number'];
        } else {
            $def_number = $this->db->getFirstFreeChannelNumber();
        }

        if (!empty($data['name'])){
            $def_name = $data['name'];
        } else {
           $def_name = "";//"$def_name $def_number";
        }

        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('number', 'text', array(
                    'constraints' => array(
                        new Assert\Range(array('min' => 0, 'max' => 9999)),
                        new Assert\NotBlank()
                    ),
                    'data' => $def_number
                    ))
                ->add('name', 'text', array(
                        'constraints' => array(new Assert\NotBlank()),
                        'data' => $def_name
                        )
                )
                ->add('tv_genre_id', 'choice', array(
                    'choices' => $genres,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($genres))))
                        )
                )
                ->add('pvr_storage_names', 'choice', array(
                    'choices' => (empty($storages['storage_names']) || !is_array($storages['storage_names']) ? array(): $storages['storage_names']),
                    'multiple' => TRUE,
                        )
                )
                ->add('storage_names', 'choice', array(
                    'choices' => (empty($storages['storage_names']) || !is_array($storages['storage_names']) ? array(): $storages['storage_names']),
                    'multiple' => TRUE,
                        )
                )

                ->add('wowza_dvr', 'checkbox', array('required' => false))
                ->add('wowza_storage_names', 'choice', array(
                        'choices' => (empty($storages['wowza_storage_names']) || !is_array($storages['wowza_storage_names']) ? array(): $storages['wowza_storage_names']),
                        'multiple' => TRUE,
                    )
                )
                ->add('flussonic_dvr', 'checkbox', array('required' => false))
                ->add('flussonic_storage_names', 'choice', array(
                        'choices' => (empty($storages['flussonic_storage_names']) || !is_array($storages['flussonic_storage_names']) ? array(): $storages['flussonic_storage_names']),
                        'multiple' => TRUE,
                    )
                )
                ->add('volume_correction', 'choice', array(
                            'choices' => array_combine(range(-20, 20, 1), range(-100, 100, 5)),
                            'constraints' => array(
                                new Assert\Range(array('min' => -20, 'max' => 20)),
                                new Assert\NotBlank()),
                            'required' => TRUE,
                            'data' => (empty($data['volume_correction']) ? '0': $data['volume_correction'])
                        )
                    )
                ->add('logo', 'hidden')
                ->add('cmd', 'collection', $this->getDefaultOptions())
                ->add('user_agent_filter', 'collection', $this->getDefaultOptions())
                ->add('priority', 'collection', $this->getDefaultOptions())
                ->add('use_http_tmp_link', 'collection', $this->getDefaultOptions('checkbox'))
                ->add('wowza_tmp_link', 'collection', $this->getDefaultOptions())
                ->add('nginx_secure_link', 'collection', $this->getDefaultOptions())
                ->add('flussonic_tmp_link', 'collection', $this->getDefaultOptions())
                ->add('enable_monitoring', 'collection', $this->getDefaultOptions('checkbox'))
                ->add('monitoring_status', 'collection', $this->getDefaultOptions())
                ->add('enable_balancer_monitoring', 'collection', $this->getDefaultOptions())
                ->add('monitoring_url', 'collection', $this->getDefaultOptions())
                ->add('use_load_balancing', 'collection', $this->getDefaultOptions('checkbox'))
                ->add('stream_server', 'collection', $this->getDefaultOptions())
                ->add('enable_tv_archive', 'checkbox', array('required' => false))
                ->add('mc_cmd', 'text', array(
                    'constraints' => array(new Assert\Regex(array(
                        'pattern' => '/^(http|udp|rtp)\:\/\//',
                    ))),
                    'required' => false))
                ->add('tv_archive_duration', 'text', array(
                    'constraints' => new Assert\Range(array('min' => 0, 'max' => 999))
                    ))
                ->add('allow_pvr', 'checkbox', array('required' => false))
                ->add('xmltv_id', 'text', array('required' => false))
                ->add('correct_time', 'text', array(
                    'constraints' => new Assert\Range(array('min' => -720, 'max' => 840))
                    ))
                ->add('censored', 'checkbox', array('required' => false))
                ->add('base_ch', 'checkbox', array('required' => false))
                ->add('allow_local_timeshift', 'checkbox', array('required' => false))
                ->add('allow_local_pvr', 'checkbox', array('required' => false))
                ->add('save', 'submit');
//                ->add('reset', 'reset');

        return $form->getForm();
    }

    private function getDefaultOptions($type = 'hidden', $constraints = FALSE) {

        $options = array(
            'type' => $type,
            'options' => array(
                'required' => FALSE,
            ),
            'required' => FALSE,
            'allow_add' => TRUE,
            'allow_delete' => TRUE,
            'prototype' => FALSE
        );

        if ($type == 'checkbox') {
            $options['options']['empty_data'] = NULL;
        }

        if ($constraints !== FALSE) {
            $options['options']['constraints'] = $constraints;
        }

        return $options;
    }

    private function dataPrepare(&$data) {

        while (list($key, $row) = each($data)) {
            if (is_array($row)) {
                $this->dataPrepare($data[$key]);
//                $data[$key] = $row;
            } elseif ($row === 'on') {
                $data[$key] = 1;
            } elseif ($row === 'off') {
                $data[$key] = 0;
            }
        }
    }

    private function getLinks($data) {
        $urls = empty($data['cmd']) ? array() : $data['cmd'];
        $links = array();

        foreach ($urls as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $links[] = array(
                'url' => $value,
                'priority' => array_key_exists($key, $data['priority']) ? (int) $data['priority'][$key] : 0,
                'use_http_tmp_link' => !empty($data['use_http_tmp_link']) && array_key_exists($key, $data['use_http_tmp_link']) ? (int) $data['use_http_tmp_link'][$key] : 0,
                'wowza_tmp_link' => !empty($data['wowza_tmp_link']) && array_key_exists($key, $data['wowza_tmp_link']) ? (int) $data['wowza_tmp_link'][$key] : 0,
                'flussonic_tmp_link' => !empty($data['flussonic_tmp_link']) && array_key_exists($key, $data['flussonic_tmp_link']) ? (int) $data['flussonic_tmp_link'][$key] : 0,
                'nginx_secure_link' => !empty($data['nginx_secure_link']) && array_key_exists($key, $data['nginx_secure_link']) ? (int) $data['nginx_secure_link'][$key] : 0,
                'user_agent_filter' => array_key_exists($key, $data['user_agent_filter']) ? $data['user_agent_filter'][$key] : '',
                'monitoring_url' => array_key_exists($key, $data['monitoring_url']) ? $data['monitoring_url'][$key] : '',
                'use_load_balancing' => !empty($data['stream_server']) && array_key_exists($key, $data['stream_server']) && !empty($data['use_load_balancing']) && array_key_exists($key, $data['use_load_balancing']) ? (int) $data['use_load_balancing'][$key] : 0,
                'enable_monitoring' => !empty($data['enable_monitoring']) && array_key_exists($key, $data['enable_monitoring']) ? (int) $data['enable_monitoring'][$key] : 0,
                'enable_balancer_monitoring' => !empty($data['enable_balancer_monitoring']) && array_key_exists($key, $data['enable_balancer_monitoring']) ? (int) $data['enable_balancer_monitoring'][$key] : 0,
                'stream_servers' => !empty($data['stream_server']) && array_key_exists($key, $data['stream_server']) ? explode(';', $data['stream_server'][$key]) : array(),
            );
        }
        return $links;
    }

    private function saveChannelData(&$form) {
        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();
            if (empty($data['id'])) {
                $is_repeating_name = count($this->db->getFieldFirstVal('name', $data['name']));
                $is_repeating_number = count($this->db->getFieldFirstVal('number', $data['number']));
                $operation = 'insertITVChannel';
            } elseif (isset($this->oneChannel)) {
                $is_repeating_name = !(($this->oneChannel['name'] != $data['name']) xor ( (bool) count($this->db->getFieldFirstVal('name', $data['name']))));
                $is_repeating_number = !(($this->oneChannel['number'] != $data['number']) xor ( (bool) count($this->db->getFieldFirstVal('number', $data['number']))));
                $operation = 'updateITVChannel';
            }

            if ((!empty($data['allow_pvr']) || !empty($data['enable_tv_archive'])) && empty($data['mc_cmd'])) {
                $error_local = array();
                $error_local['mc_cmd'] = $this->setLocalization('This field cannot be empty if enabled TV-archive or nPVR');
                $this->app['error_local'] = $error_local;
                return FALSE;
            }

            if ($form->isValid()) {
                $this->dataPrepare($data);
                if (empty($data['cmd'])) {
                    $error_local['cmd'] = $this->setLocalization('Requires at least one link of broadcast');
                    $this->app['error_local'] = $error_local;
                    return FALSE;
                }
                
                if (!$is_repeating_name && !$is_repeating_number) {
                    $ch_id = $this->db->$operation($data);
                } else {
                    $error_local = array();
                    $error_local['name'] = ($is_repeating_name ? $this->setLocalization('This name already exists') : '');
                    $error_local['number'] = ($is_repeating_number ? $this->setLocalization('This number is already in use') : '');
                    $this->app['error_local'] = $error_local;
                    return FALSE;
                }

                if ($operation == 'updateITVChannel') {
                    $this->deleteChannelTasks($data, $this->oneChannel);
                    $this->deleteDBLinks($data);
                }
                if (!empty($data['logo'])) {
                    $ext = explode('.', $data['logo']);
                    $ext = $ext[count($ext) - 1];
                    $this->saveFiles->renameFile($this->logoDir, $data['logo'], "$ch_id.$ext");

                    if (empty($this->saveFiles->_error) || strpos($this->saveFiles->_error[count($this->saveFiles->_error) - 1], 'rename') === FALSE) {
                        $this->db->updateLogoName($ch_id, "$ch_id.$ext");
                    }
                }
               
                $this->setDBLincs($ch_id, $data);
                $this->createTasks($ch_id, $data);
                $this->setAllowedStoragesForChannel($ch_id, $data);

                return TRUE;
            }
        }
        return FALSE;
    }

    private function deleteChannelTasks($new_data, $old_data) {
        if ($old_data['enable_tv_archive'] != $new_data['enable_tv_archive'] || $old_data['wowza_dvr'] != $new_data['wowza_dvr'] || $old_data['flussonic_dvr'] != $new_data['flussonic_dvr']) {

            if ($old_data['enable_tv_archive']) {

                if ($old_data['flussonic_dvr']){
                    $archive = new \FlussonicTvArchive();
                } elseif ($old_data['wowza_dvr']){
                    $archive = new \WowzaTvArchive();
                }else{
                    $archive = new \TvArchive();
                }

                $archive->deleteTasks($old_data['id']);
            }
        }
    }

    private function createTasks($id, $data) {

        if (!empty($data['enable_tv_archive']) && $data['enable_tv_archive'] !== 'off'){

            $storage_names = array();
            if (!empty($data['flussonic_dvr']) && $data['flussonic_dvr'] !== 'off'){
                $archive = new \FlussonicTvArchive();
                if (!empty($data['flussonic_storage_names'])) {
                    $storage_names = $data['flussonic_storage_names'];
                }
            } elseif (!empty($data['wowza_dvr']) && $data['wowza_dvr'] !== 'off'){
                $archive = new \WowzaTvArchive();
                if (!empty($data['wowza_storage_names'])) {
                    $storage_names = $data['wowza_storage_names'];
                }
            }else{
                $archive = new \TvArchive();
                if (!empty($data['storage_names'])) {
                    $storage_names = $data['storage_names'];
                }
            }

            $archive->createTasks($id, $storage_names);
        }
    }

    private function setAllowedStoragesForChannel($id, $data) {
        if ($data['allow_pvr']) {
            \RemotePvr::setAllowedStoragesForChannel($id, $data['pvr_storage_names']);
        }
    }

    private function deleteDBLinks($data) {
        if (!isset($this->channeLinks)) {
            $this->channeLinks = $this->db->getChannelLinksById($this->oneChannel['id']);
        }

        $need_to_delete_links = $this->db->getUnnecessaryLinks($this->oneChannel['id'], $data['cmd']);

        if ($need_to_delete_links) {
            $this->db->deleteCHLink($need_to_delete_links);
            $this->db->deleteCHLinkOnStreamer($need_to_delete_links);
        }
    }

    private function setDBLincs($ch_id, $data) {

        $current_urls = (!empty($this->channeLinks) ? $this->getFieldFromArray($this->channeLinks, 'url') : array());
        foreach ($this->getLinks($data) as $link) {
            $link['ch_id'] = $ch_id;
            if (is_array($link['stream_servers']) && !empty($link['stream_servers'])) {
                $link['stream_servers'] = call_user_func_array('array_merge', array_map(function($row){
                    if (is_string($row)) {
                        $row = explode(',', $row);
                    } else {
                        $row = array();
                    }
                    return $row;
                }, $link['stream_servers']));
            }
            $links_on_server = $link['stream_servers'];
            unset($link['stream_servers']);
            if (!in_array($link['url'], $current_urls)) {
                $link_id = $this->db->insertCHLink($link);
                if ($link_id && !empty($links_on_server)) {
                    foreach ($links_on_server as $streamer_id) {
                        $this->db->insertCHLinkOnStreamer($link_id, $streamer_id);
                    }
                }
            } else {

                $link_id = $this->getLinkIDByChIDAndUrl($ch_id, $link['url']);
                if (!$link['enable_monitoring']) {
                    $link['status'] = 1;
                }

                if ($link_id) {
                    $this->db->updateCHLink($ch_id, $link);

                    if (empty($this->streamers_map)) {
                        $this->streamers_map[$link_id] = $this->db->getStreamersIdMapForLink($link_id);
                    }
                    $on_streamers = array();
                    if (is_array($this->streamers_map) && array_key_exists($link_id, $this->streamers_map)) {
                        $on_streamers = array_keys($this->streamers_map[$link_id]);
                    }

                    if ($on_streamers) {
                        $need_to_delete = array_diff($on_streamers, $links_on_server);
                        $links_on_server = array_diff($links_on_server, $on_streamers);

                        if ($need_to_delete) {
                            $this->db->deleteCHLinkOnStreamerByLinkAndID($link_id, $need_to_delete);
                        }
                    }
                    foreach ($links_on_server as $streamer_id) {
                        $this->db->insertCHLinkOnStreamer($link_id, $streamer_id);
                    }
                }
            }
        }
    }

    private function getLinkIDByChIDAndUrl($ch_id, $url) {

        foreach ($this->channeLinks as $row) {
            if ($row['ch_id'] == $ch_id && $row['url'] == $url) {
                return $row['id'];
            }
        }
    }

    private function getIPTVfilters() {
        $filters = array();

        if (array_key_exists('filters', $this->data)) {

            if (array_key_exists('tv_genre_id', $this->data['filters']) && $this->data['filters']['tv_genre_id'] != 0) {
                $filters['tv_genre_id'] = $this->data['filters']['tv_genre_id'];
            }
            if (array_key_exists('archive_id', $this->data['filters']) && $this->data['filters']['archive_id'] != 0) {
                $filters['enable_tv_archive'] = (int) ($this->data['filters']['archive_id'] == 1);
            }
            if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
                $filters['status'] = (int) ($this->data['filters']['status_id'] == 1);
            }

            if (array_key_exists('monitoring_status', $this->data['filters']) && $this->data['filters']['monitoring_status'] != 0) {
                $filters['enable_monitoring'] = (int) !($this->data['filters']['monitoring_status'] == 1);
            }

            $this->app['filters'] = $this->data['filters'];
        } else {
            $this->app['filters'] = array();
        }
        return $filters;
    }

    private function fillEmptyRows($input_array = array()){
        $result = array();
        $empty_row = array('logo'=>'', 'name' =>'', 'id'=>'', 'number'=>0, 'empty'=>TRUE, 'locked'=>FALSE);
        reset($input_array);
        $begin_val = 1;
        while(list($key, $row) = each($input_array)){
            while ($begin_val < $row['number']) {
                $empty_row['number'] = $begin_val;
                $result[] = $empty_row;
                $begin_val++;
            }
            $row['empty'] = FALSE;
            $result[] = $row;
            $begin_val++;
        }
        reset($result);
        return $result;
    }

    private function getIptvListDropdownAttribute(){
        return array(
			array('name' => 'id',               'title' => $this->setLocalization('ID'),                                'checked' => FALSE),
			array('name' => 'number',           'title' => $this->setLocalization('Number'),                            'checked' => TRUE),
			array('name' => 'logo',             'title' => $this->setLocalization('Logo'),                              'checked' => TRUE),
            array('name' => 'name',             'title' => $this->setLocalization('Title'),                             'checked' => TRUE),
            array('name' => 'genres_name',      'title' => $this->setLocalization('Genre'),                             'checked' => TRUE),
            array('name' => 'enable_tv_archive','title' => $this->setLocalization('Archive'),                           'checked' => TRUE),
            array('name' => 'cmd',              'title' => $this->setLocalization('URL'),                               'checked' => TRUE),
            array('name' => 'xmltv_id',         'title' => $this->setLocalization('XMLTV ID'),                          'checked' => FALSE),
            array('name' => 'claims',           'title' => $this->setLocalization('Claims about audio/video/epg'),      'checked' => FALSE),
            array('name' => 'monitoring_status','title' => $this->setLocalization('Monitoring status'),                 'checked' => TRUE),
            array('name' => 'status',           'title' => $this->setLocalization('Status'),                            'checked' => TRUE),
            array('name' => 'operations',       'title' => $this->setLocalization('Operations'),                        'checked' => TRUE)
        );

    }

    private function get_epg_row($date, $epg_lines, $line_num = 0){

        $epg_line = @trim($epg_lines[$line_num]);

        preg_match("/(\d+):(\d+)[\s\t]*([\S\s]+)/", $epg_line, $tmp_line);

        if (@$tmp_line[1] && $tmp_line[2] && $tmp_line[3]){

            $result = array();

            $time = $date.' '.$tmp_line[1].':'.$tmp_line[2].':00';

            $result['time'] = $time;

            $result['name'] = $tmp_line[3];

            $next_line = $this->get_epg_row($date, $epg_lines, $line_num+1);

            if (!empty($next_line)){

                $time_to = $next_line['time'];

                $result['time_to'] = $time_to;

                $result['duration'] = strtotime($time_to) - strtotime($time);
            }else{
                $result['time_to'] = 0;
                $result['duration'] = 0;
            }

            return $result;
        }

        return false;
    }

    private function getEpgDropdownAttribute(){
        return array(
            array('name'=>'id',         'title'=>$this->setLocalization('ID'),              'checked' => TRUE),
            array('name'=>'id_prefix',  'title'=>$this->setLocalization('Prefix'),          'checked' => TRUE),
            array('name'=>'uri',        'title'=>$this->setLocalization('URL'),             'checked' => TRUE),
            array('name'=>'etag',       'title'=>$this->setLocalization('XMLTV file hash'), 'checked' => TRUE),
            array('name'=>'updated',    'title'=>$this->setLocalization('Update date'),     'checked' => TRUE),
            array('name'=>'status',     'title'=>$this->setLocalization('State'),           'checked' => TRUE),
            array('name'=>'operations', 'title'=>$this->setLocalization('Operations'),      'checked' => TRUE)
        );
    }

    private function getStorages($id = FALSE){
        $return = array('storage_names' => array(), 'wowza_storage_names' => array(), 'flussonic_storage_names' => array());
        foreach ($this->db->getStorages() as $key => $value) {
            if (($value['flussonic_dvr'] && !$value['wowza_dvr']) ) {
                $return['flussonic_storage_names'][$value['storage_name']] = $value['storage_name'];
            } elseif (!$value['flussonic_dvr'] && $value['wowza_dvr']) {
                $return['wowza_storage_names'][$value['storage_name']] = $value['storage_name'];
            } else {
                $return['storage_names'][$value['storage_name']] = $value['storage_name'];
            }
        }
        if ($id !== FALSE) {
            $tasks = ($id == FALSE ? array(): \TvArchive::getTasksByChannelId($id));
            if (!empty($tasks)){
                $return = array_map(function($row) use ($tasks){
                    $names = array_filter(array_map(function($task_row) use ($row){
                        if (in_array($task_row['storage_name'], $row)) {
                            return $task_row['storage_name'];
                        }
                    }, $tasks));
                    return (is_array($names) && !empty($names) ? array_combine(array_values($names), $names): array());
                }, $return );
            } else {
                $return = array('storage_names' => array(), 'wowza_storage_names' => array(), 'flussonic_storage_names' => array());
            }
        }
        return $return;
    }

    private function getGenresDropdownAttribute(){
        return array(
            array('name'=>'number',         'title'=>$this->setLocalization('Order'),           'checked' => TRUE),
            array('name'=>'title',          'title'=>$this->setLocalization('Title'),           'checked' => TRUE),
            array('name'=>'localized_title','title'=>$this->setLocalization('Localized title'), 'checked' => TRUE),
            array('name'=>'operations',     'title'=>$this->setLocalization('Operation'),       'checked' => TRUE)
        );
    }

    private function getLanguageCodesEN() {
        return array(
            'aa' => $this->setLocalization('Afar'),
            'ab' => $this->setLocalization('Abkhazian'),
            'af' => $this->setLocalization('Afrikaans'),
            'ak' => $this->setLocalization('Akan'),
            'am' => $this->setLocalization('Amharic'),
            'ar' => $this->setLocalization('Arabic'),
            'as' => $this->setLocalization('Assamese'),
            'av' => $this->setLocalization('Avaric'),
            'ae' => $this->setLocalization('Avestan'),
            'ay' => $this->setLocalization('Aymara'),
            'az' => $this->setLocalization('Azerbaijani'),
            'ba' => $this->setLocalization('Bashkir'),
            'bm' => $this->setLocalization('Bambara'),
            'eu' => $this->setLocalization('Basque'),
            'be' => $this->setLocalization('Belarusian'),
            'bn' => $this->setLocalization('Bengali'),
            'bi' => $this->setLocalization('Bislama'),
            'bo' => $this->setLocalization('Tibetan'),
            'bs' => $this->setLocalization('Bosnian'),
            'br' => $this->setLocalization('Breton'),
            'bg' => $this->setLocalization('Bulgarian'),
            'ca' => $this->setLocalization('Catalan'),
            'cs' => $this->setLocalization('Czech'),
            'ch' => $this->setLocalization('Chamorro'),
            'ce' => $this->setLocalization('Chechen'),
            'cu' => $this->setLocalization('Church Slavic'),
            'cv' => $this->setLocalization('Chuvash'),
            'kw' => $this->setLocalization('Cornish'),
            'co' => $this->setLocalization('Corsican'),
            'cy' => $this->setLocalization('Welsh'),
            'da' => $this->setLocalization('Danish'),
            'de' => $this->setLocalization('German'),
            'dv' => $this->setLocalization('Divehi'),
            'dz' => $this->setLocalization('Dzongkha'),
            'el' => $this->setLocalization('Greek'),
            'en' => $this->setLocalization('English'),
            'eo' => $this->setLocalization('Esperanto'),
            'et' => $this->setLocalization('Estonian'),
            'ee' => $this->setLocalization('Ewe'),
            'fo' => $this->setLocalization('Faroese'),
            'fa' => $this->setLocalization('Persian'),
            'fj' => $this->setLocalization('Fijian'),
            'fi' => $this->setLocalization('Finnish'),
            'fr' => $this->setLocalization('French'),
            'fy' => $this->setLocalization('Western Frisian'),
            'ff' => $this->setLocalization('Fulah'),
            'ka' => $this->setLocalization('Georgian'),
            'gd' => $this->setLocalization('Gaelic'),
            'ga' => $this->setLocalization('Irish'),
            'gl' => $this->setLocalization('Galician'),
            'gv' => $this->setLocalization('Manx'),
            'gn' => $this->setLocalization('Guarani'),
            'gu' => $this->setLocalization('Gujarati'),
            'ha' => $this->setLocalization('Hausa'),
            'he' => $this->setLocalization('Hebrew'),
            'hz' => $this->setLocalization('Herero'),
            'hi' => $this->setLocalization('Hindi'),
            'ho' => $this->setLocalization('Hiri Motu'),
            'hr' => $this->setLocalization('Croatian'),
            'hu' => $this->setLocalization('Hungarian'),
            'hy' => $this->setLocalization('Armenian'),
            'ig' => $this->setLocalization('Igbo'),
            'iu' => $this->setLocalization('Inuktitut'),
            'ie' => $this->setLocalization('Interlingue'),
            'ia' => $this->setLocalization('Interlingua'),
            'id' => $this->setLocalization('Indonesian'),
            'ik' => $this->setLocalization('Inupiaq'),
            'is' => $this->setLocalization('Icelandic'),
            'it' => $this->setLocalization('Italian'),
            'jv' => $this->setLocalization('Javanese'),
            'ja' => $this->setLocalization('Japanese'),
            'kl' => $this->setLocalization('Kalaallisut'),
            'kn' => $this->setLocalization('Kannada'),
            'ks' => $this->setLocalization('Kashmiri'),
            'kr' => $this->setLocalization('Kanuri'),
            'kk' => $this->setLocalization('Kazakh'),
            'km' => $this->setLocalization('Central Khmer'),
            'ki' => $this->setLocalization('Kikuyu'),
            'rw' => $this->setLocalization('Kinyarwanda'),
            'ky' => $this->setLocalization('Kirghiz'),
            'kv' => $this->setLocalization('Komi'),
            'kg' => $this->setLocalization('Kongo'),
            'ko' => $this->setLocalization('Korean'),
            'kj' => $this->setLocalization('Kuanyama'),
            'ku' => $this->setLocalization('Kurdish'),
            'lo' => $this->setLocalization('Lao'),
            'la' => $this->setLocalization('Latin'),
            'lv' => $this->setLocalization('Latvian'),
            'ln' => $this->setLocalization('Lingala'),
            'lt' => $this->setLocalization('Lithuanian'),
            'lb' => $this->setLocalization('Luxembourgish'),
            'lu' => $this->setLocalization('Luba-Katanga'),
            'lg' => $this->setLocalization('Ganda'),
            'mk' => $this->setLocalization('Macedonian'),
            'mh' => $this->setLocalization('Marshallese'),
            'ml' => $this->setLocalization('Malayalam'),
            'mi' => $this->setLocalization('Maori'),
            'mr' => $this->setLocalization('Marathi'),
            'mg' => $this->setLocalization('Malagasy'),
            'mt' => $this->setLocalization('Maltese'),
            'mn' => $this->setLocalization('Mongolian'),
            'ms' => $this->setLocalization('Malay'),
            'my' => $this->setLocalization('Burmese'),
            'na' => $this->setLocalization('Nauru'),
            'nv' => $this->setLocalization('Navajo'),
            'nr' => $this->setLocalization('Ndebele'),
            'nd' => $this->setLocalization('North Ndebele'),
            'ng' => $this->setLocalization('Ndonga'),
            'ne' => $this->setLocalization('Nepali'),
            'nl' => $this->setLocalization('Dutch'),
            'no' => $this->setLocalization('Norwegian'),
            'ny' => $this->setLocalization('Chichewa'),
            'oc' => $this->setLocalization('Occitan'),
            'oj' => $this->setLocalization('Ojibwa'),
            'or' => $this->setLocalization('Oriya'),
            'om' => $this->setLocalization('Oromo'),
            'os' => $this->setLocalization('Ossetian'),
            'pa' => $this->setLocalization('Panjabi'),
            'pi' => $this->setLocalization('Pali'),
            'pl' => $this->setLocalization('Polish'),
            'pt' => $this->setLocalization('Portuguese'),
            'ps' => $this->setLocalization('Pushto'),
            'qu' => $this->setLocalization('Quechua'),
            'rm' => $this->setLocalization('Romansh'),
            'ro' => $this->setLocalization('Romanian'),
            'rn' => $this->setLocalization('Rundi'),
            'ru' => $this->setLocalization('Russian'),
            'sg' => $this->setLocalization('Sango'),
            'sa' => $this->setLocalization('Sanskrit'),
            'si' => $this->setLocalization('Sinhala'),
            'sk' => $this->setLocalization('Slovak'),
            'sl' => $this->setLocalization('Slovenian'),
            'sm' => $this->setLocalization('Samoan'),
            'sn' => $this->setLocalization('Shona'),
            'sd' => $this->setLocalization('Sindhi'),
            'so' => $this->setLocalization('Somali'),
            'st' => $this->setLocalization('Southern Sotho'),
            'es' => $this->setLocalization('Spanish'),
            'sq' => $this->setLocalization('Albanian'),
            'sc' => $this->setLocalization('Sardinian'),
            'sr' => $this->setLocalization('Serbian'),
            'ss' => $this->setLocalization('Swati'),
            'su' => $this->setLocalization('Sundanese'),
            'sw' => $this->setLocalization('Swahili'),
            'sv' => $this->setLocalization('Swedish'),
            'ty' => $this->setLocalization('Tahitian'),
            'ta' => $this->setLocalization('Tamil'),
            'tt' => $this->setLocalization('Tatar'),
            'te' => $this->setLocalization('Telugu'),
            'tg' => $this->setLocalization('Tajik'),
            'tl' => $this->setLocalization('Tagalog'),
            'th' => $this->setLocalization('Thai'),
            'ti' => $this->setLocalization('Tigrinya'),
            'to' => $this->setLocalization('Tonga'),
            'tn' => $this->setLocalization('Tswana'),
            'ts' => $this->setLocalization('Tsonga'),
            'tk' => $this->setLocalization('Turkmen'),
            'tr' => $this->setLocalization('Turkish'),
            'tw' => $this->setLocalization('Twi'),
            'ug' => $this->setLocalization('Uighur'),
            'uk' => $this->setLocalization('Ukrainian'),
            'ur' => $this->setLocalization('Urdu'),
            'uz' => $this->setLocalization('Uzbek'),
            've' => $this->setLocalization('Venda'),
            'vi' => $this->setLocalization('Vietnamese'),
            'vo' => $this->setLocalization('Volap'),
            'wo' => $this->setLocalization('Wolof'),
            'xh' => $this->setLocalization('Xhosa'),
            'yi' => $this->setLocalization('Yiddish'),
            'yo' => $this->setLocalization('Yoruba'),
            'za' => $this->setLocalization('Zhuang'),
            'zh' => $this->setLocalization('Chinese'),
            'zu' => $this->setLocalization('Zulu')
        );
    }

    private function getMonitoringStatus($row) {
        $return = '';
        if (!((int)$row['enable_monitoring'])) {
            $return .= $this->setLocalization('monitoring off');
        } else {
            $diff = time() - strtotime($row['monitoring_status_updated']);
            if ($diff > 3600) {
                $return .= $this->setLocalization('more than an hour ago');
            } else if ($diff < 60) {
                $return .= $this->setLocalization('less than a minute ago');
            } else {
                $return .= $this->setLocalization('{{minute}} minutes ago', '', 0, array('{{minute}}' => round($diff / 60)));
            }
            $return .= '<br><span style="color: ';

            $disabled_link = $this->db->getChanelDisabledLink($row['id']);
            $status = $this->getFieldFromArray($disabled_link, 'status');
            $total_status = array_sum($status);

            if ($total_status != 0) {
                if ($total_status != count($status)) {
                    if (!empty($this->data['filters']) && array_key_exists('monitoring_status', $this->data['filters']) && ((int) $this->data['filters']['monitoring_status']) != 0 && ((int) $this->data['filters']['monitoring_status']) != 4) {
                        return FALSE;
                    }
                    $return .= '#f4c430;">' . $this->setLocalization('there are some problems');
                } else {
                    if (!empty($this->data['filters']) && array_key_exists('monitoring_status', $this->data['filters']) && ((int) $this->data['filters']['monitoring_status']) != 0 && ((int) $this->data['filters']['monitoring_status']) != 3) {
                        return FALSE;
                    }
                    $return .= 'green;">' . $this->setLocalization('no errors');
                }
            } else {
                if (!empty($this->data['filters']) && array_key_exists('monitoring_status', $this->data['filters']) && ((int) $this->data['filters']['monitoring_status']) != 0 && ((int) $this->data['filters']['monitoring_status']) != 2) {
                    return FALSE;
                }
                $return .= 'red;">' . $this->setLocalization('errors occurred');
            }
            $return .= '</span>';
        }
        return $return;
    }

    private function getAllGenres(){
        $getAllGenres = $this->db->getAllGenres();
        foreach($this->setLocalization($getAllGenres, 'title') as $key=>$row){
            $getAllGenres[$key]['title'] = $this->mb_ucfirst($row['title']);
        }
        return $getAllGenres;
    }

    private function getAllChannelsFields(){
        return array(
            'id' => 'itv.id as `id`',
            'locked' => 'itv.locked as `locked`',
            'number' => 'itv.number as `number`',
            'logo' => 'itv.logo as `logo`',
            'name' => 'itv.name as `name`',
            'genres_name' => 'tv_genre.title as `genres_name`',
            'enable_tv_archive' => 'itv.enable_tv_archive as `enable_tv_archive`',
            'cmd' => 'itv.cmd as `cmd`',
            'monitoring_status' => 'itv.monitoring_status as `monitoring_status`',
            'status' => 'itv.status as `status`',
            'media_type' => 'media_claims.media_type',
            'media_id' => ' media_claims.media_id',
            'sound_counter' =>'media_claims.sound_counter',
            'video_counter' => 'media_claims.video_counter',
            'no_epg' => 'media_claims.no_epg',
            'wrong_epg' => 'media_claims.wrong_epg',
            'enable_monitoring' => 'itv.enable_monitoring',
            'monitoring_status_updated' => 'itv.monitoring_status_updated',
            'xmltv_id' => 'itv.xmltv_id'
        );
    }
}
