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

loader.next();