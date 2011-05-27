/**
 * Redirection to Memory game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : 'MEMORY',
        "cmd"   : function(){
            window.location = '/' + stb.portal_path + '/external/memory/index.html';
        }
    });

    loader.next();
})();