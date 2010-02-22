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
    this.channels_inited = 0;
    this.aspect_idx = 0;
    this.aspect_array = [
        {alias: 'fit', mode: 0x10},
        {alias: 'big', mode: 0x40},
        {alias: 'opt', mode: 0x50},
        {alias: 'exp', mode: 0x00}
    ];
    this.player;
    
    //this.menu_clock = new main_menu_clock();
    
    this.init = function(){
        _debug('stb.init');
        
        this.player = new player();
        this.get_stb_params();
        this.get_server_params();
        this.get_user_profile();
    }
    
    this.get_server_params = function(){
        
        var pattern = /http:\/\/([\w\.\-]*)\/([\w\/]+)*\/([\w\/]+)\/(.)*/;

        this.ajax_loader = 'http://'+document.URL.replace(pattern, "$1")+'/'+document.URL.replace(pattern, "$2")+'/server/load.php';
        
        _debug('stb.ajax_loader:', this.ajax_loader);
    }
    
    this.get_stb_params = function (){
        
        try{
            
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
        _debug('this.load()');
        _debug('params:', params);
        
        context = context || window;
        
        JsHttpRequest.query(
            
            this.ajax_loader,
            
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
            
            this.player.volume_bar.set_volume(parseInt(this.user['volume']));
            
            this.user.fav_itv_on = parseInt(this.user.fav_itv_on);
            
            this.user['aspect'] = parseInt(this.user['aspect']);
            
            this.aspect_idx = this.aspect_array.getIdxByVal('mode', this.user['aspect']);
            
            if (this.aspect_idx == null){
                this.aspect_idx = 0;
            }
            
            stb.SetAspect(this.user['aspect']);
            
            /*
            mount_home_dir(this.user['storages'])
            
            get_media_cats()
            
            cur_weather = this.user['cur_weather']
            
            set_updated_places(this.user['updated'])
            epg_loader.start()*/
            
            this.load_channels();
            this.load_fav_channels();
            this.load_fav_itv();
            }catch(e){
                _debug(e);
            }
        }else if(this.user['status'] == 1){
            //cut_off()
        }
        //run_watchdog();
    }
    
    this.show_image_version = function(){
        $('ver').innerHTML = $('ver').innerHTML + '(' + this.get_image_version() + ')';
    }
    
    this.get_image_version = function(){
        _debug('check_graphic_res');
        
        var ver = '';
        
        try{
            var full_ver = stb.RDir('Img_Ver');
            
            _debug('full_ver:', full_ver);
            
            var pattern = /ImageVersion:\s([^\s]*)\s(.*)/

            var short_ver = full_ver.replace(pattern, "$1");
            
            if (short_ver.length < 10){
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
                'action' : 'get_fav_itv_ids',
            },
            
            function(result){
                _debug('fav_itv_ids', result);
                this.player.fav_channels_ids = result || [];
                if (this.player.fav_channels_ids.length > 0){
                    this.user.fav_itv_on = 0;
                }
                this.channels_loaded();
            },
            
            this
        )
    }
    
    this.channels_loaded = function(){
        
        if (this.channels_inited){
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
            
            this.player.show_info(channel);
            this.player.play(channel);
            
            this.key_lock = false;
            this.channels_inited = 1;
        }
    }
}