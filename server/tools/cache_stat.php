<?php

include __DIR__."/../common.php";

echo "hit:".Cache::getInstance()->getHits()." miss:".Cache::getInstance()->getMisses()."\n";