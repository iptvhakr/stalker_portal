var main_menu = {
    
    on : true,
    map : [],
    menu_offset : [100,250,400],
    hide_sub_t : 0,
    active_sub : 0,
    dom_obj : {},
    hor_menu_obj : {},
    vert_trans : {},
    cur_weather : {},
    main_menu_date_bar : {},
    main_menu_date : {},
    main_menu_time : {},
    
    show : function(){
        _debug('main_menu.show');
        this.dom_obj.show();
        this.on = true;
        this.render();
    },
    
    hide : function(){
        _debug('main_menu.hide');
        this.dom_obj.hide();
        this.on = false;
    },
    
    init : function(map){
        _debug('main_menu.init');
        
        this.hshift.bind(key.LEFT, main_menu, -1);
        this.hshift.bind(key.RIGHT, main_menu, 1);
        
        this.vshift.bind(key.UP, main_menu, -1);
        this.vshift.bind(key.DOWN, main_menu, 1);
        this.action.bind(key.OK, main_menu);
        
        
        (function(){
            this.hide();
            stb.player.play_last();
        }).bind(key.EXIT, this);
        
        this.map = map;
        
        this.dom_obj = create_block_element('main_menu', document.body);
        
        this.main_menu_date_bar = create_block_element('main_menu_date_bar', this.dom_obj);
        
        this.main_menu_date = create_inline_element('main_menu_date' ,this.main_menu_date_bar);
        
        this.main_menu_time = create_inline_element('main_menu_time' ,this.main_menu_date_bar);
        
        this.cur_weather = create_block_element('main_menu_cur_weather', this.dom_obj);
        
        this.vert_container = create_block_element('main_menu_vert_container', this.dom_obj);
        
        this.vert_trans = create_block_element('main_menu_vert_trans', this.dom_obj);
        
        this.reconstruct_sub();
        
        this.hor_menu_obj = create_block_element('main_menu_hor', this.dom_obj);
        
        create_block_element('main_menu_hor_left', this.dom_obj);
        
        create_block_element('main_menu_hor_right', this.dom_obj);
        
        this.build();
        
        main_menu.hide()
    },
    
    build : function(){
        
        this.clear_menu();
        
        for(var i=0; i<this.map.length; i++){
            var menu_item = create_block_element();
            menu_item.id = 'menu_item_'+(i+1);
            menu_item.innerHTML = this.map[i]['title']+"<br/>";
            
            /*var img = document.createElement('img');
            img.src = 'i/menu_item_img_'+(i+1)+'.png';
            menu_item.appendChild(img);*/
            
            if(typeof(this.menu_offset[i]) == 'number'){
                menu_item.moveX(this.menu_offset[i]);
            }else{
                menu_item.moveX(0);
            }
            
            if (i == 1){
                menu_item.setClass('main_menu_active');
            }
            
            this.map[i]['dom_obj'] = menu_item;
            this.map[i]['dom_obj_id'] = menu_item.id;
            this.hor_menu_obj.appendChild(menu_item);
          
            var sub_menu = document.createElement('div');
            sub_menu.id = 'sub_menu_'+(i+1);
            this.map[i]['sub_obj'] = sub_menu;
            this.map[i]['sub_obj_id'] = sub_menu.id;
            sub_menu.setClass('main_menu_vert');

            if (i == 2){
                sub_menu.show();
            }else{
                sub_menu.hide();
            }
            
            this.dom_obj.insertBefore(sub_menu, this.vert_trans);
            
            for (var j=0; j<this.map[i].sub.length; j++){
                var sub_menu_item = document.createElement('div');
                sub_menu_item.innerHTML = this.map[i].sub[j].title;
                
                this.map[i].sub[j]['sub_punct_obj'] = sub_menu_item;
                
                if (this.map[i].sub[j].hasOwnProperty('cut')){
                    sub_menu_item.style.borderBottom = '2px solid #667b8e';
                }
                
                sub_menu.appendChild(sub_menu_item);
            }
        }
    },
    
    render : function(){
        
        var offset = 250 - this.map[0]['dom_obj'].clientWidth - 20;
        
        this.map[0]['dom_obj'].moveX(offset);
        this.map[0]['dom_obj'].setClass('main_menu_passive');
        
        this.map[1]['dom_obj'].moveX(250);
        this.map[1]['dom_obj'].setClass('main_menu_active');
        
        offset = 250 + this.map[1]['dom_obj'].clientWidth + 20;
        
        this.map[2]['dom_obj'].moveX(offset);
        this.map[2]['dom_obj'].setClass('main_menu_passive');
        
        for(var i=3; i<this.map.length; i++){
            this.map[i]['dom_obj'].moveX(-200);
        }
        
        var self = this;
        
        if(this.hide_sub_t){
            window.clearTimeout(this.hide_sub_t);
        }
        
        this.hide_sub_t = window.setTimeout(function(){self.show_sub()}, 500);
    },
    
    show_sub : function(){
        
        for (var i=0; i<this.map.length; i++){
            if (!this.map[i].sub_obj.isHidden()){
                this.map[i].sub_obj.hide();
            }
        }
        
        this.map[1].sub_obj.show();
        
        this.render_sub();
    },
    
    hshift : function(dir){
        
        if (dir > 0){
            this.map.push(this.map.shift());
        }else{
            var menu_length = this.map.length;
            this.map.unshift(this.map[menu_length-1]);
            this.map.splice(menu_length, 1);
        }
        this.render();
    },
    
    vshift : function(dir){
        if (dir > 0){
            if (this.map[1].sub.length > 0){
                this.map[1].sub.push(this.map[1].sub.shift());
            }
        }else{
            var menu_length = this.map[1].sub.length;
            if (menu_length > 0){
                this.map[1].sub.unshift(this.map[1].sub[menu_length-1]);
                this.map[1].sub.splice(menu_length, 1);
            }
        }
        this.render_sub();
    },
    
    render_sub : function(){
        
        var offset = 0;
        var total_items = 0;
        var max_container_width = 0;
        
        if (this.map[1].sub.length < 7){
            total_items = this.map[1].sub.length;
        }else{
            total_items = 7;
        }
        
        this.active_sub = Math.ceil(total_items/2)-1;
        
        for (var i=0; i<this.map[1].sub.length; i++){
            
            if (i<7){
                
                if (i==0){
                    this.map[1].sub[i]['sub_punct_obj'].setClass('sub_menu_punct_alpha1');
                }else if (i==1){
                    this.map[1].sub[i]['sub_punct_obj'].setClass('sub_menu_punct_alpha2');
                }else{
                    this.map[1].sub[i]['sub_punct_obj'].setClass('sub_menu_punct');
                }
                
                if (i == this.active_sub){
                    this.map[1].sub[i]['sub_punct_obj'].setClass('sub_menu_active');
                }
                
                if (this.map[1].sub[i]['sub_punct_obj'].clientWidth > max_container_width){
                    max_container_width = this.map[1].sub[i]['sub_punct_obj'].clientWidth;
                }
                
                offset = this.map[1].sub_obj.offsetTop - (total_items-i)*this.map[1].sub[i]['sub_punct_obj'].clientHeight;
                
                this.map[1].sub[i]['sub_punct_obj'].moveY(offset);
            }else{
                this.map[1].sub[i]['sub_punct_obj'].moveY(-100);
            }
        }
        
        if (this.map[1].sub[0] && isNaN(parseInt(this.map[1].sub[0]['sub_punct_obj'].style.width))){
            for (var j=0; j<this.map[1].sub.length; j++){
                this.map[1].sub[j]['sub_punct_obj'].style.width = max_container_width-30 + 'px';
            }
        }
        
        if (this.map[1].sub[0]){
            this.vert_container.style.height = total_items*this.map[1].sub[0]['sub_punct_obj'].clientHeight + 'px';
            this.vert_container.style.width = 4 + max_container_width + 'px';
            this.vert_trans.style.height = total_items*this.map[1].sub[0]['sub_punct_obj'].clientHeight + 'px';
        }else{
            this.vert_container.style.height = 0;
            this.vert_trans.style.height = 0;
        }
    },
    
    reconstruct_sub : function(){
        
        var shift_items_count = 0;
        
        for(var i=0; i<this.map.length; i++){
            
            if (this.map[i].sub.length > 0){
                this.map[i].sub[this.map[i].sub.length-1]['cut'] = 1;
            }
            
            if(this.map[i].sub.length < 7){
                shift_items_count = Math.floor(this.map[i].sub.length / 2)
                
                if (Math.isEven(this.map[i].sub.length)){
                    shift_items_count --;
                }
            }else{
                shift_items_count = 3;
            }
            
            for (var j=1; j<=shift_items_count; j++){
                this.map[i].sub.unshift(this.map[i].sub.pop());
            }
        }
    },
    
    action : function(){
        //_debug(this.map[1].sub[this.active_sub].cmd);
        
        if (this.map[1].sub && this.map[1].sub[this.active_sub] && typeof(this.map[1].sub[this.active_sub].cmd) == 'object'){
            
            var context = this.map[1].sub[this.active_sub].cmd.context || window;
            
            this.map[1].sub[this.active_sub].cmd.func.apply(context, this.map[1].sub[this.active_sub].cmd.args);
            
        }else if (this.map[1].sub && this.map[1].sub[this.active_sub] && typeof(this.map[1].sub[this.active_sub].cmd) == 'function'){
            this.map[1].sub[this.active_sub].cmd();
        }else if (this.map[1].sub && this.map[1].sub[this.active_sub] && typeof(this.map[1].sub[this.active_sub].cmd) == 'string'){
            eval(this.map[1].sub[this.active_sub].cmd);
        }else{
            this.map[1].cmd();
        }
    },
    
    clear_menu : function(){
        
        _debug('clear_menu');
        _debug('nodes', this.hor_menu_obj.childNodes.length);
        
        try{
            var length = this.hor_menu_obj.childNodes.length;
            
        for(var i=0; i<length; i++){
            this.hor_menu_obj.removeChild(this.hor_menu_obj.lastChild);
        }
        
        for (var i=0; i<this.map.length; i++){
            if (this.map && this.map[i] && this.map[i]['sub_obj']){
                this.map[i]['sub_obj'].parentNode.removeChild(this.map[i]['sub_obj']);
            }
        }
        }catch(e){
            _debug(e);
        }
        
        _debug('end clear_menu');
    },
    
    add : function(title, sub, cmd){
        
        var cmd = cmd || '';
        
        var sub = sub || [];
        
        if (sub.length > 0){
            for (var k=1; k<=3; k++){
                sub.unshift(sub.pop());
            }
        }
        
        this.map.push(
            {
                "title" : title,
                "cmd"   : cmd,
                "sub"   : sub
            }
        );
        
        this.build();
    },
}