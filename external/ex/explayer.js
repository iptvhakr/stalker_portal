
/**
 * global player object
 * @type {CPage}
 */
var stbEvent = {};
var MediaPlayer = new CPage();

// получение переменных среды
function loadEnvironmentVars(){
    var varList = ["screen_clock", "video_clock","autoupdate_cond",
        "lang_audiotracks","audio_initial_volume",
        "subtitles_on", "lang_subtitles", "language",
        "graphicres", "tvsystem", "subtitles_on"];
    var query = '{"varList":["'+varList.join('","')+'"]}';
    try{
        environment = JSON.parse(gSTB.GetEnv(query)).result;
        if(!environment.lang_audiotracks) environment.lang_audiotracks = DEFAULT_AUDIO_LANG_1_IDX;
        if(environment.subtitles_on == "true"){
            if(!environment.lang_subtitles) environment.lang_subtitles = DEFAULT_SUBTITLE_LANG_1_IDX;
        } else {
            environment.lang_subtitles = -1;
        }
        if(environment.autoupdate_cond == "") environment.autoupdate_cond = 0; else environment.autoupdate_cond = parseInt(environment.autoupdate_cond);
        if(environment.betaupdate_cond == "1" || environment.betaupdate_cond == "true") environment.betaupdate_cond = true; else  environment.betaupdate_cond = false;
        if(environment.ts_on == "true") environment.ts_on = true; else  environment.ts_on = false;
        if(environment.ts_icon == "true") environment.ts_icon = true; else  environment.ts_icon = false;        
        if(environment.subtitles_on == "true") environment.subtitles_on = true; else  environment.subtitles_on = false; 
        if(environment.audio_initial_volume && environment.audio_initial_volume>=0 && environment.audio_initial_volume <=100) {curVolume = environment.audio_initial_volume};
    } catch(e){
        echo(e,"Environment load");
    }  
}


loadEnvironmentVars();


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

//  timeShift.SetMaxDuration(environment.ts_time);
//  timeShift.SetTimeShiftFolder(environment.ts_path + '/records');
    
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
    this.favorites = false;
    this.fullScreen = true;
    this.type = null;               // type of the object: "media", "image" ...
    this.obj = null;
    this.timer = {};
    this.interval = {};
//  this.dataDVD = {};
    this.slideOn = 0;
    
    /**
     * list of callback events with their subscribers
     * @type {Object}
     */
    this.subscribers = {};
    this.SubscribersReset();
    
    this.playListShow = false;
    
//    this.ts_icon = environment.ts_icon;
    this.ts_inProgress = false;
    this.ts_active = false;
//    this.ts_on = environment.ts_on;
//    MediaPlayer.ts_endType = environment.ts_endType;
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
//  this.domHeaderBlue = this.handle.querySelector('#playerHeaderBlue');

    
    this.handle.querySelector('#playerHideplist').innerHTML = playerBtnF2sh;
    this.handle.querySelector('#playerHeaderSetting').innerHTML = playerBtnMenu;
    this.handle.querySelector('#playerHideplayer').innerHTML = playerBtnInfo;
//  this.domHeaderBlue.innerHTML = LANG_MEDIA_BTN_F4_ADD;
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
//            self.handle.querySelector("#playerListBox").style.display = "block";
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
//  this.init[MEDIA_TYPE_RECORDS_ITEM] = this.init[MEDIA_TYPE_VIDEO];
    this.init[MEDIA_TYPE_AUDIO] = this.init[MEDIA_TYPE_VIDEO];
//  this.init[MEDIA_TYPE_CUE_ITEM] = this.init[MEDIA_TYPE_VIDEO];
    this.init[MEDIA_TYPE_STREAM] = function(refresh) {
        if (!refresh) {
            self.domPlayerBar.style.display = "none";
            self.domPlayerCurrentTime.style.display = "none";
            self.domPlayerBufferBar.style.display = "none";
            self.domPlayerProgressBar.style.display = "none";
            self.handle.querySelector("#playerHideplist").style.display = "none";
//            self.handle.querySelector("#playerListBox").style.display = "none";
            self.handle.querySelector("#playerPause").style.display = "none";
            self.handle.querySelector("#playerREW").style.display = "none";
            self.handle.querySelector("#playerFFWD").style.display = "none";
            self.handle.querySelector("#playerTotalTime").style.display = "none";
            self.handle.querySelector("#playerCurrentTime").style.display = "none";
            self.handle.querySelector("#playerSlash").style.display = "none";
        }    
    };
/*    
    this.init[MEDIA_TYPE_STREAM_TS] = function(refresh) {
        if (!refresh) {
            self.init[MEDIA_TYPE_VIDEO]();
            self.domPlayerBufferBar.style.display = "block";
            if (self.ts_endType === 2) {
                timeShift.SetSlidingMode(false);
            } else {
                timeShift.SetSlidingMode(true);
            }            
        }
        self.ts_active = false;

        var curTime = self.parseTime(environment.ts_time);
        self.domPlayerTotalTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
    };
*/    
    this.init[MEDIA_TYPE_IMAGE] = function() {
        self.domPlayerBar.style.display = "none";
        self.domPlayerCurrentTime.style.display = "none";
        self.domPlayerBufferBar.style.display = "none";
        self.domPlayerProgressBar.style.display = "none";
//        self.handle.querySelector("#playerListBox").style.display = "none";
        self.handle.querySelector("#playerPause").style.display = "none";
        self.handle.querySelector("#playerREW").style.display = "none";
        self.handle.querySelector("#playerFFWD").style.display = "none";
        self.handle.querySelector("#playerTotalTime").style.display = "none";
        self.handle.querySelector("#playerCurrentTime").style.display = "none";
        self.handle.querySelector("#playerSlash").style.display = "none";
    }; 
