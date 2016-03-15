<?php

use Stalker\Lib\Core\Mysql;

/**
 * Advertising for main menu & hot offer;
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Advertising
{
    public function __construct(){}
    
    public function getMain(){

        $ad = Mysql::getInstance()->from('main_page_ad')->get()->all();
        
        if (count($ad) > 0){
            return $ad[0];
        }
        
        return null;
    }
    
    public function getMainMini(){
        
        $ad = $this->getMain();
        
        if (array_key_exists('text', $ad)){
            unset($ad['text']);
        }
        
        return $ad;
    }
    
    public function setMain($title = '', $text = '', $video_id = 0){

        $rows = Mysql::getInstance()->count()->from('main_page_ad')->get()->counter();
        
        if ($rows > 0){

            Mysql::getInstance()->update('main_page_ad',
                array(
                    'title'    => $title,
                    'text'     => $text,
                    'video_id' => intval($video_id)
                ),
                array()
            );

        }else{

            Mysql::getInstance()->insert('main_page_ad', array(
                'title'    => $title,
                'text'     => $text,
                'video_id' => intval($video_id)
            ));
        }
    }
    
    public function delMain(){
        Mysql::getInstance()->query('delete from main_page_ad');
    }
    
}
