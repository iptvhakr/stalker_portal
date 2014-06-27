<?php

require_once("./common.php");

$archive = new TvArchiveTasks();
$archive->setApiUrl(API_URL.'tv_archive/'.STORAGE_NAME);

$tasks = $archive->sync();

if (!is_array($tasks)){
    return false;
}

$recorder = new TvArchiveRecorder();
echo $recorder->startAll($tasks);

?>