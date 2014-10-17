/*********************
 * ex.ua init script
 * @author B_A_S
 * @mentor DarkPark
 ********************/

// global event
var stbEvent = {
	onEvent : function(data){},
	onMessage: function( from, message, data ){
		this.trigger(message, {from: from, data: data});
	},
	onBroadcastMessage: function( from, message, data ){
		echo(message, "onBroadcastMessage");
		this.trigger("broadcast." + message, {from: from, data: data});
	},
	event : 0
};

/** 
 * global Main Page object
 * @type {CPage}
 */
var MainPage = new CPage();
MainPage.name = 'MainPage';


/**
 * global categories object
 * @type {CPage}
 */
var ListPage = new CPage();
ListPage.name = 'ListPage';


// меню предварительного просмотра
ListPage.Preview = new CBase(ListPage);
ListPage.Info    = new CBase(ListPage);


// получение ссылки на место, куда вернуться после закрытия
var _GET = {referer: '', proxy: ''};
(function get_params () {
	var get, x, l, i;
	get = String(window.location);
	x = get.indexOf('?');
	if ( x != -1 ) {
		l = get.length;
		get = get.substr(x + 1, l - x);
		l = get.split('&');
		x = 0;
		for ( i in l ) {
			if ( l.hasOwnProperty(i) ) {
				get = l[i].split('=');
				_GET[get[0]] = get[1];
				x++;
			}
		}
	}
	deviceProxy = _GET['proxy'];
})();




/**
 * Main page load handler
 */
window.onload = function onPageLoad () {
    echo('******** STB STARTED ********');
    try {
		$loadingScreen = document.getElementById('loading');
		$loadingScreen.style.display = 'block';
		var curLangIdx = getCurrentLanguage();
		if ( curLangIdx != 'ru' && curLangIdx != 'uk' ) { curLangIdx = 'en'; }
		// get content and interface languages
		var data = gSTB.LoadUserData('ex.ua.data.json');
		try {
			data = JSON.parse(data);
		} catch ( err ) {
			echo('JSON.parse(LoadUserData("ex.ua.data.json")); -> ERROR ->' + err);
			data = {contentLang: curLangIdx, interfaceLang: curLangIdx};
			gSTB.SaveUserData('ex.ua.data.json', JSON.stringify(data));
		}
		echo(data, 'data JSON.parse(LoadUserData("ex.ua.data.json"))');
		// set content lang
		type_info = content[data.contentLang].type_info;
		mainPageMenu = content[data.contentLang].mainPageMenu;

		// get localization file
        loadScript('lang/' + data.interfaceLang + '.js', function () {
                echo(screen.width + "x" + screen.height, "screen resolution");
                echo(data, "current language");
                try {
                    echo('main page init');
                    MainPage.handleInner=document.getElementById('MainPage');
                    MainPage.Init(MainPage.handleInner);
                    // переход на страницу по умолчанию в случае вылета 
                    ServiceMenu = MainPage;
                    
                    // начинаем инициализацию второй страницы
                    ListPage.handleInner=document.getElementById('ListPage');
                    ListPage.Init(ListPage.handleInner);
        
                    // инициализируем плеер
                    MediaPlayer.handleInner = document.getElementById('pageMediaPlayer');
                    MediaPlayer.Init(MediaPlayer.handleInner);
					MediaPlayer.currLang = data.interfaceLang;
                    
                    // локализация отдельных элементов
                    MainPage.$catTypeName = MainPage.handleInner.querySelector('.content  .mainContent  .servText');
                    MainPage.$catTypeName.innerHTML = lang.cat_video+':';
                    MainPage.handleInner.querySelector('.content  .rightContent .servText').innerHTML = lang.news;
                    
                    // изменение параметров при малых разрешениях
                    rightMenuTextLength = 200;
                    if(screen.width == 720 ) {rightMenuTextLength = 117;}
                    
                    // установка громкости
                    gSTB.InitPlayer();
					volume.init();
					volume.setVol(volume.currVol);
					gSTB.EnableServiceButton(true); // turn on system settings

					// start emulation server side
					if ( !EMULATION && DEBUG && DEBUG_NAME && DEBUG_SERVER ) {
						if ( window.proxyServer ) {
							proxy = new proxyServer();
							proxy.init({name:DEBUG_NAME, host:DEBUG_SERVER});
						}
					}
                } catch ( err ) {
                    echo('onPageLoad err '+err);
                }
        });
    } catch ( err ) {
        echo('onPageLoad err '+err);
    }
};


function mainEventListener ( event ) {
    echo('mainEventListener 0');
    // get real key code or exit
    if ( !eventPrepare(event, false, 'mainEventListener') ) return;
    echo('mainEventListener');    
    switch ( event.code ) {
        // sound volume
        case KEYS.MUTE:
			volume.toggleMute();
            break;
        case KEYS.VOLUME_UP:
            if ( volume.currVol < 100 ) {volume.currVol = Number(volume.currVol) + volume.step;}
			volume.setVol(volume.currVol);
            break;
        case KEYS.VOLUME_DOWN:
            if ( volume.currVol > 0 ) {volume.currVol = Number(volume.currVol) - volume.step;}
			volume.setVol(volume.currVol);
            break;
        case KEYS.POWER:
			standby = !standby;
			if ( standby ) {
				if ( currCPage === MediaPlayer ) { MediaPlayer.playPause(); }
				if ( currCPage === MainPage && MainPage.newsList.isActive ) { MainPage.typeList.Activate(true); }
				setTimeout(function () {gSTB.StandBy(standby);}, 1000);
				gSTB.ExecAction('front_panel ' + standby ? 'led-on' : 'led-off');
			} else {
				gSTB.StandBy(standby);
				setTimeout(function () {
					if ( currCPage === MediaPlayer ) {
						MediaPlayer.playPause();
					}
				}, 3000);
				gSTB.ExecAction('front_panel ' + standby ? 'led-on' : 'led-off');
			}
			break;
        default:
            if ( currCPage && currCPage.EventHandler instanceof Function ) {
                currCPage.EventHandler(event);
            } else {
                event.preventDefault();
            }
    }
}

/**
 * init volume elements
 */
volume.init = function () {
	this.$container = document.body.getElementsByClassName('volumeContainer')[0];
	this.$mute = this.$container.querySelector('.mute');
	this.$form = this.$container.querySelector('.volumeForm');
	this.$bar = this.$form.querySelector('div');
};

/**
 * set new volume level
 * @param vol {Number} new volume level (from 0 to 100)
 */
volume.setVol = function ( vol ) {
	echo('set volume=' + vol);
	var step_px = screen.width === 1920 ? 15 : 10,   //  шаг смещения ползунка при изменении громкости на 5% (px)
		vol_idx = vol / 5,
		value;

	if ( vol > 100 || vol < 0 ) {vol = 100;}
	if ( this.timerId ) {clearTimeout(this.timerId);}
	this.$mute.style.display = "none";

	if ( vol_idx ) {
		value = vol_idx * step_px - 5;
		this.$bar.style.width = value + 'px';
		if ( this.muted ) { this.toggleMute(); }                // Выкл. MUTE (включить звук)
		this.$form.style.display = 'block';
		this.$mute.style.display = "none";
		this.timerId = setTimeout(function () {
			volume.timerId = null;
			if ( volume.currVol >= 5 ) { volume.$form.style.display = "none"; }
		}, this.timeout);
	} else {
		this.$form.style.display = 'none';
		this.timerId = null;
		if ( this.currVol >= 5 ) { this.$form.style.display = "none"; }
		this.$mute.style.display = "block";
	}
	gSTB.SetVolume(vol);
};

/**
 * toogle mute state
 */
volume.toggleMute = function () {
	if ( this.muted ) {
		this.muted = false;
		if ( this.currVol >= 5 ) {
			this.$mute.style.display = "none";
			clearTimeout(this.timerId);
			this.$form.style.display = "block";
			this.timerId = setTimeout(function () { volume.$form.style.display = "none"; }, this.timeout);
		}
	} else {
		this.$mute.style.display = "block";
		this.muted = true;
		this.timerId = null;
		if ( this.currVol >= 5 ) {this.$form.style.display = "none";}
		if ( this.timerId ) { clearTimeout(this.timerId); }
	}
	gSTB.SetMute(this.muted ? 1 : 0);  // this function use numbers as params
};


      
     
/*
 * инициализация первой страницы    
 */               
