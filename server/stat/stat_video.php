<?php
include "../lib/func.php";

$db = new Database('stalker_tmp');
$max_time = 600;
$from_date = '2008-04-11 00:00:00';
$query = "select user_log.* from user_log where action='play_now()' and param like 'aut%' and mac<>'00:1A:79:00:23:C0' and dif_date>=$max_time and time>'$from_date'";
$rs = $db->executeQuery($query);

$channels = array();
$video = array();

while(@$rs->next()){
    $arr = $rs->getCurrentValuesAsHash();
    $param = $arr['param'];
    $sub_param = substr($param, 0, 3);
    if ($sub_param == 'aut'){
        preg_match("/auto \/media\/(\d+).[a-z]*$/", $param, $tmp_arr);
        $video_id = $tmp_arr[1];
        $sql = "select * from video where id=$video_id";
        $rs2 = $db->executeQuery($sql);
        $name = $rs2->getValueByName(0, 'name');
        if (!@$video[$name]){
            $video[$name] = 1;
        }else{
            $video[$name] ++;
        }
    }
    
}
arsort($video);
echo '<table border="1" align="center">';
foreach ($video as $key => $val){
    echo '<tr>';
    echo '<td>';
    echo $key."</td><td>".$val;
    echo '</td>';
    echo '</tr>';
}
echo '</table>';
?>