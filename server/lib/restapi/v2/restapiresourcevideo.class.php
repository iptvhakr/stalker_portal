<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceVideo extends RESTApiCollection
{
    protected $document;
    private   $manager;
    protected $params_map = array("users" => "users.id", "video-categories" => "video.category",
        "video-genres" => "video.genre");
    private   $favorites = array();
    private   $not_ended = array();
    private   $video_id;
    private   $genres_ids;

    public function __construct(array $nested_params, array $external_params){

        parent::__construct($nested_params, $external_params);

        $this->document = new RESTApiVideoDocument($this, $this->external_params);
        $this->document->controllers->add(new RESTApiVideoLink($this->nested_params));
        $this->document->controllers->add(new RESTApiVideoNotEnded($this->nested_params));

        $this->fields_map = array_fill_keys(array('id', "name", "description", "director", "actors", "year",
            "censored", "added", "genres", "genres_ids", "cover", "hd"), true);
        $this->manager = new \Video();

        if (!empty($this->nested_params['users.id'])){
            $user_id = $this->nested_params['users.id'];

            $user = \Stb::getById($user_id);

            if (empty($user)){
                throw new RESTNotFound("User nor found");
            }

            $user_obj = \User::getInstance();
            $this->favorites = $user_obj->getVideoFavorites();
            $this->not_ended = $user_obj->getNotEndedVideo();
        }

        if (!empty($this->nested_params['video.category']) && empty($this->nested_params['video.genre'])){
            $category_id = $this->nested_params['video.category'];

            $genre  = new \VideoGenre();
            $genres = $genre->getByCategoryId($category_id, true);

            if (empty($genres)){
                throw new RESTNotFound("Genres list is empty");
            }

            $this->genres_ids = array_map(function($genre){
                return (int) $genre['_id'];
            }, $genres);

        }else if (!empty($this->nested_params['video.genre']) && empty($this->nested_params['video.category'])){

            $genre  = new \VideoGenre();
            $genres = $genre->getById($this->nested_params['video.genre'], true);

            if (empty($genres)){
                throw new RESTNotFound("Genres list is empty");
            }

            $genres = array_map(function($genre){
                return (int) $genre['id'];
            }, $genres);

            $this->genres_ids = $genres;
        }else if(!empty($this->nested_params['video.genre']) && !empty($this->nested_params['video.category'])){
            $genre  = new \VideoGenre();
            $genres = $genre->getByIdAndCategory($this->nested_params['video.genre'], $this->nested_params['video.category'], true);

            if (empty($genres)){
                throw new RESTNotFound("Genres list is empty");
            }

            $this->genres_ids = array($genres['id']);
        }
    }

    public function setVideoId($id){
        $this->video_id = (int) $id;
    }

    private function prepareQuery(RESTApiRequest $request){

        $raw_videos = $this->manager->getRawAll();

        $search = $request->getSearch();

        if ($search !== null){
            $raw_videos->like(
                array(
                    'video.name' => '%'.$search.'%',
                    'o_name'     => '%'.$search.'%',
                    'actors'     => '%'.$search.'%',
                    'director'   => '%'.$search.'%',
                    'year'       => '%'.$search.'%'
                ), 'OR');
        }

        if ($request->getParam('mark') == 'favorite'){
            $raw_videos->in('id', $this->favorites);
        }elseif ($request->getParam('mark') == 'not_ended'){
            $raw_videos->in('id', array_keys($this->not_ended));
        }

        if (!empty($this->video_id)){
            $raw_videos->where(array('id' => $this->video_id));
        }

        if (!empty($this->genres_ids)){
            $raw_videos->group_in(
                array(
                    'cat_genre_id_1' => $this->genres_ids,
                    'cat_genre_id_2' => $this->genres_ids,
                    'cat_genre_id_3' => $this->genres_ids,
                    'cat_genre_id_4' => $this->genres_ids,
                ),
                'OR');
        }

        return $raw_videos;
    }

    public function getCount(RESTApiRequest $request){

        $counter = $this->prepareQuery($request);

        return (int) $counter->count()->get()->counter();
    }

    public function get(RESTApiRequest $request){

        $this->manager->setLocale($request->getLanguage());

        $videos = $this->prepareQuery($request);

        if ($request->getLimit() !== null){
            $videos->limit($request->getLimit(), $request->getOffset());
        }

        if ($request->getParam('sortby') == "name"){
            if (!$request->getLanguage() || $request->getLanguage() == 'ru'){
                $videos->orderby("name");
            }else{
                $videos->orderby("o_name");
            }
        }else{
            $videos->orderby("added", 'DESC');
        }

        return $this->filter($videos->get()->all());
    }

    private function filter($videos){

        $videos = $this->manager->filterList($videos);

        $fields_map = $this->fields_map;
        $favorites  = $this->favorites;
        $not_ended  = $this->not_ended;
        $user_id    = $this->nested_params['users.id'];

        $genre  = new \VideoGenre();
        $genres_map = $genre->getIdMap();

        $videos = array_map(function($video) use ($fields_map, $favorites, $not_ended, $genres_map, $user_id){

            $new_video = array_intersect_key($video, $fields_map);

            $genre_ids = $new_video['genres_ids'];

            $new_video['genres_ids'] = array();

            foreach ($genre_ids as $genre_id){
                if (!empty($genres_map[$genre_id])){
                    $new_video['genres_ids'][] = $genres_map[$genre_id];
                }
            }

            $new_video['added']         = intval(strtotime($video['added']));
            $new_video['original_name'] = $video['o_name'];
            $new_video['hd']            = (int) $video['hd'];
            $new_video['rating_kinopoisk'] = (float) $video['rating_kinopoisk'];
            $new_video['rating_imdb']   = (float) $video['rating_imdb'];

            $series = unserialize($video['series']);
            $new_video['series'] = ($series !== false) ? $series : array();

            $new_video['favorite']  = in_array($video['id'], $favorites) ? 1 : 0;

            $new_video['not_ended'] = !empty($not_ended[$video['id']]) ? 1 : 0;

            $new_video['downloadable'] = (int) in_array('downloads', \Stb::getAvailableModulesByUid($user_id));

            if ($new_video['not_ended']){

                if (!empty($new_video['series'])){
                    $new_video['not_ended_episode'] = $not_ended[$video['id']]['series'];
                }

                $new_video['end_time'] = $not_ended[$video['id']]['end_time'];
            }

            $new_video['url'] = empty($video['rtsp_url']) ? '' : $video['rtsp_url'];

            if (preg_match("/(\S+:\/\/\S+)/", $new_video['url'], $match)){
                $new_video['url'] = $match[1];
            }

            return $new_video;
        }, $videos);

        return $videos;
    }
}