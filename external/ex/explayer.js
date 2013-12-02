
/**
 * global player object
 * @type {CPage}
 */
var stbEvent = {};
var MediaPlayer = new CPage();

// получение переменных среды
var environment = (function ( global ) {
	var varList = ["lang_audiotracks", "audio_initial_volume", "graphicres",
		"subtitles_on", "lang_subtitles", "language", "subtitles_on"];
	var query = '{"varList":["' + varList.join('","') + '"]}', environment;
	try {
		environment = JSON.parse(gSTB.GetEnv(query)).result;
		if ( !environment.lang_audiotracks ) environment.lang_audiotracks = DEFAULT_AUDIO_LANG_1_IDX;
		if ( environment.subtitles_on == "true" ) {
			if ( !environment.lang_subtitles ) environment.lang_subtitles = DEFAULT_SUBTITLE_LANG_1_IDX;
		} else {
			environment.lang_subtitles = -1;
		}
		environment.subtitles_on = environment.subtitles_on == "true";
		if ( environment.audio_initial_volume && environment.audio_initial_volume >= 0 && environment.audio_initial_volume <= 100 ) {global.curVolume = environment.audio_initial_volume}
	} catch ( e ) {
		echo(e, "Environment load");
	}
	return environment;
})(window);


/**
 * Event types
 * need to be available on early loading stages
 * @type {number}
 */
MediaPlayer.EVENT_START    = 1;
MediaPlayer.EVENT_STOP     = 2;
MediaPlayer.EVENT_PAUSE    = 3;
MediaPlayer.EVENT_PROGRESS = 4;
MediaPlayer.EVENT_ERROR    = 5;
MediaPlayer.EVENT_OK       = 6;
MediaPlayer.EVENT_EXIT     = 7;

