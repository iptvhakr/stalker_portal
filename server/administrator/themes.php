<?php

session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$default_template = Mysql::getInstance()->from('settings')->get()->first('default_template');

if (!empty($_POST['template'])){

    Admin::checkAccess(AdminAccess::ACCESS_EDIT);

    Mysql::getInstance()->update('settings', array('default_template' => $_POST['template']));

    if ($_POST['template'] != $default_template){
        $event = new SysEvent();
        $event->setUserListByMac('online');
        $event->sendReboot();
    }

    header("Location: themes.php");
    exit;
}

$themes = Middleware::getThemes();

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
    <title><?= _('Templates')?></title>

    <script src="js/jquery-1.7.1.min.js"></script>

    <script>
        $(function(){
            var default_template = '<?= $default_template?>';

            $('.template_preview img').live('error', function(){
                alert('qqq');
            });

            $('.template_select').change(function(){
                var template = $('.template_select option:selected').val();
                $('.template_preview').html('<img onerror="$(\'.template_preview\').text(\'<?= _('not found')?>\')" src="../../c/template/'+template+'/preview.png"/>');
            }).change();
        });
    </script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('Templates')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="index.php"><< <?= _('Back')?></a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <font color="Red">
                <strong>
    <pre>
    <? echo $error?>
    </pre>
                </strong>
            </font>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td align="center">
            <form method="POST">
            <table class='form' width="400px">
                <tr>
                    <td align="right" width="80px"><?= _('Theme')?>:</td>
                    <td>
                        <select class="template_select" name="template">
                            <?
                            foreach ($themes as $theme){
                                echo '<option value="'.$theme.'" '.($default_template == $theme ? 'selected' : '').'>'.$theme.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top"><?= _('Preview')?>:</td>
                    <td>
                        <div class="template_preview" style="width: 320px; height: 240px; border: 1px solid #ccc"></div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="<?= _('Save')?>"/></td>
                </tr>
            </table>
            </form>
        </td>
    </tr>
</table>