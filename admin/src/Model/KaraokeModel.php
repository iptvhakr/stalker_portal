<?php

namespace Model;

class KaraokeModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsKaraokeList($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getKaraokeList($params, TRUE);
    }

    public function getKaraokeList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select']);
        $obj = $obj->from('karaoke')
                        ->join('administrators', 'administrators.id', 'karaoke.add_by', 'LEFT')
                        ->join('media_claims', 'karaoke.id', 'media_claims.media_id', 'LEFT')
                        ->where($param['where']);
        if (!empty($param['like'])) {
            $obj = $obj->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $obj = $obj->orderby($param['order']);
        }
        if (!$counter) {
            $obj = $obj->groupby(array("karaoke.id", "karaoke.add_by"));
        }
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }

    public function updateKaraoke($param, $where){
        $where = (is_array($where)? $where: array('id'=>$where));
        return $this->mysqlInstance->update('karaoke', $param, $where)->total_rows() || 1;
    }
    
    public function insertKaraoke($param){
        return $this->mysqlInstance->insert('karaoke', $param)->insert_id();
    }
    
    public function deleteKaraoke($param){
        return $this->mysqlInstance->delete('karaoke', $param)->total_rows();
    }
}
