<?php

include "../common.php";

use Stalker\Lib\Core\Mysql;

$all_channels = Mysql::getInstance()->from('itv')->where(array('status' => 1))->get()->all('id');

$all_channels = System::base64_encode(serialize($all_channels));

$result = Mysql::getInstance()->query("insert into itv_subscription (uid, sub_ch, addtime) (select id, '".$all_channels."' as sub_ch, now() from users) on duplicate key update sub_ch=VALUES(sub_ch)")->result();

if ($result){
    $event = new SysEvent();
    $event->setUserListByMac('all');
    $event->sendUpdateSubscription();
}