MediaPlayer.onInit = function() {
    var self = this;
    var defLang = "";
    for (var i=0; i<iso639.length; i++) {
        for (var j=0; j<iso639[i].code.length; j++) {
            if (iso639[i].code[j] === environment.language){
                defLang = iso639[i].code[0];
                break;
            }
        }
        if(defLang !== ""){
            break;
        }
    }
    

    gSTB.InitPlayer();
    gSTB.SetVideoControl(0); 
    gSTB.SetVideoState(0);    
    gSTB.SetTopWin(0);
    gSTB.SetWinMode(0, 0);
    gSTB.SetWinMode(1, 0);
    gSTB.SetMode(0);
    gSTB.SetWinAlphaLevel(0, 255);
    gSTB.SetWinAlphaLevel(1, 255);
    gSTB.SetChromaKey(1, 0xFFFFFF);
    gSTB.SetPIG(1, 0, 0, 0);
    gSTB.SetAudioLangs(environment.lang_audiotracks === ""? "" : iso639[environment.lang_audiotracks].code[0],defLang);
    gSTB.SetSubtitleLangs(environment.lang_subtitles === "" || environment.lang_subtitles === -1? "" : iso639[environment.lang_subtitles].code[0],defLang);


    this.countError = 0;
    this.pos = 0;
    this.posTime = "";
    this.posMod = 0;    
    this.posIntervals = [0, 10, 20];
    this.curTime = 0;
    this.totalTime = 0;
    this.infoFlag = true;
    this.coord = {};
    this.oldSol = "";
    this.oldURL = "";
    this.playNow = false;
    this.posModFlag = false;
    this.fullScreen = true;
    this.type = null;               // type of the object: "media", "image" ...
    this.obj = null;
    this.timer = {};
    this.interval = {};
    this.slideOn = 0;
    
    /**
     * list of callback events with their subscribers
     * @type {Object}
     */
    this.subscribers = {};
    this.SubscribersReset();
    this.playListShow = false;
    this.ts_active = false;
    this.subtitles_on = environment.subtitles_on;
    
    this.aspects = [
        {
            name: "fit",
            img: '/aspect_fit.png',
            mode: 0x10
        },
        {
            name: "big",
            img: '/aspect_big.png',
            mode: 0x40
        },
        {
            name: "opt",
            img: '/aspect_opt.png',
            mode: 0x50
        },
        {
            name: "exp",
            img: '/aspect_exp.png',
            mode: 0x00
        }
    ];
    this.activeAspect = 0;
    
    this.progress = {
        480: 620,
        576: 620,
        720: 1180,
        1080: 1770
    };
    
    //dom objects
    this.domPlayerREW = this.handle.querySelector('#playerREW');
    this.domPlayerFFWD = this.handle.querySelector('#playerFFWD');
    this.domPlayerName = this.handle.querySelector('#playerName');
    this.domPlayerTotalTime = this.handle.querySelector('#playerTotalTime');
    this.domPlayerCurrentTime = this.handle.querySelector('#playerCurrentTime');
    this.domPlayerBar = this.handle.querySelector('#playerBar');
    this.domPlayerTitle = this.handle.querySelector('#playerTitle');
    this.domPlayerBufferBar = this.handle.querySelector('#playerBufferBar');
    this.domPlayerProgressBar = this.handle.querySelector('#playerProgressBar');
    this.domPlayerHeader = this.handle.querySelector('#playerHeader');
    this.domPlayerFooter = this.handle.querySelector('#playerFooter');
    this.domTSIndicator = this.handle.querySelector('#ts_indicator');
    this.domPlayerList = this.handle.querySelector('#playerListBox');
    this.domPlayerClock = this.handle.querySelector('#playerClock');
    this.domChannelNumber = this.handle.querySelector('.channelNumber');

    this.handle.querySelector('#playerHideplist').innerHTML = playerBtnF2sh;
    this.handle.querySelector('#playerHeaderSetting').innerHTML = playerBtnMenu;
    this.handle.querySelector('#playerHideplayer').innerHTML = playerBtnInfo;
    this.handle.querySelector('#playlistPrev').innerHTML = playlistPrev;
    this.handle.querySelector('#playlistNext').innerHTML = playlistNext;
    
    this.Menu = new CGroupMenu();
    this.Menu.Init(this.handle.querySelector('.mbfade_sidebar'));
    
    this.init = [];
    this.init[MEDIA_TYPE_VIDEO] = function(refresh) {
        self.domPlayerTotalTime.innerHTML = "00:00:00";
        self.domPlayerCurrentTime.innerHTML = "00:00:00";
        self.domPlayerBufferBar.style.width = '0px';
        self.domPlayerProgressBar.style.width = '0px';
        if (!refresh) {
            self.domPlayerBar.style.display = "block";
            self.domPlayerCurrentTime.style.display = "block";
            self.domPlayerBufferBar.style.display = "none";
            self.domPlayerProgressBar.style.display = "block";
            self.handle.querySelector("#playerPause").style.display = "block";
            self.handle.querySelector("#playerREW").style.display = "block";
            self.handle.querySelector("#playerFFWD").style.display = "block";
            self.handle.querySelector("#playerTotalTime").style.display = "block";
            self.handle.querySelector("#playerCurrentTime").style.display = "block";
            self.handle.querySelector("#playerSlash").style.display = "block";
        }
        
        self.curTime = 0;
        self.totalTime = 0;
        self.posIntervals = [0, 10, 20];
    };
    this.init[MEDIA_TYPE_AUDIO] = this.init[MEDIA_TYPE_VIDEO];
    this.init[MEDIA_TYPE_STREAM] = function(refresh) {
        if (!refresh) {
            self.domPlayerBar.style.display = "none";
            self.domPlayerCurrentTime.style.display = "none";
            self.domPlayerBufferBar.style.display = "none";
            self.domPlayerProgressBar.style.display = "none";
            self.handle.querySelector("#playerHideplist").style.display = "none";
            self.handle.querySelector("#playerPause").style.display = "none";
            self.handle.querySelector("#playerREW").style.display = "none";
            self.handle.querySelector("#playerFFWD").style.display = "none";
            self.handle.querySelector("#playerTotalTime").style.display = "none";
            self.handle.querySelector("#playerCurrentTime").style.display = "none";
            self.handle.querySelector("#playerSlash").style.display = "none";
        }    
    };

    this.init[MEDIA_TYPE_IMAGE] = function() {
        self.domPlayerBar.style.display = "none";
        self.domPlayerCurrentTime.style.display = "none";
        self.domPlayerBufferBar.style.display = "none";
        self.domPlayerProgressBar.style.display = "none";
        self.handle.querySelector("#playerPause").style.display = "none";
        self.handle.querySelector("#playerREW").style.display = "none";
        self.handle.querySelector("#playerFFWD").style.display = "none";
        self.handle.querySelector("#playerTotalTime").style.display = "none";
        self.handle.querySelector("#playerCurrentTime").style.display = "none";
        self.handle.querySelector("#playerSlash").style.display = "none";
    }; 

    this.PlayList = new PlayList(this);
    this.PlayList.Init(this.handle.querySelector('#playerList'));
    stbEvent.onEvent = this.event;
    
    this.ModalMenu = new CModal(this);

    /**
     * main side menu
     * @type {CGroupMenu}
     */
    this.ModalMenu.Menu = new CGroupMenu(this.ModalMenu);
    this.ModalMenu.Menu.Init(this.handle.querySelector('div.cgmenu-main'));
    
    this.ModalMenu.onShow = function() {        
        this.Menu.Activate();
    };

    this.ModalMenu.Init(element('div', {className: "cmodal-menu"}, this.ModalMenu.Menu.handle));
    this.ModalMenu.EventHandler = function(event) {
        switch (event.code) {
            case KEYS.EXIT:
            case KEYS.MENU:
                self.ModalMenu.Show(false);
                break;
            default:
                self.ModalMenu.Menu.EventHandler(event);
        }
    };

    this.ModalMenu.Menu.gaudio = this.ModalMenu.Menu.AddGroup('gaudio', LANG_MEDIA_MENU_GROUPS['gaudio'], {
        onclick: function() {
            (self.ModalMenu.Menu.gaudio.slist.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            self.ModalMenu.Menu.gaudio.slist.Marked(this, true);
            self.ModalMenu.Show(false);
            gSTB.SetAudioPID(this.data);
            return false;
        }
    });
    
    this.ModalMenu.Menu.gsubtitle = this.ModalMenu.Menu.AddGroup('gsubtitle', LANG_MEDIA_MENU_GROUPS['gsubtitle'], {
        onclick: function() {
            (self.ModalMenu.Menu.gsubtitle.slist.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            self.ModalMenu.Menu.gsubtitle.slist.Marked(this, true);
            if(this.data === "OFF"){
                gSTB.SetSubtitles(false);
            } else {
                gSTB.SetSubtitles(true);
                gSTB.SetSubtitlePID(this.data);
            }
            self.ModalMenu.Show(false);
            
            return false;
        }
    });

    this.ModalMenu.Menu.g3d = this.ModalMenu.Menu.AddGroup('g3d', LANG_MEDIA_MENU_GROUPS['g3d'], {
        onclick: function() {
            (self.ModalMenu.Menu.g3d.slist.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            self.ModalMenu.Menu.g3d.slist.Marked(this, true);
            self.ModalMenu.Show(false);
            // добавленно для совместимости с 12 версией портала
            gSTB.Set3DConversionMode && gSTB.Set3DConversionMode(this.data);
            return false;
        }
    });
    
    this.ModalMenu.Menu.g3d.i1 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.g3d, 1, mediaInfoMenu_3dview +' 1', {data: 0});
    this.ModalMenu.Menu.g3d.i2 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.g3d, 2, mediaInfoMenu_3dview +' 2', {data: 1});
    this.ModalMenu.Menu.g3d.i3 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.g3d, 3, mediaInfoMenu_3dview +' 3', {data: 2});
    this.ModalMenu.Menu.g3d.i4 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.g3d, 4, mediaInfoMenu_3dview +' 4', {data: 3});
    
    this.ModalMenu.Menu.gslideOn = this.ModalMenu.Menu.AddGroup('gslideOn', LANG_MEDIA_MENU_GROUPS['gslideOn'], {
        onclick: function() {
            (self.ModalMenu.Menu.gslideOn.slist.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            self.ModalMenu.Menu.gslideOn.slist.Marked(this, true);
            self.ModalMenu.Show(false);
            self.slideOn = this.data;
            return false;
        }
    });
    
    this.ModalMenu.Menu.gslideOn.ioff = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 1, LANG_MEDIA_MENU_OFF, {data: 0, marked : true});
    this.ModalMenu.Menu.gslideOn.i3 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 2, '3 ' + time_seconds, {data: 3});
    this.ModalMenu.Menu.gslideOn.i5 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 2, '5 ' + time_seconds, {data: 5});
    this.ModalMenu.Menu.gslideOn.i10 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 2, '10 ' + time_seconds, {data: 10});
    this.ModalMenu.Menu.gslideOn.i30 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 2, '20 ' + time_seconds, {data: 20});
    this.ModalMenu.Menu.gslideOn.i30 = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gslideOn, 2, '30 ' + time_seconds, {data: 30});
    echo('MediaPlayer.modalInit = {};');
    
    this.modalInit = {};

	this.modalInit[MEDIA_TYPE_STREAM] = function () {
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gaudio, false);
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gsubtitle, false);
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gts, false);
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gtsend, false);
		self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gaudio, false);
		self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ion, false);
		self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ioff, true);
		self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.iciclick, true);
		self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.istop, false);
	};
	this.modalInit[MEDIA_TYPE_IMAGE] = function () {
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gslideOn, false);
		self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gslideOn, false);
	};
	this.modalInit[MEDIA_TYPE_AUDIO] = function () {
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gaudio, false);
		self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gsubtitle, false);
		self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gaudio, false);
	};
    this.modalInit[MEDIA_TYPE_VIDEO] = function() {
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gaudio,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gsubtitle,false);
        self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gaudio,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.g3d,false);
        (self.ModalMenu.Menu.g3d.slist.states.marked || []).forEach(function(item) {
            item.self.Marked(item, false);
        });
        self.ModalMenu.Menu.g3d.slist.Marked(self.ModalMenu.Menu.g3d.i1, true);
    };
    
    this.ModalMenu.Menu.Switch(this.ModalMenu.Menu.gaudio);
};

