<?php

class AuthAccessHandler extends AccessHandler
{
    private $token_expire = 86400;

    public function checkUserAuth($username, $password){
        sleep(1); // anti brute-force delay
        $user = Mysql::getInstance()->from('users')->where(array('login' => $username, 'password' => $password))->get()->first();
        return !empty($user);
    }

    public function generateUniqueToken($username){
        $user  = Mysql::getInstance()->from('users')->where(array('login' => $username))->get()->first();
        $token = $user['id'].'.'.md5(microtime(1));

        $token_record = Mysql::getInstance()->from('access_tokens')->where(array('uid' => $user['id']))->get()->first();

        $data = array(
            'uid'     => $user['id'],
            'token'   => $token,
            'expires' => 'FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())+'.$this->token_expire.')'
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

    public function getAuthorizationInfo(){

        if (empty($_SERVER['HTTP_AUTHORIZATION'])){
            return false;
        }

        $auth_info = array();

        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)){
            $auth_info['schema'] = 'Bearer';
            $auth_info['token']  = $matches[1];
        }
    }

    public function checkAccess(){
        
    }
}