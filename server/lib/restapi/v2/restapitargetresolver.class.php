<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiTargetResolver
{

    protected $nested_params = array();

    /**
     * @param RESTApiRequest $request
     * @return RESTApiResource
     * @throws \Exception
     * @throws RESTNotFound
     */
    public function getTarget(RESTApiRequest $request){

        $resources_chain = $request->getResource();

        $target = null;

        for ($i = 0; $i < count($resources_chain); $i++){

            try{
                $class_name = $this->getClassForResource($resources_chain[$i]);
            }catch(\Exception $e){
                continue;
            }

            $reflection = new \ReflectionClass($class_name);
            $parent     = $reflection->getParentClass();

            $parent_name = explode('\\', $parent->getName());
            $parent_name = $parent_name[count($parent_name)-1];

            if (in_array($parent_name, array("RESTApiCollection", "RESTApiStore", "RESTApiController"))){

                $this->nested_params[$resources_chain[$i]] = isset($resources_chain[$i+1]) ? $resources_chain[$i+1] : "";
            }else if($parent_name == "RESTApiDocument"){
                continue;
            }else{
                throw new \Exception("Undefined resource type");
            }

            if ($parent_name == "RESTApiCollection"){
                $target = $resources_chain[$i];
            }
        }

        if (empty($target)){
            throw new RESTNotFound("Resource not found");
        }

        $idx = array_search($target, $resources_chain);
        $external_params = array_slice($resources_chain, $idx+1);

        array_pop($this->nested_params);

        return new $class_name($this->nested_params, $external_params);
    }

    protected function getClassForResource($resource){

        $class_name = implode("", array_map(function($part){
            return ucfirst($part);
        }, explode('-', $resource)));

        $class_name = 'Stalker\Lib\RESTAPI\v2\RESTApiResource'.$class_name;

        try{
            if (!class_exists($class_name)){
                throw new \Exception("Class not exist");
            }
        }catch(\Exception $e){
            throw new RESTNotFound("Resource '".$resource."' does not exist");
        }
        /*if (!class_exists($class_name)){
            throw new RESTNotFound("Resource '".$resource."' does not exist");
        }*/

        return $class_name;
    }


}