<?php
set_time_limit(0);

ob_start();

include "../common.php";

use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Mysql;

if (!Config::getSafe('enable_m3u_file', false)){
    header("HTTP/1.0 404 Not Found");
    exit;
}

$channels = Mysql::getInstance()->from('itv')->where(array('status' => 1))->orderby('number')->get()->all();

$m3u_data = "#EXTM3U\n";

foreach ($channels as $channel){
    $m3u_data .= "#EXTINF:0,".$channel['number'].'. '.$channel['name']."\n";

    if (!empty($_GET['origin'])){
        $m3u_data .= $channel['cmd']."\n";
    }else{
        if (preg_match("/([^\s]+:\/\/[^\s]+)/", $channel['cmd'], $tmp)){
            $cmd = $tmp[1];
        }else{
            $cmd = '';
        }

        $cmd = str_replace(array('rtp://', 'udp://'), array('rtp://@', 'udp://@'), $cmd);

        $m3u_data .= $cmd."\n";
    }
}

if (is_file(PROJECT_PATH.'/tv.m3u') && is_writable(PROJECT_PATH.'/tv.m3u') || is_writable(PROJECT_PATH)){
    @file_put_contents(PROJECT_PATH.'/tv.m3u', $m3u_data);
}

header('Content-Type: audio/mpegurl');
header('Content-Disposition: attachment; filename="playlist.m3u"');

echo $m3u_data;

?>
