
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