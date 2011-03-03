
var ipad_menu = {
    
    on : true,
    map : {},
    
    cur_row : 0,
    cur_col : 0,
    page    : 1,
    
    total_items : 0,
    
    page_items : 12,
    
    cur_page_items : 0,
        
    total_pages : 0,
    
    active_dashboard : false,

    cur_item : "root",
    
    show : function(){
        _debug('ipad_menu.show');
        
        this.dom_obj.show();
        this.on = true;

        this.total_pages = Math.ceil(this.map[this.cur_item].action.length / this.page_items);
        this.count_cur_page_items();
    },
    
    hide : function(){
        _debug('ipad_menu.hide');
        
        this.dom_obj.hide();
        this.on = false;
    },
    
    init : function(){
        _debug('ipad_menu.init');
        
        this.dom_obj = create_block_element('ipad_menu');
        this.back = create_block_element('back', this.dom_obj);

        create_block_element('bg', this.dom_obj);
        
        var smokedglass = create_block_element('smokedglass', this.dom_obj);
        var date_time = create_block_element('date_time', smokedglass);
        this.date_bar = create_block_element('date', date_time);
        this.time_bar = create_block_element('time', date_time);

        var cell_body = document.createElement('ul');
        cell_body.addClass('cell_body');
        this.dom_obj.appendChild(cell_body);

        var cell_body_li = document.createElement('li');
        cell_body.appendChild(cell_body_li);

        var page_bar_container = create_block_element('page_bar_container', this.dom_obj);
        this.page_list = document.createElement('ul');
        this.page_list.addClass('page_list');
        page_bar_container.appendChild(this.page_list);

        create_block_element('shell', this.dom_obj);

        this.dashboard = document.createElement('ul');
        this.dashboard.addClass('dashboard');
        this.dom_obj.appendChild(this.dashboard);

        var dashboard_li = document.createElement('li');
        this.dashboard.appendChild(dashboard_li);

        this.map.root = {
            "alias"        : "root",
            "cell_body"    : cell_body,
            "cell_body_li" : cell_body_li,
            "parent"       : null,
            "action"       : []
        };

        create_block_element('border', this.dom_obj);

        this.hide();
    },
    
    shift_page : function(dir){
        _debug('ipad_menu.shift_page', dir);
        
        var offset = 0;

        this.prev_page = this.page;

        if (dir > 0){

            if (this.page < this.total_pages){

                this.page++;

                if (!this.active_dashboard){
                    this.set_passive();

                    this.cur_row = 0;
                    this.cur_col = 0;
                }

                offset = -(this.page-1)*720;

                this.map[this.cur_item].cell_body.moveX(offset);

                this.set_active();
            }

        }else{

            if (this.page > 1){

                this.page--;

                if (!this.active_dashboard){
                    this.set_passive();

                    this.cur_row = 0;
                    this.cur_col = 0;
                }

                offset = -(this.page-1)*720;

                this.map[this.cur_item].moveX(offset);

                this.set_active();
            }
        }

        this.count_cur_page_items();

        this.render_page_bar();
    },
    
    hshift : function(dir){
        _debug('ipad_menu.hshift', dir);

        _debug('this.cur_page_items', this.cur_page_items);

        var rows = Math.floor(this.cur_page_items/4);

        var row_items = 4;

        if (rows != 3){
            row_items = this.cur_page_items - (Math.floor(this.cur_page_items/4)-1) * 4;
        }

        _debug('row_items', row_items);

        if (dir > 0){
            if (this.cur_col < row_items - 1){
                this.set_passive();
                this.cur_col++;
            }
        }else{
            if (this.cur_col > 0){
                this.set_passive();
                this.cur_col--;
            }
        }

        this.set_active();
    },
    
    vshift : function(dir){
        _debug('ipad_menu.vshift', dir);
        
        var rows = Math.ceil(this.cur_page_items/4);

        if (dir > 0){
            if (this.cur_row < rows - 1){
                this.active_dashboard = false;
                this.set_passive();
                this.cur_row++;
            }else{
                this.set_passive();
                this.active_dashboard = true;
            }
        }else{
            if (this.active_dashboard){
                this.set_passive();
                this.active_dashboard = false;
            }else if (this.cur_row > 0){
                this.active_dashboard = false;
                this.set_passive();
                this.cur_row--;
            }
        }

        this.set_active();
    },

    set_active : function(){
        _debug('ipad_menu.set_active');

        _debug('this.cur_col', this.cur_col);
        _debug('this.cur_row', this.cur_row);

        var top = 0;

        var item_num = (this.page - 1) * this.page_items + this.cur_row * 4 + this.cur_col;

        _debug('item_num', item_num);

        window.clearTimeout(this.reflect_show_timer);

        _debug('this.active_dashboard', this.active_dashboard);

        var self = this;

        if (this.active_dashboard){
            //this.reflect_show_timer = window.setTimeout(function(){$('dcell_'+self.cur_col).addClass('active')}, 160);
            this.reflect_show_timer = window.setTimeout(function(){self.map.root.dashboard[self.cur_col].cell.addClass('active')}, 160);
            top = 126 + 4*100 - 51;
        }else{
            this.reflect_show_timer = window.setTimeout(function(){self.map[self.cur_item].action[item_num].cell.addClass('active')}, 160);
            top = 126 + (this.cur_row+1)*95 - 51;
        }

        var left = 50 + (this.cur_col) * 155;

        this.back.moveX(left);
        this.back.moveY(top);
    },

    set_passive : function(){
        _debug('ipad_menu.set_passive');

        _debug('this.cur_col', this.cur_col);
        _debug('this.cur_row', this.cur_row);

        var item_num = (this.page - 1) * this.page_items + this.cur_row * 4 + this.cur_col;

        _debug('item_num', item_num);

        _debug('this.active_dashboard', this.active_dashboard);

        if (this.active_dashboard){
            //$('dcell_'+this.cur_col).removeClass('active');
            this.map.root.dashboard[this.cur_col].cell.addClass('active');
        }else{
            //$('cell_'+item_num).removeClass('active');
            this.map[this.cur_item].action[item_num].cell.addClass('active')
        }
    },
    
    action : function(){
        _debug('ipad_menu.action');

        var item_num = (this.page - 1) * this.page_items + this.cur_row * 4 + this.cur_col;

        this.cur_item = this.map[this.cur_item].action[item_num].alias;

        _debug('this.cur_item', this.cur_item);
        
        this.total_pages = Math.ceil(this.map[this.cur_item].action.length / this.page_items);
        
    },
    
    count_cur_page_items : function(){
        _debug('ipad_menu.count_cur_page_items');

        _debug('this.total_pages', this.total_pages);

        this.total_items = this.map[this.cur_item].action.length;

        _debug('this.total_items', this.total_items);

        if (this.page < this.total_pages){
            this.cur_page_items = this.page_items;
        }else{
            
            var page = Math.floor(this.total_items/this.page_items);
            
            if (page == this.page){
                this.cur_page_items = this.page_items;
            }else{
                this.cur_page_items = this.total_items - Math.floor(this.total_items/this.page_items) * this.page_items;
            }
        }
        
        _debug('this.cur_page_items', this.cur_page_items);
    },
    
    bind_keys : function(){
        
        this.shift_page.bind(key.PAGE_PREV, this, 1);
        this.shift_page.bind(key.PAGE_NEXT, this, -1);
        
        this.vshift.bind(key.UP, this, -1);
        this.vshift.bind(key.DOWN, this, 1);
        
        this.hshift.bind(key.RIGHT, this, 1);
        this.hshift.bind(key.LEFT, this, -1);
        
        this.action.bind(key.OK, this);
    },
    
    add : function(item){

        if (!item.hasOwnProperty('parent')){
            item.parent = 'root';
        }

        if (!this.map.hasOwnProperty(item.parent)){
            _debug('Error: parent item ' + item.parent + ' not found');
            return;
        }

        if (!this.map.hasOwnProperty(item.alias)){
            item.cell = create_block_element('cell', this.map[item.parent].cell_body_li);
            item.shadow = create_block_element('shadow', item.cell);
            item.icon = create_block_element('icon', item.cell);
            var img = document.createElement('img');
            img.src = item.img;
            item.icon.appendChild(img);
            item.label = create_block_element('label', item.cell);
            item.label.innerHTML = item.title;
        }

        this.map[item.alias] = item;

        if (typeof(item.action) == 'object' && item.action.hasOwnProperty('length')){

            this.map[item.alias].cell_body = document.createElement('ul');
            this.map[item.alias].cell_body.addClass('cell_body');
            this.map[item.alias].cell_body.hide();
            this.dom_obj.appendChild(this.map[item.alias].cell_body);

            for (var i = 0; i<item.action.length; i++){

                if ((i % this.page_items) === 0){
                    this.map[item.alias].cell_body_li = document.createElement('li');
                    this.map[item.alias].cell_body.appendChild(this.map[item.alias].cell_body_li);
                }

                this.add(item.action[i]);
            }
        }

        if (typeof(this.map[item.parent].action) == 'object' && this.map[item.parent].action.hasOwnProperty('length')){
            this.map[item.parent].action.push(item);
        }
    },
    
    init_page_bar : function(){
        _debug('ipad_menu.init_page_bar');
        
        
    },
    
    render_page_bar : function(){
        _debug('ipad_menu.hide_layer');
        
        //$('page_'+this.prev_page).setAttribute('active', '');
        //$('page_'+this.page).setAttribute('active', 'active');
    }
};