<?php

include "../conf_serv.php";
include "../lib/func.php";

set_time_limit(0);

$on = 0;
$off = 0;
$db = new Database(DB_NAME);

$sql = "select * from video";
$rs  = $db->executeQuery($sql);

while(@$rs->next()){
    $dir = $rs->getCurrentValueByName('path');
    $id  = $rs->getCurrentValueByName('id');
    //var_dump($dir);
    //var_dump($master);
    $master = new Master;
    //$dir
    $master->getAllGoodStorages($dir, 'vclub');
    //var_dump($master->good_storages);
    if(count($master->good_storages) > 0){
        set_video_status($id, 1);
        $on++;
    }else{
        set_video_status($id, 0);
        $off++;
    }
    //$master->__destruct();
    unset($master);
}

echo "Включено $on фильмов, выключено $off фильмов"
?>