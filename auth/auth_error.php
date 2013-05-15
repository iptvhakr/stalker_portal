<!DOCTYPE html>
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

            if (document.location.hash.indexOf('error=invalid_request') > 0){
                $('.error-description').html("The request is missing a required parameter.");
            }else if (document.location.hash.indexOf('error=unauthorized_client') > 0){
                $('.error-description').html("The client is not authorized.");
            }else if (document.location.hash.indexOf('error=access_denied') > 0){
                $('.form-horizontal').show();
                $('.error-description').html("Please check your username and password and try again.");
            }
            
        });
    </script>
</head>
<body>

<div class="span6">

  <div class="alert alert-error">
    <strong>Authorization failed</strong> <span class="error-description"></span>
  </div>

  <form class="form-horizontal" method="post" action="authorize?response_type=token&client_id=<?echo @$_COOKIE['client_id']?>" style="display:none;">
    <fieldset>
      <legend>Stalker Authorization</legend>
      <div class="control-group">
        <label class="control-label" for="input01">Username</label>
        <div class="controls">
          <input type="text" class="input-xlarge" id="username" name="username">
        </div>
      </div>
    <div class="control-group">
        <label class="control-label" for="input01">Password</label>
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