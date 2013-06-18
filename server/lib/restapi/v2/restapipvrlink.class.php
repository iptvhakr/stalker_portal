<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiPvrLink extends RESTApiController
{
    protected $name = 'link';
    private   $params;

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request, $parent_id){

        $rec_id = $parent_id;

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        $user_id = $this->params['users.id'];

        $user = \Stb::getById($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        $pvr = new \RemotePvr();

        $recording = $pvr->getById($rec_id);

        if (empty($recording)){
            throw new RESTNotFound("Recording not found");
        }

        if ($recording['uid'] != $user_id){
            throw new RESTNotFound("User don't have access to this recording");
        }

        try{
            $url = $pvr->getUrlByRecId($rec_id);
        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        if (preg_match("/(\S+:\/\/\S+)/", $url, $match)){
            $url = $match[1];
        }

        return $url;
    }
}