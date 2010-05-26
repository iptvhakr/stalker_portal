<?php

include "../common.php";
include "../conf_serv.php";

echo 'api_callback('.json_encode(Mysql::getInstance()->from('testers')->where(array('status' => 1))->get()->all('mac')).')';

?>