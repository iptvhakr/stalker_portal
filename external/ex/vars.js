 
//------------------------------------------------------------------------------
var     debug   = false,
        version = '0.67 beta';
// Типы узлов
var TYPE_NONE              = 0,
    ROOT_VIDEO             = 1,
    ROOT_AUDIO             = 2,
    ROOT_IMAGE             = 3,
    ROOT_GLOBAL_SEARCH     = 4,
    ROOT_NEWS              = 5,
    GLOBAL_SEARCH_FOLDER   = 8,  
    MEDIA_OBJECT           = 10,
    MEDIA_FOLDER           = 11,
    RADIO_OBJECT           = 12,
    ACTORS_FOLDER          = 13,
    TYPE_BACK              = 14,
    MEDIA_TYPE_VIDEO       = 15,
    MEDIA_TYPE_AUDIO       = 16,
    MEDIA_TYPE_IMAGE       = 17,
    GLOBAL_SEARCH_REFRESH  = 18,
    LOCAL_SEARCH_REFRESH   = 19, 
    LOCAL_SEARCH_ROOT      = 20,
    WHAT_IS_THIS_TYPE      = 21,
    ORPHAN_FOLDER          = 22,
    MEDIA_TYPE_STREAM      = 23,  // ??
    MEDIA_TYPE_NONE        = 0
    ;
    
// VARIABLES
var NORMAL_MODE  = 0,
    LSEARCH_MODE = 1,
    environment = {},
    proxy = '',
    rect = {left:843, top:149, width:400, height:221},
    DEFAULT_AUDIO_LANG_1_IDX    = 1,
    DEFAULT_SUBTITLE_LANG_1_IDX = 0,
    curLangIdx= null,  
    standby,
    currCPage,
    exURL='http://www.ex.ua',
    type_focus_timer=0,
    cat_focus_timer=0, 
    loading_screen, 
    rigth_menu_text_length= 200,
    // VOLUME
    DEFAULT_VOLUME        = 100,
    curVolume             = DEFAULT_VOLUME,
    volume_CHANGE_STEP    = 5,
    volume_timer          = null,
    volume_TIMEOUT        = 2000,
    muteValue             = 0,       // stb.SetMute(muteValue)
    ServiceMenu           = {},
    pause_time_data       = 0,
    MAX_READABLE_IMAGE_WEIGHT = 500000,  // file size in bytes
    old_system_type = true
    ;    

    // определение типа приставки
    if(gSTB.SetScreenSaverImplUrl  instanceof Function ){
        old_system_type = false;
    }


