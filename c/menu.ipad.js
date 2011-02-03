
var ipad_menu = {
    
    on : true,
    map : [],
    
    cur_row : 0,
    cur_col : 0,
    page    : 1,
    
    total_items : 0,
    
    page_items : 12,
    
    cur_page_items : 0,
        
    total_pages : 0,
    
    active_dashboard : false,
    
    show : function(){
        _debug('ipad_menu.show');
        
        this.dom_obj.show();
        this.on = true;
    },
    
    hide : function(){
        _debug('ipad_menu.hide');
        
        this.dom_obj.hide();
        this.on = false;
    },
    
    init : function(){
        _debug('ipad_menu.init');
        
        
    },
    
    shift_page : function(dir){
        _debug('ipad_menu.shift_page', dir);
        
        
    },
    
    hshift : function(dir){
        _debug('ipad_menu.hshift', dir);
        
        
    },
    
    vshift : function(dir){
        _debug('ipad_menu.vshift', dir);
        
        
    },
    
    action : function(){
        _debug('ipad_menu.action');
        
        
    },
    
    count_cur_page_items : function(){
        _debug('ipad_menu.count_cur_page_items');
        
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
        
        //this.show_layer.bind(key.OK, this);
        this.action.bind(key.OK, this);
        //this.hide_layer.bind(key.EXIT, this);
    },
    
    add : function(title, sub, img, module){
        
        
    },
    
    init_page_bar : function(){
        _debug('ipad_menu.init_page_bar');
        
        
    },
    
    render_page_bar : function(){
        _debug('ipad_menu.hide_layer');
        
        //$('page_'+this.prev_page).setAttribute('active', '');
        //$('page_'+this.page).setAttribute('active', 'active');
    }
}