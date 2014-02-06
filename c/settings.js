/**
 * Settings modile.
 */

(function(){

    var submenu = module.settings_sub || [];

    if (stb.profile['use_embedded_settings']){
        main_menu.add(word['settings_title'], submenu, 'mm_ico_setting.png', '', {"layer_name" : "settings"});
    }else{
        main_menu.add(word['settings_title'], [], 'mm_ico_setting.png', function(){
            if (connection_problem && connection_problem.on){
                stb.notice.show(get_word('settings_unavailable'));
            }else{
                var url = "http://" + stb.portal_ip +  "/" + stb.portal_path + "/external/settings/index.html?ajax_loader=" + stb.ajax_loader;
                url += '&language=' + stb.stb_lang;
                url += '&token=' + stb.access_token;
                url += '&pri_audio_lang=' + stb.user['pri_audio_lang'];
                url += '&sec_audio_lang=' + stb.user['sec_audio_lang'];
                url += '&pri_subtitle_lang=' + stb.user['pri_subtitle_lang'];
                url += '&sec_subtitle_lang=' + stb.user['sec_subtitle_lang'];

                _debug(url);
                stbWindowMgr.openWebFavorites(url, 0);
            }
        }, {"layer_name" : "settings"});
    }

})();

loader.next();