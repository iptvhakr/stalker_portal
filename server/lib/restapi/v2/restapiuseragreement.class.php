<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserAgreement extends RESTApiController
{

    protected $name = 'agreement';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $user = \Stb::getById($parent_id);

        /// sptintf format: 1-account_number, 2-full name, 3-login, 4-mac
        return sprintf(_('account_agreement_info'),
            $user['ls'],
            $user['fname'],
            $user['login'],
            $user['mac']
        );
    }
}