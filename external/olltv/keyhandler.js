function keydown(e){
    var key = e.keyCode || e.which;
    log('MAIN keyhandler key: '+key+' Alt: '+e.altKey+' Ctrl: '+e.ctrlKey+' Shift: '+e.shiftKey+' Target_id: '+e.target.id+ ' Active layer: '+CUR_LAYER);
    if(keyblock == true && key != 27){
        return;
    }
    var ret = false;
    switch(CUR_LAYER){
        case main_layer:
            ret = mainPage_keyhandler(e);
        break;
        case info_layer:
            ret = infoPage_keyhandler(e);
        break;
        case player_layer:
            ret = playerPage_keyhandler(e);
        break;
        case search_layer:
            ret = searchPage_keyhandler(e);
        break;
        case alert_layer:
            ret = alertPage_keyhandler(e);
        break;
    }
    if(ret){
        switch(key){
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
function exit(){
    //console.log(back_location)
    //window.location = back_location;
    console.log('referrer: '+_GET['referrer']);
    window.location = _GET['referrer'] || '';
}
function mainPage_keyhandler(e){
    var key = e.keyCode || e.which;
    switch(key){
        case 13:
            if(vars.focus_line == 0 || vars.focus_line == main_obj.length-1){
                unscroled_area = false;
                sendreq(olltvURL+'category?id='+main_obj[0].items[vars.focus_cat].id+'&itemoffset=1', drow_main);
            }else{
                sendreq(olltvURL+'info?id='+main_obj[vars.focus_line].items[vars.focus_item].id+'&showfull=true', drow_info);
            }
        break;
        case 8:
            if(!unscroled_area){
                unscroled_area = true;
                scriptloaded();
            }
        break;
        case 27:
            keyblock = false;
            show_message('<div class="modal_title">Подтверждение</div><div id="message_">Вы действительно хотите выйти?</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Нет</div><div class="msgbtn subscript_btn" onclick="exit()" >Да</div>');
            
        break;
        case 37:
            if(vars.focus_line==0 || vars.focus_line==(main_obj.length-1)){
                if(vars.focus_cat>0){
                    $('genre'+vars.focus_cat).className = 'strip_genre';
                    vars.focus_cat--;
                    $('subline0').style.marginLeft= ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
                    //vars[win.height].catSel_pos_px = (3-vars.focus_cat)*vars[win.height].catSel_step_px
                    $('genre'+vars.focus_cat).className = 'strip_genre_act';
                    $('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">'+main_obj[0].items[vars.focus_cat].title+'</div>'
                    //refill_shortInfo();
                }
            }else{
                if(main_obj[vars.focus_line].items.length<main_obj[vars.focus_line].items_number){
                    sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+main_obj[vars.focus_line].items_number+'&start=0', rebuildMain2,'bwd');
                }else{
                    prev_item();
                }
//                if(vars.focus_item>0){
//                    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
//                    vars.focus_item--;
//                    vars.cur_itemOnLine--;
//                    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
//                    refill_shortInfo();
//                }else{
//                    if(!unscroled_area){
//                        if(main_obj[vars.focus_line].items[vars.focus_item].order-(vars[win.height].maxitems_visible+1)>0){
//                            if(main_obj[vars.focus_line].items[vars.focus_item].order>1){
//                                sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+vars[win.height].maxitems_visible+'&start='+(main_obj[vars.focus_line].items[vars.focus_item].order-(vars[win.height].maxitems_visible+1)), rebuildMain,'bwd');
//                            }
//                        }else{
//                            if(main_obj[vars.focus_line].items_number-(vars[win.height].maxitems_visible+1)>0){
//                                sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+vars[win.height].maxitems_visible+'&start='+(main_obj[vars.focus_line].items_number-(vars[win.height].maxitems_visible+1)), rebuildMain,'bwd');
//                            }
//                        }
//                    }else{
//                        promo_prev_page()
//                    }
//                }
            }
        break;
        case 39:
            if(vars.focus_line==0 || vars.focus_line==(main_obj.length-1)){
                if(vars.focus_cat<main_obj[0].items_count-1){
                    $('genre'+vars.focus_cat).className = 'strip_genre';
                    vars.focus_cat++;
                    $('subline0').style.marginLeft= ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
                    //vars[win.height].catSel_pos_px = (3-vars.focus_cat)*vars[win.height].catSel_step_px
                    $('genre'+vars.focus_cat).className = 'strip_genre_act';
                    $('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">'+main_obj[0].items[vars.focus_cat].title+'</div>'
                    //refill_shortInfo();
                }
            }else{
                if(main_obj[vars.focus_line].items.length<main_obj[vars.focus_line].items_number){
                    sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+main_obj[vars.focus_line].items_number+'&start=0', rebuildMain2,'fwd');
                }else{
                    next_item();
                }
//                if(vars.focus_item<vars[win.height].maxitems_visible-2 && vars.focus_item<(main_obj[vars.focus_line].items.length-1)){
//                    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
//                    vars.focus_item++;
//                    vars.cur_itemOnLine++;
//                    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
//                    refill_shortInfo();
//                }else{
//                    if(!unscroled_area){
//                        if(main_obj[vars.focus_line].items_number != main_obj[vars.focus_line].items[vars.focus_item].order){
//                            sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+vars[win.height].maxitems_visible+'&start='+(main_obj[vars.focus_line].items[vars.focus_item].order), rebuildMain,'fwd');
//                        }else{
//                            if(main_obj[vars.focus_line].items_number>vars[win.height].maxitems_visible){
//                                sendreq(olltvURL+'items?block_id='+main_obj[vars.focus_line].block_id+'&limit='+vars[win.height].maxitems_visible+'&start=0', rebuildMain,'fwd');
//                            }
//                        }
//                    }else{
//                        promo_next_page()
//                    }
//                }
            }
        break;
        case 33:
            document.getElementsByClassName('main_list')[0].scrollByPages(-1);
        break;
        case 34:
            document.getElementsByClassName('main_list')[0].scrollByPages(1);
        break;
        case 38:
            if(vars.focus_line>0){
                focusToPrevLine();
            }
        break;
        case 40:
            if(vars.focus_line<main_obj.length-1){
                focusToNextLine();
            }
        break;
        case 48:
            log(objToString(main_obj[vars.focus_line].items[vars.focus_item]))
        break;
        case 49:
            log(document.getElementsByClassName('main_list')[0].innerHTML)
        break;
        case 50:
            
            
        break;
        case 112:
            switchLayer(search_layer);
        break;
        case 116:
            window.location.reload(true);
        break;
        case 107:
        case 109:
        case 192:
            return true;
        break;
    }
}
function infoPage_keyhandler(e){
    var key = e.keyCode || e.which;
    switch(key){
        case 8:
        case 27:
            switchLayer(main_layer);
        break;
        case 48:
            //show_message('<div class="subscript_bg"></div><div id="message_text" class="modal_title">'+info_obj.title+'('+info_obj.release_date+')'+'</div><div id="message_">Стоимость просмотра фильма - '+info_obj.cost+info_obj.cost_currency+'<br /><div class="modal_darkbg"><div><span class="bullet">1</span>Зайди на <b>oll.tv</b></div><br /><div><div class="bullet">2</div>Найди в подписке интересующее тебя видео</div><br /><div><div class="bullet">3</div>Выбирай наиболее удобный способ оплаты</div></div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');//<div class="subscript_btn">Узнать больше</div>
        break;
        case 13:
            switch(vars.info_focus_line){
                case 0:
                    sendreq(olltvURL+'info?id='+info_obj.seasons[vars.info_focus_obj[0]].season_id+'&showfull=true', refill_infoObj);
                break;
                case 1:
                    if(info_obj.is_free == 1 || (info_obj.is_free == 0 && info_obj.is_subscription_paid == 1)){
                        if(info_obj.is_erotic==1){//,"is_horror":0,"is_erotic":0,
                            if(info_obj.userAuth.authorized!=0){
                                if(info_obj.userAge<18){
                                    show_message('<div id="message_text" class="modal_title">Внимание!</div><div id="message_">Это видео содержит сцены эротического характера и не доступно для просмотра пользователям не достигшим 18-ти летнего возраста.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');
                                }else{
                                    if(info_obj.series){
                                        sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
                                    }else{
                                        sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj);
                                    }
                                    //show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Не рекомендуется для просмотра лицам, которые не достигли 18-ти лет, а также слабонервным и особо впечатлительным.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="conf_play()">Продолжить</div></div>')
                                }
                            }else{
                                show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Это видео содержит сцены эротического характера. Для просмотра ты должен авторизироваться и быть старше 18-ти лет.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="authorisation()">Авторизация</div></div>')
                                //show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для Авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
                                vars.need_reload = true;
                            }
                        }else{
                            if(info_obj.is_horror==1){
                                show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Не рекомендуется для просмотра лицам, которые не достигли 18-ти лет, а также слабонервным и особо впечатлительным.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="conf_play()">Продолжить</div></div>')
                            }else{
                                if(info_obj.series){
                                    sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
                                }else{
                                    sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj);
                                }
                            }
                        }
                    }else{
                        if(info_obj.userAuth.authorized!=0){
                            if(info_obj.subs_type == 'svod'){
                                show_message('<div class="subscript_bg"></div><div id="message_text" class="modal_title">Подписка на oll.tv</div><div id="message_">Выбранный материал доступен только для подписанных пользователей<br />нашего сервиса. Подписка открывает доступ ко всему контенту.<div class="modal_darkbg"><div><span class="bullet">1</span>Для подписки зайди на наш сайт <b>oll.tv</b></div><br /><div><div class="bullet">2</div>Зайди на свою учетную запись, в подраздел <b>«Моя подписка»</b></div><br /><div><div class="bullet">3</div>Выбирай наиболее подходящий тариф. Далее следуй<br />инструкциям.</div></div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');//<div class="subscript_btn">Узнать больше</div>
                            }else{
                                show_message('<div class="subscript_bg"></div><div id="message_text" class="modal_title">'+info_obj.title+'('+info_obj.release_date+')'+'</div><div id="message_">Стоимость просмотра фильма - '+info_obj.cost+' '+info_obj.cost_currency+'<br /><div class="modal_darkbg"><div><span class="bullet">1</span>Зайди на <b>oll.tv</b></div><br /><div><div class="bullet">2</div>Найди в поиске интересующее тебя видео</div><br /><div><div class="bullet">3</div>Выбирай наиболее удобный способ оплаты</div></div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');//<div class="subscript_btn">Узнать больше</div>
                            }
                        }else{
                            show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
                        }
                    }   
                break;
                case 2:
                    if(info_obj.series){
                        sendreq(olltvURL+'media?id='+info_obj.series[0].series_id, getPlayerObj);
                    }else{
                        sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj, info_obj.start_play_from);
                    }
                    
                break;
                case 3:
                    if(info_obj.is_free == 1 || (info_obj.is_free == 0 && info_obj.is_subscription_paid == 1)){
                        if(info_obj.is_erotic==1){//,"is_horror":0,"is_erotic":0,
                            if(info_obj.userAuth.authorized!=0){
                                if(info_obj.userAge<18){
                                    show_message('<div id="message_text" class="modal_title">Внимание!</div><div id="message_">Это видео содержит сцены эротического характера и не доступно для просмотра пользователям не достигшим 18-ти летнего возраста.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');
                                }else{
                                    //show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Не рекомендуется для просмотра лицам, которые не достигли 18-ти лет, а также слабонервным и особо впечатлительным.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="conf_play()">Продолжить</div></div>')
                                    if(info_obj.series){
                                        sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
                                    }else{
                                        sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj);
                                    }
                                }
                            }else{
                                show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Это видео содержит сцены эротического характера. Для просмотра ты должен авторизироваться и быть старше 18-ти лет.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="authorisation()">Авторизация</div></div>')
                                //show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для Авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
                                vars.need_reload = true;
                            }
                        }else{
                            if(info_obj.is_horror==1){
                                show_message('<div class="achtung_box"><div class="modal_title">Внимание!</div><div>Не рекомендуется для просмотра лицам, которые не достигли 18-ти лет, а также слабонервным и особо впечатлительным.</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div><div class="msgbtn subscript_btn" onclick="conf_play()">Продолжить</div></div>')
                            }else{
                                if(info_obj.series){
                                    sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
                                }else{
                                    sendreq(olltvURL+'media?id='+info_obj.id, getPlayerObj);
                                }
                            }
                        }
                    }else{
                         if(info_obj.userAuth.authorized!=0){
                             if(info_obj.subs_type == 'svod'){
                                 show_message('<div class="subscript_bg"></div><div id="message_text" class="modal_title">Подписка на oll.tv</div><div id="message_">Выбранный материал доступен только для подписанных пользователей<br />нашего сервиса. Подписка открывает доступ ко всему контенту.<div class="modal_darkbg"><div><span class="bullet">1</span>Для подписки зайди на наш сайт <b>oll.tv</b></div><br /><div><div class="bullet">2</div>Зайди на свою учетную запись, в подраздел <b>«Моя подписка»</b></div><br /><div><div class="bullet">3</div>Выбирай наиболее подходящий тариф. Далее следуй<br />инструкциям.</div></div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');//<div class="subscript_btn">Узнать больше</div>
                             }else{
                                 show_message('<div class="subscript_bg"></div><div id="message_text" class="modal_title">'+info_obj.title+'('+info_obj.release_date+')'+'</div><div id="message_">Стоимость просмотра фильма - '+info_obj.cost+' '+info_obj.cost_currency+'<br /><div class="modal_darkbg"><div><span class="bullet">1</span>Зайди на <b>oll.tv</b></div><br /><div><div class="bullet">2</div>Найди в поиске интересующее тебя видео</div><br /><div><div class="bullet">3</div>Выбирай наиболее удобный способ оплаты</div></div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');//<div class="subscript_btn">Узнать больше</div>
                             }
                            
                        }else{
                            show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
                        }
                    }
                break;
                case 4:
                    if(info_obj.userAuth.authorized==0){
                        show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
                    }else{
                        vars.need_reload = true;
                        if(info_obj.is_favorite==1){
                            sendreq(olltvURL+'deleteFavorite?id='+info_obj.id,delFromFav);
                        }else{
                            sendreq(olltvURL+'addFavorite?id='+info_obj.id,addToFav);
                        }
                    }
                break;
                case 5:
                break;
                case 6:
                break;
            }
        break;
        case 37:
            infoPage_move('left');
        break;
        case 39:
            infoPage_move('right');
        break;
        case 38:
            infoPage_move('up');
        break;
        case 40:
            infoPage_move('down');
        break;
        case 33:
            document.getElementsByClassName('fullinfo_toptext')[0].scrollByPages(-1);
        break;
        case 34:
            document.getElementsByClassName('fullinfo_toptext')[0].scrollByPages(1);
        break;
        case 48:
            log($('info_page').innerHTML)
        break;
        case 49:
            drow_film_info();
        break;
        case 112:
            switchLayer(search_layer);
        break;
        case 116:
            window.location.reload(true);
        break;
        case 107:
        case 109:
        case 192:
            return true;
        break;
    }
}

