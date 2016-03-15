<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

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

    Mysql::getInstance()->insert('streaming_servers', array(
        'name'         => @$_POST['name'],
        'address'      => @$_POST['address'],
        'max_sessions' => @$_POST['max_sessions'],
        'stream_zone'  => @$_POST['stream_zone']
    ));

    header("Location: stream_servers.php");
    exit;
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        Mysql::getInstance()
            ->use_caching(array('streaming_servers.id='.intval($_GET['id'])))
            ->update('streaming_servers',
            array(
                'name'         => @$_POST['name'],
                'address'      => @$_POST['address'],
                'max_sessions' => @$_POST['max_sessions'],
                'stream_zone'  => @$_POST['stream_zone']
            ),
            array('id' => intval($_GET['id']))
        );

        header("Location: stream_servers.php");
        exit;
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()
            ->use_caching(array('streaming_servers.id='.intval($_GET['id'])))
            ->delete('streaming_servers', array('id' => intval($_GET['id'])));

        header("Location: stream_servers.php");
        exit;
    }elseif (isset($_GET['status'])){

        Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

        Mysql::getInstance()
            ->use_caching(array('streaming_servers.id='.intval($_GET['id'])))
            ->update('streaming_servers',
            array(
                'status' => $_GET['status']
            ),
            array('id' => intval($_GET['id']))
        );

        header("Location: stream_servers.php");
        exit;
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_streamer = StreamServer::getById($id);
}

$streamers = StreamServer::getAll();

$zones = Mysql::getInstance()->from('stream_zones')->orderby('name')->get()->all();

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
.stream_zone{
    width: 153px;
}
input{
    margin: 0;
}
</style>
<title><?= _('Stream servers')?></title>

</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Stream servers')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="stream_zones.php"><?= _('Zones')?></a>
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
            <td>ID</td>
            <td><?= _('Name')?></td>
            <td><?= _('Address')?></td>
            <td><?= _('Max sessions')?></td>
            <td>&nbsp;</td>
        </tr>
        <? foreach ($streamers as $streamer){
                echo '<tr ';
                
                echo '>';
                echo '<td>'.$streamer['id'].'</td>';
                echo '<td>'.$streamer['name'].'</td>';
                echo '<td>'.$streamer['address'].'</td>';
                echo '<td>'.$streamer['max_sessions'].'</td>';
                echo '<td>';
                if ($streamer['status'] == 1){
                    $status_str = 'on';
                    $color = 'Green';
                    $new_status=0;
                }else{
                    $status_str = 'off';
                    $color = 'Red';
                    $new_status=1;
                }


                echo '<a href="?status='.$new_status.'&id='.$streamer['id'].'" style="color:'.$color.'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to change the status of the server %s?'), $streamer['name']).'\')){return true}else{return false}">'.$status_str.'</a>&nbsp;';
                echo '<a href="?edit=1&id='.$streamer['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$streamer['id'].'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to delete server %s?'), $streamer['name']).'\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
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
                    <td width="190"><?= _('Name')?></td>
                    <td><input type="text" name="name" value="<?echo @$edit_streamer['name']?>" /></td>
                </tr>
                <tr>
                    <td><?= _('Address')?></td>
                    <td><input type="text" name="address" value="<?echo @$edit_streamer['address']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('Max sessions')?></td>
                    <td><input type="text" name="max_sessions" value="<?echo @$edit_streamer['max_sessions']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('Stream zone')?></td>
                    <td>
                        <select name="stream_zone" class="stream_zone">
                            <option value="0">
                            <?
                            foreach ($zones as $zone){
                                if (@$edit_streamer['stream_zone'] == $zone['id'] || empty($edit_streamer) && $zone['default_zone'] == 1){
                                    $selected = 'selected';
                                }else{
                                    $selected = '';
                                }
                                echo '<option value="'.$zone['id'].'" '.$selected.'>'.$zone['name'];
                            }
                            ?>
                        </select>
                    </td>
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