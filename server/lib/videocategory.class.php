<?php

class VideoCategory
{
    private $language;

    /**
     * @deprecated
     */
    public function setLocale($language){
        $this->language = $language;

        Stb::getInstance()->initLocale($this->language);
    }

    public function getAll($pretty_id = false){

        $categories = Mysql::getInstance()->from('media_category')->orderby('num')->get()->all();

        $categories = array_map(
            function($item) use ($pretty_id){

                if ($pretty_id){
                    $item['id'] = preg_replace("/_/i", "-", $item['category_alias']);
                }

                $item['original_title'] = $item['category_name'];
                $item['title']          = _($item['category_name']);

                return $item;
            }, $categories);

        return $categories;
    }

    public function getById($id, $pretty_id = false){

        if ($pretty_id){
            $categories = $this->getAll($pretty_id);

            $categories = array_filter($categories, function($category) use ($id){
                return $id == $category['id'];
            });

            if (empty($categories)){
                return null;
            }

            $categories = array_values($categories);

            return Mysql::getInstance()->from('media_category')->where(array('category_name' => $categories[0]['original_title']))->get()->first();
        }else{
            return Mysql::getInstance()->from('media_category')->where(array('id' => intval($id)))->get()->first();
        }
    }
}