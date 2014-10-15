

var CSListManager = {};
    
/**
 *  @param {Node} handleInner элемент DOM к которому будет крепиться все списки
 *  @param {Object} parentPage страница с которой работет менеджер 
 */       
CSListManager.init = function(handleInner, parentPage){
        /**
        * элемент для присоединения к DOM для создаваемых CScrollList
        * @type {Node}
        */
    CSListManager.handleInner = handleInner;
        /**
        * current active CScrollList
        * @type {Node}
        */
    CSListManager.current = null;
        /**
        * массив с CScrollList
        * @type {Array}
        */
    CSListManager.src = [];
    
    this.playable = [MEDIA_TYPE_VIDEO, MEDIA_TYPE_AUDIO, MEDIA_TYPE_IMAGE];
        /**
        * Флаг отображающий состояние видимости компонента поиска
        * @type {Boolean}
        */
    CSListManager.SearchBarVisibility = true;
        /**
         * обьект CPage с которым работает CSListManager
         * @type {Object}
         */
    CSListManager.parent = parentPage;
    	/**
	 * current working mode
	 * @type {Number}
	 */
	this.mode = NORMAL_MODE;
	/**
	 * current media object opened
	 * @type {Object}
	 */
	this.parentData = null;
	this.LEVEL_CHANGE_NONE = 0;
	this.LEVEL_CHANGE_UP   = -1;
	this.LEVEL_CHANGE_DOWN = 1;
      	/**
	 * list of action mapped to the media types
	 * @type {[Function]}
	 */
	this.action_open = {};
	this.action_open[ROOT_VIDEO]             = this.openRoot;
	this.action_open[ROOT_AUDIO]             = this.openRoot;
	this.action_open[ROOT_IMAGE]             = this.openRoot;
	this.action_open[TYPE_BACK]              = this.openBack;
        this.action_open[ROOT_GLOBAL_SEARCH]     = this.openGSearchRoot;
        this.action_open[LOCAL_SEARCH_ROOT]      = this.openGSearchRoot;        
	this.action_open[TYPE_NONE]              = this.openTypeNone;
	this.action_open[MEDIA_OBJECT]           = this.openMediaObject;
	this.action_open[MEDIA_FOLDER]           = this.openMediaFolder;
	this.action_open[RADIO_OBJECT]           = this.openRadioObject;
	this.action_open[ACTORS_FOLDER]          = this.openActorsFolder;
	this.action_open[GLOBAL_SEARCH_FOLDER]   = this.openGSearchFolder;
        this.action_open[ORPHAN_FOLDER]          = this.openGSearchFolder;        
	this.action_open[GLOBAL_SEARCH_REFRESH]  = this.openRefresh;
	this.action_open[LOCAL_SEARCH_REFRESH]   = this.openRefresh;
	this.action_open[WHAT_IS_THIS_TYPE]      = this.openUnknownType;
        this.action_open[ROOT_NEWS]              = this.openUnknownType;
        this.action_open[MEDIA_TYPE_VIDEO]       = this.openFile;
        this.action_open[MEDIA_TYPE_AUDIO]       = this.openFile;
        this.action_open[MEDIA_TYPE_IMAGE]       = this.openFile;

};


CSListManager.Current = function () {
return CSListManager.src[CSListManager.src.length-1];
};


/**
 * Enter the item or open it
 * @param {Object} data media item inner data
 */
