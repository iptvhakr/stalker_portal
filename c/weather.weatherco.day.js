/**
 * Day weather in infoportal.
 * 
 */

(function(){
    
    function WeathercoDayweatherConstructor(){
        
        this.layer_name = 'weatherco_dayweather';
        
        this.dom_obj = this.create_block('layer_bg');
        document.body.appendChild(this.dom_obj);
        
        this.superclass = BaseLayer.prototype;
        
        this.map = [];
        
        this.reload_timeout = 30000;
        
        this.init = function(){
            _debug('weatherco_dayweather.init');
            
            var container = create_block_element('weatherco_dayweather', this.dom_obj);
            
            for (var i=0; i<4; i++){
                this.map[i] = {};
                
                var col = create_block_element('weather_row_1 weather_col_'+(i+1), container);
                this.map[i].text = create_block_element('day_weather_text', col);
                this.map[i].descr = create_block_element('day_weather_descr', col);
                
                var layer_1_container = create_block_element('day_weather_layer_1', col);
                this.map[i].layer_1 = document.createElement('img');
                layer_1_container.appendChild(this.map[i].layer_1);
                
                /*var layer_2_container = create_block_element('day_weather_layer_2', col);
                this.map[i].layer_2 = document.createElement('img');
                layer_2_container.appendChild(this.map[i].layer_2);
                
                var layer_3_container = create_block_element('day_weather_layer_3', col);
                this.map[i].layer_3 = document.createElement('img');
                layer_3_container.appendChild(this.map[i].layer_3);*/
                
                this.map[i].temp = create_block_element('day_weather_temp', col);
            }
            
        };
        
        this.show = function(){
            _debug('weatherco_dayweather.show');
            
            this.superclass.show.call(this);
            
            this.load();
            
            this.t_load();
        };
        
        this.hide = function(){
            _debug('weatherco_dayweather.hide');
            
            this.superclass.hide.call(this);
            
            window.clearInterval(this.reload_timer);
        };
        
        this.t_load = function(){
            _debug('weatherco_dayweather.t_load');
            
            var self = this;
            
            this.reload_timer = window.setInterval(function(){self.load()}, this.reload_timeout);
        };
        
        this.load = function(){
            _debug('weatherco_dayweather.load');
            
            stb.load(
                {
                    "type"   : "weatherco",
                    "action" : "get_forecast"
                },
                
                function(result){
                    this.fill(result);
                },
                
                this
            )
        };
        
        this.fill = function(data){
            _debug('weatherco_dayweather.fill', data);

            this.update_header_path([{"alias" : "city", "item" : data.city}]);

            data = data['forecast'];
            
            for (var i=0; i<data.length; i++){
                
                this.map[i].text.innerHTML  = data[i].title;
                
                var descr = data[i].cloud_str + '<br><span class="day_weather_sub">' + word['dayweather_pressure'] + '</span> ' + data[i].p.min + '...' + data[i].p.max + ' <span class="day_weather_sub">' + word['dayweather_mmhg'] + '</span><br>';
                descr += '<span class="day_weather_sub">' + word['dayweather_wind'] + '</span> ' + data[i].w_rumb_str + ', ' + data[i].wind.min + '-' + data[i].wind.max + ' <span class="day_weather_sub">' + word['dayweather_speed'] + '</span>';
                
                this.map[i].descr.innerHTML = descr;
                
                this.map[i].layer_1.src = 'i' + img_prefix + '/' + data[i].pict;
                
                this.map[i].temp.innerHTML = data[i].temperature;
            }
        };
        
        this.bind = function(){
            
            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.EXIT, this).bind(key.LEFT, this).bind(key.MENU, this);
            
            this.load.bind(key.REFRESH, this);
        };
    }
    
    WeathercoDayweatherConstructor.prototype = new BaseLayer();
    
    var dayweather = new WeathercoDayweatherConstructor();
    
    dayweather.init();
    
    dayweather.bind();
    
    dayweather.init_left_ear(word['ears_back']);
   
    dayweather.init_header_path(word['dayweather_title']);
     
    dayweather.hide();
    
    module.weatherco_dayweather = dayweather;
    
    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }
    
    module.infoportal_sub.push({
        "title" : word['dayweather_title'],
        "cmd"   : function(){
            main_menu.hide();
            module.weatherco_dayweather.show();
        }
    })
    
})();

loader.next();