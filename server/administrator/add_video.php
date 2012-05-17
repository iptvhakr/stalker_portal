<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = Database::getInstance();

moderator_access();

$search = @$_GET['search'];
$letter = @$_GET['letter'];
$checked = '';

if (!$_SERVER['QUERY_STRING']){
    unset($_SESSION['upload']);
}

if (@$_SESSION['media_id'] != @$_GET['id'] && !empty($_GET['id'])){
    unset($_SESSION['upload']);
}

if (@$_GET['id']){
    $_SESSION['media_id'] = $_GET['id'];
}

if (@$_GET['reset_sound_vote'] && @$_GET['id']){
    
    $sql = "update video set vote_sound_good=0, vote_sound_bad=0 where id=".intval($_GET['id']);
    $rs=$db->executeQuery($sql);
    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['reset_video_vote'] && @$_GET['id']){
    
    $sql = "update video set vote_video_good=0, vote_video_bad=0 where id=".intval($_GET['id']);
    $rs=$db->executeQuery($sql);
    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (isset($_GET['accessed']) && @$_GET['id']){
	$_GET['accessed'] = intval($_GET['accessed']);

    $video_id = intval($_GET['id']);

    if (!empty($_GET['date_on'])){
        $date_on = date("Y-m-d", strtotime($_GET['date_on']));
    }

    if (isset($date_on) && $date_on == date("Y-m-d")){
        Mysql::getInstance()->delete('video_on_tasks', array('video_id' => $video_id));
    }

    if (isset($date_on) && $date_on != date("Y-m-d")){

        //var_dump($date_on, $_GET['id']); exit;

        $video_on_task = Mysql::getInstance()->from('video_on_tasks')->where(array('video_id' => $video_id))->get()->first();

        $data = array(
            'video_id' => $video_id,
            'date_on'  => $date_on
        );

        if (empty($video_on_task)){
            Mysql::getInstance()->insert('video_on_tasks', $data);
        }else{
            Mysql::getInstance()->update('video_on_tasks', $data, array('video_id' => $video_id));
        }

    }else{
    
        /*set_accessed(@$_GET['id'], @$_GET['accessed']);
        $id = $_GET['id'];
        $path = get_path($_GET['id']);

        $query = 'select * from video where id='.intval($_GET['id']);
        $rs = $db->executeQuery($query);
        $video = $rs->getValuesByRow(0);

        $name = mysql_real_escape_string($video['name']);
        $o_name = mysql_real_escape_string($video['o_name']);
        $director = mysql_real_escape_string($video['director']);

        $year = $video['year'];

        if ($_GET['accessed'] == 1){
            add_video_log('on', @$_GET['id']);
            $sql = "update updated_places set vclub=1";
            $db->executeQuery($sql);

            if ($video['hd']){
                // disable this video in SD for hd devices
                $sql = "update video set disable_for_hd_devices=1 where name='$name' and o_name='$o_name' and director='$director' and year='$year' and hd=0";
                $db->executeQuery($sql);
            }

        }else{
            add_video_log('off', @$_GET['id']);

            if ($video['hd']){
                $sql = "update video set disable_for_hd_devices=0 where name='$name' and o_name='$o_name' and director='$director' and year='$year' and hd=0";
                $db->executeQuery($sql);
            }
        }*/

        if ($_GET['accessed'] == 1){
            Video::switchOnById($video_id);
        }else{
            Video::switchOffById($video_id);
        }

        /*if ($_GET['accessed'] == 1){
            $master = new VideoMaster();
            try {
                $master->startMD5SumInAllStorages($path);
            }catch (Exception $exception){

            }
        }*/
    }
    
    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['del']){
    $query = "delete from video where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (count(@$_POST) > 0){
    if (check_access(array(1, 2))){
        if (isset($_FILES['screenshot'])){
            if (is_uploaded_file($_FILES['screenshot']['tmp_name'])){
                if (preg_match("/jpeg/",$_FILES['screenshot']['type'])){
               
                    $insert_upload = "INSERT INTO screenshots (name,
                                                               size,
                                                               type
                                                               )
                                                       VALUES ('".$_FILES['screenshot']['name']."', 
                                                               '".$_FILES['screenshot']['size']."', 
                                                               '".$_FILES['screenshot']['type']."'
                                                               )";
                    //echo $insert_upload;
                    $rs=$db->executeQuery($insert_upload);
                
                    $upload_id = mysql_insert_id();
                    
                    if (empty($_SESSION['upload'])){
                        $_SESSION['upload'] = array();
                    }
                    
                    $_SESSION['upload'][] = $upload_id;
                    
                    $img_path = get_save_folder($upload_id);
                    umask(0);
                    rename($_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
                    chmod($img_path.'/'.$upload_id.'.jpg', 0644);
                }
            }
        }
        
        $type = '';
        
        if (!$error){
            
            if (@$_POST['censored'] == 'on'){
                $censored = 1;
            }else{
                $censored = 0;
            }
            
            if (@$_POST['hd'] == 'on'){
                $hd = 1;
            }else{
                $hd = 0;
            }

            if (@$_POST['for_sd_stb'] == 'on'){
                $for_sd_stb = 1;
            }else{
                $for_sd_stb = 0;
            }
            
            $genre_id_1 = @$_POST['genre_id_1'] ? @$_POST['genre_id_1'] : 0;
            $genre_id_2 = @$_POST['genre_id_2'] ? @$_POST['genre_id_2'] : 0;
            $genre_id_3 = @$_POST['genre_id_3'] ? @$_POST['genre_id_3'] : 0;
            $genre_id_4 = @$_POST['genre_id_4'] ? @$_POST['genre_id_4'] : 0;
            
            $cat_genre_id_1 = @$_POST['cat_genre_id_1'] ? @$_POST['cat_genre_id_1'] : 0;
            $cat_genre_id_2 = @$_POST['cat_genre_id_2'] ? @$_POST['cat_genre_id_2'] : 0;
            $cat_genre_id_3 = @$_POST['cat_genre_id_3'] ? @$_POST['cat_genre_id_3'] : 0;
            $cat_genre_id_4 = @$_POST['cat_genre_id_4'] ? @$_POST['cat_genre_id_4'] : 0;
            
            $category_id = @$_POST['category_id'] ? @intval($_POST['category_id']) : 0;

            $protocol = $_POST['protocol'];

            if ($protocol == 'custom'){
                $rtsp_url = @trim($_POST['rtsp_url']);
            }else{
                $rtsp_url = '';
            }
            
            $status = $rtsp_url? 1 : 0;

            if (!empty($_GET['id'])){
                $video = Video::getById($_GET['id']);
            }
            
            if (@$_GET['save']){
                $trans_name = transliterate(@urldecode($_POST['name']));
                
                if ($hd){
                    $trans_name .= '_HD';
                }
                $sql = "select * from video where path='".$trans_name."'";
                $rs = $db->executeQuery($sql);
                $rows = @$rs->getRowCount();
                
                if ($rows > 0){
                    $error = _('Error: The folder with that name already exists');
                }

                if(@$_GET['name'] && !$error){
                    $master = new VideoMaster();
                    try{
                        $master->createMediaDir($trans_name);
                    }catch(MasterException $e){
                        //var_dump($e->getMessage(), $e->getStorageName()); exit;
                        $moderator_storages = $master->getModeratorStorages();
                        if (!empty($moderator_storages[$e->getStorageName()])){
                            $error = _('Error creating the folder on moderator storage');
                        }
                    }
                }
                
                if(@$_GET['name'] && !$error){
                    
                    $name = mysql_escape_string(@$_POST['name']);
                    $o_name = mysql_escape_string(@$_POST['o_name']);
                    $director = mysql_escape_string(@$_POST['director']);
                    $year = @$_POST['year'];
                    
                    // disable this video in SD for hd devices
                    //$sql = "update video set disable_for_hd_devices=1 where name='$name' and o_name='$o_name' and director='$director' and year='$year'";
                    //$db->executeQuery($sql);
                    
                    $datetime = date("Y-m-d H:i:s");
                    
                    $query = "insert into video (name,
                                                 o_name,
                                                 censored,
                                                 hd,
                                                 for_sd_stb,
                                                 path,
                                                 protocol,
                                                 rtsp_url,
                                                 time,
                                                 description,
                                                 genre_id_1,
                                                 genre_id_2,
                                                 genre_id_3,
                                                 genre_id_4,
                                                 cat_genre_id_1,
                                                 cat_genre_id_2,
                                                 cat_genre_id_3,
                                                 cat_genre_id_4,
                                                 category_id,
                                                 director,
                                                 actors,
                                                 added,
                                                 status,
                                                 year,
                                                 volume_correction,
                                                 kinopoisk_id,
                                                 rating_kinopoisk,
                                                 rating_count_kinopoisk,
                                                 rating_imdb,
                                                 rating_count_imdb
                                                 ) 
                                        values ('".trim(mysql_real_escape_string($name))."',
                                                '".trim(mysql_real_escape_string($o_name))."',
                                                '".$censored."',
                                                '".$hd."',
                                                '".$for_sd_stb."',
                                                '".$trans_name."',
                                                '".$protocol."',
                                                '".$rtsp_url."',
                                                '".@$_POST['time']."',
                                                '".mysql_real_escape_string(@$_POST['description'])."',
                                                '".$genre_id_1."',
                                                '".$genre_id_2."',
                                                '".$genre_id_3."',
                                                '".$genre_id_4."',
                                                '".$cat_genre_id_1."',
                                                '".$cat_genre_id_2."',
                                                '".$cat_genre_id_3."',
                                                '".$cat_genre_id_4."',
                                                '".$category_id."',
                                                '".$director."',
                                                '".mysql_real_escape_string(@$_POST['actors'])."',
                                                '".$datetime."',
                                                $status,
                                                '".$year."',
                                                ".intval($_POST['volume_correction']).",
                                                '".$_POST['kinopoisk_id']."',
                                                '".$_POST['rating_kinopoisk']."',
                                                '".$_POST['rating_count_kinopoisk']."',
                                                '".$_POST['rating_imdb']."',
                                                '".$_POST['rating_count_imdb']."'
                                                )";
                    //echo $query;
                    $rs = $db->executeQuery($query);

                    $video_id = mysql_insert_id();
                    
                    if(@$_SESSION['upload']){
                        $query = 'UPDATE screenshots SET media_id=\''.mysql_insert_id().'\' WHERE id IN ('.implode(',', $_SESSION['upload']).')';
                        $rs=$db->executeQuery($query);
                        unset($_SESSION['upload']);
                    }
                    
                    add_video_log('add', $rs->getLastInsertId());

                    //header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
                    //exit;
                }
                else if (!$error){
                    $error = _('Error: all fields are required');
                }
            }
            
            if (@$_GET['update']){
                

                $video_id = intval(@$_GET['id']);

                if(@$_GET['name']){
                    $query = "update video set name='".trim(mysql_real_escape_string($_POST['name']))."', 
                                               o_name='".trim(mysql_real_escape_string($_POST['o_name']))."', 
                                               censored='".$censored."', 
                                               hd='".$hd."', 
                                               for_sd_stb='".$for_sd_stb."',
                                               protocol='".$protocol."',
                                               rtsp_url='".$rtsp_url."',
                                               time='".@$_POST['time']."',
                                               description='".@mysql_real_escape_string($_POST['description'])."', 
                                               genre_id_1='".$genre_id_1."',  
                                               genre_id_2='".$genre_id_2."', 
                                               genre_id_3='".$genre_id_3."', 
                                               genre_id_4='".$genre_id_4."', 
                                               cat_genre_id_1='".$cat_genre_id_1."',  
                                               cat_genre_id_2='".$cat_genre_id_2."', 
                                               cat_genre_id_3='".$cat_genre_id_3."', 
                                               cat_genre_id_4='".$cat_genre_id_4."', 
                                               category_id='".$category_id."', 
                                               director='".@$_POST['director']."', 
                                               actors='".@mysql_real_escape_string($_POST['actors'])."', 
                                               status=$status,
                                               year='".@$_POST['year']."',
                                               volume_correction=".intval($_POST['volume_correction']).",
                                               kinopoisk_id='".$_POST['kinopoisk_id']."',
                                               rating_kinopoisk='".$_POST['rating_kinopoisk']."',
                                               rating_count_kinopoisk='".$_POST['rating_count_kinopoisk']."',
                                               rating_imdb='".$_POST['rating_imdb']."',
                                               rating_count_imdb='".$_POST['rating_count_imdb']."'
                                            where id=".intval(@$_GET['id']);
                    //echo $query; exit;
                    $rs=$db->executeQuery($query);
                    add_video_log('edit', intval(@$_GET['id']));
                    $query = 'UPDATE screenshots SET media_id=\''.intval(@$_GET['id']).'\' WHERE id IN ('.implode(',', $_SESSION['upload']).')';
                    //echo $query;
                    $rs=$db->executeQuery($query);
        
                    unset($_SESSION['upload']);

                    //header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".$_GET['page']);
                    //exit;
                }
                else{
                    $error = _('Error: all fields are required');
                }
            }

            if ($error){
                return;
            }

            //var_dump(!empty($_POST['rating_kinopoisk']), empty($video), $video['rating_kinopoisk'] != $_POST['rating_kinopoisk']);exit;

            if (!empty($_POST['rating_kinopoisk']) && (empty($video) || $video['rating_kinopoisk'] != $_POST['rating_kinopoisk'])){
                Mysql::getInstance()->update('video', array('rating_last_update' => 'NOW()'), array('id' => $video_id));
            }

            header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
            exit;
        }
    }else{
        $error = _('Error: insufficient permissions for this action');
    }
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link type="text/css" href="css/jquery.ui.all.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.min.js"></script>
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

#video-on-form label{
    font-style: italic;
}

