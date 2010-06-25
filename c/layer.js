/**
 * Layer constructor.
 * @constructor
 */

function Layer(){
    
    /*  required properties  */
    this.layer_name = '';
    this.row_blocks  = [];
    this.load_params = {};
    this.class_name = '';
    
    this.on = false;
    this.dom_obj = this.create_block();
    document.body.appendChild(this.dom_obj);
    
    this.total_rows  = 14;
    this.total_items = 0;
    this.total_pages = 0;
    this.cur_row = 0;
    this.cur_page = 1;
    this.data_items  = [];
    this.map = [];
    this.loading = false;
    this.total_vclub_items_obj = {};
    this.buttons_bar = {};
    this.left_ear = {};
    this.right_ear = {};
    this.header_path = {};
    this.path_container = {};
    this.header_path_map = [];
    this.main_container = {};
    this.shift_row_callback;
    
    this.cur_view = 'wide';
    
    this.sidebar = {};
    
    this.page_dir = 1;
    
    this.color_buttons = [
        {"color" : "red"},
        {"color" : "green"},
        {"color" : "yellow"},
        {"color" : "blue"}
    ];
}

Layer.prototype.show = function(do_not_load){
    _debug('layer.show');
    
    this.dom_obj.show();
    this.on = true;
    
    if (!do_not_load){
        this.load_data();
    }else{
        this.set_active_row(this.cur_row);
    }
    
    stb.set_cur_place(this.layer_name);
    stb.set_cur_layer(this);
}

Layer.prototype.hide = function(do_not_reset){
    _debug('layer.hide');
    
    if (!do_not_reset){
        this.reset();
        
        this.load_params = this.load_params_pattern.clone();
        
        this.sidebar && this.sidebar.reset && this.sidebar.reset();
        
        if (this.sidebar && this.sidebar.on){
            //this.sidebar.reset();
            this.sidebar.hide();
        }
    }
    
    
    this.set_passive_row();
    
    this.dom_obj.hide();
    
    this.on = false;
}

Layer.prototype.reset = function(){
    _debug('Layer.reset');
    
    this.cur_row = 0;
    this.cur_page = 1;
    this.page_dir = 1;
}

Layer.prototype.init = function(){
    
    this.load_params_pattern = this.load_params.clone();
    
    if (!this.class_name){
        this.class_name = 'layer_bg';
    }
    
    this.dom_obj.addClass(this.class_name);
    
    this.dom_obj.id = this.layer_name;
    
    this.init_page_bar();
    this.init_list();
}

Layer.prototype.init_page_bar = function(){
    
    this.total_vclub_items_obj = this.create_block();
    this.total_vclub_items_obj.addClass('mb_header_info text14_white');
    
    this.dom_obj.appendChild(this.total_vclub_items_obj);
}

Layer.prototype.init_list = function(){
    
    var item;
    var offset;
    
    this.init_page_bar();
    
    this.main_container = create_block_element('wide_container', this.dom_obj);
    
    for (var i=0; i<this.total_rows; i++){
        item = this.create_block('blue_row_bg');
        
        if (i > 0){
            offset = this.map[i-1].row.clientHeight + this.map[i-1].row.offsetTop;
            item.moveY(offset);
        }
        
        this.map[i] = this.init_blocks(item);
        
        this.map[i]['row'] = item;
        this.main_container.appendChild(item);
    }
    
    this.init_active_row();
}

Layer.prototype.set_wide_container = function(){
    _debug('set_wide_container');
    
    this.cur_view = 'wide';
    this.main_container.setClass('wide_container');
    
    this.info_box && this.info_box.hide && this.info_box.hide();
}

Layer.prototype.set_middle_container = function(){
    _debug('set_middle_container');
    
    this.cur_view = 'middle';
    this.main_container.setClass('middle_container');
    
    this.info_box && this.info_box.show && this.info_box.show();
}

Layer.prototype.set_short_container = function(){
    _debug('set_short_container');
    
    this.cur_view = 'short';
    this.main_container.setClass('short_container');
    
    this.info_box && this.info_box.show && this.info_box.show();
}