CSListManager.Open = function ( data ) {
    echo(data, 'CSListManager.Open :: data');
    // форсированная прорисовка инфо в фоне при открытиии плеера
    if((data.type == MEDIA_TYPE_VIDEO || data.type == MEDIA_TYPE_AUDIO || data.type == MEDIA_TYPE_IMAGE) && cat_focus_timer){
        clearTimeout(cat_focus_timer);
        // show info in preview block
        ListPage.Preview.info(CSListManager.Current().Current().data);
    } 
    if(data.type !== MEDIA_TYPE_VIDEO && data.type !== MEDIA_TYPE_AUDIO && data.type !== MEDIA_TYPE_IMAGE){
        elclear(ListPage.Preview.body);
    }
    var levelChange     = this.Build(data);
        this.parentData = data;

    // level not changed
    if ( levelChange == this.LEVEL_CHANGE_NONE ) {
        echo('LEVEL_CHANGE_NONE');
        // изменяем запись в хлебных крошках
        if(data.type != TYPE_NONE && data.type != MEDIA_TYPE_VIDEO && data.type != MEDIA_TYPE_AUDIO && data.type != MEDIA_TYPE_IMAGE){
            ListPage.breadCrumb.Pop();
            ListPage.breadCrumb.Push('/',type_info[data.type].icon, data.title);
        }
    }
    
    // level changed
    if ( levelChange == this.LEVEL_CHANGE_DOWN ) {
        echo('LEVEL_CHANGE_DOWN'); 
        // прячем предыдущий лист
        if(CSListManager.src.length>1){
            echo('this.src.length>1');
            CSListManager.src[CSListManager.src.length-2].Show(false);
        }
        // добавляем запись в хлебные крошки
        if(data.type != TYPE_NONE && data.type != MEDIA_TYPE_VIDEO && data.type != MEDIA_TYPE_AUDIO && data.type != MEDIA_TYPE_IMAGE){
            ListPage.breadCrumb.Push('/',type_info[data.type].icon, data.title);
        }
        // скрываем поиск
        if(this.SearchBarVisibility == true){
            if((this.mode == LSEARCH_MODE && this.src.length > 2)||(this.mode == NORMAL_MODE && this.src.length > 1)){
                echo('search hide');
                this.parent.SearchBar.Show(false,false);
                this.SearchBarVisibility = false;
            }
        }
    }
    
    // level changed
    if ( levelChange == this.LEVEL_CHANGE_UP ) {
        echo('LEVEL_CHANGE_UP');
        CSListManager.parent.breadCrumb.Pop();
        // поднимаемся вверх, текущим становится предыдущий лист, - обновляем текущий тип
        if(CSListManager.src.length > 0){
            CSListManager.parentData   = CSListManager.Current().handleInner.data;
        }
        if(CSListManager.src.length == 1){
            CSListManager.mode = NORMAL_MODE;
            CSListManager.Current().Current().focus();
        }
        // показываем поиск
        if(this.SearchBarVisibility == false){
            if((this.mode == LSEARCH_MODE && this.src.length < 3)||(this.mode == NORMAL_MODE && this.src.length < 2)){
                echo('search show');
                this.parent.SearchBar.Show(true,false);
                this.SearchBarVisibility = true;
            }
        }   
        
        if(CSListManager.src.length == 0){
            // выход на предыдущую страницу
            this.parent.SearchBar.Show(true,false);
            CSListManager.trash.Clear();
            this.handleInner.removeChild(CSListManager.trash.handleInner);
            echo('---PAGE CHANGE---');
            ListPage.Show(false);
            MainPage.LastActComp.Activate(true,true);
        }
    }
};


/**
 * Renders the given media item by executing associated action
 * @param {Object} data media item inner data
 * @return {Number} hierarchy change flag: 0 - no change, 1 - go level deeper, -1 - go level up
 */
CSListManager.Build = function ( data ) {
    echo(data, 'CSListManager.Build:: data');
    var levelChange = this.LEVEL_CHANGE_NONE;
    // get item associated open action and execute
    if ( data && data.type && this.action_open[data.type] instanceof Function ) {
        levelChange = this.action_open[data.type].call(this, data);
    } else {
        // wrong item type
        echo('wrong item type');
        new CModalAlert(CSListManager.parent, LANG_MEDIA_DEFAULT_ERROR, LANG_MEDIA_ITEM_WRONG_TYPE, LANG_MEDIA_DEFAULT_CLOSE);
    }
    return levelChange;
};


