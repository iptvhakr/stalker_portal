/**
 * WebKit STB constructor.
 * @constructor
 */
function webkit_xpcom(){
    
    this.StandBy = function(par){

        gSTB.StandBy(par);
    };

    this.setFrontPanel = function(num, use_colon){
        _debug('stb.setFrontPanel', num, use_colon);

        if (!num){
            return;
        }

        num = num.toString();

        var panel = ['.','.','.','.'].map(function(val, idx, arr){

            if (num.length < arr.length - idx){
                return val;
            }else{
                return num[num.length - arr.length + idx];
            }
        }).join('');

        try{

            gSTB.ExecAction('front_panel caption ' + panel);

            if (use_colon){
                gSTB.ExecAction('front_panel colon-on');
            }else{
                gSTB.ExecAction('front_panel colon-off');
            }
        }catch(e){
            
        }
    };
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