function searchPage_keyhandler(e){
    var key = e.keyCode || e.which;
    switch(key){
        case 13:
            if(vars.searchObj == 1){
                if(vars.searchline > 0){
                    sendreq(olltvURL+'info?id='+suggest_obj.items[(vars.searchline-1)].id+'&showfull=true', drow_info);
                }else{
                    //stb.ShowVirtualKeyboard();
                }
            }else{
                switch(vars.searchFocus){
                    default:
                        $('search_input').value += keybord[keybord_layout][vars.searchFocus].text;
                        getSuggest();
                    break;
                    case 0:
                        if(keybord_layout == 2){
                            keybord_layout = 0;
                        }else{
                            keybord_layout = 2;
                        }
                        //console.log(keybord[keybord_layout].length)
                        for(var i = 0;i<keybord[keybord_layout].length;i++){
                            document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[i].innerHTML = keybord[keybord_layout][i].text;
                            document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[i].className = keybord[keybord_layout][i].clas;
                        }
                        searchPage_keyhandler({"keyCode":39,"which":39});
                        searchPage_keyhandler({"keyCode":37,"which":37});
                    break;
                    case 6:
                        if(keybord_layout == 0){
                            keybord_layout = 1;
                        }else{
                            keybord_layout = 0;
                        }
                        //console.log(keybord[keybord_layout].length)
                        for(var i = 0;i<keybord[keybord_layout].length;i++){
                            document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[i].innerHTML = keybord[keybord_layout][i].text;
                            document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[i].className = keybord[keybord_layout][i].clas;
                        }
                        searchPage_keyhandler({"keyCode":39,"which":39});
                        searchPage_keyhandler({"keyCode":37,"which":37});
                    break;
                    case 12:
                        $('search_input').value+=' ';
                        getSuggest();
                    break;
                    case 18:
                        $('search_input').value = $('search_input').value.substr(0, $('search_input').value.length-1);
                        if($('search_input').value != ''){
                            getSuggest();
                        }
                    break;
                }
            }
        break;
        case 112:
        case 27:
            switchLayer(PREV_LAYER);
        break;
        case 37:
            if(vars.searchObj == 1){
                vars.searchObj = 0;
                $('search_input').blur();
                if(vars.searchFocus%6==0){
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act keypad_empty';
                }else{
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act';
                }
            }else{
                if(vars.searchFocus>0){
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = keybord[keybord_layout][vars.searchFocus].clas;
                    vars.searchFocus--;
                    if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className == 'keypad_key_null keypad_empty'){
                        vars.searchFocus--;
                    }
                    if(vars.searchFocus%6==0){
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act keypad_empty';
                    }else{
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act';
                    }
                }
            }
        break;
        case 39:
            if(vars.searchObj == 0){
                if(vars.searchFocus<keybord[keybord_layout].length-1){
                    if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus+1]){
                        if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus+1].className == 'keypad_key_null'){
                            return;
                        }
                    }
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = keybord[keybord_layout][vars.searchFocus].clas;
                    if((vars.searchFocus+1)%6==0 && vars.searchFocus!=0){
                        vars.searchObj = 1;
                        $('search_input').focus();
                        vars.searchline = 0;
                        return;
                    }
                    vars.searchFocus++;
                    if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className == 'keypad_key_null keypad_empty'){
                        vars.searchFocus++;
                    }
                    if(vars.searchFocus%6==0){
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act keypad_empty';
                    }else{
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act';
                    }
                }
            }else{
                
            }
        break;
        case 38:
            if(vars.searchObj == 0){
                if(vars.searchFocus>5){
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = keybord[keybord_layout][vars.searchFocus].clas;
                    vars.searchFocus-=6;
                    if(vars.searchFocus%6==0){
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act keypad_empty';
                    }else{
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act';
                    }
                }
            }else{
                if(vars.searchline>0){
                    $('sug_'+vars.searchline).className = 'search_result';
                    vars.searchline--;
                    if(vars.searchline == 0){
                        $('search_input').focus();
                    }else{
                        $('sug_'+vars.searchline).className = 'search_result_act'
                    }
                }
            }
        break;
        case 40:
            if(vars.searchObj==0){
                if(vars.searchFocus<keybord[keybord_layout].length-6){
                    if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus+6]){
                        if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus+6].className == 'keypad_key_null'){
                            return;
                        }
                    }
                    document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = keybord[keybord_layout][vars.searchFocus].clas;
                    vars.searchFocus+=6;
                    if(document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className == 'keypad_key_null keypad_empty'){
                        vars.searchFocus++;
                    }
                    if(vars.searchFocus%6==0){
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act keypad_empty';
                    }else{
                        document.getElementsByClassName('keypad_box')[0].getElementsByTagName('div')[vars.searchFocus].className = 'keypad_key_act';
                    }
                }
            }else{
                if(vars.searchline<suggest_obj.items.length){
                    if(vars.searchline == 0){
                        $('search_input').blur();
                    }else{
                        $('sug_'+vars.searchline).className = 'search_result';
                    }
                    vars.searchline++;
                    $('sug_'+vars.searchline).className = 'search_result_act';
                }
            }
        break;
    }
}

function playerPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('PLAYER keyhandler key: '+key);
    switch(key){
        case 116:
            //window.location.reload(true);
        break;
        case 83:
        case 8:
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            switchLayer(PREV_LAYER);
        break;
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
        case 27:
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            switchLayer(info_layer);
        break;
        case 33:
            
        break;
        case 34:

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
                    setpos_timer = window.setTimeout(function(){stb.SetPosTime(vars.file_curtime);stb.Continue();setpos_timer = false;},5000);
                    pos_timer = window.setTimeout(runner_run,6000);
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
        case 48:
        case 49:
        case 50:
        case 51:
        case 52:
        case 53:
        case 54:
        case 55:
        case 56:
        case 57:
            setdirTime(key-48);
        break;
        case 82:
            if(stb.IsPlaying()){
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

function alertPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('ALERT keyhandler key: '+key);
    switch(key){
        case 27:
            hide_message();
        break;
        case 13:
            document.getElementsByClassName('msgbtn')[vars.alert_focus].onclick();
        break;
        case 37:
            log(vars.alert_focus+'<'+vars.alert_arr)
            if(vars.alert_focus>0){
                document.getElementsByClassName('msgbtn')[vars.alert_focus].className = 'msgbtn subscript_btn'
                vars.alert_focus--;
                document.getElementsByClassName('msgbtn')[vars.alert_focus].className = 'msgbtn subscript_btn_act'
            }
        break;
        case 39:
            log(vars.alert_focus+'<'+vars.alert_arr)
            if(vars.alert_focus<vars.alert_arr-1){
                document.getElementsByClassName('msgbtn')[vars.alert_focus].className = 'msgbtn subscript_btn'
                vars.alert_focus++;
                document.getElementsByClassName('msgbtn')[vars.alert_focus].className = 'msgbtn subscript_btn_act'
            }
        break;
    }
}

function focusToPrevLine(){
    if(vars.focus_line==1){
        $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
        $('line'+vars.focus_line).className = 'strip';
        vars.focus_line--;
        $('line'+vars.focus_line).className = 'strip_act';
        $('genre'+vars.focus_cat).className = 'strip_genre_act';
        $('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">'+main_obj[0].items[vars.focus_cat].title+'</div>'
    }else{
        //if(vars.focus_line%vars.linesOnPage == 0){
            //$('line'+vars.focus_line).className = 'strip';
            //$('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
            var obj = history_obj.pop();
            document.getElementsByClassName('main_list')[0].innerHTML = obj.html;
            vars.curPage--;
            vars.focus_line--;
            
            $('line'+vars.focus_line).className = 'strip_act';
            if(main_obj[(vars.focus_line)].items.length<2){
                vars.focus_item=0;
            }else{
                if(main_obj[(vars.focus_line)].items.length<4){
                    vars.focus_item=1;
                }else{
                    vars.focus_item=2;
                }
            }
            if($('lineObj_'+vars.focus_line+vars.focus_item)){
                $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
            }
            if(main_obj[vars.focus_line].items.length > vars.focus_item){
                $('lineObj_'+vars.focus_line+''+vars.focus_item).className = 'item strip_item_act';
            }
            if(main_obj[vars.focus_line+1]){
                if((vars.focus_line+1)<main_obj.length-1){
                    var div = document.createElement('div');
                    div.id = 'line'+(vars.focus_line+1);
                    div.className = 'strip';
                    if(main_obj[(vars.focus_line+1)].items.length-1<vars.focus_item){
                        div.innerHTML = '<div id="subline'+(vars.focus_line+1)+'"><div class="strip_text">'+main_obj[vars.focus_line+1].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line+1].items[0].order+' из '+main_obj[vars.focus_line+1].items_number+'</div></div>';
                    }else{
                        div.innerHTML = '<div id="subline'+(vars.focus_line+1)+'"><div class="strip_text">'+main_obj[vars.focus_line+1].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line+1].items[vars.focus_item].order+' из '+main_obj[vars.focus_line+1].items_number+'</div></div>';
                    }

            //        if(main_obj[vars.focus_line+1].items.length < vars[win.height].maxitems_visible){
            //            div.innerHTML+='<div class="strip_item"><img src="" /></div>';
            //        }
                    for(var j = 0; j < main_obj[vars.focus_line+1].items.length && j<vars[win.height].maxitems_visible;j++){
                        div.innerHTML+='<div id="lineObj_'+(vars.focus_line+1)+''+j+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line+1].items[j].src+'" /></div>';
                    }
                    div.innerHTML += '</div>';
                    document.getElementsByClassName('main_list')[0].getElementsByClassName('strip')[1].innerHTML = div.innerHTML;
                    if(main_obj[vars.focus_line+1].items.length < 2){
                        $('lineObj_'+(vars.focus_line+1)+'0').style.marginLeft = vars[win.height].item_step*2+'px';
                    }else{
                        if(main_obj[vars.focus_line+1].items.length < 4){
                            $('lineObj_'+(vars.focus_line+1)+'0').style.marginLeft = vars[win.height].item_step+'px';
                        }
                    }
                }else{
                    ////////////////////////////////////////////////////////////////////////
                    var div = document.createElement('div');
                    div.id = 'line'+(vars.focus_line+1);
                    div.className = 'strip';
                    var text = '<div id="subline0">';
                    for(var i = 0;i<main_obj[(vars.focus_line+1)].items_count;i++){
                        text+='<div id="genre'+i+'" class="item strip_genre">'+main_obj[(vars.focus_line+1)].items[i].title.replace(/\//,'<br>')+'</div>'
                    }
                    //text+='<div id="genre'+i+'" class="item strip_genre">Плейлисты</div></div>'
                    //main_obj[0].items_count++
                    div.innerHTML = text;
                    document.getElementsByClassName('main_list')[0].getElementsByClassName('strip')[1].innerHTML = div.innerHTML;
                    $('subline0').style.marginLeft = ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
                    $('subline0').style.marginTop = '-110px';
                    ////////////////////////////////////////////////////////////////////////
                }
            }
            refill_shortInfo();
/*        }else{
            $('line'+vars.focus_line).className = 'strip';
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
            vars.focus_line--;
            $('line'+vars.focus_line).className = 'strip_act';
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
        }    */
    }
}

