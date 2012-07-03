/**
 * Redirection to ex.ua module.
 */
(function(){

    main_menu.add('EX.UA', [], 'mm_ico_ex.png', function(){

        var params = '';

        if (stb.user['web_proxy_host']){
            params = '?proxy=http://'+stb.user['web_proxy_host']+':' +stb.user['web_proxy_port'];
        }

        stb.setFrontPanel('.');
        window.location = '/' + stb.portal_path + '/external/ex/index.html'+params;
    }, {layer_name : "ex"});

    loader.next();
})();