#video-on-form label, #video-on-form input{
    display: block;
}

#video-on-form fieldset {
    padding: 0;
    border: 0;
    margin-top: 25px;
}

#video-on-form input.text{
    margin-bottom: 12px;
    width: 100%;
    padding: .4em;
}

.ui-widget{
    font-size: 12px !important;
}

</style>
<script type="text/javascript" src="js.js"></script>
<script type="text/javascript">

    $(function() {

        $("#video_on_date").datepicker({
            dateFormat  : 'dd-mm-yy',
            dayNamesMin : ['<?= _('Sun')?>', '<?= _('Mon')?>', '<?= _('Tue')?>', '<?= _('Wed')?>', '<?= _('Thu')?>', '<?= _('Fri')?>', '<?= _('Sat')?>'],
            firstDay    : 1,
            minDate     : new Date(),
            monthNames  : [
                '<?= _('January')?>',
                '<?= _('February')?>',
                '<?= _('March')?>',
                '<?= _('April')?>',
                '<?= _('May')?>',
                '<?= _('June')?>',
                '<?= _('July')?>',
                '<?= _('August')?>',
                '<?= _('September')?>',
                '<?= _('October')?>',
                '<?= _('November')?>',
                '<?= _('December')?>'
            ]
        });

        //$("#video_on_date").datepicker("setDate", new Date());

        $(".buttons").delegate(".switch_button", "click", function(){
            $("#video_on_id").val($(this).attr('video-id'));

            if ($(this).attr('date-on') != ''){
                $("#video_on_date").datepicker("setDate", $(this).attr('date-on'));
            }else{
                $("#video_on_date").datepicker("setDate", new Date());
            }

            $("#video-on-form").dialog('open');
            return false;
        });

        $("#video-on-form").dialog({
            autoOpen: false,
            height: 200,
            width: 350,
            modal: true,
            buttons: {
                "<?= _('Turn on')?>" : function(){
                    window.location = "add_video.php?date_on="+$('#video_on_date').val()+"&accessed=1&id="+$("#video_on_id").val()+"&letter="+getURLParameter('letter')+"&search="+getURLParameter('search')+"&page="+getURLParameter('page');
                },
                "<?= _('Cancel')?>" : function(){
                    $(this).dialog("close");
                }
            },
            close : function(){
                
            }
        });
    });
    
    function getURLParameter(name) {
        return decodeURI(
            (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
        );
    }

</script>
<title>
<?= _('VIDEO CLUB')?>
</title>
</head>
<body onload="init_genres();fill_category();get_cat_genres();">

<div id="video-on-form" title="<?= _('Schedule turn the video on')?>">
    <p class="validateTips"></p>

    <form onsubmit="return false">
    <fieldset>
        <label for="video_on_date"><?= _('Date')?></label>
        <input type="text" readonly="readonly" name="video_on_date" id="video_on_date" class="text ui-widget-content ui-corner-all" />
        <input type="hidden" id="video_on_id">
    </fieldset>
    </form>
</div>

<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('VIDEO CLUB')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="add_moderator_mac.php"><?= _('Moderators MAC addresses')?></a>
        <?
        if (check_access(array(1, 2))){
            echo '| <a href="myvideolog.php">'._('My logs').'</a>';
        }
        ?>
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
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&status='.@$_GET['status'].'&vote='.@$_GET['vote'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

function send_button($id){
    $db = Database::getInstance();
    
    $sql = "select * from moderator_tasks where ended=0 and media_id=$id";
    $rs = $db->executeQuery($sql);
    
    if ($rs->getRowCount() > 0){
        $task_id = $rs->getValueByName(0, 'id');;
        return "<a href='msgs.php?task=".$task_id."'><font color='#CBCB00'>"._('task')."</font></a>&nbsp;&nbsp;\n";
    }else{
        return "<a href='send_to.php?id=".$id."'>"._('send')."</a>&nbsp;&nbsp;\n";
    }
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = '';
if ($search){
    $where = 'where name like "%'.$search.'%" or o_name like "%'.$search.'%" or path like "%'.$search.'%"';
}
if (@$_GET['letter']) {
	//$where = 'where name like "'.urldecode($letter).'%"';
	$where = 'where name like "'.$letter.'%"';
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

$order_by = ' order by name ';

if (@$_GET['vote']){
    if ($_GET['vote'] == 'good_sound'){
        $order_by = ' order by vote_sound_good desc ';
    }elseif ($_GET['vote'] == 'bad_sound'){
        $order_by = ' order by vote_sound_bad desc';
    }elseif ($_GET['vote'] == 'good_video'){
        $order_by = ' order by vote_video_good desc';
    }elseif ($_GET['vote'] == 'bad_video'){
        $order_by = ' order by vote_video_bad desc';
    }
}

$query = "select * from video $where";
//echo $query;
$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select video.*, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter from video left join media_claims on video.id=media_claims.media_id and media_claims.media_type='vclub' $where group by video.id $order_by LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$rs = $db->executeQuery($query);
//echo $total_pages;
?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= _('Search')?>">&nbsp;<font color="Gray"><?= _('search by file name of movie name')?></font>
</form>
<td>
</tr>
<? if (substr($locale, 0, 2) == 'ru'){?>
<tr>
<td align="center">
<a href="?letter=А">А</a>&nbsp
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

<table>
<tr>
<td>
<?= _('Status')?>:
<select id="sort_opt" onchange="change_list()">
    <option value="">---
    <option value="on" <?if (@$_GET['status'] == 'on') echo 'selected'?>>on
    <option value="off" <?if (@$_GET['status'] == 'off') echo 'selected'?>>off
</select>&nbsp;&nbsp;&nbsp;
<?= _('Votes')?>:
<select id="sort_vote" onchange="change_list()">
    <option value="">---
    <option value="good_sound" <?if (@$_GET['vote'] == 'good_sound') echo 'selected'?>><?= _('good sound')?>
    <option value="bad_sound" <?if (@$_GET['vote'] == 'bad_sound')  echo 'selected'?>><?= _('bad sound')?>
    <option value="good_video" <?if (@$_GET['vote'] == 'good_video') echo 'selected'?>><?= _('good video')?>
    <option value="bad_video" <?if (@$_GET['vote'] == 'bad_video')  echo 'selected'?>><?= _('bad video')?>
</select>
</td>
</tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>"._('Folder')."</b></td>\n";
echo "<td class='list'><b>"._('Title')."</b></td>\n";
echo "<td class='list'><b>"._('Original title')."</b></td>\n";
echo "<td class='list'><b>"._('Restriction')."</b></td>\n";
echo "<td class='list'><b>"._('Volume correction')."</b></td>\n";
echo "<td class='list'><b>"._('Duration, min')."</b></td>\n";
echo "<td class='list'><b>"._('Series')."</b></td>\n";
echo "<td class='list'><b>"._('Claims about<br>audio/video')."</b></td>\n";
echo "<td class='list'>&nbsp;</td>\n";
echo "<td class='list'><b>"._('Turn on date')."</b></td>\n";
echo "</tr>\n";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'><a href='videolog.php?id={$arr['id']}'>".$arr['id']."</a></td>\n";
    //echo "<td class='list'>".get_path_color($arr['path'])."</td>\n";
    echo "<td class='list'><a href='javascript://' ";
    
    if (empty($arr['rtsp_url'])){
        echo "onclick='open_info({$arr['id']})'";
    }
    echo ">".get_path_color($arr['id'], $arr['path'])."</a></td>\n";
    echo "<td class='list'>".$arr['name']."</td>\n";
    echo "<td class='list'>".$arr['o_name']."</td>\n";
    echo "<td class='list'>".$arr['censored']."</td>\n";
    echo "<td class='list'>".$arr['volume_correction']."</td>\n";
    echo "<td class='list'>".$arr['time']."</td>\n";
    echo "<td class='list'><span id='series_{$arr['id']}'>".count_series($arr['series'])."</span></td>\n";
    echo "<td class='list' align='center'>";
    
    if (check_access(array(1))){
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to reset claims counter?')."\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".$arr['sound_counter']." / ".$arr['video_counter']."</span>";
    if (check_access(array(1))){
        echo "</a>";
    }
    echo "</td>\n";

    echo "<td class='list buttons'>";
    if (check_access(array(1, 2))){
        echo "<a href='?edit=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."&#form'>edit</a>&nbsp;&nbsp;\n";
    }
    if (check_access(array(1))){
        echo send_button($arr['id']);
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"add_video.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
    }
    echo get_accessed_color($arr['id']);
    echo '</td>';
    echo "<td class='list'>".$arr['added']."</td>\n";
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

if (@$_GET['edit']){
    $query = "select * from video where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $item = $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $o_name = $arr['o_name'];
        $censored = $arr['censored'];
        $description = $arr['description'];
        $category_id = $arr['category_id'];
        $director = $arr['director'];
        $actors   = $arr['actors'];
        $time     = $arr['time'];
        $year     = $arr['year'];
        $path     = $arr['path'];
        $hd       = $arr['hd'];
        $rtsp_url = $arr['rtsp_url'];
        $protocol = $arr['protocol'];
        
        $for_sd_stb = $arr['for_sd_stb'];
        
        $volume_correction = $arr['volume_correction'];
        $readonly = 'readonly';
        if ($censored){
            $checked = 'checked';
        }
        if ($hd){
            $checked_hd = 'checked';
        }
        if ($for_sd_stb){
            $checked_for_sd_stb = 'checked';
        }
    }
    $query = "select * from screenshots where media_id=".intval(@$_GET['id']);
    //echo $query;
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $_SESSION['upload'][] = $arr['id'];
    }
}

function add_video_log($action, $video_id){
    $db = Database::getInstance();
    $moderator_id = $_SESSION['uid'];
    $query = "insert into video_log (action, video_id, moderator_id, actiontime) values ('$action', $video_id, $moderator_id, NOW())";
    $rs=$db->executeQuery($query);
}

function check_incoming_path($path){
    if (is_dir(INCOMING_DIR.$path)) {
    }else{
        umask(0);
        mkdir(INCOMING_DIR.$path, 0777);
    }
    
    if (is_dir(VIDEO_STORAGE_DIR.$path)) {
    }else{
        umask(0);
        mkdir(VIDEO_STORAGE_DIR.$path, 0777);
    }
}

function del_incoming_path($path){
    return @rmdir(INCOMING_DIR.$path);
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

function count_series($series){
    return count(unserialize($series));
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

/*function get_path($id){
    $db = Database::getInstance();
    
    $query = "select * from video where id=$id";
    $rs=$db->executeQuery($query);
    $old_path = $rs->getValueByName(0, 'path');
    if ($old_path){
        return $old_path;
    }else{
        return '';
    }
}*/

/*function set_status($path, $val){
    $db = Database::getInstance();
    if ($path != ''){
        $query = "update video set status=$val where path='$path'";
        $rs=$db->executeQuery($query);
    }
}*/

/*function set_accessed($id, $val){
    $db = Database::getInstance();
    if ($id){
        $query = "update video set accessed=$val,added=NOW()  where id='$id'";
        $rs=$db->executeQuery($query);
    }
}*/

function get_accessed($id){
    $db = Database::getInstance();
    
    $query = "select * from video where id=$id";
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

    $hint = '';
    $date_on = '';

    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    if (check_access(array(1))){
        if ($accessed){
            $class = "switch_button";
            $video_on_task = Mysql::getInstance()->from('video_on_tasks')->where(array('video_id' => $id))->get()->first();
            if (!empty($video_on_task)){
                $color   = 'orange';
                $hint    = sprintf(_('turn on %s'), $video_on_task['date_on']);
                $date_on = date("d-m-Y", strtotime($video_on_task['date_on']));
            }
        }else{
            $class = "";
        }
        return "<a class='$class' title='$hint' video-id='$id' date-on='$date_on' href='add_video.php?accessed=$accessed&id=$id&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_genres(){
    $db = Database::getInstance();
    global $genre_id;
    
    $query = "select * from genre order by title";
    $rs=$db->executeQuery($query);
    $option = '';
    $str = "var all_genres = [ ";
    while(@$rs->next()){
        $selected = '';
        $arr=$rs->getCurrentValuesAsHash();
        if ($genre_id == $arr['id']){
            $selected = 'selected';
        }
        //$option .= "<option value={$arr['id']} $selected>{$arr['title']}\n";
        $str .= "{ id : ".$arr['id'].", title : '"._($arr['title'])."'},";
    }
    $str = substr($str, 0, strlen($str)-1);
    $str .= " ]";
    return $str;
}

function get_selected_genres(){
    $db = Database::getInstance();
    
    $genre_id = array();
    $str = '';
    if (@$_GET['id']){
        $id = $_GET['id'];
        $sql = "select * from video where id=".$id;
        $rs=$db->executeQuery($sql);
        //if ($rs->getRowCount() == 1){
            $genre_id[1] = $rs->getValueByName(0, 'genre_id_1');
            $genre_id[2] = $rs->getValueByName(0, 'genre_id_2');
            $genre_id[3] = $rs->getValueByName(0, 'genre_id_3');
            $genre_id[4] = $rs->getValueByName(0, 'genre_id_4');
        //}
    
    
        $genre_id[1] = $genre_id[1] ? $genre_id[1] : 0;
        $genre_id[2] = $genre_id[2] ? $genre_id[2] : 0;
        $genre_id[3] = $genre_id[3] ? $genre_id[3] : 0;
        $genre_id[4] = $genre_id[4] ? $genre_id[4] : 0;
        
        $k=0;
        
        for ($i = 1; $i <= 4; $i++){
            if ($genre_id[$i] > 0){
                $k ++;
            }
        }
        
        $str  = "var sel_genre_id_1 = ".$genre_id[1]."\n";
        $str .= "var sel_genre_id_2 = ".$genre_id[2]."\n";
        $str .= "var sel_genre_id_3 = ".$genre_id[3]."\n";
        $str .= "var sel_genre_id_4 = ".$genre_id[4]."\n";
        $str .= "var total_genres   = ".$k."\n";
    }
    return $str;
}

function get_categories(){
    $db = Database::getInstance();
    global $category_id;
    
    $query = "select * from media_category order by num";
    $rs=$db->executeQuery($query);
    $option = '';
    $str = "var all_categories = [ ";
    while(@$rs->next()){
        $selected = '';
        $arr=$rs->getCurrentValuesAsHash();
        if ($category_id == $arr['id']){
            $selected = 'selected';
        }
        //$option .= "<option value={$arr['id']} $selected>{$arr['title']}\n";
        $str .= "{ id : ".$arr['id'].", name : '"._($arr['category_name'])."'},";
    }
    $str = substr($str, 0, strlen($str)-1);
    $str .= " ]";
    return $str;
}

function get_selected_cat_genres(){
    $db = Database::getInstance();
    
    $cat_genre_id = array();
    $str = '';
    if (@$_GET['id']){
        $id = $_GET['id'];
        $sql = "select * from video where id=".$id;
        $rs=$db->executeQuery($sql);
        //if ($rs->getRowCount() == 1){
            $cat_genre_id[1] = $rs->getValueByName(0, 'cat_genre_id_1');
            $cat_genre_id[2] = $rs->getValueByName(0, 'cat_genre_id_2');
            $cat_genre_id[3] = $rs->getValueByName(0, 'cat_genre_id_3');
            $cat_genre_id[4] = $rs->getValueByName(0, 'cat_genre_id_4');
        //}
    
    
        $cat_genre_id[1] = $cat_genre_id[1] ? $cat_genre_id[1] : 0;
        $cat_genre_id[2] = $cat_genre_id[2] ? $cat_genre_id[2] : 0;
        $cat_genre_id[3] = $cat_genre_id[3] ? $cat_genre_id[3] : 0;
        $cat_genre_id[4] = $cat_genre_id[4] ? $cat_genre_id[4] : 0;
        
        $k=0;
        
        for ($i = 1; $i <= 4; $i++){
            if ($cat_genre_id[$i] > 0){
                $k ++;
            }
        }
        
        $str  = "var sel_cat_genre_id_1 = ".$cat_genre_id[1]."\n";
        $str .= "var sel_cat_genre_id_2 = ".$cat_genre_id[2]."\n";
        $str .= "var sel_cat_genre_id_3 = ".$cat_genre_id[3]."\n";
        $str .= "var sel_cat_genre_id_4 = ".$cat_genre_id[4]."\n";
        $str .= "var total_cat_genres   = ".$k."\n";
    }else{
        $str  = "var sel_cat_genre_id_1\n";
        $str .= "var sel_cat_genre_id_2\n";
        $str .= "var sel_cat_genre_id_3\n";
        $str .= "var sel_cat_genre_id_4\n";
    }
    return $str;
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
        $upload_str .= $arr['name'].'.....('._('size').': '.$arr['size'].' B) <a href="del_upload.php?id='.$arr['id'].'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&page='.@$_GET['page'].'"> '._('delete').'</a><br>';
    }
}

?>
<script>
var all_cat_genres = new Array()
<?
echo get_selected_cat_genres();
echo "\n";
echo get_categories();
echo "\n";
echo get_genres();
echo "\n";
echo get_selected_genres();
echo "\n";

if (@$_GET['id']){
    echo "var sel_category_id = ".$category_id."\n";
}else{
    echo "var sel_category_id = 0\n";
}

if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){
    echo "var can_md5dum=1\n";
}else{
    echo "var can_md5dum=0\n";
}

?>

var trans = [];
for (var i = 0x410; i <= 0x44F; i++)
  trans[i] = i - 0x350; // А-Яа-я
trans[0x401] = 0xA8;    // Ё
trans[0x451] = 0xB8;    // ё

var escapeOrig = window.escape;

function urlencode(str)
{
  var ret = [];
  for (i = 0; i < str.length; i++)
  {
    n = str.charCodeAt(i);
    if (typeof trans[n] != 'undefined')
      n = trans[n];
    if (n <= 0xFF)
      ret.push(n);
  }
  return escapeOrig(String.fromCharCode.apply(null, ret));
}
/**/

function change_list(){
    var opt_sort = document.getElementById('sort_opt');
    var sort_vote = document.getElementById('sort_vote');
    var url = 'add_video.php?status='+opt_sort.options[opt_sort.selectedIndex].value+'&vote='+sort_vote.options[sort_vote.selectedIndex].value+<?echo '\'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&page='.@$_GET['page'].'\''."\n";?>;
    document.location = url;
}

function md5sum(obj, status, media_name, storage_name){
    if (can_md5dum){
        if (status == 'done'){
            obj.innerHTML = '<?= _('please wait')?>...';
            doLoad('startmd5sum',{'media_name':media_name, 'storage_name':storage_name})
        }
    }else{
        alert('<?= _('Error: insufficient permissions for this action')?>')
    }
}

function open_info(id){
    var info_display = document.getElementById('info_'+id).style.display
    if (info_display == 'none'){
        document.getElementById('info_'+id).style.display = ''
        doLoad('vclub_info', id)
    }else{
        document.getElementById('info_'+id).style.display = 'none'
        document.getElementById('storages_content_'+id).innerHTML = '';
    }
}

function display_info(arr, id){
    //alert(arr.toSource())
    var general_storages = arr.filter(function(e){
        return e['for_moderator'] == 1;
    });

    if (arr.length > 0){
           
        if (general_storages.length > 0 && general_storages.length == arr.length){
            var path_color = '#f4c430';
        }else{
            path_color = 'green';
        }

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
                md5btn_txt = '<?= _('counting')?>...'
            }

            if (arr[i]['for_moderator'] == 1){
                var folder_color = '#f4c430';
            }else{
                folder_color = 'green';
            }

            table +='<tr>';
                 table +='<td class="list2"><b>'+arr[i]['storage_name']+'</b></td>';
                 table +='<td class="list2"><b><a href="#" onclick="document.getElementById(\'files_'+id+'_'+arr[i]['storage_name']+'\').style.display=\'\';return false;"><font color="'+folder_color+'">'+arr[i]['path']+'</font></a></b></td>';
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
                    md5sum = '<?= _('counting')?>...'
                }
                
                table +='<td nowrap width="100%" align="right"><sub><b>'+arr[i]['files'][j]['name']+'</b> '+md5sum+'</sub></td>'
                
                table +='</tr>'
            }
            table +='<tr><td><sub><br></sub></td></tr>';
            table +='</table>';
            table +='</td>';
            table +='</tr>';
            
            document.getElementById('series_'+id).innerHTML = arr[i]['series'];
        }
        
        document.getElementById('storages_content_'+id).innerHTML = table;
        document.getElementById('error_bar_'+id).style.display = 'none';
        document.getElementById('storages_'+id).style.display = '';
        document.getElementById('path_'+id).style.color = path_color;
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
                
                if (get == 'vclub_info'){
                    
                    var info = req.responseJS.data
                    if(info != null){
                        display_info(info, data)
                    }
                    return
                }
                
                if (get == 'startmd5sum'){
                    if (req.responseJS.error){
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= _('error')?>'
                        alert(req.responseJS.error)
                    }else{
                        document.getElementById('md5sum_link_'+data.media_name+'_'+data.storage_name).innerHTML = '<?= _('counting')?>'
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
                    alert('<?= _('Error: The file or directory may contain invalid characters')?>')
                }
            }
        }
    }
    req.caching = false
    
    req.open('POST', 'load.php?get='+get, true)
    send = {data : data}
    //alert(send.toSource())
    req.send(send)
}