MediaPlayer.EventHandler = function(e){
    echo('MediaPlayer.EventHandler');
    switch (e.code) {
        case KEYS.UP:
        case KEYS.DOWN:
            if (MediaPlayer.playListShow && MediaPlayer.infoFlag) {
                MediaPlayer.PlayList.EventHandler(e);
            }
            break;
        case KEYS.PAGE_DOWN:
            if (MediaPlayer.list.length > 1 && MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length) {
                //MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.Next(), true);
				MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex + 1], true);
				MediaPlayer.PlayList.Open(MediaPlayer.PlayList.activeItem.data);
            }
            e.preventDefault();
            break;
        case KEYS.PAGE_UP:
            if (MediaPlayer.list.length > 1 && MediaPlayer.PlayList.playIndex > 0) {
                //MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.Next("", true), true);
				MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex - 1], true);
                MediaPlayer.PlayList.Open(MediaPlayer.PlayList.activeItem.data);
            }
            e.preventDefault();
            break;
        case KEYS.OK: // Ok/enter
            echo('MediaPlayer.KEYS.OK;');
            if(MediaPlayer.posModFlag){
                clearTimeout(this.timer.setPos);
                echo(MediaPlayer.pos, 'set pos:');
                gSTB.SetPosTime(MediaPlayer.pos);
                MediaPlayer.pos = 0;
                MediaPlayer.posTime = "";
                MediaPlayer.posMod = 0;
                MediaPlayer.domPlayerCurrentTime.className = "time_cur";
                gSTB.Continue();
                MediaPlayer.posModFlag = false;
				MediaPlayer.timer.showInfo = setTimeout(function () {
					MediaPlayer.showInfo(false);
				}, 5);
                e.preventDefault();
                break;
            }
            if(MediaPlayer.playListShow && MediaPlayer.infoFlag){
                MediaPlayer.PlayList.EventHandler(e);
                echo('MediaPlayer.PlayList.EventHandler(e);');
                break;
            }
            ListPage.subscribeEvents[MediaPlayer.EVENT_OK].call(ListPage);/*BAS*/
            break;
        case KEYS.INFO:
            MediaPlayer.showInfo();
            break;
        case KEYS.MENU:
            MediaPlayer.ModalMenu.Show(true);
            break;
        case KEYS.BACK:
        case KEYS.EXIT: // Exit
            MediaPlayer.exit();                        
            break;                    
        case KEYS.FRAME: // Fullscreen
            MediaPlayer.aspect();
            break;
        case KEYS.PLAY_PAUSE:
            MediaPlayer.playPause();
            break;
        case KEYS.STOP:
            MediaPlayer.exit();
            break;
        case KEYS.LEFT:
        case KEYS.REWIND:
            switch (MediaPlayer.type) {
				case MEDIA_TYPE_VIDEO:
				case MEDIA_TYPE_AUDIO:
					MediaPlayer.setPos(-1);
					break;
			}
			break;
		case KEYS.RIGHT:
		case KEYS.FORWARD:
			switch ( MediaPlayer.type ) {
				case MEDIA_TYPE_VIDEO:
				case MEDIA_TYPE_AUDIO:
					MediaPlayer.setPos(1);
					break;
			}
			break;
		case KEYS.CHANNEL_NEXT:
		case KEYS.CHANNEL_PREV:
			break;
		case KEYS.NUM0:
			MediaPlayer.setPosTime("0");
			break;
		case KEYS.NUM1:
			MediaPlayer.setPosTime("1");
            break;
        case KEYS.NUM2:
            MediaPlayer.setPosTime("2");
            break;
        case KEYS.NUM3:
            MediaPlayer.setPosTime("3");
            break;
        case KEYS.NUM4:
            MediaPlayer.setPosTime("4");
            break;
        case KEYS.NUM5:
            MediaPlayer.setPosTime("5");
            break;
        case KEYS.NUM6:
            MediaPlayer.setPosTime("6");
            break;
        case KEYS.NUM7:
            MediaPlayer.setPosTime("7");
            break;
        case KEYS.NUM8:
            MediaPlayer.setPosTime("8");
            break;
        case KEYS.NUM9:
            MediaPlayer.setPosTime("9");
            break;
            
        case KEYS.F2:
            if (MediaPlayer.list.length > 1) {
                if (MediaPlayer.playListShow && MediaPlayer.infoFlag) {
                    MediaPlayer.playListShow = false;
                    MediaPlayer.handle.querySelector('#playerHideplist').innerHTML = playerBtnF2sh;
                    MediaPlayer.domPlayerList.style.display = "none";
                } else {
                    MediaPlayer.playListShow = true;
                    MediaPlayer.handle.querySelector('#playerHideplist').innerHTML = playerBtnF2hd;
                    MediaPlayer.showInfo(true);
                    MediaPlayer.domPlayerList.style.display = "block";
                    MediaPlayer.PlayList.Refresh();
                }
            }
            break;
        default : 
            e.preventDefault();
            break;
    }
};


