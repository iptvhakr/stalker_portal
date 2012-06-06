/**
 * Modal form constructor
 *
 * @constructor
 */
function ModalForm(options){

    this.on = false;
    
    this._items = [];
    this._cur_item_idx = 0;
    this._title  = "";
    this._status = "";
    this._text = "";

    this.options = options;

    if (options){
        for (var prop in options){
            if (options.hasOwnProperty(prop)){
                this["_"+prop] = options[prop];
            }
        }
    }

    this._init();
}

ModalForm.prototype._init = function(){
    this._dom_obj        = create_block_element("modal_form");
    this._title_dom_obj  = create_block_element("title", this._dom_obj);
    this._title_dom_obj.innerHTML = this._title;
    this._status_dom_obj = create_block_element("status", this._dom_obj);

    if (this._text){
        this._text_dom_obj = create_block_element("text", this._dom_obj);
        this._text_dom_obj.innerHTML = this._text;
    }

    this._items_block    = create_block_element("", this._dom_obj);
    this._buttons_block  = create_block_element("buttons", this._dom_obj);
    this.hide();
    //this._bind.call(this);
    this._bind();
};

ModalForm.prototype.show = function(text){

    if (text){
        this.setText(text);
    }

    this._dom_obj.show();
    this.on = true;
    this._items[0].focus();
};

ModalForm.prototype.hide = function(){
    this._items[this._cur_item_idx] && this._items[this._cur_item_idx].blur && this._items[this._cur_item_idx].blur();
    this._dom_obj.hide();
    this.on = false;
    this.reset();
    this.triggerCustomEventListener("hide", this);
};

ModalForm.prototype.reset = function(){
    this._items.map(function(item){
        item && item.reset && item.reset();
    });
    this._cur_item_idx = 0;
};

ModalForm.prototype.destroy = function(){
    this._dom_obj.parentNode.removeChild(this._dom_obj);
};

ModalForm.prototype.addItem = function(item){
    this._items.push(item);

    if (item instanceof ModalFormButton){
        this._buttons_block.appendChild(item.getDomElement.call(item));
    }else{
        this._items_block.appendChild(item.getDomElement.call(item));
    }
};

ModalForm.prototype._setActiveItem = function(){
    this._items[this._cur_item_idx] && this._items[this._cur_item_idx].focus && this._items[this._cur_item_idx].focus();
};

ModalForm.prototype._setPassiveItem = function(){
    this._items[this._cur_item_idx] && this._items[this._cur_item_idx].blur && this._items[this._cur_item_idx].blur();
};

ModalForm.prototype._changeFocus = function(dir){

    this._setPassiveItem();

    if (dir > 0){
        if (this._cur_item_idx < this._items.length - 1){
            this._cur_item_idx++;
        }else{
            this._cur_item_idx = 0;
        }
    }else{
        if (this._cur_item_idx > 0){
            this._cur_item_idx--;
        }else{
            this._cur_item_idx = this._items.length - 1;
        }
    }

    this._setActiveItem();
};

ModalForm.prototype.getItemByName = function(name){

    var search = this._items.filter(function(item){
        if (item.getName() == name){
            return true;
        }
    });

    if (!search){
        return null;
    }

    return search[0]; 
};

ModalForm.prototype.setStatus = function(status){

    this._status = status;
    this._status_dom_obj.innerHTML = status;
};

ModalForm.prototype.resetStatus = function(){

    _debug("this._status", this._status);

    if (this._status != ""){
        this._status_dom_obj.innerHTML = "";
        this._status = "";
    }
};

ModalForm.prototype._bind = function(){

    this._changeFocus.bind(key.DOWN, this,  1);
    this._changeFocus.bind(key.UP,   this, -1);

    (function(){
        if (this._items[this._cur_item_idx] instanceof ModalFormButton){
            this._changeFocus(-1);
        }
    }).bind(key.LEFT, this);

    (function(){
        if (this._items[this._cur_item_idx] instanceof ModalFormButton){
            this._changeFocus(1);
        }
    }).bind(key.RIGHT, this);

    (function(){
        if (this._items[this._cur_item_idx] instanceof ModalFormInput){
            stb.ShowVirtualKeyboard();
        }else if (this._items[this._cur_item_idx] instanceof ModalFormButton){
            this._items[this._cur_item_idx].action();
        }
    }).bind(key.OK, this).bind(key.KEYBOARD, this);

    (function(){}).bind(key.TV, this);
};

ModalForm.prototype.enableOnExitClose = function(){
    
    (function(){
        this.hide();
    }).bind(key.EXIT, this);
};

ModalForm.prototype.getTitleDomObj = function(){
    return this._title_dom_obj;
};

ModalForm.prototype.getButtonsBlockDomObj = function(){
    return this._buttons_block;
};

ModalForm.prototype.getTextDomObj = function(){
    return this._text_dom_obj;
};

ModalForm.prototype.setText = function(text){
    this._text_dom_obj.innerHTML = text;
};
/* END ModalForm */

/**
 * @constructor
 */
function ModalFormItem(){}

ModalFormItem.prototype.focus = function(){
    this._input_dom_odj && this._input_dom_odj.focus  && this._input_dom_odj.focus();
};

ModalFormItem.prototype.blur = function(){
    this._input_dom_odj && this._input_dom_odj.blur  && this._input_dom_odj.blur();
};

ModalFormItem.prototype.getDomElement = function(){
    return this._item;
};

ModalFormItem.prototype.getName = function(){};

/* END ModalFormItem */

/**
 * Input element
 *
 * @constructor
 * @param {Object} options
 */
function ModalFormInput(options){

    this._name  = "";
    this._type  = "text";
    this._label = "";
    this._value = "";
    this._onchange =  function(){};

    this.options = options;

    if (options){
        for (var prop in options){
            if (options.hasOwnProperty(prop)){
                this["_"+prop] = options[prop];
            }
        }
    }

    this._init();
}

ModalFormInput.prototype = new ModalFormItem();

ModalFormInput.prototype._init = function(){
    this._item = document.createElement("div");
    this._item.setClass("item");
    this._label_dom_obj = create_block_element("label", this._item);
    this._label_dom_obj.innerHTML = this._label;
    this._input_dom_odj = document.createElement("input");
    this._input_dom_odj.setAttribute("type",  this._type);
    this._input_dom_odj.setAttribute("value", this._value);
    this._input_dom_odj.onchange = this._onchange;
    this._item.appendChild(this._input_dom_odj);
};

ModalFormInput.prototype.getValue = function(){
    return this._input_dom_odj.value;
};

ModalFormInput.prototype.getName = function(){
    return this._name;
};

ModalFormInput.prototype.reset = function(){
    this._input_dom_odj.value = this._value;
};
/* END ModalFormInput */

/**
 * Button element
 * 
 * @constructor
 * @param {Object} options
 */
function ModalFormButton(options){
    this._value = "";
    this._onclick  = function(){};

    if (options){
        for (var prop in options){
            if (options.hasOwnProperty(prop)){
                this["_"+prop] = options[prop];
            }
        }
    }

    this._init();
}

ModalFormButton.prototype = new ModalFormItem();

ModalFormButton.prototype._init = function(){
    this._input_dom_odj = document.createElement("input");
    this._input_dom_odj.setAttribute("type",  "button");
    this._input_dom_odj.setAttribute("value", this._value);
};

ModalFormButton.prototype.action = function(){
    this._onclick && this._onclick();
};

ModalFormButton.prototype.getDomElement = function(){
    return this._input_dom_odj;
};
/* END ModalFormButton*/

loader.next();