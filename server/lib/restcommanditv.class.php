<?php

class RESTCommandItv extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Itv::getInstance();
        $this->allowed_fields = array_fill_keys(array('id', 'name', 'number', 'base_ch', 'hd', 'url', 'enable_monitoring'), true);
    }

    public function get(RESTRequest $request){

        $itv_list = $this->manager->getByIds($request->getIdentifiers());

        $allowed_fields = $this->allowed_fields;

        $itv_list = array_map(function($item) use ($allowed_fields){

                $item['url'] = $item['monitoring_url'];

                $item = array_intersect_key($item, $allowed_fields);

            return $item;
        },
        $itv_list
        );

        return $itv_list;
    }

    public function update(RESTRequest $request){

        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('monitoring_status'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        $ids = $request->getIdentifiers();

        if (empty($ids)){
            throw new RESTCommandException('Empty channel id');
        }

        $channel_id = intval($ids[0]);

        return Mysql::getInstance()->update('itv', $data, array('id' => $channel_id));
    }
}

?>