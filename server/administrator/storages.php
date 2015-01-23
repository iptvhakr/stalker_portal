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

if (!empty($_POST['nfs_home_path']) && strripos($_POST['nfs_home_path'], '/') != strlen($_POST['nfs_home_path'])-1){
    $_POST['nfs_home_path'] .= '/';
}
    
if (@$_POST['add']){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    Mysql::getInstance()->insert('storages', array(
        'storage_name'          => @$_POST['storage_name'],
        'storage_ip'            => @$_POST['storage_ip'],
        'apache_port'           => @$_POST['apache_port'],
        'nfs_home_path'         => @$_POST['nfs_home_path'],
        'max_online'            => @$_POST['max_online'],
        'user_agent_filter'     => @$_POST['user_agent_filter'],
        'for_moderator'         => @intval($_POST['for_moderator']),
        'for_records'           => @intval($_POST['for_records']),
        'flussonic_server'      => @intval($_POST['flussonic_server']),
        'fake_tv_archive'       => @intval($_POST['fake_tv_archive']),
        'archive_stream_server' => @$_POST['archive_stream_server'],
        'external'              => @intval($_POST['external']),
        'for_simple_storage'    => @intval($_POST['for_simple_storage']),
        'not_for_mag100'        => @intval($_POST['not_for_mag100'])
    ));

    header("Location: storages.php");
    exit;
}

$id = @intval($_GET['id']);

