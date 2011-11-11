<?php

class RESTCommandItv extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Itv::getInstance();
        $this->allowed_fields = array_fill_keys(array('id', 'name', 'number', 'base_ch', 'hd'), true);
    }

    public function get(RESTRequest $request){

        $itv_list = $this->manager->getByIds($request->getIdentifiers());

        $allowed_fields = $this->allowed_fields;

        $itv_list = array_map(function($item) use ($allowed_fields){

            $item = array_intersect_key($item, $allowed_fields);

            return $item;
        },
        $itv_list
        );

        return $itv_list;
    }
}

?>