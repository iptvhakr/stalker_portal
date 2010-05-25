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
    
    this.init();
    this.init_pause();
    this.init_show_info();
    this.init_quick_ch_switch();
    this.volume.init();
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
                
                if (this.media_type == 'stream'){
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
            
            if (this.is_tv){
                this.send_last_tv_id(this.cur_media_item.id);
            }
            
            window.clearTimeout(this.send_played_video_timer);
            
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
            
            break;
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
    
    if (cmd.indexOf('://') > 0){
        
        if (cmd.indexOf('udp://') || cmd.indexOf('rtp://')){
            this.is_tv = true;
        }
        
        return 'stream';
    }else{
        this.is_tv = false;
        return 'file';
    }
}

player.prototype.play_last = function(){
    _debug('player.play_last');
    
    this.play(this.cur_media_item);
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
    
    _debug('cmd: ', cmd);
    
    this.media_type = this.define_media_type(cmd);
    
    _debug('player.media_type: ', this.media_type);
    
    if (this.media_type == 'stream'){
        
        this.play_now(cmd);
        
    }else if (cmd.indexOf('usbdisk') > 0){
        
        this.play_now(cmd);
        
    }else{
        
        var series_number = item.cur_series || 0;
        
        this.create_link('vod', cmd, series_number);
    }
}

player.prototype.create_link = function(type, uri, series_number){
    
    var series_number = series_number || "";
    
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
            
            this.on_create_link && this.on_create_link(result);
        },
        
        this
    )
}

player.prototype.play_now = function(uri){
    _debug('player.play_now');
    
    _log('play', uri);
    
    this.start_time = Date.parse(new Date())/1000;

    if (this.need_show_info){
        this.show_info(this.cur_media_item);
    }
    
    try{
        stb.Play(uri);
    }catch(e){_debug(e)}
}

player.prototype.stop = function(){
    _debug('player.stop');
    
    this.prev_layer = {};
    
    this.need_show_info = 0;
    
    this.on_create_link = function(){};
    
    this.on = false;
    
    if(this.pause.on){
        this.disable_pause();
    }
    
    try{
        stb.Stop();
    }catch(e){}
    
    window.clearTimeout(this.send_played_itv_timer);
    window.clearTimeout(this.send_played_video_timer);
    window.clearTimeout(this.replay_channel_timer);
    
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
        
        if(this.is_tv){
            if (this.info.epg.isHidden()){
                this.info.epg.show();
            }
            
            if (!this.info.video_container.isHidden()){
                this.info.video_container.hide();
            }
            
            _debug('stb.epg_loader.get_epg(item.id)', stb.epg_loader.get_epg(item.id));
            
            this.info.epg.innerHTML = stb.epg_loader.get_epg(item.id);
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
        
        var title = '';
        
        if (item.hasOwnProperty('number')){
            title = item.number + '. ';
        }
        
        title += item.name
        
        this.info.title.innerHTML = title;
        
        if (this.is_tv){
            this.info.epg.innerHTML = stb.epg_loader.get_epg(item.id);
        }
        
        if (item.cur_series){
            this.info.pos_series.innerHTML = item.cur_series + ' серия';
        }else{
            this.info.pos_series.innerHTML = '';
        }
        
        var self = this;
        
        this.info.hide_timeout = window.setTimeout(function(){
            self.info.dom_obj.hide();
            self.info.on = false
        },
        this.info.hide_timer);
    }catch(e){
        _debug(e);
    }
}

