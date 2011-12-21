<?php
/*
    
*/

set_time_limit(0);

include "../common.php";

$db = new Database();

$updated_video = 0;
$updated_karaoke = 0;

$sql = "select * from video where protocol!='custom'";

$rs = $db->executeQuery($sql);

while(@$rs->next()){
    $master = new VideoMaster();
    $master->getAllGoodStoragesForMediaFromNet($rs->getCurrentValueByName('id'), true);
    unset($master);
    $updated_video++;
}


$sql = "select * from karaoke where protocol!='custom'";

$rs = $db->executeQuery($sql);

while(@$rs->next()){
    $master = new KaraokeMaster();
    $master->getAllGoodStoragesForMediaFromNet($rs->getCurrentValueByName('id'));
    unset($master);
    $updated_karaoke++;
}

echo 1

?>