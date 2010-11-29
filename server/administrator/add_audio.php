<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../getid3/getid3.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

$search = @$_GET['search'];
$letter = @$_GET['letter'];

//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';

if (!$_SERVER['QUERY_STRING']){
    unset($_SESSION['upload']);
}


$ext = '';
if(@$_GET['path']){
    preg_match("/[(\S+)](.)[(\S+)]$/", $_GET['path'], $arr);
    $ext = @$arr[0];
}

$audio = array('mp3', 'ogg', 'wav', 'pcm', 'aac');

if (isset($_FILES['screenshot'])){
    if (is_uploaded_file($_FILES['screenshot']['tmp_name'])){
        if (preg_match("/jpeg/",$_FILES['screenshot']['type'])){
       
            $insert_upload = 'INSERT INTO screenshots (name,
                                                       size,
                                                       type,
                                                       path
                                                       )
                                               VALUES (\''.$_FILES['screenshot']['name'].'\', 
                                                       \''.$_FILES['screenshot']['size'].'\', 
                                                       \''.$_FILES['screenshot']['type'].'\', 
                                                       \''.IMG_PATH.'\'
                                                       )';

            $rs=$db->executeQuery($insert_upload);
        
            $upload_id = mysql_insert_id();
            
            $_SESSION['upload'][] = $upload_id;
            
            $img_path = get_save_folder($upload_id);
            rename($_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
        }
    }
}

if (@$_GET['del']){
    $query = "delete from audio where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_audio.php?search=$search&letter=$letter");
}

$type = '';

if (!$error){
    
    if (@$_GET['save']){
    
        if(@$_GET['name']){
            
            $lang = get_str_lang($_POST['name']);
            $query = "insert into audio (  name,
                                           singer_id,
                                           album_id,
                                           lang,
                                           addtime
                                           ) 
                                  values ( '".@$_POST['name']."',
                                           '".@$_POST['singer']."',
                                           '".@$_POST['album']."',
                                            ".$lang.",
                                            NOW()
                                           )";
            echo $query;
            $rs=$db->executeQuery($query);

            unset($_SESSION['upload']);
            
            header("Location: add_audio.php?search=$search&letter=$letter");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update']){
        if(@$_GET['name']){
            $lang = get_str_lang($_POST['name']);
            $query = "update audio set name='".$_POST['name']."', 
                                       singer_id='".@$_POST['singer']."',
                                       album_id='".@$_POST['album']."',
                                       lang=".$lang."
                                    where id=".intval(@$_GET['id']);
            
            //echo $query;
            $rs=$db->executeQuery($query);

            unset($_SESSION['upload']);
            
            header("Location: add_audio.php?search=$search&letter=$letter");
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
.list_t{
    border-width: 2px;
    border-style: solid;
    border-color: #E1E1E1;
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
#add_singer_form{
    position:fixed;
	left: 0px;
	top: 0px;
	width:100%;
	height:100%;
}
#add_album_form{
    position:fixed;
	left: 0px;
	top: 0px;
	width:100%;
	height:100%;
}
#outer{ 
    height:100%; 
    width:100%; 
    display:table; 
    vertical-align:middle; 
}
#container { 
    text-align: center; 
    position:relative; 
    vertical-align:middle; 
    display:table-cell; 
} 
#inner {
    width: 320px; 
    height: 100px;  
    text-align: center; 
    margin:0 auto; 
}
</style>
<title>
Редактирование списка АУДИО КЛУБА
</title>
<script language="JavaScript" src="js.js"></script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка аудио файлов&nbsp;</b></font>
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
$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = ' where singer.id=audio.singer_id ';
if ($search){
    $where .= ' and singer like "%'.$search.'%" or name like "%'.$search.'%"';
}
if (isset($_GET['letter'])) {
	$where .= ' and singer like "'.$letter.'%"';
}

$query = "select * from audio,singer $where";
$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

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
//echo $query;
$query = "select audio.id as id, audio.name as name, singer.singer as singer, singer.path as path, album.id as album_id, album.name as album_name 
            from audio,singer 
            left join album on audio.album_id=album.id
            $where
            order by singer 
            LIMIT $page_offset, $MAX_PAGE_ITEMS";
$rs = $db->executeQuery($query);

