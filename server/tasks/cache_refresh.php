<?php
/*
    
*/

set_time_limit(0);

include "./common.php";

$updated_video = 0;
$updated_karaoke = 0;

$not_custom_video = Mysql::getInstance()
    ->from('video')
    ->where(array(
        'protocol!=' => 'custom'
    ))
    ->get();

while($video = $not_custom_video->next()){
    $master = new VideoMaster();
    $master->getAllGoodStoragesForMediaFromNet($video['id'], true);
    unset($master);
    $updated_video++;
}

$not_custom_karaoke = Mysql::getInstance()
    ->from('karaoke')
    ->where(array(
        'protocol!=' => 'custom'
    ))
    ->get();

while($karaoke = $not_custom_karaoke->next()){
    $master = new KaraokeMaster();
    $master->getAllGoodStoragesForMediaFromNet($karaoke['id']);
    unset($master);
    $updated_karaoke++;
}

echo 1;
