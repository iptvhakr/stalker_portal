/**
 * Settings modile.
 */

(function(){
    
    var submenu = module.settings_sub || [];
    
    main_menu.add(word['settings_title'], submenu, 'mm_ico_setting.png', '', {"layer_name" : "settings"});

    /*main_menu.add(word['settings_title'], [], 'mm_ico_setting.png', function(){
        
    }, {"layer_name" : "settings"});*/
    
})();

loader.next();