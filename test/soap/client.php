<?php


class SoapApiTest extends PHPUnit_Framework_TestCase
{
    private $test_client;
    private $test_account = array(
        'login'          => 'soap_login',
        'password'       => 'soap_pass',
        'full_name'      => 'soap full name',
        'account_number' => '1',
        'tariff_plan'    => '00',
        'status'         => '1',
        'stb_mac'        => 'FF:FF:FF:FF:FF:FF'
    );

    private $updated_account = array(
        'login'          => 'soap_login',
        'full_name'      => 'new soap full name',
        'account_number' => '2',
        'tariff_plan'    => '2',
        'status'         => '0',
        'stb_mac'        => 'FF:FF:FF:FF:FF:00'
    );

    public function setUp(){
        $this->test_client = new StalkerSoapClient();
    }

    public function tearDown(){

    }

    /*public function testCut(){
        $result = $this->test_client->UpdateAccount(array('login' => 1564, 'status' => 1));
    }*/

    public function testCreateAccount(){
        $result = $this->test_client->CreateAccount($this->test_account);
        $this->assertTrue($result);

        $this->checkCreatedAccountInfo();
    }

    public function checkCreatedAccountInfo(){
        $info = (array) $this->test_client->GetAccountInfo(array('stb_mac' => $this->test_account['stb_mac']));
        //var_dump($info, $this->test_account, $info==$this->test_account);
        $this->assertEquals($info['login'], $this->test_account['login']);
        $this->assertEquals($info['full_name'], $this->test_account['full_name']);
        $this->assertEquals($info['account_number'], $this->test_account['account_number']);
        $this->assertEquals($info['stb_mac'], $this->test_account['stb_mac']);
        $this->assertEquals($info['status'], $this->test_account['status']);
        $this->assertArrayHasKey('tariff_plan', $info);
        $this->assertArrayHasKey('stb_sn', $info);
        $this->assertArrayHasKey('stb_type', $info);
    }

    public function testUpdateAccount(){
        $result = $this->test_client->UpdateAccount($this->updated_account);
        $this->assertTrue($result);
    }

    public function testUpdatedAccountInfo(){
        $info = (array) $this->test_client->GetAccountInfo(array('login' => $this->updated_account['login']));
        $this->assertEquals($info['full_name'], $this->updated_account['full_name']);
        $this->assertEquals($info['account_number'], $this->updated_account['account_number']);
        $this->assertEquals($info['status'], $this->updated_account['status']);
        $this->assertEquals($info['stb_mac'], $this->updated_account['stb_mac']);
        $this->assertArrayHasKey('tariff_plan', $info);
        $this->assertArrayHasKey('stb_sn', $info);
        $this->assertArrayHasKey('stb_type', $info);
    }

    public function testUpdateSubscription(){

        $result = $this->test_client->UpdateAccountOptionalSubscription(
            array('login' => $this->updated_account['login']),
            array('subscribe' => array('radio_7'))
        );

        $this->assertTrue($result);

        $info = (array) $this->test_client->GetAccountInfo(array('login' => $this->updated_account['login']));
        $this->assertEquals($info['subscribed'], array('radio_7'));

        $result = $this->test_client->UpdateAccountOptionalSubscription(
            array('login' => $this->updated_account['login']),
            array('unsubscribe' => array('radio_7'))
        );

        $this->assertTrue($result);

        $info = (array) $this->test_client->GetAccountInfo(array('login' => $this->updated_account['login']));
        $this->assertEquals($info['subscribed'], array());
    }

    public function testDeleteAccount(){
        $result = $this->test_client->DeleteAccount(array('login' => $this->test_account['login']));
        $this->assertTrue($result);
    }

    /**
     * @expectedException SoapFault
     */
    public function testDeletedAccountInfo(){
        $this->test_client->GetAccountInfo(array('stb_mac' => $this->test_account['stb_mac']));
    }

    public function testCreateOrUpdate(){
        $result = $this->test_client->CreateOrUpdateAccount($this->test_account);
        $this->assertTrue($result);

        $this->checkCreatedAccountInfo();
        $this->testDeleteAccount();
    }
}


