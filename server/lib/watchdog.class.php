<?php
/**
 * Watchdog class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Watchdog extends AjaxResponse implements \Stalker\Lib\StbApi\Watchdog
{
    public function __construct(){
        parent::__construct();
    }
    
    public function getEvents(){

        $just_started = isset($_REQUEST['init']) ? (int) $_REQUEST['init'] : 0;

        if (isset($_REQUEST['init']) && Config::getSafe('log_mac_clones', false) && $just_started == 0 && Stb::getInstance()->getParam('just_started') == 0){

            $clone_ip = Middleware::getClonesIPAddress($this->stb->mac);

            if ($clone_ip){
                Stb::logDoubleMAC($clone_ip);
            }
        }
        
        $this->db->update('users',
                         array(
                            'keep_alive' => 'NOW()',
                            'ip' => $this->stb->ip,
                            'now_playing_type' => intval($_REQUEST['cur_play_type']),
                            'just_started' => $just_started,
                            'last_watchdog' => 'NOW()'
                         ),
                         array(
                            'mac' => $this->stb->mac
                         ));
        
        $events = Event::getAllNotEndedEvents($this->stb->id);

        $messages = count($events);
                
        $res['data'] = array();
        $res['data']['msgs'] = $messages;
        
        if ($messages>0){
            if ($events[0]['sended'] == 0){
                
                Event::setSended($events[0]['id']);
                
                if($events[0]['need_confirm'] == 0){
                    Event::setEnded($events[0]['id']);
                }
            }
            
            if ($events[0]['id'] != @$_GET['data']['event_active_id']){
                $res['data']['id'] = $events[0]['id'];
                $res['data']['event'] = $events[0]['event'];
                $res['data']['need_confirm'] = $events[0]['need_confirm'];
                $res['data']['msg'] = $events[0]['msg'];
                $res['data']['reboot_after_ok']   = $events[0]['reboot_after_ok'];
                $res['data']['auto_hide_timeout'] = $events[0]['auto_hide_timeout'];

                if (Config::getSafe('display_send_time_in_message', false)){
                    $res['data']['send_time'] = $events[0]['addtime'];
                }
            }
        }
        
        $res['data']['additional_services_on'] = Config::getSafe('enable_tariff_plans', false) ? '1' : $this->stb->additional_services_on;
        
        $updated_places = $this->db->from('updated_places')->where(array('uid' => $this->stb->id))->get()->first();
        
        $res['data']['updated'] = array();
        $res['data']['updated']['anec'] = intval($updated_places['anec']);
        $res['data']['updated']['vclub'] = intval($updated_places['vclub']);

        return $res;
    }
    
    public function confirmEvent(){
        
        Event::setConfirmed(intval($_REQUEST['event_active_id']));
        
        $res['data'] = 'ok';
        return $res;
    }
}

?>