<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserMessage extends RESTApiController
{

    protected $name = 'message';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $events = \Event::getAllNotEndedEvents($parent_id);

        if (empty($events)){
            return null;
        }

        $allowed_events = array('send_msg', 'update_epg', 'reboot', 'cut_off', 'cut_on');

        foreach ($events as $event){

            \Event::setConfirmed($event['id']);

            if (!in_array($event['event'], $allowed_events)){
                continue;
            }

            $result = array();

            $result['id']        = $event['id'];
            $result['type']      = $event['event'];
            $result['msg']       = $event['msg'];
            $result['send_time'] = strtotime($event['addtime']);

            return $result;
        }

        return null;
    }
}