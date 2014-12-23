<?php

namespace Model;

class VideoClubModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getAllVideo($filter = '') {
//        $filter = ' path like "%00%" ';
        $where = '';
        if (!empty($filter)) {
            $where = "where " . (is_string($filter) ? $filter : (is_array($filter) ? implode(' and ', $filter) : ''));
        }
        return $this->mysqlInstance->query("select video.*, media_claims.media_type, media_claims.media_id, media_claims.sound_counter,"
                                            . " media_claims.video_counter, video_on_tasks.id as task_id, video_on_tasks.video_id as task_video_id,  "
                                            . " video_on_tasks.date_on as task_date_on, video_on_tasks.added as task_added "
                                        . "from video "
                                            . "left join media_claims on video.id=media_claims.media_id and media_claims.media_type='vclub' "
                                            . "left join video_on_tasks on video.id=video_on_tasks.video_id "
                                        . " $where "
                                        . " group by video.id limit 0, 100")->all(); //order by video.id desc
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
        $obj = $this->mysqlInstance->count()->from('video_log')->where($where);
        if (!empty($like)) {
            $obj = $obj->like($like, 'OR');
        }
        return $obj->get()->counter();
    }

    public function getVideoLog($param){
        $obj = $this->mysqlInstance->select($param['select'])
                ->from('video_log')->join('administrators', 'video_log.moderator_id', 'administrators.id', 'LEFT')
                ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        return $obj->get()->all();
    }

    public function removeVideoById($video_id) {
        return $this->mysqlInstance->delete('video', array('id' => $video_id))->total_rows();
    }

    public function disableVideoById($video_id) {
        return $this->mysqlInstance->update('video', array('accessed' => 0, 'added' => 'NOW()'), array('id' => $video_id))->total_rows();
    }

    public function enableVideoById($video_id) {
        $this->mysqlInstance->update('updated_places', array('vclub' => 1));
        return $this->mysqlInstance->update('video', array('accessed' => 1, 'added' => 'NOW()'), array('id' => $video_id))->total_rows();
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
    
    public function getModerators($id = FALSE) {
        $obj = $this->mysqlInstance->from('moderators');
        if ($id !== FALSE) {
            $obj = $obj->where(array('id' => $id))->get()->first(); 
        } else {
            $obj = $obj->get()->all();
        }
        return $obj;
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
    
    public function checkModMac($mac_adress){
        return $this->mysqlInstance->count()->from('moderators')->where(array('mac' => $mac_adress))->get()->counter();
    }
    
    public function getAllModeratorTasks($moderator_id = FALSE) {
        $add_where = ($moderator_id !== FALSE ? " and moderator_tasks.to_usr = $moderator_id": '');
        return $this->mysqlInstance->query("select moderator_tasks.*
                                            from moderator_tasks
                                            where moderator_tasks.ended = 0 $add_where
                                            order by id")->all();
    }
    public function getAllVideoTasks($params = FALSE) {
        $query_obj =  $this->mysqlInstance->select('video_on_tasks.*, video_on_tasks.id as task_id, video_on_tasks.added as task_added, video.*')
                                    ->from('video_on_tasks')
                                    ->join('video', 'video.id', 'video_on_tasks.video_id', 'INNER');
        if ($params !== FALSE) {
            $query_obj = $query_obj->where($params);    
        }
        return $query_obj->orderby('date_on')->get()->all();
    }
    
    public function getVideoGenres() {
        return $this->mysqlInstance->from('genre')->orderby('title')->get()->all();
    }
    
    public function getCategoriesGenres() {
        return $this->mysqlInstance->from('media_category')->orderby('id')->get()->all();
    }
    
    public function getVideoCategories() {
        return $this->mysqlInstance->from('cat_genre')->orderby('category_alias, id')->get()->all();
    }
    
    public function checkName($name) {
        return $this->mysqlInstance->count()->from('video')->where(array('name' => $name))->get()->counter();
    }
    
    public function saveScreenshotData($data) {
        return $this->mysqlInstance->insert('screenshots', array('name' => $data['name'],'size' => $data['size'],'type' => $data['type']))->insert_id();
    }
    public function removeScreenshotData($id) {
        return $this->mysqlInstance->delete('screenshots', array('id' => $id))->total_rows();
    }
    
    public function cleanScreenshotData() {
        return $this->mysqlInstance->delete('screenshots', array('media_id' => 0))->total_rows();
    }
    
    public function updateScreenshotData($video_id, $id) {
        return $this->mysqlInstance->update('screenshots', array('media_id' => $video_id), array('id' => $id))->total_rows();
    }
    
    public function getScreenshotData($video_id) {
        return $this->mysqlInstance->from('screenshots')->where(array('media_id' => $video_id))->orderby(array('id' => 'desc'))->get()->first('id');
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
}