?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="Поиск">&nbsp;<font color="Gray">поиск по "названию трека"</font>
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
<a href="?letter=A">A</a>&nbsp;
<a href="?letter=B">B</a>&nbsp;
<a href="?letter=C">C</a>&nbsp;
<a href="?letter=D">D</a>&nbsp;
<a href="?letter=E">E</a>&nbsp;
<a href="?letter=F">F</a>&nbsp;
<a href="?letter=G">G</a>&nbsp;
<a href="?letter=H">H</a>&nbsp;
<a href="?letter=I">I</a>&nbsp;
<a href="?letter=J">J</a>&nbsp;
<a href="?letter=K">K</a>&nbsp;
<a href="?letter=L">L</a>&nbsp;
<a href="?letter=M">M</a>&nbsp;
<a href="?letter=N">N</a>&nbsp;
<a href="?letter=O">O</a>&nbsp;
<a href="?letter=P">P</a>&nbsp;
<a href="?letter=Q">Q</a>&nbsp;
<a href="?letter=R">R</a>&nbsp;
<a href="?letter=S">S</a>&nbsp;
<a href="?letter=T">T</a>&nbsp;
<a href="?letter=U">U</a>&nbsp;
<a href="?letter=V">V</a>&nbsp;
<a href="?letter=W">W</a>&nbsp;
<a href="?letter=X">X</a>&nbsp;
<a href="?letter=Y">Y</a>&nbsp;
<a href="?letter=Z">Z</a>&nbsp;
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
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>Физическое имя</b></td>";
echo "<td class='list'><b>Исполнитель</b></td>";
echo "<td class='list'><b>Название композиции</b></td>";
echo "<td class='list'><b>Альбом</b></td>";
echo "<td class='list'><b>Папка</b></td>";
echo "<td class='list'><b>сек</b></td>";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".check_file($arr['id'])."</td>";
    echo "<td class='list'>".$arr['singer']."</td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['album_name']."</td>";
    echo "<td class='list'>".$arr['path']."</td>";
    echo "<td class='list'>".get_playtime($arr['id'], $arr['path'])."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."&search=$search&letter=$letter#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\"Удалить данную запись?\")){document.location=\"add_audio.php?del=1&id=".$arr['id']."\"}'>del</a></td>";
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
    $query = "select * from audio where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $singer_id = $arr['singer_id'];
    }
    //unset($_SESSION['upload']);
    //$query = "select * from screenshots where media_id=".intval(@$_GET['id']);
    //$rs=$db->executeQuery($query);
    //while(@$rs->next()){
    //    $arr=$rs->getCurrentValuesAsHash();
    //    $_SESSION['upload'][] = $arr['id'];
    //}
}

function check_file($id){
    $fname = $id.".mp3";
    $path = get_singer_path($id);
    if (file_exists(AUDIO_STORAGE_DIR.$path.'/'.$fname)){
        $color = 'green';
        set_status($id, 1);
    }else{
        $color = 'red';
        set_status($id, 0);
    }
    return "<font color='$color'>$fname</font>";
}

function get_singer_path($id){
    global $db;
    $sql = "select singer_id from audio where id='$id'";
    $rs = $db->executeQuery($sql);
    $singer_id = $rs->getValueByName(0, 'singer_id');
    
    $sql = "select path from singer where id='$singer_id'";
    $rs = $db->executeQuery($sql);
    $path = $rs->getValueByName(0, 'path');
    
    return $path;
}

function set_status($id, $val){
    global $db;
    if ($id){
        $query = "update audio set status=$val where id='$id'";
        $rs=$db->executeQuery($query);
    }
}

function get_playtime($id, $path){
    global $db;
    $fname = $id.'.mp3';
    $mp3info = GetAllFileInfo(AUDIO_STORAGE_DIR.$path.'/'.$fname);
    $playtime = ceil(@$mp3info['playtime_seconds']);
    $sql = "update audio set time=$playtime where id=$id";
    $rs=$db->executeQuery($sql);
    return $playtime;
}

$upload_str = '';
if(@$_SESSION['upload']){
    $is_uploaded = 'SELECT id, name, size 
                    FROM screenshots
                    WHERE id 
                    IN ('.@implode(',', @$_SESSION['upload']).')';
    $rs=$db->executeQuery($is_uploaded);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $upload_str .= $arr['name'].'.....(размер: '.$arr['size'].' B) <a href="del_upload.php?id='.$arr['id'].'"> удалить</a><br>';
    }
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
    
    action = 'add_audio.php?name='+name+'&id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>'
        
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

function get_singer_by_letter(){
    singer_letter = document.getElementById('singer_letter')
    document.getElementById('singer').disabled = true
    letter = singer_letter.options[singer_letter.selectedIndex].value
    doLoad('singer',1,letter)
}

