/**
 * Redirection to vimeo module.
 */
(function(){

    if (stb.type == 'MAG200'){
        loader.next();
        return;
    }

    main_menu.add('VIMEO', [], 'mm_ico_vimeo.png', function(){

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

        var url = 'http://vimeo.magapps.net/app/'+params;
        _debug('url', url);
        window.location = url;
    }, {layer_name : "vimeo"});

    loader.next();
})();