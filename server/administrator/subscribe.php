<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);


if (@$_GET['save']){

    Admin::checkAccess(AdminAccess::ACCESS_EDIT);

    //var_dump($_POST);exit;

    $sub_str = '';
    $bonus_str = '';
    $uid = @$_POST['uid'];
    //$sub = @$_GET['sub'];
    //$sub = @$_POST['sub_ch'];
    if (empty($_POST['sub_ch'])){
        $sub = array();
    }else{
        $sub = explode(",", $_POST['sub_ch']);
    }

    //$bonus = @$_GET['bonus'];
    if (empty($_POST['bonus_ch'])){
        $bonus = array();
    }else{
        $bonus = explode(",", $_POST['bonus_ch']);
    }

    /*if (!is_array($sub)){
        $sub = array();
    }*/
    $sub_str = base64_encode(serialize($sub));
    
    /*if (!is_array($bonus)){
        $bonus = array();
    }*/
    $bonus_str = base64_encode(serialize($bonus));

    $itv_subscription = Mysql::getInstance()->from('itv_subscription')->where(array('uid' => $uid))->get()->first();
    
    if (!empty($itv_subscription )){

        $result = Mysql::getInstance()->update('itv_subscription',
            array(
                'sub_ch'   => $sub_str,
                'bonus_ch' => $bonus_str,
                'addtime'  => 'NOW()'
            ),
            array('uid' => $uid)
        )->result();
    }else{

        $result = Mysql::getInstance()->insert('itv_subscription', array(
            'uid'      => $uid,
            'sub_ch'   => $sub_str,
            'bonus_ch' => $bonus_str,
            'addtime'  => 'NOW()'
        ))->insert_id();
    }
    

    if ($result){
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendUpdateSubscription();
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendMsg(_('Updated according to the subscription channels.'));
        
        js_redirect('profile.php?id='.$uid, _('Subscription saved'));
    }else{
        echo _('error');
    }
    exit;
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
select {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	width: 200px;
	border: thin 1;
}
select.all {
	height: 500px;
}
select.sub {
	height: 350px;
}
select.bonus {
	height: 100px;
}
</style>
<script>
function add(){
    var all = document.getElementById('all');
    var sel = document.getElementById('sel');
    if (all.selectedIndex >= 0){
        var item = all.options[all.selectedIndex].value;
        var text = all.options[all.selectedIndex].text;
        all.options[all.selectedIndex] = null
        sel.options[sel.options.length] = new Option(text, item);
    }
}

function add_all(){
    var all = document.getElementById('all');
    var sel = document.getElementById('sel');
    
    for (i=0; i < all.options.length; i++){
        sel.options[sel.options.length] = new Option(all.options[i].text, all.options[i].value);
    }
    all.options.length = 0;
}

function del(){
    var all = document.getElementById('all');
    var sel = document.getElementById('sel');
    if (sel.selectedIndex >= 0){
        var item = sel.options[sel.selectedIndex].value;
        var text = sel.options[sel.selectedIndex].text;
        sel.options[sel.selectedIndex] = null
        all.options[all.options.length] = new Option(text, item);
    }
}

function del_all(){
    var all = document.getElementById('all');
    var sel = document.getElementById('sel');
    
    for (i=0; i < sel.options.length; i++){
        all.options[all.options.length] = new Option(sel.options[i].text, sel.options[i].value);
    }
    sel.options.length = 0;
}

function bonus_add(){
    var all = document.getElementById('all');
    var sel = document.getElementById('bonus');
    if (all.selectedIndex >= 0){
        var item = all.options[all.selectedIndex].value;
        var text = all.options[all.selectedIndex].text;
        all.options[all.selectedIndex] = null
        sel.options[sel.options.length] = new Option(text, item);
    }
}

function bonus_del(){
    var all = document.getElementById('all');
    var sel = document.getElementById('bonus');
    if (sel.selectedIndex >= 0){
        var item = sel.options[sel.selectedIndex].value;
        var text = sel.options[sel.selectedIndex].text;
        sel.options[sel.selectedIndex] = null
        all.options[all.options.length] = new Option(text, item);
    }
}

function sub(form){
    var _sel   = document.getElementById('sel');
    var _bonus = document.getElementById('bonus');
    var order  = '';

    var sub_ch = [];

    for (var i=0; i<_sel.options.length; i++){
        order += 'sub[]=' + _sel.options[i].value + '&';
        sub_ch.push(_sel.options[i].value);
    }

    document.getElementById('sub_ch').value = sub_ch.join(",");


    var bonus_ch = [];

    for (i=0; i<_bonus.options.length; i++){
        order += 'bonus[]=' + _bonus.options[i].value + '&';
        bonus_ch.push(_bonus.options[i].value);
    }

    document.getElementById('bonus_ch').value = bonus_ch.join(",");


    var form_ = document.getElementById('sub_form');

    form_.setAttribute('action', form_.action +'?save=1');
    form_.setAttribute('method', 'POST');
    form_.submit();
}
</script>
<?
$id = intval(@$_GET['id']);
$sub_ch   = get_sub_channels();
$bonus_ch = get_bonus_channels();

function get_all_channels_opt(){
    global $sub_ch, $bonus_ch;
    $opt = '';
    $total_arr = array_merge($sub_ch, $bonus_ch);
    if (count($total_arr) > 0){
        $all_sub_str = join(",",$total_arr);
        $sql = "select * from itv where base_ch=0 and id not in ($all_sub_str)";
    }else{
        $sql = "select * from itv where base_ch=0";
    }
    $channels = Mysql::getInstance()->query($sql);
    while($arr = $channels->next()){
        $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
    }
    return $opt;
}

function get_sub_channels_opt(){
    global $sub_ch;
    $opt = '';
    
    if (count($sub_ch) > 0){
        $channels = Mysql::getInstance()
            ->from('itv')
            ->where(array(
                'base_ch' => 0
            ))
            ->in('id', $sub_ch)
            ->get();

        while($arr = $channels->next()){

            $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
        }
        return $opt;
    }else{
        return '';
    }
}

function get_bonus_channels_opt(){
    global $bonus_ch;
    $opt = '';
    
    if (count($bonus_ch) > 0){

        $channels = Mysql::getInstance()
            ->from('itv')
            ->where(array(
                'base_ch' => 0
            ))
            ->in('id', $bonus_ch)
            ->get();

        while($arr = $channels->next()){
            $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
        }
        return $opt;
    }else{
        return '';
    }
}

?>
<title><?= _('TV channels subscription')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('TV channels subscription')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="profile.php?id=<?echo @$_GET['id']?>"><< <? _('Back')?></a>
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

<table width="90%" height="85%" align="center">
<tr align="center" valign="middle">
    <td valign="middle">
    <form method="POST" id="sub_form" action="subscribe.php">
    <table align="center" border="1">
    <tr> 
        <td width="33%" align="center" valign="top">
        <?= _('All channels')?>
        <select multiple id="all" class="all">
        <? echo get_all_channels_opt() ?>
        </select>
        <input type="hidden" name="uid" id="uid" value="<? echo @$_GET['id'] ?>">
        </td>


        <td width="34%" align="center" height="100%">
        <table border="0" height="100%">
        <tr>
            <td height="80%" align="center">
            <input type="button" value="<?= htmlspecialchars(_('All'), ENT_QUOTES)?> >>"  onclick="add_all()"/><br>
            <input type="button" value=">>"  onclick="add()"/><br>
            <input type="button" value="<<" onclick="del()"/><br>
            <input type="button" value="<< <?= _('All')?>" onclick="del_all()"/>
            </td>
        </tr>
        <tr>
            <td height="10%" align="center">
            <input type="button" value=">>"  onclick="bonus_add()"/><br><input type="button" value="<<" onclick="bonus_del()"/>
            </td>
        </tr>
        <tr>
            <td height="10%" align="center" valign="bottom">
            <input type="hidden" name="sub_ch" id="sub_ch"/>
            <input type="hidden" name="bonus_ch" id="bonus_ch"/>
            <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="sub(this.form)"/>
            </td>
        </tr>
        </table>
        </td>
        
        <td width="33%" align="center" valign="top">
        <?= _('Subscription')?>
        <select multiple id="sel" name="order" class="sub">
        <? echo get_sub_channels_opt() ?>
        </select>
        <br><br><br>
        <?= _('Bonus')?>
        <select multiple id="bonus" name="bonus" class="bonus">
        <? echo get_bonus_channels_opt() ?>
        </select>
        </td>
        
    </tr>
    </table>
    </form>
    </td>
</tr>
</table>

</td>
</tr>
</table>