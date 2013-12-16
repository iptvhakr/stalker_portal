<?php

include "./common.php";

$response = array(
    'result' => Master::checkAccessToken($_GET['token'])
);

echo json_encode($response);