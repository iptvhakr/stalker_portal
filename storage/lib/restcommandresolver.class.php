<?php

class RESTCommandResolver
{

    public function __construct(){}

    /**
     * @throws RESTCommandResolverException
     * @param RESTRequest $request
     * @return RESTCommand
     */
    public function getCommand(RESTRequest $request){

        //For php >= 5.3.0
        /*$resource = implode(array_map(function($part){
            return ucfirst($part);
        },explode('_', $request->getResource())));*/

        $resource = '';

        foreach (explode('_', $request->getResource()) as $part){
            $resource .= ucfirst($part);
        }

        $class = 'RESTCommand'.ucfirst($resource);

        if (!class_exists($class)){
            throw new RESTCommandResolverException('Resource "'.$class.'" does not exist');
        }

        return new $class;
    }
}

class RESTCommandResolverException extends Exception {}

?>