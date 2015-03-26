<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

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
        'enable_balancer_monitoring' => array(''),
        'monitoring_url' => array(''),
        'use_load_balancing' => array(FALSE),
        'stream_server' => array('')
    );

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);
        
        $this->logoHost = $this->baseHost . "/stalker_portal/misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
        $this->app['allArchive'] = array(
            array('id' => 1, 'title' => 'Да'),
            array('id' => 2, 'title' => 'Нет')
        );
        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => 'Включен'),
            array('id' => 2, 'title' => 'Отключен')
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
        
        $filter = array();

        if ($this->method == 'GET' && !empty($this->data['filters'])) {
            $filter = $this->getIPTVfilters();
        }

        $allChannels = $this->db->getAllChannels($filter);
        $allChannels = $this->setLocalization($allChannels, 'genres_name');
        if (is_array($allChannels)) {
            while (list($num, $row) = each($allChannels)) {
                $allChannels[$num]['logo'] = $this->getLogoUriById(FALSE, $row);
            }
        }

        $this->app['allChannels'] = $allChannels;
        $getAllGenres = $this->db->getAllGenres();
        $this->app['allGenres'] = $this->setLocalization($getAllGenres, 'title');
        
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

        $allChannels = $this->db->getAllChannels($filter);

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
        $getAllGenres = $this->db->getAllGenres();
        $this->app['allGenres'] = $this->setLocalization($getAllGenres, 'title');
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

        $getAllGenres = $this->db->getAllGenres();
        $this->app['allGenres'] = $this->setLocalization($getAllGenres, 'title');
        $this->app['channelEdit'] = TRUE;
        $this->oneChannel = $this->db->getChannelById($id);
        settype($this->oneChannel['enable_tv_archive'], 'boolean');
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

        return $this->app['twig']->render('TvChannels_add_channel.twig');
    }
    
    //----------------------- ajax method --------------------------------------

    public function remove_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (empty($this->data['id']) || (!is_numeric($this->data['id']))) {
            $this->app->abort(404, 'Канал с не найден');
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
            $this->app->abort(404, 'Канал с не найден');
        }

        $rows = $this->db->changeChannelStatus($this->data['id'], 0);
        $response = $this->generateAjaxResponse(array('rows' => $rows, 'action' => 'Включить', 'status' => 'Отключен', 'urlactfrom' => 'disable', 'urlactto' => 'enable'), ($rows) ? '' : 'Cannot find channel');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function enable_channel() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        if (empty($this->data['id']) || (!is_numeric($this->data['id']))) {
            $this->app->abort(404, 'Канал с не найден');
        }

        $rows = $this->db->changeChannelStatus($this->data['id'], 1);
        $response = $this->generateAjaxResponse(array('rows' => $rows, 'action' => 'Отключить', 'status' => 'Включен', 'urlactfrom' => 'enable', 'urlactto' => 'disable'), ($rows) ? '' : 'Cannot find channel');

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function edit_logo() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if (empty($this->data['id']) || (!is_numeric($this->data['id']) && strpos($this->data['id'], 'new') === FALSE)) {
            $this->app->abort(404, 'Канал с не найден');
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
        if (!$this->isAjax || empty($this->data['id']) || (!is_numeric($this->data['id']) && strpos($this->data['id'], 'new') == FALSE)) {
            $this->app->abort(404, 'Канал с не найден');
        }

        $channel = $this->db->getChannelById($id);

        $this->saveFiles->removeFile($this->logoDir, $channel['logo']);
        $error = $this->saveFiles->getError();
        $response = $this->generateAjaxResponse(array('data' => 0), $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function move_apply() {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if (!$this->isAjax) {
            $this->app->abort(404, 'The unexpected request');
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
                if (!$this->db->updateChannelNum($row)) {
                    $erorr = 'Failed to save, update the channel list';
                    $senddata = array('action' => 'canceled');
                }
            }
        }
        $response = $this->generateAjaxResponse($senddata, $erorr);
        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function toogle_lock_channel(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        
        if (!$this->isAjax) {
            $this->app->abort(404, 'The unexpected request');
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
                $row['locked'] = (!empty($row['locked']) || $row['locked'] == "true") ? 1: 0;
                if (!$this->db->updateChannelLockedStatus($row)) {
                    $erorr = 'Failed to save, update the channel list';
                    $senddata = array('action' => 'canceled');
                }
            }
        }
        $response = $this->generateAjaxResponse($senddata, $erorr);
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

        $storages = array();

        foreach ($this->db->getStorages() as $key => $value) {
            $storages[$value['storage_name']] = $value['storage_name'];
        }
        $def_number = 0;
        $def_name = 'Channel';
        if (!empty($data['number'])){
            $def_number = $data['number'];
        } else {
            $def_number = $this->getFirstFreeChannelNumber();
        }
        
        if (!empty($data['name'])){
            $def_name = $data['name'];
        } else {
           $def_name = "$def_name $def_number";
        }
        
        $form = $builder->createBuilder('form', $data)
                ->add('id', 'hidden')
                ->add('number', 'text', array(
                    'constraints' => new Assert\Range(array('min' => 0, 'max' => 999)),
                    'data' => $def_number
                    ))
                ->add('name', 'text', array(
                        'constraints' => array(
//                                    new Assert\Regex(array(
//                                        'pattern' => '/[^\w,\!,\@,\#,\$,\%,\^,\&,\*,\(,\),\_,\-,\+,\:,\;,\,,\.,\s]/',
//                                        'match' => false
//                                        )
//                                    ),
                                new Assert\NotBlank()
                            ),
                        'data' => $def_name
                        )
                )
                ->add('tv_genre_id', 'choice', array(
                    'choices' => $genres,
                    'constraints' => array(new Assert\Choice(array('choices' => array_keys($genres))))
                        )
                )
                ->add('pvr_storage_names', 'choice', array(
                    'choices' => $storages,
                    'multiple' => TRUE,
//                    'constraints' => array(new Assert\Choice(array('choices' => $storages)))
                        )
                )
                ->add('storage_names', 'choice', array(
                    'choices' => $storages,
                    'multiple' => TRUE,
//                    'constraints' => array(new Assert\Choice(array('choices' => $storages)))
                        )
                )
                ->add('volume_correction', 'choice', array(
                            'choices' => array_combine(range(-20, 20, 1), range(-100, 100, 5)),
                            'constraints' => array(
                                new Assert\Range(array('min' => -20, 'max' => 20)), 
                                new Assert\NotBlank()),
                            'required' => TRUE,
                            'data' => (empty($data['volume_correction']) ? 0: $data['volume_correction'])
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
                ->add('enable_balancer_monitoring', 'collection', $this->getDefaultOptions())
                ->add('monitoring_url', 'collection', $this->getDefaultOptions())
                ->add('use_load_balancing', 'collection', $this->getDefaultOptions('checkbox'))
                ->add('stream_server', 'collection', $this->getDefaultOptions())
                ->add('enable_tv_archive', 'checkbox', array('required' => false))
                ->add('mc_cmd', 'text', array('required' => false))
                ->add('tv_archive_duration', 'text', array(
                    'constraints' => new Assert\Range(array('min' => 0, 'max' => 999))
                    ))
                ->add('allow_pvr', 'checkbox', array('required' => false))
                ->add('xmltv_id', 'text', array('required' => false))
                ->add('correct_time', 'text', array(
                    'constraints' => new Assert\Range(array('min' => 0, 'max' => 999))
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
                $this->dataPrepare($row);
//                $data[$key] = $row;
            } elseif ($row == 'on') {
                $data[$key] = 1;
            } elseif ($row == 'off') {
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

            if ($form->isValid()) {
                $this->dataPrepare($data);

                if (!$is_repeating_name && !$is_repeating_number) {
                    $ch_id = $this->db->$operation($data);
                } else {
                    $error_local = array();
                    $error_local['name'] = ($is_repeating_name ? 'Такое имя уже есть' : '');
                    $error_local['number'] = ($is_repeating_number ? 'Такое номер уже есть' : '');
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
        if ($old_data['enable_tv_archive'] != $new_data['enable_tv_archive'] || $old_data['wowza_dvr'] != $new_data['wowza_dvr']) {

            if ($old_data['enable_tv_archive']) {

                if ($old_data['wowza_dvr']) {
                    $archive = new \WowzaTvArchive();
                } else {
                    $archive = new \TvArchive();
                }

                $archive->deleteTasks($old_data['id']);
            }
        }
    }

    private function createTasks($id, $data) {
        if ($data['enable_tv_archive']) {
            $archive = new \TvArchive();
            $archive->createTasks($id, $data['storage_names']);
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
            $links_on_server = $link['stream_servers'];
            unset($link['stream_servers']);
            if (!in_array($link['url'], $current_urls)) {
                $link_id = $this->db->insertCHLink($link);
                if ($link_id && $links_on_server) {
                    foreach ($links_on_server as $streamer_id) {
                        $this->db->insertCHLinkOnStreamer($link_id, $streamer_id);
                    }
                }
            } else {

                $link_id = $this->getLinkIDByChIDAndUrl($ch_id, $link['url']);
                if (!$link['enable_monitoring']) {
                    $link['status'] = 1;
                }
                $this->db->updateCHLink($ch_id, $link);

                if ($link_id) {
                    if (empty($this->streamers_map)) {
                        $this->streamers_map[$link_id] = $this->db->getStreamersIdMapForLink($link_id);
                    }
                    $on_streamers = array_keys($this->streamers_map[$link_id]);

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
        if (array_key_exists('tv_genre_id', $this->data['filters']) && $this->data['filters']['tv_genre_id'] != 0) {
            $filters[] = "tv_genre_id = '" . $this->data['filters']['tv_genre_id'] . "'";
        }
        if (array_key_exists('archive_id', $this->data['filters']) && $this->data['filters']['archive_id'] != 0) {
            $filters[] = "enable_tv_archive = '" . (int) ($this->data['filters']['archive_id'] == 1) . "'";
        }
        if (array_key_exists('status_id', $this->data['filters']) && $this->data['filters']['status_id'] != 0) {
            $filters[] = "status='" . (int) ($this->data['filters']['status_id'] == 1) . "'";
        }
        $this->app['filters'] = $this->data['filters'];
        return $filters;
    }
    
    private function getFirstFreeChannelNumber(){
        $channels = $this->db->getAllChannels();
        while(list($key, $row) = each($channels)) {
            $next_key = $key+1;
            if (array_key_exists($next_key, $channels)) {
                if (($channels[$next_key]['number'] - $row['number']) >=2 ) {
                    return ++$row['number'];
                }
            } 
        }
        return ++$row['number'];
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
			array('name' => 'id',             'title' => 'ID',      'checked' => false),
			array('name' => 'number',           'title' => 'Номер',     'checked' => TRUE),
			array('name' => 'logo',             'title' => 'Лого',      'checked' => TRUE),
            array('name' => 'name',             'title' => 'Название',  'checked' => TRUE),
            array('name' => 'genres_name',      'title' => 'Жанр',      'checked' => TRUE),
            array('name' => 'enable_tv_archive','title' => 'ТВ-архив',  'checked' => TRUE),
            array('name' => 'cmd',              'title' => 'URL',       'checked' => TRUE),
            array('name' => 'status',           'title' => 'Статус',    'checked' => TRUE),
            array('name' => 'operations',       'title' => 'Действия',  'checked' => TRUE)
        );
        
    }
}
