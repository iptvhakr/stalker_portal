<?php

class Kinopoisk
{
    public static function getInfoById($id){

        $movie_info = array('kinopoisk_id' => $id);

        $movie_url = 'http://www.kinopoisk.ru/level/1/film/'.$id.'/';

        $movie_info['kinopoisk_url'] = $movie_url;

        $ch = curl_init();

        curl_setopt_array($ch,
            array(
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
            )
        );

        $page = curl_exec($ch);

        curl_close($ch);

        if (class_exists('tidy')){

            $tidy = new tidy;

            $tidy_config = array(
                'indent' => true,
                'bare'   => true,
                'clean'  => true,
                'drop-proprietary-attributes' => true,
                'new-inline-tags' => 'spacer',
                'new-empty-tags' => 'spacer',
                'new-blocklevel-tags' => 'spacer',
                'new-pre-tags' => 'spacer',
            );

            $page = $tidy->repairString($page, $tidy_config, 'raw');
        }else{
            throw new ErrorException("php-tidy extension not installed.");
        }

        //var_dump($page);

        libxml_use_internal_errors(true);
        $dom = new DomDocument();
        $dom->loadHTML($page);
        libxml_use_internal_errors(false);

        $xpath = new DomXPath($dom);

        // Translated name
        $node_list = $xpath->query('//*[@id="content_block"]/table[1]/tr/td/div/table/tr[2]/td[1]/table/tr[1]/td/table/tr/td[2]/table/tr[1]/td/h1');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['name'] = self::getNodeText($node_list->item(0));
        }

        if (empty($movie_info['name'])){
            throw new KinopoiskException("Movie name in '".$movie_url."' not found", $page);
        }

        // Original name
        $node_list = $xpath->query('//*[@id="content_block"]/table[1]/tr/td/div/table/tr[2]/td[1]/table/tr[1]/td/table/tr/td[2]/table/tr[2]/td/table/tr/td[1]/span');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['o_name'] = self::getNodeText($node_list->item(0));
        }

        if (empty($movie_info['o_name'])){
            $movie_info['o_name'] = $movie_info['name'];
        }

        // Year
        $node_list = $xpath->query('//*[@id="content_block"]/table[1]/tr/td/div/table/tr[2]/td[1]/table/tr[2]/td[2]/div[1]/table/tr[1]/td[2]/div/a');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['year'] = self::getNodeText($node_list->item(0));
        }

        // Duration
        $node_list = $xpath->query('//*[@id="runtime"]');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['duration'] = (int) self::getNodeText($node_list->item(0));
        }

        // Director
        $node_list = $xpath->query('//*[@id="content_block"]/table[1]/tr/td/div/table/tr[2]/td[1]/table/tr[2]/td[2]/div[1]/table/tr[4]/td[2]/a');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['director'] = self::getNodeText($node_list->item(0));
        }

        // Actors
        $node_list = $xpath->query('//*[@id="content_block"]/table[1]/tr/td/div/table/tr[2]/td[2]/div/span');

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
        $node_list = $xpath->query('//*[@id="syn"]/tr[1]/td/table/tr[1]/td');

        if ($node_list !== false && $node_list->length != 0){
            $movie_info['description'] = self::getNodeText($node_list->item(0));
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

        $search_url = 'http://www.kinopoisk.ru/index.php?level=7&from=forma&result=adv&m_act[from]=forma&m_act[what]=content&m_act[find]='.urlencode($orig_name).'&m_act[content_find]=film&first=yes';

        curl_setopt_array($ch,
            array(
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
            )
        );

        $response = curl_exec($ch);

        curl_close($ch);

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

    private static function getNodeText($node){

        $text = html_entity_decode($node->nodeValue);

        //$text = str_replace('&nbsp;', ' ', $text);

        $rules = array(
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