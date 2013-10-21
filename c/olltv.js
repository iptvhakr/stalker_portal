/**
 * Redirection to oll.tv module.
 */
(function(){

    if (stb.type == 'MAG200'){
        loader.next();
        return;
    }

    main_menu.add('OLL.TV', [], 'mm_ico_oll.png', function(){

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

        params += 'referrer='+encodeURIComponent(window.location);

        _debug('url', '/' + stb.portal_path + '/external/olltv/index.html'+params);
        window.location = '/' + stb.portal_path + '/external/olltv/index.html'+params;
    }, {layer_name : "olltv"});

    loader.next();
})();