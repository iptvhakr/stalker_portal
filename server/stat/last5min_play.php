<?php
/*
    itv, vclub, karaoke
*/
include "../lib/func.php";

$db = new Database();

echo get_last5min_play($db);
?>