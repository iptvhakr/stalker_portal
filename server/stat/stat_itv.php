<?php
include "../conf_serv.php";
include "../lib/func.php";

$db = new Database('stalker_tmp');
$max_time = 600;
$from_date = '2008-04-11 00:00:00';
$query = "select user_log.*, itv.name from user_log left join itv on user_log.param=itv.cmd where action='play_now()' and param like 'rtp%' and mac<>'00:1A:79:00:23:C0' and dif_date>=$max_time and time>'$from_date' group by user_log.id";
$rs = $db->executeQuery($query);

$channels = array();
$video = array();

while(@$rs->next()){
    $arr = $rs->getCurrentValuesAsHash();
    $param = $arr['param'];
    $sub_param = substr($param, 0, 3);
    if ($sub_param == 'rtp'){
        $name = $arr['name'];
        if (!@$channels[$name]){
            $channels[$name] = 1;
        }else{
            $channels[$name] ++;
        }
    }
    
}
arsort($channels);
echo '<table border="1" align="center">';
foreach ($channels as $key => $val){
    echo '<tr>';
    echo '<td>';
    echo $key."</td><td>".$val;
    echo '</td>';
    echo '</tr>';
}
echo '</table>';
?>