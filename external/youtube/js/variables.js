var  version='1.0.3',
    pages={"back":"file:///home/web/services.html"},
    def = {"lang":"en"},
    current = {
        "mode": {
            "debug":false,                   // create a _log and insert into #debug div
            "emulate":false,                // set "true" if you wanna test version in browser
            "show_all_keydowns":false
        },
        "lang":"en",                       // system language
        "priority":"middle",                // current video quality
        "layer":0,                          // current layer (used in keyPressProcessing(key) (common.js) for navigate)
        "globalObj":new Object(),           //
        "page":1,                           // current "page" (current list of youtube moves)
        "obj":0,                            // current object in layer 0 (youtube moves list)
        "feed":"",                  //
        "buttonsStatus":true,
        "catItems":7,
        "cat": {
            "trying":1,
            "id":1,
            "url": ""
        },
        "as_cat": {
            "trying":1,
            "id":1,
            "url": ""
        },
        "playMode":"single",
        "loading":true,
        "searchItem":null,
        "isPL":false
    },
    items = {
        "atLine": 6,                        // number items at line (youtube moves view)
        "atPage": 12                         // number items at layer in one time
    },
    layers = {
        "BASE":0,
        "PLAYER":1,
        "SEARCH":2,
        "CATEGORY":3,
        "SETTINGS":4,
        "AS_CATEGORY":5,
        "VIDEO_LIKE":6
    },
    request = {
        "itemsPerRequest":24,               // number request moves from yputube at one time
        "startIndex":1,                     //
        "totalItems":0                      //
    },
    data = new Object(),                    // object data (response from youtube)
    google = {
        "ratingsFeedUrl": 'http://gdata.youtube.com/feeds/api/standardfeeds/',      // google feed of ratings
        "searchFeedUrl": 'http://gdata.youtube.com/feeds/api/videos?v=2&alt=jsonc&safesearch=strict',  // google feed for search // &lr=ru
        "playlists_search": 'http://gdata.youtube.com/feeds/api/playlists/snippets?v=2&alt=jsonc',
        "playlists_show_pre": 'http://gdata.youtube.com/feeds/api/playlists/',
        "playlists_show_post":'?v=2&alt=jsonc'
    },
    prioritets = {
        "low":   [36,18,34,35,22,37,5],     // youtube formats
        "middle":[18,36,35,34,22,37,5],
        "high":  [37,22,18,36,35,34,5]
    },
    images = new Array(),
    stbEvent = {
        onEvent:player.playerEvent,
        event: 0
    },
    obj = new Object(),
    aspects = [
        {
            "name":"fit",
            "img":null,
            "mode":0x10
        },
        {
            "name":"big",
            "img":null,
            "mode":0x40
        },
        {
            "name":"opt",
            "img":null,
            "mode":0x50
        },
        {
            "name":"exp",
            "img":null,
            "mode":0x00
        }
    ],
    aspect_current = 0,
    playModes = [{"name":"single"},{"name":"repeat"},{"name":"list"},{"name":"random"}],
    keys = {
        "POWER" : 2085,
        "MUTE"  : 2192,
        "MIC"   : 2032,
        "NUM1"  : 49,
        "NUM2"  : 50,
        "NUM3"  : 51,
        "NUM4"  : 52,
        "NUM5"  : 53,
        "NUM6"  : 54,
        "NUM7"  : 55,
        "NUM8"  : 56,
        "NUM9"  : 57,
        "NUM0"  : 48,
        "MENU"  : 122,
        "BACK"  : 8,
        "CHANNEL_PREV"  : 1009,
        "CHANNEL_NEXT"  : 9,
        "EXIT"  : 27,
        "REFRESH"  : 116,
        "UP"    : 38,
        "DOWN"  : 40,
        "LEFT"  : 37,
        "RIGHT" : 39,
        "OK"    : 13,
        "PAGE_NEXT" : 34,
        "PAGE_PREV" : 33,
        "VOL_UP"    : 107,
        "VOL_DOWN"  : 109,
        "RED"    : 112,
        "GREEN"  : 113,
        "YELLOW" : 114,
        "BLUE"   : 115,
        "SERVICES"  : 120,
        "TV"    : 121,
        "PHONE" : 119,
        "WEB"   : 123,
        "REW"   : 2066,
        "FFWD"  : 2070,
        "PLAY"  : 2082,
        "PAUSE" : 2082,
        "CONTINUE" : 2082,
        "STOP"  : 2083,
        "REC"   : 2087,
        "INFO"  : 2089,
        "FRAME" : 117,
        "APP"  : 2076,
        "USB_UNMOUNTED" : 2081,
        "USB_MOUNTED"   : 2080
    },
    categorias=[
        {"name":"most_viewed","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_viewed?v=2&alt=jsonc&time=today"},
        {"name":"most_popular","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular?v=2&alt=jsonc&time=today"},
        {"name":"top_favorites","url":"http://gdata.youtube.com/feeds/api/standardfeeds/top_favorites?v=2&alt=jsonc&time=today"},
        {"name":"top_rated","url":"http://gdata.youtube.com/feeds/api/standardfeeds/top_rated?v=2&alt=jsonc&time=today"},
        {"name":"most_discussed","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_discussed?v=2&alt=jsonc&time=today"},
        {"name":"autos","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Autos?v=2&alt=jsonc&time=today"},
        {"name":"animals","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Animals?v=2&alt=jsonc&time=today"},
        {"name":"film","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Film?v=2&alt=jsonc&time=today"},
        {"name":"entertainment","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Entertainment?v=2&alt=jsonc&time=today"},
        {"name":"gaming","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Games?v=2&alt=jsonc&time=today"},
        {"name":"people","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_People?v=2&alt=jsonc&time=today"},
        {"name":"music","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Music?v=2&alt=jsonc&time=today"},
        {"name":"technology","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Tech?v=2&alt=jsonc&time=today"},
        {"name":"news","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_News?v=2&alt=jsonc&time=today"},
        {"name":"education","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Education?v=2&alt=jsonc&time=today"},
        {"name":"howto","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Howto?v=2&alt=jsonc&time=today"},
        {"name":"activism","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Nonprofit?v=2&alt=jsonc&time=today"},
        {"name":"travel","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Travel?v=2&alt=jsonc&time=today"},
        {"name":"sports","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Sports?v=2&alt=jsonc&time=today"},
        {"name":"comedy","url":"http://gdata.youtube.com/feeds/api/standardfeeds/most_popular_Comedy?v=2&alt=jsonc&time=today"}
    ],
    adv_search={
        "categorys":[
            {"name":"all","url":""},
            {"name":"autos","url":"&category=autos"},
            {"name":"comedy","url":"&category=Comedy"},
            {"name":"education","url":"&category=Education"},
            {"name":"entertainment","url":"&category=Entertainment"},
            {"name":"film","url":"&category=Film"},
            {"name":"gaming","url":"&category=Games"},
            {"name":"howto","url":"&category=Howto"},
            {"name":"music","url":"&category=Music"},
            {"name":"nonprofit","url":"&category=Nonprofit"},
            {"name":"people","url":"&category=People"},
            {"name":"animals","url":"&category=Animals"},
            {"name":"news","url":"&category=News"},
            {"name":"technology","url":"&category=Tech"},
            {"name":"sports","url":"&category=Sports"},
            {"name":"travel","url":"&category=Travel"}
        ],
        "time":[
            {"name":"allTime","url":"&time=all_time"},
            {"name":"today","url":"&time=today"},
            {"name":"thisWeek","url":"&time=this_week"},
            {"name":"thisMonth","url":"&time=this_month"}
        ],
        "orderBy":[
            {"name":"relevance","url":"&orderby=relevance"},
            {"name":"published","url":"&orderby=published"},
            {"name":"rating","url":"&orderby=rating"},
            {"name":"viewCount","url":"&orderby=viewCount"}
        ],
        "userType":[
            {"name":"uploads","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/uploads?v=2&alt=jsonc"},
            {"name":"favorites","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/favorites?v=2&alt=jsonc"},
            {"name":"playlists","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/playlists?v=2&alt=jsonc"}
        ]
    },
    v_like = [
        {"name":"suggestion","url_pre":"http://gdata.youtube.com/feeds/api/videos/","url_post":"/related?v=2&alt=jsonc&safesearch=strict&orderby=relevance"},
        {"name":"favorites","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/favorites?v=2&alt=jsonc"},
        {"name":"uploads","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/uploads?v=2&alt=jsonc"},
        {"name":"playlists","url_pre":"http://gdata.youtube.com/feeds/api/users/","url_post":"/playlists?v=2&alt=jsonc"}
    ],
    _GET=new Array(),
    proxy_string='',
    proxy_enable=false;