/**
 * prepare Player to play and init playlist
 * @param {Array} list array playlist
 * @param {Object} parent parent page
 * @param {Boolean} fullScreen play in fullScreen 
 * @param {Boolean} play start playing
 * @param {Boolean} show show player
 * @return {Boolean} all prepare's done
 */
MediaPlayer.preparePlayer = function(list, parent, fullScreen, play, show) {
    echo(list,'MediaPlayer.preparePlayer->list to play ' + play);
    if(list.length === 0 || !parent){
        return false;
    }
    this.playListShow = false;
    this.domPlayerList.style.display = "none";
    this.parent = parent;
    play = play === true;
    this.fullScreen = fullScreen !== false;
    this.fullScreen = !this.fullScreen;
    this.PlayList.playIndex = 0;
    this.list = list;
    this.PlayList.Reset();
    for(var i=0; i<this.list.length; i++){
        this.PlayList.Add(list[i].name, {index : i, url: list[i].url}, {stared:false});
        if(this.list[i].play){
            this.PlayList.playIndex = i;
        }
    }    
    if(!this.prepare(this.list[this.PlayList.playIndex], play)){
        return false;
    }
    this.PlayList.activeItem = this.PlayList.handle.children[this.PlayList.playIndex];
    this.domPlayerList.style.display = "none";
    if (this.list.length > 1) {
        this.handle.querySelector('#playerHideplist').style.display = "block";
        this.handle.querySelector('#playerHideplist').innerHTML = playerBtnF2sh;
    } else {
        this.handle.querySelector('#playerHideplist').style.display = "none";
    }
    if (show) {
        this.Show(true, parent);
    }
    if(play) {ListPage.Preview.setPosition(this.obj);}
	this.changeScreenMode(fullScreen);
    return true;
};

MediaPlayer.prepare = function(obj, play) {
    echo(obj,'prepare '+play);
    clearTimeout(this.timer.showInfo);
    clearTimeout(this.timer.startPlaying);
    gSTB.Set3DConversionMode && gSTB.Set3DConversionMode(0); // добавленно для совместимости с 12 версией портала
    this.domPlayerTotalTime.innerHTML = "00:00:00";
    this.domPlayerCurrentTime.innerHTML = "00:00:00";
    this.domPlayerBufferBar.style.width = '0px';
    this.domPlayerProgressBar.style.width = '0px';
    this.pos = 0;
    this.curTime = 0;
    this.totalTime = 0;
    this.infoFlag = true;
    this.countError = 0;
    this.obj = obj;    
    this.domPlayerTitle.innerHTML = obj.name;
    this.domPlayerHeader.style.display = "block";
    this.domPlayerFooter.style.display = "block";
    if (this.type !== obj.type) {
        this.type = obj.type;
        if (typeof this.init[this.type] === 'function') {
            this.init[this.type]();
        } else {
            return false;
        }
        for(var a = 0; a<this.ModalMenu.Menu.handleInner.children.length; a++){
            this.ModalMenu.Menu.Hidden(this.ModalMenu.Menu.handleInner.children[a],true);
    }
         echo('this.type='+this.type);
        if (typeof this.modalInit[this.type] === 'function') {
            this.modalInit[this.type]();
        } else {
            return false;
        }
    }
    if (play) {
        this.timer.startPlaying = window.setTimeout(function(){MediaPlayer.play();},100);
    }    
    return true;
};

MediaPlayer.play = function () {
	if ( !this.obj ) { return false; }
	gSTB.SetMode(1);
	var param = "";
	switch ( this.type ) {
		case MEDIA_TYPE_STREAM:
			break;
		case MEDIA_TYPE_IMAGE:
			this.obj.sol = "jpeg";
			if ( this.slideOn > 0 )this.timer.slideShow = window.setTimeout(function () {
				if ( MediaPlayer.list.length > 1 && MediaPlayer.fullScreen ) {
					if ( MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length ) {
						MediaPlayer.PlayList.playIndex++;
						MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
						MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
						ListPage.Preview.setPosition(MediaPlayer.obj);
					}
				}
			}, this.slideOn * 1000);
			break;
	}
	if ( proxy.length < 1 ) {
		gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url + param);
	} else {
		gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url + param, proxy);
	}
};

