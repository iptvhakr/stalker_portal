<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del']){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('help_city_info', array('id' => intval($_GET['id'])));

    header("Location: help_city_info.php");
    exit;
}

if (!$error){
    
    if (@$_GET['save'] && !$error){
    
        if(@$_POST['title'] && @$_POST['number']){

            Admin::checkAccess(AdminAccess::ACCESS_CREATE);

            Mysql::getInstance()->insert('help_city_info', array(
                'num'    => @$_POST['num'],
                'title'  => @$_POST['title'],
                'number' => @$_POST['number']
            ));

            header("Location: help_city_info.php");
            exit;
        }
        else{
            $error = _('Error: all fields are required');
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_POST['title'] && @$_POST['number']){

            Admin::checkAccess(AdminAccess::ACCESS_EDIT);

            Mysql::getInstance()->update('help_city_info',
                array(
                    'title'  => $_POST['title'],
                    'num'    => $_POST['num'],
                    'number' => $_POST['number']
                ),
                array('id' => intval(@$_GET['id']))
            );

            header("Location: help_city_info.php");
            exit;
        }
        else{
            $error = _('Error: all fields are required');
        }
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
<title><?= _('Information services')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Information services')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="city_info.php"><?= _('Emergency services')?></a> | <a href="other_city_info.php"><?= _('Other services')?></a>
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

$all_info = Mysql::getInstance()->from('help_city_info')->orderby('num')->get();

echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>#</b></td>";
echo "<td class='list'><b>"._('Department')."</b></td>";
echo "<td class='list'><b>"._('Number')."</b></td>";
echo "</tr>";
while($arr = $all_info->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['num']."</td>";
    echo "<td class='list'>".$arr['title']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"help_city_info.php?del=1&id=".$arr['id']."\"}'>del</a>&nbsp;&nbsp;\n";
    
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){

    $arr = Mysql::getInstance()->from('help_city_info')->where(array('id' => intval($_GET['id'])))->get()->first();

    if (!empty($arr)){
        $title  = $arr['title'];
        $number = $arr['number'];
        $num    = $arr['num'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_');
    
    id = document.getElementById('id').value;
    
    action = 'help_city_info.php?id='+id;

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
            <input id="num" name="num" size="10" type="text" value="<? echo @$num ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Department')?>:
           </td>
           <td>
            <input type="text" name="title" size="50" id="title" value="<? echo @$title ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Phone number')?>:
           </td>
           <td>
            <input id="number" name="number" size="50" type="text" value="<? echo @$number ?>">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="save()">&nbsp;<input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='help_city_info.php'">
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