function $(id){return document.getElementById(id);}
function random(m,n){m=parseInt(m);if(!n){n=0;};return Math.floor(Math.random()*(n-m+1))+m;}
//Array.prototype.shuffle=function(b){var i=this.length,j,t;while(i){j=Math.floor((i--)*Math.random());t=b&&typeof this[i].shuffle!=='undefined'?this[i].shuffle():this[i];this[i]=this[j];this[j]=t;} return this;};
function log(line){if(modes.debug){gSTB.Debug(line)}};
function string_replace (search, replace, subject, count){var i=0,j=0,temp='',repl='',sl=0,fl=0,f=[].concat(search),r=[].concat(replace),s=subject,ra=r instanceof Array,sa=s instanceof Array;s=[].concat(s);if(count){this.window[count]=0;}for(i=0,sl=s.length;i<sl;i++){if(s[i]===''){continue;}for(j=0,fl=f.length;j<fl;j++){temp=s[i]+'';repl=ra?(r[j]!==undefined?r[j]:''):r[0];s[i]=(temp).split(f[j]).join(repl);if(count&&s[i]!==temp){this.window[count]+=(temp.length-s[i].length)/f[j].length;}}}return sa?s:s[0];}
function is_numeric(mixed_var){return (typeof(mixed_var)==='number'||typeof(mixed_var)==='string')&&mixed_var!==''&&!isNaN(mixed_var);}
function empty(mixed_var){if(mixed_var===""||mixed_var===null||mixed_var===false||typeof mixed_var==='undefined'||typeof mixed_var==='NaN'){return true;}if(typeof mixed_var=='object'){for(var key in mixed_var){log(key,false);return false;}return true;}return false;}
var trim={"left":function(str){try{returnstr.replace(/^\s+/,'');}catch(e){return str;}},"right":function(str){try{returnstr.replace(/\s+$/,'');}catch(e){return str;}},"all":function(str){returnthis.right(this.left(str));},"spaces":function(str){try{return str.replace(/\s{2,}/g,' ');} catch(e){return str;}}};
function setEnvironmentValue(name,value){log('set environmentValue {'+name+'} : {'+value+'}');stb.RDir('setenv '+name+' '+value);}
function getEnvironmentValue(name){var val=stb.RDir('getenv '+name);log('get environmentValue | {'+name+'} : {'+val+'}');return val;}
function _set_focused_param(){
    for(var i=0;i<prop_focus_emements.length;i++) {
        $(prop_focus_emements[i]).focused=false;
        $(prop_focus_emements[i]).onfocus = function(){
            this.focused=true;
        };
        $(prop_focus_emements[i]).onblur = function(){
            this.focused=false;
        };
        $(prop_focus_emements[i]).hasFocus = function() {
            if(this.disabled==true) return false;
            else return this.focused;
        };
    }
}
HTMLElement.prototype.show = function(){
    try{
    	switch(this.tagName.toLowerCase()){
    		case "span":case "strong":case "b":case "em":case "i":    			
    			this.style.display = 'inline';
			break;
			default:
				this.style.display = 'block';
			break;
    	}        
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.hide = function(){
    try{
        this.style.display = 'none';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.html = function(val){
    try{
        if(!val){
        	return this.innerHTML;
        } else {
        	this.innerHTML = val;
        }
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.isHidden = function(){
    try{
        if (this.style.display == 'none'){
            return true;
        }else{
            return false;
        }
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.moveX = function(to_x){
    try{
        this.style.left = parseInt(to_x)+'px';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.moveY = function(to_y){
    try{
        this.style.top = parseInt(to_y)+'px';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.offsetX = function(offset_x){
    try{
        this.style.left = parseInt(this.offsetLeft)+offset_x+'px';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.offsetY = function(offset_y){
    try{
        this.style.top = parseInt(this.offsetTop)+offset_y+'px';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.setClass = function(class_name){
    try{
        this.className = class_name;
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.delClass = function(){
    try{
        this.className = '';
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.addClass = function(class_name){
    try{
        if (!this.className){
            this.setClass(class_name);
        }else{
            var new_class_name = this.className;
            new_class_name += " ";
            new_class_name += class_name;
            this.setClass(new_class_name);
        }
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.removeClass = function(class_name){
    try{
        if (this.className.indexOf(class_name) >= 0){
            this.className = this.className.replace(eval('/('+class_name+')/g'), '').replace(/((\s)+)/g, ' ');
        }
    }catch(e){
        log(e);
    }
};
HTMLElement.prototype.replaceClass = function(from, to){
    try{
        if (this.className.indexOf(from) >= 0 ){
            this.className = this.className.replace(eval('/('+from+')/g'), to);
        }
    }catch(e){
        log(e);
    }
};
String.prototype.clearnl = function(){
    return this.replace(/(\n(\r)?)/g, '');
};
Array.prototype.inArray = function (value){
    for (var i=0; i < this.length; i++){
        if (this[i] === value){
            return true;
        }
    }
    return false;
};
/*
Math.__proto__.isEven = function(x){
    return !(x % 2);
};
*/
Math.__proto__.isOdd = function(x){
    return !Math.isEven(x);
};
function get_params(){
    var get = new String(window.location);
    var x = get.indexOf('?');
    if (x!=-1){
        var l = get.length;
        get = get.substr(x+1, l-x);
        l = get.split('&');
        x = 0;
        for(i in l){
            if (l.hasOwnProperty(i)){
                get = l[i].split('=');
                _GET[get[0]] = get[1];
                x++;
            }
        }
    }
}

function setCookie (name, value, expires, path, domain, secure) {
    gSTB.EnableSetCookieFrom(domain,true);
    document.cookie = name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires : "") +
      ((path) ? "; path=" + path : "") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
}
function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset);
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

function getIso639LangCode(langArr){
  var code = "";
  for (var i=0; i<langArr.length; i++) {
    if (langArr[i]) {
      code = langArr[i];
      break;
    }
  }
  return code;
}

function getLanguageNameByCode(code){
  if (code) {
    var lang_codes = [];
    for (var i=0; i<iso639.length; i++) {
      ref_codes = iso639[i].code;
      for (var j=0; j<ref_codes.length; j++) {
        if (ref_codes[j] == code[0].toLowerCase()) {
          code    = [];
          code[0] = iso639[i].name;
          code[1] = i;
          return code;
        }
      }
    }
  }
  return null;
}