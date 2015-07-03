<?php

    include "./common.php";

    $users_online = Mysql::getInstance()->from("users")->count()
        ->where(array(
            'UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout') * 2))
        ->get()->counter();

    Mysql::getInstance()->insert('users_activity', array('users_online' => $users_online));