/**
 * Alert.
 * @constructor
 */

function _alert(){
    
    this.on = false;
    
    this.dom_obj = {};
    
    this.txt_container = {};
    
    this.hide_timer = 2000;
    this.hide_to;
    
    this.init();
}

_alert.prototype.init = function(){
    try{
        this.dom_obj = create_block_element('mb_info');
        
        var alert_outer = create_block_element('alert_outer', this.dom_obj);
        
        var alert_container = create_block_element('alert_container', alert_outer);
        
        var table = document.createElement('table');
        alert_container.appendChild(table);
        
        var tr = document.createElement('tr');
        table.appendChild(tr);
        
        var td = document.createElement('td');
        td.setClass('mb_info_lt_head');
        tr.appendChild(td);
        
        td = document.createElement('td');
        td.setClass('mb_info_top_head');
        tr.appendChild(td);
        
        td = document.createElement('td');
        td.setClass('mb_info_rt_head');
        tr.appendChild(td);
        
        tr = document.createElement('tr');
        table.appendChild(tr);
        
        td = document.createElement('td');
        td.setClass('mb_info_lb');
        tr.appendChild(td);
        
        td = document.createElement('td');
        td.setClass('mb_info_main');
        tr.appendChild(td);
        
        var icon = create_block_element('ico_alert', td);
        
        this.txt_container = create_inline_element('', td);
        
        td = document.createElement('td');
        td.setClass('mb_info_rb');
        tr.appendChild(td);
        
        this.hide();
    }catch(e){
        _debug(e);
    }
}

_alert.prototype.show = function(txt){
    _debug('_alert.show');
    
    if (this.hide_to){
        window.clearTimeout(this.hide_to);
    }
    
    var txt = txt || 'empty';
    
    this.txt_container.innerHTML = txt;
    
    this.dom_obj.show();
    this.dom_obj.on = true;
    
    this.t_hide();
}

_alert.prototype.t_hide = function(){
    _debug('_alert.t_hide');
    
    var self = this;
    
    this.hide_to = window.setTimeout(function(){
        self.hide();
        
    }, this.hide_timer);
}

_alert.prototype.hide = function(){
    _debug('_alert.hide');
    
    this.dom_obj.hide();
    this.dom_obj.on = false;
}

