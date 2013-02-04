<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiKaraokeLink extends RESTApiController
{
    protected $name = 'link';
    private   $params;

    public function __construct($nested_params){

        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request, $params){

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        if (is_array($params) && (count($params) != 4)){
            throw new RESTBadRequest("Bad params");
        }

        $user_id = $this->params['users.id'];

        $user = \Stb::getById($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        if (is_array($params)){
            $karaoke_id = (int) $params[0];
        }else{
            $karaoke_id = (int) $params;
        }

        $karaoke = \Karaoke::getInstance();

        try{
            $url = $karaoke->getUrlByKaraokeId($karaoke_id);
        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        if (preg_match("/(\S+:\/\/\S+)/", $url, $match)){
            $url = $match[1];
        }

        return $url;

    }
}