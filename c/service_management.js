(function(){

    if (!stb.profile['enable_tariff_plans']){
        return;
    }

    function ServiceManagement(){

        this.layer_name = 'service_management';
        this.row_blocks  = ['name', 'description', 'subscribed_str', 'not_subscribed_str'];

        this.load_params = {
            'type'   : 'account_info',
            'action' : 'get_user_packages'
        };

        this.password_input = new password_input({"parent" : this});
        this.password_input.bind();

        this.superclass = ListLayer.prototype;

        this.hide = function(do_not_reset){
            _debug('service_management.hide', do_not_reset);

            this.password_input.on && this.password_input.hide && this.password_input.hide();

            this.superclass.hide.call(this, do_not_reset);
        };

        this.init = function(){
            this.superclass.init.apply(this);

            this.confirm_dialog = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('confirm_service_subscribe_text')});
            this.confirm_dialog.getTextDomObj().style.textAlign = "center";
            this.confirm_dialog.enableOnExitClose();
            var scope = this;

            this.confirm_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.confirm_dialog.hide();
                    }
                }
            ));

            this.confirm_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("yes_btn"),
                    "onclick" : function(){
                        scope.confirm_dialog.hide();
                        scope.do_subscribe(scope.confirm_dialog.tariff_package);
                    }
                }
            ));

            this.confirm_unsubscribe_dialog = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('confirm_service_unsubscribe_text')});
            this.confirm_unsubscribe_dialog.getTextDomObj().style.textAlign = "center";
            this.confirm_unsubscribe_dialog.enableOnExitClose();

            this.confirm_unsubscribe_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.confirm_unsubscribe_dialog.hide();
                    }
                }
            ));

            this.confirm_unsubscribe_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("yes_btn"),
                    "onclick" : function(){
                        scope.confirm_unsubscribe_dialog.hide();
                        scope.do_unsubscribe(scope.confirm_unsubscribe_dialog.tariff_package);
                    }
                }
            ));


            this.complete_confirm = new ModalForm({"title" : get_word('notice_form_title'), "text" : get_word('service_subscribe_success_reboot')});
            this.complete_confirm.enableOnExitClose();
            this.complete_confirm.getTextDomObj().style.textAlign = "center";
            this.complete_confirm.addItem(new ModalFormButton(
                {
                    "value" : get_word("ok_btn"),
                    "onclick" : function(){
                        scope.complete_confirm.hide();

                        _debug('scope.complete_confirm.need_reboot', scope.complete_confirm.need_reboot);

                        if (scope.complete_confirm.need_reboot){
                            scope.hide();
                            stb.ExecAction('reboot');
                        }
                    }
                }
            ));
        };

        this.bind = function(){
            this.superclass.bind.apply(this);

            this.action.bind(key.OK, this);
        };

        this.set_active_row = function(num){
            this.superclass.set_active_row.call(this, num);

            if (this.data_items[this.cur_row].optional && !this.data_items[this.cur_row].subscribed && stb.profile['allow_subscription_from_stb']){
                this.color_buttons.get('green').enable();
                this.color_buttons.get('red').disable();
            }else if (this.data_items[this.cur_row].optional && this.data_items[this.cur_row].subscribed){
                this.color_buttons.get('green').disable();
                this.color_buttons.get('red').enable();
            }else{
                this.color_buttons.get('green').disable();
                this.color_buttons.get('red').disable();
            }
        };

        this.action = function(){
            _debug('service_management.action');

            if (!stb.profile['allow_subscription_from_stb']){
                return;
            }

            if (this.data_items[this.cur_row].optional && !this.data_items[this.cur_row].subscribed){
                this.subscribe();
            }else if (this.data_items[this.cur_row].optional && this.data_items[this.cur_row].subscribed){
                this.unsubscribe();
            }
        };

        this.subscribe = function(){
            _debug('service_management.subscribe');

            var self = this;

            this.password_input.callback = function(){
                self.confirm_dialog.tariff_package = self.data_items[self.cur_row];
                self.confirm_dialog.show();
            };

            this.password_input.show();
        };

        this.unsubscribe = function(){
            _debug('service_management.unsubscribe');

            var self = this;

            this.password_input.callback = function(){
                self.confirm_unsubscribe_dialog.tariff_package = self.data_items[self.cur_row];
                self.confirm_unsubscribe_dialog.show();
            };

            this.password_input.show();
        };

        this.do_subscribe = function(tariff_package){
            _debug('service_management.do_subscribe', tariff_package);

            stb.load({
                    "type"   : "account",
                    "action" : "subscribe_to_package",
                    "package_id" : tariff_package.package_id
                },

                function(result){
                    _debug('on do_subscribe', result);

                    this.load_data();

                    if (result){
                        if (tariff_package.type == 'module'){
                            this.complete_confirm.need_reboot = true;
                            this.complete_confirm.show(get_word('service_subscribe_success_reboot'));
                        }else{
                            this.complete_confirm.need_reboot = false;
                            this.complete_confirm.show(get_word('service_subscribe_success'));
                        }
                    }else{
                        this.complete_confirm.need_reboot = false;
                        this.complete_confirm.show(get_word('service_subscribe_fail'));
                    }
                },

                this
            );
        };

        this.do_unsubscribe = function(tariff_package){
            _debug('service_management.do_unsubscribe', tariff_package);

            stb.load({
                    "type"   : "account",
                    "action" : "unsubscribe_from_package",
                    "package_id" : tariff_package.package_id
                },

                function(result){
                    _debug('on do_unsubscribe', result);

                    this.load_data();

                    if (result){
                        if (tariff_package.type == 'module'){
                            this.complete_confirm.need_reboot = true;
                            this.complete_confirm.show(get_word('service_unsubscribe_success_reboot'));
                        }else{
                            this.complete_confirm.need_reboot = false;
                            this.complete_confirm.show(get_word('service_unsubscribe_success'));
                        }
                    }else{
                        this.complete_confirm.need_reboot = false;
                        this.complete_confirm.show(get_word('service_subscribe_fail'));
                    }
                },

                this
            );
        };
    }

    ServiceManagement.prototype = new ListLayer();

    var service_management = new ServiceManagement();

    service_management.bind();
    service_management.init();

    service_management.init_color_buttons([
        {"label" : get_word('UNSUBSCRIBE'), "cmd" : function(){
            service_management.unsubscribe();
        }},
        {"label" : get_word('SUBSCRIBE'), "cmd" : function(){
            service_management.subscribe();
        }},
        {"label" : '', "cmd" : ''},
        {"label" : '', "cmd" : ''}
    ]);

    service_management.init_left_ear(get_word('ears_back'));
    service_management.init_header_path(get_word('account_info_title'));
    service_management.update_header_path([{"alias" : "tab", "item" : get_word('SERVICES MANAGEMENT')}]);
    service_management.hide();

    module.service_management = service_management;

    if (!module.account_menu){
        module.account_menu = [];
    }

    module.account_menu.push({
        "title" : get_word('SERVICES MANAGEMENT'),
        "cmd"   : function(){
            main_menu.hide();
            module.service_management.show();
        }
    })

})();

loader.next();