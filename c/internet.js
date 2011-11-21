/**
 * Web browser.
 */
(function(){

    if (!stbWindowMgr){
        return;
    }

    main_menu.add(get_word('internet'), [], 'mm_ico_internet.png', function(){

        stb.EnableVKButton(true);

        stbWindowMgr.openWebWindow('http://google.com');
        
    }, {});

    loader.next();
})();