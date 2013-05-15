<?php

class User
{
    private static $instance = null;
    private $mac;
    private $storage_name;

    public static function getInstance(){

        if (self::$instance === null){
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct(){
        $this->mac = !empty($_SERVER['HTTP_X_FROM']) ? $_SERVER['HTTP_X_FROM'] : '';
    }

    /**
     * @return string mac
     */
    public function getMac(){
        return $this->mac;
    }
    
    /**
     * Create stb home directory by MAC or clean it
     *
     * @return boolean
     */
    public function checkHome(){

        if (empty($this->mac)){
            return false;
        }

        $home = NFS_HOME_PATH.$this->mac;

        if (!is_dir($home)){
            umask(0);
            if(!mkdir($home, 0777)){
                throw new IOException('Could not create directory '.$home.' on '.$this->storage_name);
            }
        }else{
            if ($handle = @opendir($home)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && preg_match("/([\S\s]+)$/", $file)) {
                        unlink($home.'/'.$file);
                    }
                }
                @closedir($handle);
            }else{
                throw new IOException('Could not open directory '.$home.' on '.$this->storage_name);
            }
        }
        return true;
    }

    public function setStorageName($storage_name){

        $this->storage_name = $storage_name;
    }
}


?>