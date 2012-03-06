/**
 * Remote PVR module
 */

(function(){

    /* RECORDS */
    function RemotePvr(){

        this.layer_name = 'remote_pvr';

        this.row_blocks = ['ch_name', 't_start', 'length'];

        this.load_params = {
            "type"   : "remote_pvr",
            "action" : "get_ordered_list"
        };

        this.superclass = ListLayer.prototype;

        this.duration_input = new DurationInputBox({"parent" : stb.player, "max_val" : stb.user['record_max_length']});

        this.init = function(){
            _debug('remote_pvr.init');

            this.superclass.init.call(this);

            this.load_recording_ch_ids();

            var rest_length_block = create_block_element('rest_length_block', this.dom_obj);

            create_inline_element('rest_length_title', rest_length_block).innerHTML = get_word('rest_length_title') + ': ';
            this.rest_length = create_inline_element('rest_length', rest_length_block);
        };

        this.hide = function(do_not_reset){
            _debug('remote_pvr.hide', do_not_reset);

            try{

                if (this.on && !do_not_reset){
                    if (stb.player.on){
                        stb.player.stop();
                    }
                }

                this.superclass.hide.call(this, do_not_reset);

                /*if (!do_not_reset){
                    this.reset();
                }*/

            }catch(e){
                _debug(e);
            }
        };

        /*this.reset = function(){
            _debug('remote_pvr.reset');

            this.cur_row  = 0;
            this.cur_page = 1;
            this.total_pages = 1;
        };*/

        this.play = function(play_url){
            _debug('remote_pvr.play', play_url);

            var self = this;

            if (!this.data_items[this.cur_row].started){
                return;
            }

            stb.player.on_create_link = function(result){
                _debug('remote_pvr.on_create_link', result);

                if (result.error == 'limit'){
                    stb.notice.show(word['player_limit_notice']);
                }else if(result.error == 'nothing_to_play'){
                    stb.notice.show(word['player_file_missing']);
                }else if(result.error == 'link_fault'){
                    stb.notice.show(word['player_server_error']);
                }else{

                    if (play_url){
                        self.hide(true);

                        stb.player.prev_layer = self;
                        stb.player.need_show_info = 1;
                        stb.player.play_now(result.cmd);
                    }else{

                        var url = /(http:\/\/[^\s]*)/.exec(result.cmd)[1];

                        self.add_download.call(self, url, result.to_file);
                    }
                }
            };

            var channel = stb.player.channels[stb.player.channels.getIdxById(this.data_items[this.cur_row].ch_id)];

            _debug('this.data_items[this.cur_row].ch_id', this.data_items[this.cur_row].ch_id);
            _debug('channel', channel);

            if (channel){
                stb.player.cur_tv_item = channel;
            }

            stb.player.play(this.data_items[this.cur_row]);
        };

        this.bind = function(){
            this.superclass.bind.apply(this);

            this.play.bind(key.OK, this, true);

            (function(){

                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this).bind(key.MENU, this);
        };

        this.stop_channel_rec = function(ch){
            _debug('remote_pvr.stop_channel_rec', ch);

            var idx = this.recording_ch_ids.getIdxByVal('ch_id', ch.id);
            
            _debug('idx', idx);
            _debug('this.recording_ch_ids[idx]', this.recording_ch_ids[idx]);

            if (idx === null){
                return;
            }

            var rec_id = this.recording_ch_ids[idx].id;
            var self = this;

            if (idx !== null){
                //this.stop_rec(this.recording_ch_ids[idx].id);
                stb.confirm.push({
                    "msg" : get_word('remote_pvr_stop_confirm'),
                    "confirm_callback" : function(){
                        self.stop_rec.call(self, rec_id)
                    }
                });
            }
        };

        this.rec_switch = function(ch){
            _debug('remote_pvr.rec_switch', ch);

            if (!ch['mc_cmd']){
                stb.notice.show(get_word('channel_recording_restricted'));
                return;
            }

            _debug('stb.player.prev_layer.on', stb.player.prev_layer.on);

            if (stb.player.prev_layer.on){
                return;
            }

            var idx = this.recording_ch_ids.getIdxByVal('ch_id', ch.id);

            _debug('idx', idx);
            _debug('this.recording_ch_ids[idx]', this.recording_ch_ids[idx]);

            if (idx !== null){
                //this.stop_rec(this.recording_ch_ids[idx].id);
                var now = new Date().getTime() / 1000;

                if ((now - this.recording_ch_ids[idx].t_start_ts) < 120){

                    if (this.duration_input.on){
                        this.duration_input.hide();
                    }else{

                        var self = this;
                        var rec_id = this.recording_ch_ids[idx].id;

                        this.duration_input.callback = function(duration){
                            _debug('callback duration', duration);
                            
                            stb.load(
                                {
                                    "type"     : "remote_pvr",
                                    "action"   : "stop_rec_deferred",
                                    "rec_id"   : rec_id,
                                    "duration" : duration
                                },
                                function(result){
                                    _debug('stop_rec_deferred result', result);

                                    if (result){

                                        var stop_time = parseInt(result);

                                        var now = new Date().getTime() / 1000;

                                        var stop_t = (stop_time - now) * 1000;

                                        _debug('now', now);
                                        _debug('stop_t', stop_t);

                                        if (stop_t < 0) stop_t = 0;

                                        window.setTimeout(function(){
                                            _debug('delete rec');
                                            _debug('rec_id', rec_id);
                                            var idx = self.recording_ch_ids.getIdxByVal('id', rec_id);
                                            _debug('idx', idx);

                                            if (idx === null){
                                                return;
                                            }

                                            if (stb.player.is_tv){
                                                if (stb.player.cur_tv_item.id == self.recording_ch_ids[idx].ch_id){
                                                    self.hide_rec_icon();
                                                }
                                            }
                                            _debug('self.recording_ch_ids before', self.recording_ch_ids);
                                            self.recording_ch_ids.splice(idx, 1);
                                            _debug('self.recording_ch_ids after', self.recording_ch_ids);
                                        }, stop_t);

                                    }else{
                                        stb.notice.show(word['recorder_server_error']);
                                    }

                                },
                                self
                            );
                        };

                        this.duration_input.show();
                    }
                }
            }else{
                this.start_rec(ch.id);
            }

            _debug('this.recording_ch_ids', this.recording_ch_ids);
        };

        this.add_del_by_program = function(){
            _debug('remote_pvr.add_del_by_program');

            var program = this.get_epg_item();

            _debug('program', program);

            if (program.mark_rec == 1){
                this.stop_rec(program.rec_id);
            }else{
                this.start_rec_deferred(program.id);
            }
        };

        this.get_epg_item = function(){
            _debug('epg_reminder.get_item');

            return this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col];
        };

        this.start_rec = function(ch_id){
            _debug('remote_pvr.start_rec', ch_id);

            stb.load(
                {
                    "type"   : "remote_pvr",
                    "action" : "start_rec_now",
                    "ch_id"  : ch_id

                },
                function(result){
                    _debug('result', result);

                    if (result){

                        this.recording_ch_ids = result;

                        var record = this.recording_ch_ids[this.recording_ch_ids.getIdxByVal('ch_id', ch_id)];

                        _debug('record', record);

                        this.show_rec_icon(this.recording_ch_ids[this.recording_ch_ids.getIdxByVal('ch_id', ch_id)]);

                        var self = this;

                        window.setTimeout(function(){
                            _debug('delete rec');
                            _debug('record.id', record.id);
                            var idx = self.recording_ch_ids.getIdxByVal('id', record.id);
                            _debug('idx', idx);

                            if (idx === null){
                                return;
                            }

                            if (stb.player.is_tv){
                                if (stb.player.cur_tv_item.id == self.recording_ch_ids[idx].ch_id){
                                    self.hide_rec_icon();
                                }
                            }
                            _debug('self.recording_ch_ids before', self.recording_ch_ids);
                            self.recording_ch_ids.splice(idx, 1);
                            _debug('self.recording_ch_ids after', self.recording_ch_ids);
                        }, record['t_stop_ts'] * 1000 - new Date().getTime());
                    }
                },
                this
            )
        };

        this.start_rec_deferred = function(program_id){
            _debug('remote_pvr.start_rec_deferred', program_id);

            stb.load(
                {
                    "type"        : "remote_pvr",
                    "action"      : "start_rec_deferred",
                    "program_id"  : program_id

                },

                function(result){
                    _debug('result', result);

                    if (result){

                        //this.recording_ch_ids = result;

                        //this.show_rec_icon(this.recording_ch_ids[this.recording_ch_ids.getIdxByVal('ch_id', ch_id)]);
                    }
                },
                this
            )
        };

        this.stop_rec = function(rec_id){
            _debug('remote_pvr.stop_rec', rec_id);

            this.hide_rec_icon();

            stb.load(
                {
                    "type"   : "remote_pvr",
                    "action" : "stop_rec",
                    "rec_id"  : rec_id

                },
                function(result){
                    _debug('result', result);

                    if (result){
                        var idx = this.recording_ch_ids.getIdxByVal('id', rec_id);

                        if (idx !== null){
                            this.recording_ch_ids.splice(idx, 1);
                        }
                    }

                    if (this.on){
                        this.load_data();
                    }
                },
                this
            )
        };

        this.stop_confirm = function(){
            _debug('remote_pvr.stop_confirm');

            var rec_id = this.data_items[this.cur_row].id;

            var self = this;

            stb.confirm.push({
                "msg" : get_word('remote_pvr_stop_confirm'),
                "confirm_callback" : function(){self.stop_rec.call(self, rec_id)}
            });
        };

        this.set_active_row = function(num){
            _debug('remote_pvr.set_active_row', num);

            try{
                this.superclass.set_active_row.call(this, num);
            }catch(e){
                
            }

            if (!this.data_items[this.cur_row]){
                this.color_buttons.get('red')   .disable();
                this.color_buttons.get('green') .disable();
                this.color_buttons.get('yellow').disable();
            }else{

                if (!parseInt(this.data_items[this.cur_row].ended)){
                    if (parseInt(this.data_items[this.cur_row].started)){
                        this.color_buttons.get('red')   .disable();
                        this.color_buttons.get('green') .enable();
                        this.color_buttons.get('yellow').disable();
                    }else{
                        this.color_buttons.get('red')   .enable();
                        this.color_buttons.get('green') .disable();
                        this.color_buttons.get('yellow').enable();
                    }
                }else{
                    this.color_buttons.get('red')   .enable();
                    this.color_buttons.get('green') .disable();
                    this.color_buttons.get('yellow').enable();
                }
            }
        };

        this.load_recording_ch_ids = function(){
            _debug('remote_pvr.load_recording_ch_ids');

            stb.load(
            {
                "type"   : "remote_pvr",
                "action" : "get_recording_ch_ids"
            },
            function(result){
                _debug('recording_ch_ids result', result);

                this.recording_ch_ids = result || [];

                stb.player.on_play = function(ch_id){
                    _debug('player.on_play', ch_id);
                    
                    if (stb.player.is_tv){

                        var rec_idx = module.remote_pvr.recording_ch_ids.getIdxByVal('ch_id', ch_id);

                        if(rec_idx !== null){
                            module.remote_pvr.show_rec_icon(module.remote_pvr.recording_ch_ids[rec_idx]);
                        }else{
                            module.remote_pvr.hide_rec_icon();
                        }
                    }else{
                        stb.player.rec.hide();
                    }
                }
            },
            this
            )
        };

        this.show_rec_icon = function(record){
            _debug('remote_pvr.show_rec_icon');

            window.clearInterval(this.tick_timer);

            _debug('record[t_start_ts]', record['t_start_ts']);
            _debug('stb.clock.timestamp', stb.clock.timestamp);

            stb.player.rec.set_seconds(this.convert_sec_to_human_time(record['t_start_ts'] - stb.clock.timestamp));

            var self = this;
            this.tick_timer = window.setInterval(function(){self.tick_s(record)}, 1000);

            stb.player.rec.show();
        };

        this.tick_s = function(record){
            stb.player.rec.set_seconds(this.convert_sec_to_human_time(stb.clock.timestamp - record['t_start_ts']));
        };
        
        this.hide_rec_icon = function(){
            _debug('remote_pvr.hide_rec_icon');

            stb.player.rec.hide();
            window.clearInterval(this.tick_timer);
            stb.player.rec.set_seconds(0);
        };

        this.convert_sec_to_human_hours = function(sec){

            var h = Math.floor(sec/3600);
            var m = Math.floor((sec - (h*3600)) / 60);
            var time = '';

            time += h+':';

            if (m<10){
                m = '0'+m;
            }

            time += m;

            return time;
        };

        this.convert_sec_to_human_time = function(sec){
            
            if (sec < 0 || isNaN(sec)){
                sec = 0;
            }

            var h = Math.floor(sec/3600);

            var m = Math.floor((sec - (h*3600)) / 60);

            var s = sec - (h*3600) - (m*60);

            var time = '';

            if(h){

                if (h<10){
                    h = '0'+h;
                }

                time += h+':';
            }

            if (m<10){
                m = '0'+m;
            }

            time += m+':';

            if (s<10){
                s = '0'+s;
            }

            time += s;

            return time;
        };

        this.del_confirm = function(){
            _debug('remote_pvr.del_confirm');

            var rec_id = this.data_items[this.cur_row].id;

            var self = this;

            stb.confirm.push({
                "msg" : get_word('remote_pvr_del_confirm'),
                "confirm_callback" : function(){self.del.call(self, rec_id)}
            });
        };

        this.del = function(rec_id){
            _debug('remote_pvr.del');

            stb.load(
                {
                    "type"   : "remote_pvr",
                    "action" : "del_rec",
                    "rec_id" : rec_id
                },
                function(result){
                    _debug('remote_pvr.del result', result);

                    this.load_data();
                },
                this
            )
        };

        this.fill_list = function(data){
            _debug('remote_pvr.fill_list');

            this.rest_length.innerText = this.convert_sec_to_human_hours(this.result.records_rest_length);

            this.superclass.fill_list.call(this, data);
        };

        this.add_download = function(url, to_filename){
            _debug('remote_pvr.add_download', url, to_filename);

            if (module.downloads){
                _debug('downloads');
                module.downloads.dialog.show({"parent" : this, "url" : url, "name" : to_filename});
            }
        }
    }

    RemotePvr.prototype = new ListLayer();

    var remote_pvr = new RemotePvr();

    remote_pvr.bind();
    remote_pvr.init();

    remote_pvr.set_wide_container();

    remote_pvr.init_left_ear(word['ears_back']);

    remote_pvr.init_color_buttons([
        {"label" : word['remote_pvr_del'], "cmd" : remote_pvr.del_confirm},
        {"label" : word['remote_pvr_stop'], "cmd" : remote_pvr.stop_confirm},
        {"label" : word['downloads_download'], "cmd" : module.downloads ? function(){remote_pvr.play.call(remote_pvr, false)} : ''},
        {"label" : word['empty'], "cmd" : ''}
    ]);

    remote_pvr.init_header_path(word['records_title']);

    remote_pvr.hide();

    module.remote_pvr = remote_pvr;

    /* END RECORDS */

    main_menu.add(word['records_title'], [], 'mm_ico_usb.png', function(){
        main_menu.hide();
        module.remote_pvr.show();
    },
    module.remote_pvr);

})();

loader.next();