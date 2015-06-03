<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserToS extends RESTApiController
{

    protected $name = 'tos';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $user = \Stb::getById($parent_id);

        /// sptintf format: 1-account_number, 2-full name, 3-login, 4-mac
        return sprintf(_('account_terms_info'),
            $user['ls'],
            $user['fname'],
            $user['login'],
            $user['mac']
        );
    }
}