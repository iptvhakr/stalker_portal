<?php
/**
 * STB group management class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class StbGroup
{
    
    private $db;
    private $name = '';
    private $id = 0;
    
    public function __construct(){
        $db = Mysql::getInstance();
    }
    
    public function setName($group_name){
        
        $this->name = $group_name;
        $this->id = intval($this->db->from('stb_groups')->where(array('name' => $group_name))->get()->first('id'));
    }
    
    public function add($group_name){
        
        $group = $this->db->from('stb_groups')->where(array('name' => $group_name))->get()->all();
        
        if (empty($group)){
            $this->id = $this->db->insert('stb_groups', array('name' => $group_name));
            return $this->id;
        }
        
        return false;
    }
    
    public function del($group_name){
        
        $group = $this->db->from('stb_groups')->where(array('name' => $group_name))->get()->all();
        
        if (!empty($group)){
            return $this->db->delete('stb_groups', array('name' => $group_name));
        }
        
        return false;
    }
    
    public function push($uid){
        
        if (empty($this->name)){
            return false;
        }
        
        if (empty($this->getRecord($uid))){
            return $this->db->insert('stb_in_group',
                           array(
                               'stb_group_id' => $this->id,
                               'stb_id'       => $uid
                           ));
        }
        
        return false;
    }
    
    public function remove($uid){
        
        if (empty($this->name)){
            return false;
        }
        
        if (!empty($this->getRecord($uid))){
            return $this->db->delete('stb_in_group',
                           array(
                               'stb_group_id' => $this->id,
                               'stb_id'       => $uid
                           ));
        }
        
        return false;
    }
    
    private function getRecord($uid){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array(
                            'stb_group_id' => $this->id,
                            'stb_id'       => $uid
                        ))
                        ->get()
                        ->first();
    }
    
    public function getAllMembersByGroupId($group_id){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array('stb_group_id' => $group_id))
                        ->get()
                        ->all();
    }
}

?>