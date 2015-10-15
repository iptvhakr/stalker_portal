<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserModules extends RESTApiController
{

    protected $name = 'modules';

    public function __construct(){}

    public function get(RESTApiRequest $request, $parent_id){

        $all_modules = \Config::get('all_modules');
        $disabled_modules = \Stb::getDisabledModulesByUid((int) $parent_id);

        return array_values(array_diff($all_modules, $disabled_modules));
    }
}