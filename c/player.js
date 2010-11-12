/**
 * Player constructor
 * @constructor
 */
function player(){
    
    var self = this;
    
    this.on = false;
    
    this.f_ch_idx = 0;
    this.ch_idx   = 0;
    this.channels;
    this.fav_channels;
    this.fav_channels_ids;
    
    this.start_time;
    this.cur_media_item;
    this.cur_tv_item;
    this.need_show_info = 0;
    
    this.pause = {"on" : false};
    
    this.is_tv = false;
    
    this.cur_media_length = 0;
    this.cur_pos_time = 0;
    this.new_pos_time = 0;
    this.pos_step = 0;
    this.prev_move_pos_dir = 0;
    this.next_step = 0;
    this.diff_pos = 0;
    
    this.last_state = 0;
    
    this.send_last_tv_id_to = 1800000;
    
    this.prev_layer = {};
    
    this.info = {"on" : false, "hide_timer" : 4000};
    
    this.quick_ch_switch = {"on" : false, "hide_to" : 3000};
    
    this.on_create_link = function(){};
    this.last_storage_id = 0;
    
    this.event5_counter = 0;
    
    this.play_auto_ended = false;
    
    this.hist_ch_idx = [0,0];
    this.hist_f_ch_idx = [0,0];
    
    this.init();
    this.init_pause();
    this.init_show_info();
    this.init_quick_ch_switch();
    this.volume.init();
    
    this.send_last_tv_id_callback = function(){};
    
    this.con_menu = new context_menu();
    this.con_menu.bind();
    this.con_menu.set_x_offset(100);
    this.con_menu.set_y_offset(100);
}

player.prototype.init = function(){
    _debug('player.init');
    try{
        stb.InitPlayer();
        stb.SetTopWin(0);
        stb.SetAspect(0x10);
        stb.SetPIG(1, -1, -1, -1);
        stb.SetUserFlickerControl(1);
        stb.SetDefaultFlicker(1);
        stb.SetLoop(0);
        stb.SetMicVolume(100);
        stb.SetupRTSP(parseInt(stb.user.rtsp_type), parseInt(stb.user.rtsp_flags));
        
        stbEvent.onEvent = (function(self){
            return function(){
                self.event_callback.apply(self, arguments);
            }
        })(this);
    }catch(e){
        _debug(e);
    }
}

player.prototype.event_callback = function(event){
    _debug('event: ', event);
    
    var event = parseInt(event);
    
    this.last_state = event;
    
    switch(event){
        case 1: // End of stream
        {
            try{
                //this.prev_layer && this.prev_layer.show && this.prev_layer.show.call(this.prev_layer, true);
                
                this.play_auto_ended = true;
                
                if (this.media_type == 'stream' && this.is_tv){
                    _debug('stream error');
                    
                    var self = this;
                    
                    this.replay_channel_timer = window.setTimeout(
                        function(){
                            self.play_last();
                        },
                        1000
                    );
                }else{
                    if(this.prev_layer && this.prev_layer.show){
                        this.prev_layer.show.call(this.prev_layer, true);
                    }
                    
                    this.stop();
                }
                
            }catch(e){
                _debug(e);
            }
            
            break;
        }
        case 4: // Playback started
        {
            
            this.cur_media_length = stb.GetMediaLen();
            _debug('player.cur_media_length', this.cur_media_length);
            this.cur_pos_time = stb.GetPosTime();
            _debug('player.cur_pos_time', this.cur_pos_time);
            
            if (this.info.on){
                this.set_pos_button_to_cur_time();
            }
            
            /*if (this.is_tv){
                this.send_last_tv_id(this.cur_tv_item.id);
            }*/
            
            window.clearTimeout(this.send_played_video_timer);
            
            _debug('stb.cur_place', stb.cur_place);
            
            if (stb.cur_place == 'vclub'){
                
                var time_send_played = (this.cur_media_length*0.7)*1000
                _debug('time_send_played,', time_send_played);
                
                var self = this;
                
                this.send_played_video_timer = window.setTimeout(
                    function(){
                        self.send_played_video(self.cur_media_item.id);
                    },
                    
                    time_send_played
                )
            }
            try{
                this.audio_pid.get_all();
                
                _debug('test');
                _debug('typeof this.subtitle_pid', typeof(this.subtitle_pid));
                _debug('typeof this.subtitle_pid.get_all', typeof(this.subtitle_pid.get_all));
                
                this.subtitle_pid.get_all();
                
                this.build_con_menu();
            }catch(e){
                _debug(e);
            }
            
            break;
        }
        case 5: // Not found
        {
            
            var self = this;
            
            if (this.media_type == 'stream'){
                
                if(self.prev_layer && self.prev_layer.show){
                    self.prev_layer.show.call(self.prev_layer, true);
                }
                
                self.stop();
                
                stb.notice.show(get_word('player_file_missing'));
                
            }else{
            
                this.event5_counter++;
                
                var self = this;
                
                stb.remount_storages(
                
                    function(){
                        if (self.event5_counter == 1){
                            self.play(self.cur_media_item);
                        }else{
                            if(self.prev_layer && self.prev_layer.show){
                                self.prev_layer.show.call(self.prev_layer, true);
                            }
                            
                            self.stop();
                            
                            stb.notice.show(get_word('player_server_unavailable'));
                        }  
                    }
                )
            }
        }
    }
}