var iso639 = [
  {
    code : ['eng','en'],
    name : 'English'
  },
  {
    code : ['rus','ru'],
    name : 'Russian'
  },
  {
    code : ['ukr','uk'],
    name : 'Ukrainian'
  },
  {
    code : ['bel','be'],
    name : 'Belarusian'
  },
  {
    code : ['fre','fra','fr'],
    name : 'French'
  },
  {
    code : ['ger','deu','de'],
    name : 'German'
  },
  {
    code : ['ita','it'],
    name : 'Italian'
  },
  {
    code : ['spa','es'],
    name : 'Spanish'
  },
  {
    code : ['por','pt'],
    name : 'Portuguese'
  },
  {
    code : ['swe','sv'],
    name : 'Swedish'
  },
  {
    code : ['nor','no'],
    name : 'Norwegian'
  },
  {
    code : ['dut','nld','nl'],
    name : 'Dutch'
  },
  {
    code : ['srp','scc','sr'],
    name : 'Serbian'
  },
  {
    code : ['slv','sl'],
    name : 'Slovenian'
  },
  {
    code : ['hrv','hr','scr'],
    name : 'Croatian'
  },
  {
    code : ['alb','sqi','sq'],
    name : 'Albanian'
  },
  {
    code : ['jpn','ja'],
    name : 'Japanese'
  },
  {
    code : ['chi','zho','zh'],
    name : 'Chinese'
  },
  {
    code : ['kor','ko'],
    name : 'Korean'
  },
  {
    code : ['vie','vi'],
    name : 'Vietnamese'
  },
  {
    code : ['lav','lv'],
    name : 'Latvian'
  },
  {
    code : ['lit','lt'],
    name : 'Lithuanian'
  },
  {
    code : ['est','et'],
    name : 'Estonian'
  },
  {
    code : ['fin','fi'],
    name : 'Finnish'
  },
  {
    code : ['hun','hu'],
    name : 'Hungarian'
  },
  {
    code : ['cze','ces','cs'],
    name : 'Czech'
  },
  {
    code : ['slo','slk','sk'],
    name : 'Slovak'
  },
  {
    code : ['bul','bg'],
    name : 'Bulgarian'
  },
  {
    code : ['pol','pl'],
    name : 'Polish'
  },
  {
    code : ['rum','ron','ro'],
    name : 'Romanian'
  },
  {
    code : ['gre','ell','el'],
    name : 'Greek'
  },
  {
    code : ['heb','he'],
    name : 'Hebrew'
  },
  {
    code : ['tur','tr'],
    name : 'Turkish'
  },
  {
    code : ['dan','da'],
    name : 'Danish'
  },
  {
    code : ['ice','isl','is'],
    name : 'Icelandic'
  },
  {
    code : ['hin','hi'],
    name : 'Hindi'
  },
  {
    code : ['ben','bn'],
    name : 'Bengali'
  },
  {
    code : ['ara','ar'],
    name : 'Arabic'
  },
  {
    code : ['arm','hye','hy'],
    name : 'Armenian'
  },
  {
    code : ['geo','kat','ka'],
    name : 'Georgian'
  },
  {
    code : ['aze','az'],
    name : 'Azerbaijani'
  },
  {
    code : ['bak','ba'],
    name : 'Bashkir'
  },
  {
    code : ['baq','eus','eu'],
    name : 'Basque'
  },
  {
    code : ['bos','bs'],
    name : 'Bosnian'
  },
  {
    code : ['bua'],
    name : 'Buriat'
  },
  {
    code : ['bur','mya','my'],
    name : 'Burmese'
  },
  {
    code : ['che','ce'],
    name : 'Chechen'
  },
  {
    code : ['wel','cym','cy'],
    name : 'Welsh'
  },
  {
    code : ['dzo','dz'],
    name : 'Dzongkha'
  },
  {
    code : ['epo','eo'],
    name : 'Esperanto'
  },
  {
    code : ['per','fa'],
    name : 'Persian'
  },
  {
    code : ['gle','ga'],
    name : 'Irish'
  },
  {
    code : ['guj','gu'],
    name : 'Gujarati'
  },
  {
    code : ['ind','id'],
    name : 'Indonesian'
  },
  {
    code : ['ira'],
    name : 'Iranian'
  },
  {
    code : ['kas','ks'],
    name : 'Kashmiri'
  },
  {
    code : ['kaz','kk'],
    name : 'Kazakh'
  },
  {
    code : ['kbd'],
    name : 'Kabardian'
  },
  {
    code : ['kom','kv'],
    name : 'Komi'
  },
  {
    code : ['krl'],
    name : 'Karelian'
  },
  {
    code : ['kur','ku'],
    name : 'Kurdish'
  },
  {
    code : ['mar','mr'],
    name : 'Marathi'
  },
  {
    code : ['mac','mkd','mk'],
    name : 'Macedonian'
  },
  {
    code : ['nep','ne'],
    name : 'Nepali'
  },
  {
    code : ['oss','os'],
    name : 'Ossetian'
  },
  {
    code : ['sah'],
    name : 'Yakut'
  },
  {
    code : ['som','so'],
    name : 'Somali'
  },
  {
    code : ['tam','ta'],
    name : 'Tamil'
  },
  {
    code : ['tat','tt'],
    name : 'Tatar'
  },
  {
    code : ['tel','te'],
    name : 'Telugu'
  },
  {
    code : ['tgk','tg'],
    name : 'Tajik'
  },
  {
    code : ['tha','th'],
    name : 'Thai'
  },
  {
    code : ['tuk','tk'],
    name : 'Turkmen'
  },
  {
    code : ['udm'],
    name : 'Udmurt'
  },
  {
    code : ['urd','ur'],
    name : 'Urdu'
  },
  {
    code : ['uzb','uz'],
    name : 'Uzbek'
  },
  {
    code : ['xal'],
    name : 'Kalmyk'
  },
  {
    code : ['tib','bod','bo'],
    name : 'Tibetan'
  },
  {
    code : ['yid','yi'],
    name : 'Yiddish'
  },
  {	
	code : ['nothing','nothing'],
	name : 'Keine Audiospur'
  }
];