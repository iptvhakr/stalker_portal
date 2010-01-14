<?php
/*
    
*/
include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

$updated_video = 0;
$updated_karaoke = 0;

$sql = "select * from video";

$rs = $db->executeQuery($sql);

while(@$rs->next()){
    $master = new VideoMaster();
    $master->getAllGoodStoragesForMediaFromNet($rs->getCurrentValueByName('id'));
    unset($master);
    $updated_video++;
}


$sql = "select * from karaoke";

$rs = $db->executeQuery($sql);

while(@$rs->next()){
    $master = new KaraokeMaster();
    $master->getAllGoodStoragesForMediaFromNet($rs->getCurrentValueByName('id'));
    unset($master);
    $updated_karaoke++;
}

echo 1

?>