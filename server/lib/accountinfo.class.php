<?php

class AccountInfo
{
    private $stb;

    public function __construct(){
        $this->stb = Stb::getInstance();
    }

    public function getMainInfo(){
        return sprintf(_('account_main_info'),
            $this->stb->getParam('fname'),
            $this->stb->getParam('last_change_status'),
            $this->stb->getParam('phone'));
    }

    public function getPaymentInfo(){
        return _('account_payment_info');
    }

    public function getAgreementInfo(){
        return _('account_agreement_info');
    }

    public function getTermsInfo(){
        return _('account_terms_info');
    }

    public function getDemoVideoParts(){
        return Config::getSafe('demo_part_video_url', '');
    }
}

?>