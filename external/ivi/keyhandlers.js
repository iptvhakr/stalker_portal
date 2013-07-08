function body_keydown(e){
    var key = e.keyCode || e.which;
    var ret = false;
    log('MAIN keyhandler key: '+key+' Alt: '+e.altKey+' Ctrl: '+e.ctrlKey+' Shift: '+e.shiftKey+' Target_id: '+e.target.id)
    if(newAlert_on && key != 27){
        return;
    }
	if(key == 85 && e.altKey){
		if(CUR_LAYER == 5 && !powerOff){
			clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            switchLayer(info_layer);
		}
		powerOff = !powerOff;
		stb.StandBy(powerOff);
	}
    switch(CUR_LAYER){
        case 0:
            ret=authPage_keyhandler(e);
        break;
        case 1:
            ret=catPage_keyhandler(e);
        break;
        case 2:
            ret=subcatsPage_keyhandler(e);
        break;
        case 3:
            ret=searchPage_keyhandler(e);
        break;
        case 4:
            ret=contentPage_keyhandler(e);
        break;
        case 5:
            ret=playerPage_keyhandler(e);
        break;
        case 6:
            ret=genrePage_keyhandler(e);
        break;
        case 7:
            ret=infoPage_keyhandler(e);
        break;
        case 8:
            ret=confPage_keyhandler(e);
        break;
        case 9:
            ret=kayboardPage_keyhandler(e);
        break;
    }
    if(!empty(ret)){
        switch(key){
            default:
                log('+++global handler active+++');
            break;
            case 48:
                //console.log(document.body.innerHTML);
            break;
            case 107:
                if(vars.player_vars.mute == 1){
                    vars.player_vars.mute = 0;
                    $('mute').style.display = 'none';
                }
                if(vars.player_vars.volume<96){
                    clearTimeout(set_volume);
                    $('volumeForm').style.display = 'block';
                    vars.player_vars.volume += 5;
                    stb.SetVolume(vars.player_vars.volume);
                    $('volume_bar').style.width = vars.player_vars.volume*2+'px';
                    $('volume_num').innerHTML = vars.player_vars.volume+'%';
                    set_volume = window.setTimeout(function(){$('volumeForm').style.display = 'none'},3000)
                }else{
                    clearTimeout(set_volume);
                    $('volumeForm').style.display = 'block';
                    vars.player_vars.volume = 100;
                    stb.SetVolume(vars.player_vars.volume);
                    $('volume_bar').style.width = vars.player_vars.volume*2+'px';
                    $('volume_num').innerHTML = vars.player_vars.volume+'%';
                    set_volume = window.setTimeout(function(){$('volumeForm').style.display = 'none'},3000)
                }
            break;
            case 109:
                if(vars.player_vars.mute == 1){
                    vars.player_vars.mute = 0;
                    $('mute').style.display = 'none';
                }
                if(vars.player_vars.volume>4){
                    clearTimeout(set_volume);
                    $('volumeForm').style.display = 'block';
                    vars.player_vars.volume -= 5; 
                    stb.SetVolume(vars.player_vars.volume);
                    $('volume_bar').style.width = vars.player_vars.volume*2+'px';
                    $('volume_num').innerHTML = vars.player_vars.volume+'%';
                    set_volume = window.setTimeout(function(){$('volumeForm').style.display = 'none'},3000)
                }else{
                    clearTimeout(set_volume);
                    $('volumeForm').style.display = 'block';
                    vars.player_vars.volume = 0; 
                    stb.SetVolume(vars.player_vars.volume);
                    $('volume_bar').style.width = vars.player_vars.volume*2+'px';
                    $('volume_num').innerHTML = vars.player_vars.volume+'%';
                    set_volume = window.setTimeout(function(){$('volumeForm').style.display = 'none'},3000)
                }
            break;
            case 192:
                if(e.altKey){
                    //clearTimeout(set_volume);

                    if(vars.player_vars.mute == 0){
                        vars.player_vars.mute = 1;
                        stb.SetMute(1);
                        $('mute').style.display = 'block';
                    }else{
                        vars.player_vars.mute = 0;
                        stb.SetMute(0);
                        $('mute').style.display = 'none';
                    }
                    //set_volume = window.setTimeout(function(){$('volumeForm').style.display = 'none'},3000)
                }
            break;
        }
    }
}
function authPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('AUTH keyhandler key: '+key);
    switch(key){
        case 13:
            if(e.target.type){
                if(e.target.type == 'text'){
                    stb.ShowVirtualKeyboard();
                }
            }
        break;
        case 27:
            if (back_location){
                window.location = back_location;
            }
        break;
        case 37:
            if(e.target.id == 'login_cancel'){
                layer_indexes.active[CUR_LAYER].login_form --;
                $(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]).focus();
            }
        break;
        case 39:
            if(e.target.id == 'login_ok'){
                layer_indexes.active[CUR_LAYER].login_form ++;
                $(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]).focus();
            }
        break;
        case 38:
            if(layer_indexes.active[CUR_LAYER].login_form>0){
                layer_indexes.active[CUR_LAYER].login_form --;
            }else{
                layer_indexes.active[CUR_LAYER].login_form = layer_indexes.array[CUR_LAYER].login_form.length-1
            }
            log(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]);
            $(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]).focus();
        break;
        case 40:
            if(layer_indexes.active[CUR_LAYER].login_form<layer_indexes.array[CUR_LAYER].login_form.length-1){
                layer_indexes.active[CUR_LAYER].login_form ++;
            }else{
                layer_indexes.active[CUR_LAYER].login_form = 0;
            }
            log(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]);
            $(layer_indexes.array[CUR_LAYER].login_form[layer_indexes.active[CUR_LAYER].login_form]).focus();
        break;
    }
}
function catPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('CATS keyhandler key: '+key);
    switch(key){
        case 8:
            if(!left_side){
                $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box';
                $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'none';
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item_act';
                }
                //vars.cont_page_x = 0;
                //vars.cont_page_y = 0;
                left_side = true;
            }else{
                left_side = false;
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item';
                }
                $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box_act';
                $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'block';
            }
        break;
        case 13:
            if(left_side){
                vars.content_page = 0;
                vars.cont_page_x = 0;
                vars.cont_page_y = 0;
                switch(vars.catsArr[vars.catSel].id){
                    case 'cat_0':
                        vars.curCatSel = vars.catSel;
                        sendreq(iviURL+'categories/', drow_cats);
                    break;
                    case 'cat_1':
                        vars.curCatSel = vars.catSel;
                        vars.cats[vars.curCatSel-1][2].year_from = null;
                        vars.cats[vars.curCatSel-1][2].year_to = null;
                        vars.cats[vars.curCatSel-1][0].id = null;
                        vars.cats[vars.curCatSel-1][0].title = '';
                        vars.cats[vars.curCatSel-1][1].id = null;
                        vars.cats[vars.curCatSel-1][1].title = '';
                        open_submenu();
                        pop_layer = false;
                        sendreq(iviURL+'/catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new&category=14', init_contentlist)
                    break;
                    case 'cat_2':
                        vars.curCatSel = vars.catSel;
                        vars.cats[vars.curCatSel-1][2].year_from = null;
                        vars.cats[vars.curCatSel-1][2].year_to = null;
                        vars.cats[vars.curCatSel-1][0].id = null;
                        vars.cats[vars.curCatSel-1][0].title = '';
                        vars.cats[vars.curCatSel-1][1].id = null;
                        vars.cats[vars.curCatSel-1][1].title = '';
                        open_submenu();
                        pop_layer = false;
                        sendreq(iviURL+'/catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new&category=15', init_contentlist)
                    break;
                    case 'cat_3':
                        vars.curCatSel = vars.catSel;
                        vars.cats[vars.curCatSel-1][2].year_from = null;
                        vars.cats[vars.curCatSel-1][2].year_to = null;
                        vars.cats[vars.curCatSel-1][0].id = null;
                        vars.cats[vars.curCatSel-1][0].title = '';
                        vars.cats[vars.curCatSel-1][1].id = null;
                        vars.cats[vars.curCatSel-1][1].title = '';
                        open_submenu();
                        pop_layer = false;
                        sendreq(iviURL+'/catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new&category=17', init_contentlist)
                    break;
                    case 'cat_4':
                        vars.curCatSel = vars.catSel;
                        vars.cats[vars.curCatSel-1][2].year_from = null;
                        vars.cats[vars.curCatSel-1][2].year_to = null;
                        vars.cats[vars.curCatSel-1][0].id = null;
                        vars.cats[vars.curCatSel-1][0].title = '';
                        vars.cats[vars.curCatSel-1][1].id = null;
                        vars.cats[vars.curCatSel-1][1].title = '';
                        open_submenu();
                        pop_layer = false;
                        sendreq(iviURL+'/catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new&category=16', init_contentlist)
                    break;
                    case 'cat_5':
                        vars.curCatSel = vars.catSel;
                        CUR_LAYER = layer_search;
                        init_search_page();
                    break;
                    case 'cat_6':
                        switchLayer(layer_auth);
                    break;
                    case 'item_0':
                        sendreq(iviURL+'categories/',count_filters);
                        focus_in_filters = true;
                    break;
                    case 'item_1':
                        filters('','country');
                        focus_in_filters = true;
                    break;
                    case 'item_2':
                        filters('','year');
                        focus_in_filters = true;
                    break;
                }
            }else{
                if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].genres[0]==169){
                    show_qestion('$(\'black_bg\').style.display = \'none\';newAlert_on = false;document.getElementsByClassName(\'waiting_form\')[0].innerHTML=\'Загрузка...\';CUR_LAYER=layer_cats;cats_enter();','$(\'black_bg\').style.display = \'none\';newAlert_on = false;document.getElementsByClassName(\'waiting_form\')[0].innerHTML=\'Загрузка...\';CUR_LAYER=layer_cats;')
                }else{
                    cats_enter()
                }
            }
        break;
        case 27:
            if (back_location){
                window.location = back_location;
            }
        break;
        case 37:
            if(left_side){
                
            }else{
                if(SUB_CUR_LAYER == sub_layer_video){
                    content_move(key);
                }else{
                    if(vars.cont_page_x>0){
                        $('promo_p'+vars.cont_page_x).className = 'poster_box';
                        vars.cont_page_x --;
                        $('promo_p'+vars.cont_page_x).className = 'poster_box_act';
                        $('promo').style.background = 'url('+promo_obj[vars.cont_page_x].img_ipad.path+')';
                        $('promo').getElementsByClassName('pop_title')[0].innerHTML = promo_obj[vars.cont_page_x].title;
                    }
                    else{
                        $('promo_p'+vars.cont_page_x).className = 'poster_box';
                        if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                            $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                        }else{
                            $(vars.catsArr[vars.catSel].id).className = 'item_act';
                        }
                        vars.cont_page_x = 0;
                        vars.cont_page_y = 0;
                        left_side = true;
                    }
                }
            }
        break;
        case 39:
            if(left_side){
                if(dataset[(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))]){
                    left_side = false;
                    if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                        $(vars.catsArr[vars.catSel].id).className = 'menu cat_item_text';
                    }else{
                        $(vars.catsArr[vars.catSel].id).className = 'item';
                    }
                    if(SUB_CUR_LAYER == sub_layer_video){
                        //vars.cont_page_x = 0;
                        $('video_p'+vars.cont_page_x).className = 'poster_box_act';
                        $('video_p'+vars.cont_page_x).getElementsByClassName('movie_desc')[0].style.display = 'block';
                    }else{
                        vars.cont_page_x = 0;
                        $('promo_p'+vars.cont_page_x).className = 'poster_box_act';
                        //$('video_p'+vars.cont_page_x).getElementsByClassName('movie_desc')[0].style.display = 'block';
                    }
                }
            }else{
                if(SUB_CUR_LAYER == sub_layer_video){
                    content_move(key);
                }else{
                    if(vars.cont_page_x<vars[win.height].cont_page_x_max-1){
                        $('promo_p'+vars.cont_page_x).className = 'poster_box';
                        vars.cont_page_x ++;
                        $('promo_p'+vars.cont_page_x).className = 'poster_box_act';
                        $('promo').style.background = 'url('+promo_obj[vars.cont_page_x].img_ipad.path+')';
                        $('promo').getElementsByClassName('pop_title')[0].innerHTML = promo_obj[vars.cont_page_x].title;
                    }
                }
            }
        break;
        case 38:
            if(left_side){
                log($(vars.catsArr[vars.catSel].id).title);
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item';
                }
                if($(vars.catsArr[vars.catSel].id).className == 'item'){
                    if($(vars.catsArr[vars.catSel-1].id).className == 'menu cat_item_text'){
                        var arr = document.getElementsByClassName('submenu');
                        for(var i = 0;i<arr.length;i++){
                            arr[i].parentNode.removeChild(arr[i]);
                        }
                        for(var y = vars.catsArr.length-1;y>-1;y--){
                            if(vars.catsArr[y].normal == 'item'){
                                vars.catsArr.splice(y, 1);
                            }
                        }
                        //vars.catSel-=3;
                    }
                }
                if(vars.catSel>0){
                    vars.catSel--;
                }else{
                    vars.catSel = vars.catsArr.length-1;
                }
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item_act';
                }
            }else{
                //if(SUB_CUR_LAYER == sub_layer_video){
                    content_move(key);
                //}else{
                    
                //}
            }
        break;
        case 40:
            // '400px';
            if(left_side){
                //log(vars.catsArr[vars.catSel].id);
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item';
                }
                //log($(vars.catsArr[vars.catSel].id).className);
                //log($(vars.catsArr[vars.catSel+1].id).className);
                if($(vars.catsArr[vars.catSel].id).className == 'item'){
                    if($(vars.catsArr[vars.catSel+1].id).className == 'menu cat_item_text'){
                        var arr = document.getElementsByClassName('submenu');
                        for(var i = 0;i<arr.length;i++){
                            log(i);
                            arr[i].parentNode.removeChild(arr[i]);
                        }
                        //var t = vars.catsArr.length-1;
                        for(var y = vars.catsArr.length-1;y>-1;y--){
                            if(vars.catsArr[y].normal == 'item'){
                                vars.catsArr.splice(y, 1);
                                vars.catSel--;
                            }
                        }
                    }
                }
                if(vars.catSel<vars.catsArr.length-1){
                    vars.catSel++;
                }else{
                    vars.catSel=0;
                }
                if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                    $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                }else{
                    $(vars.catsArr[vars.catSel].id).className = 'item_act';
                }
            }else{
                //if(SUB_CUR_LAYER == sub_layer_video){
                    content_move(key);
                //}else{
                    
                //}
            }
        break;
        case 109:
           return true; 
        break;
        case 107:
           return true; 
        break;
        case 192:
           return true; 
        break;
        case 112:
            
        break;
    }
    return true;
}
function subcatsPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('SUBCATS keyhandler key: '+key);
    switch(key){
        case 8:
            switchLayer(layer_cats);
        break;
        case 13:
            pop_layer = false;
            sendreq(iviURL+'videos/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new&genre='+cats_obj[vars.catSel].genres[vars.subcats_item].id, init_contentlist)
        break;
        case 27:
            switchLayer(layer_cats);
        break;
        case 37:
        case 38:
        case 39:
        case 40:
            subcats_move(key);
        break;
    }
    return true;
}
function searchPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('SEARCH keyhandler key: '+key);
    switch(key){
        case 13:
            if($('search_line').value == '' && layer_indexes.active[CUR_LAYER]==0){
                CUR_LAYER = 9;
                $('keyboard').style.display = 'block'; 
                return;
            }
            if(suggest_active){
                if(suggest_focus == -1){
                    suggest_active = false;
                    suggest_count = 0;
                    $('search_layer').removeChild($('sug_list'));
                }else{
                    $('search_line').value = $('sug_item'+suggest_focus).innerHTML;
                    suggest_active = false;
                    suggest_count = 0;
                    $('search_layer').removeChild($('sug_list'));
                }
            }
        break;
        case 8:
            if(!empty(e.target.type)){
                if(e.target.type != 'text'){
                    exit_search()
                }
            }
        break;
        case 27:
            if(suggest_active){
                suggest_active = false;
                suggest_count = 0;
                $('search_layer').removeChild($('sug_list'));
            }else{
                exit_search()
            }
        break;
        case 37:
            if(layer_indexes.active[CUR_LAYER]==layer_indexes.array[CUR_LAYER].search_form.length-1){
                layer_indexes.active[CUR_LAYER]--;
                $(layer_indexes.array[CUR_LAYER].search_form[layer_indexes.active[CUR_LAYER]]).focus()
            }
        break;
        case 39:
            if(layer_indexes.active[CUR_LAYER]==layer_indexes.array[CUR_LAYER].search_form.length-2){
                layer_indexes.active[CUR_LAYER]++;
                $(layer_indexes.array[CUR_LAYER].search_form[layer_indexes.active[CUR_LAYER]]).focus()
            }
        break;
        case 38:
            e.preventDefault();
            if(!suggest_active){
                if(layer_indexes.active[CUR_LAYER]>0){
                    layer_indexes.active[CUR_LAYER]--;
                    $(layer_indexes.array[CUR_LAYER].search_form[layer_indexes.active[CUR_LAYER]]).focus()
                }
            }else{
                if(suggest_focus >0 ){
                    $('sug_item'+suggest_focus).className = 'sug_item';
                    suggest_focus--;
                    $('sug_item'+suggest_focus).className = 'sug_item_active';
                }else{
                    if(suggest_focus == 0){
                        $('sug_item'+suggest_focus).className = 'sug_item';
                        suggest_focus--;
                    }
                }
            }
        break;
        case 40:
            e.preventDefault();
            if(!suggest_active){
                if(layer_indexes.active[CUR_LAYER]<layer_indexes.array[CUR_LAYER].search_form.length-1){
                    layer_indexes.active[CUR_LAYER]++;
                    $(layer_indexes.array[CUR_LAYER].search_form[layer_indexes.active[CUR_LAYER]]).focus()
                }
            }else{
                if(suggest_focus < suggest_count-1){
                    if(suggest_focus != -1){
                        $('sug_item'+suggest_focus).className = 'sug_item';
                        suggest_focus++;
                        $('sug_item'+suggest_focus).className = 'sug_item_active';
                    }else{
                        suggest_focus++;
                        $('sug_item'+suggest_focus).className = 'sug_item_active';
                    }
                }
            }
        break;
        case 76:
            CUR_LAYER = 9;
            $('keyboard').style.display = 'block';
        break;
        case 109:
           return true; 
        break;
        case 107:
           return true; 
        break;
        case 192:
           return true; 
        break;
    }
}
function contentPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('CONTENT keyhandler key: '+key);
    switch(key){
        case 8:
            switchLayer(layer_subcats);
        break;
        case 13:
            //sendreq(iviURL+'videos/?from=0&to=5&sort=new&genre='+cats_obj[vars.catSel].genres[vars.subcats_item].id, init_contentlist)
        break;
        case 27:
            switchLayer(layer_cats);
        break;
        case 37:
        case 38:
        case 39:
        case 40:
            content_move(key);
        break;
        case 82:
            sendreq(iviURL+'videofullinfo/?id='+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id, get_video_data)
        break;
    }
    return true;
}
function playerPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('PLAYER keyhandler key: '+key);
    switch(key){
        case 13:
            if(!vars.player_shown){
                clearInterval(runner_timer);
                clearTimeout(pos_timer);
                setpos_timer = false;
                clearTimeout(setpos_timer);
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                vars.player_shown = false;
                $('head').style.display = 'none';
                $('player').style.display = 'none';
                if(setpos_timer){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.SetPosTime(vars.file_curtime);stb.Continue();
                    pos_timer = window.setTimeout(runner_run,3000);
                }
            }
        break;
        case 8:
        case 83:
        case 27:
            secondadvwached = false,
            thirdadvwatched = false,
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            clearInterval(adv_timer)
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            switchLayer(layer_cats);
        break;
        case 33:
            if(file.result.compilation!=null){
                if(current_series>0){
                    current_series--;
                    sesies_getdata(seasons[current_series].id);
                }
            }
        break;
        case 34:
            if(file.result.compilation!=null){
                if(current_series<seasons.length-1){
                    current_series++;
                    sesies_getdata(seasons[current_series].id);
                }
            }
        break;
        case 37:
            if(!vars.player_shown){
                clearInterval(runner_timer);
                clearTimeout(pos_timer);
                setpos_timer = false;
                clearTimeout(setpos_timer);
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime>30){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.Pause();
                    vars.file_curtime-=30;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    setpos_timer = true;
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},1000);
                    pos_timer = window.setTimeout(runner_run,4000);
                }else{
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.Pause();
                    vars.file_curtime=0;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    setpos_timer = true;
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},1000);
                    pos_timer = window.setTimeout(runner_run,4000);
                }
                
            }
        break;
        case 39:
            if(!vars.player_shown){
                clearInterval(runner_timer);
                clearTimeout(pos_timer);
                setpos_timer = false;
                clearTimeout(setpos_timer);
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime<vars.file_lenght-30){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.Pause();
                    vars.file_curtime+=30;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    setpos_timer = true;
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},1000);
                    pos_timer = window.setTimeout(runner_run,4000);
                }
            }
        break;
        case 66:
            if(!vars.player_shown){
                clearInterval(runner_timer);
                clearTimeout(pos_timer);
                setpos_timer = false;
                clearTimeout(setpos_timer);
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime>300){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.Pause();
                    vars.file_curtime-=300;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    setpos_timer = true;
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},1000);
                    pos_timer = window.setTimeout(runner_run,4000);
                }
            }
        break;
        case 70:
            if(!vars.player_shown){
                clearInterval(runner_timer);
                clearTimeout(pos_timer);
                setpos_timer = false;
                clearTimeout(setpos_timer);
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime<vars.file_lenght-300){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    stb.Pause();
                    vars.file_curtime+=300;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    setpos_timer = true;
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},1000);
                    pos_timer = window.setTimeout(runner_run,4000);
                }
            }
        break;
        case 38:
            
        break;
        case 40:
            
        break;
        case 82:
            if(stb.IsPlaying()){
                var watch_id = file.result.id;
                ga('Pause', file.result.google_analytics_id, watch_id)
                $('btn_play').style.background = 'url(img/'+win.height+'/btn_pause.png)';
                stb.Pause();
                if(!vars.player_shown){
                    clearInterval(runner_timer);
                    clearTimeout(pos_timer);
                    setpos_timer = false;
                    clearTimeout(setpos_timer);
                    vars.player_shown = true;
                    runner_run();
                    $('head').style.display = 'block';
                    $('player').style.display = 'block';
                }
            }else{
                $('btn_play').style.background = 'url(img/'+win.height+'/btn_play.png)';
                stb.Continue();
            }
        break;
        case 109:
           return true; 
        break;
        case 107:
           return true; 
        break;
        case 117:
            changeAspect();
        break;
        case 192:
           return true; 
        break;
    }
    return true;
}