function get_cat_genres(){

    cat_obj = document.getElementById('category_id')
    
    for(i = 1; i <= 4; i++){
        if (i > 1){
            document.getElementById('cat_genre_id_'+i).options.length = 0;
            document.getElementById('cat_genre_'+i).style.display = 'none'
        }else{
            document.getElementById('cat_genre_id_'+i).options.length = 0;
        }
    }
    
    if (cat_obj.selectedIndex > 0){
        sel_category_id = cat_obj.options[cat_obj.selectedIndex].value
        
        //alert(sel_category_id)
        if (sel_category_id > 0){
            tmp_cat = sel_category_id
        }else{
            tmp_cat = 0
        }
        doLoad('get_cat_genres', tmp_cat)
    }
}

function set_cat_genres(arr){
    all_cat_genres = arr
    init_cat_genres()
}

function check_name(name){
    doLoad('chk_name', name)
}

function resp_check_name(resp){
    var name_itm = document.getElementById('name_chk')
    if(resp == 1){
        name_itm.style.color = 'red'
        name_itm.innerHTML = '<?= _('Not available')?>'
    }else{
        name_itm.style.color = 'green'
        name_itm.innerHTML = '<?= _('Available')?>'
    }
}

function check_org_name(name){
    doLoad('chk_org_name', name)
}