MainPage.onInit = function(){
	MainPage.$mainContent = MainPage.handleInner.querySelector('.content .mainContent');
	MainPage.$typeList = MainPage.handleInner.querySelector('.content .typeLst');
	MainPage.$catTypeName = MainPage.handleInner.querySelector('.content  .mainContent  .servText');
	// ссылка на последний активный компонент чтобы активировать его когда мы вернемя на эту страницу
	MainPage.LastActComp = null;
    
    //    создаем меню типов контента  (аудио, видео, картинки)
    MainPage.typeList = new CScrollList(MainPage);
    MainPage.typeList.Init(MainPage.handleInner.querySelector('.content .typeLst .cslist-main'));
    // навешиваем активацию элемента при наведении на него мыши
    MainPage.typeList.handleInner.onmouseover=function(){
        if(!MainPage.typeList.isActive){
            echo('onMouseOver->MainPage.typeList.Activate(true);');
            MainPage.typeList.Activate(true);
        }
    };
    
    // заполняем меню типов контента - (у нас всегда есть только 3 неменяющихся элемента)
    // и в то же время - навешиваем onCkick и onfocus на меню типов контента, категории и новинки
	var $listItem,
		id_numb = 0,
		$catGroupPlace = MainPage.handleInner.querySelector('.content .cat_group');
    //    заполняем меню типов контента, используя кеш
    for(var level_0 in mainPageMenu){
        $listItem = element('div', {
            className:'ctypeLst',
            id:'typeItem'+id_numb
        });
        echo('add typeListItem'+id_numb);

        // заполнение средних категорий содержимым (кино, музыка, картинки)
        var cat         = new CScrollList(MainPage),
            flag        = true, // флаг определения первого элемента в списке
            $catHandler = $catGroupPlace.appendChild(element('div', {className:'cslist-main'}));
        cat.Init($catHandler);
        
        for(var level_1 in mainPageMenu[level_0]){
            cat.Add(mainPageMenu[level_0][level_1].title,{
                news_data:mainPageMenu[level_0][level_1],
                focused:flag,
                onclick : function(){
                    echo('middle column onClick func,');
                    echo('this.news_data.url='+this.news_data.url);
                    onClickFunc(this.news_data.url, this.news_data.rss,this.news_data.title,this.news_data.type);
                    return false;
                }
            });
            // первый элемент по умолчанию - фокусный
            if(flag){
                flag = false;
            }
        }
        cat.iid = id_numb;
        // навешиваем активизацию элемента при наведении на него мыши
        $catHandler.onmouseover = function(){
            var curr_cat = MainPage.typeList.Current().cat;
            echo('onMouseOver->cat_'+curr_cat.iid+' Active?='+curr_cat.isActive);
            if(!curr_cat.isActive){
                echo('onMouseOver->cat_'+curr_cat.iid+'.Activate(true);');
                curr_cat.Activate(true);
            }
        };
        
        // навешиваем действие при наведении фокуса (обнвление правого меню)
        cat.onFocus = function(current, prev){
        	MainPage.catOnFocus(current, prev);
        };

        // изменение вида фокуса при деактивации списка (становится темный фокус)
        cat.onDeactivate = function(){
            MainPage.$mainContent.className = 'crop mainContent notActive';
        };
        cat.onActivate = function(){
            MainPage.$mainContent.className = 'crop mainContent';
        };
        
        // скрываем все, чтобы при старте активировать только нужный
        cat.Show(false);
        
        MainPage.typeList.Add($listItem, {
            iid:id_numb,
            data:mainPageMenu[level_0],
            cat:cat,
            $catHandler:$catHandler,
            onclick : function(){
                // сбрасываем таймер в 0 чтобы нажатие сразу же повлекло за собой действия
                if ( type_focus_timer ) {
                    clearTimeout(type_focus_timer);
                    MainPage.typeList.typeListActionFlag = true;
                }
                cat.Activate(true);
                return false;
            }
        });
        id_numb++;
    }


	/**
	 * Hook method on focus item change at typeList
	 * @param {Node} item the new focused item
	 * @param {Node} previous the old focused item
	 */
	MainPage.typeList.onFocus = function ( item, previous ) {
		var cat_name = [lang.cat_video, lang.cat_music, lang.cat_images];
		echo('we are going to show ' + item.iid);
		MainPage.$catTypeName.innerHTML = cat_name[item.iid] + ':';
		item.cat.Show(true);
		if ( previous !== undefined && previous !== null ) {
			echo('we are going to hide ' + previous.iid);
			previous.cat.Show(false);
		}
		// задержка на втором меню при onFocus = 500. Изменяем её на 5, чтобы убрать эффект каскада задержек
		MainPage.typeList.actionFlag = true;
		MainPage.catOnFocus(item.cat.Current());
	};

	MainPage.typeList.onDeactivate = function () {
		MainPage.$typeList.className = 'typeLst notActive';
	};

	MainPage.typeList.onActivate = function () {
		MainPage.$typeList.className = 'typeLst';
	};


	/**
	 * Hook method on focus item change at categoryList
	 * @param {Node} item the new focused item
	 */
	MainPage.catOnFocus = function ( item ) {
		var delay_time = 500;
		echo('MainPage.categoryList.onFocus delay_time = 500;');
		if ( MainPage.typeList.actionFlag ) {
			delay_time = 0;
			echo('MainPage.categoryList.onFocus  NOW delay_time = 0;');
			MainPage.typeList.actionFlag = false;
		}
		if ( cat_focus_timer ) { clearTimeout(cat_focus_timer); }
		cat_focus_timer = setTimeout(function () {
			if ( item.news_data.news_cache[0].img_src ) {
				// для данной категории данные в кеше есть, - подставляем эти данные в элементы списка новинок
				MainPage.newsList.img1.firstChild.src = item.news_data.news_cache[1].img_src;
				MainPage.newsList.img2.firstChild.src = item.news_data.news_cache[2].img_src;
				MainPage.newsList.img3.firstChild.src = item.news_data.news_cache[3].img_src;
				MainPage.newsList.img0.firstChild.src = item.news_data.news_cache[0].img_src;

				MainPage.newsList.img0.news_link = item.news_data.news_cache[0].news_link;
				MainPage.newsList.img1.news_link = item.news_data.news_cache[1].news_link;
				MainPage.newsList.img2.news_link = item.news_data.news_cache[2].news_link;
				MainPage.newsList.img3.news_link = item.news_data.news_cache[3].news_link;
				echo('add news from cache');
			} else {
				// переменная, содержащая ссыылку на текущий элемент - используется в обработке после получения ajax
				MainPage.currData = item;
				// данных в кеше нет, шлем запрос с последующей обработкой и заполнением в парсере
				ajax('get', exURL + '/rss/' + item.news_data.rss, MainPage.rssParser, {charset: 'utf-8'}, 'xml');
			}
		}, delay_time);
	};
    
    
    // список новых фильмов
    MainPage.newsList = new CScrollList(MainPage);
    MainPage.newsList.Init(MainPage.handleInner.querySelector('#MainPage .content .rightContent .cslist-main'));
    // навешиваем активацию элемента при наведении на него мыши
    MainPage.newsList.handleInner.onmouseover = function(){
        if(!MainPage.newsList.isActive){
            echo('onMouseOver->MainPage.newsList.Activate(true);');
            MainPage.newsList.Activate(true);
        }
    };
    
    // обработка нажатия на новинку - с последующим определением является ли новинка каталогом либо обьектом
    MainPage.newsList.onClickFunc = function(){
        echo('MainPage.newsList.onClickFunc');
        var  data = {
            type        : WHAT_IS_THIS_TYPE,
            title       : lang.news_brcr,
            img_src     : '',
            addtime     : '',
            url         : MainPage.newsList.Current().news_link,
            iid         : ''
        };
        MainPage.LastActComp = MainPage.newsList;
        MainPage.messageHint = new CModalHint(MainPage, lang.loading);
        CSListManager.Open(data);
    };

    
    // создание и предварительное заполнение новинок
    for(var content_type_level in mainPageMenu){
        for(var cats_level in mainPageMenu[content_type_level]){
            for(var i=0;i<4;i++){
                mainPageMenu[content_type_level][cats_level].news_cache[i] = {img_src:'',news_link:''};
            }
        }
    }
    MainPage.newsList.Clear();
    MainPage.newsList.img0 = MainPage.newsList.Add(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc}), { row:0, column:0, news_link:'#' });
    MainPage.newsList.img1 = MainPage.newsList.Add(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc}), { row:0, column:1, news_link:'#' });
    MainPage.newsList.img2 = MainPage.newsList.Add(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc}), { row:1, column:0, news_link:'#' });
    MainPage.newsList.img3 = MainPage.newsList.Add(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc}), { row:1, column:1, news_link:'#' });
    /*  
    MainPage.newsList.handleInner.elchild(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc,className:'img0'}), { row:0, column:0, news_link:'#' });
    MainPage.newsList.handleInner.elchild(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc,className:'img1'}), { row:0, column:1, news_link:'#' });
    MainPage.newsList.handleInner.elchild(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc,className:'img2'}), { row:1, column:0, news_link:'#' });
    MainPage.newsList.handleInner.elchild(element('img', { src:'img/'+screen.height+'/ex_ua_noposter.png',onclick:MainPage.newsList.onClickFunc,className:'img3'}), { row:1, column:1, news_link:'#' });
      */  
    // потемнение курсора при деактивации списка
    MainPage.newsList.onDeactivate = function(){
          MainPage.handleInner.querySelector('.content .rightContent').className = 'crop rightContent notActive';
          echo('MainPage.newsList.onDeactivate');
    };
    MainPage.newsList.onActivate = function(){
        MainPage.handleInner.querySelector('.content .rightContent').className = 'crop rightContent';
    };
    
    //    строка поиска и хлебных крошек
    MainPage.SearchBar = new CSearchBar(MainPage);
    MainPage.SearchBar.Init('img/' + screen.height, MainPage.handleInner.querySelector('#MainPage .header .csbar'), lang.lang_default_hint);
    
 
    /**
     * запуск поиска
     * @param {String} value введенный поисковый запрос
     */
	MainPage.SearchBar.onSearch = function ( value ) {
		echo(value, 'MainPage.SearchBar.onSearch');
		var data = {
			title : value,
			value : encodeURI(value),
			url   : type_info[ROOT_GLOBAL_SEARCH].url_start + encodeURI(value),
			type  : ROOT_GLOBAL_SEARCH
		};
		ListPage.start(data);
		MainPage.LastActComp = MainPage.typeList.Current().cat;
	};


	/**
     *  Get search value if it was changed
     *  @param hint {String} value search value
     */
	MainPage.SearchBar.onHint = function ( hint ) {
		echo('onHint-> hint =->' + hint + '<-');
		// посчитаем количество пробелов в начале строки
		var diff = hint.length - hint.trimLeft().length,
			clean_hint = hint.trimLeft();
		if ( !hint ) {
			echo('empty value');
			MainPage.SearchBar.SetHint('');
		} else {
			//  запрос на получение подсказок
			ajax('get', 'http://www.ex.ua/r_search_hint?s=' + encodeURI(clean_hint), function ( html ) {
				echo('html=\n' + html);
				var sug_arr = html.split('\n');
				if ( sug_arr && sug_arr[0] && clean_hint == sug_arr[0].substr(0, clean_hint.length) ) {
					// меняем на первый подходящий результат с учетом пробелов
					// проверяем соответствие языка по соответствию начала hint и ключевика
					MainPage.SearchBar.SetHint(hint.substr(0, diff) + sug_arr[0]);
				} else {
					MainPage.SearchBar.SetHint('');
				}
			});
		}
	};

	// при вызове поиска - открытие клавиатуры
	MainPage.SearchBar.onActivate = function () { gSTB.EnableVKButton(true); };

	// при деактиваци поиска - активация элемента среднего списка
	MainPage.SearchBar.onDeactivate = function () {
		gSTB.EnableVKButton(false);
		setTimeout(function () {
			MainPage.typeList.Current().cat.Activate(true);
		}, 5)
	};


	/**
	 * очистка содержимого кеша (чистятся данные "новинок")
	 */
	MainPage.actionRefresh = function () {
		echo('start refresh mainPageMenu.video[0].news_cache[0].img_src=' + mainPageMenu.video[0].news_cache[0].img_src);
		for ( var content_type_level in mainPageMenu ) {
			for ( var cats_level in mainPageMenu[content_type_level] ) {
				for ( var i = 0; i < 4; i++ ) {
					mainPageMenu[content_type_level][cats_level].news_cache[i].news_link = '';
					mainPageMenu[content_type_level][cats_level].news_cache[i].img_src = '';
				}
			}
		}
		// убираем задержку - она ни к чему
		MainPage.typeList.actionFlag = true;
		MainPage.typeList.Current().cat.onFocus(MainPage.typeList.Current().cat.Current());
		echo('refresh is over mainPageMenu.video[0].news_cache[0].img_src=' + mainPageMenu.video[0].news_cache[0].img_src);
	};

	MainPage.actionLang = function () {
		echo('MainPage.actionLang');
		new CModalSelectLang(MainPage, lang.changeLang, '', {});
	};

	// панель кнопок
	MainPage.BPanel = new CButtonPanel(MainPage);
	MainPage.BPanel.Init('img/' + screen.height, MainPage.handleInner.querySelector('#MainPage .footer div.cbpanel-main'));
	MainPage.BPanel.Add(KEYS.REFRESH, 'ico_refresh.png', lang.refresh, MainPage.actionRefresh);
	MainPage.BPanel.Add(KEYS.F3, 'ico_f3.png', lang.changeLang, MainPage.actionLang);

	/**
	 * Парсер для составления списка фильмов/аудио/картинок.
	 * @param {String} data содержание страницы меню сайта (а именно - видео/картинки/аудио)
	 */
	MainPage.rssParser = function ( data ) {
		echo('rssParser');
		var place = MainPage.currData.news_data;
		echo('place = ' + place);
		// получение URL картинок из полученных дaнных и их кеширование
		for ( var i = 0; i < 4; i++ ) {
			// получаем из xml описание с ссылкой на картинку
			var img_src = data.getElementsByTagName('description').item(i + 1);
			var news_link = data.getElementsByTagName('link').item(i + 1) || '';
			if ( img_src == null || img_src.firstChild == null || news_link == '' ) {
				place.news_cache[i].img_src = 'img/' + screen.height + '/ex_ua_noposter.png?400';
			} else {
				news_link = news_link.firstChild.nodeValue;
				img_src = img_src.firstChild.nodeValue;
				echo('description->' + img_src);
				if ( img_src.indexOf("<img") < 0 || img_src.indexOf("gif") > 0 ) {
					place.news_cache[i].img_src = 'img/' + screen.height + '/ex_ua_noposter.png?400';
				} else {
					// вырезание URL из строки вида  <a href='http://www.ex.ua/view/4?r=2'><img src='http://fs181.www.ex.ua/show/2/2.jpg?200' width='135' height='200'></a>
					img_src = img_src.substring(img_src.indexOf("<img") + 10, img_src.indexOf("width"));
					img_src = img_src.substring(0, img_src.indexOf("?"));
					// сохраняем ссылки на картинки в кеш с указанием размера
					place.news_cache[i].img_src = img_src + '?400';
					echo('img mainPageMenu.video[category_index].news_cache[i].img_src=' + place.news_cache[i].img_src);
				}
			}
			// заполняем URL по которым можно перейти на новости
			place.news_cache[i].news_link = news_link;
		}
		// заполняем список новыми данными (картинки новых фильмов/музыки/фото) и ссылками на новинки
		var counter = 0;
		MainPage.newsList.Each(function ( item ) {
			// проверяем размер картинки
			checkForGiantImg(place.news_cache[counter].img_src, MAX_READABLE_IMAGE_WEIGHT, place.news_cache[counter], item.firstChild);
			item.news_link = place.news_cache[counter].news_link;
			echo('change cache elem');
			counter++;
		});
		// убрать загрузчик
		if ( $loadingScreen.style.display === 'block' ) {setTimeout(function () {$loadingScreen.style.display = 'none';}, 100)}
	};


	/**
	 * запуск первой страницы с первоначальным добавлением элементов и установкой фокуса
	 */
	(function () {
		MainPage.Show(true, null);
		echo('MainPage.start()');
		MainPage.newsList.onDeactivate();   // активировать меню типа контента (то что слева)
		MainPage.typeList.onFocus(MainPage.typeList.FindOne());   // показать данные первого типа (т.е заполнение значениями по умолчанию)
		MainPage.typeList.Activate(true, true);
	})();
};


