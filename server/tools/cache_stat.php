<?php

include __DIR__."/../common.php";

use Stalker\Lib\Core\Cache;

echo "hit:".Cache::getInstance()->getHits()." miss:".Cache::getInstance()->getMisses()."\n";