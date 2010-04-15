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
            
            if(this.data_items[this.cur_row].fav){
                this.del_from_fav();
            }else{
                this.add_to_fav();
            }
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
        {"label" : "", "cmd" : ''}
    ]);
    
    tv.init_sort_menu(
        [
            {"label" : "по номеру", "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'number'}},
            {"label" : "по имени", "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'name'}},
            {"label" : "только избранное", "cmd" : function(){this.parent.load_params.sortby = 'name'; this.parent.load_params.fav = true}}
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