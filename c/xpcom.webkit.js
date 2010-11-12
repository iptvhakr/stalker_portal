/**
 * WebKit STB constructor.
 * @constructor
 */
function webkit_xpcom(){
    
    this.StandBy = function(par){
        gSTB.StandBy(par)
        panel = '.200'
        if (par){
            panel = '....'
        }
        _debug('front_panel caption', panel)
        gSTB.ExecAction('front_panel caption ' + panel)
    }
}

var stb;

//try{
        
    var g_stb;
    
    if (typeof(gSTB) == 'undefined'){
        g_stb = {};
    }else{
        g_stb = gSTB;
    }
    
    webkit_xpcom.prototype = g_stb;
    common_xpcom.prototype = new webkit_xpcom();
    
    stb = new common_xpcom();
    //stb.init();
    
//}catch(e){
//    _debug(e)
//}