<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

moderator_access();

if (@$_GET['del']){
    $query = "delete from itv where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_itv.php");
}

if (isset($_GET['status']) && @$_GET['id']){
    $query = "update itv set status='".intval(@$_GET['status'])."' where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_itv.php");
}

if (!$error){
    
    if (@$_POST['censored'] == 'on'){
        $censored = 1;
    }else{
        $censored = 0;
    }

    if (@$_POST['use_http_tmp_link'] == 'on'){
        $use_http_tmp_link = 1;
    }else{
        $use_http_tmp_link = 0;
    }

    if (@$_POST['wowza_tmp_link'] == 'on'){
        $wowza_tmp_link = 1;
    }else{
        $wowza_tmp_link = 0;
    }
    
    if (@$_POST['wowza_dvr'] == 'on'){
        $wowza_dvr = 1;
    }else{
        $wowza_dvr = 0;
    }

    if (@$_POST['enable_tv_archive'] == 'on'){
        $enable_tv_archive = 1;
    }else{
        $enable_tv_archive = 0;
    }

    $storage_name = empty($_POST['storage_name']) ? '' : $_POST['storage_name'];

    $enable_monitoring = @intval($_POST['enable_monitoring']);

    $allow_pvr = @intval($_POST['allow_pvr']);

    $enable_wowza_load_balancing = @intval($_POST['enable_wowza_load_balancing']);

    if (@$_POST['base_ch'] == 'on'){
        $base_ch = 1;
    }else{
        $base_ch = 0;
    }
    
    if (@$_POST['bonus_ch'] == 'on'){
        $bonus_ch = 1;
    }else{
        $bonus_ch = 0;
    }
    
    if (@$_POST['hd'] == 'on'){
        $hd = 1;
    }else{
        $hd = 0;
    }

    if (@$_POST['number'] && !check_number($_POST['number']) && !@$_GET['update']){
        $error = sprintf(_('Error: channel with number "%s" is already in use').' <a href="#form">#</a>', intval($_POST['number']));
    }

    $urls = empty($_POST['cmd']) ? array() : $_POST['cmd'];

    $links = array();

    foreach ($urls as $key => $value){

        if (empty($value)){
            continue;
        }

        $links[] = array(
            'url'               => $value,
            'priority'          => array_key_exists($key, $_POST['priority']) ? (int) $_POST['priority'][$key] : 0,
            'use_http_tmp_link' => !empty($_POST['use_http_tmp_link']) && array_key_exists($key, $_POST['use_http_tmp_link']) ? (int) $_POST['use_http_tmp_link'][$key] : 0,
            'wowza_tmp_link'    => !empty($_POST['wowza_tmp_link']) && array_key_exists($key, $_POST['wowza_tmp_link']) ? (int) $_POST['wowza_tmp_link'][$key] : 0,
            'user_agent_filter' => array_key_exists($key, $_POST['user_agent_filter']) ? $_POST['user_agent_filter'][$key] : '',
            'monitoring_url'    => array_key_exists($key, $_POST['monitoring_url']) ? $_POST['monitoring_url'][$key] : '',
            'use_load_balancing' => !empty($_POST['stream_server']) && array_key_exists($key, $_POST['stream_server']) && !empty($_POST['use_load_balancing']) && array_key_exists($key, $_POST['use_load_balancing']) ? (int) $_POST['use_load_balancing'][$key] : 0,
            'enable_monitoring'  => !empty($_POST['enable_monitoring']) && array_key_exists($key, $_POST['enable_monitoring']) ? (int) $_POST['enable_monitoring'][$key] : 0,
            'stream_servers'    => !empty($_POST['stream_server']) && array_key_exists($key, $_POST['stream_server']) ? $_POST['stream_server'][$key] : array(),
        );
    }


    $mc_cmd = @$_POST['mc_cmd'];

    if (empty($mc_cmd)){
        foreach ($links  as $link){
            if ((strpos($link['url'], 'rtp://') !== false || strpos($link['url'], 'udp://') !== false) && preg_match("/(\S+:\/\/\S+)/", $link['url'], $match)){
                $mc_cmd = $match[1];
                break;
            }
        }
    }

    if (@$_GET['save'] && !$error && !empty($_POST)){
    
        if(@$_GET['name'] && @$_POST['tv_genre_id'] > 0){

            $ch_id = intval(@$_GET['id']);

            $channel = Itv::getChannelById($ch_id);

            if (!empty($channel) && $channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($channel['enable_tv_archive']){

                    if ($channel['wowza_dvr']){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->deleteTask($ch_id);
                }
            }
    
            $query = "insert into itv (
                                        name,
                                        number,
                                        use_http_tmp_link,
                                        wowza_tmp_link,
                                        wowza_dvr,
                                        censored,
                                        base_ch,
                                        bonus_ch,
                                        hd,
                                        cost,
                                        cmd,
                                        cmd_1,
                                        cmd_2,
                                        cmd_3,
                                        mc_cmd,
                                        enable_wowza_load_balancing,
                                        enable_tv_archive,
                                        allow_pvr,
                                        enable_monitoring,

                                        descr,
                                        tv_genre_id, 
                                        status,
                                        xmltv_id,
                                        service_id,
                                        volume_correction,
                                        correct_time
                                        ) 
                                values ('".@$_POST['name']."',
                                        '".@$_POST['number']."', 
                                        '".$use_http_tmp_link."',
                                        '".$wowza_tmp_link."',
                                        '".$wowza_dvr."',
                                        '".$censored."',
                                        '".$base_ch."',
                                        '".$bonus_ch."',
                                        '".$hd."',
                                        '".@$_POST['cost']."',
                                        '".(!empty($_POST['cmd'][0]) ? $_POST['cmd'][0] : "")."',
                                        '".@$_POST['cmd_1']."',
                                        '".@$_POST['cmd_2']."',
                                        '".@$_POST['cmd_3']."',
                                        '".$mc_cmd."',
                                        '".$enable_wowza_load_balancing."',
                                        '".$enable_tv_archive."',
                                        '".$allow_pvr."',
                                        '".$enable_monitoring."',

                                        '".@$_POST['descr']."',
                                        '".@$_POST['tv_genre_id']."', 
                                        1,
                                        '".@$_POST['xmltv_id']."',
                                        '".trim($_POST['service_id'])."',
                                        ".intval($_POST['volume_correction']).",
                                        ".intval($_POST['correct_time'])."
                                        )";
            echo $query;
            $rs=$db->executeQuery($query);
            //var_dump($rs);
            $ch_id = $rs->getLastInsertId();

            foreach ($links as $link){

                $link['ch_id'] = $ch_id;

                $links_on_server = $link['stream_servers'];

                unset($link['stream_servers']);

                $link_id = Mysql::getInstance()->insert('ch_links', $link)->insert_id();

                if ($link_id && $links_on_server){
                    foreach ($links_on_server as $streamer_id){
                        Mysql::getInstance()->insert('ch_link_on_streamer', array('link_id' => $link_id, 'streamer_id' => $streamer_id));
                    }
                }
            }

            if (!empty($channel) && $channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($enable_tv_archive){

                    if ($wowza_dvr){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->createTask($ch_id, $storage_name);
                }
            }

            /*if ($wowza_dvr){
                $archive = new WowzaTvArchive();
            }else{
                $archive = new TvArchive();
            }

            if ($enable_tv_archive){
                $archive->createTask($ch_id);
            }else{
                $archive->deleteTask($ch_id);
            }*/

            if (!empty($_FILES['logo'])){

                if ($logo = handle_upload_logo($_FILES['logo'], $ch_id)){
                    Mysql::getInstance()->update('itv', array('logo' => $logo), array('id' => $ch_id));
                }else{
                    $error = _('Error: could not save logo').' <a href="#form">#</a>';
                }
            }
            
            header("Location: add_itv.php");
            exit;
        }
        else{
            $error = _('Error: all fields are required').' <a href="#form">#</a>';
        }
    }
    
    if (@$_GET['update'] && !$error && !empty($_POST)){

        if(@$_GET['name']){

            $ch_id = intval(@$_GET['id']);

            $channel = Itv::getChannelById($ch_id);

            if (!empty($channel) && $channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($channel['enable_tv_archive']){

                    if ($channel['wowza_dvr']){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->deleteTask($ch_id);
                }
            }

            $query = "update itv 
                                set name='".$_POST['name']."',
                                cmd='".(!empty($_POST['cmd'][0]) ? $_POST['cmd'][0] : "")."',
                                cmd_1='".@$_POST['cmd_1']."',
                                cmd_2='".@$_POST['cmd_2']."',
                                cmd_3='".@$_POST['cmd_3']."',
                                mc_cmd='".$mc_cmd."',
                                enable_wowza_load_balancing='".$enable_wowza_load_balancing."',
                                enable_tv_archive='".$enable_tv_archive."',
                                allow_pvr='".$allow_pvr."',
                                enable_monitoring='".$enable_monitoring."',

                                wowza_tmp_link='".$wowza_tmp_link."',
                                wowza_dvr='".$wowza_dvr."',
                                use_http_tmp_link='".$use_http_tmp_link."',
                                censored='".$censored."',
                                base_ch='".$base_ch."',
                                bonus_ch='".$bonus_ch."', 
                                hd='".$hd."', 
                                cost='".$_POST['cost']."', 
                                number='".$_POST['number']."', 
                                descr='".$_POST['descr']."', 
                                tv_genre_id='".$_POST['tv_genre_id']."',
                                xmltv_id='".$_POST['xmltv_id']."',
                                service_id='".trim($_POST['service_id'])."',
                                volume_correction=".intval($_POST['volume_correction']).",
                                correct_time=".intval($_POST['correct_time'])."
                            where id=".intval(@$_GET['id']);
            //var_dump($query);
            $rs=$db->executeQuery($query);

            if (!$enable_monitoring){
                Mysql::getInstance()->update('itv', array('monitoring_status' => 1), array('id' => intval(@$_GET['id'])));
            }

            $urls = $_POST['cmd'];
            $priorities = $_POST['priority'];

            $current_urls  = Mysql::getInstance()->from('ch_links')->where(array('ch_id' => intval($_GET['id'])))->get()->all('url');
            $current_links = Mysql::getInstance()->from('ch_links')->where(array('ch_id' => intval($_GET['id'])))->get()->all();

            $urls_str = "'".implode("','", $urls)."'";

            $need_to_delete_links = Mysql::getInstance()->query("select * from ch_links where ch_id=".intval($_GET['id'])." and url not in (".$urls_str.")")->all('id');

            if ($need_to_delete_links){
                Mysql::getInstance()->query("delete from ch_links where id in (".implode(",", $need_to_delete_links).")");
                Mysql::getInstance()->query("delete from ch_link_on_streamer where link_id in (".implode(",", $need_to_delete_links).")");
            }

            foreach ($links as $link){

                $link['ch_id'] = (int) $_GET['id'];

                $links_on_server = $link['stream_servers'];

                unset($link['stream_servers']);

                if (!in_array($link['url'], $current_urls)){
                    $link_id = Mysql::getInstance()->insert('ch_links', $link)->insert_id();

                    if ($link_id && $links_on_server){
                        foreach ($links_on_server as $streamer_id){
                            Mysql::getInstance()->insert('ch_link_on_streamer', array('link_id' => $link_id, 'streamer_id' => $streamer_id));
                        }
                    }
                }else{

                    $link_id = Mysql::getInstance()
                        ->from('ch_links')
                        ->where(array(
                        'ch_id' => (int) $_GET['id'],
                        'url'   => $link['url']
                    ))
                        ->get()
                        ->first('id');

                    Mysql::getInstance()->update('ch_links',
                        $link,
                        array(
                            'ch_id' => (int) $_GET['id'],
                            'url'   => $link['url']
                        )
                    );

                    if (!$link['enable_monitoring']){
                        Mysql::getInstance()->update('ch_links',
                            array(
                                'status' => 1
                            ),
                            array(
                                'ch_id' => (int) $_GET['id'],
                                'url'   => $link['url']
                            )
                        );
                    }

                    if ($link_id){
                        $on_streamers = Mysql::getInstance()->from('ch_link_on_streamer')->where(array('link_id' => $link_id))->get()->all('streamer_id');

                        if ($on_streamers){
                            $need_to_delete = array_diff($on_streamers, $links_on_server);
                            $need_to_insert = array_diff($links_on_server, $on_streamers);

                            if ($need_to_delete){
                                Mysql::getInstance()->query("delete from ch_link_on_streamer where link_id=$link_id and streamer_id in (".implode(",", $need_to_delete).")");
                            }

                            if ($need_to_insert){
                                foreach ($need_to_insert as $streamer_id){
                                    Mysql::getInstance()->insert('ch_link_on_streamer', array('link_id' => $link_id, 'streamer_id' => $streamer_id));
                                }
                            }

                        }else{
                            foreach ($links_on_server as $streamer_id){
                                Mysql::getInstance()->insert('ch_link_on_streamer', array('link_id' => $link_id, 'streamer_id' => $streamer_id));
                            }
                        }
                    }
                }
            }

            if (!empty($channel) && $channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($enable_tv_archive){

                    if ($wowza_dvr){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->createTask($ch_id, $storage_name);
                }
            }

            if (!empty($_FILES['logo']['name'])){

                if ($logo = handle_upload_logo($_FILES['logo'], $ch_id)){
                    Mysql::getInstance()->update('itv', array('logo' => $logo), array('id' => $ch_id));
                }else{
                    $error = _('Error: could not save logo').' <a href="#form">#</a>';
                }
            }

            if (empty($error)){
                header("Location: add_itv.php?edit=1&id=".intval(@$_GET['id'])."#form");
                exit;
            }
        }
        else{
            $error = _('Error: all fields are required').' <a href="#form">#</a>';
        }
    }
}

function handle_upload_logo($file, $ch_id){

    if (empty($file)){
        return true;
    }

    $images = array('image/gif' => 'gif', 'image/jpeg' => 'jpg', 'image/png' => 'png');

    if (!array_key_exists($file['type'], $images)){
        return false;
    }

    $ext = $images[$file['type']];

    $path = realpath(PROJECT_PATH."/../misc/logos/");

    if (!$path){
        return false;
    }

    $filename = $ch_id.".".$ext;

    $resolutions = array(
        '320' => array('height' => 96, 'width' => 96),
        '240' => array('height' => 72, 'width' => 72),
        '160' => array('height' => 48, 'width' => 48),
        '120' => array('height' => 36, 'width' => 36)
    );

    umask(0);

    foreach ($resolutions as $resolution => $dimension){

        $ico_path = $path."/".$resolution;

        if (!is_dir($ico_path)){
            mkdir($ico_path, 0777);
        }

        $fullpath = $ico_path."/".$filename;

        try{
            $icon = new Imagick($file['tmp_name']);
        }catch(ImagickException $e){
            //$e->getMessage();
            return false;
        }

        if (!$icon->resizeImage($dimension['width'], $dimension['height'], Imagick::FILTER_LANCZOS, 1)){
            return false;
        }

        if (!$icon->writeImage($fullpath)){
            return false;
        }

        $icon->destroy();

        chmod($fullpath, 0666);
    }

    unlink($file['tmp_name']);

    return $filename;
}

function check_number($num){
    global $db;
    $total_items = 1;
    $query = "select * from itv where number=".intval($num);
    $rs=$db->executeQuery($query);
    $total_items = $rs->getRowCount();
    if ($total_items > 0){
        return 0;
    }else{
        return 1;
    }
}

function get_screen_name($addr){
    preg_match("/(\S+)\s(\S+):\/\/(\d+).(\d+).(\d+).(\d+):(\d+)/", $addr, $tmp_arr);
    $img_str = '/iptv/mpg/'.$tmp_arr[6].'_'.$tmp_arr[7].'.mpg';
    return $img_str;
}

$tv_archive = new TvArchive();
$storages = Mysql::getInstance()->from('storages')->where(array('status' => 1, 'for_records' => 1, 'wowza_server' => 0, 'fake_tv_archive' => 0))->get()->all();

if (!empty($_GET['id'])){

    $task = TvArchive::getTaskByChannelId((int) $_GET['id']);

    if (!empty($task)){
        $selected_storage = $task['storage_name'];
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
<?= _('IPTV channels')?>
</title>
<script type="text/javascript" src="js.js"></script>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.tmpl.min.js"></script>
<script type="text/javascript" src="js/jquery.cookies.2.2.0.js"></script>

<script id="link_item_tmpl" type="text/x-jquery-tmpl">
    <div>
        <input name="cmd[${idx}]" size="50" type="text" value="${url}"
               style="border-style:solid;border-color: {{if status==1}}#66A566{{else}}#F88787{{/if}}"><br>
        <table>
            <tr>
                <td><?= _('priority')?>:</td>
                <td><input type="text" name="priority[${idx}]" size="3" value="${priority}"></td>
            </tr>
            <tr>
                <td><?= _('filter')?>:</td>
                <td>
                    <input type="text" name="user_agent_filter[${idx}]" value="${user_agent_filter}">
                </td>
            </tr>
            <tr>
                <td><?= _('Temporary HTTP URL')?>:</td>
                <td>
                    <input type="checkbox" class="use_http_tmp_link" name="use_http_tmp_link[${idx}]" value="1" {{if use_http_tmp_link==="1"}}checked{{/if}}>
                    <span style="display:{{if use_http_tmp_link==1}} {{else}}none{{/if}}">&nbsp;&nbsp;<?= _('WOWZA support')?>:<input type="checkbox" name="wowza_tmp_link[${idx}]" value="1" {{if wowza_tmp_link==="1"}}checked{{/if}}></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?= _('Enable monitoring')?>:
                    <input type="checkbox" class="enable_monitoring"  name="enable_monitoring[${idx}]" value="1" {{if enable_monitoring==="1"}}checked{{/if}}>
                </td>
            </tr>
            <tr class="monitoring_url_block" style="display:{{if enable_monitoring==1}} {{else}}none{{/if}}; background-color:#f8f8f8">
                <td colspan="2">
                    <?= _('Channel URL for monitoring')?>:<br>
                    <input type="text" size="48" name="monitoring_url[${idx}]" value="${monitoring_url}">
                </td>
            </tr>
            <tr>
                <td>load balancing:</td>
                <td>
                    <input type="checkbox" class="use_load_balancing" name="use_load_balancing[${idx}]" value="1" {{if use_load_balancing==="1"}}checked{{/if}}>
                </td>
            </tr>
            <tr style="display:{{if use_load_balancing==1}} {{else}}none{{/if}}; background-color:#f8f8f8">
                <td colspan="2">
                    <table width="100%">
                        {{each(i, stream_server) stream_servers}}
                        <tr>
                            <td width="50%">${stream_server.name}:</td>
                            <td width="50%">
                                <input type="checkbox" class="stream_server" name="stream_server[${idx}][]" value="${stream_server.id}" {{if stream_server.selected==1}}checked{{/if}}/>
                            </td>
                        </tr>
                        {{/each}}
                    </table>
                </td>
            </tr>
        </table>

        <button href="javascript://" class="add_ch_url"><?=_('add new link')?></button>
        {{if idx>0}}
        <button href="javascript://" class="del_ch_url"><?=_('delete')?></button>
        {{/if}}
        <hr>
    </div>
</script>

<script type="text/javascript">

    jQuery.fn.sortElements = (function(){

        var sort = [].sort;

        return function(comparator, getSortable) {

            getSortable = getSortable || function(){return this;};

            var placements = this.map(function(){

                var sortElement = getSortable.call(this),
                    parentNode = sortElement.parentNode,

                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                    nextSibling = parentNode.insertBefore(
                        document.createTextNode(''),
                        sortElement.nextSibling
                    );

                return function() {

                    if (parentNode === this) {
                        throw new Error(
                            "You can't sort elements if any one is a descendant of another."
                        );
                    }

                    // Insert before flag:
                    parentNode.insertBefore(this, nextSibling);
                    // Remove flag:
                    parentNode.removeChild(nextSibling);

                };

            });

            return sort.call(this, comparator).each(function(i){
                placements[i].call(getSortable.call(this));
            });
        };

    })();

    $(function(){

        $('.add_ch_url').live('click', function(event){

            var idx = $('.links_block>div').length;

            var link = {"url":"","priority":0,"status":1,"use_http_tmp_link":0,"wowza_tmp_link":0,"user_agent_filter":"","idx":idx,"monitoring_url":"", "use_load_balancing":0,"enable_monitoring":0};

            $("#link_item_tmpl").tmpl(link).appendTo('.links_block');

            return false;
        });

        $('.del_ch_url').live('click', function(event){
            if (confirm("<?= _('Do you really want to delete this record?')?>")){
                $(this).closest('div').remove();
            }
            return false;
        });

        if (!links){
            links = [];
        }

        if (links.length == 0){
            links = [{"url":"","priority":0,"status":1,"use_http_tmp_link":0,"wowza_tmp_link":0,"user_agent_filter":"","monitoring_url":"","use_load_balancing":0,"enable_monitoring":0}];
        }

        links = links.map(function(link, idx){
            link['idx'] = idx;
            return link;
        });

        $("#link_item_tmpl").tmpl(links).appendTo('.links_block');

        $('.use_http_tmp_link').live('click', function(event){
            if ($(this).attr('checked')){
                $(this).next().show();
            }else{
                $(this).next().hide();
            }
        });

        $('.use_load_balancing').live('click', function(event){
            if ($(this).attr('checked')){
                $(this).parent().parent().next().show();
            }else{
                $(this).parent().parent().next().hide();
            }
        });

        $('.enable_monitoring').live('click', function(event){
            if ($(this).attr('checked')){
                $(this).parent().parent().next().show();
            }else{
                $(this).parent().parent().next().hide();
            }
        });

        var table = $('.item_list');

        $('.item_list th')
            .each(function(){

                var th = $(this),
                    thIndex = th.index(),
                    inverse = false;

                if (thIndex == 1){
                    inverse = true;
                }

                th.click(function(eventObject){

                    //console.log(eventObject);

                    if (eventObject.hasOwnProperty("inverse")){
                        inverse = !eventObject.inverse;
                    }

                    table.find('td').filter(function(){

                        return $(this).index() === thIndex;

                    }).sortElements(function(a, b){

                        // todo: sort numbers
                        if (th.hasClass('number_row')){
                            return parseInt($.text([a]), 10) > parseInt($.text([b]), 10) ?
                                inverse ? -1 : 1
                                : inverse ? 1 : -1
                        }else{
                            return $.text([a]) > $.text([b]) ?
                                inverse ? -1 : 1
                                : inverse ? 1 : -1
                        }

                    }, function(){

                        // parentNode is the element we want to move
                        return this.parentNode;

                    });

                    inverse = !inverse;

                    $.cookies.set('sort_by_row', $(this).index());
                    $.cookies.set('sort_inverse', inverse);

                    $('.order').remove();

                    th.append(' <span class="order">' + (inverse ? '&darr;' : '&uarr;') + '</span>');
                });
            });

        $('.add_btn').click(function(){
            var new_position = $('.itv_form').offset();
            window.scrollTo(new_position.left, new_position.top);
            return false;
        });

        var sort_by_row = $.cookies.get('sort_by_row');

        if (sort_by_row !== null){
            $('.item_list th:eq('+sort_by_row+')').trigger(jQuery.Event("click", { inverse: $.cookies.get('sort_inverse') }));
        }

        $('.add_btn').click(function(){
            $('#form_').get(0).reset();
            document.location.href = 'add_itv.php#form';
        });

        $('#mc_cmd').on('input', null, null, function(eventObject){

            if (eventObject.currentTarget.value.indexOf('rtp://') != -1 || eventObject.currentTarget.value.indexOf('udp://') != -1){
                $('#enable_tv_archive').removeAttr('disabled');
                $('#allow_pvr').removeAttr('disabled');
            }else{
                $('#enable_tv_archive').attr('disabled', 'disabled');
                $('#allow_pvr').attr('disabled', 'disabled');
            }
        });

        if ($('#mc_cmd').val().indexOf('rtp://') != -1 || $('#mc_cmd').val().indexOf('udp://') != -1){
            $('#enable_tv_archive').removeAttr('disabled');
            $('#allow_pvr').removeAttr('disabled');
        }else{
            $('#enable_tv_archive').attr('disabled', 'disabled');
            $('#allow_pvr').attr('disabled', 'disabled');
        }
    });
</script>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('IPTV channels')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="#" class="add_btn"><?= _('Add')?></a>
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

<?
$query = "select itv.*, tv_genre.title as genres_name, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter, media_claims.no_epg, media_claims.wrong_epg from itv left join media_claims on itv.id=media_claims.media_id and media_claims.media_type='itv' inner join tv_genre on itv.tv_genre_id=tv_genre.id group by itv.id order by number";

//echo $query;

$rs=$db->executeQuery($query);
echo "<center><table class='list item_list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<th class='list number_row'><b>id</b></th>";
echo "<th class='list number_row'><b>"._('Number')."</b> <span class='order'>&darr;</span></th>";
echo "<th class='list'><b>"._('Service code')."</b></th>";
echo "<th class='list'><b>"._('Name')."</b></th>";
echo "<th class='list'><b>"._('URL')."</b></th>";
echo "<th class='list'><b>"._('Genre')."</b></th>";
echo "<th class='list'><b>"._('Archive')."</b></th>";
echo "<th class='list'><b>"._('Volume correction')."</b></th>";
echo "<th class='list'><b>"._('Claims about<br>audio/video/epg')."</b></th>\n";
echo "<th class='list'><b>&nbsp;</b></th>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr ";
    if ($arr['bonus_ch'] == 1){
        echo 'bgcolor="#ffffec"';
    }else{
        if ($arr['base_ch'] == 1){
            
        }else{
            if (strlen($arr['service_id'])<5){
                echo 'bgcolor="#f7f7f7"';
            }else{
                echo 'bgcolor="#ffecec"';
            }
        }
    }
    
    /*else{
        echo 'bgcolor="#ececec"';
    }*/
    echo " >";
    echo "<td class='list'>".$arr['id']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'>".$arr['service_id']."</td>";
    //echo "<td class='list'><a href='".get_screen_name($arr['cmd'])."' >".$arr['name']."</a></td>";
    echo "<td class='list' style='color:".get_color($arr)."' title='".get_hint($arr)."'><b>".$arr['name']."</b></td>";
    echo "<td class='list'>".$arr['cmd']."</td>";
    //echo "<td class='list'>".$arr['descr']."</td>";
    echo "<td class='list'>"._($arr['genres_name'])."</td>";
    echo "<td class='list' align='center'>".($arr['enable_tv_archive'] == 1 ? '&bull;' : '')."</td>";
    echo "<td class='list'>".$arr['volume_correction']."</td>";
    
    echo "<td class='list' align='center'>\n";
    if (check_access(array(1))){
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to reset claims counter?')."\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".$arr['sound_counter']." / ".$arr['video_counter']." / ".$arr['no_epg']," / ".$arr['wrong_epg']."</span>";
    if (check_access(array(1))){
        echo "</a>";
    }
    echo "</td>\n";
    
    echo "<td class='list' nowrap><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    //echo "<a href='?del=1&id=".$arr['id']."' >del</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"add_itv.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
    if ($arr['status']){
        echo "<a href='?status=0&id=".$arr['id']."'><font color='Green'>on</font></a>&nbsp;&nbsp;";
    }else{
        echo "<a href='?status=1&id=".$arr['id']."'><font color='Red'>off</font></a>&nbsp;&nbsp;";
    }
    echo "<a href='add_epg.php?id=".$arr['id']."'>EPG</a>&nbsp;&nbsp;</td>";
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from itv where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name     = $arr['name'];
        $number   = $arr['number'];
        $cmd      = $arr['cmd'];
        $mc_cmd   = $arr['mc_cmd'];
        $tv_genre_id = $arr['tv_genre_id'];
        $descr    = $arr['descr'];
        $status   = $arr['status'];
        $status   = $arr['status'];
        $censored = $arr['censored'];
        $base_ch  = $arr['base_ch'];
        $bonus_ch = $arr['bonus_ch'];
        $cost     = $arr['cost'];
        $hd       = $arr['hd'];
        $xmltv_id = $arr['xmltv_id'];
        $service_id = $arr['service_id'];
        $volume_correction = $arr['volume_correction'];
        $correct_time    = $arr['correct_time'];
        $use_http_tmp_link = $arr['use_http_tmp_link'];
        $wowza_tmp_link    = $arr['wowza_tmp_link'];
        $wowza_dvr = $arr['wowza_dvr'];
        $enable_tv_archive = $arr['enable_tv_archive'];
        $allow_pvr = $arr['allow_pvr'];
        $enable_monitoring = $arr['enable_monitoring'];
        $monitoring_url = $arr['monitoring_url'];
        $enable_wowza_load_balancing = $arr['enable_wowza_load_balancing'];
        $logo = $arr['logo'];

        if ($use_http_tmp_link){
            $checked_http_tmp_link = 'checked';
        }

        if ($wowza_tmp_link){
            $checked_wowza_tmp_link = 'checked';
        }

        if ($wowza_dvr){
            $checked_wowza_dvr = 'checked';
        }

        if ($enable_tv_archive){
            $checked_enable_tv_archive = 'checked';
        }

        if ($allow_pvr){
            $checked_allow_pvr = 'checked';
        }

        if ($enable_monitoring){
            $checked_enable_monitoring = 'checked';
        }

        if ($enable_wowza_load_balancing){
            $checked_wowza_load_balancing = 'checked';
        }

        if ($censored){
            $checked = 'checked';
        }
        if ($base_ch){
            $checked_base = 'checked';
        }
        if ($bonus_ch){
            $checked_bonus = 'checked';
        }
        if ($hd){
            $checked_hd = 'checked';
        }

        $stream_servers = StreamServer::getAll();

        $links = Mysql::getInstance()
            ->from('ch_links')
            ->where(array('ch_id' => (int) $arr['id']))
            ->orderby('priority')
            ->get()
            ->all();

        //var_dump($stream_servers);

        $links = array_map(function($link) use ($stream_servers){

            $on_streamers = StreamServer::getStreamersIdsForLink($link['id']);

            $link['stream_servers'] = array_map(function($server) use ($on_streamers){

                if (in_array($server['id'], $on_streamers)){
                    $server['selected'] = 1;
                }else{
                    $server['selected'] = 0;
                }

                return $server;
            }, $stream_servers);

            return $link;
        }, $links);
    }
}else if (!empty($_POST)){
    $name   = @$_POST['name'];
    $number = @$_POST['number'];
    $cmd    = @$_POST['cmd'];
    $mc_cmd = @$_POST['mc_cmd'];
    $tv_genre_id = @$_POST['tv_genre_id'];
    $descr  = @$_POST['descr'];
    $cost   = @$_POST['cost'];
    $xmltv_id   = @$_POST['xmltv_id'];
    $service_id = @$_POST['service_id'];
    $volume_correction = @$_POST['volume_correction'];
    $correct_time   = @$_POST['correct_time'];
    $monitoring_url = @$_POST['monitoring_url'];

    if (@$_POST['use_http_tmp_link']){
        $checked_http_tmp_link = 'checked';
    }

    if (@$_POST['wowza_tmp_link']){
        $checked_wowza_tmp_link = 'checked';
    }

    if (@$_POST['wowza_dvr']){
        $checked_wowza_dvr = 'checked';
    }

    if (@$_POST['enable_tv_archive']){
        $checked_enable_tv_archive = 'checked';
    }

    if (@$_POST['allow_pvr']){
        $checked_allow_pvr = 'checked';
    }

    if (@$_POST['enable_monitoring']){
        $checked_enable_monitoring = 'checked';
    }

    if (@$_POST['enable_wowza_load_balancing']){
        $checked_wowza_load_balancing = 'checked';
    }

    if (@$_POST['censored']){
        $checked = 'checked';
    }

    if (@$_POST['base_ch']){
        $checked_base = 'checked';
    }

    if (@$_POST['bonus_ch']){
        $checked_bonus = 'checked';
    }

    if (@$_POST['hd']){
        $checked_hd = 'checked';
    }
}

function get_genres(){
    global $db;
    global $tv_genre_id;
    
    $query = "select * from tv_genre";
    $rs=$db->executeQuery($query);
    $option = '';
    
    while(@$rs->next()){
        $selected = '';
        $arr=$rs->getCurrentValuesAsHash();
        if ($tv_genre_id == $arr['id']){
            $selected = 'selected';
        }
        $option .= "<option value={$arr['id']} $selected>"._($arr['title'])."\n";
    }
    return $option;
}

function get_color($channel){

    if (!$channel['enable_monitoring']){
        return '#5588FF';
    }

    if (time() - strtotime($channel['monitoring_status_updated']) > 3600){
        return '#f4c430';
    }

    if ($channel['monitoring_status'] == 1){
        return 'green';
    }else{
        return 'red';
    }
}

function get_hint($channel){

    if (!$channel['enable_monitoring']){
        return _('monitoring off');
    }

    $diff = time() - strtotime($channel['monitoring_status_updated']);

    if ($diff > 3600){
        return _('more than an hour ago');
    }

    if ($diff < 60){
        return _('less than a minute ago');
    }

    return round($diff/60).' '._('minutes ago');
}

?>
<script type="text/javascript">
function save(){
    var form_ = document.getElementById('form_');
    var cmd = '';
    var name = document.getElementById('name').value;
    if (document.getElementById('cmd')){
        cmd = document.getElementById('cmd').value;
    }
    var id = document.getElementById('id').value;
    //descr = document.getElementById('descr').value
    
    var action = 'add_itv.php?name='+name+'&cmd='+cmd+'&id='+id;
    //alert(action)
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    //alert(action)
    form_.action = action;
    form_.method = 'POST';
    //document.location=action
    form_.submit()
}

function popup(src){
     window.open( src, 'win_'+src, 'width=300,height=200,toolbar=0,location=0,directories=0,menubar=0,scrollbars=0,resizable=1,status=0,fullscreen=0')
}

function delete_logo(id){
    var req = new Subsys_JsHttpRequest_Js();

    req.onreadystatechange = function() {
        if (req.readyState == 4) {

            if (req.responseJS) {

                var resp = req.responseJS.data;
                if(req.responseJS){
                    //set_cat_genres(resp)
                    document.getElementById('logo_block').innerHTML = '';
                }else{
                    alert('<?= _('Error deleting a logo')?>');
                }
            }
        }
    };

    req.caching = false;

    req.open('POST', 'load.php?get=del_tv_logo&id='+id, true);
    req.send({data:'bar'});
}
</script>
<br>
<script type="text/javascript">
    var links = <?= empty($links)?'[]':json_encode($links)?>;
</script>

<a name="form"></a>
<table align="center" class='list'>
<tr>
    <td>
    &nbsp;
    </td>
</tr>
<tr>
    <td>
    <form id="form_" class="itv_form" method="POST" enctype="multipart/form-data">
    <table align="center">
        <tr>
           <td align="right">
            <?= _('Number')?>:
           </td>
           <td>
            <input type="text" name="number" id="number" value="<? echo @$number ?>"  maxlength="3">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Name')?>:
           </td>
           <td>
            <input type="text" name="name" id="name" value="<? echo @htmlspecialchars($name) ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        
        <!--<tr>
           <td align="right" valign="top">
           <?/*= _('Temporary HTTP URL')*/?>:
           </td>
           <td>
            <input name="use_http_tmp_link" id="use_http_tmp_link" type="checkbox" <?/* echo @$checked_http_tmp_link */?> onchange="this.checked ? document.getElementById('wowza_tmp_link_tr').style.display = '' : document.getElementById('wowza_tmp_link_tr').style.display = 'none'" >
            <span id="wowza_tmp_link_tr" style="display: <?/*echo @$checked_http_tmp_link ? '' : 'none' */?>">
                <?/*= _('WOWZA support')*/?>:
                <input name="wowza_tmp_link" id="wowza_tmp_link" type="checkbox" <?/* echo @$checked_wowza_tmp_link */?> >
            </span>
           </td>
        </tr>-->
        <tr>
           <td align="right" valign="top">
           <?= _('Age restriction')?>:
           </td>
           <td>
            <input name="censored" id="censored" type="checkbox" <? echo @$checked ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           HD: 
           </td>
           <td>
            <input name="hd" id="hd" type="checkbox" <? echo @$checked_hd ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Base channel')?>:
           </td>
           <td>
            <input name="base_ch" id="base_ch" type="checkbox" <? echo @$checked_base ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Bonus channel')?>:
           </td>
           <td>
            <input name="bonus_ch" id="bonus_ch" type="checkbox" <? echo @$checked_bonus ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           <?= _('Price')?>:
           </td>
           <td>
            <input name="cost" id="cost" type="text" value="<? echo @$cost ?>" size="5" maxlength="6">
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            <?= _('Genre')?>:
           </td>
           <td>
            <select name="tv_genre_id">
                <option value="0"/>-----------
                <?echo get_genres()?>
            </select>
           </td>
        </tr>

        <? if (Config::get('enable_tv_quality_filter')){ ?>
        <tr>
           <td align="right">
            URL (HQ):
           </td>
           <td>
            <input id="cmd_1" name="cmd_1" size="50" type="text" value="<? echo @$arr['cmd_1'] ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            URL (Medium):
           </td>
           <td>
            <input id="cmd_2" name="cmd_2" size="50" type="text" value="<? echo @$arr['cmd_2'] ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            URL (Low):
           </td>
           <td>
            <input id="cmd_3" name="cmd_3" size="50" type="text" value="<? echo @$arr['cmd_3'] ?>">
           </td>
        </tr>
        <?}else{?>
        <tr>
            <td align="right" valign="top">
                <?= _('Links')?>:
            </td>
            <td class="links_block">

            </td>
        </tr>
        <?}?>

        <tr style="display: none;">
           <td align="right" valign="top">
           WOWZA load balancing:
           </td>
           <td>
            <input name="enable_wowza_load_balancing" id="enable_wowza_load_balancing" value="1" type="checkbox" <? echo @$checked_wowza_load_balancing ?> >
           </td>
        </tr>
        
        <tr>
           <td align="right">
            <?= _('URL for recording (multicast)')?>:
           </td>
           <td>
            <input id="mc_cmd" name="mc_cmd" size="50" type="text" value="<? echo @$mc_cmd ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Enable TV archive')?>:
           </td>
           <td>
            <input name="enable_tv_archive" id="enable_tv_archive" type="checkbox" <? echo @$checked_enable_tv_archive ?> onchange="this.checked ? document.getElementById('storage_name').style.display = '' : document.getElementById('storage_name').style.display = 'none'" >

            <span id="storage_name" style="display: <?echo @$checked_enable_tv_archive ? '' : 'none' ?>">
            <!--Wowza DVR:
            <input name="wowza_dvr" id="wowza_dvr" type="checkbox" <?/* echo @$checked_wowza_dvr */?> >-->
                <select name="storage_name" <?= !empty($name) && !empty($enable_tv_archive) ? 'disabled="disabled"' : '' ?>>
                    <? foreach ($storages as $storage){

                        $selected = '';

                        if (!empty($selected_storage) && $selected_storage == $storage['storage_name']){
                            $selected = 'selected="selected"';
                        }

                    echo '<option value="'.$storage['storage_name'].'" '.$selected.'>'.$storage['storage_name'].'</option>';
                    }?>
                </select>
            </span>
           </td>
        </tr>
        <tr>
            <td align="right">
                <?= _('Allow nPVR')?>:
            </td>
            <td>
                <input name="allow_pvr" id="allow_pvr" type="checkbox" value="1" <? echo @$checked_allow_pvr ?> >
            </td>
        </tr>
        <!--<tr>
           <td align="right">
            <?/*= _('Enable monitoring')*/?>:
           </td>
           <td>
            <input id="enable_monitoring" name="enable_monitoring" type="checkbox" value="1" <?/* echo @$checked_enable_monitoring */?> onchange="this.checked ? document.getElementById('monitoring_url_tr').style.display = '' : document.getElementById('monitoring_url_tr').style.display = 'none'">
           </td>
        </tr>
        <tr id="monitoring_url_tr" style="display:<?/* echo @$checked_enable_monitoring ? '' : 'none' */?>">
           <td align="right">
            <?/*= _('Channel URL for monitoring')*/?>:
           </td>
           <td>
            <input id="monitoring_url" name="monitoring_url" size="50" type="text" value="<?/* echo @$monitoring_url */?>"> * <?/*= _('http only')*/?>
           </td>
        </tr>-->
        <tr>
           <td align="right">
            xmltv id: 
           </td>
           <td>
            <input id="xmltv_id" name="xmltv_id" size="50" type="text" value="<? echo @$xmltv_id ?>">
           </td>
        </tr>
        <tr>
            <td align="right">
                <?= _('EPG correction').', '._('min')?>:
            </td>
            <td>
                <input id="correct_time" name="correct_time" size="50" type="text" value="<? echo @$correct_time ?>">
            </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Service code')?>:
           </td>
           <td>
            <input id="service_id" name="service_id" size="50" type="text" value="<? echo @$service_id ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Volume correction')?> (-20...20):
           </td>
           <td>
            <input id="volume_correction" name="volume_correction" size="50" type="text" value="<? echo @$volume_correction ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            <?= _('Comments')?>:
           </td>
           <td>
            <textarea id="descr"  name="descr" cols="39" rows="5"><? echo @$descr ?></textarea>
           </td>
        </tr>
        <?if (!empty($logo)){?>
        <tr>
            <td align="right"></td>
            <td valign="top" id="logo_block">
                <img src="<?echo Itv::getLogoUriById(intval($_GET['id']))?>" style="float: left;"/><a href="javascript://" onclick="delete_logo(<?echo intval($_GET['id'])?>); return false;"  style="float: left;">[x]</a>
            </td>
        </tr>
        <?}?>
        <tr>
            <td align="right">
                <?= _('Logo')?>:
            </td>
            <td>
                <input type="file" name="logo" id="logo"/>
            </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="<?= _('Save')?>" onclick="save()">&nbsp;<input type="button" value="<?= _('Cancel')?>" onclick="document.location='add_itv.php'">
           </td>
        </tr>
    </table>
    </form>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>