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
        $error = FALSE;
        if ($this->method == 'POST' && isset($this->postData['username']) && isset($this->postData['password'])) {
            if (\Admin::checkAuthorization($this->postData['username'], $this->postData['password'])){
                return $this->app->redirect(!empty($this->redirect)? $this->redirect: $this->workURL);
            } else {
                $error = $this->setLocalization('Incorrect Username or Password');
            }
        }
        $this->app['error_local'] = $error;
        
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    
}