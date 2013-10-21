/**
 * Redirection to vidimax module.
 */
(function(){

    main_menu.add('VIDIMAX', [], 'mm_ico_vidimax.png', function(){

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

        window.location = 'http://app-serv.vidimax.ru/mag/index.html'+params;
    }, {layer_name : "vidimax"});

    loader.next();
})();