MediaPlayer.setPos = function(a) {
    if (!this.playNow) {
        return;
    }
    this.posModFlag = true;
    clearTimeout(this.timer.setPos);
    clearTimeout(this.timer.showInfo);
    this.posTime = "";
    this.runner.stop(); 
    if (this.pos === 0) {
        this.curTime = gSTB.GetPosTime();
        this.posIntervals.splice(0, 1);
        this.posIntervals[3] = this.posIntervals[2] + 10;
        this.infoFlag = false;
        this.showInfo(true);               
        this.pos = this.curTime;
    }
    if (this.posMod !== a) {
        this.posMod = a;
        this.posIntervals = [0, 10, 20];
    } else {
        this.posIntervals.splice(0, 1);
        this.posIntervals[this.posIntervals.length] = this.posIntervals[this.posIntervals.length - 1] + 10;
    }
    var to = 0;
    for (var i = 0; i < this.posIntervals.length; i++) {
        to += a * this.posIntervals[i];
    }
    if (to > 1800) {
        to = 1800;
    }
    if (to < -1800) {
        to = -1800;
    }
    this.pos += to;    
    if (this.pos > this.totalTime) {
        this.pos = this.totalTime - 30;
    }
    if (this.pos < 0) {
        this.pos = 3;
    }
    var curTime = this.parseTime(this.pos);
    this.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
    var px = Math.round(this.pos / this.totalTime * this.progress[screen.height]);
    this.domPlayerProgressBar.style.width = px + 'px';
    this.timer.setPos = window.setTimeout(function() {
        gSTB.SetPosTime(MediaPlayer.pos);
        MediaPlayer.pos = 0;
        MediaPlayer.domPlayerCurrentTime.className = "time_cur";
        MediaPlayer.posMod = 0;
        gSTB.Continue();
        MediaPlayer.posModFlag = false;
    }, 2000);
};

MediaPlayer.setPosTime = function(a) {
    if (!this.playNow) {
        return;
    }
    this.posModFlag = true;
    this.showInfo(true);
    this.posMod = 0;
    clearTimeout(this.timer.setPos);
    clearTimeout(this.timer.showInfo);
    this.runner.stop(); 
    this.posTime += a;
    echo(this.posTime, 'this.posTime');
    this.pos = this.splitTime(this.posTime);
    if (this.pos > this.totalTime) {
        this.pos = this.totalTime - 30;
    }
    var curTime = this.parseTime(this.pos);
    this.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
    this.domPlayerCurrentTime.className = "time_cur input";
    var px = Math.round(this.pos / this.totalTime * this.progress[screen.height]);
    this.domPlayerProgressBar.style.width = px + 'px';
    this.timer.setPos = window.setTimeout(function() {
        echo(MediaPlayer.pos, 'set pos:');
        gSTB.SetPosTime(MediaPlayer.pos);
        MediaPlayer.pos = 0;
        MediaPlayer.posTime = "";
        MediaPlayer.posMod = 0;
        MediaPlayer.domPlayerCurrentTime.className = "time_cur";
        gSTB.Continue();
        MediaPlayer.posModFlag = false;
    }, 2000);
};

MediaPlayer.playPause = function () {
	echo(pauseData.paused,'!!! pauseData.paused:');
	var self = this;
	if ( !this.playNow ) { return; }
	if ( gSTB.IsPlaying() ) {
		this.showInfo(true);
		if ( !pauseData.paused ) {
			pauseData = {
				paused     : true,
				curTime    : gSTB.GetPosTime(),
				fileLength : gSTB.GetMediaLen(),
				audioTrack : gSTB.GetAudioPID()
			};
		}
		gSTB.Pause();
		ListPage.subscribeEvents[self.EVENT_PAUSE].call(ListPage, true);
		this.runner.stop();
	} else {
		if ( proxy.length < 1 ) {
			gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url + ' position:' + pauseData.curTime);
		} else {
			gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url + ' position:' + pauseData.curTime, proxy);
		}
		ListPage.subscribeEvents[self.EVENT_PAUSE].call(ListPage, false);
		this.runner.start();
		this.timer.showInfo = window.setTimeout(function () {MediaPlayer.showInfo(false);}, 5000);//bas
	}
};

