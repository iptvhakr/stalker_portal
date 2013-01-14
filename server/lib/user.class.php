<?php

class User
{
    private $id;
    private static $instance = null;
    private $profile;

    /**
     * @static
     * @param int $uid
     * @return User
     */
    public static function getInstance($uid = 0){
        if (self::$instance == null)
        {
            self::$instance = new self($uid);
        }
        return self::$instance;
    }

    public static function clear(){
        self::$instance = null;
    }

    private function __construct($uid = 0){
        $this->id = (int) $uid;
        $this->profile = Mysql::getInstance()->from('users')->where(array('id' => $this->id))->get()->first();

        if ($this->profile['tariff_plan_id'] == 0){
            $this->profile['tariff_plan_id'] = (int) Mysql::getInstance()->from('tariff_plan')->where(array('user_default' => 1))->get()->first('id');
        }
    }

    public function getId(){
        return $this->id;
    }

    public static function getUserAgent(){

        $ua = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];

        if (!empty($_SERVER['HTTP_X_USER_AGENT'])){
            $ua .= '; '.$_SERVER['HTTP_X_USER_AGENT'];
        }

        return $ua;
    }

    public static function getCountryId(){

        $ip = !empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];

        $country_code = @geoip_country_code_by_name($ip);

        if (empty($country_code)){
            return 0;
        }

        return (int) Mysql::getInstance()->from('countries')->where(array('iso2' => $country_code))->get()->first('id');
    }

    public function getMac(){
        return empty($this->profile['mac']) ? null : $this->profile['mac'];
    }

    public function getSerialNumber(){
        return $this->profile['serial_number'];
    }

    public function setSerialNumber($serial_number){

        if ($this->profile['serial_number'] != $serial_number){
            Mysql::getInstance()->update('users',
                array(
                    'serial_number' => $serial_number
                ),
                array('id' => $this->id)
            );
        }

        return $this->profile['serial_number'] = $serial_number;
    }

    public function getExternalTariffId(){
        $tariff_plan_id = $this->profile['tariff_plan_id'];
        return Mysql::getInstance()->from('tariff_plan')->where(array('id' => $tariff_plan_id))->get()->first('external_id');
    }

    public function getProfileParam($param){
        return $this->profile[$param];
    }

    public function getProfile(){
        return $this->profile;
    }

    public function getVideoFavorites(){

        $fav_video_arr = Mysql::getInstance()->from('fav_vclub')->where(array('uid' => $this->id))->get()->first();

        if (empty($fav_video_arr)){
            return array();
        }

        $fav_video = unserialize($fav_video_arr['fav_video']);

        if (!is_array($fav_video)){
            $fav_video = array();
        }

        return $fav_video;
    }

    public function getServicesByType($type = 'tv', $service_type = null){

        $plan = Mysql::getInstance()
            ->from('tariff_plan')
            ->where(array('id' => $this->profile['tariff_plan_id']))
            ->get()
            ->first();

        if (empty($plan)){
            return null;
        }

        $packages_ids = Mysql::getInstance()
            ->select('package_id as id')
            ->from('package_in_plan')
            ->where(array('plan_id' => $plan['id'], 'optional' => 0))
            ->get()
            ->all('id');

        $available_packages_ids = Mysql::getInstance()
            ->select('package_id as id')
            ->from('package_in_plan')
            ->where(array('plan_id' => $plan['id']))
            ->get()
            ->all('id');

        $subscribed_packages_ids = Mysql::getInstance()
            ->from('user_package_subscription')
            ->where(array('user_id' => $this->id))
            ->get()
            ->all('package_id');

        $subscribed_packages_ids = array_filter($subscribed_packages_ids, function($package_id) use ($available_packages_ids){
            return in_array($package_id, $available_packages_ids);
        });

        if (!empty($subscribed_packages_ids)){
            $packages_ids = array_merge($packages_ids, $subscribed_packages_ids);
        }

        $packages_ids = array_unique($packages_ids);

        if (empty($packages_ids)){
            return null;
        }

        $package_where = array('type' => $type);

        if ($service_type){
            $package_where['service_type'] = $service_type;
        }

        $packages = Mysql::getInstance()
            ->from('services_package')
            ->where($package_where)
            ->in('id', $packages_ids)
            ->get()
            ->all();

        $contain_all_services = (bool) array_filter($packages, function($package){
            return $package['all_services'] == 1;
        });

        if ($contain_all_services){
            return 'all';
        }

        if (empty($packages)){
            return null;
        }

        $service_ids = array();

        foreach ($packages as $package){

            $ids = Mysql::getInstance()
                ->select('service_id as id')
                ->from('service_in_package')
                ->where(array('package_id' => $package['id']))
                ->get()
                ->all('id');

            $service_ids = array_merge($service_ids, $ids);
        }

        $service_ids = array_unique($service_ids);

        return $service_ids;
    }

    public function getPackages(){
        $plan = Mysql::getInstance()
            ->from('tariff_plan')
            ->where(array('id' => $this->profile['tariff_plan_id']))
            ->get()
            ->first();

        if (empty($plan)){
            return null;
        }

        $packages = Mysql::getInstance()
            ->select('package_in_plan.*, services_package.id as services_package_id, services_package.name as name,'
                .' services_package.type as type, services_package.external_id as external_id,'
                .' services_package.description as description, services_package.service_type as service_type')
            ->from('package_in_plan')
            ->join('services_package', 'services_package.id', 'package_in_plan.package_id', 'INNER')
            ->where(array('plan_id' => $plan['id']))
            ->orderby('package_in_plan.optional, services_package_id')
            ->get()
            ->all();

        $subscribed_packages_ids = Mysql::getInstance()
            ->from('user_package_subscription')
            ->where(array('user_id' => $this->id))
            ->get()
            ->all('package_id');

        $packages = array_map(function($package) use ($subscribed_packages_ids){

            if ($package['optional'] == 1){
                $package['subscribed'] = in_array($package['package_id'], $subscribed_packages_ids);
            }else{
                $package['subscribed'] = true;
            }

            return $package;
        }, $packages);

        return $packages;
    }

    public function getTariffPlanName(){
        return Mysql::getInstance()
            ->from('tariff_plan')
            ->where(array('id' => $this->profile['tariff_plan_id']))
            ->get()
            ->first('name');
    }

    public function subscribeToPackage($package_id, $packages = null, $force_no_check_billing = false){

        if ($packages === null){
            $packages = $this->getPackages();
        }

        if ($packages != null){
            $filtered_packages = array_filter($packages, function($item) use ($package_id){
                return $package_id == $item['package_id'] && ($item['optional'] == 1 && !$item['subscribed'] || $item['service_type'] == 'single');
            });
        }

        if (empty($filtered_packages)){
            return false;
        }

        if (!$force_no_check_billing){

            $ext_package_id = Mysql::getInstance()->from('services_package')->where(array('id' => $package_id))->get()->first('external_id');
            $on_subscribe_result = OssWrapper::getWrapper()->subscribeToPackage($ext_package_id);

            var_dump($on_subscribe_result);

            if ($on_subscribe_result === true){
                return Mysql::getInstance()->insert('user_package_subscription', array(
                    'user_id' => $this->id,
                    'package_id' => $package_id
                ))->insert_id();
            }else{
                return false;
            }
        }

        return Mysql::getInstance()->insert('user_package_subscription', array(
            'user_id' => $this->id,
            'package_id' => $package_id
        ))->insert_id();
    }

    public function unsubscribeFromPackage($package_id, $packages = null, $force_no_check_billing = false){

        if ($packages === null){
            $packages = $this->getPackages();
        }

        $filtered_packages = array_filter($packages, function($item) use ($package_id){
            return $package_id == $item['package_id'] && $item['optional'] == 1 && $item['subscribed'];
        });

        if (empty($filtered_packages)){
            return false;
        }

        if (!$force_no_check_billing){

            $ext_package_id = Mysql::getInstance()->from('services_package')->where(array('id' => $package_id))->get()->first('external_id');
            $on_unsubscribe_result = OssWrapper::getWrapper()->unsubscribeFromPackage($ext_package_id);

            var_dump($on_unsubscribe_result);

            if ($on_unsubscribe_result === true){
                return Mysql::getInstance()->delete('user_package_subscription', array(
                    'user_id' => $this->id,
                    'package_id' => $package_id
                ))->result();
            }else{
                return false;
            }
        }

        return Mysql::getInstance()->delete('user_package_subscription', array(
            'user_id' => $this->id,
            'package_id' => $package_id
        ))->result();
    }

    public function getPriceForPackage($package_id){

        $package = Mysql::getInstance()->from('services_package')->where(array('id' => $package_id))->get()->first();

        return OssWrapper::getWrapper()->getPackagePrice($package['external_id']);
    }

    public function getAccountInfo(){
        $info = Mysql::getInstance()
            ->select('login, fname as full_name, ls as account_number, external_id as tariff_plan, serial_number as stb_sn,
                mac as stb_mac, stb_type, status')
            ->from('users')
            ->join('tariff_plan', 'tariff_plan_id', 'tariff_plan.id', 'LEFT')
            ->where(array('users.id' => $this->id))
            ->get()
            ->first();

        $info['status'] = intval(!$info['status']);

                if ($info['tariff_plan'] === null){
            $info['tariff_plan'] = Mysql::getInstance()->from('tariff_plan')->where(array('user_default' => 1))->get()->first('external_id');
        }

        $packages = $this->getPackages();

        if (count($packages) == 0){
            $info['subscribed'] = array();
        }else{
            $info['subscribed'] = array_values(array_map(function($package){
                return $package['external_id'];
            }, array_filter($packages, function($package){
                return $package['optional'] == 1 && $package['subscribed'];
            })));
        }

        //$info['subscribed'] = $packages;

        return $info;
    }

    public static function createAccount($account){

        $allowed_fields = array_fill_keys(array('login', 'password', 'full_name', 'account_number', 'tariff_plan', 'tariff_plan_id', 'stb_mac'), true);

        $key_map = array(
            'full_name'      => 'fname',
            'account_number' => 'ls',
            'stb_mac'        => 'mac',
        );

        $new_account = array_intersect_key($account, $allowed_fields);

        if (isset($account['status'])){
            $new_account['status'] = intval(!$account['status']);
        }

        foreach ($new_account as $key => $value){
            if (array_key_exists($key, $key_map)){
                $new_account[$key_map[$key]] = $value;
                unset($new_account[$key]);
            }
        }

        if (empty($new_account['tariff_plan_id']) && !empty($new_account['tariff_plan'])){
            $new_account['tariff_plan_id'] = (int) Mysql::getInstance()
                ->from('tariff_plan')
                ->where(array('external_id' => $new_account['tariff_plan']))
                ->get()
                ->first('id');

        }

        if (array_key_exists('tariff_plan', $new_account)){
            unset($new_account['tariff_plan']);
        }

        $insert_id = Mysql::getInstance()->insert('users', $new_account)->insert_id();

        if (!$insert_id){
            return false;
        }

        if (!empty($new_account['password'])){
            $password = md5(md5($new_account['password']).$insert_id);
            Mysql::getInstance()->update('users', array('password' => $password), array('id' => $insert_id));
        }

        return $insert_id;
    }

    public function updateAccount($account){

        $allowed_fields = array_fill_keys(array('password', 'full_name', 'account_number', 'tariff_plan', 'stb_mac'), true);

        $key_map = array(
            'full_name'      => 'fname',
            'account_number' => 'ls',
            'stb_mac'        => 'mac'
        );

        $new_account = array_intersect_key($account, $allowed_fields);

        if (isset($account['status'])){
            $this->setStatus($account['status']);

            if (empty($new_account)){
                return true;
            }
        }

        foreach ($new_account as $key => $value){
            if (array_key_exists($key, $key_map)){
                $new_account[$key_map[$key]] = $value;
                unset($new_account[$key]);
            }
        }

        if (!empty($new_account['tariff_plan'])){
            $new_account['tariff_plan_id'] = (int) Mysql::getInstance()
                ->from('tariff_plan')
                ->where(array('external_id' => $new_account['tariff_plan']))
                ->get()
                ->first('id');

            unset($new_account['tariff_plan']);
        }

        if (!empty($new_account['password'])){
            $password = md5(md5($new_account['password']).$this->id);
            Mysql::getInstance()->update('users', array('password' => $password), array('id' => $this->id));
            unset($new_account['password']);
        }else{
            unset($new_account['password']);
        }

        return Mysql::getInstance()->update('users', $new_account, array('id' => $this->id))->result();
    }

    /**
     * @static
     * @param $login
     * @return bool|User
     */
    public static function getByLogin($login){

        $user = Mysql::getInstance()->from('users')->where(array('login' => $login))->get()->first();

        if (empty($user)){
            return false;
        }

        return self::getInstance((int) $user['id']);
    }

    public static function getByMac($mac){

        $user = Mysql::getInstance()->from('users')->where(array('mac' => $mac))->get()->first();

        if (empty($user)){
            return false;
        }

        return self::getInstance((int) $user['id']);
    }

    public function delete(){
        return Mysql::getInstance()->delete('users', array('id' => $this->id))->result();
    }

    public function setStatus($status){

        $status = intval(!$status);

        if ($status == $this->profile['status']){
            return;
        }

        Mysql::getInstance()->update('users', array('status' => $status), array('id' => $this->id));

        $event = new SysEvent();
        $event->setUserListById($this->id);

        if ($status == 1){
            $event->sendCutOff();
        }else{
            $event->sendCutOn();
        }
    }

    public function updateOptionalPackageSubscription($params){

        if (empty($params['subscribe']) && empty($params['unsubscribe'])){
            return false;
        }

        $packages = $this->getPackages();

        $total_result = false;

        if (!empty($params['subscribe'])){

            if (!is_array($params['subscribe'])){
                $params['subscribe'] = array($params['subscribe']);
            }

            $user_packages = Mysql::getInstance()->from('services_package')->in('external_id', $params['subscribe'])->get()->all();

            foreach ($user_packages as $user_package){
                $result = $this->subscribeToPackage($user_package['id'], $packages, true);
                $total_result = $total_result || $result;
            }
        }

        if (!empty($params['unsubscribe'])){

            if (!is_array($params['unsubscribe'])){
                $params['unsubscribe'] = array($params['unsubscribe']);
            }

            $user_packages = Mysql::getInstance()->from('services_package')->in('external_id', $params['unsubscribe'])->get()->all();

            foreach ($user_packages as $user_package){
                $result = $this->unsubscribeFromPackage($user_package['id'], $packages, true);
                $total_result = $total_result || $result;
            }
        }

        return $total_result;
    }

    public function getInfoFromOSS(){

        try{
            return OssWrapper::getWrapper()->getUserInfo($this);
        }catch (OssException $e){
            Stb::logOssError($e);
            return array();
        }
    }

    public function updateUserInfoFromOSS(){

        $info = $this->getInfoFromOSS();

        if (!$info){
            return false;
        }

        $update_data = array();

        if (array_key_exists('ls', $info)){
            $this->profile['ls'] = $update_data['ls'] = $info['ls'];
        }

        if (array_key_exists('status', $info)){
            $this->profile['status'] = $update_data['status'] = intval(!$info['status']);
        }

        if (array_key_exists('additional_services_on', $info)){
            $this->profile['additional_services_on'] = $update_data['additional_services_on'] = intval($info['additional_services_on']);
        }

        if (array_key_exists('fname', $info)){
            $this->profile['fname'] = $update_data['fname'] = $info['fname'];
        }

        if (array_key_exists('phone', $info)){
            $this->profile['phone'] = $update_data['phone'] = $info['phone'];
        }

        if (array_key_exists('tariff', $info)){
            $tariff = Mysql::getInstance()->from('tariff_plan')->where(array('external_id' => $info['tariff']))->get()->first();

            if ($tariff){
                $tariff_id = $tariff['id'];
            }else{
                $tariff_id = 0;
            }

            $this->profile['tariff_plan_id'] = $update_data['tariff_plan_id'] = $tariff_id;
        }

        if (empty($update_data)){
            return false;
        }

        return Mysql::getInstance()->update('users', $update_data, array('id' => $this->id));
    }

    public function getLastChannelId(){
        return (int) Mysql::getInstance()->from('last_id')->where(array('uid' => $this->id))->get()->first('last_id');
    }

    public function setLastChannelId($ch_id){

        $last_id = Mysql::getInstance()->from('last_id')->where(array('uid' => $this->id))->get()->first();

        if (empty($last_id)){
            return (bool) Mysql::getInstance()
                ->insert('last_id',
                array(
                    'ident'   => $this->getMac(),
                    'last_id' => $ch_id,
                    'uid'     => $this->id
                ))
                ->insert_id();
        }else{
            return Mysql::getInstance()->update('last_id',
                array(
                    'last_id' => $ch_id
                ),
                array('uid' => $this->id))->result();
        }
    }

    public function getPackageByServiceId($service_id){

        $user_packages = $this->getPackages();

        if (empty($user_packages)){
            return null;
        }

        $user_packages = array_filter($user_packages, function($package){
            return $package['subscribed'];
        });

        if (empty($user_packages)){
            return null;
        }

        $user_packages_ids = array_map(function($package){
            return $package['package_id'];
        }, $user_packages);

        $user_packages_ids = array_values($user_packages_ids);

        return Mysql::getInstance()
            ->select('services_package.*')
            ->from('services_package')
            ->where(array('service_id' => $service_id))
            ->join('service_in_package', 'services_package.id', 'package_id', 'INNER')
            ->in('services_package.id', $user_packages_ids)
            ->get()
            ->first();
    }

    /**
     * Add or update rent record for user.
     *
     * @param int $video_id
     * @param int $price
     * @return bool|int $rent_session_id
     */
    public function rentVideo($video_id, $price = 0){

        $rented = Mysql::getInstance()
            ->from('video_rent')
            ->where(array('video_id' => $video_id, 'uid' => $this->id))
            ->get()
            ->first();

        $package = $this->getPackageByServiceId($video_id);

        if (empty($package)){
            return false;
        }

        $rent_data = array(
            'uid'           => $this->id,
            'video_id'      => $video_id,
            'price'         => $price,
            'rent_date'     => 'NOW()',
            'rent_end_date' => 'FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())+'.($package['rent_duration']*3600).')'
        );

        $rent_history_id = Mysql::getInstance()->insert('video_rent_history', $rent_data)->insert_id();

        $rent_data['rent_history_id'] = $rent_history_id;

        if (empty($rented)){
            return Mysql::getInstance()->insert('video_rent', $rent_data)->insert_id();
        }else{
            $result = Mysql::getInstance()->update('video_rent', $rent_data, array('id' => $rented['id']))->result();
            if (!$result){
                return false;
            }
            return (int) $rented['id'];
        }
    }

    /**
     * Return all rented video by user.
     *
     * @return array $rented_videos
     */
    public function getAllRentedVideo(){

        $raw = Mysql::getInstance()->query('select * from video_rent where uid='.$this->id.' and (rent_end_date>NOW() OR rent_date=rent_end_date)')->all();

        $map = array();

        foreach ($raw as $rent){

            if ($rent['rent_date'] != $rent['rent_end_date']){
                $rent['expires_in'] = self::humanDateDiff($rent['rent_end_date']);
            }

            $map[$rent['video_id']] = $rent;
        }

        return $map;
    }

    /**
     * @param string $date1
     * @param string $date2
     * @return bool|string
     */
    public static function humanDateDiff($date1, $date2 = 'now'){

        $diff_str = '';

        $ts1 = strtotime($date1);
        $ts2 = strtotime($date2);

        if (!$ts1 || !$ts1){
            return false;
        }

        $diff_seconds = $ts1 - $ts2;

        $days = floor($diff_seconds / 86400);

        $hours = floor(($diff_seconds-$days*86400) / 3600);

        $minutes = floor(($diff_seconds-($days*86400 + $hours*3600)) / 60);

        if ($days){
            $diff_str .= sprintf(ngettext('%d day', '%d days', $days), $days).' ';
        }

        if ($hours){
            $diff_str .= $hours._('h').' ';
        }

        if ($minutes){
            $diff_str .= $minutes._('min').' ';
        }

        return $diff_str;
    }

    public static function getPackageDescription(){

        $package_id  = (int) $_REQUEST['package_id'];

        $package = Mysql::getInstance()->from('services_package')->where(array('id' => $package_id))->get()->first();

        if (empty($package)){
            return false;
        }

        if ($package['all_services']){
            $service_filter = false;
        }else{
            $service_filter = Mysql::getInstance()
                ->from('service_in_package')
                ->where(array('package_id' => $package_id))
                ->get()
                ->all('service_id');
        }

        $services = Mysql::getInstance();

        if ($service_filter !== false){
            $services->in('id', $service_filter);
        }

        if ($package['type'] == 'tv'){

            $services = $services->from('itv')->orderby('name')->get()->all('name');

        }elseif($package['type'] == 'radio'){

            $services = $services->from('radio')->orderby('name')->get()->all('name');

        }elseif($package['type'] == 'video'){

            $services = $services->from('video')->orderby(sprintf(_('video_name_format'), 'name', 'o_name'))
                ->get()->all(sprintf(_('video_name_format'), 'name', 'o_name'));

        }else{
            $services = array_unique($service_filter);
        }

        $services_str = implode('<br>', $services);

        $type_map = array(
            'tv'     => _('TV channels'),
            'video'  => _('Movies'),
            'radio'  => _('Radio channels'),
            'module' => _('Modules')
        );

        return array(
            'type'    => array_key_exists($package['type'], $type_map) ? $type_map[$package['type']] : $package['type'],
            'content' => $services_str
        );
    }
}