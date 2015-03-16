window.onload = init;               // Initialization for event onLoad
window.onkeydown = keyProcessing;   // Intercept events keydown and sent in into function keyProcessing (key-Processing.js)

var debug = true;
var emulate = false;
var main_lang ="";
var stb = new Object();
var win = {
    "width":720,
    "height":576
};


try {
    emulate = false;
    stb = gSTB; // if emulate - set a cap for main class management of device
}
catch(e){
    emulate = true;
    stb = egSTB;
}
//;
/**
 * `init` is function for Initialization start parameters, Initialization of player and request first pack google data
 *
 * @function
 * @name init
 * @param {function():*}
 * @example
 * init();
 * @return void
 */

function init() {
    win = {"width":screen.width, "height":screen.height};
    _GET['proxy'] = '';
    get_params();
    proxy_string = _GET['proxy'];
    if(proxy_string.length>1) {
        proxy_enable=true;
    }
    pages.back = decodeURIComponent(_GET['referrer']);
    if(pages.back == 'undefined' || pages.back == null || pages.back == "null" || pages.back == ""){
        pages.back = "file:///home/web/services.html";
    }
/*
    win = { "width":720, "height":480 };
    win = { "width":720, "height":576 };
    win = { "width":1280, "height":720 };
    win = { "width":1920, "height":1280 };
*/
    var graphicres_mode = "720";
    switch(win.height) {
        case 720:
            graphicres_mode = "1280";
        break;
        case 1080:
            graphicres_mode = "1920";
        break;
        case 480:
        case 576:
            graphicres_mode = "720";
        break;
    }
    
    window.moveTo(0, 0);
    try {
        stb = gSTB;
        window.resizeTo(win.width, win.height);
    }
    catch(e){}
    
    //stb.ExecAction("graphicres " + graphicres_mode);
    /*stb.EnableServiceButton(true);*/

    var fileref = document.createElement("link");
    fileref.setAttribute("rel", "stylesheet");
    fileref.setAttribute("type", "text/css");
    fileref.setAttribute("href", 'css/screen_' + win.height + '.css');    
    document.getElementsByTagName("head")[0].appendChild(fileref);
    log('CSS file imported: "css/screen_' + win.height + '.css"');
    
    main_lang = getEnvironmentValue('language');
    try{main_lang = main_lang.toLowerCase();}catch(e){ }
    if(empty(main_lang)){main_lang='ru';}
    loadScript('js/langs/' + main_lang + '.js', 'load_vars()');    
    //log(getEnvironmentValue('graphicres'));
    
    
}
function load_vars() {
    try {
        log('test lang: some ' + lang.intervals);
        fillPage();
        
    } catch(e) {
        loadScript('js/langs/' + def.lang + '.js', 'load_vars()');
    }
}
/**
 * `getData` is request function for receive description movements from YoTube API
 *
 * @function
 * @name getData
 * @param {function(opt_feedUrl, callbackFunctionName):*}
 * @example
 * getData('http://gdata.youtube.com/feeds/api/standardfeeds/top_rated?v=2&alt=jsonc&start-index=1&max-results=10', 'callback');
 * @return void
 */
