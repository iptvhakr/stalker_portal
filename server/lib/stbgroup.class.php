<?php

use Stalker\Lib\Core\Mysql;

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
        $this->db = Mysql::getInstance();
    }
    
    public function setName($group_name){
        
        $this->name = $group_name;
        $this->id = intval($this->db->from('stb_groups')->where(array('name' => $group_name))->get()->first('id'));
    }
    /**
     * Return group list.
     *
     * @return array group list
     */
    public function getAll(){
        
        return $this->db->from('stb_groups')->get()->all();
    }
    
    public function getGroupIdByUid($uid){
        
        return $this->db->from('stb_in_group')->where(array('uid' => intval($uid)))->get()->first('stb_group_id');
    }
    
    /**
     * Return group by group id.
     *
     * @param int $group_id
     * @return array
     */
    public function getById($group_id){
        
        return $this->db->from('stb_groups')->where(array('id' => intval($group_id)))->get()->first();
    }
    
    /**
     * Add group by name.
     *
     * @param string $group_name
     * @return int|false group id() or error
     */
    public function add($group_name){
        
        $group = $this->db->from('stb_groups')->where(array('name' => $group_name))->get()->all();
        
        if (empty($group)){
            $this->id = $this->db->insert('stb_groups', array('name' => $group_name))->insert_id();
            return $this->id;
        }
        
        return false;
    }
    
    /**
     * Delete group by group id.
     *
     * @param int $group_id
     * @return bool result
     */
    public function del($group_id){
        
        $group_id = intval($group_id);
        
        $group = $this->db->from('stb_groups')->where(array('id' => $group_id))->get()->all();
        
        if (!empty($group)){
            return $this->db->delete('stb_groups', array('id' => $group_id))->result();
        }
        
        return false;
    }
    
    /**
     * Update group by group id.
     *
     * @param array $data
     * @param int $group_id
     * @return int|bool
     */
    public function set($data, $group_id = 0){
        
        if ($group_id){
            return $this->db->update('stb_groups', $data, array('id' => $group_id))->result();
        }else{
            return $this->db->insert('stb_groups', $data)->insert_id();
        }
    }
    
    /**
     * Add member to group.
     *
     * @param array $data
     * @return int|bool new member id or error
     */
    public function addMember($data){
        
        if (!empty($data) && is_array($data) && $data['uid']){
            
            $record = $this->getMemberByUid($data['uid']);
            
            if (empty($record)){
                return $this->db->insert('stb_in_group', $data)->insert_id();
            }
        }
        
        return false;
    }
    
    /**
     * Update member in group.
     *
     * @param array $data
     * @param int $member_id
     * @return bool result
     */
    public function setMember($data, $member_id){
        
        if (!empty($data) && is_array($data)){
            
            $record = $this->getMember($member_id);
            
            if (!empty($record)){
                return $this->db->update('stb_in_group', $data, array('id' => $member_id))->result();
            }
        }
        
        return false;
    }
    
    /**
     * Delete member from group.
     *
     * @param int $member_id
     * @return bool
     */
    public function removeMember($member_id){
        
        $member_id = intval($member_id);
        
        if ($member_id > 0){
            return $this->db->delete('stb_in_group',
                           array(
                               'id' => $member_id
                           ))->result();
        }
        
        return false;
    }
    
    /**
     * Return member by id.
     *
     * @param int $member_id
     * @return array member
     */
    public function getMember($member_id){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array(
                            'id' => intval($member_id)
                        ))
                        ->get()
                        ->first();
    }
    
    /**
     * Return member by uid.
     *
     * @param int $uid
     * @return array member
     */
    public function getMemberByUid($uid){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array(
                            'uid' => intval($uid)
                        ))
                        ->get()
                        ->first();
    }
    
    /**
     * Return all members from group.
     *
     * @param int $group_id
     * @return array member list
     */
    public function getAllMembersByGroupId($group_id){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array('stb_group_id' => intval($group_id)))
                        ->get()
                        ->all();
    }
    
    /**
     * Return all uids by group id.
     *
     * @param int $group_id
     * @return array uid list
     */
    public function getAllMemberUidsByGroupId($group_id){
        
        return $this->db
                        ->from('stb_in_group')
                        ->where(array('stb_group_id' => intval($group_id)))
                        ->get()
                        ->all('uid');
    }
}

?>