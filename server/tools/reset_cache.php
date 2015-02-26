<?php

include "../common.php";

array_shift($argv);

if (!empty($argv)){
    Cache::getInstance(true)->setInvalidTags($argv);
}else{
    echo "Tags missing!\n";
}

