<?php

ob_start();

require_once "../server/common.php";

use Stalker\Lib\OAuth\AuthAccessHandler as AuthAccessHandler;

$error = false;

$access_handler = new AuthAccessHandler();
if (empty($_GET['response_type']) || empty($_GET['client_id']) || $_GET['response_type'] != 'token'){
    $error = 'invalid_request';
}else if (!$access_handler->isClient($_GET['client_id'])){
    $error = 'unauthorized_client';
}else if (!empty($_POST) && (empty($_POST['username']) || empty($_POST['password']))){
    $error = 'access_denied';
}else if (!empty($_POST)){
    if ($access_handler->checkUserAuth($_POST['username'], $_POST['password'])){

        $auth = array(
            "access_token"  => $access_handler->generateUniqueToken($_POST['username']),
        );

        if (Config::getSafe("api_v2_access_type", "bearer") == "bearer"){
            $access = array(
                "token_type"    => "bearer"
            );
        }else{
            $access = array(
                "token_type"    => "mac",
                "mac_key"       => $access_handler->getSecretKey($_POST['username']),
                "mac_algorithm" => "hmac-sha-256"
            );
        }

        $auth = array_merge($auth, $access);

        $additional = $access_handler->getAdditionalParams($_POST['username']);

        $auth = array_merge($auth, $additional);

        $auth = http_build_query($auth);

    }else{
        $error = 'access_denied';
    }
}

if (!empty($_GET['client_id'])){
    setcookie("client_id", $_GET['client_id']);
}

if ($error){
    header("Location: auth_error#error=".$error);
    exit;
}else if (!empty($auth)){
    header("Location: auth_success#".$auth);
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stalker Middleware Authorization</title>

    <link href="css/bootstrap.css" rel="stylesheet">

    <style type="text/css">
        .span6{
            margin: 100px auto;
            float: none;
        }
    </style>
    <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
    
    <script type="text/javascript">
        $(function(){

            if (document.location.hash.indexOf('error=') > 0){
                $('.alert-error').show();
            }
        });
    </script>
</head>
<body>

<div class="span6">

  <div class="alert alert-error" style="display:none;">
    <strong>Authorization failed</strong> Please check your username and password and try again.
  </div>

  <form class="form-horizontal" method="post">
    <fieldset>
      <legend>Stalker Authorization</legend>
      <div class="control-group">
        <label class="control-label" for="username">Username</label>
        <div class="controls">
          <input type="text" class="input-xlarge" id="username" name="username">
        </div>
      </div>
    <div class="control-group">
        <label class="control-label" for="password">Password</label>
        <div class="controls">
          <input type="password" class="input-xlarge" id="password" name="password">
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </fieldset>
  </form>
    
</div>

</body>
</html>