
var lang = {
    'loading':'Loading...',
    'cat_audio':'Audio',
    'cat_music':'Audio',
    'cat_video':'Video',
    'cat_images':'Pictures',
    'cat_search':'Search',
    "Jan":"January",
    "Feb":"February",
    "Ma":"March",
    "Apr":"April",
    "May":"May",
    "Jn":"June",
    "Jl":"July",
    "Ag":"August",
    "Sep":"September",
    "Oct":"October",
    'Now':'November',
    "Dec": "December",
    "add":"Posted:",
    "No play":"No playable content here",
    "NotF":'Nothing was found',
    'description':'Title: ',
    'add_time':'Posted: ',
    'list_default_hint':'Find at current category',
    'back_action':'Return',
    'refresh':'Refresh',
    'search_is_over':'Search results',
    'loading_is_over':'Loading complete',
    'no_files':'No playable content was found',
    'lang_default_hint':'Find at EX.UA',
    'cats':'Categories:',
    'news':'New:',
    'news_brcr':'New',
    'about_actor':'About actor',
    'madia_menu_info_back_act':'Return to the previous level.',
    'exit':'Exit',
    'close_question':'Close this program?',
    'cancel':'Cancel',
    'close':'Yes',
    'mark':'Select',
    'unmark':'Unselect',
    'film_description':'Description',
    'size_in_bytes':'Размер: ',
    'film_description_off':'Close description',
    'main_menu':'Main menu'
};

var LANG_MEDIA_DEFAULT_ABORT         = 'Cancel',
    LANG_MEDIA_DEFAULT_CLOSE         = 'Close',
    LANG_MEDIA_DEFAULT_ERROR         = 'Error',
    LANG_MEDIA_DEFAULT_OK            = 'Yes',
    LANG_MEDIA_ITEM_WRONG_TYPE       = 'No playable content here.',
    //Media Player
    playerBtnInfo                    = 'Hide<br />player',
    playerBtnF2sh                    = 'Show<br />playlist',
    playerBtnF2hd                    = 'Hide<br />playlist',
    playerBtnMenu                    = 'Settings',
    playlistPrev                     = "Previous<br />media",
    playlistNext                     = "Next<br />media",
     mediaInfoMenu_3dview            = 'Mode',
    playingError                     = 'play error',
    mediaInfoMenu_langUnknown        = 'unknown',
    // info
    LANG_MEDIA_MENU_INFO_DURATION    = 'Duration: ',
    LANG_MEDIA_MENU_NO               = 'no',
    time_seconds                     = 'Sek.',
    LANG_MEDIA_MENU_OFF              = 'off'
    ;
    

    
var LANG_MEDIA_MENU_GROUPS = {
    gview     : 'View',
    gedit     : 'Edit',
    gsort     : 'Sort',
    gtools    : 'Tools',
    gins      : 'Selected',
    gaudio    : 'Sound',
    gsubtitle : 'Subtitles',
    gts       : 'TimeShift',
    gtsend    : 'TimeShift buffer',
    g3d       : '3D mode',
    gslideOn  : 'Slide show'
};
    




//------------------------------------------------------------------------------
var type_info = [];
    type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80925,81708' };
    type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80926,81709' };
    type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80927,81710' };
    type_info[MEDIA_OBJECT]           = {icon:'ico_folder_files.png'};
    type_info[MEDIA_FOLDER]           = {icon:'media_ico_fld.png'};
    type_info[ROOT_GLOBAL_SEARCH]     = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?s='};
    type_info[GLOBAL_SEARCH_FOLDER]   = {icon:'media_ico_fld.png'};
    type_info[LOCAL_SEARCH_ROOT]      = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?original_id=',url_end:'&s='};
    type_info[GLOBAL_SEARCH_REFRESH]  = {icon:'ico_search3.png'};
    type_info[LOCAL_SEARCH_REFRESH]   = {icon:'ico_search3.png'};
    type_info[RADIO_OBJECT]           = {icon:'media_ico_str.png', url_start:'http://radio.ex.ua:8000/', id:'6996870'};
    type_info[ACTORS_FOLDER]          = {icon:'media_ico_fld.png', id:'artist'};
    type_info[WHAT_IS_THIS_TYPE]      = {icon:'media_ico_fld.png'};
    type_info[ORPHAN_FOLDER]          = {icon:'media_ico_fld.png'};
    type_info[ROOT_NEWS]              = {icon:'media_ico_fld.png'};
    type_info[MEDIA_TYPE_VIDEO]       = {icon:'media_ico_vid.png'};
    type_info[MEDIA_TYPE_AUDIO]       = {icon:'media_ico_aud.png'};
    type_info[MEDIA_TYPE_IMAGE]       = {icon:'media_ico_img.png'};

var main_page_menu = {
    'video':[
        { type:ROOT_VIDEO,title:'Movies',                url:'82316', rss:'82316',  news_cache:[]},   
        { type:ROOT_VIDEO,title:'Documentaries',         url:'82318', rss:'82318',  news_cache:[]},  
        { type:ROOT_VIDEO,title:'Series',                url:'82325', rss:'82325',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Trailers',              url:'82326', rss:'82326',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Cartoons',              url:'82329', rss:'82329',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Anime',                 url:'82331', rss:'82331',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Clips',                 url:'82333', rss:'82333',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Live Concerts',         url:'82335', rss:'82335',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Shows and Live',        url:'82339', rss:'82339',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Lessons and Trainings', url:'82343', rss:'82343',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Sport',                 url:'82348', rss:'82348',  news_cache:[]},
        { type:ROOT_VIDEO,title:'ExTube',                url:'82350', rss:'82350',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Theatre and Musicals',  url:'82354', rss:'82354',  news_cache:[]},
        { type:ROOT_VIDEO,title:'Sermon',                url:'371177',rss:'371177', news_cache:[]},
        { type:ROOT_VIDEO,title:'Commercials',           url:'371172',rss:'371172', news_cache:[]}
    ],
    'audio':[
        { type:ROOT_AUDIO,title:'Music',                 url:'82379',rss:'82379',news_cache:[]},
        { type:ROOT_AUDIO,title:'OST',                   url:'82380',rss:'82380',news_cache:[]},
        { type:ROOT_AUDIO,title:'Audio Books',           url:'82381',rss:'82381',news_cache:[]},
        { type:ROOT_AUDIO,title:'Ringtones',             url:'82383',rss:'82383',news_cache:[]},
        { type:ROOT_AUDIO,title:'Lessons and Trainings', url:'84518',rss:'84518',news_cache:[]}
    ],
    'images':[
        { type:ROOT_IMAGE,title:'Art',                     url:'82391',rss:'82391',news_cache:[]},
        { type:ROOT_IMAGE,title:'Personal',                url:'82393',rss:'82393',news_cache:[]},
        { type:ROOT_IMAGE,title:'Wallpapers',              url:'82394',rss:'82394',news_cache:[]},
//      { type:ROOT_IMAGE,title:'Cliparts',                url:'82396',rss:'82396',news_cache:[]}, // EMPTY
        { type:ROOT_IMAGE,title:'Jokes and Funny pictures',url:'82398',rss:'82398',news_cache:[]},
        { type:ROOT_IMAGE,title:'Screenshots',             url:'82403',rss:'82403',news_cache:[]}/*,
        { type:ROOT_IMAGE,title:'Nude',                    url:'82405',rss:'82405',news_cache:[]}*/
    ]
};


