var app = {};
app.ctrls={
    'init':function(){
        if(as.dvice_model == 'MAG200'){
            views.home.child[0].child[2].attrs.id = 'button_search';
            views.home.child[0].child[2].attrs.html = '{$cat_search$}';
            views.home.child.splice(1, 1);
        }
        app.ctrls.drawScreen(
            function(){
                setTimeout(
                    function(){
                        app.views._loading.hide();
                    },
                    150
                );
            }
        );
        log($('button_search').style.margin);
        log(as.dvice_model);
        if(as.dvice_model == 'MAG200'){
            //$('page_home').getElementsByClassName('inner_box')[0].removeChild($('button_images'));
            $('button_search').style.position = 'static';
            $('button_search').style.cssFloat = 'left';
            $('button_search').style.margin = as.actualSize==1080?'30px 0 0 25px':'10px';
        }
    },
    'drawScreen':function(callback){
        for(var key in views){
            log('key='+key)
            if(views.hasOwnProperty(key) && key!='components'){
                app.views.addElement(views[key],document.body);
                if(key=='loading') {
                    app.views._loading.show();
                }
                
            }
        }
        app.views.addElement(volobj,document.body);
        //app.classes.list.ctrls.init();
        callback.call(this);
    },
    "getContent":function(feed,callback,no_clean){
        app.models.getHTMLFromUrl(feed,callback);
    },
    "getCatListFromHTML":function(html){
        log('Top categories: ---');
        var cats=[],
            source_html_splited,
            source_html;
        source_html = /include_0(.*?)table/img.exec(html)[0].toString();
        source_html_splited = source_html.split('</td>');
        for(var i=0;i<source_html_splited.length;i++){
            var result1 = /href='(\/view\/.*?)'\><b>(.*?)<\/b><\/a/igm.exec(source_html_splited[i]);
            var result2 = /href='(\/ru\/video\/.*?)'\><b>(.*?)<\/b><\/a/igm.exec(source_html_splited[i]);
            if(result1==null && result2==null){continue;}
            var result = result1 || result2;

            if (result[2].toLowerCase().indexOf('megogo.net') != -1){
                continue;
            }

            cats.push({
                "url":'http://www.ex.ua'+result [1],
                "name":result[2]
            });
        }
        $(as.navig).getElementsByClassName('first')[0].show();
        log('branch: '+branch);
        $('navigation_s').getElementsByClassName('first')[0].html('/ '+lang['cat_'+branch]);
        $('navigation').getElementsByClassName('first')[0].html('/ '+lang['cat_'+branch]);
        $(as.navig).getElementsByClassName('second')[0].style.display='none';
        
        //app.classes.list.views.fill.simple(cats);
        app.classes.list.ctrls.show('simple', cats);
    },
    "getSubCatListFromHTML":function(html){
        log('Sub categories: ---');
    	var cats=[],
            source_html_splited,
            source_html;
            try{
                source_html = /include_0(.*?)table>/img.exec(html)[0].toString();
            }catch(e){
                if(branch == 'audio'){
                    source_html = /table\swidth\=100\%\sclass\=list[\s\S]*?\/table/img.exec(html)[0].toString().replace(/<script.*?\/script>/igm,'');
                }else{
                    if(branch == 'search'){
                        //log(html); //<table width=100% border=0 cellpadding=0 cellspacing=8 class=panel>
                        //log(/nothing[\s\S]was[\s\S]found/igm.test(source_html));
                        try{source_html = /table\swidth\=100\%\sborder=0\scellpadding=0\scellspacing=8\sclass=panel[\s\S]*?\/table/img.exec(html)[0].toString().replace(/<script.*?\/script>/igm,'');}
                        catch(e){
                            create_alert(lang['alert'],lang.NotF);
                            app.views.showSection(as.layers.SEARCH,'search_line');
                            return;
                        }
                    }
                }
            }
        //log(source_html); //if test(nothing was found!) -> ���� ������
        source_html_splited = source_html.split('</td>');
        for(var i=0;i<source_html_splited.length;i++){
                var result = /href='(\/view\/.*?)'.*?src='(.*?)'.*?href='.*?'><b>(.*?)<\/b><\/a>/igm.exec(source_html_splited[i]);
                //log("\n\n"+source_html_splited[i]+"\n\n")
                var result2 = /class='?"?info'?"?>(.*?):/igm.exec(source_html_splited[i]);
                var result3 = /<small>.*?<\/small>/igm.exec(source_html_splited[i]);
                if(result==null){continue;}
                var cat = "false";
                var add="";
                if(result3) {add = result3[0];
                add=add.replace(/January/g, lang.Jan).replace(/February/, lang.Feb).replace(/March/, lang.Ma).replace(/April/, lang.Apr).replace(/May/, lang.May).replace(/June/, lang.Jn).replace(/July/, lang.Jl).replace(/August/, lang.Ag).replace(/September/, lang.Sep).replace(/October/, lang.Oct).replace(/November/, lang.Now).replace(/December/, lang.Dec);}
                try{
                    if(result2[1]=='Articles' || result2[1]=='Статей') {
                            //log(result2[1].toString());
                            //log(result2[1].toString().length);
                            cat = "true";
                    }
                } catch(e){}
                	

                cats.push(
                	{
	                    "url":'http://www.ex.ua'+result[1],
	                    "img":result[2],
	                    "name":result[3],
	                    "cat":cat,
                            "add":add
                	}
            	);
                //log('http://www.ex.ua'+result[1]);
                //log(result [1] + ' : ' + result[2]);
        }
        $(as.navig).getElementsByClassName('first')[0].show();
        $(as.navig).getElementsByClassName('second')[0].show();
        as.layer = as.layers.SUB_CATS_LIST;
        app.classes.list.ctrls.show('extended', cats);        
    },
    "getMusicSubCatListFromHTML":function(html){
        log('Music Sub categories: ---');
        var cats=[],
            source_html_splited,
            source_html;
            source_html = /<table\swidth\=100\%\sclass\=list[\s\S]*?\/table>/img.exec(html)[0].toString().replace(/<script.*?\/script>/igm,'');
            source_html_splited = source_html.replace(/<table[\s\S]*?>/igm,'').replace(/<\/table>/igm,'').replace(/style\="[\s\S]*?"/igm,'').replace(/<\/tr>/gm,'').split('<tr>');
            log(source_html_splited.length);
            for(var i = 1;i<source_html_splited.length;i++){
                var tmpresult = /href='\/get\/[\s\S]*?<\/a>/igm.exec(source_html_splited[i]);
                try{
                    var result = /href='\/get\/\d*/igm.exec(tmpresult)[0].replace(/href='/igm,'').replace(/per=.*/,'');
                    var result2 = />[\s\S]*?</g.exec(tmpresult)[0].replace(/<|>/gm);
                }catch(e){}
                if(result==null){continue;}
                //gSTB.Debug(result);
                var cat = "file";
                cats.push(
                    {
                        "url":'http://www.ex.ua'+result,
                        "img":'',
                        "name":result2,
                        "cat":cat
                    }
                )
            }
            $(as.navig).getElementsByClassName('first')[0].show();
            $(as.navig).getElementsByClassName('second')[0].show();
        as.layer = as.layers.SUB_CATS_LIST;
        app.classes.list.ctrls.show('extended', cats);
    },
    "getItemFromHTML":function(html){
        log('ITEM from list: ---');
    	var cats=[],
            source_html;
    	html = html.replace(/[\n\r\t]/igm,' ').replace(/\s{1,}/igm,' ').replace(/<script.*?\/script>/igm,'').replace(/<\!--.*?-->/igm,'').replace(/<link.*?>/igm,'').replace(/<head>.*?\/head>/igm,'');
        source_html = /list\b(.*?)table>/img.exec(html)[0].toString();
        source_html_splited = source_html.split('/tr>');
        //log('source_html_splited:\n'+source_html_splited)
        for(var i=0;i<source_html_splited.length;i++){
            var result1 = /href='(\/get\/\d{1,})'.*?>(.*?)<\/a>.*?href='(.*?)'.*?src='(.*?)'/igm.exec(source_html_splited[i]);
            var result2 = /href='(\/get\/\d{1,})'.*?>(.*?)<\/a>/igm.exec(source_html_splited[i]);
            log('\nitem result_url: '+result1+' : '+result2);
            if(result1!=null){
            	//log(result1.toString());
            	cats.push({
                    "url":'http://www.ex.ua'+result1[1],
                    "name":result1[2],
                    "img_big":result1[3],
                    "img_prev":result1[4]
                });
            	//continue;
            } else {
	            if(result2!=null){
	            	//log(result2.toString());
	            	cats.push({
                            "url":'http://www.ex.ua'+result2[1],
	                    "name":result2[2],
	                    "img_big":'',
	                    "img_prev":''
	            	});
	            }
            }
            
        }
        /*
        for(var key=0;key<cats.length;key++){
        	log(cats[key].toString());
        }
        */
        //results = cats;
        app.ctrls.playContent(cats);
    },
    "playContent":function(list){
        log('playContent.list: '+list);
    	var play={
			"video":[],
			"audio":[],
			"images":[]
		};
		for(var i =0;i<list.length;i++){
                        log('playContent.list['+i+']: '+list[i].name);
			var ext = list[i].name.split('.');
				
			list[i].ext = ext[ext.length - 1];
                        log('['+i+']ext: '+list[i].ext)
			list[i].type = "none";
			//log(i + ' | ' + ext[ext.length - 1] + ' : ' +list[i].name);
                        if(as.dvice_model == 'MAG200'){
                            switch(list[i].ext.toLowerCase()){
                                    case "mpg":
                                    case "mov":
                                    case "mp4":
                                    case "avi":
                                    case "mkv":
                                    case "ts":
                                    case "vob":
                                    case "wmv":
                                    case "mts":
                                    case "m2t":
                                    case "m2v":
                                    case "divx":
                                    case "m2ts":
                                    case "m2p":
                                    case "tp":
                                    case "flv":
                                    case "mod":
                                    case "tod":
                                    case "asf":
                                            list[i].type = "video";
                                            play.video.push(list[i]);
                                    break;
                                    case "mp3":
                                    case "wav":
                                    case "ac3":
                                    case "ape":
                                    case "ogg":
                                    case "oga":
                                    case "aiff":
                                    case "wv":
                                    case "tta":
                                    case "wma":
                                    case "flac":
                                    case "m4a":
                                            list[i].type = "audio";
                                            play.audio.push(list[i]);
                                    break;
                            }
                        }else{
                           switch(list[i].ext.toLowerCase()){
                                    case "mpg":
                                    case "mov":
                                    case "mp4":
                                    case "avi":
                                    case "mkv":
                                    case "ts":
                                    case "vob":
                                    case "wmv":
                                    case "mts":
                                    case "m2t":
                                    case "m2v":
                                    case "divx":
                                    case "m2ts":
                                    case "m2p":
                                    case "tp":
                                    case "flv":
                                    case "mod":
                                    case "tod":
                                    case "asf":
                                            list[i].type = "video";
                                            play.video.push(list[i]);
                                    break;
                                    case "mp3":
                                    case "wav":
                                    case "ac3":
                                    case "ape":
                                    case "ogg":
                                    case "oga":
                                    case "aiff":
                                    case "wv":
                                    case "tta":
                                    case "wma":
                                    case "flac":
                                    case "m4a":
                                            list[i].type = "audio";
                                            play.audio.push(list[i]);
                                    break;
                                    case "jpg":
                                    case "jpeg":
                                            list[i].type = "images";
                                            play.images.push(list[i]);
                                    break;
                            }
                        }
		}
                if(play.audio.length == 0 && play.video.length == 0 && play.images.length == 0){
                    //app.views.showSection(as.layers.HOME);
                    create_alert(lang.mes,lang['No play'])
                }else{
                    app.views.showSection(as.layers.PLAYER, 'player');
                    app.player.start(play);
                }
    },
    "volume_timer":0,
    "setvolume":function(dir){
        if(mute == 1){
            mute = 0;
            stb.SetMute(mute);
        }
        switch(dir){
            case 'up':
                if(volume < 100){
                    volume+=5;
                }
            break;
            case 'down':
                if(volume>4){
                    volume-=5;
                }
            break;
        }
        stb.SetVolume(volume);
        $('volumecont').style.display = 'block';
        $('mute').style.display = 'none';
        $('volumeForm').style.visibility = 'visible';
        $('volume_right').style.width = volume*(1.95)+'px';
        $('volume_num').innerHTML = volume+'%';
        if(volume == 0){
            $('volumeLeft').style.display = 'none';
        }else{
            $('volumeLeft').style.display = 'block';
        }
        clearTimeout(app.ctrls.volume_timer);
        app.ctrls.volume_timer = setTimeout(function(){$('volumecont').style.display = 'none';},2000)
    },
    "mute":function(){
        mute = 1 - mute;
        stb.SetMute(mute);
        $('volumecont').style.display = 'block';
        $('mute').style.display = 'block';
        $('volumeForm').style.visibility = 'hidden';
        clearTimeout(app.ctrls.volume_timer);
        app.ctrls.volume_timer = setTimeout(function(){$('volumecont').style.display = 'none';},2000)
    }
};
app.models={
	"getHTMLFromUrl":function(url, callback,no_clean){
	    try {
	        //stb.EnableSetCookieFrom(".ex.ua", false); // disabled cookie receiving from domain '.youtube.com'
	        var request = new XMLHttpRequest();
	        request.open('GET', url, true);
	        request.setRequestHeader("Content-Type", "text/xml");
	        request.setRequestHeader("Cookie", "per=100");
	        request.setRequestHeader("Cookie2", "per=100");
	        request.setRequestHeader("charset", "utf-8");
	        if (request.overrideMimeType) {
	            request.overrideMimeType('text/html');
	        }
	        request.onreadystatechange = function ()
	        {
	            if (request.readyState == 4 && request.status == 200) {
	                log("Url " + url + " get done");
	                var html = request.responseText;//.replace(/<script.*?\/script>/igm,'')
                    html = html.replace(/[\n\r\t]/igm,' ').replace(/\s{1,}/igm,' ').replace(/<\!--.*?-->/igm,'').replace(/<head>.*?\/head>/igm,'');
                        log(html)
                        callback(html);
	                //setTimeout(function(){stb.EnableSetCookieFrom(".ex.ua", true);}, 500);//
	            }
	        };
	        
                log("Url " + url + " request.send(null)");
	        request.send(null); // send object
	    } catch (e) {
	        log("catch (e): \"" + e + "\"");
	        return;
	    }
	}
};
app.views={
    'addElement':function(obj,parent){
        parent.appendChild(
            this.createHTMLFromObj(
                obj
            )
        );
    },
    "createHTMLFromObj":function(obj){
        var el=document.createElement(obj.tag);
    	for(var key in obj.attrs){
                if(obj.attrs.hasOwnProperty(key)){
    			if(key!='html'){
    				el.setAttribute(key,obj.attrs[key]);
				} else {
					el.innerHTML=this.replaceLang(obj.attrs[key]);
				}
			}
		}
    	if(typeof obj.child!='undefined'){
    		for(var i=0;i<obj.child.length;i++){
    			el.appendChild(this.createHTMLFromObj(obj.child[i]));
			}
		}
    	return el;
    },
    "replaceLang":function(html){
        var test = /\{\$(.*?)\$\}/.exec(html);
    	if(test!=null && typeof(test)=='object'){
    		return html.replace(test[0], lang[test[1]]);
    	} else {
    		return html;
		}
    },
    'showSection':function(layer,content_type){
        for(var key in as.layers){
    		if(as.layers.hasOwnProperty(key) && as.layers[key]!=layer){
    			$(as.layers_pages[as.layers[key]]).hide();
    		}
    	}
    	$(as.layers_pages[layer]).show();
    	as.layer = layer;
        //branch = content_type;
        if(as.layer == as.layers.HOME){
            active_suggest = -1
            suggests = 0
            if($('suggest_list')){document.body.removeChild($('suggest_list'));}
        }
    	switch(content_type){
    		case "video":
    			app.ctrls.getContent(
    				modes.emulate==false ? 
    							as.feeds['video']
    						:
    							as.feeds['e'+'video'], 
    				app.ctrls.getCatListFromHTML
    			);
    		break;
    		case "audio":
    			app.ctrls.getContent(
    				modes.emulate==false ? 
    							as.feeds['audio']
    						:
    							as.feeds['e'+'audio'], 
    				app.ctrls.getCatListFromHTML
    			);
    		break;
                case "images":
    			app.ctrls.getContent(
    				modes.emulate==false ? 
    							as.feeds['images']
    						:
    							as.feeds['e'+'images'], 
    				app.ctrls.getCatListFromHTML
    			);
    		break;
                case "search":
                    app.ctrls.getContent(
    				modes.emulate==false ? 
    							active_suggest != -1?as.feeds['search']+ $('sug_'+active_suggest).innerHTML:as.feeds['search']+ encodeURI($('search_input').value)
    						:
    							as.feeds['e'+'search'], 
    				app.ctrls.getSubCatListFromHTML
    			);
                            
                break;
                case "search_line":
                    $('page_searchLine').style.display = 'block';
                    $('search_text').innerHTML = lang.search_lineLabel;
                    $('search_input').setAttribute('placeholder', lang.search_plaseholder);
                    $('search_input').focus();
                break;
    		case "player":
    			$(as.layers_pages[layer]).hide();
			break;
    		case "videoBack":
    		default:
			break;
    	}
    },
    '_loading':{
        'show':function(){
            $(as.layers_pages[as.layers.LOADING]).show();
        },
        'hide':function(){
                $(as.layers_pages[as.layers.LOADING]).hide();
        },
        'isVisible':function(){
            return $(as.layers_pages[as.layers.LOADING]).style.display=='none' ? false : true;
        }
    },
    '_player':{
        'show':function(){
            $(as.layers_pages[as.layers.PLAYER]).show();
        },
        'hide':function(){
                $(as.layers_pages[as.layers.PLAYER]).hide();
                $('pls_form').hide();
        },
        'isVisible':function(){
            return $(as.layers_pages[as.layers.PLAYER]).style.display=='none' ? false : true;
        }
    },
    'searchInput_getSug':function(){
        //log(event.keyCode+' '+event.altKey+' '+event.which);
       if(as.layer == as.layers.SEARCH){
            var key = event.keyCode || event.which;
            if(key == 40 || key == 38 || key == 13){   // (key < 48 || key > 57) && (key != 32) && (key < 66 || key > 90) letter keys
                if(key == 40){
                    if(active_suggest<0){
                        if($('suggest_list')){
                            active_suggest = 0;
                            $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                        }
                    }else{
                        if(active_suggest < (suggests - 1)){
                            $('sug_'+active_suggest).style.background = 'rgba(235,235,235,0.7)';
                            active_suggest++;
                            $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                        }else{
                            $('sug_'+active_suggest).style.background = 'rgba(235,235,235,0.7)';
                            active_suggest = 0;
                            $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                        }
                    }
                }else{
                    if(key == 13){
                        return
                    }
                    else{
                        if(active_suggest<0){
                            if($('suggest_list')){
                                active_suggest = (suggests - 1);
                                $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                            }
                        }else{
                            if(active_suggest > 0){
                                $('sug_'+active_suggest).style.background = 'rgba(235,235,235,0.7)';
                                active_suggest--;
                                $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                            }else{
                                $('sug_'+active_suggest).style.background = 'rgba(235,235,235,0.7)';
                                active_suggest = (suggests - 1);
                                $('sug_'+active_suggest).style.background = 'rgba(255,0,0,0.7)';
                            }
                        }
                    }
                }
                return;
            } 
            var value = $('search_input').value
            value = encodeURI(value);
            app.models.getHTMLFromUrl('http://www.ex.ua/r_search_hint?s='+value, app.views.createSuggest);
        }
    },
    'createSuggest':function(html){
        log(encodeURI(html));
        if(html != ''){
            var sug_arr = html.split(' '+$('search_input').value);
            suggests = sug_arr.length;
            log('suggests: '+sug_arr.length);
            if(sug_arr.length > 0){
                for(var i = 1;i < sug_arr.length;i++){
                    sug_arr[i] = $('search_input').value+sug_arr[i];
                }
                if($('suggest_list')){document.body.removeChild($('suggest_list'));}
                document.body.appendChild(app.views.createHTMLFromObj(suggest_list_obj));
                for(var j = 0; j < sug_arr.length; j++){
                    var obj = {
                        'tag':'div',
                        'attrs':{
                            'id':'sug_'+j,
                            'class':'suggest_item'
                        }
                    }
                    $('suggest_list').appendChild(app.views.createHTMLFromObj(obj))
                    $('sug_'+j).innerHTML = sug_arr[j];
                }
            }
            log(sug_arr);
        }
    }
};
app.player={
    "obj":{},
    "status":"pre",
    "playModes":["single","repeat","list","random"],
    "intervals":[30,60,300,600,1800,3600],
    "playlist_length":0,
    "fileNum":0,
    "cur":{
            "playMode":"list",	// successively
            "interval":1,	// 1 minute
            "scrolbar_width":0,
            "total_time":0

    },
    "start":function(play){
        log('play mode: '+app.player.cur.playMode);
        log('Number of file: '+app.player.fileNum);
            app.player.obj=play;
            
            if(play.video.length>0){
                $('big_form').show();
                app.views.addElement(alsobj,$("big_form"));
                app.views.addElement(acsobj,$("big_form"));
                app.views.addElement(arsobj,$("big_form"));
                //app.keypress.als_active_audio=parseInt(info_menu.audio);
                //app.keypress.als_active_subt=info_menu.on?parseInt(info_menu.subt):-1;
                app.keypress.als_on=info_menu.on;
                app.keypress.als_active_subt=-1;
                app.keypress.als_active_audio=-1;
                //stb.SetAudioLangs(iso639[info_menu.audio].code[0],"rus");
                stb.SetSubtitleLangs(iso639[info_menu.subt].code[0],"rus");
                stb.SetSubtitles(info_menu.on);
                app.player.playlist_length = play.video.length;
                log('length of playlist: '+app.player.playlist_length);
                if(as.proxy.lenght>0){
                    stb.Play('ffmpeg '+ play.video[app.player.fileNum].url, as.proxy);
                    log("* stb play with proxy: " + as.proxy);
                } else {
                    stb.Play('ffmpeg '+ play.video[app.player.fileNum].url);
                    log("* stb play NO proxy");
                }
                $('exPlayerName').innerHTML = play.video[app.player.fileNum].name;
                if(!$('pls_form')){
                    app.player.create_playlist(app.player.obj.video);
                }else{
                    app.player.fillPlaylist(app.player.obj.video)
                }
                
            }else{
                if( play.audio.length>0){
                    app.player.playlist_length = play.audio.length;
                    log('length of playlist: '+app.player.playlist_length);
                    if(as.proxy.lenght>0){
                        stb.Play('ffmpeg '+ play.audio[app.player.fileNum].url, as.proxy);
                        log("* stb play with proxy: " + as.proxy);
                    } else {
                        stb.Play('ffmpeg '+ play.audio[app.player.fileNum].url);
                        log("* stb play NO proxy");
                    }
                    $('exPlayerName').innerHTML = play.audio[app.player.fileNum].name;
                    if(!$('pls_form')){
                        app.player.create_playlist(app.player.obj.audio);
                    }else{
                        app.player.fillPlaylist(app.player.obj.audio)
                    }
                }else{
                    if(play.images.length>0){
                        app.player.playlist_length = play.images.length;
                        log('length of playlist: '+app.player.playlist_length);
                        if(as.proxy.lenght>0){
                            stb.Play('jpeg '+ play.images[app.player.fileNum].url, as.proxy);
                            log("* stb play with proxy: " + as.proxy);
                        } else {
                            stb.Play('jpeg '+ play.images[app.player.fileNum].url);
                            log("* stb play NO proxy");
                        }
                        $('exPlayerName').innerHTML = play.images[app.player.fileNum].name;
                        if(!$('pls_form')){
                            app.player.create_playlist(app.player.obj.images);
                        }else{
                            app.player.fillPlaylist(app.player.obj.images)
                        }
                    }
                }
            }
            app.views._player.show();
            log(perm_player_show);
            if(!perm_player_show){
                window.setTimeout(app.views._player.hide, 3000);
                window.setTimeout(function(){if($('pls_form'))$('pls_form').hide()}, 3000);
            }
            app.player.cur.scrolbar_width = as.sizes[as.actualSize].player_scrolbar_width;
            log('app.player.cur.statusbar_width: ' + app.player.cur.scrolbar_width);


    $('exPlayerTimeCur').html('00:00:00');    // zerring actual time
    $('exPlayerTimeTotal').html('00:00:00');
    app.player._interval();
    },
    "interval_obj":null,
    "interval_func":function(pos, pos_p){
            var posTime=0,t_pers=0;
            log('pos='+pos)
            if(!pos && pos!=0){
            log('1');
            posTime = stb.GetPosTime();             // get current position in seconds
            t_pers = stb.GetPosPercent() + 1;
            } else {
            log(2);
            posTime =pos;             // get current position in seconds
            t_pers = pos_p;
            }
    var t_time_obj = {"hours":0,"minuts":0,"seconds":0};
    t_time=posTime>0?posTime:0;
    t_time_obj.hours = Math.floor(t_time / 3600);
    t_time_obj.minuts = Math.floor((t_time - t_time_obj.hours * 3600) / 60);
    t_time_obj.seconds = t_time - t_time_obj.hours * 3600 - t_time_obj.minuts * 60;


    var marginLeft = Math.floor((t_pers + 1) / 100 * app.player.cur.scrolbar_width); // count scrolling position
    $('exPlayerStatus').style.marginLeft = marginLeft + 'px';  // set scrolling position
    $('exPlayerTimeCur').html(((t_time_obj.hours < 10)? "0" + t_time_obj.hours : t_time_obj.hours) + ':' + ((t_time_obj.minuts < 10)? "0" + t_time_obj.minuts : t_time_obj.minuts) + ':' + ((t_time_obj.seconds<10)? "0" + t_time_obj.seconds : t_time_obj.seconds));
    log('* Time: {' + $('exPlayerTimeCur').html() + " / " + $('exPlayerTimeTotal').html() + '}, status: ' + t_pers + '%, : '+ t_time + 's, ' + marginLeft + 'px / ' + app.player.cur.scrolbar_width + 'px;');

    },
    "interval_clear":function(){
            if(app.player.interval_obj!=null){
                    clearInterval(app.player.interval_obj);
            app.player.interval_obj = null;
            return this.interval_clear();
            }
    },
    "events":function(event	){
            log("* stbEvent: " + event);
    event = parseInt(event);
    switch(event) {
        case 1: // the player has reached the end of the recorded media content or a long gap flux
            if($('als_form')){
                $('big_form').innerHTML='';
                $('big_form').hide();
                stb.SetSubtitles(false);
                app.keypress.audio_langv.lenght=0;
                app.keypress.subtitri_langv.lenght=0;
            }
            if($('pls_form')){
                var div = $('pls_form');
                document.body.removeChild(div);
            }
            clearInterval(app.player.interval_obj);
            switch(app.player.cur.playMode) { // switch modes
                case "repeat":
                    app.player._restart();
                break;
                case "list":
                    app.player.next();
                break;
                case "random":
                    /*

                     */
                break;
                case "single":
                default:
                    app.player._stop();
                break;
            }
        break;
        case 2: // obtained information about audio and video tracks of media content
            var time = stb.GetMediaLen();
            if(app.player.obj.video.length>0){
                //log(stb.GetAudioPIDsEx())
                //log(stb.GetSubtitlePIDs())
                app.keypress.audio_langv = eval(stb.GetAudioPIDsEx());
                app.keypress.subtitri_langv = eval(stb.GetSubtitlePIDs());
                
                log("aud="+app.keypress.audio_langv)
                log("sub="+app.keypress.subtitri_langv)
            }
            app.player.cur.total_time = time;
            var time_obj={"hours":0,"minuts":0,"seconds":0};
            time_obj.hours = Math.floor(time / 3600);   // current move duration in hours
            time_obj.minuts = Math.floor((time - time_obj.hours * 3600) / 60);   // current move duration in minuts
            time_obj.seconds = time - time_obj.hours * 3600 - time_obj.minuts * 60;   // current move duration in seconds
            $('exPlayerTimeTotal').html(((time_obj.hours<10)? "0" + time_obj.hours : time_obj.hours) + ':' + ((time_obj.minuts<10)? "0" + time_obj.minuts : time_obj.minuts) + ':' + ((time_obj.seconds<10)? "0" + time_obj.seconds : time_obj.seconds));
        break;
        case 4: // beginning to display videos and / or play sound
            app.player.status = "play";
            log('this.status = "play"');
            app.player.interval_obj = setInterval(app.player.interval_func, 1000);
            // loading hide
        break;
        case 5: // error opening content: there is no such content on a server or a fault has occurred while connecting to the server
            app.player._stop();
        break;
    }
    },
    "playOrPause": function() {
        if(app.player.status=="play") {
        	app.player._pause();
        } else {
        	app.player._continue();
        }
    },
    "previous":function(){
        if(app.player.fileNum > 0){
            app.player.fileNum--;
            app.player.start(app.player.obj);
        }else{
            app.player._stop();
        }
    },
    "next":function(){
        if(app.player.fileNum < app.player.playlist_length-1){
            app.player.fileNum++;
            app.player.start(app.player.obj);
        }else{
            app.player._stop();
        }
    },
    "_pause": function() {
        log('* stb.Pause();');
        stb.Pause();
        app.player.interval_clear();
        app.player.status = "paused";
        app.views._player.show();
        //clearInterval(app.player.interval_obj)
    },
    "_continue": function() {
        log('* stb.Continue();');
        stb.Continue();
        app.player.status = "play";
        app.views._player.hide();
        //app.player.interval_obj = setInterval(app.player.interval_func, 1000);
    },
    "_restart": function() {
    	app.player.start(app.player.obj);
    },
    "_stop": function() {
        log('* stb.Stop();');
        stb.Stop();
        if(app.player._jump_obj != null){
            clearTimeout(app.player._jump_obj);			
            app.player._jump_obj = null;
	}
        app.player.status = "pre";
        app.views.showSection(as.layers.SUB_CATS_LIST, '');
        app.player.interval_clear();
        app.player.fileNum = 0;
        if($('als_form')){
            $('big_form').innerHTML='';
            $('big_form').hide();
            stb.SetSubtitles(false);
            app.keypress.audio_langv.lenght=0;
            app.keypress.subtitri_langv.lenght=0;
        }
        if($('pls_form')){
            var div = $('pls_form');
            document.body.removeChild(div);
        }
    },
    "_jump_obj":null,	// interval
    "_jump_from":0,		// jump to point
    "_jump_to":0,		// jump to point
    "_jump_time":0,		// 
    "_jump_time_step":100,//
    "_jump_time_begin":2000,//
    "_jump_function":function(){
    	if(app.player._jump_time<=0) {
            stb.SetPosTime(app.player._jump_to_point);
            log("* _jump: " + app.player._jump_to_point);
            app.player._jump_from = 0;
            app.player._jump_time = 0;
            app.player._jump_to = 0;
            app.player._jump_to_point = 0;
    		app.player._continue();
    		clearTimeout(app.player._jump_obj);
    		app.player._jump_obj = null;
    	} else {
    		app.player._jump_time-=app.player._jump_time_step;
    		log("timer_time: "+app.player._jump_time);
    	}
    },
    "_jump":function(to){
    	clearInterval(app.player._jump_obj);
        if(app.player._jump_from==0 && stb.IsPlaying()){
    		app.player._jump_from = stb.GetPosTime();
    	}
        app.player._pause();
    	log('app.player._jump_from: ' + app.player._jump_from);
    	log('app.player._jump_to: ' + app.player._jump_to);
    	switch(to){
    		case "left":
    			app.player._jump_to-= app.player.intervals[app.player.cur.interval];
    		break;
    		case "right":
    			if(app.player._jump_to_point < app.player.cur.total_time)app.player._jump_to+= app.player.intervals[app.player.cur.interval];
			break;
    	}
    	log('app.player._jump_to: ' + app.player._jump_to);
    	if(app.player._jump_obj != null){
			clearTimeout(app.player._jump_obj);			
			app.player._jump_obj = null;
		}
    	app.player._jump_to_point = app.player._jump_from + app.player._jump_to;
    	if(app.player._jump_to_point < 0){
    		app.player._jump_to_point = 0;
    	}
        var i=0.1;
        var a=app.player._jump_to_point;
    	if(app.player._jump_to_point >app.player.cur.total_time){
            while(app.player._jump_to_point >app.player.cur.total_time){
    		{app.player._jump_to_point =i<=0.7?Math.round(a-app.player.intervals[app.player.cur.interval]* i):app.player.cur.total_time;i+=0.1;}
                //app.player._jump_to_point = Math.round((app.player.cur.total_time + app.player._jump_to_point) / 2 * 0.9);
            }
    	}
    	log('app.player._jump_to: ' + app.player._jump_to);
    	app.player.interval_func(app.player._jump_to_point, Math.round(app.player._jump_to_point / app.player.cur.total_time * 100));
    	// move indicator
    	
    	app.player._jump_time = app.player._jump_time_begin;
    	app.player._jump_obj = setInterval(app.player._jump_function, app.player._jump_time_step);
    },
    "_interval":function(to){
    	if(to){
    		log("* _interval: " + to);
    	}
    	switch(to){
			case "up":
				if(app.player.cur.interval<app.player.intervals.length - 1) {
					app.player.cur.interval++;
				} else {
					app.player.cur.interval = 0;
				}
			break;
			case "down":
				if(app.player.cur.interval>0) {
					app.player.cur.interval--;
				} else {
					app.player.cur.interval = app.player.intervals.length - 1;
				}
			break;
			default:				
			break;
		}
    	var t_time = app.player.intervals[app.player.cur.interval],
	    	h = Math.floor(t_time / 3600);
	        m = Math.floor((t_time - h * 3600) / 60);
	        s = t_time - h * 3600 - m * 60,
	        str ='';
    	if(h>0){
    		str+=h + ' ' + lang.hrs;
    	} else {
    		if(m>0){
        		str+= m + ' ' + lang.min;
        	} else {
        		str+= s + ' ' + lang.sec;
        	}
    	}
    	$('exPlayerIntervalCur').html(str);
    },
    "create_playlist":function(object){
        app.views.addElement(plsobj,document.body);//$('exPlayer')
        var tempY = 0;
        for(var i = app.player.fileNum;i<(app.player.fileNum+10) && i < app.player.playlist_length;i++){
            var pls_line_obj_y = {
                    'tag':'div',
                    'attrs':{
                        'class':'pls_line',
                        'style':'width: 300px; height: 26px; '
                    },
                    'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'id':'pls_midline_left'+tempY,
                                'style':'background: url(img/info_menu/v_menu_2a.png) left bottom; white-space: nowrap; width: 270px; height: 26px; font-size: 21px; padding: 2px 10px; overflow: hidden; text-overflow: ellipsis; float:left;'
                            }
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'id':'pls_midline_right'+tempY,
                                'class':'pls_midline_right',
                                'style':'background: url(img/info_menu/v_menu_2a.png) right bottom; width: 10px; height: 30px; float:left;'
                            }
                        }
                    ]
                }
            app.views.addElement(pls_line_obj_y,pls_form);
            $('pls_midline_left'+tempY).innerHTML = object[app.player.fileNum+tempY].name;
            playlist_len = tempY;
            tempY++;
        }
        $('pls_midline_left0').style.background = 'url(img/info_menu/v_menu_2a.png) left top';
        $('pls_midline_right0').style.background = 'url(img/info_menu/v_menu_2a.png) right top';
        tempY = 0;

        app.views.addElement(pls_lowline,pls_form);
    },
    "fillPlaylist":function(object){
        var tempY = 0;
        for(var i = app.player.fileNum;i<(app.player.fileNum+10) && tempY < (playlist_len+1);i++){
            if(i < app.player.playlist_length){
                $('pls_midline_left'+tempY).innerHTML = object[app.player.fileNum+tempY].name;
                tempY++;
            }else{
                $('pls_midline_left'+tempY).innerHTML = '';
                tempY++;
            }
        }
        tempY = 0;
    },
    "change_audiotrack":function(){
        if($('als_form').style.display!='none'){
         $('als_form').hide();
         $('acs_form').hide();
         $('ars_form').hide();
         window.onkeydown = app.keypress.press;
       } else {
         app.keypress.als_index_h=1;
         app.keypress.als_hor(-1);
         app.keypress.als_vert(-1);
         $('als_form').show();
         $('acs_form').show();
         $('ars_form').show();
         $('pls_form').hide();
         window.onkeydown = app.keypress.press_als;
       }
    },
    "InfoMenu":function(sectionName,data){
        var s = {}, lang_info, text='';
        var d=$('ars_form');
        log('audio='+info_menu.audio);
        d.innerHTML='';
        app.keypress.als_max_v=0;
        app.keypress.als_index_v=1;
        var div={'tag':'div','attrs':{'style':'width: 300px; height: 15px;'},'child':[{'tag':'div','attrs':{'class':'als_uline_left'}},{'tag':'div','attrs':{'class':'als_uline_right'}}]};
        app.views.addElement(div,d);
        log(app.keypress.als_active_audio)
        switch (sectionName) {
            case "audio":
                app.keypress.als_max_v=data.length;
                app.keypress.als_layer=1;
                for(var i=0;i<data.length;i++){
                    lang_info  = getLanguageNameByCode(data[i].lang);
                    if(lang_info != null){
                        text=lang_info[0];                       
                    }
                    else {text=lang.unk + '&nbsp;("' + getIso639LangCode(data[i].lang) + '")';}
                    log('audio='+app.keypress.als_active_audio)
                    
                    if(app.keypress.als_active_audio==-1){if(iso639[info_menu.audio].code[0]==data[i].lang[0])app.keypress.als_index_v=i+1;}
                    else if(data[app.keypress.als_active_audio].pid==data[i].pid)app.keypress.als_index_v=i+1;
                    div={
                        'tag':'div',
                        'attrs':{
                        'id':'ars_line_'+(i+1),
                        'class':'als_line',
                        'style':'width: 300px; height: 26px; '
                        },
                        'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_left'
                            },
                            'child':[
                                {
                                'tag':'div',
                                'attrs':{
                                    'id':"ars_img_"+(i+1),
                                    'class':'ars_img',
                                }
                                },
                                {'tag':'div',
                                'attrs':{
                                    'html':text
                                }}
                            ]
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_right'
                            }
                        }
                        ]};
                    app.views.addElement(div,d);
                }
                break;
                
            case "subtitles":
                app.keypress.als_max_v=data.length+1;
                app.keypress.als_layer=2;
                div={
                        'tag':'div',
                        'attrs':{
                        'id':'ars_line_1',
                        'class':'ars_line',
                        'style':'width: 300px; height: 26px; '
                        },
                        'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_left'
                            },
                            'child':[
                                {
                                'tag':'div',
                                'attrs':{
                                    'id':"ars_img_1",
                                    'class':'ars_img',
                                }
                                },
                                {'tag':'div',
                                'attrs':{
                                    'html':lang.off
                                }}
                            ]
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_right'
                            }
                        }
                        ]};
                    app.views.addElement(div,d);              
                
                for(var i=0;i<data.length;i++){
                    lang_info  = getLanguageNameByCode(data[i].lang);
                    if(lang_info != null){
                        text=lang_info[0];                       
                    }
                    else {text=lang.unk + '&nbsp;("' + getIso639LangCode(data[i].lang) + '")';}
                    log('subtitri='+app.keypress.als_active_subt)
                    if(app.keypress.als_on)if(app.keypress.als_active_subt==-1){if(iso639[info_menu.subt].code[0]==data[i].lang[0])app.keypress.als_index_v=i+2;}
                    else if(data[app.keypress.als_active_subt].pid==data[i].pid)app.keypress.als_index_v=i+2;
                    div={
                        'tag':'div',
                        'attrs':{
                        'id':'ars_line_'+(i+2),
                        'class':'als_line',
                        'style':'width: 300px; height: 26px; '
                        },
                        'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_left',
                            },
                            'child':[
                                {
                                'tag':'div',
                                'attrs':{
                                    'id':"ars_img_"+(i+2),
                                    'class':'ars_img',
                                }
                                },
                                {'tag':'div',
                                'attrs':{
                                    'html':text
                                }}
                            ]
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_right'
                            }
                        }
                        ]};
                    app.views.addElement(div,d);
                }
                break;
                case "3dmode":
                    app.keypress.als_layer=3;
                    app.keypress.als_max_v=4;
                    for(var i=1;i<=4;i++){
                        div={
                                'tag':'div',
                                'attrs':{
                                'id':'ars_line_'+i,
                                'class':'als_line',
                                'style':'width: 300px; height: 26px; '
                                },
                                'child':[
                                {
                                    'tag':'div',
                                    'attrs':{
                                        'class':'als_midline_left'
                                    },
                                    'child':[
                                        {
                                        'tag':'div',
                                        'attrs':{
                                            'id':"ars_img_"+i,
                                            'class':'ars_img',
                                        }
                                        },
                                        {'tag':'div',
                                        'attrs':{
                                            'html':lang.mode+" "+i
                                        }}
                                    ]
                                },
                                {
                                    'tag':'div',
                                    'attrs':{
                                        'class':'als_midline_right'
                                    }
                                }
                                ]};
                        app.views.addElement(div,d);
                    }
                        
                       
        }
        log(app.keypress.als_index_v)
        if($('ars_img_'+app.keypress.als_index_v))$('ars_img_'+app.keypress.als_index_v).className='ars_img_a';
        app.keypress.als_index_v=1;
        if($('ars_line_1'))$('ars_line_1').className='als_line_a';
        else
                        {div={
                        'tag':'div',
                        'attrs':{
                        'id':'ars_line_1',
                        'class':'als_line',
                        'style':'width: 300px; height: 26px; '
                        },
                        'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_left',
                                'html':lang.nota
                            }
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'als_midline_right'
                            }
                        }
                        ]};
                    app.views.addElement(div,d);
                app.keypress.als_max_v=0;}
        div={'tag':'div','attrs':{'style':'width: 300px; height: 15px;'},'child':[{'tag':'div','attrs':{'class':'als_dline_left'}},{'tag':'div','attrs':{'class':'als_dline_right'}}]};
        app.views.addElement(div,d);
        log(d.innerHTML)
    }
};
