function eventFunc(event){
    event = parseInt(event);
    log(event);
    switch(event){
        case 1:
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = '0px';
            if(info_obj.series){
                if(vars.info_focus_obj[1]<info_obj.series.length-1){
                    vars.info_focus_obj[1]++
                    sendreq(olltvURL+'media?id='+info_obj.series[vars.info_focus_obj[1]].series_id, getPlayerObj);
                }else{
                    switchLayer(info_layer);
                }
            }else{
                switchLayer(info_layer);
            }
        break;
        case 2:
            get_mediadata();
        break;
        case 4:
            $('loading').style.display = 'none';
        break;
        case 5:
            $('loading').style.display = 'none';
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            stb.Stop();
            $('cur_time').innerHTML = '';
            $('progress').style.width = 'px';
            switchLayer(info_layer);
            show_message('Контент временно недоступен.')
        break;
    }
}

function getPlayerObj(text,startPos){
    //console.log(text);
    //stb.SetCustomHeader('Cookie:'+sessionID);
    var media_obj = JSON.parse(text);
    if(media_obj.id){
        if(media_obj.id == 501){
            show_message('<div id="message_text" class="modal_title">Внимание!</div><div id="message_">Это видео доступно только для жителей Украины. Если ты являешься жителем Украины, но видео тебе не доступно, пожалуйста, сообщи нам об этом.<br>oll.tv/feedback</div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');
            return;
        }
    }
    if(media_obj.media_url){
        //sendreq(media_obj.media_url, log)
        media_obj.media_url = media_obj.media_url.replace(/\/\//igm, '/').replace(/\:\//,'://')

        if(/\?/ig.test(media_obj.media_url)){

            if (_GET.hasOwnProperty('proxy')){
                stb.Play("auto "+media_obj.media_url+'&serial_number='+vars.sn, _GET['proxy']);//
            }else{
                stb.Play("auto "+media_obj.media_url+'&serial_number='+vars.sn);//
            }
        }else{

            if (_GET.hasOwnProperty('proxy')){
                stb.Play("auto "+media_obj.media_url+'?serial_number='+vars.sn, _GET['proxy']);//
            }else{
                stb.Play("auto "+media_obj.media_url+'?serial_number='+vars.sn);//
            }
        }
        switchLayer(player_layer);
    }else{
        show_message('<div id="message_text" class="modal_title">Трансляция ещё не началась</div><div id="message_">К сожалению, эта трансляция ещё не началась. Следи за анонсами на сайте <b>www.oll.tv</b></div><div class="msgbtn subscript_btn_act" onclick="hide_message()" >Закрыть</div>');
    }
} 

function get_mediadata(){
    vars.file_lenght = stb.GetMediaLen();
    vars.file_percent = stb.GetPosPercentEx();
    vars.file_curtime = stb.GetPosTime();
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
        vars.file_percent = stb.GetPosPercentEx();
        vars.file_curtime = stb.GetPosTime();
        var curTime = media_getHourMinSec(stb.GetPosTime());
        $('cur_time').innerHTML = curTime.hour+':'+curTime.minute+':'+curTime.second;
        $('progress').style.width = vars[win.height].stripe_len*vars.file_percent/10000+'px';
    },1000);
}

function setdirTime(number){
    if(postime.length>5){
        return;
    }
    postime+=''+number;
    TimeObj = parseTime(parseInt(postime,10));
    vars.player_shown = true;
    clearInterval(runner_timer);
    clearTimeout(pos_timer);
    clearTimeout(setpos_timer);
    setpos_timer = false;
    stb.Pause();
    vars.file_curtime = (parseInt(TimeObj.hour,10)*3600+parseInt(TimeObj.min,10)*60+parseInt(TimeObj.sec,10));
    $('cur_time').innerHTML = TimeObj.hour+':'+TimeObj.min+':'+TimeObj.sec;
    $('head').style.display = 'block';
    $('player').style.display = 'block';
    
    setpos_timer = true;
    setpos_timer = window.setTimeout(function(){
        TimeObj = parseTime(parseInt(postime,10));
        if(TimeObj.hour>23){TimeObj.hour=23};
        if(TimeObj.min>59){TimeObj.min=59};
        if(TimeObj.sec>59){TimeObj.sec=59};
        vars.file_curtime = (parseInt(TimeObj.hour,10)*3600+parseInt(TimeObj.min,10)*60+parseInt(TimeObj.sec,10));
        if(vars.file_curtime>vars.file_lenght){
            vars.file_curtime=vars.file_lenght-3;
            TimeObj.hour=Math.floor(vars.file_curtime/3600);
            TimeObj.min=Math.floor((vars.file_curtime-TimeObj.hour*3600)/60);
            TimeObj.sec=vars.file_curtime-TimeObj.hour*3600-TimeObj.min*60;
        }
        vars.file_curtime = (parseInt(TimeObj.hour,10)*3600+parseInt(TimeObj.min,10)*60+parseInt(TimeObj.sec,10));
        //console.log(objToString(TimeObj));
        stb.SetPosTime(vars.file_curtime);
        stb.Continue();
        setpos_timer = false;
        postime='';
        pos_timer = window.setTimeout(runner_run,1500);}
    ,1300);
}

function parseTime(a){
    log("parseTime");
    if(a>=0){
        var h=Math.floor(a/10000);
        var m=Math.floor((a-h*10000)/100);
        var s=a-h*10000-m*100;
        if(s<10)s='0'+s;
        if(m<10)m='0'+m;
    }
    else{
        a=Math.abs(a);
        var h=Math.floor(a/10000);
        var m=Math.floor((a-h*10000)/100);
        var s=a-h*10000-m*100;
        if(s<10)s='0'+s;
        if(m<10)m='0'+m;
        h="-"+h;
    }
    var t_time={"hour":h,"min":m,"sec":s};
    return t_time;
}