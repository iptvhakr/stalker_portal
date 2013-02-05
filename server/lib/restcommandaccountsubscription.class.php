<?php

class RESTCommandAccountSubscription extends RESTCommand
{
    public function get(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12){
            $users_ids = Stb::getUidByMacs($identifiers);
        }else{
            $users_ids = Stb::getUidByAccountNumber($identifiers);
        }

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $result = array();

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $info = $user->getAccountInfo();
            $result[] = array(
                'mac' => $user->getMac(),
                'subscribed' => $info['subscribed']
            );
            User::clear();
        }

        return $result;
    }

    public function create(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }

        $account = array_intersect_key($data, array('subscribed' => true));

        if (empty($account)){
            throw new RESTCommandException('Insert data is empty');
        }

        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12){
            $users_ids = Stb::getUidByMacs($identifiers);
        }else{
            $users_ids = Stb::getUidByAccountNumber($identifiers);
        }

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);

            $info = $user->getAccountInfo();

            $unsubscribe = array_diff($info['subscribed'], $data['subscribed']);

            $subscribe = $user->updateOptionalPackageSubscription(
                array(
                    'subscribe' => $data['subscribed'],
                    'unsubscribe' => $unsubscribe
            ));
            $result = $result && $subscribe;
            User::clear();
        }

        return $result;
    }

    public function update(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $account = array_intersect_key($data, array('subscribed' => true, 'unsubscribed' => true));

        if (empty($account)){
            throw new RESTCommandException('Insert data is empty');
        }

        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12){
            $users_ids = Stb::getUidByMacs($identifiers);
        }else{
            $users_ids = Stb::getUidByAccountNumber($identifiers);
        }

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $subscribed   = empty($data['subscribed']) ? array() : $data['subscribed'];
        $unsubscribed = empty($data['unsubscribed']) ? array() : $data['unsubscribed'];

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $subscribe = $user->updateOptionalPackageSubscription(
                array(
                    'subscribe'   => $subscribed,
                    'unsubscribe' => $unsubscribed,
                ));
            $result = $result && $subscribe;
            User::clear();
        }

        return $result;
    }

    public function delete(RESTRequest $request){
        $identifiers = $request->getIdentifiers();

        if (!empty($identifiers[0]) && strlen($identifiers[0]) >= 12){
            $users_ids = Stb::getUidByMacs($identifiers);
        }else{
            $users_ids = Stb::getUidByAccountNumber($identifiers);
        }

        if (count($identifiers) == 1 && count($users_ids) == 0){
            throw new RESTCommandException('Account not found');
        }

        if (count($identifiers) > 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);

            $info = $user->getAccountInfo();

            $subscribe = $user->updateOptionalPackageSubscription(
                array(
                    'subscribe' => array(),
                    'unsubscribe' => $info['subscribed']
                ));
            $result = $result && $subscribe;
            User::clear();
        }

        return $result;
    }
}
