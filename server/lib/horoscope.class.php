<?php
/**
 * Horoscope widget
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Horoscope extends Widget 
{
    public $widget_name = 'horoscope';
    public $cache_expire = 3600;
    public $rss_url = HOROSCOPE_RSS;
    public $rss_fields = array('title', 'description');
    
}
?>