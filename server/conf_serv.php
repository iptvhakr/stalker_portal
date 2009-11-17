<?php

define("LANG", 'RU'); //RU, EN

define("ENABLE_SUBSCRIPTION", 1);

define("MAX_PAGE_ITEMS", 10);

define("MAX_USER_REC_LENGTH", 86400);

define("DB_TYPE", 'mysql');

define("MYSQL_HOST", 'localhost');

define("MEMCACHE_HOST", 'localhost');

define("OS_UNIX", 1);

define("HOROSCOPE_RSS",  'http://www.hyrax.ru/cgi-bin/bn_xml.cgi');
define("GAPI_CUR_WEATHER",  'http://www.google.com/ig/api?hl=ru&weather=Odessa,,,46430000,30770000&oe=utf8');
define("GISMETEO_XML",  'http://informer.gismeteo.ru/xml/33837_1.xml');

define("IMG_URI",  '/stalker_portal/screenshots/');
define("FILES_IN_DIR", 100);

define("PORTAL_URI", '/stalker_portal/');

define("MASTER_CACHE_EXPIRE", 24); // hours

if (OS_UNIX){
    define("XMLTV_URI",  '/home/zx/listing.xml');
    define("PORTAL_PATH", '/var/www/stalker_portal/');
    define("IMG_PATH", '/var/www/stalker_portal/screenshots/');
    define("DB_NAME", 'stalker_db');
    define("MYSQL_USER", 'stalker');
    define("MYSQL_PASS", '1');
}else{
    define("XMLTV_URI",  'D:\\listing.xml');
    define("PORTAL_PATH", 'd:\\projects\\stalker_portal\\current\\');
    define("IMG_PATH", 'd:\\media\\screenshots\\');
    define("DB_NAME", 'stalker_db_mirror');
    define("MYSQL_USER", 'root');
    define("MYSQL_PASS", '1');
}

?>