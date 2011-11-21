<?php

class RESTCommandStb extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Stb::getInstance();
        $this->allowed_fields = array_fill_keys(array('mac', 'ls', 'status', 'additional_services_on'), true);
    }

    public function get(RESTRequest $request){

        $stb_list = $this->manager->getByUids($request->getConvertedIdentifiers());

        return $this->formatList($stb_list);
    }

    public function update(RESTRequest $request){

        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('status', 'additional_services_on', 'ls'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (array_key_exists('status', $data)){
            $data['status'] = intval(!$data['status']);
        }

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        $stb_list = $this->manager->updateByUids($request->getConvertedIdentifiers(), $data);

        if (empty($stb_list)){
            return false;
        }

        return $this->formatList($stb_list);
    }

    public function delete(RESTRequest $request){

        if (count($request->getIdentifiers()) == 0){
            throw new RESTCommandException('Identifier required');
        }

        $stb_list = $request->getConvertedIdentifiers();

        if (count($stb_list) == 0){
            throw new RESTCommandException('STB not found');
        }

        return $this->manager->deleteById($stb_list);
    }

    private function formatList($list){

        $allowed_fields = $this->allowed_fields;

        $list = array_map(function($item) use ($allowed_fields){

            $item = array_intersect_key($item, $allowed_fields);

            $item['status'] = intval(!$item['status']);

            return $item;
        },
        $list
        );

        return $list;
    }
}

?>