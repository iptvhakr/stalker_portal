<?php

class ItvSubscription
{
    public static function getBonusChannelsIds($uid){
        
        $bonus_ch = Mysql::getInstance()->from('itv_subscription')->where(array('uid' => $uid))->get()->first('bonus_ch');

        if (empty($bonus_ch)){
            return array();
        }

        $bonus_ch_arr = unserialize(System::base64_decode($bonus_ch));

        if (!is_array($bonus_ch_arr)){
            return array();
        }

        return $bonus_ch_arr;
    }

    public static function getSubscriptionChannelsIds($uid){

        $mac = Mysql::getInstance()->from('users')->where(array('id' => $uid))->get()->first('mac');

        if (empty($mac)){
            return array();
        }

        $moderator = Mysql::getInstance()->from('moderators')->where(array('mac' => $mac, 'status' => 1))->get()->first();

        if (!empty($moderator)){
            return Mysql::getInstance()->from('itv')->where(array('base_ch' => 0))->get()->all('id');
        }

        $sub_ch = Mysql::getInstance()->from('itv_subscription')->where(array('uid' => $uid))->get()->first('sub_ch');

        if (empty($sub_ch)){
            return array();
        }
        
        $sub_ch_arr = unserialize(System::base64_decode($sub_ch));

        if (!is_array($sub_ch_arr)){
            return array();
        }

        return $sub_ch_arr;
    }

    public static function getByUids($uids = array()){
        
        $result = Mysql::getInstance()->select('itv_subscription.*, users.mac as mac, users.ls as ls, users.additional_services_on as additional_services_on')->from('itv_subscription')->join('users', 'itv_subscription.uid', 'users.id', 'LEFT');

        if (!empty($uids)){
            $result = $result->in('uid', $uids);
        }

        $result = $result->get()->all();

        $result = array_map(function($item){

            $item['sub_ch']   = unserialize(System::base64_decode($item['sub_ch']));
            $item['bonus_ch'] = unserialize(System::base64_decode($item['bonus_ch']));

            return $item;
        },
        $result
        );

        return $result;
    }

    public static function updateByUids($uids = array(), $data){

        if (empty($data)){
            return false;
        }

        //Mysql::$debug = true;

        $result = Mysql::getInstance();

        if (!empty($uids)){
            $result = $result->in('uid', $uids);
        }

        if (!key_exists('bonus_ch', $data) || !is_array($data['bonus_ch'])){
            $data['bonus_ch'] = array();
        }

        if (!key_exists('sub_ch', $data) || !is_array($data['sub_ch'])){
            $data['sub_ch'] = array();
        }

        if (key_exists('sub_ch', $data)){
            $data['sub_ch'] = System::base64_encode(serialize($data['sub_ch']));
        }

        if (key_exists('bonus_ch', $data)){
            $data['bonus_ch'] = System::base64_encode(serialize($data['bonus_ch']));
        }

        $data['addtime']  = 'NOW()';

        $result = $result->update('itv_subscription', $data);

        if (!$result){
            return false;
        }

        return self::getByUids($uids);
    }
}

?>