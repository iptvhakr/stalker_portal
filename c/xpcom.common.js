/**
 * XPCOM STB constructor.
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
    this.player;
    this.key_lock = true;
    this.power_off = false;
    
    this.aspect_idx = 0;
    this.aspect_array = [
        {"alias": "fit", "mode": 0x10},
        {"alias": "big", "mode": 0x40},
        {"alias": "opt", "mode": 0x50},
        {"alias": "exp", "mode": 0x00}
    ];
    
    this.video_mode = 1080;
    
    this.cur_off_on = false;
    
    this.cur_place = '';
    
    this.load_step = Math.ceil(50/4);

    //this.menu_clock = new main_menu_clock();

    this.init = function(){
        _debug('stb.init');
        
        this.browser = this.get_user_browser();
        
        this.player = new player();
        this.player.bind();
        this.get_stb_params();
        this.get_server_params();
        this.get_user_profile();
        this.epg_loader.start();
        
        this.notice = new _alert();
        
        this.msg = new _alert('info');
        this.msg.bind();
        
        this.watchdog = new watchdog();
        
        this.usbdisk = new usbdisk();
        
        this.cut_off_dom_obj = create_block_element('cut_off');
        var text_msg = create_block_element('cut_off_text', this.cut_off_dom_obj);
        text_msg.innerHTML = word['cut_off_msg'];
        this.cut_off_dom_obj.hide();
        
        this.clock.start();
    }
    
    this.get_server_params = function(){
        
        var pattern = /http:\/\/([\w\.\-]*)\/([\w\/]+)*\/([\w\/]+)\/(.)*/;

        this.ajax_loader = 'http://'+document.URL.replace(pattern, "$1")+'/'+document.URL.replace(pattern, "$2")+'/server/load.php';
        
        _debug('stb.ajax_loader:', this.ajax_loader);
    }
    
    this.get_stb_params = function (){
        
        try{
            
            this.video_mode = stb.RDir('vmode');
            
            this.mac = stb.RDir('MACAddress').toUpperCase().clearnl();
            
            this.ip  = stb.RDir('IPAddress').clearnl();
            
            this.type = stb.RDir('Model').clearnl();
            
            this.version = 'PORTAL version: '+ver+'; '+stb.Version();
            
            if (this.type == 'MAG200'){
                this.hd = 1;
            }else{
                this.hd = 0;
            }
        }catch(e){
            _debug(e);
        }
        
        if (debug){
            if (_GET['mac']){
                this.mac = _GET['mac'];
            }
        }
        
        this.set_cookie('mac', this.mac);
        
        _debug('this.mac:', this.mac);
        _debug('this.ip:', this.ip);
        _debug('this.type:', this.type);
        _debug('this.version:', this.version);
        _debug('this.hd:',this.hd);
    }
    
    this.set_cookie = function(name, val){
        document.cookie = name + '=' + escape(val) + '; path=/;'
    }
    
    this.load = function(params, callback, context){
        _debug('stb.load()');
        _debug('params:', params);
        
        var context = context || window;
        
        
        try{
            JsHttpRequest.query(
                
                //this.ajax_loader,
                'GET '+this.ajax_loader,
                
                params,
                
                function(result, errors){
                    _debug('callback');
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
    }
    
    this.preload_images = function(){
        
        this.load(
            
            {
                'type'   : 'stb',
                'action' : 'get_preload_images'
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
    }
    
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
                    this.user_init(result);
                }
            },
            
            this
        )
    }
    
    this.get_user_browser = function(){
        var ua = navigator.userAgent.toLowerCase();
        
        if (ua.indexOf("webkit") != -1) {
            return "webkit"
        }else if (ua.indexOf("firefox") != -1){
            return "firefox"
        }else{
            return "na"
        }
    }
    
    this.check_image_version = function(){
        if (this.type == 'MAG200'){
            var cur_version = stb.RDir('ImageVersion').clearnl();
            _debug('cur_version:', cur_version);
            _debug('stb.user.image_version:', stb.user['image_version']);
            if (cur_version != stb.user['image_version'] && stb.user['image_version'] != '0'){
                _debug('RebootDHCP');
                stb.ExecAction('RebootDHCP');
                return 0;
            }
        }
        return 1;
    }
    
    this.user_init = function(user_data){
        
        stb.loader.add_pos(this.load_step, 'call stb.user_init');
        
        this.user = user_data;
        
        _debug('this.user:', user_data)
        
        if (this.user['status'] == 0){
            try{
                
            if (this.type == 'MAG200'){
                if (!this.check_image_version()){
                    return;
                }
            }
            
            if (!this.check_graphic_res()){
                return;
            }

            this.preload_images();
            
            this.player.volume.set_level(parseInt(this.user['volume']));
            
            this.user.fav_itv_on = parseInt(this.user.fav_itv_on);
            
            this.user['aspect'] = parseInt(this.user['aspect']);
            
            this.aspect_idx = this.aspect_array.getIdxByVal('mode', this.user['aspect']);
            
            if (this.aspect_idx == null){
                this.aspect_idx = 0;
            }
            
            try{
                stb.SetAspect(this.user['aspect']);
            }catch(e){
                _debug(e);
            }
            
            /*
            mount_home_dir(this.user['storages'])
            
            get_media_cats()
            
            cur_weather = this.user['cur_weather']
            
            set_updated_places(this.user['updated'])
            epg_loader.start()*/
            
            this.mount_home_dir(this.user['storages']);
            //this.storages = this.user['storages'];
            //stb.loader.add_pos(this.load_step, 'call stb.mount_home_dir');
            
            this.load_channels();
            this.load_fav_channels();
            this.load_fav_itv();
            }catch(e){
                _debug(e);
            }
        }else if(this.user['status'] == 1){
            this.cut_off();
        }
        
        this.watchdog.run();
    }
    
    this.mount_home_dir = function(storages){
        _debug('stb.mount_home_dir: ', storages);
        this.storages = storages;
        
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
    }
    
    this.remount_storages = function(callback){
        _debug('stb.remount_storages');
        
        stb.load(
            {
                "type"   : "stb",
                "action" : "get_storages",
            },
            
            function(result){
                _debug('storages', result);
                
                this.user['storages'] = result;
                
                this.mount_home_dir(this.user['storages']);
                
                callback();
            },
            
            this
        );
    }
    
    this.Mount = function(link_cmd){
        /*_debug('stb.Mount', link_cmd);
        
        this.mounted_storage = link_cmd.replace( /[\s\S]*\/media\/(\S+)\/(\S+)/ , "$1");
        
        _debug('stb.mounted_storage', this.mounted_storage);
        try{
            gSTB.ExecAction('make_dir /media/'+this.mounted_storage);
            var mount_cmd = '"' + this.storages[this.mounted_storage]['storage_ip'] + ':' + this.storages[this.mounted_storage]['nfs_home_path'] + this.mac + '" /media/' + this.mounted_storage;
            gSTB.ExecAction('mount_dir '+mount_cmd);
        }catch(e){
            _debug(e);
        }*/
    }
    
    this.Umount = function(){
        /*_debug('stb.Umount()');
        _debug('stb.mounted_storage', this.mounted_storage);
        
        if (this.mounted_storage){
            try{
                gSTB.ExecAction('umount_dir /media/'+this.mounted_storage);
                this.mounted_storage = '';
            }catch(e){
                _debug(e);
            }
        }*/
    }
    
    this.get_image_version = function(){
        _debug('check_graphic_res');
        
        var ver = '';
        
        try{
            var full_ver = stb.RDir('Img_Ver');
            
            _debug('full_ver:', full_ver);
            
            var pattern = /ImageVersion:\s([^\s]*)\s(.*)/

            var short_ver = full_ver.replace(pattern, "$1");
            
            if (short_ver.length < 30){
                ver = short_ver.clearnl();
            }
            
            _debug('ver:', ver);
            
        }catch(e){
            _debug(e);
        }
        
        return ver;
    }
    
    this.check_graphic_res = function(){
        _debug('check_graphic_res');
        
        try{
            var gres = stb.RDir('gmode');
            
            _debug('gres:', gres);
            
            if (gres != '720'){
                _debug('Reboot');
                stb.ExecAction('graphicres 720');
                stb.ExecAction('reboot');
                return 0;
            }
        }catch(e){
            _debug(e);
        }
        return 1;
    }
    
    this.load_channels = function(){
        
        this.load(
            
            {
                'type'  : 'itv',
                'action': 'get_all_channels'
            },
            
            function(result){
                _debug('all_channels', result);
                
                stb.loader.add_pos(this.load_step, 'channels loaded');
                
                this.player.channels = result || [];
                this.channels_loaded();
            },
            
            this
        )
    }
    
    this.load_fav_channels = function(){
        
        this.load(
            
            {
                'type'  : 'itv',
                'action': 'get_all_fav_channels',
            },
            
            function(result){
                _debug('all_fav_channels', result);
                
                stb.loader.add_pos(this.load_step, 'fav_channels loaded');
                
                this.player.fav_channels = result || [];
                this.channels_loaded();
            },
            
            this
        )
    }
    
    this.load_fav_itv = function(){
        
        this.load(
            
            {
                'type'   : 'itv',
                'action' : 'get_fav_ids',
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
    }
    
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
    }
    
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
                    "action" : "get_epg_info",
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
    }
    
    this.cut_off = function(){
        _debug('stb.cut_off');
        
        _log('cut_off()');
        
        this.key_lock = true;
        
        this.player.stop();
        
        stb.SetDefaultFlicker(1);
        
        this.cut_off_dom_obj.show();
        
        this.cut_off_on = true;
    }
    
    this.cut_on = function(){
        _debug('stb.cut_on');
        
        if (this.cut_off_on){
            stb.ExecAction('reboot');
        }
    }
    
    this.set_cur_place = function(place){
        this.cur_place = place;
    }
    
    this.reset_cur_place = function(place){
        this.cur_place = '';
    }
    
    this.set_cur_layer = function(obj){
        this.cur_layer = obj;
    }
    
    this.get_current_place = function(){
        
        var cur_place_num = 0;
        
        _debug('stb.player.media_type', this.player.media_type);
        _debug('stb.cur_place', this.cur_place);
        
        if(this.player.media_type == 'stream'){ // TV
                if (this.cur_place == 'tv'){
                    cur_place_num = 1;
                }else if(this.cur_place == 'radio'){ // Radio
                    cur_place_num = 5;
                }else{
                    cur_place_num = 1;
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
    }
    
    this.clock = {
        
        month_arr : word['month_arr'],
        day_arr   : word['week_arr'],
        
        start : function(){
            _debug('clock.start()');
            
            this.tick();
            
            var self = this;
            
            try{
                this.t_clock = window.setInterval(function(){self.tick()}, 30000);
            }catch(e){
                _debug(e);
            }
        },
        
        stop : function(){
            _debug('clock.stop');
            
            _debug('self.t_clock', this.t_clock)
            
            var self = this;
            
            try{
                window.clearInterval(self.t_clock);
            }catch(e){
                _debug(e);
            }
        },
        
        tick : function(){
            
            this.current_date = new Date();
            
            this.year  = this.current_date.getFullYear();
            
            this.month = this.current_date.getMonth();
            
            this.date  = this.current_date.getDate();
            
            this.day   = this.current_date.getDay();
            
            this.hours = this.current_date.getHours();
            if (this.hours<10){
                this.hours = '0'+this.hours;
            }
            
            this.minutes = this.current_date.getMinutes();
            if (this.minutes<10){
                this.minutes = '0'+this.minutes;
            }
            
            this.show();
        },
        
        show : function(){
            if (main_menu){
                main_menu.time.innerHTML = this.hours + ':' + this.minutes;
                main_menu.date.innerHTML = this.day_arr[this.day] + ', ' + this.date + ' ' + this.month_arr[this.month] + ', ' + this.year + word['year'] + '.';
            }
        }
    }
}