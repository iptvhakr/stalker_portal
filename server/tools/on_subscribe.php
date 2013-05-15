<?php

include "../common.php";

if (empty($_REQUEST['mac']) || empty($_REQUEST['tariff_id']) || empty($_REQUEST['package_id'])){
    echo '{"status":"ERROR","results":false,"error":"mac and tariff_id required"}';
    exit;
}

//$mac       = $_REQUEST['mac'];
//$tariff_id = $_REQUEST['tariff_id'];


// Success!
echo '{"status":"OK","results":true}';