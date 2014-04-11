/**
 * Web browser.
 */
(function(){

    if (typeof(stbWindowMgr) == "undefined"){
        return;
    }

    module.internet = {
        win_inited : false
    };

    if (stbWindowMgr.InitWebWindow){
        stbWindowMgr.InitWebWindow(
            '/home/web/public/app/bookmarks/header.html',
            '/home/web/public/app/bookmarks/footer.html');
    }

    main_menu.add(get_word('internet'), [], 'mm_ico_internet.png', function(){

        stb.EnableVKButton(true);

        _debug('module.internet.win_inited', module.internet.win_inited);

        _debug('stbWindowMgr.IsWebWindowExist', !!stbWindowMgr.IsWebWindowExist);

        _debug('stbWindowMgr.openWebFace', !!stbWindowMgr.openWebFace);

        if (stbWindowMgr.openWebFace){
            stbWindowMgr.openWebFace('/home/web/public/app/ibman/index.html?mode=2&url='+encodeURIComponent('http://google.com'));
            module.internet.win_inited = true;
        }else if (module.internet.win_inited && stbWindowMgr.IsWebWindowExist && stbWindowMgr.IsWebWindowExist()){
            stbWindowMgr.raiseWebWindow();
        }else{
            if (stbWindowMgr.InitWebWindow){
                stbWindowMgr.LoadUrl('http://google.com');
                stbWindowMgr.raiseWebWindow();
            }else{
                stbWindowMgr.openWebWindow('http://google.com');
            }

            module.internet.win_inited = true;
        }
    }, {layer_name : "internet"});
})();

loader.next();