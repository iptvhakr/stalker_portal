/**
 * Settings modile.
 */

(function(){

    var submenu = module.settings_sub || [];

    if (stb.profile['use_embedded_settings']){
        main_menu.add(word['settings_title'], submenu, 'mm_ico_setting.png', '', {"layer_name" : "settings"});
    }else{

        if (typeof(dvbManager) != 'undefined'){
            var dvb_supported_scan_types = JSON.parse(dvbManager.GetSupportedScanTypes());
            var dvb_current_scan_types = JSON.parse(dvbManager.GetCurrentScanTypes());
        }else{
            dvb_supported_scan_types = [];
            dvb_current_scan_types = [];
        }

        dvb_supported_scan_types = dvb_supported_scan_types.map(function(scan_type){
            return scan_type.name;
        });

        dvb_current_scan_types = dvb_current_scan_types.map(function(scan_type){
            return scan_type.name;
        });

        main_menu.add(word['settings_title'], [], 'mm_ico_setting.png', function(){
            if (connection_problem && connection_problem.on){
                stb.notice.show(get_word('settings_unavailable'));
            }else{
                var url = window.location.protocol+"//" + stb.portal_ip +  "/" + stb.portal_path + "/external/settings/index.html?ajax_loader=" + stb.ajax_loader;
                url += '&language=' + stb.stb_lang;
                url += '&token=' + stb.access_token;
                url += '&pri_audio_lang=' + stb.user['pri_audio_lang'];
                url += '&sec_audio_lang=' + stb.user['sec_audio_lang'];
                url += '&pri_subtitle_lang=' + stb.user['pri_subtitle_lang'];
                url += '&sec_subtitle_lang=' + stb.user['sec_subtitle_lang'];
                url += '&dvb_supported_scan_types=' + dvb_supported_scan_types.join('|');
                url += '&dvb_current_scan_types=' + dvb_current_scan_types.join('|');

                _debug(url);
                stbWindowMgr.openWebFavorites(url, 0);
            }
        }, {"layer_name" : "settings"});
    }

})();

loader.next();