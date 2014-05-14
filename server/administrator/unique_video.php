<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$storage_name = @$_GET['storage'];

$search = @$_GET['search'];
$letter = @$_GET['letter'];

if (@$_GET['view'] != 'text'){
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?= _('Search unique movies')?></title>
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
if (Admin::isPageActionAllowed()){
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
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= htmlspecialchars(_('error'), ENT_QUOTES)?>'
                        alert(req.responseJS.error)
                    }else{
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= htmlspecialchars(_('counting'), ENT_QUOTES)?>'
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
        table += '<td class="list2" width="70"><?= htmlspecialchars(_('Server'), ENT_QUOTES)?></td>'
        table += '<td class="list2" width="200"><?= htmlspecialchars(_('Folder'), ENT_QUOTES)?></td>'
        table += '<td class="list2" width="60"><?= htmlspecialchars(_('Series'), ENT_QUOTES)?></td>'
        table += '<td class="list2">&nbsp;</td>'
        table += '</tr>'
        
        for (i=0; i<arr.length; i++){
            var md5btn_txt = '';
            if (arr[i]['files'][0]['status'] == 'done'){
                if (arr[i]['files'][0]['md5'] != ''){
                    md5btn_txt = '<?= htmlspecialchars(_('check'), ENT_QUOTES)?>'
                }else{
                    md5btn_txt = '<?= htmlspecialchars(_('count md5 sum'), ENT_QUOTES)?>'
                }
            }else{
                md5btn_txt = '<?= htmlspecialchars(_('counting'), ENT_QUOTES)?>'
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
                    md5sum = '<?= htmlspecialchars(_('counting'), ENT_QUOTES)?>...'
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
            obj.innerHTML = '<?= htmlspecialchars(_('waiting...'), ENT_QUOTES)?>'
            doLoad('startmd5sum',{'media_name':media_name, 'storage_name':storage_name})
        }
    }else{
        alert('<?= htmlspecialchars(_('You are not authorized for this action'), ENT_QUOTES)?>')
    }
}

</script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Search unique movies')?>&nbsp;</b></font>
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
<table class="search_form" align="center" width="60%">
    <tr>
        <td>
            <table>
                <tr>
                    <th><?= _('On server')?></th>
                </tr>
                <tr>
                    <td>
                        <?

                        $storages = Mysql::getInstance()->from('storages')->where(array('status' => 1))->get();
                        
                        while($arr = $storages->next()){

                            echo '<input type="radio" id="'.$arr['storage_name'].'_on_storage" name="on_storage[]" value="'.$arr['storage_name'].'"';
                            
                            $on_storage = array();
                            
                            if (@is_array($_GET['on_storage'])){
                                $on_storage = $_GET['on_storage'];
                            }
                            
                            if (in_array($arr['storage_name'], $on_storage)){
                                echo ' checked';
                            }
                            
                            echo '></input><label for="'.$arr['storage_name'].'_on_storage">'.$arr['storage_name'].'</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table>
                <tr>
                    <th><?= _('Excluding')?></th>
                </tr>
                <tr>
                    <td>
                        <?
                        $storages = Mysql::getInstance()->from('storages')->where(array('status' => 1))->get();

                        while($arr = $storages->next()){
                            echo '<input type="checkbox" id="'.$arr['storage_name'].'_exclude_storage" name="exclude[]" value="'.$arr['storage_name'].'"';
                            
                            $exclude = array();
                            
                            if (@is_array($_GET['exclude'])){
                                $exclude = $_GET['exclude'];
                            }
                            
                            if (in_array($arr['storage_name'], $exclude)){
                                echo ' checked';
                            }
                            
                            echo '></input><label for="'.$arr['storage_name'].'_exclude_storage">'.$arr['storage_name'].'</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>" name="search"/>
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

    $video = Video::getById($id);

    if (!empty($video['rtsp_url'])){
        return 2;
    }

    return $video['status'];
}

$on_storage = array();
                            
if (isset($_GET['on_storage']) && is_array($_GET['on_storage'])){
    $on_storage = $_GET['on_storage'];
}

$on_storage_ids = Mysql::getInstance()
    ->from('storage_cache')
    ->where(array(
        'media_type' => 'vclub',
        'status'     => 1,
    ))
    ->in('storage_name', $on_storage)
    ->get()
    ->all('media_id');

$on_storage_ids_str = implode(",", $on_storage_ids);

if (empty($on_storage_ids_str)){
    $on_storage_ids_str = '0';
}

$exclude = array();
                            
if (@is_array($_GET['exclude'])){
    $exclude = $_GET['exclude'];
}

$exclude_q = array();

foreach ($exclude as $storage){
    $exclude_q[] = "'".$storage."'";
}

$exclude_str = implode(",", $exclude_q);

if (empty($exclude_str)){
    $exclude_str = "'0'";
}

$sql = "select *, count(storage_name) as storages from storage_cache where media_type='vclub' and status=1 and media_id in (".$on_storage_ids_str.") and storage_name not in (".$exclude_str.") group by media_id having storages=1";
//echo $sql."<br>";
$result_ids = Mysql::getInstance()->query($sql)->all('media_id');

$result_ids_str = implode(",", $result_ids);

if (empty($result_ids_str)){
    $result_ids_str = '0';
}

$sql = "select * from video where status in(1,3) and accessed=1 and id in(".$result_ids_str.")";

$total_items = Mysql::getInstance()->query($sql)->count();
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$sql = "select *, count_first_0_5+count_second_0_5 as views from video where status in(1,3) and accessed=1 and id in(".$result_ids_str.") order by views";

if (@$_GET['view'] != 'text'){
    $sql .= " limit $page_offset, $MAX_PAGE_ITEMS";
}

//echo $sql."<br>";
$video = Mysql::getInstance()->query($sql);

$page = @intval($_GET['page']);

$i = $page*$MAX_PAGE_ITEMS + 1;

if (@$_GET['view'] != 'text'){
    echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
    echo "<tr>";
    echo "<td class='list'><b>#</b></td>\n";
    echo "<td class='list'><b>id</b></td>\n";
    echo "<td class='list'><b>"._('Movie')." ("._('folder').")</b></td>\n";
    echo "<td class='list'><b>"._('Total views per month')."</b></td>\n";
    echo "</tr>\n";
    while($arr = $video->next()){
        
        echo "<tr>";
        echo "<td class='list'>".$i."</td>\n";
        echo "<td class='list'>".$arr['id']."</td>\n";
        echo "<td class='list'><a href='javascript://' onclick='open_info({$arr['id']})'>".get_path_color($arr['id'], $arr['path'])."</a></td>\n";
        echo "<td class='list'>".$arr['views']."</td>\n";
        echo "</tr>\n";
        ?>
        
        <tr style="display:none;" id="info_<?echo $arr['id']?>" bgcolor="#f2f2f2">
        <td>
            &nbsp;
        </td>
        
        <td colspan="10">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <table cellpadding="0" cellspacing="0">
               <tr>
                <td id="loading_bar_<?echo $arr['id']?>" style="display:">
                    <?= _('Loading')?>...
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
    
    while($arr = $video->next()){
        echo $i."\t".$arr['path']."\t".$arr['views']."\r\n";
        $i++;
    }
    
}