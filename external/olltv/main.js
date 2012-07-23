function init(){
    window.moveTo(0, 0);
    window.resizeTo(win.width, win.height);
    loadStyle(win.height+'.css');
    vars.lang = getEnvironmentValue('language');
    //oadScript(lang+'.js','scriptloaded()'); // add lang cheking here
    stb.InitPlayer();
    stb.SetTopWin(0);
    stb.EnableServiceButton(true);
    stb.EnableVKButton(true);
    vars.player_vars.volume = stb.GetVolume();
    vars.player_vars.mute = stb.GetMute();
    vars.sn = stb.RDir('SerialNumber');
    //vars.lang = getEnvironmentValue('language');
    vars.device = stb.RDir("ModelExt");
    stb.SetPIG (1,0,0,0);
    back_location = decodeURIComponent(window.location.search.match(/\?referrer\=.*/));
    back_location = back_location.replace(/\?referrer\=/, '');
	if(/src\=/.test(window.location.search)){
		olltvURL = window.location.search.match(/src\=[\s\S]*/)[0].replace(/src\=/,'');
		debug = true;
	}
    stbEvent = {
        onEvent:eventFunc,
        event: 0
    };
    CUR_LAYER = main_layer;
    log('init finished');
    scriptloaded()
}

function scriptloaded(){
    document.getElementsByClassName('header_btn_back')[0].style.display = 'none';
    sendreq(olltvURL+'home?serial_number='+vars.sn+'&device_type=stb&device_model='+vars.device+'&itemoffset=1', drow_main);
}

