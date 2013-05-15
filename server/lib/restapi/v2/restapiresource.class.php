<?php

namespace Stalker\Lib\RESTAPI\v2;

abstract class RESTApiResource
{

    protected $nested_params;
    protected $external_params;
    protected $params_map;
    protected $controllers;

    public function __construct(array $nested_params, array $external_params){

        $this->nested_params   = $this->filterParams($nested_params);
        $this->external_params = $external_params;
        $this->controllers = new RESTApiControllersStorage();
    }

    private function filterParams($params){

        if (empty($params)){
            return array();
        }

        if (empty($this->params_map)){
            return array();
        }

        $filtered_params = array();

        foreach ($this->params_map as $param => $mapped_param){
            if (array_key_exists($param, $params)){
                $filtered_params[$mapped_param] = $params[$param];
            }
        }

        return $filtered_params;
    }

    public function supportsAction($action){
        return is_callable(array($this, $action));
    }

    public function execute(RESTApiRequest $request){

        $action = $request->getAction();

        if (empty($this->external_params)){

            if (!$this->supportsAction($action)){
                throw new RESTNotAllowedMethod("Resource does not support method '".$request->getMethod()."'");
            }

            return call_user_func(array($this, $action), $request);
        }

        if ($this->controllers->exist($this->external_params[0])){

            $controller = $this->controllers->getByName($this->external_params[0]);

            if (!$controller->supportsAction($request->getAction())){
                throw new RESTNotAllowedMethod("Controller does not support method '".$request->getMethod()."'");
            }

            return call_user_func(array($controller, $action), $request);
        }

        if (empty($this->document)){
            throw new RESTNotFound("Resource not found");
        }

        if (!empty($this->external_params[1])){

            if ($this->document->controllers->exist($this->external_params[1])){

                $controller = $this->document->controllers->getByName($this->external_params[1]);

                if (!$controller->supportsAction($request->getAction())){
                    throw new RESTNotAllowedMethod("Controller does not support method '".$request->getMethod()."'");
                }

                return call_user_func(array($controller, $action), $request, $this->external_params[0]);
            }else if ($this->document->controllers->exist($this->external_params[count($this->external_params) - 1])){

                $controller_name = $this->external_params[count($this->external_params) - 1];

                $controller = $this->document->controllers->getByName($controller_name);

                if (!$controller->supportsAction($request->getAction())){
                    throw new RESTNotAllowedMethod("Controller does not support method '".$request->getMethod()."'");
                }

                return call_user_func(array($controller, $action), $request, $this->external_params);
            }else{
                throw new RESTNotFound("Resource not found");
            }

        }

        if (!$this->document->supportsAction($request->getAction())){
            throw new RESTNotAllowedMethod("Document does not support method '".$request->getMethod()."'");
        }

        return call_user_func(array($this->document, $action), $request, $this->external_params[0]);
    }
}
