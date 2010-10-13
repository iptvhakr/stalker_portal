<?php
/**
 * Log engine.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Log
{
    private $db;
    private $stb;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
    }
    
    public function savePageGenerationTime($time){
        $time = $time*1000;
        
        if ($time >= 500){
            $default_row = '500ms';
        }elseif ($time >= 400){
            $default_row = '400ms';
        }elseif ($time >= 300){
            $default_row = '300ms';
        }elseif ($time >= 200){
            $default_row = '200ms';
        }elseif ($time >= 100){
            $default_row = '100ms';
        }else{
            $default_row = '0ms';
        }
        
        $item = $this->db->from('generation_time')->where(array('time' => $default_row))->get()->first();
        
        $this->db->update('generation_time',
                          array('counter' => $item['counter'] + 1),
                          array('time' => $default_row));
        
    }
}

?>