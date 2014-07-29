<?php

session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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

            $upload_id = Mysql::getInstance()->insert('screenshots', array(
                'name' => $_FILES['screenshot']['name'],
                'size' => $_FILES['screenshot']['size'],
                'type' => $_FILES['screenshot']['type']
            ))->insert_id();

            $_SESSION['upload'][] = $upload_id;
            
            $img_path = get_save_folder($upload_id);
            rename($_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
        }
    }
}

if (isset($_GET['accessed']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

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

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    set_karaoke_done(@$_GET['id'], @$_GET['done']);
    $id = $_GET['id'];
    header("Location: add_karaoke.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['del']){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('karaoke', array('id' => intval(@$_GET['id'])));

    header("Location: add_karaoke.php?search=$search&letter=$letter");
}

$type = '';

if (!$error){
    
    $rtsp_url = @trim($_POST['rtsp_url']);

    $protocol = @$_POST['protocol'];

    if ($protocol == 'custom'){
        $rtsp_url = @trim($_POST['rtsp_url']);
    }else{
        $rtsp_url = '';
    }

    $status = $rtsp_url? 1 : 0;
    
    if (@$_GET['save']){
    
        if(@$_GET['name']){

            Admin::checkAccess(AdminAccess::ACCESS_CREATE);

            $datetime = date("Y-m-d H:i:s");

            Mysql::getInstance()->insert('karaoke', array(
                'name'     => @$_GET['name'],
                'protocol' => $protocol,
                'rtsp_url' => $rtsp_url,
                'genre_id' => @$_POST['genre_id'],
                'singer'   => @$_POST['singer'],
                'author'   => @$_POST['author'],
                'added'    => $datetime,
                'status'   => $status,
                'add_by'   => @$_SESSION['uid']
            ));

            unset($_SESSION['upload']);
            
            header("Location: add_karaoke.php?search=$search&letter=$letter");
            exit;
        }
        else{
            $error = _('Error: all fields are required');
        }
    }
    
    if (@$_GET['update']){
        if(@$_GET['name']){

            Admin::checkAccess(AdminAccess::ACCESS_EDIT);

            Mysql::getInstance()->update('karaoke',
                array(
                    'name'     => $_GET['name'],
                    'protocol' => $protocol,
                    'rtsp_url' => $rtsp_url,
                    'genre_id' => @$_POST['genre_id'],
                    'singer'   => @$_POST['singer'],
                    'status'   => $status,
                    'author'   => @$_POST['author']
                ),
                array('id' => intval(@$_GET['id']))
            );

            unset($_SESSION['upload']);
            
            header("Location: add_karaoke.php?search=$search&letter=$letter");
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
<?= _('KARAOKE')?>
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('KARAOKE')?>&nbsp;</b></font>
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

$total_items = Mysql::getInstance()->query("select * from karaoke $where")->count();

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

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke',
        array(
            'accessed' => $val,
            'added'    => 'NOW()'
        ),
        array('id' => $id)
    );
}

function set_karaoke_done($id, $val){

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke',
        array(
            'done'      => $val,
            'done_time' => 'NOW()'
        ),
        array('id' => $id)
    );
}

function get_karaoke_accessed($id){
    return Mysql::getInstance()->from('karaoke')->where(array('id' => $id))->get()->first('accessed');
}

function get_done_karaoke($id){
    return Mysql::getInstance()->from('karaoke')->where(array('id' => $id))->get()->first('done');
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
    if (Admin::isActionAllowed()){
        return "<a href='add_karaoke.php?accessed=$accessed&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_done_karaoke_color($id){
	if(get_done_karaoke($id)){
		$color = 'green';
        $done = 0;
        $txt = _('done');
	}else{
        $color = 'red';
        $done = 1;
        $txt = _('not done');
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    
    if (Admin::isActionAllowed()){
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

$all_karaoke = Mysql::getInstance()->query("select karaoke.*,administrators.login, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter
    from karaoke
    left join administrators on administrators.id=karaoke.add_by
    left join media_claims on karaoke.id=media_claims.media_id and media_claims.media_type='karaoke'
    $where group by karaoke.id, karaoke.add_by order by $orderby LIMIT $page_offset, $MAX_PAGE_ITEMS");

?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by clip name')?></font>
</form>
<td>
</tr>
<? if (substr($locale, 0, 2) == 'ru'){?>
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
<?}?>
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
echo "<td class='list'><b>"._('File name')."</b></td>";
echo "<td class='list'><b>"._('Song name')."</b></td>";
echo "<td class='list'><b>"._('Performer')."</b></td>";
echo "<td class='list'><b>"._('Posted by')."</b></td>";
echo "<td class='list'><b>"._('When')."</b></td>";
echo "<td class='list'><b>"._('Claims about<br>audio/video')."</b></td>\n";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
while ($arr = $all_karaoke->next()){

    echo "<tr>\n";
    echo "<td class='list'><a href='javascript://'";
    
    if (empty($arr['rtsp_url'])){
        echo " onclick='open_info({$arr['id']})'";
    }
    
    echo ">".check_file($arr['id'])."</a></td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['singer']."</td>";
    echo "<td class='list'>".$arr['login']."</td>";
    echo "<td class='list'>".$arr['added']."</td>";
    
    echo "<td class='list' align='center'>";
    
    if (Admin::isActionAllowed()){
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to reset claims counter?')."\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".$arr['sound_counter']." / ".$arr['video_counter']."</span>";
    if (Admin::isActionAllowed()){
        echo "</a>";
    }
    echo "</td>\n";
    
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."&search=$search&letter=$letter#form'>edit</a>&nbsp;&nbsp;";
    if (Admin::isActionAllowed()){
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"add_karaoke.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
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
                &nbsp;<?= _('Loading')?>...
            </td>
            <td id="error_bar_<?echo $arr['id']?>" style="display:none">
                <font color="red"><?= _('Not found')?>!</font>
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

    $arr = Karaoke::getById(intval(@$_GET['id']));

    if (!empty($arr)){
        $name     = $arr['name'];
        $genre_id = $arr['genre_id'];
        $singer   = $arr['singer'];
        $author   = $arr['author'];
        $year     = $arr['year'];
        $rtsp_url = $arr['rtsp_url'];
        $protocol = $arr['protocol'];
    }

    $screenshots = Mysql::getInstance()->from('screenshots')->where(array('media_id' => intval(@$_GET['id'])))->get()->all('id');

    if (!empty($screenshots)){
        $_SESSION['upload'] = $screenshots;
    }
}

function check_file($id){
    //echo VIDEO_STORAGE_DIR.$fname."<br>";
    $fname = $id.".mpg";
    //if (file_exists(KARAOKE_STORAGE_DIR.$fname)){
    //var_dump(get_status($id));
    
    $color_status = get_status($id);
    
    if ($color_status == 1){
        $color = 'green';
        set_status($id, 1);
    }else if ($color_status == 0){
        $color = 'red';
        set_status($id, 0);
    }else if ($color_status == 2){
        $color = 'blue';
    }
    return "<font id='path_$id' color='$color'>$fname</font>";
}

function set_status($id, $val){

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke', array('status' => $val), array('id' => $id));
}

function get_status($id = 0){

    $karaoke = Karaoke::getById($id);

    if (!empty($karaoke['rtsp_url'])){
        return 2;
    }
    
    return $karaoke['status'];
}

function get_genres(){

    global $genre_id;
    
    $genres = Mysql::getInstance()->from('karaoke_genre')->get()->all();

    $option = '';
    
    foreach($genres as $arr){
        $selected = '';

        if ($genre_id == $arr['id']){
            $selected = 'selected';
        }
        $option .= "<option value={$arr['id']} $selected>{$arr['title']}\n";
    }
    return $option;
}

$upload_str = '';


?>
<script type="text/javascript">
function change_list(){
    var opt_sort = document.getElementById('sort_opt');
    document.location = 'add_karaoke.php?status='+opt_sort.options[opt_sort.selectedIndex].value+<?echo '\'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&page='.@$_GET['page'].'\''."\n";?>;
}

function open_info(id){
    var info_display = document.getElementById('info_'+id).style.display;
    if (info_display == 'none'){
        document.getElementById('info_'+id).style.display = '';
        doLoad('karaoke_info', id)
    }else{
        document.getElementById('info_'+id).style.display = 'none';
        document.getElementById('storages_content_'+id).innerHTML = '';
    }
}

function display_info(arr, id){
    //alert(arr.toSource())
    if (arr.length > 0){
        document.getElementById('loading_bar_'+id).style.display = 'none';
        
        
        table  = '<tr>'
        table += '<td class="list2" width="70"><?= htmlspecialchars(_('Server'), ENT_QUOTES)?></td>'
        table += '<td class="list2" width="70"><?= htmlspecialchars(_('File'), ENT_QUOTES)?></td>'
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
                    alert('<?= htmlspecialchars(_('Error: The file or directory may contain invalid characters'), ENT_QUOTES)?>')
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

    form_.setAttribute('action', action);
    form_.setAttribute('method', 'POST');
    form_.submit()
}

function check_protocol(){

    var protocol_obj = document.getElementById('protocol');
    var rtsp_url_block = document.getElementById('rtsp_url_block');

    if (protocol_obj.options[protocol_obj.selectedIndex].value == 'custom'){
        rtsp_url_block.style.display = '';
    }else{
        rtsp_url_block.style.display = 'none';
    }
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
            <?= _('Name')?>:
           </td>
           <td>
            <input type="text" size="40" name="name" id="name" value='<? echo @$name ?>'>
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Protocol')?>:
           </td>
           <td>
             <select name="protocol" id="protocol" onchange="check_protocol()">
                 <option value="nfs" <?if ($protocol == 'nfs'){ echo 'selected';}?>>NFS</option>
                 <option value="http" <?if ($protocol == 'http'){ echo 'selected';}?>>HTTP</option>
                 <option value="custom" <?if ($protocol == 'custom'){ echo 'selected';}?>>Custom URL</option>
             </select>
           </td>
        </tr>
        <tr id="rtsp_url_block" <?if ($protocol != 'custom'){ echo 'style="display:none"';}?>>
           <td align="right" valign="top">
           RTSP/HTTP URL: 
           </td>
           <td>
            <input name="rtsp_url" id="rtsp_url" type="text" onblur="" size="40" value="<? echo @$rtsp_url ?>"> (<?= _('include solution')?>)
           </td>
        </tr> 
        <tr>
           <td align="right">
            <?= _('Performer')?>:
           </td>
           <td>
            <input type="text" size="40" name="singer" id="singer" value='<? echo @$singer ?>'>
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Author')?>:
           </td>
           <td>
            <input name="author" type="text" size="40" value='<? echo @$author ?>'>
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            <?= _('Genre')?>:
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
           <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="save()">&nbsp;<input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='add_karaoke.php'">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <br> 
           <b><?= _('Form filling order')?>:</b><br><br>
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