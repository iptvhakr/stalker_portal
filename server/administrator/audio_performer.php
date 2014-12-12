<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del'] && !empty($_GET['id'])){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('audio_performers', array('id' => intval($_GET['id'])));
    header("Location: audio_performer.php");
    exit;
}

if (!empty($_POST)){

    if (empty($_POST['name'])){

        $error = _('Error: all fields are required').' <a href="#form">#</a>';

    }elseif(isset($_POST['save'])){
        Admin::checkAccess(AdminAccess::ACCESS_CREATE);

        $performer_id = Mysql::getInstance()->insert('audio_performers', array(
            'name'     => $_POST['name'],
            'modified' => 'NOW()'
        ))->insert_id();

        header("Location: audio_performer.php");
        exit;

    }elseif(isset($_POST['update'])){
        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $performer_id = intval($_GET['id']);

        Mysql::getInstance()->update('audio_performers', array(
            'name'     => $_POST['name'],
            'modified' => 'NOW()'
        ), array('id' => $performer_id));

        header("Location: audio_performer.php");
        exit;
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
        <?= _('AUDIO PERFORMERS')?>
    </title>
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

$MAX_PAGE_ITEMS = 30;
$page = isset($_GET['page']) ? $_GET['page'] : 0;

$page_offset = $page * $MAX_PAGE_ITEMS;

$items = Mysql::getInstance()->from('audio_performers');

$items_count = clone $items;
$total_items = $items_count->nolimit()->count()->get()->counter();

$total_pages = ceil($total_items/$MAX_PAGE_ITEMS);

$items->limit($MAX_PAGE_ITEMS, $page_offset);

$items = $items->get();

if (isset($_GET['id'])){
    $current_performer = Mysql::getInstance()->from('audio_performers')->where(array('id' => (int) $_GET['id']))->get()->first();
}
?>
<body>

<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('AUDIO PERFORMERS')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td>
            <a href="audio_album.php"><< <?= _('Back')?></a>
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
                            <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by name')?></font>
                        </form>
                    <td>
                </tr>
            </table>

            <center>
                <table class='list' cellpadding='3' cellspacing='0'>
                    <tr>
                        <td class='list'><b><?= _('Name')?></b></td>
                        <td class='list'>&nbsp;</td>
                    </tr>
                    <tr>
                        <?

                        while ($item = $items->next()){
                            echo "<tr>";
                                echo "<td class='list'>".$item['name']."</td>\n";
                                echo "<td class='list' nowrap><a href='?edit=1&id=".$item['id']."#form'>edit</a>&nbsp;&nbsp;";
                                echo "<a href='#' onclick='if(confirm(\"".htmlspecialchars(_('Do you really want to delete this record?'), ENT_QUOTES)."\")){document.location=\"audio_performer.php?del=1&id=".$item['id']."&search=".@$_GET['search']."\"}'>del</a>&nbsp\n";
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
                <form id="form_" enctype="multipart/form-data" method="POST">
                <table align="center">
                <tr>
                    <td align="right">
                        <?= _('Performer')?>:
                    </td>
                    <td>
                        <input name="name" type="text" value="<?= !empty($current_performer) ? $current_performer['name'] : '' ?>">
                        <input type="hidden" value="<? echo @$_GET['id'] ?>">
                        <input type="hidden" name="<?= @$_GET['id'] ? "update" : "save" ?>" value="1">
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input type="submit" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>">&nbsp;
                        <input type="button" value="<?= htmlspecialchars(_('New'), ENT_QUOTES)?>" onclick="document.location='audio_performer.php'">
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