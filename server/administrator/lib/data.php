<?php
session_start();

function get_data(){
    $get = @$_GET['get'];
    $data = @$_POST['data'];
    $db = new Database(DB_NAME);
    $arr = array();
    
    if ($data){
        switch ($get){
            case 'singer':
                {
                    $sql = "select * from singer where singer like '$data%' order by singer";
                    break;
                }
            case 'album':
                {
                    $sql = "select * from album where singer_id=".intval($data)." order by year";
                    break;
                }
            case 'new_singer':
                {
                    $sql = "select * from singer where singer='$data'";
                    $rs=$db->executeQuery($sql);
                    $count_row = $rs->getRowCount();
                    $lang = get_str_lang($data);
                    if ($count_row > 0){
                        $result['error'] = 1;
                    }else{
                        $path = transliterate($data);
                        check_audio_path($path);
                        $sql = "insert into singer (singer, path, lang, addtime) value ('$data', '$path', $lang, NOW())";
                        $result['error'] = 0;
                    }
                    break;
                }
            case 'new_album':
                {
                    $sql = "select * from album where name='{$_POST['name']}' and singer_id={$_POST['singer_id']}";
                    $rs=$db->executeQuery($sql);
                    $count_row = $rs->getRowCount();
                    if ($count_row){
                        $result['error'] = 1;
                    }else{
                        if ($_POST['year']){
                            $year = $_POST['year'];
                        }else{
                            $year = '0';
                        }
                        $sql = "insert into album (name, singer_id, year, addtime) value ('{$_POST['name']}', {$_POST['singer_id']}, '$year', NOW())";
                        $result['error'] = 0;
                    }
                    break;
                }
            case 'vclub_info':
                {
                    $media_id = intval($data);
                    
                    $sql = "select * from video where id=$media_id";
                    $rs  = $db->executeQuery($sql);
                    $path = $rs->getValueByName(0, 'path');
                    $rtsp_url = $rs->getValueByName(0, 'rtsp_url');
                    
                    if (!empty($rtsp_url)){
                        
                        $result['data'] = array();
                        return $result;
                    }
                    
                    $sql = '';
                    
                    $master = new VideoMaster();
                    $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, true);
                    
                    if(count($good_storages) > 0){
                        set_video_status($media_id, 1);
                    }else{
                        set_video_status($media_id, 0);
                    }
                    
                    foreach ($good_storages as $name => $data){
                        $arr[] = array(
                            'storage_name' => $name,
                            'path'         => $path,
                            'series'       => count($data['series']),
                            'files'        => $data['files']
                        );
                    }
                    $result['data'] = $arr;
                    return $result;
                    break;
                }
            case 'startmd5sum':
                {
                    $resp = array();
                    if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){
                        $master = new VideoMaster();
                        try {
                            $master->startMD5Sum($data['storage_name'], $data['media_name']);
                        }catch (Exception $exception){
                            $resp['error'] = $exception->getMessage();
                        }
                        return $resp;
                    }else{
                        $resp['error'] = 'У Вас нет прав на это действие';
                        return $resp;
                    }
                    break;
                }
            case 'karaoke_info':
                {
                    $media_id = intval($data);

                    $master = new KaraokeMaster();
                    $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id);
                    
                    if(count($good_storages) > 0){
                        set_karaoke_status($media_id, 1);
                    }else{
                        set_karaoke_status($media_id, 0);
                    }
                    
                    foreach ($good_storages as $name => $data){
                        $arr[] = array(
                            'storage_name' => $name,
                            'file'         => $media_id.'.mpg',
                        );
                    }
                    $result['data'] = $arr;
                    return $result;
                    
                    break;
                }
            case 'chk_name':
                {
                    $sql  = "select * from video where name='$data'";
                    $rs   = $db->executeQuery($sql);
                    $resp = $rs->getRowCount();
                    return $result['data'] = $resp;
                    break;
                }
            case 'org_name_chk':
                {
                    $o_name = $data['o_name'];
                    $year   = $data['year'];
                    $sql  = "select * from permitted_video where o_name='$o_name' and year=$year";
                    $rs   = $db->executeQuery($sql);
                    $resp = $rs->getRowCount();
                    return $result['data'] = $resp;
                    break;
                }
            case 'get_cat_genres':
                {
                    $sql = "select * from media_category where id=$data";
                    $rs  = $db->executeQuery($sql);
                    $category_alias = $rs->getValueByName(0, 'category_alias');

                    $sql = "select * from cat_genre where category_alias='$category_alias' order by title";
                    $rs  = $db->executeQuery($sql);
                    $sql = '';
                    break;
                }
        }
    }
    if(@$sql){
        echo $sql;
        $rs=$db->executeQuery($sql);
        $result['total_items'] = @$total_items;
    }
    while(@$rs->next()){
        switch ($get){
            /*case 'vclub_info':
                {
                    var_dump($good_storages);
                    foreach ($good_storages as $name => $data){
                        $arr[] = array(
                            'storage_name' => $name,
                            //'path'         => $data['path'],
                            'series'       => count($data['series']),
                        );
                    }
                    break;
                }*/
            /*case 'karaoke_info':
                {
                    for ($i=0; $i<count($master->good_storages); $i++){
                        $arr[] = array(
                            'file'         => $media_id.'.mpg',
                            'storage_name' => $master->good_storages[$i]['storage_name'],
                        );
                    }
                    break;
                }*/
            case 'singer':
                {
                    $arr[] = array(
                        'id'     => $rs->getCurrentValueByName('id'),
                        'singer' => $rs->getCurrentValueByName('singer'),
                    );
                    break;
                }
            case 'album':
                {
                    $arr[] = array(
                        'id'     => $rs->getCurrentValueByName('id'),
                        'name'   => $rs->getCurrentValueByName('name'),
                        'year'   => $rs->getCurrentValueByName('year'),
                    );
                    break;
                }
            case 'get_cat_genres':
                {
                    $arr[] = array(
                        'id'     => $rs->getCurrentValueByName('id'),
                        'title'  => $rs->getCurrentValueByName('title'),
                    );
                    break;
                }
            case 'new_singer':
                {
                    break;
                }
            case 'new_album':
                {
                    break;
                }
        }
    }
    $result['data'] = $arr;
    return $result;
}

function check_audio_path($path){    
    if (is_dir(AUDIO_STORAGE_DIR.$path)) {
    }else{
        umask(0);
        mkdir(AUDIO_STORAGE_DIR.$path, 0777);
    }
}
?>