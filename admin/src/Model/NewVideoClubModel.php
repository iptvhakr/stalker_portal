<?php

namespace Model;

use Stalker\Lib\Core\Mysql;

class NewVideoClubModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsVideoList($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getVideoList($params, TRUE);
    }
   
    public function getVideoList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('video')
                    ->join('media_claims', 'video.id', 'media_claims.media_id and media_claims.media_type = "vclub"', 'LEFT')
                    ->join('video_on_tasks', 'video.id', 'video_on_tasks.video_id', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

/*        if (!$counter) {
            print_r($this->mysqlInstance->get()); exit;
        }*/

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getVideoById($id) {
        return $this->mysqlInstance->from('video')->where(array('id' => $id))->get()->first();
    }

    public function videoLogWrite($video, $text, $moderator_id = null) {

        if ($moderator_id === null) {
            $moderator_id = array_key_exists('uid', $_SESSION) ? $_SESSION['uid'] : FALSE;
        }

        return $this->mysqlInstance->insert('video_log', array(
                    'action' => $text,
                    'video_id' => $video['id'],
                    'video_name' => $video['name'],
                    'moderator_id' => $moderator_id,
                    'actiontime' => 'NOW()'
                ))->insert_id();
    }

    public function getTotalRowsVideoLog($where = array(), $like = array()){
        $this->mysqlInstance->count()->from('video_log')
                ->join('administrators', 'video_log.moderator_id', 'administrators.id', 'LEFT')
                ->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function getVideoLog($param){
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('video_log')
                ->join('administrators', 'video_log.moderator_id', 'administrators.id', 'LEFT')
                ->join('video', 'video_log.video_id', 'video.id', 'LEFT')
                ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
/*        print_r($this->mysqlInstance->get());
        exit;*/
        
        return $this->mysqlInstance->get()->all();
    }

    public function removeVideoById($video_id) {
        $date = new \DateTime();
        $this->mysqlInstance->update(
            'moderator_tasks',
            array('ended' => 1, 'rejected' => 1, 'end_time' => $date->format('Y-m-d H:i:s')),
            array('media_id' => $video_id, 'media_type' => 2));
        return $this->mysqlInstance->delete('video', array('id' => $video_id))->total_rows();
    }

    public function disableVideoById($video_id) {
        return $this->mysqlInstance->update('video', array('accessed' => 0, 'added' => 'NOW()'), array('id' => $video_id))->total_rows();
    }

    public function enableVideoById($video_id) {
        $this->mysqlInstance->update('updated_places', array('vclub' => 1));
        return $this->mysqlInstance->update('video', array('accessed' => 1, 'status' => 1, 'added' => 'NOW()'), array('id' => $video_id))->total_rows();
    }

    public function toggleDisableForHDDevices($video, $val) {
        if ($video['hd'] && $val) {
            return $this->mysqlInstance->update('video', array('disable_for_hd_devices' => 1), array(
                        'name' => $video['name'],
                        'o_name' => $video['o_name'],
                        'director' => $video['director'],
                        'year' => $video['year'],
                        'hd' => 0
                    ))->total_rows();
        }
        return TRUE;
    }

    public function deleteVideoTask($params) {
        return $this->mysqlInstance->delete('video_on_tasks', $params)->total_rows();
    }

    public function addVideoTask($data) {
        return $this->mysqlInstance->insert('video_on_tasks', $data)->insert_id();
    }

    public function updateVideoTask($data, $params) {
        return $this->mysqlInstance->update('video_on_tasks', $data, $params)->total_rows();
    }

    public function getVideoTaskByVideoId($video_id) {
        return $this->mysqlInstance->from('video_on_tasks')->where(array('video_id' => $video_id))->get()->first();
    }

    public function setModeratorTask($data) {
        return $this->mysqlInstance->insert('moderator_tasks', array(
                    'to_usr' => $data['to_usr'],
                    'media_type' => 2,
                    'media_id' => $data['id'],
                    'start_time' => 'NOW()'
                ))->insert_id();
    }
    
    public function getModeratorTasksById($task_id) {
        return $this->mysqlInstance->select('moderator_tasks')->where(array('id' => $task_id))->orderby(array('id' => 'desc'))->get()->first();
    }

    public function setModeratorHistory($data) {
        return $this->mysqlInstance->insert('moderators_history', array(
                    'task_id' => $data["task_id"],
                    'from_usr' => $data['uid'],
                    'to_usr' => $data['to_usr'],
                    'comment' => $data['comment'],
                    'send_time' => 'NOW()'
                ))->insert_id();
    }
    
    public function getAllAdmins() {
        return $this->mysqlInstance->from('administrators')->get()->all();
    }

    public function getTotalRowsModerators($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getModerators($params, TRUE);
    }

    public function getModerators($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('moderators');

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        if ($counter){
            return $this->mysqlInstance->count()->get()->counter();
        }

        if (!empty($param['where']) && !empty($param['where']['id'])) {
            return $this->mysqlInstance->get()->first();
        } else {
            return $this->mysqlInstance->get()->all();
        }
    }
    
    public function deleteModeratorsById($id) {
        return $this->mysqlInstance->delete('moderators', array('id' => $id))->total_rows();
    }

    public function updateModeratorsById($id, $data) {
        return $this->mysqlInstance->update('moderators', $data, array('id' => $id))->total_rows();
    }
    
    public function insertModerators($data) {
        return $this->mysqlInstance->insert('moderators', $data)->insert_id();
    }
    
    public function checkModMac($params){
        if (!is_array($params)) {
            $params = array('mac' => $params);
    }
        return $this->mysqlInstance->count()->from('moderators')->where($params)->get()->counter();
    }

    public function getAllModeratorTasks($moderator_id = FALSE) {
        $add_where = ($moderator_id !== FALSE ? " WHERE moderator_tasks.to_usr = $moderator_id": '');
        return $this->mysqlInstance->query("select moderator_tasks.*, unix_timestamp(end_time) as `end_time`
                                            from moderator_tasks
                                            $add_where
                                            order by id")->all();
                                            /*where moderator_tasks.ended = 0 $add_where*/
    }

    public function getTotalRowsAllVideoTasks($where = array(), $like = array()) {
        $params = array(
            'where' => $where
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAllVideoTasks($params, TRUE);
    }

    public function getAllVideoTasks($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('video_on_tasks')
                            ->join('video', 'video.id', 'video_on_tasks.video_id', 'INNER');

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($where)) {
            $this->mysqlInstance->where($where);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], ' OR ');
        }

        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        } else {
            $this->mysqlInstance->orderby('date_on');
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getVideoGenres() {
        return $this->mysqlInstance->from('genre')->orderby('title')->get()->all();
    }
    
    public function getCategoriesGenres($param = array()) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from('media_category');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        } else {
            $this->mysqlInstance->orderby('id');
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], ( array_key_exists('offset', $param['limit']) ? $param['limit']['offset']: FALSE ) );
        }

        return $this->mysqlInstance->get()->all();
    }

    public function getTotalRowsCategoriesGenresList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('media_category')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function insertCategoriesGenres($param){
        return $this->mysqlInstance->insert('media_category', $param)->insert_id();
    }

    public function updateCategoriesGenres($data, $param){
        unset($data['id']);
        return $this->mysqlInstance->update('media_category', $data, $param)->total_rows();
    }

    public function deleteCategoriesGenres($param){
        return $this->mysqlInstance->delete('media_category', $param)->total_rows();
    }

    public function getVideoCategories() {
        return$this->mysqlInstance->from('cat_genre')->orderby('category_alias, id')->get()->all();
    }

    public function getVideoCatGenres($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from('cat_genre')->join('media_category', 'cat_genre.category_alias', 'media_category.category_alias', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['having'])) {
            $this->mysqlInstance->having($param['having']);
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        } else {
            $this->mysqlInstance->orderby('cat_genre.id');
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], ( array_key_exists('offset', $param['limit']) ? $param['limit']['offset']: FALSE ) );
        }

        return $this->mysqlInstance->get()->all();
    }

    public function getTotalRowsVideoCatGenresList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('cat_genre')
            ->join('media_category', 'cat_genre.category_alias', 'media_category.category_alias', 'LEFT')
            ->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function insertVideoCatGenres($data = array()){
        return $this->mysqlInstance->insert('cat_genre', $data['data'])->total_rows();
    }

    public function updateVideoCatGenres($data = array()){
        return $this->mysqlInstance->update('cat_genre', $data['data'], $data['where'])->total_rows();
    }

    public function deleteVideoCatGenres($param){
        return $this->mysqlInstance->delete('cat_genre', $param)->total_rows();
    }

    public function checkName($params) {
        $where['name'] = $params['name'];
        if (array_key_exists('year', $params) && !empty($params['year'])) {
            $where['year'] = $params['year'];
        }
        if (array_key_exists('id<>', $params) && !empty($params['id<>'])) {
            $where['id<>'] = $params['id<>'];
        }
        return $this->mysqlInstance->count()->from('video')->where($where)->get()->counter();
    }
    
    public function saveScreenshotData($data) {
        $ext = end(explode('.', $data['name']));
        $insert_id = $this->mysqlInstance->insert('screenshots', array(
            'name' => (!empty($data['video_episodes']) ? '_' . $data['video_episodes']: '') . ".$ext",
            'size' => $data['size'],
            'type' => $data['type'],
            'media_id' => $data['media_id'],
            'video_episodes' => (isset($data['video_episodes']) ? $data['video_episodes']: 0)
        ))->insert_id();
        if (!empty($insert_id)) {
            $this->mysqlInstance->query("UPDATE `screenshots` SET `name` = CONCAT_WS('', `id`, `name`) WHERE `id` = '$insert_id'");
        }

        return $insert_id;
    }
    
    public function removeScreenshotData($param) {
        if (is_numeric($param)) {
            $param = array('id' => $param);
        }
        return $this->mysqlInstance->delete('screenshots', $param)->total_rows();
    }
    
    public function cleanScreenshotData() {
        return $this->mysqlInstance->delete('screenshots', array('media_id' => 0))->total_rows();
    }
    
    public function updateScreenshotData($param, $where) {

        if (is_numeric($param)) {
            $param = array('media_id' => $param);
        }
        if (is_numeric($where)) {
            $where = array('id' => $where);
        }

        return $this->mysqlInstance->update('screenshots', $param, $where)->total_rows();
    }
    
    public function getScreenshotData($params = array(), $all = FALSE) {
        if (is_numeric($params)) {
            $params = array('media_id' => $params);
        }
        $this->mysqlInstance->from('screenshots')->where($params)->groupby('video_episodes')->orderby(array('video_episodes' => 'asc', 'id'=>'desc'));
        if ($all) {
            $this->mysqlInstance->limit(10);
        }

        return $all ? $this->mysqlInstance->get()->all() : $this->mysqlInstance->get()->first('id');
    }
    
    public function insertVideo($data) {
        return $this->mysqlInstance->insert('video', $data)->insert_id();
    }
    
    public function updateVideo($data, $id) {
        return $this->mysqlInstance->update('video', $data , array('id' => $id))->total_rows();
    }
    
    public function getVideoByParam($param) {
        return $this->mysqlInstance->from('video')->where($param)->get()->first();
    }

    public function mowingCategoriesRows($curr_id, $curr_pos, $target_pos, $direction){
        
        if ($direction == 'back') {
            $field_update = 'num = num + 1';
            $where = " num >= $target_pos and num < $curr_pos ";
        } else {
            $field_update = " num = num - 1 ";
            $where = " num > $curr_pos and num <= $target_pos ";
        }

        if ($this->mysqlInstance->query("UPDATE `media_category` SET $field_update WHERE $where ")->total_rows() &&
            $this->updateCategoriesGenres(array('num' => $target_pos), array('id'=>$curr_id))){
            return TRUE;
        }
        
        return FALSE;
    }

    public function getSeasonData($where = array()){
        $this->mysqlInstance
            ->select(array(
                'V_S.video_id as video_id',
                'V_S.id as season_id',
                'V_S.season_number',
                'V_S.season_name',
                'V_S.season_original_name',
                'V_S.season_series as `series_count`',
                'V_S_S.id as series_id',
                'V_S_S.series_number',
                'V_S_S.series_name',
                'V_S_S.series_original_name',
                'V_S_S.series_files',
                'V_S_F.file_name',
                'V_S_F.id as series_files_id'
            ))
            ->from('video_season as V_S')
            ->join('video_season_series as V_S_S', 'V_S.id', 'V_S_S.season_id', 'LEFT')
            ->join('video_series_files as V_S_F', 'V_S_S.id', 'V_S_F.series_id', 'LEFT')
            ->where($where)
        ->orderby(array(
            'V_S.season_number' => 'ASC',
            'V_S_S.series_number' => 'ASC'
        ));
        return $this->mysqlInstance->get()->all();
    }

    public function insertSeason($data){
        return $this->mysqlInstance->insert('video_season', $data)->insert_id();
    }

    public function updateSeason($data, $where){
        return $this->mysqlInstance->update('video_season', $data, $where)->total_rows();
    }

    public function insertSeries($data){
        return $this->mysqlInstance->insert('video_season_series', $data)->insert_id();
    }

    public function updateSeries($data, $where){
        return $this->mysqlInstance->update('video_season_series', $data, $where)->total_rows();
    }

    public function getSeriesFiles($where, $counter = FALSE){
        $this->mysqlInstance
            ->select(array(
                'V_S_F.*',
                'V_S_S.series_number',
                'V_S.season_number',
            ))
            ->from('video_series_files AS V_S_F')
            ->join('video_season_series AS V_S_S', 'V_S_F.series_id', 'V_S_S.id', 'LEFT')
            ->join('video_season AS V_S', 'V_S_S.season_id', 'V_S.id', 'LEFT')
            ->where($where);
        if ($counter) {
            $count = $this->mysqlInstance->count()->get()->all('count(*)');
            return  !empty($count) ? (int) array_sum($count) : 0;
        }
        return $this->mysqlInstance->get()->all();
    }

    public function insertSeriesFiles($data){

        $file_id = $this->mysqlInstance->insert('video_series_files', $data)->insert_id();

        if (isset($data['accessed']) && $data['accessed'] == 1 && $file_id){

            $file = Mysql::getInstance()->from('video_series_files')->where(array('id' => $file_id))->get()->first();

            if ($file){
                if ($file['series_id']){
                    //delete watched flags for season
                    $season_id = Mysql::getInstance()->from('video_season_series')->where(array('id' => $file['series_id']))->get()->first('id');

                    if ($season_id){
                        Mysql::getInstance()->delete('user_played_movies', array(
                            'video_id'   => $file['video_id'],
                            'season_id'  => $season_id,
                            'episode_id' => 0,
                            'file_id'    => 0,
                            'watched'    => 1
                        ));
                    }
                }

                //delete watched flags for movie
                Mysql::getInstance()->delete('user_played_movies', array(
                    'video_id'   => $file['video_id'],
                    'season_id'  => 0,
                    'episode_id' => 0,
                    'file_id'    => 0,
                    'watched'    => 1
                ));
            }
        }

        return $file_id;
    }

    public function updateSeriesFiles($data, $where){
        if (isset($data['accessed']) && $data['accessed'] == 1 && isset($where['id'])){

            $file = Mysql::getInstance()->from('video_series_files')->where(array('id' => $where['id']))->get()->first();

            if ($file){
                if ($file['series_id']){
                    //delete watched flags for season
                    $season_id = Mysql::getInstance()->from('video_season_series')->where(array('id' => $file['series_id']))->get()->first('id');

                    if ($season_id){
                        Mysql::getInstance()->delete('user_played_movies', array(
                            'video_id'   => $file['video_id'],
                            'season_id'  => $season_id,
                            'episode_id' => 0,
                            'file_id'    => 0,
                            'watched'    => 1
                        ));
                    }
                }

                //delete watched flags for movie
                Mysql::getInstance()->delete('user_played_movies', array(
                    'video_id'   => $file['video_id'],
                    'season_id'  => 0,
                    'episode_id' => 0,
                    'file_id'    => 0,
                    'watched'    => 1
                ));
            }
        }
        return $this->mysqlInstance->update('video_series_files', $data, $where)->total_rows();
    }

    public function deleteSeriesFiles($param) {
        if (is_numeric($param)) {
            $param = array('id' => $param);
        }
        return $this->mysqlInstance->delete('video_series_files', $param)->total_rows();
    }

    public function deleteSeason($param) {
        if (is_numeric($param)) {
            $param = array('id' => $param);
        }
        return $this->mysqlInstance->delete('video_season', $param)->total_rows();
    }

    public function deleteSeries($param) {
        if (is_numeric($param)) {
            $param = array('id' => $param);
        }
        return $this->mysqlInstance->delete('video_season_series', $param)->total_rows();
    }

    public function getAdsTotalRows($where = array(), $like = array()) {
        $params = array(
            'where' => $where,
            'like' => $like
        );
        return $this->getAdsList($params, TRUE);
    }

    public function getAdsList($param, $counter = FALSE) {

        $date = new \DateTime();
        $date->modify('1 month ago');
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from("vclub_ad as V_A")
            ->join('vclub_ads_log as V_A_L', 'V_A.id', 'V_A_L.vclub_ad_id AND UNIX_TIMESTAMP(V_A_L.added)>' . $date->getTimestamp(), 'LEFT');

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }

        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        $this->mysqlInstance->groupby('V_A.id');
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        if ($counter) {
            $count = $this->mysqlInstance->count()->get()->all('count(*)');
            return  !empty($count) ? array_sum($count) : 0;
        }

        return $this->mysqlInstance->get()->all();
    }

    public function saveWatchedSettings($params){
        return $this->mysqlInstance->update('watched_settings', $params)->total_rows();
    }
}
