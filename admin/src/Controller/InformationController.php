<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class InformationController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {
        parent::__construct($app);
    }

    public function index(Application $app) {
        return $app['twig']->render($this->getTemplateName(__METHOD__));
    }

}
