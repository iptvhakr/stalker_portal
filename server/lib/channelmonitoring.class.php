<?php

class ChannelMonitoring
{

    public static function startAll(){

        $channels = Mysql::getInstance()->from('itv')->where(array('enable_monitoring' => 1))->get()->all();

        foreach ($channels as $channel){
            $url = self::getUrl($channel['monitoring_url']);

            echo "Start check id: ".$channel['id']."\n";
            echo "Url: ".$url."\n";

            $status = self::check($url);

            //if (!!$channel['monitoring_status'] != $status){
                self::setStatus($channel['id'], intval($status));
            //}

            echo "Done id: ".$channel['id'].", status: ".intval($status)."\n\n";
        }
    }

    private static function setStatus($ch_id, $monitoring_status){

        return Mysql::getInstance()->update('itv', array('monitoring_status' => $monitoring_status, 'monitoring_status_updated' => 'NOW()'), array('id' => $ch_id));
    }

    public static function check($url){

        if (empty($url)){
            return false;
        }

        //if (strpos($url, 'udp://') !== false || strpos($url, 'rtp://') !== false){
        if (false){
            //exec("cvlc ".$url." --sout '#duplicate{dst=std{access=file,mux=ts,dst=\"/tmp/ch_mon.mpg\"}}' vlc://quit 1>/dev/null 2>&1 & sleep 10; kill $!; ls -l /tmp/ch_mon.mpg | cut -f 5 -d ' ' && rm /tmp/ch_mon.mpg", $out);

            echo "rtp\n";

            preg_match('/:\/\/([\d\.]+):(\d+)/', $url, $arr);

            $ip   = $arr[1];
            $port = $arr[2];

            exec("python ".PROJECT_PATH."/../storage/dumpstream -a".$ip." -p ".$port." > /tmp/ch_mon.mpg 2>/dev/null & sleep 5; kill $!; ls -l /tmp/ch_mon.mpg | cut -f 5 -d ' ' && rm /tmp/ch_mon.mpg", $out);
        }else if (strpos($url, 'http://') !== false){
            echo "http\n";
            exec("wget -q -O /tmp/ch_mon.mpg ".$url." >/dev/null 2>&1 & sleep 5; kill $!; ls -l /tmp/ch_mon.mpg | cut -f 5 -d ' ' && rm /tmp/ch_mon.mpg", $out);
        }else{
            return false;
        }

        if (empty($out)){
            return false;
        }

        var_dump($out);

        $size = intval($out[0]);

        return !!$size;
    }

    private static function getUrl($cmd){

        if (preg_match("/([^\s]+:\/\/[^\s]+)/", $cmd, $tmp)){
            $url = $tmp[1];
            /*$url = preg_replace(array('/rtp:\/\//',  '/udp:\/\//'),
                                array('rtp://@', 'udp://@'), $url);*/
            return $url;
        }

        return false;
    }
}

?>