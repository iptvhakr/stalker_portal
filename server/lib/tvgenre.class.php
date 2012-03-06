<?php

class TvGenre
{
    private $language;

    public function setLocale($language){
        $this->language = $language;

        Stb::getInstance()->initLocale($this->language);
    }

    public function getAll($pretty_id = false){

        $genres = Mysql::getInstance()->from('tv_genre')->get()->all();

        $genres = array_map(
            function($item) use ($pretty_id){

                if ($pretty_id){
                    $item['id'] = preg_replace(array("/\s/i", "/[^a-z0-9-]/i"), array("-", ""), $item['title']);
                }

                $item['title'] = _($item['title']);

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

            $genres = array_values($genres);

            return Mysql::getInstance()->from('tv_genre')->where(array('title' => $genres[0]['title']))->get()->first();
        }else{
            return Mysql::getInstance()->from('tv_genre')->where(array('id' => intval($id)))->get()->first();
        }
    }
}