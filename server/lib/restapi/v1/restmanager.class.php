<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTManager
{

    private static $auth_login;
    private static $auth_password;
    private static $enable_log;

    private function __construct(){}

    public static function handleRequest(){

        ob_start();

        $result = null;

        $response = new RESTResponse();

        try{
            $request  = new RESTRequest();
            $response->setRequest($request);

            if (!empty(self::$auth_login) && (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) ||
                !empty(self::$auth_login) && ($_SERVER['PHP_AUTH_USER'] != self::$auth_login || $_SERVER['PHP_AUTH_PW'] != self::$auth_password)){
                $response->sendAuthRequest();
            }

            $cmd_r  = new RESTCommandResolver();
            $cmd    = $cmd_r->getCommand($request);
            $result = $cmd->execute($request);
            
        }catch (\Exception $e){
            $response->setError($e->getMessage());
        }

        $response->setBody($result);
        $response->send();
    }

    public static function setAuthParams($login, $password){
        self::$auth_login    = $login;
        self::$auth_password = $password;
    }

    public static function enableLogger($value){
        self::$enable_log = (boolean) $value;
    }
}

?>