/**
 * Redirection to Lines game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : get_word('game') + ' LINES',
        "cmd"   : function(){
            stb.setFrontPanel('.');
            window.location = '/' + stb.portal_path + '/external/lines/index.html' + '?referrer='+encodeURIComponent(document.location.href);
        }
    });

    loader.next();
})();