MediaPlayer.event = function ( e, info ) {
	echo(e, 'event:');
	echo(info, 'event useless info:');
	e = parseInt(e);
	switch ( e ) {
		case 1:
			switch ( MediaPlayer.type ) {
				case MEDIA_TYPE_STREAM:
					MediaPlayer.countError++;
					if ( MediaPlayer.countError < 5 ) {
						MediaPlayer.play();
					} else {
						// call subscriber hooks
						ListPage.subscribeEvents[MediaPlayer.EVENT_ERROR].call(ListPage);
						new CModalHint(currCPage, playingError, 3000);
					}
					break;
				default:
					if ( MediaPlayer.list.length > 1 /*&& MediaPlayer.fullScreen*/ ) {
						if ( MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length ) {
							MediaPlayer.PlayList.playIndex++;
							MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
							MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
							ListPage.Preview.setPosition(MediaPlayer.obj);
							break;
						}
					}
					if ( currCPage === MediaPlayer ) {
						MediaPlayer.exit();
					} else {
						MediaPlayer.end();
					}
					break;
			}
			break;
		case 2:
			switch ( MediaPlayer.type ) {
				case MEDIA_TYPE_AUDIO:
				case MEDIA_TYPE_VIDEO:
					if ( !pauseData.paused ) {
						echo('init audio track');
						MediaPlayer.curTime = 0;
						MediaPlayer.totalTime = gSTB.GetMediaLen();
						echo("MediaPlayer.curTime : " + MediaPlayer.curTime + " MediaPlayer.totalTime : " + MediaPlayer.totalTime);
						var curTime = MediaPlayer.parseTime(MediaPlayer.totalTime);
						MediaPlayer.domPlayerTotalTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
						MediaPlayer.setAudioMenu();
					} else {
						echo('restore audio track, reset pause');
						gSTB.SetAudioPID(pauseData.audioTrack);
						pauseData = {curTime : 0, fileLength : 0, paused : false, audioTrack : 0};
					}
					break;
			}
			break;
		case 4:
			if ( !MediaPlayer.playNow )
			// call subscriber hook
				ListPage.subscribeEvents[MediaPlayer.EVENT_START].call(ListPage);
			switch ( MediaPlayer.type ) {
				case MEDIA_TYPE_AUDIO:
				case MEDIA_TYPE_VIDEO:
					MediaPlayer.runner.start();
					MediaPlayer.timer.showInfo = window.setTimeout(function () {MediaPlayer.showInfo();}, 5000);//bas
					break;
				case MEDIA_TYPE_STREAM:
					MediaPlayer.timer.showInfo = window.setTimeout(function () {MediaPlayer.showInfo();}, 5000);//bas
					break;
			}
			MediaPlayer.playNow = true;
			break;
		case 5:
			// call subscriber hook
			ListPage.subscribeEvents[MediaPlayer.EVENT_ERROR].call(ListPage);
			switch ( MediaPlayer.type ) {
				case MEDIA_TYPE_VIDEO:
				case MEDIA_TYPE_STREAM:
				case MEDIA_TYPE_AUDIO:
					if ( MediaPlayer.list.length > 1 && MediaPlayer.fullScreen ) {
						if ( MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length ) {
							MediaPlayer.PlayList.playIndex++;
							MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
							MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
							ListPage.Preview.setPosition(MediaPlayer.obj);
							new CModalHint(currCPage, playingError, 3000);
						}
					} else {
						if ( currCPage === MediaPlayer ) {
							MediaPlayer.exit();
							window.setTimeout(function () {new CModalHint(currCPage, playingError, 3000);}, 50);
						} else {
							MediaPlayer.end();
						}
					}
					break;
			}
			break;
		case 6:
			// "Dual Mono" sound
//            if (stbEventCallback_DualMono) {
//                eval(stbEventCallback_DualMono);
//            }
			break;
		case 35:
			break;
		case 34:
			break;
	}
};

MediaPlayer.aspect = function() {
    clearTimeout(this.timer.hideAspect);
    this.activeAspect++;
    if (this.activeAspect >= this.aspects.length) {
        this.activeAspect = 0;
    }
    var ico = document.getElementById("mediaHeader_Aspect");
    gSTB.SetAspect(this.aspects[this.activeAspect].mode);
    ico.innerHTML = '<img src="lang/' + curLangIdx + '/' + this.aspects[this.activeAspect].img + '">';
    ico.style.display = "inline";
    this.timer.hideAspect = window.setTimeout(function() {
        document.getElementById("mediaHeader_Aspect").style.display = "none";
    }, 2000);
};


MediaPlayer.end = function () {
	if ( !this.obj ) { return true; }
	var self = this;
	// call subscriber hook
	ListPage.subscribeEvents[self.EVENT_STOP].call(ListPage);
	this.obj = null;
	this.runner.stop();
	for (var i in this.timer) { clearTimeout(this.timer[i]); }
	for (var j in this.timer) { clearInterval(this.interval[j]); }
	gSTB.Stop();
	gSTB.SetMode(0);
	this.playNow = false;
	return true;
};

MediaPlayer.exit = function () {
	var self = this;
	this.handle.querySelector('#cright').style.display = "none";
	if ( this.end() ) {
		// call all subscribers hooks
		ListPage.subscribeEvents[self.EVENT_EXIT].call(ListPage);
		this.Show(false);
	}
	return false;
};

/**
 * set coordinates from preview mode
 * @param {Number} x
 * @param {Number} y
 * @param {Number} a
 * @param {Number} b
 */
MediaPlayer.setCoord = function(x,y,a,b){
    echo('MediaPlayer.setCoord '+x+' '+y+' '+a+' '+b+ '!');
    if(x)this.coord.x = x;
    if(y)this.coord.y = y;
    if(a)this.coord.a = a;
    if(b)this.coord.b = b;    
};

/**
 * Chande screen mode and set Viewport
 * @param {Boolean} fullScreen
 */
MediaPlayer.changeScreenMode = function ( fullScreen ) {
	echo('MediaPlayer.changeScreenMode ' + fullScreen + ' ' + (this.fullScreen === fullScreen));
	if ( fullScreen === true || fullScreen === false ) {
		if ( this.fullScreen === fullScreen ) return;
		this.fullScreen = fullScreen;
	} else {
		this.fullScreen = !this.fullScreen;
	}
	if ( this.fullScreen ) {
		gSTB.SetPIG(1, 0, 0, 0);
	} else {
		if ( this.coord.a && this.coord.b ) {
			echo("SET VIEW PORT TO WINDOW");
			gSTB.SetViewport(this.coord.a, this.coord.b, this.coord.x, this.coord.y);
		}
	}
};

MediaPlayer.showInfo = function ( show, hidetime ) {
	clearTimeout(this.timer.showInfo);
	if ( show === false || show === true ) {
		if ( show === this.infoFlag ) { return; }
		this.infoFlag = !show;
	}
	if ( this.infoFlag ) {
		this.domPlayerHeader.style.display = "none";
		this.domPlayerList.style.display = "none";
		this.domPlayerFooter.style.display = "none";
	} else {
		this.domPlayerHeader.style.display = "block";
		if ( this.playListShow ) {
			this.domPlayerList.style.display = "block";
			this.PlayList.Refresh();
		}
		this.domPlayerFooter.style.display = "block";
	}
	this.infoFlag = !this.infoFlag;
	if ( hidetime ) {
		this.timer.showInfo = window.setTimeout(function () {MediaPlayer.showInfo(!MediaPlayer.infoFlag);}, hidetime);
	}
};

