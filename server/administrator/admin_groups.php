<?php

ob_start();
session_start();
include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$groups = Mysql::getInstance()
    ->from('admin_groups')
    ->get()
    ->all();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!empty($_POST['add']) && !empty($_POST['name'])){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    $data = array(
        'name' => $_POST['name'],
    );

    $group_id = Mysql::getInstance()->insert('admin_groups', $data)->insert_id();

    $access_map = AdminAccess::convertPostParamsToAccessMap($_POST);

    if (!empty($access_map)){

        $access_map = array_map(function($item) use ($group_id){

            $item['gid'] = $group_id;

            return $item;
        }, $access_map);

        if (!empty($access_map))

        Mysql::getInstance()->insert('acl', $access_map);
    }

    header("Location: admin_groups.php");
    exit;
}

if (!empty($id)){

    if (!empty($_POST['edit_action']) && !empty($_POST['name'])){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $data = array(
            'name' => $_POST['name'],
        );

        Mysql::getInstance()->update('admin_groups', $data, array('id' => $id));

        $access_map = AdminAccess::convertPostParamsToAccessMap($_POST);

        if (!empty($access_map)){

            $access_map = array_map(function($item) use ($id){

                $item['gid'] = $id;

                return $item;
            }, $access_map);

            Mysql::getInstance()->delete('acl', array('gid' => $id));
            Mysql::getInstance()->insert('acl', $access_map);
        }

        header("Location: admin_groups.php");
        exit;

    }elseif(!empty($_GET['del'])){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()->delete('admin_groups', array('id' => $id));

        header("Location: admin_groups.php");
        exit;
    }
}

if (isset($_GET['edit']) && !empty($id)){
    $action_name  = 'edit_action';
    $action_value = _('Save');
    $edit_group = Mysql::getInstance()->from('admin_groups')->where(array('id' => $id))->get()->first();

    $access = Mysql::getInstance()->from('acl')->where(array('gid' => $id))->get()->all();

    $edit_group['access'] = array();

    foreach ($access as $row){
        $edit_group['access'][$row['page']] = $row;
    }
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= _('Administrators groups')?></title>
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
        .list, .list td, .form{
            border-width: 1px;
            border-style: solid;
            border-color: #E5E5E5;
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

        .access tr{
            background-color: #fff;
        }

        .access tr:hover{
            background-color: #EFF5FB;
        }

        .access td{
            text-align: center;
        }
    </style>

    <script type="text/javascript" src="../adm/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">


        $(function(){
            $('.select_all').click(function(){

                var th_idx = $(this).parent().index();

                var col_checked = $(this).attr('checked');

                var table = $('.access');

                table.find('td').filter(function(){

                    if ($(this).index() === th_idx){
                        if (col_checked){
                            $(this).find('input[type="checkbox"]').attr('checked', col_checked);
                        }else{
                            $(this).find('input[type="checkbox"]').removeAttr('checked');
                        }
                    }

                });

            });
        });


    </script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="800">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;&nbsp;<?= _('Administrators groups')?> &nbsp;&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="administrators.php"><< <?= _('Back')?></a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <font color="Red">
                <strong>
                    <? echo @$error?>
                </strong>
            </font>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td align="center">
            <table class='list' cellpadding='3' cellspacing='0'>
                <tr>
                    <td><?= _('Name')?></td>
                    <td>&nbsp;</td>
                </tr>
                <?
                foreach ($groups as $group){
                    echo '<td>'.$group['name'].'</td>';
                    echo '<td>';
                    echo '<a href="?edit=1&id='.$group['id'].'">edit</a>&nbsp;';
                    echo '<a href="?del=1&id='.$group['id'].'" onclick="if(confirm(\''._('Do you really want to delete this record?').'\')){return true}else{return false}">del</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center">
            <br>
            <br>
            <form method="POST">
                <table class="form" width="600">
                    <tr>
                        <td width="100"><?= _('Name')?></td>
                        <td><input type="text" name="name" size="30" value="<?= @$edit_group['name']?>"/></td>
                    </tr>

                    <!-- todo: access table-->
                    <tr>
                    <td colspan="2">
                        <table width="100%" class="access">
                        <tr>
                        <th></th>
                        <th><input type="checkbox" class="select_all" /><br>view</th>
                        <th><input type="checkbox" class="select_all" /><br>create</th>
                        <th><input type="checkbox" class="select_all" /><br>edit</th>
                        <th><input type="checkbox" class="select_all" /><br>delete</th>
                        <th><input type="checkbox" class="select_all" /><br>action</th>
                        <th><input type="checkbox" class="select_all" /><br>context action</th>
                        </tr>
                    <?

                    $pages = scandir(PROJECT_PATH.'/administrator/');

                    $exclude_pages = array(
                        'ad.php',
                        'common.php',
                        'get.php',
                        'get_img.php',
                        'index.php',
                        'administrators.php',
                        'load.php',
                        'login.php',
                        'logout.php',
                    );

                    $pages = array_filter($pages, function($page) use ($exclude_pages){
                        return strpos($page, '.php') && array_search($page, $exclude_pages) === false;
                    });

                    foreach ($pages as $page){

                        $page_name = str_replace('.php', '', $page);

                        echo '<tr>';

                        echo '<td style="text-align: left">';
                        echo $page;
                        echo '<input type="hidden" name="page" value="'.$page_name.'">';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="view['.$page_name.']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['view']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="create['.$page_name.']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['create']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="edit['.$page_name.']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['edit']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="delete['.$page_name.']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['delete']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="page_action['.$page_name.']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['page_action']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '<td>';
                        echo '<input name="context_action['.$page_name  .']" value="1" type="checkbox" '.(!empty($edit_group['access'][$page_name]['context_action']) ? 'checked' : '').'/>';
                        echo '</td>';

                        echo '</tr>';

                    }

                    ?>
                        </table>
                    </td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"/></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>