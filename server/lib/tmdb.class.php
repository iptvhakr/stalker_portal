<?php

class Tmdb {

    public static function getInfoById($id, $type = 'movie') {

        $movie_info = array('kinopoisk_id' => $id);
        $tmdb_api_key = Config::get('tmdb_api_key');
        $lang = self::getLanguage();
        $request_url = 'http://api.themoviedb.org/3/'.$type.'/' . $id . '?append_to_response=releases,credits&api_key=' . $tmdb_api_key . "&language=$lang&include_image_language=$lang";
        $movie_url = 'https://www.themoviedb.org/'.$type.'/' . $id;
        $movie_info['kinopoisk_url'] = $movie_url;

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $request_url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Accept: application/json'
            )
        );

        if (Config::exist('http_proxy')) {
            $curl_options[CURLOPT_PROXY] = str_replace('tcp://', '', Config::get('http_proxy'));

            if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')) {
                $curl_options[CURLOPT_PROXYUSERPWD] = Config::get('http_proxy_login') . ":" . Config::get('http_proxy_password');
            }
        }

        curl_setopt_array($ch, $curl_options);
        $page = curl_exec($ch);
        $moviedata = json_decode($page, true);

        if (!array_key_exists('status_code', $moviedata) || $moviedata['status_code'] == 1) {

            if (!empty($moviedata['imdb_id'])) {
                $imdb_request = 'http://www.omdbapi.com/?i=' . $moviedata['imdb_id'];
                $curl_options[CURLOPT_URL] = $imdb_request;
                curl_setopt_array($ch, $curl_options);
                $page = curl_exec($ch);
                curl_close($ch);
                $imdbdata = json_decode($page, true);
            }

            if (isset($moviedata['title'])){
                $movie_info['name'] = $moviedata['title'];
            }elseif (isset($moviedata['name'])){
                $movie_info['name'] = $moviedata['name'];
            }

            if (empty($movie_info['name'])) {
                throw new tmdbException("Movie name in '" . $movie_url . "' not found", $page);
            }

            if (isset($moviedata['original_title'])){
                $movie_info['o_name'] = $moviedata['original_title'];
            }elseif (isset($moviedata['original_name'])){
                $movie_info['o_name'] = $moviedata['original_name'];
            }

            if (empty($movie_info['o_name'])) {
                $movie_info['o_name'] = $movie_info['name'];
            }

            $movie_info['cover'] = 'http://image.tmdb.org/t/p/w154' . $moviedata['poster_path'];
            $movie_info['cover_big'] = 'http://image.tmdb.org/t/p/w342' . $moviedata['poster_path'];

            if (isset($moviedata['release_date'])){
                $movie_info['year'] = substr($moviedata['release_date'], 0, 4);
            }elseif(isset($moviedata['last_air_date'])){
                $movie_info['year'] = substr($moviedata['last_air_date'], 0, 4);
            }

            $movie_info['duration'] = (int) $moviedata['runtime'];

            // Directors (max 3)
            $crew = $moviedata['credits']['crew'];
            $directors = array();
            $count = 0;
            foreach ($crew as $crew_member) {
                if ($crew_member['job'] === 'Director') {
                    $directors[] = $crew_member['name'];
                    $count++;
                    if ($count == 3)
                        break;
                }
            }
            $movie_info['director'] = implode(", ", $directors);

            // Actors (max 8)
            $cast = $moviedata['credits']['cast'];
            $actors = array();
            $count = 0;
            foreach ($cast as $cast_member) {
                $actors[] = $cast_member['name'];
                $count++;
                if ($count == 8)
                    break;
            }
            $movie_info['actors'] = implode(", ", $actors);

            // Description
            $movie_info['description'] = $moviedata['overview'];

            // Age Ratings
            $mpaa_rating = '';
            $age_rating = '';
            $releases = $moviedata['releases']['countries'];
            foreach ($releases as $release_item) {
                if ($release_item['iso_3166_1'] === 'US')
                    $mpaa_rating = $release_item['certification'];
                if ($release_item['iso_3166_1'] === 'DE')
                    $age_rating = $release_item['certification'];
            }
            $movie_info['age'] = $age_rating . '+';
            $movie_info['rating_mpaa'] = $mpaa_rating;

            // TMDB vote average
            $movie_info['rating_kinopoisk'] = $moviedata['vote_average'];

            // TMDB vote count
            $movie_info['rating_count_kinopoisk'] = (int) $moviedata['vote_count'];

            // IMDB Rating (from www.omdbapi.com)
            if (!empty($imdbdata['imdbRating'])){
                $movie_info['rating_imdb'] = $imdbdata['imdbRating'];
            }

            if (!empty($imdbdata['imdbVotes'])){
                $movie_info['rating_count_imdb'] = (int) str_replace(',', '', $imdbdata['imdbVotes']);
            }

            //Production Countries
            $production_countries = $moviedata['production_countries'];
            $prod_countries = array();
            foreach ($production_countries as $prod_country_item) {
                $prod_countries[] = $prod_country_item['name'];
            }
            $movie_info['country'] = implode(", ", $prod_countries);
        }
        return $movie_info;
    }

    public static function getInfoByName($orig_name) {

        if (empty($orig_name)) {
            return false;
        }

        $ch = curl_init();

        if ($ch === false) {
            throw new tmdbException("Curl initialization error", curl_error($ch));
        }

        $orig_name = urlencode($orig_name);
        $lang = self::getLanguage();
        $search_url = 'http://api.themoviedb.org/3/search/multi?query=' . $orig_name . '&api_key=' . Config::get('tmdb_api_key') . "&language=$lang&include_image_language=$lang";

        $curl_options = array(
            CURLOPT_URL => $search_url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Accept: application/json'
            )
        );

        if (Config::exist('http_proxy')) {
            $curl_options[CURLOPT_PROXY] = str_replace('tcp://', '', Config::get('http_proxy'));

            if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')) {
                $curl_options[CURLOPT_PROXYUSERPWD] = Config::get('http_proxy_login') . ":" . Config::get('http_proxy_password');
            }
        }

        curl_setopt_array($ch, $curl_options);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response === false) {
            throw new tmdbException("Curl exec failure", curl_error($ch));
        }

        $results = json_decode($response, true);
        if ((!array_key_exists('status_code', $results) || $results['status_code'] == 1) && !empty($results['results'])) {
            foreach ($results['results'] as $result){
                if (!empty($result['media_type']) && ($result['media_type'] == 'tv' || $result['media_type'] == 'movie')){
                    $movie_id = $result['id'];
                    return self::getInfoById($movie_id, $result['media_type']);
                }
            }
        }
        return $results;
    }

    public static function getRatingByName($orig_name) {

        $info = self::getInfoByName($orig_name);

        if (!$info) {
            return false;
        }

        $fields = array_fill_keys(array('kinopoisk_url', 'kinopoisk_id', 'rating_kinopoisk', 'rating_count_kinopoisk', 'rating_imdb', 'rating_count_imdb'), true);

        return array_intersect_key($info, $fields);
    }

    public static function getRatingById($kinopoisk_id) {

        $result = array(
            'kinopoisk_id' => $kinopoisk_id
        );

        $xml_url = 'http://www.kinopoisk.ru/rating/' . $kinopoisk_id . '.xml';

        $xml = @simplexml_load_file($xml_url);

        if (!$xml) {
            throw new tmdbException("Can't get rating from " . $xml_url . "; " . implode(', ', libxml_get_errors()), '');
        }

        $result['rating_kinopoisk'] = (string) $xml->kp_rating;
        $result['rating_count_kinopoisk'] = (int) $xml->kp_rating->attributes()->num_vote;

        if ($xml->imdb_rating) {
            $result['rating_imdb'] = (string) $xml->imdb_rating;
            $result['rating_count_imdb'] = (int) $xml->imdb_rating->attributes()->num_vote;
        }

        return $result;
    }

    private static function getLanguage() {
        $locales = array();

        $allowed_locales = Config::get("allowed_locales");

        foreach ($allowed_locales as $lang => $locale) {
            $locales[substr($locale, 0, 2)] = $locale;
        }

        $accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

        if (!empty($_COOKIE['language']) && array_key_exists($_COOKIE['language'], $locales)) {
            $language = $_COOKIE['language'];
        } else if ($accept_language && array_key_exists(substr($accept_language, 0, 2), $locales)) {
            $language = substr($accept_language, 0, 2);
        } else {
            $language = key($locales);
        }

        return $language;
    }

}

class tmdbException extends Exception {

    protected $response;

    public function __construct($message, $response = "") {
        $this->message = $message;
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

}