CSListManager.openBack = function(data){
    echo(data,'openBack');
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnF3, true);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOnINFO, true);
    CSListManager.parent.BPanel.Hidden(CSListManager.parent.BPanel.btnOffINFO, true);
    // старый CSList
    CSListManager.trash = CSListManager.src.pop();
    if(this.src.length>0){
        // возвращение на предыдущий CSList
        CSListManager.Current().Show(true);
        CSListManager.Current().Activate(true);
        CSListManager.trash.Show(false);
        CSListManager.trash.Clear();
        CSListManager.handleInner.removeChild(CSListManager.trash.handleInner);
        // обходим ограничение оnChange и возвращаем старое описание и картинку
        CSListManager.parent.onFocusAction(CSListManager.Current().Current(), '');
    }
    return this.LEVEL_CHANGE_UP;
};


CSListManager.openTypeNone = function(data){
    echo('openTypeNone');
    echo('Wrong file type. Press "BACK" to exit.');
};


CSListManager.openRoot = function(data){
    echo('openRoot type='+data.type);
    // перестраиваем URL под текущий тип данных
    data.url = type_info[data.type].url_start+data.url+type_info[data.type].url_end;
    // создаем лист
    CSListManager.AddCSList(data);
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current_item){
        echo(current_item, 'CSListManager.Current().onFocus');
        // обычное поведение правого меню при просмотре списка папок
        CSListManager.parent.onFocusAction(current_item, '');
        // навешиваем на onFocus слежение за текущей позицией чтобы знать когда дойдем до предпоследнего экрана и послать запрос на дополнение 
        var curr_list_length = CSListManager.Current().handleInner.childNodes.length;
        var cur_list_handleInner = CSListManager.Current().handleInner;
        echo('current_item.data.iid ='+current_item.data.iid+' curr_list_length='+curr_list_length);
        if(current_item.data.iid != curr_list_length-2 && current_item.data.iid > curr_list_length-CSListManager.parent.ItemsPerPage/2 && cur_list_handleInner.page_length >= cur_list_handleInner.page_index){
            var doc = CSListManager.Current().handleInner;
            // подгрузка следующей порции при достижении предпоследнего листа
            echo('iid_scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+ListPage.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
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
        if (percentageScrolled == 100 && doc.page_length >= doc.page_index){
            echo('scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
        }
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url+'&p=0&per='+this.parent.ItemsPerPage);
    ajax ( 'GET', data.url+'&p=0&per='+this.parent.ItemsPerPage, htmlListParser, {} );
    return this.LEVEL_CHANGE_DOWN;       
};


CSListManager.openGSearchRoot = function(data){
    echo(data,'openGSearchRoot');
    // создаем лист
    CSListManager.AddCSList(data);
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current_item){
        echo(current_item, 'CSListManager.Current().onFocus');
        // обычное поведение правого меню при просмотре списка папок
        CSListManager.parent.onFocusAction(current_item, '');
        // навешиваем на onFocus слежение за текущей позицией чтобы знать когда дойдем до предпоследнего экрана и послать запрос на дополнение 
        var curr_list_length = CSListManager.Current().handleInner.childNodes.length;
        var cur_list_handleInner = CSListManager.Current().handleInner;
        echo('current_item.data.iid ='+current_item.data.iid+' curr_list_length='+curr_list_length);
        if(current_item.data.iid != curr_list_length-2 && current_item.data.iid > curr_list_length-CSListManager.parent.ItemsPerPage/2 && cur_list_handleInner.page_length >= cur_list_handleInner.page_index){
            var doc = CSListManager.Current().handleInner;
            echo('iid_scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per=20');
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlSearchParser, {} );
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
        if (percentageScrolled == 100 && doc.page_length >= doc.page_index){
            echo('scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlSearchParser, {} );
        }
    };
           
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url+'&p=0&per='+this.parent.ItemsPerPage);
    ajax ( 'GET', data.url+'&p=0&per='+this.parent.ItemsPerPage, htmlSearchParser, {} );  
    
    return this.LEVEL_CHANGE_DOWN;
};


CSListManager.openRefresh = function(data){
    echo(data,'openRefresh');
    // обновляем данные текущего листа, - избегая тем самым лишней перезаписи
    CSListManager.Current().Clear();
    CSListManager.Current().handleInner.data.url = data.url;
    CSListManager.Current().handleInner.page_index = 0;
    CSListManager.Current().handleInner.page_length = 0;
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('rewrite req='+data.url+'&p=0&per='+this.parent.ItemsPerPage);
    ajax ( 'GET', data.url+'&p=0&per='+this.parent.ItemsPerPage, htmlSearchParser, {} );  
    
    return this.LEVEL_CHANGE_NONE;
};



CSListManager.openGSearchFolder = function(data){
    echo(data, 'openGSearchFolder');
    // создаем лист
    CSListManager.AddCSList(data);
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current,previous){
        // обычное поведение правого меню при просмотре списка папок
        this.parent.onFocusAction(current,previous);
        // навешиваем на onFocus слежение за текущей позицией чтобы знать когда дойдем до предпоследнего экрана и послать запрос на дополнение 
        var curr_list_length = CSListManager.Current().handleInner.childNodes.length;
        var cur_list_handleInner = CSListManager.Current().handleInner;
        echo('current.data.iid ='+current.data.iid+' curr_list_length='+curr_list_length);
        if(current.data.iid != curr_list_length-2 && current.data.iid > curr_list_length-CSListManager.parent.ItemsPerPage/2 && cur_list_handleInner.page_length >= cur_list_handleInner.page_index){
            var doc = CSListManager.Current().handleInner;
            // подгрузка следующей порции при достижении предпоследнего листа
            echo('iid_scroll=100%=>ajax='+doc.data.url+'?p='+doc.page_index+'&per='+ListPage.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
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
        if (percentageScrolled == 100 && doc.page_length >= doc.page_index){
            echo('scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
        }
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url+'?p=0&per='+this.parent.ItemsPerPage);
    ajax ( 'GET', data.url+'?p=0&per='+this.parent.ItemsPerPage, htmlListParser, {} );
    return this.LEVEL_CHANGE_DOWN; 
};



CSListManager.openMediaObject = function(data){
    echo(data,'openMediaObject');
    // создаем лист
    CSListManager.AddCSList(data);
    // скрываем поиск
    if( CSListManager.SearchBarVisibility==true ){
        echo('search hide');
        this.parent.SearchBar.Show(false,false);
        CSListManager.SearchBarVisibility=false;
    }   
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current,previous){
        // обычное поведение правого меню при просмотре списка обьекта
        CSListManager.parent.onFocusAction(current,previous);
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('AJAX START='+data.url);
    ajax ( 'GET', data.url, htmlObjectParser, {} );
    // окно ожидания загрузки
    setTimeout(function(){
        CSListManager.parent.messageHint = new CModalHint(CSListManager.parent, 'Loading...'/*, false, true*/);
    },1);
    return this.LEVEL_CHANGE_DOWN; 
};


CSListManager.openMediaFolder = function(data){
    echo('openMediaFolder type='+data.type);
    // создаем лист
    CSListManager.AddCSList(data);
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current,previous){
        // обычное поведение правого меню при просмотре списка папок
        this.parent.onFocusAction(current,previous);
        // навешиваем на onFocus слежение за текущей позицией чтобы знать когда дойдем до предпоследнего экрана и послать запрос на дополнение 
        var curr_list_length = CSListManager.Current().handleInner.childNodes.length;
        var cur_list_handleInner = CSListManager.Current().handleInner;
        echo('current.data.iid ='+current.data.iid+' curr_list_length='+curr_list_length);
        if(current.data.iid != curr_list_length-2 && current.data.iid > curr_list_length-CSListManager.parent.ItemsPerPage/2 && cur_list_handleInner.page_length >= cur_list_handleInner.page_index){
            var doc = CSListManager.Current().handleInner;
            // подгрузка следующей порции при достижении предпоследнего листа
            echo('iid_scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+ListPage.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
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
        if (percentageScrolled == 100 && doc.page_length >= doc.page_index){
            echo('scroll=100%=>ajax='+doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage);
            ajax ( 'GET', doc.data.url+'&p='+doc.page_index+'&per='+CSListManager.parent.ItemsPerPage, htmlListParser, {} );
        }
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url+'&p=0&per='+this.parent.ItemsPerPage);
    ajax ( 'GET', data.url+'&p=0&per='+this.parent.ItemsPerPage, htmlListParser, {} );
    return this.LEVEL_CHANGE_DOWN;         
};


CSListManager.openRadioObject = function(data){
    echo(data,'openRadioObject');
    // создаем лист
    CSListManager.AddCSList(data);
    // скрываем поиск
    if( this.SearchBarVisibility==true ){
        echo('search hide');
        this.parent.SearchBar.Show(false,false);
        this.SearchBarVisibility=false;
    }   
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current,previous){
        // обычное поведение правого меню при просмотре списка обьекта
        CSListManager.parent.onFocusAction(current,previous);
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('AJAX START='+data.url);
    ajax ( 'GET', data.url, htmlRadioParser, {} );
    // окно ожидания загрузки
    setTimeout(function(){
        CSListManager.parent.messageHint = new CModalHint(CSListManager.parent, 'Loading...');
    },1);
    
    return this.LEVEL_CHANGE_DOWN;
};


CSListManager.openActorsFolder = function(data){
    echo(data,'openActorsFolder');
    // создаем лист
    CSListManager.AddCSList(data);
    // навешиваем действие при наведении фокуса
    CSListManager.Current().onFocus = function(current,previous){
        // обычное поведение правого меню при просмотре списка папок
        this.parent.onFocusAction(current,previous);
    };
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url);
    ajax ( 'GET', data.url, htmlActorsParser, {charset:'utf-8'} );
    
    return this.LEVEL_CHANGE_DOWN; 
};


CSListManager.openUnknownType = function(data){
    echo(data,'openUnknownType');
    CSListManager.AddCSList(data);
 
    // источник взятия данных - сохраняется на предыдущей странице перед началом работы данной страницы
    echo('first request is='+data.url);
    ajax ( 'GET', data.url, htmlWhatIsThisParser, { charset:'utf-8' } );
    
    return this.LEVEL_CHANGE_DOWN; 
};


/**
 * Enter the item or open it
 * @param {Object} [data] media item inner data
 * @return {Number} hierarchy change flag: 0 - no change, 1 - go level deeper, -1 - go level up
 */
CSListManager.openFile = function ( data ) {
    echo(data,'openFile');
    var list = [];

	// playing video (showing a picture) or not
	if ( MediaPlayer.playNow || MediaPlayer.obj !== null ) {
		MediaPlayer.changeScreenMode(true);
		MediaPlayer.Show(true, this.parent);
		// autohide info panel
//		MediaPlayer.showInfo(true);
//		MediaPlayer.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo(false);},3000);
		// stay here
		return this.LEVEL_CHANGE_NONE;
	}

    // get all marked items
    var items = CSListManager.Current().Find({marked:true});
    echo('FIND marked items?');
    // no marked so take all available
    if ( items.length === 0 ) {
        echo('NOPE');
        items = Array.prototype.slice.call(CSListManager.Current().handleInner.children);
    }
    // prepare play list
    items.forEach(function(item){
        // allow only some media types
        if ( CSListManager.playable.indexOf(item.data.type) !== -1 ) {
            // set current focused item to play at once
            item.data.play = CSListManager.Current().Current() === item;
            // append to play list
            list.push(item.data);
        }
    });
	// fullscreen
    // send to player and start
    MediaPlayer.preparePlayer(list, ListPage, true, true, true);
    // stay here
    return this.LEVEL_CHANGE_NONE; 
};


/**
 * создание и ативация нового уровня вложенности 
 * @param {Object} data данные о уровне
 */
CSListManager.AddCSList = function(data){
    echo('creating CSList');
       // создаем лист
    var slist = new CScrollList(CSListManager.parent);
    slist.Init(element('div', {
        className    :'cslist-main', 			
        data         : data,
        page_index   : 0,
        page_length  : 0
    }));
    // add to dom
    elchild(CSListManager.handleInner, slist.handleInner);
    //  добавляем в массив листов - при этом последний добавленный становится активным и текущим
    CSListManager.src.push(slist);
    CSListManager.Current().Show(true);
    CSListManager.Current().Activate(true); 
};