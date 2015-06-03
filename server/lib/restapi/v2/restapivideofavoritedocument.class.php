<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiVideoFavoriteDocument extends RESTApiDocument
{
    private $fav_id;
    private $parent;

    public function __construct(RESTApiResourceVideoFavorites $parent, array $params){
        parent::__construct();

        if (empty($params)){
            return;
        }

        $this->parent = $parent;

        $this->fav_id = (int) $params[0];
    }

    public function delete(RESTApiRequest $request){

        $user_id = $this->parent->getUserId();

        $fav = $this->parent->manager->getFavByUid($user_id);
        $fav = array_values($fav);

        $idx = array_search($this->fav_id, $fav);

        if ($idx === false){
            return false;
        }

        unset($fav[$idx]);

        $fav = array_values($fav);

        return (bool) $this->parent->manager->saveFav($fav, $user_id);
    }
}