Layer.prototype.init_blocks = function(parent, is_active_row){
    
    var block_name;
    var block_map = {};
    
    for (var j=0; j<this.row_blocks.length; j++){
        
        block_name = this.row_blocks[j]+'_block';
        
        block_map[block_name] = this.create_block(block_name, is_active_row);
        
        parent.appendChild(block_map[block_name]);
    }
    
    return block_map;
}

Layer.prototype.init_active_row = function(){
    
    var active_row = document.createElement('div');
    active_row.addClass('active_row_bg');
    
    active_row.hide();
    
    this.active_row = this.init_blocks(active_row, true);
    
    this.active_row['row'] = active_row;
    
    this.main_container.appendChild(active_row);
}

Layer.prototype.init_sidebar = function(options){
    this.sidebar = new sidebar(this, options);
    this.sidebar.init();
}

Layer.prototype.create_block = function(class_name, is_active){
    
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

Layer.prototype.load_data = function(){
    _debug('Layer.load_data');
    
    this.set_passive_row();
    
    this.load_params['p'] = this.cur_page;
    
    stb.load(

        this.load_params,
        
        function(result){
            _debug('callback run');
            _debug('result', result);
            this.result = result;
            this.total_pages = Math.ceil(result.total_items/result.max_page_items);
            
            if (result.selected_item !=0 || result.cur_page !=0){
                this.cur_row  = result.selected_item-1;
                this.cur_page = result.cur_page;
            }
            
            this.set_total_items(result.total_items);
            
            this.fill_list(result.data);
        },
        
        this
    )
}

Layer.prototype.set_total_items = function(count){
    _debug('Layer.set_total_items: ', count);
    
    var str = '';
    
    if (this.total_pages != 0){
        str += 'СТР <span class="text20_white bold">'+this.cur_page+'</span> ИЗ <span class="text20_white bold">'+this.total_pages+'</span>.';
    }
    
    str += ' НАЙДЕНО <span class="text20_white bold">'+count+'</span> ЗАПИСЕЙ.';
    this.total_vclub_items_obj.innerHTML = str;
}

Layer.prototype.fill_list = function(data){
    _debug('Layer.fill_list');
    
    this.total_items = data.length;
    this.data_items = data;
    
    for (var i=0; i<data.length; i++){
        
        for (var j=0; j<this.row_blocks.length; j++){
            this.handling_block(data[i][this.row_blocks[j]], this.map[i], this.row_blocks[j]);
        }
    }
    
    if (i < this.total_rows){
        for (var j=i; j<this.total_rows; j++){
            this.clear_row(this.map[j].row);
        }
    }
    
    if (this.result.selected_item ==0 && this.result.cur_page ==0){
        if (this.page_dir > 0){
            this.cur_row = 0;
        }else{
            this.cur_row = this.total_items-1;
        }
    }
    
    this.set_active_row(this.cur_row);
    this.loading = false;
}

Layer.prototype.handling_block = function(data, row_items, block_name){
    
    var block_obj = row_items[block_name+'_block']
    
    if (data === 1){
        if (block_obj.isHidden()){
            block_obj.show();
        }
    }else if (data === 0 || typeof(data) == "undefined"){
        if (!block_obj.isHidden()){
            block_obj.hide();
        }
    }else{
        if (block_obj.isHidden()){
            block_obj.show();
        }
        block_obj.innerHTML = data;
    }
}

Layer.prototype.clear_row = function(row_obj){
    
    for (var i=0; i<row_obj.childNodes.length; i++){
        if (!row_obj.childNodes[i].isHidden()){

            if (row_obj.childNodes[i].innerHTML){
                row_obj.childNodes[i].innerHTML = '&nbsp;';
                row_obj.childNodes[i].hide();
            }else{
                row_obj.childNodes[i].hide();
            }
        }
    }
}

Layer.prototype.set_active_row = function(num){
    
    _debug('set_active_row', num);
    try{
        if (num == 0){
            if (!this.data_items[num]){
                if (!this.active_row['row'].isHidden()){
                    this.active_row['row'].hide();
                }
            }else{
                if (this.active_row['row'].isHidden()){
                    this.active_row['row'].show();
                }
            }
        }
        
        var offset = this.map[num]['row'].offsetTop - 15;
        
        this.active_row['row'].moveY(offset);
        
        if (this.active_row['row'].isHidden()){
            this.active_row['row'].show();
        }
        
        if(!this.fav_manage_mode){
            
            if (this.data_items.length != 0){
            
                for (var j=0; j<this.row_blocks.length; j++){
                    this.handling_block(this.data_items[num][this.row_blocks[j]], this.active_row, this.row_blocks[j]);
                }    
                
                if (this.shift_row_callback && (this.cur_view == 'middle' || this.cur_view == 'short')){
                    this.shift_row_callback.call(this, this.data_items[num]);
                }
            }else{
                this.active_row['row'].hide();
            }
        }
    }catch(e){
        _debug(e);
    }
}

Layer.prototype.set_passive_row = function(){
    this.active_row['row'].hide();
}

Layer.prototype.shift_row = function(dir){
    
    if (this.loading){
        return;
    }
    
    if (dir > 0){
        if (this.cur_row < this.total_items - 1){
            this.cur_row++;
            this.set_active_row(this.cur_row);
        }else{
            this.set_passive_row();
            this.shift_page(1);
        }
    }else{
        if (this.cur_row > 0){
            this.cur_row--;
            this.set_active_row(this.cur_row);
        }else{
            this.set_passive_row();
            this.shift_page(-1);
        }
    }
}

Layer.prototype.shift_page = function(dir){

    this.page_dir = dir;
    
    if (dir > 0){
        if (this.cur_page < this.total_pages){
            this.cur_page++;   
        }else{
            this.cur_page = 1;
        }
        //this.cur_row = 0;
    }else{
        if (this.cur_page > 1){
            this.cur_page--;
        }else{
            this.cur_page = this.total_pages;
        }
        //this.cur_row = this.total_rows-1;
    }
    
    this.load_data();
}

Layer.prototype.sidebar_switcher = function(){

    if (this.sidebar && this.sidebar.on){
        this.sidebar.hide();
    }else{
        this.sidebar.show();
    }
}

Layer.prototype.bind = function(){
    
    this.shift_row.bind(key.UP, this, -1);
    this.shift_row.bind(key.DOWN, this, 1);
    
    this.shift_page.bind(key.PAGE_PREV, this, -1);
    this.shift_page.bind(key.PAGE_NEXT, this, 1);
    
    (function(){
        this.hide();
        main_menu.show();
    }).bind(key.MENU, this).bind(key.EXIT, this).bind(key.LEFT, this);
}

Layer.prototype.init_color_buttons = function(map){
    
    this.buttons_bar = this.create_block('color_button_bar');
    
    /*var table = '<table>';
    table += '<tr>';
    table += '<td><img src="i/1x1.gif"><div class="btn_red"></div><span class="footer_text">'+map[0].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_green"></div><span class="footer_text">'+map[1].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_yellow"></div><span class="footer_text">'+map[2].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_blue"></div><span class="footer_text">'+map[3].label+'</span></td>';
    table += '</tr>';
    table += '</table>';*/
    
    /*var table = '<table>';
    table += '<tr>';
    table += '<td><img src="i/1x1.gif"><div class="btn_red"></div><span>'+map[0].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_green"></div><span>'+map[1].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_yellow"></div><span>'+map[2].label+'</span></td>';
    table += '<td><img src="i/footer_bg2.png"><div class="btn_blue"></div><span>'+map[3].label+'</span></td>';
    table += '</tr>';
    table += '</table>';*/
    
    var table = document.createElement('table');
    //this.buttons_bar.appendChild(table);
    
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

Layer.prototype.init_left_ear = function(class_name){
    
    this.left_ear = this.create_block();
    
    if (class_name){
        this.left_ear.addClass(class_name);
    }
    
    this.dom_obj.appendChild(this.left_ear);
}

Layer.prototype.init_right_ear = function(class_name){
    
    this.right_ear = this.create_block();
    
    if (class_name){
        this.right_ear.addClass(class_name);
    }
    
    this.dom_obj.appendChild(this.right_ear);
}

Layer.prototype.init_header_path = function(begin){
    
    this.header_path = this.create_block('mb_header_first text24_white');
    this.path_container = document.createElement('span');
    this.header_path.innerHTML = begin + ' / ';
    this.header_path.appendChild(this.path_container);
    this.dom_obj.appendChild(this.header_path);
}

Layer.prototype.update_header_path = function(map){
    
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