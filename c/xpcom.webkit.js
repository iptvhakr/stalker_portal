/**
 * WebKit STB constructor.
 * @constructor
 */
function webkit_xpcom(){
    
    this.StandBy = function(par){

        gSTB.StandBy(par);

        if (gSTB.SetLedIndicatorMode){
            gSTB.SetLedIndicatorMode(par ? 2 : 1);
        }

        if (gSTB.SetLedIndicatorLevels){
            return;
        }

        _debug('gSTB.RDir(HardwareVersion).substr(0, 3)', gSTB.RDir('HardwareVersion').substr(0, 3));

        if (['0.1', '0.2'].indexOf(gSTB.RDir('HardwareVersion').substr(0, 3)) == -1){
            if (par){
                gSTB.ExecAction('front_panel led-on');
            }else{
                gSTB.ExecAction('front_panel led-off');
            }
        }
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