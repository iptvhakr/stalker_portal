/**
 * Video Club modile.
 */

//(function(context){
    
    stb.ajax_loader = 'http://bb2.sandbox/stalker_portal/server/load.php'
    
    /* VCLUB */
    function vclub_constructor(){
            
        this.row_blocks  = ['hd', 'sd', 'fav', 'lock', 'name'];
        
        this.load_params = {
            'type'   : 'vod',
            'action' : 'get_ordered_list'
        };
        
        this.superclass = Layer.prototype;
        
        this.category_alias = '';
        
        this.sort_menu = {};
        
        this.search_box = {};
        
        this.load_genres = function(alias){
            
            alias = alias || '';
            
            _debug('vclub.load_genres', alias);
        
            stb.load(
                {
                    "type"      : "vod",
                    "action"    : "get_genres_by_category_alias",
                    "cat_alias" : alias
                },
                function(result){
                    _debug('callback categories');
    
                    this.sidebar.fill_items("genre", result);
                },
                this
            )
        };
        
        this.load_years = function(category_id){
            _debug('vclub.load_years');
            
            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "get_years",
                    "category" : category_id
                },
                function(result){
                    _debug('callback years');
    
                    this.sidebar.fill_items("years", result);
                },
                this
            )
        };
        
        this.load_abc = function(){
            _debug('vclub.load_abc');
            
            stb.load(
                {
                    "type"   : "vod",
                    "action" : "get_abc"
                },
                function(result){
                    _debug('callback abc');
                    
                    this.sidebar.fill_items("abc", result);
                },
                this
            )
        };
        
        this.show = function(category){
            
            _debug('vclub.show');
            
            this.load_params['category'] = category.id;
            
            try{
                this.sort_menu.action();
                
                this.superclass.show.apply(this);
                
                this.load_abc();
                this.load_genres(category.alias);
                this.load_years(category.id);
            }catch(e){
                _debug(e);
            }
        };
        
        this.hide = function(){
            
            _debug('vclub.hide');
            
            this.search_box && this.search_box.reset && this.search_box.reset();
            
            this.superclass.hide.apply(this);
            
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
    }
    
    vclub_constructor.prototype = new Layer();
    
    var vclub = new vclub_constructor();
    
    vclub.bind();
    vclub.init();
    
    vclub.init_left_ear('ears_back');
    vclub.init_right_ear('ears_movie');
    
    vclub.init_color_buttons([
        {"label" : "ОТОБРАЖЕНИЕ", "cmd" : ""},
        {"label" : "СОРТИРОВКА", "cmd" : vclub.sort_menu_switcher},
        {"label" : "ПОИСК", "cmd" : vclub.search_box_switcher},
        {"label" : "ВЫБОРКА", "cmd" : vclub.sidebar_switcher}
    ]);
    
    vclub.init_sidebar();
    
    vclub.sidebar.init_items("abc", {"header" : "ПО БУКВЕ", "width" : 26, "align" : "center"});
    vclub.sidebar.init_items("genre",  {"header" : "ПО ЖАНРУ", "width" : 95});
    vclub.sidebar.init_items("years",  {"header" : "ПО ГОДУ", "width" : 35});
    
    vclub.sidebar.bind();
    
    vclub.init_sort_menu(
        [
            {"label" : "по имени", "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'name'}},
            {"label" : "по добавлению", "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'added'}},
            {"label" : "по популярности", "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'top'}},
            {"label" : "только избранное", "cmd" : function(){this.parent.load_params.sortby = 'name'; this.parent.load_params.fav = true}}
        ],
        {
            "offset_x" : 217
        }
    );
    
    vclub.init_search_box(
        {
            "offset_x" : 323
        }
    );
   
    vclub.init_header_path('ВИДЕО КЛУБ');
    
    vclub.sidebar.dependency = [vclub.sort_menu, vclub.search_box];
    vclub.sort_menu.dependency = [vclub.sidebar, vclub.search_box];
    vclub.search_box.dependency = [vclub.sidebar, vclub.sort_menu];
    
    vclub.hide();
    
    module.vclub = vclub;
    /* END VCLUB */
    
    /* Integrate vclub in main menu */
    stb.load(
        {
            "type"   : "vod",
            "action" : "get_categories"
        },
        function(result){
            _debug('callback categories');
            
            var categories = result;
            
            var map = [];
    
            for(var i=0; i<categories.length; i++){
                map.push(
                
                {
                    "title" : categories[i].title,
                    "cmd"   : (function(category){
                        
                        
                        return function(){
                            _debug('alias', category.alias);
                        
                            main_menu.hide();
                            module.vclub.show(category);
                        }
                        
                    })(categories[i]),
                }
                
                );
            }
            
            main_menu.add('ВИДЕОКЛУБ', map);
        },
        this
    )
    
//})()