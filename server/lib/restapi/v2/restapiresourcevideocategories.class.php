<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceVideoCategories extends RESTApiCollection
{
    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiVideoCategoryDocument();
    }

    public function getCount(RESTApiRequest $request){
        $categories = new \VideoCategory();
        return count($categories->getAll(true));
    }

    public function get(RESTApiRequest $request){
        $categories = new \VideoCategory();
        $categories->setLocale($request->getLanguage());

        return $this->filter($categories->getAll(true));
    }

    private function filter($categories){

        $categories = array_map(function($category){

            unset($category['category_name']);
            unset($category['original_title']);
            unset($category['category_alias']);
            unset($category['num']);

            return $category;

        },$categories);

        return $categories;
    }
}