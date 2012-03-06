<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceUsers extends RESTApiCollection
{
    protected $manager;

    public function __construct(array $nested_params, array $external_params){

        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiUsersDocument();
        $this->document->controllers->add(new RESTApiUserSettings());

        $this->fields_map = array_fill_keys(array('id', "ls", "status", "mac"), true);

        $this->manager = \Stb::getInstance();
    }

    public function getCount(RESTApiRequest $request){
        return (int) $this->manager->getRawAll()->count()->get()->counter();
    }

    /*public function get(RESTApiRequest $request){

        $users = $this->manager->getAll($request->getLimit(), $request->getOffset());

        return $this->filter($users);
    }*/

    public function filter($collection){

        $fields_map = $this->fields_map;

        $collection = array_map(function($document) use ($fields_map){

            $document = array_intersect_key($document, $fields_map);

            $document['status'] = intval(!$document['status']);
            $document['account'] = $document['ls'];
            unset($document['account']);

            return $document;

        }, $collection);

        return $collection;
    }
}