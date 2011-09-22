/**
 * Alias for document.getElementById.
 * @param {Number} id 
 * @return {*} DOMElement
 */
var $ = function(id){
    return document.getElementById(id);
};

/**
 * Debug function.
 */
function _debug(){
    if (debug){
        
        var text = '';
        
        for (var i = 0; i < arguments.length; i++){
            if (arguments[i] === null){
                text += ' null';
            }else if (typeof(arguments[i]) === "undefined"){
                text += ' undefined';
            }else if (arguments[i].message && arguments[i].name){
                text += ' '+arguments[i].name+' '+arguments[i].message;
            }else{
                text += ' '+arguments[i].toSource();
            }
        }
        
        try{
            gSTB.Debug(text);
        }catch(e){
            console&&console.log&&console.log(text);
        }
    }
}

function _log(action, param){
    
    var param = param || '';
    
    stb.load(
        {
            "type"   : "stb",
            "action" : "log",
            "real_action" : action,
            "param"  : param,
            "tmp_type" : stb.get_current_place()
        },
        
        function(result){
            
        }
    )
}

/**
 * Module loader.
 */

/*function load_module(module){
    
    _debug('load_module', module);
    
    try{
        
        var head = document.getElementsByTagName("head")[0];
        
        var _script = document.createElement('script');
        _script.type = "text/javascript";
        _script.src  = module + ".js";
        
        var _style = document.createElement('link');
        _style.type = "text/css";
        _style.rel = "stylesheet";
        _style.href = module + ".css";
        
        head.appendChild(_script);
        _debug('append', _script.src);
        head.appendChild(_style);
        _debug('append', _style.href);
    }catch(e){
        _debug(e);
    }
}*/

/**
 * DOM element creating functions
 */

function create_dom_element(type, class_name, parent){
            
    var dom_element = document.createElement(type);
    
    if (class_name){
        dom_element.addClass(class_name);
    }
    
    parent = parent || document.body;
    
    if (parent){
        parent.appendChild(dom_element);
    }
    
    return dom_element;
}

function create_inline_element(class_name, parent){
    
    return create_dom_element('span', class_name, parent);
}

function create_block_element(class_name, parent){
                
    return create_dom_element('div', class_name, parent);
}

/**
 * Custom prototype metods.
 */

