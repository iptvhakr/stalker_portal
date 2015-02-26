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
            }else if (arguments[i] instanceof Date){
                text += ' '+arguments[i].toString();
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

function _log(action, param, content_id){

    param = param || '';
    content_id = content_id || 0;

    if (typeof(param) == 'object'){
        var add_data = param;
    }else{
        param = encodeURIComponent(param);
    }


    var data = {
        "type"   : "stb",
        "action" : "log",
        "real_action" : action,
        "param"  : param,
        "content_id" : content_id,
        "tmp_type" : stb.get_current_place()
    };

    if (_log.hasOwnProperty('last_data')
        && _log.last_data.type == data.type
        && _log.last_data.action == data.action
        && _log.last_data.real_action == data.real_action
        && _log.last_data.param == data.param
        && _log.last_data.tmp_type == data.tmp_type
        ){
        return;
    }

    _log.last_data = data;

    for (var prop in add_data){
        if (add_data.hasOwnProperty(prop)){
            data[prop] = add_data[prop];
        }
    }

    stb.load(
        data,

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
        switch(this.tagName.toLowerCase()){
            case "span": case "strong": case "b": case "em": case "i":
                this.style.display = 'inline';
            break;
            default:
                this.style.display = 'block';
        }
    }catch(e){
        _debug(e);
    }
    return this;
};

HTMLElement.prototype.hide = function(){
    try{
        this.style.display = 'none';
    }catch(e){
        _debug(e);
    }
    return this;
};

HTMLElement.prototype.isHidden = function(){
    try{
        return this.style.display == 'none';
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

    return this;
};

HTMLElement.prototype.delClass = function(){
    try{
        this.className = '';
    }catch(e){
        _debug(e);
    }

    return this;
};

HTMLElement.prototype.haveClass = function(class_name){
    var classes = this.className.replace(/\s+/g, ' ').split(' ');

    return classes.indexOf(class_name) != -1;
};

HTMLElement.prototype.addClass = function(class_name){
    try{
        if (!this.className){
            this.setClass(class_name);
        }else{
            var classes = this.className.replace(/\s+/g, ' ').split(' ');

            if (classes.indexOf(class_name) == -1){
                classes.push(class_name);
            }

            this.setClass(classes.join(' '));
        }
    }catch(e){
        _debug(e);
    }

    return this;
};

HTMLElement.prototype.removeClass = function(class_name){
    try{
        if (this.className.indexOf(class_name) >= 0){
            this.className = this.className.replace(eval('/('+class_name+')/g'), '').replace(/((\s)+)/g, ' ');
        }
    }catch(e){
        _debug(e);
    }

    return this;
};

HTMLElement.prototype.replaceClass = function(from, to){
    try{
        if (this.className.indexOf(from) >= 0 ){
            this.className = this.className.replace(eval('/('+from+')/g'), to);
        }
    }catch(e){
        _debug(e);
    }

    return this;
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
                    }else if (this[i] === undefined){
                        res += 'undefined';
                    }else{
                        //console.log(this[i]);
                        res += this[i].toSource(l) + ']';
                    }
                }else{
                    if(this[i] === null){
                        res += 'null, ';
                    }else if (this[i] === undefined){
                        res += 'undefined, ';
                    }else{
                        res += this[i].toSource(l) + ', ';
                    }
                }
            }
            return res+']';
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

    var result = true;

    if (this._listeners && this._listeners.hasOwnProperty(type)){
        for (var i = 0; i < this._listeners[type].length; i++){
            try{
                result = !!this._listeners[type][i](param) && result;
            }catch(e){
                _debug(e)
            }
        }
    }

    return result;
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
    return word[alias] || alias;
}

String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
        return typeof args[number] != 'undefined' ? args[number]: '{' + number + '}';
    });
};

String.prototype.toTranslit = function(){
    
    var map = {
        'а' : 'a',
        'б' : 'b',
        'в' : 'v',
        'г' : 'g',
        'д' : 'd',
        'е' : 'e',
        'ж' : 'g',
        'з' : 'z',
        'и' : 'i',
        'й' : 'y',
        'к' : 'k',
        'л' : 'l',
        'м' : 'm',
        'н' : 'n',
        'о' : 'o',
        'п' : 'p',
        'р' : 'r',
        'с' : 's',
        'т' : 't',
        'у' : 'u',
        'ф' : 'f',
        'ы' : 'i',
        'э' : 'e',
        'А' : 'A',
        'Б' : 'B',
        'В' : 'V',
        'Г' : 'G',
        'Д' : 'D',
        'Е' : 'E',
        'Ж' : 'G',
        'З' : 'Z',
        'И' : 'I',
        'Й' : 'Y',
        'К' : 'K',
        'Л' : 'L',
        'М' : 'M',
        'Н' : 'N',
        'О' : 'O',
        'П' : 'P',
        'Р' : 'R',
        'С' : 'S',
        'Т' : 'T',
        'У' : 'U',
        'Ф' : 'F',
        'Ы' : 'I',
        'Э' : 'E',
        'ё':"yo", 'х':"h", 'ц':"ts", 'ч':"ch", 'ш':"sh",
        'щ':"shch", 'ъ':'', 'ь':'', 'ю':"yu", 'я':"ya",
        'Ё':"Yo", 'Х':"H", 'Ц':"Ts", 'Ч':"Ch", 'Ш':"Sh",
        'Щ':"Shch", 'Ъ':'', 'Ь':'', 'Ю':"Yu", 'Я':"Ya",
        ' ':"_", '!':"", '?':"", ',':"", '.':"", '"':"",
        '\'':"", '\\':"", '/':"", ';':"", ':':"", '«':"", '»':"", '`':"", '-' : "-", '—' : "-"
    };

    var arr = this.split('').map(function(letter){
        if (map.hasOwnProperty(letter)){
            letter = map[letter];
        }

        return letter;
    });

    return arr.join('').replace(/[^a-z0-9_-]/ig, '');
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

function HTMLDefinitionList(class_name, parent){

    this._class   = class_name;
    this._parent  = parent;
    this._rows    = [];
    this._separator = '';
    
    this._init();
}

HTMLDefinitionList.prototype._init = function(){

    this.dom_obj = document.createElement('dl');
    this.dom_obj.addClass('definition_list');
    this.dom_obj.addClass(this._class);
    this._parent.appendChild(this.dom_obj);
};

HTMLDefinitionList.prototype.addRow = function(title, value){

    var dt = document.createElement('dt');
    dt.innerHTML = title + this._separator;

    var dd = document.createElement('dd');
    dd.innerHTML = value;

    this._rows.push({"title" : title, "value" : value, "title_dom_obj" : dt, "value_dom_obj" : dd});

    this.dom_obj.appendChild(dt);
    this.dom_obj.appendChild(dd);
};

HTMLDefinitionList.prototype.setSeparator = function(separator){

    this._separator = separator;
};

HTMLDefinitionList.prototype.getRowByTitle = function(title){

    var idx = this._rows.getIdxByVal('title', title);

    if (idx === null){
        return undefined;
    }

    return this._rows[idx];
};

HTMLDefinitionList.prototype.updateValueByTitle = function(title, value){

    var row = this.getRowByTitle(title);

    if (!row){
        return false;
    }

    row.value = value;
    row.value_dom_obj.innerHTML = value;
};