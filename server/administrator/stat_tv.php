<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
echo '</pre>';

$search = @$_GET['search'];
$letter = @$_GET['letter'];
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="../adm/js/jquery-1.7.1.min.js"></script>

<script type="text/javascript">
    $(function(){

        var selected_locale = '<?= isset($_GET['locale']) ? $_GET['locale'] : ''?>';

        $('.locale option[value="'+selected_locale+'"]').attr('selected', 'selected');

        $('.locale').change(function(){
            window.location = 'stat_tv.php?locale='+$(this).find('option:selected').val();
        });

    });
</script>

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
<title><?= _('TV views statistics per month')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('TV views statistics per month')?>&nbsp;</b></font>
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
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = '';

if (!empty($_GET['locale'])) {
    $where = ' and user_locale="'.$_GET['locale'].'"';
}

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

$query = "select itv_id, name, count(played_itv.id) as counter from played_itv,itv where played_itv.itv_id=itv.id and playtime>'$from_time' $where group by itv_id";
//echo $query;
$total_items = Mysql::getInstance()->query($query)->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = $query." order by counter desc LIMIT $page_offset, $MAX_PAGE_ITEMS";

$played_itv = Mysql::getInstance()->query($query);

$locales = Mysql::getInstance()
    ->select('user_locale')
    ->from('played_itv')
    ->groupby('user_locale')
    ->orderby('user_locale')
    ->get()
    ->all('user_locale');

?>
<div style="width: 600px; margin: 0 auto">
    <?= _('User language')?>: <select class="locale">
        <option value="">---</option>
        <?
            foreach ($locales as $locale){
                if ($locale){
                    echo '<option value="'.$locale.'">' . strtoupper(substr($locale, 0, 2)) . '</option>';
                }
            }
        ?>
    </select>
</div>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0' width='600'>\n";
echo "<tr>";
echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>"._('Title')."</b></td>\n";
echo "<td class='list'><b>"._('Views')."</b></td>\n";
echo "</tr>\n";
while($arr = $played_itv->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['itv_id']."</td>\n";
    echo "<td class='list'>".$arr['name']."</td>\n";
    echo "<td class='list'>".$arr['counter']."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='700' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo page_bar();
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";