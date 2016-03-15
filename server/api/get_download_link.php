<?php

include "./common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;

if (empty($_GET['lid'])){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    exit;
}

$link = Mysql::getInstance()->from('download_links')->where(array('link_hash' => $_GET['lid']))->get()->first();

if (empty($link)){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    exit;
}

$user = User::getInstance((int) $link['uid']);

ob_start();

if ($link['type'] == 'tv_archive'){

    $tv_archive = new TvArchive();

    try{
        $url = $tv_archive->getUrlByProgramId($link['media_id']);
    }catch(\Exception $e){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    if ($url){
        header("Location: ".$url);
        ob_end_clean();
        exit;
    }

} elseif ($link['type'] == 'vclub'){

    $video = Vod::getInstance();

    try{
        $url = $video->getUrlByVideoId($link['media_id'], (int) $link['param1']);
    }catch(\Exception $e){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    if (preg_match('/(\S+:\/\/\S+)/', $url, $match)){
        $url = $match[1];
    }

    if ($url){
        header("Location: ".$url);
        ob_end_clean();
        exit;
    }

} elseif ($link['type'] == 'pvr'){

    $user = Stb::getById((int) $link['uid']);

    if (empty($user)){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    $pvr = new RemotePvr();

    $recording = $pvr->getById($link['media_id']);

    if (empty($recording)){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    if ($recording['uid'] != $link['uid']){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    try{
        $url = $pvr->getUrlByRecId($link['media_id']);
    }catch(\Exception $e){
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        exit;
    }

    if (preg_match('/(\S+:\/\/\S+)/', $url, $match)){
        $url = $match[1];
    }

    if ($url){
        header("Location: ".$url);
        ob_end_clean();
        exit;
    }
}

header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
exit;
