<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

moderator_access();

//echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
//echo '</pre>';

$search = @$_GET['search'];
$letter = @$_GET['letter'];

if (!empty($_POST['change_tariff_plan'])){

    Mysql::getInstance()->update('users', array('tariff_plan_id' => intval($_POST['tariff_plan_id'])), array('id' => intval($_GET['id'])));

    if (Config::get('enable_tariff_plans')){
        $event = new SysEvent();
        $event->setUserListById(array(intval($_GET['id'])));
        $event->sendMsgAndReboot(_('Tariff plan is changed, please restart your STB'));
    }

    header("Location: profile.php?id=".@$_GET['id']);
    exit;
}

if (@$_POST['save']){
    
    $stb_groups = new StbGroup();
    $member = $stb_groups->getMemberByUid(intval($_GET['id']));
    
    if (empty($member)){
        $stb_groups->addMember(array('mac' => Middleware::normalizeMac($_POST['mac']), 'uid' => Middleware::getUidByMac($_POST['mac']), 'stb_group_id' => $_POST['group_id']));
    }else{
        $stb_groups->setMember(array('stb_group_id' => $_POST['group_id']), $member['id']);
    }
    
    header("Location: profile.php?id=".@$_GET['id']);
    exit;
}

if (@$_POST['account']){

    $stb_groups = new StbGroup();
    $member = $stb_groups->getMemberByUid(intval($_GET['id']));

    if (empty($member)){
        $stb_groups->addMember(array('mac' => Middleware::normalizeMac($_POST['mac']), 'uid' => Middleware::getUidByMac($_POST['mac']), 'stb_group_id' => $_POST['group_id']));
    }else{
        $stb_groups->setMember(array('stb_group_id' => $_POST['group_id']), $member['id']);
    }

    Mysql::getInstance()->update('users',
        array(
            'fname'  => $_POST['fname'],
            'phone'  => $_POST['phone'],
            'ls'     => $_POST['ls']
        ),
        array('id' => intval($_GET['id'])));

    header("Location: profile.php?id=".@$_GET['id']);
    exit;
}


if (@$_GET['video_out']){
    $video_out = @$_GET['video_out'];
    $id = intval(@$_GET['id']);
    
    if ($video_out == 'svideo'){
        $new_video_out = 'svideo';
    }else{
        $new_video_out = 'rca';
    }
    $sql = "update users set video_out='$new_video_out' where id=$id";
    $rs=$db->executeQuery($sql);
    
    header("Location: profile.php?id=".@$_GET['id']);
    exit();
}

if (@$_GET['parent_password'] && $_GET['parent_password'] == 'default'){
    $id = intval(@$_GET['id']);
    
    $sql = "update users set parent_password='0000' where id=$id";
    $rs=$db->executeQuery($sql);
    
    header("Location: profile.php?id=".@$_GET['id']);
    exit();
}

if (@$_GET['fav_itv'] && $_GET['fav_itv'] == 'default'){
    $id = intval(@$_GET['id']);
    
    $sql = "update fav_itv set fav_ch='' where uid=$id";
    $rs=$db->executeQuery($sql);
    
    header("Location: profile.php?id=".@$_GET['id']);
    exit();
}

if (isset($_GET['set_services'])){
    $id = intval(@$_GET['id']);
    
    $set = intval($_GET['set_services']);
    if ($set == 0){
        
    }else{
        $set = 1;
    }
    
    $sql = "update users set additional_services_on=$set where id=$id";
    $rs=$db->executeQuery($sql);
    
    header("Location: profile.php?id=".@$_GET['id']);
    exit();
}

