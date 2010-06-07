/**
 * Video club info layer constructor.
 * @constructor
 */

function vclub_info(parent){
    
    this.on = false;
    
    this.parent = parent;
   
    this.dom_obj = create_block_element('layer_bg', document.body);
    
    this.main_container = {};
    
    this.film_title = {};
    this.full_info  = {};
    this.film_cover = {};
    
    this.scrollbar  = {};
}

vclub_info.prototype.show = function(item){
    _debug('vclub_info.show');
    
    this.dom_obj.show();
    this.on = true;
    
    this.fill(item);
}

vclub_info.prototype.hide = function(){
    _debug('vclub_info.hide');
    
    this.parent.on = true;
    
    this.full_info.scrollTop = 0;
    this.reset();
    this.dom_obj.hide();
    this.on = false;
    
    this.scrollbar && this.scrollbar.reset && this.scrollbar.reset();
}

vclub_info.prototype.reset = function(){
    _debug('vclub_info.reset');
    
    this.film_title.innerHTML = '';
    this.full_info.innerHTML  = '';
    this.film_cover.innerHTML = '';
}

vclub_info.prototype.init = function(){
    
    this.dom_obj.innerHTML = '<div class="mb_header_first text24_white">ВИДЕО КЛУБ / <span class="text20_white uppercase">информация о фильме</span></div>';
    
    create_block_element('ears_back', this.dom_obj);
    
    this.main_container = create_block_element('mb_main_filminfo', this.dom_obj);
    
    this.film_title = create_block_element('mb_filminfo_name text25_white shadow_dark2', this.main_container);
    
    var info_container  = create_block_element('mb_filminfo_fullinfo text15_white align_justify', this.main_container);
    
    this.full_info = create_block_element('mb_hidden_overflow', info_container);
    
    this.scrollbar = new scrollbar(info_container, this.full_info, {"height" : 350});
    
    info_container.insertBefore(this.scrollbar.dom_obj, this.full_info);
    
    create_block_element('mb_filminfo_trans', this.main_container);
    
    this.film_cover = create_block_element('mb_filminfo_prev', this.main_container);
    
    this.hide();
}

vclub_info.prototype.fill = function(item){
    _debug('vclub_info.fill');
    
    this.film_title.innerHTML = item.name + ' / ' + item.o_name;
    
    this.full_info.innerHTML = '<span>Год выпуска:</span> ' + item.year + '<br>' + 
                     '<span>Жанр:</span> ' + item.genres_str + '<br>' + 
                     '<span>Продолжительность:</span> ' + item.time + ' мин.' +
                     '<div class="hr_filminfo"></div>' + 
                     '<span>Режиссер:</span> ' + item.director + '<br>' +
                     '<span>В ролях:</span> ' + item.actors + 
                     '<div class="hr_filminfo"></div>' + item.description + '<br><br>';
    
    this.film_cover.innerHTML = '<img src="'+ item.screenshot_uri +'" width="240" height="320">';
}

vclub_info.prototype.shift = function(dir){
    _debug('vclub_info.shift', dir);
    
    var top = this.full_info.scrollTop;
    
    _debug('top before: ', top);
    
    if (dir > 0){
        top = top + 40;
    }else{
        top = top - 40;
    }
    
    this.full_info.scrollTop = top;
    
    _debug('top after: ', top);
    _debug('this.full_info.scrollTop: ', this.full_info.scrollTop);
    
    this.scrollbar.refresh();
}

vclub_info.prototype.shift_page = function(dir){
    _debug('vclub_info.shift_page', dir);
    
    if (dir > 0){
        this.full_info.scrollTop = this.full_info.scrollTop + 200;
    }else{
        this.full_info.scrollTop = this.full_info.scrollTop - 200;
    }
    
    this.scrollbar.refresh();
}

vclub_info.prototype.bind = function(){
    
    this.hide.bind(key.EXIT, this).bind(key.LEFT, this);
    
    (function(){
        this.hide();
        this.parent.hide();
        main_menu.show();
    }).bind(key.MENU, this);
    
    this.shift.bind(key.UP, this, -1);
    this.shift.bind(key.DOWN, this, 1);
    
    this.shift_page.bind(key.PAGE_PREV, this, -1);
    this.shift_page.bind(key.PAGE_NEXT, this, 1);
}

loader.next();