function focusToNextLine(){
    if(vars.focus_line==0){
        $('genre'+vars.focus_cat).className = 'strip_genre';
    }else{
        $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item';
    }
    if(vars.focus_line==0){
        $('line'+vars.focus_line).className = 'strip';
        vars.focus_line++;
        $('line'+vars.focus_line).className = 'strip_act';
        if(main_obj[(vars.focus_line)].items.length<2){
            vars.focus_item=0;
        }else{
            if(main_obj[(vars.focus_line)].items.length<4){
                vars.focus_item=1;
            }else{
                vars.focus_item=2;
            }
        }
        if($('lineObj_'+vars.focus_line+vars.focus_item)){
            $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
        }
        refill_shortInfo();
    }else{
        var obj = {};
        obj.html = document.getElementsByClassName('main_list')[0].innerHTML;
        obj.focus_line = vars.focus_line;
        obj.focus_item = vars.focus_item;
        history_obj.push(obj);
        makeNextPage();
    }
    
}

function makeNextPage(page){
        if(main_obj[vars.focus_line+1]){
            vars.curPage++;
            vars.focus_line++;
            document.getElementsByClassName('main_list')[0].innerHTML = ''; 
        //////////////////////////////////////////////////////////////////////// 
            var div = document.createElement('div');
            div.id = 'line'+(vars.focus_line-1);
            div.className = 'strip';
            if(main_obj[(vars.focus_line-1)].items.length-1<vars.focus_item){
                div.innerHTML = '<div id="subline'+(vars.focus_line-1)+'"><div class="strip_text">'+main_obj[(vars.focus_line-1)].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line-1].items[0].order+' из '+main_obj[vars.focus_line-1].items_number+'</div></div>';
            }else{
                div.innerHTML = '<div id="subline'+(vars.focus_line-1)+'"><div class="strip_text">'+main_obj[(vars.focus_line-1)].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line-1].items[vars.focus_item].order+' из '+main_obj[vars.focus_line-1].items_number+'</div></div>';
            }
            
    //        if(main_obj[vars.focus_line-1].items.length < vars[win.height].maxitems_visible){
    //            div.innerHTML+='<div class="strip_item_empty"><img src="" /></div>';
    //        }
            for(var y = 0; y < main_obj[(vars.focus_line-1)].items.length && y<vars[win.height].maxitems_visible;y++){
                div.innerHTML+='<div id="lineObj_'+(vars.focus_line-1)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[(vars.focus_line-1)].items[y].src+'" /></div>';
            }
            div.innerHTML += '</div>';
            document.getElementsByClassName('main_list')[0].appendChild(div);
            if(main_obj[vars.focus_line-1].items.length < 2){
                $('lineObj_'+(vars.focus_line-1)+'0').style.marginLeft = vars[win.height].item_step*2+'px';
            }else{
                if(main_obj[vars.focus_line-1].items.length < 4){
                    $('lineObj_'+(vars.focus_line-1)+'0').style.marginLeft = vars[win.height].item_step+'px';
                }
            }
        }
        if(vars.focus_line<main_obj.length-1){
            ////////////////////////////////////////////////////////////////////////
            var div = document.createElement('div');
            div.id = 'line'+(vars.focus_line);
            div.className = 'strip';
            if(main_obj[(vars.focus_line)].items.length-1<vars.focus_item){
                div.innerHTML = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[(vars.focus_line)].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[0].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
            }else{
                div.innerHTML = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[(vars.focus_line)].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
            }
            //div.innerHTML = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line].items[vars.focus_item].order+' из '+main_obj[vars.focus_line].items_number+'</div></div>';
            //        if(main_obj[vars.focus_line].items.length < vars[win.height].maxitems_visible){
            //            div.innerHTML+='<div class="strip_item_empty"><img src="" /></div>';
            //        }
            for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
                div.innerHTML+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
            }
            div.innerHTML += '</div>';
            document.getElementsByClassName('main_list')[0].appendChild(div);
            if(main_obj[vars.focus_line].items.length < 2){
                $('lineObj_'+(vars.focus_line)+'0').style.marginLeft = vars[win.height].item_step*2+'px';
            }else{
                if(main_obj[vars.focus_line].items.length < 4){
                    $('lineObj_'+(vars.focus_line)+'0').style.marginLeft = vars[win.height].item_step+'px';
                }
            }
            ////////////////////////////////////////////////////////////////////////
        }else{
            ////////////////////////////////////////////////////////////////////////
            var div = document.createElement('div');
            div.id = 'line'+(vars.focus_line);
            div.className = 'strip';
            var text = '<div id="subline0">';
            for(var i = 0;i<main_obj[0].items_count;i++){
                text+='<div id="genre'+i+'" class="item strip_genre">'+main_obj[(vars.focus_line)].items[i].title.replace(/\//,'<br>')+'</div>'
            }
            //text+='<div id="genre'+i+'" class="item strip_genre">Плейлисты</div></div>'
            //main_obj[0].items_count++
            div.innerHTML = text;
            document.getElementsByClassName('main_list')[0].appendChild(div);
            $('subline0').style.marginLeft = ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
            $('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">'+main_obj[0].items[vars.focus_cat].title+'</div>'
            $('subline0').style.marginTop = '-110px';
            ////////////////////////////////////////////////////////////////////////
        }
        if(main_obj[vars.focus_line+1]){
            if((vars.focus_line+1)<main_obj.length-1){
                var div = document.createElement('div');
                div.id = 'line'+(vars.focus_line+1);
                div.className = 'strip';
                if(main_obj[(vars.focus_line-1)].items.length-1<vars.focus_item){
                    div.innerHTML = '<div id="subline'+(vars.focus_line+1)+'"><div class="strip_text">'+main_obj[vars.focus_line+1].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line+1].items[0].order+' из '+main_obj[vars.focus_line+1].items_number+'</div></div>';
                }else{
                    div.innerHTML = '<div id="subline'+(vars.focus_line+1)+'"><div class="strip_text">'+main_obj[vars.focus_line+1].block_title+' <div class="strip_text_count">'+main_obj[vars.focus_line+1].items[vars.focus_item].order+' из '+main_obj[vars.focus_line+1].items_number+'</div></div>';
                }
                
        //        if(main_obj[vars.focus_line+1].items.length < vars[win.height].maxitems_visible){
        //            div.innerHTML+='<div class="strip_item"><img src="" /></div>';
        //        }
                for(var j = 0; j < main_obj[vars.focus_line+1].items.length && j<vars[win.height].maxitems_visible;j++){
                    div.innerHTML+='<div id="lineObj_'+(vars.focus_line+1)+''+j+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line+1].items[j].src+'" /></div>';
                }
                div.innerHTML += '</div>';
                document.getElementsByClassName('main_list')[0].appendChild(div);
                if(main_obj[vars.focus_line+1].items.length < 2){
                    $('lineObj_'+(vars.focus_line+1)+'0').style.marginLeft = vars[win.height].item_step*2+'px';
                }else{
                    if(main_obj[vars.focus_line+1].items.length < 4){
                        $('lineObj_'+(vars.focus_line+1)+'0').style.marginLeft = vars[win.height].item_step+'px';
                    }
                }
            }else{
                ////////////////////////////////////////////////////////////////////////
                var div = document.createElement('div');
                div.id = 'line'+(vars.focus_line+1);
                div.className = 'strip';
                var text = '<div id="subline0">';
                for(var i = 0;i<main_obj[(vars.focus_line+1)].items_count;i++){
                    text+='<div id="genre'+i+'" class="item strip_genre">'+main_obj[(vars.focus_line+1)].items[i].title.replace(/\//,'<br>')+'</div>'
                }
                //text+='<div id="genre'+i+'" class="item strip_genre">Плейлисты</div></div>'
                //main_obj[0].items_count++
                div.innerHTML = text;
                document.getElementsByClassName('main_list')[0].appendChild(div);
                $('subline0').style.marginLeft = ((3-vars.focus_cat)*vars[win.height].catSel_step_px-vars[win.height].catSel_pos_px)+'px';
                $('subline0').style.marginTop = '-110px';
                ////////////////////////////////////////////////////////////////////////
                }
        }
    ////////////////////////////////////////////////////////////////////////
    
    $('line'+vars.focus_line).className = 'strip_act';
    if(main_obj[(vars.focus_line)].items.length<2){
        vars.focus_item=0;
    }else{
        if(main_obj[(vars.focus_line)].items.length<4){
            vars.focus_item=1;
        }else{
            vars.focus_item=2;
        }
    }
    if(vars.focus_line<main_obj.length-1){
        if(main_obj[vars.focus_line].items.length > vars.focus_item){
            $('lineObj_'+vars.focus_line+''+vars.focus_item).className = 'item strip_item_act';
        }else{
            vars.focus_item = 0;
            $('lineObj_'+vars.focus_line+''+vars.focus_item).className = 'item strip_item_act';
        }
        refill_shortInfo();
    }else{
        $('genre'+vars.focus_cat).className = 'strip_genre_act';
        //$('short_info').innerHTML = '<div class="infopanel_shadow"></div><div class="text_title">Краткая информация:</div>'
    }
    
}

