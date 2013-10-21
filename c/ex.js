/**
 * Redirection to ex.ua module.
 */
(function(){

    main_menu.add('EX.UA', [], 'mm_ico_ex.png', function(){

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

        _debug('url', '/' + stb.portal_path + '/external/ex/index.html'+params);

        window.location = '/' + stb.portal_path + '/external/ex/index.html'+params;
    }, {layer_name : "ex"});

    loader.next();
})();