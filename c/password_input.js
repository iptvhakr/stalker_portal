/**
 * Password input box.
 * @constructor
 */

function password_input(options){
    
    this.on = false;
    this.dom_obj = {};
    this.input = {};
    this.input_value = '';
    this.pattern = '';
    this.callback;
    this.parent = {};
    
    if (options && options.hasOwnProperty('pattern')){
        this.pattern = options.pattern;
    }else{
        if (stb && stb.user && (stb.user.parent_password || stb.user.parent_password == '')){
            this.pattern = stb.user.parent_password;
        }
    }
    
    if (options && options.hasOwnProperty('parent')){
        this.parent = options.parent;
    }
    
    this.init();
}

password_input.prototype.init = function(){
    
    this.dom_obj   = create_block_element('password');
    this.error_ico = create_block_element('password_error_ico', this.dom_obj);
    this.input     = create_block_element('password_input', this.dom_obj);
    
    this.error_ico.hide();
    
    this.hide();
}

password_input.prototype.show = function(){
    _debug('password_input.show');
    
    this.dom_obj.show();
    this.on = true;
    
    _debug('this.parent.on before', this.parent.on);
    if (this.parent && this.parent.on){
        this.parent.on = false;
    }
    _debug('this.parent.on after', this.parent.on);
}

password_input.prototype.hide = function(){
    _debug('password_input.hide');
    
    this.input.innerHTML = '';
    this.input_value = '';
    this.hide_error();
    
    this.dom_obj.hide();
    this.on = false;
    
    if (this.parent){
        this.parent.on = true;
    }
}

password_input.prototype.check = function(){
    _debug('password_input.check');
    
    if (this.input_value == this.pattern){
        this.hide();
        this.callback();
    }else{
        this.show_error();
    }
}

password_input.prototype.add_number = function(num){
    _debug('password_input.add_number', num);
    
    this.input.innerHTML = this.input.innerHTML + '*';
    
    this.input_value = this.input_value + '' + num;
    
    this.hide_error();
}

password_input.prototype.del_number = function(){
    _debug('password_input.del_number');
    
    this.input.innerHTML = this.input.innerHTML.substr(0, this.input.innerHTML.length - 1);
}

password_input.prototype.show_error = function(){
    _debug('password_input.show_error');
    
    this.input.innerHTML = '';
    this.input_value = '';
    this.error_ico.show();
}

password_input.prototype.hide_error = function(){
    _debug('password_input.hide_error');
    this.error_ico.hide();
}

password_input.prototype.bind = function(){
    _debug('password_input.bind');
    
    this.check.bind(key.OK, this);
    
    this.add_number.bind(key.NUM1, this, 1);
    this.add_number.bind(key.NUM2, this, 2);
    this.add_number.bind(key.NUM3, this, 3);
    this.add_number.bind(key.NUM4, this, 4);
    this.add_number.bind(key.NUM5, this, 5);
    this.add_number.bind(key.NUM6, this, 6);
    this.add_number.bind(key.NUM7, this, 7);
    this.add_number.bind(key.NUM8, this, 8);
    this.add_number.bind(key.NUM9, this, 9);
    this.add_number.bind(key.NUM0, this, 0);
    
    this.del_number.bind(key.BACK, this);
    
    this.hide.bind(key.EXIT, this);
}

loader.next();
