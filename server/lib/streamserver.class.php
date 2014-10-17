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

    public static function getIdMap(){
        $all_servers = self::getAll();

        $map = array();

        foreach ($all_servers as $server){
            $map[$server['id']] = $server;
        }

        return $map;
    }

    public static function getForLink($link_id){

        $streamer_ids = self::getGoodStreamersIdsForLink($link_id);

        if (empty($streamer_ids)){
            throw new EmptyStreamList();
        }

        $streamers = Mysql::getInstance()
            ->from('streaming_servers')
            ->where(array('status' => 1))
            ->in('id', $streamer_ids)
            ->get()
            ->all();

        if (empty($streamers)){
            throw new EmptyStreamList();
        }

        $streamers = self::filterByCountry($streamers, User::getCountryId());

        if (empty($streamers)){
            throw new NotAvailableForZone();
        }

        $streamers = self::countStats($streamers, true);

        return $streamers;
    }

    private static function filterByCountry($streamers, $country_id){

        $streamer_ids = array();

        $streamers = array_map(function($streamer) use (&$streamer_ids){

            $streamer['countries'] = StreamServer::getCountries($streamer['stream_zone']);
            $streamer_ids[] = $streamer['id'];

            return $streamer;
        }, $streamers);

        $filtered_streamers = array_filter($streamers, function($streamer) use ($country_id){
            return $streamer['stream_zone'] == 0 || array_search($country_id, $streamer['countries']) !== false;
        });

        if (empty($filtered_streamers)){
            $filtered_streamers = Mysql::getInstance()
                ->select('streaming_servers.*, stream_zones.default_zone')
                ->from('streaming_servers')
                ->join('stream_zones', 'stream_zone', 'stream_zones.id', 'LEFT')
                ->where(array('default_zone' => 1))
                ->in('streaming_servers.id', $streamer_ids)
                ->get()
                ->all();
        }

        return $filtered_streamers;
    }

    public static function getCountries($zone_id){
        return Mysql::getInstance()->from('countries_in_zone')->where(array('zone_id' => $zone_id))->get()->all('country_id');
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

    public static function getGoodStreamersIdsForLink($link_id){

        $link = Itv::getLinkById($link_id);

        if (empty($link)){
            return false;
        }

        if ($link['enable_monitoring'] && $link['enable_balancer_monitoring']){
            return Mysql::getInstance()
                ->from('ch_link_on_streamer')
                ->where(array(
                    'link_id' => $link_id,
                    'monitoring_status' => 1
                ))
                ->get()
                ->all('streamer_id');
        }else{
            return Mysql::getInstance()
                ->from('ch_link_on_streamer')
                ->where(array('link_id' => $link_id))
                ->get()
                ->all('streamer_id');
        }
    }

    public static function getStreamersIdMapForLink($link_id){

        $streamers = Mysql::getInstance()
            ->from('ch_link_on_streamer')
            ->where(array('link_id' => $link_id))
            ->get()
            ->all();

        $map = array();

        foreach ($streamers as $streamer){
            $map[$streamer['streamer_id']] = $streamer;
        }

        return $map;
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

class EmptyStreamList extends Exception
{
    protected $code = 'nothing_to_play';
}

class NotAvailableForZone extends Exception
{
    protected $code = 'not_available_for_zone';
}