/*    
    this.init[MEDIA_TYPE_ISO] = function(refresh) {
        self.handle.querySelector("#playerHideplist").style.display = "block";
        if (!refresh) {
            self.init[MEDIA_TYPE_VIDEO]();
        }
    };
*/
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
/*    
    this.ModalMenu.Menu.gts = this.ModalMenu.Menu.AddGroup('gts', LANG_MEDIA_MENU_GROUPS['gts'], {
        onclick: function() {
            var iid = this.iid;
            if (this.data !== self.ts_on) {
                self.ts_on = this.data;
                (self.ModalMenu.Menu.gts.slist.states.marked || []).forEach(function(item) {
                    item.self.Marked(item, false);
                });
                self.ModalMenu.Menu.gts.slist.Marked(this, true);
                if(self.ts_on){
                    if (self.obj.sol === "extTimeShift" && !self.ts_inProgress && self.type === MEDIA_TYPE_STREAM) {
                        if (!self.checkUsb(self.ts_path)) {
                            new CModalConfirm(currCPage, err_ERROR, alert_no_usb,
                                    mb_sel_charsetCancel, function(){},
                                    mb_sel_charsetOk, function(){MediaPlayer.tsOnOff(false);});
                        } else {
                            self.ts_start(0, true);
                        }
                    }                   
                } else {
                    if(self.ts_inProgress){
                        self.tsExitCheck("stop");
                    }
                }
            }            
            self.ModalMenu.Show(false);
            return false;
        }
    });   
    
    
    this.ModalMenu.Menu.gts.ion = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gts, MEDIA_ACTION_TS_ON, LANG_MEDIA_MENU_ACTIONS[MEDIA_ACTION_TS_ON], {data: true});
    this.ModalMenu.Menu.gts.ioff = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gts, MEDIA_ACTION_TS_OFF, LANG_MEDIA_MENU_ACTIONS[MEDIA_ACTION_TS_OFF], {data: false});
    
    this.ModalMenu.Menu.gtsend = this.ModalMenu.Menu.AddGroup('gtsend', LANG_MEDIA_MENU_GROUPS['gtsend'], {
        onclick: function() {
            if (this.data !== self.ts_endType) {
                self.ts_endType = this.data;
                (self.ModalMenu.Menu.gtsend.slist.states.marked || []).forEach(function(item) {
                    item.self.Marked(item, false);
                });
                self.ModalMenu.Menu.gtsend.slist.Marked(this, true);
                if(self.ts_endType === 1){
                    timeShift.SetSlidingMode(true);
                } else {
                    timeShift.SetSlidingMode(false);
                }
            }
            
            self.ModalMenu.Show(false);
            return false;
        }
    });

    this.ModalMenu.Menu.gtsend.iciclick = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gtsend, MEDIA_ACTION_TSEND_CICLICK, LANG_MEDIA_MENU_ACTIONS[MEDIA_ACTION_TSEND_CICLICK], {data: 1});
    this.ModalMenu.Menu.gtsend.istop = this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gtsend, MEDIA_ACTION_TSEND_STOP, LANG_MEDIA_MENU_ACTIONS[MEDIA_ACTION_TSEND_STOP], {data: 2});
*/    
    this.ModalMenu.Menu.g3d = this.ModalMenu.Menu.AddGroup('g3d', LANG_MEDIA_MENU_GROUPS['g3d'], {
        onclick: function() {
            (self.ModalMenu.Menu.g3d.slist.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            self.ModalMenu.Menu.g3d.slist.Marked(this, true);
            self.ModalMenu.Show(false);
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

    this.modalInit[MEDIA_TYPE_STREAM] = function() {
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gaudio,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gsubtitle,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gts,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gtsend,false);
        self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gaudio,false);
        if (self.ts_on) {
            self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ion, true);
            self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ioff, false);
        } else {
            self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ion, false);
            self.ModalMenu.Menu.gts.slist.Marked(self.ModalMenu.Menu.gts.ioff, true);
        }
        if (self.ts_endType === 2) {
            self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.istop, true);
            self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.iciclick, false);
        } else {
            self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.iciclick, true);
            self.ModalMenu.Menu.gtsend.slist.Marked(self.ModalMenu.Menu.gtsend.istop, false);
        }        
    };
/*  this.modalInit[MEDIA_TYPE_STREAM_TS] = this.modalInit[MEDIA_TYPE_STREAM];
    
    this.modalInit[MEDIA_TYPE_ISO] = function() {
        self.modalInit[MEDIA_TYPE_VIDEO]();
    };*/
    this.modalInit[MEDIA_TYPE_IMAGE] = function() {
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gslideOn,false);
        self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gslideOn,false);
    };
    this.modalInit[MEDIA_TYPE_AUDIO] = function() {
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gaudio,false);
        self.ModalMenu.Menu.Hidden(self.ModalMenu.Menu.gsubtitle,false);
        self.ModalMenu.Menu.Switch(self.ModalMenu.Menu.gaudio,false);
    };
//  this.modalInit[MEDIA_TYPE_CUE_ITEM] = this.modalInit[MEDIA_TYPE_AUDIO];
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
            if (MediaPlayer.list.length > 1 && MediaPlayer.PlayList.activeItem.data.index + 1 < MediaPlayer.list.length) {
                MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.Next(), true);
				MediaPlayer.PlayList.Open(MediaPlayer.PlayList.activeItem.data);
                //MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index],true);
            }
            e.preventDefault();
            break;
        case KEYS.PAGE_UP:
            if (MediaPlayer.list.length > 1 && MediaPlayer.PlayList.activeItem.data.index > 0) {
                MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.Next("", true), true);
                MediaPlayer.PlayList.Open(MediaPlayer.PlayList.activeItem.data);
            }
            e.preventDefault();
            break;
        case KEYS.OK: // Ok/enter
            echo('MediaPlayer.KEYS.OK;');
            if(MediaPlayer.posModFlag){
                clearTimeout(this.timer.setPos);
                echo(MediaPlayer.pos, 'set pos:')
                gSTB.SetPosTime(MediaPlayer.pos);
                MediaPlayer.pos = 0;
                MediaPlayer.posTime = "";
                MediaPlayer.posMod = 0;
                MediaPlayer.domPlayerCurrentTime.className = "time_cur";
                gSTB.Continue();
                MediaPlayer.posModFlag = false;
                MediaPlayer.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo(false);},10);//BAS
                e.preventDefault();
                break;
            }
            if(MediaPlayer.playListShow && MediaPlayer.infoFlag){
                MediaPlayer.PlayList.EventHandler(e);
                echo('MediaPlayer.PlayList.EventHandler(e);');
                break;
            }

            /*BAS*/ ListPage.subscribeEvents[MediaPlayer.EVENT_OK].call(ListPage);
            // call all subscribers hooks