MainPage.onShow = function(){
    // если в поиске что-то вводили то при возврате на главную страницу, сменить значение на стандартную подсказку
    if(MainPage.SearchBar.items.hint.value && MainPage.SearchBar.items.hint.value !== lang.lang_default_hint){
        MainPage.SearchBar.items.hint.value = '';
    }
};

/**
 * Events handler entry point
 * @param {Event} event global event object
 */
MainPage.EventHandler = function ( event ) {
	echo('MainPage.EventHandler->activation status:');
	echo('-typeList:  ' + MainPage.typeList.isActive);
	echo('-newsList:  ' + MainPage.newsList.isActive);
	echo('-SearchBar: ' + MainPage.SearchBar.isActive);

	if ( event.code === KEYS.REFRESH ) { MainPage.actionRefresh(); }
	if ( event.code === KEYS.F3 ) { MainPage.actionLang(); }

	if ( MainPage.SearchBar.isActive ) {
		switch ( event.code ) {
			case KEYS.RIGHT:
				// дополнение написанного в поисковой строке из подсказки
				echo('MainPage.SearchBar.items.hint.value=' + MainPage.SearchBar.items.hint.value);
				echo('MainPage.SearchBar.items.text.value=' + MainPage.SearchBar.items.text.value);
				setTimeout(function () {
					if ( MainPage.SearchBar.items.hint.value != '' ) {
						MainPage.SearchBar.items.text.value = MainPage.SearchBar.items.hint.value;
						echo('new MainPage.SearchBar.items.text.value' + MainPage.SearchBar.items.text.value);
					}
				}, 350);
				break;
			case KEYS.DOWN:
			case KEYS.F2:
			case KEYS.EXIT:
				echo('SearchBar down)))');
				MainPage.typeList.Current().cat.Activate(true);
				event.preventDefault();
				break;
		}
	} else if ( MainPage.typeList.isActive ) {
		switch ( event.code ) {
			case KEYS.PAGE_DOWN:
			case KEYS.PAGE_UP:
				break;
			case KEYS.LEFT:
				break;
			case KEYS.OK:
			case KEYS.RIGHT:
				echo('To the right typeList)))');
				MainPage.typeList.Current().cat.Activate(true);
				break;
			case KEYS.EXIT:
			case KEYS.BACK:
				MainPage.actionExit();
				break;
			case KEYS.INFO:
				var refererPage = (_GET['referrer'] || '').indexOf('online-media.infomir.com.ua') === -1 ? '' : '<br>Source: Online media';
				new CModalAlert(MainPage, 'Info', 'Program version: ' + version + refererPage, lang.back_action, function () {});
				break;
			default:
				MainPage.typeList.EventHandler(event);
		}
	} else if ( MainPage.typeList.Current().cat.isActive ) {
		echo('-MainPage.typeList.Current().cat.isActive true');
		var curr_cat = MainPage.typeList.Current().cat;
		switch ( event.code ) {
			case KEYS.LEFT:
				echo('To the left categoryList)))');
				MainPage.typeList.Activate(true);
				break;
			case KEYS.RIGHT:
				echo('To the right categoryList)))');
				MainPage.newsList.Activate(true);
				break;
			case KEYS.OK:
				echo('keys->ok');
				curr_cat.Current().onclick();
				break;
			case KEYS.EXIT:
			case KEYS.BACK:
				MainPage.actionExit();
				break;
			default:
				curr_cat.EventHandler(event);
		}
	} else if ( MainPage.newsList.isActive ) {
		var current_news = MainPage.newsList.Current();
		switch ( event.code ) {
			case KEYS.PAGE_DOWN:
			case KEYS.PAGE_UP:
				break;
			case KEYS.LEFT:
				echo('To the LEFT typeList))) current_news.column=' + current_news.column);
				if ( current_news.column ) {MainPage.newsList.Focused(MainPage.newsList.Next('', true), true);} else {
					MainPage.typeList.Current().cat.Activate(true);
				}
				break;
			case KEYS.RIGHT:
				echo('To the RIGHT typeList))) current_news.column=' + current_news.column);
				if ( !current_news.column ) {
					MainPage.newsList.Focused(MainPage.newsList.Next(), true);
					echo('r->next');
				}
				break;
			case KEYS.UP:
				echo('To the UP typeList))) current_news.row=' + current_news.row);
				if ( current_news.row ) {MainPage.newsList.Focused(MainPage.newsList.Next('', true, 2), true);}
				break;
			case KEYS.DOWN:
				echo('To the DOWN typeList))) current_news.row=' + current_news.row);
				if ( !current_news.row ) {MainPage.newsList.Focused(MainPage.newsList.Next('', false, 2), true);}
				break;
			case KEYS.OK:
				MainPage.newsList.onClickFunc();
				break;
			case KEYS.EXIT:
			case KEYS.BACK:
				MainPage.actionExit();
				break;
			default:
				MainPage.newsList.EventHandler(event);
		}
	}

	if ( event.code === KEYS.F2 ) { MainPage.SearchBar.Activate(true); }
};


/*
 * выход их приложения на то место, с которого приложение запустили
 */
