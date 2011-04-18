<?php
/*
    itv, vclub, karaoke
*/
include "../lib/func.php";

$db = new Database();

echo get_cur_active_playing_type($db);

?>