player.prototype.volume = new function(){
    this.on = false;
    this.level = 100;
    this.step  = 5;
    this.dom_obj = {};
    this.mute = {"on" : false};
    this.hide_to = 3000;
    
    this.init = function(){
        _debug('volume.init');
        
        this.dom_obj = create_block_element('volume');
        
        this.container = create_block_element('volume_bar', this.dom_obj);

        this.bar = create_block_element('volume_progress', this.container);
        
        this.mute.dom_obj = create_block_element('volume_off', this.dom_obj);
        this.mute.dom_obj.hide();
        
        this.dom_obj.hide();
    }
    
    this.set_level = function(v){
        _debug('volume.set_level', v);

        this.level = v;
        
        try{
            stb.SetVolume(this.level);
        }catch(e){
            _debug(e);
        }
        
        if (this.level == 0){
            if (!this.mute.on){
                this.show_mute();
            }
        }else{
            if (this.mute.on){
                this.hide_mute();
            }
        }
        
        this.update_bar();
    }
    
    this.show = function(){
        _debug('volume.show');
        
        this.dom_obj.show();
        this.on = true;
        
        this.t_hide();
    }
    
    this.t_hide = function(){
        _debug('volume.t_hide');
        
        window.clearTimeout(this.hide_timer);
        
        var self = this;
        
        this.hide_timer = window.setTimeout(function(){
            
            self.hide();
            
        }, this.hide_to);
    }
    
    this.hide = function(){
        _debug('volume.hide');
        
        this.dom_obj.hide();
        this.on = false;
        this.save();
    }
    
    this.control = function(dir){
        _debug('volume.control', dir);
        
        if (!this.on){
            this.show();
        }else{
            this.t_hide();
        }
        
        try{
            if (dir>0){
                if (this.level < 100){
                    this.level += this.step;
                }
            }else{
                if (this.level > 0){
                    this.level -= this.step;
                }
            }
            
            if (this.level > 100){
                this.level = 100;
            }else if (this.level < 0){
                this.level = 0;
            }
            
            this.set_level(this.level);
        }catch(e){
            _debug(e);
        }
    }
    
    this.show_mute = function(){
        _debug('volume.show_mute');
        
        try{
            stb.SetVolume(0);
        }catch(e){
            _debug(e);
        }
        
        this.mute.dom_obj.show();
        
        if (!this.on){
            this.show();
        }
        
        window.clearTimeout(this.hide_timer);
        
        this.container.hide();
        this.mute.on = true;
    }
    
    this.hide_mute = function(){
        _debug('volume.hide_mute');
        
        try{
            stb.SetVolume(this.level);
        }catch(e){
            _debug(e);
        }
        
        this.mute.dom_obj.hide();
        this.container.show();
        
        this.update_bar();
        this.mute.on = false;
        
        if (!this.on){
            this.show();
        }else{
            this.t_hide();
        }
    }
    
    this.mute_switch = function(){
        _debug('volume.mute_switch');
        
        if (this.mute.on){
            this.hide_mute();
        }else{
            this.show_mute();
        }
    }
    
    this.update_bar = function(){
        _debug('volume.update_bar');
        
        //var width = 14*this.level/this.step;
        var width = 10*((this.level/this.step) - 1);
        
        if (width > 0){
            width += 5;
        }else{
            width = 10;
        }
        
        _debug('bar width', width);
        
        this.bar.style.width = width + 'px';
    }
    
    this.save = function(){
        _debug('volume.save');
        
        stb.load(

            {
                'type'   : 'stb',
                'action' : 'set_volume',
                'vol'    : this.level
            },
            
            function(result){
                
            }

        )
    }
}

player.prototype.seek_bar = new function(){
    
    this.seek_bar_dom_obj = $('seek_bar');
    
    this.show = function(){
        this.seek_bar_dom_obj.show();
    }
    
    this.hide = function(){
        this.seek_bar_dom_obj.hide();
    }
    
    this.set_pos = function(){
        
    }
}

player.prototype.define_media_type = function(cmd){
    _debug('player.define_media_type', cmd);
    
    if (cmd.indexOf('://') > 0){
        
        _debug('stb.cur_place', stb.cur_place);
        
        if ((cmd.indexOf('udp://') >=0 || cmd.indexOf('rtp://') >=0 || cmd.indexOf('http://') >=0) && stb.cur_place != 'radio' && stb.cur_place != 'vclub' && stb.cur_place != 'karaoke'){
            this.is_tv = true;
        }else{
            this.is_tv = false;
        }
        
        return 'stream';
    }else{
        this.is_tv = false;
        return 'file';
    }
}

player.prototype.play_last = function(){
    _debug('player.play_last');
    
    this.prev_layer = module.tv;
    this.show_info_after_play();
    this.play(this.cur_tv_item);
}

player.prototype.first_play = function(){
    
    if (this.channels_inited){
        return;
    }
    
    if (typeof(this.channels) != 'undefined' &&
        typeof(this.fav_channels) != 'undefined' &&
        typeof(this.fav_channels_ids) != 'undefined'){
        
            
        if (stb.user.fav_itv_on){
            
            this.f_ch_idx = this.fav_channels.getIdxById(stb.user.last_itv_id);
            
            if (this.f_ch_idx === null){
                this.f_ch_idx = 0;
            }
            
            var channel = this.fav_channels[this.f_ch_idx];
            
        }else{
            
            this.ch_idx = this.channels.getIdxById(stb.user.last_itv_id);
            
            if (this.ch_idx === null){
                this.ch_idx = 0;
            }
            
            var channel = this.channels[this.ch_idx];
        }
        
        this.need_show_info = 1;
        this.play(channel);
        
        stb.key_lock = false;
        this.channels_inited = true;
    }
}

