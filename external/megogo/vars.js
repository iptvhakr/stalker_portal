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

var debug = false,
	sort ='add',
	genreList =[],
	back_location = decodeURIComponent(window.location.search.match(/\?referrer\=.*/)),
	flFavorUpdate=0,
	flSubCatTurn =0,
	runFl = true,
	playlist = [],
	playTime = 0,
	currLst = catLst,
	prevLst = catLst,
	childFree = 0,
	favorites = [],
	megogoURL =  'http://megogo.net/',
    iviURL =  'http://megogo.net/p/',
    proxyURL = 'http://alu5.rl3u.owl.e.s3.hideme.ru/mobileapi/videofullinfo/?id=',
   win = {"width":screen.width,"height":screen.height},
  /* win = {"width":720,"height":576},*/
            /*win = {"width":1080,"height":720},*/

    layers = ['login_page','cats_page','subcats_page','search_page','content_page','player_page','genre_page','info_page'/*, 'menu_series'*/],
    layer_auth = 0,
    layer_cats = 1,
    layer_subcats = 2,
    layer_search = 3,
    layer_content = 4,
    layer_player = 5,
    layer_genre = 6,
    layer_info = 7,
   /* layer_series = 8,*/
    PREV_LAYER = 0,
    CUR_LAYER = 1,

    sub_layers = ['pop_layer','video_layer'],
    sub_layer_pop = 0,
    sub_layer_video = 1,
    SUB_CUR_LAYER = 1,
    left_side = true,
    alt_left_side = true,
    pop_layer = true,
    layer_indexes = {
        "active":[
            {'login_form':0},0,0,0,0,0,0
        ],
        "array":[
            {'login_form':['login','password','submit','login_ok','login_cancel']},
            [],
            [],
            {'search_form':['search_line','search_cats','search_country','search_ok','search_cancel']},
            [],
            [],
            [{'genre_len':0}]
        ]
    },

    autologin = false,
    promo_obj = [],
    cats_obj = [],
    dataset = [],
    seasons,
    search_obj = [],
    subcatsArr = [],
    newAlert_on = false,
    //catsSelector = {"step":[0,0,234,0],position:[0,0,166,0]},
    suggest_active = false,
    suggest_count = 0,
    suggest_focus = -1,

    vars={
        "catSel":0,
        "curCatSel":0,
        "catsArr":[
                    {"id":"cat_0","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_1","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_2","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_3","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_4","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_5","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"},
                    {"id":"cat_6","normal":"menu","focus":"menu_focus","click":"menu_act","func":"req"}],
        "catID":[0,16,4,6,9, -1/*,17*/],
        "cats":[
            [{"id":null,"title":''},{"id":null,"title":''},{"year_from":null,"year_to":null}],
            [{"id":null,"title":''},{"id":null,"title":''},{"year_from":null,"year_to":null}],
            [{"id":null,"title":''},{"id":null,"title":''},{"year_from":null,"year_to":null}],
            [{"id":null,"title":''},{"id":null,"title":''},{"year_from":null,"year_to":null}]]
        ,
        "promo_page":0,
        "subcats_page":0,
        "subcats_item":0,
        "cont_page_x":0,
        "cont_page_y":0,
        "content_page":0,
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
        "player_shown":0,
        "file_curtime":0,
        "file_lenght":0,
        "file_percent":0,


        "480":{
            "subcats_onpage":11,
            "cont_page_x_max":0,
            "cont_page_y_max":0,
            "stripe_len":1120,
        },
        "576":{
            "subcats_onpage":11,
            "cont_page_x_max":1,
            "cont_page_y_max":3,
            "ext_cont_page_x_max":3,
            "poster_width":'100px',
            "poster_height":'150px',
            "infoposter_width":'140px',
            "infoposter_height":'220px',
            "promo_width":'',
            "promo_height":'',
            "stripe_len":560,
            "seriesLen":7,
            "seasonTextLen":11,
            "episodeTexLen":11
        },
        "720":{
            "subcats_onpage":11,
            "cont_page_x_max":1,
            "cont_page_y_max":3,
             "ext_cont_page_x_max":5,
            "poster_width":'110px',
            "poster_height":'160px',
            "infoposter_width":'280px',
            "infoposter_height":'315px',
            "promo_width":'',
            "promo_height":'',
            "stripe_len":1120,
            "seriesLen":9,
             "seasonTextLen":11,
            "episodeTexLen":11
        },
        "1080":{
            "subcats_onpage":11,
            "cont_page_x_max":1,
            "cont_page_y_max":3,
             "ext_cont_page_x_max":5,
            "poster_width":'165px',
            "poster_height":'240px',
            "infoposter_width":'300',
            "infoposter_height":'422',
            "promo_width":'',
            "promo_height":'',
            "stripe_len":1760,
            "seriesLen":9,
            "seasonTextLen":11,
            "episodeTexLen":11
        },


        "menu_items":[
        	"blue",
			"violet",
			"green",
			"orange",
			"red",
			"blue",
			"separator",
			"special",
			"settings"
        ]
    };

    //Таймера
    var runner_timer = null,
        pos_timer = null,
        setpos_timer = null,
        set_volume = null;

    var genres = {
            94:"боевик",
            95:"комедия",

    }

var countries ={};
