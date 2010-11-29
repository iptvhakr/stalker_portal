<?php

session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

$search = @$_GET['search'];
$letter = @$_GET['letter'];

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
echo '</pre>';

if (!$_SERVER['QUERY_STRING']){
    unset($_SESSION['upload']);
}

if (isset($_GET['accessed']) && @$_GET['id']){
    set_video_clip_accessed(@$_GET['id'], @$_GET['accessed']);
    $id = $_GET['id'];
    if ($_GET['accessed'] == 1){
        chmod(VIDEO_CLIP_STORAGE_DIR.'/'.$id.'.mpg', 0444);
    }else{
        chmod(VIDEO_CLIP_STORAGE_DIR.'/'.$id.'.mpg', 0666);
    }
    header("Location: add_video_clip.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (isset($_GET['done']) && @$_GET['id']){
    set_video_clip_done(@$_GET['id'], @$_GET['done']);
    $id = $_GET['id'];
    
    header("Location: add_video_clip.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

$type = '';

if (!$error){
    
    if (@$_GET['save']){
    
        if(@$_GET['name'] && @$_POST['singer'] && @$_POST['genre_id']){
            $datetime = date("Y-m-d H:i:s");
            $query = "insert into video_clips (name,
                                           genre_id,
                                           singer,
                                           added,
                                           add_by
                                           ) 
                                  values ('".@$_GET['name']."',
                                           '".@$_POST['genre_id']."',
                                           '".@$_POST['singer']."',
                                           '".$datetime."',
                                           ".@$_SESSION['uid']."
                                           )";
            //echo $query;
            $rs=$db->executeQuery($query);
            
            unset($_SESSION['upload']);
            
            header("Location: add_video_clip.php?search=$search&letter=$letter");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update']){
        if(@$_GET['name']){

            $query = "update video_clips set name='".$_GET['name']."', 
                                       genre_id='".@$_POST['genre_id']."',  
                                       singer='".@$_POST['singer']."'
                                    where id=".intval(@$_GET['id']);
            
            //echo $query;
            $rs=$db->executeQuery($query);
            //var_dump($rs);
            unset($_SESSION['upload']);
            
            header("Location: add_video_clip.php?search=$search&letter=$letter");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
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
.list {
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
}
a {
	color:#0000FF;
	font-weight: bold;
	text-decoration:none;
}
a:link,a:visited {
	color:#5588FF;
	font-weight: bold;
}
a:hover {
	color:#0000FF;
	font-weight: bold;
	text-decoration:underline;
}
</style>
<title>
Редактирование списка ВИДЕО КЛИПОВ
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка видео клипов&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a>
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
$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = 'where administrators.id=video_clips.add_by ';
if ($search){
    $where .= 'and video_clips.name like "%'.$search.'%"';
}

if ($letter) {
	$where .= 'and video_clips.name like "'.$letter.'%"';
}

$query = "select * from video_clips, administrators $where";
//echo $query;
$rs = $db->executeQuery($query);
//var_dump($rs);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

function page_bar(){
	
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

function set_video_clip_accessed($id, $val){
    global $db;
    if ($id){
        $query = "update video_clips set accessed=$val, added=NOW() where id=$id";
        $db->executeQuery($query);
    }
}

function set_video_clip_done($id, $val){
    global $db;
    if ($id){
        $query = "update video_clips set done=$val, done_time=NOW() where id=$id";
        $db->executeQuery($query);
    }
}

function get_video_clip_accessed($id){
    global $db;
    
    $query = "select * from video_clips where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'accessed');
    return $accessed;
}

function get_done_video_clip($id){
	$db = Database::getInstance(DB_NAME);
	
	$query = "select * from video_clips where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'done');
    return $accessed;
}

function get_video_clip_accessed_color($id){
    if (get_video_clip_accessed($id)){
        $color = 'green';
        $accessed = 0;
        $txt = 'on';
    }else{
        $color = 'red';
        $accessed = 1;
        $txt = 'off';
    }
    if (check_access(array(1))){
        return "<a href='add_video_clip.php?accessed=$accessed&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_done_video_clip_color($id){
	if(get_done_video_clip($id)){
		$color = 'green';
        $done = 0;
        $txt = 'сделано';
	}else{
        $color = 'red';
        $done = 1;
        $txt = 'не сделано';
    }
    if (check_access(array(1))){
    	return "<font color='$color'><b>$txt</b></font>";
    }else{
        return "<a href='add_video_clip.php?done=$done&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }
}

$query = "select video_clips.*,administrators.login from video_clips,administrators  $where LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
//echo $_GET['search'];
$rs = $db->executeQuery($query);
//echo $total_pages;

//$rs = $db->executeQuery($query);
?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="Поиск">&nbsp;<font color="Gray">поиск по "названию клипа"</font>
</form>
<td>
</tr>
<tr>
<td align="center">
<a href="?letter=А">А</a>&nbsp;
<a href="?letter=Б">Б</a>&nbsp;
<a href="?letter=В">В</a>&nbsp;
<a href="?letter=Г">Г</a>&nbsp;
<a href="?letter=Д">Д</a>&nbsp;
<a href="?letter=Е">Е</a>&nbsp;
<a href="?letter=Ё">Ё</a>&nbsp;
<a href="?letter=Ж">Ж</a>&nbsp;
<a href="?letter=З">З</a>&nbsp;
<a href="?letter=И">И</a>&nbsp;
<a href="?letter=Й">Й</a>&nbsp;
<a href="?letter=К">К</a>&nbsp;
<a href="?letter=Л">Л</a>&nbsp;
<a href="?letter=М">М</a>&nbsp;
<a href="?letter=Н">Н</a>&nbsp;
<a href="?letter=О">О</a>&nbsp;
<a href="?letter=П">П</a>&nbsp;
<a href="?letter=Р">Р</a>&nbsp;
<a href="?letter=С">С</a>&nbsp;
<a href="?letter=Т">Т</a>&nbsp;
<a href="?letter=У">У</a>&nbsp;
<a href="?letter=Ф">Ф</a>&nbsp;
<a href="?letter=Х">Х</a>&nbsp;
<a href="?letter=Ц">Ц</a>&nbsp;
<a href="?letter=Ч">Ч</a>&nbsp;
<a href="?letter=Ш">Ш</a>&nbsp;
<a href="?letter=Щ">Щ</a>&nbsp;
<a href="?letter=Ъ">Ъ</a>&nbsp;
<a href="?letter=Ы">Ы</a>&nbsp;
<a href="?letter=Ь">Ь</a>&nbsp;
<a href="?letter=Э">Э</a>&nbsp;
<a href="?letter=Ю">Ю</a>&nbsp;
<a href="?letter=Я">Я</a>&nbsp;
<td>
</tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>Физическое имя</b></td>";
echo "<td class='list'><b>Название клипа</b></td>";
echo "<td class='list'><b>Исполнитель</b></td>";
echo "<td class='list'><b>Добавил</b></td>";
echo "<td class='list'><b>Когда</b></td>";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".check_file($arr['id'])."</td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['singer']."</td>";
    echo "<td class='list'>".$arr['login']."</td>";
    echo "<td class='list'>".$arr['added']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."&search=$search&letter=$letter#form'>edit</a>&nbsp;&nbsp;";
    echo get_video_clip_accessed_color($arr['id'])."&nbsp;&nbsp;";
    echo get_done_video_clip_color($arr['id']);
    echo "</tr>";
}
echo "</table>";
echo "<table width='600' align='center' border=0>";
echo "<tr>";
echo "<td width='100%' align='center'>";
echo page_bar();
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</center>";

if (@$_GET['edit']){
    $query = "select * from video_clips where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $genre_id = $arr['genre_id'];
        $singer   = $arr['singer'];
    }
    //unset($_SESSION['upload']);
    $query = "select * from screenshots where media_id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $_SESSION['upload'][] = $arr['id'];
    }
}

function check_file($id){
    $fname = $id.".mpg";
    if (file_exists(VIDEO_CLIP_STORAGE_DIR.$fname)){
        $color = 'green';
        set_status($id, 1);
    }else{
        $color = 'red';
        set_status($id, 0);
    }
    return "<font color='$color'>$fname</font>";
}

function set_status($id, $val){
    global $db;
    if ($id){
        $query = "update video_clips set status=$val where id='$id'";
        $db->executeQuery($query);
    }
}

function get_genres(){
    global $db;
    global $genre_id;
    
    $query = "select * from video_clip_genres";
    $rs=$db->executeQuery($query);
    $option = '';
    
    while(@$rs->next()){
        $selected = '';
        $arr=$rs->getCurrentValuesAsHash();
        if ($genre_id == $arr['id']){
            $selected = 'selected';
        }
        $option .= "<option value={$arr['id']} $selected>{$arr['title']}\n";
    }
    return $option;
}

?>
<script>
function hint(){
    alert(document.getElementById('f_file').value)
}

function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    id = document.getElementById('id').value
    
    action = 'add_video_clip.php?name='+name+'&id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>'
    
    
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    //alert(action)
    form_.action = action
    form_.method = 'POST'
    //document.location=action
    form_.submit()
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
    <form id="form_" enctype="multipart/form-data" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            Название: 
           </td>
           <td>
            <input type="text" size="40" name="name" id="name" value='<? echo @$name ?>'>
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Исполнитель: 
           </td>
           <td>
            <input type="text" size="40" name="singer" id="singer" value='<? echo @$singer ?>'>
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            Жанр: 
           </td>
           <td>
            <select name="genre_id">
                <option value="0"/>-----------
                <?echo get_genres()?>
            </select>
           </td>
        </tr> 
        <tr>
           <td align="right">
           </td>
           <td>
           
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_video_clip.php'">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <br> 
           <b>Порядок заполнения:</b><br><br>
           1. Заполнить поля.<br>
           2. Сохранить.<br>
           3. Поместить сконвертированный файл с названием, <br>
           &nbsp;&nbsp;&nbsp;&nbsp;указанным в колонке "Имя файла" в общую директорию.<br>
           4. При необходимости редактировать или удалить запись.<br>
           5. Если файл физически присутствует в директории, то он <br> 
           &nbsp;&nbsp;&nbsp;&nbsp;отображается зеленым цветом, если отсутствует -  красным.
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