<?php

class RESTCommand extends APICommand
{

    public function doExecute(APIRequest $request){

        $action = $request->getAction();

        if (!is_callable(array($this, $action))){
            throw new RESTCommandException('Resource "'.$request->getResource().'" does not support action "'.$action.'"');
        }

        return call_user_func(array($this, $action), $request);
    }
}

class RESTCommandException extends Exception {}

?>