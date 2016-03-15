<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Config;

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

    public function createDownloadLink($type, $media_id, $user_id, $param = ''){

        $link_hash = md5(microtime(1).uniqid());

        $id = Mysql::getInstance()->insert('download_links',
            array(
                'link_hash' => $link_hash,
                'uid'       => $user_id,
                'type'      => $type,
                'media_id'  => $media_id,
                'param1'    => $param,
                'added'     => 'NOW()'
            ))->insert_id();

        return 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
            .'://'.$_SERVER['HTTP_HOST']
            .'/'.str_replace('/', '', Config::getSafe('portal_url', '/stalker_portal/'))
            .'/server/api/get_download_link.php?lid='.($id ? $link_hash : '');
    }
}

