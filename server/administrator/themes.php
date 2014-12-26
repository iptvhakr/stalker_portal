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

        .template{
            width: 350px;
            height: 220px;
            border: 1px solid #fff;
            float: left;
            margin: 0 10px 50px 10px;
            cursor: pointer;
        }

        .apply-btn{
            visibility: hidden;
        }

        .template:hover, .template[data-selected="true"]{
            border: 1px solid #88BBFF;
        }

        .template[data-selected="true"] .apply-btn{
            visibility: visible;
        }

        .template-title{
            height: 20px;
            width: 200px;
            position: relative;
            top: -8px;
            background-color: #fff;
            font-weight: bold;
        }

        .template-preview{
            width: 320px;
            height: 180px;
            border: 1px solid #ccc;
        }
    </style>
    <title><?= _('Templates')?></title>

    <script src="js/jquery-1.7.1.min.js"></script>

    <script>
        $(function(){
            var default_template = '<?= $default_template?>';

            /*$('.template_select').change(function(){
                var template = $('.template_select option:selected').val();
                $('.template_preview').html('<img onerror="$(\'.template_preview\').text(\'<?= _('not found')?>\')" src="../../c/template/'+template+'/preview.png"/>');
            }).change();*/

            $('.template').click(function(){
                $('.template').each(function(){
                    $(this).removeAttr('data-selected');
                });

                $(this).attr('data-selected', 'true');
            });
        });
    </script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="750">
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

            <b><?= _('CURRENT THEME')?>: <span class="current-skin"><?= ucwords(str_replace('_', ' ', $default_template))?></span></b>
            <div class="template_preview" style="width: 160px; height: 90px; border: 1px solid #ccc;margin-top: 10px">
                <img width="160" height="90" onerror="$(this).parent().text('<?= _('preview not available')?>')" src="../../c/template/<?=$default_template?>/preview.png"/>
            </div>

            <div class="preview-list" style="margin-top: 60px">
                <?
                foreach ($themes as $theme){
                    ?>
                    <div class="template">
                        <div class="template-title"><?= ucwords(str_replace('_', ' ', $theme))?></div>
                        <div class="template-preview">
                            <img width="320" height="180" onerror="$(this).parent().text('<?= _('preview not available')?>')" src="../../c/template/<?=$theme?>/preview.png"/>
                        </div>

                        <form method="POST">
                            <input class="template_select" name="template" value="<?= $theme?>" type="hidden">
                            <input type="submit" class="apply-btn" value="<?= _('Apply')?>" style="margin-top: 30px"/>
                        </form>
                    </div>
                    <?
                }
                ?>
            </div>
        </td>
    </tr>
</table>