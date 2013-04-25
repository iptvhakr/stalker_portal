<?php

namespace Stalker\Lib\StbApi;

interface AccountInfo{

    public function getMainInfo();

    public function getPaymentInfo();

    public function getAgreementInfo();

    public function getTermsInfo();

    public function getDemoVideoParts();

    public function getUserPackages();
}