MainPage.actionExit = function () {
	MainPage.exitMessage = new CModalConfirm(MainPage, lang.exit, lang.close_question, lang.cancel, function () {
		MainPage.exitMessage.Show(false);
	}, LANG_MEDIA_DEFAULT_OK, function () {
		MediaPlayer.end();
		window.location = decodeURIComponent(_GET['referrer']);
	});
};



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////  SECOND PAGE /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/*
*  Инициализация второй страницы
*/
ListPage.onInit = function(){
    /** link to the object for limited scopes */
    this.id = 1; // оставлено для совместимости с плеером, обозначает id вызывающей плеер страницы
    var self = this;
    // количество элементов которое будет добавляться на страницу за раз (т.е. браться из ajax ответа)
    this.ItemsPerPage = 32;
    // используем собственные иконки для сообщений
    CMODAL_IMG_PATH = 'img/'+screen.height;
    // перекрывающее ListPage сообщение о содержании фильма
    this.$infoMessage = ListPage.handleInner.querySelector('.infoMessage');
    this.Info.Init(ListPage.$infoMessage);
    this.Info.Show(false, false);
    
    // инициализируем меню предварительного просмотра
    this.Preview.Init(this.handle.querySelector('.rightContent .info'));
    this.Preview.Show(false, false);
    ListPage.Preview.body    = ListPage.Preview.handle.querySelector('.view');
    ListPage.Preview.file    = ListPage.Preview.handle.querySelector('.file');
    
    this.Preview.showAttr    = "table-cell";
    this.Preview.infoIcon    = element('img', {align: 'left', src: 'img/' + screen.height + '/ico_finfo.png'});
    // список действий при открытиии определенных типов узлов    
    this.Preview.action_info = {};
    this.Preview.action_info[ROOT_VIDEO]             = this.Preview.infoFolder;
    this.Preview.action_info[ROOT_AUDIO]             = this.Preview.infoFolder;
    this.Preview.action_info[ROOT_IMAGE]             = this.Preview.infoFolder;
    this.Preview.action_info[TYPE_BACK]              = this.Preview.infoBack;
    this.Preview.action_info[ROOT_GLOBAL_SEARCH]     = this.Preview.infoFolder;
    this.Preview.action_info[LOCAL_SEARCH_ROOT]      = this.Preview.infoFolder;        
    this.Preview.action_info[TYPE_NONE]              = this.Preview.infoFolder;
    this.Preview.action_info[MEDIA_OBJECT]           = this.Preview.infoFolder;
    this.Preview.action_info[MEDIA_FOLDER]           = this.Preview.infoFolder;
    this.Preview.action_info[RADIO_OBJECT]           = this.Preview.infoFolder;
    this.Preview.action_info[ACTORS_FOLDER]          = this.Preview.infoFolder;
    this.Preview.action_info[GLOBAL_SEARCH_FOLDER]   = this.Preview.infoFolder;
    this.Preview.action_info[ORPHAN_FOLDER]          = this.Preview.infoFolder;        
    this.Preview.action_info[GLOBAL_SEARCH_REFRESH]  = this.Preview.infoFolder;
    this.Preview.action_info[LOCAL_SEARCH_REFRESH]   = this.Preview.infoFolder;
    this.Preview.action_info[WHAT_IS_THIS_TYPE]      = this.Preview.infoFolder;
    this.Preview.action_info[ROOT_NEWS]              = this.Preview.infoFolder;
    this.Preview.action_info[MEDIA_TYPE_VIDEO]       = this.Preview.infoFile;
    this.Preview.action_info[MEDIA_TYPE_AUDIO]       = this.Preview.infoFile;
    this.Preview.action_info[MEDIA_TYPE_IMAGE]       = this.Preview.infoFile;
    //this.Preview.action_info[MEDIA_TYPE_IMAGE]       = this.Preview.infoFile;
    
    // инициализация хлебных крошек
    this.breadCrumb = new CBreadCrumb(ListPage);
    this.breadCrumb.Init('img/'+screen.height,ListPage.handleInner.querySelector('.header .cbcrumb'));
    this.breadCrumb.showAttr = 'table-cell';
    // количество нескрываемых крошек слева при большом пути
    this.breadCrumb.litems = 2;
    this.breadCrumb.Show(true);
	

    // инициализация менеджера CScrollList, позволяющего работать с уровнями вложенности и узлами
    CSListManager.init(document.getElementById('manager'), ListPage);

    /**
     * list of events associated with player
     * @type {Object}
     */
    ListPage.subscribeEvents = {};
    
    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_START] = function () {
        var file = CSListManager.Current().Current().data;
        echo('MediaPlayer.EVENT_START');
        ListPage.Preview.player.className = file.type === MEDIA_TYPE_AUDIO ? 'player audio' : 'player play';
        if ( ListPage.Preview.body.info && ListPage.Preview.body.info.time === undefined && MediaPlayer.totalTime > 0 ) {
            var time = MediaPlayer.parseTime(MediaPlayer.totalTime);
            ListPage.Preview.body.info.time = element('div', { className: 'text' }, [LANG_MEDIA_MENU_INFO_DURATION, element('span', { className: 'txt' }, time.hour + ':' + time.min + ':' + time.sec)]);
            elchild(ListPage.Preview.body.info, ListPage.Preview.body.info.time);
        }
    };

    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_STOP] = function () {
        echo('MediaPlayer.EVENT_STOP');
                if(ListPage.isVisible && ListPage.Preview.player){
                ListPage.Preview.player.className = 'player stop';
                ListPage.Preview.setProgress(0);}
    };

    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_PAUSE] = function ( state ) {
        echo('MediaPlayer.EVENT_PAUSE');
        var file = CSListManager.Current().Current().data;
        // preview available
            if ( state ) {
                ListPage.Preview.player.className = 'player stop';
            } else {
                ListPage.Preview.player.className = file.type === MEDIA_TYPE_AUDIO ? 'player audio' : 'player play';
            }
    };

    ListPage.subscribeEvents[MediaPlayer.EVENT_PROGRESS] = function ( position ) {
        echo('MediaPlayer.EVENT_PROGRESS');
                ListPage.Preview.setProgress(position);
    };

    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_ERROR] = function ( ) {
            echo('MediaPlayer.EVENT_ERROR');
            // preview and player window are available
            if ( ListPage.Preview.player ) {
                    ListPage.Preview.player.className = 'player stop';
                    ListPage.Preview.setProgress(0);
            }
            // show info
            new CModalHint(currCPage,playingError,3000);
    };
        
    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_OK] = function ( ) {
        echo('MediaPlayer.EVENT_OK');
        ListPage.Preview.info(CSListManager.Current().Current().data);
        var file = CSListManager.Current().Current().data;
            ListPage.Preview.player.className = file.type === MEDIA_TYPE_AUDIO ? 'player audio' : 'player play';
            if ( gSTB.IsPlaying() ) MediaPlayer.runner.start();
            MediaPlayer.Show(false);
            MediaPlayer.changeScreenMode(false);
    };
    
    /** player event hook */
    ListPage.subscribeEvents[MediaPlayer.EVENT_EXIT] = function ( ) {
        echo('MediaPlayer.EVENT_EXIT');
        ListPage.Preview.setProgress(0);
        ListPage.Preview.player.className = 'player stop';
        ListPage.Preview.info(CSListManager.Current().Current().data);
    };


    /**
     * заполнение списка папок и обьектов
     * @param {Object} obj обьект с необходимыми для заполнения правого меню и onClick() полями
     */
    ListPage.addListItems = function(obj){
        var j = 0, current = CSListManager.Current(),data = {}, somelistItem;
        echo('ListPage.addListItems');
        for(var i in obj){
            echo('list item add_'+j+'= <'+obj[i].title+'>');
            if(current.handleInner.page_index == 0 && j == 0){
                data = {
                    title       : '',
                    img_src     : '',
                    addtime     : '',
                    markable    : false,
                    marked      : false,
                    url         : '',
                    iid         : '',
                    type        : TYPE_BACK
                };
                // добавим элемент выхода на более верхний уровень
                somelistItem = element('div',{ className: 'back' },'..');
                current.Add(somelistItem, {
                    data    : data,
                    onclick : function(){
                        echo('onClick-> back');
                        setTimeout(function(){ 
                            var data = {type:TYPE_BACK};
                            CSListManager.Open(data);
                        }, 5); 
                        return false;
                    }
                });
            }
            
            data = {};
            // ВСЯКАЯ ЛОГИКА ОПРЕДЕЛЕНИЯ ТИПОВ ОТКРЫТИЯ 
            // обычные папка и обьект
            data.type  = obj[i].cat ? MEDIA_FOLDER : MEDIA_OBJECT;
            // открытие результатов поиска
            if(CSListManager.parentData.type == ROOT_GLOBAL_SEARCH || CSListManager.parentData.type == GLOBAL_SEARCH_REFRESH 
                || CSListManager.parentData.type == LOCAL_SEARCH_ROOT || CSListManager.parentData.type == LOCAL_SEARCH_REFRESH){
                data.type  = obj[i].cat ? GLOBAL_SEARCH_FOLDER : MEDIA_OBJECT;
            }
            // открытие радио
            if(CSListManager.Current().handleInner.data.url.indexOf(type_info[RADIO_OBJECT].id) !== -1){
                data.type = RADIO_OBJECT;
                // It's strange but radio doesn't have add_time value.
                obj[i].add = '';
            }
            // открытие корневой папки актеров
            if(CSListManager.Current().handleInner.data.url.indexOf(type_info[ACTORS_FOLDER].id) !== -1){
                data.type = ACTORS_FOLDER;
            }
            var title_text   = obj[i].title;
            // сбрасываем символы вида "&qute;" в обычный вид
            var elem         = element('span', {}, '');
            
            elem.innerHTML   = title_text;
            title_text       = elem.innerHTML;
            var css_class    = obj[i].cat ? 'cat' : 'obj';
            data.img_src     = obj[i].img;
            data.addtime     = obj[i].add;
            data.url         = obj[i].url;
            data.title       = title_text;
            data.markable    = true;
            data.iid         = current.handleInner.page_index*ListPage.ItemsPerPage+j;
            
            somelistItem = element('div',{
                className : css_class, 
                innerHTML : title_text
            });
            current.Add(somelistItem, {
                data    : data,
                onclick : function(){
                    echo(this.data,'onClick->EnterData=');
                    CSListManager.Open(this.data);
                    return false;
                }
            });
        // проверка на превышения картинкой определенного размера, а значит - и верояных тормозов при открытии    
        var check_data = CSListManager.Current().handleInner.children[current.handleInner.page_index*ListPage.ItemsPerPage+j+1].data;
        echo('*******url for check '+check_data.img_src);
        checkForGiantImg(check_data.img_src, MAX_READABLE_IMAGE_WEIGHT, check_data );
          j++;
        }
        if(obj.length>0){
            // считаем сколько нам страниц пришло и увеличиваем переменную, иначе - стопорим добавление
            current.handleInner.page_index += Math.ceil(j/ListPage.ItemsPerPage);
            current.handleInner.page_length = current.handleInner.page_index;
            echo('new page_index='+current.handleInner.page_index+' was added pages='+Math.ceil(j/ListPage.ItemsPerPage));
        }else{
            current.handleInner.page_length = current.handleInner.page_index-1;
            echo('end page_index='+current.handleInner.page_index);
            // в пустой папке показываем только back
            if(current.handleInner.page_index == 0){
                data = {
                    title       : '',
                    img_src     : '',
                    addtime     : '',
                    markable    : false,
                    marked      : false,
                    url         : '',
                    iid         : '',
                    type        : TYPE_BACK
                };
                // добавим элемент выхода на более верхний уровень
                somelistItem = element('div',{ className: 'back' },'..');
                current.Add(somelistItem, {
                    data    : data,
                    onclick : function(){
                        echo('onClick-> back');
                        setTimeout(function(){ 
                            var data = {type:TYPE_BACK};
                            CSListManager.Open(data);
                        }, 5); 
                        return false;
                    }
                });
            }
        }
        current.Activate(true);
        CSListManager.Current().onFocus(CSListManager.Current().Current(),true);
    };
    
    
    
    /**
     * перерисовка правого меню (т.е. постера и описания) при получении соотв. элементом левого меню фокуса
     * @param {Object} current текущий элемент
	 *  @param {Object} previous предыдущий элемент
     */
    ListPage.onFocusAction = function(current, previous){
        echo('list on focus');
        if(ListPage.isVisible){
            echo('ListPage.isVisible');
            // stop current play
            MediaPlayer.end();
            // переустанавливаем счетчик окна
            if ( cat_focus_timer ) {
                clearTimeout(cat_focus_timer);
            }
            cat_focus_timer = setTimeout(function(){
                // show info in preview block
                try{
                    ListPage.Preview.info(CSListManager.Current().Current().data);
                } catch(e){
                    echo('wrong info update time! '+e);
                }
                cat_focus_timer = 0;
            },500);
        }
    };
      
       
    /**
     * строка поиска и хлебных крошек
     */    
    this.SearchBar = new CSearchBar(ListPage);
    this.SearchBar.Init('img/' + screen.height, ListPage.handleInner.querySelector('.header .csbar'), lang.list_default_hint);
    this.SearchBar.showAttr = 'table-cell';

    /**
     * запуск поиска
     * @param {String} value введенный поисковый запрос
     */
    this.SearchBar.onSearch = function ( value ) {
        echo(value, 'ListPage.SearchBar.onSearch');
        var data   = {};
        data.title = value;
        data.value = encodeURI(value);
        // запуск поиска из разных мест приложения (локальный, глобальный, повторный)
        if(CSListManager.parentData.type == ROOT_GLOBAL_SEARCH || CSListManager.parentData.type == GLOBAL_SEARCH_REFRESH){
            data.url=type_info[ROOT_GLOBAL_SEARCH].url_start + data.value;
            data.type = GLOBAL_SEARCH_REFRESH; 
        } else
        if(CSListManager.parentData.type == LOCAL_SEARCH_REFRESH || CSListManager.parentData.type == LOCAL_SEARCH_ROOT){
            echo(value, 'MainPage.SearchBar.onSearch');
            data.url=type_info[LOCAL_SEARCH_ROOT].url_start + CSListManager.src[0].handleInner.data.real_cat_id + type_info[LOCAL_SEARCH_ROOT].url_end + data.value;
            data.type = LOCAL_SEARCH_REFRESH; 
        } else {
            echo(value, 'MainPage.SearchBar.onSearch');
            data.url=type_info[LOCAL_SEARCH_ROOT].url_start + CSListManager.src[0].handleInner.data.real_cat_id + type_info[LOCAL_SEARCH_ROOT].url_end + data.value;
            data.type = LOCAL_SEARCH_ROOT; 
            CSListManager.mode = LSEARCH_MODE;
        }
        CSListManager.Open(data);
        ListPage.breadCrumb.Show(true);
    };
    
    
    /**
     *  Get search value if it was changed
     *  @param hint {String} value search value
     */
    this.SearchBar.onHint = function(hint){
        // посчитаем количество пробелов в начале строки
        var diff = hint.length - hint.trimLeft().length;
        var clean_hint=hint.trimLeft();
        echo('onHint-> hint =->' + hint+'<-');
        if(hint == ''){
            echo('empty value');
            ListPage.SearchBar.SetHint('');
        } else {
            //  запрос на получение подсказок
            ajax('get','http://www.ex.ua/r_search_hint?s='+encodeURI(clean_hint), function(html){
                echo('html=\n'+html);
                if(html != ''){
                    // меняем на первый подходящий результат с учетом пробелов
                    var sug_arr = html.split('\n');
                    // проверяем соответствие языка по соответствию начала hint и ключевика
                    if(clean_hint == sug_arr[0].substr(0,clean_hint.length)){
                        MainPage.SearchBar.SetHint(hint.substr(0,diff)+sug_arr[0]);
                    }
                    else{
                        MainPage.SearchBar.SetHint('');
                    }
                } else { 
                    ListPage.SearchBar.SetHint(''); 
                }
            });
        }
    };
    
    
    ListPage.SearchBar.onActivate = function () {
        ListPage.breadCrumb.Show(false);
        gSTB.EnableVKButton(true); 
    };
    
    
    ListPage.SearchBar.onDeactivate = function(){
        ListPage.breadCrumb.Show(true);
        gSTB.EnableVKButton(false); 
        setTimeout(function(){
            CSListManager.Current().Activate(true);
        },5) 
    };
    
    
    // панель кнопок
    ListPage.BPanel = new CButtonPanel(ListPage);
    ListPage.BPanel.Init('img/' + screen.height, ListPage.handleInner.querySelector('.footer div.cbpanel-main'));
    ListPage.BPanel.btnMenu    = ListPage.BPanel.Add(KEYS.MENU,    'ico_menu.png',lang.main_menu, ListPage.actionMainMenu );
    ListPage.BPanel.btnF3      = ListPage.BPanel.Add(KEYS.F3,    'ico_f3.png',    lang.mark, ListPage.actionF3 );
    ListPage.BPanel.btnOnINFO  = ListPage.BPanel.Add(KEYS.INFO,    'ico_info.png',lang.film_description, ListPage.actionINFO );
    ListPage.BPanel.btnOffINFO = ListPage.BPanel.Add(KEYS.INFO,    'ico_info.png',lang.film_description_off, ListPage.actionBack );
	ListPage.BPanel.btnOKINFO = ListPage.BPanel.Add(KEYS.OK,    'ico_ok.png',lang.toFiles, ListPage.objShortCut );
    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, true);
    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, true);
    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
	ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOKINFO, true);

    /**
     * заполнение списка содержимого обьекта (картинки, видео, аудио, - всё кроме папок)
     * @param {Object} obj содержит необходимые для заполнения правого меню и onClick() действия поля 
     */
    ListPage.addObjectItems = function(obj){
        echo('ListPage.addObjectItems');
        var i = 0,somelistItem,data = {}, media_types = [MEDIA_TYPE_VIDEO,MEDIA_TYPE_AUDIO,MEDIA_TYPE_IMAGE];
        var current = CSListManager.Current();
        for(var cont_type in obj){
            if(i == 0) {
                data.img_big        = '';
                data.img_prev       = '';
                data.title          = '';
                data.url            = '';
                data.markable       = false;
                data.marked         = false;
                data.type           = TYPE_BACK;
                // добавим элемент выхода на более верхний уровень
                somelistItem = element('div',{ className: 'back' },'..');
                current.Add(somelistItem, {
                    data    : data,
                    focused : true,
                    onclick : function(){
                        echo('onClick-> back');
                        setTimeout(function(){ 
                            var data = {type:TYPE_BACK};
                            CSListManager.Open(data);
                        }, 5); 
                        return false;
                    }
                });
            }
            for(var item in obj[cont_type]){
                // сбрасываем символы вида "&qute;" в обычный вид
                var elem = element('span', {}, '');
                elem.innerHTML = obj[cont_type][item].name;
                obj[cont_type][item].name = elem.innerHTML;
                data = {
                    type           : media_types[i],
                    markable       : true,
                    marked         : false,
                    img_big        : obj[cont_type][item].img_big,
                    img_prev       : obj[cont_type][item].img_prev,
                    name           : obj[cont_type][item].name,
                    title          : obj[cont_type][item].name,
                    url            : obj[cont_type][item].url,
                    size_in_bytes  : obj[cont_type][item].size_in_bytes,
                    resolution     : obj[cont_type][item].resolution
                };
                // иконка радио
                if(CSListManager.parentData.type == RADIO_OBJECT){
                    obj[cont_type][item].type = 'stream';
                }
                somelistItem = element('div',{
                    className: obj[cont_type][item].type,
                    innerHTML: obj[cont_type][item].name
                });
                
                current.Add(somelistItem, {
                    data:data,
                    onclick : function(){
                        echo('PLAY data.type='+data.type);
                        CSListManager.Open(this.data);
                        return false;
                    }
                });
            }
            i++;
        }
        //  механизм притемнения новинок при переходе между категориями
        //  setTimeout(function(){
        //  echo('___CLOSE showLoadinMess id='+ListPage.messageHint+' is visible='+ListPage.messageHint.isVisible);
            if(ListPage.messageHint){ListPage.messageHint.Show(false);}
            // если внутри ничего нет, то показываем пустую папку с back
            if(obj.length == 0){
                data.img_big        = '';
                data.img_prev       = '';
                data.title          = '';
                data.url            = '';
                data.markable       = false;
                data.marked         = false;
                data.type           = TYPE_BACK;
                // добавим элемент выхода на более верхний уровень
                somelistItem = element('div',{ className: 'back' },'..');
                current.Add(somelistItem, {
                    data    : data,
                    focused : true,
                    onclick : function(){
                        echo('onClick-> back');
                        setTimeout(function(){ 
                            var data = {type:TYPE_BACK};
                            CSListManager.Open(data);
                        }, 5); 
                        return false;
                    }
                });
            }

            CSListManager.Current().Activate(true);
            CSListManager.Current().onFocus(CSListManager.Current().Current(),true);
//        },100)
    };
    