if (isset($_GET['id']) && isset($_GET['package_id']) && isset($_GET['subscribed'])){
    $id = intval($_GET['id']);
    $package_id = intval($_GET['package_id']);
    $subscribed = intval($_GET['subscribed']);

    $user = User::getInstance($id);

    if ($subscribed){
        $user->subscribeToPackage($package_id, null, true);
    }else{
        $user->unsubscribeFromPackage($package_id, null, true);
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
    color: #000000;
}
td.other {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
	border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
	background-color:#FFFFFF;
}

table.other {
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
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
<?
$id = intval(@$_GET['id']);

function get_video_out($video_out, $id){
    if ($video_out == 'rca'){
        $change_link = 'video_out=svideo';
        $change_v_out = 'S-video';
        $now_v_out = 'RCA';
    }else{
        $change_link = 'video_out=rca';
        $change_v_out = 'RCA';
        $now_v_out = 'S-video';
    }
    $link = '<a href="#" onclick="if(confirm(\''._('Change the video output on the').' '.$change_v_out.'?\')){document.location=\'profile.php?'.$change_link.'&id='.$id.'\'}">'.$now_v_out.'</a>';
    
    return $link;
}

function get_cost_sub_channels(){
    global $db,$id;
    
    $sub_ch = get_sub_channels();
    if (count($sub_ch) > 0){
        $sub_ch_str = join(",", get_sub_channels());
        $sql = "select SUM(cost) as total_cost from itv where id in ($sub_ch_str)";
        $rs = $db->executeQuery($sql);
        $total_cost = @$rs->getValueByName(0, 'total_cost');
        return $total_cost;
    }else{
        return 0;
    }
}

function additional_services_btn(){
    global $db,$id;
    
    $sql = "select * from users where id=".$id;
    $rs = $db->executeQuery($sql);
    $additional_services_on = @$rs->getValueByName(0, 'additional_services_on');
    if ($additional_services_on == 0){
        $color = 'red';
        $txt = _('Disabled');
        $set = 1;
    }else{
        $color = 'green';
        $txt = _('Enabled');
        $set = 0;
    }
    return '<a href="profile.php?id='.$id.'&set_services='.$set.'" style="color:'.$color.'"><b>'.$txt.'</b></a>';
}


$sql = "select * from users where id=$id";
$rs=$db->executeQuery($sql);

while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $user = $arr;
        $mac = $arr['mac'];
        $ip  = $arr['ip'];
        $video_out  = $arr['video_out'];
        $parent_password  = $arr['parent_password'];
        $tariff_plan_id  = $arr['tariff_plan_id'];
}

$rs=$db->executeQuery("select * from fav_itv where uid=".$id);
$fav_ch = $rs->getValueByName(0, 'fav_ch');

$fav_ch_arr = unserialize(base64_decode($fav_ch));

if (is_array($fav_ch_arr)){
    $fav_ch_count = count($fav_ch_arr);
}else{
    $fav_ch_count = 0;
}

$tariff_plans = Mysql::getInstance()->select('id, name')->from('tariff_plan')->orderby('name')->get()->all();

$users = User::getInstance($id);
//var_dump($users->getServicesByType('tv'));
//var_dump($users->getPackages());
$packages = $users->getPackages();

if (empty($packages)){
    $packages = array();
}

?>
<title><?= _('User profile')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('User profile')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="users.php"><< <?= _('Back')?></a> | <a href="userlog.php?id=<?echo $id?>"><?= _('Logs')?></a> | <a href="events.php?mac=<?echo $mac?>"><?= _('Events')?></a>
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

<table cellpadding="0" cellspacing="3" style="float:left;">
    <tr>
        <td class="other" width="320">
        <table>
            <tr>
                <td></td>
                <td><b><?echo check_keep_alive_txt($arr['keep_alive'])?></b></td>
            </tr>
            <tr>
                <td>mac:</td>
                <td><b><?echo $mac?></b></td>
            </tr>
            <tr>
                <td>ip:</td>
                <td><b><?echo $ip?></b></td>
            </tr>
            <tr>
                <td>v/out:</td>
                <td><b><?echo get_video_out($video_out, $id)?></b></td>
            </tr>
            <tr>
                <td>pass:</td>
                <td>[<?echo $parent_password?>] <a href="#" onclick="if(confirm('<?= _('Reset to default password?')?>')){document.location='profile.php?parent_password=default&id=<?echo $id?>'}"><?= _('Reset')?></a></td>
            </tr>
            <tr>
                <td><?= _('favorite tv')?>:</td>
                <td>[<? printf(_('%s channels'), $fav_ch_count)?>] <a href="#" onclick="if(confirm('<?= _('Reset favorite TV channels? The channels will be reset only if immediately restart the stb!')?>')){document.location='profile.php?fav_itv=default&id=<?echo $id?>'}"><?= _('Reset')?></a></td>
            </tr>
        </table>
        </td>
        <td>
        </td>
    </tr>
</table>

<form method="post">
    <table style="float:left;margin-top: 3px" class="other" cellpadding="0" cellspacing="3">
        <tr>
            <td>
                <?= _('Full name')?>:
            </td>
            <td>
                <input type="text" name="fname" value="<? echo $user['fname'] ?>"/>
            </td>
        </tr>
        <tr>
            <td>
                <?= _('Login')?>:
            </td>
            <td>
                <input type="text" name="login" value="<? echo $user['login'] ?>" disabled="disabled"/>
            </td>
        </tr>
        <tr>
            <td>
                <?= _('Account number')?>:
            </td>
            <td>
                <input type="text" name="ls" value="<? echo $user['ls'] ?>"/>
            </td>
        </tr>
        <tr>
            <td>
                <?= _('Last change of status')?>:
            </td>
            <td>
                <input type="text" name="" readonly="readonly" disabled="disabled" value="<? echo $user['last_change_status'] ?>"/>
            </td>
        </tr>
        <tr>
            <td>
                <?= _('Phone number')?>:
            </td>
            <td>
                <input type="text" name="phone" value="<? echo $user['phone'] ?>"/>
            </td>
        </tr>
        <tr>
            <td>
                <?= _('Group')?>:
            </td>
            <td>
                <select name="group_id">
                <option value="0">--------</option>
                <?

                $stb_groups = new StbGroup();
                $all_groups = $stb_groups->getAll();

                $member = $stb_groups->getMemberByUid(intval($_GET['id']));

                foreach ($all_groups as $group){
                    $selected = '';

                    if (!empty($member) && $member['stb_group_id'] == $group['id']){
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
            <td>
                <input type="submit" name="account" />
            </td>
        </tr>
    </table>
</form>

<? if (check_access(array(3)) || @$_SESSION['login'] == 'alex'){ ?>
<table cellpadding="0" cellspacing="3" width="641">
    <tr>
        <td class="other">
        <table align="center" width="80%">

            <? if (Config::getSafe('enable_tariff_plans', false)){?>
            <tr>
                <td align="center">
                    <form method="post">
                        <?= _('Tariff plan')?>:
                        <select name="tariff_plan_id">
                            <option value="0">---</option>
                            <?
                                foreach ($tariff_plans as $plan){
                                    if ($tariff_plan_id == $plan['id']){
                                        $selected = 'selected="selected"';
                                    }else{
                                        $selected = '';
                                    }

                                    echo '<option value="'.$plan['id'].'" '.$selected.'>'.$plan['name'].'</option>';
                                }

                            ?>
                        </select>
                        <input type="submit" name="change_tariff_plan" value="<?= _('Change')?>">
                    </form>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <?

                    if (empty($packages)){
                        echo _('No packages available');
                    }else{
                    ?>
                    <table align="center" class="list" cellspacing="0" cellpadding="3">
                        <caption><?= _('Packages')?></caption>
                        <tr>
                            <th>Название</th>
                            <th>Опциональный</th>
                            <th>Подписан</th>
                        </tr>
                        <?
                        foreach ($packages as $package){
                            echo '<tr>';
                            echo '<td><a href="services_packages.php?edit=1&id='.$package['package_id'].'">'.$package['name'].'</a></td>';
                            echo '<td>'.($package['optional'] ? 'yes' : 'no').'</td>';
                            if ($package['optional']){
                                echo '<td><a href="?id='.$id.'&package_id='.$package['package_id'].'&subscribed='.($package['subscribed'] ? 0 : 1).'">'.($package['subscribed'] ? 'yes' : 'no').'</a></td>';
                            }else{
                                echo '<td>'.($package['subscribed'] ? 'yes' : 'no').'</td>';
                            }
                            echo '</tr>';
                        }
                        ?>
                    </table>
                    <?}?>
                </td>
            </tr>

            <? }else{?>
            <tr align="center">
                <td><a href="subscribe.php?id=<?echo $id?>"><?= _('TV subscription')?></a> (<?echo kop2grn(get_cost_sub_channels())?>)</td>
            </tr>
            <tr align="center">
                <td><b><?= _('Additional services')?></b>: <? echo additional_services_btn() ?></td>
            </tr>
            <?}?>

        </table>
        </td>
        <td>
        </td>
    </tr>
</table>

<?}?>

</td>
</tr>
</table>

