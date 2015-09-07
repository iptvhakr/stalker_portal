/**
 * Karaoke module.
 */
(function(){
    
    /* KARAOKE */
    function karaoke_constructor(){
        
        this.layer_name = 'karaoke';
        
        this.row_blocks  = ['singer', 'name'];
        
        this.load_params = {
            "type"   : "karaoke",
            "action" : "get_ordered_list"
        };
        
        this.superclass = ListLayer.prototype;
        
        this.sort_menu = {};
        
        this.search_box = {};
        
        this.load_abc = function(){
            _debug('karaoke.load_abc');
            
            stb.load(
                {
                    "type"   : "karaoke",
                    "action" : "get_abc"
                },
                function(result){
                    _debug('callback abc');
                    
                    this.sidebar.fill_items("abc", result);
                },
                this
            )
        };
        
        this._show = function(){
            
            _debug('karaoke._show');
            
            try{
                this.sort_menu.action();
                
                this.superclass.show.call(this);
                
                this.load_abc();
            }catch(e){
                _debug(e);
            }
        };
        
        this.hide = function(do_not_reset){
            
            _debug('karaoke.hide');
            
            if(!do_not_reset){
                this.search_box && this.search_box.reset && this.search_box.reset();
            }
            
            this.search_box.on && this.search_box.hide && this.search_box.hide();
            this.sort_menu.on && this.sort_menu.hide && this.sort_menu.hide();
            
            this.superclass.hide.call(this, do_not_reset);
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
        
        this.init_search_box = function(options){
            this.search_box = new search_box(this, options);
            this.search_box.init();
            this.search_box.bind();
        };
        
        this.search_box_switcher = function(){
            if (this.search_box && this.search_box.on){
                this.search_box.hide();
            }else{
                this.search_box.show();
            }
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);

            (function(){

                if (single_module.indexOf(this.layer_name) != -1){
                    if (window.self !== window.top) {
                        stb.player.stop();
                        // minimize
                        this.hide();
                        parent.postMessage('hide', '*');
                    } else if (typeof(stbWebWindow) != 'undefined' && windowId !== 1) {
                        stb.player.stop();
                        // minimize
                        this.hide();
                        stbWindowMgr.windowHide(windowId);
                    } else if (window.referrer){
                        stb.player.stop();
                        window.location = window.referrer;
                    }
                    return;
                }

                this.hide();
                main_menu.show();
            }).bind(key.MENU, this).bind(key.EXIT, this).bind(key.LEFT, this);
            
            this.play.bind(key.OK, this);
        };
        
        this.play = function(){
            _debug('karaoke.play');
            
            var self = this;
            
            _debug('cmd', this.data_items[this.cur_row].cmd);
            _debug('indexOf', this.data_items[this.cur_row].cmd.indexOf('://'));
            
            if (this.data_items[this.cur_row].cmd.indexOf('://') < 0){
                stb.player.on_create_link = function(result){
                    _debug('karaoke.on_create_link', result);
                    
                    if (result.error == 'limit'){
                        stb.notice.show(word['player_limit_notice']);
                    }else if(result.error == 'nothing_to_play'){
                        stb.notice.show(word['player_file_missing']);
                    }else if(result.error == 'link_fault'){
                        stb.notice.show(word['player_server_error']);
                    }else{
                        
                        self.hide(true);
                        
                        stb.player.prev_layer = self;
                        stb.player.need_show_info = 1;
                        stb.player.play_now(result.cmd);
                    }
                }
            }else{
                
                this.hide(true);
                
                stb.player.prev_layer = self;
                stb.player.need_show_info = 1;
            }
            
            stb.player.play(this.data_items[this.cur_row]);
        };
    }
    
    karaoke_constructor.prototype = new ListLayer();

    var karaoke = new karaoke_constructor();
    
    karaoke.bind();
    karaoke.init();

    if (single_module.indexOf('karaoke') == -1) {
        karaoke.init_left_ear(word['ears_back']);
    }
    
    karaoke.init_color_buttons([
        {"label" : word['karaoke_view'], "cmd" : (function(){})},
        {"label" : word['karaoke_sort'], "cmd" : karaoke.sort_menu_switcher},
        {"label" : word['karaoke_search'], "cmd" : karaoke.search_box_switcher},
        {"label" : word['karaoke_sampling'], "cmd" : karaoke.sidebar_switcher}
    ]);
    
    //karaoke.color_buttons[karaoke.color_buttons.getIdxByVal('color', 'red')].text_obj.setClass('disable_color_btn_text');
    karaoke.color_buttons.get('red').disable();

    karaoke.init_sidebar();
    
    karaoke.sidebar.init_items("abc", {"header" : word['karaoke_by_letter'], "width" : 26, "align" : "center"});
    
    karaoke.sidebar.bind();
    
    karaoke.init_sort_menu(
        [
            {"label" : word['karaoke_by_performer'], "cmd" : function(){this.parent.load_params.sortby = 'singer'}},
            {"label" : word['karaoke_by_title'], "cmd" : function(){this.parent.load_params.sortby = 'name'}}
        ],
        {
            "offset_x" : 217,
            "color"    : "green"
        }
    );
    
    karaoke.init_search_box(
        {
            "offset_x"  : 323,
            "color"     : "yellow",
            "languages" : get_word('search_box_languages')
        }
    );
    
    karaoke.init_header_path(word['karaoke_title']);
    
    karaoke.sidebar.dependency    = [karaoke.sort_menu, karaoke.search_box];
    karaoke.sort_menu.dependency  = [karaoke.sidebar, karaoke.search_box];
    karaoke.search_box.dependency = [karaoke.sidebar, karaoke.sort_menu];
    
    karaoke.hide();
    
    module.karaoke = karaoke;
    
    /* END KARAOKE */
    
    /* Integrate karaoke in main menu */
    
    main_menu.add(get_word('karaoke_title'), [], 'mm_ico_karaoke.png', function(){
            main_menu.hide();
            module.karaoke._show();
    },
    module.karaoke);
    
})();

loader.next();