<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Middleware;

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}
    
if (@$_POST['add']){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    Mysql::getInstance()->insert('testers', array('mac' => Middleware::normalizeMac($_POST['mac'])));
    
    header("Location: testers.php");
    exit;
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        Mysql::getInstance()->update('testers',
                    array('mac' => Middleware::normalizeMac($_POST['mac'])),
                    array('id' => $id));
        
        header("Location: testers.php");
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()->delete('testers', array('id' => $id));
        
        header("Location: testers.php");
    }elseif (isset($_GET['status'])){

        Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

        $new_status = $_GET['status'];

        Mysql::getInstance()->update('testers',
                    array('status' => $new_status),
                    array('id' => $id));
        
        header("Location: testers.php");
    }

    exit;
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    
    $edit_tester = Mysql::getInstance()->from('testers')->where(array('id' => $id))->get()->first();
}

$testers = Mysql::getInstance()->from('testers')->get()->all();

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
<title><?= _('Testers')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Testers')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="users.php"><< <?= _('Back')?></a>
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
    <table class='list' cellpadding='3' cellspacing='0'>
        <tr>
            <td>#</td>
            <td>MAC</td>
            <td>&nbsp;</td>
        </tr>
        <? 
          $n = 1;
          foreach ($testers as $tester){
                echo '<tr>';
                echo '<td>'.$n.'</td>';
                echo '<td>'.$tester['mac'].'</td>';
                echo '<td>';
                if ($tester['status'] == 1){
                    $status_str = 'on';
                    $color = 'Green';
                    $new_status=0;
                }else{
                    $status_str = 'off';
                    $color = 'Red';
                    $new_status=1;
                }
                echo '<a href="?status='.$new_status.'&id='.$tester['id'].'" style="color:'.$color.'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to change the status of the tester %s?'), $tester['mac']).'\')){return true}else{return false}">'.$status_str.'</a>&nbsp;';
                echo '<a href="?edit=1&id='.$tester['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$tester['id'].'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to remove the tester %s?'), $tester['mac']).'\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
                
                $n++;
           }?>
    </table>
</td>
</tr>
<tr>
    <td align="center">
<br>
<br>
        <form method="POST">
            <table class="form">
                <tr>
                    <td>MAC</td>
                    <td><input type="text" name="mac" value="<?echo @$edit_tester['mac']?>"/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"/></td>
                </tr>
            </table>
        </form>
    </td>
</tr>
</table>

</body>
</html>