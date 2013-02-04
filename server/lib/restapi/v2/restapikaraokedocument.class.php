<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiKaraokeDocument extends RESTApiDocument
{
    private $parent;
    private $id;

    public function __construct(RESTApiResourceKaraoke $parent, array $params){
        parent::__construct();

        $this->parent = $parent;

        if (empty($params)){
            return;
        }

        $this->id = (int) $params[0];
        $this->parent->setKaraokeId($this->id);
    }

    public function get(RESTApiRequest $request){

        $karaoke = $this->parent->get($request);

        if (empty($karaoke)){
            throw new RESTNotFound("Karaoke not found");
        }

        return $karaoke[0];
    }

}