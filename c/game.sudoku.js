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
            window.location = '/' + stb.portal_path + '/external/sudoku/index.html';
        }
    });

    loader.next();
})();