/**
 *
 *
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
    if (!this.style.left){
        this.style.left = 0+'px';
    };
    
    try{
        this.style.left = parseInt(this.style.left)+offset_x+'px';
    }catch(e){
        _debug(e);
    }
}

HTMLElement.prototype.offsetY = function(offset_y){
    if (!this.style.top){
        this.style.top = 0+'px';
    };
    
    try{
        this.style.top = parseInt(this.style.top)+offset_y+'px';
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

String.prototype.clearnl = function(){
    return this.replace(/(\n(\r)?)/g, '');
}
