<?php

class AccountInfo
{
    private $stb;

    public function __construct(){
        $this->stb = Stb::getInstance();
    }

    public function getMainInfo(){

        $user = User::getInstance(Stb::getInstance()->id);

        $info = $user->getInfoFromOSS();

        if (!$info){
            $info = array(
                'fname' => $this->stb->getParam('fname'),
                'phone' => $this->stb->getParam('phone'),
                'ls' => $this->stb->getParam('ls')
            );
        }

        $info['last_change_status'] = $this->stb->getParam('last_change_status');

        if (array_key_exists('end_date', $info)){
            $end_time = strtotime($info['end_date']);

            if ($end_time){
                $days = ceil(($end_time - time())/(24*3600));

                $info['end_date'] = $info['end_date'].' ('.sprintf(ngettext('%d day', '%d days', $days), $days).')';
            }
        }

        /*$info = array(
            'fname' => $this->stb->getParam('fname'),
            'last_change_status' => $this->stb->getParam('last_change_status'),
            'phone' => $this->stb->getParam('phone'),
            'ls' => $this->stb->getParam('ls')
        );*/

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
        $user = User::getInstance($this->stb->id);
        $packages = $user->getPackages();

        $page = intval($_GET['p']);

        if ($page == 0){
            $page = 1;
        }

        $sliced_packages = array_slice($packages, ($page-1) * 10, 10);

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