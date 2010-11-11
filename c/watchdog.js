/**
 * Watchdog.
 * @constructor
 */

function watchdog(){
    
    this.request_timeout = 30000;
    this.event_active_id = 0;
    this.reboot_after_ok = false;
}

watchdog.prototype.run = function(){
    
    var self = this;
    
    window.setInterval(function(){
        self.send_request();
    }, this.request_timeout);
}

watchdog.prototype.send_request = function(){
    
    var self = this;
    
    var cur_play_type = 0
    
    if (!stb.player.pause.on){
        cur_play_type = stb.get_current_place();
    }
    
    _debug('cur_play_type', cur_play_type);
    
    stb.load(
        {
            "type"            : "watchdog",
            "action"          : "get_events",
            "cur_play_type"   : cur_play_type,
            "event_active_id" : this.event_active_id
        },
        
        function(result){
            try{
                
                self.parse_result(result.data);
                
            }catch(e){
                _debug(e);
            }
        }
    );
}

watchdog.prototype.parse_result = function(data){
    
    _debug('watchdog.parse_result', data);
    
    module.curweather && module.curweather.set && module.curweather.set(data.cur_weather);
    
    module.course && module.course.set && module.course.set(data.course);
    
    stb.check_additional_services(data.additional_services_on);
    
    if (typeof(data.id) != 'undefined'){
        
        _debug('data.id', data.id);

        switch(data.event){
            case 'reboot':
            {
                stb.ExecAction('reboot');
                break;
            }
            case 'send_msg':
            {
                this.event_active_id = data.id;
                
                var self = this;
                
                stb.msg.set_callback(
                    function(){
                        self.send_confirm();
                    });
                
                stb.msg.show(data.msg);
                
                this.reboot_after_ok = data.reboot_after_ok;
                
                break;
            }
            case 'update_channels':
            {
                stb.load_channels();
                stb.load_fav_channels();
                stb.load_fav_itv();
                break;
            }
            case 'update_subscription':
            {
                if (stb.cur_off_on){
                    stb.ExecAction('reboot');
                }else{
                    stb.load_channels();
                    stb.load_fav_channels();
                    stb.load_fav_itv();
                }
                break;
            }
            case 'cut_off':
            {
                stb.cut_off();
                break;
            }
            case 'cut_on':
            {
                stb.cut_on();
                break;
            }
            case 'mount_all_storages':
            {
                //stb.mount_home_dir(data.msg);
                stb.set_storages(data.msg);
                break;
            }
            case 'show_menu': // reset paused
            {
                keydown_observer.emulate_key(key.MENU);
                break;
            }
            case 'play_channel':
            {
                keydown_observer.emulate_key(key.MENU);
                
                stb.user.fav_itv_on = 0;
                
                var ch_idx = stb.player.channels.getIdxByNumber(parseInt(data.msg));
                
                stb.player.ch_idx = ch_idx || 0;
                stb.player.cur_media_item = stb.player.channels[stb.player.ch_idx];
                stb.player.cur_tv_item = stb.player.channels[stb.player.ch_idx];
                
                keydown_observer.emulate_key(key.EXIT);
                break;
            }
        }
    }
}

watchdog.prototype.send_confirm = function(){
    
    stb.load(
    {
        "type"   : "watchdog",
        "action" : "confirm_event",
        "event_active_id" : this.event_active_id
    },
    
    function(result){
        _debug(result);
    });
    
    this.event_active_id = 0;
}