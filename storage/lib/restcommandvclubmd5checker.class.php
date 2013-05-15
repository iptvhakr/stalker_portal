<?php

class RESTCommandVclubMd5Checker extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new Vclub();
    }

    public function create(RESTRequest $request){

        $media_name = $request->getData('media_name');

        if (empty($media_name)){
            throw new ErrorException('Empty media_name');
        }

        return $this->manager->startMD5Sum($media_name);
    }
}

?>