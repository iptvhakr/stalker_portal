<?php

namespace Model;

class TasksModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsTasksList($incoming = array(), $all = FALSE) {
        /*$incoming['select'] = '*';*/
        if ($all) {
            $incoming['like'] = array();    
        }
        return $this->getTasksList($incoming, TRUE);
    }

    public function getTasksList($param, $counter = FALSE) {

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from($param['from']);
        if (array_key_exists('joined', $param)) {
            foreach ($param['joined'] as $table => $keys) {
                $this->mysqlInstance->join($table, $keys['left_key'], $keys['right_key'], $keys['type']);
            }
        }
        $this->mysqlInstance->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['groupby'])) {
            $this->mysqlInstance->groupby($param['groupby']);
        }

        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit']) && !$counter) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->count()->get()->all();
            if (count($result) > 1) {
                return count($result);
            } else if (!empty ($result[0])){
                list($key, $data) = each($result[0]);
            } else {
                return 0;
            }
            return $data;
        }
//        print_r($this->mysqlInstance->get());
//        exit;
        
        return $this->mysqlInstance->get()->all();
    }

    public function getAdmins($id = FALSE) {
        $this->mysqlInstance->from('administrators');
        if ($id !== FALSE) {
            $this->mysqlInstance->where(array('id'=>$id));
        }
        return $this->mysqlInstance->orderby('login')->get()->all();
    }
    
    public function getSimpleTasks($task_id, $table) {
        return $this->mysqlInstance->from($table)->where(array('id' => $task_id))->get()->first();
    }

    public function videoLogWrite($video, $text, $moderator_id) {
        return $this->mysqlInstance->insert('video_log', array(
                    'action' => $text,
                    'video_id' => $video['id'],
                    'video_name' => $video['name'],
                    'moderator_id' => $moderator_id,
                    'actiontime' => 'NOW()'
                ))->insert_id();
    }
    
    public function updateSimpleTasks($task_id, $table, $params) {
        return $this->mysqlInstance->update($table, $params, array('id' => $task_id))->total_rows();
    }
    
    public function getVideoById($id) {
        return $this->mysqlInstance->from('video')->where(array('id' => $id))->get()->first();
    }
    
    public function getVideoTaskDetailInfoValues($task_id) {
        return $this->mysqlInstance->query("
            SELECT 
            V.`name` as `name`,
            if(V.`hd` = 0, 'SD', 'HD') as `quality`,
            A_1.`login` as `from_usr`,
            A_1.`id` as `from_usr_id`,
            A_2.`login` as `to_usr`,
            A_2.`id` as `to_usr_id`,
            if(M_T.`ended`=0 and M_T.`archived`=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(M_T.`start_time`))>864000, 3, M_T.`ended` + M_T.`rejected`) as `state`,
            M_H.`comment` as `comment`,
            M_T.`start_time` as `added`
             FROM moderator_tasks as M_T 
            left join video as V on M_T.media_id = V.id
            left join `moderators_history` as M_H on M_T.id = M_H.task_id
            left join `administrators` as A_1 on M_H.from_usr = A_1.id
            left join `administrators` as A_2 on M_H.to_usr = A_2.id
            where M_T.id = $task_id
            order by M_H.send_time
            limit 1")->first();
        
    }
    
    public function getVideoTaskChatList($task_id, $after_id = 0) {
        $this->mysqlInstance->select(
            array( 
                "M_H.*", 
                "A.login as `from_usr_login`",
                "M_T.`start_time`",
                "M_T.`end_time`",
                "M_T.`ended`",
                "M_T.`rejected`",
                "M_T.`archived`",
                "M_T.`archived_time`",
                "if(M_T.`ended`=0 and M_T.`archived`=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(M_T.`start_time`))>864000, 3, M_T.`ended` + M_T.`rejected`) as `state`"
                ))
            ->from("`moderators_history` as M_H")
            ->join("`administrators`  as A", "M_H.from_usr", "A.id", "LEFT")
            ->join("`moderator_tasks`  as M_T", "M_H.task_id", "M_T.id", "LEFT")
            ->where(array('M_H.task_id' => $task_id, 'M_H.id >= ' => $after_id))
            ->orderby("M_H.id");
        return $this->mysqlInstance->get()->all();
    }
    
    public function setReadedTaskMessage($task_id, $user_id){
        $this->mysqlInstance->query("update moderators_history set readed=1 where readed=0 and to_usr=$user_id and task_id = $task_id");
    }
    
    public function setTaskMessage($from_user_id, $to_user_id, $task_id, $reply_to_id, $message){
        $this->mysqlInstance->query("insert into moderators_history set"
                . " task_id = $task_id, "
                . " from_usr=$from_user_id, "
                . " to_usr=$to_user_id, "
                . " comment = '$message',"
                . " send_time = NOW(),"
                . " readed=0, "
                . " reply_to = $reply_to_id");
    }
    
    public function getKaraokeTaskChatList($task_id) {
        $this->mysqlInstance->select(
            array( 
                "K.*",
                "concat_ws(' - ', K.`singer`, K.`name`) as `name`",
                "K.`id` as `id`",
                "A.`login` as `from_usr`",
                "A.`id` as `from_usr_id`",
                "K.`done_time` as `end_time`",
                "if(K.done=0 and K.archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(K.added))>864000, 3, K.done) as `state`"
            ))
            ->from("`karaoke` as K")
            ->join("`administrators`  as A", "K.add_by", "A.id", "LEFT")
            ->where(array('K.id' => $task_id));        
        return $this->mysqlInstance->get()->all();
    }
}