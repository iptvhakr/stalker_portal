<?php

ob_start();
session_start();
include "./common.php";

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$administrators = Mysql::getInstance()
    ->select('administrators.*, admin_groups.name as group_name')
    ->from('administrators')
    ->join('admin_groups', 'admin_groups.id', 'administrators.gid', 'LEFT')
    ->get()
    ->all();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!empty($_POST['add']) && !empty($_POST['login'])){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    $data = array(
        'login' => $_POST['login'],
        'gid'   => isset($_POST['gid']) ? (int) $_POST['gid'] : 0
    );

    if (empty($_POST['pass'])){

        $error = _('Password can not be blank');

    }elseif ($_POST['pass'] != $_POST['pass_repeat']){

        $error = _('Passwords do not match');

    }else{

        $data['pass'] = md5($_POST['pass']);

        $admin_id = Mysql::getInstance()->insert('administrators', $data)->insert_id();

        header("Location: administrators.php");
        exit;
    }
}

if (!empty($id)){
    if (!empty($_POST['edit']) && !empty($_POST['login'])){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $data = array(
            'login' => $_POST['login'],
            'gid'   => isset($_POST['gid']) ? (int) $_POST['gid'] : 0
        );

        if (!empty($_POST['pass']) && $_POST['pass'] != $_POST['pass_repeat']){

            $error = _('Passwords do not match');

        }else{

            if (!empty($_POST['pass'])){
                $data['pass'] = md5($_POST['pass']);
            }

            Mysql::getInstance()->update('administrators', $data, array('id' => $id));

            header("Location: administrators.php");
            exit;
        }
    }elseif(!empty($_GET['del'])){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()->delete('administrators', array('id' => $id));

        header("Location: administrators.php");
        exit;
    }
}

if (isset($_GET['edit']) && !empty($id)){
    $action_name  = 'edit';
    $action_value = _('Save');
    $edit_admin = Mysql::getInstance()->from('administrators')->where(array('id' => $id))->get()->first();
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= _('Administrators')?></title>
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
    </style>

    <script type="text/javascript">


    </script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;&nbsp;<?= _('Administrators')?> &nbsp;&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="index.php"><< <?= _('Back')?></a> | <a href="admin_groups.php"><?= _('Administrators groups')?></a>
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
                    <td><?= _('Login')?></td>
                    <td><?= _('Group')?></td>
                    <td>&nbsp;</td>
                </tr>
                <?
                foreach ($administrators as $admin){
                    echo '<td>'.$admin['login'].'</td>';
                    echo '<td>'.$admin['group_name'].'</td>';
                    echo '<td>';
                    echo '<a href="?edit=1&id='.$admin['id'].'">edit</a>&nbsp;';
                    if ($admin['login'] != 'admin'){
                        echo '<a href="?del=1&id='.$admin['id'].'" onclick="if(confirm(\''._('Do you really want to delete this record?').'\')){return true}else{return false}">del</a>';
                    }
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
                <table class="form">
                    <tr>
                        <td width="100"><?= _('Login')?></td>
                        <td><input type="text" name="login" value="<?= @$edit_admin['login']?>" <?= @$edit_admin['login'] == 'admin' ? 'readonly="readonly" style="color:#777"' : '' ?> autocomplete="off" /></td>
                    </tr>
                    <tr>
                        <td><?= _('Password')?></td>
                        <td><input type="password" name="pass" /></td>
                    </tr>
                    <tr>
                        <td><?= _('Password repeat')?></td>
                        <td><input type="password" name="pass_repeat" /></td>
                    </tr>

                    <? if (@$edit_admin['login'] != 'admin'){ ?>

                    <tr>
                        <td><?= _('Group')?></td>
                        <td>
                            <select name="gid">
                            <option value="0">---</option>
                            <?
                            $groups = Mysql::getInstance()->from('admin_groups')->orderby('name')->get()->all();

                            foreach ($groups as $group){

                                echo '<option value="'.$group['id'].'" ';
                                echo (!empty($edit_admin['gid']) && $group['id']==$edit_admin['gid'] ? 'selected' : '');
                                echo '>'.$group['name'].'</option>';

                            }
                            ?>
                            </select>
                        </td>
                    </tr>
                    <?}?>
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