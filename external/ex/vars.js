var version = '2.01.05';

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
    MEDIA_TYPE_NONE        = 0;
    
// VARIABLES
var NORMAL_MODE = 0,
	LSEARCH_MODE = 1,
	languages = [
		{label: 'English', id: 0, langVal: 'en'},
		{label: 'Русский', id: 1, langVal: 'ru'},
		{label: 'Українська', id: 2, langVal: 'uk'}
	],
	deviceProxy = '',
	DEFAULT_AUDIO_LANG_1_IDX = 1,
	DEFAULT_SUBTITLE_LANG_1_IDX = 0,
	standby,
	currCPage,
	exURL = 'http://www.ex.ua',
	type_focus_timer = 0,
	cat_focus_timer = 0,
	$loadingScreen,
	rightMenuTextLength = 200,
	ServiceMenu = {},
	pauseData = {curTime: 0, fileLength: 0, paused: false, audioTrack: 0}, // use it to restore player position after disconnection from server
	MAX_READABLE_IMAGE_WEIGHT = 500000,  // file size in bytes
	old_system_type = true,
	type_info,
	mainPageMenu,
	volume = { currVol: 50, step: 5, muted: false, timerId: null, timeout: 2000 };

    // определение типа приставки
	if ( gSTB.SetScreenSaverImplUrl  instanceof Function ) {
		old_system_type = false;
	}


var content = {ru: {}, en: {}, uk: {}};

content.uk.type_info = [];
content.uk.type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/',         url_end:'?r=80934' };
content.uk.type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/',             url_end:'?r=80935' };
content.uk.type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/',             url_end:'?r=80936' };
content.uk.type_info[MEDIA_OBJECT]           = {icon:'ico_folder_files.png'};
content.uk.type_info[MEDIA_FOLDER]           = {icon:'media_ico_fld.png'};
content.uk.type_info[ROOT_GLOBAL_SEARCH]     = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?s='};
content.uk.type_info[GLOBAL_SEARCH_FOLDER]   = {icon:'media_ico_fld.png'};
content.uk.type_info[LOCAL_SEARCH_ROOT]      = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?original_id=',url_end:'&s='};
content.uk.type_info[GLOBAL_SEARCH_REFRESH]  = {icon:'ico_search3.png'};
content.uk.type_info[LOCAL_SEARCH_REFRESH]   = {icon:'ico_search3.png'};
content.uk.type_info[RADIO_OBJECT]           = {icon:'media_ico_str.png', url_start:'http://radio.ex.ua:8000/', id:'6996870'};
content.uk.type_info[ACTORS_FOLDER]          = {icon:'media_ico_fld.png', id:'artist'};
content.uk.type_info[WHAT_IS_THIS_TYPE]      = {icon:'media_ico_fld.png'};
content.uk.type_info[ORPHAN_FOLDER]          = {icon:'media_ico_fld.png'};
content.uk.type_info[ROOT_NEWS]              = {icon:'media_ico_fld.png'};
content.uk.type_info[MEDIA_TYPE_VIDEO]       = {icon:'media_ico_vid.png'};
content.uk.type_info[MEDIA_TYPE_AUDIO]       = {icon:'media_ico_aud.png'};
content.uk.type_info[MEDIA_TYPE_IMAGE]       = {icon:'media_ico_img.png'};

content.en.type_info = [];
content.en.type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80925,81708' };
content.en.type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80926,81709' };
content.en.type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/view/',  url_end:'?r=80927,81710' };
content.en.type_info[MEDIA_OBJECT]           = {icon:'ico_folder_files.png'};
content.en.type_info[MEDIA_FOLDER]           = {icon:'media_ico_fld.png'};
content.en.type_info[ROOT_GLOBAL_SEARCH]     = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?s='};
content.en.type_info[GLOBAL_SEARCH_FOLDER]   = {icon:'media_ico_fld.png'};
content.en.type_info[LOCAL_SEARCH_ROOT]      = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?original_id=',url_end:'&s='};
content.en.type_info[GLOBAL_SEARCH_REFRESH]  = {icon:'ico_search3.png'};
content.en.type_info[LOCAL_SEARCH_REFRESH]   = {icon:'ico_search3.png'};
content.en.type_info[RADIO_OBJECT]           = {icon:'media_ico_str.png', url_start:'http://radio.ex.ua:8000/', id:'6996870'};
content.en.type_info[ACTORS_FOLDER]          = {icon:'media_ico_fld.png', id:'artist'};
content.en.type_info[WHAT_IS_THIS_TYPE]      = {icon:'media_ico_fld.png'};
content.en.type_info[ORPHAN_FOLDER]          = {icon:'media_ico_fld.png'};
content.en.type_info[ROOT_NEWS]              = {icon:'media_ico_fld.png'};
content.en.type_info[MEDIA_TYPE_VIDEO]       = {icon:'media_ico_vid.png'};
content.en.type_info[MEDIA_TYPE_AUDIO]       = {icon:'media_ico_aud.png'};
content.en.type_info[MEDIA_TYPE_IMAGE]       = {icon:'media_ico_img.png'};

