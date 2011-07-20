/**
 * Day weather in infoportal.
 * 
 */

(function(){
    
    function dayweather_constructor(){
        
        this.layer_name = 'dayweather';
        
        this.dom_obj = this.create_block('layer_bg');
        document.body.appendChild(this.dom_obj);
        
        this.superclass = BaseLayer.prototype;
        
        this.map = [];
        
        this.reload_timeout = 30000;
        this.reload_timer;
        
        this.init = function(){
            _debug('dayweather.init');
            
            var container = create_block_element('day_weather', this.dom_obj);
            
            for (var i=0; i<4; i++){
                this.map[i] = {};
                
                var col = create_block_element('weather_row_1 weather_col_'+(i+1), container);
                this.map[i].text = create_block_element('day_weather_text', col);
                this.map[i].descr = create_block_element('day_weather_descr', col);
                
                var layer_1_container = create_block_element('day_weather_layer_1', col);
                this.map[i].layer_1 = document.createElement('img');
                layer_1_container.appendChild(this.map[i].layer_1);
                
                var layer_2_container = create_block_element('day_weather_layer_2', col);
                this.map[i].layer_2 = document.createElement('img');
                layer_2_container.appendChild(this.map[i].layer_2);
                
                var layer_3_container = create_block_element('day_weather_layer_3', col);
                this.map[i].layer_3 = document.createElement('img');
                layer_3_container.appendChild(this.map[i].layer_3);
                
                this.map[i].temp = create_block_element('day_weather_temp', col);
            }
            
        };
        
        this.show = function(){
            _debug('dayweather.show');
            
            this.superclass.show.call(this);
            
            this.load();
            
            this.t_load();
        };
        
        this.hide = function(){
            _debug('dayweather.hide');
            
            this.superclass.hide.call(this);
            
            window.clearInterval(this.reload_timer);
        };
        
        this.t_load = function(){
            _debug('dayweather.t_load');
            
            var self = this;
            
            this.reload_timer = window.setInterval(function(){self.load()}, this.reload_timeout);
        };
        
        this.load = function(){
            _debug('dayweather.load');
            
            stb.load(
                {
                    "type"   : "gismeteo",
                    "action" : "get_data"
                },
                
                function(result){
                    this.fill(result);
                },
                
                this
            )
        };
        
        this.fill = function(data){
            _debug('dayweather.fill', data);
            
            for (var i=0; i<data.length; i++){
                
                this.map[i].text.innerHTML  = data[i].title;
                
                var descr = data[i].phenomena + '<br><span class="day_weather_sub">' + word['dayweather_pressure'] + '</span> ' + data[i].pressure + ' <span class="day_weather_sub">' + word['dayweather_mmhg'] + '</span><br>';
                descr += '<span class="day_weather_sub">' + word['dayweather_wind'] + '</span> ' + data[i].wind + ' <span class="day_weather_sub">' + word['dayweather_speed'] + '</span>';
                
                this.map[i].descr.innerHTML = descr;
                
                this.map[i].layer_1.src = 'i' + img_prefix + '/' + data[i].img_1;
                this.map[i].layer_2.src = 'i' + img_prefix + '/' + data[i].img_2;
                this.map[i].layer_3.src = 'i' + img_prefix + '/' + data[i].img_3;
                
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
    
    dayweather_constructor.prototype = new BaseLayer();
    
    var dayweather = new dayweather_constructor();
    
    dayweather.init();
    
    dayweather.bind();
    
    dayweather.init_left_ear(word['ears_back']);
   
    dayweather.init_header_path(word['dayweather_title']);
     
    dayweather.hide();
    
    module.dayweather = dayweather;
    
    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }
    
    module.infoportal_sub.push({
        "title" : word['dayweather_title'],
        "cmd"   : function(){
            main_menu.hide();
            module.dayweather.show();
        }
    })
    
})();

loader.next();