function hint(){
    alert(document.getElementById('f_file').value)
}

function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    
    id = document.getElementById('id').value
    description = document.getElementById('description').value
    
    action = 'add_video.php?name='+name+'&id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>&page=<? echo @$_GET['page'] ?>'
    
    
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

function genre_proc(num){
    if (num < 4){
        genre_id_obj = document.getElementById('genre_id_'+num)
        genre_obj = document.getElementById('genre_'+num)
        if (genre_id_obj.options[genre_id_obj.selectedIndex].value != '0'){
            fill_genres(num+1)
            document.getElementById('genre_'+(num+1)).style.display = ''
        }else{
            for(i = 1; i <= 4; i++){
                if (i > num){
                    document.getElementById('genre_id_'+i).options.length = 0;
                    document.getElementById('genre_'+i).style.display = 'none'
                }
            }
        }
    }
}

function cat_genre_proc(num){
    if (num < 4){
        cat_genre_id_obj = document.getElementById('cat_genre_id_'+num)
        cat_genre_obj = document.getElementById('cat_genre_'+num)
        if (cat_genre_id_obj.options[cat_genre_id_obj.selectedIndex].value != '0'){
            fill_cat_genres(num+1)
            document.getElementById('cat_genre_'+(num+1)).style.display = ''            
        }else{
            for(i = 1; i <= 4; i++){
                if (i > num){
                    document.getElementById('cat_genre_id_'+i).options.length = 0;
                    document.getElementById('cat_genre_'+i).style.display = 'none'
                }
            }
        }
    }
}

