function eventFunc(event){
    event = parseInt(event);
    log(event);
    switch(event){
        case 1:
            if(!advIsPlaying){
                secondadvwached = false;
                thirdadvwatched = false;
            }
            if(file.result.compilation!=null){
                markTNS('playend', file.result.tns_id);
                var watch_id = file.result.id;
                ga('End', file.result.google_analytics_id, watch_id)
                if(current_series<seasons.length-1){
                    current_series++;
                    sesies_getdata(seasons[current_series].id);
                }
            }else{
                if(advIsPlaying){
                    advIsPlaying = false;
                    start_playing(vars.player_vars.timetostart);
                }else{
                    try{
                        markTNS('playend', file.result.tns_id);
                        var watch_id = file.result.id;
                        ga('End', file.result.google_analytics_id, watch_id)
                    }catch(e){log(e)}
                    switchLayer(layer_cats);
                }
            }
        break;
        case 2:
            get_mediadata();
            if(!advIsPlaying){
                adv_timer = setInterval(function(){
                    vars.file_percent = stb.GetPosPercentEx();
                    if(!empty(reclama)){
                        if(Math.floor(vars.file_percent/100)==50 && !secondadvwached){
                            startADVplaying(2);
                            secondadvwached = true;
                        }
                        if(Math.floor(vars.file_percent/100)==95 && !thirdadvwatched){
                            startADVplaying(1);
                            thirdadvwatched = true;
                        }
                    }
                },1000)
            }
        break;
        case 4:
        break;
        case 5:
            try{
                var watch_id = file.result.id;
                ga('Linkerror', file.result.google_analytics_id, watch_id)
            }catch(e){log(e)}
            switchLayer(layer_cats);
        break;
    }
}

function start_playing(timetostart){
    $('video_title').innerHTML = file.result.title;
    switchLayer(layer_player);
    try{
        markTNS('playstart', file.result.tns_id);
        var watch_id = file.result.id;
        var time = new Date()
        ga('Start', file.result.google_analytics_id, watch_id);
        sendreq_post(
            'http://api.digitalaccess.ru/api/json/',
            log,
            '{"method":"da.content.watched","params":['+file.result.id+',{"contentid":'+file.result.id+',"watchid":"'+watch_id+''+time.getTime()+'","site":"s127"}]}'//
        );
    }catch(e){log(e)}
    var best_quality = {"idx":0,"type":0};
    for (var i = 0;i<file.result.files.length;i++){
        for(var y = 0;y<fileTyps.length;y++){
            if(file.result.files[i].content_format == fileTyps[y]){
                if(y>best_quality.type){
                    best_quality.type=y;
                    best_quality.idx=i;
                }
            }
        }
    }
    if(!empty(timetostart)){
        stb.Play('ffmpeg '+file.result.files[best_quality.idx].url+' position:'+timetostart);
    }else{
        stb.Play('ffmpeg '+file.result.files[best_quality.idx].url);
    }
}

function startADVplaying(idx){
    $('video_title').innerHTML = 'Реклама';
    switchLayer(layer_player);
    try{
        var time = new Date();
        var watch_id = file.result.id+'.'+time.getTime();
        var advwatch_id = file.result.midroll[idx]+'.'+time.getTime();
    var ivifile = stb.LoadUserData('ivifile');
    if(empty(ivifile)){
        ivifile = [];
    }else{
        ivifile = JSON.parse(ivifile);
    }
    }catch(e){log(e)}
    var best_quality = {"idx":0,"type":0};
    if(reclama.result[idx]){
        for (var i = 0;i<reclama.result[idx].files.length;i++){
            for(var y = 0;y<fileTyps.length;y++){
                if(reclama.result[idx].files[i].content_format == fileTyps[y]){
                    if(y>best_quality.type){
                        best_quality.type=y;
                        best_quality.idx=i;
                    }
                }
            }
        }
        ivifile.splice(ivifile.length,0,{"time":time.getTime(),"id":reclama.result[idx].files[best_quality.idx].id});
        //console.log(JSON.stringify(ivifile));
        stb.SaveUserData('ivifile',JSON.stringify(ivifile))
        sendreq_post(
                    'http://api.digitalaccess.ru/api/json/',
                    log,
                    '{"params":['+file.result.id+','+reclama.result[idx].files[best_quality.idx].id+',{"contentid":'+file.result.id+',"advwatchid":"'+advwatch_id+'","watchid":"'+watch_id+'","advid":"'+reclama.result[idx].files[best_quality.idx].id+'","site":"s127"}],"method":"da.adv.watched"}'//
                );
                log('{"params":['+file.result.id+','+reclama.result[idx].files[best_quality.idx].id+',{"contentid":'+file.result.id+',"advwatchid":"'+advwatch_id+'","watchid":"'+watch_id+'","advid":'+reclama.result[idx].files[best_quality.idx].id+',"site":"s127"}],"method":"da.adv.watched"}');
                    if(idx==3){
                        ga('Midroll', file.result.google_analytics_id, watch_id)
                    }else{
                        if(idx == 2){
                            ga('Postroll', file.result.google_analytics_id, watch_id)
                        }
                    }

        stb.Play('ffmpeg '+reclama.result[idx].files[best_quality.idx].url);
        advIsPlaying = true;
        log('ABS: '+vars.file_curtime)
        vars.player_vars.timetostart = vars.file_curtime;
    }
}