player.prototype.play = function(item){
    _debug('player.play');
    
    var cmd;
    
    this.on = true;
    
    this.cur_media_item = item;
    
    if (typeof(item) == 'object'){
        if (!item.hasOwnProperty('cmd')){
            return;
        }
        
        cmd = item.cmd;
    }else{
        cmd = item;
    }
    
    if (item.hasOwnProperty('position') && parseInt(item.position) > 0){
        cmd += ' position:'+item.position;
    }
    
    _debug('cmd: ', cmd);
    
    this.media_type = this.define_media_type(cmd);
    
    if (this.is_tv){
        this.cur_tv_item = item;
    }
    
    _debug('player.media_type: ', this.media_type);
    
    if (this.media_type == 'stream'){
        
        if (item.hasOwnProperty('open') && !item.open){
            _debug('channel is closed');
            stb.Stop();
            this.show_info(this.cur_media_item);
        }else{
            this.play_now(cmd);
            
            if (this.is_tv){
                
                this.send_last_tv_id(this.cur_tv_item.id);
                
                if (stb.user.fav_itv_on){
                    
                    this.f_ch_idx = this.fav_channels.getIdxByVal('number', item.number);
                    
                    this.hist_f_ch_idx.push(item);
                    this.hist_f_ch_idx.shift();
                    _debug('this.hist_f_ch_idx', this.hist_f_ch_idx);
                }else{
                    
                    this.ch_idx = this.channels.getIdxByVal('number', item.number);
                    
                    this.hist_ch_idx.push(item);
                    this.hist_ch_idx.shift();
                    _debug('this.hist_ch_idx', this.hist_ch_idx);                
                }
            }
        }
        
    }else if (cmd.indexOf('usbdisk') > 0){
        
        this.play_now(cmd);
        
    }else if (stb.cur_place == 'karaoke'){
        
        this.create_link('karaoke', cmd, 0);
    }else{
        
        var series_number = item.cur_series || 0;
        
        this.create_link('vod', cmd, series_number);
    }
}

player.prototype.create_link = function(type, uri, series_number){
    
    var series_number = series_number || "";
    
    _debug('player.create_link', type, uri, series_number);
    
    stb.load(

        {
            "type"   : type,
            "action" : "create_link",
            "cmd"    : uri,
            "series" : series_number
        },
        
        function(result){
            
            _debug('create_link callback: ', result);
            
            this.last_storage_id = result.storage_id;
            
            _debug('this.last_storage_id', this.last_storage_id);
            
            stb.Mount(result.cmd);
            
            this.on_create_link && this.on_create_link(result);
        },
        
        this
    )
}

player.prototype.play_now = function(uri){
    _debug('player.play_now', uri);
    
    _log('play', uri);
    
    this.start_time = Date.parse(new Date())/1000;
    
    if (this.need_show_info){
        this.show_info(this.cur_media_item);
    }
    
    this.init_con_menu();
    
    try{
        stb.Play(uri);
    }catch(e){_debug(e)}
}

player.prototype.stop = function(){
    _debug('player.stop');
    
    this.prev_layer = {};
    
    this.event5_counter = 0;
    
    this.need_show_info = 0;
    
    this.on_create_link = function(){};
    
    this.on = false;
    
    if(this.pause.on){
        this.disable_pause();
    }
    
    if(this.quick_ch_switch.on){
        this.cancel_quick_ch_switch();
    }
    
    try{
        stb.Stop();
    }catch(e){}
    
    if (this.media_type == 'file'){
        //stb.Umount();
        this.umount_timer = window.setTimeout(function(){stb.Umount()}, 500);
    }
    
    if (stb.cur_place == 'vclub' && !this.play_auto_ended){
        
        var cur_series = this.cur_media_item.cur_series || 0;
        var end_time = stb.GetPosTime();
        
        module && module.vclub && module.vclub.set_not_ended && module.vclub.set_not_ended(this.cur_media_item.id, cur_series, end_time);
    }
    
    this.play_auto_ended = false;
    
    window.clearTimeout(this.send_played_itv_timer);
    window.clearTimeout(this.send_played_video_timer);
    window.clearTimeout(this.replay_channel_timer);
    
    this.hide_info();
    _log('stop');
}

player.prototype.init_pause = function(){
    this.pause.dom_obj = create_block_element('pause');
    this.pause.dom_obj.hide();
}

player.prototype.pause_switch = function(){
    _debug('player.pause_switch');
    
    if (this.is_tv){
        return;
    }
    
    if (this.pause.on){
        this.disable_pause();
    }else{
        try{
            stb.Pause();
        }catch(e){};
        this.pause.on = true;
        this.pause.dom_obj.show();
    }
}

player.prototype.disable_pause = function(){
    try{
        stb.Continue();
    }catch(e){};
    this.pause.on = false;
    this.pause.dom_obj.hide();
}

player.prototype.show_info_after_play = function(){
    this.need_show_info = 1;
}

