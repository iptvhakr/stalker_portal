<?php
include "./common.php";

ob_start();
$response = array();

try{
    if ($_GET['get'] == 'kinopoisk_info'){
        $response['result'] = Kinopoisk::getInfoByName($_GET['oname']);
    }else if ($_GET['get'] == 'kinopoisk_rating'){
        $response['result'] = Kinopoisk::getRatingByName($_GET['oname']);
    }
}catch (KinopoiskException $e){
    echo $e->getMessage();

    $logger = new Logger();
    $logger->setPrefix("kinopoisk_");

    // format: [date] - error_message - [base64 encoded response];
    $logger->error(sprintf("[%s] - %s - \"%s\"\n",
        date("r"),
        $e->getMessage(),
        base64_encode($e->getResponse())
    ));
}

$output = ob_get_contents();
ob_end_clean();

if ($output){
    $response['output'] = $output;
}

echo json_encode($response);
