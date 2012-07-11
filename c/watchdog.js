/**
 * Watchdog.
 * @constructor
 */

function watchdog(){
    
    this.request_timeout = 30000;
    this.event_active_id = 0;
    this.reboot_after_ok = false;
}

watchdog.prototype.run = function(timeout, timeslot){
    _debug('watchdog.run', timeout, timeslot);

    this.request_timeout = timeout*1000 || this.request_timeout;
    timeslot = timeslot*1000;

    _debug('this.request_timeout', this.request_timeout);

    var day_start = new Date();
    day_start.setHours(0);
    day_start.setMinutes(0);
    day_start.setSeconds(0);
    day_start.setMilliseconds(0);
    day_start = day_start.getTime();

    var now = new Date().getTime();

    _debug('now', now);
    _debug('day_start', day_start);

    _debug('Math.floor((now - day_start)/this.request_timeout)', Math.floor((now - day_start)/this.request_timeout));

    var delay = this.request_timeout - ((now - day_start) - Math.floor((now - day_start)/this.request_timeout) * this.request_timeout);

    _debug('delay', delay);

    //delay += timeslot;

    if ((delay + timeslot) > this.request_timeout){
        delay = delay - (this.request_timeout - timeslot);
    }else{
        delay += timeslot;
    }

    _debug('delay', delay);
    _debug('date', new Date(now + delay) + " " + new Date(now + delay).getMilliseconds() + "ms");

    var self = this;

    this.send_request();

    window.setTimeout(function(){
        window.setInterval(function(){
            self.send_request();
        }, self.request_timeout);
        self.send_request();
    }, delay);
};

watchdog.prototype.send_request = function(){
    
    var self = this;
    
    var cur_play_type = 0;
    
    if (!stb.player.pause.on){
        cur_play_type = stb.get_current_place();
    }
    
    _debug('cur_play_type', cur_play_type);

    _debug('now', new Date() + " " + new Date().getMilliseconds() + "ms");

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
};

watchdog.prototype.parse_result = function(data){
    
    _debug('watchdog.parse_result', data);

    try{
    
        //module.curweather && module.curweather.set && module.curweather.set(data.cur_weather);

        //module.course && module.course.set && data.course && module.course.set(data.course);

        stb.check_additional_services(data.additional_services_on);
    }catch(e){
        _debug(e);
    }
    
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

                if (!stb.msg){
                    return;
                }

                if (this.event_active_id == data.id){
                    return;
                }
                
                this.event_active_id = data.id;
                
                var self = this;
                
                stb.msg.set_callback(
                    function(){
                        self.send_confirm(function(){
                            if (data.reboot_after_ok == 1){
                                stb.ExecAction('reboot');
                            }
                        });
                    });
                
                stb.msg.push(
                    {
                        msg               : data.msg,
                        auto_hide_timeout : data.auto_hide_timeout || 0
                    }
                );
                
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
            case 'update_epg':
            {
                stb.epg_loader.load();
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
            case 'update_image':
            {
                keydown_observer.emulate_key(key.MENU);
                stbUpdate.startAutoUpdate(stb.user['update_url'], false);
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
                
                //keydown_observer.emulate_key(key.EXIT);
                main_menu.hide();
                stb.player.play_last();
                break;
            }
            case 'update_modules':
            {
                stb.update_modules();
                break;
            }
        }
    }
};

watchdog.prototype.send_confirm = function(callback){
    
    stb.load(
    {
        "type"   : "watchdog",
        "action" : "confirm_event",
        "event_active_id" : this.event_active_id
    },
    
    function(result){
        _debug(result);
        callback && callback();
    });
    
    this.event_active_id = 0;
};