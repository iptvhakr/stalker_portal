<?php

/**
 * OAuth 2.0 server implementation
 */

namespace Stalker\Lib\OAuth;

class OAuthServer
{
    private $access_handler;
    private $token_type;

    public function __construct(AccessHandler $access_handler){
        $this->access_handler = $access_handler;
    }

    public function handleAuthRequest(){

        $response = new OAuthResponse();

        $request  = new OAuthRequest();
        $response->setRequest($request);
        $response->setTokenType($this->token_type);

        try{
            
            $request->parse();

            if ($request->isImplicitGrantAuth()){

            }else{

                if ($request->getRefreshToken()){

                    $username = $this->access_handler->getUsernameByRefreshToken($request->getRefreshToken());

                    if (empty($username)){
                        throw new OAuthInvalidClient("request_token not valid");
                    }

                    $request->setUsername($username);

                    $token = $this->access_handler->generateUniqueToken($request->getUsername());

                    if (!$token){
                        throw new OAuthServerError("Token making failed");
                    }

                    $response->setAccessToken($token);

                    $refresh_token = $this->access_handler->getRefreshToken($token);

                    if ($refresh_token){
                        $response->setRefreshToken($refresh_token);
                    }

                    if ($this->token_type == "mac"){
                        $key = $this->access_handler->getSecretKey($request->getUsername());
                        $response->setMacKey($key);
                    }

                    $additional_params = $this->access_handler->getAdditionalParams($request->getUsername());

                    if (!empty($additional_params)){
                        $response->setAdditionalParams($additional_params);
                    }

                }else if ($this->access_handler->checkUserAuth($request->getUsername(), $request->getPassword(), $request->getMacAddress(), $request->getSerialNumber(), $request)){

                    $user  = \Mysql::getInstance()->from('users')->where(array('login' => $request->getUsername()))->get()->first();

                    if ($user['status'] == 1){
                        throw new OAuthAccessDenied("Account is disabled");
                    }

                    $token = $this->access_handler->generateUniqueToken($request->getUsername());

                    if (!$token){
                        throw new OAuthServerError("Token making failed");
                    }

                    $response->setAccessToken($token);

                    $refresh_token = $this->access_handler->getRefreshToken($token);

                    if ($refresh_token){
                        $response->setRefreshToken($refresh_token);
                    }

                    if ($this->token_type == "mac"){
                        $key = $this->access_handler->getSecretKey($request->getUsername());
                        $response->setMacKey($key);
                    }

                    $additional_params = $this->access_handler->getAdditionalParams($request->getUsername());

                    if (!empty($additional_params)){
                        $response->setAdditionalParams($additional_params);
                    }
                }else{
                    throw new OAuthInvalidClient("Username or password is incorrect");
                }
            }
            
        }catch(OAuthException $e){
            if ($request->isImplicitGrantAuth()){
                echo $e->getMessage();
            }else{
                $response->setError($e->getCode(), $e->getMessage(), $e->getUrl());
            }
        }
        
        $response->send();
    }

    public function setTokenType($token_type){

        if (!in_array($token_type, array("bearer", "mac"))){
            throw new OAuthInvalidRequest("Not supported access type");
        }

        $this->token_type = $token_type;
    }
}

class OAuthResponse
{
    protected $body = array();
    protected $request;
    private   $error_fields;
    private   $token_type;

    public function __construct(){
        ob_start();
        $this->error_fields = array_fill_keys(array('status', 'error', 'error_description', 'error_uri', 'debug'), true);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed");
            echo "<pre>Method not allowed</pre>";
            exit;
        }
    }

    public function setTokenType($token_type){
        $this->token_type = $token_type;

        $this->body['token_type'] = $this->token_type;

        if ($this->token_type == "mac"){
            $this->body['mac_algorithm'] = 'hmac-sha-256';
        }
    }

    public function setRequest($request){
        $this->request = $request;
    }

    public function setError($error, $description = "", $error_uri = ""){

        $this->body['error'] = $error;

        if (!empty($description)){
            $this->body['error_description'] = $description;
        }

        if (!empty($error_uri)){
            $this->body['error_uri'] = $error_uri;
        }
    }

    public function setAccessToken($token){
        $this->body['access_token'] = $token;
    }

    public function setRefreshToken($refresh_token){
        $this->body['refresh_token'] = $refresh_token;
    }

    public function setMacKey($key){
        $this->body['mac_key'] = $key;
    }

    public function setMacAlgorithm($algorithm){
        $this->body['mac_algorithm'] = $algorithm;
    }

    public function setAdditionalParams($key, $value = ''){

        if (!is_array($key)){
            $key = array($key => $value);
        }

        $this->body = array_merge($this->body, $key);

        //$this->body[$key] = $value;
    }

    private function setOutput(){
        $debug = ob_get_contents();
        ob_end_clean();
        if ($debug){
            $this->body['debug'] = $debug;
        }
    }

    public function send(){
        header("Content-Type: application/json");
        $this->setOutput();
        if (!empty($this->body['error'])){
            /*unset($this->body['token_type']);*/
            $this->body = array_intersect_key($this->body, $this->error_fields);
        }
        echo json_encode($this->body);
    }
}

