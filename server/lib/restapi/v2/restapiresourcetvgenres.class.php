<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceTvGenres extends RESTApiCollection
{
    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);
        $this->document = new RESTApiTvGenreDocument();
    }

    public function getCount(RESTApiRequest $request){
        $genres = new \TvGenre();
        return count($genres->getAll(true));
    }

    public function get(RESTApiRequest $request){
        $genres = new \TvGenre();
        $genres->setLocale($request->getLanguage());

        return $genres->getAll(true);
    }
}