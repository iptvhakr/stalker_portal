<?php
set_time_limit(0);

include "../conf_serv.php";
include "../lib/func.php";

$epg = new Epg();
echo "<pre>";
echo $epg->updateEpg();
echo "</pre>";
?>