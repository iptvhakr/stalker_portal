<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceVideoGenres extends RESTApiCollection
{

    protected $params_map = array("video-categories" => "video.category");
    protected $category;

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiVideoGenreDocument();

        if (!empty($this->nested_params['video.category'])){
            $category = new \VideoCategory();
            $this->category = $category->getById($this->nested_params['video.category'], true);
        }
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

        $category = $this->category;

        $genres = array_map(function($genre){
            unset($genre['category_alias']);
            return $genre;
        }, array_filter($genres, function($genre) use ($category) {
            return $category['category_alias'] == $genre['category_alias'] || empty($category);
        }));

        $genres = array_values($genres);

        return $genres;
    }
}