<?php

class RESTCommandItvSubscription extends RESTCommand
{
    private $allowed_fields;

    public function __construct(){
        $this->allowed_fields = array_fill_keys(array('ls' , 'mac', 'sub_ch'), true);
    }

    public function get(RESTRequest $request){

        $list = ItvSubscription::getByUids($request->getConvertedIdentifiers());

        return $this->formatList($list);
    }

    public function update(RESTRequest $request){
        
        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('sub_ch'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        //var_dump($data);

        $list = ItvSubscription::updateByUids($request->getConvertedIdentifiers(), $data);

        if (empty($list)){
            return false;
        }

        return $this->formatList($list);
    }

    private function formatList($list){

        $allowed_fields = $this->allowed_fields;

        $list = array_map(function($item) use ($allowed_fields){

            $item = array_intersect_key($item, $allowed_fields);

            return $item;
        },
        $list
        );

        return $list;
    }
}

?>