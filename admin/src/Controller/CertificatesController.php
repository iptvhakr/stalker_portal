<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterface;
use Symfony\Component\Form\FormError;
use Stalker\Lib\Core\LicenseManager;
use Stalker\Lib\Core\LicenseManagerException;

class CertificatesController extends \Controller\BaseStalkerController {

    public function __construct(Application $app) {

        parent::__construct($app, __CLASS__);

        $this->app['allLicCount'] = $this->getLicenseCountAndCost();

        $this->app['allStatus'] = array(
            array('id' => 1, 'title' => $this->setLocalization('Valid'),            'label' => 'ok'),
            array('id' => 2, 'title' => $this->setLocalization('Requested'),        'label' => 'not_valid'),
            array('id' => 3, 'title' => $this->setLocalization('Disabled'),         'label' => 'disabled'),
            array('id' => 4, 'title' => $this->setLocalization('Expired'),          'label' => 'expired'),
            array('id' => 5, 'title' => $this->setLocalization('Wrong signature'),  'label' => 'wrong_signature'),
            array('id' => 6, 'title' => $this->setLocalization('Undefined'),        'label' => 'undefined'),
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

        $data_set = $this->current_list_json(TRUE);

        $this->app['data_set'] = $data_set['data'];
        $this->app['lic_count_set'] = array_combine($this->getFieldFromArray($this->app['allLicCount'], 'count'), $this->getFieldFromArray($this->app['allLicCount'], 'title'));
        $this->app['status_set'] = array_combine($this->getFieldFromArray($this->app['allStatus'], 'id'), $this->getFieldFromArray($this->app['allStatus'], 'title'));

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
        $allLicenseCountAndCost = array_combine($this->getFieldFromArray($this->app['allLicCount'], 'count'), array_values($this->app['allLicCount']));
        while(list($id, $row) = each($allLicenseCountAndCost)) {
            $allLicenseCountAndCost[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }
        $this->app['allLicenseCountAndCost'] = $allLicenseCountAndCost;
        $this->app['allLicensePeriodAndDiscount'] = array_combine($this->getFieldFromArray($this->getLicensePeriodAndDiscount(), 'count'), array_values($this->getLicensePeriodAndDiscount()));

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of certificates'), $this->app['controller_alias'] . '/current');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Certificate request'));

        return $this->app['twig']->render($this->getTemplateName(__METHOD__));
    }

