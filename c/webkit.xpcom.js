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