HTMLElement.prototype.show = function(){
    try{
        this.style.display = 'block';
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.hide = function(){
    try{
        this.style.display = 'none';
    }catch(e){
        _debug(e);
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
        _debug(e);
    }
};

HTMLElement.prototype.moveX = function(to_x){
    try{
        this.style.left = parseInt(to_x)+'px';
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.moveY = function(to_y){
    try{
        this.style.top = parseInt(to_y)+'px';
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.offsetX = function(offset_x){
    try{
        this.style.left = parseInt(this.offsetLeft)+offset_x+'px';
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.offsetY = function(offset_y){
    try{
        this.style.top = parseInt(this.offsetTop)+offset_y+'px';
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.setClass = function(class_name){
    try{
        this.className = class_name;
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.delClass = function(){
    try{
        this.className = '';
    }catch(e){
        _debug(e);
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
        _debug(e);
    }
};

HTMLElement.prototype.removeClass = function(class_name){
    try{
        if (this.className.indexOf(class_name) >= 0){
            this.className = this.className.replace(eval('/('+class_name+')/g'), '').replace(/((\s)+)/g, ' ');
        }
    }catch(e){
        _debug(e);
    }
};

HTMLElement.prototype.replaceClass = function(from, to){
    try{
        if (this.className.indexOf(from) >= 0 ){
            this.className = this.className.replace(eval('/('+from+')/g'), to);
        }
    }catch(e){
        _debug(e);
    }
};

String.prototype.clearnl = function(){
    return this.replace(/(\n(\r)?)/g, '');
};

if (typeof Object.prototype.toSource != 'function'){
    
    Object.prototype.toSource = function(level) {
        
        var con = this.constructor;

        var l = level || 0;
        l++;

        //console.log('level', level);
        //console.log(l);
        //_debug('l', l);

        if (l > 4){
            //console.log('[Max level exceeded]');
            return '';
        }

        if(con == String) {
            //return '"' + this + '"';
            return this;
        } else if(con == Number) {
            return this;
        } else if(con == Array) {
            var res = '[';
            for(var i=0,len=this.length;i<len;i++) {
                if(i == len-1){
                    if(this[i] === null){
                        res += 'null]';
                    }else{
                        res += this[i].toSource(l) + ']';
                    }
                }else{
                    if(this[i] === null){
                        res += 'null, ';
                    }else{
                        res += this[i].toSource(l) + ', ';
                    }
                }
            }
            return res;
        } else if(con == RegExp) {
            return this;
        } else if(con == Function) {
            return "[function]";
        } else if(con == Object) {
            var res = '{';
            var i=0;
            for(var j in this) {
                if (this.hasOwnProperty(j)){
                    if(j != 'toSource') {
                        if(i == 0) {
                            if (this[j] === null){
                                res += j + ': null';
                            }else if (typeof(this[j]) == 'undefined'){
                                res += ', ' + j + ': undefined';
                            }else{
                                res += j + ':' + this[j].toSource(l);
                            }
                        } else {
                            if (this[j] === null){
                                res += ', ' + j + ': null';
                            }else if (typeof(this[j]) == 'undefined'){
                                res += ', ' + j + ': undefined';
                            }else{
                                res += ', ' + j + ':' + this[j].toSource(l);
                            }
                        }
                        i++;
                    }
                }
            }
            res += '}';
            if(arguments.length) {
                return res;
            } else {
                return '(' + res + ')';
            }
        }else if(con == Boolean){
            return this;
        }
    }
    
}

Object.prototype.clone = function() {  
    
    var newObj = (this instanceof Array) ? [] : {};  
    
    for (var i in this) {
        
        if (this.hasOwnProperty(i)){
            //_debug(i);
            
            if (i == 'clone') continue;  
            
            if (this[i] && typeof this[i] == "object") {  
                newObj[i] = this[i].clone();  
            }else{
                newObj[i] = this[i];
            }
        }
    }
    return newObj;
};

Object.prototype.addCustomEventListener = function(type, listener){
    this._listeners = this._listeners || {};

    if (!this._listeners.hasOwnProperty(type)){
        this._listeners[type] = [];
    }
    
    this._listeners[type].push(listener);
};

Object.prototype.triggerCustomEventListener = function(type, param){

    var self = this;

    if (this._listeners && this._listeners.hasOwnProperty(type)){
        for (var i = 0; i < this._listeners[type].length; i++){
            try{
                window.setTimeout(function(){self._listeners[type][i](param)}, 10);
            }catch(e){
                _debug(e);
            }
        }
    }

    return false;
};

Array.prototype.getIdxById = function(id){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty('id')){
            if (this[i].id == id){
                return i;
            }
        }
    }
    return null;
};

Array.prototype.getIdxByVal = function(what, eq){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty(what)){
            if (this[i][what] == eq){
                return i;
            }
        }
    }
    return null;
};

Array.prototype.getIdxByNumber = function(number){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty('number')){
            if (this[i].number == number){
                return i;
            }
        }
    }
    return null;
};

Array.prototype.inArray = function (value){
    for (var i=0; i < this.length; i++){
        if (this[i] === value){
            return true;
        }
    }
    return false;
};

Math.__proto__.isEven = function(x){
    return !(x % 2);
};

Math.__proto__.isOdd = function(x){
    return !Math.isEven(x);
};

function empty(val){
    
    var type = typeof(val);
    
    if (type == 'string' && val == ''){
        return true;
    }else if (type == 'undefined'){
        return true;
    }else{
        if (!val){
            return true;
        }else if (type == 'object'){
            for(var p in val){
                if (val.hasOwnProperty(p)){
                    return false;
                }
            }
            
            return true;
        }
    }
    
    return false;
}

function get_params(){
    var get = new String(window.location);
    var x = get.indexOf('?');
    if (x!=-1){
        var l = get.length;
        get = get.substr(x+1, l-x);
        l = get.split('&');
        x = 0;
        for(var i in l){
            if (l.hasOwnProperty(i)){
                get = l[i].split('=');
                _GET[get[0]] = get[1];
                x++;
            }
        }
    }
}

function get_word(alias){
    return word[alias];
}

String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
        return typeof args[number] != 'undefined' ? args[number]: '{' + number + '}';
    });
};

/**
 * @example
 * var obj ={
 *     'tag':'div',
 *     'attrs':{
 *         'id':'id',
 *         'class': 'new_class',
 *         'style':'"width:50px'
 *     },
 *     'child':[
 *         {
 *             'tag':'div',
 *             'attrs':{
 *                 'id':'id',
 *                 'class': 'new_class',
 *                 'style':'"width:50px'
 *             },
 *             'child':[
 *
 *             ]
 *         },
 *         {
 *             'tag':'div',
 *
 *             'attrs':{
 *                 'id':'id',
 *                 'class': 'new_class',
 *                 'style':'"width:50px'
 *             },
 *             'child':[
 *
 *             ]
 *         }
 *    ]
 * };
 * createHTMLTree(obj);
 *
 * @param obj
 */
function createHTMLTree(obj){
    var el = document.createElement(obj.tag);
    for(var key in obj.attrs) {
        if (obj.attrs.hasOwnProperty(key)){
            el.setAttribute(key, obj.attrs[key]);
        }
    }
    if(typeof obj.child != 'undefined'){
        for(var i=0; i<obj.child.length; i++){
            el.appendChild(createHTMLTree(obj.child[i]));
        }
    }
    return el;
}