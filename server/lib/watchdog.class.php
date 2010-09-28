<?php
/**
 * Watchdog class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Watchdog extends AjaxResponse 
{
    public function __construct(){
        parent::__construct();
    }
    
    public function getEvents(){
        
        $this->db->update('users',
                         array(
                            'keep_alive' => 'NOW()',
                            'ip' => $this->stb->ip,
                            'now_playing_type' => intval($_REQUEST['data']['cur_play_type']),
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
                $res['data']['reboot_after_ok'] = $events[0]['reboot_after_ok'];
            }
        }
        
        /**
         * @todo вынести в events
         */
        $res['data']['additional_services_on'] = $this->stb->additional_services_on;
        
        $weather = new Weatherco();
        $res['data']['cur_weather'] = $weather->getCurrent();
        
        $course = new Course();
        $res['data']['course'] = $course->getData();
        
        //$cur_weather = new Curweather();
        //$res['data']['cur_weather'] = $cur_weather->getData();
        
        $updated_places = $this->db->from('updated_places')->where(array('uid' => $this->stb->id))->get()->first();
        
        $res['data']['updated'] = array();
        $res['data']['updated']['anec'] = intval($updated_places['anec']);
        $res['data']['updated']['vclub'] = intval($updated_places['vclub']);
        
        //$ad = new Advertising();
        //$res['data']['main_ad'] = $ad->getMainMini();
        
        return $res;
    }
    
    public function confirmEvent(){
        
        Event::setConfirmed(intval($_REQUEST['event_active_id']));
        
        $res['data'] = 'ok';
        return $res;
    }
}

?>