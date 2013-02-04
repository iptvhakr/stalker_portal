app.keypress={
    "press":function(e){
        var code = 0;
        code = e.keyCode || e.which;
        if (stb && stb.key_lock === true && code != key.FRAME){
            return;
        }
        if (e.shiftKey) {
            code += 1000;
        }
        if (e.altKey) {
            code += 2000;
        }
        switch(code) {
            case keys.OK:
                app.keypress.processingOK();
            break;
            case keys.UP:
            case keys.RIGHT:
            case keys.DOWN:
            case keys.LEFT:
            case keys.CHANNEL_PREV:
            case keys.CHANNEL_NEXT:
            case keys.PAGE_NEXT:
            case keys.PAGE_PREV:
                app.keypress.processingNavigate(code);
            break;
            case keys.RED:
            case keys.GREEN:
            case keys.YELLOW:
            case keys.BLUE:
                app.keypress.processingColor(code);
            break;
            case keys.BACK:
            case keys.EXIT:
            case keys.REFRESH:
            case keys.APP:
            case keys.SERVICES:
            case keys.TV:
            case keys.PHONE:
            case keys.WEB:
            case keys.POWER:
            case keys.INFO:
                app.keypress.processingSytem(code);
            break;
            case keys.REW:
            case keys.FFWD:
            case keys.PLAY:
            case keys.PAUSE:
            case keys.CONTINUE:
            case keys.STOP:
            case keys.REC:
            case keys.MIC:
                app.keypress.processingTV(code);
            break;
            case keys.VOL_UP:
            case keys.VOL_DOWN:
            case keys.MUTE:
                app.keypress.processingVolume(code);
            break;
            case keys.USB_MOUNTED:
            case keys.USB_UNMOUNTED:
                app.keypress.processingUSDEvents(code);
            break;
            case keys.MENU:
                app.keypress.processingMENU(code);
            break;
            break;
            case keys.FRAME:
                app.keypress.changeAspect();
            break;
        }
    },
    "press_als":function(e){
        var code = 0;
        code = e.keyCode || e.which;
        log('code='+code)
        if (stb && stb.key_lock === true && code != key.FRAME){
            return;
        }
        if (e.shiftKey) {
            code += 1000;
        }
        if (e.altKey) {
            code += 2000;
        }
        log('code2='+code)
        switch(code) {
            case keys.OK:app.keypress.als_ok();
                break;
            case keys.UP:app.keypress.als_vert(-1);
                break;
            case keys.DOWN:app.keypress.als_vert(1);
                break;
            case keys.RIGHT:app.keypress.als_hor(1);
                break;
            case keys.LEFT:app.keypress.als_hor(-1);
                break;
            case keys.EXIT:
            case keys.INFO:app.player.change_audiotrack();
                break;
            case keys.VOL_UP:
            case keys.VOL_DOWN:
            case keys.MUTE:app.keypress.processingVolume(code);
            break;
            case keys.USB_MOUNTED:
            case keys.USB_UNMOUNTED:
                app.keypress.processingUSDEvents(code);
            break;
            break;
        }
    },
    
    "als_index_h":1,
    "als_index_v":1,
    "ars_index_v":1,
    "ars_max_v":3,
    "ars_3dmode":0,
    "als_layer":1,
    "als_max_v":0,
    "als_active_audio":-1,
    "als_active_subt":-1,
    "als_on":false,
    "audio_langv":[],
    "subtitri_langv":[],
    "als_hor":function(a){
        if(a==1){$('ars_form').className='form_a';$('als_form').className='form_n';app.keypress.als_index_h=2;}
        else {$('ars_form').className='form_n';$('als_form').className='form_a';app.keypress.als_index_h=1;}
    },
    "als_vert":function(a){
        switch(app.keypress.als_index_h){
            case 1:
                app.keypress.ars_index_v+=a;
                if(app.keypress.ars_index_v<1)app.keypress.ars_index_v=1;else if(app.keypress.ars_index_v>app.keypress.ars_max_v)app.keypress.ars_index_v=app.keypress.ars_max_v;
                switch(app.keypress.ars_index_v){
                case 1:
                    $('acs_form').style.marginTop='15px';
                    $('ars_form').style.marginTop='0px';
                    $('als_line_1').className='als_line_a';
                    $('als_line_2').className='als_line';
                    app.player.InfoMenu('audio',app.keypress.audio_langv);
                    break;
                case 2:
                    $('acs_form').style.marginTop='45px';
                    $('ars_form').style.marginTop='30px';
                    $('als_line_2').className='als_line_a';
                    $('als_line_1').className='als_line';
                    $('als_line_3').className='als_line';
                    app.player.InfoMenu('subtitles',app.keypress.subtitri_langv);
                    break;
                case 3:
                    $('acs_form').style.marginTop='75px';
                    $('ars_form').style.marginTop='60px';
                    $('als_line_3').className='als_line_a';
                    $('als_line_2').className='als_line';
                    app.player.InfoMenu('3dmode');
                    break;
                }
                break;
            case 2:
                if($('ars_line_'+app.keypress.als_index_v)){$('ars_line_'+app.keypress.als_index_v).className='als_line';
                app.keypress.als_index_v+=a;
                if(app.keypress.als_index_v<1)app.keypress.als_index_v=1;else if(app.keypress.als_index_v>app.keypress.als_max_v)app.keypress.als_index_v=app.keypress.als_max_v;
                $('ars_line_'+app.keypress.als_index_v).className='als_line_a';}
                break;
        }
    },
    "als_ok":function(){
        if(app.keypress.als_index_h==2)switch(app.keypress.als_layer){
            case 1:
                document.getElementsByClassName('ars_img_a')[0].className='ars_img';
                app.keypress.als_active_audio=app.keypress.als_index_v-1;
                $('ars_img_'+app.keypress.als_index_v).className='ars_img_a';
                log(app.keypress.audio_langv[app.keypress.als_active_audio].pid);
                stb.SetAudioPID(app.keypress.audio_langv[app.keypress.als_active_audio].pid);
                break;
            case 2:
                log('ars_img_'+app.keypress.als_index_v)
                document.getElementsByClassName('ars_img_a')[0].className='ars_img';
                app.keypress.als_active_subt=app.keypress.als_index_v-2;
                $('ars_img_'+app.keypress.als_index_v).className='ars_img_a';
                if(app.keypress.als_index_v!=1){
                log(app.keypress.subtitri_langv[app.keypress.als_active_subt].pid);
                stb.SetSubtitles(true);
                app.keypress.als_on=true;
                stb.SetSubtitlePID(app.keypress.subtitri_langv[app.keypress.als_active_subt].pid);}
                else {stb.SetSubtitles(false);app.keypress.als_on=false;}
                break;
            case 3:
                document.getElementsByClassName('ars_img_a')[0].className='ars_img';
                $('ars_img_'+app.keypress.als_index_v).className='ars_img_a';
                stb.Set3DConversionMode(app.keypress.als_index_v-1);
                break;
        }
        else {app.keypress.als_hor(1);}
    },
    
    // OK
    "processingOK":function(){
        log(app.classes.list.cur_page);
        log(app.classes.list.cur_exPage);
        switch(as.layer){
            case as.layers.HOME:
                as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_l;
                as.list='list';
                as.navig='navigation';
                switch(as.cur_home_position){
                    case "button_video":
                        app.views.showSection(as.layers.CATS_LIST,'video');
                        branch = 'video';
                    break;
                    case "button_music":
                        app.views.showSection(as.layers.CATS_LIST,'audio');
                        branch = 'audio';
                    break;
                    case "button_images":
                        app.views.showSection(as.layers.CATS_LIST,'images');
                        branch = 'images';
                    break;
                    case "button_search":
                        app.views.showSection(as.layers.SEARCH,'search_line');
                        branch = 'search';
                    break;
                }
            break;
            case as.layers.CATS_LIST:
                //app.views.showSection(as.layers.SUB_CATS_LIST,'video');
                $(as.layers_pages[as.layers.CATS_LIST]).hide();
                $(as.layers_pages[as.layers.SUB_CATS_LIST]).show();
                var obj = app.classes.list.ctrls.getCurItemInfo();
                app.classes.list.cur_exUrl = obj.url;
                app.ctrls.getContent(modes.emulate==false?obj.url:'emulate/listsubcats.html', app.ctrls.getSubCatListFromHTML, true);
                as.list='list_s';
                as.navig='navigation_s';
                $(as.navig).getElementsByClassName('second')[0].html('/ '+obj.name);
                as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_s;
            break;
            case as.layers.SUB_CATS_LIST:
                var obj = app.classes.list.ctrls.getCurItemInfo();
                log(obj.cat);
                log(obj.toString());
                switch(obj.cat){
                    case "true":
                        as.sublist=true;
                        app.ctrls.getContent(modes.emulate==false?obj.url:'emulate/listsubcats.html', app.ctrls.getSubCatListFromHTML, true);
                    break;
                    case "false":
                        app.ctrls.getContent(modes.emulate==false?obj.url:'emulate/item.html', app.ctrls.getItemFromHTML);
                    break;
                    case "music":
                        app.ctrls.getContent(modes.emulate==false?obj.url:'emulate/item.html', app.ctrls.getItemFromHTML);
                        //app.ctrls.getContent(modes.emulate==false?obj.url:'emulate/listsubcats.html', app.ctrls.getMusicSubCatListFromHTML, true);
                    break;
                    case "file":
                        var object_to_play = {};
                        object_to_play.name = $(as.list).getElementsByClassName('line')[app.classes.list.cur_item].html();
                        object_to_play.url = $(as.list).getElementsByClassName('line')[app.classes.list.cur_item].getAttribute('rel');
                        app.player.start(play)(object_to_play);
                    break;
                }
            break;
            case as.layers.PLAYER:
                if(app.views._player.isVisible()==true){
                    app.views._player.hide();
                    $('pls_form').hide()
                    log('app.views._player.hide();');
                    perm_player_show = 0;
                } else {
                    app.views._player.show();
                    $('pls_form').show()
                    log('app.views._player.show();');
                    perm_player_show = 1;
                }
            break;
            case as.layers.SEARCH:
                stb.HideVirtualKeyboard();
                app.views.showSection(as.layers.SUB_CATS_LIST,'search'); 
                branch = 'search';
                as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_s;
                as.list='list_s';
                as.navig='navigation_s';
                if($('suggest_list')){
                    document.body.removeChild($('suggest_list'));
                }
            break;
        }
    },
    // UP, RIGHT, DOWN, LEFT, CHANNEL_PREV, CHANNEL_NEXT, PAGE_NEXT, PAGE_PREV
    "processingNavigate":function(code){
        log('layer: ' + as.layer);
        switch(as.layer){
            case as.layers.SUB_CATS_LIST:	
            case as.layers.CATS_LIST:
                if(as.layer==as.layers.SUB_CATS_LIST){as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_s;as.list='list_s';as.navig='navigation_s';}
                else {as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_l;as.list='list';as.navig='navigation';}
                switch(code){
                    case keys.UP:
                    case keys.DOWN:
                    case keys.PAGE_NEXT:
                    case keys.PAGE_PREV:
                        app.classes.list.keypress(code);
                    break;
                }
                break;
            case as.layers.HOME:
                $(as.cur_home_position).className='';
                switch(as.cur_home_position){
                    case "button_video":
                        switch(code){
                            case keys.LEFT:
                                as.cur_home_position = "button_music";
                            break;
                            case keys.RIGHT:
                                if(as.dvice_model == 'MAG200'){
                                    as.cur_home_position = "button_search";
                                }else{
                                    as.cur_home_position = "button_images";
                                }
                            break;
                            case keys.UP:
                            case keys.DOWN:
                                if(as.dvice_model == 'MAG200'){
                                }else{
                                    as.cur_home_position = "button_search";
                                }
                            break;
                        }
                    break;
                    case "button_music":
                        switch(code){
                            case keys.LEFT:
                                if(as.dvice_model == 'MAG200'){
                                    as.cur_home_position = "button_search";
                                }else{
                                    as.cur_home_position = "button_images";
                                }
                            break;
                            case keys.RIGHT:
                                as.cur_home_position = "button_video";
                            break;
                            case keys.UP:
                            case keys.DOWN:
                                if(as.dvice_model == 'MAG200'){
                                }else{
                                    as.cur_home_position = "button_search";
                                }
                            break;
                        }
                        break;
                    case "button_images":
                        switch(code){
                            case keys.LEFT:
                                as.cur_home_position = "button_video";
                            break;
                            case keys.RIGHT:
                                as.cur_home_position = "button_music";
                            break;
                            case keys.UP:
                            case keys.DOWN:
                                if(as.dvice_model == 'MAG200'){
                                }else{
                                    as.cur_home_position = "button_search";
                                }
                            break;
                        }
                        break;
                    case "button_search":
                        switch(code){
                            case keys.LEFT:
                                if(as.dvice_model == 'MAG200'){
                                    as.cur_home_position = "button_video";
                                }else{
                                    as.cur_home_position = "button_music";
                                }
                            break;
                            case keys.RIGHT:
                                if(as.dvice_model == 'MAG200'){
                                    as.cur_home_position = "button_music";
                                }else{
                                    as.cur_home_position = "button_images";
                                }
                            break;
                            case keys.UP:
                            case keys.DOWN:
                                if(as.dvice_model == 'MAG200'){
                                }else{
                                    as.cur_home_position = "button_video";
                                }
                            break;

                        }
                    break;
                }
                $(as.cur_home_position).className='act';
                break;
            case as.layers.PLAYER:
                switch(code){
                    case keys.UP:
                        log(branch);
                        if(branch == 'images'){
                            app.player.previous();
                        }else{
                            app.player._interval("up");
                        }                                            
                    break;
                    case keys.DOWN:
                        if(branch == 'images'){
                            app.player.next();
                        }else{
                            app.player._interval("down");
                        }
                    break;
                    case keys.LEFT:
                        if(branch == 'images'){
                            app.player.previous();
                        }else{
                            app.player._jump("left");
                        }					    
                    break;
                    case keys.RIGHT:
                        if(branch == 'images'){
                            app.player.next();
                        }else{
                            app.player._jump("right");
                        }                                            
                    break;
                    case keys.PAGE_PREV:
                        app.player.previous();
                    break;
                    case keys.PAGE_NEXT:
                        app.player.next();
                    break;
                }
            break;
        }
    },
    // BACK, EXIT, REFRESH,APP, INFO, FRAME, SERVICES, TV, PHONE, WEB, POWER:
    "processingSytem":function(code){
        switch(code){
            case keys.EXIT:
                switch(as.layer){
                    case as.layers.HOME:
                        if(document.referrer.length > 4) {
                            window.location = document.referrer;
                            if(as.layer==as.layers.SUB_CATS_LIST){as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_s;as.list='list_s';as.navig='navigation_s';}
                            else {as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_l;as.list='list';as.navig='navigation';}
                        } else {
                           window.location= decodeURIComponent(_GET['referrer']);
                        }
                    break;
                    case as.layers.PLAYER:
                        app.player._stop();
                    break;
                    default:
                        as.sublist=false;
                        as.subcount=0;
                        app.classes.list.cur_page=0;
                        app.classes.list.cur_exPage=0;
                        app.views.showSection(as.layers.HOME);
                    break;
                }
            break;
            case keys.BACK:
                switch(as.layer){
                    case as.layers.SUB_CATS_LIST:
                        app.classes.list.cur_page=0;
                        app.classes.list.cur_exPage=0;
                        if(!as.sublist){
                        as.sizes[as.actualSize].max_list_lines=as.sizes[as.actualSize].max_list_lines_l;
                        app.classes.list.deep-=as.subcount;
                        as.subcount=0;
                        as.list='list';
                        as.navig='navigation';
                        as.layer=as.layers.CATS_LIST;
                        $(as.layers_pages[as.layers.SUB_CATS_LIST]).hide();
                        $(as.layers_pages[as.layers.CATS_LIST]).show();}
                        log('after all1: '+branch);
                        log('dip='+app.classes.list.deep);
                        log(as.layer);
                        if(branch == 'search'){
                            app.views.showSection(as.layers.SEARCH);
                        }else{
                            if(app.classes.list.history.length>2){
                                app.classes.list.ctrls.show(false,false,true);
                            } else {
                                //as.layer = as.layers.CATS_LIST;
                                app.classes.list.ctrls.show(false,false,true);
                            }
                        }
                                            
                    break;
                    case as.layers.CATS_LIST:
                        app.classes.list.cur_page=0;
                        app.classes.list.ctrls.show.cur_page=0;
                        app.views.showSection(as.layers.HOME);
                    break;
                    case as.layers.HOME:
                        if(document.referrer.length > 4) {
                            window.location = document.referrer;
                        } else {
                            window.location = '../services.html';
                        }
                    break;
                    case as.layers.PLAYER:
                        //app.player.change_audiotrack();
                    break;
                    case as.layers.SEARCH:
                        if(event.target.type=='text'){log("back cancel");return;}
                        app.views.showSection(as.layers.HOME);
                    break;
                }
            break;
            case keys.INFO:
                switch(as.layer){
                    case as.layers.PLAYER:
                        app.player.change_audiotrack();
                    break;
                }
            break;
        }
    },
    // RED, GREEN, YELLOW, BLUE
    "processingColor":function(code){
		
    },
    // USB_UNMOUNTED, USB_MOUNTED
    "processingUSDEvents":function(code){
		
    },
    "this.processingMENU":function(code){},
    // REW, FFWD, PLAY, PAUSE, CONTINUE, STOP, REC, VOL_UP, VOL_DOWN, MUTE, MIC
    "processingTV":function(code){
        switch(as.layer){
            case as.layers.PLAYER:
                switch(code){
                    case keys.STOP:
                        app.player._stop();
                    break;
                    case keys.PLAY:
                    case keys.PAUSE:
                        app.player.playOrPause();
                    break;
                    case keys.REW:
                        app.player._jump("left");
                    break;
                    case keys.FFWD:
                        app.player._jump("right");
                    break;
                }
            break;
        }
    },//Volume
    "processingVolume":function(code){
        switch(code){
            case keys.VOL_UP:
                app.ctrls.setvolume('up');
            break;
            case keys.VOL_DOWN:
                app.ctrls.setvolume('down');
            break;
            case keys.MUTE:
                app.ctrls.mute();
            break;
        }
    },
    "aspect_timer":0,
    "changeAspect":function(code){
        if(as.layer == as.layers.PLAYER){
            if($('aspectdiv')){
                clearTimeout(app.keypress.aspect_timer);
                document.body.removeChild($('aspectdiv'));
            }
            if(cur_aspect<3){
                cur_aspect++;
            }else{
                cur_aspect = 0;
            }
            log(aspect_array[cur_aspect].mode);
            stb.SetAspect(aspect_array[cur_aspect].mode);
            var aspect_obj = {
                'tag':'div',
                'attrs':{
                    'id':'aspectdiv',
                    'style':'width: 210px; height:41px; position:absolute;position:absolute; top: 70px; right:70px;'
                }
            };
            document.body.appendChild(app.views.createHTMLFromObj(aspect_obj));
            $('aspectdiv').style.background = 'url(img/aspect/'+as.cur_lang+'/'+aspect_array[cur_aspect].img+'.png) 50% 50% no-repeat';
            app.keypress.aspect_timer = setTimeout(function(){
                if($('aspectdiv')){
                    document.body.removeChild($('aspectdiv'));
                }
            },2000)
        }
    },
    // MENU
    "processingMENU":function(code){
		
    },
    // NUM1, NUM2, NUM3, NUM4, NUM5, NUM6, NUM7, NUM8, NUM9, NUM0
    "processingNUM":function(code){
		
    },
};

window.onkeydown = app.keypress.press;