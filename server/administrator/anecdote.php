<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del']){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('anec', array('id' => intval($_GET['id'])));

    header("Location: anecdote.php");
    exit();
}
if (!$error){
    
    if (@$_GET['save'] && !$error){
    
        if(@$_POST['anec_body']){

            Admin::checkAccess(AdminAccess::ACCESS_CREATE);

            Mysql::getInstance()->insert('anec', array(
                'anec_body' => @$_POST['anec_body'],
                'added'     => 'NOW()'
            ));

            Mysql::getInstance()->update('updated_places', array('anec' => 1));
            
            header("Location: anecdote.php");
            exit();
        }
        else{
            $error = _('Error: all fields are required');
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_POST['anec_body']){

            Admin::checkAccess(AdminAccess::ACCESS_EDIT);

            Mysql::getInstance()->update('anec',
                array(
                    'anec_body' => $_POST['anec_body'],
                    'added'     => 'NOW()'
                ),
                array('id' => intval(@$_GET['id']))
            );

            header("Location: anecdote.php");
            exit();
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
<title>
<?= _('Jokes')?>
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Jokes')?>&nbsp;</b></font>
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
<td>
<?

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 10;

$total_items = Mysql::getInstance()->query("select * from anec")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=ceil($total_items/$MAX_PAGE_ITEMS);

$query = "select * from anec order by id desc LIMIT $page_offset, $MAX_PAGE_ITEMS";

$all_anecs = Mysql::getInstance()->query($query)->all();

echo "<table align='center' class='list' border='0' cellpadding='3' cellspacing='0'>";
foreach($all_anecs as $arr){

    echo "<tr align='center'>";
    
    echo "<table align='center' class='list' width='400'>";
    echo "<tr>";
    echo "<td>";
    
    echo $arr['added']." <a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"anecdote.php?del=1&id=".$arr['id']."\"}'>del</a>&nbsp;&nbsp;\n";
    echo "<br><br>".nl2br($arr['anec_body']);
    
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "<br>";
    
    echo "</tr>";
}
echo "</table>";
echo "<center>".page_bar()."</center>";

if (@$_GET['edit']){

    $arr = Mysql::getInstance()->from('anec')->where(array('id' => intval($_GET['id'])))->get()->first();

    if (!empty($arr)){
        $anec_body = $arr['anec_body'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_');
    
    id = document.getElementById('id').value;
    
    action = 'anecdote.php?id='+id;

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
           <td align="right" valign="top">
            <?= _('Joke')?>:
           </td>
           <td>
            <textarea name="anec_body" id="anec_body" cols="40" rows="10"><? echo @$anec_body ?></textarea>
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="save()">&nbsp;<input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='anecdote.php'">
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