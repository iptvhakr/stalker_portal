(function(){

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
            this.confirm_dialog.enableOnExitClose();
            var scope = this;

            this.confirm_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.confirm_dialog.hide();
                        scope.do_subscribe();
                    }
                }
            ));

            this.confirm_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("yes_btn"),
                    "onclick" : function(){
                        scope.confirm_dialog.hide();
                    }
                }
            ));

            this.confirm_unsubscribe_dialog = new ModalForm({"title" : get_word('confirm_form_title'), "text" : get_word('confirm_service_unsubscribe_text')});
            this.confirm_unsubscribe_dialog.enableOnExitClose();

            this.confirm_unsubscribe_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("cancel_btn"),
                    "onclick" : function(){
                        scope.confirm_unsubscribe_dialog.hide();
                        scope.do_unsubscribe();
                    }
                }
            ));

            this.confirm_unsubscribe_dialog.addItem(new ModalFormButton(
                {
                    "value" : get_word("yes_btn"),
                    "onclick" : function(){
                        scope.confirm_unsubscribe_dialog.hide();
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

            if (this.data_items[this.cur_row].optional && !this.data_items[this.cur_row].subscribed){
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
                self.confirm_dialog.show();
            };

            this.password_input.show();
        };

        this.unsubscribe = function(){
            _debug('service_management.unsubscribe');

            var self = this;

            this.password_input.callback = function(){
                self.confirm_unsubscribe_dialog.show();
            };

            this.password_input.show();
        };

        this.do_subscribe = function(){
            _debug('service_management.do_subscribe');
        };

        this.do_unsubscribe = function(){
            _debug('service_management.do_unsubscribe');
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