player.prototype.init_show_info = function(){
    
    this.info.dom_obj = create_block_element("osd_info");
    
    this.info.clock = create_block_element("osd_info_clock", this.info['dom_obj']);
    
    this.info.title = create_block_element("osd_info_title", this.info['dom_obj']);
    
    this.info.epg   = create_block_element("osd_info_epg", this.info['dom_obj']);
    
    this.info.video_container = create_block_element("", this.info['dom_obj']);
    
    var pos_bar = create_block_element("pos_bar", this.info.video_container);
    
    this.info.pos_button = create_block_element("pos_button", pos_bar);
    
    this.info.pos_time = create_block_element("pos_time", this.info.video_container);
    
    this.info.pos_series = create_block_element("pos_series", this.info.video_container);
    
    this.info.epg.hide();
    this.info.video_container.hide();
    
    this.info.dom_obj.hide();
}

player.prototype.show_info = function(item){
    _debug('show_info');
    
    var item = item || this.cur_media_item;
    
    try{
        
        if (stb.cur_place == 'radio'){
            if (!this.info.epg.isHidden()){
                this.info.epg.hide();
            }
            
            if (!this.info.video_container.isHidden()){
                this.info.video_container.hide();
            }
            
        }else if(this.is_tv){
            if (this.info.epg.isHidden()){
                this.info.epg.show();
            }
            
            if (!this.info.video_container.isHidden()){
                this.info.video_container.hide();
            }
            
            //_debug('stb.epg_loader.get_epg(item.id)', stb.epg_loader.get_epg(item.id));
            
            if (item.hasOwnProperty('open') && !item.open){
                this.info.epg.innerHTML = get_word('msg_channel_not_available');
            }else{
                this.info.epg.innerHTML = stb.epg_loader.get_epg(item.id);
            }
        }else{
            
            _debug('this.info.epg.isHidden()', this.info.epg.isHidden());
            
            if (!this.info.epg.isHidden()){
                this.info.epg.hide();
            }
            
            _debug('this.info.video_container.isHidden()', this.info.video_container.isHidden());
            
            if (this.last_state == 4){
                this.set_pos_button_to_cur_time();
            }
            
            if (this.info.video_container.isHidden()){
                this.info.video_container.show();
            }
        }
        
        if(this.info.on){
            window.clearTimeout(this.info.hide_timeout);
        }else{
            this.info.dom_obj.show();
            this.info.on = true;
        }
        
        stb.clock.show();
        
        var title = '';
        
        if (item.hasOwnProperty('number')){
            title = item.number + '. ';
        }
        
        title += item.name;
        
        this.info.title.innerHTML = title;
        
        //if (!this.info.epg.isHidden()){
        //    this.info.epg.innerHTML = stb.epg_loader.get_epg(item.id);
        //}
        
        if (item.cur_series && parseInt(item.cur_series) > 0){
            this.info.pos_series.innerHTML = item.cur_series + ' ' + get_word('player_series');
        }else{
            this.info.pos_series.innerHTML = '';
        }
        
        var self = this;
        
        this.info.hide_timeout = window.setTimeout(function(){
            self.info.dom_obj.hide();
            self.info.on = false;
        },
        this.info.hide_timer);
    }catch(e){
        _debug(e);
    }
}

player.prototype.hide_info = function(){
    window.clearTimeout(this.info.hide_timeout);
    this.info.dom_obj.hide();
    this.info.on = false;
}

player.prototype.switch_channel = function(dir, show_info){
    
    _debug('switch_channel', dir);
    
    if (!this.is_tv){
        return;
    }
    
    if (show_info){
        this.need_show_info = 1;
    }else{
        this.need_show_info = 0;
    }
    
    if (dir > 0){
        
        if (stb.user.fav_itv_on){
            
            if (this.f_ch_idx < this.fav_channels.length-1){
                this.f_ch_idx++;
            }else{
                this.f_ch_idx = 0;
            }
            
            _debug('this.f_ch_idx:', this.f_ch_idx);
            
            /*if (show_info){
                this.show_info(this.fav_channels[this.f_ch_idx]);
            }*/
            
            this.play(this.fav_channels[this.f_ch_idx]);
            
        }else{
            
            if (this.ch_idx < this.channels.length-1){
                this.ch_idx++;
            }else{
                this.ch_idx = 0;
            }
            
            _debug('this.ch_idx:', this.ch_idx);
            
            /*if (show_info){
                this.show_info(this.channels[this.ch_idx]);
            }*/
            
            this.play(this.channels[this.ch_idx]);
        }
        
    }else{
        if (stb.user.fav_itv_on){
            
            if (this.f_ch_idx > 0){
                this.f_ch_idx--;
            }else{
                this.f_ch_idx = this.fav_channels.length-1;
            }
            
            _debug('this.f_ch_idx:', this.f_ch_idx);
            
            /*if (show_info){
                this.show_info(this.fav_channels[this.f_ch_idx]);
            }*/
            this.play(this.fav_channels[this.f_ch_idx]);
            
        }else{
            
            if (this.ch_idx > 0){
                this.ch_idx--;
            }else{
                this.ch_idx = this.channels.length-1;
            }
            
            _debug('this.ch_idx:', this.ch_idx);
            
            /*if (show_info){
                this.show_info(this.channels[this.ch_idx]);
            }*/
            this.play(this.channels[this.ch_idx]);
        }
    }
}

player.prototype.send_last_tv_id = function(id){
    _debug('send_last_tv_id', id);
    
    var self = this;
    
    window.clearTimeout(this.send_played_itv_timer);
    
    this.send_played_itv_timer = window.setTimeout(

        function(){
            self.send_played_itv(id);
        },
        
        this.send_last_tv_id_to
    );
    
    stb.load(

        {
            "type"   : "itv",
            "action" : "set_last_id",
            "id"     : id
        },
        
        function(result){
            _debug('last_tv_id saved', result);
            
            _debug('typeof this.send_last_tv_id_callback', typeof(this.send_last_tv_id_callback));
            
            this.send_last_tv_id_callback();
            
            this.send_last_tv_id_callback = function(){};
        },
        
        this
    )
}

