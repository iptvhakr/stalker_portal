<?php
/**
 * Main Karaoke class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Karaoke
{
    
    private $db;
    private $stb;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Karaoke();
        }
        return self::$instance;
    }
    
    public function __construct(){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
    }
    
    public function createLink(){
        
        preg_match("/auto \/media\/(\d+).mpg$/", $data_req, $tmp_arr);
        
        $media_id = $tmp_arr[1];

        $master = new KaraokeMaster();
        
        try {
            $res['data'] = $master->play($media_id);
        }catch (Exception $e){
            echo trigger_error($e->getMessage());
        }
        
        var_dump($res);
        
        return $res;
    }
}

?>