content.ru.type_info = [];
content.ru.type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/ru/video/',         url_end:'?r=23775' };
content.ru.type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/view/',             url_end:'?r=23776' };
content.ru.type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/view/',             url_end:'?r=23777' };
content.ru.type_info[MEDIA_OBJECT]           = {icon:'ico_folder_files.png'};
content.ru.type_info[MEDIA_FOLDER]           = {icon:'media_ico_fld.png'};
content.ru.type_info[ROOT_GLOBAL_SEARCH]     = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?s='};
content.ru.type_info[GLOBAL_SEARCH_FOLDER]   = {icon:'media_ico_fld.png'};
content.ru.type_info[LOCAL_SEARCH_ROOT]      = {icon:'ico_search3.png',   url_start:'http://www.ex.ua/search?original_id=',url_end:'&s='};
content.ru.type_info[GLOBAL_SEARCH_REFRESH]  = {icon:'ico_search3.png'};
content.ru.type_info[LOCAL_SEARCH_REFRESH]   = {icon:'ico_search3.png'};
content.ru.type_info[RADIO_OBJECT]           = {icon:'media_ico_str.png', url_start:'http://radio.ex.ua:8000/', id:'6996870'};
content.ru.type_info[ACTORS_FOLDER]          = {icon:'media_ico_fld.png', id:'artist'};
content.ru.type_info[WHAT_IS_THIS_TYPE]      = {icon:'media_ico_fld.png'};
content.ru.type_info[ORPHAN_FOLDER]          = {icon:'media_ico_fld.png'};
content.ru.type_info[ROOT_NEWS]              = {icon:'media_ico_fld.png'};
content.ru.type_info[MEDIA_TYPE_VIDEO]       = {icon:'media_ico_vid.png'};
content.ru.type_info[MEDIA_TYPE_AUDIO]       = {icon:'media_ico_aud.png'};
content.ru.type_info[MEDIA_TYPE_IMAGE]       = {icon:'media_ico_img.png'};