player.prototype.send_played_itv = function(id){
    
    stb.load(
        {
            "type"   : "itv",
            "action" : "set_played",
            "itv_id" : id,
        },
        
        function(result){
            
        }
    );
}

player.prototype.send_played_video = function(id){
    _debug('player.send_played_video', id);
    
    stb.load(
        {
            "type"       : "vod",
            "action"     : "set_played",
            "video_id"   : id,
            "storage_id" : this.last_storage_id
        },
        
        function(result){
            
        }
    );
    
}

player.prototype.show_prev_layer = function(){
    _debug('player.show_prev_layer');
    
    try{
        if(this.prev_layer && this.prev_layer.show){
            this.prev_layer.show.call(this.prev_layer, true);
        }else{
            if (this.is_tv){
                module.tv._show();
            }
        }
        
        if (this.prev_layer && this.prev_layer.cur_view == 'short'){
            return;
        }
        
        this.stop();
    }catch(e){
        _debug(e);
    }
}

player.prototype.bind = function(){
    
    var self = this;
    
    this.switch_channel.bind(key.UP, self, 1, true);
    this.switch_channel.bind(key.DOWN, self, -1, true);
    
    this.switch_channel.bind(key.CHANNEL_NEXT, self, 1, true);
    this.switch_channel.bind(key.CHANNEL_PREV, self, -1, true);
    
    (function(){
        
        if (this.info.on){
            this.set_pos_and_play();
        }else{
            this.pause_switch();
        }
        
    }).bind(key.PAUSE, this);
    
    
    (function(){
        if (this.info.on){
            this.set_pos_and_play(true);
        }else if(this.quick_ch_switch.on){
            this.cancel_quick_ch_switch();
        }else{
            this.show_prev_layer();
        }
    }).bind(key.EXIT, this);
    
    (function(){
        this.prev_layer && this.prev_layer.hide && this.prev_layer.hide();
        this.play_last();
    }).bind(key.TV);
    
    this.show_prev_layer.bind(key.STOP, self);
    
    this.show_info.bind(key.INFO, self);
    
    this.move_pos.bind(key.FFWD, this, 1);
    this.move_pos.bind(key.REW, this, -1);
    
    (function(){
        
        if (this.info.on){
            this.set_pos_and_play();
        }else  if (this.quick_ch_switch.on){
            this.hide_quick_ch_switch();
        }else  if (this.prev_layer && this.prev_layer.cur_view == 'short'){
            this.show_prev_layer();
        }else if (this.is_tv){
            module.tv.set_short_container();
            module.tv._show();
        }/*else{
            this.con_menu && this.con_menu.show && this.con_menu.show();
        }*/
        
    }).bind(key.OK, this);
    
    (function(){
        
        if (!module.tv.on){
            this.con_menu && this.con_menu.show && this.con_menu.show();
        }
        
    }).bind(key.NULL, this);
    
    
    (function(){
        if (this.on){
            this.volume.control(1);
        }
    }).bind(key.VOL_UP, this).bind(key.RIGHT, this, 1);;
    
    (function(){
        if (this.on){
            this.volume.control(-1);
        }
    }).bind(key.VOL_DOWN, this).bind(key.LEFT, this, -1);
    
    
    (function(){
        if (this.on){
            this.volume.mute_switch();
        }
    }).bind(key.MUTE, this);
    
    this.show_quick_ch_switch.bind(key.NUM1, this, 1);
    this.show_quick_ch_switch.bind(key.NUM2, this, 2);
    this.show_quick_ch_switch.bind(key.NUM3, this, 3);
    this.show_quick_ch_switch.bind(key.NUM4, this, 4);
    this.show_quick_ch_switch.bind(key.NUM5, this, 5);
    this.show_quick_ch_switch.bind(key.NUM6, this, 6);
    this.show_quick_ch_switch.bind(key.NUM7, this, 7);
    this.show_quick_ch_switch.bind(key.NUM8, this, 8);
    this.show_quick_ch_switch.bind(key.NUM9, this, 9);
    this.show_quick_ch_switch.bind(key.NUM0, this, 0);
    
    (function(){
        if (this.quick_ch_switch.on){
            this.del_quick_go_ch();
        }else{
            if (this.is_tv){
                this.hist_back();
            }
        }
    }).bind(key.BACK, this);
    
    this.change_aspect.bind(key.FRAME, this);
    
    (function(){
        if (stb.pvr){
            if (stb.pvr.has_active_rec){
                
            }else{
                if (this.ch_idx == stb.pvr.current_ch_id){
                    stb.pvr.stop_rec();
                }else{
                    stb.pvr.start_rec(this.cur_tv_item);
                }
            }
        }
    }).bind(key.REC, this);
}

player.prototype.save_fav_ids = function(){
    _debug('player.save_fav');
    
    stb.load(

        {
            'type'   : 'itv',
            'action' : 'set_fav',
            'fav_ch' : this.fav_channels_ids
        },
        
        function(result){
            _debug('fav_saved', result);
        },
        
        this
    )
}

