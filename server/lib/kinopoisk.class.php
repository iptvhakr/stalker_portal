<?php

class Kinopoisk
{
    public static function getInfoById($id){

        $movie_info = array('kinopoisk_id' => $id);

        $movie_url = 'http://www.kinopoisk.ru/film/'.$id.'/';

        $movie_info['kinopoisk_url'] = $movie_url;
        $movie_info['cover'] = 'http://st.kinopoisk.ru/images/film/'.$id.'.jpg';

        $cover_big_url = 'http://st.kinopoisk.ru/images/film_big/'.$id.'.jpg';

        $big_cover_headers = get_headers($cover_big_url, 1);

        if ($big_cover_headers !== false){

            if (strpos($big_cover_headers[0], '302') !== false && !empty($big_cover_headers['Location'])){
                $movie_info['cover_big'] = $big_cover_headers['Location'];
            }else{
                $movie_info['cover_big'] = $cover_big_url;
            }
        }

        $ch = curl_init();

        $curl_options = array(
            CURLOPT_URL => $movie_url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5',
                'Accept: text/css,*/*;q=0.1',
                'Accept-Encoding: deflate,sdch',
                'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
                'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.3'
            )
        );

        if (Config::exist('http_proxy')){
            $curl_options[CURLOPT_PROXY] = str_replace('tcp://', '', Config::get('http_proxy'));

            if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')){
                $curl_options[CURLOPT_PROXYUSERPWD] = Config::get('http_proxy_login').":".Config::get('http_proxy_password');
            }
        }

        curl_setopt_array($ch, $curl_options);

        $page = curl_exec($ch);

        curl_close($ch);

        //var_dump($page);

        libxml_use_internal_errors(true);
        $dom = new DomDocument();
        $dom->loadHTML($page);
        libxml_use_internal_errors(false);

        $xpath = new DomXPath($dom);

        // Translated name
        $node_list = $xpath->query('//*[@id="headerFilm"]/h1');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['name'] = self::getNodeText($node_list->item(0));
        }

        if (empty($movie_info['name'])){
            throw new KinopoiskException("Movie name in '".$movie_url."' not found", $page);
        }

        // Original name
        $node_list = $xpath->query('//*[@id="headerFilm"]/span');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['o_name'] = self::getNodeText($node_list->item(0));
        }

        if (empty($movie_info['o_name'])){
            $movie_info['o_name'] = $movie_info['name'];
        }

        // Year
        $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[1]/td[2]/div/a');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['year'] = self::getNodeText($node_list->item(0));
        }

        // Country
        $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[2]/td[2]/div');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['country'] = self::getNodeText($node_list->item(0));
        }

        // Duration
        $node_list = $xpath->query('//*[@id="runtime"]');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['duration'] = (int) self::getNodeText($node_list->item(0));
        }

        // Director
        $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[4]/td[2]/a');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['director'] = self::getNodeText($node_list->item(0));
        }

        // Actors
        $node_list = $xpath->query('//*[@id="actorList"]/ul[1]/li');

        if ($node_list !== false && $node_list->length != 0){

            $actors = array();

            foreach ($node_list as $node){
                $actors[] = self::getNodeText($node);
            }

            if ($actors[count($actors) - 1] == '...'){
                unset($actors[count($actors) - 1]);
            }

            $movie_info['actors'] = implode(", ", $actors);
        }

        // Description
        //$node_list = $xpath->query('//*[@id="syn"]/tr[1]/td/table/tr[1]/td');
        $node_list = $xpath->query('//div[@itemprop="description"]');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['description'] = self::getNodeText($node_list->item(0));
        }

        // Age limit
        $node_list = $xpath->query('//div[contains(@class, "ageLimit")]');

        if ($node_list !== false && $node_list->length != 0){
            $class = $node_list->item(0)->attributes->getNamedItem('class')->nodeValue;
            $movie_info['age'] = substr($class, strrpos($class, 'age')+3);
            if ($movie_info['age']){
                $movie_info['age'] .= '+';
            }
        }

        // Rating MPAA
        $node_list = $xpath->query('//td[contains(@class, "rate_")]');

        if ($node_list !== false && $node_list->length != 0){
            $class = $node_list->item(0)->attributes->getNamedItem('class')->nodeValue;
            $movie_info['rating_mpaa'] = strtoupper(substr($class, 5));

            if ($movie_info['rating_mpaa'] == 'PG13'){
                $movie_info['rating_mpaa'] = 'PG-13';
            }elseif($movie_info['rating_mpaa'] == 'NC17'){
                $movie_info['rating_mpaa'] = 'NC-17';
            }
        }

        // Kinopoisk rating
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[1]/a/span[1]');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['rating_kinopoisk'] = self::getNodeText($node_list->item(0));
        }

        // Kinopoisk rating count
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[1]/a/span[2]');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['rating_count_kinopoisk'] = self::getNodeText($node_list->item(0));
        }

        // IMDB rating
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[2]');

        if ($node_list !== false && $node_list->length != 0){
            $imdb_raw = self::getNodeText($node_list->item(0));

            if (preg_match("/IMDb: (.*) \((.*)\)/", $imdb_raw, $match)){
                $movie_info['rating_imdb'] = $match[1];
                $movie_info['rating_count_imdb'] = $match[2];
            }
        }

        return $movie_info;
    }

    public static function getInfoByName($orig_name){

        if (empty($orig_name)){
            return false;
        }

        $ch = curl_init();

        if ($ch === false){
            throw new KinopoiskException("Curl initialization error", curl_error($ch));
        }

        $orig_name = iconv("utf-8", "windows-1251", $orig_name);

        $orig_name = urlencode($orig_name);

        $search_url = 'http://www.kinopoisk.ru/index.php?level=7&from=forma&result=adv&m_act[from]=forma&m_act[what]=content&m_act[find]='.$orig_name.'&m_act[content_find]=film,serial&first=yes';

        $curl_options = array(
            CURLOPT_URL => $search_url,
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5',
                'Accept: text/css,*/*;q=0.1',
                'Accept-Encoding: gzip,deflate,sdch',
                'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
                'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.3'
            )
        );

        if (Config::exist('http_proxy')){
            $curl_options[CURLOPT_PROXY] = str_replace('tcp://', '', Config::get('http_proxy'));

            if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')){
                $curl_options[CURLOPT_PROXYUSERPWD] = Config::get('http_proxy_login').":".Config::get('http_proxy_password');
            }
        }

        curl_setopt_array($ch, $curl_options);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response === false){
            throw new KinopoiskException("Curl exec failure", curl_error($ch));
        }

        if (preg_match("/Location: ([^\s]*)/", $response, $match)){
            $location = $match[1];
        }

        if (empty($location)){
            throw new KinopoiskException("Empty location header", $response);
        }

        if (strpos($location, 'http') === 0){
            throw new KinopoiskException("Wrong location header. Location: ('".$location."')", $response);
        }

        if (preg_match("/\/([\d]*)\/$/", $location, $match)){
            $movie_id = $match[1];
        }else{
            throw new KinopoiskException("Location does not contain movie id. Location: ('".$location."')", $response);
        }

        return self::getInfoById($movie_id);
    }

    public static function getRatingByName($orig_name){

        $info = self::getInfoByName($orig_name);

        if (!$info){
            return false;
        }

        $fields = array_fill_keys(array('kinopoisk_url', 'kinopoisk_id', 'rating_kinopoisk', 'rating_count_kinopoisk', 'rating_imdb', 'rating_count_imdb'), true);

        return array_intersect_key($info, $fields);
    }

    public static function getRatingById($kinopoisk_id){

        $result = array(
            'kinopoisk_id' => $kinopoisk_id
        );

        $xml_url = 'http://www.kinopoisk.ru/rating/'.$kinopoisk_id.'.xml';

        $xml = @simplexml_load_file($xml_url);

        if (!$xml){
            throw new KinopoiskException("Can't get rating from ".$xml_url."; ".implode(', ', libxml_get_errors()), '');
        }

        $result['rating_kinopoisk']       = (string) $xml->kp_rating;
        $result['rating_count_kinopoisk'] = (int) $xml->kp_rating->attributes()->num_vote;

        if ($xml->imdb_rating){
            $result['rating_imdb']        = (string) $xml->imdb_rating;
            $result['rating_count_imdb']  = (int) $xml->imdb_rating->attributes()->num_vote;
        }

        return $result;
    }

    private static function getNodeText($node){

        $text = html_entity_decode($node->nodeValue);

        //$text = str_replace('&nbsp;', ' ', $text);

        $rules = array(
            "/\x{0085}/u" => "...",
            "/(\s+)/" => " ",
            "/\n/" => ""
        );

        $text = trim(preg_replace(array_keys($rules), array_values($rules), $text));

        return $text;
    }
}

class KinopoiskException extends Exception{

    protected $response;

    public function __construct($message, $response = ""){
        $this->message  = $message;
        $this->response = $response;
    }

    public function getResponse(){
        return $this->response;
    }
}