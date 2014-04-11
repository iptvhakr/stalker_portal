<?php

class AccountInfo implements \Stalker\Lib\StbApi\AccountInfo
{
    private $stb;

    public function __construct(){}

    public function getMainInfo(){

        $user = User::getInstance(Stb::getInstance()->id);

        $oss_info = $user->getInfoFromOSS();

        $info = array(
            'fname' => $user->getProfileParam('fname'),
            'phone' => $user->getProfileParam('phone'),
            'ls'    => $user->getProfileParam('ls'),
            'mac'   => $user->getProfileParam('mac')
        );

        if (is_array($oss_info)){
            $info = array_merge($info, $oss_info);
        }

        $info['last_change_status'] = $user->getProfileParam('last_change_status');

        if (array_key_exists('end_date', $info)){
            $end_time = strtotime($info['end_date']);

            if ($end_time){
                $days = ceil(($end_time - time())/(24*3600));

                /// in format of php date() function
                $info['end_date'] = date(_('end_date_format'), strtotime($info['end_date'])).' ('.sprintf(ngettext('%d day', '%d days', $days), $days).')';
            }
        }

        if (Config::get('enable_tariff_plans')){
            $info['tariff_plan'] = $user->getTariffPlanName();
        }

        return $info;
    }

    public function getPaymentInfo(){
        /// sptintf format: 1-account_number, 2-full name, 3-login, 4-mac
        return sprintf(_('account_payment_info'),
            Stb::getInstance()->getParam('ls'),
            Stb::getInstance()->getParam('fname'),
            Stb::getInstance()->getParam('login'),
            Stb::getInstance()->getParam('mac')
        );
    }

    public function getAgreementInfo(){
        /// sptintf format: 1-account_number, 2-full name, 3-login, 4-mac
        return sprintf(_('account_agreement_info'),
            Stb::getInstance()->getParam('ls'),
            Stb::getInstance()->getParam('fname'),
            Stb::getInstance()->getParam('login'),
            Stb::getInstance()->getParam('mac')
        );
    }

    public function getTermsInfo(){
        /// sptintf format: 1-account_number, 2-full name, 3-login, 4-mac
        return sprintf(_('account_terms_info'),
            Stb::getInstance()->getParam('ls'),
            Stb::getInstance()->getParam('fname'),
            Stb::getInstance()->getParam('login'),
            Stb::getInstance()->getParam('mac')
        );
    }

    public function getDemoVideoParts(){
        return Config::getSafe('demo_part_video_url', '');
    }

    public function getUserPackages(){
        $user = User::getInstance(Stb::getInstance()->id);
        $packages = $user->getPackages();

        $page = intval($_GET['p']);

        if ($page == 0){
            $page = 1;
        }

        $sliced_packages = array_slice($packages, ($page-1) * 14, 14);

        //var_dump($packages);

        $sliced_packages = array_map(function($package){
            $package['optional'] = (boolean) $package['optional'];

            if ($package['subscribed']){
                $package['subscribed_str'] = _('Subscribed');
            }else{
                $package['not_subscribed_str'] = _('Not subscribed');
            }

            return $package;
        }, $sliced_packages);

        $data = array(
            'total_items' => count($packages),
            'max_page_items' => 14,
            'selected_item' => 0,
            'cur_page' => 0,
            'data' => $sliced_packages,
        );

        return $data;
    }
}

?>