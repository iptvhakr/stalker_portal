<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

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
<title><?= _('Search movies on storages')?></title>

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

<script type="text/javascript" src="js.js"></script>

<script type="text/javascript">
<?
if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){
    echo "var can_md5dum=1\n";
}else{
    echo "var can_md5dum=0\n";
}
?>
function doLoad(get, data){
    
    var req = new Subsys_JsHttpRequest_Js();
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            
            if (req.responseJS) {
                
                if (get == 'vclub_info'){
                    
                    var info = req.responseJS.data;
                    if(info != null){
                        display_info(info, data);
                    }
                    return
                }
                
                if (get == 'startmd5sum'){
                    if (req.responseJS.error){
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= _('error')?>';
                        alert(req.responseJS.error);
                    }else{
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= _('counting')?>';
                    }
                }
                
                if (get == 'chk_name'){
                    var resp = req.responseJS;
                    if(resp != null){
                        resp_check_name(resp)
                    }
                    return;
                }
                
                if (get == 'chk_org_name'){
                    var resp = req.responseJS;
                    if(resp != null){
                        resp_check_org_name(resp)
                    }
                    return;
                }
                
                if (get == 'get_cat_genres'){
                    var resp = req.responseJS.data;
                    if(resp != null){
                        set_cat_genres(resp)
                    }
                    return;
                }
                
            }else{
                if (get == 'vclub_info'){
                    alert('<?= _('Error: The file or directory may contain invalid characters')?>')
                }
            }
        }
    };

    req.caching = false;
    
    req.open('POST', 'load.php?get='+get, true);
    send = {data : data};
    
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
        
        var md5sum = '';
        var table  = '<tr>';
        table += '<td class="list2" width="70"><?= _('Server')?></td>';
        table += '<td class="list2" width="200"><?= _('Folder')?></td>';
        table += '<td class="list2" width="60"><?= _('Series')?></td>';
        table += '<td class="list2">&nbsp;</td>';
        table += '</tr>';
        
        for (i=0; i<arr.length; i++){
            var md5btn_txt = '';
            if (arr[i]['files'][0]['status'] == 'done'){
                if (arr[i]['files'][0]['md5'] != ''){
                    md5btn_txt = '<?= _('check')?>'
                }else{
                    md5btn_txt = '<?= _('count md5 sum')?>'
                }
            }else{
                md5btn_txt = '<?= _('counting')?>'
            }
            table +='<tr>';
                 table +='<td class="list2"><b>'+arr[i]['storage_name']+'</b></td>';
                 table +='<td class="list2"><b><a href="#" onclick="document.getElementById(\'files_'+id+'_'+arr[i]['storage_name']+'\').style.display=\'\';return false;"><font color="green">'+arr[i]['path']+'</font></a></b></td>';
                 table +='<td class="list2">'+arr[i]['series']+'</td>';
                 table +='<td class="list2"><sub><a href="#" id="md5sum_link_'+arr[i]['path']+'_'+arr[i]['storage_name']+'" onclick="md5sum(this,\''+arr[i]['files'][0]['status']+'\',\''+arr[i]['path']+'\', \''+arr[i]['storage_name']+'\');return false;">'+md5btn_txt+'</a></sub></td>';
            table +='</tr>';
            
            table +='<tr style="display:none" id="files_'+id+'_'+arr[i]['storage_name']+'">';
            table +='<td colspan="4" class="list2" width="100%" style="padding-right:5px">';
            table +='<table width="100%" border="0" cellpadding="0" cellspacing="0">';
            for (j=0; j<arr[i]['files'].length; j++){
                table +='<tr>';
                if(arr[i]['files'][j]['status'] == 'done'){
                    md5sum = arr[i]['files'][j]['md5'];
                }else{
                    md5sum = '<?= _('counting')?>...';
                }
                
                table +='<td nowrap width="100%" align="right"><sub><b>'+arr[i]['files'][j]['name']+'</b> '+md5sum+'</sub></td>';
                
                table +='</tr>';
            }
            table +='<tr><td><sub><br></sub></td></tr>';
            table +='</table>';
            table +='</td>';
            table +='</tr>';
            
            //document.getElementById('series_'+id).innerHTML = arr[i]['series'];
        }
        
        document.getElementById('storages_content_'+id).innerHTML = table;
        document.getElementById('error_bar_'+id).style.display = 'none';
        document.getElementById('storages_'+id).style.display = '';
        //document.getElementById('path_'+id).style.color = 'green';
    }else{
        document.getElementById('loading_bar_'+id).style.display = 'none';
        document.getElementById('error_bar_'+id).style.display = '';
        //document.getElementById('path_'+id).style.color = 'red';
    }
}

