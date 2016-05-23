<?php
set_time_limit(0);

session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$updated_video = 0;
$updated_karaoke = 0;

$not_custom_video = Mysql::getInstance()->from('video')->where(array('protocol!=' => 'custom'))->get();

while($item = $not_custom_video->next()){
    $master = new VideoMaster();
    $master->getAllGoodStoragesForMediaFromNet($item['id'], 0, true);
    unset($master);
    $updated_video++;
}

$not_custom_karaoke = Mysql::getInstance()->from('karaoke')->where(array('protocol!=' => 'custom'))->get();

while($item = $not_custom_video->next()){
    $master = new KaraokeMaster();
    $master->getAllGoodStoragesForMediaFromNet($item['id'], 0);
    unset($master);
    $updated_karaoke++;
}

$error = sprintf(_('Updated %s videos and %s karaokes'), $updated_video, $updated_karaoke);

$debug = '<!--'.ob_get_contents().'-->';
ob_clean();
echo $debug;
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
.list, .list td, .form{
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
<title><?= _('Storage cache refresh')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Storage cache refresh')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="storages.php"><< <?= _('Back')?></a>
    </td>
</tr>
<tr>
    <td align="center"><br><br>
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
  
</table>
</body>
</html>