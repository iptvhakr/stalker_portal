<?php

session_start();

ob_start();

include "../common.php";
include "../conf_serv.php";
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


$ext = '';
if(@$_GET['path']){
    preg_match("/[(\S+)](.)[(\S+)]$/", $_GET['path'], $arr);
    $ext = @$arr[0];
}

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
            //echo $insert_upload;
            $rs=$db->executeQuery($insert_upload);
        
            $upload_id = mysql_insert_id();
            
            $_SESSION['upload'][] = $upload_id;
            
            $img_path = get_save_folder($upload_id);
            rename($_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
        }
    }
}

if (isset($_GET['accessed']) && @$_GET['id']){
    set_karaoke_accessed(@$_GET['id'], @$_GET['accessed']);
    $id = $_GET['id'];
    if ($_GET['accessed'] == 1){
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0444);
    }else{
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0666);
    }
    header("Location: add_karaoke.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (isset($_GET['done']) && @$_GET['id']){
    set_karaoke_done(@$_GET['id'], @$_GET['done']);
    $id = $_GET['id'];
    header("Location: add_karaoke.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['del']){
    $query = "delete from karaoke where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_karaoke.php?search=$search&letter=$letter");
}

$type = '';

if (!$error){
    
    if (@$_GET['save']){
    
        if(@$_GET['name']){
            $datetime = date("Y-m-d H:i:s");
            $query = "insert into karaoke (name,
                                           genre_id,
                                           singer,
                                           author,
                                           added,
                                           add_by
                                           ) 
                                  values ('".@$_GET['name']."',
                                           '".@$_POST['genre_id']."',
                                           '".@$_POST['singer']."',
                                           '".@$_POST['author']."',
                                           '".$datetime."',
                                           ".@$_SESSION['uid']."
                                           )";
            //echo $query;
            $rs=$db->executeQuery($query);

            unset($_SESSION['upload']);
            
            header("Location: add_karaoke.php?search=$search&letter=$letter");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update']){
        if(@$_GET['name']){

            $query = "update karaoke set name='".$_GET['name']."', 
                                       genre_id='".@$_POST['genre_id']."',  
                                       singer='".@$_POST['singer']."',
                                       author='".@$_POST['author']."'
                                    where id=".intval(@$_GET['id']);
            
            //echo $query;
            $rs=$db->executeQuery($query);

            unset($_SESSION['upload']);
            
            header("Location: add_karaoke.php?search=$search&letter=$letter");
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
.list2{
    border-width: 1px;
    border-style: solid;
    border-color: #c5c5c5;
    padding-left: 5px;
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
<script language="JavaScript" src="js.js"></script>
<title>
Редактирование списка КАРАОКЕ
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка караоке файлов&nbsp;</b></font>
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

//$where = 'where administrators.id=karaoke.add_by ';
$where = '';
if ($search){
    if (!$where){
        $where .= ' where ';
    }else{
        $where .= ' and ';
    }
    $where .= ' karaoke.name like "%'.$search.'%"';
}

if ($letter) {
	if (!$where){
        $where .= ' where ';
    }else{
        $where .= ' and ';
    }
    $where .= ' karaoke.name like "'.$letter.'%"';
}

if (@$_GET['status']){
    if (@$_GET['status'] == 'on'){
        $op_accessed=1;
    }else if (@$_GET['status'] == 'off'){
        $op_accessed=0;
    }
    
    if ($where){
        $where .= ' and accessed='.$op_accessed;
    }else{
        $where .= 'where accessed='.$op_accessed;
    }
    
}

$query = "select * from karaoke $where";
//echo $query;
$rs = $db->executeQuery($query);
//var_dump($rs);
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
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&status='.@$_GET['status'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;

    
}

function set_karaoke_accessed($id, $val){
    global $db;
    if ($id){
        $query = "update karaoke set accessed=$val, added=NOW() where id=$id";
        $rs=$db->executeQuery($query);
    }
}

function set_karaoke_done($id, $val){
    global $db;
    if ($id){
        //$query = "update karaoke set done=$val, added=NOW() where id=$id";
        $query = "update karaoke set done=$val, done_time=NOW() where id=$id";
        $rs=$db->executeQuery($query);
    }
}

function get_karaoke_accessed($id){
    global $db;
    
    $query = "select * from karaoke where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'accessed');
    return $accessed;
}

function get_done_karaoke($id){
	$db = Database::getInstance(DB_NAME);
	
	$query = "select * from karaoke where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'done');
    return $accessed;
}

function get_karaoke_accessed_color($id){
    if (get_karaoke_accessed($id)){
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
    if (check_access(array(1))){
        return "<a href='add_karaoke.php?accessed=$accessed&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_done_karaoke_color($id){
	if(get_done_karaoke($id)){
		$color = 'green';
        $done = 0;
        $txt = 'сделано';
	}else{
        $color = 'red';
        $done = 1;
        $txt = 'не сделано';
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    
    if (check_access(array(1))){
    	return "<a href='add_karaoke.php?done=$done&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

if (!empty($_GET['letter'])){
    $orderby = 'name';
}else{
    $orderby = 'id';
}

$query = "select karaoke.*,administrators.login, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter
    from karaoke 
    inner join administrators on administrators.id=karaoke.add_by
    left join media_claims on karaoke.id=media_claims.media_id and media_claims.media_type='karaoke'
    $where group by karaoke.id, karaoke.add_by order by $orderby LIMIT $page_offset, $MAX_PAGE_ITEMS";
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
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="Поиск">&nbsp;<font color="Gray">поиск по "названию ролика"</font>
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
</table>

<table>
<tr>
<td>
<select id="sort_opt" onchange="change_list()">
    <option value="">---
    <option value="on" <?if (@$_GET['status'] == 'on') echo 'selected'?>>on
    <option value="off" <?if (@$_GET['status'] == 'off') echo 'selected'?>>off
</select>
</td>
</tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
//echo "<td class='list'><b>Имя файла</b></td>";
echo "<td class='list'><b>Физическое имя</b></td>";
echo "<td class='list'><b>Название композиции</b></td>";
echo "<td class='list'><b>Исполнитель</b></td>";
echo "<td class='list'><b>Добавил</b></td>";
echo "<td class='list'><b>Когда</b></td>";
echo "<td class='list'><b>Жалобы на<br>звук/видео</b></td>\n";
//echo "<td class='list'><b>Путь</b></td>";
//echo "<td class='list'><b>Длительность, с</b></td>";
//echo "<td class='list'><b>Описание</b></td>";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>\n";
    echo "<td class='list'><a href='javascript://' onclick='open_info({$arr['id']})'>".check_file($arr['id'])."</a></td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['singer']."</td>";
    echo "<td class='list'>".$arr['login']."</td>";
    echo "<td class='list'>".$arr['added']."</td>";
    
    echo "<td class='list' align='center'>";
    
    if (check_access(array(1))){
        echo "<a href='#' onclick='if(confirm(\"Вы действительно хотите сбросить счетчик жалоб?\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".$arr['sound_counter']." / ".$arr['video_counter']."</span>";
    if (check_access(array(1))){
        echo "</a>";
    }
    echo "</td>\n";
    
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."&search=$search&letter=$letter#form'>edit</a>&nbsp;&nbsp;";
    if (check_access(array(1))){
        echo "<a href='#' onclick='if(confirm(\"Удалить данную запись?\")){document.location=\"add_karaoke.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
    }
    echo get_karaoke_accessed_color($arr['id'])."&nbsp;&nbsp;";
    echo get_done_karaoke_color($arr['id']);
    echo "</td>\n";
    echo "</tr>\n";
?>
    <tr style="display:none;" id="info_<?echo $arr['id']?>" bgcolor="#f2f2f2">
    
    <td colspan="7">
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <table cellpadding="0" cellspacing="0">
           <tr>
            <td id="loading_bar_<?echo $arr['id']?>" style="display:">
                &nbsp;Загрузка...
            </td>
            <td id="error_bar_<?echo $arr['id']?>" style="display:none">
                <font color="red">Не найден!</font>
            </td>
            <td style="display:none" id="storages_<?echo $arr['id']?>">
                <table class='list' border="1" cellpadding='0' cellspacing='0' id="storages_content_<?echo $arr['id']?>">
                </table>
            </td>
           </tr>           
          </table>           
        </td>
      </tr>
    </table>
    </td>
    </tr>
<?
}
echo "</table>\n";
echo "<table width='600' align='center' border=0>";
echo "<tr>";
echo "<td width='100%' align='center'>";
echo page_bar();
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</center>";

if (@$_GET['edit']){
    $query = "select * from karaoke where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        //$description = $arr['description'];
        //$path = $arr['path'];
        //$fname    = $arr['fname'];
        $genre_id = $arr['genre_id'];
        $singer   = $arr['singer'];
        $author   = $arr['author'];
        $year     = $arr['year'];
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
    //echo VIDEO_STORAGE_DIR.$fname."<br>";
    $fname = $id.".mpg";
    //if (file_exists(KARAOKE_STORAGE_DIR.$fname)){
    //var_dump(get_status($id));
    if (get_status($id) == 1){
        $color = 'green';
        set_status($id, 1);
    }else{
        $color = 'red';
        set_status($id, 0);
    }
    return "<font id='path_$id' color='$color'>$fname</font>";
}

function set_status($id, $val){
    global $db;
    if ($id){
        $query = "update karaoke set status=$val where id='$id'";
        $rs=$db->executeQuery($query);
    }
}

function get_status($id){
    global $db;
    if ($id){
        $query = "select * from karaoke where id='$id'";
        $rs=$db->executeQuery($query);
	return $rs->getValueByName(0, 'status');
    }
}

function get_genres(){
    global $db;
    global $genre_id;
    
    $query = "select * from karaoke_genre";
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

$upload_str = '';
if(@$_SESSION['upload']){
    $is_uploaded = 'SELECT id, name, size 
                    FROM screenshots
                    WHERE id 
                    IN ('.@implode(',', @$_SESSION['upload']).')';
    $rs=$db->executeQuery($is_uploaded);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
    }
}

?>
<script>
function change_list(){
    var opt_sort = document.getElementById('sort_opt')
    var url = 'add_karaoke.php?status='+opt_sort.options[opt_sort.selectedIndex].value+<?echo '\'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&page='.@$_GET['page'].'\''."\n";?>
    document.location = url
}

function open_info(id){
    var info_display = document.getElementById('info_'+id).style.display
    if (info_display == 'none'){
        document.getElementById('info_'+id).style.display = ''
        doLoad('karaoke_info', id)
    }else{
        document.getElementById('info_'+id).style.display = 'none'
        document.getElementById('storages_content_'+id).innerHTML = '';
    }
}

function display_info(arr, id){
    //alert(arr.toSource())
    if (arr.length > 0){
        document.getElementById('loading_bar_'+id).style.display = 'none';
        
        
        table  = '<tr>'
        table += '<td class="list2" width="70">Сервер</td>'
        table += '<td class="list2" width="70">Файл</td>'
        table += '</tr>'
        
        for (i=0; i<arr.length; i++){
            table +='<tr>'
                 table +='<td class="list2"><b>'+arr[i]['storage_name']+'</b></td>'
                 table +='<td class="list2"><b><font color="green">'+arr[i]['file']+'</font></b></td>'
            table +='</tr>' 
        }
        
        document.getElementById('storages_content_'+id).innerHTML = table;
        document.getElementById('error_bar_'+id).style.display = 'none';
        document.getElementById('storages_'+id).style.display = '';
        document.getElementById('path_'+id).style.color = 'green';
    }else{
        document.getElementById('loading_bar_'+id).style.display = 'none';
        document.getElementById('error_bar_'+id).style.display = '';
        document.getElementById('path_'+id).style.color = 'red';
    }
}

function doLoad(get, data){
    
    var req = new Subsys_JsHttpRequest_Js()
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            
            if (req.responseJS) {
                
                if (get == 'karaoke_info'){
                    
                    var info = req.responseJS.data
                    if(info != null){
                        display_info(info, data)
                    }
                    return
                }
                
            }else{
                if (get == 'karaoke_info'){
                    alert('Ошибка: Возможно файл или директория содержат запрещенные символы')
                }
            }
        }
    }
    req.caching = false
    
    req.open('POST', 'load.php?get='+get, true)
    send = {data : data}
    req.send(send)
}

function hint(){
    alert(document.getElementById('f_file').value)
}

function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    //path = document.getElementById('f_file').value
    id = document.getElementById('id').value
    //description = document.getElementById('description').value
    
    //action = 'add_video.php?name='+name+'&path='+path+'&id='+id
    action = 'add_karaoke.php?name='+name+'&id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>'
    
    
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
        <!--<tr>
           <td align="right">
            Автор: 
           </td>
           <td>
            <input type="text" size="40" name="author" id="author" value="<? //echo @$author ?>">
           </td>
        </tr>-->
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
           Автор: 
           </td>
           <td>
            <input name="author" type="text" size="40" value='<? echo @$author ?>'>
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
           <? echo $upload_str ?>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_karaoke.php'">
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