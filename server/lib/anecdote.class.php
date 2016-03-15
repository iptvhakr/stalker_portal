<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;

class Anecdote implements \Stalker\Lib\StbApi\Anecdote
{
    private $db;
    private $stb;
    
    public function __construct(){
        
        $this->db   = Mysql::getInstance();
        $this->stb  = Stb::getInstance();
        $this->page = @intval($_REQUEST['p']);
    }
    
    public function getByPage(){
        
        $pages = $this->db->from('anec')->count()->get()->counter();
        
        $response = array();
        $response['total_items'] = $pages;
        
        $response['data'] = $this->prepareData($this->db
                                                        ->select('*, DATE(added) as added')
                                                        ->from('anec')
                                                        ->orderby('id', 'DESC')
                                                        ->limit(1, $this->page)
                                                        ->get()
                                                        ->first());
        
        return $response;
    }
    
    private function prepareData($data){
        
        if (empty($data)){
            return null;
        }
        
        $data['anec_body'] = nl2br($data['anec_body']);
        $data['rating']    = $this->getRating($data['id']);
        $data['voted']     = $this->isVoted($data['id']);
        
        return $data;
    }
    
    private function getRating($id){
        
        $rating = $this->db->from('anec_rating')->count()->where(array('anec_id' => $id))->get()->counter();
        
        /*if ($rating > 0){
            $rating = '+'.$rating;
        }
        
        if ($rating == 0){
            $rating = '';
        }*/
        
        return $rating;
    }
    
    private function isVoted($id){
        
        return $this->db->from('anec_rating')->count()->where(array('anec_id' => $id, 'uid' => $this->stb->id))->get()->counter();
    }
    
    public function getBookmark(){
        
        $bookmark = $this->db->from('anec_bookmark')->where(array('uid' => $this->stb->id))->get()->first();
        
        if (!empty($bookmark)){
            
            return $this->db->from('anec')->count()->where(array('id>=' => $bookmark['anec_id']))->orderby('added', 'DESC')->get()->counter();
            
        }
        
        return 0;
    }
    
    public function setBookmark(){

        $anec_id = intval($_REQUEST['anec_id']);

        $bookmark = $this->db->from('anec_bookmark')->where(array('uid' => $this->stb->id))->get()->first();
        
        if (!empty($bookmark)){
            
            return $this->db->update('anec_bookmark',
                                 array(
                                     'anec_id' => $anec_id
                                 ),
                                 array(
                                     'uid' => $this->stb->id
                                 ));
            
        }else{
            return $this->db->insert('anec_bookmark',
                                     array(
                                         'uid'     => $this->stb->id,
                                         'anec_id' => $anec_id
                                     ))
                                     ->insert_id();
        }
    }
    
    public function setVote(){

        $anec_id = intval($_REQUEST['anec_id']);

        if (!$this->isVoted($anec_id)){
            
            $this->db->insert('anec_rating',
                              array(
                                  'uid'     => $this->stb->id,
                                  'anec_id' => $anec_id
                               ))
                               ->insert_id();
        }
        
        return $anec_id;
    }
    
    public function setReaded(){
        
        return $this->db->insert('readed_anec',
                                 array(
                                     'mac'    => $this->stb->mac,
                                     'readed' => 'NOW()'
                                 ))->insert_id();
    }
}

?>