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
        return $this->profile['mac'];
    }

    public function getProfileParam($param){
        return $this->profile[$param];
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

    public function getServicesByType($type = 'tv'){

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

        if (empty($packages_ids)){
            return null;
        }

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

        $packages = Mysql::getInstance()
            ->from('services_package')
            ->where(array('type' => $type))
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
            ->select('package_in_plan.*, services_package.name as name, services_package.external_id as external_id, services_package.description as description')
            ->from('package_in_plan')
            ->join('services_package', 'services_package.id', 'package_in_plan.package_id', 'INNER')
            ->where(array('plan_id' => $plan['id']))
            ->orderby('name')
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
                return $package_id == $item['package_id'] && $item['optional'] == 1 && !$item['subscribed'];
            });
        }

        if (empty($filtered_packages)){
            return false;
        }

        if (!$force_no_check_billing){
            // api hook place
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
            // api hook place
        }

        return Mysql::getInstance()->delete('user_package_subscription', array(
            'user_id' => $this->id,
            'package_id' => $package_id
        ));
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

        if ($info['tariff_plan'] == 0){
            $info['tariff_plan'] = (int) Mysql::getInstance()->from('tariff_plan')->where(array('user_default' => 1))->get()->first('id');
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

        if (!Config::exist('oss_url')){
            return false;
        }

        if (Config::get('oss_url') == ''){
            return false;
        }

        $data = file_get_contents(Config::get('oss_url').'?mac='.$this->getMac());

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

        if (array_key_exists(0, $data['results'])){
            $info = $data['results'][0];
        }else{
            $info = $data['results'];
        }

        var_dump($info);

        return $info;
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
}