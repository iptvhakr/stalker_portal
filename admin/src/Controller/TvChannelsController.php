<?php

namespace Controller;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class TvChannelsController{

    public function index(Application $app)
    {
        return $app['twig']->render('tv_channels.twig');
    }
}