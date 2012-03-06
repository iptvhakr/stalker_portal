<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

if (!check_access(array(3))){
    exit;
}

$error = '';

$db = new Database();

moderator_access();


if (@$_GET['save']){
    
    $sub_str = '';
    $bonus_str = '';
    $uid = @$_POST['uid'];
    $sub = @$_GET['sub'];

    $bonus = @$_GET['bonus'];
    $stb = Stb::getInstance();
    
    if (!is_array($sub)){
        $sub = array();
    }
    $sub_str = base64_encode(serialize($sub));
    
    if (!is_array($bonus)){
        $bonus = array();
    }
    $bonus_str = base64_encode(serialize($bonus));
    
    $sql = "select * from itv_subscription where uid=$uid";
    $rs  = $db->executeQuery($sql);
    
    if ($rs->getRowCount() > 0){
        $sql = "update itv_subscription set sub_ch='$sub_str', bonus_ch='$bonus_str', addtime=NOW() where uid=$uid";
    }else{
        $sql = "insert into itv_subscription (uid, sub_ch, bonus_ch, addtime) value ($uid, '$sub_str', '$bonus_str', NOW())";
    }
    
    $rs = $db->executeQuery($sql);
    if (!$db->getLastError()){
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendUpdateSubscription();
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendMsg('Каналы обновлены согласно подписке.');
        
        js_redirect('profile.php?id='.$uid, 'Подписка сохранена');
    }else{
        echo 'ошибка';
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
    var _sel = document.getElementById('sel');
    var _bonus = document.getElementById('bonus');
    var order = '';
    /*if (_sel.options.length < 5){
        alert("Подписка должна состоять минимум из 5 каналов")
        return 0
    }*/
    for (i=0; i<_sel.options.length; i++){
        order += 'sub[]=' + _sel.options[i].value + '&';
    }
    for (i=0; i<_bonus.options.length; i++){
        order += 'bonus[]=' + _bonus.options[i].value + '&';
    }
    //alert(order);
    form_ = document.getElementById('sub_form')
    
    form_.action = form_.action +'?save=1&'+ order
    form_.method = 'POST'
    form_.submit();
}
</script>
<?
$id = intval(@$_GET['id']);
$sub_ch   = get_sub_channels();
$bonus_ch = get_bonus_channels();

function get_all_channels_opt(){
    global $db, $sub_ch, $bonus_ch;
    $opt = '';
    $total_arr = array_merge($sub_ch, $bonus_ch);
    if (count($total_arr) > 0){
        $all_sub_str = join(",",$total_arr);
        $sql = "select * from itv where base_ch=0 and id not in ($all_sub_str)";
    }else{
        $sql = "select * from itv where base_ch=0";
    }
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr = $rs->getCurrentValuesAsHash();
        $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
    }
    return $opt;
}

function get_sub_channels_opt(){
    global $db, $sub_ch;
    $opt = '';
    
    if (count($sub_ch) > 0){
        $sub_str = join(",",$sub_ch);
        
        $sql = "select * from itv where base_ch=0 and id in ($sub_str)";
        
        $rs = $db->executeQuery($sql);
        while(@$rs->next()){
            $arr = $rs->getCurrentValuesAsHash();
            $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
        }
        return $opt;
    }else{
        return '';
    }
}

function get_bonus_channels_opt(){
    global $db, $bonus_ch;
    $opt = '';
    
    if (count($bonus_ch) > 0){
        $bonus_str = join(",",$bonus_ch);
        
        $sql = "select * from itv where base_ch=0 and id in ($bonus_str)";
        echo $sql;
        $rs = $db->executeQuery($sql);
        while(@$rs->next()){
            $arr = $rs->getCurrentValuesAsHash();
            $opt .= "<option value={$arr['id']}>{$arr['number']}. {$arr['name']}\n";
        }
        return $opt;
    }else{
        return '';
    }
}

?>
<title>Подписка на каналы</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Подписка на каналы&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="profile.php?id=<?echo @$_GET['id']?>"><< Назад</a> 
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
        Все каналы
        <select multiple id="all" class="all">
        <? echo get_all_channels_opt() ?>
        </select>
        <input type="hidden" name="uid" id="uid" value="<? echo @$_GET['id'] ?>">
        </td>


        <td width="34%" align="center" height="100%">
        <table border="0" height="100%">
        <tr>
            <td height="80%" align="center">
            <input type="button" value="Все >>"  onclick="add_all()"/><br>
            <input type="button" value=">>"  onclick="add()"/><br>
            <input type="button" value="<<" onclick="del()"/><br>
            <input type="button" value="<< Все" onclick="del_all()"/>
            </td>
        </tr>
        <tr>
            <td height="10%" align="center">
            <input type="button" value=">>"  onclick="bonus_add()"/><br><input type="button" value="<<" onclick="bonus_del()"/>
            </td>
        </tr>
        <tr>
            <td height="10%" align="center" valign="bottom">
            <input type="button" value="Сохранить подписку" onclick="sub(this.form)"/>
            </td>
        </tr>
        </table>
        </td>
        
        <td width="33%" align="center" valign="top">
        Подписка
        <select multiple id="sel" name="order" class="sub">
        <? echo get_sub_channels_opt() ?>
        </select>
        <br><br><br>
        Бонус
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