function promo_prev_page(){
    var item;
    for(var i=0;i<vars[win.height].maxitems_visible-1;i++){
        item = main_obj[(vars.focus_line)].items.splice(main_obj[(vars.focus_line)].items.length-1, 1)
        main_obj[(vars.focus_line)].items.splice(0, 0,item[0]);
    }
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count"></div></div>';
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    vars.focus_item = vars[win.height].maxitems_visible-2;
    vars.cur_itemOnLine--;
    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
    refill_shortInfo();
}

function promo_next_page(){
    var item;
    for(var i=0;i<vars[win.height].maxitems_visible-1;i++){
        item = main_obj[(vars.focus_line)].items.splice(0, 1)
        main_obj[(vars.focus_line)].items.splice(main_obj[(vars.focus_line)].items.length, 0,item[0]);
        
    }
    var div = $('line'+(vars.focus_line));
    var html = '<div id="subline'+(vars.focus_line)+'"><div class="strip_text">'+main_obj[vars.focus_line].block_title+' <div class="strip_text_count"></div></div>';
    for(var y = 0; y < main_obj[vars.focus_line].items.length && y<vars[win.height].maxitems_visible;y++){
        html+='<div id="lineObj_'+(vars.focus_line)+''+y+'" class="strip_item"><img width="'+vars[win.height].img_width+'" height="'+vars[win.height].img_height+'" src="'+main_obj[vars.focus_line].items[y].src+'" /></div>';
    }
    html += '</div>';
    div.innerHTML = html;
    vars.focus_item = 0;
    vars.cur_itemOnLine++;
    $('lineObj_'+vars.focus_line+vars.focus_item).className = 'strip_item_act';
    refill_shortInfo();
}

