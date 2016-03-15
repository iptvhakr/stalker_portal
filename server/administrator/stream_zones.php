<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';
$action_name = 'add';
$action_value = _('Add');

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}

if (@$_POST['add']){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    $zone_id = Mysql::getInstance()->insert('stream_zones', array(
        'name'         => @$_POST['name'],
        'default_zone' => @$_POST['default_zone']
    ))->insert_id();

    $countries = empty($_POST['countries']) ? array() : json_decode($_POST['countries'], true);

    foreach ($countries as $country_id){
        Mysql::getInstance()->insert('countries_in_zone', array('zone_id' => $zone_id, 'country_id' => $country_id));
    }

    header("Location: stream_zones.php");
}

$id = @intval($_GET['id']);

if (!empty($id)){

    if (@$_POST['edit']){

        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        Mysql::getInstance()->update('stream_zones',
            array(
                'name'         => @$_POST['name'],
                'default_zone' => @$_POST['default_zone']
            ),
            array('id' => $id)
        );

        $new_countries_id = empty($_POST['countries']) ? array() : json_decode($_POST['countries'], true);

        $exists_zone_countries_id = Mysql::getInstance()->from('countries_in_zone')->where(array('zone_id' => $id))->get()->all('country_id');

        $need_to_delete = array_diff($exists_zone_countries_id, $new_countries_id);
        $need_to_add    = array_diff($new_countries_id, $exists_zone_countries_id);

        if ($need_to_delete){
            Mysql::getInstance()->query("delete from countries_in_zone where zone_id=$id and country_id in (".implode(",", $need_to_delete).")");
        }

        if ($need_to_add){
            foreach ($need_to_add as $country_id){
                Mysql::getInstance()->insert('countries_in_zone', array('zone_id' => $id, 'country_id' => $country_id));
            }
        }

        header("Location: stream_zones.php");
        exit;
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        Mysql::getInstance()->delete('stream_zones', array('id' => $id));

        Mysql::getInstance()->delete('countries_in_zone', array('zone_id' => $id));

        header("Location: stream_zones.php");
        exit;
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = _('Save');
    $edit_zone = Mysql::getInstance()->from('stream_zones')->where(array('id' => $id))->get()->first();
    $zone_countries = Mysql::getInstance()->from('countries_in_zone')->where(array('zone_id' => $edit_zone['id']))->get()->all('country_id');
    //$picked_countries = Mysql::getInstance()->from('countries')->in('id', $zone_countries)->get()->all();
}else{
    $zone_countries = array();
}

$zone_countries = array_values($zone_countries);

$zones = Mysql::getInstance()->from('stream_zones')->get()->all();

$used_countries = Mysql::getInstance()->from('countries_in_zone')->groupby('country_id')->get()->all('country_id');

$countries = Mysql::getInstance()->from('countries')->orderby('name_en')->get()->all();

$countries = array_map(function($country) use ($zone_countries){

    if (array_search($country['id'], $zone_countries) !== false){
        $country['picked'] = 1;
    }

    $country['name'] = $country['name_en'];

    return $country;
}, $countries);

$countries = array_filter($countries, function($country) use ($used_countries){
    return !empty($country['picked']) || array_search($country['id'], $used_countries) === false;
});

$countries = array_values($countries);

$default_zone_id = Mysql::getInstance()->from('stream_zones')->where(array('default_zone' => 1))->get()->first('id');

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
        .multi-selection{
            width: 200px;
            height: 300px;
        }
    </style>

    <script type="text/javascript" src="../adm/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">

        var countries = <?= empty($countries) ? '[]' : json_encode($countries)?>;
        var picked_zone = '<?= empty($edit_zone['id']) ? '' : $edit_zone['id']?>';

        $(function(){

            var countries_str = countries.reduce(function(prev, curr){
                if (!curr.hasOwnProperty('picked')){
                    return prev + '<option value='+curr.id+'>'+curr.name+'</option>';
                }else{
                    $('<option value='+curr.id+'>'+curr.name+'</option>').appendTo('.countries-picked');
                    return prev;
                }
            },'');

            $(countries_str).appendTo('.countries-available');

            $('.countries-available').dblclick(function(eventObj){
                if (eventObj.target instanceof HTMLOptionElement){
                    $(eventObj.target).appendTo('.countries-picked');
                }
            });

            $('.countries-picked').dblclick(function(eventObj){
                if (eventObj.target instanceof HTMLOptionElement){
                    $(eventObj.target).appendTo('.countries-available');
                }
            });

            $('.multiple-add').click(function(){
                $('.countries-available option:selected').each(function(idx, element){
                    $(element).appendTo('.countries-picked');
                });
                return false;
            });

            $('.multiple-delete').click(function(){
                $('.countries-picked option:selected').each(function(idx, element){
                    $(element).appendTo('.countries-available');
                });
                return false;
            });

            $('.submit-form').submit(function(){

                var countries = [];

                $('.countries-picked option').each(function(idx, element){
                    countries.push($(element).val());
                });

                $('.json-countries').val(JSON.stringify(countries));
                return true;
            });
        });



    </script>

    <title><?= _('Stream zones')?></title>

</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
            <font size="5px" color="White"><b>&nbsp;<?= _('Stream zones')?>&nbsp;</b></font>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" valign="bottom">
            <a href="stream_servers.php"><< <?= _('Back')?></a>
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
            <table class='list' cellpadding='3' cellspacing='0'>
                <tr>
                    <td>ID</td>
                    <td><?= _('Name')?></td>
                    <td>&nbsp;</td>
                </tr>
                <? foreach ($zones as $zone){
                echo '<tr ';
                if ($zone['default_zone'] == 1){
                    echo 'style="background: #ecffec"';
                }
                echo '>';
                echo '<td>'.$zone['id'].'</td>';
                echo '<td>'.$zone['name'].'</td>';
                echo '<td>';

                echo '<a href="?edit=1&id='.$zone['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$zone['id'].'" onclick="if(confirm(\''.sprintf(_('Are you sure you want to delete record %s?'), $zone['name']).'\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
            }?>
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
                        <td width="190"><?= _('Name')?></td>
                        <td><input type="text" name="name" value="<?echo @$edit_zone['name']?>" /></td>
                    </tr>

                    <tr>
                        <td width="190"><?= _('Default zone')?></td>
                        <?
                            if (!empty($default_zone_id) && @$edit_zone['id'] != $default_zone_id){
                                $disabled = 'disabled';
                            }else{
                                $disabled = '';
                            }
                        ?>
                        <td><input type="checkbox" name="default_zone" value="1" <?= (@$edit_zone['default_zone'] == 1 ? 'checked' : ''); echo $disabled?> /></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td align="center">
                                        <?= _('Countries')?><br>
                                        <select multiple="multiple" class="multi-selection countries-available edit-mode">
                                        </select>
                                    </td>
                                    <td>
                                        <button class="edit-mode multiple-add"> >> </button><br>
                                        <button class="edit-mode multiple-delete"> << </button>
                                    </td>
                                    <td align="center">
                                        <?= _('Selected')?><br>
                                        <select multiple="multiple" class="multi-selection countries-picked edit-mode">
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td>
                            <input type="hidden" name="countries" class="json-countries">
                            <input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"/>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>