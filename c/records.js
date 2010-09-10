/**
 * Records.
 */

(function(){
    
    /* RECORDS */
    function records_constructor(){
        
        this.layer_name = 'records';
        
        this.row_blocks = ['t_start', 'ch_name', 'length'];
        
        this.superclass = Layer.prototype;
        
        this.init = function(){
            
            this.superclass.init.call(this);
            
            var self = this;
            
            stb.usbdisk.add_onmount_callback(function(){
                self.load_data();
            });
            
            stb.usbdisk.add_onumount_callback(function(){
                if (self.on){
                    
                    if (stb.player.on){
                        stb.player.stop();
                    }
                    
                    self.hide();
                    main_menu.show();
                }
            });
        }
        
        this.hide = function(do_not_reset){
            _debug('records.hide', do_not_reset);
            
            try{
                
                if (this.on){
                    if (stb.player.on){
                        stb.player.stop();
                    }
                }
                
                this.superclass.hide.call(this, do_not_reset);
                
                if (!do_not_reset){
                    this.reset();
                }
                
            }catch(e){
                _debug(e);
            }
        };
        
        this.reset = function(){
            _debug('records.reset');
            
            this.cur_row  = 0;
            this.cur_page = 1;
            this.total_pages = 1;
        };
        
        this.load_data = function(){
            _debug('records.load_data');
            
            var list = stb.pvr.get_rec_list();
            
            this.total_pages = Math.ceil(list.length/14);
            
            this.set_total_items(list.length);
            
            this.cur_dir_list = list;
            
            var begin = (this.cur_page - 1) * 14;
            var end   = this.cur_page * 14;
            
            this.data_items = this.cur_dir_list.slice(begin, end);
            
            this.fill_list(this.data_items);
        };
        
        this.action = function(){
            _debug('records.action');
            
            stb.player.prev_layer = this;
            this.hide(true);
            
            stb.player.need_show_info = 1;
            
            stb.player.play(this.data_items[this.cur_row]);
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.action.bind(key.OK, this);
            
            (function(){
                
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this).bind(key.MENU, this);
        };
    }
    
    stb.pvr = new pvr();
    
    records_constructor.prototype = new Layer();
    
    var records = new records_constructor();
    
    records.bind();
    records.init();
    
    records.set_wide_container();
    
    records.init_left_ear('ears_back');
    
    records.init_header_path(word['records_title']);
    
    records.hide();
    
    module.records = records;
    
    /* END RECORDS */
    
    main_menu.add(word['records_title'], [], 'i/mm_ico_usb.png', function(){
        if (stb.usbdisk.is_drive_mounted()){
            main_menu.hide();
            module.records.show();
        }else{
            stb.notice.show(word['mbrowser_title'] + ' ' + word['mbrowser_not_found']);
        }
    });
    
})();

loader.next();