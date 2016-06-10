<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;


class RESTCommandEpg extends RESTCommand
{

    public function __construct(){
    }

    public function get(RESTRequest $request){
        return Mysql::getInstance()->select('id, uri, etag, updated, id_prefix, status')->from('epg_setting')->get()->all();
    }
    
    public function create(RESTRequest $request){

        $data = $request->getData();

        if (empty($data)){
            throw new RESTCommandException('HTTP POST data is empty');
        }
        
        $res = Mysql::getInstance()->insert('epg_setting', $data)->insert_id();
        //$epg = new Epg();
        //$error = $epg->updateEpg(true);
        return $res;
    }
    
    public function delete(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (count($identifiers) != 1){
            throw new RESTCommandException('Identifier count failed');
        }

        return Mysql::getInstance()->delete('epg_setting', array('id' => $identifiers[0]));
    }

}

?>