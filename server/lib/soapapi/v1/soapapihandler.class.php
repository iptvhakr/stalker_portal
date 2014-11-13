<?php
namespace Stalker\Lib\SOAPApi\v1;

/**
 * @service Stalker
 */
class SoapApiHandler
{

    /**
     * If there is an account with a specified login, it updates its, otherwise - create new account.
     *
     * @param Account $params
     * @return boolean
     */
    public function CreateOrUpdateAccount($params){

        $params = (array) $params;

        $this->checkLoginAndMac($params);

        $user = \User::getByLogin($params['login']);

        if (empty($user)){
            return $this->CreateAccount($params);
        }else{
            return $this->UpdateAccount($params);
        }
    }

    /**
     * Create new account.
     *
     * @param Account $params
     * @return boolean
     * @throws SoapServerError
     */
    public function CreateAccount($params){

        $params = (array) $params;

        $this->checkLoginAndMac($params);

        $user_id = \User::createAccount($params);

        if (!$user_id){
            throw new SoapServerError(__METHOD__, __FILE__.':'.__LINE__);
        }

        return (boolean) $user_id;
    }

    /**
     * Update account by specified login param.
     *
     * @param Account $params
     * @return boolean
     * @throws SoapServerError
     */
    public function UpdateAccount($params){

        $params = (array) $params;

        $this->checkLoginAndMac($params);

        $user = \User::getByLogin($params['login']);

        $result = $user->updateAccount($params);

        if (!$result){
            throw new SoapServerError(__METHOD__, __FILE__.':'.__LINE__);
        }

        return $result;
    }

    /**
     * Return first account, that match params.
     *
     * @param SearchCondition $params
     * @return AccountInfo
     * @throws SoapAccountNotFound
     */
    public function GetAccountInfo($params){

        $user = $this->getUserByParams($params);
        return $user->getAccountInfo();
    }

    /**
     * Delete account by search conditions.
     *
     * @param SearchCondition $params
     * @return boolean
     * @throws SoapAccountNotFound
     */
    public function DeleteAccount($params){

        $user = $this->getUserByParams($params);
        return $user->delete();
    }

    /**
     * Update optional package subscription.
     *
     * @param SearchCondition $params
     * @param SubscriptionAction $subscription
     * @return boolean
     * @throws SoapMissingRequiredParam
     * @throws SoapSubscriptionUpdateError
     */
    public function UpdateAccountOptionalSubscription($params, $subscription){

        $params = (array) $params;
        $subscription = (array) $subscription;

        $user = $this->getUserByParams($params);

        if (!$this->subscriptionManageMode($subscription)){
            throw new SoapMissingRequiredParam(__METHOD__, __FILE__.':'.__LINE__);
        }

        $subscription_result = $user->updateOptionalPackageSubscription($subscription);

        if (!$subscription_result){
            throw new SoapSubscriptionUpdateError('1', 'Subscription update error');
        }

        return true;
    }

    private function getUserByParams($params){

        $params = (array) $params;

        if (!empty($params['stb_mac'])){
            $user = \User::getByMac($params['stb_mac']);
        }elseif (!empty($params['login'])){
            $user = \User::getByLogin($params['login']);
        }

        if (empty($user)){
            throw new SoapAccountNotFound(__METHOD__, __FILE__.':'.__LINE__);
        }

        return $user;
    }

    private function checkLoginAndMac($params){

        if (empty($params['login'])){
            throw new SoapMissingRequiredParam();
        }

        if (!empty($params['stb_mac'])){
            $params['stb_mac'] = \Middleware::normalizeMac($params['stb_mac']);
            if (empty($params['stb_mac'])){
                throw new SoapWrongMacFormat(__METHOD__, __FILE__.':'.__FILE__);
            }

            $user = \User::getByLogin($params['login']);

            if (empty($user) || $user->getMac() != $params['stb_mac']){

                $stb = \Stb::getByMac($params['stb_mac']);

                if (!empty($stb)){
                    throw new SoapMacAddressInUse(__METHOD__, __FILE__.':'.__FILE__);
                }
            }
        }
    }

    private function subscriptionManageMode($params){
        return !empty($params['subscribe']) || !empty($params['unsubscribe']);
    }
}

/**
 * @pw_complex stringArray
 */
class stringArray {}

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
 * @pw_element stringArray $subscribed
 * @pw_complex AccountInfo
 */
class AccountInfo
{
    public $login; // string
    public $full_name; // string
    public $account_number; // string
    public $tariff_plan; // string
    public $stb_sn; // string
    public $stb_mac; //string
    public $stb_type; //string
    public $status; // int
    public $subscribed; // array
}

/**
 * Account complex type
 *
 * @pw_element string $login
 * @pw_set minoccurs=0
 * @pw_element string $password
 * @pw_set minoccurs=0
 * @pw_element string $full_name
 * @pw_set minoccurs=0
 * @pw_element string $account_number
 * @pw_set minoccurs=0
 * @pw_element string $tariff_plan
 * @pw_set minoccurs=0
 * @pw_element int $status
 * @pw_set minoccurs=0
 * @pw_element string $stb_mac
 * @pw_complex Account
 */
class Account
{
    public $login; // string
    public $password; // string
    public $full_name; // string
    public $account_number; // string
    public $tariff_plan; // string
    public $status; // int
    public $stb_mac; //string
}

/**
 * Optional subscription complex type.
 *
 * @pw_set minoccurs=0
 * @pw_element stringArray $subscribe
 * @pw_set minoccurs=0
 * @pw_element stringArray $unsubscribe
 * @pw_complex SubscriptionAction
 */
class SubscriptionAction
{
    public $subscribe; //array
    public $unsubscribe; //array
}

/**
 * SearchCondition complex type
 *
 * @pw_set minoccurs=0
 * @pw_element string $login
 * @pw_set minoccurs=0
 * @pw_element string $stb_mac
 * @pw_complex SearchCondition
 */
class SearchCondition
{
    public $login; // string
    public $stb_mac; //string
}

class SoapException extends \SoapFault{

    public function __construct($faultactor = null, $detail = null){

        if ($faultactor){
            $this->faultactor = $faultactor;
        }

        if ($detail){
            $this->detail = $detail;
        }
    }
}

class SoapWrongMacFormat extends SoapException{
    public $faultcode = "2";
    public $faultstring = "Wrong MAC address format";
}

class SoapServerError extends SoapException{
    public $faultcode = "500";
    public $faultstring = "Server error";
}

class SoapAccountNotFound extends SoapException{
    public $faultcode = "4";
    public $faultstring = "Account not found";
}

class SoapMissingRequiredParam extends SoapException{
    public $faultcode = "5";
    public $faultstring = "Missing required param";
}

class SoapSubscriptionUpdateError extends SoapException{
    public $faultcode = "6";
    public $faultstring = "Subscription update error";
}

class SoapMacAddressInUse extends SoapException{
    public $faultcode = "7";
    public $faultstring = "MAC address already in use";
}