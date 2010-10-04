/**
 * Current weather widget.
 * Displayed in the main menu.
 */

(function(){
    
    var curweather = {
        
        //current : {},
        dom_obj : {},
        
        init : function(){
            
            this.dom_obj = create_block_element('curweather_block', main_menu.dom_obj);
        },
        
        set : function(weather){
            _debug('curweather.set', weather);
            this.current = weather;
            
            if (main_menu){
                this.render();
            }
        },
        
        render : function(){
            _debug('curweather.render');
            
            var cur = '<div class="curweather_img"><img src="i/weather/' + this.current.pict + '"/></div>';
            cur += '<div class="curweather_descr">' + this.current.t +'&deg; C<br>';
            cur += this.current.cloud_str + '<br>';
            cur += '<span class="curweather_title">' + word['weather_comfort'] + ':</span> ' + this.current.t_flik +'&deg; C<br>';
            cur += '<span class="curweather_title">' + word['weather_pressure'] + ':</span> ' + this.current.p + ' ' + word['weather_mmhg'] +'<br>';
            cur += '<span class="curweather_title">' + word['weather_wind'] + ':</span> '+ this.current.w_rumb_str + ', ' + this.current.w + ' ' + word['weather_speed'] + '<br>';
            cur += '<span class="curweather_title">' + word['weather_humidity'] + ':</span> '+ this.current.h + '%<br>';
            cur += '</div>';
            
            this.dom_obj.innerHTML = cur;
        }
        
    }
    
    curweather.init();
    
    module.curweather = curweather;
    
    loader.next();
    
})();