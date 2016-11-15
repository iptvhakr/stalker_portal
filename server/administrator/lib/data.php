<?php

session_start();

use Stalker\Lib\Core\Mysql;

function get_data(){
    $get = @$_GET['get'];
    $data = @$_POST['data'];
    $arr = array();

    if ($data){
        switch ($get){
            case 'del_tv_logo':
                {
                    if (!Admin::isEditAllowed('add_itv')){
                        header($_SERVER["SERVER_PROTOCOL"].' 405 Method Not Allowed');
                        echo _('Action "edit" denied for page "add_itv"');
                        exit;
                    }
                    return Itv::delLogoById(intval($_GET['id']));
                }
            case 'vclub_info':
                {
                    $media_id = intval($data);

                    $video = Video::getById($media_id);

                    $path = $video['path'];
                    $rtsp_url = $video['rtsp_url'];
                    
                    if (!empty($rtsp_url)){
                        
                        $result['data'] = array();
                        return $result;
                    }

                    $master = new VideoMaster();
                    $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, 0, true);
                    
                    foreach ($good_storages as $name => $data){
                        $arr[] = array(
                            'storage_name' => $name,
                            'path'         => $path,
                            'series'       => count($data['series']),
                            'files'        => $data['files'],
                            'tv_series'    => $data['tv_series'],
                            'for_moderator' => $data['for_moderator']
                        );
                    }
                    $result['data'] = $arr;
                    return $result;
                    break;
                }
            case 'startmd5sum':
                {
                    $resp = array();
                    if (Admin::isPageActionAllowed('add_video')){
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
                    $good_storages = $master->getAllGoodStoragesForMediaFromNet($media_id, 0, true);
                    
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
                    return $result['data'] = Mysql::getInstance()
                        ->count()
                        ->from('video')
                        ->where(array(
                            'name' => $data
                        ))
                        ->get()
                        ->counter();
                    break;
                }
            case 'org_name_chk':
                {
                    return $result['data'] = Mysql::getInstance()
                        ->count()
                        ->from('permitted_video')
                        ->where(array(
                            'o_name' => $data['o_name'],
                            'year'   => $data['year']
                        ))
                        ->get()
                        ->counter();
                    break;
                }
            case 'get_cat_genres':
                {
                    $category_alias = Mysql::getInstance()
                        ->from('media_category')
                        ->where(array(
                            'id' => $data
                        ))
                        ->get()
                        ->first('category_alias');

                    $genres = Mysql::getInstance()
                        ->from('cat_genre')
                        ->where(array(
                            'category_alias' => $category_alias
                        ))
                        ->orderby('title')
                        ->get()
                        ->all();

                    $genres = array_map(function($genre){

                        return array(
                            'id'     => $genre['id'],
                            'title'  => _($genre['title']),
                        );

                    }, $genres);

                    return array('data' => $genres);

                    break;
                }
        }
    }
}
