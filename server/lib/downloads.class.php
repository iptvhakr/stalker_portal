<?php

class Downloads implements \Stalker\Lib\StbApi\Downloads
{

    public function getAll(){
        return System::base64_decode(Mysql::getInstance()->from('user_downloads')->where(array('uid' => Stb::getInstance()->id))->get()->first('downloads'));
    }

    public function save(){
        $downloads = @$_REQUEST['downloads'];

        if (empty($downloads)){
            $downloads = '""';
        }

        $downloads = System::base64_encode($downloads);

        $record = Mysql::getInstance()->from('user_downloads')->where(array('uid' => Stb::getInstance()->id))->get()->first();

        if (empty($record)){
            return Mysql::getInstance()->insert('user_downloads', array('downloads' => $downloads, 'uid' => Stb::getInstance()->id))->insert_id();
        }else{
            return Mysql::getInstance()->update('user_downloads', array('downloads' => $downloads), array('uid' => Stb::getInstance()->id))->result();
        }
    }

}