MediaPlayer.parseTime = function ( a ) {
	var h, m, s;
	if ( a >= 0 ) {
		h = Math.floor(a / 3600);
		m = Math.floor((a - h * 3600) / 60);
		s = a - h * 3600 - m * 60;
		if ( h < 10 )h = '0' + h;
		if ( s < 10 )s = '0' + s;
		if ( m < 10 )m = '0' + m;
	} else {
		a = Math.abs(a);
		h = Math.floor(a / 3600);
		m = Math.floor((a - h * 3600) / 60);
		s = a - h * 3600 - m * 60;
		if ( h < 10 )h = '0' + h;
		if ( s < 10 )s = '0' + s;
		if ( m < 10 )m = '0' + m;
		h = "-" + h;
	}
	return {"hour" : h, "min" : m, "sec" : s};
};

MediaPlayer.splitTime = function ( a ) {
	var s, m, h;
	a = parseInt(a);
	s = a % 100;
	m = (a % 10000 - s) / 100;
	h = Math.floor(a / 10000);
	if ( s > 59 ) {s = 59;}
	if ( m > 59 ) {m = 59;}
	return s + m * 60 + h * 3600;
};

MediaPlayer.runner = {
	id    : {},
	run   : false,
	start : function () {
		if ( this.run ) { return; }
		MediaPlayer.curTime = gSTB.GetPosTime();
		if ( MediaPlayer.curTime > MediaPlayer.totalTime ) { MediaPlayer.curTime = MediaPlayer.totalTime; }
		if ( MediaPlayer.curTime < 0 ) { MediaPlayer.curTime = 0; }
		var px = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * MediaPlayer.progress[screen.height]);
		var curTime = MediaPlayer.parseTime(MediaPlayer.curTime);
		var persent = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * 100);
		// call all subscribers hooks
		ListPage.subscribeEvents[MediaPlayer.EVENT_PROGRESS].call(ListPage, persent);
		MediaPlayer.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
		MediaPlayer.domPlayerProgressBar.style.width = px + 'px';
		this.id = window.setInterval(function () {
			MediaPlayer.curTime = gSTB.GetPosTime();
			if ( MediaPlayer.curTime > MediaPlayer.totalTime ) { MediaPlayer.curTime = MediaPlayer.totalTime; }
			if ( MediaPlayer.curTime < 0 ) { MediaPlayer.curTime = 0; }
			var persent = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * 100);
			// call all subscribers hooks
			ListPage.subscribeEvents[MediaPlayer.EVENT_PROGRESS].call(ListPage, persent);
			var px = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * MediaPlayer.progress[screen.height]);
			var curTime = MediaPlayer.parseTime(MediaPlayer.curTime);
			MediaPlayer.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
			MediaPlayer.domPlayerProgressBar.style.width = px + 'px';
		}, 1000);
		this.run = true;
	},
	stop  : function () {
		if ( this.run ) {
			this.run = false;
			clearInterval(this.id);
		}
	}
};


/**
 * Clears/inits all subscribers
 * @constructor
 */
MediaPlayer.SubscribersReset = function( ) {
    this.subscribers[this.EVENT_START] = [];
    this.subscribers[this.EVENT_STOP] = [];
    this.subscribers[this.EVENT_PAUSE] = [];
    this.subscribers[this.EVENT_PROGRESS] = [];
    this.subscribers[this.EVENT_ERROR] = [];
    this.subscribers[this.EVENT_OK] = [];
    this.subscribers[this.EVENT_EXIT] = [];
};

/**
 * Subscribe to player events
 * @param {CBase|CPage|Object} subscriber
 * @param {Number} eventType event id (see MediaPlayer.EVENT_START and so on)
 */
MediaPlayer.Subscribe = function(subscriber, eventType) {
	// valid subscriber and has associated method
    if (subscriber.subscribeEvents && subscriber.subscribeEvents[eventType] instanceof Function) {
		// doesn't already have it
		if ( this.subscribers[eventType].indexOf(subscriber) === -1 ) {
			// subscribe
			this.subscribers[eventType].push(subscriber);
	}
	}
};

/**
 * Unsubscribe from player events
 * @param {CBase|CPage|Object} subscriber
 * @param {Number} eventType event id (see MediaPlayer.EVENT_START and so on)
 */
MediaPlayer.Unsubscribe = function(subscriber, eventType) {
	// find
	var index = this.subscribers[eventType].indexOf(subscriber);
	// and remove
	if ( index !== -1 ) this.subscribers[eventType].splice(index, 1);
};

