<?php

namespace Stalker\Lib\RESTAPI\v2;
use Stalker\Lib\OAuth;

class RESTApiManager
{
    private $auth_handler;

    public function __construct(OAuth\AccessHandler $handler){
        $this->auth_handler = $handler;
    }

    public function handleRequest(){

        $result = null;
        $response = new RESTApiResponse();

        try{
            $request = new RESTApiRequest();
            $request->init();

            $response->setRequest($request);

            try{
                $access = OAuth\AuthAccessHandler::getAccessSchema($request);
                $access->checkRequest();
            }catch(OAuth\AuthUnauthorized $access_exception){
                throw new RESTUnauthorized($access_exception->getMessage());
            }catch(OAuth\AuthBadRequest $access_exception){
                throw new RESTBadRequest($access_exception->getMessage());
            }catch(OAuth\AuthForbidden $access_exception){
                throw new RESTForbidden($access_exception->getMessage());
            }

            $session = $access->getSession();

            \User::getInstance($session['uid']);

            $request->setUserId($session['uid']);

            $target_resolver = new RESTApiTargetResolver();
            $target = $target_resolver->getTarget($request);

            $result = $target->execute($request);

        }catch(\Exception $e){
            $response->setError($e);
        }

        $response->setBody($result);

        $response->send();
    }
}
