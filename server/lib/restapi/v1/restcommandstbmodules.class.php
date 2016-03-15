<?php

namespace Stalker\Lib\RESTAPI\v1;

use Stalker\Lib\Core\Stb;

class RESTCommandStbModules extends RESTCommand
{

    public function get(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        if (empty($stb_list)){
            throw new RESTCommandException('Empty stb list');
        }

        if (count($stb_list) != 1){
            throw new RESTCommandException('Only one identifier allowed');
        }

        $uid = $stb_list[0];

        return array(
            'disabled'   => Stb::getDisabledModulesByUid($uid),
            'restricted' => Stb::getRestrictedModulesByUid($uid)
        );
    }

    public function update(RESTRequest $request){

        $stb_list = $request->getConvertedIdentifiers();

        if (empty($stb_list)){
            throw new RESTCommandException('Empty stb list');
        }

        /*if (count($stb_list) != 1){
            throw new RESTCommandException('Only one identifier allowed');
        }*/

        $uids = $stb_list;

        $data = $request->getPut();

        if (empty($data)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        if (!array_key_exists('disabled', $data) && !array_key_exists('restricted', $data)){
            throw new RESTCommandException('Update data is empty');
        }

        if (array_key_exists('disabled', $data)){
            foreach ($uids as $uid){
                Stb::setDisabledModulesByUid($uid, $data['disabled']);
            }
        }

        if (array_key_exists('restricted', $data)){
            foreach ($uids as $uid){
                Stb::setRestrictedModulesByUid($uid, $data['restricted']);
            }
        }

        return array(
            'disabled'   => Stb::getDisabledModulesByUid($uids[0]),
            'restricted' => Stb::getRestrictedModulesByUid($uids[0])
        );
    }
}

?>