player.prototype.get_file_type = function(item){
    
    var cmd = '';
    
    if (typeof(item) == 'object'){
        cmd = item.cmd;
    }else{
        cmd = item;
    }
    
    var p = /^(.*)\.(\S+)$/
    
    var ext = ['mp3', 'ac3', 'mov', 'vob', 'wav'];
    
    var type = 'video';
    
    if (ext.indexOf(cmd.replace(p, "$2")) != -1){
        type = 'audio';
    }
    
    _debug('file_type', type);
    
    return type;
}

player.prototype.set_pos_button_to_cur_time = function(){
    _debug('player.set_pos_button_to_cur_time');
    
    try{
        this.cur_pos_time = stb.GetPosTime();
        
        _debug('this.cur_pos_time', this.cur_pos_time);
    
        this.set_pos_button(this.cur_pos_time);
    }catch(e){
        _debug(e);
    }
}

player.prototype.set_pos_button = function(to_time){
    _debug('player.set_pos_button', to_time);
    
    this.new_pos_time = to_time;
    
    try{
        
        if (to_time > this.cur_media_length){
            to_time = this.cur_media_length;
        }else if (to_time < 0){
            to_time = 0;
        }
        
        this.update_current_time(to_time);
        
        var to_pos = Math.round(590*to_time/this.cur_media_length);
        _debug('to_pos', to_pos);
        
        this.info.pos_button.moveX(to_pos);
    }catch(e){
        _debug(e);
    }
}

player.prototype.set_pos_and_play = function(reset){
    _debug('set_pos_and_play');
    
    if(!this.info.on){
        return;
    }
    
    window.clearTimeout(this.info.hide_timeout);
    
    try{
        if (!reset){
            _debug('stb.SetPosTime', this.new_pos_time);
            stb.SetPosTime(this.new_pos_time);
        }
    }catch(e){
        _debug(e);
    }finally{
        this.disable_pause();
        this.info.dom_obj.hide();
        this.info.on = false;
        
        this.pos_step  = 10;
        this.diff_pos  = 0;
        this.next_step = 0;
    }
}

player.prototype.move_pos = function(dir){
    _debug('player.move_pos', dir);
    
    _debug('this.info.on', this.info.on);
    _debug('this.is_tv', this.is_tv);
    
    if (this.is_tv){
        return;
    }
    
    if (!this.info.on){
        this.show_info();
    }
    
    window.clearTimeout(this.info.hide_timeout);
    
    var self = this;
    
    this.info.hide_timeout = window.setTimeout(function(){
        self.set_pos_and_play();
    }, 4000);
    
    if (!this.pause.on){
        try{
            stb.Pause();
        }catch(e){
            _debug(e);
        };
        this.pause.on = true;
    }
    
    if (this.prev_move_pos_dir != dir){
        this.pos_step = 10;
        this.next_step = 0;
    }
    
    this.prev_move_pos_dir = dir;
    
    _debug('this.next_step', this.next_step);
    
    if ((this.diff_pos+this.next_step+this.cur_pos_time) > this.cur_media_length){
        this.diff_pos = this.cur_media_length - this.cur_pos_time;
    }else if ((this.diff_pos+this.next_step+this.cur_pos_time) < 0){
        this.diff_pos = -this.cur_pos_time;
    }else{
        this.next_step += this.pos_step;
    }
    
    if (dir>0){
        this.diff_pos += this.next_step;
    }else{
        this.diff_pos -= this.next_step;
    }
    
    _debug('this.diff_pos', this.diff_pos);
    
    var new_pos_time = this.cur_pos_time + this.diff_pos;
    
    _debug('new_pos_time', new_pos_time);
    
    try{
        this.set_pos_button(new_pos_time);
    }catch(e){
        _debug(e);
    }
}

player.prototype.update_current_time = function(cur_time){
    _debug('player.update_current_time', cur_time);
    
    this.info.pos_time.innerHTML = this.sec_to_human_time(cur_time) + '/' + this.sec_to_human_time(this.cur_media_length);
}

player.prototype.sec_to_human_time = function(seconds){
    var hh = Math.floor(seconds/3600);
    var mm = Math.floor((seconds - 3600*hh)/60);
    var ss = seconds - hh*3600 - mm*60;
    
    if (hh<10){
        hh = '0'+hh;
    }
    
    if(mm<10){
        mm = '0'+mm;
    }
    
    if(ss<10){
        ss = '0'+ss;
    }
    
    return hh+':'+mm+':'+ss;
}

player.prototype.init_quick_ch_switch = function(){
    _debug('player.init_quick_ch_switch');
    
    this.quick_ch_switch.dom_obj = create_block_element('quick_ch_switch');
    
    this.quick_ch_switch.input = create_block_element('quick_ch_input', this.quick_ch_switch.dom_obj);
    
    this.quick_ch_switch.dom_obj.hide();
}

player.prototype.show_quick_ch_switch = function(num){
    _debug('player.show_quick_ch_switch');
    
    if (!this.is_tv){
        return;
    }
    
    if (!this.quick_ch_switch.on){
        this.quick_ch_switch.dom_obj.show();
        this.quick_ch_switch.on = true;
    }
    
    if (this.quick_ch_switch.input.innerHTML.length < 3){
        if (this.quick_ch_switch.input.innerHTML.length == 0 && num == 0){
            
        }else{
            this.quick_ch_switch.input.innerHTML = this.quick_ch_switch.input.innerHTML + '' + num;
        }
    }
    
    this.t_hide_quick_ch_switch();
}

