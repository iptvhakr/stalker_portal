
var lang = {
    'loading':'Завантаження...',
    'cat_music':'Аудіо',
    'cat_video':'Відео',
    'cat_images':'Зображення',
    'cat_search':'Пошук',
    'Jan':'Січня',
    'Feb':'Лютого',
    'Ma':'Березня',
    'Apr':'Квітня',
    'May':'Травня',
    'Jn':'Червня',
    'Jl':'Липня',
    'Ag':'Серпня',
    'Sep':'Вересня',
    'Oct':'Жовтня',
    'Now':'Листопаду',
    'Dec':'Грудня',
    'add':'Додано:',
    'add_time':'Додано: ',
    'list_default_hint':'Знайти в поточній категорії',
    'back_action':'Повернутися',
    'refresh':'Оновити',
    'search_is_over':'Пошук завершено',
    'loading_is_over':'Завантаження завершено',
    'no_files':'Відповідних файлів знайдено не було',
    'lang_default_hint':'Знайти на EX.UA',
    'cats':'Категорії:',
    'news':'Новинки:',
    'news_brcr':'Новинки',
    'description':'Им\'я: ',
    'about_actor':'Про артиста',
    'madia_menu_info_back_act':'Повернутися на попередній рівень.',
    'exit':'Вихiд',
    'close_question':'Закрити цей сервiс?',
    'cancel':'Відміна',
    'close':'Да',
    'mark':'Виділити',
    'unmark':'Зняти виділення',
    'film_description':'Опис',
    'size_in_bytes':'Розмiр: ',
    'film_description_off':'Закрити опис',
    'main_menu':'Головне меню'
};

var LANG_MEDIA_DEFAULT_ABORT = 'Відміна',
    LANG_MEDIA_DEFAULT_CLOSE = 'Закрити',
    LANG_MEDIA_DEFAULT_ERROR = 'Помилка',
    LANG_MEDIA_DEFAULT_OK    = 'Да',
    LANG_MEDIA_ITEM_WRONG_TYPE = 'Невідомий тип обраного об\'єкта.',
    LANG_MEDIA_MENU_OFF = 'Викл.';
    
    
var LANG_MEDIA_MENU_GROUPS = {
    gview     : 'Вид',
    gedit     : 'Редагування',
    gsort     : 'Сортування',
    gtools    : 'Інструменти',
    gins      : 'Виділене',
    gaudio    : 'Звукові доріжки',
    gsubtitle : 'Субтитри',
    gts       : 'TimeShift',
    gtsend    : 'TimeShift буфер',
    g3d       : '3D режим',
    gslideOn  : 'Слайдшоу'
};
    
var mediaInfoMenu_3dview     = 'Режим',
    playerBtnF2sh            = 'Показати <br />плейлист',
    playerBtnF2hd            = 'Приховати<br />плейлист',
    playingError             = 'Помилка відтворення',
    mediaInfoMenu_langUnknown= 'невідомий',
    playerBtnInfo            = 'Приховати <br />плеєр',
    playerBtnMenu            = 'Параметри',
    playlistPrev             = 'Попереднє <br />медіа',
    playlistNext             = 'Наступне <br />медіа',
    time_seconds             = 'Сек.',
    LANG_MEDIA_MENU_INFO_DURATION = 'Тривалість: ',
    LANG_MEDIA_MENU_NO       = 'Нi';
 

//------------------------------------------------------------------------------
var type_info = [];
    type_info[ROOT_VIDEO]             = {icon:'media_ico_vid.png', url_start:'http://www.ex.ua/',         url_end:'?r=80934' };
    type_info[ROOT_AUDIO]             = {icon:'media_ico_aud.png', url_start:'http://www.ex.ua/',             url_end:'?r=80935' };
    type_info[ROOT_IMAGE]             = {icon:'media_ico_img.png', url_start:'http://www.ex.ua/',             url_end:'?r=80936' };
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
        { type:ROOT_VIDEO,title:'Рекламні ролики',url:'371168',rss:'371168',news_cache:[]},
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
        { type:ROOT_IMAGE,title:'Постери',url:'38503396',rss:'38503396',news_cache:[]},
    ]
};