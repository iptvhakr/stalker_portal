<?php

include "../conf_serv.php";
include "../common.php";

echo 'api_callback('.json_encode(Mysql::getInstance()->from('testers')->where(array('status' => 1))->get()->all('mac')).')';

?>