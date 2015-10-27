<?php

namespace Model;

class AudioClubModel extends \Model\BaseStalkerModel {

    private $tmp_table_name = 'audio_tmp_table';
    private $max_insert_values = 10;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getTotalRowsAudioAlbumsList($where = array(), $like = array()) {
        $params = array(
            'select' => array("(select count(*) from audio_compositions as a_c where a_c.album_id = audio_albums.id) as tracks_count"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAudioAlbumsList($params, TRUE);
    }
   
    public function getAudioAlbumsList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_albums')
                    ->join('audio_performers', 'audio_albums.performer_id', 'audio_performers.id', 'LEFT')
                    ->join('audio_years', 'audio_albums.year_id', 'audio_years.id', 'LEFT')
                    ->join('countries', 'audio_albums.country_id', 'countries.id', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getGenreForAlbum($id, $field = null) {
        return $this->mysqlInstance->from('audio_genre')
                ->join('audio_genres', 'audio_genre.genre_id', 'audio_genres.id', 'LEFT')
                ->where(array('album_id'=>$id))->get()->all($field);
    }
    
    public function getLanguagesForAlbum($id, $field = null){
        return $this->mysqlInstance->from('audio_compositions')
                ->where(array('album_id' => $id))
                ->join('audio_languages', 'audio_compositions.language_id', 'audio_languages.id', 'LEFT')
                ->orderby('audio_languages.name')
                ->groupby('audio_languages.name')
                ->get()->all($field);
    }
    
    public function getTotalRowsAudioGenresList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('audio_genres')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getAudioGenresList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_genres');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */
        return $this->mysqlInstance->get()->all();
    }
    
    public function insertAudioGenres($param){
        return $this->mysqlInstance->insert('audio_genres', $param)->insert_id();
    }
    
    public function updateAudioGenres($data, $param){
        return $this->mysqlInstance->update('audio_genres', $data, $param)->total_rows();
    }
    
    public function deleteAudioGenres($param){
        return $this->mysqlInstance->delete('audio_genres', $param)->total_rows();
    }
    
    public function getTotalRowsAudioArtistList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('audio_performers')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getAudioArtistList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_performers');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
 */
        return $this->mysqlInstance->get()->all();
    }
    
    public function insertAudioArtist($param){
        return $this->mysqlInstance->insert('audio_performers', $param)->insert_id();
    }
    
    public function updateAudioArtist($data, $param){
        return $this->mysqlInstance->update('audio_performers', $data, $param)->total_rows();
    }
    
    public function deleteAudioArtist($param){
        return $this->mysqlInstance->delete('audio_performers', $param)->total_rows();
    }
    
    public function getTotalRowsAudioLanguageList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('audio_languages')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getAudioLanguageList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_languages');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */

        return $this->mysqlInstance->get()->all();
    }
    
    public function insertAudioLanguage($param){
        return $this->mysqlInstance->insert('audio_languages', $param)->insert_id();
    }
    
    public function updateAudioLanguage($data, $param){
        return $this->mysqlInstance->update('audio_languages', $data, $param)->total_rows();
    }
    
    public function deleteAudioLanguage($param){
        return $this->mysqlInstance->delete('audio_languages', $param)->total_rows();
    }
    
    public function getTotalRowsAudioYearList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('audio_years')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getAudioYearList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_years');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */

