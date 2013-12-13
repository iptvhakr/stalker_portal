<?php

class RESTCommandAccounts extends RESTCommand
{
    public function get(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        $users_ids = $this->getUsersIdsFromIdentifiers($identifiers);

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if ($identifiers != null && count($identifiers) > count($users_ids)){
            throw new RESTCommandException('One or more identifiers are incorrect');
        }

        $result = array();

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $result[] = $user->getAccountInfo();
            User::clear();
        }

        return $result;
    }

    public function create(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('login', 'password', 'full_name', 'phone', 'account_number', 'tariff_plan', 'status', 'stb_mac', 'comment'), true);

        $account = array_intersect_key($data, $allowed_to_update_fields);

        if (empty($account)){
            throw new RESTCommandException('Insert data is empty');
        }

        if (!empty($account['stb_mac'])){
            $mac = Middleware::normalizeMac($account['stb_mac']);

            if (!$mac){
                throw new RESTCommandException('Not valid mac address');
            }

            $account['stb_mac'] = $mac;
        }

        if (empty($account['login'])){
            throw new RESTCommandException('Login required');
        }

        $user = User::getByLogin($account['login']);

        if (!empty($user)){
            throw new RESTCommandException('Login already in use');
        }

        if (!empty($account['stb_mac'])){
            $user = User::getByMac($account['stb_mac']);

            if (!empty($user)){
                throw new RESTCommandException('MAC address already in use');
            }
        }

        return (boolean) User::createAccount($account);
    }

    public function update(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('login', 'password', 'full_name', 'phone', 'account_number', 'tariff_plan', 'status', 'stb_mac', 'comment'), true);

        $account = array_intersect_key($data, $allowed_to_update_fields);

        if (empty($account)){
            throw new RESTCommandException('Insert data is empty');
        }

        $identifiers = $request->getIdentifiers();

        if (count($identifiers) == 0){
            throw new RESTCommandException('Identifier required');
        }

        $users_ids = $this->getUsersIdsFromIdentifiers($identifiers);

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        if (!empty($account['login'])){

            $user = User::getByLogin($account['login']);

            if (!empty($user) && ($user->getId() != $users_ids[0] || count($users_ids) > 1)){
                throw new RESTCommandException('Login already in use');
            }
        }

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $result = $user->updateAccount($account) && $result;
            User::clear();
        }

        return $result;
    }

    public function delete(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (count($identifiers) == 0){
            throw new RESTCommandException('Identifier required');
        }

        $users_ids = $this->getUsersIdsFromIdentifiers($identifiers);

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $result = $user->delete() && $result;
            User::clear();
        }

        return $result;
    }

    protected function getUsersIdsFromIdentifiers($identifiers){
        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12 && strpos($identifiers[0], ":")){
            return Stb::getUidByMacs($identifiers);
        }else{
            return Stb::getUidByAccountNumber($identifiers);
        }
    }
}