class OAuthRequest
{
    protected $username;
    protected $password;
    protected $mac;
    protected $serial_number;
    protected $client_type;
    protected $model;
    protected $version;
    protected $device_id;
    protected $device_id2;
    protected $signature;
    protected $client_id;
    protected $client_secret;
    protected $refresh_token;
    private $is_implicit_grant_auth = false;

    public function __construct(){

    }

    public function parse(){

        if (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') === false){
            throw new OAuthInvalidRequest("Invalid content-type. Require Content-Type: application/x-www-form-urlencoded");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!empty($_POST['grant_type']) && $_POST['grant_type'] == 'refresh_token'){
                $this->proceedRefreshToken();
            }else{
                $this->proceedResourceOwnerPasswordCredentialsAuth();
            }
        }else if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            $this->proceedImplicitGrantAuth();
        }else{
            throw new OAuthInvalidRequest("Invalid request method");
        }
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.2
     * @throws OAuthInvalidRequest
     * @return void
     */
    private function proceedImplicitGrantAuth(){

        $this->is_implicit_grant_auth = true;

        if (empty($_GET['response_type']) || $_GET['response_type'] != 'token'){
            throw new OAuthInvalidRequest("Require valid response_type", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.2.2.1");
        }

        if (empty($_GET['client_id'])){
            throw new OAuthInvalidRequest("client_id must bee specified", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.2.1");
        }

        $this->client_id = $_GET['client_id'];
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3
     * @throws OAuthInvalidRequest
     * @return boolean
     */
    private function proceedResourceOwnerPasswordCredentialsAuth(){

        if (empty($_POST['grant_type']) || $_POST['grant_type'] != 'password'){
            throw new OAuthInvalidRequest("Require valid grant_type", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3.2");
        }

        if ((empty($_POST['username']) || empty($_POST['password'])) && empty($_POST['mac'])){
            throw new OAuthInvalidRequest("Username and password must bee specified", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3.2");
        }

        $this->username = $_POST['username'];
        $this->password = $_POST['password'];

        if (isset($_POST['mac'])){
            $this->mac = $_POST['mac'];
        }

        if (isset($_POST['serial_number'])){
            $this->serial_number = $_POST['serial_number'];
        }

        if (isset($_POST['stb_type'])){
            $this->model = $_POST['stb_type'];
            $this->client_type = 'STB';
        }elseif(isset($_POST['model'])){
            $this->model = $_POST['model'];
        }

        if (isset($_POST['client_type'])){
            $this->client_type = $_POST['client_type'];
        }

        if (isset($_POST['version'])){
            $this->version = $_POST['version'];
        }

        if (isset($_POST['device_id'])){
            $this->device_id = $_POST['device_id'];
        }

        if (isset($_POST['device_id2'])){
            list($this->signature, $this->device_id2) = explode('.', $_POST['device_id2']);
        }

        return true;
    }

    private function proceedRefreshToken(){

        if (empty($_POST['grant_type']) || $_POST['grant_type'] != 'refresh_token'){
            throw new OAuthInvalidRequest("Require valid grant_type", "http://tools.ietf.org/html/draft-ietf-oauth-v2-26#section-6");
        }

        if (empty($_POST['refresh_token'])){
            throw new OAuthInvalidRequest("refresh_token must bee specified", "http://tools.ietf.org/html/draft-ietf-oauth-v2-26#section-6");
        }

        $this->refresh_token = $_POST['refresh_token'];

        return true;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getPassword(){
        return $this->password;
    }

    public function getMacAddress(){
        return $this->mac;
    }

    public function getSerialNumber(){
        return $this->serial_number;
    }

    public function getClientType(){
        return $this->client_type;
    }

    public function getModel(){
        return $this->model;
    }

    public function getVersion(){
        return $this->version;
    }

    public function getDeviceId(){
        return $this->device_id;
    }

    public function getDeviceId2(){
        return $this->device_id2;
    }

    public function getSignature(){
        return $this->signature;
    }

    public function getClientId(){
        return $this->client_id;
    }

    public function getClientSecret(){
        return $this->client_secret;
    }

    public function getRefreshToken(){
        return $this->refresh_token;
    }

    public function isImplicitGrantAuth(){
        return $this->is_implicit_grant_auth;
    }
}

class OAuthException extends \Exception{
    protected $url;
    protected $code;

    public function __construct($message = "", $url = ""){
        $this->url     = $url;
        $this->message = $message;
    }

    public function getUrl(){
        return $this->url;
    }
}

class OAuthServerError extends OAuthException
{
    protected $code = 'server_error';
}

class OAuthRequestException extends OAuthException{}

class OAuthInvalidRequest extends OAuthRequestException
{
    protected $code = 'invalid_request';
}

class OAuthAccessDenied extends OAuthException
{
    protected $code = 'access_denied';
}

class OAuthInvalidClient extends OAuthException
{
    protected $code = 'invalid_client';
}

?>