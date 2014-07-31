var main_menu = {
    
    on : true,
    map : [],
    cells : [],
    dom_obj : {},
    date : {},
    time : {},
    
    show : function(){
        _debug('main_menu.show');
        this.dom_obj.show();
        this.on = true;
        this.render();
        stb.set_cur_place('main_menu');
        stb.clock.show();
        stb.set_cur_layer(this);
        
        module.curweather && module.curweather.current && module.curweather.render();

        this.triggerCustomEventListener('mainmenushow');
    },
    
    hide : function(){
        _debug('main_menu.hide');
        this.dom_obj.hide();
        this.on = false;

        this.triggerCustomEventListener('mainmenuhide');
    },
    
    init : function(){
        _debug('main_menu.init');
        
        this.hshift.bind(key.LEFT, main_menu, -1);
        this.hshift.bind(key.RIGHT, main_menu, 1);
        
        this.vshift.bind(key.UP, main_menu, -1);
        this.vshift.bind(key.DOWN, main_menu, 1);
        this.action.bind(key.OK, main_menu);
        
        this.page_shift.bind(key.PAGE_PREV, main_menu, -1);
        this.page_shift.bind(key.PAGE_NEXT, main_menu, 1);
        
        (function(){
            _debug('window.referrer', window.referrer);
            if (window.referrer){
                //stb.LoadURL(window.referrer);
                this.exit_comfirm.show();
                return;
            }

            _debug('stb.player.channels', stb.player.channels);
            if (stb.player.channels && stb.player.channels.length>0 && module.tv){
                this.hide();
                stb.player.play_last();
            }
        }).bind(key.EXIT, this);

        (function(){
            screensaver.toggle();
        }).bind(key.CLOCK, this);
        
        this.dom_obj = create_block_element('main_menu', document.body);
        
        this.main_menu_date_bar = create_block_element('main_menu_date_bar', this.dom_obj);
        
        this.date = create_inline_element('main_menu_date' ,this.main_menu_date_bar);
        
        this.time = create_inline_element('main_menu_time' ,this.main_menu_date_bar);
        
        //this.cur_weather = create_block_element('main_menu_cur_weather', this.dom_obj);
        
        var main_menu_ver = create_block_element('main_menu_ver', this.dom_obj);
        main_menu_ver.innerHTML = ver + ' (' + stb.get_image_version() + ')';
        
        var mm_menu_hor = create_block_element('mm_menu_hor', this.dom_obj);
        
        var cell;
        
        for (var i=0; i<=2; i++){
            
            var style_class = 'menu_hor_cell';
            
            if (i == 1){
                style_class = 'menu_hor_cell_act';
            }
            
            cell = create_block_element(style_class, mm_menu_hor);
            
            this.cells.push({"img_dom_obj" : create_block_element('menu_hor_cell_img', cell), "title_dom_obj" : create_inline_element('', cell)});
        }
        
        create_block_element('mm_hor_left', this.dom_obj);
        create_block_element('mm_hor_right', this.dom_obj);
        
        this.empty_vert_menu = create_block_element('mm_menu_vert_empty', this.dom_obj);
        this.empty_vert_menu.hide();
        
        this.mm_menu_vert = create_block_element('mm_menu_vert', this.dom_obj);
        this.vert_body = create_block_element('mm_menu_vert_body', this.mm_menu_vert);
        
        //this.vert_container = create_block_element('mm_vert_container', this.vert_body);
        
        create_block_element('mm_menu_vert_bottom', this.mm_menu_vert);
        
        
        //this.build();
        
        main_menu.hide();

        this.exit_comfirm = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('exit_title')});
        this.exit_comfirm.getTextDomObj().style.textAlign = "center";
        this.exit_comfirm.enableOnExitClose();

        var scope = this;

        this.exit_comfirm.addItem(new ModalFormButton(
            {
                "value" : get_word("cancel_btn"),
                "onclick" : function(){
                    scope.exit_comfirm.hide();
                }
            }
        ));

        this.exit_comfirm.addItem(new ModalFormButton(
            {
                "value" : get_word("yes_btn"),
                "onclick" : function(){
                    scope.exit_comfirm.hide();
                    stb.LoadURL(window.referrer);
                }
            }
        ));
    },
    
    build : function(){
        _debug('main_menu.build');
        
        this.clear_menu();
        
        var sub_menu_item;
        
        for(var i=0; i<this.map.length; i++){
            
            if (i<3){
                this.cells[i].img_dom_obj.style.background = 'url('+this.map[i].img+')';
                this.cells[i].title_dom_obj.innerHTML = this.map[i].title;
            }
            
            this.map[i].sub_obj = create_block_element('mm_vert_container', this.vert_body);
            this.map[i].sub_items_dom_obj = [];
            
            var total_items = 6;
            
            if (this.map[i].sub.length < 6){
                total_items = this.map[i].sub.length;
            }
            
            this.map[i].active_sub = Math.ceil(total_items/2)-1;
            
            for (var j=0; j<this.map[i].sub.length; j++){
                if (j<6){
                    sub_menu_item = create_block_element('', this.map[i].sub_obj);
                    sub_menu_item.innerHTML = this.map[i].sub[j].title;
                    this.map[i].sub_items_dom_obj.push(sub_menu_item);
                    
                    if (j == this.map[i].active_sub){
                        sub_menu_item.setClass('active');
                    }
                }
            }
            
            if (i != 1){
                this.map[i].sub_obj.hide();
                
                if (this.map[i].sub.length > 0){
                    this.sub_menu_show();
                }else{
                    this.sub_menu_hide();
                }
            }
        }
        
        if(this.map[1]){
            this.active_sub = this.map[1].active_sub;
        }
        
        _debug('this.active_sub', this.active_sub);
    },
    
    render : function(){
        _debug('main_menu.render');
        
        for (var i=0; i<=2; i++){
            this.cells[i].img_dom_obj.style.background = 'url('+this.map[i].img+')';
            this.cells[i].title_dom_obj.innerHTML = this.map[i].title;
        }
        
        var self = this;
        
        if(this.hide_sub_t){
            window.clearTimeout(this.hide_sub_t);
        }
        
        //this.hide_sub_t = window.setTimeout(function(){self.map[1].sub_obj.show()}, 500);
        this.hide_sub_t = window.setTimeout(function(){self.sub_menu_show()}, 500);
        
    },
    
    sub_menu_hide : function(){
        
        if(this.map[1] && this.map[1].sub_obj && this.map[1].sub_obj.hide){
            this.map[1].sub_obj.hide();
            this.mm_menu_vert.hide();
            this.empty_vert_menu.show();
        }
    },
    
    sub_menu_show : function(){
        if (this.map.hasOwnProperty(1) && this.map[1].sub.length > 0){
            this.map[1] && this.map[1].sub_obj && this.map[1].sub_obj.show();
            this.active_sub = this.map[1].active_sub;
            this.mm_menu_vert.show();
            this.empty_vert_menu.hide();
        }
    },
    
    hshift : function(dir){
        
        //this.map[1].sub_obj.hide();
        this.sub_menu_hide();
        
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
    
    page_shift : function(dir){
        if (dir){
            if (this.map[1].sub.length > 0){
                for (var i=0; i<=6; i++){
                    this.map[1].sub.push(this.map[1].sub.shift());
                }
            }
        }else{
            var menu_length = this.map[1].sub.length;
            if (menu_length > 0){
                for (i=0; i<=6; i++){
                    this.map[1].sub.unshift(this.map[1].sub[menu_length-1]);
                    this.map[1].sub.splice(menu_length, 1);
                }
            }
        }
        this.render_sub();
    },
    
    render_sub : function(){
        
        /*if (this.map[1].sub.length < 6){
            var total_items = this.map[1].sub.length;
        }else{
            var total_items = 6;
        }
        
        this.active_sub = Math.ceil(total_items/2)-1;*/
        
        //this.active_sub = this.map[1].active_sub;
        
        _debug('this.active_sub', this.active_sub);
        
        for (var i=0; i<this.map[1].sub.length; i++){
            
            if (i<6){
                this.map[1].sub_items_dom_obj[i].innerHTML = this.map[1].sub[i].title;
            }
            
            /*if (i == this.active_sub){
                this.map[1].sub_items_dom_obj[i].setClass('active');
            }*/
        }
    },
    
    action : function(){
        _debug('main_menu.action');
        
        _debug('this.active_sub', this.active_sub);
        
        if (stb.is_restricted_module(this.map[1].module)){
            stb.notice.show(get_word('msg_service_off'));
            return;
        }
        
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
        
        try{
            for (var i=0; i<=2; i++){
                this.cells[i].img_dom_obj.style.background = '';
                this.cells[i].title_dom_obj.innerHTML = '';
            }
            
            var length = this.vert_body.childNodes.length;
            
            for(i=0; i<length; i++){
                this.vert_body.removeChild(this.vert_body.lastChild);
            }
        }catch(e){
            _debug(e);
        }
        
        _debug('end clear_menu');
    },
    
    add : function(title, sub, img, cmd, module){
        
        cmd = cmd || '';
        
        sub = sub || [];
        
        img = img || '';
        
        if (sub.length > 0){
            for (var k=1; k<3; k++){
                sub.unshift(sub.pop());
            }
        }
        
        this.map.push(
            {
                "title"    : title,
                "img"      : 'template/' + loader.template + '/i' + resolution_prefix + '/' + img,
                "cmd"      : cmd,
                "sub"      : sub,
                "module"   : module
            }
        );
        
        this.build();
    }
};

main_menu.init();

loader.next();