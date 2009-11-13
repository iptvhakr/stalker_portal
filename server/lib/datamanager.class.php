<?php
/**
 * Factory of Data objects.
 * @package stalker_portal
 */

class DataManager
{
    private static $map = array(
        'user' => array('get_profile'),
        'itv'  => array(),
        'vod'  => array(),
        'karaoke'  => array(),
    );
    
    public static function create($type){
        $type = $type || '';
        
        foreach (self::$map as $name => $types){
            if (in_array($type, $types)){
                $class = ucfirst($name).'DataLoader';
                if (class_exists($class)){
                    return new $class;
                }
            }
        }
    }
}
?>