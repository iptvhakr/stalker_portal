<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiTvChannelLink extends RESTApiController
{
    protected $name = 'link';
    private   $user_channels;
    private   $params;

    public function __construct($nested_params){

        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request, $parent_id){

        if (empty($this->params['users.login'])){
            throw new RESTBadRequest("User required");
        }

        $user_login = $this->params['users.login'];

        $stb = \Stb::getInstance();
        $user = $stb->getByLogin($user_login);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        $itv = \Itv::getInstance();

        $this->user_channels = $itv->getAllUserChannelsIdsByUid($user['id']);

        if (!in_array($parent_id, $this->user_channels)){
            throw new RESTForbidden("User don't have access to this channel");
        }

        $channel = $itv->getById($parent_id);

        if (empty($channel)){
            throw new RESTNotFound("Channel not found");
        }

        $start = $request->getParam('start');

        if ($start){
            // todo: time shift!
            throw new RESTNotFound("Time shift in progress...");
        }

        try{
            $url = $itv->getUrlByChannelId($parent_id);
        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        if (preg_match("/(\S+:\/\/\S+)/", $url, $match)){
            $url = $match[1];
        }

        return $url;
    }
}