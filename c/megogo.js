/**
 * Redirection to megogo module.
 */
(function(){

    if (stb.type == 'MAG200' || resolution_prefix != '_720'){
        loader.next();
        return;
    }

    main_menu.add('MEGOGO', [], 'mm_ico_megogo.png', function(){

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

        var url = 'http://aura-app.megogo.net/'+params;
        _debug('url', url);
        window.location = url;
    }, {layer_name : "megogo"});

    loader.next();
})();