/*          MediaPlayer.subscribers[MediaPlayer.EVENT_OK].forEach(function(subscriber) {
                subscriber.subscribeEvents[MediaPlayer.EVENT_OK].call(subscriber);
            });
            switch (MediaPlayer.parent) {
                case IPTVChannels:
                    if (IPTVChannels.goToChFlag) {
                        clearTimeout(IPTVChannels.timer.goToChannel);
                        IPTVChannels.TVList.goToChannel(parseInt(IPTVChannels.TVnumber) + IPTVChannels.TVList.channelStart);
                        IPTVChannels.TVnumber = "";
                        IPTVChannels.goToChFlag = false;
                        IPTVChannels.domChannelNumber.style.display = "none";
                        MediaPlayer.domChannelNumber.style.display = "none";
                    } else {
                        MediaPlayer.Show(false);
                    }*/
            break;
/*          }
            break;*/
        case KEYS.INFO:
            MediaPlayer.showInfo();
            break;
        case KEYS.MENU:
            MediaPlayer.ModalMenu.Show(true);
            break;
        case KEYS.BACK:
/*          if (IPTVChannels.goToChFlag && MediaPlayer.parent === IPTVChannels) {
                IPTVChannels.actionBack();
                break;
            }
*/
        case KEYS.EXIT: // Exit
            MediaPlayer.exit();                        
            break;                    
        case KEYS.FRAME: // Fullscreen
            MediaPlayer.aspect();
            break;
        case KEYS.PLAY_PAUSE:
            MediaPlayer.playPause();
            break;
        case KEYS.STOP:/*
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_STREAM_TS:
                    MediaPlayer.tsExitCheck("stop");
                    break;
                default:*/
            MediaPlayer.exit();
            break;
/*          }
            break;*/
        case KEYS.LEFT:    
        case KEYS.REWIND:
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_VIDEO:
//              case MEDIA_TYPE_ISO:
                case MEDIA_TYPE_AUDIO:    
//                case MEDIA_TYPE_RECORDS_ITEM:
//                case MEDIA_TYPE_STREAM_TS:
                    MediaPlayer.setPos(-1);
                    break;
            }
            break;
        case KEYS.RIGHT:
        case KEYS.FORWARD:
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_VIDEO:
//              case MEDIA_TYPE_ISO:
                case MEDIA_TYPE_AUDIO:    
//              case MEDIA_TYPE_RECORDS_ITEM:
//              case MEDIA_TYPE_STREAM_TS:
                    MediaPlayer.setPos(1);
                    break;
            }
            break;
/*       case KEYS.CHANNEL_NEXT:
            switch (MediaPlayer.parent) {
                case IPTVChannels:
                    var item = IPTVChannels.TVList.Next(false,false);
                    if(item.data.type === MEDIA_TYPE_STREAM){
                        clearTimeout(IPTVChannels.timer.goToChannel);
                        MediaPlayer.domChannelNumber.innerHTML = (item.data.index -IPTVChannels.TVList.channelStart);
                        MediaPlayer.domChannelNumber.style.display = "block";
                        IPTVChannels.TVList.Focused(item,true);                        
                        IPTVChannels.timer.goToChannel = window.setTimeout(function() {                            
                            MediaPlayer.domChannelNumber.style.display = "none";
                        }, 2000);
                    }                    
                    break;
            }
            break;
        case KEYS.CHANNEL_PREV:
            switch (MediaPlayer.parent) {
                case IPTVChannels:
                    var item = IPTVChannels.TVList.Next(false,true);
                    if(item.data.type === MEDIA_TYPE_STREAM){
                        clearTimeout(IPTVChannels.timer.goToChannel);
                        MediaPlayer.domChannelNumber.innerHTML = (item.data.index -IPTVChannels.TVList.channelStart);
                        MediaPlayer.domChannelNumber.style.display = "block";
                        IPTVChannels.TVList.Focused(item,true);                        
                        IPTVChannels.timer.goToChannel = window.setTimeout(function() {                            
                            MediaPlayer.domChannelNumber.style.display = "none";
                        }, 2000);
                    } 
                    break;
            }
            break;*/
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
                if (MediaPlayer.playListShow) {
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
    play = play === true ? true : false;
    this.fullScreen = fullScreen === false ? false : true;
    this.fullScreen = !this.fullScreen;
/*  this.favorites = favorites === false ? false : true;
    if (this.favorites) {
        this.domHeaderBlue.style.display = "block";
    } else {
        this.domHeaderBlue.style.display = "none";
    }
*/
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
    gSTB.Set3DConversionMode && gSTB.Set3DConversionMode(0);
//  this.domTSIndicator.style.display = 'none';
    this.domPlayerTotalTime.innerHTML = "00:00:00";
    this.domPlayerCurrentTime.innerHTML = "00:00:00";
    this.domPlayerBufferBar.style.width = '0px';
    this.domPlayerProgressBar.style.width = '0px';
    this.pos = 0;
    this.posInerval = 0;
    this.curTime = 0;
    this.totalTime = 0;
    this.buffTime = 0;
    this.infoFlag = true;
    this.ts_inProgress = false;
    this.countError = 0;
    this.obj = obj;    
//  if (this.parent === IPTVChannels) {
//      if(IPTVChannels.TVList.activeItem)this.domPlayerTitle.innerHTML = IPTVChannels.TVList.activeItem.data.number + '&nbsp;&nbsp;&nbsp;' + obj.name;
//  } else {
    this.domPlayerTitle.innerHTML = obj.name;
//  }    
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

MediaPlayer.play = function() {
    if(!this.obj){
        return false;
    }
    gSTB.SetMode(1);
    var param = "";
    switch (this.type) {
        case MEDIA_TYPE_STREAM:
            if (this.obj.tsOn && this.ts_on) {
                this.oldSol = this.obj.sol;
                this.oldURL = this.obj.url;
                this.obj.sol = "extTimeShift";
                this.obj.url = this.obj.url.replace(/^[A-Za-z0-9]+\s/,'');
//                if (!this.checkUsb(environment.ts_path)) {
//                    new CModalConfirm(currCPage, err_ERROR, alert_no_usb,
//                        mb_sel_charsetCancel, function(){},
//                        mb_sel_charsetOk, function(){MediaPlayer.tsOnOff(false);});
            }
//            }
            break;
        case MEDIA_TYPE_IMAGE:
            this.obj.sol = "jpeg";
            if(this.slideOn > 0)this.timer.slideShow = window.setTimeout(function(){
                if (MediaPlayer.list.length > 1 && MediaPlayer.fullScreen) {
                    if (MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length) {
                        MediaPlayer.PlayList.playIndex ++;
                        MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
                        MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
                        ListPage.Preview.setPosition(MediaPlayer.obj);
                    }                        
                }},this.slideOn*1000);
            break;
/*      case MEDIA_TYPE_RECORDS_ITEM:
            this.obj.sol = "extTimeShiftPlayer";
            break;
        case MEDIA_TYPE_ISO:
            this.obj.sol = "extBDDVD";
            break;
        case MEDIA_TYPE_CUE_ITEM:
            param = " position:"+this.obj.time;
            break;*/
    }
    if(proxy.length<1){
    gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url + param);}else{
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
/*      if(MediaPlayer.obj.type === MEDIA_TYPE_CUE_ITEM && MediaPlayer.list.length>1){
            for(var i=0; i<MediaPlayer.list.length; i++){
                if(MediaPlayer.pos > MediaPlayer.list[i].time){
                    MediaPlayer.PlayList.playIndex = i-1;
                    MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
                    MediaPlayer.obj = MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index];
                    MediaPlayer.domPlayerTitle.innerHTML = MediaPlayer.obj.name;
                    break;
                }
            }
        }*/
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
    echo(this.posTime, 'this.posTime')
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
        echo(MediaPlayer.pos, 'set pos:')
        gSTB.SetPosTime(MediaPlayer.pos);
        MediaPlayer.pos = 0;
        MediaPlayer.posTime = "";
        MediaPlayer.posMod = 0;
        MediaPlayer.domPlayerCurrentTime.className = "time_cur";
        gSTB.Continue();
        MediaPlayer.posModFlag = false;
    }, 2000);
};

