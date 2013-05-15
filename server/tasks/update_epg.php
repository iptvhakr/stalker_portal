<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
set_time_limit(0);

include "./common.php";

$epg = new Epg();

if (isset($_GET['force'])){
    $force = true;
}else{
    $force = false;
}

echo "<pre>";
echo $epg->updateEpg($force);
echo "</pre>";
?>
</body>
</html>