/**
* заполнение страницы ListPage при её показе
* @param {Object} data данные необходимые для запуска страницы
*/ 
    ListPage.start = function(data){
        echo('we are starting page 2 with type '+data.type);
		CSListManager.Open(data);
        ListPage.Show( true, MainPage );
    };
    
    // инициализировать окно preview 
    ListPage.Preview.playerRect = ListPage.Preview.getRect();
    ListPage.Preview.Show(true, false);
};

ListPage.onShow = function () {
	echo('ListPage onShow');
	if ( CSListManager.src.length !== 0 ) { CSListManager.Current().Activate(); }
	// clear search hint if this is not GSearch
	if ( CSListManager.parentData.type !== ROOT_GLOBAL_SEARCH ) {
		if ( this.SearchBar.items.text.value || this.SearchBar.items.hint.value && this.SearchBar.items.hint.value !== lang.list_default_hint ) {
			this.SearchBar.items.hint.value = lang.list_default_hint;
			this.SearchBar.items.text.value = '';
		}
	} else {
		this.SearchBar.items.hint.value = '';
		this.SearchBar.items.text.value = CSListManager.parentData.title;
	}
	// init player preview window
	MediaPlayer.setCoord(ListPage.Preview.playerRect.x, ListPage.Preview.playerRect.y, ListPage.Preview.playerRect.a, ListPage.Preview.playerRect.b);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_START);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_STOP);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_PAUSE);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_PROGRESS);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_ERROR);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_OK);
	MediaPlayer.Subscribe(ListPage, MediaPlayer.EVENT_EXIT);
};


/*
 * выход из приложения на место с которого его запустили
 */
ListPage.actionExit = function () {
    ListPage.exitMessage = new CModalConfirm(currCPage, lang.exit, lang.close_question, lang.cancel, function(){
        ListPage.exitMessage.Show(false);
    }, lang.close, function(){
          MediaPlayer.end();
          window.location = decodeURIComponent(_GET['referrer']);
    });
};

/**
 * выход на предыдущую страницу/переход по спискам текущей страницы
 */
ListPage.actionBack = function(){
    echo('ListPage action Back');
    if(!ListPage.Info.isActive){
        var data = {type:TYPE_BACK};
        CSListManager.Open(data);
    }else{
        // если мы находимся в режиме просмотра информации о фильме
        if(ListPage.Info.f3_visibility_flag === true){
            // возвращаем старое состояние кнопки F3
            ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, false);
            ListPage.Info.f3_visibility_flag = false;
        }
        ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, false);
        ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
		ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOKINFO, true);
        ListPage.Info.Show(false, true);
        CSListManager.Current().Activate(true);
    }
};

/**
 * Shortcut from info page to files page (object)
 */
ListPage.objShortCut = function () {
	var data = CSListManager.Current().Current().data,
		type = data.type;
	ListPage.actionBack();
	if ( type !== MEDIA_TYPE_VIDEO && type !== MEDIA_TYPE_AUDIO && type !== MEDIA_TYPE_IMAGE ) {
		CSListManager.Open(data);
	}
	//ListPage.Info.htmlData            // TODO: use this data to reduce 1 extra request
};