player.prototype.quick_go_to_ch = function(){
    _debug('player.quick_go_to_ch');
    
    var ch_num = parseInt(this.quick_ch_switch.input.innerHTML);
    
    var item = {};
    
    if (stb.user.fav_itv_on){
        
        this.f_ch_idx = this.fav_channels.getIdxByVal('number', ch_num);
        
        if (this.f_ch_idx >= 0){
            
        }else{
            this.f_ch_idx = 0;
        }
        
        item = this.fav_channels[this.f_ch_idx];
        
    }else{
        
        this.ch_idx = this.channels.getIdxByVal('number', ch_num);
        
        if (this.ch_idx >= 0){
            
        }else{
            this.ch_idx = 0;
        }
        
        item = this.channels[this.ch_idx];
        
    }
    
    if (!empty(item)){
        stb.player.need_show_info = 1;
        stb.player.play(item);
    }
}

player.prototype.del_quick_go_ch = function(){
    _debug('player.del_quick_go_ch');
    
    if (!this.quick_ch_switch.on){
        return;
    }
    
    this.t_hide_quick_ch_switch();
    
    this.quick_ch_switch.input.innerHTML = this.quick_ch_switch.input.innerHTML.substr(0, this.quick_ch_switch.input.innerHTML.length - 1);
    
    this.quick_ch_switch.input.innerHTML = ch_hum;
}

player.prototype.t_hide_quick_ch_switch = function(){
    _debug('player.t_hide_quick_ch_switch');
    
    window.clearTimeout(this.quick_ch_switch.hide_timer);
    
    var self = this;
    
    this.quick_ch_switch.hide_timer = window.setTimeout(function(){
        
        self.hide_quick_ch_switch();
        
    }, this.quick_ch_switch.hide_to);
}

player.prototype.hide_quick_ch_switch = function(){
    _debug('player.hide_quick_ch_switch');
    
    if (!this.quick_ch_switch.on){
        return;
    }
    
    this.quick_go_to_ch();
    
    this.quick_ch_switch.dom_obj.hide();
    this.quick_ch_switch.on = false;
    
    this.quick_ch_switch.input.innerHTML = '';
}

player.prototype.cancel_quick_ch_switch = function(){
    _debug('player.cancel_quick_ch_switch');
    
    window.clearTimeout(this.quick_ch_switch.hide_timer);
            
    this.quick_ch_switch.dom_obj.hide();
    this.quick_ch_switch.on = false;
    
    this.quick_ch_switch.input.innerHTML = '';
}

player.prototype.change_aspect = function(){
    _debug('player.change_aspect');
    
    if (stb.aspect_idx < stb.aspect_array.length-1){
        stb.aspect_idx++;
    }else{
        stb.aspect_idx = 0;
    }
    
    _debug('set aspect', stb.aspect_array[stb.aspect_idx].alias);
    
    stb.SetAspect(stb.aspect_array[stb.aspect_idx].mode);
    
    stb.load(

        {
            "type"   : "stb",
            "action" : "set_aspect",
            "aspect" : stb.aspect_array[stb.aspect_idx].mode
        },
        
        function(result){
            
        },
        
        this
    )
}

player.prototype.hist_back = function(){
    
    this.need_show_info = 1;
    
    var item;
    
    if (stb.user.fav_itv_on){
        item = this.hist_f_ch_idx[0];
    }else{
        item = this.hist_ch_idx[0];
    }
    
    if (!empty(item)){
        this.play(item);
    }
}

player.prototype.audio_pid = {
    
    all_pids : [],
    cur_pid : 0,
    cur_pid_idx : 0,
    
    get_all : function(){
        _debug('audio_pid.get_all');
    
        var audio_pids = stb.GetAudioPIDs();
        
        _debug('audio_pids str', audio_pids);
        
        audio_pids = eval('(' + audio_pids + ')');
        
        _debug('audio_pids obj', audio_pids);
        
        this.cur_pid = stb.GetAudioPID();
        
        _debug('cur_pid', this.cur_pid);
        
        this.cur_pid_idx = audio_pids.getIdxByVal('pid', this.cur_pid);
        
        if (this.cur_pid_idx !== null){
            audio_pids[this.cur_pid_idx].selected = true;
        }
        
        this.all_pids = audio_pids;
    },
    
    set : function(pid){
        _debug('audio_pid.set', pid);
    
        this.all_pids[this.cur_pid_idx].selected = false;
        this.cur_pid = pid;
        this.cur_pid_idx = this.all_pids.getIdxByVal('pid', this.cur_pid);
        
        stb.SetAudioPID(pid);
    },
    
    get_for_menu : function(){
        _debug('audio_pid.get_for_menu');
    
        var lang;
        var title;
        var map = [];
        
        for (var i=0; i<this.all_pids.length; i++){
            
            if (this.all_pids[i].lang[1] != ''){
                lang = ' - ' + this.all_pids[i].lang[1];
            }else if (this.all_pids[i].lang[0] != ''){
                lang = ' - ' + this.all_pids[i].lang[0];
            }else{
                lang = '';
            }
            
            title = get_word('player_track') + ' ' + (i+1) + lang;
            
            map.push({"title" : title, "cmd" : (function(pid){return function(){stb.player.audio_pid.set(pid)}})(this.all_pids[i].pid), "active" : !!this.all_pids[i].selected});
        }
        
        _debug('map', map);
        
        return map;
    }
}

