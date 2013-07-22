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

$playlist = new Playlist();
    
if (@$_POST['add']){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    $playlist->add($_POST['name'], $_POST['group_id']);
    
    header("Location: playlists.php");
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $playlist->set(array('name' => $_POST['name'], 'group_id' => $_POST['group_id']), $_GET['id']);
        
        header("Location: playlists.php");
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        $playlist->del($id);
        
        header("Location: playlists.php");
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_playlist = $playlist->getById($id);
}

$playlists = $playlist->getAll();

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
<title><?= _('Playlists')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Playlists')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a>
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
            <td>&nbsp;</td>
        </tr>
        <? foreach ($playlists as $playlist){
                echo '<tr>';
                echo '<td>'.$playlist['id'].'</td>';
                echo '<td><a href="playlist.php?playlist_id='.$playlist['id'].'">'.$playlist['name'].'</a></td>';
                echo '<td>';
                
                echo '<a href="?edit=1&id='.$playlist['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$playlist['id'].'" onclick="if(confirm(\''._('Do you really want to delete this record?').'\')){return true}else{return false}">del</a>';
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
                    <td><?= _('Name')?></td>
                    <td><input type="text" name="name" value="<?echo @$edit_playlist['name']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('Group')?></td>
                    <td>
                        <select name="group_id">
                            <option value="0">--------</option>
                            <?
         
                            $stb_groups = new StbGroup();
                            $all_groups = $stb_groups->getAll();
                            
                            foreach ($all_groups as $group){
                                $selected = '';
                                
                                if ($edit_playlist['group_id'] == $group['id']){
                                    $selected = 'selected';
                                }
                                
                                echo '<option value="'.$group['id'].'" '.$selected.'>'.$group['name'].'</option>';
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