<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserMessageHistory extends RESTApiController
{

    protected $name = 'message-history';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $events = \Mysql::getInstance()->from('events')
            ->where(array('uid' => $parent_id))
            ->orderby('addtime', 'DESC')
            ->get()->all();

        if (empty($events)){
            return null;
        }

        $allowed_events = array('send_msg');

        $user_events = array();

        foreach ($events as $event){

            if (!in_array($event['event'], $allowed_events)){
                continue;
            }

            $result = array();

            $result['id']        = $event['id'];
            $result['type']      = $event['event'];
            $result['msg']       = $event['msg'];
            $result['send_time'] = strtotime($event['addtime']);

            $user_events[] = $result;
        }

        return $user_events;
    }
}