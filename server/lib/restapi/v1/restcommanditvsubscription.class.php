<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Stb;

class RESTCommandItvSubscription extends RESTCommand
{
    private $allowed_fields;

    public function __construct(){
        $this->allowed_fields = array_fill_keys(array('ls' , 'mac', 'sub_ch', 'additional_services_on'), true);
    }

    public function get(RESTRequest $request){

        $list = \ItvSubscription::getByUids($request->getConvertedIdentifiers());

        return $this->formatList($list);
    }

    public function update(RESTRequest $request){
        
        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('sub_ch', 'additional_services_on'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        $stb_data = array_intersect_key($put, array('additional_services_on' => true));

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        unset($data['additional_services_on']);

        if (!empty($stb_data)){
            //$stb = Stb::getInstance();
            //$stb->setParam('additional_services_on', intval($stb_data['additional_services_on']));

            $uids = $request->getConvertedIdentifiers();

            foreach ($uids as $uid){
                Stb::setAdditionServicesById($uid, intval($stb_data['additional_services_on']));
            }
        }

        //var_dump($stb_data);

        if (!empty($data)){

            $list = \ItvSubscription::updateByUids($request->getConvertedIdentifiers(), $data);

            if (empty($list)){
                return false;
            }
        }

        return $this->formatList(\ItvSubscription::getByUids($request->getConvertedIdentifiers()));
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