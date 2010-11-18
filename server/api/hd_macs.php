<?php

include "../common.php";
include "../conf_serv.php";

echo 'api_callback('.json_encode(Mysql::getInstance()->from('users')->where(array('hd' => 1))->get()->all('mac')).')';

?>