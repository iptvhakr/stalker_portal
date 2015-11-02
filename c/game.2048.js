/**
 * Redirection to 2048 game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : get_word('game') + ' 2048',
        "cmd"   : function(){
            stb.setFrontPanel('.');
            window.location = '/' + stb.portal_path + '/external/2048/index.html' + '?referrer='+encodeURIComponent(document.location.href);
        }
    });

    loader.next();
})();