/**
 * быстрый выход на главную страницу
 */
ListPage.actionMainMenu = function(){
    // выход на предыдущую страницу
    ListPage.SearchBar.Show(true,false);
    MediaPlayer.end();        
    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, false);
    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
	ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOKINFO, true);
//    ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnEXIT, false);
    ListPage.Info.Show(false, false);
    // clear all children and breadcrumbs
    while (CSListManager.src.length > 0){
        ListPage.breadCrumb.Pop();
        CSListManager.src.length--;
    }
    while (CSListManager.handleInner.lastChild){
        CSListManager.handleInner.removeChild(CSListManager.handleInner.lastChild);
    }
    echo('---PAGE CHANGE---');
    ListPage.Show(false);
    MainPage.LastActComp.Activate(true,true);
};


/**
 * mark/unmark the current item
 * @param {Boolean} [move=true] move to the next after marking
 * Global key
 */
ListPage.actionF3 = function(move){
    echo('ListPage action F3');
    // check if action is permitted
    if ( !ListPage.BPanel.btnF3.data.hidden ) {
        // get affected item
        var item = CSListManager.Current().Current();
        if ( item.data.markable && CSListManager.Current().Marked(item, !item.marked) ) {
            // optional move to the next after marking
            if ( move !== false ) CSListManager.Current().Focused(CSListManager.Current().Next(), true);
        }
    }
};


/**
 * переход на страницу описания фильма
 */
ListPage.actionINFO = function(){
    echo('ListPage.actionINFO=>ajax='+CSListManager.Current().handleInner.data.url);
    var type = CSListManager.Current().Current().data.type;
    if(type !== MEDIA_TYPE_VIDEO && type !== MEDIA_TYPE_AUDIO && type !== MEDIA_TYPE_IMAGE ){
        ajax ( 'GET', CSListManager.Current().Current().data.url, htmlObjectInfoParser );
    }else{
        ajax ( 'GET', CSListManager.Current().handleInner.data.url, htmlObjectInfoParser );
    }
};


/**
 * Events handler entry point
 * @param {Event} event global event object
 */
ListPage.EventHandler = function ( event ) {
	// get real key code or exit
	if ( !eventPrepare(event) ) return;
	echo('ListPage.EventHandler');
	if ( ListPage.SearchBar.isActive ) {
		echo('SearchBar.event.code=' + event.code);
		switch ( event.code ) {
			case KEYS.RIGHT:
				echo('To the right SearchBar)))');
				if ( ListPage.SearchBar.items.hint.value !== '' ) {
					ListPage.SearchBar.items.text.value = MainPage.SearchBar.items.hint.value;
				}
				break;
			case KEYS.DOWN:
			case KEYS.EXIT:
				echo('SearchBar down)))');
				event.preventDefault();
				break;
			default:
				ListPage.SearchBar.EventHandler(event);
		}
	} else if ( CSListManager.Current().isActive ) {
		echo('CSListManager.Current().keyHandler=' + CSListManager.Current().isActive);
		switch ( event.code ) {
			case KEYS.F2:
				// показываем либо скрываем поиск
				if ( CSListManager.SearchBarVisibility == true && !ListPage.Info.isActive ) {
					ListPage.SearchBar.Activate(true);
				}
				break;
			case KEYS.EXIT:
			case KEYS.BACK:
				ListPage.actionBack();
				break;
			case KEYS.F3:
			case KEYS.MENU:
			case KEYS.INFO:
				ListPage.BPanel.EventHandler(event);
				break;
			case KEYS.PLAY_PAUSE:
				ListPage.Preview.playPause();
				break;
			case KEYS.STOP:
				MediaPlayer.end();
				break;
			default:
				CSListManager.Current().EventHandler(event);
		}
	} else if ( ListPage.Info.isActive ) {
		echo('ListPage.Info.keyHandler=');
		ListPage.$infoMessage.focus();
		switch ( event.code ) {
			case KEYS.BACK:
			case KEYS.INFO:
			case KEYS.EXIT:
				ListPage.actionBack();
				break;
			case KEYS.MENU:
			case KEYS.OK:
				ListPage.BPanel.EventHandler(event);
				break;
			case KEYS.DOWN:
				ListPage.$infoMessage.scrollByLines(3);
				break;
			case KEYS.UP:
				ListPage.$infoMessage.scrollByLines(-3);
				break;
			case KEYS.PAGE_DOWN:
				ListPage.$infoMessage.scrollByPages(1);
				break;
			case KEYS.PAGE_UP:
				ListPage.$infoMessage.scrollByPages(-1);
				break;
		}
	}
};

 

/**
 * Парсер для составления списка фильмов/аудио/картинок.
 * @param {String} html содержание страницы меню сайта (а именно - видео/картинки/аудио)
 */
function htmlListParser(html){
//    var date = new Date(); // подсчет скорости работы
//    var start = date.getTime();
    echo('new htmlListParser');
    var cats = [], img, url, title, cat, add;
    try{
        html = html.split("include_0");
        // случай когда закончились страницы (т.е. текущая страница>0 но фильмов на странице больше нету)
        if (html[1]==undefined && CSListManager.Current().handleInner.page_index>0 ){
            CSListManager.Current().handleInner.page_length = CSListManager.Current().handleInner.page_index-1;
            echo('this is end page. page_index='+CSListManager.Current().handleInner.page_index);  
            return;
        }
        html = html[1].split('</table>');
        html = html[0].split("<td");
    } catch (error) {
        echo('WARNING! PARSER htmlListParser ERROR!');
        echo('ERROR INFO: '+error);
        ListPage.addListItems(cats);
        return;
    }
    
    for(var i=1;i<html.length;i++){
        // убираем ссылки на мегого и рекламу и закрытые обьекты
        if(html[i].indexOf('iframe')>0 || html[i].indexOf('/user/megogo') != -1 || html[i].indexOf('Нет доступа') != -1 || html[i].indexOf('No access') != -1){
            continue;
        }
        url   = html[i].substring(html[i].indexOf("href='")   +6, html[i].indexOf("'><"));
        // отсутствие картинки
        if(html[i].indexOf("img src='")>0){
            img   = html[i].substring(html[i].indexOf("img src='")+9, html[i].indexOf("' ", html[i].indexOf("img src='")+11));
        } else { img = 'img/'+screen.height+'/ex_ua_noposter.png?100'; }
        title = html[i].substring(html[i].indexOf('<b>')      +3, html[i].indexOf('</b>'));
        add   = html[i].substring(html[i].indexOf('<small>')  +7, html[i].indexOf('</small>'));
        img = img.replace('?100','?400');
        // обьект или папка?
		cat = !(html[i].indexOf('Articles')<0 && html[i].indexOf('Статей')<0);
        // некорректное имя
        if(title == '&nbsp;' || title == '' || title == ' ' || title === null || title === undefined){ title = 'unnamed'; }
        cats.push({
            "url":'http://www.ex.ua'+url,
            "img":img,
            "title":unescape(title),
            "cat":cat,
            "add":add
        });
    }
    
    ListPage.addListItems(cats);
    echo('PARSER->ANSWER LENGTH='+cats.length);
}


 /**
 * Парсер для составления списка фильмов/аудио/картинок.
 * @param {String} html содержание страницы меню сайта (а именно - видео/картинки/аудио)
 */
function htmlSearchParser(html){
    echo('new htmlSearchParser');
    var cats = [], img, url, title, cat, add;
    try{
        html = html.split("class=panel");
        html = html[1].split('</table>');
        html = html[0].split("<td");
    } catch (error) {
        echo('WARNING! PARSER htmlSearchParser ERROR!');
        echo('ERROR INFO: '+error);
        new CModalAlert(ListPage, lang.search_is_over, lang.no_files, lang.back_action, function(){
            setTimeout(function(){
                var data = {type:TYPE_BACK};
                CSListManager.Open(data);
            }, 5);
        });
        return;
    }
    for(var i=1;i<html.length;i++){
        echo('thru each descr');
        // убираем ссылки на мегого и рекламу и закрытые обьекты
        if(html[i].indexOf('iframe')>0 || html[i].indexOf('/user/megogo') != -1 || html[i].indexOf('Нет доступа') != -1 || html[i].indexOf('No access') != -1){
            echo('continue');
            continue;
        }
        url   = html[i].substring(html[i].indexOf("href='")   +6, html[i].indexOf("'><"));
        // отсутствие картинки
        if(html[i].indexOf("img src='")>0){
            img   = html[i].substring(html[i].indexOf("img src='")+9, html[i].indexOf("' ", html[i].indexOf("img src='")+11));
        } else { img = ('img/'+screen.height+'/ex_ua_noposter.png'); }
            title = html[i].substring(html[i].indexOf('<b>')      +3, html[i].indexOf('</b>'));
        if(html[i].indexOf('modify_time')>0){
            add   = html[i].substring(html[i].indexOf('<small>')  +7, html[i].indexOf('<span'));
        }else{
            add   = html[i].substring(html[i].indexOf('<small>')  +7, html[i].indexOf('</small>'));
        }
        img = img.replace('?100','?400');
        // обьект или папка?
		cat = !(html[i].indexOf('Articles')<0 && html[i].indexOf('Статей')<0);
        // некорректное имя
        if(title == '&nbsp;' || title == '' || title == ' ' || title === null || title === undefined){ title = 'unnamed'; }
        
        cats.push({
            "url":'http://www.ex.ua'+url,
            "img":img,
            "title":unescape(title),
            "cat":cat,
            "add":add
        });
    }
    
    echo('PARSER->ANSWER LENGTH='+cats.length);
    ListPage.addListItems(cats);
}

/**
 * Переход на вторую страницу с передачей данных о текущей категории (для заполнения новой страницы)
 * @param {String} url ссылка на участок данных кеша, соответствующая новинкам выбранного на данный момент пункта
 * @param {String} cat_id настоящий id текущей категории
 * @param {String} cat_title название текущей категории (для хлебных крошек)
 * @param {int} type тип узла
 */
function onClickFunc(url, cat_id, cat_title, type){
    // настоящий id и имя категории, - необходим для локального поиска
    var data = {
        url         : url,
        real_cat_id : cat_id,
        title       : cat_title,        
        type        : type
    };
    ListPage.start(data);
    MainPage.LastActComp = MainPage.typeList.Current().cat;
}