function fill_singer_by_letter(arr){
    singer = document.getElementById('singer')
    singer.options.length = 0;
    singer.options[0] = new Option('- - -', 0)
    for (i=0; i<arr.length; i++){
        singer.options[i+1] = new Option(arr[i].singer, arr[i].id)
    }
    document.getElementById('singer').disabled = false
}

function add_new_singer(){
    new_singer = document.getElementById('new_singer').value
    doLoad('new_singer',1,new_singer)
}

function add_new_singer_msg(txt){
    document.getElementById('new_singer_msg').innerHTML = txt
}

function doLoad(get, page, data) {
    
    var req = new Subsys_JsHttpRequest_Js()
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            if (req.responseJS) {
                
                if (get == 'singer'){
                    singers = req.responseJS.data
                    if(singers != null){
                        fill_singer_by_letter(singers)
                    }
                    return
                }
                
                if (get == 'album'){
                    album = req.responseJS.data
                    if(album != null){
                        fill_album_by_singer(album)
                    }
                    return
                }
                
                if (get == 'new_singer'){
                    new_singers_resp = req.responseJS.data
                    new_singers_err = req.responseJS.error
                    if (new_singers_err == 0){
                        add_new_singer_msg('Исполнитель добавлен')
                        get_singer_by_letter()
                    }else{
                        add_new_singer_msg('Ошибка добавления')
                    }
                    return
                }
                
                if (get == 'new_album'){
                    new_album_resp = req.responseJS.data
                    new_album_err = req.responseJS.error
                    if (new_album_err == 0){
                        add_new_album_msg('Альбом добавлен')
                        get_album_by_singer()
                    }else{
                        add_new_album_msg('Ошибка добавления')
                    }
                }
                
            }
        }
    }
    req.caching = false
    if (get == 'new_album'){
        //data = '1&name='+data.name+'&year='+data.year+'&singer_id='+singer_id
    }
    
    req.open('POST', 'load.php?get='+get, true)
    if (get == 'new_singer'){
        send = {data: data}
    }else if(get == 'new_album'){
        send = {data: 1, name: data.name, year: data.year, singer_id: singer_id}
    }else{
        send = {data: data}
    }
    req.send(send)
}

function show_add_singer_form(){
    document.getElementById('add_singer_form').style.display = ''
    add_new_singer_msg('&nbsp;')
}

function hide_add_singer_form(){
    document.getElementById('add_singer_form').style.display = 'none'
    add_new_singer_msg('&nbsp;')
    document.getElementById('new_singer').value = ''
}

function show_add_album_form(){
    singer = document.getElementById('singer')
    if(singer.selectedIndex >= 0){
        singer_id = singer.options[singer.selectedIndex].value
    }else{
        singer_id = 0
    }
    if (singer_id > 0){
        document.getElementById('add_new_album_btn').disabled = false
    }else{
        document.getElementById('add_new_album_btn').disabled = true
    }
    document.getElementById('add_album_form').style.display = ''
    add_new_singer_msg('&nbsp;')
}

function hide_add_album_form(){
    document.getElementById('add_album_form').style.display = 'none'
    add_new_singer_msg('&nbsp;')
    document.getElementById('new_album').value = ''
    document.getElementById('new_album_year').value = ''
}

function add_new_album_msg(txt){
    document.getElementById('new_album_msg').innerHTML = txt
}

function get_album_by_singer(){
    singer = document.getElementById('singer')
    document.getElementById('album').disabled = true
    singer_id = singer.options[singer.selectedIndex].value
    doLoad('album',1,singer_id)
}

function fill_album_by_singer(arr){
    album = document.getElementById('album')
    album.options.length = 0;
    album.options[0] = new Option('- - -', 0)
    for (i=0; i<arr.length; i++){
        album.options[i+1] = new Option(arr[i].name, arr[i].id)
    }
    document.getElementById('album').disabled = false
}

function add_new_album(){
    new_album = document.getElementById('new_album').value
    new_album_year = document.getElementById('new_album_year').value
    singer = document.getElementById('singer')
    singer_id = singer.options[singer.selectedIndex].value
    albom_obj = {'name' : new_album, 'year': new_album_year, 'singer_id' : singer_id}
    doLoad('new_album',1,albom_obj)
}

