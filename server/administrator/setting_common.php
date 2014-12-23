<?php
session_start();

ob_start();

include "./common.php";

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}

$settings = ImageAutoUpdate::getAll();

if (!empty($_GET['del']) && !empty($_GET['id'])){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    $setting = ImageAutoUpdate::getById((int) $_GET['id']);
    $setting->delete();

    header("Location: setting_common.php");
    exit;
};

if (!empty($_POST)){

    if ($_POST['id'] == 0){

        Admin::checkAccess(AdminAccess::ACCESS_CREATE);

        ImageAutoUpdate::create($_POST);
    }else{

        $image_update = ImageAutoUpdate::getById((int) $_POST['id']);

        if (!empty($_POST['switch_autoupdate'])){

            Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

            $image_update->toggle();
        }else{

            Admin::checkAccess(AdminAccess::ACCESS_EDIT);

            $image_update->setSettings($_POST);
        }
    }

    header("Location: setting_common.php");
    exit;
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
}.list, .list td, .form{
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
.form{
    width: 80%;
}

.form td{
    width: 50%;
}

.form input{
    width: 90%;
}

h3{
    text-align: left;
    margin-left: 30px;
}

.setting-block{
    border: 1px solid #E5E5E5;
}

.del-block{
    float: right;
    font-size: 16px;
    margin-right: 4px;
}

.del-block a {
    color: #8b0000 !important;
}

</style>
<title><?= _('Firmware auto update')?></title>
<script type="text/javascript" src="js.js"></script>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.min.js"></script>

<script id="update_item_tmpl" type="text/x-jquery-tmpl">
    <div class="setting-block">
        <div class="del-block"><a href="?del=1&id=${id}" style="{{if idx==0}}display:none{{/if}}">x</a></div>
        <form method="POST">
            <input type="hidden" name="id" value="${id}">
            <h3><?= _('Firmware auto update')?> ({{if enable==="1"}}<?=_('enabled')?>{{else}}<?=_('disabled')?>{{/if}})
                <input type="submit" name="switch_autoupdate" value="{{if enable==="1"}}<?=_('Disable')?>{{else}}<?=_('Enable')?>{{/if}}"/>
            </h3>
            <table class="form">
                <tr>
                    <td><?= _('STB Model')?></td>
                    <td>
                        <select name="stb_type" class="stb-type">
                            <option value="MAG200" {{if stb_type==="MAG200"}}selected{{/if}} >MAG200</option>
                            <option value="MAG245" {{if stb_type==="MAG245"}}selected{{/if}} >MAG245</option>
                            <option value="MAG250" {{if stb_type==="MAG250"}}selected{{/if}} >MAG250</option>
                            <option value="MAG254" {{if stb_type==="MAG254"}}selected{{/if}} >MAG254</option>
                            <option value="MAG255" {{if stb_type==="MAG255"}}selected{{/if}} >MAG255</option>
                            <option value="MAG270" {{if stb_type==="MAG270"}}selected{{/if}} >MAG270</option>
                            <option value="MAG275" {{if stb_type==="MAG275"}}selected{{/if}} >MAG275</option>
                            <option value="WR320" {{if stb_type==="WR320"}}selected{{/if}} >WR320</option>
                            <option value="AuraHD0" {{if stb_type==="AuraHD0"}}selected{{/if}} >AuraHD0</option>
                            <option value="AuraHD1" {{if stb_type==="AuraHD1"}}selected{{/if}} >AuraHD1</option>
                            <option value="AuraHD9" {{if stb_type==="AuraHD9"}}selected{{/if}} >AuraHD9</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>ImageVersion</td>
                    <td><input type="text" name="require_image_version" value="${require_image_version}"/></td>
                </tr>
                <tr>
                    <td>ImageDate</td>
                    <td><input type="text" name="require_image_date" value="${require_image_date}"/></td>
                </tr>
                <tr>
                    <td><?= _('Required')?> ImageDescription</td>
                    <td><input type="text" name="image_description_contains" value="${image_description_contains}"/></td>
                </tr>
                <tr>
                    <td><?= _('Required')?> ImageVersion</td>
                    <td><input type="text" name="image_version_contains" value="${image_version_contains}"/></td>
                </tr>
                <tr>
                    <td><?= _('Required')?> HardwareVersion</td>
                    <td><input type="text" name="hardware_version_contains" value="${hardware_version_contains}"/></td>
                </tr>
                <tr>
                    <td><?= _('Update type')?></td>
                    <td>
                        <select name="update_type">
                            <option value="http_update" {{if update_type==="http_update"}}selected{{/if}} >http update</option>
                            <option value="reboot_dhcp" {{if update_type==="reboot_dhcp"}}selected{{/if}} >reboot dhcp</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= _('Prefix')?></td>
                    <td><input type="text" name="prefix" value="${prefix}"/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>"/></td>
                </tr>
            </table>
        </form>
    </div>
</script>

<script type="text/javascript">

    var update_settings = <?= empty($settings) ? '[]' : json_encode($settings)?>;

    update_settings = update_settings.map(function(item, idx){
        item['idx'] = idx;
        return item;
    });

    $(function(){

        $("#update_item_tmpl").tmpl(update_settings).appendTo('.blocks-container');

        $('.add-block').live('click', function(event){

            var empty_setting = {"idx":$('.blocks-container>div').length,"id":"0","enable":"0","require_image_version":"","require_image_date":"","image_version_contains":"","image_description_contains":"","update_type":"","changed":"","stb_type":"","prefix":""};

            $("#update_item_tmpl").tmpl(empty_setting).appendTo('.blocks-container');

            $(".add-block").hide();

            updateDisabledStbTypes();

            return false;
        });

        $('.del-block a').live('click', function(event){

            var item = $(this);

            if (confirm('<?= htmlspecialchars(_('Do you really want to delete this item?'), ENT_QUOTES)?>')){
                if (item.attr('href').indexOf('&id=0') != -1){
                    item.parent().parent().remove();
                    updateDisabledStbTypes();
                    return false;
                }else{
                    updateDisabledStbTypes();
                    return true;
                }
            }

            return false;
        });

        $('.stb-type').live('change', function(event){
            updateDisabledStbTypes()
        });

        updateDisabledStbTypes();
    });

    function updateDisabledStbTypes(){

        return true;

        var selected = {};

        $('.stb-type option:selected').each(function(idx, element){
            selected[$(element).val()] = true;
        });

        $('.stb-type option').each(function(idx, element){

            var picked = $(element).parent().find('option:selected').val();

            if (selected.hasOwnProperty($(element).val()) && $(element).val() != picked){
                $(element).attr('disabled', 'disabled');
            }else{
                $(element).removeAttr('disabled');
            }
        });
    }

</script>

</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="640">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Firmware auto update')?>&nbsp;</b></font>
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
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>

    <td align="center">
        
        <div class="blocks-container">

        </div>

        <input type="button" value="<?= htmlspecialchars(_('Add'), ENT_QUOTES)?>" class="add-block">
    </td>
</tr>
</table>