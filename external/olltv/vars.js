var _GET = {};

(function(){
    var get = new String(window.location);
    var x = get.indexOf('?');
    if (x!=-1){
        var l = get.length;
        get = get.substr(x+1, l-x);
        l = get.split('&');
        x = 0;
        for(var i in l){
            if (l.hasOwnProperty(i)){
                get = l[i].split('=');
                _GET[get[0]] = get[1];
                x++;
            }
        }
    }
})();

if (_GET.hasOwnProperty('referrer')){
    _GET['referrer'] = decodeURIComponent(_GET['referrer']);
}

var layers = [{"name":"main_page","child":[]},{"name":"info_page","child":[]},{"name":"player_page","child":[]},{"name":"search_page","child":[]},{"name":"login","child":['login']}],
    debug = false,
    stb = gSTB,
    olltvURL = 'http://oll.tv/smartAPI/',
    CUR_LAYER = 0,
    main_layer = 0,
    info_layer = 1,
    player_layer = 2,
    search_layer = 3,
    alert_layer = 4,
    PREV_LAYER = 0,
    CUR_SUBLAYER = 0,
    sessionID = '',
    unscroled_area = true,
    prev_class = '',
    win = {
        "width":screen.width,
        "height":screen.height
    },
    history_obj=[
//        {
//            "html":'',
//            "focus_line":0,
//            "focus_item":0
//        },
    ],
    keyblock = false,
    lines_arr=[],
    postime='',
    suggest_obj = {},
    vars ={
        "sn":0,
        "lang":'ru',
        "device":'',
        "480":{
            "img_width":'101px',
            "img_height":'136px',
            "maxitems_visible":5,
            "maxitems_load":12,
            "catSel_pos_px":188,
            "seasonSel_pos_px":250,
            "seriesSel_pos_px":250,
            "catSel_step_px":134,
            "seriesSel_step_px":90,
            "stripe_len":560,
            "item_step":107
        },
        "576":{
            "img_width":'101px',
            "img_height":'136px',
            "maxitems_visible":5,
            "maxitems_load":12,
            "catSel_pos_px":188,
            "seasonSel_pos_px":250,
            "seriesSel_pos_px":250,
            "catSel_step_px":134,
            "seriesSel_step_px":90,
            "stripe_len":560,
            "item_step":107
        },
        "720":{
            "img_width":'152px',
            "img_height":'204px',
            "maxitems_visible":6,
            "maxitems_load":12,
            "catSel_pos_px":179,
            "seasonSel_pos_px":500,
            "seriesSel_pos_px":500,
            "catSel_step_px":174,
            "seriesSel_step_px":124,
            "stripe_len":1120,
            "item_step":168
        },
        "1080":{
            "img_width":'182px',
            "img_height":'244px',
            "maxitems_visible":7,
            "maxitems_load":12,
            "catSel_pos_px":140,
            "seasonSel_pos_px":750,
            "seriesSel_pos_px":750,
            "catSel_step_px":245,
            "seriesSel_step_px":184,
            "stripe_len":1760,
            "item_step":198
        },
        "promo_page":0,
        "need_reload":false,
        "info_focus_line":0,
        //"real_info_nav":[],
        "info_focus_obj":[0,0,0,0],
        "info_focus_btn":[0,0,0,0],
        "info_sel_obj":[0,0,0,0],
        "active_audiolang":0,
        "active_sublang":0,
        "select_audiolang":0,
        "select_sublang":0,
        "focus_line":0,
        "focus_cat":0,
        "focus_item":2,
        "linesOnPage":2,
        "searchline":0,
        "searchObj":0,
        "searchFocus":0,
        "alert_focus":0,
        "alert_arr":1,
        "curPage":0,
        "player_vars":{
            "volume":0,
            "mute":0,
            "playback":0,
            "playlist":0,
            "roller_step":0,
            "isplaying":0,
            "pause":false,
            "aspect_current":0,
            "aspects":[
                {
                    "name":"fit",
                    "img":'img/aspect_fit.png',
                    "mode":0x10
                },
                {
                    "name":"big",
                    "img":'img/aspect_big.png',
                    "mode":0x40
                },
                {
                    "name":"opt",
                    "img":'img/aspect_opt.png',
                    "mode":0x50
                },
                {
                    "name":"exp",
                    "img":'img/aspect_exp.png',
                    "mode":0x00
                }
            ],
        },
        "player_shown":false,
        "file_curtime":0,
        "file_lenght":0,
        "file_percent":0,
        
    };
    keybord_layout=0;
    var keybord =  [[{"text":"12#","clas":"keypad_key_add keypad_empty"},{"text":"а","clas":"keypad_key"},{"text":"б","clas":"keypad_key"},{"text":"в","clas":"keypad_key"},{"text":"г","clas":"keypad_key"},{"text":"д","clas":"keypad_key"},{"text":"RU","clas":"keypad_key_add keypad_empty"},{"text":"е","clas":"keypad_key"},{"text":"ё","clas":"keypad_key"},{"text":"ж","clas":"keypad_key"},{"text":"з","clas":"keypad_key"},{"text":"и","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_space.png' />","clas":"keypad_key_add keypad_empty"},{"text":"й","clas":"keypad_key"},{"text":"к","clas":"keypad_key"},{"text":"л","clas":"keypad_key"},{"text":"м","clas":"keypad_key"},{"text":"н","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_del.png' />","clas":"keypad_key_add keypad_empty"},{"text":"о","clas":"keypad_key"},{"text":"п","clas":"keypad_key"},{"text":"р","clas":"keypad_key"},{"text":"с","clas":"keypad_key"},{"text":"т","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":"у","clas":"keypad_key"},{"text":"ф","clas":"keypad_key"},{"text":"х","clas":"keypad_key"},{"text":"ц","clas":"keypad_key"},{"text":"ч","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":"ш","clas":"keypad_key"},{"text":"щ","clas":"keypad_key"},{"text":"ъ","clas":"keypad_key"},{"text":"ы","clas":"keypad_key"},{"text":"ь","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":"э","clas":"keypad_key"},{"text":"ю","clas":"keypad_key"},{"text":"я","clas":"keypad_key"}],
                    [{"text":"12#","clas":"keypad_key_add keypad_empty"},{"text":"a","clas":"keypad_key"},{"text":"b","clas":"keypad_key"},{"text":"c","clas":"keypad_key"},{"text":"d","clas":"keypad_key"},{"text":"e","clas":"keypad_key"},{"text":"EN","clas":"keypad_key_add keypad_empty"},{"text":"f","clas":"keypad_key"},{"text":"g","clas":"keypad_key"},{"text":"h","clas":"keypad_key"},{"text":"i","clas":"keypad_key"},{"text":"j","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_space.png' />","clas":"keypad_key_add keypad_empty"},{"text":"k","clas":"keypad_key"},{"text":"l","clas":"keypad_key"},{"text":"m","clas":"keypad_key"},{"text":"n","clas":"keypad_key"},{"text":"o","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_del.png' />","clas":"keypad_key_add keypad_empty"},{"text":"p","clas":"keypad_key"},{"text":"q","clas":"keypad_key"},{"text":"r","clas":"keypad_key"},{"text":"s","clas":"keypad_key"},{"text":"t","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":"u","clas":"keypad_key"},{"text":"v","clas":"keypad_key"},{"text":"w","clas":"keypad_key"},{"text":"x","clas":"keypad_key"},{"text":"y","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":"z","clas":"keypad_key"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"}],
                    [{"text":"абв","clas":"keypad_key_add keypad_empty"},{"text":"0","clas":"keypad_key"},{"text":"1","clas":"keypad_key"},{"text":"2","clas":"keypad_key"},{"text":"3","clas":"keypad_key"},{"text":"4","clas":"keypad_key"},{"text":"EN","clas":"keypad_key_add keypad_empty"},{"text":"5","clas":"keypad_key"},{"text":"6","clas":"keypad_key"},{"text":"7","clas":"keypad_key"},{"text":"8","clas":"keypad_key"},{"text":"9","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_space.png' />","clas":"keypad_key_add keypad_empty"},{"text":"@","clas":"keypad_key"},{"text":"#","clas":"keypad_key"},{"text":"\"","clas":"keypad_key"},{"text":"*","clas":"keypad_key"},{"text":"!","clas":"keypad_key"},{"text":"<img src='img/"+screen.height+"/keypad_del.png' />","clas":"keypad_key_add keypad_empty"},{"text":"(","clas":"keypad_key"},{"text":")","clas":"keypad_key"},{"text":"?","clas":"keypad_key"},{"text":",","clas":"keypad_key"},{"text":":","clas":"keypad_key"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":";","clas":"keypad_key"},{"text":"-","clas":"keypad_key"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":"","clas":"keypad_key_null keypad_empty"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"},{"text":" ","clas":"keypad_key_null"}]]
    var runner_timer = null,
        pos_timer = null,
        setpos_timer = false,
        set_volume = null,
        aspectTimer = null;
    