/**
*  разбор присланного кода страницы сайта, - сбор данных о выбранном обьекте
*  @param {String} html код страницы выбранного обьекта
*/  
function htmlObjectParser(html){
//    var date = new Date();
//    var start = date.getTime();
    echo('htmlObjectParser new');
    var cats = [], html_td, url, name, img_big, img_prev, size_in_bytes, resolution;
    try{
        html = html.split("class=list");
        html = html[1].split('</table>');
        html = html[0].split("<tr>");
    } catch (error) {
        echo('WARNING! PARSER htmlObjectParser ERROR!->50');
        echo('ERROR INFO: '+error);
        ListPage.addObjectItems(cats);
        return;
    }
    for(var i=2;i<html.length;i++){
        html_td = html[i].split("<td");
       
        url           = html_td[2].substring(html_td[2].indexOf("href=")+6, html_td[2].indexOf("' title="));
        size_in_bytes = html_td[4].substring(html_td[4].indexOf("<b>")+3, html_td[4].indexOf("</b>"));
        resolution    = html_td[4].substring(html_td[4].indexOf("</a><br>")+8, html_td[4].indexOf("<p><span"));
        if(resolution.indexOf('x') < 0){ resolution = ''; }
        if(html_td[3].indexOf('img src')<0){
            // image
            name     = html_td[2].substring(html_td[2].indexOf("title=")+7, html_td[2].indexOf("' rel='nofollow'"));
            img_big  = '';
            img_prev = '';
        } else {
            // file
            name     = html_td[2].substring(html_td[2].indexOf('title=')  +7, html_td[2].indexOf("'>",html_td[2].indexOf("' title=")));
            img_big  = html_td[3].substring(html_td[3].indexOf('href=')   +6, html_td[3].indexOf("' id"));
            img_prev = img_big.replace('?100','?400');
        }
        cats.push({
            "url":           'http://www.ex.ua'+url,
            "name":          name,
            "img_big":       img_big,
            "img_prev":      img_prev,
            "size_in_bytes": size_in_bytes,
            "resolution":    resolution
        });
    }
    
    var play={
        "video":[],
        "audio":[],
        "images":[]
    };
    var model = gSTB.RDir("Model");
    for(i = 0;i<cats.length;i++){
        var ext = cats[i].name.split('.');
        cats[i].ext = ext[ext.length - 1];
        echo('['+i+']ext: '+cats[i].ext);
        cats[i].type = "none";
        if(model == 'MAG200'){
            switch(cats[i].ext.toLowerCase()){
                case "mpg":
                case "mov":
                case "mp4":
                case "avi":
                case "mkv":
                case "ts":
                case "vob":
                case "wmv":
                case "mts":
                case "m2t":
                case "m2v":
                case "divx":
                case "m2ts":
                case "m2p":
                case "tp":
                case "flv":
                case "mod":
                case "tod":
                case "asf":
                    cats[i].type = "video";
                    play.video.push(cats[i]);
                    break;
                case "mp3":
                case "wav":
                case "ac3":
                case "ape":
                case "ogg":
                case "oga":
                case "aiff":
                case "wv":
                case "tta":
                case "wma":
                case "flac":
                case "m4a":
                    cats[i].type = "audio";
                    play.audio.push(cats[i]);
                    break;
            }
        }else{
            switch(cats[i].ext.toLowerCase()){
                case "mpg":
                case "mov":
                case "mp4":
                case "avi":
                case "mkv":
                case "ts":
                case "vob":
                case "wmv":
                case "mts":
                case "m2t":
                case "m2v":
                case "divx":
                case "m2ts":
                case "m2p":
                case "tp":
                case "flv":
                case "mod":
                case "tod":
                case "asf":
                    cats[i].type = "video";
                    play.video.push(cats[i]);
                    break;
                case "mp3":
                case "wav":
                case "ac3":
                case "ape":
                case "ogg":
                case "oga":
                case "aiff":
                case "wv":
                case "tta":
                case "wma":
                case "flac":
                case "m4a":
                    cats[i].type = "audio";
                    play.audio.push(cats[i]);
                    break;
                case "jpg":
                case "jpeg":
                case "bmp":
                case "png":    
                    cats[i].type = "image";
                    play.images.push(cats[i]);
                    break;
            }
        }
    }
    
    ListPage.addObjectItems(play);
}


/**
*  сбор данных о выбранном обьекте который явлеется не обьектом а радио
*/  
function htmlRadioParser(html){
    echo('htmlRadioParser');
    var play={
        "video":[],
        "audio":[{}],
        "images":[]
    };
    var result = /<script>radio\('(.*?)'\);<\/script>/igm.exec(html);
    if(result == null){
        echo('Found nothing for this radio');
        ListPage.addObjectItems(play);
        return;
    }
    echo('result[1]='+result[1]);

    var current_radio      = CSListManager.src[0].Current();
    play.audio[0].url      = result[1];
    play.audio[0].name     = current_radio.data.title;
    play.audio[0].img_big  = current_radio.data.img_src;
    play.audio[0].img_prev = current_radio.data.img_src;
        
    ListPage.addObjectItems(play);
}


function htmlActorsParser(html){
    echo('htmlActorsParser');
    var result = /pg_menu"([\s\S]*?pg_menu)/.exec(html),
        myRe = /href="(.*?)"><b>/g,
        tmp,
        counter = 0,
        somelistItem,
        names = [lang.about_actor, lang.cat_video, lang.cat_music, lang.cat_images],
        current = CSListManager.Current();
    // добавим элемент выхода на более верхний уровень
    var data = {
        title       : '',
        img_src     : '',
        addtime     : '',
        url         : '',
        iid         : '',
        type        : TYPE_BACK
    };
    somelistItem = element('div',{
        className: 'back'
    },'..');
    current.Add(somelistItem, {
        data    : data,
        onclick : function(){
            echo('onClick-> back');
            setTimeout(function(){ 
                var data = {type:TYPE_BACK};
                CSListManager.Open(data);
            }, 5); 
            return false;
        }
    });
    // заменяем отсутствующие пункты меню на заглушки
    if(result[1] != undefined){
        result[1] = result[1].replace(/<\!--<a href="\/view\/.*?<\/b><\/a>-->/igm,'<!--<a href="/view/BROKEN"><b>broken</b></a>-->');
    }
    while ((tmp = myRe.exec(result[1])) != null){
        var title_text   = names[counter];
        var css_class    = 'cat';
        data = {
            type        : WHAT_IS_THIS_TYPE,
            title       : title_text,
            img_src     : CSListManager.parentData.img_src,
            addtime     : '',
            url         : 'http://www.ex.ua'+tmp[1],
            iid         : counter
        };
            
        somelistItem = element('div',{
            className : css_class, 
            innerHTML :title_text
        });
        current.Add(somelistItem, {
            data    : data,
            onclick : function(){
                echo(this.data,'onClick->EnterData=');
                CSListManager.Open(this.data);
                return false;
            }
        });
        counter++;
    }
        
    echo('current.handleInner.page_index'+current.handleInner.page_index);
    current.Activate(true);
}


 /**
 * Парсер для определения типа полученной страницы.
 * @param {String} html содержание страницы сайта
 * @param {int} status статус полученного ответа
 */
 function htmlWhatIsThisParser ( html, status ) {
	 echo('htmlWhatIsThisParser');
	 if ( MainPage.messageHint ) {MainPage.messageHint.Show(false);}
	 ListPage.Show(true, MainPage);
	 var source_html;
	 var current = CSListManager.Current().handleInner;
	 source_html = /include_0/im.exec(html);
	 if ( source_html != null ) {
		 // THIS IS FOLDER
		 echo('current.data.type  = ORPHAN_FOLDER;');
		 current.data.type = ORPHAN_FOLDER;
		 CSListManager.parentData.type = ORPHAN_FOLDER;
		 // навешиваем действие при наведении фокуса
		 CSListManager.Current().onFocus = function ( current, previous ) {
			 // обычное поведение правого меню при просмотре списка папок
			 CSListManager.parent.onFocusAction(current, previous);
			 // навешиваем на onFocus слежение за текущей позицией чтобы знать когда дойдем до предпоследнего экрана и послать запрос на дополнение
			 var currLen = CSListManager.Current().handleInner.childNodes.length;
			 var curHandleInner = CSListManager.Current().handleInner;
			 var minStep = CSListManager.parent.ItemsPerPage / 4;
			 echo('current.data.iid =' + current.data.iid + ' currLen=' + currLen);
			 if ( current.data.iid > currLen - minStep && currLen > minStep && curHandleInner.page_length >= curHandleInner.page_index ) {
				 var doc = CSListManager.Current().handleInner;
				 // подгрузка следующей порции при достижении предпоследнего листа
				 echo('iid_scroll=100%=>ajax=' + doc.data.url + '?p=' + doc.page_index + '&per=' + ListPage.ItemsPerPage);
				 ajax('GET', doc.data.url + '?p=' + doc.page_index + '&per=' + CSListManager.parent.ItemsPerPage, htmlListParser, {

				 });
			 }
		 };
		 // навешиваем на csroll слежение за текущей позицией чтобы знать когда дойдем до текущего края и послать запрос на дополнение
		 CSListManager.Current().handleInner.onscroll = function () {
			 echo('on scroll ');
			 var doc = CSListManager.Current().handleInner,
				 scrollPosition = doc.scrollTop,
				 pageSize = (doc.scrollHeight - doc.clientHeight),
				 percentageScrolled = Math.floor((scrollPosition / pageSize) * 100);
			 // подгрузка следующей порции при достижении скроллингом скролла в 100%
			 if ( percentageScrolled == 100 && doc.page_length >= doc.page_index ) {
				 echo('scroll=100%=>ajax=' + doc.data.url + '?p=' + doc.page_index + '&per=' + CSListManager.parent.ItemsPerPage);
				 ajax('GET', doc.data.url + '?p=' + doc.page_index + '&per=' + CSListManager.parent.ItemsPerPage, htmlListParser, {});
			 }
		 };
		 htmlListParser(html);
		 return;
	 }
	 if ( source_html == null ) {
		 // THIS IS OBJECT
		 source_html = /class=list/im.exec(html);
		 if ( source_html != null ) {
			 echo('current.data.type  = MEDIA_OBJECT;');
			 current.data.type = MEDIA_OBJECT;
			 CSListManager.parentData.type = MEDIA_OBJECT;
			 // навешиваем действие при наведении фокуса
			 CSListManager.Current().onFocus = function ( current, previous ) {
				 // обычное поведение правого меню при просмотре списка обьекта
				 ListPage.onFocusAction(current, previous);
			 };
			 ListPage.messageHint = new CModalHint(ListPage, lang.loading);
			 htmlObjectParser(html);
			 return;
		 }
	 }
	 // THIS IS... NOTHING
	 if ( source_html == null ) {
		 new CModalAlert(ListPage, lang.loading_is_over, lang.no_files, lang.back_action, function () {
			 setTimeout(function () {
				 var data = {type: TYPE_BACK};
				 CSListManager.Open(data);
			 }, 5);
		 });
	 }
 }



ListPage.Preview.setProgress = function ( value ) {
    echo('ListPage.Preview.setProgress->'+value);
    if(ListPage.Preview.pgval){
        // boundary check
        if ( value >= 100 ) value = 100;
        if ( value < 0 )    value = 0;
        // apply
        ListPage.Preview.pgval.style.width = value + '%';
    }
};

/**
 * Moves the cursor to the given element
 * @param {Object} data
 * @return {Boolean} operation status
 */
ListPage.Preview.setPosition = function ( data ) {
    echo(data,'ListPage.Preview.setPosition ');
	// find it in the new list if necessary
	if ( data ) for ( var item, i = 0, l = CSListManager.Current().handleInner.children.length; i < l; i++ ) {
		item = CSListManager.Current().handleInner.children[i];
		// url and type match
		if ( data.type === item.data.type && data.url === item.data.url && data.name === item.data.name ) {
			// make it active again
			return CSListManager.Current().Focused(item, true);
		}
	}
	return false;
};


ListPage.Preview.playPause = function () {
	echo('ListPage.Preview.playPause');
	echo(MediaPlayer.list, 'MediaPlayer.list 0');
	var list = [];
	// playing or not
	if ( MediaPlayer.playNow || MediaPlayer.obj !== null ) {
		MediaPlayer.playPause();
	} else {
		// check current selected item that it can be played
		var file = CSListManager.Current().Current();
		if ( CSListManager.playable.indexOf(file.data.type) !== -1 ) {
			this.player.className = 'player load';
			// add files to playlist
			list = MediaPlayer.addPreviewList();
			// start playing
			echo(list, 'MediaPlayer.preparePlayer(list,');
			MediaPlayer.preparePlayer(list, ListPage, false, true, false);
			echo(MediaPlayer.list, 'MediaPlayer.list 1');
			// only images or audio
			if ( file.data.type === MEDIA_TYPE_IMAGE ) {ListPage.Preview.player.className = 'player play'};
		}
	}
};


/**
* small player window size for preview
* @return {{x:Number,y:Number,a:Number,b:Number}}
*/
ListPage.Preview.getRect = function () {
	var videoMode = gSTB.RDir('vmode');
	switch ( videoMode ) {
		case '480i':
		case '480p':
			return {x : 457, y : 97, a : 218, b : 140};
		case '576i':
		case '576p':
			return {x : 457, y : 108, a : 219, b : 140};
		case '720p':
		case '720p60':
			switch ( screen.width ) {
				case 720         :
					return {x : 837, y : 129, a : 389, b : 177};
				case 1280        :
					return {x : 830, y : 129, a : 394, b : 220};
			}
			break;
		case '1080i':
		case '1080i60':
		case '1080p':
		case '1080p60':
			switch ( screen.width ) {
				case 720         :
					return {x : 1253, y : 205, a : 584, b : 260};
				case 1280        :
					return {x : 1245, y : 192, a : 588, b : 330};
				case 1920        :
					return {x : 1247, y : 194, a : 591, b : 330};
			}
			break;
	}
	// just in case
	return { x : 0, y : 0, a : 0, b : 0 };
};


/**
 * Convert number of bytes into human readable format
 * @param {Number} bytes Number of bytes to convert
 * @param {Number} precision Number of digits after the decimal separator
 * @return string
 */
ListPage.Preview.bytesToSize = function ( bytes, precision ) {
	var kilobyte = 1024;
	var megabyte = kilobyte * 1024;
	var gigabyte = megabyte * 1024;
	var terabyte = gigabyte * 1024;

	if ( (bytes >= 0) && (bytes < kilobyte) ) {
		return bytes + ' B';
	} else if ( (bytes >= kilobyte) && (bytes < megabyte) ) {
		return (bytes / kilobyte).toFixed(precision) + ' KB';
	} else if ( (bytes >= megabyte) && (bytes < gigabyte) ) {
		return (bytes / megabyte).toFixed(precision) + ' MB';
	} else if ( (bytes >= gigabyte) && (bytes < terabyte) ) {
		return (bytes / gigabyte).toFixed(precision) + ' GB';
	} else if ( bytes >= terabyte ) {
		return (bytes / terabyte).toFixed(precision) + ' TB';
	} else {
		return bytes + ' B';
	}
};


/**
 * The main method of an item info display
 * @param {Object} data media item inner data
 */
ListPage.Preview.info = function ( data ) {
    echo(data,'ListPage.Preview.info');
    // get item associated open action and execute
    if ( data && data.type && ListPage.Preview.action_info[data.type] instanceof Function ) {
        ListPage.Preview.action_info[data.type].call(this, data);
    }else{
        echo('wrong info type');
    }
};


/**
 * Display the given item info
 * @param {Object} data media item inner data
 */
ListPage.Preview.infoFolder = function(data){
    echo(data, 'infoFolder');
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, true);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, false);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
    var self = this,
    size = rightMenuTextLength,
    name = data.title.substr(0, size - 1) + (data.title.length > size ? '...' : '');
        
    elchild(elclear(this.body), element('div', {}, [
            element('img', {className: 'itemImg',src:data.img_src}),
            element('div', {className: 'text'}, [lang.description, element('span', {className: 'txt'}, name.split('').join("\u200B"))]),
            element('div', {className: 'text'}, [lang.add_time, element('span', {className: 'txt'}, data.addtime)])
    ]));
};