    public function certificate_detail(){
        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $id = !empty($this->data['id']) ? (int) $this->data['id'] : FALSE;
        if (!$id) {
            return $this->app->redirect('current');
        }

        try{
            $sert = new LicenseManager();
            $lics = $sert->getLicense($id);
            $data = array(
                'id' => $lics->getId(),
                'company' => $lics->getCompany(),
                'contact_name' => $lics->getContactName(),
                'phone' => $lics->getPhone(),
                'contact_address' => $lics->getContactAddress(),
                'country' => $lics->getCountry(),
                'quantity' => $lics->getQuantity(),
                'date_begin' => $lics->getDateFrom(),
                'date_to' => $lics->getDateTo(),
                'period' => (int)date('Y', $lics->getDateTo()) - (int)date('Y', $lics->getDateFrom()),
                'expire' => \DateTime::createFromFormat('U', $lics->getDateTo())->diff(\DateTime::createFromFormat('U', $lics->getDateFrom()))->format('%a'),
                'status' => $lics->getStatusStr(),
                'is_show' => TRUE
            );

            $form = $this->buildCertificateRequestForm($data, TRUE);
            if ((int)$data['expire'] <= 30){
                $form->get('date_to')->addError(new FormError($this->setLocalization('Validity of the certificate expires after {expire} days', '', $data['expire'], array('{expire}' => $data['expire']))));
            }
        } catch (\Exception $e){
            $data = array();
            $form = $this->buildCertificateRequestForm($data, TRUE);
            $form->addError(new FormError($e->getMessage()));
        }
        $this->app['form'] = $form->createView();

        $allLicenseCountAndCost = array_combine($this->getFieldFromArray($this->app['allLicCount'], 'count'), array_values($this->app['allLicCount']));
        while(list($id, $row) = each($allLicenseCountAndCost)) {
            $allLicenseCountAndCost[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }
        $this->app['allLicenseCountAndCost'] = $allLicenseCountAndCost;
        $this->app['allLicensePeriodAndDiscount'] = array_combine($this->getFieldFromArray($this->getLicensePeriodAndDiscount(), 'count'), array_values($this->getLicensePeriodAndDiscount()));

        $this->app['breadcrumbs']->addItem($this->setLocalization('List of certificates'), $this->app['controller_alias'] . '/current');
        $this->app['breadcrumbs']->addItem($this->setLocalization('Certificate detail'));

        return $this->app['twig']->render('Certificates_certificate_request.twig');
    }
    //----------------------- ajax method --------------------------------------

    public function current_list_json($local_use = FALSE){
        if ((!$this->isAjax || $this->method != 'POST') && !$local_use) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data['action'] = 'reDrawDataTable';
        $data['data'] = array();
        $error = '';

        try{
            $status_label = array_combine($this->getFieldFromArray($this->app['allStatus'], 'label'), $this->getFieldFromArray($this->app['allStatus'], 'id'));

            $sert = new LicenseManager();
            $lics_arr = $sert->getLicenses();

            $expires_30_days = 60*60*24*30;

            while(list($num, $lics) = each($lics_arr)){
                $error = $lics->getError();
                if (empty($error)) {
                    $data['data'][] = array(
                        'id'                => $lics->getId(),
                        'lic_count'         => $lics->getQuantity(),
                        'cert_begin'        => $lics->getDateFrom(),
                        'cert_end'          => $lics->getDateTo(),
                        'status'            => $status_label[$lics->getStatusStr()],
                        'status_bool'       => $lics->getStatus(),
                        'awaiting'          => $lics->getStatus() && ($lics->getHash() !== $lics->getServerHash()),
                        'expires_30_days'   => ($lics->getDateTo() - $lics->getDateFrom()) <= $expires_30_days
                    );
                }
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $response = $this->generateAjaxResponse($data, $error);

        return $local_use ? $response: new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    public function certificate_install(){
        if (!$this->isAjax || $this->method != 'POST' || empty($this->postData['id'])) {
            $this->app->abort(404, $this->setLocalization('Page not found'));
        }

        if ($no_auth = $this->checkAuth()) {
            return $no_auth;
        }

        $data = array();
        $data['action'] = 'updateTableData';
        $error = '';

        try{
            $sert = new LicenseManager();
            $sert->updateLicense((int) $this->postData['id']);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $response = $this->generateAjaxResponse($data, $error);

        return new Response(json_encode($response), (empty($error) ? 200 : 500));
    }

    //------------------------ service method ----------------------------------

    private function buildCertificateRequestForm(&$data = array(), $show = FALSE) {

        $this->app['is_show'] = $show;

        $builder = $this->app['form.factory'];
        $quantity = $this->app['allLicCount'];
        while(list($id, $row) = each($quantity)) {
            $quantity[$id]['title'] .= ' ' . $this->setLocalization($id == 1 ? 'device': 'devices');
        }

        $period = $this->getLicensePeriodAndDiscount();

        $countries_name = $this->app['language'] == 'ru' ? 'name' : 'name_en';

        $countries = $this->db->getAllFromTable('countries', $countries_name);
        $countries = array_combine($this->getFieldFromArray($countries, 'iso2'), $this->getFieldFromArray($countries, $countries_name));

        $form = $builder->createBuilder('form', $data)
            ->add('id', ($show? 'text': 'hidden'))
            ->add('company', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank()
                    ),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'required' => TRUE)
            )
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
            ->add('phone', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Regex(array(
                            'pattern' => '/^[\d\+\-]+$/',
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
            ->add('country', 'choice', array(
                    'choices' => array(''=>'') + $countries,
                    'required' => TRUE,
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'data' => (empty($data['country']) ? '': $data['country']),
                )
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
            ->add('server_host', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank()
                    ),
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'required' => TRUE)
            )
            ->add('save', 'submit');
            $status_ids = array(''=>'') + array_combine($this->getFieldFromArray($this->app['allStatus'], 'label'), $this->getFieldFromArray($this->app['allStatus'], 'title'));
            $form->add('date_to', $show ? 'date': 'hidden', $show ? array(
                    'attr' => array('readonly' => $show, 'disabled' => $show),
                    'input'  => 'timestamp',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'empty_value' => time()
                ) : array()
            )
            ->add('status', $show? 'choice' : 'hidden', $show ? array(
                    'choices' => $status_ids,
                    'attr' => array('disabled' => $show)
                ) : array()
            )
            ->add('expire', 'hidden');
        return $form->getForm();
    }

    private function saveCertificateRequestData(&$form) {

        if (!empty($this->method) && $this->method == 'POST') {
            $form->handleRequest($this->request);
            $data = $form->getData();

            if ($form->isValid()) {
                $data['date_from'] = \DateTime::createFromFormat('d.m.Y', $data['form']['date_begin'])->getTimestamp();
                $data['date_to'] = \DateTime::createFromFormat('d.m.Y', $data['form']['date_begin'])->add(new \DateInterval("P{$data['period']}Y"))->getTimestamp();
                $sert = new LicenseManager();

                try{

                    return $sert->requestLicense(
                        (string) 	$data['contact_name'],
                        (string) 	$data['contact_address'],
                        (int) 	    $data['quantity'],
                        (int) 	    $data['date_from'],
                        (int) 	    $data['date_to'],
                        (string)    $data['server_host'],
                        (string) 	$data['country'],
                        (string) 	$data['company'],
                        (string) 	$data['phone']
                    );

                } catch(LicenseManagerException $e) {
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
        $sert = new LicenseManager();
        $lics = $sert->getPrices();
        $return = array();
        while(list($count, $cost) = each($lics)){
            $return[$count] = array('id' => $count, 'title' => number_format($count, 0, '.', ' '), 'count' => $count, 'cost' => $cost);
        }
        return $return;
        /*return array(
            array('id' => 1, 'title' => '1', 'count' => 1, 'cost' => 0),
            array('id' => 2, 'title' => '50', 'count' => 50, 'cost' => 100),
            array('id' => 3, 'title' => '100', 'count' => 100, 'cost' => 200),
            array('id' => 4, 'title' => '500', 'count' => 500, 'cost' => 1000),
            array('id' => 5, 'title' => '1 000', 'count' => 1000, 'cost' => 2000),
            array('id' => 6, 'title' => '2 000', 'count' => 2000, 'cost' => 4000),
            array('id' => 7, 'title' => '5 000', 'count' => 5000, 'cost' => 10000),
            array('id' => 8, 'title' => '10 000', 'count' => 10000, 'cost' => 20000)
        );*/
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