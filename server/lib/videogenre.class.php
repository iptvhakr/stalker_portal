<?php

class VideoGenre
{
    private $language;

    /**
     * @deprecated
     */
    public function setLocale($language){
        $this->language = $language;

        Stb::getInstance()->initLocale($this->language);
    }

    public function getAll($pretty_id = false, $group = true, $include_internal_id = false){

        $genres = Mysql::getInstance()->from('cat_genre');

        if ($group){
            $genres->groupby('title');
        }

        $genres = $genres->get()->all();

        $genres = array_map(
            function($item) use ($pretty_id, $include_internal_id){

                if ($include_internal_id){
                    $item['_id'] = $item['id'];
                }

                if ($pretty_id){
                    $item['id'] = preg_replace(array("/\s/i", "/[^a-z0-9-]/i"), array("-", ""), $item['title']);
                }

                $item['original_title'] = $item['title'];
                $item['title']          = _($item['title']);

                return $item;
            }, $genres);

        return $genres;
    }

    public function getIdMap(){

        $genres = $this->getAll(true, false, true);

        $map = array();

        foreach ($genres as $genre){
            $map[$genre['_id']] = $genre['id'];
        }

        return $map;
    }

    public function getById($id, $pretty_id = false){

        if ($pretty_id){
            $genres = $this->getAll($pretty_id);

            $genres = array_filter($genres, function($genre) use ($id){
                return $id == $genre['id'];
            });

            if (empty($genres)){
                return null;
            }

            $titles = array_map(function($genre){
                return $genre['original_title'];
            }, array_values($genres));

            return Mysql::getInstance()->from('cat_genre')->in('title', $titles)->get()->all();
        }else{
            return Mysql::getInstance()->from('cat_genre')->where(array('id' => intval($id)))->get()->first();
        }
    }

    public function getByIdAndCategory($id, $category_id, $pretty_id = false){

        $category = new VideoCategory();
        $category = $category->getById($category_id, $pretty_id);

        if (empty($category)){
            return null;
        }

        if ($pretty_id){
            $genres = $this->getAll($pretty_id, false);

            $genres = array_filter($genres, function($genre) use ($id, $category){
                return $id == $genre['id'] && $genre['category_alias'] == $category['category_alias'];
            });

            if (empty($genres)){
                return null;
            }

            $genres = array_values($genres);

            return Mysql::getInstance()->from('cat_genre')->where(array('title' => $genres[0]['original_title']))->get()->first();
        }else{
            return Mysql::getInstance()->from('cat_genre')->where(array('id' => intval($id), 'category_alias' => $category['category_alias']))->get()->first();
        }
    }

    public function getByCategoryId($category_id, $pretty_id = false){

        $category = new VideoCategory();

        $category = $category->getById($category_id, $pretty_id);

        if (empty($category)){
            return array();
        }

        return Mysql::getInstance()->from('cat_genre')->where(array('category_alias' => $category['category_alias']))->get()->all();
    }
}