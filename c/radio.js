/**
 * Radio modile.
 */

(function(){
    
    /* Radio */
    function radio_constructor(){
        
        this.layer_name = 'radio';
        
        this.row_blocks = ['playing', 'paused', 'number', 'name'];
        
        this.load_params = {
            'type'   : 'radio',
            'action' : 'get_ordered_list'
        };

        this.map = [];

        this.superclass = ListLayer.prototype;

        this.init = function() {

            _debug('radio.init');

            this.superclass.init.call(this);

            var self = this;

            stb.player.addCustomEventListener("radiostart", function(item){
                _debug('radio.radiostart', item);

                if (self.on && !self.data_items[0].is_track && !item.is_track){

                    var cur_idx = stb.player.radio_idx || 0;

                    _debug('cur_idx', cur_idx);

                    if (cur_idx >= 0){
                        self.data_items[cur_idx].playing = 0;
                        self.map[cur_idx].playing_block.hide();
                    }

                    _debug('item.cmd', item.cmd);

                    var idx = self.data_items.getIdxByVal("id", item.id);

                    if (idx == -1){
                        return;
                    }

                    _debug('idx', idx);

                    self.data_items[idx].playing = 1;
                    self.map[idx].playing_block.show();

                    self.data_items[cur_idx].paused = 0;
                    self.map[cur_idx].paused_block.hide();

                    if (self.cur_row == idx){
                        self.active_row.playing_block.show();
                        self.active_row.paused_block.hide();
                    }

                    stb.player.radio_idx = idx;

                    self.set_active_row(self.cur_row);
                }
            });

            stb.player.addCustomEventListener("radiostop", function(item){
                _debug('radio.radiostop', item);

                if (self.on && !self.data_items[0].is_track && !item.is_track){

                    var cur_idx = stb.player.radio_idx || -1;

                    _debug('cur_idx', cur_idx);

                    if (cur_idx >= 0){
                        self.data_items[cur_idx].playing = 0;
                        self.map[cur_idx].playing_block.hide();

                        self.data_items[cur_idx].paused = 0;
                        self.map[cur_idx].paused_block.hide();

                        if (self.cur_row == cur_idx){
                            self.active_row.playing_block.hide();
                            self.active_row.paused_block.hide();
                        }
                    }

                    self.set_active_row(self.cur_row);
                }
            });

            stb.player.addCustomEventListener("radiopause", function(item){
                _debug('radio.radiopause', item);

                if (self.on && !self.data_items[0].is_track && !item.is_track){

                    var cur_idx = self.data_items.getIdxByVal("id", item.id);

                    _debug('cur_idx', cur_idx);

                    if (cur_idx >= 0){
                        self.data_items[cur_idx].playing = 0;
                        self.map[cur_idx].playing_block.hide();

                        self.data_items[cur_idx].paused = 1;
                        self.map[cur_idx].paused_block.show();

                        if (self.cur_row == cur_idx){
                            self.active_row.playing_block.hide();
                            self.active_row.paused_block.show();
                        }
                    }
                }
            });

            stb.player.addCustomEventListener("radiocontinue", function(item){
                _debug('radio.radiocontinue', item);

                if (self.on && !self.data_items[0].is_track && !item.is_track){

                    var cur_idx = self.data_items.getIdxByVal("id", item.id);

                    _debug('cur_idx', cur_idx);

                    if (cur_idx >= 0){
                        self.data_items[cur_idx].paused = 0;
                        self.map[cur_idx].paused_block.hide();

                        self.data_items[cur_idx].playing = 1;
                        self.map[cur_idx].playing_block.show();

                        if (self.cur_row == cur_idx){
                            self.active_row.paused_block.hide();
                            self.active_row.playing_block.show();
                        }
                    }
                }
            });
        };

        this.show = function(){
            _debug('radio.show');
            this.superclass.show.call(this);
        };

        this.hide = function(do_not_reset){
            _debug('radio.hide');
            
            this.superclass.hide.call(this, do_not_reset);
            /*stb.player.stop();*/
            this.update_header_path([{"alias" : "playing", "item" : "*"}]);
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.play.bind(key.OK, this);

            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this).bind(key.MENU, this);
        };
        
        this.play = function(){
            _debug('radio.play');
            
            this.update_header_path([{"alias" : "playing", "item" : this.data_items[this.cur_row].name}]);
            
            stb.player.stop();
            stb.player.need_show_info = 1;
            if (this.data_items) {
                stb.player.playlist = this.data_items;
            }
            stb.player.play(this.data_items[this.cur_row]);
            stb.player.cur_media_item.radio = true;
        };

        this.set_active_row = function(num){

            _debug('radio.set_active_row', num);

            this.superclass.set_active_row.call(this, num);

            if (!this.data_items[this.cur_row]) {
                _debug("next step row - ", this.cur_row);
                return;
            }
            _debug('this.data_items[this.cur_row].cmd', this.data_items[this.cur_row].cmd);
            _debug('stb.player.on', stb.player.on);
            _debug('stb.player.cur_media_item', stb.player.cur_media_item);
            _debug('stb.player.file_type', stb.player.file_type);

            if (stb.player.cur_media_item && this.data_items[this.cur_row].id == stb.player.cur_media_item.id && stb.player.on && stb.player.media_type == 'stream'){

                this.active_row['row'].setAttribute("status", "playing");
                this.active_row['row'].addClass("playing");

            }else{

                this.active_row['row'].setAttribute("status", "");
                this.active_row['row'].removeClass("playing");

            }

            if (num==0 && stb.player.cur_media_item.radio == true && typeof (stb.player.radio_idx) != 'undefined') {
                var idx = stb.player.radio_idx;
                this.data_items[idx].playing = 1;
                this.map[idx].playing_block.show();

                this.data_items[idx].paused = 0;
                this.map[idx].paused_block.hide();

                if (this.cur_row == idx) {
                    this.active_row.playing_block.show();
                    this.active_row.paused_block.hide();
                }
            }

        };
    }
    
    radio_constructor.prototype = new ListLayer();
    
    var radio = new radio_constructor();
    
    radio.bind();
    radio.init();

    if (single_module != 'radio'){
        radio.init_left_ear(word['ears_back']);
    }
    
    radio.init_header_path(word['radio_title']);
    
    radio.hide();
    module.radio = radio;
    
    /* END RADIO */
    
    /* Integrate karaoke in main menu */

    main_menu.add(word['radio_title'], [], 'mm_ico_radio.png', function(){
        main_menu.hide();
        module.radio.show();
    },
    module.radio);

})();

loader.next();