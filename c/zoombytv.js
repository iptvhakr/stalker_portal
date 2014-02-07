/**
 * Redirection to zoombytv module.
 */
(function(){

    main_menu.add('ZOOMBY', [], 'mm_ico_zoomby.png', function(){

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

        var url = 'http://www.zoomby.ru/ce/mag/mag.html' + params;

        _debug('url', url);
        window.location = url;
    }, {layer_name : "zoombytv"});

    loader.next();
})();