if (!empty($id)){

    if (@$_POST['edit']){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        Mysql::getInstance()->update('storages',
            array(
                'storage_name'          => @$_POST['storage_name'],
                'storage_ip'            => @$_POST['storage_ip'],
                'apache_port'           => @$_POST['apache_port'],
                'nfs_home_path'         => @$_POST['nfs_home_path'],
                'max_online'            => @$_POST['max_online'],
                'user_agent_filter'     => @$_POST['user_agent_filter'],
                'for_moderator'         => @intval($_POST['for_moderator']),
                'for_records'           => @intval($_POST['for_records']),
                'flussonic_server'      => @intval($_POST['flussonic_server']),
                'fake_tv_archive'       => @intval($_POST['fake_tv_archive']),
                'archive_stream_server' => @$_POST['archive_stream_server'],
                'external'              => @intval($_POST['external']),
                'for_simple_storage'    => @intval($_POST['for_simple_storage']),
                'not_for_mag100'        => @intval($_POST['not_for_mag100'])
            ),
            array('id' => intval($_GET['id']))
        );

        header("Location: storages.php");
        exit;
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()->delete('storages', array('id' => intval($_GET['id'])));

        header("Location: storages.php");
        exit;
    }elseif (isset($_GET['status'])){

        Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

        Mysql::getInstance()->update('storages', array('status' => $_GET['status']), array('id' => intval($_GET['id'])));

        header("Location: storages.php");
        exit;
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_storage = Mysql::getInstance()->from('storages')->where(array('id' => $id))->get()->first();
}

if (@$_GET['reset_cache'] && !empty($id)){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    $storage = Mysql::getInstance()->from('storages')->where(array('id' => $id))->get()->first();
    $storage_name = $storage['storage_name'];

    if (!empty($storage_name)){

        $reset_result = Mysql::getInstance()->update('storage_cache',
            array(
                'changed' => '0000-00-00 00:00:00'
            ),
            array('storage_name' => $storage_name)
        )->result();

        if ($reset_result){
            $error = sprintf(_('Cache %s is cleared'), $storage_name);
        }
    }else{
        $error = sprintf(_('Storage with id %s not found'), $id);
    }
}

if (@$_GET['reset_all_cache']){

    Admin::checkAccess(AdminAccess::ACCESS_PAGE_ACTION);

    $reset_result = Mysql::getInstance()->update('storage_cache', array('changed' => '0000-00-00 00:00:00'))->result();

    if ($reset_result){
        $error = _('Cache is cleared');
    }
}

$storages = Mysql::getInstance()->from('storages')->get()->all();

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
<title><?= _('Storages')?></title>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript">
    $(function(){

        $('.for_records').click(function(){
            if ($(this).attr('checked')){
                $('.flussonic_server').show();
                $('.fake_tv_archive').show();
            }else{
                $('.flussonic_server').hide();
                $('.fake_tv_archive').hide();
            }
        });

    })
</script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Storages')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="master_log.php"><?= _('Logs')?></a> | <a href="?reset_all_cache=1" onclick="if(confirm('<?= htmlspecialchars(_('Are you sure you want to reset a cache?'), ENT_QUOTES)?>')){return true}else{return false}"><?= _('Clear cache')?></a> | <a href="cache_refresh.php" onclick="if(confirm('<?= htmlspecialchars(_('Are you sure you want to update a cache? This operation will take some time.'), ENT_QUOTES)?>')){return true}else{return false}"><?= _('Refresh cache')?></a> | <a href="unique_video.php"><?= _('Unique movies')?></a> | <a href="video_search.php"><?= _('Search in storages')?></a>
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
            <td>IP</td>
            <td><?= _('Home path')?></td>
            <td>Max online</td>
            <td>&nbsp;</td>
        </tr>
        <? foreach ($storages as $storage){
                echo '<tr ';
                
                if ($storage['for_moderator']){
                    echo 'style="background-color: #ffecec;"';
                }
                
                echo '>';
                echo '<td>'.$storage['id'].'</td>';
                echo '<td>'.$storage['storage_name'].'</td>';
                echo '<td>'.$storage['storage_ip'].'</td>';
                echo '<td>'.$storage['nfs_home_path'].'</td>';
                echo '<td>'.$storage['max_online'].'</td>';
                echo '<td>';
                if ($storage['status'] == 1){
                    $status_str = 'on';
                    $color = 'Green';
                    $new_status=0;
                }else{
                    $status_str = 'off';
                    $color = 'Red';
                    $new_status=1;
                }
                echo '<a href="?status='.$new_status.'&id='.$storage['id'].'" style="color:'.$color.'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to change the status of the storage %s?'), $storage['storage_name']).'\')){return true}else{return false}">'.$status_str.'</a>&nbsp;';
                echo '<a href="?reset_cache=1&id='.$storage['id'].'" style="color:grey" onclick="if(confirm(\''.sprintf(_('Are you sure you want to reset the cache of the storage %s?'), $storage['storage_name']).'\')){return true}else{return false}">'._('clear cache').'</a>&nbsp;';
                echo '<a href="?edit=1&id='.$storage['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$storage['id'].'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to delete storage %s?'), $storage['storage_name']).'\')){return true}else{return false}">del</a>';
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
                    <td><input type="text" name="storage_name" value="<?echo @$edit_storage['storage_name']?>" <? if (!empty($_GET['id'])) echo "readonly" ?>/></td>
                </tr>
                <tr>
                    <td>IP</td>
                    <td><input type="text" name="storage_ip" value="<?echo @$edit_storage['storage_ip']?>"/></td>
                </tr>
                <tr>
                    <td>Apache port</td>
                    <td><input type="text" name="apache_port" value="<?echo empty($edit_storage['apache_port']) ? '88' : $edit_storage['apache_port']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('Home path')?></td>
                    <td><input type="text" name="nfs_home_path" value="<?echo @$edit_storage['nfs_home_path']?>"/></td>
                </tr>
                <tr>
                    <td>Max online</td>
                    <td><input type="text" name="max_online" value="<?echo @$edit_storage['max_online']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('User-Agent Filter')?></td>
                    <td><input type="text" name="user_agent_filter" value="<?echo @$edit_storage['user_agent_filter']?>"/></td>
                </tr>
                <tr>
                    <td><?= _('Storing content')?></td>
                    <td><input type="checkbox" name="for_simple_storage" value="1" <? if(@$edit_storage['for_simple_storage']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td><?= _('Not available for MAG100')?></td>
                    <td><input type="checkbox" name="not_for_mag100" value="1" <? if(@$edit_storage['not_for_mag100']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td style="vertical-align: top"><?= _('Record TV')?></td>
                    <td>
                        <input type="checkbox" class="for_records" name="for_records" value="1" <? if(@$edit_storage['for_records']){ echo 'checked="checked"'; } ?> onchange="this.checked ? document.getElementById('fake_tv_archive').style.display = '' : document.getElementById('fake_tv_archive').style.display = 'none'"/>
                        <!--<span id="wowza_server" style="margin-left: 5px; display: <?/*echo @$edit_storage['for_records'] ? '' : 'none' */?>">
                            Wowza server
                            <input type="checkbox" name="wowza_server" value="1" <?/* if(@$edit_storage['wowza_server']){ echo 'checked="checked"'; } */?> onchange="this.checked ? document.getElementById('archive_playback_row').style.display = '' : document.getElementById('archive_playback_row').style.display = 'none'"/>
                        </span>-->
                        <div class="flussonic_server" style="text-align: right; margin-left: 5px; display: <?echo @$edit_storage['for_records'] ? '' : 'none' ?>">
                            Flussonic server
                            <input type="checkbox" name="flussonic_server" value="1" <? if(@$edit_storage['flussonic_server']){ echo 'checked="checked"'; } ?> />
                        </div>
                        <div class="fake_tv_archive" style="text-align: right; margin-left: 5px; display: <?echo @$edit_storage['for_records'] ? '' : 'none' ?>">
                            <?= _('Emulation')?>
                            <input type="checkbox" name="fake_tv_archive" value="1" <? if(@$edit_storage['fake_tv_archive']){ echo 'checked="checked"'; } ?> />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><?= _('External')?></td>
                    <td><input disabled="disabled" type="checkbox" name="external" value="1" <? if(@$edit_storage['external']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td><?= _('Only moderators')?></td>
                    <td><input type="checkbox" name="for_moderator" value="1" <? if(@$edit_storage['for_moderator']){ echo 'checked="checked"'; } ?>/></td>
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