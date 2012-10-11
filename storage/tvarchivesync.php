<?php

require_once("./common.php");

$archive = new TvArchiveTasks(TASKS_API_URL.STORAGE_NAME);

$tasks = $archive->sync();

if (!is_array($tasks)){
    return false;
}

$recorder = new TvArchiveRecorder();
echo $recorder->startAll($tasks);

?>