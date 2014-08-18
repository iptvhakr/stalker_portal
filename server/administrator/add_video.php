<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    Mysql::getInstance()->update('video',
        array('vote_sound_good' => 0, 'vote_sound_bad' => 0),
        array('id' => intval($_GET['id'])));

    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['reset_video_vote'] && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    Mysql::getInstance()->update('video',
        array('vote_video_good' => 0, 'vote_video_bad' => 0),
        array('id' => intval($_GET['id'])));

    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (isset($_GET['accessed']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

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

        if ($_GET['accessed'] == 1){
            Video::switchOnById($video_id);
        }else{
            Video::switchOffById($video_id);
        }
    }

    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@urldecode($_GET['search'])."&page=".@$_GET['page']);
    exit;
}

if (@$_GET['del']){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Video::log(intval(@$_GET['id']), _('video deleted'));

    Mysql::getInstance()->delete('video', array('id' => intval(@$_GET['id'])));

    header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
    exit;
}

if (count(@$_POST) > 0){
    if (Admin::isEditAllowed() || Admin::isCreateAllowed()){
        if (isset($_FILES['screenshot'])){
            if (is_uploaded_file($_FILES['screenshot']['tmp_name'])){
                if (preg_match("/jpeg/",$_FILES['screenshot']['type'])){

                    $upload_id = Mysql::getInstance()->insert('screenshots', array(
                        'name' => $_FILES['screenshot']['name'],
                        'size' => $_FILES['screenshot']['size'],
                        'type' => $_FILES['screenshot']['type']
                    ))->insert_id();

                    if (empty($_SESSION['upload'])){
                        $_SESSION['upload'] = array();
                    }

                    $_SESSION['upload'][] = $upload_id;

                    $img_path = get_save_folder($upload_id);
                    umask(0);

                    if (!rename($_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg')){
                        $error = sprintf(_('Error during file moving from %s to %s'), $_FILES['screenshot']['tmp_name'], $img_path.'/'.$upload_id.'.jpg');
                        unset($_SESSION['upload']);
                    }else{
                        chmod($img_path.'/'.$upload_id.'.jpg', 0644);
                    }
                }
            }
        }

        if ((empty($_FILES['screenshot']) || empty($_FILES['screenshot']['tmp_name'])) && !empty($_POST['cover_big'])){

            try{
                $tmpfname = tempnam("/tmp", "video_cover");
                $cover_blob = file_get_contents($_POST['cover_big']);
                file_put_contents($tmpfname, $cover_blob);
                $cover = new Imagick($tmpfname);
                unlink($tmpfname);
            }catch(ImagickException $e){
                $error = _('Error: '.$e->getMessage());
            }

            if ($cover){

                if (!$cover->resizeImage(240,320,Imagick::FILTER_LANCZOS,1)){
                    $error = _('Error: could not resize cover');
                }

                $cover_filename = substr($_POST['cover_big'], strrpos($_POST['cover_big'], '/')+1);

                $cover_id = Mysql::getInstance()->insert('screenshots', array('name' => $cover_filename))->insert_id();

                if (empty($_SESSION['upload'])){
                    $_SESSION['upload'] = array();
                }

                $_SESSION['upload'][] = $cover_id;

                $img_path = get_save_folder($cover_id);
                umask(0);

                if (!$error && !$cover->writeImage($img_path.'/'.$cover_id.'.jpg')){
                    $error = _('Error: could not save cover image');
                }

                $cover->destroy();
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

            if (@$_POST['high_quality'] == 'on'){
                $high_quality = 1;
            }else{
                $high_quality = 0;
            }

            if (@$_POST['low_quality'] == 'on'){
                $low_quality = 1;
            }else{
                $low_quality = 0;
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

                $existed = Mysql::getInstance()->from('video')->where(array('path' => $trans_name))->get()->first();

                if (!empty($existed)){
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

                    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

                    $video_id = Mysql::getInstance()->insert(
                        'video',
                        array(
                            'name'           => trim($_POST['name']),
                            'o_name'         => trim($_POST['o_name']),
                            'censored'       => $censored,
                            'hd'             => $hd,
                            'for_sd_stb'     => $for_sd_stb,
                            'protocol'       => $protocol,
                            'rtsp_url'       => $rtsp_url,
                            'time'           => @$_POST['time'],
                            'description'    => $_POST['description'],
                            'genre_id_1'     => $genre_id_1,
                            'genre_id_2'     => $genre_id_2,
                            'genre_id_3'     => $genre_id_3,
                            'genre_id_4'     => $genre_id_4,
                            'cat_genre_id_1' => $cat_genre_id_1,
                            'cat_genre_id_2' => $cat_genre_id_2,
                            'cat_genre_id_3' => $cat_genre_id_3,
                            'cat_genre_id_4' => $cat_genre_id_4,
                            'category_id'    => $category_id,
                            'director'       => $_POST['director'],
                            'actors'         => $_POST['actors'],
                            'status'         => $status,
                            'year'           => $_POST['year'],
                            'volume_correction' => (int) $_POST['volume_correction'],
                            'kinopoisk_id'      => $_POST['kinopoisk_id'],
                            'rating_kinopoisk'  => $_POST['rating_kinopoisk'],
                            'rating_count_kinopoisk' => $_POST['rating_count_kinopoisk'],
                            'rating_imdb'    => $_POST['rating_imdb'],
                            'rating_count_imdb' => $_POST['rating_count_imdb'],
                            'age'            => $_POST['age'],
                            'rating_mpaa'    => $_POST['rating_mpaa'],
                            'path'           => $trans_name,
                            'high_quality'   => $high_quality,
                            'low_quality'    => $low_quality,
                            'comments'       => $_POST['comments'],
                            'country'        => $_POST['country'],
                            'added'          => 'NOW()'
                        )
                    )->insert_id();

                    if(!empty($_SESSION['upload'])){
                        Mysql::getInstance()->query('DELETE from screenshots where media_id='.intval($video_id).' and id not IN ('.@implode(',', $_SESSION['upload']).')');

                        Mysql::getInstance()->query('UPDATE screenshots SET media_id='.intval($video_id).' WHERE id IN ('.@implode(',', $_SESSION['upload']).')');

                        unset($_SESSION['upload']);
                    }

                    if ((empty($_FILES['screenshot']) || empty($_FILES['screenshot']['tmp_name'])) && empty($_POST['cover_big']) && empty($_POST['cover_id'])){
                        Mysql::getInstance()->delete('screenshots', array('media_id' => $video_id));
                    }

                    add_video_log('add', $video_id);

                    //header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
                    //exit;
                }
                else if (!$error){
                    $error = _('Error: all fields are required');
                }
            }

            if (@$_GET['update']){

                Admin::checkAccess(AdminAccess::ACCESS_EDIT);

                $video_id = intval(@$_GET['id']);

                if(@$_GET['name']){

                    Mysql::getInstance()->update(
                        'video',
                        array(
                            'name'           => trim($_POST['name']),
                            'o_name'         => trim($_POST['o_name']),
                            'censored'       => $censored,
                            'hd'             => $hd,
                            'for_sd_stb'     => $for_sd_stb,
                            'protocol'       => $protocol,
                            'rtsp_url'       => $rtsp_url,
                            'time'           => @$_POST['time'],
                            'description'    => $_POST['description'],
                            'genre_id_1'     => $genre_id_1,
                            'genre_id_2'     => $genre_id_2,
                            'genre_id_3'     => $genre_id_3,
                            'genre_id_4'     => $genre_id_4,
                            'cat_genre_id_1' => $cat_genre_id_1,
                            'cat_genre_id_2' => $cat_genre_id_2,
                            'cat_genre_id_3' => $cat_genre_id_3,
                            'cat_genre_id_4' => $cat_genre_id_4,
                            'category_id'    => $category_id,
                            'director'       => $_POST['director'],
                            'actors'         => $_POST['actors'],
                            'status'         => $status,
                            'year'           => $_POST['year'],
                            'volume_correction' => (int) $_POST['volume_correction'],
                            'kinopoisk_id'      => $_POST['kinopoisk_id'],
                            'rating_kinopoisk'  => $_POST['rating_kinopoisk'],
                            'rating_count_kinopoisk' => $_POST['rating_count_kinopoisk'],
                            'rating_imdb'    => $_POST['rating_imdb'],
                            'rating_count_imdb' => $_POST['rating_count_imdb'],
                            'age'            => $_POST['age'],
                            'rating_mpaa'    => $_POST['rating_mpaa'],
                            'high_quality'   => $high_quality,
                            'low_quality'    => $low_quality,
                            'comments'       => $_POST['comments'],
                            'country'        => $_POST['country']
                        ),
                        array(
                            'id' => (int) $_GET['id']
                        )
                    );

                    add_video_log('edit', intval(@$_GET['id']));

                    if (!empty($_SESSION['upload'])){
                        Mysql::getInstance()->query('DELETE from screenshots where media_id=\''.intval(@$_GET['id']).'\' and id not IN ('.@implode(',', $_SESSION['upload']).')');

                        Mysql::getInstance()->query('UPDATE screenshots SET media_id=\''.intval(@$_GET['id']).'\' WHERE id IN ('.@implode(',', $_SESSION['upload']).')');

                        unset($_SESSION['upload']);
                    }

                    if ((empty($_FILES['screenshot']) || empty($_FILES['screenshot']['tmp_name'])) && empty($_POST['cover_big']) && empty($_POST['cover_id'])){
                        Mysql::getInstance()->delete('screenshots', array('media_id' => intval(@$_GET['id'])));
                    }

                    //header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".$_GET['page']);
                    //exit;
                }
                else{
                    $error = _('Error: all fields are required');
                }
            }

            if ($error){
                //return;
            }

            //var_dump(!empty($_POST['rating_kinopoisk']), empty($video), $video['rating_kinopoisk'] != $_POST['rating_kinopoisk']);exit;

            if (!$error){

                if (!empty($_POST['rating_kinopoisk']) && (empty($video) || $video['rating_kinopoisk'] != $_POST['rating_kinopoisk'])){
                    Mysql::getInstance()->update('video', array('rating_last_update' => 'NOW()'), array('id' => $video_id));
                }

                header("Location: add_video.php?letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']);
                exit;
            }
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

.kinopoisk_id[readonly="readonly"]{
    color:#555;
}

</style>
<script type="text/javascript" src="js.js"></script>
<script type="text/javascript">

    $(function() {

        $("#video_on_date").datepicker({
            dateFormat  : 'dd-mm-yy',
            dayNamesMin : [
                '<?= htmlspecialchars(_('Sun'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Mon'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Tue'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Wed'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Thu'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Fri'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('Sat'), ENT_QUOTES)?>'
            ],
            firstDay    : 1,
            minDate     : new Date(),
            monthNames  : [
                '<?= htmlspecialchars(_('January'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('February'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('March'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('April'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('May'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('June'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('July'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('August'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('September'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('October'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('November'), ENT_QUOTES)?>',
                '<?= htmlspecialchars(_('December'), ENT_QUOTES)?>'
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
                "<?= htmlspecialchars(_('Turn on'), ENT_QUOTES)?>" : function(){
                    window.location = "add_video.php?date_on="+$('#video_on_date').val()+"&accessed=1&id="+$("#video_on_id").val()+"&letter="+getURLParameter('letter')+"&search="+getURLParameter('search')+"&page="+getURLParameter('page');
                },
                "<?= htmlspecialchars(_('Cancel'), ENT_QUOTES)?>" : function(){
                    $(this).dialog("close");
                }
            },
            close : function(){

            }
        });

        $('.goto_form').click(function(){
            $('#form_').get(0).reset();
            document.location.href = 'add_video.php#form';
        })
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
        <a href="index.php"><< <?= _('Back')?></a> | <a href="javascript://" class="goto_form"><?= _('Add')?></a> | <a href="vclub_schedule.php"><?= _('Schedule')?></a> | <a href="vclub_ad.php"><?= _('Advertising')?></a> | <a href="add_moderator_mac.php"><?= _('Moderators MAC addresses')?></a>
        <?
        if (Admin::isAccessAllowed('myvideolog')){
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

    $task = Mysql::getInstance()->from('moderator_tasks')->where(array('ended' => 0, 'media_id' => $id))->get()->first();

    if (!empty($task)){
        return "<a href='msgs.php?task=".$task['id']."'><font color='#CBCB00'>"._('task')."</font></a>&nbsp;&nbsp;\n";
    }else{
        return "<a href='send_to.php?id=".$id."'>"._('send')."</a>&nbsp;&nbsp;\n";
    }
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

if (@$_GET['status'] == 'closed_n_off'){
    $where = 'where video.id=moderator_tasks.media_id ';
}else{
    $where = '';
}

if ($search){
    if ($where){
        $where .= ' and ';
    }else{
        $where .= 'where ';
    }
    $where .= 'name like "%'.$search.'%" or o_name like "%'.$search.'%" or path like "%'.$search.'%" or video.id like "%'.$search.'%"';
}
if (isset($_GET['letter'])) {
	//$where = 'where name like "'.urldecode($letter).'%"';
    if ($where){
        $where .= ' and ';
    }else{
        $where .= 'where ';
    }
    $where .= 'name like "'.$letter.'%"';
}

if (@$_GET['status']){
    if (@$_GET['status'] == 'on'){
        $op_accessed=1;
    }else if (@$_GET['status'] == 'off'){
        $op_accessed=0;
    }else if (@$_GET['status'] == 'closed_n_off'){
        $op_accessed=0;
    }

    if ($where){
        $where .= ' and ';
    }else{
        $where .= 'where ';
    }

    if (isset($op_accessed)){
        $where .= 'accessed='.$op_accessed;
    }else if (@$_GET['status'] == 'red'){
        $where .= 'status=0';
    }else if (@$_GET['status'] == 'sd_double_hd'){
        $where .= 'disable_for_hd_devices=1';
    }

    if (@$_GET['status'] == 'closed_n_off'){
        $where .= ' and video.id=moderator_tasks.media_id and moderator_tasks.media_type=2'
        .' group by(path)';
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

if (@$_GET['status'] == 'closed_n_off'){
    $query = "select video.*, BIT_AND(moderator_tasks.ended) as ended, GROUP_CONCAT(moderator_tasks.rejected) as rejected from video, moderator_tasks $where";
}else{
    $query = "select * from video $where";
}

$total_items = Mysql::getInstance()->query($query)->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

if (@$_GET['status'] == 'closed_n_off'){
    $query = "select video.*, BIT_AND(moderator_tasks.ended) as ended, GROUP_CONCAT(moderator_tasks.rejected) as rejected from video, moderator_tasks $where having ended=1 and rejected not like '%1' $order_by LIMIT $page_offset, $MAX_PAGE_ITEMS";
}else{
    $query = "select video.*, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter from video left join media_claims on video.id=media_claims.media_id and media_claims.media_type='vclub' $where group by video.id $order_by LIMIT $page_offset, $MAX_PAGE_ITEMS";
}

$all_video = Mysql::getInstance()->query($query);

?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by file name of movie name')?></font>
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
    <option value="closed_n_off" <?if (@$_GET['status'] == 'closed_n_off') echo 'selected'?>>closed and off
    <option value="red" <?if (@$_GET['status'] == 'red') echo 'selected'?>>red
    <option value="sd_double_hd" <?if (@$_GET['status'] == 'sd_double_hd') echo 'selected'?>>sd_double_hd
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

while ($arr = $all_video->next()){

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

    if (Admin::isActionAllowed() && !empty($arr['media_id'])){
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to reset claims counter?')."\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".@$arr['video_counter']." / ".@$arr['sound_counter']."</span>";
    if (Admin::isActionAllowed() && !empty($arr['media_id'])){
        echo "</a>";
    }
    echo "</td>\n";

    echo "<td class='list buttons'>";
    if (Admin::isActionAllowed()){
        echo "<a href='?edit=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."&#form'>edit</a>&nbsp;&nbsp;\n";
    }
    if (Admin::isActionAllowed()){
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

    $arr = $item = Video::getById(intval(@$_GET['id']));

    if (!empty($arr)){

        $name = $arr['name'];
        $o_name = $arr['o_name'];
        $censored = $arr['censored'];
        $description = $arr['description'];
        $category_id = $arr['category_id'];
        $director = $arr['director'];
        $actors   = $arr['actors'];
        $time     = $arr['time'];
        $year     = $arr['year'];
        $country  = $arr['country'];
        $path     = $arr['path'];
        $hd       = $arr['hd'];
        $high_quality = $arr['high_quality'];
        $low_quality  = $arr['low_quality'];
        $rtsp_url = $arr['rtsp_url'];
        $protocol = $arr['protocol'];
        $rating_mpaa = $arr['rating_mpaa'];
        $age = $arr['age'];

        $cover_id = (int) Mysql::getInstance()->from('screenshots')->where(array('media_id' => $item['id']))->get()->first('id');

        if ($cover_id){
            $dir_name = ceil($cover_id/100);
            $cover_big = Config::get('screenshots_url').$dir_name;
            $cover_big .= '/'.$cover_id.'.jpg';
        }

        $for_sd_stb = $arr['for_sd_stb'];

        $volume_correction = $arr['volume_correction'];
        $comments = $arr['comments'];
        $readonly = 'readonly';
        if ($censored){
            $checked = 'checked';
        }
        if ($hd){
            $checked_hd = 'checked';
        }
        if ($high_quality){
            $checked_high_quality = 'checked';
        }else{
            $checked_high_quality = '';
        }
        if ($low_quality){
            $checked_low_quality = 'checked';
        }else{
            $checked_low_quality = '';
        }
        if ($for_sd_stb){
            $checked_for_sd_stb = 'checked';
        }
    }
}

function add_video_log($action, $video_id){

    Mysql::getInstance()->insert('video_log', array(
        'action'       => $action,
        'video_id'     => $video_id,
        'moderator_id' => $_SESSION['uid'],
        'actiontime'   => 'NOW()'
    ));
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

    $video = Video::getById($id);

    if (!empty($video['rtsp_url'])){
        return 2;
    }

    return $video['status'];
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

function get_accessed($id){

    $video = Video::getById($id);

    return $video['accessed'];
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
    if (Admin::isActionAllowed()){
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

    $genres = Mysql::getInstance()->from('genre')->orderby('title')->get()->all();

    $str = "var all_genres = [ ";

    foreach($genres as $arr){
        $str .= "{ id : ".$arr['id'].", title : '"._($arr['title'])."'},";
    }

    $str = substr($str, 0, strlen($str)-1);
    $str .= " ]";
    return $str;
}

function get_selected_genres(){

    if (empty($_GET['id'])){
        return '';
    }

    $genre_id = array();

    $id = (int) $_GET['id'];

    $video = Video::getById($id);


    $genre_id[1] = $video['genre_id_1'] ? $video['genre_id_1'] : 0;
    $genre_id[2] = $video['genre_id_2'] ? $video['genre_id_2'] : 0;
    $genre_id[3] = $video['genre_id_3'] ? $video['genre_id_3'] : 0;
    $genre_id[4] = $video['genre_id_4'] ? $video['genre_id_4'] : 0;

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

    return $str;
}

function get_categories(){

    $categories = Mysql::getInstance()->from('media_category')->orderby('num')->get()->all();

    $str = "var all_categories = [ ";

    foreach($categories as $arr){
        $str .= "{ id : ".$arr['id'].", name : '"._($arr['category_name'])."'},";
    }
    $str = substr($str, 0, strlen($str)-1);
    $str .= " ]";
    return $str;
}

function get_selected_cat_genres(){

    $cat_genre_id = array();

    if (!empty($_GET['id'])){

        $video = Video::getById((int) $_GET['id']);

        $cat_genre_id[1] = $video['cat_genre_id_1'] ? $video['cat_genre_id_1'] : 0;
        $cat_genre_id[2] = $video['cat_genre_id_2'] ? $video['cat_genre_id_2'] : 0;
        $cat_genre_id[3] = $video['cat_genre_id_3'] ? $video['cat_genre_id_3'] : 0;
        $cat_genre_id[4] = $video['cat_genre_id_4'] ? $video['cat_genre_id_4'] : 0;

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

if (Admin::isPageActionAllowed()){
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
            obj.innerHTML = '<?= htmlspecialchars(_('please wait'), ENT_QUOTES)?>...';
            doLoad('startmd5sum',{'media_name':media_name, 'storage_name':storage_name})
        }
    }else{
        alert('<?= htmlspecialchars(_('Error: insufficient permissions for this action'), ENT_QUOTES)?>')
    }
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
        table += '<td class="list2" width="70"><?= htmlspecialchars(_('Server'), ENT_QUOTES)?></td>';
        table += '<td class="list2" width="200"><?= htmlspecialchars(_('Folder'), ENT_QUOTES)?></td>';
        table += '<td class="list2" width="60"><?= htmlspecialchars(_('Series'), ENT_QUOTES)?></td>';
        table += '<td class="list2">&nbsp;</td>';
        table += '</tr>';

        for (i=0; i<arr.length; i++){

            var md5btn_txt = '';

            if (arr[i]['files'][0]['status'] == 'done'){
                if (arr[i]['files'][0]['md5'] != ''){
                    md5btn_txt = '<?= htmlspecialchars(_('check'), ENT_QUOTES)?>'
                }else{
                    md5btn_txt = '<?= htmlspecialchars(_('count md5 sum'), ENT_QUOTES)?>'
                }
            }else{
                md5btn_txt = '<?= htmlspecialchars(_('counting'), ENT_QUOTES)?>...'
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
                    md5sum = '<?= htmlspecialchars(_('counting'), ENT_QUOTES)?>...'
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
        name_itm.innerHTML = '<?= htmlspecialchars(_('Not available'), ENT_QUOTES)?>'
    }else{
        name_itm.style.color = 'green'
        name_itm.innerHTML = '<?= htmlspecialchars(_('Available'), ENT_QUOTES)?>'
    }
}

function check_org_name(name){
    doLoad('chk_org_name', name)
}

function hint(){
    alert(document.getElementById('f_file').value)
}

function save(){
    var form_ = document.getElementById('form_');

    var name = document.getElementById('name').value;

    var id = document.getElementById('id').value;
    var description = document.getElementById('description').value;

    var action = 'add_video.php?name='+name+'&id='+id+'&letter=<? echo @$_GET['letter'] ?>&search=<? echo @$_GET['search']?>&page=<? echo @$_GET['page'] ?>';

    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }

    form_.setAttribute('action', action);
    form_.setAttribute('method', 'POST');
    form_.submit();
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

function check_kinopoisk_info_by_name(orig_name){
    check_kinopoisk_info(orig_name)
}

function check_kinopoisk_info_by_id(id){
    check_kinopoisk_info(null, id);
}

function check_kinopoisk_info(orig_name, kinopoisk_id){

    $('.kinopoisk_url').attr('href', '');
    $('.kinopoisk_url').html('');

    if (orig_name){
        var url = 'get.php?get=kinopoisk_info';
        var data = {"oname" : orig_name}
    }else if (kinopoisk_id){
        url = 'get.php?get=kinopoisk_info_by_id';
        data = {"kinopoisk_id" : kinopoisk_id}
    }else{
        return;
    }

    $.get(url, data, function(response){
        $('.info_loader').hide();
        $('.get_info').show();
        response = JSON.parse(response);

        var result = response.result;

        if (result){

            if (result.hasOwnProperty('cover_big')){
                $('.cover_block').html('<img src="get_img.php?url='+result['cover_big']+'" width="240" height="320" style="float:left"/>' +
                    '<div style="float:left"><a href="#" class="del_cover">x</a></div>');
                $('.screenshot').hide();
                $('.cover_id').val('');
            }

            if (result.hasOwnProperty('age')){
                $('.age option[value="'+result.age+'"]').attr('selected', 'selected');
            }

            if (result.hasOwnProperty('rating_mpaa')){
                $('.rating_mpaa option[value="'+result.rating_mpaa+'"]').attr('selected', 'selected');
            }

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

    $(".del_cover").live('click', function(){
        $('.cover_block').html('');
        $('.cover_big').val('');
        $('.cover_id').val('');
        $('.screenshot').show();
        return false;
    });

    $(".get_info").click(function(){
        $('.get_info').hide();
        $('.info_loader').show();

        if ($(".o_name").val() || $(".name").val()){
            check_kinopoisk_info($(".o_name").val() || $(".name").val());
        }else if ($(".kinopoisk_id").val()){
            check_kinopoisk_info_by_id($(".kinopoisk_id").val())
        }
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

    $('.name').blur(function(){
        if (!$(this).val() && !$('.o_name').val()){
            $('.kinopoisk_id').removeAttr('readonly');
        }else{
            $('.kinopoisk_id').attr('readonly', 'readonly');
        }
    });

    $('.o_name').blur(function(){
        if (!$(this).val() && !$('.name').val()){
            $('.kinopoisk_id').removeAttr('readonly');
        }else{
            $('.kinopoisk_id').attr('readonly', 'readonly');
        }
    });

    if (!$('.name').val() && !$('.o_name').val()){
        $('.kinopoisk_id').removeAttr('readonly');
    }else{
        $('.kinopoisk_id').attr('readonly', 'readonly');
    }
});

</script>
<br>
<a name="form"></a>
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
            <!--<input type="hidden" name="kinopoisk_id" class="kinopoisk_id" value="<?/* echo @$item['kinopoisk_id'] */?>">-->
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
                <?= _('Kinopoisk ID')?>:
            </td>
            <td>
                <input type="text" name="kinopoisk_id" class="kinopoisk_id" value="<? echo @$item['kinopoisk_id'] ?>" title="<?= _('For unlock please delete Name and Original name fields')?>">
                <a href="javascript://" title="<?= _('For unlock please delete Name and Original name fields')?>">?</a>
                <!--<a href="javascript://" class="edit_kinopoisk_id">edit</a>-->
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

        <tr style="">
            <td align="right" valign="top">
                <?= _('Age rating')?>:
            </td>
            <td>
                <select name="age" class="age">
                    <option value="" >---</option>
                    <option value="0+" <?if (@$age == '0+'){ echo 'selected';}?>>0+</option>
                    <option value="6+" <?if (@$age == '6+'){ echo 'selected';}?>>6+</option>
                    <option value="12+" <?if (@$age == '12+'){ echo 'selected';}?>>12+</option>
                    <option value="14+" <?if (@$age == '14+'){ echo 'selected';}?>>14+</option>
                    <option value="16+" <?if (@$age == '16+'){ echo 'selected';}?>>16+</option>
                    <option value="18+" <?if (@$age == '18+'){ echo 'selected';}?>>18+</option>
                    <option value="21+" <?if (@$age == '21+'){ echo 'selected';}?>>21+</option>
                </select>
            </td>
        </tr>

        <tr style="">
            <td align="right" valign="top">
                <?= _('Rating MPAA')?>:
            </td>
            <td>
                <select name="rating_mpaa" class="rating_mpaa">
                    <option value="">---</option>
                    <option value="G" <?if (@$rating_mpaa == 'G'){ echo 'selected';}?>>G</option>
                    <option value="PG"  <?if (@$rating_mpaa == 'PG'){ echo 'selected';}?>>PG</option>
                    <option value="PG-13"  <?if (@$rating_mpaa == 'PG-13'){ echo 'selected';}?>>PG-13</option>
                    <option value="R"  <?if (@$rating_mpaa == 'R'){ echo 'selected';}?>>R</option>
                    <option value="NC-17"  <?if (@$rating_mpaa == 'NC-17'){ echo 'selected';}?>>NC-17</option>
                </select>
            </td>
        </tr>

        <tr>
           <td align="right" valign="top">
           <?= _('Protocol')?>:
           </td>
           <td>
             <select name="protocol" id="protocol" onchange="check_protocol()">
                 <option value="http" <?if (@$protocol == 'http'){ echo 'selected';}?>>HTTP</option>
                 <option value="custom" <?if (@$protocol == 'custom'){ echo 'selected';}?>>Custom URL</option>
                 <option value="nfs" <?if (@$protocol == 'nfs'){ echo 'selected';}?>>NFS</option>
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

        <? if (Config::getSafe('enable_video_high_quality_option', false)){ ?>
        <tr>
            <td align="right" valign="top">
                <?= _('High quality')?>:
            </td>
            <td>
                <input name="high_quality" id="high_quality" type="checkbox" <? echo isset($checked_high_quality) ? $checked_high_quality : 'checked' ?> >
            </td>
        </tr>
        <?}?>

        <? if (Config::getSafe('enable_video_low_quality_option', false)){ ?>
            <tr>
                <td align="right" valign="top">
                    <?= _('Low quality')?>:
                </td>
                <td>
                    <input name="low_quality" id="low_quality" type="checkbox" <? echo isset($checked_low_quality) ? $checked_low_quality : 'checked' ?> >
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
           <?= _('Country')?>:
           </td>
           <td>
            <input name="country" class="country" type="text" size="50" value="<? echo @$country ?>">
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
            <input name="director" type="text" class="director" size="50" value="<? echo @$director ?>">
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            <?= _('Actors')?>:
           </td>
           <td>
            <textarea id="actors" name="actors" class="actors" rows="6" cols="39"><? echo @$actors ?></textarea>
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            <?= _('Description')?>:
           </td>
           <td>
            <textarea id="description" name="description" class="description" rows="10" cols="39"><? echo @$description ?></textarea>
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Volume correction')?> (-20...20):
           </td>
           <td>
            <input id="service_id" name="volume_correction" size="4" maxlength="3" type="text" value="<? echo @$volume_correction ?>">
           </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?= _('Comments')?>:
            </td>
            <td>
                <textarea id="comments" name="comments" cols="39" rows="5"><? echo @$comments ?></textarea>
            </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            <?= _('Cover')?>:
           </td>
           <td>
            <input name="screenshot" class="screenshot" size="27" type="file" style="<?= !empty($cover_big) ? 'display:none' : '' ?>">
            <input type="hidden" name="cover_big" class="cover_big">
            <input type="hidden" name="cover_id" class="cover_id" value="<?= @$cover_id?>">
            <div class="cover_block"><?
                if (!empty($cover_big)){
                    echo '<img src="'.$cover_big.'" width="240" height="320" style="float:left"/><div style="float:left"><a href="#" class="del_cover">x</a></div>';
                }
            ?></div>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
           <input type="button" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>" onclick="save()">&nbsp;<input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='add_video.php'">
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
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>