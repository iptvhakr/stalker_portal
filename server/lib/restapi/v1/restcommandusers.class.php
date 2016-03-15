<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Stb;

class RESTCommandUsers extends RESTCommandAccounts
{

    public function get(RESTRequest $request){

        $accounts = parent::get($request);

        if (!empty($accounts)){
            return $accounts[0];
        }

        return $accounts;
    }

    protected function getUsersIdsFromIdentifiers($identifiers){
        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12 && strpos($identifiers[0], ":")){
            return Stb::getUidByMacs($identifiers);
        }else{
            return Stb::getUidByLogin($identifiers);
        }
    }
}