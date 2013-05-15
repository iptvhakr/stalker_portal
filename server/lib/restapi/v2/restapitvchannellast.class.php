<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiTvChannelLast extends RESTApiController
{
    protected $name = 'last';

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request){

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        $user_id = $this->params['users.id'];

        $user = \User::getInstance($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        return $user->getLastChannelId();
    }

    public function update(RESTApiRequest $request){

        $ch_id = (int) $request->getData('ch_id');

        if (empty($ch_id)){
            throw new RESTBadRequest("Update data is empty");
        }

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        $user_id = $this->params['users.id'];

        $user = \User::getInstance($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        return $user->setLastChannelId($ch_id);
    }
}