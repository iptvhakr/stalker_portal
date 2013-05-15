<?php

namespace Stalker\Lib\Utils;

class FormatM3U extends Format
{
    public function __construct($array){

        $this->formatted = "#EXTM3U\n";

        foreach ($array as $item){

            $this->formatted .= "#EXTINF:0,".$item['number'].'. '.$item['name']."\n";

            if (preg_match("/([^\s]+:\/\/[^\s]+)/", $item['url'], $tmp)){
                $url = $tmp[1];
            }else{
                $url = '';
            }

            $this->formatted .= $url."\n";
        }
    }
}