<?php

namespace Stalker\Lib\RESTAPI\v2;

abstract class RESTApiCollection extends RESTApiResource
{
    protected $document;

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->controllers->add(new RESTApiCountController($this));
    }

    //abstract public function get(RESTApiRequest $request);
    abstract public function getCount(RESTApiRequest $request);
}