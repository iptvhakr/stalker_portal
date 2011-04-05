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
};

TextInput.prototype.add = function(symbol){
    _debug('TextInput.add', symbol);
    
    this.input.value = this.input.value + '' + symbol;
};

TextInput.prototype.del = function(){
    _debug('TextInput.del');
    
    this.input.value = this.input.value.substr(0 ,this.input.value.length-1)
};

TextInput.prototype.reset = function(){
    _debug('TextInput.reset');
    
    this.input.value = '';
};

TextInput.prototype.set_active = function(){
    _debug('TextInput.set_active');

    this.input.setClass('active_input');
};

TextInput.prototype.set_passive = function(){
    _debug('TextInput.set_passive');

    this.input.setClass('passive_input');
};

TextInput.prototype.get_value = function(){
    _debug('TextInput.get_value');

    return this.input.value;
};

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
        }
    };
    
    this.map = this.map || [];

    this.map.options = this.map.options || [];

    this.onchange = function(){};
    this.onselect = function(){};

    this.default_checked_idx = -1;
    this.cur_idx = 0;
    
    this.build();
}

OptionInput.prototype = new BaseInput();

OptionInput.prototype.build = function(){
    _debug('OptionInput.build');

    var container = create_block_element('option_container', this.parent.container);

    var label = create_block_element('option_label', container);
    label.innerHTML = this.label;

    this.larr = create_block_element('larr', container);
    this.larr.innerHTML = '&lsaquo;';

    this.option = create_block_element('option_value', container);

    this.rarr = create_block_element('rarr', container);
    this.rarr.innerHTML = '&rsaquo;';

    var idx = this.map.options.getIdxByVal('selected', 1);

    if (idx !== null){
        this.default_checked_idx = this.cur_idx = idx;
    }

    this._set_option(this.cur_idx);
};

OptionInput.prototype.set_active = function(){
    _debug('OptionInput.set_passive', this.cur_idx);

    this.option.setAttribute('active', 'active');
};

OptionInput.prototype.set_passive = function(){
    _debug('OptionInput.set_active', this.cur_idx);

    this.option.setAttribute('active', '');
};

OptionInput.prototype.set_default = function(){
    _debug('OptionInput.set_default');

    if (this.default_checked_idx < 0){
        return;
    }

    this.cur_idx = this.default_checked_idx;

    this._set_option(this.cur_idx);
};

OptionInput.prototype.fill = function(data){
    _debug('OptionInput.fill', data);
    
    this.map = data;

    var idx = this.map.options.getIdxByVal('selected', 1);

    if (idx !== null){
        this.default_checked_idx = this.cur_idx = idx;
    }else{
        this.default_checked_idx = -1;
        this.cur_idx = 0;
    }

    this._set_option(this.cur_idx);
};

OptionInput.prototype._set_option = function(idx){
    _debug('OptionInput._set_option', idx);

    this.cur_idx = idx;

    window.clearTimeout(this.onchange_timer);

    var self = this;

    this.onchange_timer = window.setTimeout(function(){self.onchange()}, 500);

    _debug('this.cur_idx', this.cur_idx);

    if (this.map.options[this.cur_idx]){
        this.option.innerHTML = this.map.options[this.cur_idx].label || this.map.options[this.cur_idx].value || '...';
    }else{
        this.option.innerHTML = '...';
    }

    if (this.cur_idx == this.map.options.length - 1 || this.map.options.length == 0){
        this.rarr.style.visibility = 'hidden';
    }else{
        this.rarr.style.visibility = 'visible';
    }

    if (this.cur_idx == 0){
        this.larr.style.visibility = 'hidden';
    }else{
        this.larr.style.visibility = 'visible';
    }
};

OptionInput.prototype.get_value = function(){
    _debug('OptionInput.get_value');

    return this.map.options[this.cur_idx].value;
};

OptionInput.prototype.get_selected = function(){
    _debug('OptionInput.get_value');

    return this.map.options[this.cur_idx];
};

OptionInput.prototype.set_selected_by_value = function(value){
    _debug('OptionInput.set_selected_by_value');

    //if (this.default_checked_idx >= 0){
    //    return;
    //}

    var idx = this.map.options.getIdxByVal('value', value);

    if (idx !== null){
        return this._set_option(idx);
    }

    return false;
};

OptionInput.prototype.shift = function(dir){
    _debug('OptionInput.shift', dir);

    _debug('this.cur_idx', this.cur_idx);
    _debug('this.map.options.length', this.map.options.length);

    window.clearTimeout(this.onselect_timer);
    var self = this;
    this.onselect_timer = window.setTimeout(function(){self.onselect()}, 500);

    if (dir > 0){

        if (this.cur_idx < this.map.options.length - 1){
            this.cur_idx++;
        }else{
            return false;
        }
    }else{

        if (this.cur_idx > 0){
            this.cur_idx--;
        }else{
            return false;
        }
    }

    this._set_option(this.cur_idx);

    return true;
};

OptionInput.prototype.shift_page = function(dir){
    _debug('OptionInput.shift_page', dir);

    window.clearTimeout(this.onselect_timer);
    var self = this;
    this.onselect_timer = window.setTimeout(function(){self.onselect()}, 500);

    if (dir > 0){
        if (this.cur_idx < this.map.options.length - 11){
            this.cur_idx = this.cur_idx + 10;
        }else{
            this.cur_idx = this.map.options.length - 1;
        }
    }else{
        if (this.cur_idx > 10){
            this.cur_idx = this.cur_idx - 10;
        }else{
            this.cur_idx = 0;
        }
    }

    this._set_option(this.cur_idx);

    return true;
};

loader.next();