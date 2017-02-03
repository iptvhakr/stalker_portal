<?php

require "../common.php";

use \Stalker\Lib\Core\Mysql;

if (!isset($argv[1]) || $argv[1] == '--help'){
    echo "Usage: php ./m3u_to_radio.php [M3U FILE]\n";
    exit;
}

$dir = dirname(__FILE__);

$inputFileName = realpath($dir.'/'.$argv[1]);

if (!$inputFileName){
    echo "File $argv[1] not found\n";
    exit;
}

$file = file($inputFileName);

$result = array();

foreach ($file as $line) {

    if (strpos($line, '#') === 0 && strpos($line, ',') > 0){
        list($foo, $name) = explode(',', $line);
        $name = trim($name);
    }elseif (strpos($line, '#') === false){
        $url = trim($line);
        if (isset($name)){
            echo 'Found '.$name.' with url: '.$url."\n";
            $result[$name] = $url;
        }
    }
}

$number = $max_number = (int) Mysql::getInstance()->select('max(number) as max_number')
    ->from('radio')->get()->first('max_number');

foreach ($result as $name => $url){
    $number++;
    Mysql::getInstance()->insert('radio', array(
        'number' => $number,
        'name'   => $name,
        'cmd'    => 'ifm ' . $url,
        'status' => 1
    ));
}
