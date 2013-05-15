<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiControllersStorage
{
    private $controllers;

    public function add(RESTApiController $controller){

        $name = $controller->getName();

        if (empty($this->controllers[$name])){
            $this->controllers[$name] = $controller;
        }
    }

    public function getAll(){
        return $this->controllers;
    }

    /**
     * @param string $name
     * @return RESTApiController
     * @throws \Exception
     */
    public function getByName($name){

        if (empty($this->controllers[$name])){
            throw new \Exception("Controller not found");
        }

        return $this->controllers[$name];
    }

    public function exist($name){
        return !empty($this->controllers[$name]);
    }

}