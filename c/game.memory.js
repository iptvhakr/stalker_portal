/**
 * Redirection to Memory game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : get_word('game') + ' MEMORY',
        "cmd"   : function(){
            stb.setFrontPanel('.');
            window.location = '/' + stb.portal_path + '/external/memory/index.html' + '?referrer='+encodeURIComponent(document.location.href);
        }
    });

    loader.next();
})();