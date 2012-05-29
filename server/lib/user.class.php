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

    private function __construct($uid = 0){
        $this->id = (int) $uid;

        $this->profile = Mysql::getInstance()->from('users')->where(array('id' => $this->id))->get()->first();
    }

    public function getId(){
        return $this->id;
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
            ->select('package_in_plan.*, services_package.name as name, services_package.description as description')
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
}