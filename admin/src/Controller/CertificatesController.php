<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Symfony\Component\Form\FormError;

class CertificatesController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);

        $this->app['allLicCount'] = $this->getLicenseCountAndCost();

        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Valid')),
            array('id' => 2, 'title' => $this->setLocalization('Not valid')),
            array('id' => 3, 'title' => $this->setLocalization('Requested')),
            array('id' => 4, 'title' => $this->setLocalization('Awaiting'))
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

        if (empty($this->data['filters'])) {
            $this->data['filters'] = array();
        }

        $this->app['filters'] = $this->data['filters'];

        $attribute = $this->getDropdownAttribute();
        $this->checkDropdownAttribute($attribute);
        $this->app['dropdownAttribute'] = $attribute;

        $data_set = array();
        $data_set_row = array(
            'id' => 0,
            'lic_count' => 0,
            'cert_begin' => 0,
            'cert_end' => 0,
            'status' => 0);
        $status_ids = $this->getFieldFromArray($this->app['allStatus'], 'id');
        $status_ids = array_combine(array_values($status_ids), array_values($status_ids));
        $lic_count_ids = $this->getFieldFromArray($this->app['allLicCount'], 'count');
        $lic_count_ids = array_combine(array_values($lic_count_ids), array_values($lic_count_ids));

        $sert = new \LicenseManager();
        $lics_arr = $sert->getLicenses();

        while(list($num, $lics) = each($lics_arr)){
            $error = $lics->getError();
            if (empty($error)) {
                $data_set_row['id'] = $num; //@todo get real id
                $data_set_row['lic_count'] = $lics->getQuantity();
                $data_set_row['cert_begin'] = $lics->getDateFrom();
                $data_set_row['cert_end'] = $lics->getDateTo();
                $data_set_row['status'] = array_rand($status_ids); //@todo get real status
                $data_set[] = $data_set_row;
            }
        }

        $this->app['data_set'] = $data_set;
        $this->app['lic_count_set'] = array_combine($lic_count_ids, $this->getFieldFromArray($this->app['allLicCount'], 'title'));
        $this->app['status_set'] = array_combine($status_ids, $this->getFieldFromArray($this->app['allStatus'], 'title'));

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

    public function certificate_request(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = !empty($this->postData) ? $this->postData: array();

        $form = $this->buildCertificateRequestForm($data, !empty($data['form']['is_show']) );

        if ($this->saveCertificateRequestData($form)) {
            return $this->app->redirect('current');
        }
        $this->app['form'] = $form->createView();
        $this->app['certificateRequestEdit'] = FALSE;
        $allLicenseCountAndCost = array_combine($this->getFieldFromArray($this->getLicenseCountAndCost(), 'count'), array_values($this->getLicenseCountAndCost()));
        while(list($id, $row) = each($allLicenseCountAndCost)) {
            $allLicenseCountAndCost[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }
        $this->app['allLicenseCountAndCost'] = $allLicenseCountAndCost;
        $this->app['allLicensePeriodAndDiscount'] = array_combine($this->getFieldFromArray($this->getLicensePeriodAndDiscount(), 'count'), array_values($this->getLicensePeriodAndDiscount()));

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of certificates'), $this->app['controller_alias'] . '/current');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Certificate Request'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function certificate_detail(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $id = !empty($this->data['id']) ? (int) $this->data['id'] : FALSE;
        if (!$id && 0) { //@todo remove && 0 for real check
            return $this->app->redirect('current');
        }

        try{
            $sert = new \LicenseManager();
            $lics = $sert->getLicenses(); //@todo get real license $sert->getLicense($id);
            $data = array(
                'contact_name' => $lics[0]->getContactName(),
                'contact_address' => $lics[0]->getContactAddress(),
                'quantity' => $lics[0]->getQuantity(),
                'date_begin' => $lics[0]->getDateFrom(),
                'date_to' => $lics[0]->getDateTo(),
                'period' => (int)date('Y', $lics[0]->getDateTo()) - (int)date('Y', $lics[0]->getDateFrom()),
                'expire' => \DateTime::createFromFormat('U', $lics[0]->getDateTo())->diff(\DateTime::createFromFormat('U', $lics[0]->getDateFrom()))->format('%a'),
                'is_show' => TRUE
            );

            $form = $this->buildCertificateRequestForm($data, TRUE);
            if ((int)$data['expire'] <= 30){
                $form->get('expire')->addError(new FormError($this->setLocalization('Validity of the certificate expires after {expire} days', $data['expire'], array('{expire}' => $data['expire']))));
            }
        } catch (\Exception $e){
            $data = array();
            $form = $this->buildCertificateRequestForm($data, TRUE);
            $form->addError(new FormError($e->getMessage()));
        }
        $this->app['form'] = $form->createView();

        $allLicenseCountAndCost = array_combine($this->getFieldFromArray($this->getLicenseCountAndCost(), 'count'), array_values($this->getLicenseCountAndCost()));
        while(list($id, $row) = each($allLicenseCountAndCost)) {
            $allLicenseCountAndCost[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }
        $this->app['allLicenseCountAndCost'] = $allLicenseCountAndCost;
        $this->app['allLicensePeriodAndDiscount'] = array_combine($this->getFieldFromArray($this->getLicensePeriodAndDiscount(), 'count'), array_values($this->getLicensePeriodAndDiscount()));

        return $this->app['twig']->render('Certificates_certificate_request.twig');
    }
    //----------------------- ajax method --------------------------------------

    //------------------------ service method ----------------------------------

    private function buildCertificateRequestForm(&$data = array(), $show = FALSE) {

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];
        $quantity = $this->getLicenseCountAndCost();
        while(list($id, $row) = each($quantity)) {
            $quantity[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }

        $period = $this->getLicensePeriodAndDiscount();

        $form = $builder->createBuilder('form', $data)
            ->add('id', ($show? 'text': 'hidden'))
            ->add('stalker_id', ($show? 'text': 'hidden'))
            ->add('contact_name', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Regex(array(
                            'pattern' => '/^[^\d]+$/',
                        ))
                    ),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'required' => TRUE)
            )
            ->add('contact_address', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank()
                    ),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'required' => TRUE)
            )
            ->add('quantity', 'choice', array(
                    'choices' => array(''=>'') + array_combine($this->getFieldFromArray($quantity, 'count'), $this->getFieldFromArray($quantity, 'title')),
                    'required' => TRUE,
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'data' => (empty($data['quantity']) ? 0: $data['quantity']),
                )
            )
            ->add('period', 'choice', array(
                    'choices' => array(''=>'') + array_combine($this->getFieldFromArray($period, 'count'), $this->getFieldFromArray($period, 'title')),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'required' => TRUE,
                    'data' => (empty($data['period']) ? 0: $data['period']),
                )
            )
            ->add('date_begin', 'date', array(
                    'constraints' => array(
                        new Assert\NotBlank()
                    ),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'input'  => 'timestamp',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'empty_value' => time(),
                    'required' => TRUE
                )
            )
            ->add('save', 'submit');
        if ($show) {
            $status_ids = $this->getFieldFromArray($this->app['allStatus'], 'id');
            $status_ids = array(''=>'') + array_combine(array_values($status_ids), $this->getFieldFromArray($this->app['allStatus'], 'title'));
            $form->add('date_to', 'date', array(
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'input'  => 'timestamp',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'empty_value' => time()
                )
            )
            ->add('status', 'choice', array(
                    'choices' => $status_ids,
                    'attr' => array('disabled' => $show)
                )
            )->add('expire', 'hidden');
        }