function infoPage_move(dir){
    switch(dir){
        case 'left':
            switch(vars.info_focus_line){
                case 0:
                    if(vars.info_focus_obj[0]>0){
                        if(vars.info_focus_obj[0]<5){
                            $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                            vars.info_focus_obj[0]--;
                            vars.info_focus_btn[0]--;
                            $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_act';
                            $("seasons").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*vars.info_focus_obj[0]))+'px';
                        }else{
                            if(vars.info_focus_obj[0]>info_obj.seasons.length-5){
                                $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                                vars.info_focus_obj[0]--;
                                vars.info_focus_btn[0]--;
                                $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_act';
                                $("seasons").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*(vars.info_focus_btn[0])))+'px';
                            }else{
                                vars.info_focus_obj[0]--;
                                for(var i = 0;i<9;i++){
                                    $("seasons").childNodes[i].innerHTML = info_obj.seasons[i+vars.info_focus_obj[0]].season_title;
                                }
                            }
                        }
                    }
                break;
                case 1:
                    log('vars.info_focus_obj[1]: '+vars.info_focus_obj[1]+' >><< info_obj.series.length: '+info_obj.series.length)
                    if(vars.info_focus_obj[1]>0){
                        if(vars.info_focus_obj[1]<5){
                            $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
                            vars.info_focus_obj[1]--;
                            vars.info_focus_btn[1]--;
                            $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                            $("series").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*vars.info_focus_obj[1]))+'px';
                        }else{
                            if(vars.info_focus_obj[1]>info_obj.series.length-5){
                                $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
                                vars.info_focus_obj[1]--;
                                vars.info_focus_btn[1]--;
                                $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                                $("series").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*(vars.info_focus_btn[1])))+'px';
                            }else{
                                vars.info_focus_obj[1]--;
                                for(var i = 0;i<9;i++){
                                    $("series").childNodes[i].innerHTML = info_obj.series[(i+vars.info_focus_obj[1]-4)].series_title;
                                }
                            }
                        }
                    }
                break;
                case 2:
                case 3:
                case 4:
                break;
                case 5:
                    if(vars.active_audiolang>0){
                        document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small';
                        vars.active_audiolang--;
                        document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_act';

                    }
                break;
                case 6:
                    if(vars.active_sublang>0){
                        document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small';
                        vars.active_sublang--;
                        document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small_act';
                    }
                break;
            }
        break;
        case 'right':
            switch(vars.info_focus_line){
                case 0:
                    if(vars.info_focus_obj[0]<info_obj.seasons.length-1){
                        if(vars.info_focus_obj[0]<4){
                            if(vars.info_sel_obj[0]==vars.info_focus_obj[0]){
                                $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_press';
                            }else{
                                $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                            }
                            vars.info_focus_obj[0]++;
                            vars.info_focus_btn[0]++;
                            $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_act';
                            $("seasons").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*vars.info_focus_obj[0]))+'px';
                        }else{
                            if(vars.info_focus_obj[0]>info_obj.seasons.length-6){
                                if(vars.info_sel_obj[0]==vars.info_focus_obj[0]){
                                    $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_press';
                                }else{
                                    $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                                }
                                vars.info_focus_obj[0]++;
                                vars.info_focus_btn[0]++;
                                if(vars.info_sel_obj[0]==vars.info_focus_obj[0]){
                                    $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_press';
                                }else{
                                    $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                                }
                                $("seasons").style.marginLeft = '-'+(vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*(vars.info_focus_btn[0])))+'px';
                            }else{
                                vars.info_focus_obj[0]++;
                                for(var i = 0;i<9;i++){
                                    $("seasons").childNodes[i].innerHTML = info_obj.seasons[i+vars.info_focus_obj[0]].season_title;
                                }
                            }
                        }
                    }
                break;
                case 1:
                    log('vars.info_focus_obj[1]: '+vars.info_focus_obj[1]+' >><< info_obj.series.length: '+info_obj.series.length)
                    if(vars.info_focus_obj[1]<info_obj.series.length-1){
                        if(vars.info_focus_obj[1]<4){
                            $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
                            vars.info_focus_obj[1]++;
                            vars.info_focus_btn[1]++;
                            $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                            $("series").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*vars.info_focus_obj[1]))+'px';
                        }else{
                            if(vars.info_focus_obj[1]>info_obj.series.length-6){
                                $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
                                vars.info_focus_obj[1]++;
                                vars.info_focus_btn[1]++;
                                $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                                $("series").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*(vars.info_focus_btn[1])))+'px';
                            }else{
                                vars.info_focus_obj[1]++;
                                for(var i = 0;i<9;i++){
                                    $("series").childNodes[i].innerHTML = info_obj.series[(i+vars.info_focus_obj[1]-4)].series_title;
                                }
                            }
                        }
                    }
                break;
                case 2:
                case 3:
                case 4:
                break;
                case 5:
                    if(vars.active_audiolang<info_obj.subtitles.length-1){
                        document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small';
                        vars.active_audiolang++;
                        document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_act';

                    }
                break;
                case 6:
                    if(vars.active_sublang<info_obj.subtitles.length-1){
                        document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small';
                        vars.active_sublang++;
                        document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small_act';
                    }
                break;
            }
        break;
        case 'up':
            switch(vars.info_focus_line){
                case 0:
                break;
                case 1:
                    if(info_obj.seasons){
                        $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
                        vars.info_focus_line=0;
                        $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series_act';
                        if(vars.info_sel_obj[1]>(vars.info_focus_obj[1]-5) && (vars.info_sel_obj[1]<(vars.info_focus_obj[1]+4))){
                            $("series").childNodes[vars.info_sel_obj[1]].className = 'fullinfo_btn_series_press';
                        }
                    }
                break;
                case 2:
                break;
                case 3:
                    if(info_obj.start_play_from != 0){
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large';
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large_act';
                        vars.info_focus_line=2;
                    }
                    else{
                        if(info_obj.series){
                            document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large';
                            vars.info_focus_line=1;
                            $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                        }
                    }
                break;
                case 4:
                    vars.info_focus_line=3;
                    if(info_obj.start_play_from != 0){
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[2].className = 'fullinfo_btn_large';
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large_act';
                    }
                    else{
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large';
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large_act';
                    }
                break;
                case 5:
                    vars.info_focus_line=4;
                    document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_press';
                    if(info_obj.start_play_from != 0){
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[2].className = 'fullinfo_btn_large_act';
                    }
                    else{
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large_act';
                    }
                break;
                case 6:
                    vars.info_focus_line=5;
                    document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_act';
                    
                    document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small_press';
                break;
            }
        break;
        case 'down':
            switch(vars.info_focus_line){
                case 0:
                    if(info_obj.series){
                        $("seasons").childNodes[vars.info_focus_btn[0]].className = 'fullinfo_btn_series';
                        vars.info_focus_line=1;
                        $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_act';
                        $("seasons").childNodes[vars.info_sel_obj[0]].className = 'fullinfo_btn_series_press';
                    }
                break;
                case 1:
//                    $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series';
//                    if(vars.info_sel_obj[1]>(vars.info_focus_obj[1]-5) && (vars.info_sel_obj[1]<(vars.info_focus_obj[1]+4))){
                    $("series").childNodes[vars.info_focus_btn[1]].className = 'fullinfo_btn_series_press';
//                    }
                    document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large_act';
                    if(info_obj.start_play_from != 0){
                        vars.info_focus_line=2;
                    }else{
                        vars.info_focus_line=3;
                    }
                break;
                case 2:
                    document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large';
                    document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large_act';
                    vars.info_focus_line=3;
                break;
                case 3:
                    if(info_obj.start_play_from != 0){
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large';
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[2].className = 'fullinfo_btn_large_act';
                    }
                    else{
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[0].className = 'fullinfo_btn_large';
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large_act';
                    }
                    vars.info_focus_line=4;
                break;
                case 4:
                    if(info_obj.start_play_from != 0){
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[2].className = 'fullinfo_btn_large';
                    }
                    else{
                        document.getElementsByClassName('fullinfo_menu')[0].childNodes[1].className = 'fullinfo_btn_large';
                    }
                    vars.info_focus_line=5;
                    document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_act';
                break;
                case 5:
                    if(info_obj.subtitles.length>0){
                        vars.info_focus_line=6;
                        document.getElementsByClassName('fullinfo_menu_block')[0].childNodes[vars.active_audiolang+1].className = 'fullinfo_btn_small_press';
                        document.getElementsByClassName('fullinfo_menu_block')[1].childNodes[vars.active_sublang+1].className = 'fullinfo_btn_small_act';
                    }
                break;
                case 6:
                break;
            }
        break;
    }
    
}

