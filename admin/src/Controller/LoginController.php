<?php

namespace Controller;

use Silex\Application;

class LoginController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
    }

    public function index() {
        if ($this->method == 'POST' && isset($this->postData['username']) && isset($this->postData['password'])) {
            if (\Admin::checkAuthorization($this->postData['username'], $this->postData['password'])){
                return $this->app->redirect($this->workURL);
            }
        }
        $error = array('user_undefined' => $this->setLocalization('User is undefined'));
        $this->app['error_local'] = $error;
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
}