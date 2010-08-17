/**
 *
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
            
            var cur = '<img src="i/weather/' + this.current.pict + '"/><br>';
            cur += this.current.t +'&deg; C<br>';
            cur += this.current.cloud_str + '<br>';
            cur += word['weather_comfort'] + ': ' + this.current.t_flik +'&deg; C<br>';
            cur += word['weather_pressure'] + ': ' + this.current.p + ' ' + word['weather_mmhg'] +'<br>';
            cur += word['weather_wind'] + ': '+ this.current.w_rumb_str + ', ' + this.current.w + ' ' + word['weather_speed'] + '<br>';
            cur += word['weather_humidity'] + ': '+ this.current.h + '%<br>';
            
            this.dom_obj.innerHTML = cur;
        }
        
    }
    
    curweather.init();
    
    module.curweather = curweather;
    
    loader.next();
    
})();