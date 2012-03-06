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
                //todo: render auth page
            }else{

                if (!$this->access_handler->isValidClient($request->getClientId(), $request->getClientSecret())){
                    throw new OAuthInvalidClient("Client authentication failed", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-5.2");
                }

                if ($this->access_handler->checkUserAuth($request->getUsername(), $request->getPassword())){

                    $token = $this->access_handler->generateUniqueToken($request->getUsername());

                    if (!$token){
                        throw new OAuthServerError("Token making failed");
                    }

                    $response->setAccessToken($token);

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
    protected $client_id;
    protected $client_secret;
    private $is_implicit_grant_auth = false;

    public function __construct(){

        /*if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $this->proceedResourceOwnerPasswordCredentialsAuth();
        }else if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            $this->proceedImplicitGrantAuth();
        }else{
            throw new OAuthInvalidRequest("Invalid request method", "invalid_request");
        }*/
    }

    public function parse(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $this->proceedResourceOwnerPasswordCredentialsAuth();
        }else if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            $this->proceedImplicitGrantAuth();
        }else{
            throw new OAuthInvalidRequest("Invalid request method");
        }
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.2
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
     * @return void
     */
    private function proceedResourceOwnerPasswordCredentialsAuth(){

        if (empty($_POST['grant_type']) || $_POST['grant_type'] != 'password'){
            throw new OAuthInvalidRequest("Require valid grant_type", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3.2");
        }

        if (empty($_POST['username']) || empty($_POST['password'])){
            throw new OAuthInvalidRequest("Username and password must bee specified", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3.2");
        }

        if (empty($_POST['client_id']) || empty($_POST['client_secret'])){
            throw new OAuthInvalidRequest("Client ID and secret must bee specified", "http://tools.ietf.org/html/draft-ietf-oauth-v2-23#section-4.3.2");
        }

        $this->username      = $_POST['username'];
        $this->password      = $_POST['password'];
        $this->client_id     = $_POST['client_id'];
        $this->client_secret = $_POST['client_secret'];
    }

    public function getUsername(){
        return $this->username;
    }

    public function getPassword(){
        return $this->password;
    }

    public function getClientId(){
        return $this->client_id;
    }

    public function getClientSecret(){
        return $this->client_secret;
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