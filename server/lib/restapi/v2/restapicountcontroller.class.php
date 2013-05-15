<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiCountController extends RESTApiController
{
    protected $name = 'count';
    protected $parent;

    public function __construct($parent){
        $this->parent = $parent;
    }

    public function get(RESTApiRequest $request){
        return $this->parent->getCount($request);
    }
}