<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del'] && !empty($_GET['id'])){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('audio_compositions', array('album_id' => intval($_GET['id'])));
    Mysql::getInstance()->delete('audio_genre', array('album_id' => intval($_GET['id'])));
    Mysql::getInstance()->delete('audio_albums', array('id' => intval($_GET['id'])));
    header("Location: audio_album.php");
    exit;
}

if (isset($_GET['status']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    Mysql::getInstance()->update('audio_albums',
        array(
             'status' => intval(@$_GET['status'])
        ),
        array('id' => intval(@$_GET['id']))
    );

    header("Location: audio_album.php");
    exit;
}

if (!empty($_POST)){

    if (empty($_POST['performer_id']) || empty($_POST['name']) || empty($_POST['genre_ids'])){

        $error = _('Error: all fields are required').' <a href="#form">#</a>';

    }elseif(isset($_POST['save'])){
        Admin::checkAccess(AdminAccess::ACCESS_CREATE);

        $album_id = Mysql::getInstance()->insert('audio_albums', array(
            'performer_id' => $_POST['performer_id'],
            'name'         => $_POST['name'],
            'year_id'      => $_POST['year_id'],
            'country_id'   => $_POST['country_id'],
            'added'        => 'NOW()'
        ))->insert_id();

        $_POST['genre_ids'] = array_unique(array_values(array_filter($_POST['genre_ids'], function($genre_id){
            return $genre_id != 0;
        })));

        $genres_data = array();

        foreach ($_POST['genre_ids'] as $genre_id){
            $genres_data[] = array(
                'album_id' => $album_id,
                'genre_id' => $genre_id
            );
        }

        if (!empty($genres_data)){
            Mysql::getInstance()->insert('audio_genre', $genres_data);
        }

        if (!empty($_FILES['cover']['name'])){

            if ($cover = handle_upload_logo($_FILES['cover'], $album_id)){
                Mysql::getInstance()->update('audio_albums', array('cover' => $cover), array('id' => $album_id));
            }else{
                $error = _('Error: could not save cover').' <a href="#form">#</a>';
            }
        }

        if (empty($error)){
            header("Location: audio_album.php");
            exit;
        }

    }elseif(isset($_POST['update'])){
        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $album_id = intval($_GET['id']);

        Mysql::getInstance()->update('audio_albums', array(
            'performer_id' => $_POST['performer_id'],
            'name'         => $_POST['name'],
            'year_id'      => $_POST['year_id'],
            'country_id'   => $_POST['country_id'],
            'added'        => 'NOW()'
        ), array('id' => $album_id));

        $existed_genres = Mysql::getInstance()
            ->from('audio_genre')
            ->where(array('album_id' => $album_id))
            ->get()->all('genre_id');

        $_POST['genre_ids'] = array_unique(array_values(array_filter($_POST['genre_ids'], function($genre_id){
            return $genre_id != 0;
        })));

        $need_to_add_genres    = array_diff($_POST['genre_ids'], $existed_genres);
        $need_to_delete_genres = array_diff($existed_genres, $_POST['genre_ids']);

        if (!empty($need_to_add_genres)){
            $genres_data = array();

            foreach ($need_to_add_genres as $genre_id){
                $genres_data[] = array(
                    'album_id' => $album_id,
                    'genre_id' => $genre_id
                );
            }

            Mysql::getInstance()->insert('audio_genre', $genres_data);
        }

        if (!empty($need_to_delete_genres)){

            foreach ($need_to_delete_genres as $genre_id){
                Mysql::getInstance()->delete('audio_genre', array(
                    'album_id' => $album_id,
                    'genre_id' => $genre_id
                ));
            }
        }

        if ($_POST['remove_cover'] && empty($_FILES['cover']['name'])){
            Mysql::getInstance()->update('audio_albums', array('cover' => ''), array('id' => $album_id));
        } elseif (!empty($_FILES['cover']['name'])){

            if ($cover = handle_upload_logo($_FILES['cover'], $album_id)){
                Mysql::getInstance()->update('audio_albums', array('cover' => $cover), array('id' => $album_id));
            }else{
                $error = _('Error: could not save cover').' <a href="#form">#</a>';
            }

            if (empty($error)){
                header("Location: audio_album.php?edit=1&id=".intval(@$_GET['id'])."#form");
                exit;
            }
        }
    }
}

function handle_upload_logo($file, $album_id){

    if (empty($file)){
        return true;
    }

    $images = array('image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/png' => 'png');

    if (!array_key_exists($file['type'], $images)){
        return false;
    }

    $ext = $images[$file['type']];

    $path = realpath(PROJECT_PATH."/../misc/audio_covers/");

    if (!$path){
        return false;
    }

    $filename = $album_id.".".$ext;

    umask(0);

    $subfolder = ceil($album_id/100);

    $path = $path.'/'.$subfolder;

    if (!is_dir($path)){
        mkdir($path, 0755);
    }

    $fullpath = $path."/".$filename;

    try{
        $icon = new Imagick($file['tmp_name']);
    }catch(ImagickException $e){
        //$e->getMessage();
        return false;
    }

    if (!$icon->resizeImage(240, 240, Imagick::FILTER_LANCZOS, 1)){
        return false;
    }

    if (!$icon->writeImage($fullpath)){
        return false;
    }

    $icon->destroy();

    chmod($fullpath, 0644);

    unlink($file['tmp_name']);

    return $filename;
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


    </style>
    <title>
        <?= _('AUDIO ALBUMS')?>
    </title>
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script>

        $(function(){
            $(".del_cover").live('click', function(){
                $('.cover_block').html('');
                $('.remove_cover').val('1');
                return false;
            });

            $(".genre").change(function(){
                var genre_id = $(this).find('option:selected').val();
                var idx = parseInt($(this).attr('data-number'), 10);

                if (idx < 4){
                    if (genre_id == 0){
                        $('.genre-'+(idx+1)).hide();
                    }else if (genre_id > 0){
                        $('.genre-'+(idx+1)).show();
                    }
                }
            });

            $('.goto_form').click(function(){
                $('html, body').animate({
                    scrollTop: $("#form").offset().top
                }, 2000);
            })

        })
    </script>
</head>
<?

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;

    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&status='.@$_GET['status'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

function get_album_genres($album_id){

    $genres = Mysql::getInstance()
        ->select('audio_genres.name')
        ->from('audio_genre')
        ->where(array('album_id' => $album_id))
        ->join('audio_genres', 'audio_genre.genre_id', 'audio_genres.id', 'LEFT')
        ->orderby('audio_genres.name')
        ->get()
        ->all('name');

    return array_map(function($genre){
        return _($genre);
    }, $genres);
}

function count_album_tracks($album_id){
    return Mysql::getInstance()->from('audio_compositions')
        ->where(array('album_id' => $album_id))
        ->count()
        ->get()->counter();
}

function get_album_languages($album_id){

    $languages = Mysql::getInstance()
        ->select('audio_languages.name')
        ->from('audio_compositions')
        ->where(array('album_id' => $album_id))
        ->join('audio_languages', 'audio_compositions.language_id', 'audio_languages.id', 'LEFT')
        ->orderby('audio_languages.name')
        ->groupby('audio_languages.name')
        ->get()
        ->all('name');

    return $languages;
}

$MAX_PAGE_ITEMS = 30;
$page = isset($_GET['page']) ? $_GET['page'] : 0;

$total_items = Mysql::getInstance()->from('audio_albums')->count()->get()->counter();

$page_offset = $page * $MAX_PAGE_ITEMS;
$total_pages = ceil($total_items/$MAX_PAGE_ITEMS);

$albums = Mysql::getInstance()
    ->select('audio_albums.*,
        audio_performers.name as performer_name,
        audio_years.name as album_year,
        countries.name as album_country
    ')
    ->from('audio_albums')
    ->join('audio_performers', 'audio_albums.performer_id', 'audio_performers.id', 'LEFT')
    ->join('audio_years', 'audio_albums.year_id', 'audio_years.id', 'LEFT')
    ->join('countries', 'audio_albums.country_id', 'countries.id', 'LEFT')
    ->limit($MAX_PAGE_ITEMS, $page_offset)->get();

if (isset($_GET['id'])){
    $current_album = Mysql::getInstance()->from('audio_albums')->where(array('id' => (int) $_GET['id']))->get()->first();
    $current_album_genres = Mysql::getInstance()->from('audio_genre')->where(array('album_id' => (int) $_GET['id']))->get()->all('genre_id');
}

$all_performers = Mysql::getInstance()->from('audio_performers')->orderby('name')->get()->all();
$all_genres = Mysql::getInstance()->from('audio_genres')->orderby('name')->get()->all();
$all_years = Mysql::getInstance()->from('audio_years')->orderby('name')->get()->all();
$all_countries = Mysql::getInstance()->from('countries')->orderby('name')->get()->all();

?>
<body>

<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('AUDIO ALBUMS')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td>
            <a href="index.php"><< <?= _('Back')?></a>
            | <a href="javascript://" class="goto_form"><?= _('Add')?></a>
            | <a href="audio_performer.php"><?= _('Performers')?></a>
            | <a href="audio_genre.php"><?= _('Genres')?></a>
            | <a href="audio_year.php"><?= _('Years')?></a>
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
            <table border="0" align="center" width="620">
                <tr>
                    <td>
                        <form action="" method="GET">
                            <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by album name or performer')?></font>
                        </form>
                    <td>
                </tr>
            </table>

            <center>
                <table class='list' cellpadding='3' cellspacing='0'>
                    <tr>
                        <td class='list'><b><?= _('Title')?></b></td>
                        <td class='list'><b><?= _('Tracks')?></b></td>
                        <td class='list'><b><?= _('Genre')?></b></td>
                        <td class='list'><b><?= _('Year')?></b></td>
                        <td class='list'><b><?= _('Country')?></b></td>
                        <td class='list'><b><?= _('Language')?></b></td>
                        <td class='list'>&nbsp;</td>
                    </tr>
                    <tr>
                        <?

                        while ($album = $albums->next()){
                            echo "<tr>";
                            echo "<td class='list'><a href='audio_track.php?album_id=".$album['id']."'>".$album['performer_name']." - ".$album['name']."</a></td>\n";
                            echo "<td class='list'>".count_album_tracks($album['id'])."</td>\n";
                            echo "<td class='list'>".implode(', ', get_album_genres($album['id']))."</td>\n";
                            echo "<td class='list'>"._($album['album_year'])."</td>\n";
                            echo "<td class='list'>".$album['album_country']."</td>\n";
                            echo "<td class='list'>".implode(', ', get_album_languages($album['id']))."</td>\n";

                            echo "<td class='list' nowrap><a href='?edit=1&id=".$album['id']."#form'>edit</a>&nbsp;&nbsp;";
                            echo "<a href='#' onclick='if(confirm(\"".htmlspecialchars(_('Do you really want to delete this record?'), ENT_QUOTES)."\")){document.location=\"audio_album.php?del=1&id=".$album['id']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
                            if ($album['status']){
                                echo "<a href='?status=0&id=".$album['id']."'><font color='Green'>on</font></a>";
                            }else{
                                echo "<a href='?status=1&id=".$album['id']."'><font color='Red'>off</font></a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }

                        ?>
                    </tr>
                </table>
                <table width='600' align='center' border=0>
                    <tr>
                        <td width='100%' align='center'>
                            <?= page_bar()?>
                        </td>
                    </tr>
                </table>

                <a name="form"></a>
                <table align="center" class='list'>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                <td>
                <form id="form" enctype="multipart/form-data" method="POST">
                <table align="center">
                <tr>
                    <td align="right">
                        <?= _('Performer')?>:
                    </td>
                    <td>
                        <select name="performer_id">
                            <option value="0">---</option>
                            <?
                            foreach ($all_performers as $performer){
                                if (!empty($current_album) && $current_album['performer_id'] == $performer['id']){
                                    $selected = 'selected';
                                }else{
                                    $selected = '';
                                }
                                echo '<option value="'.$performer['id'].'" '.$selected.'>'.$performer['name'].'</option>';
                            }
                            ?>
                        </select>
                        <a href="audio_performer.php"><?= _('add')?></a>
                        <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
                        <input type="hidden" name="<?= @$_GET['id'] ? "update" : "save" ?>" value="1">
                    </td>
                </tr>

                <tr>
                    <td align="right" valign="top">
                        <?= _('Album')?>:
                    </td>
                    <td>
                        <input name="name" type="text" size="40" value="<? echo @$current_album['name']?>">
                    </td>
                </tr>

                <tr>
                    <td align="right" valign="top">
                        <?= _('Genres')?>:
                    </td>
                    <td>

                        <? for ($i = 0; $i <= 3; $i++){
                            ?>
                            <div class="genre-<?= $i+1?>" <?= (empty($current_album_genres[$i]) && $i>0 && empty($current_album_genres[$i-1]) && !empty($current_album) || empty($current_album) && $i>0) ? 'style="display:none"' : '' ?>>
                                <select class="genre" data-number="<?= $i+1 ?>" name="genre_ids[]">
                                    <option value="0">---</option>
                                    <?
                                    foreach ($all_genres as $genre){
                                        if (!empty($current_album_genres[$i]) && $current_album_genres[$i] == $genre['id']){
                                            $selected = 'selected';
                                        }else{
                                            $selected = '';
                                        }
                                        echo '<option value="'.$genre['id'].'" '.$selected.'>'._($genre['name']).'</option>';
                                    }
                                    ?>
                                </select>
                                <? if ($i == 0){ ?>
                                    <a href="audio_genre.php"><?= _('add')?></a>
                                <? } ?>
                            </div>
                        <? } ?>
                    </td>
                </tr>

                <tr style="">
                    <td align="right" valign="top">
                        <?= _('Year')?>:
                    </td>
                    <td>
                        <select name="year_id">
                            <option value="0">---</option>
                            <?
                            foreach ($all_years as $year){
                                if (!empty($current_album) && $current_album['year_id'] == $year['id']){
                                    $selected = 'selected';
                                }else{
                                    $selected = '';
                                }
                                echo '<option value="'.$year['id'].'" '.$selected.'>'.$year['name'].'</option>';
                            }
                            ?>
                        </select>
                        <a href="audio_year.php"><?= _('add')?></a>
                    </td>
                </tr>

                <tr style="">
                    <td align="right" valign="top">
                        <?= _('Country')?>:
                    </td>
                    <td>
                        <select name="country_id">
                            <option value="0">---</option>
                            <?
                            foreach ($all_countries as $country){
                                if (!empty($current_album) && $current_album['country_id'] == $country['id']){
                                    $selected = 'selected';
                                }else{
                                    $selected = '';
                                }
                                echo '<option value="'.$country['id'].'" '.$selected.'>'.$country['name'].'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td align="right" valign="top">
                        <?= _('Cover')?>:
                    </td>
                    <td>
                        <? if (!empty($current_album['cover'])){ ?>
                            <div class="cover_block">
                                <img src="<?= Config::get('portal_url').'misc/audio_covers/'.ceil($current_album['id']/100).'/'.$current_album['cover']?>" style="float: left"/>
                                <div style="float:left"><a href="#" class="del_cover">x</a></div>
                            </div>
                        <? } ?>
                        <input name="remove_cover" class="remove_cover" type="hidden" value="0">
                        <input name="cover" class="cover" size="27" type="file">
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input type="submit" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>">&nbsp;
                        <input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='audio_album.php'">
                    </td>
                </tr>
                </table>
                </form>
                </td>
                </tr>
                </table>
            </center>
        </td>
    </tr>
</table>

</body>
</html>