<?php

namespace Controller;

use Silex\Application;

class LogoutController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
    }

    public function index() {
        $this->app['request']->getSession()->remove('login');
        $this->app['request']->getSession()->save('pass');
        return $this->app->redirect($this->workURL);
    }
    
}