/**
 * Display the given item info
 * @param {Object} data media item inner data
 */
ListPage.Preview.infoFile = function(data){
    echo(data, 'infoFile');
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, false);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, false);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
    if(CSListManager.Current().Current().marked){
        ListPage.BPanel.btnF3.children[1].innerHTML = lang.unmark;
    } else {
        ListPage.BPanel.btnF3.children[1].innerHTML = lang.mark;
    }
       echo('name 0= '+data.name);
            var self = this,
            size = rightMenuTextLength,
            name = data.name.substr(0, size - 1) + (data.name.length > size ? '...' : ''),
            size_in_bytes;
            if(data.size_in_bytes){
                size_in_bytes = data.size_in_bytes == '' ? '' : ListPage.Preview.bytesToSize(Number(data.size_in_bytes.split(',').join('')), 1);
            } else {
                size_in_bytes = 0;
            }
        echo('name 1= '+name);
	elchild(elclear(this.body), this.body.info = element('div', {}, [
		element('div', {className: 'file'}, [
                        this.player = element('div', {className: 'player stop', onclick: function () {
				self.playPause();
                                setTimeout(function(){CSListManager.Current().Activate();}, 5);
			}}),
			this.pgbar = element('div', {className: 'pgbar'}),
			this.pgval = element('div', {className: 'pgval'})
		]),
		element('div', {className: 'text fleft'}, [lang.description, element('span', {className: 'txt'}, name.split('').join("\u200B"))]),
                this.file_size = element('div', {className: 'text'}, [lang.size_in_bytes, element('span', {className: 'txt'}, size_in_bytes)])
	]));
        echo('name 2= '+name.split('').join("\u200B"));
	// show images at once
	if ( data.type === MEDIA_TYPE_IMAGE ){ListPage.Preview.playPause();}
};



ListPage.Preview.infoBack = function(){
    echo('infoBack');
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, true);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, true);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
	elchild(elclear(this.body), element('div', {className:'preview_back'}, [
		this.infoIcon,
		element('div', {className: 'text'}, lang.madia_menu_info_back_act)
	]));
};


ListPage.Info.start = function ( html ) {
	echo('ListPage.Info.start');
	var mess;
//	this.htmlData = html; // save ajax req res to use it for shortcut btn
	ListPage.SearchBar.Show(false, false);
	// проверяем, есь ли F3, и если есть - запоминаем это, чтобы показать её при выходе
	if ( !CSListManager.parent.BPanel.btnF3.data.hidden ) {
		ListPage.Info.f3_visibility_flag = true;
		ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, true);
	}
	ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, true);
	ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, false);
	ListPage.BPanel.Hidden(CSListManager.parent.BPanel.btnOKINFO, false);
	mess = element('div', { className: 'messText', innerHTML: html });
	elchild(elclear(ListPage.$infoMessage), mess);
	// пока идет работа с DOM отложим поиск
	setTimeout(function () {
		var button = ListPage.$infoMessage.querySelector('.r_button');
		if ( button ) { button.innerHTML = ''; }
	}, 600);
	ListPage.Info.Show(true, true);
	ListPage.Info.Activate();
};


/**
*  разбор присланного кода страницы сайта, - сбор описания выбранного обьекта
*  @param {String} html код страницы выбранного обьекта
*/  
function htmlObjectInfoParser(html){
//    var date = new Date();
//    var start = date.getTime();
    echo('htmlObjectInfoParser');
    html = html.split("id='body_element'");
    html = html[1].split('table');
    // на тот случай если ничего подходящего на нашлось
    if(html[1] == null){
        echo('Found nothing for this object');
        new CModalAlert(ListPage, lang.search_is_over, lang.no_files, lang.back_action, function(){
            setTimeout(function(){
                var data = {type:TYPE_BACK};
                CSListManager.Open(data);
            }, 5);
        });
        return;
    }
    html = html[2].split('td');
    if(html[1] == null){
        html[1] = html[0];
    }
    html = html[1];
    // убираем ссылки
    html = html.split('href=').join('title=');
//    date = new Date();
//    echo('htmlObjectInfoParser speed='+(date.getTime()-start));
    ListPage.Info.start('<div'+html+'div>');
}


/*
 * проверка размера картинки по её url. При превышении максимального - замена на заглушку.
 * @param {String}  img_url         url картинки
 * @param {int} max_size            максимально возможный размер в байтах
 * @param {Obj} data_from_cache     обьект данных в памяти, содержащий src картинки
 * @param {Obj} [data_from_browser] optional обьект DOM в браузере, содержащий src картинки 
 */
function checkForGiantImg(img_url, max_size, data_from_cache, data_from_browser ){
   	var timeout = null, xhr = new XMLHttpRequest();
        if(!max_size){max_size = 500000;}
    xhr.onreadystatechange = function() {
        if ( xhr.readyState === 4 ) {
            clearTimeout(timeout);
            var file_size_head = xhr.getResponseHeader("Content-Length");
            echo(xhr.responseText, 'AJAX :: ' + 'head' + ' ' + img_url + ' (status:' + xhr.status + ', length:'+xhr.responseText.length+')'+' file length '+file_size_head);
            if(!file_size_head || file_size_head > max_size ){
                echo('I FOUND TOO BIG FILE -_- ');
                if( data_from_browser !== undefined ){data_from_browser.src   = 'img/'+screen.height+'/ex_ua_noposter.png?100';}  
                data_from_cache.img_src = 'img/'+screen.height+'/ex_ua_noposter.png?100'; 
            } else{
                if(data_from_browser !== undefined ){data_from_browser.src   = img_url;}  
                data_from_cache.img_src = img_url; 
            }
        }
    };
    xhr.open('head', img_url, true);
    xhr.send();
    // abort after some time (30s)
    timeout = setTimeout(function(){
        xhr.abort();
        echo('ABORT on timeout', 'AJAX :: ' + 'head' + ' ' + img_url);
    }, 60000);
}