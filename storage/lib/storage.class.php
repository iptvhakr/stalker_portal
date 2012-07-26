<?php

abstract class Storage{

    protected $media_ext_arr = array(
        'mpg',
        'mpeg',
        'avi',
        'ts',
        'mkv',
        'mp4',
        'mov',
        'm2ts'
    );

    protected $media_ext_str = '';
    protected $storage_name = '';

    /**
     * @var User
     */
    protected $user;

    public function __construct(){
        $this->media_ext_str = join('|', $this->media_ext_arr);

        if (empty($_SERVER['SERVER_ADDR']) && empty($_SERVER['SERVER_NAME'])){
            return;
        }

        $this->storage_name = ($_SERVER['SERVER_NAME'])? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

        $this->user = User::getInstance();
        $this->user->setStorageName($this->storage_name);
    }
}

class IOException extends Exception{}

?>