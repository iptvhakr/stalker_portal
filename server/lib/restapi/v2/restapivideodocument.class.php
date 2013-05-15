<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiVideoDocument extends RESTApiDocument
{
    private $parent;
    private $id;

    public function __construct(RESTApiResourceVideo $parent, array $params){
        parent::__construct();

        $this->parent = $parent;

        if (empty($params)){
            return;
        }

        $this->id = (int) $params[0];
        $this->parent->setVideoId($this->id);
    }

    public function get(RESTApiRequest $request){

        $videos = $this->parent->get($request);

        if (empty($videos)){
            throw new RESTNotFound("Video not found");
        }

        return $videos[0];
    }

}