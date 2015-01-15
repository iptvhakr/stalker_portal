<?php
session_start();

ob_start();

include "./common.php";

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
<title>
<?= _('Video views statistics by genres per month')?>
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Video views statistics by genres per month')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="stat_video.php"><< <?= _('Back')?></a>
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
function count_rate($sarr){
    if (is_array($rate = unserialize($sarr))){
        return array_sum($rate);
    }else{
        return 0;
    }
}

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

$genres = Mysql::getInstance()->from('genre')->get()->all();

$from = date("Y-m-d 00:00:00", time()-60*60*24*30);

echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>"._('Genre')."</b></td>\n";
echo "<td class='list'><b>"._('Views')."</b></td>\n";
echo "<td class='list'><b>"._('Total movies')."</b></td>\n";
echo "<td class='list'><b>%</b></td>\n";
echo "</tr>\n";

foreach ($genres as $genre){

    $total_movies_in_genre = Mysql::getInstance()
        ->from('video')
        ->count()
        ->where(array('genre_id_1' => $genre['id'], 'genre_id_2' => $genre['id'], 'genre_id_3' => $genre['id'], 'genre_id_4' => $genre['id']), 'OR ')
        ->get()
        ->counter();

    //Mysql::$debug = true;

    $played_movies_in_genre = Mysql::getInstance()
        ->from('played_video')
        ->count()
        ->join('video', 'video.id', 'played_video.video_id', 'left')
        ->where(array('playtime>' => $from))
        ->where(array('genre_id_1' => $genre['id'], 'genre_id_2' => $genre['id'], 'genre_id_3' => $genre['id'], 'genre_id_4' => $genre['id']), 'OR ')
        ->get()->counter();

    $ratio = $total_movies_in_genre == 0 ? 0 : round(($played_movies_in_genre / $total_movies_in_genre)*100, 2);

    echo "<tr>";
    //echo "<td class='list'>".$arr['genre_title']."</td>\n";
    echo "<td class='list'>".$genre['title']."</td>\n";
    echo "<td class='list'>".$played_movies_in_genre."</td>\n";
    echo "<td class='list'>".$total_movies_in_genre."</td>\n";
    echo "<td class='list'>".$ratio."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='700' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";