function init_genres(){
    if (typeof(total_genres) != 'undefined'){
        for (var i=1; i<=total_genres; i++){
            document.getElementById('genre_'+i).style.display = ''
            fill_genres(i)
        }
        if (i<=4){
            document.getElementById('genre_'+(i)).style.display = ''
            fill_genres(i)
        }
    }else{
        fill_genres(1)
    }
}

function init_cat_genres(){
    if (typeof(total_cat_genres) != 'undefined'){
        for (var i=1; i<=total_cat_genres; i++){
            document.getElementById('cat_genre_'+i).style.display = ''
            fill_cat_genres(i)
        }
        if (i<=4){
            document.getElementById('cat_genre_'+(i)).style.display = ''
            fill_cat_genres(i)
        }
    }else{
        fill_cat_genres(1)
    }
}

function fill_genres(num){
    genre_id_obj = document.getElementById('genre_id_'+num)
    genre_id_obj.options[0] = new Option('- - -', 0)
    selected = false
    for (var i=0; i<all_genres.length; i++){
        try{
            if (eval('sel_genre_id_'+num) == all_genres[i].id){
                selected = true
            }else{
                selected = false
            }
        }catch(e){}
        genre_id_obj.options[i+1] = new Option(all_genres[i].title, all_genres[i].id, selected, selected)
    }
}

