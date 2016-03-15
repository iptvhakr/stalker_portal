<?php

include "../common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;

if (!Config::getSafe('kinopoisk_rating', true)){
    _log('Notice: kinopoisk rating disabled');
    return;
}

$movies = Mysql::getInstance()
    ->from('video')
    ->where(
    array(
        'accessed' => 1,
        'status' => 1,
        'rating_last_update<' => date("Y-m-d H:i:s", time() - 30*24*3600)
    ))
    ->get()
    ->all();

foreach ($movies as $movie){

    try{

        if (!empty($movie['kinopoisk_id'])){
            $rating = Kinopoisk::getRatingById($movie['kinopoisk_id']);
        }else{
            $rating = Kinopoisk::getRatingByName($movie['o_name']);
        }

    }catch (KinopoiskException $e){

        _log('Error: '.$movie['path'].' ('.$movie['id'].') - '.$e->getMessage());

        $logger = new Logger();
        $logger->setPrefix("kinopoisk_");

        // format: [date] - error_message - [base64 encoded response];
        $logger->error(sprintf("[%s] - %s - \"%s\"\n",
            date("r"),
            $e->getMessage(),
            base64_encode($e->getResponse())
        ));

        continue;
    }

    if ($rating && !empty($rating['kinopoisk_id']) && !empty($rating['rating_kinopoisk']) && $rating['rating_kinopoisk'] != $movie['rating_kinopoisk']){

        Mysql::getInstance()->update('video',
            array(
                'kinopoisk_id'           => $rating['kinopoisk_id'],
                'rating_kinopoisk'       => empty($rating['rating_kinopoisk']) ? '' : $rating['rating_kinopoisk'],
                'rating_count_kinopoisk' => empty($rating['rating_count_kinopoisk']) ? '' : $rating['rating_count_kinopoisk'],
                'rating_imdb'            => empty($rating['rating_imdb']) ? '' : $rating['rating_imdb'],
                'rating_count_imdb'      => empty($rating['rating_count_imdb']) ? '' : $rating['rating_count_imdb'],
                'rating_last_update'     => 'NOW()'
            ),
            array(
                'id' => $movie['id']
            ));

        _log('Update: movie '.$movie['path'].' ('.$movie['id'].')');
    }else{
        _log('Ignore: movie '.$movie['path'].' ('.$movie['id'].') rating updated');
    }

    sleep(1);
}

function _log($str){
    echo $str."\n";
}