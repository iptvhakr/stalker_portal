<?php

include "../common.php";

$mac = $argv[1];
$ch  = $argv[2];

Mysql::getInstance()->update('last_id', array('last_id' => $ch), array('ident' => $mac));