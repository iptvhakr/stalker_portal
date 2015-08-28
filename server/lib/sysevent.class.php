<?php
/**
 * System events from server to client
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class SysEvent extends Event
{
    /**
     * Send "message" event
     *
     * @param string $msg
     */
    public function sendMsg($msg, $header = ''){
        $this->setEvent('send_msg');
        $this->setNeedConfirm(1);
        $this->setMsg($msg);
        $this->setHeader($header);
        $this->send();
    }

    /**
     * Send "message with video" event
     *
     * @param string $msg
     * @param string $video_url
     */
    public function sendMsgWithVideo($msg, $video_url, $header = ''){
        $this->setEvent('send_msg_with_video');
        $this->setNeedConfirm(1);
        $this->setMsg($msg);
        $this->setParam1($video_url);
        $this->setHeader($header);
        $this->send();
    }
    
    /**
     * Send "message and reboot after OK" event
     *
     * @param string $msg
     */
    public function sendMsgAndReboot($msg, $header = ''){
        $this->setEvent('send_msg');
        $this->setNeedConfirm(1);
        $this->setMsg($msg);
        $this->setHeader($header);
        $this->setRebootAfterOk(1);
        $this->send();
    }
    
    /**
     * Send "update subscription" event
     */
    public function sendUpdateSubscription(){
        $this->sendUpdateChannels();
    }
    
    /**
     * Send "update channels" event
     */
    public function sendUpdateChannels(){
        $this->setEvent('update_subscription');
        $this->send();
    }

    /**
     * Send "update epg" event
     */
    public function sendUpdateEpg(){
        $this->setEvent('update_epg');
        $this->send();
    }

    /**
     * Send "update modules" event
     */
    public function sendUpdateModules(){
        $this->setEvent('update_modules');
        $this->send();
    }
    
    /**
     * Send "mount all storages" event
     */
    public function sendMountAllStorages(){
        $this->setEvent('mount_all_storages');
        $master = new VideoMaster();
        $this->setMsg(json_encode($master->getStoragesForStb()));
        $this->send();
    }
    
    /**
     * Send "play channel" event
     *
     * @param int $ch_num
     */
    public function sendPlayChannel($ch_num){
        $this->setEvent('play_channel');
        $this->setMsg($ch_num);
        $this->send();
    }

    /**
     * Send "play radio channel" event
     *
     * @param int $ch_num
     */
    public function sendPlayRadioChannel($ch_num){
        $this->setEvent('play_radio_channel');
        $this->setMsg($ch_num);
        $this->send();
    }
    
    /**
     * Send "cut off" event
     */
    public function sendCutOff(){
        $this->setEvent('cut_off');
        $this->send();
    }
    
    /**
     * Send "cut on" event
     */
    public function sendCutOn(){
        $this->setEvent('cut_on');
        $this->send();
    }
    
    /**
     * Send "reset paused" event
     */
    public function sendResetPaused(){
        $this->sendShowMenu();
    }

    /**
     * Send "show_menu" event
     */
    public function sendShowMenu(){
        $this->setEvent('show_menu');
        $this->send();
    }
    
    /**
     * Send "reboot" event
     */
    public function sendReboot(){
        $this->setEvent('reboot');
        $this->send();
    }

    public function sendReloadPortal(){
        $this->setEvent('reload_portal');
        $this->send();
    }
    
    /**
     * Send "additional services status" event 
     *
     * @param int $status must be 1 or 0
     */
    public function sendAdditionalServicesStatus($status = 1){
        $this->setEvent('additional_services_status');
        $this->setMsg($status);
        $this->send();
    }
    
    /**
     * Send "updated places" event
     *
     * @param string $place must be 'vclub' or 'anec'
     */
    public function sendUpdatedPlaces($place = 'vclub'){
        $this->setEvent('updated_places');
        $this->setMsg($place);
        $this->send();
    }

    /**
     * Send "update image" event
     */
    public function sendUpdateImage(){
        $this->setEvent('update_image');
        $this->send();
    }

    /**
     * Set post function parameters
     *
     * @param string $post_func post function name
     * @param string $param1 post function parameter, e.g. video URL
     */
    public function setPostFunctionParam($post_func, $param1){
        $this->setPostFunction($post_func);
        $this->setParam1($param1);
    }
}

?>