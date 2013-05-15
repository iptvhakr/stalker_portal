<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiVideoLink extends RESTApiTvChannelLink
{
    protected $name = 'link';
    private   $params;

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request, $params){

        /*var_dump($params);*/

        if (is_array($params) && (count($params) != 4 || $params[1] != 'episodes')){
            throw new RESTBadRequest("Bad params");
        }

        $episode = 0;

        if (is_array($params)){
            $video_id = (int) $params[0];
            $episode  = (int) $params[2];
        }else{
            $video_id = (int) $params;
        }

        $video = \Vod::getInstance();

        try{
            $url = $video->getUrlByVideoId($video_id, $episode);
        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        if (preg_match("/(\S+:\/\/\S+)/", $url, $match)){
            $url = $match[1];
        }

        return $url;
    }
}