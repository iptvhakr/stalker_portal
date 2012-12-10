var startPosFromContinue = 0;

function eventFunc(event){
    event = parseInt(event);
    log(event);
    switch(event){
        case 1:
            if(fileInfo.isSeries){
                season = file.video[0].season_list[seasonLst.pos].id;
                if(!empty(episodeLst.idLst[season][episodeLst.pos+1]))
                        episodeLst.pos++;
                else{
                        if(!empty(file.video[0].season_list[seasonLst.pos+1].episode_list[0].id)){
                                seasonLst.pos++;
                                season = file.video[0].season_list[seasonLst.pos].id;
                                log(season);
                                initSeriesLst(seasonLst.pos, 0) ;
                                episodeLst.pos = 0;
                        }
                        else {
                                $('menu_series').style.display = 'block';
                                $('player_page').style.display = 'none';
                                $('footer').style.display = 'block';
                                currLst = seriesLst;
                                PREV_LAYER = 5;
                                CUR_LAYER = 1;
                                currLst.onChange();
                                currLst.reset();
                        }
                }

                urlVideo['episode'] = episodeLst.idLst[season][episodeLst.pos];
                urlVideo['season'] = season;
                if(seriesLst.bitrates[bitratesLst.pos] != undefined)
                    urlVideo['bitrate'] = seriesLst.bitrates[bitratesLst.pos].id;
                sendreq(iviURL+'info?'+createSign(urlVideo),start_playing1);
                return;
            }

            currLst = seriesLst;
            $('menu_series').style.display = 'block';
            $('player_page').style.display = 'none';
            $('footer').style.display = 'block';
            currLst = seriesLst;
            PREV_LAYER = 5;
            CUR_LAYER = 1;
            currLst.onChange();
            currLst.reset();
        break;
        case 2:
            get_mediadata();
        break;
        case 4:
            if(startPosFromContinue != 0){
                stb.SetPosTime(startPosFromContinue);
                startPosFromContinue = 0;
            }
            runFl = true;
            runner_run();
        break;
        case 5:
        break;
    }
}

function finish(text){
}

function get_mediadata(){
 log('get_mediadata');
    vars.file_lenght = stb.GetMediaLen();
    vars.file_percent = stb.GetPosPercentEx();
    vars.file_curtime = stb.GetPosTime();
    start_showinfo();
}

function start_showinfo(){
    log(vars.player_shown);
    log('start_showinfo');
    var curTime = media_getHourMinSec(vars.file_curtime);
    var totalTime = media_getHourMinSec(vars.file_lenght);
    $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
    $('total_time').innerHTML = totalTime.hour+':'+totalTime.minute+':'+totalTime.second;
    $('progress').style.width = vars[win.height].stripe_len*vars.file_percent/10000+'px';
}

function runner_run(){
    runner_timer = window.setInterval(function(){
        if(runFl) {
        vars.file_percent = stb.GetPosPercentEx();
        vars.file_curtime = stb.GetPosTime();

        var curTime = media_getHourMinSec(vars.file_curtime);
        $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
        $('progress').style.width = vars[win.height].stripe_len*vars.file_percent/10000+'px';}
    },1000);
}