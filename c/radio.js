/**
 * Radio modile.
 */

(function(){
    
    /* Radio */
    function radio_constructor(){
        
        this.layer_name = 'radio';
        
        this.row_blocks = ['number', 'name'];
        
        this.load_params = {
            'type'   : 'radio',
            'action' : 'get_ordered_list'
        };
        
        this.superclass = Layer.prototype;
        
        this.hide = function(do_not_reset){
            _debug('radio.hide');
            
            this.superclass.hide.call(this, do_not_reset);
            stb.player.stop();
            this.update_header_path([{"alias" : "playing", "item" : "*"}]);
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.play.bind(key.OK, this);
            
            (function(){
                
                if (stb.player.on){
                    stb.player.stop();
                    this.update_header_path([{"alias" : "playing", "item" : "*"}]);
                    return;
                }
                
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this);
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.LEFT, this).bind(key.MENU, this);
        };
        
        this.play = function(){
            _debug('radio.play');
            
            this.update_header_path([{"alias" : "playing", "item" : this.data_items[this.cur_row].name}]);
            
            stb.player.need_show_info = 1;
            stb.player.play(this.data_items[this.cur_row]);
        };
    }
    
    radio_constructor.prototype = new Layer();
    
    var radio = new radio_constructor();
    
    radio.bind();
    radio.init();
    
    radio.init_left_ear(word['ears_back']);
    
    radio.init_header_path(word['radio_title']);
    
    radio.hide();
    
    module.radio = radio;
    
    /* END RADIO */
    
    /* Integrate karaoke in main menu */
    
    main_menu.add(word['radio_title'], [], 'i/mm_ico_radio.png', function(){
        main_menu.hide();
        module.radio.show();
    });
    
})();

loader.next();