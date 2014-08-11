<?php
ob_start();
session_start();
include "./common.php";

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    //$_POST[$key] = trim($value);
}

$error = '';
$action_name = 'add';
$action_value = _('Add');

$tariff_plans = Mysql::getInstance()->select('id, name')->from('tariff_plan')->orderby('name')->get()->all();

if (!empty($_POST)){

    if (!empty($_POST['login']) && !empty($_POST['password'])){

        $user = \User::getByLogin($_POST['login']);

        if (!empty($_POST['stb_mac'])){
            $mac = Middleware::normalizeMac($_POST['stb_mac']);
            $_POST['stb_mac'] = $mac;

            if (!$mac){
                $error = _('Error: Not valid mac address');
            }else{
                $user_by_mac = \User::getByMac($mac);

                if (!empty($user_by_mac)){
                    $error = _('Error: STB with such MAC address already exists');
                }
            }
        }

        if ($error){

        }else if (!empty($user)){
            $error = _('Error: Login already in use');
        }else{

            Admin::checkAccess(AdminAccess::ACCESS_CREATE);

            $user_id = \User::createAccount($_POST);

            if ($user_id){
                header("Location: profile.php?id=".$user_id);
                exit();
            }else{
                $error = _('Error: could not create account');
            }
        }

    }else{
        $error = _('Error: Not all required fields are filled');
    }
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= _('User account')?></title>
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

    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">
    </script>
</head>
<body>

<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('User account')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="users.php"><< <?= _('Back')?></a>
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
            <br>
            <br>
            <form method="POST">
                <table class="form">
                    <tr>
                        <td width="100"><?= _('Login')?>*</td>
                        <td><input type="text" name="login"></td>
                    </tr>
                    <tr>
                        <td><?= _('Password')?>*</td>
                        <td><input type="text" name="password"></td>
                    </tr>
                    <tr>
                        <td><?= _('Full name')?></td>
                        <td><input type="text" name="full_name"></td>
                    </tr>
                    <tr>
                        <td><?= _('Account number')?></td>
                        <td><input type="text" name="account_number"></td>
                    </tr>
                    <tr>
                        <td><?= _('MAC')?></td>
                        <td><input type="text" name="stb_mac"></td>
                    </tr>
                    <tr>
                        <td><?= _('Account disabled')?></td>
                        <td><input type="checkbox" name="status" value="0"></td>
                    </tr>
                    <tr>
                        <td><?= _('Tariff plan')?></td>
                        <td>
                            <select name="tariff_plan_id">
                                <option value="0">---</option>
                                <?
                                foreach ($tariff_plans as $plan){
                                    echo '<option value="'.$plan['id'].'">'.$plan['name'].'</option>';
                                }

                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="<?= htmlspecialchars(_('Add'), ENT_QUOTES)?>"/></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
    
</body>
</html>