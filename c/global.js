/**
 * Alias for document.getElementById.
 * @param {Number} id 
 * @return {*} DOMElement
 */
var $ = function(id){
    return document.getElementById(id);
}

/**
 * Debug function.
 */
function _debug(){
    if (debug){
        
        var text = '';
        for (var i = 0; i < arguments.length; i++){
            if (arguments[i].message && arguments[i].name){
                text += ' '+arguments[i];
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

/**
 * Custom prototype metods
 */

HTMLElement.prototype.show = function(){
    try{
        this.style.display = 'block';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.hide = function(){
    try{
        this.style.display = 'none';
    }catch(e){
        _debug(e);
    }
}

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
}

HTMLElement.prototype.moveX = function(to_x){
    try{
        this.style.left = parseInt(to_x)+'px';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.moveY = function(to_y){
    try{
        this.style.top = parseInt(to_y)+'px';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.offsetX = function(offset_x){
    try{
        this.style.left = parseInt(this.offsetLeft)+offset_x+'px';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.offsetY = function(offset_y){
    try{
        this.style.top = parseInt(this.offsetTop)+offset_y+'px';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.setClass = function(class_name){
    try{
        this.className = class_name;
    }catch(e){
        _debug(e);
    }
}

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
}

HTMLElement.prototype.removeClass = function(class_name){
    try{
        if (this.className.indexOf(class_name) >= 0){
            this.className.replace(/(class_name)/g, '').replace(/((\s)+)/g, ' ');
        }
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.replaceClass = function(from, to){
    try{
        if (this.className.indexOf(from) >= 0 ){
            this.className.replace(/(from)/g, to);
        }
    }catch(e){
        _debug(e);
    }
}

String.prototype.clearnl = function(){
    return this.replace(/(\n(\r)?)/g, '');
}

if (typeof Object.prototype.toSource != 'function'){
    
    Object.prototype.toSource = function() {
        var con = this.constructor;
        if(con == String) {
            //return '"' + this + '"';
            return this;
        } else if(con == Number) {
            return this;
        } else if(con == Array) {
            var res = '[';
            for(var i=0,len=this.length;i<len;i++) {
                if(i == len-1)
                    res += this[i].toSource() + ']';
                else
                    res += this[i].toSource() + ', ';
            }
            return res;
        } else if(con == RegExp) {
            return this;
        } else if(con == Object) {
            var res = '{';
            var i=0;
            for(var j in this) {
                if(j != 'toSource') {
                    if(i == 0) {
                        res += j + ':' + this[j].toSource(1);
                    } else {
                        res += ', ' + j + ':' + this[j].toSource(1);
                    }
                    i++;
                }
            }
            res += '}';
            if(arguments.length) {
                return res;
            } else {
                return '(' + res + ')';
            }
        }
    }
    
}

Array.prototype.getIdxById = function(id){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty('id')){
            if (this[i].id == id){
                return i;
            }
        }
    }
    return null;
}

Array.prototype.getIdxByVal = function(what, eq){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty(what)){
            if (this[i][what] == eq){
                return i;
            }
        }
    }
    return null;
}

Array.prototype.getIdxByNumber = function(number){
    for (var i=0; i<this.length; i++){
        if (this[i].hasOwnProperty('number')){
            if (this[i].number == number){
                return i;
            }
        }
    }
    return null;
}

Array.prototype.inArray = function (value){
    for (var i=0; i < this.length; i++){
        if (this[i] === value){
            return true;
        }
    }
    return false;
};