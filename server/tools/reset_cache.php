<?php

include "../common.php";

use Stalker\Lib\Core\Cache;

array_shift($argv);

if (!empty($argv)){
    Cache::getInstance(true)->setInvalidTags($argv);
}else{
    echo "Tags missing!\n";
}

