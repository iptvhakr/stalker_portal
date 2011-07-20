<?php
set_time_limit(0);

include "../common.php";

$channels = Mysql::getInstance()->from('itv')->where(array('status' => 1))->orderby('number')->get()->all();

$m3u_data = "#EXTM3U\n";

foreach ($channels as $channel){
    $m3u_data .= "#EXTINF:0,".$channel['name']."\n";
    $m3u_data .= $channel['cmd']."\n";
}

file_put_contents(PROJECT_PATH.'/tv.m3u', $m3u_data);

?>
