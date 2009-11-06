<?php

session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

if (@$_POST['login'] && @$_POST['password']){
    if (check_db_user_login($_POST['login'], $_POST['password'])){
        header("Location: index.php");
        exit();
    }
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">

body {
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
}
td {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #FFFFFF;
}
.login {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
	border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
	background-color:#88BBFF
}
</style>
<title>ВХОД</title>
</head>
<body onload="document.getElementById('login').focus()">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr>
    <td align="center" valign="middle" width="100%" height="100%">&nbsp;
      <form method="POST" action="login.php">
        <table border="0" bgcolor="#88BBFF" cellpadding="3" cellspacing="3" class="login">
            <tr>
                <td align="center" colspan="2"><b>Вход</b></td>
            </tr>
            <tr>
                <td align="right"><b>Логин:</b></td>
                <td><input type="text" name="login" id="login"></td>
            </tr>
            <tr>
                <td><b>Пароль:</b></td>
                <td align="right"><input type="password" name="password"></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" title="Вход" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Вход&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"></td>
            </tr>
        </table>
      </form>
    </td>
</tr>
</table>