function drow_main(text){
    //console.log(text);
    vars.need_reload = false;
    if(!empty(text)){
        main_obj = JSON.parse(text);
    }
    main_obj.splice(main_obj.length, 0, main_obj[0]);
    document.getElementsByClassName('main_list')[0].innerHTML = '';
    vars.focus_line = 0;
    if(main_obj[(vars.focus_line+1)].items.length<2){
        vars.focus_item=0;
    }else{
        if(main_obj[(vars.focus_line+1)].items.length<4){
            vars.focus_item=1;
        }else{
            vars.focus_item=2;
        }
    }
    //vars.focus_item = 2;
    //////////////////////////////////////////
    var div = document.createElement('div');
    div.id = 'line0';
    div.className = 'strip';
    var text = '<div id="subline0">';
    for(var i = 0;i<main_obj[0].items_count;i++){
        text+='<div id="genre'+i+'" class="item strip_genre">'+main_obj[0].items[i].title.replace(/\//,'<br>')+'</div>'
    }
    //text+='<div id="genre'+i+'" class="item strip_genre">Плейлисты</div></div>'
    //main_obj[0].items_count++
    div.innerHTML = text;
    document.getElementsByClassName('main_list')[0].appendChild(div);
    $('line'+vars.focus_line).className = 'strip_act';
    $('genre'+vars.focus_cat).className = 'item strip_genre_act';
    //////////////////////////////////////////
    var div = document.createElement('div');
    div.id = 'line1';
    div.className = 'strip';
    text = '';
    if(main_obj[1].items.length-1<vars.focus_item){
        text = '<div id="subline1"><div class="strip_text">'+main_obj[1].block_title+' <div class="strip_text_count">'+main_obj[1].items[0].order+' из '+main_obj[1].items_number+'</div></div>';
    }else{
        text = '<div id="subline1"><div class="strip_text">'+main_obj[1].block_title+' <div class="strip_text_count">'+main_obj[1].items[vars.focus_item].order+' из '+main_obj[1].items_number+'</div></div>';
    }
    for(var y = 0; y < main_obj[1].items.length && y<vars[win.height].maxitems_visible;y++){
        text+='<div id="lineObj_1'+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[1].items[y].src+'" /></div>';
    }
    text += '</div>'
    div.innerHTML = text;
    document.getElementsByClassName('main_list')[0].appendChild(div);
    if(main_obj[1].items.length < 2){
        $('lineObj_10').style.marginLeft = vars[win.height].item_step*2+'px';
    }else{
        if(main_obj[1].items.length < 4){
            $('lineObj_10').style.marginLeft = vars[win.height].item_step+'px';
        }
    }
    //////////////////////////////////////////
    if(main_obj.length>3){
        var div = document.createElement('div');
        div.id = 'line2';
        div.className = 'strip';
        text = '';
        if(main_obj[2].items.length-1<vars.focus_item){
            text = '<div id="subline2"><div class="strip_text">'+main_obj[2].block_title+' <div class="strip_text_count">'+main_obj[2].items[0].order+' из '+main_obj[2].items_number+'</div></div>';
        }else{
            text = '<div id="subline2"><div class="strip_text">'+main_obj[2].block_title+' <div class="strip_text_count">'+main_obj[2].items[vars.focus_item].order+' из '+main_obj[2].items_number+'</div></div>';
        }
        log(objToString(main_obj[2].items[0]))
        //    if(main_obj[2].items.length < vars[win.height].maxitems_visible){
        //        div.innerHTML+='<div class="strip_item_empty"><img src="" /></div>';
        //    }
        for(var j = 0; j < main_obj[2].items.length && j<vars[win.height].maxitems_visible;j++){
            text+='<div id="lineObj_2'+j+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[2].items[j].src+'" /></div>';
        }
        text += '</div>'
        div.innerHTML = text;
        document.getElementsByClassName('main_list')[0].appendChild(div);
        if(main_obj[2].items.length < 2){
            $('lineObj_20').style.marginLeft = vars[win.height].item_step*2+'px';
        }else{
            if(main_obj[2].items.length < 4){
                $('lineObj_20').style.marginLeft = vars[win.height].item_step+'px';
            }
        }
    }
    //////////////////////////////////////////
    //$('headerTitle').innerHTML = 'Главная';
    $('subline0').style.marginLeft = ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
    $('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">'+main_obj[0].items[vars.focus_cat].title+'</div>'
}

function rebuildMain(text,dir){
    //console.log(text);
    var new_block = JSON.parse(text);
    main_obj[vars.focus_line] = new_block;
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
//    if(main_obj[vars.focus_line].items.length < vars[win.height].maxitems_visible){
//        html+='<div class="strip_item_empty"><img src="" /></div>';
//    }
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    switch(dir){
        case 'bwd':
            //$('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
            vars.focus_item = vars[win.height].maxitems_visible-2;
            vars.cur_itemOnLine--;
            if($('lineObj_'+vars.focus_line+vars.focus_item)){
                $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
            }else{
                vars.focus_item--;
                vars.cur_itemOnLine--;
                $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
            }
        break;
        case 'fwd':
            //$('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
            vars.focus_item = 0;
            vars.cur_itemOnLine++;
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
        break;
    }
    if(main_obj[vars.focus_line].items.length < 2){
        $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step*2+'px';
    }else{
        if(main_obj[vars.focus_line].items.length < 4){
            $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step+'px';
        }
    }
    refill_shortInfo();
}

function rebuildMain2(text,dir){
    //console.log(text);
    var new_block = JSON.parse(text);
    main_obj[vars.focus_line] = new_block;
    switch(dir){
        case 'bwd':
            var item = main_obj[vars.focus_line].items.splice(main_obj[vars.focus_line].items.length-1,1);
            main_obj[vars.focus_line].items.splice(0, 0,item[0]) 
        break;
        case 'fwd':
            var item = main_obj[vars.focus_line].items.splice(0,1);
            main_obj[vars.focus_line].items.splice(main_obj[vars.focus_line].items.length, 0,item[0]) 
        break;
    }
    
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    switch(dir){
        case 'bwd':
            vars.cur_itemOnLine--;
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
        break;
        case 'fwd':
            vars.cur_itemOnLine++;
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
        break;
    }
    if(main_obj[vars.focus_line].items.length < 2){
        $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step*2+'px';
    }else{
        if(main_obj[vars.focus_line].items.length < 4){
            $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step+'px';
        }
    }
    refill_shortInfo();
}

function prev_item(){
    var item = main_obj[vars.focus_line].items.splice(main_obj[vars.focus_line].items.length-1,1);
    main_obj[vars.focus_line].items.splice(0, 0,item[0])
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    if(vars.cur_itemOnLine>0){
        vars.cur_itemOnLine--;
    }else{
        vars.cur_itemOnLine = main_obj[1].items_number-1;
    }
    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
    if(main_obj[vars.focus_line].items.length < 2){
        $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step*2+'px';
    }else{
        if(main_obj[vars.focus_line].items.length < 4){
            $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step+'px';
        }
    }
    refill_shortInfo();
}

function next_item(){
    var item = main_obj[vars.focus_line].items.splice(0,1);
    main_obj[vars.focus_line].items.splice(main_obj[vars.focus_line].items.length, 0,item[0]) 
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    if(vars.cur_itemOnLine<main_obj[1].items_number-1){
        vars.cur_itemOnLine++;
    }else{
        vars.cur_itemOnLine=0;
    }
    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
    if(main_obj[vars.focus_line].items.length < 2){
        $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step*2+'px';
    }else{
        if(main_obj[vars.focus_line].items.length < 4){
            $('lineObj_'+vars.focus_line+'0').style.marginLeft = vars[win.height].item_step+'px';
        }
    }
    refill_shortInfo();
}

function refill_shortInfo(){
    var html = '<div class="infopanel_shadow"></div>';
        html+= '<div class="text_title">'+main_obj[vars.focus_line].items[vars.focus_item].title;
        if(main_obj[vars.focus_line].items[vars.focus_item].hd_quality!=0){
            html+= '<span class="hd_ico"></span>'
        }
        html+= '</div>';
        html+= '<div class="gray_text">';
        if(main_obj[vars.focus_line].items[vars.focus_item].release_date){
            html+= '<span class="year_text">'+main_obj[vars.focus_line].items[vars.focus_item].release_date+'</span> |';
        }
        if(main_obj[vars.focus_line].items[vars.focus_item].country!=''){
            html+= main_obj[vars.focus_line].items[vars.focus_item].country+' | ';
        }
        if(main_obj[vars.focus_line].items[vars.focus_item].genre!=''){
            html+= main_obj[vars.focus_line].items[vars.focus_item].genre;
        }
        html+= '</div>';
        html+= '<div id="descr">'+main_obj[vars.focus_line].items[vars.focus_item].descr+'</div>'
//        if(main_obj[vars.focus_line].items[vars.focus_item].rating){
//            html+= '<div class="rating"><div id="small_rate" class="rating_act"></div>'+main_obj[vars.focus_line].items[vars.focus_item].rating+'</div>'
//        }
        if(main_obj[vars.focus_line].items[vars.focus_item].is_free){
            html+= '<div class="infopanel_bottom"><div class="infopanel_bottom_link">Cмотри бесплатно</div></div>'
        }else{
            if(main_obj[vars.focus_line].items[vars.focus_item].is_subscription_paid == 1){
                html+= '<div class="infopanel_bottom"><div class="infopanel_bottom_link">Cмотреть</div></div>'
            }else{
                if(main_obj[vars.focus_line].items[vars.focus_item].subs_type=='tvod'){
                    html+= '<div class="infopanel_bottom"><div class="infopanel_bottom_link">Cмотри за '+main_obj[vars.focus_line].items[vars.focus_item].cost+' '+main_obj[vars.focus_line].items[vars.focus_item].cost_currency+'</div></div>'
                }else{
                    html+= '<div class="infopanel_bottom"><div class="infopanel_bottom_link">Смотри в подписке</div></div>'
                }
            } 
        }
        
        $('short_info').innerHTML = html;
//        if(main_obj[vars.focus_line].items[vars.focus_item].rating){
//            $('small_rate').style.width = main_obj[vars.focus_line].items[vars.focus_item].rating*10+'px';
//        }
    /*
        <div class="infopanel_shadow"></div>
        <div class="text_title">Прямая трансляция церемонии вручения премии «Оскар»<span class="hd_ico"></span></div>
        <div class="gray_text"><span class="year_text">2008</span> | Франция, США | Исторический, Драма</div>
        <div id="descr">После провала очередной операции и гибели любовницы профессиональный киллер Джек решает, что следующее задание, которое поручает ему таинственная женщина, будет последним в его карьере. Он тайно поселяется в маленьком итальянском городке, затерявшемся в...</div>
        <div class="rating"><div class="rating_act"></div>6.8</div>
        <div class="infopanel_bottom">
            <div class="infopanel_bottom_link">смотри бесплатно</div>
        </div>
     */
}

function drow_info(text){
    info_obj = JSON.parse(text);
    //console.log(text);
    if(info_obj.series){
        drow_series_info();
    }else{
        drow_film_info();
    }
    switchLayer(info_layer);
}

function drow_series_info(){
    var page = $('info_page');
    var text = '';
        text =  '<div id="infoTitle" class="fullinfo_title">'+info_obj.title+'</div>';
        if(info_obj.seasons){
            text += '<div class="fullinfo_stripe">';
            text += '<div id="seasons">'
            for(var b = 0;b<info_obj.seasons.length && b<9;b++){
                text += '<div class="fullinfo_btn_series">'+info_obj.seasons[b].season_title+'</div>'
            }
            text += '</div>';
            text += '</div>';
        }
        if(info_obj.series){
            text += '<div class="fullinfo_stripe">';
            text += '<div id="series">'
            for(var b = 0;b<info_obj.series.length && b<9;b++){
                text += '<div class="fullinfo_btn_series">'+info_obj.series[b].series_title+'</div>'
            }
            text += '</div>';
            text += '</div>';
        }
        text += '<div class="fullinfo_content">'
        text +=     '<div class="fullinfo_menu">';
        if(info_obj.start_play_from != 0){
            text += '       <div class="fullinfo_btn_large">Продолжить просмотр</div>';
        }
        if(info_obj.is_free || info_obj.is_subscription_paid == 1){
            text += '<div class="fullinfo_btn_large">Смотреть сначала</div>'
        }else{
            if(info_obj.subs_type=='tvod'){
                text += '<div class="fullinfo_btn_large">Смотреть за '+info_obj.cost+' '+info_obj.cost_currency+'</div>'
            }else{
                text += '<div class="fullinfo_btn_large">Смотреть в подписке</div>'
            }
        }
        if(info_obj.is_favorite == 0){
            text += '<div class="fullinfo_btn_large">Добавить в избранное</div>'
        }else{
            text += '<div class="fullinfo_btn_large">Удалить из избранного</div>'
        }
        if(info_obj.audio_language.length>0){
            text += '<div class="fullinfo_menu_block"><div>Звуковые дорожки на выбор</div>';
            for(var i = 0;i<info_obj.audio_language.length;i++){
                text += '<div  class="fullinfo_btn_small">'+info_obj.audio_language[i].title+'</div>'
            }
            text += '</div>';
        }
        if(info_obj.subtitles.length>0){
            text += '<div class="fullinfo_menu_block"><div>Субтитры на выбор</div>';
            for(var i = 0;i<info_obj.subtitles.length;i++){
                text += '<div  class="fullinfo_btn_small">'+info_obj.subtitles[i].title+'</div>'
            }
            text += '</div>';
        }
        text += '</div>';
        text += '<div class="fullinfo_toptext"><div>';
        if(info_obj.release_date != ''){
            text += '<span id="info_year" class="year_text">'+info_obj.release_date+'</span> | ';
        }
        if(info_obj.country != ''){
            text += info_obj.country+' | ';
        }
        if(info_obj.genre){
            text += info_obj.genre;
        }
        if(info_obj.hd_quality == 1){
            text += '<span class="hd_ico"></span>';
        }
        if(!empty(info_obj.src)){
            text += '<div class="fullinfo_cover">'
            text +=     '<img src="'+info_obj.src+'" /><br /><br />'
//        if(info_obj.rating){
//            text +=     '<div class="rating"><div id="cur_rating" class="rating_act"></div>'+info_obj.rating+'</div>'
//        }
        text += '</div>';
        }
            text += '<div class="fullinfo_text">';
        if(!empty(info_obj.descr)){
            text += info_obj.descr+'<br /><br />';
        }
        if(!empty(info_obj.actors)){
            text += '<b>Актеры:</b> '+info_obj.actors+'<br />';
        }
        if(!empty(info_obj.authors)){
            text += '<b>Режисер:</b> '+info_obj.authors;
        }
        text += '</div>';
        text += '</div>';
        text += '</div>';
        
        
        text += '<div id="pgup">Вверх</div>';
        text += '<div id="pgdown">Вниз</div>';
        page.innerHTML = text;
        
        var elem = document.getElementsByClassName("fullinfo_toptext")[0];
        var elemwidth = window.getComputedStyle(elem,null).getPropertyValue("height");
        log(document.getElementsByClassName("fullinfo_text")[0].scrollHeight)
        log(window.getComputedStyle(document.getElementsByClassName("fullinfo_text")[0],null).getPropertyValue("height"))
        log(elemwidth)
//        if(!empty(info_obj.rating)){
//            $('cur_rating').style.width = info_obj.rating*10+'px'; //If need to resize rating, put 10 in vars for 720p
//        }
        if(info_obj.seasons){
            vars.info_focus_line=0;
        }else{
            if(info_obj.series){
                vars.info_focus_line=1;
            }else{
                if(info_obj.start_play_from != 0){
                    vars.info_focus_line=2;
                }else{
                    vars.info_focus_line=3;
                }
            }
        }
        
        //"real_info_nav":[],
        vars.info_focus_obj=[0,0,0,0];
        vars.info_focus_btn=[0,0,0,0];
        vars.info_sel_obj=[0,0,0,0];
        info_setActive();
}

function drow_film_info(){
    var page = $('info_page');
    var text = '';
        text =  '<div id="infoTitle" class="fullinfo_title">'+info_obj.title+'</div>';
        text += '<div class="fullinfo_content">'
        text +=     '<div class="fullinfo_menu">';
        if(info_obj.start_play_from != 0){
            text += '       <div class="fullinfo_btn_large">Продолжить просмотр</div>';
        }
        if(info_obj.is_free || info_obj.is_subscription_paid == 1){
            text += '<div class="fullinfo_btn_large">Смотреть сначала</div>'
        }else{
            if(info_obj.subs_type=='tvod'){
                text += '<div class="fullinfo_btn_large">Смотреть за '+info_obj.cost+' '+info_obj.cost_currency+'</div>'
            }else{
                text += '<div class="fullinfo_btn_large">Смотреть в подписке</div>'
            }
        }
        if(empty(info_obj.is_favorite)){
            text += '<div class="fullinfo_btn_large">Добавить в избранное</div>'
        }else{
            text += '<div class="fullinfo_btn_large">Удалить из избранного</div>'
        }
        if(info_obj.audio_language.length>0){
            text += '<div class="fullinfo_menu_block"><div>Звуковые дорожки на выбор</div>';
            for(var i = 0;i<info_obj.audio_language.length;i++){
                text += '<div  class="fullinfo_btn_small">'+info_obj.audio_language[i].title+'</div>'
            }
            text += '</div>';
        }
        if(info_obj.subtitles.length>0){
            text += '<div class="fullinfo_menu_block"><div>Субтитры на выбор</div>';
            for(var i = 0;i<info_obj.subtitles.length;i++){
                text += '<div  class="fullinfo_btn_small">'+info_obj.subtitles[i].title+'</div>'
            }
            text += '</div>';
        }
        text += '</div>';
        text += '<div class="fullinfo_toptext">';
        if(info_obj.release_date != ''){
            text += '<span id="info_year" class="year_text">'+info_obj.release_date+'</span> | ';
        }
        if(info_obj.country != ''){
            text += info_obj.country+' | ';
        }
        if(info_obj.genre){
            text += info_obj.genre;
        }
        if(info_obj.hd_quality == 1){
            text += '<span class="hd_ico"></span>';
        }
        if(!empty(info_obj.src)){
            text += '<div class="fullinfo_cover">'
            text +=     '<img src="'+info_obj.src+'" /><br /><br />'
//        if(info_obj.rating){
//            text +=     '<div class="rating"><div id="cur_rating" class="rating_act"></div>'+info_obj.rating+'</div>'
//        }
        text += '</div>';
        }
            text += '<div class="fullinfo_text">';
        if(!empty(info_obj.descr)){
            text += info_obj.descr+'<br /><br />';
        }
        if(!empty(info_obj.actors)){
            text += '<b>Актеры:</b> '+info_obj.actors+'<br />';
        }
        if(!empty(info_obj.director)){
            text += '<b>Режисер:</b> '+info_obj.director;
        }
        //text += '</div>';
        text += '</div>';
        text += '</div>';
        text += '<div id="pgup">Вверх</div>';
        text += '<div id="pgdown">Вниз</div>';
        page.innerHTML = text;
//        if(!empty(info_obj.rating)){
//            $('cur_rating').style.width = info_obj.rating*10+'px'; //If need to resize rating, put 10 in vars for 720p
//        }
        if(info_obj.start_play_from != 0){
            vars.info_focus_line=2;
        }else{
            vars.info_focus_line=3;
        }
        info_setActive();
}

function info_setActive(){
    if(info_obj.seasons){
        $("seasons").style.marginLeft = vars[win.height].seasonSel_pos_px+'px';
        $("seasons").childNodes[vars.info_focus_obj[0]].className = 'fullinfo_btn_series_act';
    }
    if(info_obj.series){
        $("series").style.marginLeft = vars[win.height].seriesSel_pos_px+'px';
        if(info_obj.seasons){
            $("series").childNodes[vars.info_focus_obj[1]].className = 'fullinfo_btn_series_press';
        }else{
            $("series").childNodes[vars.info_focus_obj[1]].className = 'fullinfo_btn_series_act';
        }
    }else{
        document.getElementsByClassName('fullinfo_btn_large')[0].className = 'fullinfo_btn_large_act';
    }
    if(info_obj.audio_language.length>0){
        document.getElementsByClassName('fullinfo_menu_block')[0].getElementsByClassName('fullinfo_btn_small')[vars.info_focus_obj[2]].className = 'fullinfo_btn_small_press';
    }
    if(info_obj.subtitles.length>0){
        document.getElementsByClassName('fullinfo_menu_block')[1].getElementsByClassName('fullinfo_btn_small')[vars.info_focus_obj[3]].className = 'fullinfo_btn_small_press';
    }
    
}

function getSuggest(){
    var text = $('search_input').value
    sendreq(olltvURL+'searchSuggest?q='+encodeURI(text), buildsugg)
}

function buildsugg(text){
    //console.log(text);
    if(text != ''){
        suggest_obj = JSON.parse(text);
        if(suggest_obj.items){
            //$('search_count').innerHTML = '<span class="black">'+suggest_obj.items.length+'</span> фильмов';
            document.getElementsByClassName('search_box')[0].style.display = 'block';
            $('suggests').innerHTML = '';
            var sug_text = '';
            for(var i = 0;i<5&&i<suggest_obj.items.length;i++){
                sug_text+='<div id="sug_'+(i+1)+'" class="search_result">'+suggest_obj.items[i].name+'</div>'
            }
            $('suggests').innerHTML = sug_text;
        }else{
            document.getElementsByClassName('search_box')[0].style.display = 'none';
            //$('search_count').innerHTML = 0;
            //$('suggests').innerHTML = '<div id="sug_1" class="search_result">Ничего не найдено</div>';
        }
    }
}

function show_message(text,text2){
    CUR_SUBLAYER = CUR_LAYER;
    CUR_LAYER = alert_layer;
    $('message_html').innerHTML = text;
    vars.alert_focus = 0;
    vars.alert_arr = document.getElementsByClassName('msgbtn').length;
    //document.getElementsByClassName('subscript_btn')[0].className = 'subscript_btn_act';
    $('message').style.display = 'block';
}

function hide_message(){
    CUR_LAYER = CUR_SUBLAYER;
    
    if(PREV_LAYER == 3){
        if(CUR_LAYER == info_layer){
            sendreq(olltvURL+'info?id='+suggest_obj.items[(vars.searchline-1)].id+'&showfull=true', drow_info);
        }
    }else{
        if(CUR_LAYER == info_layer){
            sendreq(olltvURL+'info?id='+main_obj[vars.focus_line].items[vars.focus_item].id+'&showfull=true', drow_info);
        }
    }
    $('message').style.display = 'none';
    
}

function conf_play(){
    CUR_LAYER = CUR_SUBLAYER;
    $('message').style.display = 'none';
    if(info_obj.series){
        sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
    }else{
        sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj);
    }
}

function changeAspect(){
    if(aspectTimer) {clearTimeout(aspectTimer);}
    aspectTimer = null;
    vars.player_vars.aspect_current = (vars.player_vars.aspect_current + 1) % 4;
    stb.SetAspect(vars.player_vars.aspects[vars.player_vars.aspect_current].mode);
    $('screenAspect').style.backgroundImage = 'url(' + vars.player_vars.aspects[vars.player_vars.aspect_current].img + ')';
    $('screenAspect').style.display = 'block';
    aspectTimer = setTimeout(function() {$('screenAspect').style.display = 'none';}, 3000);
}

function switchLayer(layer){
    log('Switch layer from <'+layers[CUR_LAYER].name+' : '+CUR_LAYER+'> to <'+layers[layer].name+' : '+layer+'>');
    switch(CUR_LAYER){
        
        case 0:
           //document.getElementsByClassName('header_btn_red')[0].style.display = 'none';
        break;
        case 2:
           $('loading').style.display = 'none';
           $('inteface').style.display = 'block';
           document.body.style.background = 'url(img/bg.png)';
           document.getElementsByClassName('header')[0].style.display = 'block';
        break;
    }
    if(CUR_LAYER == info_layer && layer == main_layer){
        if(unscroled_area){
            document.getElementsByClassName('header_btn_back')[0].style.display = 'none';
        }
    }
    if($(layers[layer].name) && $(layers[CUR_LAYER].name)){
        $(layers[CUR_LAYER].name).style.display = 'none';
        $(layers[layer].name).style.display = 'block';
    }
    PREV_LAYER = CUR_LAYER;
    CUR_LAYER = layer;
    switch(layer){
        case 0:
            if(vars.need_reload){
                sendreq(olltvURL+'home?serial_number='+vars.sn+'&device_type=stb&device_model='+vars.device+'&itemoffset=1', drow_main);
            }
            //document.getElementsByClassName('header_btn_red')[0].style.display = 'block';
        break;
        case 2:
           $('loading').style.display = 'block';
           $('inteface').style.display = 'none';
           document.body.style.background = 'none';
           document.getElementsByClassName('header')[0].style.display = 'none';
        break;
        case 3:
        break;
    }
}
 
function switchSubLayer(layer){
    switch(SUB_CUR_LAYER){

    }
    SUB_CUR_LAYER = layer;
    switch(SUB_CUR_LAYER){

    }
}
