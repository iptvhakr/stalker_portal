<?php

class SimpleOssWrapper implements OssWrapperInterface
{
    public function getUserInfo(User $user){

        if (!Config::exist('oss_url')){
            return false;
        }

        if (Config::get('oss_url') == ''){
            return false;
        }

        $data = file_get_contents(Config::get('oss_url').'?mac='.$user->getMac().'&serial_number='.$user->getSerialNumber());

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

        return $info;
    }

    public function registerSTB($mac, $serial_number, $model){
        return true;
    }

    public function getPackagePrice($ext_package_id){
        return 0;
    }

    public function subscribeToPackage($ext_package_id){

        if (!Config::exist('on_subscribe_hook_url')){
            return true;
        }

        return $this->onSubscriptionHookResult('on_subscribe_hook_url', $ext_package_id);
    }

    public function unsubscribeFromPackage($ext_package_id){

        if (!Config::exist('on_unsubscribe_hook_url')){
            return true;
        }

        return $this->onSubscriptionHookResult('on_unsubscribe_hook_url', $ext_package_id);
    }

    private function onSubscriptionHookResult($config_param, $ext_package_id){

        if (Config::get($config_param) == ''){
            return false;
        }

        $url = Config::get($config_param).'?mac='.Stb::getInstance()->mac.'&tariff_id='.User::getInstance(Stb::getInstance()->id)->getExternalTariffId().'&package_id='.$ext_package_id;

        var_dump($url);

        $data = file_get_contents($url);

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

        return $data['results'];
    }
}