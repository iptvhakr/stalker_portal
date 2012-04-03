<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

moderator_access();

$search = @$_GET['search'];
$letter = @$_GET['letter'];
$checked = '';

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
echo '</pre>';

$ext = '';

if (isset($_GET['accessed']) && @$_GET['id']){
    set_accessed(@$_GET['id'], @$_GET['accessed']);
    header("Location: add_video_records.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['del']){
    $query = "delete from video_records where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_video_records.php?letter=".@$_GET['letter']."&search=".@$_GET['search']);
    exit;
}

$type = '';

if (!$error){
    if (@$_POST['censored'] == 'on'){
        $censored = 1;
    }else{
        $censored = 0;
    }
    
    $genre_id_1 = @$_POST['genre_id_1'] ? @$_POST['genre_id_1'] : 0;
    $genre_id_2 = @$_POST['genre_id_2'] ? @$_POST['genre_id_2'] : 0;
    $genre_id_3 = @$_POST['genre_id_3'] ? @$_POST['genre_id_3'] : 0;
    $genre_id_4 = @$_POST['genre_id_4'] ? @$_POST['genre_id_4'] : 0;
    
    if (@$_GET['save']){                
        
        if(@$_POST['descr'] && !$error){

            $datetime = date("Y-m-d H:i:s");
            
            $query = "insert into video_records (
                                         descr, 
                                         addtime
                                         ) 
                                values (
                                         '".mysql_escape_string(@$_POST['descr'])."',
                                         '".$datetime."'
                                         )";
            //echo $query;
            $rs = $db->executeQuery($query);
            
            header("Location: add_video_records.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
            exit;
        }
        else if (!$error){
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update']){
        
        
        if(@$_POST['descr']){
            $query = "update video_records set descr='".$_POST['descr']."' 
                                    where id=".intval(@$_GET['id']);
            
            $rs=$db->executeQuery($query);
            header("Location: add_video_records.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".$_GET['page']);
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
Редактирование списка файлов ВИДЕО ЗАПИСЕЙ
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка видео записей&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.html"><< Назад</a> 
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
if ($search){
    $where = 'where descr like "%'.$search.'%"';
}
if (isset($_GET['letter'])) {
	$where = 'where descr like "'.$letter.'%"';
}

$query = "select * from video_records $where";
$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from video_records $where LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$rs = $db->executeQuery($query);
//echo $total_pages;
?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="Поиск">&nbsp;<font color="Gray">поиск по описанию</font>
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
<tr>
<td align="center">
<a href="?letter=1">1</a>&nbsp;
<a href="?letter=2">2</a>&nbsp;
<a href="?letter=3">3</a>&nbsp;
<a href="?letter=4">4</a>&nbsp;
<a href="?letter=5">5</a>&nbsp;
<a href="?letter=6">6</a>&nbsp;
<a href="?letter=7">7</a>&nbsp;
<a href="?letter=8">8</a>&nbsp;
<a href="?letter=9">9</a>&nbsp;
<a href="?letter=0">0</a>&nbsp;
<td>
</tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>Описание</b></td>\n";
echo "<td class='list'>&nbsp;</td>\n";
echo "</tr>\n";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$arr['id']."</td>\n";
    echo "<td class='list'>".get_path_color($arr['id'],$arr['descr'])."</td>\n";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."&#form'>edit</a>&nbsp;&nbsp;\n";
    echo "<a href='#' onclick='if(confirm(\"Удалить данную запись?\")){document.location=\"add_video_records.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
    echo get_accessed_color($arr['id']).'</td>';
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='600' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo page_bar();
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";
/*
if (!@$_GET['edit'] && $_POST['name']){
    $name     = $_POST['name'];
    $o_name   = $_POST['o_name'];
    $censored = $_POST['censored'];
    $description = $_POST['description'];
    $genre_id = $_POST['genre_id'];
    $director = $_POST['director'];
    $actors   = $_POST['actors'];
    $time     = $_POST['time'];
    $year     = $_POST['year'];
    if ($censored){
        $checked = 'checked';
    }
}
*/
if (@$_GET['edit']){
    $query = "select * from video_records where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $descr = $arr['descr'];
    }
}

function check_storage_path($id){
    $result = 0;
    if ($handle = @opendir(VIDEO_RECORDS_STORAGE_DIR)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") { 
                preg_match("/([\S\s]+).mpg$/", $file, $tmp_arr);
                if ($id == $tmp_arr[1]){
                    $result = 1;
                }
            }
        }
    }
    @closedir($handle); 
    return $result;
}

function get_path_color($id, $descr){
    if (check_storage_path($id)){
            $color = 'green';
            set_status($id, 1);
        }else{
            $color = 'red';
            set_status($id, 0);
    }
    return "<font color='$color'>$descr</font>";
}

function set_status($id, $val){
    global $db;
    if ($id){
        $query = "update video_records set status=$val where id='$id'";
        $rs=$db->executeQuery($query);
    }
}

function set_accessed($id, $val){
    global $db;
    if ($id){
        $query = "update video_records set accessed=$val  where id=$id";
        $rs=$db->executeQuery($query);
    }
}

function get_accessed($id){
    global $db;
    
    $query = "select * from video_records where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'accessed');
    return $accessed;
}

function get_accessed_color($id){
    if (get_accessed($id)){
        $color = 'green';
        $accessed = 0;
        $txt = 'on';
    }else{
        $color = 'red';
        $accessed = 1;
        $txt = 'off';
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    return "<a href='add_video_records.php?accessed=$accessed&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
}

?>
<script>
function hint(){
    alert(document.getElementById('f_file').value)
}

function save(){
    form_ = document.getElementById('form_')
    
    id = document.getElementById('id').value
    
    action = 'add_video_records.php?id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>&page=<? echo @$_GET['page'] ?>'
    
    
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    form_.action = action
    form_.method = 'POST'
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
           <td align="right" valign="top">
            Описание: 
           </td>
           <td>
            <textarea id="descr" name="descr" rows="8" cols="50"><? echo @$descr ?></textarea>
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>   
        <tr>
           <td>
           </td>
           <td>
           <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_video_records.php'">
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
           &nbsp;&nbsp;&nbsp;&nbsp;указанным в колонке "id" +.mpg в общую директорию.<br>
           4. При необходимости редактировать или удалить запись.<br>
           5. Если файл физически присутствует в директории, то он <br> 
           &nbsp;&nbsp;&nbsp;&nbsp;отображается зеленым цветом, если отсутствует - красным.
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