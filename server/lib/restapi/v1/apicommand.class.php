<?php

namespace Stalker\Lib\RESTAPI\v1;

abstract class APICommand
{

    public function execute(APIRequest $request){

        return $this->doExecute($request);
    }

    abstract function doExecute(APIRequest $request);
}

?>