function getData(opt_feedUrl, callbackFunctionName) {
    loading.show();
    if(!opt_feedUrl) {return;}
    opt_feedUrl += '&callback=' + callbackFunctionName;
    log("request to youtube: \"" + opt_feedUrl +  "\"");
    var apiRequestNode = document.createElement('script');
    apiRequestNode.src = opt_feedUrl;
    apiRequestNode.type = 'text/javascript';
    document.documentElement.firstChild.appendChild(apiRequestNode);
}
function loadScript(src, callback) {
    //log(src);
    var apiRequestNode = document.createElement('script');
    apiRequestNode.src = src;
    apiRequestNode.type = 'text/javascript';
    document.documentElement.firstChild.appendChild(apiRequestNode);
    log('- - - - - -script "' + src + '" loaded');
    setTimeout(function(){eval(callback);}, 500)
}
function fillPage() {
    var tmp_items = new Object();
    var items_req = new Object();
    switch(win.width) {
        case 1280:
        case 1920:
            tmp_items = {
                "atLine":6,
                "atPage":12
            };
            items_req = 36;
        break;
        case 720:
            tmp_items = {
                "atLine":4,
                "atPage":8
            };
            items_req = 24;
        break;
    }
    m.html('quality',lang.quality['middle']);

    m.html('f_mode',lang.f_mode);
    byID('mode').className = 'single';

    m.html('f_m_modes',lang.f_mode);
    m.html('f_m_quality',lang.f_quality);
    m.html('f_m_q_low',lang.quality.low);
    m.html('f_m_q_middle',lang.quality.middle);
    m.html('f_m_q_high',lang.quality.high);

    m.html('f_search',lang.f_search);
    m.html('f_category',lang.f_category);
    m.html('f_quality', lang.f_quality);
    m.html('cats_all', lang.cats.all);
    m.html('f_category2', lang.f_category);
    
    m.html('f_search2',lang.f_search);
    m.html('loading2', lang.loading);

    m.html('f_videolike2',lang.videolike);
    m.html('f_settings2',lang.settings);

    m.html('vl_suggestion', lang.v_like.suggestion);
    m.html('vl_favorites', lang.v_like.favorites);
    m.html('vl_uploads', lang.v_like.uploads);
    m.html('vl_playlists', lang.v_like.playlists);

    m.html('as_main_field_search',lang.main_field_search);
    m.html('as_field_cat',lang.f_category + ':');
    m.html('as_field_time',lang.as_time);
    m.html('as_field_orderby',lang.as_orderby);

    m.html('as_main_field_users_video',lang.main_field_users_video);
    m.html('as_field_data',lang.as_data);

    m.html('as_main_field_playlists_search',lang.main_field_playlists_search);

    var img_lang = main_lang == 'ru' ? 'ru' : 'en';

    aspects[0].img = 'img/player/v_btn_'+img_lang+'.png';
    aspects[1].img = 'img/player/v_btn_'+img_lang+'.png';
    aspects[2].img = 'img/player/v_btn_'+img_lang+'.png';
    aspects[3].img = 'img/player/v_btn_'+img_lang+'.png';

    items = tmp_items;
    request.itemsPerRequest = items_req;
    current.lang = main_lang;
    current.mode.debug = debug;
    current.mode.emulate = emulate;

    byID('shell').style.display='block';

    current.layer = 0;
    request.startIndex = 1;
    stb.InitPlayer();
    log('stb.InitPlayer();');
    stb.SetTopWin(0);
    log('stb.SetTopWin(0);');
    stb.SetPIG (1,0,0,0);
    stb.EnableVKButton(true);
/*    var vout = getEnvironmentValue('tvsystem'); //PAL : 576p-50 : 720p-50 : 1080i-50 : 1080p-50 : NTSC : 720p-60 : 1080i-60 : 1080p-60
    switch(vout){
        case 'NTSC':
            stb.SetViewport(720,480,0,0);
        break;
        case 'PAL':
        case '576p-50':
            stb.SetViewport(720,576,0,0);
        break;
        case '720p-50':
        case '720p-60':
            stb.SetViewport(1280,720,0,0);
        break;
        case '1080i-50':
        case '1080p-50':
        case '1080i-60':
        case '1080p-60':
            stb.SetViewport(1920,1080,0,0);
        break;
    }*/
    //log('video output: '+vout);
    current.obj = 0;
    current.page = 1;
    workWithItems.shift = current.obj;
    current.cat.url = current.feed = categorias[0].url;
    getData(current.feed+'&start-index='+request.startIndex.toString()+'&max-results='+request.itemsPerRequest.toString(),'rebuildCurrentGlobalObj');

    var imgs_preload = [new Image(),new Image(),];
    imgs_preload[0].src = 'img/pls_def.png';
    imgs_preload[1].src = 'img/pls.png';
}
/**
 * `rebuildCurrentGlobalObj` is function for rebuild current.globalObj.
 * add current requests to exist data in object
 * used like callback function from YouTube API
 *
 * @function
 * @name rebuildCurrentGlobalObj
 * @param {function(dataYouTubeJSONObject):*}
 * @example
 * rebuildCurrentGlobalObj(dataYouTubeJSONObject);
 * @return void
 */
function rebuildCurrentGlobalObj(list) {
    current.isPL = false;
    Deferred.next(function(){
        var tmp = empty(current.globalObj) ? new Array(0) : current.globalObj,
            tmp_list = new Array(),
            counter = 0;
        for(var i=0; i < list.data.items.length + tmp.length; i++) {
            try {
                if(i < tmp.length) {
                    tmp_list[i] = tmp[i];
                } else {
                    if(typeof list.data.items[i - tmp.length].video != 'undefined') {
                        tmp_list[i] = {
                            "id":list.data.items[i - tmp.length].video.id,
                            "img":list.data.items[i - tmp.length].video.thumbnail.sqDefault, //sqDefault | hqDefault
                            "title":list.data.items[i - tmp.length].video.title,
                            "uploaded":list.data.items[i - tmp.length].video.uploaded,
                            "uploader":list.data.items[i - tmp.length].video.uploader,
                            "viewCount":list.data.items[i - tmp.length].video.viewCount,
                            "category":list.data.items[i - tmp.length].video.category,
                            "duration":list.data.items[i - tmp.length].video.duration
                        };
                    } else {
                        tmp_list[i] = {
                            "id":list.data.items[i - tmp.length].id,
                            "img":list.data.items[i - tmp.length].thumbnail.sqDefault, //sqDefault | hqDefault
                            "title":list.data.items[i - tmp.length].title,
                            "uploaded":list.data.items[i - tmp.length].uploaded,
                            "uploader":list.data.items[i - tmp.length].uploader,
                            "viewCount":list.data.items[i - tmp.length].viewCount,
                            "category":list.data.items[i - tmp.length].category,
                            "duration":list.data.items[i - tmp.length].duration
                        };
                    }
                    images[counter++] = new Image();
                    images[counter - 1].src = tmp_list[i].img;

                    if (list.data.items[i].hasOwnProperty('status')
                        && list.data.items[i].status.value == 'restricted'
                        && list.data.items[i].status.reason == 'limitedSyndication' ){
                        tmp_list[i].restricted = 'limitedSyndication';
                    }
                }
            } catch(e){
                log('\n\n* * * * * * * * * * * * * * *\nExaption: "' + e + '"\n* * * * * * * * * * * * * * *\n')
            }
        }
        current.globalObj = tmp_list; // set value of current.globalObj

        request.startIndex = list.data.startIndex;  // set current startIndex
        request.totalItems = list.data.totalItems;  // set current totalItems

        log("drawBoxes | response from youtube: \"start-index:" + list.data.startIndex + "; items-per-page:" + list.data.itemsPerPage + "; " + list.data.items.length.toString() + " of " + list.data.totalItems + "\"");
    });
    Deferred.next(function() {
        workWithItems.drawBoxes();  //draw boxes in current layer
    });
    Deferred.next(function() {
        loading.hide();             // hide loading layer and enable management from keyboard
    });
}
