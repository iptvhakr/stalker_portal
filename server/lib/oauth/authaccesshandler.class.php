<?php

namespace Stalker\Lib\OAuth;

use \Mysql;
use \Stalker\Lib\RESTAPI\v2\RESTApiRequest;
use \Stalker\Lib\HTTP\HTTPRequest;

class AuthAccessHandler extends AccessHandler
{
    private $token_expire = 86400;

    public function checkUserAuth($username, $password, $mac = null, $serial_number = null){
        sleep(1); // anti brute-force delay

        $user = \User::getByLogin($username);

        if (!$user){
            $user = \User::authorizeFromOss($username, $password);
        }

        if (!$user){
            return false;
        }

        $possible_user = $user->getProfile();

        if ((strlen($possible_user['password']) == 32 && md5(md5($password).$possible_user['id']) == $possible_user['password'])
            || (strlen($possible_user['password']) < 32 && $password == $possible_user['password'])){

            if (\Config::getSafe('oauth_force_mac_check', false) && \Config::getSafe('oauth_force_serial_number_check', false)){
                if ($mac == $possible_user['mac'] && ($serial_number == $possible_user['serial_number'] || $possible_user['serial_number'] == '')){
                    $verified_user = $possible_user;
                }
            }else if (\Config::getSafe('oauth_force_mac_check', false)){
                if ($mac == $possible_user['mac']){
                    $verified_user = $possible_user;
                }
            }else  if (\Config::getSafe('oauth_force_serial_number_check', false)){
                if ($serial_number == $possible_user['serial_number'] || $possible_user['serial_number'] == ''){
                    $verified_user = $possible_user;
                }
            }else{
                $verified_user = $possible_user;
            }
        }

        if (!empty($verified_user)){
            $user->setSerialNumber($serial_number);
            $user->updateUserInfoFromOSS();
        }

        $user->updateIp();

        return !empty($verified_user);
    }

    public function generateUniqueToken($username){
        $user  = Mysql::getInstance()->from('users')->where(array('login' => $username))->get()->first();
        $token = $user['id'].'.'.md5(microtime(1));

        $token_record = Mysql::getInstance()->from('access_tokens')->where(array('uid' => $user['id']))->get()->first();

        $data = array(
            'uid'     => $user['id'],
            'token'   => $token,
            'refresh_token' => md5($token.''.uniqid()),
            'secret_key'    => md5($token.microtime(1)),
            'started' => 'NOW()',
            'expires' => date('Y-m-d H:i:s', time() + $this->token_expire)
        );

        if (empty($token_record)){
            $result = Mysql::getInstance()->insert('access_tokens', $data)->insert_id();
        }else{
            $result = Mysql::getInstance()->update('access_tokens', $data, array('uid' => $user['id']));
        }

        if (!$result){
            return false;
        }

        return $token;
    }

    public function getSecretKey($username){
        $user  = Mysql::getInstance()->from('users')->where(array('login' => $username))->get()->first();

        return Mysql::getInstance()->from('access_tokens')->where(array('uid' => $user['id']))->get()->first('secret_key');
    }

    public function isValidClient($client_id, $client_secret){
        $client = Mysql::getInstance()->from('clients')->where(array('id' => $client_id, 'secret' => $client_secret, 'active' => 1))->get()->first();
        return !empty($client);
    }

    public function isClient($client_id){
        $client = Mysql::getInstance()->from('clients')->where(array('id' => $client_id, 'active' => 1))->get()->first();
        return !empty($client);
    }

    public function getUserId($username){
        return (int) Mysql::getInstance()->from('users')->where(array('login' => $username))->get()->first('id');
    }

    public function getAdditionalParams($username){
        return array(
            'user_id'    => $this->getUserId($username),
            'expires_in' => $this->token_expire
        );
    }

    public function getAccessSessionByToken($token){
        return Mysql::getInstance()->from('access_tokens')->where(array('token' => $token, 'expires>' => 'NOW()'))->get()->first();
    }

    public function setTimeDeltaForToken($token, $delta){
        $session = $this->getAccessSessionByToken($token);

        if (empty($session['time_delta'])){
            Mysql::getInstance()->update('access_tokens', array('time_delta' => $delta), array('token' => $token));
        }
    }

    public function getAccessSessionByDeveloperApiKey($key){
        return Mysql::getInstance()->from('developer_api_key')->where(array('api_key' => $key, 'expires>' => 'NOW()'))->get()->first();
    }

    public static function getAccessSchema(HTTPRequest $request){

        $auth_header = $request->getAuthorization();

        if (empty($auth_header) && $request->getParam('api_key') === null){
            throw new AuthUnauthorized("Authorization required");
        }

        if (strpos($auth_header, "MAC ") === 0 && \Config::getSafe('api_v2_access_type', 'bearer') == 'mac'){
            return new MACAccessType($request, new self);
        }else if (strpos($auth_header, "Bearer ") === 0 && \Config::getSafe('api_v2_access_type', 'bearer') == 'bearer'){
            return new BearerAccessType($request, new self);
        }else if ($request->getParam('api_key') !== null){
            return new DeveloperAccessType($request, new self);
        }else{
            throw new AuthBadRequest("Unsupported authentication type");
        }
    }

    public function getRefreshToken($token){

        return Mysql::getInstance()->from('access_tokens')->where(array('token' => $token))->get()->first('refresh_token');
    }

    public function getUsernameByRefreshToken($refresh_token){

        if (empty($refresh_token)){
            return null;
        }

        $uid = Mysql::getInstance()->from('access_tokens')->where(array('refresh_token' => $refresh_token))->get()->first('uid');

        if (empty($uid)){
            return null;
        }

        $username = Mysql::getInstance()->from('users')->where(array('id' => $uid))->get()->first('login');

        return $username;
    }

    public static function setInvalidAccessTokenByUid($uid){
        return Mysql::getInstance()
            ->update('access_tokens',
                array(
                    'token'         => 'invalid_'.md5(mktime(1).uniqid()),
                    'refresh_token' => 'invalid'
                ),
                array('uid' => $uid))
            ->result();
    }
}

class AuthBadRequest extends \Exception{}

class AuthForbidden extends \Exception{}

class AuthUnauthorized extends \Exception{}