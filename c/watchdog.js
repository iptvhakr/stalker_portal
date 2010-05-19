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
        //cur_play_type = stb.get_current_place();
    }
    
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
    
    if (typeof(data.id) != 'undefined'){
        
        _debug('data.id', data.id);
        
        switch(data.event){
            case 'reboot':
            {
                stb.ExecAction('reboot');
                break
            }
            case 'send_msg':
            {
                this.event_active_id = data.id
                
                //show_full_incoming_message(event_msg);
                this.reboot_after_ok = data.reboot_after_ok;
                break
            }
            case 'update_channels':
            {
                stb.load_channels();
                stb.load_fav_channels();
                stb.load_fav_itv();
                break
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
                break
            }
            case 'cut_off':
            {
                stb.cut_off();
                break
            }
            case 'cut_on':
            {
                stb.cut_on();
                break
            }
            case 'mount_all_storages':
            {
                stb.mount_home_dir(data.msg);
                break
            }
            case 'show_menu': // reset paused
            {
                
                break
            }
            case 'play_channel':
            {
                
                break
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
        
    });
    
    this.event_active_id = 0;
}