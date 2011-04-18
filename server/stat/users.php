<?php
/*
    online, offline
*/
include "../lib/func.php";

$db = new Database();

echo get_cur_users($db);

?>