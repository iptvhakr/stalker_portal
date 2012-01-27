/**
 * Exchange rate of NBU widget.
 * Displayed in the main menu.
 */

(function(){
    
    var course = {
        
        dom_obj : {},
        map     : [],
        
        init : function(){
            
            this.dom_obj = create_block_element('course_block', main_menu.dom_obj);
            
            var title = create_inline_element('', this.dom_obj);
            title.setClass('course_title');
            title.innerHTML = word['course_title']+' ';

            this.date_obj = create_inline_element('', this.dom_obj);
            this.date_obj.setClass('course_date');
            
            for(var i=0; i<3; i++){
                var item = {};
                
                var ul = document.createElement('ul');
                
                var currency = document.createElement('li');
                currency.setClass('course_currency');
                ul.appendChild(currency);
                
                item['currency_dom_obj'] = currency;
                
                var value = document.createElement('li');
                value.setClass('course_value');
                ul.appendChild(value);
                
                item['value_dom_obj'] = value;
                
                var trend = document.createElement('li');
                trend.setClass('course_trend');
                
                var uarr = create_inline_element('uarr', trend);
                uarr.innerHTML = '&uarr;';
                uarr.hide();
                
                var darr = create_inline_element('darr', trend);
                darr.innerHTML = '&darr;';
                darr.hide();
                
                item['uarr'] = uarr;
                
                item['darr'] = darr;
                
                var trend_val = create_inline_element('trend_val', trend);
                
                item['trend_val'] = trend_val;
                
                ul.appendChild(trend);
                
                this.map.push(item);
                
                this.dom_obj.appendChild(ul);
            }
            
            this.dom_obj.hide();
        },
        
        set : function(data){
            _debug('course.set', data);
            
            this.current = data;
            
            if (!empty(this.current)){
                this.dom_obj.show();
            }
            
            this.date_obj.innerHTML = data.on_date || '';
            
            for(var i=0; i<this.map.length; i++){
                
                if (data['data'].hasOwnProperty(i)){
                    
                    this.map[i].currency_dom_obj.innerHTML = data['data'][i].currency;
                    this.map[i].value_dom_obj.innerHTML    = data['data'][i].value;
                    
                    if (parseFloat(data['data'][i].trend) == -1){
                        this.map[i].darr.show();
                        this.map[i].uarr.hide();
                        this.map[i].trend_val.innerHTML = data['data'][i].diff;
                    }else if (parseFloat(data['data'][i].trend) == 1){
                        this.map[i].darr.hide();
                        this.map[i].uarr.show();
                        this.map[i].trend_val.innerHTML = data['data'][i].diff;
                    }else{
                        this.map[i].darr.hide();
                        this.map[i].uarr.hide();
                        this.map[i].trend_val.innerHTML = '';
                    }
                }else{
                    this.map[i].currency_dom_obj.innerHTML = '';
                    this.map[i].value_dom_obj.innerHTML    = '';
                    this.map[i].trend_val.innerHTML = '';
                    this.map[i].darr.hide();
                    this.map[i].uarr.hide();
                }
            }
        }
    };
    
    course.init();
    
    module.course = course;
    
    loader.next();
    
})();