/**
 * Redirection to internal youtube module.
 */
(function(){
    
    main_menu.add('YouTube', [], 'i/mm_ico_youtube.png', function(){

        var params = '';

        if (stb.user['web_proxy_host']){
            params = '?proxy=http://'+stb.user['web_proxy_host']+':' +stb.user['web_proxy_port'];
        }

        window.location = '/' + stb.portal_path + '/external/youtube/index.html'+params;
    }, {});

    loader.next();
})();