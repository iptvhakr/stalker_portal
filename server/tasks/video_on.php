<?php

include "../conf_serv.php";
include "../common.php";

$db = new Database(DB_NAME); 

$data = array(
    '2009-08-03' => array(2215, 2298, 2289),
    '2009-08-04' => array(2343, 2354, 2249),
    '2009-08-05' => array(2342, 2344, 2355),
    '2009-08-06' => array(2236, 1788, 2222),
    '2009-08-07' => array(2335, 2253, 2316),
    '2009-08-10' => array(2347, 2273, 2268),
    '2009-08-11' => array(2351, 2348, 2360),
    '2009-08-12' => array(2267, 2281, 2361),
    '2009-08-13' => array(2308, 2303, 2184),
    '2009-08-14' => array(2292, 2266, 2285)
);

$today = date("Y-m-d");

if (array_key_exists($today, $data)){
    
    foreach ($data[$today] as $video_id){
        $query = "update video set accessed=1,added=NOW()  where id='$video_id'";
        $db->executeQuery($query);
        
        $sql = "update updated_places set vclub=1";
        $db->executeQuery($sql);
    }
}

?>