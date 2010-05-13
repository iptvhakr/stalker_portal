<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

$storage_name = @$_GET['storage'];

$sql = "select * from storages where storage_name='$storage_name'";
$rs = $db->executeQuery($sql);
/*if ($rs->getRowCount() != 1){
    
    exit();
}*/

//echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
//echo '</pre>';

$search = @$_GET['search'];
$letter = @$_GET['letter'];

if (@$_GET['view'] != 'text'){
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Поиск фильмов по хранилищам</title>

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
.list2{
    border-width: 1px;
    border-style: solid;
    border-color: #c5c5c5;
    padding-left: 5px;
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

.search_form th{
    text-align: center;
}
.search_form td{
    text-align: center;
}
</style>

<script language="JavaScript" src="js.js"></script>

<script>
<?
if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){
    echo "var can_md5dum=1\n";
}else{
    echo "var can_md5dum=0\n";
}
?>
function doLoad(get, data){
    
    var req = new Subsys_JsHttpRequest_Js()
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            
            if (req.responseJS) {
                
                if (get == 'vclub_info'){
                    
                    var info = req.responseJS.data
                    if(info != null){
                        display_info(info, data)
                    }
                    return
                }
                
                if (get == 'startmd5sum'){
                    if (req.responseJS.error){
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = 'ошибка'
                        alert(req.responseJS.error)
                    }else{
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = 'идет подсчет'
                    }
                }
                
                if (get == 'chk_name'){
                    var resp = req.responseJS
                    if(resp != null){
                        resp_check_name(resp)
                    }
                    return
                }
                
                if (get == 'chk_org_name'){
                    var resp = req.responseJS
                    if(resp != null){
                        resp_check_org_name(resp)
                    }
                    return
                }
                
                if (get == 'get_cat_genres'){
                    var resp = req.responseJS.data
                    if(resp != null){
                        set_cat_genres(resp)
                    }
                    return
                }
                
            }else{
                if (get == 'vclub_info'){
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

function open_info(id){
    var info_display = document.getElementById('info_'+id).style.display;
    if (info_display == 'none'){
        document.getElementById('info_'+id).style.display = '';
        doLoad('vclub_info', id);
    }else{
        document.getElementById('info_'+id).style.display = 'none';
        document.getElementById('storages_content_'+id).innerHTML = '';
    }
}

function display_info(arr, id){
    //alert(arr.toSource())
    if (arr.length > 0){
        document.getElementById('loading_bar_'+id).style.display = 'none';
        
        var md5sum = ''
        var table  = '<tr>'
        table += '<td class="list2" width="70">Сервер</td>'
        table += '<td class="list2" width="200">Каталог</td>'
        table += '<td class="list2" width="60">Серии</td>'
        table += '<td class="list2">&nbsp;</td>'
        table += '</tr>'
        
        for (i=0; i<arr.length; i++){
            var md5btn_txt = '';
            if (arr[i]['files'][0]['status'] == 'done'){
                if (arr[i]['files'][0]['md5'] != ''){
                    md5btn_txt = 'проверить'
                }else{
                    md5btn_txt = 'посчитать MD5 сумму'
                }
            }else{
                md5btn_txt = 'идет подсчет'
            }
            table +='<tr>'
                 table +='<td class="list2"><b>'+arr[i]['storage_name']+'</b></td>'
                 table +='<td class="list2"><b><a href="#" onclick="document.getElementById(\'files_'+id+'_'+arr[i]['storage_name']+'\').style.display=\'\';return false;"><font color="green">'+arr[i]['path']+'</font></a></b></td>'
                 table +='<td class="list2">'+arr[i]['series']+'</td>'
                 table +='<td class="list2"><sub><a href="#" id="md5sum_link_'+arr[i]['path']+'_'+arr[i]['storage_name']+'" onclick="md5sum(this,\''+arr[i]['files'][0]['status']+'\',\''+arr[i]['path']+'\', \''+arr[i]['storage_name']+'\');return false;">'+md5btn_txt+'</a><sub></td>'
            table +='</tr>'
            
            table +='<tr style="display:none" id="files_'+id+'_'+arr[i]['storage_name']+'">'
            table +='<td colspan="4" class="list2" width="100%" style="padding-right:5px">'
            table +='<table width="100%" border="0" cellpadding="0" cellspacing="0">'
            for (j=0; j<arr[i]['files'].length; j++){
                table +='<tr>'
                if(arr[i]['files'][j]['status'] == 'done'){
                    md5sum = arr[i]['files'][j]['md5'];
                }else{
                    md5sum = 'подсчитывается...'
                }
                
                table +='<td nowrap width="100%" align="right"><sub><b>'+arr[i]['files'][j]['name']+'</b> '+md5sum+'</sub></td>'
                
                table +='</tr>'
            }
            table +='<tr><td><sub><br></sub></td></tr>'
            table +='</table>'
            table +='</td>'
            table +='</tr>'
            
            //document.getElementById('series_'+id).innerHTML = arr[i]['series'];
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

function md5sum(obj, status, media_name, storage_name){
    if (can_md5dum){
        if (status == 'done'){
            obj.innerHTML = 'подождите...'
            doLoad('startmd5sum',{'media_name':media_name, 'storage_name':storage_name})
        }
    }else{
        alert('У Вас нет прав на это действие')
    }
}

</script>

</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Поиск фильмов по хранилищам&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="storages.php"><< Назад</a> | <a href="?<?echo $_SERVER['QUERY_STRING'].'&view=text'?>">В текстовом виде</a>
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

<form method="get">
<table class="search_form" align="center" width="70%">
    <tr>
        <td valign="top">
            <table width="100%">
                <tr>
                    <th>На сервере</th>
                </tr>
                <tr>
                    <td>
                        <?
                        $rs  = $db->executeQuery("select * from storages where status=1");
                        
                        while(@$rs->next()){
                            $arr=$rs->getCurrentValuesAsHash();
                            echo '<input type="radio" id="'.$arr['storage_name'].'_on_storage" name="on_storage" value="'.$arr['storage_name'].'"';
                            
                            $on_storage = @$_GET['on_storage'];
                            
                            if ($arr['storage_name'] == $on_storage){
                                echo ' checked';
                            }
                            
                            echo '></input><label for="'.$arr['storage_name'].'_on_storage">'.$arr['storage_name'].'</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </td>
        <td valign="top">
            <table width="100%">
                <tr>
                    <th width="100%">и еще на</th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="total_storages" value="<? echo @$_GET['total_storages']?>" size="5" maxlength="2"></input> хранилищах
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="Поиск" name="search"></input>
        </td>
    </tr>
</table>
<script>
    function sortby(obj){
        document.location = 'video_search.php?on_storage=<?echo @$_GET['on_storage']?>&total_storages=<?echo @$_GET['total_storages']?>&search=<?echo @$_GET['search']?>&view=<?echo @$_GET['view']?>&sortby='+obj.options[obj.selectedIndex].value;
    }
</script>
<table>
<tr>
    <td colspan="2" width="100%" style="text-align: left">
        Сортировка по
        <select onchange="sortby(this)">
            <option value="">--------</option>
            <option value="count" <?if (@$_GET['sortby'] == 'count') echo 'selected'?>>просмотрам всего</option>
            <option value="month_counter" <?if (@$_GET['sortby'] == 'month_counter') echo 'selected'?>>просмотрам за месяц</option>
            <option value="last_played" <?if (@$_GET['sortby'] == 'last_played') echo 'selected'?>>последнему просмотру</option>
        </select>
    </td>
    </tr>
</table>

</form>

<?
}else{
    header("Content-Type: text/plain");
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;
$page_offset=$page*$MAX_PAGE_ITEMS;

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            //$page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&sort_by='.@$_GET['sort_by'].'">'.$i.'</a> |';
            
            
            if (strpos($_SERVER['QUERY_STRING'], '&page=')){
                $params = str_replace('page='.$page, 'page='.($i-1), $_SERVER['QUERY_STRING']);
            }else{
                $params = $_SERVER['QUERY_STRING'].'&page='.($i-1);
            }
            
            
            $page_bar .= ' <a href="?'.$params.'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

function get_path_color($id, $path){
    if (check_video_status($id)){
            $color = 'green';
        }else{
            $color = 'red';
    }
    return "<span id='path_$id' style='color:$color'>$path</font>";
}

function check_video_status($id){
    $db = Database::getInstance(DB_NAME);
    
    $query = "select * from video where id=$id";
    $rs=$db->executeQuery($query);
    return $rs->getValueByName(0, 'status');
}

$on_storage = '';
                            
if (@$_GET['on_storage']){
    $on_storage = $_GET['on_storage'];
}

$sql = "select * from storage_cache where status=1 and media_type='vclub' and storage_name='$on_storage'";
$rs  = $db->executeQuery($sql);
$on_storage_ids = $rs->getValuesByName('media_id');

$on_storage_ids_str = implode(",", $on_storage_ids);

if (empty($on_storage_ids_str)){
    $on_storage_ids_str = '0';
}

$sortby = '';

if (!empty($_GET['sortby'])){
    
    $sortby = 'order by '.$_GET['sortby'];
}

$sql = "select video.id as video_id, video.id as video_id, path, count(storage_cache.id) as on_storages, (count_first_0_5+count_second_0_5) as month_counter, count, last_played from video left join storage_cache on video.id=storage_cache.media_id and storage_cache.status=1 and storage_cache.media_type='vclub' where video.id in ($on_storage_ids_str) group by video.id having on_storages=".(@intval($_GET['total_storages'])+1).' '.$sortby;

$rs = $db->executeQuery($sql);
$total_items = $rs->getRowCount();

$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

if (@$_GET['view'] != 'text'){
    $sql .= " limit $page_offset, $MAX_PAGE_ITEMS";
}

//echo $sql."<br>";
$rs  = $db->executeQuery($sql);

$page = @intval($_GET['page']);

$i = $page*$MAX_PAGE_ITEMS + 1;

if (@$_GET['view'] != 'text'){
    echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
    echo "<tr>";
    echo "<td class='list'><b>#</b></td>\n";
    echo "<td class='list'><b>id</b></td>\n";
    echo "<td class='list'><b>Фильм (имя папки)</b></td>\n";
    echo "<td class='list'><b>Кол-во хранилищ</b></td>\n";
    echo "<td class='list'><b>Просмотров всего</b></td>\n";
    echo "<td class='list'><b>Просмотров за месяц</b></td>\n";
    echo "<td class='list'><b>Последний просмотр</b></td>\n";
    echo "</tr>\n";
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        
        echo "<tr>";
        echo "<td class='list'>".$i."</td>\n";
        echo "<td class='list'>".$arr['video_id']."</td>\n";
        //echo "<td class='list'>".$arr['path']."</td>\n";
        echo "<td class='list'><a href='javascript://' onclick='open_info({$arr['video_id']})'>".get_path_color($arr['video_id'], $arr['path'])."</a></td>\n";
        echo "<td class='list'>".$arr['on_storages']."</td>\n";
        echo "<td class='list'>".$arr['count']."</td>\n";
        echo "<td class='list'>".$arr['month_counter']."</td>\n";
        echo "<td class='list'>".$arr['last_played']."</td>\n";
        echo "</tr>\n";
        
        ?>
        
        <tr style="display:none;" id="info_<?echo $arr['video_id']?>" bgcolor="#f2f2f2">
        <td>
            &nbsp;
        </td>
        
        <td colspan="10">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <table cellpadding="0" cellspacing="0">
               <tr>
                <td id="loading_bar_<?echo $arr['video_id']?>" style="display:">
                    Загрузка...
                </td>
                <td id="error_bar_<?echo $arr['video_id']?>" style="display:none">
                    <font color="red">Не найден!</font>
                </td>
                <td style="display:none" id="storages_<?echo $arr['video_id']?>">
                    <table class='list' border="1" cellpadding='0' cellspacing='0' id="storages_content_<?echo $arr['video_id']?>">
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
        
        $i++;
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
}else{
    
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        
        echo $i."\t".$arr['path']."\t".$arr['on_storages']."\r\n";
        $i++;
    }
}