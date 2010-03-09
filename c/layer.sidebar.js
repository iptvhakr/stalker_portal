/**
 * Sidebar constructor.
 * @constructor
 */

function sidebar(parent){
    
    this.on = false;
    this.parent = parent;
    
    this.dom_obj = {};
    this.width   = 0;
    
    this.arrows  = {};
    this.items   = [];
    this.cur_item_idx = 0;
}

sidebar.prototype.show = function(){
    this.dom_obj.moveX(720-this.width-96);
    this.on = true;
}

sidebar.prototype.hide = function(){
    this.dom_obj.moveX(720);
    this.on = false;
}

sidebar.prototype.init = function(){
    
    this.dom_obj = document.createElement('div');
    this.dom_obj.addClass('footer_sidepanel');
    
    var side_r = document.createElement('div');
    side_r.addClass('footer_sidepanel_r');
    
    this.dom_obj.appendChild(side_r);
    
    this.parent.dom_obj.appendChild(this.dom_obj);
    
    this.arrows = document.createElement('div');
    this.arrows.addClass('sidebar_arrows');
    this.dom_obj.appendChild(this.arrows);
}

sidebar.prototype.init_items = function(alias, map){
    
    var item;
    var left_offset = 0;
    var container_class = 'items_container_active';
    
    var options = {};
    
    if (arguments.length = 3){
        options = arguments[2];
    }
    
    this.items.push({"header" : options.header, "alias": alias, "container": {}, "map" : [], "max_width" : 0, "map_dom_obj" : [], "selected" : ''});
    
    var cur_idx = this.items.length-1;
    
    if (cur_idx > 0){
        container_class = 'items_container_passive'
    }
    
    this.items[cur_idx].map = map;
    
    this.items[cur_idx].container = document.createElement('div');
    this.items[cur_idx].container.addClass(container_class);
    this.dom_obj.appendChild(this.items[cur_idx].container);
    
    var active_bg_row = document.createElement('div');
    active_bg_row.addClass('active_bg_row');
    this.items[cur_idx].container.appendChild(active_bg_row);
    
    for (var i=0; i<map.length; i++){
        
        item = document.createElement('div');
        
        item.moveX(this.width);
        item.innerHTML = map[i].title;
        
        if (options && options.align){
            item.style.textAlign = options.align;
        }
        
        this.items[cur_idx].map_dom_obj[this.items[cur_idx].map_dom_obj.length] = item;
        
        this.items[cur_idx].container.appendChild(item);
        
        if (i < 9){
            if (i > 0){
                offset = this.items[cur_idx].map_dom_obj[i-1].offsetTop + this.items[cur_idx].map_dom_obj[i-1].clientHeight;
            }else{
                offset = 53;
            }
            this.items[cur_idx].map_dom_obj[i].moveY(offset);
        }else{
            this.items[cur_idx].map_dom_obj[i].moveY(400);
        }
        
        if (i == 4){
            item.addClass('active_item');
        }
        
        if (item.clientWidth > this.items[cur_idx].max_width){
            this.items[cur_idx].max_width = item.clientWidth;
        }
    }
    
    if (cur_idx > 0){
        var separator = this.create_separator();
        separator.moveX(this.width + 45 + 6);
        this.dom_obj.appendChild(separator);
        
        container_class = 'items_container_passive'
    }
    
    if (cur_idx == 0){
        this.arrows.moveX(this.items[cur_idx].max_width/2 + 45 + this.width);
    }
    
    for (var i=0; i<map.length; i++){
        this.items[cur_idx].map_dom_obj[i].style.width = this.items[cur_idx].max_width + 'px';
    }
    
    active_bg_row.style.width = this.items[cur_idx].max_width + 'px';
    active_bg_row.moveX(this.width);
    
    this.dom_obj.style.width = this.width + this.items[cur_idx].max_width + 102 + 'px';
    
    this.width += this.items[cur_idx].max_width + 6;
    
    this.render_items(cur_idx);
}

sidebar.prototype.vshift = function(dir){
    
    if (dir > 0){
        this.items[this.cur_item_idx].map.push(this.items[this.cur_item_idx].map.shift());
    }else{
        var menu_length = this.items[this.cur_item_idx].map.length;
        this.items[this.cur_item_idx].map.unshift(this.items[this.cur_item_idx].map[menu_length-1]);
        this.items[this.cur_item_idx].map.splice(menu_length, 1);
    }
    
    this.render_items(this.cur_item_idx);
}

sidebar.prototype.hshift = function(dir){
    
    if (dir > 0){
        if (this.cur_item_idx < this.items.length - 1){
            this.cur_item_idx++;
        }
    }else{
        if (this.cur_item_idx > 0){
            this.cur_item_idx--;
        }
    }
    
    this.set_active_item();
}

sidebar.prototype.set_active_item = function(){
    var offset = 0;
            
    for (var i=0; i<this.items.length; i++){
        
        if (i != this.cur_item_idx){
            this.items[i].container.setClass('items_container_passive');
            offset += this.items[i].max_width;
        }else{
            this.items[i].container.setClass('items_container_active');
            this.arrows.moveX(this.items[this.cur_item_idx].max_width/2 + 46 + (this.items.length-1)*6 + offset);
        }
    }
}

sidebar.prototype.render_items = function(item_idx){
    
    for (var i=0; i < 9; i++){

        this.items[item_idx].map_dom_obj[i].innerHTML = this.items[item_idx].map[i].title;

        if (this.items[item_idx].map[i].id == this.items[item_idx].selected){
            this.items[item_idx].map_dom_obj[i].setClass('selected_item');
        }else{
            this.items[item_idx].map_dom_obj[i].setClass('passive_item');
        }
    }
}

sidebar.prototype.create_separator = function(){
    
    var separator = document.createElement('div');
    separator.addClass('sidebar_line');
    this.dom_obj.appendChild(separator);
    
    return separator;
}

sidebar.prototype.set_selected_item = function(){
    
    for(var i=0; i < 9; i++){
        if (this.items[this.cur_item_idx].map[i].id == this.items[this.cur_item_idx].selected){
            this.items[this.cur_item_idx].map_dom_obj[i].setClass('passive_item');
        }
    }
    
    this.items[this.cur_item_idx].selected = this.items[this.cur_item_idx].map[4].id;
    this.items[this.cur_item_idx].map_dom_obj[4].setClass('selected_item');
    
    this.action();
}

sidebar.prototype.action = function(){
            
    for (var i=0; i<this.items.length; i++){
        this.parent.load_params[this.items[i].alias] = this.items[i].selected;
        
        this.parent.update_header_path([{"alias" : this.items[i].alias, "title" : this.items[i].header, "item" : this.items[this.cur_item_idx].map[4].title}]);
    }
    
    this.parent.load_data();
    
    _debug(this.parent.load_params);
}

sidebar.prototype.bind = function(){
    this.vshift.bind(key.UP, this, -1);
    this.vshift.bind(key.DOWN, this, 1);
    
    this.hshift.bind(key.LEFT, this, -1);
    this.hshift.bind(key.RIGHT, this, 1);
    
    this.set_selected_item.bind(key.OK, this);
}