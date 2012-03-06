<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUsersDocument extends RESTApiDocument
{
    private $fields_map;
    private $manager;

    public function __construct(){
        parent::__construct();

        $this->fields_map = array_fill_keys(array('id', "ls", "status", "mac"), true);

        $this->manager = \Stb::getInstance();
    }

    public function get(RESTApiRequest $request, $id){

        $user = $this->manager->getByLogin($id);

        return $this->filterDocument($user);
    }

    public function filterDocument($document){

        if (empty($document)){
            throw new RESTNotFound("Document not found");
        }

        $document = array_intersect_key($document, $this->fields_map);

        $document['status'] = intval(!$document['status']);
        $document['account'] = $document['ls'];
        unset($document['account']);

        return $document;
    }
}