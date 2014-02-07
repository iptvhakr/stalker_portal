/**
 * Redirection to tvzavr module.
 */
(function(){

    main_menu.add('TVZAVR', [], 'mm_ico_tvzavr.png', function(){

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

        var url = 'http://services.tvzavr.ru/mag/' + params;

        _debug('url', url);
        window.location = url;
    }, {layer_name : "tvzavr"});

    loader.next();
})();