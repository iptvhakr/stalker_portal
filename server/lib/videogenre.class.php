<?php

class VideoGenre
{
    private $language;

    public function setLocale($language){
        $this->language = $language;

        Stb::getInstance()->initLocale($this->language);
    }

    public function getAll($pretty_id = false){

        $genres = Mysql::getInstance()->from('cat_genre')->groupby('title')->get()->all();

        $genres = array_map(
            function($item) use ($pretty_id){

                if ($pretty_id){
                    $item['id'] = preg_replace(array("/\s/i", "/[^a-z0-9-]/i"), array("-", ""), $item['title']);
                }

                $item['title'] = _($item['title']);
                //unset($item['category_alias']);

                return $item;
            }, $genres);

        return $genres;
    }

    public function getById($id, $pretty_id = false){

        if ($pretty_id){
            $this->setLocale('en');
            $genres = $this->getAll($pretty_id);

            $genres = array_filter($genres, function($genre) use ($id){
                return $id == preg_replace(array("/\s/i", "/[^a-z0-9-]/i"), array("-", ""), $genre['title']);
            });

            if (empty($genres)){
                return null;
            }

            $titles = array_map(function($genre){
                return $genre['title'];
            }, array_values($genres));

            return Mysql::getInstance()->from('cat_genre')->in('title', $titles)->get()->all();
        }else{
            return Mysql::getInstance()->from('cat_genre')->where(array('id' => intval($id)))->get()->first();
        }
    }

    public function getByIdAndCategory($id, $category_id, $pretty_id = false){

        $category = new VideoCategory();
        $category = $category->getById($category_id, $pretty_id);

        /*var_dump($category);*/

        if (empty($category)){
            return null;
        }

        if ($pretty_id){
            $this->setLocale('en');
            $genres = $this->getAll($pretty_id);

            $genres = array_filter($genres, function($genre) use ($id, $category){
                return $id == preg_replace(array("/\s/i", "/[^a-z0-9-]/i"), array("-", ""), $genre['title'])
                    && $genre['category_alias'] == $category['category_alias'];
            });

            if (empty($genres)){
                return null;
            }

            return Mysql::getInstance()->from('cat_genre')->where(array('title' => $genres[0]['title']))->get()->first();
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