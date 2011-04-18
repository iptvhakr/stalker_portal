<?php

include "../common.php";

echo 'api_callback('.json_encode(Mysql::getInstance()->from('users')->where(array('hd' => 1))->get()->all('mac')).')';

?>