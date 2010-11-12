/**
 * USB Disk manager.
 * @constructor
 */
function usbdisk(){
    
    this.mounted = false;
    this.onmount_callbacks  = [];
    this.onumount_callbacks = [];

    this.dirs  = [];
    this.files = [];
    
    try{
        stb.SetListFilesExt('.mpg .mkv .avi .ts .mp4 .wmv .mp3 .ac3 .mov .vob .wav');
    }catch(e){
        _debug(e);
    }
    
    var self = this;
            
    (function(){
        try{
            self.drive_mounted();
        }catch(e){
            _debug(e);
        }
    }).bind(key.USB_MOUNTED);
    
    (function(){
        self.drive_umounted();
    }).bind(key.USB_UNMOUNTED);

    this.check_mounted();
}

usbdisk.prototype.drive_mounted = function(){
    _debug('usbdisk.drive_mounted');
    
    this.mounted = true;
    
    stb.notice.show(get_word('mbrowser_title') + ' ' + get_word('mbrowser_connected'));
    
    var self = this;
    
    window.setTimeout(function(){self.fire_onmount_callbacks()}, 100);
}

usbdisk.prototype.drive_umounted = function(){
    _debug('usbdisk.drive_umounted');
    
    this.mounted = false;
    
    stb.notice.show(get_word('mbrowser_title') + ' ' + get_word('mbrowser_disconnected'));
    
    this.fire_onumount_callbacks();
}

usbdisk.prototype.is_drive_mounted = function(){
    _debug('usbdisk.is_drive_mounted');
    
    if (this.mounted){
        return true;
    }
    
    return false;
}

usbdisk.prototype.check_mounted = function(){
    _debug('usbdisk.check_mounted');
    
    try{
        
        var list = this.read_dir("/media/usbdisk/");
        
        for (var i=0; i < list.length; i++){
            if (!empty(list[i])){
                this.drive_mounted();
                return;
            }
        }
    }catch(e){
        _debug(e);
    }
    
    return;  
}

usbdisk.prototype.read_dir = function(path){
    _debug('usbdisk.read_dir');
    
    try{
        
        var txt = stb.ListDir(path);
        
        _debug(txt);
        
        eval(txt);
        
        this.dirs = dirs;
        this.files = files;
        
        return this.dirs.concat(this.files);
    }catch(e){
        _debug(e);
    }
}

usbdisk.prototype.add_onmount_callback = function(func){
    _debug('usbdisk.add_onmount_callback');
    
    this.onmount_callbacks.push(func);
}

usbdisk.prototype.add_onumount_callback = function(func){
    _debug('usbdisk.add_onumount_callback');
    
    this.onumount_callbacks.push(func);
}

usbdisk.prototype.fire_onmount_callbacks = function(){
    _debug('usbdisk.fire_onmount_callbacks')
    
    for(var i=0; i<this.onmount_callbacks.length; i++){
        this.onmount_callbacks[i]();
    }
}

usbdisk.prototype.fire_onumount_callbacks = function(){
    _debug('usbdisk.fire_onumount_callbacks');
    
    for(var i=0; i<this.onumount_callbacks.length; i++){
        this.onumount_callbacks[i]();
    }
}