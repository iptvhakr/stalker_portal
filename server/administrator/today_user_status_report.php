<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$from = mktime(0,0,0,date("n"),date("j"), date("Y"));
$to   = mktime(23,59,59,date("n"),date("j"), date("Y"));

$users = Mysql::getInstance()->from('users')->where(array(
    'UNIX_TIMESTAMP(last_change_status)>=' => $from,
    'UNIX_TIMESTAMP(last_change_status)<' => $to
))->orderby('status')->get();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?= sprintf(_('Report for the stb status changed on %s on %s'), date("d.m.Y"), date("H:i:s"))?></title>
<style>
table, td {
    border: 1px solid #000000;
}
table .head td {
    font-weight: bold;
    text-align: center;
}
table .item_row td {
    padding-left: 3px;
}
</style>
</head>

<body>
<center><h2><?= sprintf(_('Report of the stb status changing on %s on %s'), date("d.m.Y"), date("H:i:s"))?></h2></center>
<table width="600" align="center" cellpadding="0" cellspacing="0">
<tr class="head">
    <td>#</td>
    <td>MAC</td>
    <td><?= _('Current status')?></td>
    <td><?= _('Time of change')?></td>
</tr>

<?
$status_arr = array('On','Off');
$i = 0;
while($arr = $users->next()){
    $i++;

    $status = $status_arr[$arr['status']];
    
    echo '<tr class="item_row">';
    
    echo "<td>".$i."</td>\n";
    echo "<td>".$arr['mac']."</td>\n";
    echo "<td>".$status."</td>\n";
    echo "<td>".$arr['last_change_status']."</td>\n";
    
    echo "</tr>";
}

?>

</table>
</body>
</html>