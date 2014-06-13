<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiTvChannelDocument extends RESTApiDocument
{
    private $parent;
    private $id;

    public function __construct(RESTApiResourceTvChannels $parent, array $params){
        parent::__construct();

        $this->parent = $parent;

        if (empty($params)){
            return;
        }

        $this->id = (int) $params[0];
        $this->parent->setChannelId($this->id);
    }

    public function get(RESTApiRequest $request){

        $channels = $this->parent->get($request);

        if (empty($channels)){
            throw new RESTNotFound("Channel not found");
        }

        return $channels[0];
    }
}