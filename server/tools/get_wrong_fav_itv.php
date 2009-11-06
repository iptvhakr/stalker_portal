<?php

include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

$sql = "select users.mac, fav_itv.* from fav_itv, users where fav_itv.uid=users.id";
$rs=$db->executeQuery($sql);
while(@$rs->next()){
    $arr=$rs->getCurrentValuesAsHash();
    $tmp_arr = unserialize(base64_decode($arr['fav_ch']));
    if ($tmp_arr[-1]){
        echo "wrong: ".$arr['fav_ch']."\n";
        print_r($tmp_arr);
        echo $arr['mac']."\n";
        unset($tmp_arr[-1]);
        print_r($tmp_arr)."\n";
        $fav_itv = base64_encode(serialize($tmp_arr));
        echo "fixed: ".$fav_itv."\n";
    }
}
?>