/**
 * Software update module.
 */

(function(){
    
    if (stb.type != 'MAG250'){
        return;
    }
    
    function update_settings_constructor(){
        
        this.layer_name = 'update_settings';
        
        this.superclass = SettingLayer.prototype;
        
        this.source_idx = 0;
        
        this.sources = [
            {
                "title" : word['update_from_http'],
                "uri"   : (function(){ try{ return stb.RDir('getenv update_url').clearnl()}catch(e){_debug(e)}})()
            },
            {
                "title" : word['update_from_usb'],
                "uri"   : "/media/usbdisk/mag200/imageupdate"
            }
        ];
        
        this.show = function(){
            _debug('update_settings.show');
            
            this.superclass.show.call(this);
            
            this.fill_select();
        };
        
        this.init = function(){
            _debug('update_settings.init');
            
            this.superclass.init.call(this);
            
            this.update_container = create_block_element('select_container', this.container);
            
            var method = create_block_element('method', this.update_container);
            method.innerHTML = word['update_method_select'];
            
            var larr = create_block_element('larr', this.update_container);
            larr.innerHTML = '&lsaquo;';
            this.select = create_block_element('select_source', this.update_container);
            var rarr = create_block_element('rarr', this.update_container);
            rarr.innerHTML = '&rsaquo;';
            
            var source = create_block_element('source', this.update_container);
            
            var source_txt = create_inline_element('', source);
            source_txt.innerHTML = word['update_source'] + ': ';
            
            this.uri = create_inline_element('uri', source);
        };
        
        this.bind = function(){
            
            this.h_shift.bind(key.LEFT, this, -1).bind(key.DOWN, this, -1);
            this.h_shift.bind(key.RIGHT, this, 1).bind(key.UP, this, 1);
            
            this.start_update.bind(key.OK, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.MENU, this);
        };
        
        this.h_shift = function(dir){
            _debug('update_settings.h_shift', dir);
            
            if (dir > 0){
                if (this.source_idx < this.sources.length - 1){
                    this.source_idx++;
                }else{
                    this.source_idx = 0;
                }
            }else{
                if (this.source_idx > 0){
                    this.source_idx--;
                }else{
                    this.source_idx = this.sources.length - 1
                }
            }
            
            this.fill_select();
        };
        
        this.fill_select = function(){
            _debug('update_settings.fill_select');
            
            this.select.innerHTML = this.sources[this.source_idx].title;
            this.uri.innerHTML    = this.sources[this.source_idx].uri;
        };
        
        this.start_update = function(){
            _debug('update_settings.start_update');
            
            stbUpdate.startAutoUpdate(this.sources[this.source_idx].uri, false);
        };
        
    }
    
    update_settings_constructor.prototype = new SettingLayer();
    
    var update_settings = new update_settings_constructor();
    
    update_settings.init();
    
    update_settings.bind();
    
    update_settings.init_color_buttons([
        {"label" : word['update_settings_cancel'], "cmd" : function(){update_settings.hide(); main_menu.show()} },
        {"label" : word['update_settings_start_update'], "cmd" : update_settings.start_update},
        {"label" : word['empty'], "cmd" : ''},
        {"label" : word['empty'], "cmd" : ''}
    ]);
    
    update_settings.color_buttons[update_settings.color_buttons.getIdxByVal('color', 'yellow')].text_obj.setClass('disable_color_btn_text');
    update_settings.color_buttons[update_settings.color_buttons.getIdxByVal('color', 'blue')].text_obj.setClass('disable_color_btn_text');
    
    update_settings.init_header_path(word['settings_software_update']);
    
    update_settings.hide();
    
    module.update_settings = update_settings;
    
    if (!module.settings_sub){
        module.settings_sub = [];
    }
    
    module.settings_sub.push({
        "title" : word['settings_software_update'],
        "cmd"   : function(){
            main_menu.hide();
            module.update_settings.show();
        }
    })
    
})();

loader.next();