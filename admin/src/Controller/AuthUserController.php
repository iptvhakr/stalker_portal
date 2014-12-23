<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class AuthUserController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app);
    }

    public function index(Application $app) {
        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_profile(Application $app) {

        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_messages(Application $app) {

        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_tasks(Application $app) {

        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_settings(Application $app) {

        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function auth_user_logout(Application $app) {
        
        $this->app['request']->getSession()->remove('login');
        $this->app['request']->getSession()->save('pass');
        return $this->app->redirect( $this->workURL . '/login', 302);
    }

}
