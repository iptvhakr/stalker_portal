<?php

include "../lib/func.php";

$db = new Database();
$mac = @$_GET['mac'];

$sql = "insert into loading_fail (mac, added, ff_crash) value ('$mac', NOW(), 1)";
$rs=$db->executeQuery($sql);

?>