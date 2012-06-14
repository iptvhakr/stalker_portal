window.onload = init;
function init(){
    _load.stb();
    _load.get();
    _load.grafics();
    _load.css('screen_' + as.actualSize + '.css');
    _load.lang(app.ctrls.init);
    
}