//                ->add('reset', 'reset');
        return $form->getForm();
    }

    private function saveCertificateRequestData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                $data['date_from'] = \DateTime::createFromFormat('d.m.Y', $data['form']['date_begin'])->getTimestamp();
                $data['date_to'] = \DateTime::createFromFormat('d.m.Y', $data['form']['date_begin'])->add(new \DateInterval("P{$data['period']}Y"))->getTimestamp();
                $sert = new \LicenseManager();
                try{
                    $result = call_user_func(array($sert, 'requestLicense'), array(
                        (string) 	$data['contact_name'],
                        (string) 	$data['contact_address'],
                        (int) 	$data['quantity'],
                        (int) 	$data['date_from'],
                        (int) 	$data['date_to']
                    ));
                    return $result;
                } catch(\LicenseManagerException $e) {
                    $form->addError(new FormError($e->getMessage()));
                    return FALSE;
                }
            }
        }
        return FALSE;
    }

    private function getDropdownAttribute(){
        $attribute = array(
            array('name' => 'id',           'title' => $this->setLocalization('ID'),                            'checked' => TRUE),
            array('name' => 'lic_count',    'title' => $this->setLocalization('License count'),                 'checked' => TRUE),
            array('name' => 'cert_begin',   'title' => $this->setLocalization('Begin of certificate validity'), 'checked' => TRUE),
            array('name' => 'cert_end',     'title' => $this->setLocalization('End of certificate validity'),   'checked' => TRUE),
            array('name' => 'status',       'title' => $this->setLocalization('Status'),                        'checked' => TRUE)/*,
            array('name' => 'operations',   'title' => $this->setLocalization('Operations'),        'checked' => TRUE)*/
        );
        return $attribute;
    }

    private function getLicenseCountAndCost(){
        return array(
            array('id' => 1, 'title' => '1', 'count' => 1, 'cost' => 0),
            array('id' => 2, 'title' => '50', 'count' => 50, 'cost' => 100),
            array('id' => 3, 'title' => '100', 'count' => 100, 'cost' => 200),
            array('id' => 4, 'title' => '500', 'count' => 500, 'cost' => 1000),
            array('id' => 5, 'title' => '1 000', 'count' => 1000, 'cost' => 2000),
            array('id' => 6, 'title' => '2 000', 'count' => 2000, 'cost' => 4000),
            array('id' => 7, 'title' => '5 000', 'count' => 5000, 'cost' => 10000),
            array('id' => 8, 'title' => '10 000', 'count' => 10000, 'cost' => 20000)
        );
    }

    private function getLicensePeriodAndDiscount(){
        return array(
            array('id' => 1, 'title' => '1 ' . $this->setLocalization('year'), 'count' => 1, 'discount' => 0),
            array('id' => 2, 'title' => '2 ' . $this->setLocalization('years'), 'count' => 2, 'discount' => 1),
            array('id' => 3, 'title' => '3 ' . $this->setLocalization('years'), 'count' => 3, 'discount' => 2),
            array('id' => 4, 'title' => '4 ' . $this->setLocalization('years'), 'count' => 4, 'discount' => 3),
            array('id' => 5, 'title' => '5 ' . $this->setLocalization('years'), 'count' => 5, 'discount' => 4)
        );
    }
}