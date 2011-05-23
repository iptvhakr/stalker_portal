<?php

class RESTManager
{

    private function __construct(){}

    public static function handleRequest(){

        $result = null;

        $response = new RESTResponse();

        try{
            $request  = new RESTRequest();

            $cmd_r  = new RESTCommandResolver();
            $cmd    = $cmd_r->getCommand($request);
            $result = $cmd->execute($request);
            
        }catch (Exception $e){
            $response->setError($e->getMessage());
        }

        $response->setBody($result);
        $response->send();
    }
}

?>