function md5sum(obj, status, media_name, storage_name){
    if (can_md5dum){
        if (status == 'done'){
            obj.innerHTML = '<?= _('waiting...')?>'
            doLoad('startmd5sum',{'media_name':media_name, 'storage_name':storage_name})
        }
    }else{
        alert('<?= _('You are not authorized for this action')?>')
    }
}

</script>

</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Search movies on storages')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="storages.php"><< <?= _('Back')?></a> | <a href="?<?echo $_SERVER['QUERY_STRING'].'&view=text'?>"><?= _('Plain text')?></a>
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
                    <th><?= _('On server')?></th>
                </tr>
                <tr>
                    <td>
                        <?
                        $rs  = $db->executeQuery("select * from storages where status=1");

                        $on_storages = @$_GET['on_storages'];

                        while(@$rs->next()){
                            $arr=$rs->getCurrentValuesAsHash();
                            echo '<input type="checkbox" id="'.$arr['storage_name'].'_on_storage" name="on_storages[]" value="'.$arr['storage_name'].'"';

                            if (in_array($arr['storage_name'], $on_storages)){
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
                    <th width="100%"><?= _('and on')?></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="total_storages" value="<? echo @$_GET['total_storages']?>" size="5" maxlength="2" /> <?= _('storages')?>
                    </td>
                </tr>
            </table>
        </td>

        <td valign="top">
            <table width="100%">
                <tr>
                    <th width="100%"><?= _('and not on server')?></th>
                </tr>
                <tr>
                    <td>
                        <?
                        $rs  = $db->executeQuery("select * from storages where status=1");

                        $not_on_storages = @$_GET['not_on_storages'];

                        while(@$rs->next()){
                            $arr=$rs->getCurrentValuesAsHash();
                            echo '<input type="checkbox" id="'.$arr['storage_name'].'_not_on_storage" name="not_on_storages[]" value="'.$arr['storage_name'].'"';

                            if (in_array($arr['storage_name'], $not_on_storages)){
                                echo ' checked';
                            }

                            echo '></input><label for="'.$arr['storage_name'].'_not_on_storage">'.$arr['storage_name'].'</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="<?= _('Search')?>" name="search" />
        </td>
    </tr>
</table>
<script type="text/javascript">
    function sortby(obj){
        document.location = 'video_search.php?on_storage=<?echo @$_GET['on_storage']?>&total_storages=<?echo @$_GET['total_storages']?>&search=<?echo @$_GET['search']?>&view=<?echo @$_GET['view']?>&sortby='+obj.options[obj.selectedIndex].value;
    }
</script>
<table>
<tr>
    <td colspan="2" width="100%" style="text-align: left">
        <?= _('Sort by')?>
        <select onchange="sortby(this)">
            <option value="">--------</option>
            <option value="count" <?if (@$_GET['sortby'] == 'count') echo 'selected'?>><?= _('total views')?></option>
            <option value="month_counter" <?if (@$_GET['sortby'] == 'month_counter') echo 'selected'?>><?= _('total views per month')?></option>
            <option value="last_played" <?if (@$_GET['sortby'] == 'last_played') echo 'selected'?>><?= _('last viewed')?></option>
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

    $color_status = check_video_status($id);

    if ($color_status == 1){
        $color = 'green';
    }else if ($color_status == 0){
        $color = 'red';
    }else if ($color_status == 2){
        $color = 'blue';
    }else if ($color_status == 3){
        $color = '#f4c430';
    }
    return "<span id='path_$id' style='color:".$color."'>$path</span>";
}

function check_video_status($id){
    $db = Database::getInstance();
    
    $query = "select * from video where id=$id";
    $rs=$db->executeQuery($query);

    $rtsp_url = $rs->getValueByName(0, 'rtsp_url');

    if (!empty($rtsp_url)){
        return 2;
    }

    return $rs->getValueByName(0, 'status');
}

$on_storages = array();
                            
if (@$_GET['on_storages']){
    $on_storages = $_GET['on_storages'];
}

$not_on_storages = array();

if (@$_GET['not_on_storages']){
    $not_on_storages = $_GET['not_on_storages'];
}

$sql = "select media_id as video_id, path, GROUP_CONCAT(storage_name) as storages, count(storage_name) as on_storages, (count_first_0_5+count_second_0_5) as month_counter, count, last_played from storage_cache,video where video.id=media_id and media_type='vclub' group by media_id";

$having = array();

if (!empty($on_storages)){
    foreach ($on_storages as $on_storage){
        $having[] = 'storages like "%'.$on_storage.'%"';
    }
}

if (!empty($not_on_storages)){
    foreach ($not_on_storages as $not_on_storage){
        $having[] = 'storages not like "%'.$not_on_storage.'%"';
    }
}

if (isset($_GET['total_storages']) && $_GET['total_storages'] !== ''){
    $having[] = "on_storages=".(@intval($_GET['total_storages'])+count($on_storages));
}

if (!empty($having)){
    $sql .= " having ".implode(' and ', $having);
}

if (!empty($_GET['sortby'])){

    $sql .= ' order by '.$_GET['sortby'].', path';
}

//echo $sql;

$rs = $db->executeQuery($sql);
$total_items = $rs->getRowCount();

$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

if (@$_GET['view'] != 'text'){
    $sql .= " limit $page_offset, $MAX_PAGE_ITEMS";
    $i = $page*$MAX_PAGE_ITEMS + 1;
}else{
    $i = 1;
}

//echo $sql."<br>";
$rs  = $db->executeQuery($sql);

$page = @intval($_GET['page']);

//$i = $page*$MAX_PAGE_ITEMS + 1;

if (@$_GET['view'] != 'text'){
    echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
    echo "<tr>";
    echo "<td class='list'><b>#</b></td>\n";
    echo "<td class='list'><b>id</b></td>\n";
    echo "<td class='list'><b>"._('Movie')." ("._('folder').")</b></td>\n";
    echo "<td class='list'><b>"._('Number of storages')."</b></td>\n";
    echo "<td class='list'><b>"._('Total views')."</b></td>\n";
    echo "<td class='list'><b>"._('Total views per month')."</b></td>\n";
    echo "<td class='list'><b>"._('Last viewed')."</b></td>\n";
    echo "</tr>\n";
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        
        echo "<tr>";
        echo "<td class='list'>".$i."</td>\n";
        echo "<td class='list'>".$arr['video_id']."</td>\n";
        //echo "<td class='list'>".$arr['path']."</td>\n";
        echo "<td class='list'><a href='javascript://' onclick='open_info({$arr['video_id']})'>".get_path_color($arr['video_id'], $arr['path'])."</a></td>\n";
        echo "<td class='list'>".$arr['on_storages']." (".$arr['storages'].")</td>\n";
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
                    <?= _('Loading')?>...
                </td>
                <td id="error_bar_<?echo $arr['video_id']?>" style="display:none">
                    <font color="red"><?= _('Not found')?>!</font>
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