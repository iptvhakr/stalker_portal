<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiVideoNotEnded extends RESTApiController
{
    protected $name = 'not-ended';

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function update(RESTApiRequest $request, $video_id){

        $end_time = (int) $request->getData('end_time');

        if (empty($end_time)){
            throw new RESTBadRequest("Update data is empty");
        }

        $episode  = (int) $request->getData('episode');

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        $user_id = $this->params['users.id'];

        $user = \User::getInstance($user_id);

        return $user->setNotEndedVideo($video_id, $end_time, $episode);
    }

    public function delete(RESTApiRequest $request, $video_id){

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        $user_id = $this->params['users.id'];

        $user = \User::getInstance($user_id);

        return $user->setEndedVideo($video_id);
    }
}