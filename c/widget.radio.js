(function () {

    module.radio_widget = {

        on: false,
        widget_on: false,

        init: function () {
            _debug('radio_widget.init');

            this.dom_obj = create_block_element('radio_widget_block', main_menu.dom_obj);

            this.title_obj = create_block_element('title', this.dom_obj);

            this.items_obj = create_block_element('items', this.dom_obj);

            this.buttons_block_obj = create_block_element('buttons_block', this.dom_obj);

            this.prev_btn = create_block_element('prev_btn', this.buttons_block_obj);
            this.next_btn = create_block_element('next_btn', this.buttons_block_obj);
            this.play_btn = create_block_element('play_btn', this.buttons_block_obj);
            this.pause_btn = create_block_element('pause_btn', this.buttons_block_obj);

            var self = this;

            stb.player.addCustomEventListener("radiostart", function (item) {
                if (item.radio) {
                    _debug('radio_widget.radiostart');
                    self.show(item);
                }
            });

            stb.player.addCustomEventListener("radiostop", function (item) {
                if (self.on || self.widget_on) {
                    _debug('radio_widget.radiostop');
                    self.hide();
                }
            });

            stb.player.addCustomEventListener("radiopause", function (item) {
                if (item.radio) {
                    _debug('radio_widget.radiopause');
                    self.show(item);
                    self.pause_btn.hide();
                    self.play_btn.show();
                }
            });

            stb.player.addCustomEventListener("radiocontinue", function (item) {
                if (item.radio) {
                    _debug('radio_widget.radiocontinue');
                    self.show(item);
                    self.play_btn.hide();
                    self.pause_btn.show();
                }
            });

            main_menu.addCustomEventListener("mainmenushow", function (layer_name) {
                if (layer_name != 'radio') {
                    return;
                }
                _debug('radio_widget.mainmenushow');

                self.on = self.widget_on;
            });

            main_menu.addCustomEventListener("mainmenuhide", function () {
                _debug('radio_widget.mainmenuhide');

                self.on = false;
            });

            this.hide();
        },

        show: function (item) {
            _debug('radio_widget.show', item);

            this.title_obj.innerHTML = item.name;
            this._update_cur_items();
            this.dom_obj.show();
            this.widget_on = true;

            if (main_menu.on) {
                this.on = true;
            }

            this.play_btn.hide();
            this.pause_btn.show();
        },

        hide: function () {
            _debug('radio_widget.hide');
            this.dom_obj.hide();
            this.widget_on = this.on = false;
        },

        bind: function () {
            _debug('radio_widget.bind');

            (function (dir) {

                _debug('dir', dir);

                var idx = this._get_current_idx();

                _debug('playlist idx', idx);

                if (idx >= 0 && idx <= stb.player.playlist.length - 1) {

                    idx = idx + dir;

                    if (!stb.player.playlist[idx]) {
                        return;
                    }
                    try{
                        if (typeof(stb.player.playlist[idx]) == 'object') {
                            var cur_media_item = stb.player.playlist[idx].clone();
                            stb.player.radio_idx = idx;
                            stb.player.play(cur_media_item);
                        }
                    } catch (e){
                        _debug(e);
                        stb.player.stop();
                    }
                }

            }).bind(key.NEXT, this, 1).bind(key.PREV, this, -1).bind(key.CHANNEL_NEXT, this, 1).bind(key.CHANNEL_PREV, this, -1);

        },

        _update_cur_items: function () {

            var cur_idx = this._get_current_idx();
            var total_items = this._get_total_playlist_items();

            this.items_obj.innerHTML = '[' + (cur_idx + 1) + '/' + total_items + ']';

            if (cur_idx < total_items - 1) {
                this.next_btn.show();
            } else {
                this.next_btn.hide();
            }

            if (cur_idx > 0) {
                this.prev_btn.show();
            } else {
                this.prev_btn.hide();
            }
        },

        _get_current_idx: function () {
            _debug('radio_widget._get_current_idx');
            return stb.player.radio_idx;
        },

        _get_total_playlist_items: function () {
            _debug('radio_widget._get_total_playlist_items');
            return stb.player.playlist.length || 0;
        },

        shift_playlist: function (dir) {
            _debug('radio_widget.shift_playlist', dir);
        }

    };

    module.radio_widget.init();
    module.radio_widget.bind();

})();

loader.next();