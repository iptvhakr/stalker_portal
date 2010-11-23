/**
 * TV modile.
 */

(function(){

    /* TV */
    function tv_constructor(){
        
        this.layer_name = 'tv';
        
        this.row_blocks  = ['number', 'fav', 'lock', 'name', 'cur_playing'];
        
        this.load_params = {
            'type'   : 'itv',
            'action' : 'get_ordered_list'
        };
        
        this.preview_pos_map = [
            {"mode" : 576,  "xsize" : 320, "ysize" : 256, "x" : 350, "y" : 74},
            {"mode" : 720,  "xsize" : 569, "ysize" : 320, "x" : 622, "y" : 93},
            {"mode" : 1080, "xsize" : 854, "ysize" : 480, "x" : 933, "y" : 139}
        ];
        
        this.preview_pos = this.preview_pos_map[this.preview_pos_map.getIdxByVal("mode", parseInt(stb.video_mode))];
        
        this.superclass = ListLayer.prototype;
        
        this.sort_menu = {};
        
        this.view_menu = {};
        this.genres    = [];
        
        //this.last_ch_id = 0;
        
        this.quick_ch_switch = {"on" : false, "hide_to" : 3000};
        
        this.row_callback_timer;
        this.row_callback_timeout = 500;
        
        this.fav_manage_mode = false;
        
        this.password_input = new password_input({"parent" : this});
        this.password_input.bind();
        
        this._show = function(genre){
            
            _debug('tv._show', genre);
            
            var genre = genre || this.genres[0];
            
            this.load_params['genre'] = genre.id;
            
            this.update_header_path([{"alias" : "genre", "item" : genre.title}]);
            
            this.sort_menu.action();
            
            this.show();
        };
        
        this.show = function(do_not_load){
            _debug('tv.show', do_not_load);
            
            this.cur_page = 0;
            
            //this.superclass.show.call(this, do_not_load);
            //this.superclass.show.call(this, false);
            
            //_debug('this.last_ch_id', this.last_ch_id);
            //_debug('stb.player.cur_media_item.id', stb.player.cur_media_item.id);
            //_debug('this.cur_view', this.cur_view);
            
            /*if ((this.cur_view == 'short') && (this.last_ch_id == stb.player.cur_media_item.id)){
                this.superclass.show.call(this, true);
            }else{
                this.superclass.show.call(this, false);
            }*/
            
            this.superclass.show.call(this, false);
            
            stb.clock.show();
            
            try{
                _debug('tv.cur_view', this.cur_view);
                
                if (this.cur_view == 'short'){
                    stb.SetTopWin(1);
                    stb.SetViewport(this.preview_pos.xsize, this.preview_pos.ysize, this.preview_pos.x, this.preview_pos.y);
                    //stb.player.need_show_info = 0;
                    //stb.player.play(item);
                }else{
                    stb.SetTopWin(0);
                }
            }catch(e){
                _debug(e);
            }
        };
        
        this.hide = function(do_not_reset){
            _debug('tv.hide', do_not_reset);
            
            this.cur_page = 0;
            
            try{
                
                if (this.fav_manage_mode){
                    this.switch_fav_manage_mode();
                }
                
                this.sort_menu.on && this.sort_menu.hide && this.sort_menu.hide();
                this.view_menu.on && this.view_menu.hide && this.view_menu.hide();
                
                this.password_input.on && this.password_input.hide && this.password_input.hide();
                
                this.superclass.hide.call(this, do_not_reset);
            
                _debug('SetTopWin');
                
                if (this.quick_ch_switch.on){
                    this.cancel_quick_ch_switch();
                }
                
                if (!do_not_reset){
                    stb.player.stop();
                    //this.last_ch_id = 0;
                }
                
                stb.SetTopWin(0);
                stb.SetPIG(1, -1, -1, -1);
            }catch(e){
                _debug(e);
            }
        };
        
        this.clear_list = function(){
            _debug('tv.clear_list');
            
            this.superclass.clear_list.call(this);
            
            this.short_info_box.innerHTML = '';
        }
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            //this.check_for_play.bind(key.OK, this);
            
            (function(){
        
                if (this.quick_ch_switch.on){
                    this.hide_quick_ch_switch();
                }else{
                    this.check_for_play();
                }
                
            }).bind(key.OK, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.LEFT, this).bind(key.MENU, this);
            
            (function(){
                if (this.quick_ch_switch.on){
                    this.cancel_quick_ch_switch();
                }else{
                    this.hide();
                    main_menu.show();
                }
            }).bind(key.EXIT, this);
            
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
                }
            }).bind(key.BACK, this);
        };
        
        this.check_for_play = function(){
            _debug('tv.check_for_play');
            
            _debug('lock', this.data_items[this.cur_row].lock);
            
            if(!this.data_items[this.cur_row].open){
                stb.notice.show(word['msg_channel_not_available']);
                return;
            }
            
            if (this.data_items[this.cur_row].lock){
                var self = this;
                
                this.password_input.callback = function(){
                    self.play();
                }
                
                this.password_input.show();
            }else{
                this.play();
            }
        };
        
        this.play = function(){
            _debug('tv.play');
            
            _debug('this.data_items[this.cur_row]', this.data_items[this.cur_row]);
            _debug('empty(this.data_items[this.cur_row]', empty(this.data_items[this.cur_row]));
            
            if (empty(this.data_items[this.cur_row])){
                return;
            }
            
            /*this.hide(true);
            
            stb.player.prev_layer = this;*/
            
            _debug('stb.player.on', stb.player.on);
            
            if (!stb.player.on){
                /*if (this.cur_view == 'short'){
                    stb.player.need_show_info = 0;
                }else{
                    stb.player.need_show_info = 1;
                }*/
                
                //this.last_ch_id = this.data_items[this.cur_row].id;
                
                if (this.cur_view != 'short'){
                    this.hide(true);
            
                    stb.player.prev_layer = this;
                    
                    stb.player.need_show_info = 1;
                    stb.player.play(this.data_items[this.cur_row]);
                }else{
                    //this.show_info(this.cur_media_item);
                }
            }else{
                if (this.cur_view == 'short'){
                    this.hide(true);
                
                    stb.player.prev_layer = this;
                }
            }
        };
        
        this.init_sort_menu = function(map, options){
            this.sort_menu = new bottom_menu(this, options);
            this.sort_menu.init(map);
            this.sort_menu.bind();
        };
        
        this.sort_menu_switcher = function(){
            
            if (this.fav_manage_mode){
                return;
            }
            
            if (this.sort_menu && this.sort_menu.on){
                this.sort_menu.hide();
            }else{
                this.sort_menu.show();
            }
        };
        
        this.init_view_menu = function(map, options){
            this.view_menu = new bottom_menu(this, options);
            this.view_menu.init(map);
            this.view_menu.bind();
        };
        
        this.view_switcher = function(){
            
            if (this.fav_manage_mode){
                return;
            }
            
            if (this.view_menu && this.view_menu.on){
                this.view_menu.hide();
            }else{
                this.view_menu.show();
            }
        };
        
        this.set_wide_container = function(){
            
            if (this.cur_view == 'short'){
                stb.player.stop();
            }
            
            this.superclass.set_wide_container.apply(this);

            try{
                stb.SetTopWin(0);
                stb.SetPIG(1, -1, -1, -1);
            }catch(e){
                _debug(e);
            }
        };
        
        this.set_short_container = function(){
            _debug('tv.set_short_container');
            
            this.superclass.set_short_container.apply(this);
            
            try{
                _debug('this.preview_pos', this.preview_pos);
                stb.SetTopWin(1);
                stb.SetViewport(this.preview_pos.xsize, this.preview_pos.ysize, this.preview_pos.x, this.preview_pos.y);
            }catch(e){
                _debug(e);
            }
            
            this.fill_short_info(this.data_items[this.cur_row]);
            
            if (this.data_items && this.data_items[this.cur_row]){
                stb.player.need_show_info = 0;
                stb.player.play(this.data_items[this.cur_row]);
            }
            
            // set active list w/ info item
            if (this.view_menu && this.view_menu.set_passive_row){
                this.view_menu.set_passive_row();
                this.view_menu.cur_row_idx = 0;
                this.view_menu.set_active_row();
            }
        };
        
        this.init_short_info = function(){
            this.info_box = create_block_element('', this.main_container);
            
            this.short_info_box = create_block_element('tv_timetable', this.info_box);
            this.preview_box = create_block_element('tv_prev_window', this.info_box);
            this.clock_box = create_block_element('tv_clock', this.info_box);
        };
        
        this.fill_list = function(data){
            _debug('tv.fill_list');
            
            this.superclass.fill_list.call(this, data);
            
            if (this.cur_view == 'short'){
                this.fill_short_info(this.data_items[this.cur_row])
            }
        };
        
        this.fill_short_info = function(item){
            _debug('tv.fill_short_info');
            
            var epg = '';
            
            if (item && !item.open){
                epg += '<span class="current">' + word['msg_channel_not_available'] + '</span>';
            }else if (item && item.epg){
                
                var class_name = '';
                
                for (var i=0; i<item.epg.length; i++){
                    
                    if (i == 0){
                        class_name = 'current';
                    }else{
                        class_name = '';
                    }
                    
                    epg += '<span class="time">' + item.epg[i].t_time + ' - </span><span class="' + class_name + '">' + item.epg[i].name + '</span><br>';
                }
            }
            
            this.short_info_box.innerHTML = epg;
            
            try{
                //this.last_ch_id = this.data_items[this.cur_row].id;
                
                //stb.player.need_show_info = 0;
                //stb.player.play(item);
            }catch(e){
                _debug(e);
            }
        };
        
        this.shift_row_callback = function(item){
            _debug('tv.shift_row_callback');
            
            if(this.data_items[this.cur_row].id == stb.player.cur_media_item.id && stb.player.on){
                return;
            }
            
            window.clearTimeout(this.row_callback_timer);
            
            var self = this;
            
            this.row_callback_timer = window.setTimeout(function(){
                
                if (!self.on){
                    return;
                }
                
                self.fill_short_info(item);
                
                if (item.open){
                
                    stb.player.need_show_info = 0;
                    stb.player.play(item);
                }else{
                    stb.player.stop();
                }
                
            },
            this.row_callback_timeout);
        };
        
        this.add_to_fav = function(){
            _debug('tv.add_to_fav');
            
            _debug('this.player.fav_channels before', stb.player.fav_channels_ids);
            
            stb.player.fav_channels_ids.push(this.data_items[this.cur_row].id);
            
            _debug('this.player.fav_channels after', stb.player.fav_channels_ids);
            
            this.data_items[this.cur_row].fav = 1;
            
            this.map[this.cur_row].fav_block.show();
            this.active_row.fav_block.show();
            
            this.data_items[this.cur_row].number = stb.player.fav_channels.length + 1;
            
            stb.player.fav_channels.push(this.data_items[this.cur_row]);
            
            stb.player.save_fav_ids();
        };
        
        this.del_from_fav = function(){
            _debug('tv.del_from_fav');
            
            _debug('this.player.fav_channels before', stb.player.fav_channels_ids);
            
            var fav_idx = stb.player.fav_channels_ids.indexOf(this.data_items[this.cur_row].id);
            
            var removed_idx = stb.player.fav_channels_ids.splice(fav_idx, 1);
            
            _debug('removed_idx', removed_idx);
            
            _debug('this.player.fav_channels after', stb.player.fav_channels_ids);
            
            this.data_items[this.cur_row].fav = 0;
            
            this.map[this.cur_row].fav_block.hide();
            this.active_row.fav_block.hide();
            
            var fav_ch_idx = stb.player.fav_channels.getIdxByVal('id', this.data_items[this.cur_row].id);
            
            if (fav_ch_idx !== null){
                stb.player.fav_channels.splice(fav_ch_idx, 1);
            }
            
            stb.player.save_fav_ids();
        };
        
        this.add_del_fav = function(){
            _debug('tv.add_del_fav');
            
            if (this.fav_manage_mode){
                return;
            }
            
            if (this.load_params.fav == true){
                return;
            }
            
            if(this.data_items[this.cur_row].fav){
                this.del_from_fav();
            }else{
                this.add_to_fav();
            }
        };
        
        this.set_active_row = function(num){
            
            this.superclass.set_active_row.call(this, num);
            
            this.handling_block(this.data_items[num].number, this.active_row, 'number');
        };
        
        this.shift_row = function(dir){
            
            if (this.fav_manage_mode){
                var cur_data_items = this.data_items.clone();
                //var cur_row_data = this.data_items[this.cur_row].clone();
                var cur_row_data = this.data_items[this.cur_row]
                var cur_row_num  = this.cur_row;
                var cur_number   = cur_row_data.number;
                var cur_item_id  = cur_row_data.id;
                
                var cur_row_fav_idx = stb.player.fav_channels_ids.indexOf(cur_item_id);
                
                _debug('stb.player.fav_channels_ids before', stb.player.fav_channels_ids);
                
            }
            
            this.superclass.shift_row.call(this, dir);
            
            if (this.fav_manage_mode){
    
                var next_row_data = this.data_items[this.cur_row];
                var next_number   = next_row_data.number;
                var next_item_id  = this.data_items[this.cur_row].id;
                
                _debug('cur_number', cur_number);
                
                _debug('next_row_data.number before', next_row_data.number);
                
                next_row_data.number = cur_number;
                cur_row_data.number  = next_number;
                
                _debug('next_row_data.number after', next_row_data.number);
                
                var next_row_fav_idx = stb.player.fav_channels_ids.indexOf(next_item_id);
                
                stb.player.fav_channels_ids[cur_row_fav_idx]  = next_item_id;
                stb.player.fav_channels_ids[next_row_fav_idx] = cur_item_id;
                
                this.data_items[this.cur_row] = cur_row_data;
                this.data_items[cur_row_num] = next_row_data;
                                
                _debug('stb.player.fav_channels_ids after', stb.player.fav_channels_ids);
                
                for (var j=0; j<this.row_blocks.length; j++){
                    this.handling_block(cur_row_data[this.row_blocks[j]], this.map[this.cur_row], this.row_blocks[j]);
                    
                    this.handling_block(next_row_data[this.row_blocks[j]], this.map[cur_row_num], this.row_blocks[j]);
                }
            }
        };
        
        this.shift_page = function(dir){
            
            if (this.fav_manage_mode){
                stb.player.save_fav_ids();
            }
            
            this.superclass.shift_page.call(this, dir);
        };
        
        this.switch_fav_manage_mode = function(){
            _debug('tv.switch_fav_manage_mode');
            
            if (this.load_params.fav != true || this.cur_view != 'wide'){
                return;
            }
            
            _debug('typeof(tv.fav_manage_mode)', typeof(this.fav_manage_mode));
            _debug('tv.fav_manage_mode before', this.fav_manage_mode);
            
            if (this.fav_manage_mode){
                stb.player.save_fav_ids();
                this.active_row['row'].setClass('active_row_bg');
                
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'red')].text_obj.delClass();
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'green')].text_obj.delClass();
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'yellow')].text_obj.delClass();
            }else{
                this.active_row['row'].setClass('moved_active_row_bg');
                
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'red')].text_obj.setClass('disable_color_btn_text');
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'green')].text_obj.setClass('disable_color_btn_text');
                this.color_buttons[this.color_buttons.getIdxByVal('color', 'yellow')].text_obj.setClass('disable_color_btn_text');
            }
            
            this.fav_manage_mode = !this.fav_manage_mode;
            
            _debug('tv.fav_manage_mode after', this.fav_manage_mode);
        };
        
        this.init_quick_ch_switch = function(){
            _debug('tv.init_quick_ch_switch');
            
            this.quick_ch_switch.dom_obj = create_block_element('quick_ch_switch');
            
            this.quick_ch_switch.dom_obj.moveY(300);
            
            this.quick_ch_switch.input = create_block_element('quick_ch_input', this.quick_ch_switch.dom_obj);
            
            this.quick_ch_switch.dom_obj.hide();
        };
        
        this.show_quick_ch_switch = function(num){
            _debug('tv.show_quick_ch_switch');
            
            if (this.cur_view != 'short'){
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
        };
        
        this.quick_go_to_ch = function(){
            _debug('tv.quick_go_to_ch');
            
            var ch_num = parseInt(this.quick_ch_switch.input.innerHTML);
            
            _debug('ch_num', ch_num);
            
            var item = {};
            
            if (stb.user.fav_itv_on){
                
                stb.player.f_ch_idx = stb.player.fav_channels.getIdxByVal('number', ch_num);
                
                _debug('stb.player.f_ch_idx', stb.player.f_ch_idx);
                
                if (stb.player.f_ch_idx >= 0){
                    
                }else{
                    stb.player.f_ch_idx = 0;
                }
                
                _debug('stb.player.f_ch_idx', stb.player.f_ch_idx);
                
                item = stb.player.fav_channels[stb.player.f_ch_idx];
                
                _debug('item', item);
                
            }else{
                
                stb.player.ch_idx = stb.player.channels.getIdxByVal('number', ch_num);
                
                _debug('stb.player.ch_idx', stb.player.ch_idx);
                
                if (stb.player.ch_idx >= 0){
                    
                }else{
                    stb.player.ch_idx = 0;
                }
                
                _debug('stb.player.ch_idx', stb.player.ch_idx);
                
                item = stb.player.channels[stb.player.ch_idx];
                
                _debug('item', item);
            }
            
            if (!empty(item)){
                if (this.cur_view == 'short'){
                    
                    var self = this;
                    
                    stb.player.send_last_tv_id_callback = function(){self.load_data.apply(self)};
                    stb.player.need_show_info = 0;
                    stb.player.play(item);
                }else{
                    stb.player.send_last_tv_id(item.id);
                }
                
                
                this.cur_page = 0;
                //this.load_data();
            }
        };
        
        this.del_quick_go_ch = function(){
            _debug('tv.del_quick_go_ch');
            
            if (!this.quick_ch_switch.on){
                return;
            }
            
            this.t_hide_quick_ch_switch();
            
            this.quick_ch_switch.input.innerHTML = this.quick_ch_switch.input.innerHTML.substr(0, this.quick_ch_switch.input.innerHTML.length - 1);
            
            //this.quick_ch_switch.input.innerHTML = ch_hum;
        };
        
        this.t_hide_quick_ch_switch = function(){
            _debug('tv.t_hide_quick_ch_switch');
            
            window.clearTimeout(this.quick_ch_switch.hide_timer);
            
            var self = this;
            
            this.quick_ch_switch.hide_timer = window.setTimeout(function(){
                
                self.hide_quick_ch_switch();
                
            }, this.quick_ch_switch.hide_to);
        };
        
        this.hide_quick_ch_switch = function(){
            _debug('tv.hide_quick_ch_switch');
            
            if (!this.quick_ch_switch.on){
                return;
            }
            
            this.quick_go_to_ch();
            
            this.fill_short_info(this.data_items[this.cur_row]);
            
            this.cancel_quick_ch_switch();
        };
        
        this.cancel_quick_ch_switch = function(){
            _debug('tv.cancel_quick_ch_switch');
            
            window.clearTimeout(this.quick_ch_switch.hide_timer);
            
            this.quick_ch_switch.dom_obj.hide();
            this.quick_ch_switch.on = false;
            
            this.quick_ch_switch.input.innerHTML = '';
        };
    }
    
    tv_constructor.prototype = new ListLayer();
    
    var tv = new tv_constructor();
    
    tv.bind();
    tv.init();
    
    tv.init_short_info();
    
    tv.init_quick_ch_switch();
    
    //tv.set_wide_container();
    tv.set_short_container();
    
    tv.init_left_ear(word['ears_back']);
    
    tv.init_color_buttons([
        {"label" : word['tv_view'], "cmd" : tv.view_switcher},
        {"label" : word['tv_sort'], "cmd" : tv.sort_menu_switcher},
        {"label" : word['tv_favorite'], "cmd" : tv.add_del_fav},
        {"label" : word['tv_move'], "cmd" : tv.switch_fav_manage_mode}
    ]);
    
    tv.init_sort_menu(
        [
            {"label" : word['tv_by_number'], "cmd" : function(){
                this.parent.load_params.fav = false;
                stb.user.fav_itv_on = 0;
                stb.player.set_fav_status();
                this.parent.load_params.sortby = 'number';
                
                var idx = this.parent.color_buttons.getIdxByVal('color', 'blue');
                this.parent.color_buttons[idx].text_obj.setClass('disable_color_btn_text');
            }},
            {"label" : word['tv_by_title'], "cmd" : function(){
                this.parent.load_params.fav = false;
                stb.user.fav_itv_on = 0;
                stb.player.set_fav_status();
                this.parent.load_params.sortby = 'name';
                
                var idx = this.parent.color_buttons.getIdxByVal('color', 'blue');
                this.parent.color_buttons[idx].text_obj.setClass('disable_color_btn_text');
            }},
            {"label" : word['tv_only_favorite'], "cmd" : function(){
                this.parent.load_params.sortby = 'fav';
                stb.user.fav_itv_on = 1;
                stb.player.set_fav_status();
                this.parent.load_params.fav = true
                
                var idx = this.parent.color_buttons.getIdxByVal('color', 'blue');
                this.parent.color_buttons[idx].text_obj.delClass();
                
            }}
        ],
        {
            "offset_x" : 217
        }
    );
    
    tv.init_view_menu(
        [
            {"label" : word['tv_list_w_info'], "cmd" : function(){this.parent.set_short_container()}},
            {"label" : word['tv_list'], "cmd" : function(){this.parent.set_wide_container()}}
        ],
        {
            "offset_x" : 27,
            "need_reset_load_data" : false,
            "need_update_header"   : false
        }
    );
    
    tv.sort_menu.dependency  = [tv.view_menu];
    
    _debug('stb.user.fav_itv_on', stb.user.fav_itv_on);
    
    if(stb.user.fav_itv_on){
        tv.sort_menu.set_passive_row();
        tv.sort_menu.cur_row_idx = 2;
        tv.sort_menu.set_active_row();
        tv.sort_menu.action();
    }
    
    tv.view_menu.dependency  = [tv.sort_menu];
    
    tv.init_header_path(word['tv_title']);
    
    tv.hide();
    
    module.tv = tv;
    
    /* END TV */
    
    /* Integrate tv in main menu */
    
    stb.load(
        {
            "type"   : "itv",
            "action" : "get_genres"
        },
        function(result){
            _debug('callback genres');
            
            var genres = result;
            
            module.tv.genres = genres;
            
            var map = [];
    
            for(var i=0; i<genres.length; i++){
                map.push(
                
                    {
                        "title" : genres[i].title,
                        "cmd"   : (function(genre){
                            
                            
                            return function(){
                                _debug('genre.id', genre.id);
                            
                                main_menu.hide();
                                module.tv._show(genre);
                            }
                            
                        })(genres[i]),
                    }
                );
            }
            
            main_menu.add(word['tv_title'], map, 'i/mm_ico_tv.png', '', module.tv);
            
            loader.next();
        }
    )

})();

//loader.next();