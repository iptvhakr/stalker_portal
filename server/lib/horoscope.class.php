<?php

use Stalker\Lib\Core\Config;

/**
 * Horoscope widget
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Horoscope extends Widget implements \Stalker\Lib\StbApi\Horoscope
{
    public $widget_name = 'horoscope';
    public $cache_expire = 3600;
    public $rss_url;
    public $rss_fields = array('title', 'description');

    public function __construct(){
        parent::__construct();
        $this->rss_url = Config::get('horoscope_rss');
    }
}
?>