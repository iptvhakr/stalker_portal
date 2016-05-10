<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Mysql;

class RESTCommandItv extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = \Itv::getInstance();
        $this->allowed_fields = array_fill_keys(array('id', 'name', 'number', 'base_ch', 'hd', 'url', 'enable_monitoring', 'descr'), true);
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

    public function create(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }
        
        $data['modified'] = date("Y-m-d H:i:s");
        $data['base_ch'] = 1;
        $data['cmd'] = $url = $data['url'];
        unset ($data['url']);
        
        Mysql::getInstance()->delete('ch_links', array('ch_id' => $data['id']));
        
        $link = array ('ch_id'=>$data['id'], 'url'=>$url, 'status'=>$data['status']);
        Mysql::getInstance()->insert('ch_links', $link);
        return Mysql::getInstance()->insert('itv', $data)->insert_id();
    }
    
    public function delete(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (count($identifiers) != 1){
            throw new RESTCommandException('Identifier count failed');
        }

        Mysql::getInstance()->delete('ch_links', array('ch_id' => $identifiers[0]));
        return Mysql::getInstance()->delete('itv', array('id' => $identifiers[0]));
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