function genrePage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('GENRE keyhandler key: '+key);
    switch(key){
        case 48:
            //show_qestion()
        break;
        case 13:
            if(vars.catSel-vars.curCatSel == 3 && e.target.id != 'modal_exit'){
                if(parseInt($('year_0').value,10)<parseInt($('year_1').value,10)){
                    vars.cats[vars.curCatSel-1][2].year_from = $('year_0').value;
                    vars.cats[vars.curCatSel-1][2].year_to = $('year_1').value;
                }else{
                    vars.cats[vars.curCatSel-1][2].year_from = $('year_1').value;
                    vars.cats[vars.curCatSel-1][2].year_to = $('year_0').value;
                }
            
                document.getElementsByClassName('item_act')[0].innerHTML = 'C '+$('year_0').value+' по '+$('year_1').value;
                document.getElementsByClassName('modal')[0].style.display = 'none'; 
                document.getElementsByClassName('modal_list')[0].innerHTML = '';
                CUR_LAYER = PREV_LAYER;
                layer_indexes.active[layer_genre]=0
                vars.content_page = 0;
                var url = '';
                url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
                if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                    url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
                }
                if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                    url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
                }
                if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                    url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
                }
                if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                    url+='&country='+vars.cats[vars.curCatSel-1][1].id;
                }
                if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                    url+='&category='+vars.catID[vars.curCatSel]
                }
                sendreq(url, next_cont_page);
            }
            log(e.target.onclick);
        break;
        case 27:
            document.getElementsByClassName('modal')[0].style.display = 'none'; 
            document.getElementsByClassName('modal_list')[0].innerHTML = '';
            CUR_LAYER = PREV_LAYER;
            layer_indexes.active[layer_genre]=0;
        break;
        case 38:
            if(vars.catSel-vars.curCatSel == 3){
                if(year_focus != 2){
                    var year = parseInt($('year_'+year_focus).value,10);
                    if(year<2050){
                        year = year+1;
                        $('year_'+year_focus).value = year;
                    }
                }else{
                    year_focus--;
                    $('year_'+year_focus).focus();
                }
            }else{
                if(layer_indexes.active[layer_genre]>1){
                    layer_indexes.active[layer_genre]-=2;
                    document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus();
                }
            }
        break;
        case 40:
            if(vars.catSel-vars.curCatSel == 3){
                if(year_focus != 2){
                    var year = parseInt($('year_'+year_focus).value,10);
                    if(year>1900){
                        year = year-1;
                        $('year_'+year_focus).value = year;
                    }
                }
            }else{
                if(layer_indexes.active[layer_genre]+2<document.getElementsByClassName('genrebtn').length){
                    layer_indexes.active[layer_genre]+=2;
                    document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus();
                }else{
                    if(layer_indexes.active[layer_genre]+1<document.getElementsByClassName('genrebtn').length){
                        layer_indexes.active[layer_genre]++;
                        document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus();
                    }
                }
            }
        break;
        case 37:
            if(vars.catSel-vars.curCatSel == 3){
                if(year_focus==1){
                    year_focus = year_focus-1;
                    $('year_'+year_focus).focus();
                }else{
                    if(year_focus==2){
                        year_focus--;
                        $('year_'+year_focus).focus();
                    }else{
						if(year_focus==3){
							year_focus = 2;
							document.getElementsByClassName('genrebtn')[0].focus();
						}else{
							year_focus = 3;
							document.getElementsByClassName('genrebtn')[1].focus();
						}
                    }
                }
            }else{
                if(layer_indexes.active[layer_genre]>0){
                    layer_indexes.active[layer_genre]--;
                    document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus();
                }
            }
        break;
        case 39:
            if(vars.catSel-vars.curCatSel == 3){
                if(year_focus<1){
                    year_focus++;
                    $('year_'+year_focus).focus();
                }else{
                    if(year_focus<3){
                        year_focus++;
                        document.getElementsByClassName('genrebtn')[year_focus-2].focus();
                    }else{
                        year_focus=0;
                        $('year_'+year_focus).focus();
                    }
                }
            }else{
                if(layer_indexes.active[layer_genre]+1<document.getElementsByClassName('genrebtn').length){
                    layer_indexes.active[layer_genre]++;
                    document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus();
                }
            }
        break;
        case 109:
           return true; 
        break;
        case 107:
           return true; 
        break;
        case 192:
           return true; 
        break;
    }
}

function infoPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('INFO keyhandler key: '+key);
    switch(key){
        case 13:
            if($('info_btn_0').className == 'movie_menu_act'){
                if(typeof(file.seasons_count) == 'undefined'){
                    if(!empty(reclama)){
                        if(!empty(reclama.result)){
                            startADVplaying(0);
                        }else{
                            start_playing();
                            markTNS('player', file.result.tns_id);
                        }
                    }else{
                        //console.log(iviURL+'videofromcompilation/?id='+file.id);
                        sendreq(iviURL+'videofromcompilation/?id='+file.id, init_series);
                    }
                }else{
                    //console.log(iviURL+'videofromcompilation/?id='+file.id);
                    sendreq(iviURL+'videofromcompilation/?id='+file.id, init_series);
                }
            }else{
                if($('info_btn_1').className == 'movie_menu_act'){
                    switchLayer(layer_cats);
                }
                else{
                    switchLayer(layer_cats);
                }
            }
        break;
        case 8:
        case 27:
            switchLayer(layer_cats);
        break;
        case 38:
            if($('info_btn_1').className == 'movie_menu_act'){
                $('info_btn_1').className = 'movie_menu';
                $('info_btn_0').className = 'movie_menu_act';
            }
            else{
                $('info_btn_0').className = 'movie_menu';
                $('info_btn_1').className = 'movie_menu_act';
            }
        break;
        case 40:
            if($('info_btn_0').className == 'movie_menu_act'){
                $('info_btn_0').className = 'movie_menu';
                $('info_btn_1').className = 'movie_menu_act';
            }
            else{
                $('info_btn_1').className = 'movie_menu';
                $('info_btn_0').className = 'movie_menu_act';
            }
        break;
        case 37:

        break;
        case 39:

        break;
        case 33:
            document.getElementsByClassName('movie_info_text')[0].scrollByPages(-1);
        break;
        case 34:
            document.getElementsByClassName('movie_info_text')[0].scrollByPages(1);
        break;
        case 48:
            
        break;
        case 109:
           return true; 
        break;
        case 107:
           return true; 
        break;
        case 192:
           return true; 
        break;
    }
}

function confPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('CONFIRM keyhandler key: '+key);
    switch(key){
        case 37:
            if(conf_focus > 0){
                conf_focus--;
            }else{
                conf_focus=$('modal_btn').childNodes.length-1
            }
            $('modal_btn').childNodes[conf_focus].focus()
        break;
        case 39:
            if(conf_focus < $('modal_btn').childNodes.length-1){
                conf_focus++;
            }else{
                conf_focus=0
            }
            $('modal_btn').childNodes[conf_focus].focus()
        break;
    }
}

function kayboardPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('KB keyhandler key: '+key);
    switch(key){
        case 114:
        case 8:
            e.preventDefault();
            var position = getCaretPos($('search_line'));
            $('search_line').value = $('search_line').value.substr(0, position-1)+$('search_line').value.substr(position, ($('search_line').value.length-position));
            $('search_line').setSelectionRange(position-1,position-1);
            $('search_line').oninput();
        break;
        case 13:
            switch(keyboard_idx){
                default:
                    $('search_line').value += alphabet[0][keyboard_idx].text;
                    $('search_line').oninput();
                break;
                case 46:
                    $('search_line').value += ' ';
                    $('search_line').oninput();
                break;
                case 47:
                    var position = getCaretPos($('search_line'));
                    $('search_line').setSelectionRange(position-1,position-1);
                break;
                case 48:
                    var position = getCaretPos($('search_line'));
                    $('search_line').setSelectionRange(position+1,position+1);
                break;
                case 49:
                    var position = getCaretPos($('search_line'));
                    $('search_line').value = $('search_line').value.substr(0, position-1)+$('search_line').value.substr(position, ($('search_line').value.length-position));
                    $('search_line').setSelectionRange(position-1,position-1);
                    $('search_line').oninput();
                break;
            }
        break;
        case 37:
            e.preventDefault();
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas;
            if(keyboard_idx>0 && (keyboard_idx%10 != 0)){
                $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas;
                if(keyboard_idx != 40 && keyboard_idx != 38){
                    keyboard_idx--;
                }else{
                    keyboard_idx-=2;
                }
                
            }else{
                if(keyboard_idx==30){
                    keyboard_idx+=8;
                }else{
                    keyboard_idx+=9;
                }
            }
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas_act;
        break;
        case 39:
            e.preventDefault();
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas;
            if(keyboard_idx<49 && ((keyboard_idx+1)%10 != 0)){
                if(keyboard_idx == 36){
                    keyboard_idx+=2;
                }else{
                    if(keyboard_idx == 38){
                        keyboard_idx-=8;
                    }else{
                        keyboard_idx++;
                    }
                }
            }else{
                keyboard_idx-=9;
            }
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas_act;
        break;
        case 38:
            e.preventDefault();
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas;
            if(keyboard_idx>9){
                if(keyboard_idx!=49 && keyboard_idx!=47){
                    keyboard_idx-=10;
                }else{
                    keyboard_idx-=20;
                }
            }else{
                keyboard_idx+=40;
            }
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas_act;
        break;
        case 40:
            e.preventDefault();
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas;
            if(keyboard_idx<40){
                if(keyboard_idx!=29 && keyboard_idx!=27){
                    keyboard_idx+=10;
                }else{
                    keyboard_idx+=20;
                }
            }else{
                keyboard_idx-=40;
            }
            $('k'+keyboard_idx).className = alphabet[0][keyboard_idx].clas_act;
        break;
        case 27:
        case 76:
            CUR_LAYER = layer_search;
            $('keyboard').style.display = 'none';
        break;
        case 112:
            var position = getCaretPos($('search_line'));
            $('search_line').setSelectionRange(position-1,position-1);
        break;
        case 113:
            var position = getCaretPos($('search_line'));
            $('search_line').setSelectionRange(position+1,position+1);
        break;
    }
}

