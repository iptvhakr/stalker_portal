<?php
/*
    itv, vclub, karaoke
*/
include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

echo get_last5min_play($db);
?>