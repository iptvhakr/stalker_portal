<?php
/*
    
*/


set_time_limit(0);

include "./common.php";

use Stalker\Lib\Core\Mysql;

$updated_video = 0;
$updated_karaoke = 0;

$not_custom_video = Mysql::getInstance()
    ->from('video')
    ->where(array(
        'protocol!=' => 'custom'
    ))
    ->get();

while($video = $not_custom_video->next()){
    //$timer = microtime(1);
    $master = new VideoMaster();
    $master->getAllGoodStoragesForMediaFromNet($video['id'], 0, true);
    unset($master);
    $updated_video++;
    //echo "Updated video ".round(microtime(1) - $timer, 3)."s: ".$video['path']."\n";
}

$not_custom_karaoke = Mysql::getInstance()
    ->from('karaoke')
    ->where(array(
        'protocol!=' => 'custom'
    ))
    ->get();

while($karaoke = $not_custom_karaoke->next()){
    //$timer = microtime(1);
    $master = new KaraokeMaster();
    $master->getAllGoodStoragesForMediaFromNet($karaoke['id'], 0);
    unset($master);
    $updated_karaoke++;
    //echo "Updated karaoke: ".round(microtime(1) - $timer, 3)."s: ".$karaoke['id']."\n";
}

echo 1;
