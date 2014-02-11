<?php

class RESTCommandVclub extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new Vclub();
    }

    public function get(RESTRequest $request){
        
        $identifiers = $request->getIdentifiers();

        if (empty($identifiers[0]) || $identifiers[0] == '.' || $identifiers[0] == '..'){
            throw new ErrorException('Empty media_name');
        }

        return $this->manager->checkMedia($identifiers[0]);
    }

    public function create(RESTRequest $request){

        $media_name = $request->getData('media_name');
        $media_id   = $request->getData('media_id');
        $proto      = $request->getData('proto');

        if (empty($media_name)){
            throw new ErrorException('Empty media_name');
        }

        if (empty($media_id)){
            throw new ErrorException('Empty media_id');
        }

        return $this->manager->createLink($media_name, $media_id, $proto);
    }

    public function update(RESTRequest $request){

        $media_name = $request->getData('media_name');

        if (empty($media_name)){
            throw new ErrorException('Empty media_name');
        }

        return $this->manager->createDir($media_name);
    }
}

?>