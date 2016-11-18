<?php


require_once __DIR__ . '/../../admin/vendor/autoload.php';
include "./common.php";
require_once PROJECT_PATH . '/../storage/config.php';

use Stalker\Lib\Core\Mysql;

$run_start_time = time();

$five_minute_interval = $run_start_time - ($run_start_time % 300);

echo "Scheduled events time - ", strftime('%Y-%m-%d %H:%M:%S', $five_minute_interval), PHP_EOL;

$_SERVER['TARGET'] = 'ADM';
$schedule_events = Mysql::getInstance()->from("`schedule_events`")->where(array(
    'state'        => 1,
    'date_begin<=' => 'now()'
))->where(array(
    'date_end>last_run and date_end>' => 'now()',
    'date_end'                        => 0
), 'OR ')->get()->all();
$update_data = array();

$event = new AdminPanelEvents();
$cronTab = new CronExpression('* * * * *', new Cron\FieldFactory());
$cronTab->setCurrentTime('now');

foreach ($schedule_events as $row) {
    if (!empty($row['schedule'])) {
        $cronTab->setExpression($row['schedule']);
        $next_run_interval = $cronTab->getNextRunDate(NULL, NULL, TRUE)->getTimestamp() - $five_minute_interval;

        echo "next_run_interval - $next_run_interval", PHP_EOL;

        if (abs($next_run_interval) > 30) {
            continue;
        }

        $event->setTtl((int)$row['ttl']);
        if (!empty($row['post_function']) && !empty($row['param1'])) {
            $event->setPostFunctionParam($row['post_function'], $row['param1']);
        } else {
            $event->setPostFunctionParam('', '');
        }
        if ($add_params = json_decode($row['recipient'], TRUE)) {
            list($user_list_type, $param) = each($add_params);
            $row['user_list_type'] = $user_list_type;
            if (is_array($param)) {
                $row = array_merge($row, $param);
            }
            $event->setPostData($row);
            echo $get_list_func_name = 'get_userlist_' . str_replace('to_', '', $row['user_list_type']), PHP_EOL;
            echo $set_event_func_name = 'set_event_' . str_replace('to_', '', $row['event']), PHP_EOL, PHP_EOL;

            if ($event->$get_list_func_name()->cleanAndSetUsers()->$set_event_func_name()) {
                $update_data[] = $row['id'];
            }
        }

    }
}

if (!empty($update_data)) {
    $last_run = new DateTime();
    $last_run->setTimestamp($run_start_time);
    Mysql::getInstance()->update("`schedule_events`", array('state = IF(periodic=1, state, 0), last_run' => $last_run->format('Y-m-d H:i:s')), array(" `id` IN ('" . implode("', '", $update_data) . "') AND 1=" => 1));
}
?>