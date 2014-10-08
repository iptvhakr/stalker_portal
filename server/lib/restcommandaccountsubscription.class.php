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
                'subscribed'    => $info['subscribed'],
                'subscribed_id' => $info['subscribed_id']
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

        $account = array_intersect_key($data, array('subscribed' => true, 'subscribed_id' => true));

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

            $subscribe      = empty($data['subscribed']) ? array() : array_unique($data['subscribed']);
            $subscribe_id   = empty($data['subscribed_id']) ? array() : $data['subscribed_id'];
            $unsubscribe    = empty($data['subscribed']) ? array() : array_diff($info['subscribed'], $data['subscribed']);
            $unsubscribe_id = empty($data['subscribed_id']) ? array() : array_diff($info['subscribed_id'], $data['subscribed_id']);

            $subscribe = $user->updateOptionalPackageSubscription(
                array(
                    'subscribe'       => $subscribe,
                    'subscribe_ids'   => $subscribe_id,
                    'unsubscribe'     => $unsubscribe,
                    'unsubscribe_ids' => $unsubscribe_id
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

        $account = array_intersect_key($data, array('subscribed' => true, 'subscribed_id' => true, 'unsubscribed' => true, 'unsubscribed_id' => true));

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

        $subscribed      = empty($data['subscribed']) ? array() : $data['subscribed'];
        $subscribed_id   = empty($data['subscribed_id']) ? array() : $data['subscribed_id'];
        $unsubscribed    = empty($data['unsubscribed']) ? array() : $data['unsubscribed'];
        $unsubscribed_id = empty($data['unsubscribed_id']) ? array() : $data['unsubscribed_id'];

        $result = true;

        foreach($users_ids as $user_id){
            $user = User::getInstance($user_id);
            $subscribe = $user->updateOptionalPackageSubscription(
                array(
                    'subscribe'       => $subscribed,
                    'subscribe_ids'   => $subscribed_id,
                    'unsubscribe'     => $unsubscribed,
                    'unsubscribe_ids' => $unsubscribed_id,
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
