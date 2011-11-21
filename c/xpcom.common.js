/**
 * Common XPCOM STB constructor.
 * @constructor
 */
function common_xpcom(){

    this.user = {};
    this.mac = '';
    this.ip  = '';
    this.hd  = 0;
    this.type  = '';
    this.version    = '';
    this.ajax_loader  = '';
    this.images   = [];
    this.storages = {};
    this.player = {};
    this.key_lock = true;
    this.power_off = false;
    this.additional_services_on = 0;

    this.aspect_idx = 0;
    this.aspect_array = [
        {"alias": "fit", "mode": 0x10},
        {"alias": "big", "mode": 0x40},
        {"alias": "opt", "mode": 0x50},
        {"alias": "exp", "mode": 0x00},
        {"alias": "cmb", "mode": 0x30}
    ];

    this.video_mode = 1080;

    this.cur_off_on = false;

    this.cur_place = '';

    this.load_step = Math.ceil(50/4);

    this.base_modules = [
        "reset",
        "context_menu",
        "main_menu",
        "alert",
        "layer.base",
        "layer.list",
        "layer.setting",
        "layer.simple",
        "layer.input",
        "layer.sidebar",
        "layer.search_box",
        "layer.bottom_menu",
        "layer.scrollbar",
        "layer.vclub_info",
        "password_input",
        "series_switch",
        "duration_input"
    ];

    this.init = function(){
        _debug('stb.init');

        this.browser = this.get_user_browser();

        this.player = new player();
        this.player.bind();
        this.get_server_params();
        this.get_stb_params();
        this.get_user_profile();
        //this.epg_loader.start();

        /*this.notice = new _alert();

        this.msg = new _alert('info');
        this.msg.bind();

        this.confirm = new _alert('confirm');
        this.confirm.bind();*/

        this.watchdog = new watchdog();

        this.usbdisk = new usbdisk();

        /*this.cut_off_dom_obj = create_block_element('cut_off');
        this.cut_off_dom_obj.hide()*/

        //this.clock.start();
    };

    this.init_alerts = function(){
        this.notice = new _alert();

        this.msg = new _alert('info');
        this.msg.bind();

        this.confirm = new _alert('confirm');
        this.confirm.bind();
    };

    this.get_server_params = function(){

        var pattern = /http:\/\/([\w\.\-]*)\/([\w\/]+)*\/([\w\/]+)\/(.)*/;

        this.portal_path = document.URL.replace(pattern, "$2");

        _debug('stb.portal_path:', this.portal_path);

        this.ajax_loader = 'http://'+document.URL.replace(pattern, "$1")+'/'+this.portal_path+'/server/load.php';

        _debug('stb.ajax_loader:', this.ajax_loader);
    };

    this.get_modules = function(){
        _debug('stb.get_modules');

        this.load(

            {
                "type"   : "stb",
                "action" : "get_modules"
            },

            function(result){
                _debug('stb.get_modules callback', result);
                var all_modules = result.all_modules;
                this.disabled_modules = result.disabled_modules;

                this.all_modules = this.base_modules.concat(all_modules);
                _debug('all_modules', this.all_modules);
                loader.add(this.all_modules);
            },

            this
        );
    };

    this.is_disabled_module = function(module){
        _debug('stb.is_disabled_module');
        _debug('module.layer_name', module.layer_name);

        _debug('this.additional_services_on', this.additional_services_on);

        if (this.additional_services_on){
            return false;
        }

        if (this.disabled_modules.indexOf(module.layer_name) >= 0){
            return true;
        }

        return false;
    };

    this.check_additional_services = function(param){
        _debug('check_additional_services', param);

        this.additional_services_on = parseInt(param, 10);
    };

    this.get_stb_params = function (){

        try{

            this.video_mode   = stb.RDir('vmode');
            //this.graphic_mode = stb.RDir('gmode');

            this.mac = stb.RDir('MACAddress').toUpperCase().clearnl();

            this.ip  = stb.RDir('IPAddress').clearnl();

            this.type = stb.RDir('Model').clearnl();

            this.stb_lang = stb.RDir('getenv language').clearnl();
            
            this.timezone = stb.RDir('getenv timezone_conf').clearnl();
            
            this.ntp_server = stb.RDir('getenv ntpurl').clearnl();

            this.version = 'PORTAL version: '+ver+'; '+stb.Version();

            var mtdparts = stb.RDir('getenv mtdparts').clearnl();

            this.num_banks = mtdparts.indexOf('RootFs2') > 0 ? 2 : 1;

            if (this.type == 'MAG200' || this.type == 'MAG250'){
                this.hd = 1;
            }else{
                this.hd = 0;
            }

            if (this.graphic_mode >= 720){
                _debug('gSTB.SetObjectCacheCapacities');
                gSTB.SetObjectCacheCapacities(1000000,7000000,10000000);
            }
            
        }catch(e){
            _debug(e);
        }

        if (debug){
            if (_GET['mac']){
                this.mac = _GET['mac'];
            }

            this.set_cookie('debug', 1);
        }

        this.set_cookie('mac', this.mac);
        this.set_cookie('stb_lang', this.stb_lang);
        this.set_cookie('timezone', this.timezone);

        //this.get_localization();

        _debug('this.mac:', this.mac);
        _debug('this.stb_lang:', this.stb_lang);
        _debug('this.timezone:', this.timezone);
        _debug('this.ntp_server:', this.ntp_server);
        _debug('this.ip:', this.ip);
        _debug('this.type:', this.type);
        _debug('this.version:', this.version);
        _debug('this.hd:',this.hd);
    };

    this.set_cookie = function(name, val){
        document.cookie = name + '=' + escape(val) + '; path=/;'
    };

    this.get_localization = function(){
        _debug('stb.get_localization');

        this.load(
            {
                "type"   : "stb",
                "action" : "get_localization"
            },

            function(result){

                word = result;
                //this.clock.start();

                this.user_init(this.profile);

                this.clock.start();
            },

            this
        )
    };

    /**
     * Ajax wrapper.
     * @param params
     * @param {...} var_args
     */
    this.load = function(params, var_args){
        _debug('stb.load()');
        _debug('params:', params);

        var callback = arguments[1];

        var context = window;

        if (arguments.length == 3){
            context = arguments[2];
        }

        try{

            return JsHttpRequest.query(

                //this.ajax_loader,
                'GET '+this.ajax_loader,

                params,

                function(result, errors){
                    _debug('stb.load callback');
                    _debug(errors);
                    try{
                        callback.call(context, result);
                    }catch(e){
                        _debug(e);
                    }
                },

                true
            );
        }catch(e){
            _debug(e);
        }
    };

    this.preload_images = function(){

        this.load(

            {
                'type'   : 'stb',
                'action' : 'get_preload_images',
                'gmode'  : resolution_prefix.substr(1)
            },

            function(result){
                if (result != null){
                    for (var i=0; i<result.length; i++){
                        stb.images[i] = new Image();
                        stb.images[i].src = result[i];
                    	stb.images.onload = function(){};
                    }
                }
            }
        );
    };

    this.get_user_profile = function(){
        _debug('this.get_user_profile');

        this.load(

            {
                'type'  : 'stb',
                'action': 'get_profile',
                'hd'    : this.hd,
                'ver'   : this.version
            },

            function(result){
                if (result != null){
                    this.profile = result;
                    //this.user_init(result);
                    this.get_localization();


                }
            },

            this
        )
    };

    this.get_user_browser = function(){
        var ua = navigator.userAgent.toLowerCase();

        if (ua.indexOf("webkit") != -1) {
            return "webkit"
        }else if (ua.indexOf("firefox") != -1){
            return "firefox"
        }else{
            return "na"
        }
    };

    this.check_image_version = function(){

        var cur_version = stb.RDir('ImageVersion').clearnl();
        this.firmware_version = cur_version;
        _debug('cur_version:', cur_version);
        _debug('stb.user.image_version:', stb.user['image_version']);

        if (this.firmware_version < 203){
            return 0;
        }

        if (cur_version != stb.user['image_version'] && stb.user['image_version'] != '0' && this.num_banks == 2){
        //if (cur_version != stb.user['image_version'] && stb.user['image_version'] != '0' && this.num_banks == 2 && this.type == 'MAG250'){

            try{
                _debug('this.user[update_url]', this.user['update_url']);
                
                stbUpdate.startAutoUpdate(this.user['update_url'], false);
            }catch(e){
                _debug(e);
            }

            return 0;
        }
        
        return 1;
    };

    this.user_init = function(user_data){

        stb.loader.add_pos(this.load_step, 'call stb.user_init');

        this.user = user_data;

        _debug('this.user:', user_data);

        /*if (!this.check_graphic_res()){
            return;
        }*/

        screensaver.init();

        if (this.user['status'] == 0){
            try{

                //if (this.type == 'MAG200'){

                /*if (!this.check_graphic_res()){
                    return;
                }*/

                //this.get_localization();

                this.usbdisk.init();

                this.preload_images();

                this.player.volume.set_level(parseInt(this.user['volume'], 10));

                this.player.setup_rtsp(this.user['rtsp_type'], this.user['rtsp_flags']);

                this.user.fav_itv_on = parseInt(this.user.fav_itv_on, 10);

                this.user['aspect']    = parseInt(this.user['aspect'],    10);
                this.user['audio_out'] = parseInt(this.user['audio_out'], 10);

                stb.user['playback_limit'] = parseInt(this.user['playback_limit'], 10);

                this.user['tv_archive_days']   = parseInt(this.user['tv_archive_days'], 10);
                this.user['screensaver_delay'] = parseInt(this.user['screensaver_delay'], 10);

                this.user['update_url'] = this.profile['update_url'] ? this.profile['update_url'] + this.type.substr(3) + '/imageupdate' : 'http://mag.infomir.com.ua/' + this.type.substr(3) + '/imageupdate';

                if (['MAG200', 'MAG250'].indexOf(this.type) >= 0){
                    this.check_image_version();
                }

                this.epg_loader.start();

                this.locale = this.user.locale;

                this.aspect_idx = this.aspect_array.getIdxByVal('mode', this.user['aspect']);

                this.check_additional_services(this.user['additional_services_on']);

                if (this.aspect_idx == null){
                    this.aspect_idx = 0;
                }

                try{

                    _debug('stb.GetBrightness before', stb.GetBrightness());
                    _debug('stb.GetContrast before', stb.GetContrast());
                    _debug('stb.GetSaturation before', stb.GetSaturation());

                    stb.SetBrightness(127);
                    stb.SetContrast(-27);
                    stb.SetSaturation(100);

                    _debug('stb.GetBrightness after', stb.GetBrightness());
                    _debug('stb.GetContrast after', stb.GetContrast());
                    _debug('stb.GetSaturation after', stb.GetSaturation());

                    stb.SetAspect(this.user['aspect']);

                    stb.SetBufferSize(this.user['playback_buffer_size'], this.user['playback_buffer_bytes']);

                    this.user['playback_buffer_size'] = this.user['playback_buffer_size'] / 1000;

                    stb.SetupSPdif(this.user['audio_out']);

                    stb.EnableServiceButton(false);

                    //stb.SetWebProxy(string proxy_addr,int proxy_port,string user_name,string passwd,string exclude_list);
                    if (this.user['web_proxy_host']){
                        stb.SetWebProxy && stb.SetWebProxy(this.user['web_proxy_host'], this.user['web_proxy_port'], this.user['web_proxy_user'], this.user['web_proxy_pass'], this.user['web_proxy_exclude_list']);
                    }
                }catch(e){
                    _debug(e);
                }

                this.get_modules();

                //this.mount_home_dir(this.user['storages']);
                this.set_storages(this.user['storages']);
                stb.loader.add_pos(this.load_step, 'call stb.mount_home_dir');

                this.load_channels();
                this.load_fav_channels();
                this.load_fav_itv();

            }catch(e){
                _debug(e);
            }
        }else if(this.user['status'] == 1){
            stb.loader.stop();
            this.cut_off();
            //module.blocking.show()
        }

        this.watchdog.run();
    };

    this.post_loading_handle = function(){
        _debug('stb.post_loading_handle');

        _debug('this.user[display_menu_after_loading]', this.user['display_menu_after_loading']);

        this.key_lock = false;

        if (single_module && module[single_module]){
            module[single_module]._show && module[single_module]._show() || module[single_module].show && module[single_module].show();
            return;
        }

        this.player.init_first_channel();

        if (this.user['display_menu_after_loading']){
            main_menu.show();
        }else{
            this.player.first_play();
        }
    };

    this.mount_home_dir = function(storages){
        _debug('stb.mount_home_dir: ', storages);

        this.set_storages(storages);

        stb.loader.add_pos(this.load_step, 'call stb.mount_home_dir');

        for(var i in storages){
            if (storages.hasOwnProperty(i)){
                stb.ExecAction('make_dir /media/'+storages[i]['storage_name']);

                var mount_cmd = '"'+storages[i]['storage_ip']+':'+storages[i]['nfs_home_path']+stb.mac+'" /media/'+storages[i]['storage_name'];
                _debug('mount_cmd: '+mount_cmd);

                try{
                    stb.ExecAction('mount_dir '+mount_cmd);
                }catch(e){
                    _debug(e);
                }
            }
        }
    };

    this.set_storages = function(storages){
        _debug('stb.set_storages', storages);

        this.storages = this.user['storages'] = storages;
    };

    this.remount_storages = function(callback){
        _debug('stb.remount_storages');

        stb.load(
            {
                "type"   : "stb",
                "action" : "get_storages"
            },

            function(result){
                _debug('storages', result);

                this.set_storages(result);

                //this.mount_home_dir(this.user['storages']);

                callback();
            },

            this
        );
    };

    this.Mount = function(link_cmd){
        _debug('stb.Mount', link_cmd);

        var mounted_storage = link_cmd.replace(/[\s\S]*\/media\/(.*)\/(.*)/ , "$1");

        if (mounted_storage == this.mounted_storage){
            _debug('clear Umount Timeout', mounted_storage);
            window.clearTimeout(stb.player.umount_timer);
        }

        this.mounted_storage = mounted_storage;

        _debug('stb.mounted_storage', this.mounted_storage);
        try{
            gSTB.ExecAction('make_dir /media/'+this.mounted_storage);
            var mount_cmd = '"' + this.storages[this.mounted_storage]['storage_ip'] + ':' + this.storages[this.mounted_storage]['nfs_home_path'] + this.mac + '" /media/' + this.mounted_storage;
            gSTB.ExecAction('mount_dir '+mount_cmd);
        }catch(e){
            _debug(e);
        }
    };

    this.Umount = function(storage){
        _debug('stb.Umount', storage);
        //_debug('stb.mounted_storage', this.mounted_storage);

        if (storage){
            try{
                gSTB.ExecAction('umount_dir /media/'+storage);
                //this.mounted_storage = '';
            }catch(e){
                _debug(e);
            }
        }
    };

    this.switchPower = function(){
        _debug('stb.switchPower()');

        if(this.power_off){
            this.StandBy(0);
            this.power_off = false;
            keydown_observer.emulate_key(key.MENU);
            this.clock && this.clock.show && this.clock.show();
        }else{
            keydown_observer.emulate_key(key.MENU);
            this.StandBy(1);
            this.power_off = true;
            this.setFrontPanel('');
        }
    };

    this.get_image_version = function(){
        _debug('get_image_version');

        var ver = '';

        try{
            var full_ver = stb.RDir('Img_Ver');

            _debug('full_ver:', full_ver);

            var pattern = /ImageVersion:\s([^\s]*)\s(.*)/;

            var short_ver = full_ver.replace(pattern, "$1");

            if (short_ver.length < 30){
                ver = short_ver.clearnl();
            }

            _debug('ver:', ver);

        }catch(e){
            _debug(e);
        }

        return ver;
    };

    this.check_graphic_res = function(){
        _debug('check_graphic_res');

        try{
            //var gres = stb.RDir('gmode');

            //_debug('gres:', gres);

            var res = {
                "r480" :{
                    "w"        : 720,
                    "h"        : 480,
                    "window_w" : 720,
                    "window_h" : 480,
                    "prefix"   : '_480'
                },
                "r576" :{
                    "w"        : 720,
                    "h"        : 576,
                    "window_w" : 720,
                    "window_h" : 576,
                    "prefix"   : ''
                },
                "r720" : {
                    "w"        : 1280,
                    "h"        : 720,
                    "window_w" : 1280,
                    "window_h" : 720,
                    "prefix"   : '_720'

                },
                "r1080" : {
                    "w"        : 1920,
                    "h"        : 1080,
                    "window_w" : 1280,
                    "window_h" : 720,
                    "prefix"   : '_720'
                }
            };

            //if (gres == 'tvsystem_res'){
                var gres = screen.height;
            //}

            this.graphic_mode = gres;

            _debug('gres', gres);

            //if (gres == '720'){
            if (res["r"+gres]){

                resolution_prefix = res["r"+gres].prefix;

                _debug('window.moveTo', (res["r"+gres].w - res["r"+gres].window_w)/2, (res["r"+gres].h - res["r"+gres].window_h)/2);
                window.moveTo((res["r"+gres].w - res["r"+gres].window_w)/2, (res["r"+gres].h - res["r"+gres].window_h)/2);

                window.resizeTo(res["r"+gres].window_w, res["r"+gres].window_h);

                if (gres == 1080 && !window.referrer){
                    stb.ExecAction('graphicres 1280');
                }else{
                    return 1;
                }
            }else{
                stb.ExecAction('graphicres 720');
            }

            _debug('Reboot');
            stb.ExecAction('reboot');
            return 0;


            /*if (gres != '720'){

                _debug('window.referrer', window.referrer);

                if (stb.type == 'MAG200'){
                    if (window.referrer){
                        window.resizeTo(720, 576);
                        if (res["r"+gres]){
                            _debug('window.moveTo', (res["r"+gres].w - 720)/2, (res["r"+gres].h - 576)/2);
                            window.moveTo((res["r"+gres].w - 720)/2, (res["r"+gres].h - 576)/2);
                        }
                    }else{
                        //_debug('Reboot');
                        //stb.ExecAction('graphicres 720');
                        //stb.ExecAction('reboot');
                        //return 0;
                    }
                }
            }*/
        }catch(e){
            _debug(e);
            return 1;
        }
    };

    this.load_channels = function(){

        this.load(

            {
                'type'  : 'itv',
                'action': 'get_all_channels'
            },

            function(result){
                _debug('all_channels', result);

                stb.loader.add_pos(this.load_step, 'channels loaded');

                this.player.channels = result.data || [];
                this.channels_loaded();
            },

            this
        )
    };

    this.load_fav_channels = function(){

        this.load(

            {
                'type'  : 'itv',
                'action': 'get_all_fav_channels',
                'fav'   : 1
            },

            function(result){
                _debug('all_fav_channels', result);

                stb.loader.add_pos(this.load_step, 'fav_channels loaded');

                this.player.fav_channels = result.data || [];
                this.channels_loaded();
            },

            this
        )
    };

    this.load_fav_itv = function(){

        this.load(

            {
                'type'   : 'itv',
                'action' : 'get_fav_ids'
            },

            function(result){
                _debug('fav_itv_ids', result);
                this.player.fav_channels_ids = result || [];
                if (this.player.fav_channels_ids.length == 0){
                    this.user.fav_itv_on = 0;
                }
                this.channels_loaded();
            },

            this
        )
    };

    this.channels_loaded = function(){

        /*if (this.channels_inited){
            return;
        }

        if (typeof(this.player.channels) != 'undefined' &&
            typeof(this.player.fav_channels) != 'undefined' &&
            typeof(this.player.fav_channels_ids) != 'undefined'){


            if (this.user.fav_itv_on){
                this.player.f_ch_idx = this.player.fav_channels.getIdxById(this.user.last_itv_id);
                if (this.player.f_ch_idx === null){
                    this.player.f_ch_idx = 0;
                }
                var channel = this.player.fav_channels[this.player.f_ch_idx];
            }else{
                this.player.ch_idx = this.player.channels.getIdxById(this.user.last_itv_id);
                if (this.player.ch_idx === null){
                    this.player.ch_idx = 0;
                }
                var channel = this.player.channels[this.player.ch_idx];
            }

            this.player.need_show_info = 1;
            this.player.play(channel);

            this.key_lock = false;
            this.channels_inited = 1;
        }*/
    };

    this.epg_loader = {

        timeout  : 21600000, // 6h
        timer_id : 0,
        epg : [],

        start : function(){
            _debug('epg_loader.start');

            this.load();
            var self = this;
            this.timer_id = window.setInterval(function(){self.load()}, this.timeout);
        },

        stop : function(){
            _debug('epg_loader.stop');

            window.clearInterval(this.timer_id);
        },

        load : function(){
            _debug('epg_loader.load');

            stb.load(
                {
                    "type"   : "itv",
                    "action" : "get_epg_info"
                },

                function(result){
                    this.set_epg(result.data);
                },

                this
            )
        },

        set_epg : function(data){
            _debug('epg_loader.set_epg', data);
            this.epg = data;
            _debug('typeof(this.epg)', typeof(this.epg));
        },

        get_epg : function(ch_id){
            _debug('epg_loader.get_epg', ch_id);

            var ch_id = ''+ch_id;

            _debug('typeof(ch_id)', typeof(ch_id));

            var now = Date.parse(new Date())/1000;
            var result = '';

            _debug('now', now);
            _debug('this.epg[ch_id]', this.epg[ch_id]);
            _debug('typeof this.epg[ch_id]', typeof(this.epg[ch_id]));
            _debug('this.epg[ch_id].length', this.epg[ch_id].length);

            try{
                if (typeof(this.epg[ch_id]) == 'object' && this.epg[ch_id].length > 0){
                    _debug('this.epg[ch_id].length: '+this.epg[ch_id].length);
                    for (var i=0; i < this.epg[ch_id].length; i++){
                        _debug('i', i);
                        if (this.epg[ch_id][i]['start_timestamp'] < now){
                            _debug('continue');
                            continue;
                        }else if (this.epg[ch_id][i]['start_timestamp'] == now){
                            result = this.epg[ch_id][i].time + ' ' + this.epg[ch_id][i].name;
                            if (typeof(this.epg[ch_id][i+1]) == 'object'){
                                result += '<br>'+this.epg[ch_id][i+1].t_time + ' ' + this.epg[ch_id][i+1].name;
                            }
                            return result;
                        }else{
                            if (typeof(this.epg[ch_id][i-1]) == 'object'){
                                result = this.epg[ch_id][i-1].t_time + ' ' + this.epg[ch_id][i-1].name;
                                if (typeof(this.epg[ch_id][i]) == 'object'){
                                    result += '<br>'+this.epg[ch_id][i].t_time + ' ' + this.epg[ch_id][i].name;
                                }
                                return result;
                            }
                        }
                    }
                }
            }catch(e){
                _debug(e);
            }
            return '';
        }
    };

    this.cut_off = function(){
        _debug('stb.cut_off');

        if (module.blocking.on){
            return;
        }

        _log('cut_off()');

        this.key_lock = false;

        this.player.stop();

        if(this.cur_layer){
            this.cur_layer.on = false;
        }

        stb.SetDefaultFlicker && stb.SetDefaultFlicker(1);

        /*var text_msg = create_block_element('cut_off_text', this.cut_off.dom_obj);
        text_msg.innerHTML = get_word('cut_off_msg');*/

        module.blocking.show();
    };

    this.cut_on = function(){
        _debug('stb.cut_on');

        if (module.blocking.on){
            stb.ExecAction('reboot');
        }
    };

    this.set_cur_place = function(place){
        this.cur_place = place;
    };

    this.reset_cur_place = function(place){
        this.cur_place = '';
    };

    this.set_cur_layer = function(obj){
        this.cur_layer = obj;
    };

    this.get_current_place = function(){

        var cur_place_num = 0;

        _debug('stb.player.media_type', this.player.media_type);
        _debug('stb.cur_place', this.cur_place);

        if(this.player.media_type == 'stream'){ // TV
            if (this.player.on){
                if (this.cur_place == 'tv'){
                    cur_place_num = 1;
                }else if(this.cur_place == 'radio'){ // Radio
                    cur_place_num = 5;
                }else{
                    cur_place_num = 1;
                }
            }
        }else if(this.player.media_type == 'file'){
            if (this.player.on){
                if (this.cur_place == 'vclub'){ // Video Club
                    cur_place_num = 2;
                }else if(this.cur_place == 'karaoke'){ // Karaoke
                    cur_place_num = 3;
                }else if(this.cur_place == 'audio_club'){ // Audio Club
                    cur_place_num = 4;
                }else if(this.cur_place == 'video_clips'){ // Video Clips
                    cur_place_num = 8;
                }else if(this.cur_place == 'ad'){
                    cur_place_num = 9;
                }else if(this.cur_place == 'media_browser'){
                    cur_place_num = 10;
                }
            }
        }else{
            if (this.cur_place == 'city_info'){
                cur_place_num = 20;
            }else if(this.cur_place == 'anec_page'){
                cur_place_num = 21;
            }else if(this.cur_place == 'weather_page'){
                cur_place_num = 22;
            }else if(this.cur_place == 'game_page'){
                cur_place_num = 23;
            }else if(this.cur_place == 'horoscope_page'){
                cur_place_num = 24;
            }else if(this.cur_place == 'course_page'){
                cur_place_num = 25;
            }
        }

        return cur_place_num;
    };

    this.clock = {

        start : function(){
            _debug('clock.start()');

            this.tick();

            var self = this;

            try{
                this.t_clock   = window.setInterval(function(){self.tick()}, 30000);
                this.t_clock_s = window.setInterval(function(){self.tick_s()}, 1000);
            }catch(e){
                _debug(e);
            }
        },

        stop : function(){
            _debug('clock.stop');

            _debug('self.t_clock', this.t_clock);

            var self = this;

            try{
                window.clearInterval(self.t_clock);
                window.clearInterval(self.t_clock_s);
            }catch(e){
                _debug(e);
            }
        },

        tick_s : function(){
            this.timestamp = Math.round(new Date().getTime() / 1000);
        },

        tick : function(){

            this.current_date = new Date();

            this.year  = this.current_date.getFullYear();

            this.month = this.current_date.getMonth();

            this.date  = this.current_date.getDate();

            this.day   = this.current_date.getDay();

            this.hours = this.current_date.getHours();
            this.hours = this.current_date.getHours();

            if (this.hours > 11){
                this.ap_mark = 'PM';
            }else{
                this.ap_mark = 'AM';
            }

            if (this.hours > 12){
                this.ap_hours = this.hours - 12;
            }else{
                this.ap_hours = this.hours;
            }

            if (this.ap_hours == 12){
                this.ap_hours = 0
            }

            this.minutes = this.current_date.getMinutes();
            if (this.minutes<10){
                this.minutes = '0'+this.minutes;
            }

            this.show();
        },

        show : function(){
            if (typeof(main_menu) != 'undefined' && main_menu && main_menu.time && main_menu.date && main_menu.on){
                main_menu.time.innerHTML = get_word('time_format').format(this.hours, this.minutes, this.ap_hours, this.ap_mark);
                main_menu.date.innerHTML = get_word('date_format').format(get_word('week_arr')[this.day], this.date, get_word('month_arr')[this.month], this.year);
            }

            if (stb.player && stb.player.info && stb.player.info.on && stb.player.info.clock){
                stb.player.info.clock.innerHTML = get_word('time_format').format(this.hours, this.minutes, this.ap_hours, this.ap_mark);
            }

            if (module && module.tv && module.tv.on && module.tv.clock_box){
                module.tv.clock_box.innerHTML = get_word('time_format').format(this.hours, this.minutes, this.ap_hours, this.ap_mark);
            }

            if (!stb.player.on || (stb.player.on && !stb.player.is_tv)){
                stb.setFrontPanel(this.hours + '' + this.minutes, true);
            }

            this.triggerCustomEventListener("tick", this);
        }
    }
}

