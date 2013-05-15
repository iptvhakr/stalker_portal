/**
 * Redirection to Sudoku game.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : get_word('game') + ' SUDOKU',
        "cmd"   : function(){
            stb.setFrontPanel('.');
            window.location = '/' + stb.portal_path + '/external/sudoku/index.html' + '?referrer='+encodeURIComponent(document.location.href);;
        }
    });

    loader.next();
})();