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
        
        this.superclass = Layer.prototype;
        
        this.sort_menu = {};
        
        this.view_menu = {};
        
        this.row_callback_timer;
        this.row_callback_timeout = 1000;
        
        this._show = function(genre){
            
            _debug('tv._show');
            
            this.load_params['genre'] = genre.id;
            
            this.superclass.show.call(this);
        }
        
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
            
            stb.player.play(this.data_items[this.cur_row]);
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
    }
    
    tv_constructor.prototype = new Layer();
    
    var tv = new tv_constructor();
    
    tv.bind();
    tv.init();
    
    tv.set_wide_container();
    
    tv.init_left_ear('ears_back');
    
    tv.init_color_buttons([
        {"label" : "ОТОБРАЖЕНИЕ", "cmd" : tv.view_switcher},
        {"label" : "СОРТИРОВКА", "cmd" : tv.sort_menu_switcher},
        {"label" : "ИЗБРАННОЕ", "cmd" : tv.manage_fav},
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
            {"label" : "список с инфо", "cmd" : function(){this.parent.set_middle_container()}}
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