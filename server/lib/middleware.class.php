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
     * Return online boxes users id's
     *
     * @return array users id's
     */
    public static function getOnlineUsersId(){
        return Mysql::getInstance()
            ->from('users')
            ->where(array(
                'UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout')*2
            ))
            ->get()
            ->all('id');
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

    public static function isValidMAC($mac){

        return preg_match("/^00:1A:79:[0-9,A-F]{2}:[0-9,A-F]{2}:[0-9,A-F]{2}$/", $mac);
    }

    public static function getClonesIPAddress(){

        $cache = Cache::getInstance();

        $mac = Stb::getInstance()->mac;

        if(empty($mac)){
            return false;
        }

        $history = Cache::getInstance()->get($mac);

        if ($history === false){
            $history = array();
        }else{
            $history = json_decode($history, true);

            if ($history === null){
                $history = array();
            }
        }

        $history[] = Stb::getInstance()->ip;

        $result = $cache->set($mac, json_encode($history), array(), 10);

        //var_dump($history);

        if (count($history) > 1){
            return $history;
        }

        return false;
    }

    /**
     * Clean perhaps "dirty" array of mac addresses
     *
     * @param array $macs
     * @return array clean mac address array
     */
    public static function normalizeMacArray($macs){

        $clean = array();

        foreach ($macs as $mac){

            $clean_mac = self::normalizeMac($mac);
            //var_dump($mac, $clean_mac);
            if ($clean_mac){
                $clean[] = $clean_mac; 
            }
        }

        return $clean;
    }
    
    public static function log($text, $type = null){
        
        if (!$type){
            $type = 'notice';
        }
        
        Mysql::getInstance()->insert('sys_log',
                          array(
                              'text' => $text,
                              'type' => $type
                          ));
    }

    public static function getThemes(){

        $path = realpath(PROJECT_PATH.'/../c/template/');

        if (!$path){
            return array();
        }

        $items = scandir($path);

        if (!$items){
            return array();
        }

        $themes = array();

        foreach ($items as $item){
            if ($item != '.' && $item != '..' && is_dir($path.'/'.$item)){
                $themes[] = $item;
            }
        }

        return $themes;
    }
}
