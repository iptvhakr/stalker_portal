<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceVideoGenres extends RESTApiCollection
{

    protected $params_map = array("video-categories" => "video.category");
    protected $categories;

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiVideoGenreDocument();

        if (!empty($this->nested_params['video.category'])){

            $category = new \VideoCategory();
            $category_ids = explode(',', $this->nested_params['video.category']);

            foreach ($category_ids as $category_id) {
                $this->categories[] = $category->getById($category_id, true);
            }

            if (empty($this->categories)){
                throw new RESTNotFound("Category not found");
            }
        }
    }

    public function getCount(RESTApiRequest $request){
        $genres = new \VideoGenre();
        return count($genres->getAll(true));
    }

    public function get(RESTApiRequest $request){
        $genres = new \VideoGenre();
        $genres->setLocale($request->getLanguage());

        if (!empty($this->categories)){
            $response = array();

            if (count($this->categories) == 1){
                $response = $this->filter($genres->getByCategoryId($this->categories[0]['id'], true));
            }else{

                foreach ($this->categories as $category){

                    $response[] = array(
                        'id'     => $category['id'],
                        'genres' => $this->filter($genres->getByCategoryId($category['id'], true))
                    );
                }
            }

            return $response;
        }else{
            return $this->filter($genres->getAll(true));
        }
    }

    private function filter($genres){

        $genres = array_map(function($genre){
            unset($genre['category_alias']);
            unset($genre['original_title']);
            unset($genre['_id']);
            return $genre;
        }, $genres);

        return $genres;
    }
}