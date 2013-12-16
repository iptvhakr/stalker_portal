<?php
/**
 * Main STB class.
 * 
 * @package stalker_portal
 * @author Aleksey Zhurbitsky <zhurbitsky@gmail.com>
 * Special thanks to Ivan Bratash (Johnatan) for the security audit.
 */

class Stb implements \Stalker\Lib\StbApi\Stb
{
    public $id  = 0;
    public $mac = '';
    public $ip;
    public $hd  = 0;
    private $user_agent = '';
    private $access_token = null;
    private $is_moderator = false;
    private $params = array();
    private $db;
    public $lang;
    private $locale;
    private $country_id;
    public $city_id;
    public $timezone;
    public static $server_timezone;
    public $timezone_diff = 0;
    private $stb_lang;
    public $additional_services_on = 0;
    private static $just_created = false;

    //private static $all_modules = array();
    //private static $disabled_modules = array();
    private static $allowed_languages;
    //private static $allowed_locales;
    
    private static $instance = NULL;

    /**
     * @static
     * @return Stb
     */
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Stb();
        }
        return self::$instance;
    }
    
    private function __construct(){

        /*if (!empty($_COOKIE['debug']) || !empty($_REQUEST['debug'])){
            Mysql::$debug = true;
        }*/

        $debug_key = $this->getDebugKey();

        $this->user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];

        $this->parseAuthorizationHeader();

        if (!empty($debug_key) && $this->checkDebugKey($debug_key)){

            if (!empty($_REQUEST['mac'])){
                $this->mac = @trim(urldecode($_REQUEST['mac']));
            }elseif (!empty($_COOKIE['mac'])){
                $this->mac = @trim(urldecode($_COOKIE['mac']));
            }else{
                echo 'Identification failed';
                exit;
            }

            if (!empty($_COOKIE['debug']) || !empty($_REQUEST['debug'])){
                Mysql::$debug = true;
            }

        }else if (!empty($_COOKIE['mac']) && empty($_COOKIE['mac_emu'])){
            $this->mac = @trim(urldecode($_COOKIE['mac']));

            if (!empty($_GET['action']) && $_GET['action'] != 'handshake' && $_GET['action'] != 'get_profile' && $_GET['action'] != 'get_localization' && $_GET['action'] != 'do_auth' && !$this->isValidAccessToken($this->access_token)){
                error_log("STB authorization failed. MAC: ".$this->mac.", token: ".$this->access_token);
                echo 'Authorization failed.';
                exit;
            }

        }else if (!empty($_SERVER['TARGET']) && ($_SERVER['TARGET'] == 'API' || $_SERVER['TARGET'] == 'ADM') || !empty($_GET['type']) && $_GET['type'] == 'stb'){

        }else{
            $this->mac = '';
            echo 'Unauthorized request.';
            exit;
        }

        $this->mac = strtoupper($this->mac);

        if (!empty($_COOKIE['stb_lang'])){
            $this->stb_lang = @trim(urldecode($_COOKIE['stb_lang']));
        }

        if (!empty($_COOKIE['timezone']) && $_COOKIE['timezone'] != 'undefined'){
            $this->timezone = @trim($_COOKIE['timezone']);
        }

        //var_dump($_COOKIE, $this->stb_lang);
        
        if (@$_SERVER['HTTP_X_REAL_IP']){
            $this->ip = @$_SERVER['HTTP_X_REAL_IP'];
        }else{
            $this->ip = @$_SERVER['REMOTE_ADDR'];
        }
        
        $this->db = Mysql::getInstance();
        $this->getStbParams();

        if (empty($this->id)){
            $this->initLocale($this->stb_lang);

            if (!empty($_GET['action']) && $_GET['action'] != 'handshake' && $_GET['action'] != 'get_profile' && $_GET['action'] != 'get_localization' && $_GET['action'] != 'do_auth' && $_GET['action'] != 'get_events'){
                error_log("STB not found in the database, authorization failed. MAC: ".$this->mac.", token: ".$this->access_token);
                echo 'Authorization failed.';
                exit;
            }
        }

        if ($this->db->from('moderators')->where(array('mac' => $this->mac, 'status' => 1))->get()->count() == 1){
            $this->is_moderator = true;
        }

        //if ($this->is_moderator && !empty($_COOKIE['debug'])){
        /*if (!empty($_COOKIE['debug']) || !empty($_REQUEST['debug'])){
            Mysql::$debug = true;
        }*/
    }

    private function checkDebugKey($key){
        return (bool) Mysql::getInstance()->from('administrators')->where(array('debug_key' => $key, 'login' => 'admin'))->get()->first();
    }

    private function getDebugKey(){

        if (!empty($_REQUEST['debug_key'])){
            return $_REQUEST['debug_key'];
        }elseif (!empty($_COOKIE['debug_key'])){
            return $_COOKIE['debug_key'];
        }

        return null;
    }
    
    public function setId($id){
        $this->id = $id;
        $this->params['id'] = $id;
    }

    public function getTimezone(){
        return $this->timezone;
    }

    public function getParam($name){
        return $this->params[$name];
    }

    public function getUserAgent(){
        return $this->user_agent;
    }

    public function setParam($key, $value){

        if (!array_key_exists($key, $this->params)){
            return false;
        }

        if ($this->params[$key] == $value){
            return true;
        }

        $this->params[$key] = $value;

        if (property_exists($this, $key)){
            $this->$key = $value;
        }

        return Mysql::getInstance()->update('users', array($key => $value), array('id' => $this->id));
    }
    
    public function getStbParams(){

        if (!empty($this->mac)){
            $user = $this->db->from('users')
                ->where(array('mac' => $this->mac))
                ->get()
                ->first();
        }elseif (User::isInitialized() && User::getInstance()->getId()){
            $user = $this->db->from('users')
                ->where(array('id' => User::getInstance()->getId()))
                ->get()
                ->first();
        }
        
        if (!empty($user)){
            $this->params = $user;
            $this->id     = $user['id'];
            $this->hd     = $user['hd'];

            $this->locale     = (empty($user['locale']) && Config::exist('default_locale')) ? Config::get('default_locale') : $user['locale'];

            if (Config::getSafe('default_city_id', 0) == 0 && $user['city_id'] == 0){
                $this->city_id = 0;
            }else{
                $this->city_id = (empty($user['city_id']) && Config::exist('default_city_id')) ? Config::get('default_city_id') : intval($user['city_id']);
            }

            $this->country_id = !$this->city_id ? 0 : intval(Mysql::getInstance()->from('cities')->where(array('id' => $this->city_id))->get()->first('country_id'));

            $this->timezone   = (empty($this->timezone) && Config::exist('default_timezone')) ? Config::get('default_timezone') : $this->timezone;

            self::$server_timezone = date_default_timezone_get();

            date_default_timezone_set($this->timezone);

            $date_server = new DateTime();
            $date_server->setTimezone(new DateTimeZone(Stb::$server_timezone));

            $date_stb = new DateTime();
            $date_stb->setTimezone(new DateTimeZone($this->timezone));

            $this->timezone_diff = $date_server->format('Z') - $date_stb->format('Z');

            $date = new DateTime();
            $offset = $date->format('P');
            Mysql::getInstance()->set_timezone($offset);

            $this->additional_services_on = $user['additional_services_on'];

            $this->initLocale($this->stb_lang);
        }
    }

    public function initLocale($lang){
        
        $stb_lang = $lang;

        if (!empty($lang) && strlen($lang) >= 2){
            $preferred_locales = array_filter(Config::get('allowed_locales'),
                function ($e) use ($stb_lang){
                    return (strpos($e, $stb_lang) === 0);
                }
            );

            if (!empty($preferred_locales)){

                $preferred_locales = array_values($preferred_locales);

                $this->locale = $preferred_locales[0];
            }
        }

        $this->stb_lang = substr($this->locale, 0, 2);

        setlocale(LC_MESSAGES, $this->locale);
        putenv('LC_MESSAGES='.$this->locale);

        if (!function_exists('bindtextdomain')){
            throw new ErrorException("php-gettext extension not installed.");
        }

        if (!function_exists('locale_accept_from_http')){
            throw new ErrorException("php-intl extension not installed.");
        }

        bindtextdomain('stb', PROJECT_PATH.'/locale');
        textdomain('stb');
        bind_textdomain_codeset('stb', 'UTF-8');
    }
    
    public function getStorages(){

        $master = new VideoMaster();
        return $master->getStoragesForStb();
    }

    private function isValidAccessToken($access_token, $mac = null){

        if (!$mac){
            $mac = $this->mac;
        }

        $user = Mysql::getInstance()
            ->from('users')
            ->where(array(
                'mac' => $mac
            ))
            ->get()
            ->first();

        if (empty($user)){
            return true;
        }

        return empty($user['access_token']) || $user['access_token'] == $access_token;
    }

    private function parseAuthorizationHeader(){

        if (function_exists('getallheaders')){
            $headers = getallheaders();
        }else{
            $headers = $this->getHttpHeaders();
        }

        if (!$headers){
            return;
        }

        $auth_header = !empty($headers["Authorization"]) ? $headers["Authorization"] : null;

        if ($auth_header && preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)){
            $this->access_token = trim($matches[1]);
        }
    }

    private function getHttpHeaders(){

        $headers = array();

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    public function handshake(){

        $debug_key = $this->getDebugKey();

        if (!empty($debug_key) && $this->checkDebugKey($debug_key)){
            return array('token' => $this->getParam('access_token'));
        }

        $token = strtoupper(md5(mktime(1).uniqid()));

        return array('token' => $token);
    }

    private function passAccessFilter($country, &$model, $mac, $serial_number, $version, $device_id, $signature){

        $filter_file = PROJECT_PATH.'/access_filter.php';

        $rnd = $this->access_token;

        if (is_readable($filter_file)){ // load rules file
            return require_once($filter_file);
        }

        return true;
    }
    
    public function getProfile(){

        if (function_exists('geoip_country_code_by_name')){
            $country = geoip_country_code_by_name($this->ip);
        }else{
            $country = '';
        }
        $model         = isset($_REQUEST['stb_type']) ? $_REQUEST['stb_type'] : '';
        $serial_number = isset($_REQUEST['sn']) ? $_REQUEST['sn'] : '';
        $version       = isset($_REQUEST['ver']) ? $_REQUEST['ver'] : '';
        $device_id     = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';
        $device_id2    = isset($_REQUEST['device_id2']) ? $_REQUEST['device_id2'] : '';
        $signature     = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : '';

        $filter_response = $this->passAccessFilter($country, $model, $this->mac, $serial_number, $version, $device_id2, $signature);

        $this->params['stb_type'] = $model;

        if (is_array($filter_response)){
            $filter_result = $filter_response['result'];
        }else{
            $filter_result = $filter_response;
        }

        if (!$filter_result){

            $this->logDeniedByFilter($country, $model, $this->mac, $version);

            $profile = array(
                'status' => 1,
                'msg'    => 'access denied'
            );

            if (!empty($filter_response['message'])){
                $profile['block_msg'] = _($filter_response['message']);
            }

            return $profile;
        }

        if ((empty($_SERVER['TARGET']) || ($_SERVER['TARGET'] != 'API' && $_SERVER['TARGET'] != 'ADM')) && Config::getSafe('enable_mac_format_validation', true) && !Middleware::isValidMAC($this->mac)){
            $this->logNotValidMAC(isset($_REQUEST['sn']) ? $_REQUEST['sn'] : '', isset($_REQUEST['stb_type']) ? $_REQUEST['stb_type'] : '');
            return array(
                'status' => 1
            );
        }

        $debug_key = $this->getDebugKey();

        if (!empty($debug_key) && $this->checkDebugKey($debug_key)){
            // emulation
        }elseif (Config::getSafe('enable_device_id_validation', true)){

            if ($device_id2){

                $device = Mysql::getInstance()
                    ->from('users')
                    ->where(array(
                         'device_id2' =>  $device_id2
                    ))
                    ->get()
                    ->first();

                if (!empty($device) && strtoupper($device['mac']) != $this->mac){

                    $this->logDeviceConflict($device_id2, $this->mac, $serial_number, $model, 'MAC address mismatch, reason - device_id2');

                    return array(
                        'status' => 1,
                        'msg'    => 'device conflict - MAC address mismatch'
                    );
                }
            }

            if ($this->id){

                $update = array();

                if (!$this->getParam('device_id') && $device_id){
                    $update['device_id'] = $device_id;
                }

                if (!$this->getParam('device_id2') && $device_id2){
                    $update['device_id2'] = $device_id2;
                }

                if (!empty($update)){
                    Mysql::getInstance()->update('users',
                        $update,
                        array('id' => $this->id)
                    );
                }

                if ($this->getParam('device_id') && ($this->getParam('device_id') != $device_id)){

                    $this->logDeviceConflict($device_id, $this->mac, $serial_number, $model, 'device_id mismatch');

                    return array(
                        'status'    => 1,
                        'msg'       => 'device conflict - device_id mismatch',
                        'block_msg' => _('Your STB is damaged.<br/> Call the provider.')
                    );
                }

                if ($this->getParam('device_id2') && ($this->getParam('device_id2') != $device_id2)){

                    $this->logDeviceConflict($device_id2, $this->mac, $serial_number, $model, 'device_id2 mismatch');

                    return array(
                        'status'    => 1,
                        'msg'       => 'device conflict - device_id mismatch',
                        'block_msg' => _('Your STB is damaged.<br/> Call the provider.')
                    );
                }
            }
        }

        if (!$this->id){

            $disable_auth_for_models = Config::exist('disable_auth_for_models') ? preg_split("/\s*,\s*/", trim(Config::get('disable_auth_for_models'))) : array();

            if (Config::exist('auth_url') && !in_array($model, $disable_auth_for_models)){

                return array(
                    'status' => 2 // authentication request
                );

            }else{
                $this->initProfile(null, null, $device_id, $device_id2);
                $this->params['stb_type'] = $model;
            }
        }else{
            Mysql::getInstance()->update('users', array('access_token' => $this->access_token), array('id' => $this->id));

            if (intval($_REQUEST['auth_second_step']) === 0 && Config::exist('auth_url') && strpos(Config::get('auth_url'), 'auth_every_load')){
                return array(
                    'status' => 2
                );
            }
        }

        $this->db->update('users', array(
                'last_start'    => 'NOW()',
                'keep_alive'    => 'NOW()',
                'version'       => @$_REQUEST['ver'],
                'hd'            => @$_REQUEST['hd'],
                'stb_type'      => $model,
                'serial_number' => isset($_REQUEST['sn']) ? $_REQUEST['sn'] : '',
                'num_banks'     => isset($_REQUEST['num_banks']) ? $_REQUEST['num_banks'] : 0,
                'image_version' => isset($_REQUEST['image_version']) ? $_REQUEST['image_version'] : '',
                'locale'        => $this->locale,
                'country'       => $country
            ),
            array('id' => $this->id)
        );

        $info = $this->getInfoFromOss();

        if (self::$just_created == true && Config::getSafe('enable_welcome_message', false)){
            $event = new SysEvent();
            $event->setUserListById($this->id);
            $event->sendMsg(sprintf(_('Welcome %s<br>We are glad to see you on the Stalker portal!'), $this->getParam('fname')));
        }

        $master = new VideoMaster();

        $profile = $this->params;

        if ($info && array_key_exists('error_msg', $info)){
            $profile['block_msg'] = $info['error_msg'];
        }

        $profile['storages'] = $master->getStoragesForStb();

        $itv = Itv::getInstance();
        $profile['last_itv_id'] = $itv->getLastId();

        $profile['updated'] = $this->getUpdatedPlaces();

        $profile['rtsp_type']  = Config::get('rtsp_type');
        $profile['rtsp_flags'] = Config::get('rtsp_flags');

        $profile['locale'] = $this->locale;
        $profile['stb_lang'] = $this->stb_lang;

        $profile['display_menu_after_loading'] = Config::get('display_menu_after_loading');
        $profile['record_max_length']          = intval(Config::get('record_max_length'));

        $profile['web_proxy_host']         = Config::exist('stb_http_proxy_host') ? Config::get('stb_http_proxy_host') : '';
        $profile['web_proxy_port']         = Config::exist('stb_http_proxy_port') ? Config::get('stb_http_proxy_port') : '';
        $profile['web_proxy_user']         = Config::exist('stb_http_proxy_user') ? Config::get('stb_http_proxy_user') : '';
        $profile['web_proxy_pass']         = Config::exist('stb_http_proxy_pass') ? Config::get('stb_http_proxy_pass') : '';
        $profile['web_proxy_exclude_list'] = Config::exist('stb_http_proxy_exclude_list') ? Config::get('stb_http_proxy_exclude_list') : '';
        $profile['update_url']             = self::getImageUpdateUrl(empty($_REQUEST['stb_type']) ? 'mag250' : $_REQUEST['stb_type']);
        $profile['tv_archive_days']        = Config::exist('tv_archive_parts_number') ? Config::get('tv_archive_parts_number') / 24 : 0;
        $profile['tv_archive_hours']       = Config::getSafe('tv_archive_parts_number', 0);

        if (!in_array($this->mac, Config::getSafe('playback_limit_whitelist', array()))){
            $profile['playback_limit'] = (int) Config::get('enable_playback_limit', 0);
        }else{
            $profile['playback_limit'] = 0;
        }

        $profile['demo_video_url']         = Config::getSafe('demo_video_url', '');
        $profile['tv_quality_filter']      = Config::get('enable_tv_quality_filter');
        $profile['use_embedded_settings']  = Config::getSafe('use_embedded_settings', false);

        $profile['test_download_url']      = Config::getSafe('test_download_url', '');

        $profile['is_moderator']           = $this->is_moderator;

        $profile['watchdog_timeout']       = Config::getSafe('watchdog_timeout', 30000);

        $max_id = Mysql::getInstance()->select('max(id) as max_id')->from('users')->get()->first('max_id');

        $profile['timeslot_ratio']         = $this->id / $max_id;
        $profile['timeslot']               = $profile['timeslot_ratio'] * $profile['watchdog_timeout'];

        $profile['kinopoisk_rating']       = Config::getSafe('kinopoisk_rating', true);

        $profile['enable_tariff_plans']    = Config::getSafe('enable_tariff_plans', false);

        $profile['enable_buffering_indication'] = Config::getSafe('enable_buffering_indication', false);

        $profile['default_timezone']       = Config::getSafe('default_timezone', '');
        $profile['default_locale']         = Config::getSafe('default_locale', '');

        $profile['allowed_stb_types']      = array_map(function($item){
            return strtolower(trim($item));
        },explode(',', Config::getSafe('allowed_stb_types', 'MAG200,MAG245,MAG250,AuraHD')));

        $profile['allowed_stb_types_for_local_recording'] = array_map(function($item){
            return strtolower(trim($item));
        },explode(',', Config::getSafe('allowed_stb_types_for_local_recording', 'MAG245,MAG250,MAG255,AuraHD')));

        $auto_update_setting = ImageAutoUpdate::getSettingByStbType($this->params['stb_type']);

        if ($auto_update_setting){
            $profile['autoupdate'] = $auto_update_setting;
        }

        $profile['strict_stb_type_check'] = Config::getSafe('strict_stb_type_check', false);

        $profile['cas_type']   = Config::getSafe('cas_type', 0);
        $profile['cas_params'] = Config::getSafe('cas_params', array());
        $profile['cas_additional_params'] = Config::getSafe('cas_additional_params', array());
        $profile['cas_hw_descrambling']   = Config::getSafe('cas_hw_descrambling', 0);
        $profile['cas_ini_file']          = Config::getSafe('cas_ini_file', "");

        $profile['logarithm_volume_control'] = Config::getSafe('logarithm_volume_control', false);

        $profile['allow_subscription_from_stb'] = Config::getSafe('allow_subscription_from_stb', true);

        $profile['deny_720p_gmode_on_mag200'] = Config::getSafe('deny_720p_gmode_on_mag200', false);
        $profile['enable_arrow_keys_setpos']  = Config::getSafe('enable_arrow_keys_setpos', false);

        $profile['show_purchased_filter']  = Config::getSafe('show_purchased_filter', false);

        $profile['timezone_diff']  = $this->timezone_diff;

        $profile['enable_connection_problem_indication']  = Config::getSafe('enable_connection_problem_indication', true);

        $profile['invert_channel_switch_direction']  = Config::getSafe('invert_channel_switch_direction', false);

        $profile['play_in_preview_only_by_ok']  = Config::getSafe('play_in_preview_only_by_ok', false);

        $profile['enable_stream_error_logging'] = Config::getSafe('enable_stream_error_logging', false);

        $profile['always_enabled_subtitles'] = Config::getSafe('always_enabled_subtitles', false);

        $profile['enable_service_button'] = Config::getSafe('enable_service_button', false);

        $profile['show_tv_channel_logo'] = Config::getSafe('show_tv_channel_logo', true);

        $profile['enable_hdmi_events_handler'] = Config::getSafe('enable_hdmi_events_handler', true);

        $profile['tv_archive_continued'] = Config::getSafe('tv_archive_continued', false);

        $profile['plasma_saving_timeout'] = Config::getSafe('plasma_saving_timeout', false);

        $profile['show_tv_only_hd_filter_option'] = Config::getSafe('show_tv_only_hd_filter_option', false);

        $profile['tv_playback_retry_limit'] = Config::getSafe('tv_playback_retry_limit', 0);

        $profile['fading_tv_retry_timeout'] = Config::getSafe('fading_tv_retry_timeout', true);

        $profile['epg_update_time_range'] = floatval(Config::getSafe('epg_update_delay_per_user', 0.2)) * $max_id;

        if (Config::getSafe('enable_tariff_plans', false)){
            $profile['additional_services_on'] = '1';
        }

        unset($profile['device_id']);
        unset($profile['device_id2']);
        unset($profile['access_token']);
        unset($profile['serial_number']);

        return $profile;
    }

    public function getSettingsProfile(){

        return array(
            "parent_password"      => $this->params['parent_password'],
            "update_url"           => self::getImageUpdateUrl($this->params['stb_type']),
            "test_download_url"    => Config::getSafe('test_download_url', ''),
            "playback_buffer_size" => $this->params['playback_buffer_size'] / 1000,
            "screensaver_delay"    => $this->params['screensaver_delay'],
            "plasma_saving"        => $this->params['plasma_saving'],
            "spdif_mode"           => $this->params['audio_out'] == 0 ? "1" : $this->params['audio_out'],
            "modules"              => $this->getSettingsMenuModules(),
            'ts_enabled'           => $this->params['ts_enabled'],
            'ts_enable_icon'       => $this->params['ts_enable_icon'],
            'ts_path'              => $this->params['ts_path'],
            'ts_max_length'        => $this->params['ts_max_length'],
            'ts_buffer_use'        => $this->params['ts_buffer_use'],
            'ts_action_on_exit'    => $this->params['ts_action_on_exit'],
            'ts_delay'             => $this->params['ts_delay'],
        );
    }

    private static function getImageUpdateUrl($stb_model){

        if (strpos($stb_model, 'AuraHD') !== false){
            $stb_type = 'aurahd';
        }else{
            $stb_type = substr($stb_model, 3);
        }

        return Config::getSafe('update_url', '') != '' ? Config::get('update_url').$stb_type.'/imageupdate' : '';
    }

    public function getSettingsMenuModules($mask = "module"){

        if (!Config::exist($mask)){
            return null;
        }

        $scope = $this;

        $modules = array_map(function ($item) use ($scope){
            return array('name' => $item, "sub" => $scope->getSettingsMenuModules($item));
        }, Config::get($mask));

        return $modules;
    }

    public static function create($data){

        if (!empty($data['mac'])){
            $user = Stb::getUidByMacs($data['mac']);

            if (!empty($user)){
                throw new ErrorException('Stb already exists');
            }
        }

        $data['created'] = 'NOW()';

        if (Config::exist('default_stb_status')){
            $data['status'] = intval(!Config::get('default_stb_status'));
        }

        try{
            OssWrapper::getWrapper()->registerSTB(
                Stb::getInstance()->mac,
                isset($_REQUEST['sn']) ? $_REQUEST['sn'] : '',
                isset($_REQUEST['stb_type']) ? $_REQUEST['stb_type'] : ''
            );
        }catch(OssException $e){
            self::logOssError($e);
        }

        $data['serial_number'] = isset($_REQUEST['sn']) ? $_REQUEST['sn'] : '';

        $user_id = Mysql::getInstance()->insert('users', $data)->insert_id();

        if ($user_id && !empty($data['password'])){
            $password = md5(md5($data['password']).$user_id);
            Mysql::getInstance()->update('users', array('password' => $password), array('id' => $user_id));
        }

        self::$just_created = true;

        return $user_id;
    }
    
    private function initProfile($login = null, $password = null, $device_id = null, $device_id2 = null){

        if (!empty($login)){
            $user = Mysql::getInstance()->from('users')->where(array('login' => $login))->get()->first();
        }

        if (empty($login)){

            $data = array(
                'mac'          => $this->mac,
                'access_token' => $this->access_token,
                'name'         => substr($this->mac, 12, 16),
                'device_id'    => $device_id,
                'device_id2'   => $device_id2
            );
            $uid = self::create($data);
        }else if (!empty($user)){

            Mysql::getInstance()->update('users',
                array(
                    'mac'        => $this->mac,
                    'name'       => substr($this->mac, 12, 16),
                    'device_id'  => $device_id,
                    'device_id2' => $device_id2
                ),
                array(
                    'login' => $login
                )
            );

            $uid = intval(Mysql::getInstance()->from('users')->where(array('mac' => $this->mac))->get()->first('id'));
        }else{
            $data = array(
                'mac'          => $this->mac,
                'access_token' => $this->access_token,
                'name'         => substr($this->mac, 12, 16),
                'device_id'    => $device_id,
                'device_id2'   => $device_id2,
                'login'        => $login,
            );
            $uid = self::create($data);
        }
                
        $this->getStbParams();        
        
        $this->setId($uid);

        if (empty($login)){
            $this->db->insert('updated_places', array('uid' => $this->id));
        }
    }
    
    public function getLocalization(){
        return System::get_all_words();
    }
    
    public function isModerator(){
        return $this->is_moderator;
    }
    
    public function getPreloadImages(){

        $gmode = $_REQUEST['gmode'];

        $prefix = $gmode ? '_'.$gmode : '';
        
        $dir = PROJECT_PATH.'/../c/i'.$prefix.'/';
        $files = array();

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_file($dir.$file)){
                        $files[] = 'i'.$prefix.'/'.$file;
                    }
                }
                closedir($dh);
            }
        }
        
        return $files;
    }
    
    public function setParentPassword(){
        
        if (isset($_REQUEST['pass'])){
            $this->db->update('users', array('parent_password' => $_REQUEST['pass']), array('mac' => $this->mac));
            $this->params['parent_password'] = $_REQUEST['pass'];
        }
        
        return true;
    }
    
    public function setVolume(){
        
        $volume = intval($_REQUEST['vol']);
        
        if($volume < 0 || $volume > 100){
            $volume = 100;
        }
        
        $this->db->update('users', array('volume' => $volume), array('mac' => $this->mac));
        $this->params['volume'] = $volume;
        
        return true;
    }
    
    public function setAspect(){
        $aspect = intval($_REQUEST['aspect']);
        
        $this->db->update('users', array('aspect' => $aspect), array('mac' => $this->mac));
        $this->params['aspect'] = $aspect;
        
        return true;
    }
    
    public function setFavItvStatus(){
        
        $fav_itv_on = intval($_REQUEST['fav_itv_on']);
        
        $this->db->update('users', array('fav_itv_on' => $fav_itv_on), array('mac' => $this->mac));
        $this->params['fav_itv_on'] = $fav_itv_on;
        
        return true;
    }
    
    public function getUpdatedPlaces(){
        //return $this->db->getFirstData('updated_places', array('uid' => $this->id));
        return $this->db->from('updated_places')->where(array('uid' => $this->id))->get()->first();
    }
    
    public function setUpdatedPlaceConfirm(){
        
        $place = $_REQUEST['place'];
        
        $this->db->update('updated_places', array($place => 0), array('uid' => $this->id));
        
        return true;
    }
    
    public function setEventConfirm(){
        
        $event_id = intval($_REQUEST['event_id']);

        Event::setConfirmed($event_id);
        
        return true;
    }

    public function setStreamError(){

        if (!Config::getSafe('enable_stream_error_logging', false)){
            return false;
        }

        $this->db->insert('stream_error',
                           array(
                                'ch_id'      => intval($_REQUEST['ch_id']),
                                'event'      => intval($_REQUEST['event']),
                                'mac'        => $this->mac,
                                'error_time' => 'NOW()'
                           ));
        return true;
    }
    
    public function log(){
        
        $action = strval($_REQUEST['real_action']);
        $param  = urldecode($_REQUEST['param']);
        $type   = $_REQUEST['tmp_type'];

        if ($type == 1 && !empty($_REQUEST['ch_id'])){
            $param = $this->db->from('itv')->where(array('id' => (int) $_REQUEST['ch_id']))->get()->first('cmd');
        }
        
        $this->db->insert('user_log',
                            array(
                                'mac'    => $this->mac,
                                'action' => $action,
                                'param'  => $param,
                                'time'   => 'NOW()',
                                'type'   => $type
                            ));
        
        $update_data = array();
                            
        if ($action == 'play'){
            $update_data['now_playing_start'] = 'NOW()';

            switch ($type){
                case 1: // TV

                    if (!empty($_REQUEST['ch_id'])){
                        $ch_name = $this->db->from('itv')->where(array('id' => (int) $_REQUEST['ch_id']))->get()->first('name');
                    }else{
                        $ch_name = $this->db
                            ->from('ch_links')
                            ->join('itv', 'itv.id', 'ch_links.ch_id', 'INNER')
                            ->where(array(
                                'ch_links.url'    => $param,
                                'ch_links.status' => 1
                            ))
                            ->get()
                            ->first('name');
                    }

                    if (empty($ch_name)){
                        $ch_name = $param;
                    }

                    $update_data['now_playing_content'] = $ch_name;

                    if (!empty($_REQUEST['link_id'])){
                        $update_data['now_playing_link_id'] = $_REQUEST['link_id'];
                    }else{
                        $update_data['now_playing_link_id'] = 0;
                    }

                    if (!empty($_REQUEST['streamer_id'])){
                        $update_data['now_playing_streamer_id'] = $_REQUEST['streamer_id'];
                    }else{
                        $update_data['now_playing_streamer_id'] = 0;
                    }

                    break;
                case 2: // Video Club
                    
                    //preg_match("/auto \/media\/([\S\s]+)\/(\d+)\.[a-z]*$/", $param, $tmp_arr);

                    if (strpos($param, '://') !== false){

                        $video = $this->db->from('video')->where(array('rtsp_url' => $param, 'protocol' => 'custom'))->get()->first();

                        if (empty($video)){
                            preg_match("/\/([^\/]+)\/[^\/]+\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
                        }

                    }else{
                        preg_match("/auto \/media\/([\S\s]+)\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
                    }

                    if (empty($video) && !empty($tmp_arr)){

                        $storage  = $tmp_arr[1];
                        $media_id = intval($tmp_arr[2]);

                        $video = $this->db->from('video')->where(array('id' => $media_id))->get()->first();

                        $update_data['storage_name'] = $storage;
                    }
                    
                    if (!empty($video)){
                        
                        $update_data['now_playing_content'] = $video['name'];
                        $update_data['hd_content']          = $video['hd'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }

                    break;
                case 3: // Karaoke
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $karaoke_id = intval($tmp_arr[1]);
                    
                    $karaoke = $this->db->from('karaoke')->where(array('id' => $karaoke_id))->get()->first();
                    
                    if (!empty($karaoke)){
                        $update_data['now_playing_content'] = $karaoke['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 4: // Audio Club
                    
                    preg_match("/(\d+).mp3$/", $param, $tmp_arr);
                    $audio_id = intval($tmp_arr[1]);
                    
                    $audio = $this->db->from('audio')->where(array('id' => $audio_id))->get()->first();
                    
                    if (!empty($audio)){
                        $update_data['now_playing_content'] = $audio['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 5: // Radio
                
                    $radio = $this->db->from('radio')->where(array('cmd' => $param, 'status' => 1))->get()->first();
                    
                    if (!empty($radio)){
                        $update_data['now_playing_content'] = $radio['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 6: // My Records
                
                    /*$my_record_name = '';
                    
                    preg_match("/\/(\d+).mpg/", $param, $tmp_arr);
                    $my_record_id = $tmp_arr[1];
                    
                    $sql = "select t_start,itv.name from users_rec, itv where users_rec.ch_id=itv.id and users_rec.id=$my_record_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $my_record_name = $rs->getValueByName(0, 't_start').' '.$rs->getValueByName(0, 'name');
                    }else{
                        $my_record_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$my_record_name'";
                    break;*/
                case 7: // Shared Records
                    /*$shared_record_name = '';

                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $shared_record_id = $tmp_arr[1];

                    $sql = "select * from video_records where id=$shared_record_id";
                    $rs = $db->executeQuery($sql);

                    if ($rs->getRowCount() == 1){
                        $shared_record_name = $rs->getValueByName(0, 'descr');
                    }else{
                        $shared_record_name = $param;
                    }

                    $_sql .= ", now_playing_content='$shared_record_name'";*/
                    break;
                case 8: // Video clips
                    /*$video_name = '';

                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $media_id = $tmp_arr[1];

                    $sql = "select * from video_clips where id=$media_id";
                    $rs = $db->executeQuery($sql);

                    if ($rs->getRowCount() == 1){
                        $video_name = $rs->getValueByName(0, 'name');
                    }else{
                        $video_name = $param;
                    }

                    $_sql .= ", now_playing_content='$video_name'";
                    break;*/
                case 11:
                    if (preg_match("/http:\/\/([^:\/]*)/", $param, $tmp_arr)){
                         $storage_ip = $tmp_arr[1];
                         $update_data['storage_name'] = Mysql::getInstance()->from('storages')->where(array('storage_ip' => $storage_ip, 'for_records' => 1))->get()->first('storage_name');
                    }

                    $update_data['now_playing_content'] = $param;

                    if (preg_match("/ch_id=(\d+)/", $param, $match)){
                        $ch_id = $match[1];
                        $channel = Itv::getById($ch_id);
                        if (!empty($channel)){
                            $update_data['now_playing_content'] = $channel['name'];
                        }
                    }
                        
                    break;
                case 14:
                    if (preg_match("/http:\/\/([^:\/]*)/", $param, $tmp_arr)){
                        $storage_ip = $tmp_arr[1];
                        $update_data['storage_name'] = Mysql::getInstance()->from('storages')->where(array('storage_ip' => $storage_ip, 'for_records' => 1))->get()->first('storage_name');
                    }

                    $update_data['now_playing_content'] = $param;

                    if (preg_match("/\/archive\/(\d+)\//", $param, $match)){
                        $ch_id = $match[1];
                        $channel = Itv::getById($ch_id);
                        if (!empty($channel)){
                            $update_data['now_playing_content'] = $channel['name'];
                        }
                    }

                    break;
                default:
                    $update_data['now_playing_content'] = 'unknown media '.$param;
            }
        }
        
        if ($action == 'infoportal'){
            $update_data['now_playing_start'] = 'NOW()';

            $info_arr = array(
                20 => 'city_info',
                21 => 'anec_page',
                22 => 'weather_page',
                23 => 'game_page',
                24 => 'horoscope_page',
                25 => 'course_page'
            );
            
            if (@$info_arr[$type]){
                $info_name = $info_arr[$type];
            }else{
                $info_name = 'unknown';
            }
            
            $update_data['now_playing_content'] = $info_name;
        }
        
        if ($action == 'stop' || $action == 'close_infoportal'){
            
            $update_data['now_playing_content'] = '';
            $update_data['storage_name'] = '';
            $update_data['hd_content'] = '';
            $update_data['now_playing_link_id'] = '';
            $update_data['now_playing_streamer_id'] = '';

            $type = 0;
        }
        
        if ($action == 'pause'){
            
            $this->db->insert('vclub_paused',
                              array(
                                  'uid' => $this->id,
                                  'mac' => $this->mac,
                                  'pause_time' => 'NOW()'
                              ));
        }
        
        if ($action == 'continue' || $action == 'stop' || $action == 'set_pos()' || $action == 'play'){
            
            $this->db->delete('vclub_paused',
                              array(
                                  'mac' => $this->mac
                              ));
        }
        
        if ($action == 'readed_anec'){
            
            return $this->db->insert('readed_anec',
                              array(
                                  'mac'    => $this->mac,
                                  'readed' => 'NOW()'
                              ));
        }
        
        if ($action == 'loading_fail'){
            
            return $this->db->insert('loading_fail',
                                     array(
                                         'mac'   => $this->mac,
                                         'added' => 'NOW()'
                                     ));
        }
        
        $update_data['last_active'] = 'NOW()';
        $update_data['keep_alive']  = 'NOW()';
        $update_data['now_playing_type']  = $type;
        
        $this->db->update('users', $update_data, array('mac' => $this->mac));
        
        return 1;
    }
    
    public function getModules(){

        return array(
            'all_modules'        => Config::get('all_modules'),
            'switchable_modules' => Config::get('disabled_modules'),
            'disabled_modules'   => $this->getDisabledModules(),
            'restricted_modules' => $this->getRestrictedModules()
            );
    }

    private function getDisabledModules(){

        return self::getDisabledModulesByUid($this->id);
    }

    public static function getDisabledModulesByUid($uid){

        if (Config::get('enable_tariff_plans')){
            $user = User::getInstance(Stb::getInstance()->id);

            $user_enabled_modules = $user->getServicesByType('module');

            if ($user_enabled_modules === null){
                $user_enabled_modules = array();
            }

            $disabled_modules = array_values(array_diff(Config::get('disabled_modules'), $user_enabled_modules));
        }else{
            $disabled_modules = Mysql::getInstance()->from('user_modules')->where(array('uid' => intval($uid)))->get()->first('disabled');

            if (empty($disabled_modules)){
                return array();
            }

            $disabled_modules = unserialize($disabled_modules);

            if ($disabled_modules === false){
                return array();
            }
        }

        $idx = array_search('ivi', $disabled_modules);

        if ($idx !== false){
            array_splice($disabled_modules, $idx, 1);
        }

        return $disabled_modules;
    }

    public static function setDisabledModulesByUid($uid, $disabled_modules = array()){

        self::initModulesRecord($uid);

        /*$event = new SysEvent();
        $event->setUserListById(array($uid));
        $event->sendUpdateModules();*/

        return Mysql::getInstance()->update('user_modules', array('disabled' => serialize($disabled_modules)), array('uid' => intval($uid)));
    }

    private static function initModulesRecord($uid){
        
        $record = Mysql::getInstance()->from('user_modules')->where(array('uid' => intval($uid)))->get()->first();

        if (empty($record)){
            return Mysql::getInstance()->insert('user_modules', array('uid' => intval($uid)))->insert_id();
        }

        return false;
    }

    private function getRestrictedModules(){

        return self::getRestrictedModulesByUid($this->id);
    }

    public static function getRestrictedModulesByUid($uid){
        
        $restricted_modules = Mysql::getInstance()->from('user_modules')->where(array('uid' => intval($uid)))->get()->first('restricted');

        if (empty($restricted_modules)){
            return array();
        }

        $restricted_modules = unserialize($restricted_modules);

        if ($restricted_modules === false){
            return array();
        }

        return $restricted_modules;
    }

    public static function setRestrictedModulesByUid($uid, $restricted_modules = array()){

        self::initModulesRecord($uid);

        $event = new SysEvent();
        $event->setUserListById(array($uid));
        $event->sendUpdateModules();

        return Mysql::getInstance()->update('user_modules', array('restricted' => serialize($restricted_modules)), array('uid' => intval($uid)));
    }
    
    public static function setAllowedLanguages($languages){
        
        self::$allowed_languages = $languages;
    }

    public function getLocales(){

        $result = array();

        foreach (Config::get('allowed_locales') as $label => $locale){
            $selected = ($this->locale == $locale)? 1 : 0;
            $result[] = array('label' => $label, 'value' => $locale, 'selected' => $selected);
        }

        return $result;
    }

    public function setLocale(){
        $locale  = $_REQUEST['locale'];
        $city_id = intval($_REQUEST['city']);

        if (in_array($locale, Config::get('allowed_locales'))){

            return $this->db->update('users', array('locale' => $locale, 'city_id' => $city_id), array('id' => $this->id));
        }

        return false;
    }

    public function setPlaybackBuffer(){
        $playback_buffer_bytes = intval($_REQUEST['playback_buffer_bytes']);
        $playback_buffer_size  = intval($_REQUEST['playback_buffer_size']) * 1000;

        return Mysql::getInstance()->update('users',
            array(
                'playback_buffer_bytes' => $playback_buffer_bytes,
                'playback_buffer_size'  => $playback_buffer_size
            ),
            array('id' => $this->id));
    }

    public function setSpdifMode(){

        $audio_out = intval($_REQUEST['spdif_mode']);

        return Mysql::getInstance()->update('users',
            array(
                'audio_out' => $audio_out
            ),
            array('id' => $this->id));
    }

    public function setPlaybackSettings(){
        $playback_buffer_bytes = intval($_REQUEST['playback_buffer_bytes']);
        $playback_buffer_size  = intval($_REQUEST['playback_buffer_size']) * 1000;
        $audio_out             = intval($_REQUEST['audio_out']);
        $playback_limit        = intval($_REQUEST['playback_limit']);

        return Mysql::getInstance()->update('users',
            array(
                'playback_buffer_bytes' => $playback_buffer_bytes,
                'playback_buffer_size'  => $playback_buffer_size,
                'audio_out'             => $audio_out,
                'playback_limit'        => $playback_limit
            ),
            array('id' => $this->id));
    }

    public function setScreensaverDelay(){
        return $this->setCommonSettings();
    }

    public function setPlasmaSaving(){

        return Mysql::getInstance()->update('users',
            array(
                'plasma_saving' => (int) $_REQUEST['plasma_saving']
            ),
            array('id' => $this->id)
        );
    }

    public function setTimeshiftSettings(){

        $data = $_REQUEST['data'];

        $data = json_decode($data, true);

        if ($data === false){
            return false;
        }

        return Mysql::getInstance()->update('users',
            array(
                 'ts_enabled'        => $data['ts_enabled'],
                 'ts_enable_icon'    => $data['ts_enable_icon'],
                 'ts_path'           => $data['ts_path'],
                 'ts_max_length'     => $data['ts_max_length'],
                 'ts_buffer_use'     => $data['ts_buffer_use'],
                 'ts_action_on_exit' => $data['ts_action_on_exit'],
                 'ts_delay'          => $data['ts_delay'],
            ),
            array('id' => $this->id)
        );

    }

    public function setCommonSettings(){

        $screensaver_delay = intval($_REQUEST['screensaver_delay']);

        return Mysql::getInstance()->update('users',
            array(
                'screensaver_delay' => $screensaver_delay
            ),
            array('id' => $this->id));
    }

    public function getCountries(){

        $result = array();

        $countries = Mysql::getInstance()->from('countries')->orderby('name_en')->get()->all();

        foreach ($countries as $country){
            $selected = ($this->country_id == $country['id'])? 1 : 0;
            $result[] = array('label' => $country['name_en'], 'value' => $country['id'], 'selected' => $selected);
        }

        return $result;
    }

    public function searchCountries(){

        $search = $_REQUEST['search'];

        if (empty($search)){
            return array();
        }

        $countries = Mysql::getInstance()
            ->select('id, name_en')
            ->from('countries')
            ->like(array(
                'name'    => $search.'%',
                'name_en' => $search.'%'
            ), 'OR ')
            ->limit(3)
            ->get()
            ->all();

        $result = array();

        foreach ($countries as $country){
            $result[] = array('label' => $country['name_en'], 'value' => $country['id']);
        }

        return $result;
    }

    public function getCities(){

        $country_id = intval($_REQUEST['country_id']);

        $result = array();

        /// TRANSLATORS: don't translate this.
        $cities = Mysql::getInstance()->from('cities')->where(array('country_id' => $country_id))->orderby('name_en')->get()->all();

        foreach ($cities as $city){
            $selected = ($this->city_id == $city['id'])? 1 : 0;
            //$city_name = empty($city[_('city_name_field')]) ? $city['name_en'] : $city[_('city_name_field')];
            $city_name = $city['name_en'];
            $result[] = array('label' => $city_name , 'value' => $city['id'], 'timezone' => $city['timezone'], 'selected' => $selected);
        }

        return $result;
    }

    public function searchCities(){

        $search = $_REQUEST['search'];
        $country_id = intval($_REQUEST['country_id']);

        if (empty($search)){
            return array();
        }

        $cities = Mysql::getInstance()
            ->select('id, name_en')
            ->from('cities')
            ->where(array('country_id' => $country_id))
            ->like(array(
                'name'    => iconv('windows-1251', 'utf-8', $search).'%',
                'name_en' => $search.'%'
            ), 'OR ')
            ->limit(3)
            ->get()
            ->all();

        $result = array();
        
        foreach ($cities as $city){
            $result[] = array('label' => $city['name_en'] , 'value' => $city['id']);
        }

        return $result;
    }

    public function getTimezones(){

        $result = array();

        $timezones = Mysql::getInstance()->from('cities')->groupby('timezone')->orderby('timezone')->get()->all('timezone');

        foreach ($timezones as $timezone){

            if (empty($timezone)) continue;
            
            $selected = ($this->timezone == $timezone)? 1 : 0;
            $result[] = array('label' => $timezone, 'value' => $timezone, 'selected' => $selected);
        }

        return $result;
    }

    public function getByUids($uids = array()){

        $result = Mysql::getInstance()->select('*, keep_alive>=FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-'.Config::get('watchdog_timeout').') online')->from('users');

        //if (!empty($uids)){
        $result = $result->in('id', $uids);
        //}

        $result = $result->get()->all();

        return $result;
    }

    public function updateByUids($uids = array(), $data){

        if (empty($data)){
            return false;
        }

        $result = Mysql::getInstance();

        //if (!empty($uids)){
        $result = $result->in('id', $uids);
        //}

        if (array_key_exists("reboot", $data)){
            unset($data['reboot']);
            $event = new SysEvent();
            $event->setUserListById($uids);
            $event->sendReboot();
        }

        if (!empty($data)){
            $result = $result->update('users', $data);
        }

        if (array_key_exists("status", $data)){
            $event = new SysEvent();
            $event->setUserListById($uids);
            if ($data["status"] == 0){
                $event->sendCutOn();
            }else{
                $event->sendCutOff();
            }
        }

        if (!$result){
            return false;
        }

        return $this->getByUids($uids);
    }

    private function getInfoFromOss(){

        $user = User::getInstance($this->id);

        $user->refreshProfile();

        $info = $user->getInfoFromOSS();

        if (!$info){
            return false;
        }

        $update_data = array();

        if (array_key_exists('ls', $info)){
            $this->params['ls'] = $update_data['ls'] = $info['ls'];
        }

        if (array_key_exists('status', $info)){
            $this->params['status'] = $update_data['status'] = intval(!$info['status']);
        }

        if (array_key_exists('additional_services_on', $info)){
            $this->params['additional_services_on'] = $update_data['additional_services_on'] = intval($info['additional_services_on']);
        }

        if (array_key_exists('fname', $info)){
            $this->params['fname'] = $update_data['fname'] = $info['fname'];
        }

        if (array_key_exists('phone', $info)){
            $this->params['phone'] = $update_data['phone'] = $info['phone'];
        }

        if (array_key_exists('tariff', $info)){
            $tariff = Mysql::getInstance()->from('tariff_plan')->where(array('external_id' => $info['tariff']))->get()->first();

            if ($tariff){
                $tariff_id = $tariff['id'];
            }else{
                $tariff_id = 0;
            }

            $this->params['tariff_plan_id'] = $update_data['tariff_plan_id'] = $tariff_id;
        }

        if (empty($update_data)){
            return false;
        }

        Mysql::getInstance()->update('users', $update_data, array('id' => $this->id));

        return $info;
    }

    public static function getUidByLs($ls){

        $result = Mysql::getInstance()->from('users');

        if ($ls !== null){

            if (!is_array($ls)){
                $ls = array($ls);
            }

            $result = $result->in('ls', $ls);
        }

        return $result->get()->all('id');
    }

    public static function getUidByAccountNumber($account_number){

        $result = Mysql::getInstance()->from('users');

        if ($account_number !== null){

            if (!is_array($account_number)){
                $account_number = array($account_number);
            }

            $result = $result->in('ls', $account_number);
        }

        return $result->get()->all('id');
    }

    public static function getUidByLogin($login){

        $result = Mysql::getInstance()->from('users');

        if ($login !== null){

            if (!is_array($login)){
                $login = array($login);
            }

            $result = $result->in('login', $login);
        }

        return $result->get()->all('id');
    }

    public static function getUidByMacs($mac){

        $result = Mysql::getInstance()->from('users');

        if ($mac !== null){

            if (!is_array($mac)){
                $mac = array($mac);
            }

            $mac = Middleware::normalizeMacArray($mac);

            //var_dump($mac);
            //var_dump($mac);

            $result = $result->in('mac', $mac);
        }

        return $result->get()->all('id');

        //return Mysql::getInstance()->from('users')->in('mac', $mac)->get()->all('id');
    }

    public static function setAdditionServicesById($uid, $value){

        return Mysql::getInstance()->update('users', array('additional_services_on' => intval($value)), array('id' => intval($uid)));
    }

    public static function getById($id){

        return Mysql::getInstance()->from('users')->where(array('id' => $id))->get()->first();
    }

    public function deleteById($ids){

        if (empty($ids)){
            return false;
        }

        if (!is_array($ids)){
            $ids = array($ids);
        }

        foreach ($ids as $id){
            Mysql::getInstance()->delete('users', array('id' => $id));
        }

        return true;
    }

    public function doAuth(){

        $login      = $_REQUEST['login'];
        $password   = $_REQUEST['password'];
        $device_id  = $_REQUEST['device_id'];
        $device_id2 = $_REQUEST['device_id2'];

        $data = file_get_contents(Config::get('auth_url').'?login='.$login.'&password='.$password.'&mac='.$this->mac);

        if (!$data){
            return false;
        }

        $data = json_decode($data, true);

        if (empty($data)){
            return false;
        }

        var_dump($data);

        if ($data['status'] != 'OK' || empty($data['results'])){
            return false;
        }

        $auth_result = $data['results'];

        if ($auth_result == "true"){
            
            $this->initProfile($login, $password, $device_id, $device_id2);

            return true;
        }else{
            return false;
        }
    }

    public function getAll($limit = null, $offset = null){
        return self::getRawAll($limit, $offset)->get()->all();
    }

    public static function getRawAll($limit = null, $offset = null){

        $result = Mysql::getInstance()->from('users');

        if ($limit !== null){
            $result = $result->limit($limit, $offset);
        }

        return $result;
    }

    public function getByLogin($login){
        return Mysql::getInstance()->from('users')->where(array('login' => $login))->get()->first();
    }

    public function updateByLogin($login, $data){
        return Mysql::getInstance()->update('users', $data, array('login' => $login))->result();
    }

    public static function updateById($id, $data){
        return Mysql::getInstance()->update('users', $data, array('id' => $id))->result();
    }

    public static function getByMac($mac){
        $mac = Middleware::normalizeMac($mac);

        if (empty($mac)){
            return null;
        }

        return Mysql::getInstance()->from('users')->where(array('mac' => $mac))->get()->first();
    }

    private function logDeviceConflict($device_id, $mac, $serial_number, $model, $msg){
        $logger = new Logger();
        $logger->setPrefix("device_id_");
        $date = new DateTime('now', new DateTimeZone(Config::get('default_timezone')));

        $logger->error(sprintf("%s - [%s] mac:%s, serial_number:%s, model:%s, device_id:%s, reason:%s\n",
            empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
            $date->format('r'),
            $mac,
            $serial_number,
            $model,
            $device_id,
            $msg
        ));
    }

    private function logDeniedByFilter($country, $model, $mac, $version){
        $logger = new Logger();
        $logger->setPrefix("access_filter_");
        $date = new DateTime('now', new DateTimeZone(Config::get('default_timezone')));

        $logger->error(sprintf("%s - [%s] country:%s, model:%s, mac:%s, version:[%s], referrer:%s\n",
            empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
            $date->format('r'),
            $country,
            $model,
            $mac,
            $version,
            $_SERVER['HTTP_REFERER']
        ));
    }

    private function logNotValidMAC($sn, $stb_type){
        $logger = new Logger();
        $logger->setPrefix("mac_validation_");
        $date = new DateTime('now', new DateTimeZone(Config::get('default_timezone')));
        $logger->error(sprintf("%s - [%s] %s, %s, %s, url=%s\n",
            empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'] ,
            $date->format('r'),
            $this->mac,
            $sn,
            $stb_type,
            $_SERVER['REQUEST_URI']
        ));
    }

    public static function logDoubleMAC($ips){
        $logger = new Logger();
        $logger->setPrefix("mac_clone_");
        $date = new DateTime('now', new DateTimeZone(Config::get('default_timezone')));
        $logger->error(sprintf("[%s] %s (%s)\n",
            $date->format('r'),
            Stb::getInstance()->mac,
            implode(", ", $ips)
        ));
    }

    public static function logOssError(Exception $e){
        $logger = new Logger();
        $logger->setPrefix("oss_");
        $date = new DateTime('now', new DateTimeZone(Config::get('default_timezone')));
        $logger->error(sprintf("[%s] %s\nMessage:%s\nTrace:[%s]\n-------\n",
            $date->format('r'),
            Stb::getInstance()->mac,
            $e->getMessage(),
            $e->getTraceAsString()
        ));
    }

    public function setClockOnVideo(){
        $clockType = stripslashes($_REQUEST['clockType']);
        $this->db->update('users', array('video_clock' => $clockType), array('mac' => $this->mac));
        $this->params['video_clock'] = $clockType;
        return true;
    }
}