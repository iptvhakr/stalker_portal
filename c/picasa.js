/**
 * Redirection to Picasa.
 */
(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : 'PICASA',
        "cmd"   : function(){
            window.location = '/' + stb.portal_path + '/external/picasa/picasa_main.html';
        }
    });

    loader.next();
})();