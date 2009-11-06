<?php
/*
    online, offline
*/
include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

echo get_cur_users($db);

?>