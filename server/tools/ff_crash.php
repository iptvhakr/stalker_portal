<?php

include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);
$mac = @$_GET['mac'];

$sql = "insert into loading_fail (mac, added, ff_crash) value ('$mac', NOW(), 1)";
$rs=$db->executeQuery($sql);

?>