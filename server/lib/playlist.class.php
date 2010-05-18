<?php
/**
 * Playlist management class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Playlist extends AjaxResponse 
{
    
    private $id = 0;
    
    public function __construct(){
        parent::__construct();
    }
    
    public function getAll(){
        
        return $this->db->from('playlists')->get()->all();
    }
    
    public function getById($playlist_id){
        
        return $this->db->from('playlists')->where(array('id' => intval($playlist_id)))->get()->first();
    }
    
    public function getByUid(){
        
        $stb_groups = new StbGroup();
        
        $group_id = $stb_groups->getGroupIdByUid($this->stb->id);
        
        $playlist_id = $this->db->from('playlists')->where(array('group_id' => intval($group_id)))->get()->first('id');
        
        $playlist = $this->getAllRecordsByPlaylistId($playlist_id);
        
        $this->setResponse('data', $playlist);
        
        return $this->getResponse();
    }
    
    public function add($playlist_name, $group_id){
        
        $playlist = $this->db->from('playlists')->where(array('name' => $playlist_name))->get()->all();
        
        if (empty($playlist)){
            $this->id = $this->db->insert('playlists', array('name' => $playlist_name, 'group_id' => intval($group_id)))->insert_id();
            return $this->id;
        }
        
        return false;
    }
    
    public function del($playlist_id){
        
        $playlist_id = intval($playlist_id);
        
        $playlist = $this->db->from('playlists')->where(array('id' => $playlist_id))->get()->all();
        
        if (!empty($playlist)){
            return $this->db->delete('playlists', array('id' => $playlist_id))->result();
        }
        
        return false;
    }
    
    public function set($data, $playlist_id = 0){
        
        if ($playlist_id){
            return $this->db->update('playlists', $data, array('id' => $playlist_id))->result();
        }else{
            return $this->db->insert('playlists', $data)->insert_id();
        }
    }
    
    public function addRecord($data){
        
        if (!empty($data) && is_array($data)){
            
            return $this->db->insert('playlist_members', $data)->insert_id();
        }
        
        return false;
    }
    
    public function updateRecord($data, $record_id){
        
        if (!empty($data) && is_array($data) && $record_id > 0){
            
            $record = $this->getRecord($record_id);
            
            if (!empty($record)){
                return $this->db->update('playlist_members', $data, array('id' => $record_id))->result();
            }
        }
        
        return false;
    }
    
    public function delRecord($record_id){
        
        $record_id = intval($record_id);
        
        if ($record_id > 0){
            return $this->db->delete('playlist_members',
                           array(
                               'id' => $record_id
                           ))->result();
        }
        
        return false;
    }
    
    public function getRecord($record_id){
        
        return $this->db
                        ->from('playlist_members')
                        ->where(array(
                            'id' => intval($record_id)
                        ))
                        ->get()
                        ->first();
    }
    
    public function getAllRecordsByPlaylistId($playlist_id){
        
        return $this->db
                        ->from('playlist_members')
                        ->where(array('playlist_id' => intval($playlist_id)))
                        ->get()
                        ->all();
    }
}

?>