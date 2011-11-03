/**
 * Media browser.
 */

(function(){
    
    /* MEDIA BROWSER */
    function media_browser_constructor(){
        
        this.layer_name = 'media_browser';
        
        this.row_blocks  = ["dir", "name"];
        this.mounted = false;
        this.cur_dir_list = [];
        this.data_items   = [];
        this.is_audio = false;
        
        this.superclass = ListLayer.prototype;
        
        //this.dir_hist = [{"path" : "/media/usbdisk/", "page" : 1, "row" : 1}];
        this.dir_hist = [{"path" : "/media/", "page" : 1, "row" : 1}];

        this.init = function(){
            
            this.superclass.init.call(this);
            
            var self = this;
            
            stb.usbdisk.add_onmount_callback(function(){
                self.load_data();
            });
            
            stb.usbdisk.add_onumount_callback(function(){
                _debug('media_browser onunmount');
                _debug('self.on', self.on);
                
                if (stb.player.prev_layer == self || self.on){
                    
                    if (stb.player.on){
                        stb.player.stop();
                    }
                    
                    self.hide();
                    main_menu.show();
                }
            });
            
        };
        
        this.hide = function(do_not_reset){
            _debug('media_browser.hide', do_not_reset);
            
            try{
                
                /*if (this.on){*/
                    if (stb.player.on){
                        stb.player.stop();
                    }
                /*}*/
                
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
                
                stb.usbdisk.read_dir(path);
                
                //_debug(txt);

                _debug('stb.storages', stb.storages);
                _debug('stb.usbdisk.dirs', stb.usbdisk.dirs);

                try{
                    var storage_info = JSON.parse(stb.RDir('get_storage_info'));
                }catch(e){
                    _debug(e);
                }

                var devices = {};

                if (storage_info){
                    for (var i=0; i<storage_info.length; i++){
                        devices['USB-' + storage_info[i].sn + '-' + storage_info[i].partitionNum] = storage_info[i].vendor
                                + ' ' + storage_info[i].model
                                + (storage_info[i].label ? '(' + storage_info[i].label + ')' : '')
                                + (storage_info.length > 1 ? ' #' + storage_info[i].partitionNum : '');
                    }
                }

                if (this.dir_hist.length == 1){
                    stb.usbdisk.dirs = stb.usbdisk.dirs.filter(function(el){return !stb.storages.hasOwnProperty(el.substr(0, el.length-1))});
                }

                /*if (devices.length > 0){
                    stb.usbdisk.dirs = stb.usbdisk.dirs.map(function(item){
                        if (devices.[item.substr(0, item.length-1)]){
                            return devices[item.substr(0, item.length-1)];
                        }

                        return item;
                    });
                }*/

                _debug('stb.usbdisk.dirs 2', stb.usbdisk.dirs);
                
                var new_dirs = [];
                
                for (var i=0; i < stb.usbdisk.dirs.length; i++){
                    if (!empty(stb.usbdisk.dirs[i])){

                        if (devices[stb.usbdisk.dirs[i].substring(0, stb.usbdisk.dirs[i].length - 1)]){
                            var name = devices[stb.usbdisk.dirs[i].substring(0, stb.usbdisk.dirs[i].length - 1)];
                        }else{
                            name = stb.usbdisk.dirs[i].substring(0, stb.usbdisk.dirs[i].length - 1);
                        }

                        if (name == 'av'){
                            name = 'UPnP';
                        }else if (name.indexOf('USB-') == 0){
                            continue;
                        }

                        new_dirs.push({"name" : name, "dir" : 1, "dir_name" : stb.usbdisk.dirs[i]})
                    }
                }
                
                var new_files = [];
                
                for (var i=0; i < stb.usbdisk.files.length; i++){
                    if (!empty(stb.usbdisk.files[i])){
                        new_files.push({"name" : stb.usbdisk.files[i].name, "cmd" : ("auto " + path + stb.usbdisk.files[i].name), "size" : stb.usbdisk.files[i].size});
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
        
        this.action = function(){
            
            if (this.data_items[this.cur_row].hasOwnProperty('dir')){
                
                if (this.data_items[this.cur_row].name == '..'){
                    this.out_dir();
                }else{
                    this.in_dir(this.data_items[this.cur_row].dir_name);
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
                this.hide(true);
                stb.player.prev_layer = this;
            }
            
            _debug('this.dir_hist', this.dir_hist);
            
            stb.player.need_show_info = 1;
            stb.player.play(item);
        };
        
        this.out_dir = function(){
            _debug('out_dir');
            
            if (this.dir_hist.length > 1){
                this.dir_hist.pop();
                
                this.reset();
                
                this.cur_row  = this.dir_hist[this.dir_hist.length - 1].row;
                this.cur_page = this.dir_hist[this.dir_hist.length - 1].page;
                
                this.change_level = true;
                
                this.load_data();
            }
        };
        
        this.in_dir = function(dir){
            _debug('in_dir', dir);
            
            this.dir_hist[this.dir_hist.length - 1].page = this.cur_page;
            this.dir_hist[this.dir_hist.length - 1].row  = this.cur_row;
            
            var hist_item = {'path' : dir , 'page' : 1, 'row' : 1};
            
            this.dir_hist.push(hist_item);
            
            this.reset();
            
            this.change_level = true;
            
            this.load_data();
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.action.bind(key.OK, this);
            
            (function(){
                
                if (stb.player.on && this.is_audio){
                    stb.player.stop();
                    return;
                }
                
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.LEFT, this).bind(key.MENU, this);
            
            this.out_dir.bind(key.BACK, this);
        };
        
        this.fill_list = function(data){
            
            this.data_items = data;
            
            if (!this.change_level){
                
                if (this.page_dir > 0){
                    this.cur_row = 0;
                }else{
                    this.cur_row = this.data_items.length - 1;
                }
            }
            
            this.change_level = false;
            
            this.superclass.fill_list.call(this, data);
        };
    }
    
    media_browser_constructor.prototype = new ListLayer();
    
    var media_browser = new media_browser_constructor();
    
    media_browser.bind();
    media_browser.init();
    
    media_browser.set_wide_container();
    
    media_browser.init_left_ear(word['ears_back']);
    
    media_browser.init_header_path(word['mbrowser_title']);
    
    media_browser.hide();
    
    module.media_browser = media_browser;
    
    /* END MEDIA BROWSER */
    
    main_menu.add(word['mbrowser_title'], [], 'mm_ico_usb.png', function(){
        //if (stb.usbdisk.is_drive_mounted()){
            main_menu.hide();
            module.media_browser.show();
        //}else{
        //    stb.notice.show(word['mbrowser_title'] + ' ' + word['mbrowser_not_found']);
        //}
    },
    module.media_browser
    );
    
})();

loader.next();