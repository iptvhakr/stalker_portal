<?php
ob_start();
session_start();
include "./common.php";

moderator_access();

foreach (@$_POST as $key => $value){
    //$_POST[$key] = trim($value);
}

$error = '';
$action_name = 'add';
$action_value = _('Add');

$packages = Mysql::getInstance()->from('services_package')->get()->all();

if (!empty($_POST['add']) && !empty($_POST['name'])){

    $package_id = Mysql::getInstance()->insert('services_package', array(
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'external_id' => empty($_POST['external_id']) ? '' : $_POST['external_id'],
        'type' => $_POST['package_type']
    ))->insert_id();

    $services = json_decode($_POST['services'], true);

    if ($services){
        foreach ($services as $service){
            Mysql::getInstance()->insert('service_in_package',
                array(
                    'service_id' => $service,
                    'package_id' => $package_id,
                    'type' => $_POST['package_type']
                ));
        }
    }

    header("Location: services_packages.php");
    exit;
}

$id = @intval($_GET['id']);

if (!empty($id)){
    if (!empty($_POST['edit']) && !empty($_POST['name'])){

        Mysql::getInstance()->update('services_package', array(
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'external_id' => empty($_POST['external_id']) ? '' : $_POST['external_id'],
            'type' => $_POST['package_type']
        ), array('id' => $id));

        Mysql::getInstance()->delete('service_in_package', array('package_id' => $id));

        $services = json_decode($_POST['services'], true);

        if ($services){
            foreach ($services as $service){
                Mysql::getInstance()->insert('service_in_package',
                    array(
                        'service_id' => $service,
                        'package_id' => $id,
                        'type' => $_POST['package_type']
                    ));
            }
        }

        header("Location: services_packages.php");
        exit;
    }elseif(!empty($_GET['del'])){
        Mysql::getInstance()->delete('services_package', array('id' => $id));
        Mysql::getInstance()->delete('service_in_package', array('package_id' => $id));

        header("Location: services_packages.php");
        exit;
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_package = Mysql::getInstance()->from('services_package')->where(array('id' => $id))->get()->first();
    $edit_services = Mysql::getInstance()->from('service_in_package')->where(array('package_id' => $id))->get()->all('service_id');
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= _('SERVICES PACKAGES')?></title>
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
        .multi-selection{
            width: 200px;
            height: 300px;
        }
    </style>

    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">


        var picked_services = <?= empty($edit_services) ? '[]' : json_encode($edit_services)?>;
        var picked_type = '<?= empty($edit_package['type']) ? '' : $edit_package['type']?>';

        $(function(){

            $('.edit-mode').attr('disabled', 'disabled');

            $('.submit-form').submit(function(){

                var services = [];

                $('.services-picked option').each(function(idx, element){
                    services.push($(element).val());
                });

                $('.json-services').val(JSON.stringify(services));
                return true;
            });

            $('.package-type').change(function(eventObj){

                var type = $('.package-type option:selected').val();

                if (type != picked_type){
                    picked_services = [];
                }

                $('.services-available option').each(function(index, option) {
                    $(option).remove();
                });

                $('.services-picked option').each(function(index, option) {
                    $(option).remove();
                });

                if (type != 0){

                    $('.edit-mode').removeAttr('disabled');

                    $.get('get.php?get='+type+'_services', function(data){
                        data = JSON.parse(data);
                        var options = data && data.result || [];
                        var options_str = options.reduce(function(prev, curr){
                            if (picked_services.indexOf(curr.id) == -1){
                                return prev + '<option value='+curr.id+'>'+curr.name+'</option>';
                            }else{
                                $('<option value='+curr.id+'>'+curr.name+'</option>').appendTo('.services-picked');
                                return prev;
                            }
                        },'');

                        $(options_str).appendTo('.services-available');
                    });
                }
            });

            $('.services-available').dblclick(function(eventObj){
                if (eventObj.target instanceof HTMLOptionElement){
                    $(eventObj.target).appendTo('.services-picked');
                }
            });

            $('.services-picked').dblclick(function(eventObj){
                if (eventObj.target instanceof HTMLOptionElement){
                    $(eventObj.target).appendTo('.services-available');
                }
            });

            $('.multiple-add').click(function(){
                $('.services-available option:selected').each(function(idx, element){
                    $(element).appendTo('.services-picked');
                });
                return false;
            });

            $('.multiple-delete').click(function(){
                $('.services-picked option:selected').each(function(idx, element){
                    $(element).appendTo('.services-available');
                });
                return false;
            });

            $('.package-type option[value='+picked_type+']').attr('selected', 'selected');
            $('.package-type').change();
        });

    </script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('SERVICES PACKAGES')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="tariffs.php"><< <?= _('Back')?></a>
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
                    <td><?= _('External ID')?></td>
                    <td><?= _('Title')?></td>
                    <td>&nbsp;</td>
                </tr>
                <?
                foreach ($packages as $package){
                    echo '<tr>';
                    echo '<td>'.$package['external_id'].'</td>';
                    echo '<td>'.$package['name'].'</td>';
                    echo '<td>';
                    echo '<a href="?edit=1&id='.$package['id'].'">edit</a>&nbsp;';
                    echo '<a href="?del=1&id='.$package['id'].'" onclick="if(confirm(\''._('Do you really want to delete this record?').'\')){return true}else{return false}">del</a>';
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
            <form class="submit-form" method="POST">
                <table class="form">
                    <tr>
                        <td width="130"><?= _('External ID')?></td>
                        <td><input type="text" name="external_id" value="<?= @$edit_package['external_id']?>"></td>
                    </tr>
                    <tr>
                        <td><?= _('Title')?></td>
                        <td><input type="text" name="name" value="<?= @$edit_package['name']?>"></td>
                    </tr>
                    <tr>
                        <td><?= _('Short description')?></td>
                        <td><textarea name="description"><?= @$edit_package['description']?></textarea>
                    </tr>
                    <tr>
                        <td><?= _('Type')?></td>
                        <td>
                            <select name="package_type" class="package-type">
                                <option value="">---</option>
                                <option value="tv">tv</option>
                                <option value="video">video</option>
                                <option value="radio">radio</option>
                                <option value="module">module</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <table>
                                <tr>
                                    <td align="center">
                                        <?= _('Available')?><br>
                                        <select multiple="multiple" class="multi-selection services-available edit-mode">
                                        </select>
                                    </td>
                                    <td>
                                        <button class="edit-mode multiple-add"> >> </button><br>
                                        <button class="edit-mode multiple-delete"> << </button>
                                    </td>
                                    <td align="center">
                                        <?= _('Selected')?><br>
                                        <select multiple="multiple" class="multi-selection services-picked edit-mode">
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><input type="hidden" name="services" class="json-services">
                        <input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"/></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>