<?php

class Audioclub extends AjaxResponse implements \Stalker\Lib\StbApi\Audioclub

{
    public function createLink(){

    }

    public function getCategories(){

        return array(
            array(
                'alias' => 'albums',
                'title' => _('Albums')
            ),
            array(
                'alias' => 'performers',
                'title' => _('Artists')
            ),
            array(
                'alias' => 'genres',
                'title' => _('Genres')
            ),
            array(
                'alias' => 'years',
                'title' => _('Years')
            ),
            array(
                'alias' => 'playlists',
                'title' => _('Playlists')
            )
        );

    }

    public function getOrderedList(){

        $category = empty($_REQUEST['category']) ? 'albums' : $_REQUEST['category'];

        if ($category == 'albums'){
            return $this->getAlbumsList();
        }elseif ($category == 'performers'){
            return $this->getPerformersList();
        }elseif ($category == 'genres'){
            return $this->getGenresList();
        }elseif ($category == 'years'){
            return $this->getYearsList();
        }
    }

    private function getAlbumsList(){

        $offset = $this->page * self::max_page_items;

        $result = Mysql::getInstance()
            ->select('audio_albums.*,
                audio_performers.name as performer_name,
                audio_years.name as album_year,
                countries.name as album_country
            ')
            ->from('audio_albums')
            ->join('audio_performers', 'audio_albums.performer_id', 'audio_performers.id', 'LEFT')
            ->join('audio_years', 'audio_albums.year_id', 'audio_years.id', 'LEFT')
            ->join('countries', 'audio_albums.country_id', 'countries.id', 'LEFT')
            ->where(array('audio_albums.status' => 1))
            ->orderby('added', 'DESC')
            ->limit(self::max_page_items, $offset);

        if (!empty($_REQUEST['performer_id'])){
            $result->where(array('performer_id' => (int) $_REQUEST['performer_id']));
        }

        if (!empty($_REQUEST['genre_id'])){
            $result->join('audio_genre', 'audio_albums.id', 'audio_genre.album_id', 'LEFT')
                ->where(array('genre_id' => (int) $_REQUEST['genre_id']));
        }

        if (!empty($_REQUEST['year_id'])){
            $result->where(array('year_id' => (int) $_REQUEST['year_id']));
        }

        $this->setResponseData($result);

        for ($i = 0; $i < count($this->response['data']); $i++) {

            $this->response['data'][$i]['name'] = $this->response['data'][$i]['performer_name'].' - '.$this->response['data'][$i]['name'];

            $this->response['data'][$i]['genres'] = implode(', ', $this->getAlbumGenres($this->response['data'][$i]['id']));
            $this->response['data'][$i]['tracks'] = $this->countAlbumTracks($this->response['data'][$i]['id']);
            $this->response['data'][$i]['languages'] = implode(', ', $this->getAlbumLanguages($this->response['data'][$i]['id']));

            $this->response['data'][$i]['cover_uri'] = Config::get('portal_url').'misc/audio_covers/'
                .ceil($this->response['data'][$i]['id']/100)
                .'/'.$this->response['data'][$i]['cover'];

            $this->response['data'][$i]['is_album'] = true;
        }

        if (isset($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    private function getPerformersList(){

        $offset = $this->page * self::max_page_items;

        $result = Mysql::getInstance()
            ->from('audio_performers')
            ->orderby('name')
            ->limit(self::max_page_items, $offset);

        $this->setResponseData($result);

        for ($i = 0; $i < count($this->response['data']); $i++) {
            $this->response['data'][$i]['is_performer'] = true;
        }

        if (isset($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    private function getGenresList(){

        $offset = $this->page * self::max_page_items;

        $result = Mysql::getInstance()
            ->from('audio_genres')
            ->orderby('name')
            ->limit(self::max_page_items, $offset);

        $this->setResponseData($result);

        for ($i = 0; $i < count($this->response['data']); $i++) {
            $this->response['data'][$i]['is_genre'] = true;
        }

        if (isset($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    private function getYearsList(){

        $offset = $this->page * self::max_page_items;

        $result = Mysql::getInstance()
            ->from('audio_years')
            ->orderby('name')
            ->limit(self::max_page_items, $offset);

        $this->setResponseData($result);

        for ($i = 0; $i < count($this->response['data']); $i++) {
            $this->response['data'][$i]['is_year'] = true;
        }

        if (isset($_REQUEST['row'])){
            $this->response['selected_item'] = $_REQUEST['row']+1;
            $this->response['cur_page']      = $this->cur_page == 0 ? 1 : $this->cur_page;
        }

        return $this->response;
    }

    public function getTrackList(){

        $album_id = empty($_REQUEST['album_id']) ? 0 : (int) $_REQUEST['album_id'];

        $album = Mysql::getInstance()
        ->select('audio_albums.*,
            audio_performers.name as performer_name,
            audio_years.name as album_year,
            countries.name as album_country
        ')
        ->from('audio_albums')
        ->join('audio_performers', 'audio_albums.performer_id', 'audio_performers.id', 'LEFT')
        ->join('audio_years', 'audio_albums.year_id', 'audio_years.id', 'LEFT')
        ->join('countries', 'audio_albums.country_id', 'countries.id', 'LEFT')
        ->where(array('audio_albums.id' => $album_id))
        ->get()->first();

        $offset = $this->page * self::max_page_items;

        $result = Mysql::getInstance()->from('audio_compositions')
            ->select('audio_compositions.*, audio_languages.name as language')
            ->where(array('album_id' => $album_id, 'audio_compositions.status' => 1))
            ->join('audio_languages', 'audio_compositions.language_id', 'audio_languages.id', 'LEFT')
            ->orderby('number');

        if (empty($_REQUEST['as_playlist'])){
            $result->limit(self::max_page_items, $offset);
        }

        $this->setResponseData($result);

        for ($i = 0; $i < count($this->response['data']); $i++) {
            $this->response['data'][$i]['name']           = $this->response['data'][$i]['number'].'. '.$this->response['data'][$i]['name'];
            $this->response['data'][$i]['performer_name'] = $album['performer_name'];
            $this->response['data'][$i]['cmd']            = $this->response['data'][$i]['url'];
            $this->response['data'][$i]['album_name']     = $album['name'];
            $this->response['data'][$i]['album_year']     = $album['album_year'];
            $this->response['data'][$i]['album_country']  = $album['album_country'];
            $this->response['data'][$i]['cover_uri']      = Config::get('portal_url').'misc/audio_covers/'
                .ceil($album_id/100)
                .'/'.$album['cover'];
            $this->response['data'][$i]['is_track']       = true;
            $this->response['data'][$i]['is_audio']       = true;
        }

        return $this->response;
    }

    public function getAlbumGenres($album_id){
        return Mysql::getInstance()
             ->select('audio_genres.name')
             ->from('audio_genre')
             ->where(array('album_id' => $album_id))
             ->join('audio_genres', 'audio_genre.genre_id', 'audio_genres.id', 'LEFT')
             ->orderby('audio_genres.name')
             ->get()
             ->all('name');
    }

    function countAlbumTracks($album_id){
        return Mysql::getInstance()->from('audio_compositions')
            ->where(array('album_id' => $album_id))
            ->count()
            ->get()
            ->counter();
    }

    function getAlbumLanguages($album_id){

        return Mysql::getInstance()
            ->select('audio_languages.name')
            ->from('audio_compositions')
            ->where(array('album_id' => $album_id))
            ->join('audio_languages', 'audio_compositions.language_id', 'audio_languages.id', 'LEFT')
            ->orderby('audio_languages.name')
            ->groupby('audio_languages.name')
            ->get()
            ->all('name');
    }
}