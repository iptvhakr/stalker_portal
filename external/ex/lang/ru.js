
var lang = {
    'loading':'Загрузка...',
    'cat_music':'Аудио',
    'cat_video':'Видео',
    'cat_images':'Изображения',
    'cat_search':'Поиск',
    "Jan":"Января",
    "Feb":"Февраля",
    "Ma":"Марта",
    "Apr":"Апреля",
    "May":"Мая",
    "Jn":"Июнь",
    "Jl":"Июля",
    "Ag":"Августа",
    "Sep":"Сентября",
    "Oct":"Октября",
    "Now":"Ноября",
    "Dec":"Декабря",
    "add":"Добавлено:",
    'add_time':'Добавлен: ',
    'list_default_hint':'Найти в текущей категории',
    'back_action':'Вернуться',
    'refresh':'Обновить',
    'search_is_over':'Поиск завершен',
    'loading_is_over':'Загрузка завершена',
    'no_files':'Подходящих файлов найдено не было',
    'lang_default_hint':'Найти на EX.UA',
    'cats':'Категории:',
    'news':'Новинки:',
    'news_brcr':'Новинки',
    'description':'Имя: ',
    'about_actor':'Об артисте',
    'madia_menu_info_back_act':'Вернуться на предыдущий уровень.',
    'exit':'Выход',
    'close_question':'Закрыть это приложение?',
    'cancel':'Отмена',
    'close':'Да',
    'mark':'Выделить',
    'unmark':'Снять выделение',
    'film_description':'Описание',
    'size_in_bytes':'Размер: ',
    'film_description_off':'Закрыть описание',
    'main_menu':'Главное меню'
}

var LANG_MEDIA_MENU_INFO_ITEM_TOTAL  = 'Записей всего:',
    LANG_MEDIA_MENU_INFO_ITEM_MARKED = 'Выбрано:',
    LANG_MEDIA_BTN_BACK  = 'На уровень<br>выше',
    LANG_MEDIA_BTN_MENU  = 'Настройки',
    LANG_MEDIA_BTN_F3    = 'Выделить',
    LANG_MEDIA_DEFAULT_ABORT = 'Отмена',
    LANG_MEDIA_DEFAULT_CLOSE = 'Закрыть',
    LANG_MEDIA_DEFAULT_ERROR = 'Ошибка',
    LANG_MEDIA_DEFAULT_OK    = 'Да',
    LANG_MEDIA_ITEM_WRONG_TYPE = 'Неизвестный тип выбранного объекта.',
    LANG_MEDIA_MENU_OFF = 'Выкл.';
    
    
var LANG_MEDIA_MENU_GROUPS = {
    gview     : 'Вид',
    gedit     : 'Редактирование',
    gsort     : 'Сортировка',
    gtools    : 'Инструменты',
    gins      : 'Выделенное',
    gaudio    : 'Звуковые дорожки',
    gsubtitle : 'Субтитры',
    gts       : 'TimeShift',
    gtsend    : 'TimeShift буфер',
    g3d       : '3D режим',
    gslideOn  : 'Слайдшоу'
};
    
var mediaInfoMenu_3dview     = 'Режим',
    playerBtnF2sh            = 'Показать<br />плейлист',
    playerBtnF2hd            = 'Скрыть<br />плейлист',
    playingError             = 'Ошибка воспроизведения',
    err_ERROR                = 'Ошибка',
    mediaInfoMenu_langUnknown= 'неизвестный',
    playerBtnInfo            = 'Скрыть<br />плеер',
    playerBtnMenu            = 'Настройки',
    playlistPrev             = "Предыдущее<br />медиа",
    playlistNext             = "Следующее<br />медиа",
    time_seconds             = 'Сек.',
    LANG_MEDIA_MENU_INFO_DURATION = 'Продолжительность: ',
    LANG_MEDIA_MENU_INFO_NO  = 'нет',
    LANG_MEDIA_MENU_NO       = 'Нет',
    LANG_MEDIA_MENU_OFF      = 'Выкл.';
 

//------------------------------------------------------------------------------
var type_info = [];
    type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/ru/video/',         url_end:'?r=23775' };
    type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/view/',             url_end:'?r=23776' };
    type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/view/',             url_end:'?r=23777' };
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
        { type:ROOT_VIDEO,title:'Артисты',url:'artist',rss:'7513588',news_cache:[]},
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