var screensaver = {

    on : false,

    init : function(){
        //_debug('screensaver.init');

        //return;

        this.build();

        var self = this;

        keydown_observer.addCustomEventListener("keypress", function(event){
            _debug('screensaver keypress', event);

            if (self.on){
                self.hide();
            }

            self.restart_timer.call(self);
        });

        this.restart_timer();

        stb.player.addCustomEventListener("onplay", function(event){
            if (self.on){
                self.hide();
            }
        });

        stb.clock.addCustomEventListener("tick", function(date){
            if (self.on){
                self.clock.innerHTML = get_word('time_format').format(date.hours, date.minutes, date.ap_hours, date.ap_mark);
            }
        });
    },

    restart_timer : function(){
        _debug('screensaver.restart_timer');

        var self = this;

        window.clearTimeout(this.activate_timer);

        if (stb.user['screensaver_delay'] > 0){
            this.activate_timer = window.setTimeout(function(){
                self.show.call(self);
            }, stb.user['screensaver_delay'] * 60000);
            //}, stb.user['screensaver_delay'] * 10000);
        }
    },

    build : function(){
        //_debug('screensaver.build');

        this.dom_obj = create_block_element("screensaver");
        this.clock   = create_block_element("screensaver_clock", this.dom_obj);
        this.hide();
    },

    show : function(){
        _debug('screensaver.show');

        if (stb.player.on){
            _debug('stb.player.on', stb.player.on);
            this.restart_timer();
            return;
        }

        //stb.cur_layer && stb.cur_layer.dom_obj.hide();
        this.dom_obj.show();
        this.on = true;

        this.clock.innerHTML = get_word('time_format').format(stb.clock.hours, stb.clock.minutes, stb.clock.ap_hours, stb.clock.ap_mark);

        this.move();
        var self = this;
        this.move_timer = window.setInterval(function(){self.move.call(self)}, 5000);
    },

    hide : function(){
        _debug('screensaver.hide');

        //stb.cur_layer && stb.cur_layer.dom_obj.show();
        this.dom_obj.hide();
        this.on = false;
        window.clearInterval(this.move_timer);
    },

    move : function(){
        _debug('screensaver.start');

        var top  = Math.floor(Math.random() * (screen.height - this.clock.offsetHeight));
        var left = Math.floor(Math.random() * (screen.width  - this.clock.offsetWidth));
        _debug('top', top);
        _debug('left', left);

        this.clock.moveX(left);
        this.clock.moveY(top);
    }
};