MediaPlayer.setAudioMenu = function(){
    try{
        var audArr = eval(gSTB.GetAudioPIDsEx());
    } catch(e){
        var audArr = [];
        echo(e);
    }
    var currAud = gSTB.GetAudioPID();
    this.ModalMenu.Menu.gaudio.slist.Clear();
    echo(audArr,'audArr');
    echo('audio add');
    echo('this.subtitles_on='+this.subtitles_on+' currAud='+currAud);
    if(audArr.length > 0 && audArr[0] && audArr[0].pid){
        echo('there is another audio');
        for(var i=0; i<audArr.length; i++){
            var lang_info  = getLanguageNameByCode(audArr[i].lang);
            if(!lang_info){
                lang_info    = [];
                lang_info[0] = mediaInfoMenu_langUnknown + ' ("' + getIso639LangCode(audArr[i].lang) + '")';
                lang_info[1] = "null";
            }
            var fl = currAud === audArr[i].pid;
            this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gaudio, lang_info[1], lang_info[0], {data: audArr[i].pid, marked : fl});
            if(fl){
                this.handle.querySelector('#audioType').src = "img/codec/codec"+audArr[i].type+".png";
                this.handle.querySelector('#cright').style.display = "block";
                this.timer.audio = window.setTimeout(function(){MediaPlayer.handle.querySelector('#cright').style.display = "none";},5000);//bas
        }
       }
    } else {
        echo('no other audio');
        this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gaudio, "no", LANG_MEDIA_MENU_NO, {disabled : true});
    }
    var currSub = gSTB.GetSubtitlePID();
    echo('gSTB.GetSubtitlePID()='+currSub);
    try{
        var subArr = eval(gSTB.GetSubtitlePIDs());
        echo(subArr,'normal subArr');
    } catch(e){
        echo(subArr,'error subArr');
        var subArr = [];
        echo(e);
    }
    this.ModalMenu.Menu.gsubtitle.slist.Clear();
    echo(subArr,'subArr');
    if(this.subtitles_on){        
        gSTB.SetSubtitles(true);
    } else {        
        gSTB.SetSubtitles(false);
    }
    if(subArr.length > 0){
        this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gsubtitle, "OFF", LANG_MEDIA_MENU_OFF, {data : "OFF", marked : this.subtitles_on !== true});
        for(var i=0; i<subArr.length; i++){
            lang_info  = getLanguageNameByCode(subArr[i].lang);
            echo(lang_info,'lang_info');
            if(!lang_info){
                lang_info = [];
                lang_info[0] = mediaInfoMenu_langUnknown + '&nbsp;("' + getIso639LangCode(subArr[i].lang) + '")';
                lang_info[1] = "null";
            }
            this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gsubtitle, lang_info[1], lang_info[0], {data: subArr[i].pid, marked : currSub === subArr[i].pid && this.subtitles_on === true});
        }
    } else {
        this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gsubtitle, "no", LANG_MEDIA_MENU_NO, {disabled : true});
        echo('no subtitles');
    }
};


function PlayList(parent) {
    // parent constructor
    CScrollList.call(this, parent);

    /**
     * link to the BreadCrumb component
     * @type {CBreadCrumb}
     */
    this.bcrumb = null;

    /**
     * link to the BreadCrumb component
     * @type {CSearchBar}
     */
    this.sbar = null;

    /**
     * type filter for file listing
     * @type Number
     */
    this.filterType = MEDIA_TYPE_NONE;

    /**
     * data filter for file listing
     * @type String
     */
    this.filterText = '';

    /**
     * list of all media types on the current level
     * @type {Array}
     */
    this.mtypes = [];

    /**
     * list of media objects data
     * full chain from the root
     * @type {[Object]}
     */
    this.path = [];

    /**
     * current media object opened
     * @type {Object}
     */
    this.parentItem = null;
}

// extending
extend(PlayList, CScrollList);



/**
 * Create new item and put it in the list
 * @param {String} name item label
 * @param {Object} attrs set of item data parameters
 * @param {Object} states set of additional parameters (stared)
 * @return {Node}
 */
PlayList.prototype.Add = function ( name, attrs, states ) {
	var self = this;

	// html prepare
	var body = element('div', {className : 'data'}, name);
	var star = element('div', {className : 'star'});

	// actual filling
	var item = CScrollList.prototype.Add.call(this, [body, star], {
		star          : star,
		data          : {index : attrs.index || attrs.index === 0 ? attrs.index : '', url : attrs.url ? attrs.url : "", pos : attrs.pos || attrs.pos === 0 ? attrs.pos : "", parentI : attrs.parentI || attrs.parentI === 0 ? attrs.parentI : ""},
		disabled      : attrs.disabled ? true : false,
		focused       : attrs.focused ? true : false,
		marked        : attrs.marked ? true : false,
		// handlers
		onclick       : function () {
			// open or enter the item
			this.self.Open(this.data);
			return false;
		},
		oncontextmenu : function () {
			return false;
		}
	});
	return item;
};


/**
 * Reset and clear all items
 * This will make the component ready for a new filling.
 */
PlayList.prototype.Clear = function() {
    CScrollList.prototype.Clear.call(this);
    this.filterType = MEDIA_TYPE_NONE;
    this.filterText = '';
    this.mtypes = [];
};

PlayList.prototype.Open = function(data) {
    echo(data, 'PlayList.Open :: data');
    echo(MediaPlayer.list,'MediaPlayer.list');
    MediaPlayer.prepare(MediaPlayer.list[data.index], true);
    echo(MediaPlayer.list,'MediaPlayer.list');
    this.playIndex = data.index;
    this.parentIndex = data.parentI;
    // B_A_S CAREFULL !!! 
    ListPage.Preview.setPosition(this.parent.obj);
    echo(MediaPlayer.list,'MediaPlayer.list');
};


PlayList.prototype.Reset = function() {
    this.parentItem = null;
    this.path = [];
    this.Clear();
};

PlayList.prototype.Refresh = function() {
    this.Activate();
    this.Focused(this.activeItem, true);
};

function getLanguageNameByCode ( code ) {
	if ( code.length ) {
		for (var i = 0; i < iso639.length; i++) {
			ref_codes = iso639[i].code;
			for (var j = 0; j < ref_codes.length; j++) {
				if ( ref_codes[j] == code[0].toLowerCase() ) {
					code = [];
					code[0] = iso639[i].name;
					code[1] = i;
					return code;
				}
			}
		}
	}
	return null;
}

function getIso639LangCode(langArr){
    var code = "";
    for (var i=0; i<langArr.length; i++) {
        if (langArr[i]) {
            code = langArr[i];
            break;
        }
    }
    return code;
}


MediaPlayer.addPreviewList = function ( file ) {
	var list = [];
	if ( file ) {
		file.play = true;
		list.push(file);
	}
	// get all marked items
	var items = CSListManager.Current().Find({marked : true});
	echo('FIND marked items?');
	// no marked so take all available
	if ( items.length === 0 ) {
		echo('NOPE');
		items = Array.prototype.slice.call(CSListManager.Current().handleInner.children);
	}
	// prepare play list
	items.forEach(function ( item ) {
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
	//    MediaPlayer.preparePlayer(list, ListPage, true, true, true);
	return list;
};