<?php

include "../common.php";

$mac = $_GET['mac'];
$ch  = $_GET['ch'];

Mysql::getInstance()->update('last_id', array('last_id' => $ch), array('ident' => $mac));