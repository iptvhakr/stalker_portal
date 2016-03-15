<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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
    color: #000000;
}
.list{
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
}
a{
	color:#0000FF;
	font-weight: bold;
	text-decoration:none;
}
a:link,a:visited {
	color:#5588FF;
	font-weight: bold;
}
a:hover{
	color:#0000FF;
	font-weight: bold;
	text-decoration:underline;
}
a.notreaded:link,a.notreaded:visited {
	color:#FF8855;
	font-weight: bold;
}
a.notreaded:hover{
	color:#FF0000;
	font-weight: bold;
	text-decoration:underline;
}
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title><?= _('My messages')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('My messages')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="tasks.php"><< <?= _('Back')?></a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>
<td>
<?

$task = @$_GET['task'];
$uid = $_SESSION['uid'];

$sql = "select * from moderators_history where (task_id=$task and to_usr=$uid) or (task_id=$task and from_usr=$uid) order by send_time desc";

$history = Mysql::getInstance()->query($sql);

?>

<table border="0" align="center" width="620">
<tr>
<td align="center"><br>
<br>
<br>

<table width="100%" border="1" cellspacing="0">
<tr>
    <td width="20">&nbsp;</td>
    <td width="140"><?= _('Date')?></td>
    <td><?= _('From')?></td>
    <td><?= _('Media')?></td>
</tr>

<?
while($arr = $history->next()){

    echo "<tr>\n";
    echo "<td align='center'>";
    if ($arr['to_usr'] == @$_SESSION['uid']){
        echo "\/";
    }else{
        echo "/\\";
    }
    echo "</td>";
    echo "<td><a ";
    if($arr['readed'] == 0) echo 'class="notreaded"';
    echo " href='msg.php?id={$arr['id']}'>".$arr['send_time']."</a></td>\n";
    echo "<td>".get_moderator_login_by_id($arr['from_usr'])."</td>\n";
    echo "<td>".get_media_name_by_task_id($arr['task_id'])."</td>\n";
    echo "</tr>\n";
}
?>

</table>

<td>
</tr>
</table>

</td>
</tr>
</table>