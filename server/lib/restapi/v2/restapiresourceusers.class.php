<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceUsers extends RESTApiCollection
{

    public function __construct(array $nested_params, array $external_params){

        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiUsersDocument();
        $this->document->controllers->add(new RESTApiUserSettings());
        $this->document->controllers->add(new RESTApiUserPing());
        $this->document->controllers->add(new RESTApiUserMediaInfo());
        $this->document->controllers->add(new RESTApiPvrSummary());
        $this->document->controllers->add(new RESTApiUserMessage());

        $this->fields_map = array_fill_keys(array('id', "ls", "status", "mac"), true);
    }

    public function getCount(RESTApiRequest $request){
        return (int) \Stb::getRawAll()->count()->get()->counter();
    }

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