var modes= {
        "debug":false,
        "emulate":false
    },
    stb,
    _GET={},
    win={"width":screen.width,"height":screen.height},
    as = {
		"proxy":"",
		"referer":"",
        "subcount":0,//������� ��������� �� ������
        "sublist":false,
        "cur_lang":"ru",
        "list":"list",
        "navig":"navigation",
        "default_lang":"ru",
        "actualSize":576,
        "sizes":{
            480:{
                "act_line_margin_top_first":5,
            	"act_line_margin_top":30,
            	"max_list_lines":10,
                "max_list_lines_s":7,
                "max_list_lines_l":10,
            	"num_line_for_rotate_suggest":5,
            	"player_scrolbar_width":395
            },
            576:{
            	"act_line_margin_top_first":5,
            	"act_line_margin_top":30,
            	"max_list_lines":13,
                "max_list_lines_s":10,
                "max_list_lines_l":13,
            	"num_line_for_rotate_suggest":7,
            	"player_scrolbar_width":395
            },
            720:{
                "act_line_margin_top_first":-12,
            	"act_line_margin_top":38,
            	"max_list_lines":13,
                "max_list_lines_s":10,
                "max_list_lines_l":13,
            	"num_line_for_rotate_suggest":7,
            	"player_scrolbar_width":395
            },
            
            1080:{
                "act_line_margin_top_first":-75,
            	"act_line_margin_top":56,
            	"max_list_lines":13,
                "max_list_lines_s":10,
                "max_list_lines_l":13,
            	"num_line_for_rotate_suggest":6,
            	"player_scrolbar_width":395
            }
        },
        "dvice_model":"MAG200",
        "layer":0,
        "layers":{
        	"HOME":0,
            "CATS_LIST":1,
            "SUB_CATS_LIST":2,
            "PLAYER":3,
            "LOADING":4,
            "SEARCH":5/*,
            "SEARCH_LINE":6*/
        },
        "layers_pages":{
        	0:"page_home",
        	1:"page_main",
        	2:"page_main_s",
        	3:"exPlayer",
        	4:"page_loading",
                5:"page_searchLine"
        },
        //http://www.ex.ua/language?lang=ru
        //"cur_act_list_position":0,
        "cur_home_position":"button_video",
        "branch":"",
        "feeds":{
            "video":"http://www.ex.ua/ru/video?per=100",
            "audio":"http://www.ex.ua/ru/audio?per=100",
            "images":"http://www.ex.ua/ru/images?per=100",
            "search":"http://www.ex.ua/search?per=100&s="
        }        
    },
    info_menu={
        "audio":0,
        "subt":0,
        "on":false
    },
    volume = 100,
    mute = 0,
    source_html,
    paths={'js':'js/','css':'css/'},    
    prop_focus_emements=[];
    var branch = '',
    perm_player_show = 0,
    playlist_len = 0,
    cur_aspect = 0,
    aspect_array = [
        {img : 'aspect_fit', mode : 0x10},
        {img : 'aspect_big', mode : 0x40},
        {img : 'aspect_opt', mode : 0x50},
        {img : 'aspect_exp', mode : 0x00}],//['00010000','01000000','01010000','00000000'],
    active_suggest = -1,
    suggests = 0,
    proxy_string='',
    proxy_enable=false,
    keys={"POWER":2085,"MUTE":2192,"MIC":2032,"NUM1":49,"NUM2":50,"NUM3":51,"NUM4":52,"NUM5":53,"NUM6":54,"NUM7":55,"NUM8":56,"NUM9":57,"NUM0":48,"MENU":122,"BACK":8,"CHANNEL_PREV":1009,"CHANNEL_NEXT":9,"EXIT":27,"REFRESH":116,"UP":38,"DOWN":40,"LEFT":37,"RIGHT":39,"OK":13,"PAGE_NEXT":34,"PAGE_PREV":33,"VOL_UP":107,"VOL_DOWN":109,"RED":112,"GREEN":113,"YELLOW":114,"BLUE":115,"SERVICES":120,"TV":121,"PHONE":119,"WEB":123,"REW":2066,"FFWD":2070,"PLAY":2082,"PAUSE":2082,"CONTINUE":2082,"STOP":2083,"REC":2087,"INFO":2089,"FRAME":117,"APP":2076,"USB_UNMOUNTED":2081,"USB_MOUNTED":2080};