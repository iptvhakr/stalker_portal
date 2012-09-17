<?php

class StreamServer
{

    public static function getById($id){
        return Mysql::getInstance()
            ->from('streaming_servers')
            ->where(array('id' => $id))
            ->get()
            ->first();
    }

    public static function getAllActive($with_load = false){
        $streamers = Mysql::getInstance()
            ->from('streaming_servers')
            ->where(array('status' => 1))
            ->orderby('name')
            ->get()
            ->all();

        if ($with_load){
            return self::countStats($streamers);
        }else{
            return $streamers;
        }
    }

    public static function getAll(){
        return Mysql::getInstance()
            ->from('streaming_servers')
            ->orderby('name')
            ->get()
            ->all();
    }

    public static function getForLink($link_id){

        $streamer_ids = self::getStreamersIdsForLink($link_id);

        if (empty($streamer_ids)){
            return null;
        }

        $streamers = Mysql::getInstance()
            ->from('streaming_servers')
            ->where(array('status' => 1))
            ->in('id', $streamer_ids)
            ->get()
            ->all();

        $streamers = self::countStats($streamers, true);

        return $streamers;
    }

    private static function countStats($streamers, $sort_by_load = false){

        $streamers = array_map(function($streamer){

            $streamer['sessions'] = StreamServer::getStreamerSessions($streamer['id']);
            $streamer['load']     = StreamServer::getLoad($streamer['id'], $streamer['sessions']);

            return $streamer;
        }, $streamers);

        if ($sort_by_load){
            $streamers = self::sortByLoad($streamers);
        }

        return $streamers;
    }

    public static function getStreamersIdsForLink($link_id){

        return Mysql::getInstance()
            ->from('ch_link_on_streamer')
            ->where(array('link_id' => $link_id))
            ->get()
            ->all('streamer_id');
    }

    public static function getLoad($streamer_id, $sessions = null){

        $streamer = self::getById($streamer_id);

        if ($streamer['max_sessions'] > 0){

            if ($sessions === null){
                $sessions = self::getStreamerSessions($streamer_id);
            }

            return $sessions / $streamer['max_sessions'];
        }
        return 1;
    }

    public static function getStreamerSessions($streamer_id){

        $sessions = Mysql::getInstance()->count()->from('users')
            ->where(array(
                'now_playing_type' => 1,
                'UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout')*2,
                'now_playing_streamer_id' => $streamer_id
            ))
            ->get()
            ->counter();

        return $sessions;
    }

    private static function sortByLoad($streamers){

        if (!empty($streamers)){

            foreach ($streamers as $name => $streamer) {
                $load[$name] = $streamer['load'];
            }

            array_multisort($load, SORT_ASC, SORT_NUMERIC, $streamers);
        }

        return $streamers;
    }
}