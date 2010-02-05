/**
 * Player constructor
 * @constructor
 */
function player(){
    
    var self = this;
    
    this.f_ch_idx = 0;
    this.ch_idx   = 0;
    this.channels;
    this.fav_channels;
    this.fav_channels_ids;
    
    this.start_time;
    this.cur_media_item;
    this.paused = false;
    this.need_show_info = 0;
    
    this.pause_dom_obj = $('pause');
    
    this.init();
}

player.prototype.init = function(){
    try{
        stb.InitPlayer();
        stb.SetTopWin(0);
        stb.SetAspect(0x10);
        stb.SetPIG(1, -1, -1, -1);
        stb.SetUserFlickerControl(1);
        stb.SetDefaultFlicker(1);
        stb.SetLoop(0);
        stb.SetMicVolume(100);
        
        stbEvent.onEvent = event_callback;
    }catch(e){
        _debug(e);
    }
}

player.prototype.volume_bar = new function(){
    this.volume = 100;
    this.volume_bar_dom_obj = $('volume_bar');
    
    this.set_volume = function(v){
        this.volume = v;
        stb.SetVolume(this.volume);
    }
    
    this.show = function(){
        this.volume_bar_dom_obj.show();
    }
    
    this.hide = function(){
        this.volume_bar_dom_obj.hide();
    }
    
    this.save = function(){
        stb.load(

            {
                'type'   : 'stb',
                'action' : 'set_volume',
                'vol'    : this.volume
            },
            
            function(result){

            }

        );
    }
}

player.prototype.seek_bar = new function(){
    
    this.seek_bar_dom_obj = $('seek_bar');
    
    this.show = function(){
        this.seek_bar_dom_obj.show();
    }
    
    this.hide = function(){
        this.seek_bar_dom_obj.hide();
    }
    
    this.set_pos = function(){
        
    }
}

player.prototype.define_media_type = function(cmd){
    
    if (cmd.indexOf('://') > 0){
        return 'stream';
    }else{
        return 'file';
    }
}

player.prototype.play = function(item){
    
    var cmd;
    
    this.cur_media_item = item;
    
    if (typeof(item) == 'object'){
        if (!item.hasOwnProperty('cmd')){
            return;
        }
        cmd = item.cmd;
    }else{
        cmd = item;
    }
    
    this.media_type = this.define_media_type(cmd);
    
    if (this.media_type == 'stream'){
        this.play_now(cmd);
    }else{
        this.create_link(item);
    }
    
}

player.prototype.create_link = function(type,uri){
    
    stb.load(

        {
            'type'   : type,
            'action' : 'create_link',
            'cmd'    : uri,
            'series' : ''
        },
        
        function(result){
            
        }
    );
}

player.prototype.play_now = function(uri){
    
    this.start_time = Date.parse(new Date())/1000;

    if (this.need_show_info){
        this.show_info(cur_media_item);
    }
    
    try{
        stb.Play(uri);
    }catch(e){}
}

player.prototype.stop = function(){
    this.need_show_info = 0;
    try{
        stb.Stop();
    }catch(e){}
}

player.prototype.pause = function(){
        
    if (this.paused){
        try{
            stb.Continue();
        }catch(e){};
        this.paused = false;
        this.pause_dom_obj.hide();
    }else{
        try{
            stb.Pause();
        }catch(e){};
        this.paused = true;
        this.pause_dom_obj.show();
    }
        
}

player.prototype.show_info_after_play = function(){
    this.need_show_info = 1;
}

player.prototype.show_info = function(item){
    
}
/*
 * END Player
 */