//
//'Accept':'*/*',
//'Accept-Encoding':'gzip,deflate,sdch',
//'Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
//'Connection':'keep-alive',
//'Cookie':'language=en; vimeo_cart=%7B%22ptv%22%3A%7B%22store%22%3A%22ptv%22%2C%22version%22%3A1%2C%22quantities%22%3A%7B%2286276%22%3A1%7D%2C%22items%22%3A%5B%7B%22id%22%3A86276%7D%5D%2C%22attributes%22%3A%5B%5D%7D%7D; xsrft=7cec78a82fcb4dd9cd11a5b4885fc6f6; has_logged_in=1; stats_start_date=2013%2F06%2F09; stats_end_date=2013%2F06%2F13; html_player=0; pl_volume=57.14285714285714; site_settings=%7B%22browse_format%22%3A%22thumbnail%22%2C%22sticky_page%22%3Anull%7D; __utma=256147786.768767120.1370615499.1370615499.1371627988.2; __utmc=256147786; __utmz=256147786.1371627988.2.2.utmcsr=webdesignerwall.com|utmccn=(referral)|utmcmd=referral|utmcct=/demo/adaptive-design/final.html; __utma=18302654.1294437847.1370511398.1371537253.1371659797.34; __utmb=18302654.16.9.1371659820690; __utmc=18302654; __utmz=18302654.1370511398.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=18302654.|2=user_type=basic=1^7=video_count=0=1',
//'Host':'player.vimeo.com',
//'Referer':'http://vimeo.com/18439821',
//'User-Agent':'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36'
//

//function ajax ( method, url, headers ) {
//	var hname, xhr = new XMLHttpRequest();
//	xhr.onreadystatechange = function() {
//		if ( xhr.readyState === 4 ) {
//			console.log(xhr.getAllResponseHeaders());
//			console.log('\n ansver \n'+xhr.responseText);
//		}
//	};
//	xhr.open(method, url, true);
//	// set headers if present
//	if ( headers ) for ( hname in headers ) {
//		xhr.setRequestHeader(hname, headers[hname]);
//	}
//	xhr.send();
//	return xhr;
//}
//
//ajax('GET','http://player.vimeo.com/play_redirect?clip_id=18439821&sig=46d6c504ff6c0f3b9c941c087fdf04a0&time=1371659831&quality=hd&codecs=H264,VP8,VP6&type=moogaloop_local&embed_location=&seek=0',{'Accept':'*/*','Accept-Encoding':'gzip,deflate,sdch','Accept-Language':'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4','Connection':'keep-alive','Cookie':'language=en; imeo_cart=%7B%22ptv%22%3A%7B%22store%22%3A%22ptv%22%2C%22version%22%3A1%2C%22quantities%22%3A%7B%2286276%22%3A1%7D%2C%22items%22%3A%5B%7B%22id%22%3A86276%7D%5D%2C%22attributes%22%3A%5B%5D%7D%7D; xsrft=7cec78a82fcb4dd9cd11a5b4885fc6f6; has_logged_in=1; stats_start_date=2013%2F06%2F09; stats_end_date=2013%2F06%2F13; html_player=0; pl_volume=57.14285714285714; site_settings=%7B%22browse_format%22%3A%22thumbnail%22%2C%22sticky_page%22%3Anull%7D; __utma=256147786.768767120.1370615499.1370615499.1371627988.2; __utmc=256147786; __utmz=256147786.1371627988.2.2.utmcsr=webdesignerwall.com|utmccn=(referral)|utmcmd=referral|utmcct=/demo/adaptive-design/final.html; __utma=18302654.1294437847.1370511398.1371537253.1371659797.34; __utmb=18302654.16.9.1371659820690; __utmc=18302654; __utmz=18302654.1370511398.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=18302654.|2=user_type=basic=1^7=video_count=0=1','Host':'player.vimeo.com','Referer':'http://vimeo.com/18439821','User-Agent':'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36'});
//ajax('GET','http://player.vimeo.com/play_redirect?clip_id=46765791&sig=c7ea88d076dde1df918ab46a0fb07e6b&time=1371662689&quality=sd&codecs=H264,VP8,VP6&type=moogaloop_local&embed_location=&seek=0')
//
//http://vimeo.com/46765791
//
//
//
//
//Host: player.vimeo.com
//Connection: keep-alive
//User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36
//Accept: */*
//Referer: http://vimeo.com/46765791
//Accept-Encoding: gzip,deflate,sdch
//Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4
//Cookie: language=en; vimeo_cart=%7B%22ptv%22%3A%7B%22store%22%3A%22ptv%22%2C%22version%22%3A1%2C%22quantities%22%3A%7B%2286276%22%3A1%7D%2C%22items%22%3A%5B%7B%22id%22%3A86276%7D%5D%2C%22attributes%22%3A%5B%5D%7D%7D; xsrft=7cec78a82fcb4dd9cd11a5b4885fc6f6; has_logged_in=1; stats_start_date=2013%2F06%2F09; stats_end_date=2013%2F06%2F13; html_player=0; pl_volume=57.14285714285714; __utma=256147786.768767120.1370615499.1370615499.1371627988.2; __utmc=256147786; __utmz=256147786.1371627988.2.2.utmcsr=webdesignerwall.com|utmccn=(referral)|utmcmd=referral|utmcct=/demo/adaptive-design/final.html; site_settings=%7B%22browse_format%22%3A%22thumbnail%22%2C%22sticky_page%22%3Anull%7D; __utma=18302654.1294437847.1370511398.1371537253.1371659797.34; __utmb=18302654.90.9.1371662670590; __utmc=18302654; __utmz=18302654.1370511398.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=18302654.|2=user_type=basic=1^7=video_count=0=1
