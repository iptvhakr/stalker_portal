/**
 * Localization settings module.
 */

(function(){
    
    /* SETTINGS */
    
    function localization_settings_constructor(){
        
        this.layer_name = 'localization_settings';
        
        this.save_params = {"type" : "stb", "action" : "set_default_lang"};
        
        this.superclass = SettingLayer.prototype;
        
        this.save_callback = function(){
            _debug('save_callback');
            stb.msg.set_callback(function(){stb.ExecAction('reboot')});
            stb.msg.push(word['settings_saved_reboot']);
        };
        
        this.load_default = function(){
            _debug('localization_settings.load_default');
            
            //this.set_default({});
            
            this.controls[0].set_default();
        };
        
        this.load_map = function(){
            _debug('localization_settings.load_map');
            
            stb.load(
                {
                    "type"   : "stb",
                    "action" : "get_languages"
                },
                
                function(result){
                    _debug('localization_settings.load_map callback', result);
                    
                    this.map = result;
                    
                    this.add_control(new OptionInput(this, {"name" : "default_lang", "label" : word['localization_label'], "map" : this.map}));
                },
                
                this
            )
            
        }
        
        this.save = function(){
            _debug('localization_settings.save');
            
            this.get_input_value('default_lang');
            this.superclass.save.apply(this);
            
        }
    }
    
    localization_settings_constructor.prototype = new SettingLayer();
    
    var localization_settings = new localization_settings_constructor();
    
    localization_settings.init();
    
    localization_settings.load_map();
    
    //localization_settings.add_control(new OptionInput(localization_settings, {"name" : "default_lang", "label" : word['localization_label']}));
    
    localization_settings.bind();
    
    localization_settings.init_left_ear(word['ears_back']);
    
    localization_settings.init_color_buttons([
        {"label" : word['parent_settings_cancel'], "cmd" : localization_settings.cancel},
        {"label" : word['parent_settings_save'], "cmd" : localization_settings.save},
        {"label" : word['empty'], "cmd" : ''},
        {"label" : word['empty'], "cmd" : ''}
    ]);
    
    localization_settings.color_buttons[localization_settings.color_buttons.getIdxByVal('color', 'yellow')].text_obj.setClass('disable_color_btn_text');
    localization_settings.color_buttons[localization_settings.color_buttons.getIdxByVal('color', 'blue')].text_obj.setClass('disable_color_btn_text');
    
    localization_settings.init_header_path(word['localization_settings_title']);
    
    localization_settings.hide();
    
    module.localization_settings = localization_settings;
    
    /* END SETTINGS */
    
    if (!module.settings_sub){
        module.settings_sub = [];
    }
    
    module.settings_sub.push({
        "title" : word['localization_settings_title'],
        "cmd"   : function(){
            main_menu.hide();
            module.localization_settings.show();
        }
    })
    
})()

loader.next();