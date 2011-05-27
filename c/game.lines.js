/**
 * Redirection to Lines game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : 'LINES',
        "cmd"   : function(){
            window.location = '/' + stb.portal_path + '/external/lines/index.html';
        }
    });

    loader.next();
})();