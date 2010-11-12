/**
 * Base input constructor.
 * @constructor
 */

function BaseInput(){
    
    this.label = '';
    this.name  = '';
    this.default_val = '';
    this.parent = parent;
    //this.type = 'text';
    
    /*if (options){
        for (prop in options){
            if (options.hasOwnProperty(prop)){
                this[prop] = options[prop]
            }
        }
    }*/
}

/**
 * Text input constructor.
 * @constructor
 */

function TextInput(parent, options){
    
    this.type = 'text';
    
    this.parent = parent;
    
    this.options = options;
    
    if (options){
        for (prop in options){
            if (options.hasOwnProperty(prop)){
                this[prop] = options[prop]
            }
        }
    }
    
    this.input = {};
    
    this.build();
}

TextInput.prototype = new BaseInput();

TextInput.prototype.build = function(){
    _debug('TextInput.build');
    
    var dl = document.createElement('dl');
    
    var dt = document.createElement('dt');
    dt.innerHTML = this.label;
    
    var dd = document.createElement('dd');
    
    this.input = document.createElement('input');
    this.input.type  = this.type;
    this.input.value = this.default_val;
    this.input.disabled = "disabled";
    
    dd.appendChild(this.input);
    
    dl.appendChild(dt);
    dl.appendChild(dd);
    
    this.parent.container.appendChild(dl);
}

TextInput.prototype.add = function(symbol){
    _debug('TextInput.add', symbol);
    
    this.input.value = this.input.value + '' + symbol;
}

TextInput.prototype.del = function(){
    _debug('TextInput.del');
    
    this.input.value = this.input.value.substr(0 ,this.input.value.length-1)
}

TextInput.prototype.reset = function(){
    _debug('TextInput.reset')
    
    this.input.value = '';
}
/* END Text input */

/**
 * Option input constructor.
 * @constructor
 */

function OptionInput(parent, options){
    
    this.parent = parent;
    
    this.options = options;
    
    if (options){
        for (prop in options){
            if (options.hasOwnProperty(prop)){
                this[prop] = options[prop]
            }
        }
    }
    
    var self = this;
    
    this.input = {
        "parent"   : self,
        "value"    : '',
        "setClass" : function(class_name){
            if (class_name == 'active_input'){
                this.parent.set_active();
            }else{
                this.parent.set_passive();
            }
        },
    }
    
    this.map = this.map || [];
    
    this.default_checked_idx = 0;
    this.checked_idx = 0;
    this.cur_idx = 0;
    
    this.build();
}

OptionInput.prototype = new BaseInput();

OptionInput.prototype.build = function(){
    _debug('OptionInput.build');
    
    var dl = document.createElement('dl');
    
    var dt = document.createElement('dt');
    dt.innerHTML = this.label;
    
    var dd = document.createElement('dd');
    
    /*this.input = document.createElement('input');
    this.input.type  = this.type;
    this.input.value = this.default_val;
    this.input.disabled = "disabled";
    
    dd.appendChild(this.input);*/
    
    if (this.map && this.map.options){
        
        for (var i=0; i<this.map.options.length; i++){
            
            var opt_dl = document.createElement('dl');
            if(i == 0){
                opt_dl.style.clear = 'none';
            }
            
            var opt_dt = document.createElement('dt');
            
            var btn = this.map.options[i].border = create_block_element('passive_input', opt_dt);
            btn.id = 'radio_btn';
            
            var bullet = this.map.options[i].bullet = create_block_element('checked', btn);
            
            if(!this.map.options[i].selected){
                bullet.hide();
            }else{
                this.default_checked_idx = this.checked_idx = i;
                this.input.value = this.map.options[i].value || this.map.options[i].label;
            }
            
            var opt_dd = document.createElement('dt');
            
            opt_dd.innerHTML = this.map.options[i].label;
            
            opt_dl.appendChild(opt_dt);
            opt_dl.appendChild(opt_dd);
            dd.appendChild(opt_dl);
        }
    }
    
    dl.appendChild(dt);
    dl.appendChild(dd);
    
    this.parent.container.appendChild(dl);
}

OptionInput.prototype.set_passive = function(){
    _debug('OptionInput.set_passive', this.cur_idx);
    
    this.map.options[this.cur_idx].border.setClass('passive_input');
}

OptionInput.prototype.set_active = function(){
    _debug('OptionInput.set_active', this.cur_idx);
    
    this.map.options[this.cur_idx].border.setClass('active_input');
}

OptionInput.prototype.set_unchecked = function(){
    _debug('OptionInput.set_unchecked', this.checked_idx);
    
    this.map.options[this.checked_idx].bullet.hide();
}

OptionInput.prototype.set_checked = function(){
    _debug('OptionInput.set_checked');
    
    this.set_unchecked();
    
    this.checked_idx = this.cur_idx;
    
    this.input.value = this.map.options[this.checked_idx].value || this.map.options[this.checked_idx].label;
    
    this.map.options[this.checked_idx].bullet.show();
}

OptionInput.prototype.set_default = function(){
    _debug('OptionInput.set_default');
    
    this.set_passive();
    
    this.cur_idx = this.default_checked_idx;
    
    this.set_checked();
    
    this.set_active();
}

OptionInput.prototype.shift = function(dir){
    _debug('OptionInput.shift', dir);
    
    _debug('this.cur_idx', this.cur_idx);
    _debug('this.map.options.length', this.map.options.length);
    
    if (dir > 0){
        
        if (this.cur_idx < this.map.options.length - 1){
            this.set_passive();
            this.cur_idx++;
            this.set_active();
        }else{
            
            return false;
        }
    }else{
        
        if (this.cur_idx > 0){
            this.set_passive();
            this.cur_idx--;
            this.set_active();
        }else{
            return false;
        }
    }
    
    return true;
}

loader.next();