function refill_infoObj(text){
    var new_info = JSON.parse(text);
    log(text);
    info_obj = new_info;
    for(var i = 0;i<$('seasons').getElementsByClassName('fullinfo_btn_series_press').length;i++){
        $('seasons').getElementsByClassName('fullinfo_btn_series_press')[i].className = 'fullinfo_btn_series';
    }
    vars.info_sel_obj[0] = vars.info_focus_obj[0];
    $('seasons').getElementsByClassName('fullinfo_btn_series_act')[0].className = 'fullinfo_btn_series_press';
    vars.info_focus_obj[1] = 0;
    vars.info_focus_btn[1] = 0;
    vars.info_sel_obj[1] = 0;
    if(info_obj.series){
        var text = '';
        for(var b = 0;b<info_obj.series.length && b<9;b++){
            text += '<div class="fullinfo_btn_series">'+info_obj.series[b].series_title+'</div>'
        }
        $('series').innerHTML = text;
        $('series').getElementsByClassName('fullinfo_btn_series')[0].className = 'fullinfo_btn_series_act';
        $("series").style.marginLeft = (vars[win.height].seasonSel_pos_px - (vars[win.height].seriesSel_step_px*vars.info_focus_obj[1]))+'px';
    }
    vars.info_focus_line++;
}

function addToFav(text){
    var obj = JSON.parse(text);
    if(obj.state == 'OK'){
        info_obj.is_favorite=1;
        document.getElementsByClassName('fullinfo_btn_large_act')[0].innerHTML = 'Удалить из избранного';
    }else{
        
    }
    
}

function delFromFav(text){
    var obj = JSON.parse(text);
    if(obj.state == 'OK'){
        info_obj.is_favorite=0;
        document.getElementsByClassName('fullinfo_btn_large_act')[0].innerHTML = 'Добавить в избранное';
    }
}

function authorisation(){
    CUR_LAYER = CUR_SUBLAYER;
    $('message').style.display = 'none';
    show_message('<div class="authorization_bg"></div><div class="modal_title">Авторизация</div><div>Для Авторизации зайди на специальную страницу нашего сайта <b>oll.tv/dev</b><br />Впиши в поле <b>активизационный код,</b> который показан ниже и нажми «ок».<div class="modal_darkbg"><div>Авторизационный код будет действителен в течении часа</div><br /><div class="auth_box">'+info_obj.userAuth.suggest_cache+'</div></div><div></div><div class="msgbtn subscript_btn_act" onclick="hide_message()">Закрыть</div></div>');
}