<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

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
<title><?= _('Karaoke Archive')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
<tr>
    <td align="center" valign="middle" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Karaoke Archive')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="stat_moderators.php"><< <?= _('Back')?></a>
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

if (@$_GET['id']){
    
    $archive_id = intval($_GET['id']);
    
    $sql = "select * from administrators";
    
    if (!Admin::isPageActionAllowed()){
        $sql .= " where login='".$_SESSION['login']."'";
    }

    $administrators = Mysql::getInstance()->query($sql)->all();
    
    foreach($administrators as $arr){

        $uid = $arr['id']
        ?>
        
        <table border="0" align="center" width="760">
        <tr>
        <td align="center">
        <b><?echo $arr['login'] ?></b>
        <table border="1" width="100%" cellspacing="0">
            <tr>
                <td>#</td>
                <td><?= _('Title')?></td>
                <td><?= _('Performer')?></td>
                <td><?= _('Turn on date')?></td>
            </tr>
            <?

            $done_karaoke = Mysql::getInstance()->from('karaoke')
                ->where(array(
                    'accessed' => 1,
                    'status'   => 1,
                    'archived' => $archive_id,
                    'add_by'   => $uid
                ))
                ->get();
            
            $num = 0;
            while($arr_done = $done_karaoke->next()){
                $num++;
                echo "<tr>";
                echo "<td>$num</td>";
                echo "<td>".$arr_done['name']."</td>";
                echo "<td nowrap>".$arr_done['singer']."&nbsp;</td>";
                echo "<td nowrap>".$arr_done['added']."</td>";
                echo "</tr>";
        }
        ?>
    </table>
    <br>
    <br>
    
<br>
<br>
<hr>
<br>
<br>
        
    <?
    }
}
else{
    $page=@$_REQUEST['page']+0;
    $MAX_PAGE_ITEMS = 30;

    $total_items = Mysql::getInstance()->count()->from('karaoke_archive')->get()->counter();
    
    $page_offset=$page*$MAX_PAGE_ITEMS;
    $total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);
    
    $sql = "select * from karaoke_archive order by year, month LIMIT $page_offset, $MAX_PAGE_ITEMS";

    $archive = Mysql::getInstance()->query($sql);
    
    while($arr = $archive->next()){
        ?>
        
        <table border="1" width="200" cellspacing="0">
          <tr>
            <td align="center">
               <a href="karaoke_archive.php?id=<?echo $arr['id']?>"><?echo $arr['year'].'-'.$arr['month']?></a>
            </td>
          </tr>
        </table>
        <br>
    
    <?
    }
    echo "<table width='600' align='center' border=0>\n";
    echo "<tr>\n";
    echo "<td width='100%' align='center'>\n";
    echo page_bar();
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
}
?>

</td>
</tr>
</table>
</body>
</html>