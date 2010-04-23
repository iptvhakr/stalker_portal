/**
 * Media browser.
 */

(function(){
    
    /* MEDIA BROWSER */
    function media_browser_constructor(){
        
        this.row_blocks  = ["dir", "name"];
        this.mounted = false;
        this.cur_dir_list = [];
        this.data_items   = [];
        this.is_audio = false;
        
        this.superclass = Layer.prototype;
        
        this.dir_hist = [{"path" : "/media/usbdisk/", "page" : 1, "row" : 1}];
        
        this.init = function(){
            this.superclass.init.call(this);
            
            try{
                stb.SetListFilesExt('.mpg .mkv .avi .ts .mp4 .wmv .mp3 .ac3 .mov .vob .wav');
            }catch(e){
                _debug(e);
            }
            
            var self = this;
            
            (function(){
                try{
                    self.drive_mounted();
                }catch(e){
                    _debug(e);
                }
            }).bind(key.USB_MOUNTED);
            
            (function(){
                self.drive_umounted();
            }).bind(key.USB_UNMOUNTED);
            
            this.check_mounted();
        };
        
        this.hide = function(do_not_reset){
            _debug('media_browser.hide', do_not_reset);
            
            try{
                this.superclass.hide.call(this, do_not_reset);
                
                if (!do_not_reset){
                    this.dir_hist.splice(1, this.dir_hist.length-1);
                    
                    this.reset();
                }
            }catch(e){
                _debug(e);
            }
        };
        
        this.reset = function(){
            this.cur_row  = 0;
            this.cur_page = 1;
            this.total_pages = 1;
        };
        
        this.load_data = function(){
            _debug('load_data');
            
            var path = this.compile_path();
            
            try{
                var txt = stb.ListDir(path);
                
                _debug(txt);
                
                eval(txt);
                
                var new_dirs = [];
                
                for (var i=0; i < dirs.length; i++){
                    if (!empty(dirs[i])){
                        new_dirs.push({"name" : dirs[i].substring(0, dirs[i].length - 1), "dir" : 1})
                    }
                }
                
                var new_files = [];
                
                for (var i=0; i < files.length; i++){
                    if (!empty(files[i])){
                        new_files.push({"name" : files[i].name, "cmd" : (path + files[i].name), "size" : files[i].size});
                    }
                }
                
                var list = new_dirs.concat(new_files);
                
                if (this.dir_hist.length == 1){
                    var clear_arr = [];
                }else{
                    var clear_arr = [{"name" : "..", "dir" : 1}];
                }
                
                for (var i=0; i < list.length; i++){
                    if (!empty(list[i])){
                        clear_arr.push(list[i]);
                    }
                }
                
                this.total_pages = Math.ceil(clear_arr.length/14);
                this.set_total_items(clear_arr.length);
                
                this.cur_dir_list = clear_arr;
                
                var begin = (this.cur_page - 1) * 14;
                var end   = this.cur_page * 14;
                
                this.data_items = this.cur_dir_list.slice(begin, end);
                
                this.fill_list(this.data_items);
            
            }catch(e){
                _debug(e);
            }
        };
        
        this.compile_path = function(){
            _debug('media_browser.compile_path');
            
            var path = '';
            
            for(var i=0; i<this.dir_hist.length; i++){
                path += this.dir_hist[i].path;
            }
            
            return path;
        };
        
        this.check_mounted = function(){
            _debug('media_browser.check_mounted');
            
            try{
                var txt = stb.ListDir(this.compile_path());
                
                eval(txt);
                
                var list = dirs.concat(files);
                
                for (var i=0; i < list.length; i++){
                    if (!empty(list[i])){
                        this.drive_mounted();
                        return;
                    }
                }
            }catch(e){
                _debug(e);
            }
            
            return;  
        };
        
        this.drive_mounted = function(){
            _debug('media_browser.drive_mounted');
            
            this.mounted = true;
            this.load_data();
            
            stb.notice.show('USB устройство подключено');
        };
        
        this.is_drive_mounted = function(){
            _debug('media_browser.is_drive_mounted');
            
            if (this.mounted){
                return true;
            }
            
            return false;
        }
        
        this.drive_umounted = function(){
            _debug('media_browser.drive_umounted');
            
            this.mounted = false;
            
            stb.notice.show('USB устройство отключено');
            
            /*if (this.hidden || (this.on && this.is_audio)){
                
                if (player_on){
                    stop()
                    hide_black()
                    stb.SetDefaultFlicker(1)
                }
                
                this.hide();
                show_club();
            }*/
        }
        
        this.action = function(){
            
            if (this.data_items[this.cur_row].hasOwnProperty('dir')){
                
                if (this.data_items[this.cur_row].name == '..'){
                    this.out_dir();
                }else{
                    this.in_dir(this.data_items[this.cur_row].name);
                }
            }else{
                this.play(this.data_items[this.cur_row])
            }
        };
        
        this.play = function(item){
            _debug('media_browser.play');
            
            if (stb.player.get_file_type(item) == 'audio'){
                this.is_audio = true;
            }else{
                this.is_audio = false;
                
                stb.player.prev_layer = this;
                this.hide(true);
            }
            
            stb.player.show_info(item);
            stb.player.play_now(item.cmd);
        }
        
        this.out_dir = function(){
            _debug('out_dir');
            
            if (this.dir_hist.length > 1){
                this.dir_hist.pop();
                
                this.reset();
                
                this.cur_row  = this.dir_hist[this.dir_hist.length - 1].row;
                this.cur_page = this.dir_hist[this.dir_hist.length - 1].page;
                
                this.load_data();
            }
        };
        
        this.in_dir = function(dir){
            _debug('in_dir');
            
            this.dir_hist[this.dir_hist.length - 1].page = this.cur_page;
            this.dir_hist[this.dir_hist.length - 1].row  = this.cur_row;
            
            var hist_item = {'path' : dir , 'page' : 1, 'row' : 1};
            
            this.dir_hist.push(hist_item);
            
            this.reset();
            
            this.load_data();
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.action.bind(key.OK, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this);
        };
    }
    
    media_browser_constructor.prototype = new Layer();
    
    var media_browser = new media_browser_constructor();
    
    media_browser.bind();
    media_browser.init();
    
    media_browser.set_wide_container();
    
    media_browser.init_left_ear('ears_back');
    
    media_browser.init_header_path('USB Устройство');
    
    media_browser.hide();
    
    module.media_browser = media_browser;
    
    /* END MEDIA BROWSER */
    
    main_menu.add('USB Устройство', [], function(){
        if (module.media_browser.is_drive_mounted()){
            main_menu.hide();
            module.media_browser.show();
        }else{
            stb.notice.show('USB устройство не найдено');
        }
    });
    
})();

loader.next();