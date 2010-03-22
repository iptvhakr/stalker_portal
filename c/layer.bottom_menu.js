/**
 * Bottom menu constructor.
 * @constructor
 */

function bottom_menu(parent, options){
    
    this.on = false;
    this.parent = parent;
    
    this.dom_obj = {};
    this.main_container = {};
    
    this.cur_row_idx = 0;
    
    this.items = [];
    
    this.offset_x = 190;
    
    if (options){
        if (options.offset_x){
            this.offset_x = options.offset_x;
        }
    }
}

bottom_menu.prototype.show = function(){
    _debug('bottom_menu.show');
    
    this.dom_obj.moveY(576 - this.dom_obj.clientHeight - 36);
    this.on = true;
}

bottom_menu.prototype.hide = function(){
    _debug('bottom_menu.hide');
    
    this.dom_obj.moveY(576);
    this.on = false;
}

bottom_menu.prototype.init = function(map){
    _debug('bottom_menu.init');
    
    this.items = map;
    
    this.dom_obj = create_block_element('bottom_menu', this.parent.dom_obj);
    
    this.dom_obj.moveX(this.offset_x);
    
    create_block_element('bottom_menu_corners', this.dom_obj);
    
    this.main_container = document.createElement('ul');
    this.main_container.addClass('bottom_menu_container');
    
    var item;
    
    for (var i = 0; i < map.length; i++){
        item = document.createElement('li');
        item.innerHTML = map[i].label;
        
        this.items[i].dom_obj = item;
        
        this.main_container.appendChild(item);
    }
    
    this.dom_obj.appendChild(this.main_container);
    
    this.set_active_row();
    
    this.hide();
}

bottom_menu.prototype.shift_row = function(dir){
    _debug('bottom_menu.shift_row', dir);
    
    this.set_passive_row();
    
    if (dir > 0){
        if (this.cur_row_idx < this.items.length - 1){
            this.cur_row_idx++;
        }
    }else{
        if (this.cur_row_idx > 0){
            this.cur_row_idx--;
        }
    }
    
    this.set_active_row();
}

bottom_menu.prototype.set_active_row = function(){
    _debug('bottom_menu.set_active_row');
    
    this.items[this.cur_row_idx].dom_obj.setClass('bottom_menu_active_row');
}

bottom_menu.prototype.set_passive_row = function(){
    _debug('bottom_menu.set_passive_row');
    
    this.items[this.cur_row_idx].dom_obj.delClass();
}

bottom_menu.prototype.action = function(){
    _debug('bottom_menu.action');
    
    try{
        this.items[this.cur_row_idx].cmd();
    }catch(e){
        _debug(e);
    }
}

bottom_menu.prototype.bind = function(){
    
    this.shift_row.bind(key.UP, this, -1);
    this.shift_row.bind(key.DOWN, this, 1);
    
    this.hide.bind(key.EXIT, this);
    
    this.action.bind(key.OK, this);
}
