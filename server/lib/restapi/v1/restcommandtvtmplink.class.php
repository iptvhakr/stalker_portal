<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandTvTmpLink extends RESTCommand
{

    public function get(RESTRequest $request){

        $ids = $request->getIdentifiers();

        if (empty($ids[0])){
            throw new \ErrorException('Empty token');
        }

        return \Itv::checkTemporaryLink($ids[0]);
    }
}

?>
