/**
 * Video Club modile.
 */

(function(context){

    var genres = [{"id" : 1, "title" : "ИНФОРМАЦИОННЫЙ"},
            {"id" : 2, "title" : "РАЗВЛЕЧЕНИЯ"},
            {"id" : 3, "title" : "ДЕТСКИЙ"},
            {"id" : 4, "title" : "КИНО"},
            {"id" : 5, "title" : "НАУКА"},
            {"id" : 6, "title" : "СПОРТ"},
            {"id" : 7, "title" : "МУЗЫКА"},
            {"id" : 8, "title" : "БИЗНЕС"},
            {"id" : 9, "title" : "КУЛЬТУРА"},
            {"id" : 10, "title" : "НАШЕ"},
            {"id" : 11, "title" : "ЗАРУБЕЖНОЕ"},
            {"id" : 12, "title" : "*"}]
    
    var vclub_constructor = function(){
        
        this.row_blocks  = ['hd', 'sd', 'fav', 'lock', 'name'];
        
        this.load_params = {
            'type'   : 'vod',
            'action' : 'get_by_name'
        }
    }
    
    stb.ajax_loader = 'http://bb2.sandbox/stalker_portal/server/load.php'
    
    vclub_constructor.prototype = new Layer();
    
    var vclub = new vclub_constructor();
    vclub.bind();
    
    vclub.init();
    stb.key_lock = false;
    
    vclub.on = true;

    var color_buttons = [
        {"label" : "ОТОБРАЖЕНИЕ", "cmd" : ""},
        {"label" : "СОРТИРОВКА", "cmd" : ""},
        {"label" : "ПОИСК", "cmd" : ""},
        {"label" : "ДОПОЛНИТЕЛЬНО", "cmd" : vclub.sidebar_switcher}
    ];
    
    vclub.init_color_buttons(color_buttons);
    vclub.init_left_ear('ears_back');
    vclub.init_right_ear('ears_movie');
    
    vclub.init_header_path('ВИДЕО КЛУБ');
    
    vclub.init_sidebar();
    vclub.sidebar.init_items("genre", genres, {"header" : "ПО ИМЕНИ"});
    vclub.sidebar.bind();
    vclub.hide();
    
    module.vclub = vclub;
    
    main_menu.add('ВИДЕОКЛУБ', 
        [
            {
            "title" : "ВСЕ",
            "cmd"   : function(){main_menu.hide(); module.vclub.show()}
            },
            {
            "title" : "ДОКУМЕНТАЛЬНЫЙ",
            "cmd"   : function(){main_menu.hide(); module.vclub.show()}
            }
        ]
    );
    
})()