function get_video_data(text){
    //console.log(text)
	var file_dump = JSON.stringify(file);
    file = JSON.parse(text);
    try{
        markTNS('playstart', file.result.tns_id);
        var watch_id = file.result.id;
        ga('Start', file.result.google_analytics_id, watch_id);
        var time = new Date()
        sendreq_post(
            'http://api.digitalaccess.ru/api/json/',
            log,
            '{"method":"da.content.watched","params":['+file.result.id+',{"contentid":'+file.result.id+',"watchid":"'+watch_id+''+time.getTime()+'","site":"s127"}]}'//
        );
    }catch(e){log(e)}
    var best_quality = {"idx":0,"type":0};
	if(!empty(file.error)){
		newMyAlert(file.error.message,"temp");
		file = JSON.parse(file_dump);
		return;
	}
    if(!empty(file.result.compilation_id)){
        $('video_title').innerHTML = file.result.compilation + ' ' +file.result.title;
    }else{
        $('video_title').innerHTML = file.result.title;
    }
    for (var i = 0;i<file.result.files.length;i++){
        for(var y = 0;y<fileTyps.length;y++){
            if(file.result.files[i].content_format == fileTyps[y]){
                if(y>best_quality.type){
                    best_quality.type=y;
                    best_quality.idx=i;
                }
            }
        }
    }
    switchLayer(layer_player);
    stb.Play('ffmpeg '+file.result.files[best_quality.idx].url);
}

function get_mediadata(){
    vars.file_lenght = stb.GetMediaLen();
    vars.file_percent = stb.GetPosPercentEx();
    vars.file_curtime = stb.GetPosTime();
    vars.player_vars.quarter = Math.floor(vars.file_lenght/4);
    start_showinfo();
}

function start_showinfo(){
    log(vars.player_shown)
    var curTime = media_getHourMinSec(vars.file_curtime);
    var totalTime = media_getHourMinSec(vars.file_lenght);
    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
    $('total_time').innerHTML = totalTime.hour+':'+totalTime.minute+':'+totalTime.second;
    $('progress').style.width = vars[win.height].stripe_len*vars.file_percent/10000+'px';
    if(vars.player_shown){
        runner_run();
    }

}

function runner_run(){
    clearInterval(runner_timer);
    runner_timer = window.setInterval(function(){
        
        vars.file_curtime = stb.GetPosTime();
        var curTime = media_getHourMinSec(stb.GetPosTime());
        $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
        $('progress').style.width = vars[win.height].stripe_len*vars.file_percent/10000+'px';
        if(!advIsPlaying){
            if(vars.file_curtime==vars.player_vars.quarter){
                var watch_id = file.result.id;
                ga('25', file.result.google_analytics_id, watch_id)
            }else{
                if(vars.file_curtime==vars.player_vars.quarter*2){
                    var watch_id = file.result.id;
                    ga('50', file.result.google_analytics_id, watch_id)
                }else{
                    if(vars.file_curtime==vars.player_vars.quarter*3){
                        var watch_id = file.result.id;
                        ga('75', file.result.google_analytics_id, watch_id)
                    }
                }
            }
        }
    },1000);
}