/**
 * Layer constructor.
 * @constructor
 */

function Layer(){
    
    this.on = false;
    this.dom_obj = this.create_block();
    this.row_blocks  = [];
    this.total_rows  = 14;
    this.total_items = 0;
    this.total_pages = 0;
    this.cur_row = 0;
    this.data_items  = [];
    this.map = [];
    this.loading = false;
    this.load_params = {};
    this.total_vclub_items_obj = {};
    
    this.sidebar = {};
    
    this.bind();
}

Layer.prototype.show = function(){
    this.dom_obj.show();
    this.on = true;
}

Layer.prototype.hide = function(){
    this.dom_obj.hide();
    this.on = false;
}

Layer.prototype.init = function(){
    this.init_page_bar();
    this.init_list()
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
    
    for (var i=0; i<this.total_rows; i++){
        item = this.create_block('blue_row_bg');
        
        if (i > 0){
            offset = this.map[i-1].row.clientHeight + this.map[i-1].row.offsetTop;
            item.moveY(offset);
        }
        
        this.map[i] = this.init_blocks(item);
        
        this.map[i]['row'] = item;
        this.dom_obj.appendChild(item);
    }
    
    this.init_active_row();
    
    this.sidebar();
    
    this.load_data();
}

Layer.prototype.init_blocks = function(parent, is_active_row){
    
    var block_name;
    var block_map = {};
    
    for (var j=0; j<this.row_blocks; j++){
        
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
    
    this.dom_obj.appendChild(active_row);
}

Layer.prototype.init_sidebar = function(){
    this.sidebar = new sidebar(this);
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
    
    stb.load(

        this.load_params,
        
        function(result){
            this.total_pages = Math.ceil(req.responseJS.total_items/req.responseJS.max_page_items);
            this.set_total_items(req.responseJS.total_items);
            
            this.fill_list(result);
        },
        
        this
    )
}

Layer.prototype.set_total_items = function(count){
    var str = 'СТР <span class="text20_white bold">'+this.cur_page+'</span> ИЗ <span class="text20_white bold">'+this.total_pages+'</span>.';
    str += ' НАЙДЕНО <span class="text20_white bold">'+count+'</span> ЗАПИСЕЙ.';
    this.total_vclub_items_obj.innerHTML = str;
}

Layer.prototype.fill_list = function(data){
    
    this.total_items = data.length;
    this.data_items = data;
    
    
    for (var i=0; i<data.length; i++){
        
        for (var j=0; j<this.row_blocks; j++){
            this.handling_block(data[i][this.row_blocks[j]], this.map[i][this.row_blocks[j]+'_block']);
        }
    }
    
    if (i < this.total_rows){
        for (var j=i; j<this.total_rows; j++){
            this.clear_row(this.map[j].row);
        }
    }
    
    this.set_active_row(this.cur_row);
    this.loading = false;
    
}

Layer.prototype.handling_block = function(data, block_obj){
    
    switch (typeof(data)){
        case 'boolean': {
            
            if (typeof(data) == ''){
                if (data){
                    if (block_obj.isHidden()){
                        block_obj.show();
                    }
                }else{
                    if (!block_obj.isHidden()){
                        block_obj.hide();
                    }
                }
            }
            
            break;
        }
        default: {
            block_obj.innerHTML = data;
        }
    }
}

Layer.prototype.clear_row = function(row_obj){
    
    for (var i=0; i<row_obj.childNodes.length; i++){
        if (!row_obj.childNodes[i].isHidden()){

            if (row_obj.childNodes[i].innerHTML){
                row_obj.childNodes[i].innerHTML = '';
            }
            
            row_obj.childNodes[i].hide();
        }
    }
}

Layer.prototype.set_active_row = function(num){
    
    var offset = this.map[num]['row'].offsetTop - 15;
        
    this.active_row['row'].moveY(offset);
    
    for (var j=0; j<this.row_blocks; j++){
        this.handling_block(this.data_items[num][this.row_blocks[j]], this.active_row[this.row_blocks[j]+'_block']);
    }    
    
    if (this.active_row['row'].isHidden()){
        this.active_row['row'].show()
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
            this.shift_page(1);
            this.set_passive_row();
        }
    }else{
        if (this.cur_row > 0){
            this.cur_row--;
            this.set_active_row(this.cur_row);
        }else{
            this.shift_page(-1);
            this.set_passive_row();
        }
    }
}

Layer.prototype.shift_page = function(dir){

    if (dir > 0){
        if (this.cur_page < this.total_pages){
            this.cur_page++;
            this.cur_row = 0;
        }
    }else{
        if (this.cur_page > 1){
            this.cur_page--;
            this.cur_row = this.total_rows-1;
        }
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
    
    this.sidebar_switcher.bind(key.BLUE, this);
}