MediaPlayer.playPause = function() {
	var self = this;
    if (!this.playNow) {
        return;
    }
    if(gSTB.IsPlaying()){        
        this.showInfo(true);
        pause_time_data = gSTB.GetPosTime();
        gSTB.Pause();
//	this.subscribers[this.EVENT_PAUSE].forEach(function(subscriber) {
		ListPage.subscribeEvents[self.EVENT_PAUSE].call(ListPage, true);
//	});
 
        this.runner.stop();
//      }
    } else {
//        gSTB.Continue();
        if(proxy.length<1){
        gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url +' position:'+pause_time_data);}else{
        gSTB.Play((this.obj.sol ? this.obj.sol + ' ' : 'auto ') + this.obj.url +' position:'+pause_time_data, proxy);
        }
//		this.subscribers[this.EVENT_PAUSE].forEach(function(subscriber){
			ListPage.subscribeEvents[self.EVENT_PAUSE].call(ListPage, false);
//		});
                
        this.runner.start();
//        }
        this.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo(false);},5000);//bas
    } 
};

MediaPlayer.event = function(e, info) {
    echo('event : ' + e);
    e = parseInt(e);    
    switch (e) {
        case 1:
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_STREAM:
                    MediaPlayer.countError++;
                    if (MediaPlayer.countError < 5) {
                    MediaPlayer.play();
                    } else {
						// call all subscribers hooks
//                        MediaPlayer.subscribers[MediaPlayer.EVENT_ERROR].forEach(function(subscriber) {
                            ListPage.subscribeEvents[MediaPlayer.EVENT_ERROR].call(ListPage);
//                        });
                        new CModalHint(currCPage, playingError, 3000);
                    }
                    break;
                default:
                    if (MediaPlayer.list.length > 1 /*&& MediaPlayer.fullScreen*/) {
                        if (MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length) {
                            MediaPlayer.PlayList.playIndex ++;
                            MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
                            MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
                            ListPage.Preview.setPosition(MediaPlayer.obj);
                            break;
                        }
                        }
                        if (currCPage === MediaPlayer) {
                            MediaPlayer.exit();
                        } else {
                            MediaPlayer.end();
                        }
                    break;
            }
            break;
        case 2:
            switch (MediaPlayer.type) {
//              case MEDIA_TYPE_ISO:                    
//              case MEDIA_TYPE_RECORDS_ITEM:
                case MEDIA_TYPE_AUDIO:
//              case MEDIA_TYPE_CUE_ITEM:
                case MEDIA_TYPE_VIDEO:
                    MediaPlayer.curTime = 0;
                    MediaPlayer.totalTime = gSTB.GetMediaLen();
                    echo("MediaPlayer.curTime : "+MediaPlayer.curTime+" MediaPlayer.totalTime : "+MediaPlayer.totalTime);
                    var curTime = MediaPlayer.parseTime(MediaPlayer.totalTime);
                    MediaPlayer.domPlayerTotalTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;                    
                    break;
            }
            MediaPlayer.setAudioMenu();
            break;
        case 4:
            if (!MediaPlayer.playNow) 
				// call all subscribers hooks
//				MediaPlayer.subscribers[MediaPlayer.EVENT_START].forEach(function(subscriber){
					ListPage.subscribeEvents[MediaPlayer.EVENT_START].call(ListPage);
//				});
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_AUDIO:
                case MEDIA_TYPE_VIDEO:
                    MediaPlayer.runner.start();
                    MediaPlayer.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo();},5000);//bas
                    break;
                case MEDIA_TYPE_STREAM:
                    MediaPlayer.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo();},5000);//bas
                    break;                    
            }
            MediaPlayer.playNow = true;
            break;
        case 5:
			// call all subscribers hooks
//			MediaPlayer.subscribers[MediaPlayer.EVENT_ERROR].forEach(function(subscriber){
				ListPage.subscribeEvents[MediaPlayer.EVENT_ERROR].call(ListPage);
