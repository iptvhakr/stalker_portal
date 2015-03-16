/**
 * Redirection to ivi.ru app.
 */
(function(){

    main_menu.add('IVI', [], 'mm_ico_ivi.png', function(){

        var params = '';

        if (stb.user['web_proxy_host']){
            params += '?proxy=http://';
            if (stb.user['web_proxy_user']){
                params += stb.user['web_proxy_user']+':'+stb.user['web_proxy_pass']+'@';
            }
            params += stb.user['web_proxy_host']+':' +stb.user['web_proxy_port'];
        }

        stb.setFrontPanel('.');

        if (!params){
            params += '?';
        }else{
            params += '&';
        }

        params = stb.add_referrer(params, this.module.layer_name);

        _debug('url', '/' + stb.portal_path + '/external/ivi/index.html'+params);
        window.location = '/' + stb.portal_path + '/external/ivi/index.html'+params;
    }, {layer_name : "ivi"});

    loader.next();
})();