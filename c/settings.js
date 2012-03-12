/**
 * Settings modile.
 */

(function(){

    var submenu = module.settings_sub || [];

    if (stb.firmware_version < 212 || stb.profile['use_embedded_settings']){
        main_menu.add(word['settings_title'], submenu, 'mm_ico_setting.png', '', {"layer_name" : "settings"});
    }else{
        main_menu.add(word['settings_title'], [], 'mm_ico_setting.png', function(){
            _debug("http://" + stb.portal_ip +  "/" + stb.portal_path + "/external/settings/index.html");
            stbWindowMgr.openWebFavorites("http://" + stb.portal_ip +  "/" + stb.portal_path + "/external/settings/index.html?ajax_loader="+stb.ajax_loader, 0);
        }, {"layer_name" : "settings"});
    }

})();

loader.next();