/**
 * Base layer constructor.
 * @constructor
 */

function BaseLayer(){
    
    this.layer_name = '';
    
    this.on = false;
    
    /*this.dom_obj = this.create_block();
    document.body.appendChild(this.dom_obj);*/
    this.dom_obj = {};
    
    this.color_buttons = [
        {"color" : "red"},
        {"color" : "green"},
        {"color" : "yellow"},
        {"color" : "blue"}
    ];
}

BaseLayer.prototype.show = function(){
    _debug('BaseLayer.show');
    
    this.dom_obj.show();
    this.on = true;
    
    stb.set_cur_place(this.layer_name);
    stb.set_cur_layer(this);
}

BaseLayer.prototype.hide = function(){
    _debug('BaseLayer.hide');
    
    this.dom_obj.hide();
    
    this.on = false;
}

BaseLayer.prototype.init = function(){
    _debug('BaseLayer.init');
    
    if (!this.class_name){
        this.class_name = 'layer_bg';
    }
    
    this.dom_obj.addClass(this.class_name);
    
    this.dom_obj.id = this.layer_name;
}

BaseLayer.prototype.create_block = function(class_name, is_active){
    
    var prefix = '';
        
    if (is_active){
        prefix = 'active_';
    }
    
    var block = document.createElement('div');
    
    if (class_name){
        block.addClass(prefix + class_name);
    }
    
    return block;
}

BaseLayer.prototype.init_color_buttons = function(map){
    this.buttons_bar = this.create_block('color_button_bar');
    
    var table = document.createElement('table');
    
    var row = document.createElement('tr');
    table.appendChild(row);
    
    for (var i=0; i<=3; i++){
        
        var cell = document.createElement('td');
        row.appendChild(cell);
        
        var separator = document.createElement('img');
        
        if (i == 0){
            separator.src = 'i/1x1.gif';
        }else{
            separator.src = 'i/footer_bg2.png';
        }
        
        cell.appendChild(separator);
        
        this.color_buttons[i].img_obj = create_block_element('btn_'+this.color_buttons[i].color, cell);
        
        this.color_buttons[i].text_obj = create_inline_element('', cell);
        
        this.color_buttons[i].text_obj.innerHTML = map[i].label;
    }
    
    if (typeof(map[0].cmd) == 'function'){
        map[0].cmd.bind(key.RED, this);
    }
    
    if (typeof(map[1].cmd) == 'function'){
        map[1].cmd.bind(key.GREEN, this);
    }
    
    if (typeof(map[2].cmd) == 'function'){
        map[2].cmd.bind(key.YELLOW, this);
    }
    
    if (typeof(map[3].cmd) == 'function'){
        map[3].cmd.bind(key.BLUE, this);
    }
       
    this.buttons_bar.appendChild(table);
    
    this.dom_obj.appendChild(this.buttons_bar);
}

BaseLayer.prototype.init_left_ear = function(txt){
    
    var ears_left_container = create_block_element('ears_left_container');
    
    this.left_ear = create_block_element('ears_left');
    
    var left_arrow = create_block_element('ears_arrow_left');
    var text_element = create_block_element();
    text_element.innerHTML = txt;
    
    this.left_ear.appendChild(left_arrow);
    this.left_ear.appendChild(text_element);
    this.left_ear.appendChild(left_arrow.cloneNode(true));
    
    ears_left_container.appendChild(this.left_ear);
    
    this.dom_obj.appendChild(ears_left_container);
}

BaseLayer.prototype.init_right_ear = function(txt){
    
    var ears_right_container = create_block_element('ears_right_container');
    
    this.right_ear = create_block_element('ears_right');
    
    var right_arrow = create_block_element('ears_arrow_right');
    var text_element = create_block_element();
    text_element.innerHTML = txt;
    
    this.right_ear.appendChild(right_arrow);
    this.right_ear.appendChild(text_element);
    this.right_ear.appendChild(right_arrow.cloneNode(true));
    
    ears_right_container.appendChild(this.right_ear);
    
    this.dom_obj.appendChild(ears_right_container);
}

BaseLayer.prototype.init_header_path = function(begin){
    
    this.header_path = this.create_block('mb_header_first text24_white');
    this.path_container = document.createElement('span');
    this.header_path.innerHTML = begin + ' / ';
    this.header_path.appendChild(this.path_container);
    this.dom_obj.appendChild(this.header_path);
}

BaseLayer.prototype.update_header_path = function(map){
    
    var path = '';
    
    for(var i=0; i<map.length; i++){
        
        var idx = this.header_path_map.getIdxByVal('alias', map[i].alias);
        
        if (this.header_path_map.hasOwnProperty(idx)){
            
            this.header_path_map.splice(idx, 1);
        }
        
        if (map[i].item != '*' && map[i].item != ''){
            this.header_path_map.push({"alias" : map[i].alias, "title" : map[i].item + ' /'});
        }
    }
    
    for (var i=0; i<this.header_path_map.length; i++){
        path += '<span class="text20_white uppercase">'+this.header_path_map[i].title+'</span>';
    }
    
    this.path_container.innerHTML = path;
}

loader.next();