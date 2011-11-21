/**
 * Network info.
 */

(function(){

    if (stb.firmware_version < 212){
        return;
    }

    function NetworkStatusConstructor(){

        this.layer_name = 'network_status';

        this.superclass = SimpleLayer.prototype;

        this.show = function(){
            _debug('network_status.show');

            this.superclass.show.call(this);

            this.refresh();
        };

        this.init = function(){
            _debug('network_status.init');

            this.superclass.init.call(this);

            this.list = new HTMLDefinitionList("network_status_list", this.container);
            
            this.list.setSeparator(':');
            
            this.list.addRow(get_word('LAN'),   '');
            this.list.addRow(get_word('WLAN'),  '');

            if (stb.profile['test_download_url']){
                this.list.addRow(get_word('test_speed'), '');
            }
        };

        this.bind = function(){

            (function(){
                this.hide();
                main_menu.show();
            }).bind(key.MENU, this).bind(key.EXIT, this).bind(key.LEFT, this);

            this.refresh.bind(key.REFRESH, this);
        };

        this.refresh = function(){
            _debug('network_status.refresh');

            this.list.updateValueByTitle(get_word('LAN'),
                stb.GetLanLinkStatus()  ? '<span class="if_up">' + get_word('lan_up') + '</span>' : '<span class="if_down">' + get_word('lan_down') + '</span>');

            this.list.updateValueByTitle(get_word('WLAN'),
                stb.GetWifiLinkStatus() ? '<span class="if_up">' + get_word('lan_up') + '</span>' : '<span class="if_down">' + get_word('lan_down') + '</span>');

            if (!stb.profile['test_download_url']){
                return;
            }
            
            var self = this;

            var speedtest = new Speedtest(stb.profile['test_download_url']);

            speedtest.onSuccess(function(speed){
                _debug('speed', speed);
                self.list.updateValueByTitle(get_word('test_speed'), speed);
            });

            speedtest.onCheck(function(result){
                if (result.state == 2){
                    self.list.updateValueByTitle(get_word('test_speed'), get_word('speedtest_testing'));
                }else if (result.state == 4 || result.state == 5){
                    self.list.updateValueByTitle(get_word('test_speed'), get_word('speedtest_error'));
                }else if (result.state == 1){
                    self.list.updateValueByTitle(get_word('test_speed'), get_word('speedtest_waiting'));
                }
            });

            speedtest.start();
        };
    }

    function SpeedtestStatic(){}

    function Speedtest(url){
        
        this._url           = url;
        this._interval_time = 1;
        this._static         = SpeedtestStatic;
        //this._job_id        = 0;
    }

    Speedtest.prototype.start = function(){
        _debug('Speedtest.start');

        var result = this._getResult();

        if (result && result.hasOwnProperty('id')){
            this.stop(result.id);
        }

        stbDownloadManager.AddMeasureJob(this._url);

        this.startChecking();
    };

    Speedtest.prototype.stop = function(id){
        _debug('Speedtest.stop', id);

        window.clearInterval(this._static._interval);
        stbDownloadManager.DeleteJob(id, false);
    };

    Speedtest.prototype.startChecking = function(){
        _debug('Speedtest.t_check');

        window.clearInterval(this._static._interval);

        var self = this;
        self.check();
        this._static._interval = window.setInterval(function(){
            self.check();
        }, this._interval_time * 1000);
    };

    Speedtest.prototype.setIntervalTime = function(value){
        _debug('Speedtest.setIntervalTime', value);

        this._interval_time = value;
    };

    Speedtest.prototype.onSuccess = function(callback){
        _debug('Speedtest.onSuccess');

        this._callback = callback;
    };

    Speedtest.prototype.onCheck = function(callback){
        _debug('Speedtest.onCheck');
        
        this._check_callback = callback;
    };

    Speedtest.prototype._getResult = function(){

        var result = stbDownloadManager.GetMeasureInfo();

        _debug('GetMeasureInfo', result);

        result = JSON.parse(result);
        result = result[0];

        return result;
    };

    Speedtest.prototype.check = function(){
        _debug('Speedtest.check');

        var result = this._getResult();

        //this._job_id = result.id;

        _debug('this._static._interval', this._static._interval);

        if (result.progressPct == 100){
            //window.clearInterval(this._static._interval);
            this._callback && this._callback(this.getHumanReadableSpeed(result));
            this.stop(result.id);
        }else{
            this._check_callback && this._check_callback(result);
        }
    };

    Speedtest.prototype.getSpeed = function(result){
        _debug('Speedtest.getSpeed');

        return (result.sizeDone*1000)/result.timeWasted;
    };

    Speedtest.prototype.getHumanReadableSpeed = function(result){
        _debug('Speedtest.getHumanReadableSpeed');

        var speed = this.getSpeed(result)*8;

        if (speed >= 1048576){
            postfix = get_word('Mbps');
            divider = 1048576;
        }else if (speed >= 1024){
            var postfix = get_word('Kbps');
            var divider = 1024;
        }else{
            postfix = get_word('bps');
            divider = 1;
        }

        return (speed/divider).toFixed(2) + ' ' + postfix;
    };
    

    NetworkStatusConstructor.prototype = new SimpleLayer();

    var network_status = new NetworkStatusConstructor();

    network_status.init();
    network_status.init_left_ear(get_word('ears_back'));
    network_status.bind();

    network_status.init_color_buttons([
        {"label" : get_word('network_status_refresh'), "cmd" : function(){network_status.refresh()}},
        {"label" : get_word('empty'), "cmd" : ''},
        {"label" : get_word('empty'), "cmd" : ''},
        {"label" : get_word('empty'), "cmd" : ''}
    ]);

    network_status.init_header_path(get_word('network_status_title'));

    network_status.hide();

    module.network_status = network_status;

    if (!module.settings_sub){
        module.settings_sub = [];
    }

    module.settings_sub.push({
        "title" : get_word('network_status_title'),
        "cmd"   : function(){
            main_menu.hide();
            module.network_status.show();
        }
    })

})();

loader.next();