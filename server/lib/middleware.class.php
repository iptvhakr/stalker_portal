<?php
/**
 * Basic middleware class
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Middleware
{
       
    /**
     * Return all users id's
     *
     * @return array users id's
     */
    public static function getAllUsersId(){
        $db = Mysql::getInstance();
        //return $db->executeQuery('select * from users')->getValuesByName('id');
        return $db->get('users')->all('id');
    }
    
    /**
     * Return users ids by mac
     *
     * @param string $mac
     * @return int|false user id or error
     */
    public static function getUidByMac($mac){
        if ($mac){
            $mac = self::normalizeMac($mac);
            $db = Mysql::getInstance();
            
            //$id = $db->executeQuery('select * from users where mac="'.$mac.'"')->getValueByName(0, 'id');
            $id = $db->from('users')->where(array('mac' => $mac))->get()->first('id');
            
            if ($id > 0){
                return intval($id);
            }
        }
        return false;
    }
    
    /**
     * Return user id by pattern
     *
     * @param string $pattern
     * @return array users id's
     */
    public static function getUidsByPattern($pattern){
        
        if (!empty($pattern) && is_array($pattern)){
            
            $where = $pattern;
            
            $ids = Mysql::getInstance()->from('users')->where($where)->get()->all('id');
            return $ids;
        }
        
        return array();
    }

    
    /**
     * Clean perhaps "dirty" mac address
     *
     * @param string $mac 
     * @return string|false clean mac address or error if string don't looks like mac address
     */
    public static function normalizeMac($mac){
        $mac = iconv("WINDOWS-1251","UTF-8", $mac);
        
        $mac = strtoupper($mac);
        
        $pattern = array('А', 'В', 'С', 'Е'); // ru
        $replace = array('A', 'B', 'C', 'E'); // en
        
        $mac = str_replace($pattern, $replace, trim($mac));
        
        if (strlen($mac)==12){
            $mac = substr($mac, 0,2).":".substr($mac, 2,2).":".substr($mac, 4,2).":".substr($mac, 6,2).":".substr($mac, 8,2).":".substr($mac, 10,2);
        }
        
        if (strlen($mac)==17){
            return $mac;
        }else{
            return false;
        }
    }
    
    public static function log($text, $type = null){
        
        if (!$type){
            $type = 'notice';
        }
        
        $db = Mysql::getInstance();
        
        $this->db->insert('sys_log',
                          array(
                              'text' => $text,
                              'type' => $type
                          ));
    }
}

?>