/**
 * TV modile.
 */

(function(){

    /* TV */
    function tv_constructor(){
        this.row_blocks  = ['number', 'fav', 'lock', 'name'];
        
        this.load_params = {
            'type'   : 'itv',
            'action' : 'get_ordered_list'
        };
        
        this.preview_pos_map = [
            {"mode" : 576, "xsize" : 320, "ysize" : 256, "x" : 350, "y" : 74},
            {"mode" : 720, "xsize" : 569, "ysize" : 320, "x" : 622, "y" : 93},
            {"mode" : 1080, "xsize" : 854, "ysize" : 480, "x" : 933, "y" : 139}
        ];
        
        this.preview_pos = this.preview_pos_map[this.preview_pos_map.getIdxByVal("mode", parseInt(stb.video_mode))];
        
        this.superclass = Layer.prototype;
        
        this.sort_menu = {};
        
        this.view_menu = {};
        
        this.row_callback_timer;
        this.row_callback_timeout = 100;
        
        this.fav_manage_mode = false;
        
        this._show = function(genre){
            
            _debug('tv._show');
            
            this.load_params['genre'] = genre.id;
            
            this.update_header_path([{"alias" : "genre", "item" : genre.title}]);
            
            this.sort_menu.action();
            
            this.superclass.show.call(this);
            
            try{
                if (this.cur_view == 'short'){
                    stb.SetTopWin(1);
                    stb.SetViewport(this.preview_pos.xsize, this.preview_pos.ysize, this.preview_pos.x, this.preview_pos.y);
                }else{
                    stb.SetTopWin(0);
                }
            }catch(e){
                _debug(e);
            }
        }
        
        this.hide = function(do_not_reset){
            _debug('tv.hide', do_not_reset);
            
            try{
                
                if (this.fav_manage_mode){
                    this.switch_fav_manage_mode();
                }
                
                this.superclass.hide.call(this, do_not_reset);
            
                _debug('SetTopWin');
                stb.SetTopWin(0);
                stb.SetPIG(1, -1, -1, -1);
            }catch(e){
                _debug(e);
            }
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.play.bind(key.OK, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this);
        };
        
        this.play = function(){
            _debug('tv.play');
            
            this.hide(true);
            
            stb.player.prev_layer = this;
            
            if (!stb.player.on){
                stb.player.play(this.data_items[this.cur_row]);
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
            
            this.superclass.set_short_container.apply(this);
            
            try{
                _debug('this.preview_pos', this.preview_pos);
                stb.SetTopWin(1);
                stb.SetViewport(this.preview_pos.xsize, this.preview_pos.ysize, this.preview_pos.x, this.preview_pos.y);
            }catch(e){
                _debug(e);
            }
            
            this.fill_short_info(this.data_items[this.cur_row]);
        };
        
        this.init_short_info = function(){
            this.info_box = create_block_element('', this.main_container);
            
            this.short_info_box = create_block_element('tv_timetable', this.info_box);
            this.preview_box = create_block_element('tv_prev_window', this.info_box);
        };
        
        this.fill_short_info = function(item){
            _debug('fill_short_info');
            
            var epg = '';
            
            for (var i=0; i<item.epg.length; i++){
                epg += '<span>' + item.epg[i].t_time + ' - </span>' + item.epg[i].name + '<br>';
            }
            
            this.short_info_box.innerHTML = epg;
            
            try{
                stb.player.play(item);
            }catch(e){
                _debug(e);
            }
        };
        
        this.shift_row_callback = function(item){
            
            window.clearTimeout(this.row_callback_timer);
            
            var self = this;
            
            this.row_callback_timer = window.setTimeout(function(){
                
                self.fill_short_info(item);
                
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
    }
    
    tv_constructor.prototype = new Layer();
    
    var tv = new tv_constructor();
    
    tv.bind();
    tv.init();
    
    tv.init_short_info();
    
    tv.set_wide_container();
    
    tv.init_left_ear('ears_back');
    
    tv.init_color_buttons([
        {"label" : "ОТОБРАЖЕНИЕ", "cmd" : tv.view_switcher},
        {"label" : "СОРТИРОВКА", "cmd" : tv.sort_menu_switcher},
        {"label" : "ИЗБРАННОЕ", "cmd" : tv.add_del_fav},
        {"label" : "ДВИГАТЬ", "cmd" : tv.switch_fav_manage_mode}
    ]);
    
    tv.init_sort_menu(
        [
            {"label" : "по номеру", "cmd" : function(){
                this.parent.load_params.fav = false;
                this.parent.load_params.sortby = 'number';
            
                var idx = this.parent.color_buttons.getIdxByVal('color', 'blue');
                this.parent.color_buttons[idx].text_obj.setClass('disable_color_btn_text');
            }},
            {"label" : "по имени", "cmd" : function(){
                this.parent.load_params.fav = false;
                this.parent.load_params.sortby = 'name';
                
                var idx = this.parent.color_buttons.getIdxByVal('color', 'blue');
                this.parent.color_buttons[idx].text_obj.setClass('disable_color_btn_text');
            }},
            {"label" : "только избранное", "cmd" : function(){
                this.parent.load_params.sortby = 'fav';
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
            {"label" : "список", "cmd" : function(){this.parent.set_wide_container()}},
            {"label" : "список с инфо", "cmd" : function(){this.parent.set_short_container()}}
        ],
        {
            "offset_x" : 27,
            "need_reset_load_data" : false,
            "need_update_header"   : false
        }
    );
    
    tv.sort_menu.dependency  = [tv.view_menu];
    tv.view_menu.dependency  = [tv.sort_menu];
    
    tv.init_header_path('ТВ');
    
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
            
            main_menu.add('ТВ', map);
        }
    )

})();

loader.next();