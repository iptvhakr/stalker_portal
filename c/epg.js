/**
 * EPG modile.
 */

(function(){

    function epg_constructor(){
        
        this.layer_name = 'epg';
        
        this.total_rows  = 10;
        
        this.active_row_offset = 0;
        
        this.row_blocks  = ['number', 'name', 'epg_container'];
        
        this.load_params = {
            "type"   : "epg",
            "action" : "get_data_table"
        };
        
        this.ch_id = 0;
        
        this.parent = {};
        
        this.superclass = ListLayer.prototype;
        
        this.minutes_offset = 0;
        this.minutes_step = 90;
        
        this.cur_cell_col = 0;
        
        this.horiz_dir = 1;
        
        this.time_marks = [];
        
        this.marks_map = [];
        
        this.live_line = {
            
            on : false,
            
            init : function(parent){
                _debug('epg.live_line.init');
                
                this.parent = parent;
                
                var container = create_block_element('live_line_container', this.parent.dom_obj);
                this.dom_obj = create_block_element('live_line', container);
                
                this.hide();
            },
            
            show : function(){
                _debug('epg.live_line.show');
                
                this.dom_obj.show();
                this.on = true;
            },
            
            hide : function(){
                _debug('epg.live_line.hide');
                
                this.dom_obj.hide();
                this.on = false;
            },
            
            check_time : function(){
                _debug('epg.live_line.check_time');
                
                if (!this.parent.result){
                    return;
                }
                
                var now = Math.ceil((new Date().getTime())/1000);
                var from = parseInt(this.parent.result.from_ts);
                var to   = parseInt(this.parent.result.to_ts);
                
                _debug('now', now);
                _debug('from', from);
                _debug('to', to);
                
                if (now > from && now < to){
                    this.show();
                    var minute = Math.floor((now - from)/60);
                    this.set_pos(minute);
                }else{
                    this.hide();
                }
            },
            
            set_pos : function(minute){
                _debug('epg.live_line.set_pos');
                
                /* 447/90 = 5px */
                var offset = 5*minute;
                
                _debug('offset', offset);
                
                this.dom_obj.moveX(offset);
            },
            
            start : function(){
                _debug('epg.live_line.start');
                
                this.check_time();
                
                var self = this;
                
                this.timer = window.setTimeout(function(){self.start()}, 10000);
            },
            
            stop : function(){
                _debug('epg.live_line.stop');
                
                window.clearTimeout(this.timer);
            },
        };
        
        this.show = function(){
            _debug('epg.show');
            
            this.cur_page = 0;
            
            this.parent.on = false;
            
            this.set_date_period();
            
            this.superclass.show.call(this, false);
        };
        
        this.hide = function(do_not_reset){
            _debug('epg.hide');
            
            this.superclass.hide.call(this, do_not_reset);
            
            this.live_line.stop();
        };
        
        this.init = function(){
            _debug('epg.init');
            
            this.superclass.init.call(this);
            
            this.program_info = create_block_element('program_info', this.dom_obj);
            
            this.on_date = create_block_element('on_date', this.dom_obj);
            
            var mark1 = create_block_element('time_mark', this.dom_obj);
            mark1.style.left = '230px';
            this.time_marks.push(mark1);
            
            var mark2 = create_block_element('time_mark', this.dom_obj);
            mark2.style.left = '380px';
            this.time_marks.push(mark2);
            
            var mark3 = create_block_element('time_mark', this.dom_obj);
            mark3.style.left = '530px';
            this.time_marks.push(mark3);
            
            var mark4 = create_block_element('time_mark', this.dom_obj);
            mark4.style.right = '38px';
            this.time_marks.push(mark4);
        };
        
        this.reset = function(){
            _debug('epg.reset');
            
            this.minutes_offset = 0;
            
            this.superclass.reset.call(this);
        };
        
        this.bind = function(){
            
            this.shift_row.bind(key.UP, this, -1);
            this.shift_row.bind(key.DOWN, this, 1);
            
            this.shift_page.bind(key.PAGE_PREV, this, -1);
            this.shift_page.bind(key.PAGE_NEXT, this, 1);
            
            (function(){
                this.hide();
                this.parent.hide();
                main_menu.show();
            }).bind(key.MENU, this);
            
            (function(){
                this.parent.load_params['from_ch_id'] = this.data_items[this.cur_row].ch_id;
                this.parent.show(true);
                this.hide();
            }).bind(key.EXIT, this).bind(key.OK, this);
            
            this.horizontal_shift.bind(key.VOL_UP, this, 1);
            this.horizontal_shift.bind(key.VOL_DOWN, this, -1);
            
            this.horizontal_cell_shift.bind(key.RIGHT, this, 1);
            this.horizontal_cell_shift.bind(key.LEFT, this, -1);
        };
        
        this.set_date_period = function(){
            _debug('epg.set_date_period');
            
            var date = new Date();

            var year    = date.getFullYear();
            var month   = date.getMonth();
            var day     = date.getDate();
            var hours   = date.getHours();
            var minutes = date.getMinutes();
            
            if (minutes<30){
                minutes = 0;
            }else{
                minutes = 30;
            }
            
            minutes += this.minutes_offset;
            
            _debug('date', year, month, day, hours, minutes);
            
            var date_from = new Date(year, month, day, hours, minutes);
            var from = date_from.getFullYear()+'-'+this.format_date(date_from.getMonth()+1)+'-'+this.format_date(date_from.getDate())+' '+this.format_date(date_from.getHours())+':'+this.format_date(date_from.getMinutes())+':00';
            _debug('from', from);
            this.load_params['from'] = from;
            
            var date_to = new Date(year, month, day, hours, minutes+90);
            var to = date_to.getFullYear()+'-'+this.format_date(date_to.getMonth()+1)+'-'+this.format_date(date_to.getDate())+' '+this.format_date(date_to.getHours())+':'+this.format_date(date_to.getMinutes())+':00';
            _debug('to', to);
            this.load_params['to'] = to;
            
        };
        
        this.load_data = function(){
            _debug('epg.load_data');
            
            _debug('this.ch_id', this.ch_id);
            
            this.set_passive_cell();
            this.set_passive_row();
            
            this.marks_map = [];
            
            this.live_line.stop();
            
            this.load_params['ch_id'] = this.ch_id;
            
            this.superclass.load_data.call(this);
        }
        
        this.fill_list = function(data){
            _debug('epg.fill_list');
            
            this.superclass.fill_list.call(this, data);
            
            for (var i=0; i<data.length; i++){
                
                this.post_handling_epg_block(this.map[i], data[i].epg);
            }
            
            this.set_marks(this.result.time_marks);
            
            this.set_active_cell();
            
            this.live_line.start();
        };
        
        this.set_marks = function(marks){
            _debug('epg.set_marks', marks);
            
            for (var i=0; i<this.time_marks.length; i++){
                
                if (i < (this.time_marks.length - 1)){
                    this.time_marks[i].innerHTML = '|' + marks[i];
                }else{
                    this.time_marks[i].innerHTML = marks[i] + '|';
                }
            }
        };
        
        this.set_active_row = function(num){
            _debug('epg.set_active_row', num);
            
            this.set_passive_cell();
            
            this.set_passive_row();
            
            this.active_row = this.map[num];
            
            this.active_row.row.setAttribute('active', 'active');
            
            //this.set_passive_cell();
            
            //this.superclass.set_active_row.call(this, num);
            //this.post_handling_epg_block(this.active_row, this.data_items[num].epg, true)
            
            this.ch_id = this.data_items[num].ch_id;
            
            this.set_active_cell();
        }
        
        this.set_passive_row = function(){
            _debug('epg.set_passive_row', this.prev_row);
            
            this.map[this.prev_row].row.setAttribute('active', '');
            this.map[this.cur_row].row.setAttribute('active', '');
        }
        
        this.post_handling_epg_block = function(item, epg, is_active_row){
            _debug('epg.post_handling_block', is_active_row);
            
            var total_container_width = 449;
            
            this.clear_program_container(item, 'epg_container_block');
                
            var epg_length = epg.length;
            
            _debug('epg_length', epg_length);
            
            /* 2px - on each separator */
            var separator_width = (epg_length-1)*2;
            
            var container_width = total_container_width - separator_width;
            
            var total_program_width = 0;
            
            if (is_active_row){
                this.active_row['epg_cell'] = [];
            }
            
            item['epg_cell'] = [];
            
            for (var j=0; j<epg_length; j++){
                
                var block = create_block_element('program', item['epg_container_block']);
                
                var program_width = Math.floor(container_width * epg[j]['display_duration'] / 5400); // 90m = 5400s
                _debug('program_width', program_width);
                
                total_program_width += program_width;
                
                if (j == (epg_length-1)){
                    var diff = container_width - total_program_width;
                    program_width += diff;
                }
                
                block.style.width = program_width+'px';
                
                /*if (is_active_row){
                    this.active_row['epg_cell'].push({"cell" : block, "data" : epg[j]});
                }*/
                
                item['epg_cell'].push({"cell" : block, "data" : epg[j]});
                
                if (epg[j]['larr']){
                    create_block_element('larr', block);
                }
                
                if (epg[j]['rarr']){
                    create_block_element('rarr', block);
                }
                
                var txt_block = create_block_element('program_txt', block);
                
                txt_block.innerHTML = epg[j]['name'];
                
                var marks = create_block_element('marks', block);
                
                var mark_memo = create_block_element('mark_memo', marks);
                
                if (epg[j]['mark_memo']){
                    mark_memo.show();
                }else{
                    mark_memo.hide();
                }
                
                var mark_rec = create_block_element('mark_rec', marks);
                
                if (epg[j]['mark_rec']){
                    mark_rec.show();
                }else{
                    mark_rec.hide();
                }
                
                if (!is_active_row){
                    this.marks_map.push({"program_id" : epg[j]['id'], "mark_memo" : mark_memo, "mark_rec" : mark_rec});
                }
                
                if (j < epg_length-1){
                    create_block_element('program_separator', item['epg_container_block']);
                }
            }
        }
        
        this.clear_program_container = function(item, block_name){
            _debug('epg.clear_program_container', item, block_name);
            
            var container = item[block_name];
            
            var length = container.childNodes.length;
            
            for (var i=0; i<length; i++){
                container.removeChild(container.lastChild);
            }
        };
        
        this.format_date = function(param){
            if (param<10){
                return '0'+param
            }
            return param
        };
        
        this.horizontal_shift = function(dir){
            _debug('epg.horizontal_shift', dir);
            
            this.horiz_dir = dir;
            
            if (dir>0){
                this.minutes_offset += this.minutes_step;
            }else{
                this.minutes_offset -= this.minutes_step;
            }
            
            _debug('this.minutes_offset', this.minutes_offset);
            
            this.set_date_period();
            this.load_data();
        };
        
        this.horizontal_cell_shift = function(dir){
            _debug('epg.horizontal_cell_shift', dir);
            
            this.set_passive_cell();
            
            if (dir > 0){
                
                if (this.cur_cell_col < this.data_items[this.cur_row].epg.length - 1){
                    this.cur_cell_col++;
                    this.set_active_cell();
                }else{
                    this.horizontal_shift(1);
                }
                
            }else{
                if (this.cur_cell_col > 0){
                    this.cur_cell_col--;
                    this.set_active_cell();
                }else{
                    this.horizontal_shift(-1);
                }
            }
        };
        
        this.set_active_cell = function(){
            _debug('epg.set_active_cell', this.cur_cell_col);
            
            if (this.horiz_dir > 0){
                this.cur_cell_col = 0;
            //}else if (this.horiz_dir < 0){
            }else if (this.horiz_dir < 0){
                this.cur_cell_col = this.active_row['epg_cell'].length - 1;
            }else{
                
                if (empty(this.active_row['epg_cell'][this.cur_cell_col])){
                    this.cur_cell_col = this.active_row['epg_cell'].length - 1;
                }
            }
            
            _debug('this.horiz_dir', this.horiz_dir);
            _debug('this.cur_cell_col', this.cur_cell_col);
            
            if (!empty(this.active_row['epg_cell']) && !empty(this.active_row['epg_cell'][this.cur_cell_col])){
                
                this.active_row['epg_cell'][this.cur_cell_col].cell.setAttribute('rel', 'active');
            }
            
            this.fill_program_info();
            
            this.horiz_dir = 0;
        };
        
        this.set_passive_cell = function(){
            _debug('epg.set_passive_cell', this.cur_cell_col);
            
            if (!empty(this.active_row['epg_cell']) && !empty(this.active_row['epg_cell'][this.cur_cell_col])){
            
                this.active_row['epg_cell'][this.cur_cell_col].cell.setAttribute('rel', '');
            }
        };
        
        this.init_page_bar = function(){};
        
        this.set_total_items = function(count){
            _debug('epg.set_total_items', count);
        };
        
        this.fill_program_info = function(){
            _debug('epg.fill_program_info');
            
            if (!empty(this.active_row['epg_cell']) && !empty(this.active_row['epg_cell'][this.cur_cell_col])){
                
                this.program_info.innerHTML = '<span class="time">' + this.active_row['epg_cell'][this.cur_cell_col].data['t_time'] + ' - ' + this.active_row['epg_cell'][this.cur_cell_col].data['t_time_to'] +'</span> - ' + this.active_row['epg_cell'][this.cur_cell_col].data['name'];
                
                _debug('on_date', this.active_row['epg_cell'][this.cur_cell_col].data['on_date']);
                this.on_date.innerHTML = this.active_row['epg_cell'][this.cur_cell_col].data['on_date'];
            }else{
                this.program_info.innerHTML = '';
                this.on_date.innerHTML = '';
            }
        }
        
        this.reminder = {
        
            memos : [],
            
            get_list : function(){
                _debug('epg.reminder.get_list');
                
                stb.load(
                    {
                        "type"   : "tvreminder",
                        "action" : "get_all_active"
                    },
                    
                    function (result){
                        
                        if (!result){
                            return;
                        }
                        
                        this.memos = result;
                        
                        var timestamp = (new Date().getTime())/1000;
                        
                        var self = this;
                        
                        for (var i=0; i<this.memos.length; i++){
                            
                            var diff = (this.memos[i].fire_ts - timestamp);
                            
                            if (diff > 0){
                                
                                this.memos[i]['timer'] = window.setTimeout((function(context, memo){
                                    
                                    return function(){
                                        stb.msg.push(function(){
                                            _debug('return show_notification');
                                            return context.show_notification.call(context, memo)
                                        });
                                    }
                                    
                                })(this, this.memos[i]), diff*1000);
                                //})(this, this.memos[i]), 30000);
                                
                            }
                        }
                    },
                    
                    this
                )
            },
            
            add_del : function(){
                _debug('add_del');
                
                var program_id = this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col].id;
                
                _debug('this.memos', this.memos);
                
                var memo_idx = this.memos.getIdxByVal('tv_program_id', program_id);
                
                if (memo_idx !== null){
                    this.del(memo_idx);
                }else{
                    this.add();
                }
            },
            
            add : function(){
                _debug('epg.reminder.add');
                
                var ch_id      = this.parent.data_items[this.parent.cur_row].ch_id;
                var program_id = this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col].id;
                var fire_ts    = this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col].start_timestamp;
                
                _debug('ch_id', ch_id);
                _debug('program_id', program_id);
                _debug('fire_ts', fire_ts);
                
                stb.load(
                    {
                        "type"        : "tvreminder",
                        "action"      : "add",
                        "ch_id"       : ch_id,
                        "program_id"  : program_id
                    },
                    
                    function(result){
                        _debug('epg.reminder.add result', result);
                        
                        if (empty(result)){
                            return;
                        }
                        
                        var memo = result;
                        
                        var timestamp = (new Date().getTime())/1000;
                        
                        var diff = (fire_ts - timestamp);
                            
                        _debug('diff', diff);
                        
                        if (diff > 0){
                            
                            var mark_idx = this.parent.marks_map.getIdxByVal('program_id', program_id);
                        
                            if (mark_idx !== null){
                                this.parent.marks_map[mark_idx].mark_memo.show();
                                
                                this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col].mark_memo = 1;
                                this.parent.set_active_row(this.parent.cur_row);
                            }
                            
                            memo.timer = window.setTimeout((function(context, memo){
                                    
                                return function(){
                                    stb.msg.push(function(){return context.show_notification.call(context, memo)});
                                }
                                
                            })(this, memo), diff*1000);
                            //})(this, memo), 1000);
                            
                            
                            this.memos.push(memo);
                            
                            _debug('this.memos', this.memos);
                        }
                        
                    },
                    
                    this
                )
            },
            
            del : function(memo_idx){
                _debug('epg.reminder.del');
                
                var memo = this.memos[memo_idx];
                
                var program_id = memo.tv_program_id;
                
                stb.load(
                    {
                        "type"        : "tvreminder",
                        "action"      : "del",
                        "program_id"  : program_id
                    },
                    
                    function(result){
                        _debug('epg.reminder.del result', result);
                    },
                    
                    this
                )
                
                var mark_idx = this.parent.marks_map.getIdxByVal('program_id', program_id);
                        
                if (mark_idx !== null){
                    this.parent.marks_map[mark_idx].mark_memo.hide();
                    
                    this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col].mark_memo = 0;
                    this.parent.set_active_row(this.parent.cur_row);
                }
                
                window.clearTimeout(memo.timer);
                
                this.memos.splice(memo_idx, 1);
                
                _debug('this.memos', this.memos);
            },
            
            show_notification : function(memo){
                _debug('epg.reminder.show_notification', memo);
                
                var timestamp = (new Date().getTime())/1000;
                
                var msg = word['epg_memo'] + ' - ' + word['epg_on_ch'] + ' ' + memo['itv_name'] + ' ';
                
                if ((timestamp - memo.fire_ts) > 60){
                    msg += word['epg_on_time'] + ' ' + memo['t_fire_time'] + ' ' + word['epg_started'] + ' ';
                }else{
                    msg += word['epg_now_begins'] + ' ';
                }
                
                msg += memo['program_name'];
                
                msg += '<br>OK - ' + word['epg_goto_ch'];
                
                _debug('msg', msg);
                
                stb.msg.set_confirm_callback(function(){
                    _debug('stb.msg ok callback');
                    
                    var fav_ch_idx = null;
                    
                    if (stb.user.fav_itv_on){
                        fav_ch_idx = stb.player.fav_channels.getIdxByVal('id', parseInt(memo['ch_id']));
                    }
                    
                    _debug('fav_ch_idx', fav_ch_idx);
                    
                    if (fav_ch_idx === null){
                        
                        var ch_idx = stb.player.channels.getIdxByVal('id', parseInt(memo['ch_id']));
                        
                        if (ch_idx !== null){
                            
                            _debug('ch_idx', ch_idx);
                            
                            stb.user.fav_itv_on = 0;
                            
                            stb.player.ch_idx = ch_idx;
                            stb.player.cur_media_item = stb.player.channels[stb.player.ch_idx];
                            stb.player.cur_tv_item = stb.player.channels[stb.player.ch_idx];
                            
                            keydown_observer.emulate_key(key.MENU);
                            keydown_observer.emulate_key(key.EXIT);
                        }
                        
                    }else{
                        
                        stb.player.f_ch_idx = fav_ch_idx;
                        
                        _debug('stb.player.f_ch_idx', stb.player.f_ch_idx);
                        
                        stb.player.cur_media_item = stb.player.fav_channels[stb.player.f_ch_idx];
                        stb.player.cur_tv_item = stb.player.fav_channels[stb.player.f_ch_idx];
                        
                        keydown_observer.emulate_key(key.MENU);
                        keydown_observer.emulate_key(key.EXIT);
                    }
                })
                
                return msg;
            },
        }
    }
    
    epg_constructor.prototype = new ListLayer();
    
    var epg = new epg_constructor();
    
    epg.parent = module.tv;
    
    epg.reminder.parent = epg;
    epg.reminder.get_list();
    
    epg.bind();
    epg.init();
    epg.live_line.init(epg);
    
    epg.init_header_path(word['epg_title']);
    
    epg.init_color_buttons([
        {"label" : word['epg_record'], "cmd" : ''},
        {"label" : word['epg_remind'], "cmd" : function(){epg.reminder.add_del()}},
        {"label" : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', "cmd" : ''},
        {"label" : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', "cmd" : ''}
    ]);
    
    epg.hide();
    
    module.epg = epg;
    
})();

loader.next();