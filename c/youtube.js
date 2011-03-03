/**
 * Redirection to internal youtube module.
 */
(function(){
    
    main_menu.add('YouTube', [], 'i/mm_ico_youtube.png', function(){
        window.location = '/' + stb.portal_path + '/external/youtube/index.html';
    }, {});

    loader.next();
})();