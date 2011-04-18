<?php

include "../lib/func.php";

$db = new Database();

$mac = $_GET['mac'];
$ch  = $_GET['ch'];

$sql = "update last_id set last_id='".$ch."' where ident='".$mac."'";

$rs = $db->executeQuery($sql);

?>