function delete_album_list(){
    album = document.getElementById('album')
    album.options.length = 0;
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
            <select id="singer_letter" style="width:100px" onchange="delete_album_list();get_singer_by_letter()">
                <optgroup label="Русские">
                    <option value="А">А
                    <option value="Б">Б
                    <option value="В">В
                    <option value="Г">Г
                    <option value="Д">Д
                    <option value="Е">Е
                    <option value="Ё">Ё
                    <option value="Ж">Ж
                    <option value="З">З
                    <option value="И">И
                    <option value="Й">Й
                    <option value="К">К
                    <option value="Л">Л
                    <option value="М">М
                    <option value="Н">Н
                    <option value="О">О
                    <option value="П">П
                    <option value="Р">Р
                    <option value="С">С
                    <option value="Т">Т
                    <option value="У">У
                    <option value="Ф">Ф
                    <option value="Х">Х
                    <option value="Ц">Ц
                    <option value="Ч">Ч
                    <option value="Ш">Ш
                    <option value="Щ">Щ
                    <option value="Ъ">Ъ
                    <option value="Ы">Ы
                    <option value="Ь">Ь
                    <option value="Э">Э
                    <option value="Ю">Ю
                    <option value="Я">Я
                </optgroup>
                <optgroup label="Зарубежные">
                    <option value="A">A
                    <option value="B">B
                    <option value="C">C
                    <option value="D">D
                    <option value="E">E
                    <option value="F">F
                    <option value="G">G
                    <option value="H">H
                    <option value="I">I
                    <option value="J">J
                    <option value="K">K
                    <option value="L">L
                    <option value="M">M
                    <option value="N">N
                    <option value="O">O
                    <option value="P">P
                    <option value="Q">Q
                    <option value="R">R
                    <option value="S">S
                    <option value="T">T
                    <option value="U">U
                    <option value="V">V
                    <option value="W">W
                    <option value="X">X
                    <option value="Y">Y
                    <option value="Z">Z
                </optgroup>
                <optgroup label="Цифры">
                    <option value="0">0
                    <option value="1">1
                    <option value="2">2
                    <option value="3">3
                    <option value="4">4
                    <option value="5">5
                    <option value="6">6
                    <option value="7">7
                    <option value="8">8
                    <option value="9">9
                </optgroup>
            </select>
            
            <select id="singer" name="singer" style="width:163px" onchange="get_album_by_singer()">
            </select>&nbsp;<a href="javascript://" onclick="show_add_singer_form()">добавить</a>
           </td>
        </tr>
        <tr>
           <td align="right">
            Альбом: 
           </td>
           <td>
            <select id="album" name="album" style="width:163px">
            </select>&nbsp;<a href="javascript://" onclick="show_add_album_form()">добавить</a>
           </td>
        </tr>
        <tr>
           <td align="right">
           </td>
           <td>
           <? //echo $upload_str ?>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_audio.php'">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <br> 
           <!--<b>Порядок заполнения:</b><br><br>
           1. Заполнить поля.<br>
           2. Сохранить.<br>
           3. Поместить сконвертированный файл с названием, <br>
           &nbsp;&nbsp;&nbsp;&nbsp;указанным в колонке "Имя файла" в общую директорию.<br>
           4. При необходимости редактировать или удалить запись.<br>
           5. Если файл физически присутствует в директории, то он <br> 
           &nbsp;&nbsp;&nbsp;&nbsp;отображается зеленым цветом, если отсутствует -  красным.-->
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

<div id="add_singer_form" style="display:none">
<div id="outer">
<div id="container">
<div id="inner">
    <table bgcolor="#efefef" class="list_t" width="300px">
        <tr>
            <td colspan="2" id="new_singer_msg" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td nowrap>Новый исполнитель:</td>
            <td><input id="new_singer" type="text"></td>
        </tr>
        <tr>
            <td></td>
            <td nowrap><input type="button" value="Добавить" onclick="add_new_singer()">&nbsp;<input type="button" value="Закрыть" onclick="hide_add_singer_form()"></td>
        </tr>
    </table>
</div>
</div>
</div>
</div>

<div id="add_album_form" style="display:none">
  <div id="outer">
  <div id="container">
  <div id="inner">
    <table bgcolor="#efefef" class="list_t" width="300px">
        <tr>
            <td colspan="2" id="new_album_msg" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td nowrap>Новый альбом:</td>
            <td><input id="new_album" type="text"></td>
        </tr>
        <tr>
            <td nowrap>Год:</td>
            <td><input id="new_album_year" type="text"></td>
        </tr>
        <tr>
            <td></td>
            <td nowrap><input type="button" id="add_new_album_btn" value="Добавить" onclick="add_new_album()">&nbsp;<input type="button" value="Закрыть" onclick="hide_add_album_form()"></td>
        </tr>
    </table>
  </div>
  </div>
  </div>
</div>

</body>
</html>