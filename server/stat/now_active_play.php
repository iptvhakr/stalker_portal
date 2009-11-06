<?php
/*
    itv, vclub, karaoke
*/
include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

echo get_cur_active_playing_type($db);

?>