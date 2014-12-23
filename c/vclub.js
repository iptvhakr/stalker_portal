/**
 * Video Club module.
 */
(function(){
    
    //stb.ajax_loader = 'http://bb2.sandbox/stalker_portal/server/load.php'
    
    /* VCLUB */
    function vclub_constructor(){
        
        this.layer_name = 'vclub';
        
        this.row_blocks  = ['hd', 'sd', 'fav', 'lock', 'low_quality', 'name', 'today', 'yesterday', 'week_and_more'];
        
        this.load_params = {
            'type'   : 'vod',
            'action' : 'get_ordered_list'
        };
        
        this.superclass = ListLayer.prototype;
        
        this.category_alias = '';
        
        this.sort_menu = {};
        
        this.search_box = {};
        
        this.view_menu = {};
        
        this.other_menu = {};
        
        this.row_callback_timer;
        this.row_callback_timeout = 1000;
        
        this.password_input = new password_input({"parent" : this});
        this.password_input.bind();

        this.rent_password_input = new password_input({"parent" : this});
        this.rent_password_input.bind();
        
        this.series_switch = new series_switch({"parent" : this});
        this.series_switch.bind();

        this.storage_switch = new series_switch({"parent" : this});
        this.storage_switch.box_input_format = '{0}';
        this.storage_switch.continuously_box_enable = false;
        this.storage_switch.bind();

        /*this.dialog = new downloads_dialog_constructor();
        this.dialog.hide();*/

        this.init = function(){
            this.superclass.init.call(this);
            this.init_info();
            this.download_exist = new ModalForm({title: get_word("alert_form_title"), text : get_word("identical_download_exist")});
            this.download_exist.getButtonsBlockDomObj().style.textAlign = "center";
            this.download_exist.getTextDomObj().style.textAlign = "center";
            this.download_exist.enableOnExitClose();

            var scope = this;

            this.download_exist.addItem(new ModalFormButton(
                {
                    "value" : get_word("close_btn"),
                    "onclick" : function(){
                        scope.download_exist.hide();
                    }
                }
            ));


            this.rent_confirm = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('rent_movie_text')});
            this.rent_confirm.getTextDomObj().style.textAlign = "center";
            this.rent_confirm.enableOnExitClose();
            this.rent_confirm.addCustomEventListener('show', function(){
                scope.on = false;
            });
            this.rent_confirm.addCustomEventListener('hide', function(){
                scope.on = true;
            });

            this.rent_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.rent_confirm.hide();
                    }
                }
            ));

            this.rent_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("yes_btn"),
                    "onclick" : function(){
                        scope.rent_confirm.hide();

                        scope.rent_password_input.callback = function(){
                            scope.check_price(scope.rent_confirm.video_id);
                        };

                        scope.rent_password_input.show();
                    }
                }
            ));

            this.parent_password_promt = new ModalForm({"title" : get_word('parent_password_title'), "parent" : main_menu});
            this.parent_password_promt.enableOnExitClose();

            this.parent_password_promt.addItem(new ModalFormInput({
                "label" : get_word('password_label'),
                "name" : "parent_password",
                "type" : "password",
                "onchange" : function(){_debug('change'); scope.parent_password_promt.resetStatus()}
            }));

            this.parent_password_promt.addItem(new ModalFormButton(
                {
                    "value" : get_word("ok_btn"),
                    "onclick" : function(){

                        var parent_password = scope.parent_password_promt.getItemByName('parent_password').getValue();

                        _debug('parent_password', parent_password);
                        _debug('stb.user.parent_password', stb.user.parent_password);

                        if (parent_password == stb.user.parent_password){
                            scope.parent_password_promt.hide();
                            scope.parent_password_promt.callback && scope.parent_password_promt.callback();
                        }else{
                            scope.parent_password_promt.setStatus(get_word('parent_password_error'));
                        }
                    }
                }
            ));

            this.price_confirm = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('rent_movie_price_text')});
            this.price_confirm.getTextDomObj().style.textAlign = "center";
            this.price_confirm.enableOnExitClose();
            this.price_confirm.addCustomEventListener('show', function(){
                scope.on = false;
            });
            this.price_confirm.addCustomEventListener('hide', function(){
                scope.on = true;
            });

            this.price_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.price_confirm.hide();
                    }
                }
            ));

            this.price_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("pay_btn"),
                    "onclick" : function(){

                        scope.rent(scope.price_confirm.video_id, scope.price_confirm.price);

                        scope.price_confirm.hide();
                    }
                }
            ));

            this.subscription_message = new ModalForm({"title" : get_word('notice_form_title'), "text" : " "});
            this.subscription_message.getTextDomObj().style.textAlign = "center";
            this.subscription_message.enableOnExitClose();
            this.subscription_message.addCustomEventListener('show', function(){
                scope.on = false;
            });
            this.subscription_message.addCustomEventListener('hide', function(){
                scope.on = true;
            });
            this.subscription_message.addItem(new ModalFormButton(
                {
                    "value" : get_word("ok_btn"),
                    "onclick" : function(){
                        scope.subscription_message.hide();
                    }
                }
            ));


            this.complete_confirm = new ModalForm({"title" : get_word('notice_form_title'), "text" : get_word('service_subscribe_success')});
            this.complete_confirm.enableOnExitClose();
            this.complete_confirm.getTextDomObj().style.textAlign = "center";
            this.complete_confirm.addCustomEventListener('show', function(){
                scope.on = false;
            });
            this.complete_confirm.addCustomEventListener('before_hide', function(){
                scope.on = true;
            });
            this.complete_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("ok_btn"),
                    "onclick" : function(){
                        //scope.on = true;
                        scope.complete_confirm.hide();
                    }
                }
            ));

            this.complete_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("play_btn"),
                    "onclick" : function(){
                        scope.complete_confirm.hide();
                        //scope.on = false;
                        scope.check_for_series(scope.play_url, scope.storage);
                    }
                }
            ));
        };
        
        this.load_genres = function(alias){
            
            alias = alias || '';
            
            _debug('vclub.load_genres', alias);
        
            stb.load(
                {
                    "type"      : "vod",
                    "action"    : "get_genres_by_category_alias",
                    "cat_alias" : alias
                },
                function(result){
                    _debug('callback categories');
    
                    this.sidebar.fill_items("genre", result);
                },
                this
            )
        };
        
        this.load_years = function(category_id){
            _debug('vclub.load_years');
            
            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "get_years",
                    "category" : category_id
                },
                function(result){
                    _debug('callback years');
    
                    this.sidebar.fill_items("years", result);
                },
                this
            )
        };
        
        this.load_abc = function(){
            _debug('vclub.load_abc');
            
            stb.load(
                {
                    "type"   : "vod",
                    "action" : "get_abc"
                },
                function(result){
                    _debug('callback abc');
                    
                    this.sidebar.fill_items("abc", result);
                },
                this
            )
        };
        
        this._show = function(category){
            
            _debug('vclub.show');

            category = category || this.categories[0];
            
            this.load_params['category'] = category.id;

            this.update_header_path([{"alias" : "genre", "item" : category.title}]);
            
            try{
                this.sort_menu.action();
                
                this.superclass.show.call(this);
                
                this.load_abc();
                this.load_genres(category.alias);
                this.load_years(category.id);
            }catch(e){
                _debug(e);
            }
        };
        
        /*this.show = function(category){
            
            _debug('vclub.show');
            
            this.load_params['category'] = category.id;
            
            try{
                this.sort_menu.action();
                
                this.superclass.show.call(this);
                
                this.load_abc();
                this.load_genres(category.alias);
                this.load_years(category.id);
            }catch(e){
                _debug(e);
            }
        };*/
        
        this.hide = function(do_not_reset){
            
            _debug('vclub.hide', do_not_reset);
            
            if(!do_not_reset){
                this.search_box && this.search_box.reset && this.search_box.reset();
            }
            
            this.search_box.on && this.search_box.hide && this.search_box.hide();
            this.sort_menu.on && this.sort_menu.hide && this.sort_menu.hide();
            this.view_menu.on && this.view_menu.hide && this.view_menu.hide();
            this.other_menu.on && this.other_menu.hide && this.other_menu.hide();
            
            this.info.on && this.info.hide && this.info.hide();
            this.password_input.on && this.password_input.hide && this.password_input.hide();
            this.rent_password_input.on && this.rent_password_input.hide && this.rent_password_input.hide();
            this.series_switch.on && this.series_switch.hide && this.series_switch.hide();
            this.storage_switch.on && this.storage_switch.hide && this.storage_switch.hide();

            stb.player.pause && stb.player.pause.on && stb.player.hide_pause();

            if (!do_not_reset && stb.player.on){
                stb.player.stop && stb.player.stop();
            }

            this.clear_short_info();
            
            this.superclass.hide.call(this, do_not_reset);
        };
        
        this.init_sort_menu = function(map, options){
            this.sort_menu = new bottom_menu(this, options);
            this.sort_menu.init(map);
            this.sort_menu.bind();
        };
        
        this.sort_menu_switcher = function(){
            if (this.sort_menu && this.sort_menu.on){
                this.sort_menu.hide();
            }else{
                this.sort_menu.show();
            }
        };
        
        this.init_search_box = function(options){
            _debug('vclub.init_search_box', options);
            
            this.search_box = new search_box(this, options);
            this.search_box.init();
            this.search_box.bind();
        };
        
        this.search_box_switcher = function(){
            if (this.search_box && this.search_box.on){
                this.search_box.hide();
            }else{
                this.sidebar && this.sidebar.full_reset && this.sidebar.full_reset();
                this.search_box.show();
            }
        };
        
        this.init_view_menu = function(map, options){
            this.view_menu = new bottom_menu(this, options);
            this.view_menu.init(map);
            this.view_menu.bind();
        };
        
        this.view_switcher = function(){
            if (this.view_menu && this.view_menu.on){
                this.view_menu.hide();
            }else{
                this.view_menu.show();
            }
        };
        
        this.init_other_menu = function(map, options){
            this.other_menu = new bottom_menu(this, options);
            this.other_menu.init(map);
            this.other_menu.bind();
        };
        
        this.other_switcher = function(){
            if (this.other_menu && this.other_menu.on){
                this.other_menu.hide();
            }else{
                this.other_menu.show();
            }
        };
        
        this.fill_short_info = function(item){
            _debug('fill_short_info');

            if (!item){
                return;
            }
            
            //item.name
            //item.o_name
            //item.year
            //item.director
            //item.screenshot_uri
            //item.genres_str

            var info = '';

            if (item.rating_kinopoisk && stb.profile['kinopoisk_rating']){
                info += '<span>' + get_word('vclub_rating') + ': </span>' + item.rating_kinopoisk + '<br>';
            }

            if (item.rent_info){
                info += get_word('vclub_purchased');

                if (item.rent_info['expires_in']){
                    info += ', '+get_word('vclub_rent_expires_in')+': '+item.rent_info['expires_in'];
                }
                info += '<br>';
            }

            if (item.age){
                info += '<span>' + get_word('vclub_age') + ': </span>' + item.age + '<br>';
            }

            if (item.rating_mpaa){
                info += '<span>' + get_word('vclub_rating_mpaa') + ': </span>' + item.rating_mpaa + '<br>';
            }

            info += '<span>' + word['vclub_genre'] + ': </span>' + item.genres_str;

            if (item.country){
                info += '<br><span>' + word['vclub_country'] + ': </span>' + item.country
            }

            info += '<br><span>' + word['vclub_year'] + ': </span>' + item.year
                + ' <span>' + word['vclub_length'] + ': </span>' + item.time + ' ' + word['vclub_minutes'] + '.<br>'
                + '<span>' + word['vclub_director'] + ': </span>' + item.director;

            this.short_info_box.innerHTML = info;
            this.screenshot_box.innerHTML = '<img src="' + item.screenshot_uri + '">';
        };

        this.clear_short_info = function(){
            _debug('clear_short_info');

            this.short_info_box.innerHTML = '';
            this.screenshot_box.innerHTML = '';
        };
        
        this.init_short_info = function(){
            this.info_box = create_block_element('', this.main_container);
            
            this.short_info_box = create_block_element('vclub_info_box', this.info_box);

            if (!stb.IsEmulator){
                this.short_info_box.addClass('vclub_info_box_mask');
            }

            this.screenshot_box = create_block_element('vclub_screenshot_box', this.info_box);
        };
        
        this.shift_row_callback = function(item){
            
            window.clearTimeout(this.row_callback_timer);
            
            var self = this;
            
            this.row_callback_timer = window.setTimeout(function(){
                
                self.fill_short_info(item);
                
            },
            this.row_callback_timeout);
        };
        
        this.set_middle_container = function(){
            
            this.superclass.set_middle_container.apply(this);
            
            this.fill_short_info(this.data_items[this.cur_row]);
            
        };
        
        this.handling_block = function(data, row_items, block_name){
        
            var passive_blocks = ['today_block', 'yesterday_block', 'week_and_more_block'];
            var active_blocks  = ['active_today_block', 'active_yesterday_block', 'active_week_and_more_block'];
            
            var block_obj = row_items[block_name+'_block'];
        
            if (data === 1){
                if (block_obj.isHidden()){
                    block_obj.show();
                }
            }else if (data === 0 || typeof(data) == "undefined"){
                if (!block_obj.isHidden()){
                    block_obj.hide();

                    /*var reset_margin = !passive_blocks.splice(0, passive_blocks.indexOf(block_name)).reduce(function(previousValue, currentValue, index, array){
                        return previousValue || !row_items[currentValue].isHidden();
                    }, false);

                    _debug('block_name', block_name);
                    _debug('reset_margin', reset_margin);*/

                    if ((passive_blocks.indexOf(block_obj.className) >= 0 && reset_margin)){

                        var reset_margin = !passive_blocks.splice(0, passive_blocks.indexOf(block_name)).reduce(function(previousValue, currentValue, index, array){
                            return previousValue || !row_items[currentValue].isHidden();
                        }, false);

                        _debug('block_name', block_name);
                        _debug('reset_margin', reset_margin);

                        if (reset_margin){
                            row_items['name_block'].style.marginRight = '';
                        }
                    }else if (active_blocks.indexOf(block_obj.className) >= 0){

                        /*reset_margin = !active_blocks.splice(0, active_blocks.indexOf(block_name)).reduce(function(previousValue, currentValue, index, array){
                            return previousValue || !row_items[currentValue].isHidden();
                        }, false);

                        _debug('active_blocks', active_blocks);
                        _debug('reset_margin', reset_margin);*/

                        row_items['name_block'].style.marginRight = (screen.height == 720) ? '229px' : '130px';
                    }else if (passive_blocks.indexOf(block_obj.className) == -1){
                        row_items['name_block'].style.marginRight = '';
                    }
                }
            }else{
                if (block_obj.isHidden()){
                    block_obj.show();
                }
                /*
                    if (passive_blocks.indexOf(block_obj.className) >= 0){
                        row_items['name_block'].style.marginRight = '103px';
                    }else if (active_blocks.indexOf(block_obj.className) >= 0){
                        row_items['name_block'].style.marginRight = '130px';
                    }else if (passive_blocks.indexOf(block_obj.className) == -1){
                        row_items['name_block'].style.marginRight = '';
                    }
                }else{
                    if (passive_blocks.indexOf(block_obj.className) >= 0){
                        row_items['name_block'].style.marginRight = '103px';
                    }else if (active_blocks.indexOf(block_obj.className) >= 0){
                        row_items['name_block'].style.marginRight = '130px';
                    }else if (passive_blocks.indexOf(block_obj.className) == -1){
                        row_items['name_block'].style.marginRight = '';
                    }
                }*/

                if (passive_blocks.indexOf(block_obj.className) >= 0){
                    row_items['name_block'].style.marginRight = (screen.height == 720) ? '200px' : '103px';
                }else if (active_blocks.indexOf(block_obj.className) >= 0){
                    row_items['name_block'].style.marginRight = (screen.height == 720) ? '229px' : '130px';
                }else if (passive_blocks.indexOf(block_obj.className) == -1){
                    row_items['name_block'].style.marginRight = '';
                }

                block_obj.innerHTML = data;
            }
        };
        
        this.init_info = function(){
            this.info = new vclub_info(this);
            this.info.init();
            this.info.bind();

            this.info.color_buttons = new ColorButtonsBar([
                {"label" : word['downloads_download'], "cmd" : function(){
                    if (module.downloads){
                        this.action.call(this, false);
                    }
                }},
                {"label" : '', "cmd" : ''},
                {"label" : '', "cmd" : ''},
                {"label" : '', "cmd" : ''}
            ], this.info.dom_obj, undefined);

            this.info.color_buttons.bind.call(this.info);
            
            this.full_info_switch.bind(key.RIGHT, this).bind(key.INFO, this);

            /*var color_buttons = this.color_buttons.buttons_bar.cloneNode(true);
            
            color_buttons.addClass('disabled_all_buttons');
            
            this.info.dom_obj.appendChild(color_buttons);*/
        };
        
        this.full_info_switch = function(){
            _debug('full_info_switch');
            
            if (this.info && this.info.on){
                this.on = true;
                this.info.hide();
            }else{
                this.on = false;
                this.info.show(this.data_items[this.cur_row]);
            }
        };
        
        this.bind = function(){
            this.superclass.bind.apply(this);
            
            this.check_for_pass.bind(key.OK, this, true);
            this.check_for_storage_selection.bind(key.PLAY, this, true);
            this.check_for_pass.bind(key.REC, this, false);

            (function(){

                if (single_module == this.layer_name){
                    if (window.referrer){
                        window.location = window.referrer;
                    }
                    return;
                }

                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this).bind(key.MENU, this);

            this.load_data.bind(key.REFRESH, this);
        };

        this.check_for_storage_selection = function(play_url){
            _debug('vclub.check_for_storage_selection', play_url);

            _debug('stb.user.is_moderator', stb.user.is_moderator);

            var self = this;

            if (!stb.user.is_moderator || this.data_items[this.cur_row].rtsp_url){
                this.check_for_pass(play_url);
                return;
            }

            stb.load(
                {
                    "type"     : "video_master",
                    "action"   : "get_storages_for_video",
                    "video_id" : this.data_items[this.cur_row].id
                },
                function(result){
                    _debug('get_storages_for_video result', result);

                    if (!result || !result.length || result.length == 0){
                        stb.notice.show(get_word('player_file_missing'));
                        return;
                    }

                    self.storage_switch.callback = function(storage){
                        _debug('storage', storage);
                        self.check_for_pass.call(self, play_url, storage);
                    };

                    self.storage_switch.show(result, result[0]);
                },
                this
            );
        };

        this.check_price = function(video_id){
            _debug('vclub.check_price', video_id);

            stb.load({
                    "type"     : "account",
                    "action"   : "check_video_price",
                    "video_id" : video_id
                },

                function(result){
                    _debug('on check_price', result);

                    if (!result){
                        this.subscription_message.show(get_word('service_subscribe_server_error'));
                    }else if (result.hasOwnProperty('message')){
                        this.subscription_message.show(result['message']);
                    }else if (result.result === '0' || result.result === 0){
                        this.rent(video_id, 0);
                    }else if (result.result !== false){
                        this.price_confirm.price = this.price_confirm.price_str = result.result;

                        if (!/[^0-9\.,]/.test(this.price_confirm.price)){
                            this.price_confirm.price_str = this.price_confirm.price_str + get_word('package_price_measurement');
                        }

                        this.price_confirm.video_id = video_id;
                        _debug('this.price_confirm.video_id', this.price_confirm.video_id);

                        var msg = get_word('rent_movie_price_text').format(this.price_confirm.price_str);

                        if (result.hasOwnProperty('rent_duration') && result.rent_duration != 0){
                            msg += '<br>'+get_word('rent_duration_text').format(result.rent_duration);
                        }

                        this.price_confirm.show(msg);
                    }
                },

                this
            );
        };

        this.rent = function(video_id, price){
            _debug('video.rent', video_id, price);

            stb.load({
                    "type"   : "account",
                    "action" : "rent_video",
                    "video_id" : video_id,
                    "price"    : price
                },

                function(result){
                    _debug('on do_subscribe', result);

                    if (!result || result.hasOwnProperty('result') && result.result === 0){
                        this.subscription_message.show(get_word('service_subscribe_server_error'));
                    }else if (result.hasOwnProperty('message')){
                        this.subscription_message.show(result['message']);
                    }else if (result.result > 0){
                        this.complete_confirm.show(get_word('service_subscribe_success'));

                        this.cur_item['rent_info'] = result.rent_info;
                        this.cur_item['open'] = 1;

                        if (this.cur_item['id'] == this.data_items[this.cur_row]['id']){
                            this.set_active_row(this.cur_row);
                            this.map[this.cur_row]['row'].removeClass('close');
                        }

                    }else{
                        this.complete_confirm.show(get_word('service_subscribe_fail'));
                    }
                },

                this
            );
        };
        
        this.check_for_pass = function(play_url, storage){
            _debug('vclub.check_for_play', play_url, storage);
            
            _debug('lock', this.data_items[this.cur_row].lock);

            _debug('this.load_params[category]', this.load_params['category']);

            if (this.load_params['category'] == 'coming_soon'){
                stb.notice.show(get_word('coming_soon_video'));
                return;
            }

            var self = this;

            if (this.data_items[this.cur_row].for_rent && !this.data_items[this.cur_row].hasOwnProperty('rent_info')){

                self.rent_confirm.video_id = self.data_items[self.cur_row].id;
                self.rent_confirm.show();

                this.play_url = play_url;
                this.storage  = storage;
                this.cur_item = this.data_items[this.cur_row];

            }else if (this.data_items[this.cur_row].lock){
                
                this.password_input.callback = function(){
                    self.check_for_series(play_url, storage);
                };
                
                this.password_input.show();
            }else{
                this.check_for_series(play_url, storage);
            }
        };
        
        this.check_for_series = function(play_url, storage){
            _debug('vclub.check_for_series', play_url, storage);


            if (this.data_items[this.cur_row].series.length > 0){
                
                var self = this;
                
                this.series_switch.callback = function(series){
                    _debug('series', series);
                    self.data_items[self.cur_row].cur_series = series;
                    if (play_url){
                        self.play(play_url, storage);
                    }else{
                        self.add_download.call(self, self.data_items[self.cur_row]);
                    }
                };
                
                this.series_switch.show(this.data_items[this.cur_row].series, this.data_items[this.cur_row].cur_series);
            }else{
                if (play_url){
                    this.play(play_url, storage);
                }else{
                    this.add_download.call(this, this.data_items[this.cur_row]);
                }
            }
        };

        this.get_link = function(video_cmd, episode, callback){

            if (video_cmd.indexOf('://') < 0){

                stb.player.on_create_link = function(result){
                    _debug('vclub.on_create_link', result);

                    if (result.cmd){
                        if (match = /[\s]([^\s]*)$/.exec(result.cmd)){
                            result.cmd = match[1];
                        }
                    }
                    callback && callback(result.cmd);
                }

            }else{
                callback(video_cmd);
                return;
            }

            stb.player.create_link('vod', video_cmd, episode, '');
        };
        
        this.play = function(play_url, storage, callback){
            _debug('vclub.play', play_url);
            
            var self = this;
            
            _debug('cmd', this.data_items[this.cur_row].cmd);
            _debug('indexOf', this.data_items[this.cur_row].cmd.indexOf('://'));

            if (this.data_items[this.cur_row].cmd.indexOf('://') < 0){
            
                stb.player.on_create_link = function(result){
                    _debug('vclub.on_create_link', result);

                    if (!play_url && callback){
                        if (result.cmd){
                            if (match = /[\s]([^\s]*)$/.exec(result.cmd)){
                                result.cmd = match[1];
                            }
                        }
                        callback && callback(result);
                    }
                    
                    if (result.error == 'limit'){
                        stb.notice.show(word['player_limit_notice']);
                    }else if(result.error == 'nothing_to_play'){
                        stb.notice.show(word['player_file_missing']);
                    }else if(result.error == 'link_fault'){
                        stb.notice.show(word['player_server_error']);
                    }else if(result.error == 'access_denied'){
                        stb.notice.show(word['player_access_denied']);
                    }else{

                        var match;
                        if (match = /[\s]([^\s]*)$/.exec(result.cmd)){
                            var url = match[1];
                        }else{
                            url = result.cmd;
                        }
                        _debug('url: ', url);

                        if (result.hasOwnProperty('cmd') && result.cmd && result.cmd.indexOf('://') != -1 && module.downloads && module.downloads.identical_download_exist(url)){
                            _debug('identical_download_exist!');

                            self.download_exist.show();

                            return;
                        }

                        if (play_url){

                            if (self.info.on){
                                self.info.hide();
                            }

                            self.hide(true);

                            stb.player.on_stop = (function(player){return function(){
                                _debug('player.on_stop');
                                if (result.hasOwnProperty('cmd')){
                                    player.delete_link(result.cmd);
                                }else if (result.length && result[1].cmd){
                                    player.delete_link(result[1].cmd);
                                }
                            }})(stb.player);

                            stb.player.prev_layer = self;
                            stb.player.need_show_info = 1;

                            if (result.length && result.length == 2){
                                stb.player.need_show_info = 0;

                                if (result[1].hasOwnProperty('subtitles')){

                                    stb.player.cur_media_item.subtitles = result[1].subtitles.map(function(item, idx){
                                        item.pid  = 'external_'+idx;
                                        item.lang = [item.lang, ''];
                                        return item;
                                    });
                                }

                                stb.player.cur_media_item.cmd = result[0].cmd;
                                stb.player.cur_media_item.playlist = [result[0].cmd, result[1].cmd];
                                stb.player.cur_media_item.keep_original_name = true;
                                stb.player.cur_media_item.ad_must_watch = result[0].ad_must_watch;
                                stb.player.cur_media_item.show_osd = true;
                                stb.player.cur_media_item.media_type = 'vclub_ad';
                                stb.player.cur_media_item.ad_id = result[0].ad_id;

                                stb.key_lock = true;

                                stb.player.play_now(result[0].cmd);
                                stb.player.ad_indication.show();
                                return;

                            }else if (result.hasOwnProperty('subtitles')){

                                stb.player.cur_media_item.subtitles = result.subtitles.map(function(item, idx){
                                    item.pid  = 'external_'+idx;
                                    item.lang = [item.lang, ''];
                                    return item;
                                });
                            }

                            stb.player.play_now(result.cmd);
                        }else{
                            //callback && callback(result.cmd);
                            self.add_download.call(self, self.data_items[self.cur_row], url);
                        }
                    }
                }
            }else{

                if (play_url){
                    if (this.info.on){
                        this.info.hide();
                    }

                    this.hide(true);

                    stb.player.prev_layer = self;
                    stb.player.need_show_info = 1;
                    //stb.player.play(this.data_items[this.cur_row]);
                }else{
                    var url = /[\s]([^\s]*)$/.exec(this.data_items[this.cur_row].cmd)[1];
                    _debug('url: ', url);

                    self.add_download.call(self, self.data_items[self.cur_row], url);

                    return;
                }
            }

            var played_item = this.data_items[this.cur_row].clone();

            if (storage){
                played_item.forced_storage = storage;
            }

            stb.player.play(played_item);
        };

        this.add_download = function(item, url){
            _debug('vclub.add_download', item);
            
            _debug('path: ', this.data_items[this.cur_row].path);
            _debug('url', url);

            if (this.data_items[this.cur_row].protocol == 'custom'){
                url = this.data_items[this.cur_row].cmd;
                var url_match = /(\S+):\/\/(\S+)/.exec(url);
                _debug('url_match', url_match);
                if (url_match){
                    url = url_match[0];
                }
            }

            var filename = this.data_items[this.cur_row].path;

            if (this.data_items[this.cur_row].cur_series && parseInt(this.data_items[this.cur_row].cur_series, 10) != 0){
                filename += '_E' + this.data_items[this.cur_row].cur_series;
            }

            if (url){
                var ext = /\.(\w*)$/.exec(url);
                if (!ext){
                    ext = [,'mpg'];
                }
            }else{
                ext = [,'mpg'];
            }

            filename += '.'+ext[1];

            _debug('filename: ', filename);

            var self = this;

            var video_cmd = this.data_items[this.cur_row].cmd;
            var episode   = this.data_items[this.cur_row].cur_series || 0;

            var dialog_options = {"parent" : this, "url" : url, "name" : filename, "secure_url" : true};

            if (!url){
                dialog_options.url = {"secure_url" : true, "type" : "vclub", "exec" : "module.vclub.get_link", "scope" : "module.vclub", "options" : [video_cmd, episode]};
            }

            if (module.downloads){
                _debug('downloads');
                module.downloads.dialog.show(dialog_options);
            }
        };

        this.set_ad_ended_time = function(ad_id, end_time, total_time, ended){
            _debug('vclub.set_not_ended', ad_id, end_time, total_time, ended);

            stb.load(
                {
                    "type"       : "vclub_advertising",
                    "action"     : "set_ad_ended_time",
                    "ad_id"      : ad_id,
                    "end_time"   : end_time,
                    "total_time" : total_time,
                    "ended"      : ended
                },
                function(result){
                    _debug('on set_ad_ended_time', result);
                },
                this
            );
        };
        
        this.set_not_ended = function(video_id, series, end_time){
            _debug('vclub.set_not_ended', video_id, series, end_time);

            if (this.load_params.not_ended && empty(this.data_items[this.cur_row].series)){
                this.data_items[this.cur_row].position = end_time;
            }

            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "set_not_ended",
                    "video_id" : video_id,
                    "series"   : series,
                    "end_time" : end_time
                },

                function(result){

                },

                this
            )
        };

        this.set_ended = function(video_id){
            _debug('vclub.set_not_ended', video_id);

            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "set_ended",
                    "video_id" : video_id
                },

                function(result){

                },

                this
            )
        };
        
        this.add_to_fav = function(){
            _debug('vclub.add_to_fav');
            
            this.data_items[this.cur_row].fav = 1;
            
            this.map[this.cur_row].fav_block.show();
            this.active_row.fav_block.show();
            
            this.set_fav(this.data_items[this.cur_row].id);
        };
        
        this.del_from_fav = function(){
            _debug('vclub.del_from_fav');
            
            this.data_items[this.cur_row].fav = 0;
            
            this.map[this.cur_row].fav_block.hide();
            this.active_row.fav_block.hide();
            
            this.del_fav(this.data_items[this.cur_row].id);
        };
        
        this.add_del_fav = function(){
            _debug('vclub.add_del_fav');
            
            //if (this.load_params.fav == true){
            //    return;
            //}
            
            if(this.data_items[this.cur_row].fav){
                this.del_from_fav();
            }else{
                this.add_to_fav();
            }
        };
        
        this.set_fav = function(id){
            _debug('vclub.set_fav', id);
            
            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "set_fav",
                    "video_id" : id
                },
                
                function(result){
                    
                }
            )
        };
        
        this.del_fav = function(id){
            _debug('vclub.del_fav', id);
            
            stb.load(
                {
                    "type"     : "vod",
                    "action"   : "del_fav",
                    "video_id" : id
                },
                
                function(result){
                    
                }
            )
        };
        
        this.sidebar_switcher = function(){
            _debug('vclub.sidebar_switcher');
            
            if (this.sidebar && !this.sidebar.on){
                this.search_box && this.search_box.reset && this.search_box.reset();
                this.load_data();
            }
            
            this.superclass.sidebar_switcher.call(this);
        };
    }
    
    vclub_constructor.prototype = new ListLayer();

    var vclub = new vclub_constructor();
        
    vclub.bind();
    vclub.init();
    
    vclub.init_short_info();
    
    //vclub.set_wide_container();
    vclub.set_middle_container();

    if (single_module != 'vclub'){
        vclub.init_left_ear(word['ears_back']);
    }

    vclub.init_right_ear(word['ears_about_movie']);
    
    vclub.init_color_buttons([
        {"label" : word['vclub_view'], "cmd" : vclub.view_switcher},
        {"label" : word['vclub_sort'], "cmd" : vclub.sort_menu_switcher},
        {"label" : word['vclub_fav'],  "cmd" : vclub.add_del_fav},
        {"label" : word['vclub_find'], "cmd" : vclub.other_switcher}
    ]);
    
    //vclub.init_info();
    
    vclub.init_sidebar();
    
    vclub.sidebar.init_items("abc", {"header" : word['vclub_by_letter'], "width" : 26, "align" : "center"});
    vclub.sidebar.init_items("genre",  {"header" : word['vclub_by_genre'], "width" : 95});
    vclub.sidebar.init_items("years",  {"header" : word['vclub_by_year'], "width" : 45});
    
    vclub.sidebar.bind();

    var sort_menu = [
        {"label" : word['vclub_by_addtime'], "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'added'; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}},
        {"label" : word['vclub_by_title'], "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'name'; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}},
        {"label" : word['vclub_top'], "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'top'; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}},
        {"label" : word['vclub_only_hd'], "cmd" : function(){this.parent.load_params.sortby = 'added'; this.parent.load_params.fav = false; this.parent.load_params.hd = true; this.parent.load_params.not_ended = false}},
        {"label" : word['vclub_only_favorite'], "cmd" : function(){this.parent.load_params.sortby = 'name'; this.parent.load_params.fav = true; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}},
        {"label" : word['vclub_not_ended'], "cmd" : function(){this.parent.load_params.sortby = 'last_ended'; this.parent.load_params.fav = false; this.parent.load_params.hd = false; this.parent.load_params.not_ended = true}}
    ];

    if (stb.profile['kinopoisk_rating']){

        var rating_item = {"label" : get_word('vclub_by_rating'), "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'rating'; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}};

        sort_menu.splice(1, 0, rating_item);
    }

    if (stb.profile['show_purchased_filter']){
        var purchased_item = {"label" : get_word('vclub_only_purchased'), "cmd" : function(){this.parent.load_params.fav = false; this.parent.load_params.sortby = 'purchased'; this.parent.load_params.hd = false; this.parent.load_params.not_ended = false}};
        sort_menu.push(purchased_item);
    }
    
    vclub.init_sort_menu(
        sort_menu,
        {
            "offset_x" : 217,
            "color"    : "green"
        }
    );
    
    vclub.init_search_box(
        {
            "offset_x"  : 323,
            "color"     : "blue",
            "languages" : get_word('search_box_languages')
        }
    );
    
    vclub.init_view_menu(
        [
            {"label" : word['vclub_list_w_info'], "cmd" : function(){this.parent.set_middle_container()}},
            {"label" : word['vclub_list'], "cmd" : function(){this.parent.set_wide_container()}}
            //{"label" : word['vclub_list_w_info'], "cmd" : function(){this.parent.set_middle_container()}}
        ],
        {
            "offset_x" : 27,
            "color" : "red",
            "need_reset_load_data" : false,
            "need_update_header"   : false
        }
    );
    
    vclub.init_other_menu(
        [
            {"label" : word['vclub_search_box'], "cmd" : function(){this.parent.search_box_switcher()}},
            {"label" : word['vclub_query_box'],  "cmd" : function(){this.parent.sidebar_switcher()}}
        ],
        {
            "offset_x" : 520,
            "color"    : "blue",
            "need_reset_load_data" : false
        }
    );
   
    vclub.init_header_path(word['vclub_title']);
    
    vclub.sidebar.dependency    = [vclub.sort_menu, vclub.search_box, vclub.view_menu, vclub.other_menu];
    vclub.sort_menu.dependency  = [vclub.sidebar, vclub.search_box, vclub.view_menu, vclub.other_menu];
    vclub.search_box.dependency = [vclub.sidebar, vclub.sort_menu, vclub.view_menu, vclub.other_menu];
    vclub.view_menu.dependency  = [vclub.sidebar, vclub.sort_menu, vclub.search_box, vclub.other_menu];
    vclub.other_menu.dependency  = [vclub.sidebar, vclub.sort_menu, vclub.search_box, vclub.view_menu];
    
    vclub.hide();
    
    module.vclub = vclub;
    /* END VCLUB */
    
    /* Integrate vclub in main menu */
    stb.load(
        {
            "type"   : "vod",
            "action" : "get_categories"
        },
        function(result){
            _debug('callback categories');
            
            var categories = result;
            
            var map = [];

            module.vclub.categories = categories;

            for(var i=0; i<categories.length; i++){
                map.push(
                
                {
                    "title" : categories[i].title,
                    "cmd"   : (function(category){
                        
                        
                        return function(){
                            _debug('alias', category.alias);

                            if (category.alias == 'adult'){
                                module.vclub.parent_password_promt.callback = function(){
                                    main_menu.hide();
                                    module.vclub._show(category);
                                };
                                module.vclub.parent_password_promt.show();
                            }else{
                                main_menu.hide();
                                module.vclub._show(category);
                            }
                        }
                        
                    })(categories[i])
                }
                
                );
            }
            
            main_menu.add(word['vclub_title'], map, 'mm_ico_video.png', '', module.vclub);
            
            loader.next();
        },
        this
    )
    
})();