        return $this->mysqlInstance->get()->all();
    }
    
    public function insertAudioYear($param){
        return $this->mysqlInstance->insert('audio_years', $param)->insert_id();
    }
    
    public function updateAudioYear($data, $param){
        return $this->mysqlInstance->update('audio_years', $data, $param)->total_rows();
    }
    
    public function deleteAudioYear($param){
        return $this->mysqlInstance->delete('audio_years', $param)->total_rows();
    }
    
    public function deleteAudioGenre($param){
        return $this->mysqlInstance->delete('audio_genre', $param)->total_rows();
    }
    
    public function deleteAudioAlbum($param){
        return $this->mysqlInstance->delete('audio_albums', $param)->total_rows();
    }
    
    public function deleteAudioCompositions($param){
        return $this->mysqlInstance->delete('audio_compositions', $param)->total_rows();
    }
    
    public function getAudioCountryList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('countries');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */

        return $this->mysqlInstance->get()->all();
    }
    
    public function insertAudioAlbum($param){
        return $this->mysqlInstance->insert('audio_albums', $param)->insert_id();
    }
    
    public function updateAudioAlbum($param, $id){
        return $this->mysqlInstance->update('audio_albums', $param, array('id'=>$id))->total_rows() || 1;
    }
    
    public function getAudioAlbum($id){
        return $this->mysqlInstance->from('audio_albums')->where(array('id'=>$id))->get()->first();
    }
    
    public function insertAudioGenre($param){
        return $this->mysqlInstance->insert('audio_genre', $param)->total_rows();
    }
    
    public function getTotalRowsAlbumsCompositionList($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAlbumsCompositionList($params, TRUE);
    }
    
    public function getAlbumsCompositionList($param, $counter = FALSE) {

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('audio_compositions')->join('audio_languages', 'audio_compositions.language_id', 'audio_languages.id', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
/*         if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        } */

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function updateAlbumsComposition($param, $where){
        $where = (is_array($where)? $where: array('id'=>$where));
        return $this->mysqlInstance->update('audio_compositions', $param, $where)->total_rows() || 1;
    }
    
    public function insertAlbumsComposition($param){
        return $this->mysqlInstance->insert('audio_compositions', $param)->insert_id();
    }

    public function updateCover($id, $cover_name){
        return $this->mysqlInstance->update('audio_albums', array('cover' => $cover_name), array('id' => $id))->total_rows();
    }
    
    //--------------------------------------------------------------------------
    private function setTmpTable(){
        if (!$this->existsTable($this->tmp_table_name, TRUE)){
            if ($this->createTmpTable() && $this->existsTable($this->tmp_table_name, TRUE)){
                $this->fillTmpTable();
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    private function createTmpTable(){
        $this->mysqlInstance->query("
            CREATE TEMPORARY TABLE if not exists `$this->tmp_table_name`(
            `id` int primary key,
            `name` varchar(1024),
            `tracks_count` int,
            `ganre_name`  varchar(1024),
            `year` varchar(255),
            `country`  varchar(255),
            `language`  varchar(1024),
            `complaints` varchar(255),
            `tasks` varchar(255),
            `status` int(1)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8
            ");
        return TRUE;
    }
    
    private function dropTmpTable(){
        $this->mysqlInstance->query("drop table if exists `$this->tmp_table_name`");
    }
    
    private function fillTmpTable(){
        $rows = $this->getAlbumsTableData();
        if (!empty($rows)) {
            $counter = 0;
            $insert_key = "(`" . implode('`, `', array_keys($rows[0])) . "`)";
            $insert_val = '';
            reset($rows);
            while(list($key, $row) = each($rows)){
                $rows[$key]['ganre_name'] = implode(', ', $this->getGenreForAlbum($row['id'], 'name'));
                $rows[$key]['language'] = implode(', ', $this->getLanguagesForAlbum($row['id'], 'name'));
                $counter++;
                $insert_val .= ((!empty($insert_val)? ',': '') . "('" . implode("', '", array_map('addslashes', array_values($rows[$key]))) . "')");
                if ($counter>=$this->max_insert_values) {
                    $this->mysqlInstance->query("INSERT INTO $this->tmp_table_name $insert_key VALUES $insert_val");
                    $insert_val = '';
                    $counter = 0;
                }
            }
            if (!empty($insert_val)) {
                $this->mysqlInstance->query("INSERT INTO $this->tmp_table_name $insert_key VALUES $insert_val");
            }
        }
    }
    
    private function getAlbumsTableData() {
        return $this->mysqlInstance->query("SELECT `audio_albums`.`id` as `id`,
                                                   CONCAT_WS(' - ', `audio_performers`.`name`, `audio_albums`.`name`) as `name`,
                                                   (SELECT COUNT(*) FROM `audio_compositions` WHERE `album_id` = `audio_albums`.`id`) as `tracks_count`,
                                                   '' as `ganre_name`,
                                                   `audio_years`.`name` as `year`,
                                                   `countries`.`name` as `country`,
                                                   0 as `language`,
                                                   0 as `complaints`,
                                                   0 as `tasks`,
                                                   `audio_albums`.`status` as `status`
                                            FROM (audio_albums)
                                            LEFT JOIN audio_performers ON (audio_albums.performer_id=audio_performers.id)
                                            LEFT JOIN audio_years ON (audio_albums.year_id=audio_years.id)
                                            LEFT JOIN countries ON (audio_albums.country_id=countries.id)")->all();
    }
    
}
