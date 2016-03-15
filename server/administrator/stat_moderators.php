<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
echo '</pre>';

$search = @$_GET['search'];
$letter = @$_GET['letter'];
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
</style>
<title><?= _('Moderators statistics')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
<tr>
    <td align="center" valign="middle" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Moderators statistics')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="tasks_archive.php"><?= _('Video archive')?></a> | <a href="karaoke_archive.php"><?= _('Karaoke archive')?></a>
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
<td align="center">
<?

function get_total_tasks($uid){

    return Mysql::getInstance()
        ->count()
        ->from('moderator_tasks')
        ->where(array(
            'to_usr'   => $uid,
            'archived' => 0
        ))
        ->get()
        ->counter();
}

function get_open_tasks($uid){

    return Mysql::getInstance()
        ->count()
        ->from('moderator_tasks')
        ->where(array(
            'ended'    => 0,
            'to_usr'   => $uid,
            'archived' => 0
        ))
        ->get()
        ->counter();
}

function get_closed_tasks($uid){

    return Mysql::getInstance()
        ->count()
        ->from('moderator_tasks')
        ->where(array(
            'ended'    => 1,
            'to_usr'   => $uid,
            'archived' => 0
        ))
        ->get()
        ->counter();
}

function get_rejected_tasks($uid){

    return Mysql::getInstance()
        ->count()
        ->from('moderator_tasks')
        ->where(array(
            'rejected' => 1,
            'to_usr'   => $uid,
            'archived' => 0
        ))
        ->get()
        ->counter();
}

function get_open_karaoke($uid){

    return Mysql::getInstance()
        ->count()
        ->from('karaoke')
        ->where(array(
            'add_by'   => $uid,
            'archived' => 0
        ))
        ->get()
        ->counter();
}

function get_closed_karaoke($uid){

    return Mysql::getInstance()
        ->count()
        ->from('karaoke')
        ->where(array(
            'add_by'   => $uid,
            'archived' => 0,
            'accessed' => 1,
            'status'   => 1,
            'done'     => 1
        ))
        ->get()
        ->counter();
}

$sql = "select * from administrators where access=2";

if (!Admin::isPageActionAllowed()){
    $sql .= " and login='".$_SESSION['login']."'";
}

$administrators = Mysql::getInstance()->query($sql);

while($arr = $administrators->next()){
    
    $closed   = get_closed_tasks($arr['id']);
    $rejected = get_rejected_tasks($arr['id']);
    
    $closed_2 = $closed - $rejected;
    
    
    $total_open_karaoke = get_open_karaoke($arr['id']);
    $closed_karaoke = get_closed_karaoke($arr['id']);
    
    $open_karaoke = $total_open_karaoke - $closed_karaoke;
    ?>
    
    <b><?echo $arr['login'] ?></b>
    
    <table width="600" align="center">
    <tr>
    <td width="50%" align="center" valign="top">
    <a href="last_closed_tasks.php?id=<?echo $arr['id'] ?>"><?= _('Movie')?></a>
        <table border="1" width="200" cellspacing="0">
          <tr>
            <td width="170">
                <?= _('Total')?>
            </td>
            <td width="30">
            <? echo get_total_tasks($arr['id'])?>
            </td>
          </tr>
          <tr>
            <td>
                <?= _('Opened')?>
            </td>
            <td>
            <? echo get_open_tasks($arr['id'])?>
            </td>
          </tr>
          <tr>
            <td>
                <?= _('Closed')?>
            </td>
            <td>
            <? echo $closed_2?>
            </td>
          </tr>
          <tr>
            <td>
                <?= _('Rejected')?>
            </td>
            <td>
            <? echo $rejected?>
            </td>
          </tr>
        </table>
    </td>
    
    <td width="50%" align="center" valign="top">
    <a href="last_closed_karaoke.php?id=<?echo $arr['id'] ?>"><?= _('Karaoke')?></a>
        <table border="1" width="200" cellspacing="0">
          <tr>
            <td width="170">
                <?= _('Total')?>
            </td>
            <td width="30">
            <? echo $total_open_karaoke ?>
            </td>
          </tr>
          <tr>
            <td width="170">
                <?= _('Opened')?>
            </td>
            <td width="30">
            <? echo $open_karaoke ?>
            </td>
          </tr>
          <tr>
            <td width="170">
                <?= _('Closed')?>
            </td>
            <td width="30">
            <? echo $closed_karaoke ?>
            </td>
          </tr>
        </table>
    </td>
    
    </tr>
    </table>
    <br>

<?
}
?>

</td>
</tr>
</table>
</body>
</html>