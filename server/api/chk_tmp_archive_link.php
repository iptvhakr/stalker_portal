<?php

include "./common.php";

$response = array(
    'result' => TvArchive::checkTemporaryToken($_GET['token'])
);

echo json_encode($response);