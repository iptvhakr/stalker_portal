function log(text){
    if(debug){
        stb.Debug(text);
    }
}
function $(id){
    return document.getElementById(id);
}
function byclass(classname){
    return document.getElementsByClassName(classname);
}
function sendreq(url, callback,call_argument,hide_blackbg){
    try {
        keyblock = true;
/*        if(hide_blackbg != true){
            show_waiting();
        }*/
        log('REQUEST URL: '+url);
        var request = new XMLHttpRequest();
        request.open('GET', url, true);
        request.setRequestHeader("Content-Type", "text/xml");
        request.setRequestHeader("charset", "utf-8");
        request.onreadystatechange = function ()
        {   
            log(request.readyState+' : '+request.status);
            if (request.readyState == 4 && request.status == 200) {
                var answer = request.responseText;
				log(request.responseText);
                if(!empty(call_argument)){
                    callback(answer,call_argument);
                }else{
                    callback(answer);
                }
                keyblock = false;
                //hide_waiting();
            }else{
                if(request.readyState == 4 && request.status == 404){
                    log('something wrong whith this request: '+url)
                }
            }
        };
        request.send(null); // send object
    } catch (e) {
        return;
    }
}
function utf8_to_b64( str ) {
    return window.btoa(unescape(encodeURIComponent( str )));
}

function b64_to_utf8( str ) {
    return decodeURIComponent(escape(window.atob( str )));
}
function loadScript(src, onLoad){
    var elem = document.createElement('script');
    elem.setAttribute('language','JavaScript');
    elem.setAttribute('src',src);
    if (onLoad) {
        elem.setAttribute('onLoad',onLoad);
    }
    document.getElementsByTagName('head')[0].appendChild(elem);
}

// Динамическая подгрузка файла CSS
// src = URL подгружаемого файла
function loadStyle(src){
    var elem = document.createElement('link');
    elem.setAttribute('rel','stylesheet');
    elem.setAttribute('type','text/css');
    elem.setAttribute('href',src);
    document.getElementsByTagName('head')[0].appendChild(elem);
}
var Utf8 = {
 
    // public method for url encoding
    encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },
    // public method for url decoding
    decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
 
        while ( i < utftext.length ) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}
function empty(mixed_var) {         //проверка на все варианты отсутсвия значения
    if (mixed_var === "" ||
        mixed_var === 0 ||
        mixed_var === "0" ||
        mixed_var === null ||
        mixed_var === false ||
        typeof mixed_var === 'undefined' ||
        typeof mixed_var === 'NaN'
        ) {
        return true;
    }
    if (typeof mixed_var == 'object') {
        for (var key in mixed_var) {
            return false;
        }
        return true;
    }
    return false;
}
function getEnvironmentValue(name){
    var value;
    value = stb.RDir('getenv ' + name);
    return value;
}
function objToString(o) {
    var s = '{';
    for (var p in o)
        s += p + ': "' + o[p] + '",';
    return s + '}';
}
function newMyAlert(text,type){
    newAlert_on = true;
    window.setTimeout(function(){newAlert_on = true;}, 10);
    if(document.getElementsByClassName('modal_new')[0]){
        document.body.removeChild(document.getElementsByClassName('modal_new')[0]);
    }
    var div = document.createElement('div');
    div.className = 'modal_new';
    if(type && type == 'temp'){
        div.innerHTML = text;
        div.style.paddingBottom='20px';
    }else{
        div.innerHTML = text+'<div id="modal_btn_new"><input align="middle" id="alert_btn_ok" name="btn" type="button" onclick="document.body.removeChild(document.getElementsByClassName(\'modal\')[0]);newAlert_on = false;" value="OK" /></div>'
    }
    document.body.appendChild(div);
    if(type && type == 'temp'){
        //document.getElementsByClassName('modal_new')[0].removeChild(document.getElementById('modal_btn_new'))
        window.setTimeout(function(){
            document.body.removeChild(document.getElementsByClassName('modal_new')[0]);
            newAlert_on = false;
        }, 3000)
    }    
    var elem = document.getElementsByClassName('modal_new')[0];
    var elemwidth = window.getComputedStyle(elem,null).getPropertyValue("width");
    elemwidth = parseInt(elemwidth.match(/\d*/ig));
    document.getElementsByClassName('modal_new')[0].style.left = (win.width - elemwidth - 40)/2+'px';
    var elemheight = window.getComputedStyle(elem,null).getPropertyValue("height");
    elemheight = parseInt(elemheight.match(/\d*/ig));
    document.getElementsByClassName('modal_new')[0].style.top = (win.height - elemheight - 40)/2+'px';
    if(!type || type != 'temp'){
        document.getElementById('alert_btn_ok').focus();
    }
}
function createHTMLTree(obj){
    var el = document.createElement(obj.tag);
    for(var key in obj.attrs) {
        if (obj.attrs.hasOwnProperty(key)){
            if(key!='html'){
                el.setAttribute(key, obj.attrs[key]);
            }else{
                el.innerHTML = obj.attrs[key];
            }
        }
    }
    if(typeof obj.child != 'undefined'){
        for(var i=0; i<obj.child.length; i++){
            el.appendChild(createHTMLTree(obj.child[i]));
        }
    }
    return el;
}
function media_getHourMinSec(time){
  var res = {};
  res.hour = Math.floor (time / 3600);
  time -= res.hour * 3600;
  res.minute = Math.floor (time / 60);
  res.second = time - res.minute * 60;
  res.minute = addZero(res.minute.toString());
  res.second = addZero(res.second.toString());
  
  return res;
}

function addZero(x){
  if (x.length < 2) {
    x = '0' + x;
  }
  return x;
}