function fill_cat_genres(num){
    cat_genre_id_obj = document.getElementById('cat_genre_id_'+num)
    cat_genre_id_obj.options.length = 0;
    cat_genre_id_obj.options[0] = new Option('- - -', 0)
    selected = false
    //alert(all_cat_genres.toSource())
    for (var i=0; i<all_cat_genres.length; i++){
        try{
            if (eval('sel_cat_genre_id_'+num) == all_cat_genres[i].id){
                selected = true
            }else{
                selected = false
            }
        }catch(e){}
        cat_genre_id_obj.options[i+1] = new Option(all_cat_genres[i].title, all_cat_genres[i].id, selected, selected)
    }
}

function fill_category(){
    category_id_obj = document.getElementById('category_id')
    category_id_obj.options[0] = new Option('- - -', 0)
    selected = false
    for (var i=0; i<all_categories.length; i++){
        try{
            if (eval('sel_category_id') == all_categories[i].id){
                selected = true
            }else{
                selected = false
            }
        }catch(e){}
        category_id_obj.options[i+1] = new Option(all_categories[i].name, all_categories[i].id, selected, selected)
    }
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

function check_kinopoisk_info(orig_name){

    $('.kinopoisk_url').attr('href', '');
    $('.kinopoisk_url').html('');

    $.get('get.php?get=kinopoisk_info', {"oname" : orig_name}, function(response){
        $('.info_loader').hide();
        $('.get_info').show();
        response = JSON.parse(response);

        var result = response.result;

        if (result){
            for (var id in result){
                if (result.hasOwnProperty(id)){
                    //console.log(id, result[id]);
                    $("."+id).val(result[id]);

                    if (id == 'kinopoisk_url'){
                        $('.kinopoisk_url').attr('href', result[id]);
                        $('.kinopoisk_url').html(result[id]);
                    }
                }
            }
        }
    });
}

function check_kinopoisk_rating(orig_name){

    $('.kinopoisk_url').attr('href', '');
    $('.kinopoisk_url').html('');

    $.get('get.php?get=kinopoisk_rating', {"oname" : orig_name}, function(response){

        $('.refresh_img').attr('src', 'css/refresh-static.gif');

        response = JSON.parse(response);

        var result = response.result;

        if (result){
            for (var id in result){
                if (result.hasOwnProperty(id)){
                    $("."+id).val(result[id]);

                    if (id == 'kinopoisk_url'){
                        $('.kinopoisk_url').attr('href', result[id]);
                        $('.kinopoisk_url').html(result[id]);
                    }
                }
            }
        }
    });
}

$(function(){

    $(".get_info").click(function(){
        $('.get_info').hide();
        $('.info_loader').show();

        check_kinopoisk_info($(".o_name").val() || $(".name").val());
    });

    $(".rating_refresh").click(function(){
        $('.refresh_img').attr('src', 'css/refresh-anim.gif');

        check_kinopoisk_rating($(".o_name").val() || $(".name").val());
    });

    if ($('.kinopoisk_id').val()){
        var kinopoisk_url = 'http://www.kinopoisk.ru/level/1/film/'+$('.kinopoisk_id').val()+'/';

        $('.kinopoisk_url').attr('href', kinopoisk_url);
        $('.kinopoisk_url').html(kinopoisk_url);
    }
});

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
            <?= _('Title')?>:
           </td>
           <td>
            <input type="text" size="40" class="name" name="name" id="name" onblur="check_name(this.value)" value="<? echo @htmlspecialchars($name) ?>" <? //echo @$readonly ?>>
            <span id="name_chk"></span>
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
            <input type="hidden" name="kinopoisk_id" class="kinopoisk_id" value="<? echo @$item['kinopoisk_id'] ?>">
            <!--<input type="hidden" name="rating_kinopoisk" class="rating_kinopoisk" value="<?/* echo $item['rating_kinopoisk'] */?>">-->
            <input type="hidden" name="rating_count_kinopoisk" class="rating_count_kinopoisk" value="<? echo @$item['rating_count_kinopoisk'] ?>">
            <input type="hidden" name="rating_imdb" class="rating_imdb" value="<? echo @$item['rating_imdb'] ?>">
            <input type="hidden" name="rating_count_imdb" class="rating_count_imdb" value="<? echo @$item['rating_count_imdb'] ?>">
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Original title')?>:
           </td>
           <td>
            <input name="o_name" id="o_name" class="o_name" type="text" size="40" value="<? echo @$o_name ?>">
            <a style="display: <? echo (Config::getSafe('kinopoisk_rating', true) ? '' : 'none')?>" href="javascript://" class="get_info"><?= _('autofill')?></a><img class="info_loader" src="css/ajax-loader.gif" style="display: none;"/>
            <span id="org_name_chk"></span>
            <div><a class="kinopoisk_url" href=""></a></div>
           </td>
        </tr>

        <tr style="display: <? echo (Config::getSafe('kinopoisk_rating', true) ? '' : 'none')?>">
           <td align="right" valign="top">
           <?= _('Kinopoisk rating')?>:
           </td>
           <td>
               <input type="text" readonly="readonly" name="rating_kinopoisk" class="rating_kinopoisk" value="<? echo @$item['rating_kinopoisk'] ?>">
               <a href="javascript://" class="rating_refresh"><img src="css/refresh-static.gif" class="refresh_img"/></a>
           </td>
        </tr>

        <tr>
           <td align="right" valign="top">
           <?= _('Protocol')?>:
           </td>
           <td>
             <select name="protocol" id="protocol" onchange="check_protocol()">
                 <option value="nfs" <?if (@$protocol == 'nfs'){ echo 'selected';}?>>NFS</option>
                 <option value="http" <?if (@$protocol == 'http'){ echo 'selected';}?>>HTTP</option>
                 <option value="custom" <?if (@$protocol == 'custom'){ echo 'selected';}?>>Custom URL</option>
             </select>
           </td>
        </tr>
        
        <tr id="rtsp_url_block" <?if (@$protocol != 'custom'){ echo 'style="display:none"';}?>>
           <td align="right" valign="top">
           RTSP/HTTP URL: 
           </td>
           <td>
            <input name="rtsp_url" id="rtsp_url" type="text" onblur="" size="40" value="<? echo @$rtsp_url ?>"> (<?= _('include solution')?>)
           </td>
        </tr> 
        
        <tr>
           <td align="right" valign="top">
           <?= _('Age restriction')?>:
           </td>
           <td>
            <input name="censored" id="censored" type="checkbox" <? echo @$checked ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           HD: 
           </td>
           <td>
            <input name="hd" id="hd" type="checkbox" <? echo @$checked_hd ?> >
           </td>
        </tr>

        <? if (Config::get('vclub_mag100_filter')){ ?>
        <tr>
           <td align="right" valign="top">
           <?= _('Only for MAG100')?>:
           </td>
           <td>
            <input name="for_sd_stb" id="for_sd_stb" type="checkbox" <? echo @$checked_for_sd_stb ?> >
           </td>
        </tr>
        <?}?>
        
        <tr id="genre_1" style="background-color:#e0e0e0">
           <td align="right" valign="top">
            <?= _('old genre')?> 1:
           </td>
           <td>
            <select name="genre_id_1" id="genre_id_1" onchange="genre_proc(1)">
            </select>
           </td>
        </tr> 
        <tr id="genre_2" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
           <?= _('old genre')?> 2:
           </td>
           <td>
            <select name="genre_id_2" id="genre_id_2" onchange="genre_proc(2)">
            </select>
           </td>
        </tr>  
        <tr id="genre_3" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
           <?= _('old genre')?> 3:
           </td>
           <td>
            <select name="genre_id_3" id="genre_id_3" onchange="genre_proc(3)">
            </select>
           </td>
        </tr> 
        <tr id="genre_4" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
           <?= _('old genre')?> 4:
           </td>
           <td>
            <select name="genre_id_4" id="genre_id_4" onchange="genre_proc(4)">
            </select>
           </td>
        </tr>
        
        <tr>
           <td align="right" valign="top">
            <?= _('Category')?>:
           </td>
           <td>
            <select name="category_id" id="category_id" onchange="get_cat_genres()">
            </select>
           </td>
        </tr> 
        
        <tr id="cat_genre_1">
           <td align="right" valign="top">
            <?= _('Genre')?> 1:
           </td>
           <td>
            <select name="cat_genre_id_1" id="cat_genre_id_1" onchange="cat_genre_proc(1)">
            </select>
           </td>
        </tr> 
        <tr id="cat_genre_2" style="display:none">
           <td align="right" valign="top">
           <?= _('Genre')?> 2:
           </td>
           <td>
            <select name="cat_genre_id_2" id="cat_genre_id_2" onchange="cat_genre_proc(2)">
            </select>
           </td>
        </tr>  
        <tr id="cat_genre_3" style="display:none">
           <td align="right" valign="top">
           <?= _('Genre')?> 3:
           </td>
           <td>
            <select name="cat_genre_id_3" id="cat_genre_id_3" onchange="cat_genre_proc(3)">
            </select>
           </td>
        </tr> 
        <tr id="cat_genre_4" style="display:none">
           <td align="right" valign="top">
           <?= _('Genre')?> 4:
           </td>
           <td>
            <select name="cat_genre_id_4" id="cat_genre_id_4" onchange="cat_genre_proc(4)">
            </select>
           </td>
        </tr> 
        <tr>
           <td align="right" valign="top">
           <?= _('Year')?>:
           </td>
           <td>
            <input name="year" class="year" type="text" size="4" value="<? echo @$year ?>">
           </td>
        </tr> 
        <tr>
           <td align="right" valign="top">
            <?= _('Duration')?>:
           </td>
           <td>
            <input name="time" type="text" class="duration" size="4" value="<? echo @$time ?>">, <?= _('min')?>
           </td>
        </tr> 
        <tr>
           <td align="right" valign="top">
            <?= _('Director')?>:
           </td>
           <td>
            <input name="director" type="text" class="director" size="40" value="<? echo @$director ?>">
           </td>
        </tr> 
        <tr>
           <td align="right" valign="top">
            <?= _('Actors')?>:
           </td>
           <td>
            <textarea id="actors" name="actors" class="actors" rows="6" cols="30"><? echo @$actors ?></textarea>
           </td>
        </tr>  
        <tr>
           <td align="right" valign="top">
            <?= _('Description')?>:
           </td>
           <td>
            <textarea id="description" name="description" class="description" rows="10" cols="30"><? echo @$description ?></textarea>
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Volume correction')?> (-20...20):
           </td>
           <td>
            <input id="service_id" name="volume_correction" size="50" type="text" value="<? echo @$volume_correction ?>">
           </td>
        </tr>   
        <tr>
           <td align="right">
            <?= _('Cover')?>:
           </td>
           <td>
            <input name="screenshot" size="27" type="file"><input type="submit" value="<?= _('Upload')?>" name="load" >
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
           <input type="button" value="<?= _('Save')?>" onclick="save()">&nbsp;<input type="button" value="<?= _('New')?>" onclick="document.location='add_video.php'">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <br> 
           <b><?= _('Form filling order')?>:</b><br><br>
           <?= _('1. Upload cover.<br>2. Fill form.<br>3. Save.<br>4. Put the converted in folder<br>&nbsp;&nbsp;&nbsp;&nbsp;specified in the column "Folder".<br>5. If necessary, edit or delete a record.<br>6. Click on folder name.<br>7. If the file is physically present in the directory,<br>&nbsp;&nbsp;&nbsp;&nbsp;it is green, if there is no - in red.')?>
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