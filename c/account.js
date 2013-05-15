/**
 * Account info module.
 */

(function(){

    function AccountConstructor(){

        this.layer_name = 'account';
        this.tab        = {};
        this.cur_tab    = 'main';

        this.superclass = SimpleLayer.prototype;

        this.init = function(){
            _debug('account.init');

            this.superclass.init.apply(this);

            var self = this;

            this.tab['main']         = new Showable(create_block_element("main_container", this.container)).setParent(this).setDependencies(this.tab, ['payment', 'agreement', 'terms']);
            this.tab['main'].content = new Scrollable(create_block_element("main_content", this.tab['main'].dom_obj), this.tab['main'].dom_obj);
            this.tab['main'].onshow  = function(){self.tab['main'].content.scrollbar.reset()};
            stb.load(
                {
                    "type"   : "account_info",
                    "action" : "get_main_info"
                },
                function(result){
                    this.fill_main_info(result);
                },
                this
            );
            //this.tab['main'].content.dom_obj.innerHTML = 'main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>main<br>';

            this.tab['payment']   = new Showable(create_block_element("payment_container", this.container)).setParent(this).setDependencies(this.tab, ['main', 'agreement', 'terms']);
            this.tab['payment'].content = new Scrollable(create_block_element("payment_content", this.tab['payment'].dom_obj), this.tab['payment'].dom_obj);
            this.tab['payment'].onshow  = function(){self.tab['payment'].content.scrollbar.reset()};
            //this.tab['payment'].content.dom_obj.innerHTML = 'payment';
            this.tab['payment'].dom_obj.hide();
            stb.load(
                {
                    "type"   : "account_info",
                    "action" : "get_payment_info"
                },
                function(result){
                    this.tab['payment'].content.dom_obj.innerHTML = result;
                },
                this
            );

            this.tab['agreement'] = new Showable(create_block_element("agreement_container", this.container)).setParent(this).setDependencies(this.tab, ['main', 'payment', 'terms']);
            this.tab['agreement'].content = new Scrollable(create_block_element("agreement_content", this.tab['agreement'].dom_obj), this.tab['agreement'].dom_obj);
            this.tab['agreement'].onshow  = function(){self.tab['agreement'].content.scrollbar.reset()};
            //this.tab['agreement'].content.dom_obj.innerHTML = 'agreement';
            this.tab['agreement'].dom_obj.hide();
            stb.load(
                {
                    "type"   : "account_info",
                    "action" : "get_agreement_info"
                },
                function(result){
                    this.tab['agreement'].content.dom_obj.innerHTML = result;
                },
                this
            );

            this.tab['terms']     = new Showable(create_block_element("terms_container", this.container)).setParent(this).setDependencies(this.tab, ['main', 'payment', 'agreement']);
            this.tab['terms'].content = new Scrollable(create_block_element("terms_content", this.tab['terms'].dom_obj), this.tab['terms'].dom_obj);
            this.tab['terms'].onshow  = function(){self.tab['terms'].content.scrollbar.reset()};
            //this.tab['terms'].content.dom_obj.innerHTML = 'terms';
            this.tab['terms'].dom_obj.hide();
            stb.load(
                {
                    "type"   : "account_info",
                    "action" : "get_terms_info"
                },
                function(result){
                    this.tab['terms'].content.dom_obj.innerHTML = result;
                },
                this
            );

            this.hide();

            (function(){
                _debug('blocking key.blue');
                module.blocking.hide();
                module.account.show(module.blocking);

                module.account.tab['payment'].show();
                module.account.cur_tab = 'payment';
                module.account.update_header_path([{"alias" : "tab", "item" : word['account_payment']}]);
                module.account.color_buttons.get('red').enable();
                module.account.color_buttons.get('green').disable();
                module.account.color_buttons.get('yellow').enable();
                module.account.color_buttons.get('blue').enable();

            }).bind(key.BLUE, module.blocking);


            (function(){
                _debug('blocking key.info');
                module.blocking.hide();
                module.account.show(module.blocking);
            }).bind(key.INFO, module.blocking).bind(key.YELLOW, module.blocking);

            var blocking_buttons = create_block_element('blocking_buttons', module.blocking.dom_obj);

            var blocking_account_info = create_block_element('blocking_account_info', blocking_buttons);
            blocking_account_info.innerHTML = '<div class="color_btn blue"></div> '+get_word('blocking_account_payment');

            var blocking_account_payment = create_block_element('blocking_account_payment', blocking_buttons);
            blocking_account_payment.innerHTML = '<div class="color_btn yellow"></div> '+get_word('blocking_account_info');
        };

        this.show = function(return_layer){
            _debug('account.show');

            this.superclass.show.apply(this);

            this.return_layer = return_layer;

            this.update_header_path([{"alias" : "tab", "item" : word['account_info']}]);

            this.color_buttons.get('red').disable();
            this.color_buttons.get('green').enable();
            this.color_buttons.get('yellow').enable();
            this.color_buttons.get('blue').enable();

            stb.load(
                {
                    "type"   : "account_info",
                    "action" : "get_main_info"
                },
                function(result){
                    this.fill_main_info(result);
                },
                this
            );
        };

        this.fill_main_info = function(result){
            _debug('account.fill_main_info', result);

            var info = '';

            if (result['fname']){
                info += '<span class="label">' + get_word('User') + ':</span> ' + result['fname'] + '<br>';
            }

            if (result['phone']){
                info += '<span class="label">' + get_word('Phone') + ':</span> ' + result['phone'] + '<br>';
            }

            if (result['ls']){
                info += '<span class="label">' +get_word('Account number') + ':</span> ' + result['ls'] + '<br>';
            }

            info += '<span class="label">' +get_word('MAC') + ':</span> ' + stb.mac + '<br>';

            if (result['tariff_plan']){
                info += '<span class="label">' +get_word('Tariff plan') + ':</span> ' + result['tariff_plan'] + '<br>';
            }

            if (result['account_balance']){
                info += '<span class="label">' +get_word('Account balance') + ':</span> ' + result['account_balance'] + '<br>';
            }

            if (result['end_date']){
                info += '<span class="label">' +get_word('End date') + ':</span> ' + result['end_date'] + '<br>';
            }

            this.tab['main'].content.dom_obj.innerHTML = info;
        };

        this.hide = function(){
            _debug('account.hide');
            
            this.superclass.hide.apply(this);

            this.tab['main'].show();
            this.cur_tab = 'main';
        };

        this.shift = function(dir){
            _debug('account.shift', dir);

            this.tab[this.cur_tab].content.scroll && this.tab[this.cur_tab].content.scroll(dir);
        };

        this.shift_page = function(dir){
            _debug('account.shift_page', dir);

            this.tab[this.cur_tab].content.scrollPage && this.tab[this.cur_tab].content.scrollPage(dir);
        };

        this.bind = function(){
            this.shift.bind(key.UP, this, -1);
            this.shift.bind(key.DOWN, this, 1);

            this.shift_page.bind(key.PAGE_PREV, this, -1);
            this.shift_page.bind(key.PAGE_NEXT, this, 1);

            (function(){
                this.hide();
                //main_menu.show();
                this.get_return_layer().show(stb.user.hasOwnProperty('block_msg') ? stb.user['block_msg'] : '');
            }).bind(key.MENU, this).bind(key.EXIT, this).bind(key.LEFT, this);
        };

        this.get_return_layer = function(){
            _debug('account.get_return_layer');

            if (!this.return_layer){
                return main_menu;
            }

            return this.return_layer;
        };
    }

    function Scrollable(dom_obj, parent){
        this.dom_obj = dom_obj;
        this.parent  = parent;
        this.initScrollbar();
    }

    Scrollable.prototype.initScrollbar = function(){
        this.scrollbar = new scrollbar(this.parent, this.dom_obj);
    };

    Scrollable.prototype.scroll = function(dir){
        
        if (dir > 0){
            this.dom_obj.scrollTop = this.dom_obj.scrollTop + 40;
        }else{
            this.dom_obj.scrollTop = this.dom_obj.scrollTop - 40;
        }

        this.scrollbar.refresh();
    };

    Scrollable.prototype.scrollPage = function(dir){

        if (dir > 0){
            this.dom_obj.scrollTop = this.dom_obj.scrollTop + 200;
        }else{
            this.dom_obj.scrollTop = this.dom_obj.scrollTop - 200;
        }

        this.scrollbar.refresh();
    };

    function Showable(dom_obj){
        this.dom_obj = dom_obj;
        this.on = !this.dom_obj.isHidden();
        this.dependencies = [];
    }

    Showable.prototype.setParent = function(parent){
        this.parent = parent;
        return this;
    };

    Showable.prototype.show = function(){
        this.hideDependencies();
        this.dom_obj.show();
        this.onshow && this.onshow();
        this.on = true;
        return this;
    };

    Showable.prototype.hide = function(){
        this.dom_obj.hide();
        this.onhide && this.onhide();
        this.on = false;
        return this;
    };

    Showable.prototype.toggle = function(){
        this.on ? this.hide() : this.show();
        return this;
    };

    Showable.prototype.setDependencies = function(scope, names){
        this.scope = scope;
        this.dependencies = names;
        return this;
    };

    Showable.prototype.hideDependencies = function(){
        for (var i = 0; i < this.dependencies.length; i++){
            this.scope[this.dependencies[i]].hide && this.scope[this.dependencies[i]].hide();
        }
        return this;
    };

    AccountConstructor.prototype = new SimpleLayer();

    var account = new AccountConstructor();

    account.init();

    account.init_color_buttons([
        {"label" : word['account_info'], "cmd" : function(){
            account.tab['main'].show();
            account.cur_tab = 'main';
            this.update_header_path([{"alias" : "tab", "item" : word['account_info']}]);
            this.color_buttons.get('red').disable();
            this.color_buttons.get('green').enable();
            this.color_buttons.get('yellow').enable();
            this.color_buttons.get('blue').enable();
        }},
        {"label" : word['account_payment'], "cmd" : function(){
            account.tab['payment'].show();
            account.cur_tab = 'payment';
            this.update_header_path([{"alias" : "tab", "item" : word['account_payment']}]);
            this.color_buttons.get('red').enable();
            this.color_buttons.get('green').disable();
            this.color_buttons.get('yellow').enable();
            this.color_buttons.get('blue').enable();
        }},
        {"label" : word['account_agreement'], "cmd" : function(){
            account.tab['agreement'].show();
            account.cur_tab = 'agreement';
            this.update_header_path([{"alias" : "tab", "item" : word['account_agreement']}]);
            this.color_buttons.get('red').enable();
            this.color_buttons.get('green').enable();
            this.color_buttons.get('yellow').disable();
            this.color_buttons.get('blue').enable();
        }},
        {"label" : word['account_terms'], "cmd" : function(){
            account.tab['terms'].show();
            account.cur_tab = 'terms';
            this.update_header_path([{"alias" : "tab", "item" : word['account_terms']}]);
            this.color_buttons.get('red').enable();
            this.color_buttons.get('green').enable();
            this.color_buttons.get('yellow').enable();
            this.color_buttons.get('blue').disable();
        }}
    ]);

    account.bind();

    account.init_left_ear(word['ears_back']);

    account.init_header_path(word['account_info_title']);

    account.hide();

    module.account = account;

    if (!module.account_menu){
        module.account_menu = [];
    }

    module.account_menu.push({
        "title" : get_word('account_info'),
        "cmd"   : function(){
            main_menu.hide();
            module.account.show();
        }
    })
    
})();

loader.next();