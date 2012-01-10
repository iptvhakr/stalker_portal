/**
 * Web browser.
 */
(function(){

    if (!stbWindowMgr){
        return;
    }

    module.internet = {
        win_inited : false
    };

    main_menu.add(get_word('internet'), [], 'mm_ico_internet.png', function(){

        stb.EnableVKButton(true);

        _debug('module.internet.win_inited', module.internet.win_inited);

        /*if (module.internet.win_inited){
            stbWindowMgr.raiseWebWindow();
        }else{*/
            stbWindowMgr.openWebWindow('http://google.com');
            module.internet.win_inited = true;
        //}
    }, {});

    loader.next();
})();