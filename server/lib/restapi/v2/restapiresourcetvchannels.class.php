<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceTvChannels extends RESTApiCollection
{
    protected $manager;
    protected $params_map = array("users" => "users.id", "tv-genres" => "genre");
    private   $user_id;
    private   $fav_channels  = array();
    private   $user_channels = array();
    private   $favorite_filter_enabled = false;
    private   $genre_id;
    private   $fields_map;
    private   $channel_id;

    public function __construct(array $nested_params, array $external_params){

        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiTvChannelDocument($this, $external_params);
        $this->document->controllers->add(new RESTApiTvChannelLink($this->nested_params));
        $this->document->controllers->add(new RESTApiTvChannelRecord($this->nested_params));
        $this->controllers->add(new RESTApiTvChannelLast($this->nested_params));

        $this->fields_map = array_fill_keys(array('id', "name", "number", "archive", "censored"), true);

        $this->manager = \Itv::getInstance();

        if (!empty($this->nested_params['users.id'])){
            $user_id = $this->nested_params['users.id'];

            $user = \Stb::getById($user_id);

            if (empty($user)){
                throw new RESTNotFound("User not found");
            }

            $this->user_id = $user['id'];
            $this->fav_channels  = $this->manager->getFav($this->user_id);
            $this->user_channels = $this->manager->getAllUserChannelsIdsByUid($this->user_id);
        }

        if (!empty($this->nested_params['genre'])){
            $genres = new \TvGenre();
            $genre = $genres->getById($this->nested_params['genre'], true);

            if (empty($genre)){
                throw new RESTNotFound("Genre not found");
            }

            $this->genre_id = (int) $genre['id'];
        }
    }

    public function setChannelId($id){
        $this->channel_id = (int) $id;
    }

    public function getCount(RESTApiRequest $request){
        $counter = $this->manager->getRawAllUserChannels($this->user_id)->count();

        if ($request->getParam('mark') == 'favorite'){
            $counter->in('id', $this->fav_channels);
        }

        if ($this->genre_id){
            $counter->where(array('tv_genre_id' => $this->genre_id));
        }

        if (!empty($this->channel_id)){
            $counter->where(array('id' => $this->channel_id));
        }

        return (int) $counter->get()->counter();
    }

    public function get(RESTApiRequest $request){

        if ($request->getParam('mark') == 'favorite'){
            $this->favorite_filter_enabled = true;
        }

        $channels = $this->manager->getRawAllUserChannels($this->user_id);

        if ($this->favorite_filter_enabled){
            $channels->in('id', $this->fav_channels);
        }

        if ($request->getLimit() !== null){
            $channels->limit($request->getLimit(), $request->getOffset());
        }

        if ($this->genre_id){
            $channels->where(array('tv_genre_id' => $this->genre_id));
        }

        if (!empty($this->channel_id)){
            $channels->where(array('id' => $this->channel_id));
        }

        return $this->filter($channels->get()->all());
    }

    public function filter($channels){

        $fav_channels  = $this->fav_channels;
        $fields_map    = $this->fields_map;
        $user_channels = $this->user_channels;

        $channels = array_map(function($channel) use ($fav_channels, $fields_map, $user_channels){

            $new_channel = array_intersect_key($channel, $fields_map);

            $new_channel['id']     = (int) $channel['id'];
            $new_channel['number'] = (int) $channel['number'];

            $new_channel['favorite'] = in_array($channel['id'], $fav_channels) ? 1 : 0;

            $new_channel['archive']  = (int) $channel['enable_tv_archive'];
            $new_channel['censored'] = (int) $channel['censored'];
            $new_channel['archive_range'] = (int) $channel['tv_archive_duration'];
            $new_channel['pvr']      = (int) $channel['allow_pvr'];

            if ($channel['enable_monitoring']){
                $new_channel['monitoring_status'] = (int) $channel['monitoring_status'];
            }else{
                $new_channel['monitoring_status'] = 1;
            }

            if (!empty($_SERVER['HTTP_UA_RESOLUTION']) && in_array($_SERVER['HTTP_UA_RESOLUTION'], array(120, 160, 240, 320))){
                $resolution = (int) $_SERVER['HTTP_UA_RESOLUTION'];
            }else{
                $resolution = 320;
            }

            $new_channel['logo'] = \Itv::getLogoUriById($channel['id'], $resolution);

            $urls = \Itv::getUrlsForChannel($channel['id']);

            if (!empty($urls) && $urls[0]['use_http_tmp_link'] == 0 && $urls[0]['use_load_balancing'] == 0){
                $new_channel['url'] = $urls[0]['url'];
            }else{
                $new_channel['url'] = "";
            }

            if (preg_match("/(\S+:\/\/\S+)/", $new_channel['url'], $match)){
                $new_channel['url'] = $match[1];
            }


            return $new_channel;
        }, $channels);

        return $channels;
    }
}