player.prototype.switch_channel = function(dir, show_info){
    
    _debug('switch_channel', dir);
    
    if (!this.is_tv){
        return;
    }
    
    if (dir > 0){
        
        if (stb.user.fav_itv_on){
            
            if (this.f_ch_idx < this.fav_channels.length-1){
                this.f_ch_idx++;
            }else{
                this.f_ch_idx = 0;
            }
            
            _debug('this.f_ch_idx:', this.f_ch_idx);
            
            if (show_info){
                this.show_info(this.fav_channels[this.f_ch_idx]);
            }
            
            this.play(this.fav_channels[this.f_ch_idx]);
            
        }else{
            
            if (this.ch_idx < this.channels.length-1){
                this.ch_idx++;
            }else{
                this.ch_idx = 0;
            }
            
            _debug('this.ch_idx:', this.ch_idx);
            
            if (show_info){
                this.show_info(this.channels[this.ch_idx]);
            }
            
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
            
            if (show_info){
                this.show_info(this.fav_channels[this.f_ch_idx]);
            }
            this.play(this.fav_channels[this.f_ch_idx]);
            
        }else{
            
            if (this.ch_idx > 0){
                this.ch_idx--;
            }else{
                this.ch_idx = this.channels.length-1;
            }
            
            _debug('this.ch_idx:', this.ch_idx);
            
            if (show_info){
                this.show_info(this.channels[this.ch_idx]);
            }
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
        }
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
                //module.tv.cur_page = 0;
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
    
    this.switch_channel.bind(key.UP, self, 1);
    this.switch_channel.bind(key.DOWN, self, -1);
    
    this.switch_channel.bind(key.CHANNEL_NEXT, self, 1, true);
    this.switch_channel.bind(key.CHANNEL_PREV, self, -1, true);
    
    this.pause_switch.bind(key.PAUSE, this);
    
    (function(){
        if (this.info.on){
            this.set_pos_and_play(true);
        }else{
            this.show_prev_layer();
        }
    }).bind(key.EXIT, this);
    
    this.show_prev_layer.bind(key.STOP, self);
    
    this.show_info.bind(key.INFO, self);
    
    this.move_pos.bind(key.FFWD, this, 1).bind(key.RIGHT, this, 1);
    this.move_pos.bind(key.REW, this, -1).bind(key.LEFT, this, -1);
    
    (function(){
        
        if (this.info.on){
            this.set_pos_and_play();
        }else  if (this.quick_ch_switch.on){
            this.hide_quick_ch_switch();
        }else  if (this.prev_layer && this.prev_layer.cur_view == 'short'){
            this.show_prev_layer();
        }
        
    }).bind(key.OK, this);
    
    (function(){
        if (this.on){
            this.volume.control(1);
        }
    }).bind(key.VOL_UP, this);
    
    (function(){
        if (this.on){
            this.volume.control(-1);
        }
    }).bind(key.VOL_DOWN, this);
    
    
    (function(){
        if (this.on){
            this.volume.mute_switch();
        }
    }).bind(key.MUTE, this);
    
    this.show_quick_ch_switch.bind(key.NUM1, this, 1);
    this.show_quick_ch_switch.bind(key.NUM2, this, 2);
    this.show_quick_ch_switch.bind(key.NUM3, this, 3);
    this.show_quick_ch_switch.bind(key.NUM4, this, 3);
    this.show_quick_ch_switch.bind(key.NUM5, this, 5);
    this.show_quick_ch_switch.bind(key.NUM6, this, 6);
    this.show_quick_ch_switch.bind(key.NUM7, this, 7);
    this.show_quick_ch_switch.bind(key.NUM8, this, 8);
    this.show_quick_ch_switch.bind(key.NUM9, this, 9);
    this.show_quick_ch_switch.bind(key.NUM0, this, 0);
    
    this.del_quick_go_ch.bind(key.BACK, this);
    
    this.change_aspect.bind(key.FRAME, this);
    
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
            stb.SetPosTime(this.new_pos_time);
        }
    }catch(e){
        _debug(e);
    }finally{
        this.disable_pause();
        this.info.dom_obj.hide();
        this.info.on = false;
        
        this.pos_step = 10;
        this.diff_pos = 0;
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
        //this.set_pos_button_to_cur_time();
        //this.info.dom_obj.show();
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
    }
    
    this.prev_move_pos_dir = dir;
    
    if (dir>0){
        this.pos_step = 2*this.pos_step;
        this.next_step = this.pos_step;
    }else{
        this.pos_step = 2*this.pos_step;
        this.next_step = -this.pos_step;
    }
    
    _debug('this.next_step', this.next_step);
    
    if ((this.diff_pos+this.next_step+this.cur_pos_time) > this.cur_media_length){
        this.diff_pos = this.cur_media_length - this.cur_pos_time;
    }else if ((this.diff_pos+this.next_step+this.cur_pos_time) < 0){
        this.diff_pos = -this.cur_pos_time;
    }
    
    this.diff_pos += this.next_step;
    
    _debug('this.diff_pos', this.diff_pos);
    
    var new_pos_time = this.cur_pos_time + this.diff_pos;
    
    _debug('new_pos_time', new_pos_time);
    
    if (new_pos_time > this.cur_media_length){
        new_pos_time = this.cur_media_length;
    }else if (new_pos_time < 0){
        new_pos_time = 0;
    }
    
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

/*
 * END Player
 */