content.uk.mainPageMenu = {
	'video':[
		{ type:ROOT_VIDEO,title:'Закордонне кіно',url:'82470',rss:'82470',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Наше кіно',url:'82473',rss:'82473',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Документальне',url:'82476',rss:'82476',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Серіали',url:'82480',rss:'82480',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Трейлери',url:'82483',rss:'82483',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Мультфільми',url:'82484',rss:'82484',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Аніме',url:'82488',rss:'82488',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Кліпи',url:'82489',rss:'82489',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Концерти',url:'82490',rss:'82490',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Шоу та передачі',url:'82493',rss:'82493',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Уроки та Тренінги',url:'82495',rss:'82495',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Спорт',url:'82496',rss:'82496',news_cache:[]},
		{ type:ROOT_VIDEO,title:'ExTube',url:'82499',rss:'82499',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Театр',url:'82508',rss:'82508',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Проповіді',url:'371167',rss:'371167',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Рекламні ролики',url:'371168',rss:'371168',news_cache:[]}
	],
	'audio':[
		{ type:ROOT_AUDIO,title:'Музика',url:'82515',rss:'82515',news_cache:[]},
		{ type:ROOT_AUDIO,title:'OST',url:'82518',rss:'82518',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудіокниги',url:'82521',rss:'82521',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Рінгтони',url:'82522',rss:'82522',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Уроки та Тренінги',url:'84516',rss:'84516',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудіоспектаклі',url:'38502573',rss:'38502573',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудіодоріжки',url:'38502830',rss:'38502830',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Проповіді, Молитви',url:'38503040',rss:'38503040',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Гумор',url:'38503215',rss:'38503215',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Караоке',url:'38506848',rss:'38506848',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудiоказки',url:'38507143',rss:'38507143',news_cache:[]}
	],
	'images':[
		{ type:ROOT_IMAGE,title:'Арт',url:'82524',rss:'82524',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Приватні фото',url:'82527',rss:'82527',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Фон на робочий стіл',url:'82528',rss:'82528',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Кліпарт',url:'82531',rss:'82531',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Приколи',url:'82533',rss:'82533',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Скріншоти',url:'82535',rss:'82535',news_cache:[]},
		//{ type:ROOT_IMAGE,title:'НЮ',url:'82539',rss:'82539',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Постери',url:'38503396',rss:'38503396',news_cache:[]}
	]
};

content.en.mainPageMenu = {
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

content.ru.mainPageMenu = {
	'video':[
		{ type:ROOT_VIDEO,title:'Зарубежное',url:'foreign',rss:'2',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Наше',url:'our',rss:'70538',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Зарубежные сериалы',url:'foreign_series',rss:'1988',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Наши сериалы',url:'our_series',rss:'422546',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Мультфильмы',url:'cartoon',rss:'1989',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Аниме',url:'anime',rss:'23786',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Документальное',url:'documentary',rss:'1987',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Трейлеры',url:'trailer',rss:'1990',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Клипы',url:'clip',rss:'1991',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Концерты',url:'concert',rss:'70533',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Шоу и Передачи',url:'show',rss:'28713',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Уроки и Тренинги',url:'training',rss:'28714',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Спорт',url:'sport',rss:'69663',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Короткие видео ролики, приколы',url:'short',rss:'23785',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Театр',url:'theater',rss:'70665',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Проповеди',url:'sermon',rss:'371146',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Рекламные ролики',url:'commercial',rss:'371152',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Для мобильных устройств',url:'mobile',rss:'607160',news_cache:[]},
		{ type:ROOT_VIDEO,title:'Артисты',url:'artist',rss:'7513588',news_cache:[]}
	],
	'audio':[
		{ type:ROOT_AUDIO,title:'Музыка',url:'3',rss:'3',news_cache:[]},
		{ type:ROOT_AUDIO,title:'OST',url:'71793',rss:'71793',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудиокниги',url:'3454',rss:'3454',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Рингтоны',url:'28712',rss:'28712',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Уроки и Тренинги',url:'84513',rss:'84513',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Караоке',url:'707499',rss:'707499',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Радио',url:'6996870',rss:'6996870',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудиоспектакли',url:'38494793',rss:'38494793',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Проповеди',url:'38495531',rss:'38495531',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Юмор',url:'38495711',rss:'38495711',news_cache:[]},
		{ type:ROOT_AUDIO,title:'Аудиосказки',url:'39032910',rss:'39032910',news_cache:[]}
	],
	'images':[
		{ type:ROOT_IMAGE,title:'Арт',url:'28739',rss:'28739',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Частные фото',url:'904',rss:'904',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Обои на рабочий стол',url:'16984',rss:'16984',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Клипарт',url:'28726',rss:'28726',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Приколы',url:'28727',rss:'28727',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Скриншоты',url:'31049',rss:'31049',news_cache:[]},
		//{ type:ROOT_IMAGE,title:'НЮ',url:'28725',rss:'28725',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Архитектура и города',url:'371136',rss:'371136',news_cache:[]},
		{ type:ROOT_IMAGE,title:'Fashion',url:'508267',rss:'508267',news_cache:[]}
	]
};


var iso639 = [
	{code: ['eng', 'en'], name: 'English'},
	{code: ['rus', 'ru'], name: 'Russian'},
	{code: ['ukr', 'uk'], name: 'Ukrainian'},
	{code: ['bel', 'be'], name: 'Belarusian'},
	{code: ['fre', 'fra', 'fr'], name: 'French'},
	{code: ['ger', 'deu', 'de'], name: 'German'},
	{code: ['ita', 'it'], name: 'Italian'},
	{code: ['spa', 'es'], name: 'Spanish'},
	{code: ['por', 'pt'], name: 'Portuguese'},
	{code: ['swe', 'sv'], name: 'Swedish'},
	{code: ['nor', 'no'], name: 'Norwegian'},
	{code: ['dut', 'nld', 'nl'], name: 'Dutch'},
	{code: ['srp', 'scc', 'sr'], name: 'Serbian'},
	{code: ['slv', 'sl'], name: 'Slovenian'},
	{code: ['hrv', 'hr', 'scr'], name: 'Croatian'},
	{code: ['alb', 'sqi', 'sq'], name: 'Albanian'},
	{code: ['jpn', 'ja'], name: 'Japanese'},
	{code: ['chi', 'zho', 'zh'], name: 'Chinese'},
	{code: ['kor', 'ko'], name: 'Korean'},
	{code: ['vie', 'vi'], name: 'Vietnamese'},
	{code: ['lav', 'lv'], name: 'Latvian'},
	{code: ['lit', 'lt'], name: 'Lithuanian'},
	{code: ['est', 'et'], name: 'Estonian'},
	{code: ['fin', 'fi'], name: 'Finnish'},
	{code: ['hun', 'hu'], name: 'Hungarian'},
	{code: ['cze', 'ces', 'cs'], name: 'Czech'},
	{code: ['slo', 'slk', 'sk'], name: 'Slovak'},
	{code: ['bul', 'bg'], name: 'Bulgarian'},
	{code: ['pol', 'pl'], name: 'Polish'},
	{code: ['rum', 'ron', 'ro'], name: 'Romanian'    },
	{code: ['gre', 'ell', 'el'], name: 'Greek'},
	{code: ['heb', 'he'], name: 'Hebrew'},
	{code: ['tur', 'tr'], name: 'Turkish'},
	{code: ['dan', 'da'], name: 'Danish'},
	{code: ['ice', 'isl', 'is'], name: 'Icelandic'},
	{code: ['hin', 'hi'], name: 'Hindi'},
	{code: ['ben', 'bn'], name: 'Bengali'},
	{code: ['ara', 'ar'], name: 'Arabic'},
	{code: ['arm', 'hye', 'hy'], name: 'Armenian'},
	{code: ['geo', 'kat', 'ka'], name: 'Georgian'},
	{code: ['aze', 'az'], name: 'Azerbaijani'},
	{code: ['bak', 'ba'], name: 'Bashkir'},
	{code: ['baq', 'eus', 'eu'], name: 'Basque'},
	{code: ['bos', 'bs'], name: 'Bosnian'},
	{code: ['bua'], name: 'Buriat'},
	{code: ['bur', 'mya', 'my'], name: 'Burmese'},
	{code: ['che', 'ce'], name: 'Chechen'},
	{code: ['wel', 'cym', 'cy'], name: 'Welsh'},
	{code: ['dzo', 'dz'], name: 'Dzongkha'},
	{code: ['epo', 'eo'], name: 'Esperanto'},
	{code: ['per', 'fa'], name: 'Persian'},
	{code: ['gle', 'ga'], name: 'Irish'},
	{code: ['guj', 'gu'], name: 'Gujarati'},
	{code: ['ind', 'id'], name: 'Indonesian'},
	{code: ['ira'], name: 'Iranian'},
	{code: ['kas', 'ks'], name: 'Kashmiri'},
	{code: ['kaz', 'kk'], name: 'Kazakh'},
	{code: ['kbd'], name: 'Kabardian'},
	{code: ['kom', 'kv'], name: 'Komi'},
	{code: ['krl'], name: 'Karelian'},
	{code: ['kur', 'ku'], name: 'Kurdish'},
	{code: ['mar', 'mr'], name: 'Marathi'},
	{code: ['mac', 'mkd', 'mk'], name: 'Macedonian'},
	{code: ['nep', 'ne'], name: 'Nepali'},
	{code: ['oss', 'os'], name: 'Ossetian'},
	{code: ['sah'], name: 'Yakut'},
	{code: ['som', 'so'], name: 'Somali'},
	{code: ['tam', 'ta'], name: 'Tamil'},
	{code: ['tat', 'tt'], name: 'Tatar'},
	{code: ['tel', 'te'], name: 'Telugu'},
	{code: ['tgk', 'tg'], name: 'Tajik'},
	{code: ['tha', 'th'], name: 'Thai'},
	{code: ['tuk', 'tk'], name: 'Turkmen'},
	{code: ['udm'], name: 'Udmurt'},
	{code: ['urd', 'ur'], name: 'Urdu'},
	{code: ['uzb', 'uz'], name: 'Uzbek'},
	{code: ['xal'], name: 'Kalmyk'},
	{code: ['tib', 'bod', 'bo'], name: 'Tibetan'},
	{code: ['yid', 'yi'], name: 'Yiddish'},
	{code: ['nothing', 'nothing'], name: 'Keine Audiospur'}
];