//			});
            switch (MediaPlayer.type) {
                case MEDIA_TYPE_VIDEO:
//              case MEDIA_TYPE_STREAM_TS:
                case MEDIA_TYPE_STREAM:
                case MEDIA_TYPE_AUDIO:
//              case MEDIA_TYPE_CUE_ITEM:
                    if (MediaPlayer.list.length > 1 && MediaPlayer.fullScreen) {
                        if (MediaPlayer.PlayList.playIndex + 1 < MediaPlayer.list.length) {
                            MediaPlayer.PlayList.playIndex ++;
                            MediaPlayer.PlayList.Focused(MediaPlayer.PlayList.handle.children[MediaPlayer.PlayList.playIndex], true);
                            MediaPlayer.prepare(MediaPlayer.list[MediaPlayer.PlayList.activeItem.data.index], true);
                            ListPage.Preview.setPosition(MediaPlayer.obj);
                            new CModalHint(currCPage, playingError, 3000);
                        }
                    } else {
                        if (currCPage === MediaPlayer) {
                        MediaPlayer.exit();
                            window.setTimeout(function(){new CModalHint(currCPage,playingError,3000);},50);
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
    
    info = JSON.parse(info);
    echo(info, 'info');
    if (!MediaPlayer.ts_inProgress) {
        return;
    }
    switch (info.event_code) {
        case 1:
            break;
        case 2:
            break;
        case 3:
            gSTB.SetPosTime(12);
            gSTB.Continue();
            break;
        case 4:
            break;
        case 5:
            MediaPlayer.showInfo(true);
            break;
        case 6:
            if (!gSTB.IsPlaying()) {
                gSTB.Continue();
            }
            switch (parseInt(info.event_data)) {
                case 1:
                    MediaPlayer.ts_inProgress = false;
                    MediaPlayer.obj.sol = MediaPlayer.oldSol;
                    MediaPlayer.obj.url = MediaPlayer.oldURL;
//                    MediaPlayer.play();
/*                  if (environment.ts_icon) {
                        MediaPlayer.domTSIndicator.style.display = "none";
                    }
                    new CModalConfirm(currCPage, err_ERROR, alert_no_usb,
                        mb_sel_charsetCancel, function(){},
                        mb_sel_charsetOk, function(){MediaPlayer.tsOnOff(false)});*/                    
                    break;
                case 2:
//                    MediaPlayer.tsExitCheck('error');
                    break;
            }
            break;
        case 7:
/*
            if (MediaPlayer.ts_inProgress && MediaPlayer.ts_endType === 2) {
                stb.Pause();
                document.getElementById('ts_playbutt').style.background = 'url(img/' + win.width + '/audioplay_pause.png) no-repeat';
                var del = ts_sol[win.width];
                var wid = del[0] - del[1];
                document.getElementById('ts_curtime').innerHTML = '-' + secondsToTimeString(0);
                document.getElementById('ts_progress').style.width = '100%';
                document.getElementById('ts_runner').style.marginLeft = wid + 'px';
                document.getElementById('ts_setpos_form').style.display = 'block';
                clearInterval(ts_runInterval);
            }
                                                                 */
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

/*
MediaPlayer.tsOnOff = function(flag) {
    if (this.ts_on === flag) {
        return;
    }
    this.ts_on = flag;
    if (this.ts_on) {
        this.play();
    } else {
        this.obj.sol = this.oldSol;
        this.obj.url = this.oldURL;
        this.type = this.obj.type;
        this.init[this.type]();
    }
};

MediaPlayer.ts_start = function(start, stop) {
    if(!start)start = 0;
    clearTimeout(this.timer.ts_timer);
    this.timer.ts_timer = window.setTimeout(function() {
        var done = timeShift.EnterTimeShift();
        if (done === 0) {
            if (MEDIA_TYPE_STREAM_TS !== MediaPlayer.type) {
                MediaPlayer.type = MEDIA_TYPE_STREAM_TS;
                MediaPlayer.init[MEDIA_TYPE_STREAM_TS]();
            }            
            MediaPlayer.showInfo();
            MediaPlayer.ts_inProgress = true;
            if (this.fullScreen) {
                if (environment.ts_icon) {
                    this.domTSIndicator.style.display = 'block';
                }
            }
            IPTVChannels.TVList.activeItem.querySelector('.timeshift').className = "timeshift tsActive";
            if (stop) {
                gSTB.Pause();
                MediaPlayer.showInfo(true);
            }
        } else {
            MediaPlayer.ts_inProgress = false;
            new CModalAlert(currCPage, LANG_MEDIA_DEFAULT_ERROR, alert_tsError, lang.alertClose);
        }
    }, start * 1000);
};

MediaPlayer.checkUsb = function(usb) {
    try {
        var usbs = JSON.parse(stb.RDir("get_storage_info"));
    } catch (e) {
        echo(e, 'parse get_storage_info in MediaPlayer.checkUsb');
        return false;
    }
    for (var i = 0; i < usbs.length; i++) {
        if (usb === usbs[i].mountPath && usbs[i].isReadOnly === 0) {
            return true;
        }
    }
    return false;
};


MediaPlayer.tsExitCheck = function(type, item) {
    gSTB.Pause();
    MediaPlayer.runner.stop();
    IPTVChannels.TVList.activeItem.querySelector('.timeshift').className = "timeshift tsOn";
    if (environment.ts_icon) {
        MediaPlayer.domTSIndicator.style.display = 'none';
    }
    clearTimeout(MediaPlayer.timer.ts_timer);
    echo(environment.ts_exitType, 'environment.ts_exitType');
    switch (parseInt(environment.ts_exitType)) {
        case 1:
            timeShift.ExitTimeShift();
            gSTB.Continue();
            MediaPlayer.ts_inProgress = false;
            return false;
        case 2:
            MediaPlayer.tsSave(type, item);
            return true;
        case 3:
            new CModalConfirm(currCPage, sett_user_tsEnd3, conf_ts_save,
                    mb_sel_charsetCancel, function(){MediaPlayer.tsExit(type,item);},
                    mb_sel_charsetOk, function(){MediaPlayer.tsSave(type,item);}
            );
            return true;
    }
    return false;
};

MediaPlayer.tsSave = function(type, item) {
    var date = new Date();
    var day = date.getDate();
    var mon = date.getMonth() + 1;
    var year = date.getFullYear();
    var min = date.getMinutes();
    var hour = date.getHours();
    var sec = date.getSeconds();
    var cor = timeShift.ExitTimeShiftAndSave(Utf8.encode(environment.ts_path + '/records/' + this.obj.name + '/' + year + '-' + (mon > 9 ? mon : '0' + mon) + '-' + (day > 9 ? day : '0' + day) + '/'), (hour > 9 ? hour : '0' + hour) + '-' + (min > 9 ? min : '0' + min) + '-' + (sec > 9 ? sec : '0' + sec));
    if (cor !== 0) {
        new CModalAlert(currCPage,msgwin_title_ERROR, ts_save_error,lang.alertClose,function(){if(type === exit)MediaPlayer.tsExit(type,item);});
    } else {
        new CModalHint(currCPage, ts_save_to + ' Records/' + this.obj.name + '/' + year + '-' + (mon > 9 ? mon : '0' + mon) + '-' + (day > 9 ? day : '0' + day) + '/' + (hour > 9 ? hour : '0' + hour) + ':' + (min > 9 ? min : '0' + min) + ':' + (sec > 9 ? sec : '0' + sec) + '.tspinf', 3000);
    }
    MediaPlayer.ts_inProgress = false;
    switch (type) {
        case 'error':
            new CModalConfirm(currCPage, err_ERROR, alert_no_usb,
                    mb_sel_charsetCancel, function(){},
                    mb_sel_charsetOk, function(){MediaPlayer.tsOnOff(false)});
        case 'stop':
            MediaPlayer.obj.url = MediaPlayer.oldURL;
            MediaPlayer.obj.sol = MediaPlayer.oldSol;
            MediaPlayer.type = MediaPlayer.obj.type;
            MediaPlayer.init[MediaPlayer.type]();
            MediaPlayer.play();
            break;
        case 'exit':
            if (cor === 0) {
                window.setTimeout(function(){MediaPlayer.end();currCPage.Show(false)},50);
            }
            break;
        case 'focus':
            window.setTimeout(function(){IPTVChannels.TVList.Focused(item,true,true);},100);
            break;
    }
};

MediaPlayer.tsExit = function(type, item) {
    timeShift.ExitTimeShift();
    MediaPlayer.ts_inProgress = false;
    switch (type) {
        case 'error':
            new CModalConfirm(currCPage, err_ERROR, alert_no_usb,
                    mb_sel_charsetCancel, function(){},
                    mb_sel_charsetOk, function(){MediaPlayer.tsOnOff(false)});
        case 'stop':
            echo('stop');
            MediaPlayer.obj.url = MediaPlayer.oldURL;
            MediaPlayer.obj.sol = MediaPlayer.oldSol;
            MediaPlayer.type = MediaPlayer.obj.type;
            MediaPlayer.init[MediaPlayer.type]();
            MediaPlayer.play();
            break;
        case 'exit':
            window.setTimeout(function(){MediaPlayer.end();currCPage.Show(false)},50);
            break;
        case 'focus':
            window.setTimeout(function(){IPTVChannels.TVList.Focused(item,true,true);},100);
            break;
    }
};

*/
MediaPlayer.end = function() {
    if(!this.obj){
        return true;
    }
    var self = this;
/*    
    if (this.ts_inProgress) {
        if (this.tsExitCheck('exit')) {
            return false;
        }
    } 
*/    
    // call all subscribers hooks
//  this.subscribers[this.EVENT_STOP].forEach(function(subscriber) {
        ListPage.subscribeEvents[self.EVENT_STOP].call(ListPage);
//  });
    this.obj = null;
    this.runner.stop();
    for (var i in this.timer) {
        clearTimeout(this.timer[i]);
    }
    for (var i in this.timer) {
        clearInterval(this.interval[i]);
    }
    gSTB.Stop();
    gSTB.SetMode(0);
    this.playNow = false;
    return true;
};

MediaPlayer.exit = function() {
	var self = this;
    this.handle.querySelector('#cright').style.display = "none"
    if(this.end()){
    
    
    
    	// call all subscribers hooks
//	this.subscribers[this.EVENT_EXIT].forEach(function(subscriber){
		ListPage.subscribeEvents[self.EVENT_EXIT].call(ListPage);
//	});
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
MediaPlayer.changeScreenMode = function(fullScreen) {
    echo('!!!!!!!!!!!    MediaPlayer.changeScreenMode ' + fullScreen + ' ' + (this.fullScreen === fullScreen))
    if (fullScreen === true || fullScreen === false) {
        if(this.fullScreen === fullScreen) return;
        this.fullScreen = fullScreen;
    } else {
        this.fullScreen = !this.fullScreen;
    }
    if (this.fullScreen) {
        gSTB.SetPIG(1, 0, 0, 0);
    } else {
        if (this.coord.a && this.coord.b) {
            echo("SET VIEW PORT TO WINDOW");
            gSTB.SetViewport(this.coord.a, this.coord.b, this.coord.x, this.coord.y);
    }
    }
};

MediaPlayer.showInfo = function(show,hidetime) {
    clearTimeout(this.timer.showInfo);
    if (show === false || show === true) {
        if (show === this.infoFlag) {
            return;
        }
        this.infoFlag = !show;
    }
    if (this.infoFlag) {
        this.domPlayerHeader.style.display = "none";
        this.domPlayerList.style.display = "none";
        this.domPlayerFooter.style.display = "none";
    } else {
        this.domPlayerHeader.style.display = "block";
        if (this.playListShow) {
            this.domPlayerList.style.display = "block";
            this.PlayList.Refresh();
        }
        this.domPlayerFooter.style.display = "block";
    }
    this.infoFlag = !this.infoFlag;
    if(hidetime){
        this.timer.showInfo = window.setTimeout(function(){MediaPlayer.showInfo(!MediaPlayer.infoFlag);},hidetime);
    }
};

MediaPlayer.parseTime = function(a) {
    if (a >= 0) {
        var h = Math.floor(a / 3600);
        var m = Math.floor((a - h * 3600) / 60);
        var s = a - h * 3600 - m * 60;
        if(h<10)h='0'+h;
        if(s<10)s='0'+s;
        if(m<10)m='0'+m;
    }
    else {
        a = Math.abs(a);
        var h = Math.floor(a / 3600);
        var m = Math.floor((a - h * 3600) / 60);
        var s = a - h * 3600 - m * 60;
       if(h<10)h='0'+h;
       if(s<10)s='0'+s;
       if(m<10)m='0'+m;
        h = "-" + h;
    }
    var t_time = {"hour": h, "min": m, "sec": s};
    return t_time;
};

MediaPlayer.splitTime = function(a) {
    a = parseInt(a);
    var s = a % 100;
    var m = (a % 10000 - s) / 100;
    var h = Math.floor(a / 10000);
    if(s>59){s = 59;}
    if(m>59){m = 59;}
    var t_time = s + m * 60 + h * 3600;
    return t_time;
};

MediaPlayer.runner = {
    id: {},
    run: false,
    start: function() {
        if (this.run) {
            return;
        }
        MediaPlayer.curTime = gSTB.GetPosTime();  
/*
        if (MediaPlayer.type === MEDIA_TYPE_STREAM_TS) {
            MediaPlayer.totalTime = gSTB.GetMediaLen();
            if (MediaPlayer.totalTime > environment.ts_time) {
                MediaPlayer.totalTime = environment.ts_time;
            }
            if (MediaPlayer.curTime > MediaPlayer.totalTime) {
                MediaPlayer.curTime = MediaPlayer.totalTime;
            }
            if (MediaPlayer.curTime < 0) {
                MediaPlayer.curTime = 0;
            }
            var px = Math.round(MediaPlayer.curTime / environment.ts_time * MediaPlayer.progress[screen.height]);
            var buf = Math.round(MediaPlayer.totalTime / environment.ts_time * MediaPlayer.progress[screen.height]);
            MediaPlayer.domPlayerBufferBar.style.width = buf + 'px';
            var curTime = MediaPlayer.parseTime(MediaPlayer.totalTime - MediaPlayer.curTime);
            MediaPlayer.domPlayerCurrentTime.innerHTML = "-" + curTime.hour + ':' + curTime.min + ':' + curTime.sec;
            if(MediaPlayer.type === MEDIA_TYPE_CUE_ITEM){
                MediaPlayer.activeCUE();
            }
            if(MediaPlayer.type === MEDIA_TYPE_ISO){
                MediaPlayer.activeDVD();
            }
        } else {
         */
        if (MediaPlayer.curTime > MediaPlayer.totalTime) {
            MediaPlayer.curTime = MediaPlayer.totalTime;
        }
            if (MediaPlayer.curTime < 0) {
            MediaPlayer.curTime = 0;
        }
            var px = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * MediaPlayer.progress[screen.height]);
        var curTime = MediaPlayer.parseTime(MediaPlayer.curTime);
            var persent = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * 100);
            // call all subscribers hooks
//            MediaPlayer.subscribers[MediaPlayer.EVENT_PROGRESS].forEach(function(subscriber) {
                ListPage.subscribeEvents[MediaPlayer.EVENT_PROGRESS].call(ListPage, persent);
//            });
            MediaPlayer.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
//        }
        MediaPlayer.domPlayerProgressBar.style.width = px + 'px';
        this.id = window.setInterval(function() {
            MediaPlayer.curTime = gSTB.GetPosTime();
/*
            if (MediaPlayer.type === MEDIA_TYPE_STREAM_TS) {
                MediaPlayer.totalTime = gSTB.GetMediaLen();
                if (MediaPlayer.totalTime > environment.ts_time) {
                    MediaPlayer.totalTime = environment.ts_time;
                }
                if (MediaPlayer.curTime > MediaPlayer.totalTime) {
                    MediaPlayer.curTime = MediaPlayer.totalTime - 20;
                }
                echo("MediaPlayer.curTime : "+MediaPlayer.curTime+" MediaPlayer.totalTime : "+MediaPlayer.totalTime);
                var px = Math.round(MediaPlayer.curTime / environment.ts_time * MediaPlayer.progress[screen.height]);
                var buf = Math.round(MediaPlayer.totalTime / environment.ts_time * MediaPlayer.progress[screen.height]);
                MediaPlayer.domPlayerBufferBar.style.width = buf + 'px';
                var curTime = MediaPlayer.parseTime(MediaPlayer.totalTime - MediaPlayer.curTime);
                MediaPlayer.domPlayerCurrentTime.innerHTML = "-" + curTime.hour + ':' + curTime.min + ':' + curTime.sec;
            } else {
*/                                    
            if (MediaPlayer.curTime > MediaPlayer.totalTime) {
                MediaPlayer.curTime = MediaPlayer.totalTime;
            }
                if (MediaPlayer.curTime < 0) {
                MediaPlayer.curTime = 0;
            }
                var persent = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * 100);
				// call all subscribers hooks
//				MediaPlayer.subscribers[MediaPlayer.EVENT_PROGRESS].forEach(function(subscriber){
					ListPage.subscribeEvents[MediaPlayer.EVENT_PROGRESS].call(ListPage, persent);
//				});
            var px = Math.round(MediaPlayer.curTime / MediaPlayer.totalTime * MediaPlayer.progress[screen.height]);
            var curTime = MediaPlayer.parseTime(MediaPlayer.curTime);
                MediaPlayer.domPlayerCurrentTime.innerHTML = curTime.hour + ':' + curTime.min + ':' + curTime.sec;
/*
                if(MediaPlayer.type === MEDIA_TYPE_CUE_ITEM){
                    MediaPlayer.activeCUE();
                }
                if(MediaPlayer.type === MEDIA_TYPE_ISO){
                    MediaPlayer.activeDVD();
                }
            }
*/
            MediaPlayer.domPlayerProgressBar.style.width = px + 'px';
        }, 1000);
        this.run = true;
    },
    stop: function() {
        if (this.run) {
            this.run = false;
            clearInterval(this.id);
        }
    }
};

/*
MediaPlayer.activeCUE = function(){
    if(this.PlayList.playIndex + 1 < this.list.length){
        if(this.curTime >= this.list[this.PlayList.playIndex+1].time){
            this.PlayList.playIndex ++;
            (this.PlayList.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            this.PlayList.Marked(this.PlayList.handle.children[this.PlayList.playIndex], true);     
            this.obj = this.list[this.PlayList.activeItem.data.index];
            this.domPlayerTitle.innerHTML = this.obj.name;
        }
    }
};

MediaPlayer.activeDVD = function(){
    if(this.PlayList.playIndex + 1 < this.dataDVD.titles[this.PlayList.parentIndex].chapters.length){
        if(this.curTime >= this.dataDVD.titles[this.PlayList.parentIndex].chapters[this.PlayList.playIndex+1].startTime/1000){
            this.PlayList.playIndex ++;
            (this.PlayList.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            this.PlayList.Marked(this.PlayList.handle.children[this.dataDVD.titles[this.PlayList.parentIndex].chapters[this.PlayList.playIndex].chN],true);
        }
    }
};

*/
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
        this.ModalMenu.Menu.AddItem(this.ModalMenu.Menu.gsubtitle, "OFF", LANG_MEDIA_MENU_OFF, {data : "OFF", marked : this.subtitles_on === true? false : true});
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

/*
MediaPlayer.setDVDInfo = function(){
    try{
        this.dataDVD = JSON.parse(stb.GetMetadataInfo());
    } catch(e) {
        this.dataDVD = {};
        echo(e);
    }
    this.PlayList.Reset();
    var y = 1;
    var chN = 0;
    this.list = this.dataDVD.titles;
    for(var i=0; i<this.dataDVD.titles.length; i++){
        if(this.dataDVD.titles[i].Titleduration > 10000){ 
            var cTitle = (i==this.dataDVD.infoCurtitle);
            if(cTitle){
                this.PlayList.parentIndex = i;
            }
            this.PlayList.Add(mediaInfoMenu_dvd_title+' '+y, {disabled: true}, {stared : false});
            chN++;
            for(var j=0; j<this.dataDVD.titles[i].chapters.length; j++){
                var time = this.parseTime(parseInt(this.dataDVD.titles[i].chapters[j].startTime/1000));
                this.PlayList.Add(mediaInfoMenu_dvd_chapter+' '+(j+1)+' '+time.hour+':'+time.min+':'+time.sec , {index: j, parentI : i, marked : j==0 && cTitle, focused : j==0 && cTitle, pos : this.dataDVD.titles[i].chapters[j].startTime, disabled: false}, {stared : false});
                this.dataDVD.titles[i].chapters[j].chN = chN;
                chN++;
            }
            y++;          
        }
    }
    this.PlayList.playIndex = 0;
};

*/
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
PlayList.prototype.Add = function(name, attrs, states) {
    var self = this;
    
    // html prepare
    var body = element('div', {className: 'data'}, name);
    var star = element('div', {className: 'star'});

    // actual filling
    var item = CScrollList.prototype.Add.call(this, [body, star], {
        star: star,
        data: {index : attrs.index || attrs.index === 0?attrs.index:'', url : attrs.url? attrs.url : "", pos : attrs.pos || attrs.pos === 0? attrs.pos : "", parentI : attrs.parentI || attrs.parentI === 0? attrs.parentI : ""},
        disabled : attrs.disabled? true: false,
        focused : attrs.focused? true: false,
        marked : attrs.marked? true: false,
        // handlers
        onclick: function() {
            // open or enter the item
            this.self.Open(this.data);
            return false;
        },
        oncontextmenu: function() {
            return false;
        }
    });
    // mark as favourite
    //if (states && states.stared)
    //    item.self.SetStar(item, true);
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
/*
    switch(MediaPlayer.obj.type){
        case MEDIA_TYPE_CUE_ITEM:
            MediaPlayer.obj = MediaPlayer.list[this.activeItem.data.index];
            MediaPlayer.domPlayerTitle.innerHTML = MediaPlayer.obj.name;
            gSTB.SetPosTime(MediaPlayer.obj.time);
            break;
        case MEDIA_TYPE_ISO:
            (this.states.marked || []).forEach(function(item) {
                item.self.Marked(item, false);
            });
            this.Marked(this.states.focused[0], true);
            if(this.parentIndex === data.parentI){
                if(this.playIndex !== data.index){                    
                    gSTB.SetPosTime(parseInt(MediaPlayer.dataDVD.titles[data.parentI].chapters[data.index].startTime/1000));                    
                }
            } else {
                var param = "?title="+data.parentI+" position:"+parseInt(MediaPlayer.dataDVD.titles[data.parentI].chapters[data.index].startTime/1000);
                echo((MediaPlayer.obj.sol ? MediaPlayer.obj.sol + ' ' : 'auto ') + MediaPlayer.obj.url + param,'TO PLAY');
                MediaPlayer.playNow = false;
                MediaPlayer.runner.stop();
                gSTB.Play((MediaPlayer.obj.sol ? MediaPlayer.obj.sol + ' ' : 'auto ') + MediaPlayer.obj.url + param);
            }
            break;
        default:
    MediaPlayer.prepare(MediaPlayer.list[data.index], true);
            break;
    }
    this.playIndex = data.index;
    this.parentIndex = data.parentI;
	if ( this.parent.parent === MediaBrowser ) MediaBrowser.FileList.SetPosition(this.parent.obj);
};
*/

PlayList.prototype.Reset = function() {
    this.parentItem = null;
    this.path = [];
    this.Clear();
};

PlayList.prototype.Refresh = function() {
    this.Activate();
    this.Focused(this.activeItem, true);
};

function getLanguageNameByCode(code){
    if (code.length) {
        for (var i=0; i<iso639.length; i++) {
            ref_codes = iso639[i].code;
            for (var j=0; j<ref_codes.length; j++) {
                if (ref_codes[j] == code[0].toLowerCase()) {
                    code    = [];
                    code[0] = iso639[i].name;
                    code[1] = i;
                    return code;
                }
            }
        }
    }
    return null;
};

function getIso639LangCode(langArr){
    var code = "";
    for (var i=0; i<langArr.length; i++) {
        if (langArr[i]) {
            code = langArr[i];
            break;
        }
    }
    return code;
};


MediaPlayer.addPreviewList = function(file){
    var list = [];
    if(file){
        file.play = true;
        list.push(file);
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
    //    MediaPlayer.preparePlayer(list, ListPage, true, true, true);
    
    return list;
}