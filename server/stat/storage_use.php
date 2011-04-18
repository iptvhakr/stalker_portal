<?php
/*
    online, offline
*/
include "../lib/func.php";

$db = new Database();

echo get_storage_use($db);

?>