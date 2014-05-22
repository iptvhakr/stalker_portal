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
            stb.setFrontPanel('.');
            window.location = '/' + stb.portal_path + '/external/picasa/index.html';
        }
    });

    loader.next();
})();