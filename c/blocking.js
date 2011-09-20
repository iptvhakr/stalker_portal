(function(){
    var blocking = {
        
        on : false,

        init : function(){
            this.bind.call(this);
        },

        init_layer : function(){
            if (!this.dom_obj){

                var _style = document.createElement('link');
                _style.type = "text/css";
                _style.rel = "stylesheet";
                _style.href = 'blocking' + resolution_prefix +".css";
                document.getElementsByTagName("head")[0].appendChild(_style);

                this.dom_obj  = create_block_element('cut_off', document.body);
                this.text_msg = create_block_element('cut_off_text', this.dom_obj);
                this.hide();
            }
        },

        show : function(){
            _debug('blocking.show');
            this.init_layer();
            this.text_msg.innerHTML = get_word('cut_off_msg');
            this.dom_obj.show();
            this.on = true;
        },

        hide : function(){
            this.dom_obj.hide();
            this.on = false;
        },

        bind : function(){
            (function(){
                _debug('window.referrer', window.referrer);
                if (window.referrer){
                    window.location = window.referrer
                }
            }).bind(key.EXIT, this);
        }
    };
    
    blocking.init();

    window.module = window.module || {};

    window.module.blocking = blocking;
})();