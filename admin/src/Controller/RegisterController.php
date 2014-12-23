<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class RegisterController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app, __CLASS__);
        $this->logoHost = $this->baseHost . "/stalker_portal/misc/logos";
        $this->logoDir = str_replace('/admin', '', $this->baseDir) . "/misc/logos";
        $this->app['error_local'] = array();
        $this->app['baseHost'] = $this->baseHost;
    }

    public function index(Application $app) {
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }
}

