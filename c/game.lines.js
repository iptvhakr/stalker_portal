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
            window.location = '/' + stb.portal_path + '/external/lines/index.html';
        }
    });

    loader.next();
})();