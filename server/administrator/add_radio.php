<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del']){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('radio', array('id' => intval(@$_GET['id'])));

    header("Location: add_radio.php");
    exit;
}

if (isset($_GET['status']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    Mysql::getInstance()->update('radio',
        array('status' => intval(@$_GET['status'])),
        array('id' => intval(@$_GET['id']))
    );

    header("Location: add_radio.php");
    exit;
}

if (!$error){
    
    if (@$_POST['number'] && !check_number($_POST['number']) && !@$_GET['update']){
        $error = sprintf(_('Error: channel with number "%s" is already in use'), intval($_POST['number']));
    }
    
    if (@$_GET['save'] && !$error){
    
        if(@$_GET['cmd'] && @$_GET['name']){

            Admin::checkAccess(AdminAccess::ACCESS_CREATE);

            Mysql::getInstance()->insert('radio', array(
                'name'              => @$_POST['name'],
                'number'            => @$_POST['number'],
                'volume_correction' => @$_POST['volume_correction'],
                'cmd'               => @$_POST['cmd']
            ));

            header("Location: add_radio.php");
            exit;
        }
        else{
            $error = _('Error: all fields are required');
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_GET['cmd'] && @$_GET['name']){

            Admin::checkAccess(AdminAccess::ACCESS_EDIT);

            Mysql::getInstance()->update('radio',
                array(
                    'name'              => $_POST['name'],
                    'cmd'               => $_GET['cmd'],
                    'volume_correction' => $_POST['volume_correction'],
                    'number'            => $_POST['number']
                ),
                array('id' => intval(@$_GET['id']))
            );

            header("Location: add_radio.php");
        }
        else{
            $error = _('Error: all fields are required');
        }
    }
}

function check_number($num){

    $radio = Mysql::getInstance()->from('radio')->where(array('number' => intval($num)))->get()->first();

    return empty($radio);
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
</style>
<title>
<?= _('RADIO channels')?>
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('RADIO channels')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.html"><< <?= _('Back')?></a>
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
<?

$all_radio = Mysql::getInstance()->from('radio')->orderby('number')->get()->all();

echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>id</b></td>";
echo "<td class='list'><b>"._('Number')."</b></td>";
echo "<td class='list'><b>"._('Name')."</b></td>";
echo "<td class='list'><b>"._('URL')."</b></td>";
echo "</tr>";
foreach ($all_radio as $arr){

    echo "<tr>";
    echo "<td class='list'>".$arr['id']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['cmd']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='?del=1&id=".$arr['id']."'>del</a>&nbsp;&nbsp;";
    if ($arr['status']){
        echo "<a href='?status=0&id=".$arr['id']."'><font color='Green'>on</font></a>&nbsp;&nbsp;";
    }else{
        echo "<a href='?status=1&id=".$arr['id']."'><font color='Red'>off</font></a>&nbsp;&nbsp;";
    }
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){

    $arr = Mysql::getInstance()->from('radio')->where(array('id' => intval(@$_GET['id'])))->get()->first();

    if (!empty($arr)){
        $name              = $arr['name'];
        $number            = $arr['number'];
        $cmd               = $arr['cmd'];
        $status            = $arr['status'];
        $volume_correction = $arr['volume_correction'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    cmd = document.getElementById('cmd').value
    id = document.getElementById('id').value
    
    action = 'add_radio.php?name='+name+'&cmd='+cmd+'&id='+id

    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }

    form_.setAttribute('action', action);
    form_.setAttribute('method', 'POST');
    form_.submit()
}

function popup(src){
     window.open( src, 'win_'+src, 'width=300,height=200,toolbar=0,location=0,directories=0,menubar=0,scrollbars=0,resizable=1,status=0,fullscreen=0')
}
</script>
<br>
<table align="center" class='list'>
<tr>
    <td>
    &nbsp;
    </td>
</tr>
<tr>
    <td>
    <form id="form_" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            <?= _('Number')?>:
           </td>
           <td>
            <input type="text" name="number" id="number" value="<? echo @$number ?>"  maxlength="3">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Name')?>:
           </td>
           <td>
            <input type="text" name="name" size="50" id="name" value="<? echo @$name ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('URL')?>:
           </td>
           <td>
            <input id="cmd" name="cmd" size="50" type="text" value="<? echo @$cmd ?>">
           </td>
        </tr>
        <tr>
            <td align="right">
                <?= _('Volume correction')?>:
            </td>
            <td>
                <input id="volume_correction" name="volume_correction" size="50" type="text" value="<? echo @$volume_correction ?>">
            </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="save()">&nbsp;<input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='add_radio.php'">
           </td>
        </tr>
    </table>
    </form>
    <a name="form"></a>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>