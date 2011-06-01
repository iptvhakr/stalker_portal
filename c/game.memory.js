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
            window.location = '/' + stb.portal_path + '/external/memory/index.html';
        }
    });

    loader.next();
})();