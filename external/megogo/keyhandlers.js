var powerOff = false;


function body_keydown(e){
//timing();
  //if (empty(currLst))
//currLst = catLst;
    log('newAlert_on : '+newAlert_on);    
    var key = e.keyCode || e.which;
    var ret = false;
    if (e.altKey && key == 85) {
        if(!powerOff){
            stb.Stop();
            if(CUR_LAYER == 5){
                $('cur_time').innerHTML = '';
                $('progress').style.width = 'px';
                $('menu_series').style.display = 'block';
                $('player_page').style.display = 'none';
                $('footer').style.display = 'block';
                currLst = seriesLst;
                PREV_LAYER = 5;
                CUR_LAYER = 1;
                currLst.onChange();
                playlist.finish = str_replace(playlist.finish, 'http://megogo.net/b/stat?', '');
                var pos = stb.GetPosTime();
                playlist.finish += pos;
                startPosFromContinue = pos;
                stb.Stop();
                sendreq(megogoURL+'b/stat?'+createSign({'action':'stop', 'video':'2704','season':'0','episode':'0','position':pos}),finish);
            }
        }
        powerOff = !powerOff;
        stb.StandBy(powerOff);
        e.preventDefault();
        return;
    }
    if(powerOff){
        e.preventDefault();
        return;
    }
    log('MAIN keyhandler key: '+key+' Alt: '+e.altKey+' Ctrl: '+e.ctrlKey+' Shift: '+e.shiftKey+' Target_id: '+e.target.id)
    if(newAlert_on && key != 27){
        return;
    }

    log('+++CURR_LAYER+++'+CUR_LAYER);    
    switch(CUR_LAYER){
        case 0:
           // ret=authPage_keyhandler(e);
       // break;
        case 1:
            ret=infoPage_keyhandler(e);//catPage_keyhandler(e);
        break;
        case 5:
            ret=playerPage_keyhandler(e);
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
                 log('volume'+stb.GetVolume());
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
                log('volume'+stb.GetVolume());
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
                 log('mute'+stb.GetMute());
            break;
        }
    }
//timing();
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
            startPosFromContinue = 0;
            window.location = '../services.html'
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

var contentListCat = -1;




function playerPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('PLAYER keyhandler key: '+key);
    switch(key){
        case 8:
        case 83:
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            //switchLayer(PREV_LAYER);
            $('menu_series').style.display = 'block';
            $('player_page').style.display = 'none';
            $('footer').style.display = 'block';
            currLst = seriesLst;
            PREV_LAYER = 5;
            CUR_LAYER = 1;
            currLst.onChange();
        break;
        case 13:
            if(!vars.player_shown){
                vars.player_shown = true;
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                vars.player_shown = false;
                $('head').style.display = 'none';
                $('player').style.display = 'none';
                if(setpos_timer){
                }
            }
        break;
        case 27:
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            $('menu_series').style.display = 'block';
            $('player_page').style.display = 'none';
            $('footer').style.display = 'block';
            currLst = seriesLst;
            PREV_LAYER = 5;
            CUR_LAYER = 1;
            currLst.onChange();
            playlist.finish = str_replace(playlist.finish, 'http://megogo.net/b/stat?', '');
            var pos = stb.GetPosTime();
            playlist.finish += pos;
            startPosFromContinue = pos;
            stb.Stop();
            sendreq(megogoURL+'b/stat?'+createSign({'action':'stop', 'video':'2704','season':'0','episode':'0','position':pos}),finish);
        break;
        case 66:
        case 37:
            if(!vars.player_shown){
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime>30){
                    clearInterval(runner_timer);
                    clearTimeout(setpos_timer);
                    clearTimeout(pos_timer);
                     if(runFl){
                    	vars.file_curtime = stb.GetPosTime();
                    	stb.Pause();
                    }

                    runFl = false;

                    vars.file_curtime-=30;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    log(' vars.file_curtime '+curTime.hour+':'+curTime.minute+':'+curTime.second);
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    //stb.SetPosTime(vars.file_curtime);stb.Continue();
                    stb.SetPosTime(vars.file_curtime);
                    setpos_timer = window.setTimeout(function(){log('stb.Continue');stb.Continue();runFl=1;  //vars.file_curtime = stb.GetPosTime();
                    },3000);
                }
            }
        break;
        case 70:
        case 39:
            if(!vars.player_shown){
                vars.player_shown = true;
                runner_run();
                $('head').style.display = 'block';
                $('player').style.display = 'block';
            }else{
                if(vars.file_curtime<vars.file_lenght-30){
                    clearInterval(runner_timer);
                    clearTimeout(setpos_timer);
                    clearTimeout(pos_timer);
                     if(runFl){
                    	vars.file_curtime = stb.GetPosTime();
                    	stb.Pause();
                    }

                    runFl = false;

                    vars.file_curtime+=30;
                    var curTime = media_getHourMinSec(vars.file_curtime);
                    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
                    log(' vars.file_curtime '+curTime.hour+':'+curTime.minute+':'+curTime.second);
                    $('progress').style.width = vars[win.height].stripe_len*(vars.file_curtime/vars.file_lenght)+'px';
                    stb.SetPosTime(vars.file_curtime);
                    setpos_timer = window.setTimeout(function(){log('stb.Continue');stb.Continue();runFl=1;  //vars.file_curtime = stb.GetPosTime();
                    },3000);



                }
            }
        break;
        case 38:

        break;
        case 82:
            if(stb.IsPlaying()){
                $('btn_play').style.background = 'url(img/'+win.height+'/btn_pause.png)';
                stb.Pause();
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


function infoPage_keyhandler(e){
    var key = e.keyCode || e.which;
    log('INFO keyhandler key: '+key);
    switch(key){
        case 13:
            currLst.onEnter();
        break;
         case 33:
        	if(currLst == movieInfoLst && currLst.pos == 0)
            	currLst.onPageUp();
        break;

 		case 34:
        	if(currLst == movieInfoLst && currLst.pos == 0)
            	currLst.onPageDown();
        break;



        case 38:
        	if(typeof currLst.onUp == 'function')
            	currLst.onUp();
        	else currLst.prev();

        break;
        case 40:
        	if(typeof currLst.onDown == 'function')
            	currLst.onDown();
        	else currLst.next();
        break;
        case 37:
        	startPosFromContinue = 0;
                if(typeof currLst.onLeft == 'function')
            	currLst.onLeft();
        	else currLst.onExit();
        break;
        case 39:
            if(typeof currLst.onRight == 'function')
            	currLst.onRight();
        	else currLst.onEnter();
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
        case 8:
            startPosFromContinue = 0;
            if(typeof currLst.onRefresh == 'function')
            	currLst.onRefresh();
        else  currLst.onExit();
        break;
        case 27:
        	if(typeof currLst.onExit == 'function')
            	startPosFromContinue = 0;
                currLst.onExit();
        break;

        case 112:

        	if(currLst == extSubCatLst){

        		sort = 'year';
        		currLst = genreLst;
        		currLst.onEnter();
        	}
        break;

        case 113:

        	if(currLst == extSubCatLst){

        		sort = 'popular';
        		currLst = genreLst;
        		currLst.onEnter();
        	}
        break;


        case 114:

        	if(currLst == extSubCatLst){

        		sort = 'rate';
        		currLst = genreLst;
        		currLst.onEnter();
        	}
        break;

        case 115:

        	if(currLst == extSubCatLst){

        		sort = 'add';
        		currLst = genreLst;
        		currLst.onEnter();
        	}
        break;

    }
}