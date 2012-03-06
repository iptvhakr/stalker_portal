<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceVideoGenres extends RESTApiCollection
{
    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiVideoGenreDocument();
    }

    public function getCount(RESTApiRequest $request){
        $genres = new \VideoGenre();
        return count($genres->getAll(true));
    }

    public function get(RESTApiRequest $request){
        $genres = new \VideoGenre();
        $genres->setLocale($request->getLanguage());

        return $this->filter($genres->getAll(true));
    }

    private function filter($genres){

        $genres = array_map(function($genre){

            unset($genre['category_alias']);

            return $genre;

        }, $genres);

        return $genres;
    }
}