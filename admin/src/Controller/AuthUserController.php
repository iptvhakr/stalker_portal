<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class AuthUserController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
    }

    // ------------------- action method ---------------------------------------

    public function index() {
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_profile() {

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_messages() {

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_tasks() {

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_settings() {

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_logout() {
        
        $this->app['request']->getSession()->clear();
        session_destroy();
        return $this->app->redirect( $this->workURL . '/login', 302);
    }

}
