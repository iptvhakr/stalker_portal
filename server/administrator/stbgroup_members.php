<?php
session_start();

ob_start();

include "./common.php";

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}

$id = @intval($_GET['id']);

$stb_groups = new StbGroup();

$group = $stb_groups->getById($_GET['group_id']);

if (empty($group)){
    echo 'wtf?';
    exit;
}

if (@$_POST['add']){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);
    
    $stb_groups->addMember(array('mac' => Middleware::normalizeMac($_POST['mac']), 'uid' => Middleware::getUidByMac($_POST['mac']), 'stb_group_id' => $_GET['group_id']));

    header("Location: stbgroup_members.php?group_id=".@$_GET['group_id']);
    exit;
}

$action  = !empty($_POST['edit']) ? 'edit': (!empty($_GET['del']) ? 'del': FALSE);
if (!empty($id) && $action){

    if ($action == 'edit'){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);
        $stb_groups->setMember(array('mac' => Middleware::normalizeMac($_POST['mac']), 'uid' => Middleware::getUidByMac($_POST['mac'])), $id);
        
    }else{

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);
        $stb_groups->removeMember($id);

    }
    header("Location: stbgroup_members.php?group_id=".@$_GET['group_id']);
    exit;
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_member = $stb_groups->getMember($id);
}

$members = $stb_groups->getAllMembersByGroupId($_GET['group_id']);

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
<title><?= _('Stb in group')?> "<? echo $group['name'] ?>"</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Stb in group')?> "<? echo $group['name'] ?>"&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="stbgroups.php"><< <?= _('Back')?></a>
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
            <td>UID</td>
            <td>&nbsp;</td>
        </tr>
        <? 
           $i=1;
           
           foreach ($members as $member){
                echo '<tr>';
                echo '<td>'.$i.'</td>';
                echo '<td><a href="profile.php?id='.$member['uid'].'">'.$member['mac'].'</a></td>';
                echo '<td>'.$member['uid'].'</td>';
                echo '<td>';
                
                echo '<a href="?group_id='.$_GET['group_id'].'&edit=1&id='.$member['id'].'">edit</a>&nbsp;';
                echo '<a href="?group_id='.$_GET['group_id'].'&del=1&id='.$member['id'].'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to remove stb %s from the database?'), $member['mac']).'\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
                
                $i++;
           }
           ?>
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
                    <td><input type="text" name="mac" value="<?echo @$edit_member['mac']?>"/></td>
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