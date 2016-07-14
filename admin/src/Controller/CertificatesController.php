<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;

class CertificatesController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);

        $this->app['allLicCount'] = array(
            array('id' => 1, 'title' => '50'),
            array('id' => 2, 'title' => '100'),
            array('id' => 3, 'title' => '500'),
            array('id' => 4, 'title' => '1 000'),
            array('id' => 4, 'title' => '2 000'),
            array('id' => 4, 'title' => '5 000'),
            array('id' => 4, 'title' => '10 000')
        );

    }

    // ------------------- action method ---------------------------------------

    public function index() {

        if (empty($this->app['action_alias'])) {
            return $this->app->redirect($this->app['controller_alias'] . '/current');
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }
        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function current() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Valid')),
            array('id' => 2, 'title' => $this->setLocalization('Not valid')),
            array('id' => 3, 'title' => $this->setLocalization('Requested')),
            array('id' => 4, 'title' => $this->setLocalization('Awaiting'))
        );

        if (empty($this->data['filters'])) {
            $this->data['filters'] = array();
        }

        $this->app['filters'] = $this->data['filters'];

        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function requests() {

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }
    //----------------------- ajax method --------------------------------------

    //------------------------ service method ----------------------------------

    private function getDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),                'checked' => TRUE),
            array('name' => 'lic_count',    'title' => $this->setLocalization('License count'),       'checked' => TRUE),
            array('name' => 'cert_begin',   'title' => $this->setLocalization('Begin of certificate validity'),               'checked' => TRUE),
            array('name' => 'cert_end',     'title' => $this->setLocalization('End of certificate validity'), 'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization('Status'),             'checked' => TRUE)/*,
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),        'checked' => TRUE)*/
        );
        return $attribute;
    }

}