require "../../server/common.php";

//StalkerSoapClient::$_WsdlUri = Config::get('wsdl_uri');
StalkerSoapClient::$_WsdlUri = 'http://192.168.1.71/stalker_portal/api/soap.php?wsdl';

/**
 * @service StalkerSoapClient
 */
class StalkerSoapClient
{
    /**
     * The WSDL URI
     *
     * @var string
     */
    public static $_WsdlUri;
    /**
     * The PHP SoapClient object
     *
     * @var object
     */
    public static $_Server = null;

    /**
     * Send a SOAP request to the server
     *
     * @param string $method The method name
     * @param array $param The parameters
     * @return mixed The server response
     */
    public static function _Call($method, $param)
    {
        if (is_null(self::$_Server)){
            ini_set('soap.wsdl_cache_enabled', '0');
            self::$_Server = new \SoapClient(self::$_WsdlUri, array('trace' => true, 'cache_wsdl' => WSDL_CACHE_NONE));
        }

        return self::$_Server->__soapCall($method, $param);
    }

    /**
     * If there is an account with a specified login, it updates its, otherwise - create new account.
     *
     * @param Account $params
     * @return boolean
     */
    public function CreateOrUpdateAccount($params)
    {
        return self::_Call('CreateOrUpdateAccount', Array(
            $params
        ));
    }

    /**
     * Create new account.
     *
     * @param Account $params
     * @return boolean
     */
    public function CreateAccount($params)
    {
        return self::_Call('CreateAccount', Array(
            $params
        ));
    }

    /**
     * Update account by specified login param.
     *
     * @param Account $params
     * @return boolean
     */
    public function UpdateAccount($params)
    {
        return self::_Call('UpdateAccount', Array(
            $params
        ));
    }

    /**
     * Return first account, that match params.
     *
     * @param SearchCondition $params
     * @return AccountInfo
     */
    public function GetAccountInfo($params)
    {
        return self::_Call('GetAccountInfo', Array(
            $params
        ));
    }

    /**
     * Delete account.
     *
     * @param SearchCondition $params
     * @return boolean
     */
    public function DeleteAccount($params)
    {
        return self::_Call('DeleteAccount', Array(
            $params
        ));
    }

    /**
     * @param SearchCondition $params
     * @param SubscriptionAction $subscription
     * @return boolean
     */
    public function UpdateAccountOptionalSubscription($params, $subscription){
        return self::_Call('UpdateAccountOptionalSubscription', Array(
            $params, $subscription
        ));
    }
}

/**
 * AccountInfo complex type
 *
 * @pw_element string $login Unique login
 * @pw_element string $full_name Full Name or description
 * @pw_element string $account_number
 * @pw_element string $tariff_plan
 * @pw_element string $stb_sn
 * @pw_element string $stb_mac
 * @pw_element string $stb_type
 * @pw_element int $status
 * @pw_complex AccountInfo
 */
class AccountInfo
{
    /**
     * Unique login
     *
     * @var string
     */
    public $login;
    /**
     * Full Name or description
     *
     * @var string
     */
    public $full_name;
    /**
     * @var string
     */
    public $account_number;
    /**
     * @var string
     */
    public $tariff_plan;
    /**
     * @var string
     */
    public $stb_sn;
    /**
     * @var string
     */
    public $stb_mac;
    /**
     * @var string
     */
    public $stb_type;
    /**
     * @var int
     */
    public $status;
}

/**
 * Account complex type
 *
 * @pw_element string $login
 * @pw_element string $password
 * @pw_element string $full_name
 * @pw_element string $account_number
 * @pw_element string $tariff_plan
 * @pw_element int $status
 * @pw_complex Account
 */
class Account
{
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $full_name;
    /**
     * @var string
     */
    public $account_number;
    /**
     * @var string
     */
    public $tariff_plan;
    /**
     * @var int
     */
    public $status;
}

/**
 * SearchCondition complex type
 *
 * @pw_element string $login
 * @pw_element string $stb_mac
 * @pw_complex SearchCondition
 */
class SearchCondition
{
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $stb_mac;
}