function cats_enter(){
    if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].thumbnails){
        $('movie_info_poster').src = dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].thumbnails[0].path;
    }else{
        $('movie_info_poster').src = 'img/'+win.height+'/video-preview.png';
    }
    $('movie_info_poster').setAttribute('width',vars[win.height].infoposter_width);
    $('movie_info_poster').setAttribute('height',vars[win.height].infoposter_height);
    $('info_title').innerHTML = dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].title;
    var genre_str = '';
    if(!empty(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].years)){
        genre_str += dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].years;
    }

    if(!empty(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].country)){
        if(!empty(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].years)){
            genre_str +=', '+countries[dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].country];
        }else{
            genre_str +=countries[dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].country];
        }
    }
    if(!empty(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].genres)){
        for(var y = 0;y<dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].genres.length;y++){
            genre_str +=', '+genres[dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].genres[y]]
        }
    }
    byclass('movie_info_genre')[0].innerHTML = genre_str;
    if(win.height == 576 || win.height == 480){
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10 || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating){
            $('descr').innerHTML = 'Рейтинги:<br />';
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10){
            $('descr').innerHTML += ('<b>ivi.ru:</b> '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10+'&nbsp;&nbsp;')
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating){
            $('descr').innerHTML += ('<b>IMDB:</b> '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating+'&nbsp;&nbsp;')
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating){
            $('descr').innerHTML += ('<b>Kinopoisk:</b> '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating)
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10 || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating){
            $('descr').innerHTML += '<br><br>'+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].descrtiption.replace(/\[.*?\]/igm,'');
        }else{
            $('descr').innerHTML = '<br>'+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].descrtiption.replace(/\[.*?\]/igm,'');
        }
    }else{
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10 || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating || 
           dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating){
            $('film_rates').innerHTML = 'Рейтинги:<br />';
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10){
            $('film_rates').innerHTML += ('<br>ivi.ru: '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].ivi_rating_10)
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating){
            $('film_rates').innerHTML += ('<br>IMDB: '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].imdb_rating)
        }
        if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating){
            $('film_rates').innerHTML += ('<br>Kinopoisk: '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kp_rating)
        }
        $('descr').innerHTML = '<br>'+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].descrtiption.replace(/\[.*?\]/igm,'');
    }
    log('KIND: '+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kind)
    var time = new Date();
    var watch_id = dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id+''+time.getTime();
    if(dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].kind == 1){
        $('info_btn_0').innerHTML = 'Воспроизвести';
        //sendreq(iviURL+'videofullinfo/?id='+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id,init_info)
        sendreq_post(
                    'http://api.digitalaccess.ru/api/json/',
                    init_info,
                    '{"method":"da.content.get","params":['+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id+',{"contentid":'+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id+',"watchid":"'+watch_id+'","site":"s127"}]}'//
                );
    }else{
        $('info_btn_0').innerHTML = 'Список серий';
        sendreq(iviURL+'compilationinfo/?id='+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id,init_info)
        log('{"method":"da.content.get","params":['+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id+',{"contentid":'+dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id+',"watchid":"'+watch_id+'","site":"s127"}]}');
    }
}
