<?php

namespace Stalker\Lib\RESTAPI\v2;

use Stalker\Lib\Core\Mysql;

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

        $has_files = (int) Mysql::getInstance()
            ->from('video_series_files')
            ->where(array(
                'video_id' => $video_id
            ))
            ->count()
            ->get()
            ->counter();

        try{
            $file_id = 0;

            if ($has_files){

                if (!$episode){
                    $file_id = (int) Mysql::getInstance()
                        ->from('video_series_files')
                        ->where(array(
                            'video_id'  => $video_id,
                            'file_type' => 'video'
                        ))
                        ->get()->first('id');
                }else{
                    $season = Mysql::getInstance()
                        ->from('video_season')
                        ->where(array('video_id' => $video_id))
                        ->orderby('season_number')
                        ->get()->first();

                    if ($season){
                        $episode_item = Mysql::getInstance()
                            ->from('video_season_series')
                            ->where(array(
                                'season_id'     => $season['id'],
                                'series_number' => $episode
                            ))
                            ->orderby('series_number')
                            ->get()->first();

                        if ($episode_item){
                            $file_id = (int) Mysql::getInstance()
                                ->from('video_series_files')
                                ->where(array(
                                    'video_id'  => $video_id,
                                    'series_id' => $episode_item['id'],
                                    'file_type' => 'video'
                                ))
                                ->get()->first('id');
                        }
                    }
                }
            }

            $url = $video->getUrlByVideoId($video_id, $episode, '', $file_id);

        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        if (preg_match("/(\S+:\/\/\S+)/", $url, $match)){
            $url = $match[1];
        }

        return $url;
    }
}