<?php

set_time_limit(0);

sleep(rand(0, 300));

include "./common.php";

$notifications = new NotificationFeed();
$notifications->sync();