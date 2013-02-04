<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceKaraoke extends RESTApiCollection
{

    private $manager;
    private $genres_ids;
    private $karaoke_id;
    protected $params_map = array("users" => "users.id", "karaoke-genres" => "karaoke.genre");

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);

        $this->document = new RESTApiKaraokeDocument($this, $this->external_params);
        $this->document->controllers->add(new RESTApiKaraokeLink($this->nested_params));

        $this->manager = new \Karaoke();
    }

    public function setKaraokeId($id){
        $this->karaoke_id = (int) $id;
    }

    private function prepareQuery(RESTApiRequest $request){

        $raw_karaoke = $this->manager->getRawAll();

        $search = $request->getSearch();

        if ($search !== null){
            $raw_karaoke->like(
                array(
                    'karaoke.name'   => '%'.$search.'%',
                    'karaoke.singer' => '%'.$search.'%',
                    'karaoke_genre.title' => '%'.$search.'%'
                ), 'OR');
        }

        if (!empty($this->genres_ids)){
            $raw_karaoke->in('genre_id', $this->genres_ids);
        }

        if (!empty($this->karaoke_id)){
            $raw_karaoke->where(array('karaoke.id' => $this->karaoke_id));
        }

        return $raw_karaoke;
    }

    public function getCount(RESTApiRequest $request){
        return (int) $this->prepareQuery($request)->count()->get()->counter();
    }

    public function get(RESTApiRequest $request){

        $karaoke = $this->prepareQuery($request);
        $karaoke->orderby("name");

        if ($request->getLimit() !== null){
            $karaoke->limit($request->getLimit(), $request->getOffset());
        }

        return $this->filter($karaoke->get()->all());
    }

    public function filter($list){

        $list = array_map(function($karaoke){

            $new_karaoke = array(
                'id'        => $karaoke['id'],
                'name'      => $karaoke['name'],
                'performer' => $karaoke['singer'],
                'genre'     => empty($karaoke['genre']) ? '' : $karaoke['genre']
            );

            if (preg_match("/(\S+:\/\/\S+)/", $karaoke['rtsp_url'], $match)){
                $new_karaoke['url'] = $match[1];
            }else{
                $new_karaoke['url'] = '';
            }

            return $new_karaoke;
        }, $list);

        return $list;
    }
}