player.prototype.subtitle_pid = {
    
    all_pids : [],
    cur_pid : 0,
    cur_pid_idx : 0,
    
    get_all : function(){
        _debug('subtitle_pid.get_all');
    
        var subtitle_pids = stb.GetSubtitlePIDs();
        
        _debug('subtitle_pids str', subtitle_pids);
        
        subtitle_pids = eval('(' + subtitle_pids + ')');
        
        this.cur_pid = stb.GetSubtitlePID();
        
        _debug('cur_pid', this.cur_pid);
        
        this.cur_pid_idx = subtitle_pids.getIdxByVal('pid', this.cur_pid);
        
        _debug('this.cur_pid_idx', this.cur_pid_idx);
        
        if (this.cur_pid_idx !== null){
            subtitle_pids[this.cur_pid_idx].selected = true;
        }
        
        this.all_pids = subtitle_pids;
    },
    
    set : function(){
        _debug('subtitle_pid.set', pid);
    
        stb.SetSubtitles(true);
        
        this.all_pids[this.cur_pid_idx].selected = false;
        this.cur_pid = pid;
        this.cur_pid_idx = this.all_pids.getIdxByVal('pid', this.cur_pid);
        
        stb.SetSubtitlePID(pid);
    },
    
    get_for_menu : function(){
        _debug('subtitle_pid.get_for_menu');
    
        var lang;
        var title;
        var map = [];
        
        map.push({'title' : get_word('player_off'), 'cmd' : function(){stb.SetSubtitles(false)}, 'active' : true});
        
        for (var i=0; i<this.all_pids.length; i++){
            
            if (this.all_pids[i].lang[1] != ''){
                lang = ' - ' + this.all_pids[i].lang[1];
            }else if (this.all_pids[i].lang[0] != ''){
                lang = ' - ' + this.all_pids[i].lang[0];
            }else{
                lang = '';
            }
            
            title = get_word('player_subtitle') + (i+1) + lang;
        
            map.push({'title' : title, 'cmd' : (function(pid){return function(){stb.player.subtitle_pid.set(pid)}})(this.all_pids[i].pid), 'active' : this.all_pids[i].selected});
        }
        
        return map;
    }
}

player.prototype.init_con_menu = function(){
    _debug('player.init_con_menu');
    
    var map = [
            {
                "title" : get_word('player_claim'),
                "cmd"   : [
                    {
                        "cmd"   : function(){stb.player.send_claim('sound')},
                        "title" : get_word('player_on_sound'),
                    },
                    {
                        "cmd"   : function(){stb.player.send_claim('video')},
                        "title" : get_word('player_on_video'),
                    }
                  ]
            }
        ];

    
    this.con_menu.construct(map);
        
    /*this.con_menu = new context_menu(map);
    this.con_menu.bind();
    this.con_menu.set_x_offset(100);
    this.con_menu.set_y_offset(100);*/
}

player.prototype.build_con_menu = function(){
    _debug('player.build_con_menu');
    
    if (this.con_menu.map.length > 1){
        return;
    }
    
    this.con_menu.map.unshift(
        {
            "title" : get_word('player_subtitle'),
            "type"  : "switch",
            "cmd"   : this.subtitle_pid.get_for_menu()
        }
    )
        
    this.con_menu.map.unshift(
        {
            "title" : get_word('player_audio'),
            "type"  : "switch",
            "cmd"   : this.audio_pid.get_for_menu()
        }
    );
    
    _debug('this.con_menu.map', this.con_menu.map);
    
    if (this.con_menu.on){
        this.con_menu.hide();
        
        /*this.con_menu.destroy_container();
        this.con_menu = new context_menu(this.con_menu.map);
        this.con_menu.bind();
        this.con_menu.set_x_offset(100);
        this.con_menu.set_y_offset(100);*/
        
        this.con_menu.construct(this.con_menu.map);
        this.con_menu.show();
    }else{
        
        this.con_menu.construct(this.con_menu.map);
        
        /*this.con_menu.destroy_container();
        this.con_menu = new context_menu(this.con_menu.map);
        this.con_menu.bind();
        this.con_menu.set_x_offset(100);
        this.con_menu.set_y_offset(100);*/
    }
}

player.prototype.send_claim = function(type){
    _debug('player.send_claim', type);
    
    if (this.is_tv){
        this.itv_claim(type);
    }else if(stb.cur_place == 'vclub'){
        this.video_claim(type);
    }else if(stb.cur_place == 'karaoke'){
        this.karaoke_claim(type);
    }
    
    stb.notice.show(get_word('player_ty'));
}

player.prototype.video_claim = function(type){
    _debug('player.video_claim', type);
    
    stb.load(
        {
            "type"      : "vod",
            "action"    : "set_claim",
            "id"        : this.cur_media_item.id,
            "real_type" : type
        },
        function(result){
            
        }
    );
}

player.prototype.itv_claim = function(type){
    _debug('player.itv_claim', type);
    
    stb.load(
        {
            "type"      : "itv",
            "action"    : "set_claim",
            "id"        : this.cur_media_item.id,
            "real_type" : type
        },
        function(result){
            
        }
    );
}

player.prototype.karaoke_claim = function(type){
    _debug('player.karaoke_claim', type);
    
    stb.load(
        {
            "type"      : "karaoke",
            "action"    : "set_claim",
            "id"        : this.cur_media_item.id,
            "real_type" : type
        },
        function(result){
            
        }
    );
}

player.prototype.set_fav_status = function(){
    _debug('player.save_fav_status');
    
    stb.load(
        {
            "type"       : "itv",
            "action"     : "set_fav_status",
            "fav_itv_on" : stb.user.fav_itv_on
        },
        function(result){
            
        }
    );
}

/*
 * END Player
 */