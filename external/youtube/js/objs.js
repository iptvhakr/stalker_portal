var player_cond = 0,
    player = {
    "obj":new Object(), //current object
    "condition": 1, // play/pause
    "current_step": 30, // step rewind
    "steps": [5,10,20,30,60,300,600,900], // step rewinds
    "interval": new Object(), // timer for everystep
    "interval_text": "setInterval(player.every_sec, 1000);", // text
    "last_step": 10, // in percents
    "where": 0,      //position for set after rewind
    "timer": new Object(),  // timer for rewind
    "timer_cur": 0,         // current residual time for cheking and end timer rewind
    "timer_iv": 1000,
    "timer_iv_len": 1000,   // started timer period
    "timer_iv_step": 100,   // step for rewind timer
    "obj": new Object(),    // playying obj
    "scrolbar_width": 390,  // #scrollbar width
    "n_where": "",          // string for player position after press nums
    "n_where_sec": 0,       // int number of seconds for player position after press nums
    //
    //
    //
    "timer_check_start_player_obj":null,
    "timer_check_start_player_count":0,
    "timer_check_start_player_max_count":5,
    "timer_check_start_player_interval": 15000, //seconds waite for some action 10s
    "timer_check_start_player_action": function() {
        log('\ntimer_check_start_player_action\n');
        player.timer_check_start_player_obj = null;
        switch(current.playMode) {
            case "single":
            case "repeat":
                player.timer_check_start_player_count++;
                player.playingPreparation(player.obj);
            break;
            case "random":
                if(player.timer_check_start_player_count < player.timer_check_start_player_max_count) {
                    player.playingPreparation(player.obj);
                    return;
                }
                player.timer_check_start_player_count = 0;
                player.playingPreparation(current.globalObj[random(current.globalObj.length)]);
            break;
            case "list":
                if(player.timer_check_start_player_count < player.timer_check_start_player_max_count) {
                    player.playingPreparation(player.obj);
                    return;
                }
                player.timer_check_start_player_count = 0;
                var metka = false;
                for(var i = 0; i < current.globalObj.length; i++) {
                    if(metka) {
                        player.playingPreparation(current.globalObj[i]);
                        break;
                    }
                    if(current.globalObj[i] == player.obj) {
                        metka = true;
                    }
                    if(metka && i == current.globalObj.length - 1) {
                        player.playingPreparation(current.globalObj[0]);
                    }
                }
            break;
        }
    },
    //
    //
    //
    //
    /**
    * `playingPreparation` is method for set start 
    *
    * @function
    * @name playingPreparation
    * @param {function(value):*}
    * @example
    * player.playingPreparation(obj);
    * @return void
    */
    "playingPreparation": function(obj) {
        if(win.width==1920) {
            player.scrolbar_width = 780;
        }
        player.obj = obj;
        var url = "http://www.youtube.com/watch?v=" + player.obj.id;
        //var url = "http://www.youtube.com/get_video_info?video_id=" + player.obj.id;
        getHtmlByUrl(url);
        player.current_step = 30;
        byID('playModeInPlayer').className = current.playMode;
        player.timer_check_start_player_obj = setTimeout("player.timer_check_start_player_action();", player.timer_check_start_player_interval);
    },
    /**
    * `iv_stop` is method for start everyseconds timer
    *
    * @function
    * @name iv_stop
    * @param {function():*}
    * @example
    * player.iv_stop();
    * @return void
    */
    "iv_stop": function() {
        clearInterval(player.interval);
        player.interval = null;

        clearInterval(player.timer);
        player.timer = null;
    },
    /**
    * `iv_start` is method for start everyseconds timer
    *
    * @function
    * @name iv_start
    * @param {function():*}
    * @example
    * player.iv_start();
    * @return void
    */
    "iv_start": function() {
        clearInterval(player.interval);
        player.interval = null;
        player.interval = eval(player.interval_text);
    },
    /**
    * `play` is method for start playing move
    *
    * @function
    * @name play
    * @param {function(arr):*} //arr - array arr[(int)format] = (string)'url';
    * @example
    * player.play(arr);
    * @return void
    */
    "play": function(arr) {
        byID('shell').style.display = 'none';   // hide
        player.current_step = 30;               // interval step
        player_cond = 1;                        // set player condition in playing mode
        current.layer = layers.PLAYER;          // set manage layer PLAYER
        
        var url =  "";
        log('- - - - player.play(obj) - - - - current quality:' + current.priority + ' - - - -');
        var  isset_qualites = '- - - - ';
        for(var item in arr) {
            isset_qualites += item + ',';
        }
        isset_qualites += ' - - - -';
        log(isset_qualites);
        for(var i=0; i<prioritets[current.priority].length; i++) {  // check all current priorites
            if(undefined != arr[prioritets[current.priority][i]]) {
                log('- - - playing result | format:' + prioritets[current.priority][i]);
                url = arr[prioritets[current.priority][i]]; // set url
                break;
            }
        }
        byID('conditiion').style.marginLeft = '0px';    // set current position of scroll to begin
        byID('filename').innerHTML = player.obj.title.length > 35 ? player.obj.title.substr(0, 34) + "…" : player.obj.title;    // cut move name
        var time = player.obj.duration;
        var time_obj = {
            "hours":0,
            "minuts":0,
            "seconds":0
        };
        time_obj.hours = Math.floor(time / 3600);   // current move duration in hours
        time_obj.minuts = Math.floor((time - time_obj.hours * 3600) / 60);   // current move duration in minuts
        time_obj.seconds = time - time_obj.hours * 3600 - time_obj.minuts * 60;   // current move duration in seconds
        log(time_obj.hours + ':' + time_obj.minuts + ':' + time_obj.seconds);
        byID('time_act').innerHTML = '00:00:00';    // zerring atual time
        byID('time_total').innerHTML = '/ ' + ((time_obj.hours<10)? "0" + time_obj.hours : time_obj.hours) + ':' + ((time_obj.minuts<10)? "0" + time_obj.minuts : time_obj.minuts) + ':' + ((time_obj.seconds<10)? "0" + time_obj.seconds : time_obj.seconds);
        byID('interval_input').innerHTML = lang.intervals[player.current_step]; // set lang interval
        
        if(proxy_enable==true){
            stb.Play('ffrt3 '+ url, proxy_string);
        } else {
            stb.Play('ffrt3 '+ url);            // device start
        }
        
        
        byID('mute').style.display = (this.muteStatus==1) ? 'block' : 'none';   // show/hide mute picture
    },
    /**
    * `playerEvent` is method for check events from device
    *
    * @function
    * @name playerEvent
    * @param {function(event):*} (int)
    * @example
    * player.playerEvent(event);
    * @return void
    */
    "playerEvent": function(event) {
        log(" - - - stbEvent: " + event);
        event = parseInt(event);
        switch(event) {
            case 1: // the player has reached the end of the recorded media content or a long gap flux
                switch(current.playMode) { // switch modes
                    case "repeat":
                        stb.Continue();
                    break;
                    case "list":
                        var metka = false;
                        for(var i = 0; i < current.globalObj.length; i++) {
                            if(metka) {
                                player.playingPreparation(current.globalObj[i]);
                                break
                            }
                            if(current.globalObj[i] == player.obj) {
                                metka = true;
                            }
                            if(metka && i == current.globalObj.length - 1) {
                                player.playingPreparation(current.globalObj[0]);
                            }
                        }
                    break;
                    case "random":
                        player.playingPreparation(current.globalObj[random(current.globalObj.length)]);
                    break;
                    case "single":
                    default:
                        player_cond = 0;
                        byID('main').style.display = 'none';
                        byID('mute').style.display = 'none';
                        byID('shell').style.display = 'block';
                        current.layer = layers.BASE;
                        log('player.iv_stop();');
                        player.iv_stop();
                    break;
                }
            break;
            case 2: // obtained information about audio and video tracks of media content
            break;
            case 4: // beginning to display videos and / or play sound
                loading.hide();
                player_cond = 1;
                player.iv_start();
                clearTimeout(player.timer_check_start_player_obj);
                player.timer_check_start_player_obj = null;
                log('\nclearTimeout(player.timer_check_start_player_obj);\n');
            break;
            case 5: // error opening content: there is no such content on a server or a fault has occurred while connecting to the server
                player_cond = 0;
                log('player.iv_stop();');
                byID('shell').style.display = 'block';
                current.layer = layers.BASE;
                player.iv_stop();
            break;
        }
    },
    /**
    * `stop` is method for stop move playing
    *
    * @function
    * @name stop
    * @param {function():*}
    * @example
    * player.stop();
    * @return void
    */
    "stop": function() {
        log('stb.Stop();');
        player.iv_stop();
        clearTimeout(player.timer_check_start_player_obj);
        player.timer_check_start_player_obj = null;
        stb.Stop();
        byID('main').style.display = 'none';
        byID('shell').style.display = 'block';
        current.layer = layers.BASE;
        byID('playingStatus').style.display = 'none';
        byID('mute').style.display = 'none';
    },
    /**
    * `stop_pre_play` is method for stop move playing
    *
    * @function
    * @name stop_pre_play
    * @param {function():*}
    * @example
    * player.stop_pre_play();
    * @return void
    */
    "stop_pre_play": function() {
        log('stb.Stop();');
        player.iv_stop();
        stb.Stop();
        byID('playingStatus').style.display = 'none';
        byID('mute').style.display = 'none';
        loading.show();
    },
    /**
    * `playOrPause` is method for trigging player condition (pause/play)
    *
    * @function
    * @name playOrPause
    * @param {function():*}
    * @example
    * player.playOrPause();
    * @return void
    */
    "playOrPause": function() {
        log("- - - status pre: " + player_cond);
        if(player_cond==1) {
            byID('main').style.display = 'block';
            player._pause();
        } else {
            byID('main').style.display = 'none';
            player._continue();
        }
        log("- - - status post: " + player_cond);
    },
    /**
    * `_pause` is method for pause move
    *
    * @function
    * @name _pause
    * @param {function():*}
    * @example
    * player._pause();
    * @return void
    **/
    "_pause": function() {
        log('- - - stb.Pause(); (status: ' + player_cond + ')');
        if(player_cond != 0) {
            stb.Pause();
        }
        player_cond = 0;
        player.iv_stop();
        byID('playingStatus').style.display = 'block';
    },
    /**
    * `_continue` is method for continue move
    *
    * @function
    * @name _continue
    * @param {function():*}
    * @example
    * player._continue();
    * @return void
    **/
    "_continue": function() {
        log('- - - stb.Continue(); (status: ' + player_cond + ')');
        if(player_cond != 1) {
            stb.Continue();
        }
        player_cond = 1;
        player.iv_start();
        byID('playingStatus').style.display = 'none';
    },
    /**
    * `toBegin` is method for start move again
    *
    * @function
    * @name toBegin
    * @param {function():*}
    * @example
    * player.toBegin();
    * @return void
    **/
    "toBegin": function() {
        player.iv_stop();
        log('stb.Stop();');
        log('stb.Continue();');
        stb.Stop();
        stb.Continue();
        player.iv_start();
        byID('conditiion').style.marginLeft = '0px';
        byID('time_act').innerHTML = '00:00:00';
        byID('main').style.display = 'block';
    },
    /**
    * `changeStep` is method for change rewind interval by circle
    *
    * @function
    * @name changeStep
    * @param {function((string)direction):*}
    * @example
    * player.changeStep('up');
    * @return void
    **/
    "changeStep": function(direction) {
        byID('main').style.display = 'block';
        var i;
        switch(direction) {
            case "up":
                for(i = player.steps.length - 1; i >= 0; i--) {
                    if(player.steps[i]==player.current_step) {
                        log("down | current " + i);
                        if(i==player.steps.length - 1) {
                            player.current_step = player.steps[0];
                        } else {
                            player.current_step = player.steps[i + 1];
                        }
                        byID('interval_input').innerHTML = lang.intervals[player.current_step];
                        break;
                    }
                }
            break;
            case "down":
                for(i = 0; i < player.steps.length; i++) {
                    if(player.steps[i] == player.current_step) {
                        log("up | current " + i);
                        if(i == 0) {
                            player.current_step = player.steps[player.steps.length - 1];
                        } else {
                            player.current_step = player.steps[i - 1];
                        }
                        byID('interval_input').innerHTML = lang.intervals[player.current_step];
                        break;
                    }
                }
            break;
        }
    },
    /**
    * `every_sec` is method for every second change visual status move
    *
    * @function
    * @name every_sec
    * @param {function():*}
    * @example
    * player.every_sec();
    * @return void
    **/
    "every_sec": function() {
        var posTime = stb.GetPosTime();             // get current position in seconds
        if(player.obj.duration < posTime) {return;} // sequrity
        var t_pers = stb.GetPosPercent() + 1;
        var t_time = posTime>=0 ? posTime: 0;
        var t_time_obj = {"hours":0,"minuts":0,"seconds":0};
        t_time_obj.hours = Math.floor(t_time / 3600);
        t_time_obj.minuts = Math.floor((t_time - t_time_obj.hours * 3600) / 60);
        t_time_obj.seconds = t_time - t_time_obj.hours * 3600 - t_time_obj.minuts * 60;
        var marginLeft = Math.floor((t_pers + 1) / 100 * player.scrolbar_width); // count scrolling position
        byID('conditiion').style.marginLeft = marginLeft + 'px';  // set scrolling position
        byID('time_act').innerHTML = ((t_time_obj.hours < 10)? "0" + t_time_obj.hours : t_time_obj.hours) + ':' + ((t_time_obj.minuts < 10)? "0" + t_time_obj.minuts : t_time_obj.minuts) + ':' + ((t_time_obj.seconds<10)? "0" + t_time_obj.seconds : t_time_obj.seconds);
        log('- - - -\nTime:' + ((t_time_obj.hours < 10)? "0" + t_time_obj.hours : t_time_obj.hours) + ':' + ((t_time_obj.minuts < 10)? "0" + t_time_obj.minuts : t_time_obj.minuts) + ':' + ((t_time_obj.seconds<10)? "0" + t_time_obj.seconds : t_time_obj.seconds) + 'status: ' + t_pers + '%, : '+ t_time + 's, ' + marginLeft + 'px / ' + player.scrolbar_width + 'px\n - - - -');
    },
    /**
    * `rewind_every_100_ms` is method for check ending timeout and start move from seted position
    *
    * @function
    * @name rewind_every_100_ms
    * @param {function():*}
    * @example
    * player.rewind_every_100_ms();
    * @return void
    **/
   "rewind_every_100_ms": function() {
        log('player.timer_cur: ' + player.timer_cur);
        if(player.timer_cur < player.timer_iv_step) {
            stb.SetPosTime(player.where);
            player._continue();
            clearInterval(player.timer);
            player.timer = null;
            log('player.iv_start();');
            player.iv_start();
            player.where = 0;
        }
        player.timer_cur = player.timer_cur - player.timer_iv_step;
    },
    /**
    * `rewind_every_100_ms` is method for check ending timeout and start move from seted position
    *
    * @function
    * @name rewind
    * @param {function():*}
    * @example
    * player.rewind((int)to);
    * @return void
    **/
    "rewind": function(to) {
        byID('main').style.display = 'block';
        player._pause();
        clearInterval(player.timer);
        player.timer_cur = player.timer_iv_len;
        var len = stb.GetMediaLen();

        if(player.where == 0)
        {
            player.where = stb.GetPosTime();
        }

        if (to > 0) {
            var rest = len - player.where;
            if (rest > player.current_step)
            {
                player.where += player.current_step;
            }
            else
            {
                player.where = Math.floor ((((len - player.where)*9)/10) + player.where);
            }
        }
        else
        {
            if (player.where > player.current_step) {
                player.where -= player.current_step;
            }
            else
            {
                player.where = Math.floor (player.where/10);
            }
        }

        var t_time = player.where;
        var t_time_obj = {
            "hours":0,
            "minuts":0,
            "seconds":0
        };
        t_time_obj.hours = Math.floor(t_time / 3600);
        t_time_obj.minuts = Math.floor((t_time - t_time_obj.hours * 3600) / 60);
        t_time_obj.seconds = t_time - t_time_obj.hours * 3600 - t_time_obj.minuts * 60;
        byID('time_act').innerHTML = ((t_time_obj.hours<10)? "0" + t_time_obj.hours : t_time_obj.hours) + ':' + ((t_time_obj.minuts<10)? "0" + t_time_obj.minuts : t_time_obj.minuts) + ':' + ((t_time_obj.seconds<10)? "0" + t_time_obj.seconds : t_time_obj.seconds);

        byID('conditiion').style.marginLeft = Math.floor(player.where / len * player.scrolbar_width) + 'px';
        player.timer = setInterval(player.rewind_every_100_ms, player.timer_iv_step);
    },
    /**
    * `pressNums` is method for set nums from console
    *
    * @function
    * @name pressNums
    * @param {function(num):*}
    * @example
    * player.pressNums((int)num);
    * @return void
    **/
    "pressNums": function(num) {
        if(byID('main').style.display == 'block') {
            player._pause();
            if(num > -1) {
                if(player.n_where.length < 6) {
                    player.n_where += num.toString();
                }
            } else {
                player.n_where = player.n_where.substr(0, player.n_where.length - 1);
            }
            var sec = 0,
                min = 0,
                hou = 0;

            log("\n---\nn_where.length:" + player.n_where.length + "\nn_where: " + player.n_where + "\n---");
            switch (player.n_where.length) {
                case 0:
                    byID('time_act').innerHTML = '';
                    player.n_where_sec = 0;
                break;
                case 1:
                    byID('time_act').innerHTML = '00:00:0' + player.n_where;
                    player.n_where_sec = parseInt(player.n_where);
                break;
                case 2:
                    byID('time_act').innerHTML = '00:00:' + player.n_where;
                    sec = parseInt(player.n_where);
                    player.n_where_sec = sec > 60 ? 58 : sec;
                break;
                case 3:
                    byID('time_act').innerHTML = '00:0' + player.n_where.substr(0, 1) + ':' + player.n_where.substr(1, 2);
                    sec = parseInt(player.n_where.substr(1, 2));
                    min = parseInt(player.n_where.substr(0, 1));
                    player.n_where_sec = sec > 60 ? 58 : sec + min * 60;
                break;
                case 4:
                    byID('time_act').innerHTML = '00:' + player.n_where.substr(0, 2) + ':' + player.n_where.substr(2, 2);
                    sec = parseInt(player.n_where.substr(0, 2));
                    min = parseInt(player.n_where.substr(2, 2));
                    player.n_where_sec = sec > 60 ? 58 : sec + (min > 60 ? 58 : min) * 60;
                break;
                case 5:
                    byID('time_act').innerHTML = '0' + player.n_where.substr(0, 1) + ':' + player.n_where.substr(1, 2) + ':' + player.n_where.substr(3, 2);
                    sec = parseInt(player.n_where.substr(3, 2));
                    min = parseInt(player.n_where.substr(1, 2));
                    hou = player.n_where.substr(0, 1);
                    player.n_where_sec = sec > 60 ? 58 : sec + (min > 60 ? 58 : min) * 60 + hou * 60 * 60;
                break;
                case 6:
                    byID('time_act').innerHTML = player.n_where.substr(0, 2) + ':' + player.n_where.substr(2, 2) + ':' + player.n_where.substr(4, 2);
                    sec = parseInt(player.n_where.substr(4, 2));
                    min = parseInt(player.n_where.substr(2, 2));
                    hou = player.n_where.substr(0, 2);
                    player.n_where_sec = sec > 60 ? 58 : sec + (min > 60 ? 58 : min) * 60 + hou * 60 * 60;
                break;
            }
            log("- - - ");
            log("- player.n_where: " + player.n_where);
            log("- player.n_where_sec: " + player.n_where_sec);
            log("- - - ");
        }
    },
    /**
    * `pressOK` is method for set position after consol numbers enter or show/hide player
    *
    * @function
    * @name pressOK
    * @param {function():*}
    * @example
    * player.pressOK();
    * @return void
    **/
    "pressOK": function() {
        log('- - - player.pressOK();');
        if(player.n_where.length > 0) {
            stb.SetPosTime(player.n_where_sec);
            player._continue();
            player.n_where = "";
        } else {
            if(byID('main').style.display!='none') {
                byID('main').style.display = 'none';
            } else {
                byID('main').style.display = 'block';
            }
        }
    },
    "volume_timer": null, // timer for change volume level
    "volume_time": 3000,  // current volume timer (ms)
    "volume_time_std": 3000,    // standart time for start timer
    "volume_time_step": 100,    // step volume timer
    /**
    * `volume_every_100_ms` is method for check ending timeout and hide volume level
    *
    * @function
    * @name volume_every_100_ms
    * @param {function():*}
    * @example
    * player.volume_every_100_ms();
    * @return void
    **/
    "volume_every_100_ms": function() {
        if(player.volume_time < player.volume_time_step) {
            clearInterval(player.volume_timer);
            player.volume_timer = null;
            byID('volumeForm').style.display = 'none';
        }
        player.volume_time = player.volume_time - player.volume_time_step;
    },
    /**
    * `pressVolume` is method for change volume level
    *
    * @function
    * @name pressVolume
    * @param {function(direction):*}
    * @example
    * player.pressVolume((int)direction);
    * @return void
    **/
    "pressVolume": function(direction) {
        clearInterval(player.volume_timer);
        player.volume_timer = null;

        if(this.muteStatus==1) {
            player.pressMute();
        }
        
        byID('volumeForm').style.display = 'block';

        player.volume_time = player.volume_time_std;
        player.volume_timer = setInterval(player.volume_every_100_ms, player.volume_time_step);

        var step_px = (win.width == 1920) ? 15 : 10;
        var vol_bar = stb.GetVolume(); // 300, / 20 = 5% : 15px

        log("level volume: " + vol_bar);

        if(direction > 0) {
            vol_bar = vol_bar + 5;
            if(vol_bar>100) {vol_bar = 100;}
        } else {
            vol_bar = vol_bar - 5;
            if(vol_bar < 5) {vol_bar = 0;}
        }

        log("level volume: " + vol_bar);

        var vol_bar_len = vol_bar * ((win.width == 1920) ? 3 : 2);
        byID('volume_bar').style.width = vol_bar_len + 'px';
        stb.SetVolume(vol_bar);
        byID('volume_num').innerHTML = vol_bar + '%';
        
    },
    "muteStatus": 0,
    /**
    * `pressMute` is method for change mute condition
    *
    * @function
    * @name pressMute
    * @param {function():*}
    * @example
    * player.pressMute();
    * @return void
    **/
    "pressMute": function() {
        byID('volumeForm').style.display = 'none';
        log(this.muteStatus);
        this.muteStatus = (this.muteStatus==0) ? 1 : 0;
        log(this.muteStatus);
        stb.SetMute(this.muteStatus);
        byID('mute').style.display = (this.muteStatus==1) ? 'block' : 'none';
    },
    "dmc_var": null,
    /**
    * `displayModeChange` is method for change display mode change
    *
    * @function
    * @name displayModeChange
    * @param {function():*}
    * @example
    * player.displayModeChange();
    * @return void
    **/
    "displayModeChange": function() {
        if(player.dmc_var) {clearTimeout(player.dmc_var);}
        player.dmc_var = null;
        log(aspects[aspect_current].mode);
        aspect_current = (aspect_current + 1) % 4;
        stb.SetAspect(aspects[aspect_current].mode);
        log(aspects[aspect_current].mode);
        byID('screenAspect').style.backgroundImage = 'url(' + aspects[aspect_current].img + ')';
        byID('screenAspect').style.backgroundPosition = '0 -'+(aspect_current*41)+'px';
        byID('screenAspect').style.display = 'block';
        player.dmc_var = setTimeout(function() {byID('screenAspect').style.display = 'none';}, 3000);
    }
},
workWithItems = {
    /*
    * var
    * used in relocation active object
    * */
    "shift":0,
    /**
    * `drawBoxes` is method for draw current boxes with movements
    *
    * @function
    * @name drawBoxes
    * @param {function():*}
    * @example
    * workWithItems.drawBoxes();
    * @return void
    */
    "drawBoxes":function() {
        byID("frame").innerHTML = '';   // clean parent bopx
        byID("arrow_left").style.display = ((current.page - 1) < 1) ? "none" : "block"; // set visibility of arrow to up
        for(var i=(current.page - 1) * items.atPage; i <  current.page * items.atPage; i++) {
            if(!empty(current.globalObj[i])) {
                var time =  new Date().Difference(current.globalObj[i].uploaded);       // get text value of uploaded time ago
                var viewsCount = separate(current.globalObj[i].viewCount);              // get text value of count views
                byID("frame").innerHTML += '' +
                    '<div class="box" id="' + i + '" title="' + current.globalObj[i].id + '">' + "\n" +
                    '   <div class="inner">' + "\n" +
                    '       <div class="img_cover">' + "\n" +
                    '           <img src="' + current.globalObj[i].img + '" class="prew" alt="" />' + "\n" +
                    '       </div>' + "\n" +
                    '       <p class="title">' + current.globalObj[i].title + '</p>' +  "\n" +//&hellip;((current.globalObj[i].title.length<=27)? current.globalObj[i].title : current.globalObj[i].title.substr(0, 27) + '…') + '</p>' +  "\n" +//&hellip;
                    ((win.height==480)?'':'<p>&nbsp;' + time + '</p>' + "\n") +
                    '       <p>&nbsp;' + lang.views + viewsCount + '</p>' + "\n" +
                    '       <p>&nbsp;' + current.globalObj[i].category + '</p>' + "\n" +
                    '   </div>' + "\n" +
                    '</div>';
            }
        }
        byID("arrow_right").style.display = (current.page == Math.ceil(request.totalItems/items.atPage)) ? "none" : "block"; // set visibility of arrow to down
        this.focusMovie.apply(this);    // call to function change focus
    },
    /**
    * `focusMovie` is method for change position of active object on @this.shift elements
    *
    * @function
    * @name focusMovie
    * @param {function():*}
    * @example
    * workWithItems.focusMovie();
    * @return void
    */
    "focusMovie":function() {
        var c_o = current.obj;
        var n_o = parseInt(current.obj.toString()) + this.shift;
        var o1 = byID('' + c_o);
        var o2 = byID('' + n_o);
        var n_class = "active";
        if(isset(o2)) {
            if(isset(o1)) {
                o1.className = o1.className.replace( new RegExp('(^|\\s)' + n_class + '(\\s|$)') ,' ');
            }
            current.obj = n_o;
            o2.className += " " + n_class;
            log("page: " + current.page + ", obj: " + current.obj);
        } else {
            if(current.page == Math.ceil(request.totalItems/items.atPage)) {
                current.obj = current.globalObj.length - 1;
                o2 = byID('' + current.obj);
                o2.className += " " + n_class;
            }
        }
        loading.hide();
    }
},
settings = {
    /**
    * `quality_set` is method for change current quality
    *
    * @function
    * @name quality_set
    * @param {function():*}
    * @example
    * settings.quality_set();
    * @return void
    */
    "quality_set": function() {
        switch(current.priority) {
            case "low":
                current.priority = "middle";
                byID('quality').innerHTML = lang.quality.middle;
            break;
            case "middle":
                current.priority = "high";
                byID('quality').innerHTML = lang.quality.high;
            break;
            case "high":
                current.priority = "low";
                byID('quality').innerHTML = lang.quality.low;
            break;
        }
    },
    /**
    * `quality_show` is method for show started quality
    *
    * @function
    * @name quality_show
    * @param {function(value):*}
    * @example
    * settings.quality_show('middle');
    * @return void
    */
    "quality_show": function(value) {
        switch(value) {
            case "low":
            case "middle":
            case "high":
                current.priority = value;
                byID('quality').innerHTML = lang.quality[value];
            break;
            default:
                current.priority = 'middle';
                byID('quality').innerHTML = lang.quality['middle'];
            break;
        }
    },
    /**
    * `cats_show` is method for show cats menu
    *
    * @function
    * @name cats_show
    * @param {function():*}
    * @example
    * settings.cats_show();
    * @return void
    */
    "cats_show": function() {
        current.layer = layers.CATEGORY;
        categoryes.draw();
        byID('category').style.display = "block";
    },
    /**
    * `cats_start` is method for start query categorias
    *
    * @function
    * @name cats_start
    * @param {function():*}
    * @example
    * settings.cats_start();
    * @return void
    */
    "cats_start": function() {
        current.layer = layers.BASE;
        current.feed = current.cat.url;
        byID('category').style.display = "none";
        byID('search_block_inner').style.display = 'none';
        byID('cur_cat').innerHTML = byID('cat_1').innerHTML;
        loading.show();
        current.globalObj = new Array();
        request.startIndex = 1;  // set current startIndex
        request.totalItems = 0;  // set current totalItems
        current.obj = 0;
        current.page = 1;
        workWithItems.shift = current.obj;
        getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(),
            'rebuildCurrentGlobalObj');
    },
    "video_like":{
        "menuItem":0,
        "show":function(){
            current.layer = layers.VIDEO_LIKE;
            m.show('menu_video_like');

            settings.video_like.menuItem = 0;

            var lis = byID('menu_video_like').getElementsByTagName('li');
            for(var i = 0;i < lis.length;i++) {
                lis[i].className = (i==0) ? "active" : "";
            }

        },
        "hide":function() {
            current.layer = layers.BASE;
            m.hide('menu_video_like');
        },
        "suggestion":function(){

        },
        "handler":function(key){
            switch (key) {
                case keys.UP:
                case keys.DOWN:
                    var shift = 0;
                    if(key==keys.UP){
                        shift = -1;
                        if(settings.video_like.menuItem==0){settings.video_like.menuItem=v_like.length;}
                    }else{
                        shift = 1;
                    }
                    var lis = byID('menu_video_like').getElementsByTagName('li');
                    var act = settings.video_like.menuItem = Math.abs(settings.video_like.menuItem + shift) % v_like.length;
                    for(var i=0;i<lis.length;i++){
                        lis[i].className=(i==act)?"active":"";
                    }
                break;
                case keys.OK:
                    try {
                        switch (settings.video_like.menuItem) {
                            case 0:
                                current.feed = v_like[settings.video_like.menuItem].url_pre + current.globalObj[current.obj].id + v_like[settings.video_like.menuItem].url_post;
                            break;
                            case 1:
                            case 2:
                                current.feed = v_like[settings.video_like.menuItem].url_pre + current.globalObj[current.obj].uploader + v_like[settings.video_like.menuItem].url_post;
                            break;
                            case 3:
                                current.feed = v_like[settings.video_like.menuItem].url_pre + current.globalObj[current.obj].uploader + v_like[settings.video_like.menuItem].url_post;
                            break;
                        }
                        byID('cur_cat').innerHTML = lang.v_like[v_like[settings.video_like.menuItem].name];
                        byID('search_query_line').innerHTML = settings.video_like.menuItem==0 ? current.globalObj[current.obj].title : (lang.user + ' ' + current.globalObj[current.obj].uploader);
                        byID('search_block_inner').style.display = 'block';
                        loading.show();
                        current.globalObj = new Array();
                        request.startIndex = 1;  // set current startIndex
                        request.totalItems = 0;  // set current totalItems
                        current.obj = 0;
                        current.page = 1;
                        workWithItems.shift = 0;
                        settings.video_like.hide();
                        getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(),settings.video_like.menuItem==3?'search.playlistsResult':'rebuildCurrentGlobalObj');
                    }
                    catch(e){}
                break;
                case keys.GREEN:
                case keys.EXIT:
                    settings.video_like.hide();
                break;
            }
        }
    },
    /**
    * `cats_hide` is method for hide cats menu
    *
    * @function
    * @name cats_hide
    * @param {function():*}
    * @example
    * settings.cats_hide();
    * @return void
    */
    "cats_hide": function() {
        current.layer = layers.BASE;
        byID('category').style.display = "none";
    },
    /**
    * `changePlayMode` is method for change play mode
    *
    * @function
    * @name changePlayMode
    * @param {function():*}
    * @example
    * settings.changePlayMode();
    * @return void
    */
    "changePlayMode": function() {
        for(var i = 0; i < playModes.length; i++) {
            if(playModes[i].name == current.playMode) {
                if(playModes.length - 1 == i) {
                    current.playMode = playModes[0].name;
                    break;
                } else {
                    current.playMode = playModes[i + 1].name;
                    break;
                }
            }
        }
        log('current.playMode.new: ' + current.playMode);
        
        byID('playModeInPlayer').className = current.playMode;
        byID('mode').className = current.playMode;
    },
    "options":{
        "menuItems":["PlayModes","Quality"],
        "subMenuItems":[["single","repeat","list","random"],["low","middle","high"]],
        "currentLevel":-1,
        "menuItem":0,
        "subMenuItem":0,
        "show":function(){
            current.layer = layers.SETTINGS;
            byID('footer_menu_settings').style.display = "block";

            settings.options.menuItem = 0;
            settings.options.subMenuItem = 0;
            settings.options.currentLevel = 0;

            for(var i = 0;i<settings.options.menuItems.length;i++) {
                byID("menu" + settings.options.menuItems[i]).className = "";
            }
            byID("menu" + settings.options.menuItems[settings.options.menuItem]).className = "active";
        },
        "hide":function() {
            current.layer = layers.BASE;
            byID('footer_menu_settings').style.display = "none";
        },
        "handler":function(key) {
            switch(key){
                case keys.UP:
                case keys.DOWN:
                    var shift = 0;
                    if(key==keys.UP){
                        shift = -1;
                    } else {
                        shift = 1;
                    }
                    switch(settings.options.currentLevel) {
                        case 0:
                            for(var i = 0;i<settings.options.menuItems.length;i++) {
                                byID("menu" + settings.options.menuItems[i]).className = "";
                            }
                            settings.options.menuItem = Math.abs(settings.options.menuItem + shift) % settings.options.menuItems.length;
                            byID("menu" + settings.options.menuItems[settings.options.menuItem]).className = "active";
                        break;
                        case 1:
                            for(var i = 0; i < settings.options.subMenuItems[settings.options.menuItem].length; i++) {
                                byID("menuPM" + settings.options.subMenuItems[settings.options.menuItem][i]).className = "";
                            }
                            settings.options.subMenuItem = (settings.options.subMenuItem + shift) % settings.options.subMenuItems[settings.options.menuItem].length;
                            settings.options.subMenuItem = settings.options.subMenuItem >=0 ? settings.options.subMenuItem : settings.options.subMenuItems[settings.options.menuItem].length - 1;
                            byID("menuPM" + settings.options.subMenuItems[settings.options.menuItem][settings.options.subMenuItem]).className = "active";
                        break;
                    }
                break;
                case keys.LEFT:
                    if(settings.options.currentLevel==0) {
                        settings.options.handler(keys.OK);
                    }
                break;
                case keys.RIGHT:
                    if(settings.options.currentLevel==1) {
                        settings.options.handler(keys.BACK);
                    }
                break;
                case keys.OK:
                    switch(settings.options.currentLevel){
                        case 0:
                            byID("subMenu" + settings.options.menuItems[settings.options.menuItem]).style.display = "block";
                            for(var i = 0; i<settings.options.subMenuItems[settings.options.menuItem].length; i++) {
                                byID("menuPM" + settings.options.subMenuItems[settings.options.menuItem][i]).className = "";
                            }
                            switch(settings.options.menuItem) {
                                case 0:
                                    for(var i = 0; i < settings.options.subMenuItems[settings.options.menuItem].length; i++) {
                                        if(settings.options.subMenuItems[settings.options.menuItem][i] == current.playMode) {
                                           settings.options.subMenuItem = i;
                                        }
                                    }
                                    byID("menuPM" + current.playMode).className = "active";
                                break;
                                case 1:
                                    for(var i = 0; i < settings.options.subMenuItems[settings.options.menuItem].length; i++) {
                                        if(settings.options.subMenuItems[settings.options.menuItem][i] == current.priority) {
                                           settings.options.subMenuItem = i;
                                        }
                                    }
                                    byID("menuPM" + current.priority).className = "active";
                                break;
                            }
                            settings.options.currentLevel = 1;
                        break;
                        case 1:
                            var id = document.getElementById("menu" + settings.options.menuItems[settings.options.menuItem]).getElementsByClassName('active')[0].id;
                            switch(settings.options.menuItem) {
                                case 0:
                                    current.playMode = id.substr(6);
                                    byID('mode').className = current.playMode;
                                    settings.options.hide();
                                break;
                                case 1:
                                    settings.quality_show(id.substr(6));
                                    settings.options.hide();
                                break;
                            }
                            var sm = document.getElementById("footer_menu_settings").getElementsByClassName('submenu');
                            for(var i=0;i<sm.length;i++) {
                                sm[i].style.display = "none";
                            }
                        break;
                    }
                break;
                case keys.BLUE:
                case keys.BACK:
                case keys.EXIT:
                    switch(settings.options.currentLevel){
                        case -1:
                        case 0:
                            settings.options.hide();
                        break;
                        case 1:
                            byID("subMenu" + settings.options.menuItems[settings.options.menuItem]).style.display = "none";
                            settings.options.currentLevel = 0;
                        break;
                    }
                break;
            }
        }
    }
},
loading = {
    "show":function(block){
        if(!block) {
            current.buttonsStatus = false;
        }
        byID('loading').style.display = "block";
        current.loading = true;
    },
    "hide":function() {
        current.buttonsStatus = true;
        byID('loading').style.display = "none";
        current.loading = false;
    }
},

toast = {
    "show" : function(text){
        byID('toast').innerHTML = text;
        byID('toast_block').style.display = "block";
        this.t_hide()
    },
    "t_hide" : function(){
        var self = this;
        window.clearTimeout(this.hide_timeout);
        this.hide_timeout = window.setTimeout(function(){self.hide()}, 2000);
    },
    "hide" : function(){
        byID('toast_block').style.display = "none";
    }
},

categoryes = {
    "shift":0,
    "draw":function() {
        for(var i = 0; i < current.catItems; i++) {
            var index = this.getOne(i + this.shift + current.cat.trying);
            byID('cat_' + i).innerHTML = lang.cats[categorias[index].name];
            if(i==1) {
                current.cat.url = categorias[index].url;
            }
        }
        current.cat.trying += this.shift;
    },
    "getOne":function(cur) {
        return cur % categorias.length >= 0 ? cur % categorias.length : categorias.length + cur % categorias.length;
    }
},
search = {
    "as_cat":0,
    "as_time":0,
    "as_orderby":0,
    "as_data":0,
    "focus":"",
    "show":function(){
        m.show('yt_search_bg');
        current.layer = layers.SEARCH;
        if(current.searchItem==null) {
            current.searchItem = 0;
        }
        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
        search.focus = 'as_query_search';
        byID('as_query_search').focus();

        if(m.html('as_cat')==""){
            search.as_cat = 0;
            m.html('as_cat',lang.cats[adv_search.categorys[search.as_cat].name]);
        }
        if(m.html('as_time')==""){
            search.as_time = 0;
            m.html('as_time',lang.adv_search.time[adv_search.time[search.as_time].name]);
        }
        if(m.html('as_orderby')==""){
            search.as_orderby = 0;
            m.html('as_orderby',lang.adv_search.orderBy[adv_search.orderBy[search.as_orderby].name]);
        }
        if(m.html('as_data')==""){
            search.as_data = 0;
            m.html('as_data',lang.adv_search.userType[adv_search.userType[search.as_data].name]);
        }
        byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByTagName('input')[0].focus();
        stb.HideVirtualKeyboard();
    },
    "hide":function(){
        m.hide('yt_search_bg');
        current.layer = layers.BASE;
    },
    "focusMove":function(direction){
        switch(direction){
            case "up":
                switch(current.searchItem) {
                    case 0:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 2;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                    case 1:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 0;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                    case 2:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 1;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                }
            break;
            case "down":
                switch(current.searchItem) {
                    case 0:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 1;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                    case 1:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 2;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                    case 2:
                        m.show(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                        current.searchItem = 0;
                        m.hide(byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByClassName('yt_searchbox0')[0]);
                    break;
                }
            break;
        }
        byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByTagName('input')[0].focus();
        stb.HideVirtualKeyboard();
    },
    "pressOK":function(){
        switch(current.searchItem) {
            case 0:
                search.focus = 'as_query_search';
                byID('as_query_search').focus();
            break;
            case 1:
                search.focus = 'as_query_users_video';
                byID('as_query_users_video').focus();
            break;
            case 2:
                search.focus = 'as_query_playlists_search';
                byID('as_query_playlists_search').focus();
            break;
        }
        if(m.val(search.focus)!=""){
            search.start(current.searchItem, m.val(search.focus));
        } else {
            stb.ShowVirtualKeyboard();
        }
        //as_query_search
        //as_query_users_video
        //as_query_playlists_search
    },
    "pressEXIT":function(){
        search.hide();
    },
    "start":function(type, query){
        stb.HideVirtualKeyboard();
        byID('search_block_inner').style.display = 'block';
        switch(type) {
            case 0: // advanced search
                loading.show();
                search.hide();
                current.globalObj = new Array();
                current.feed = google.searchFeedUrl + '&q=' + encodeURIComponent(query) + adv_search.categorys[search.as_cat].url + adv_search.time[search.as_time].url + adv_search.orderBy[search.as_orderby].url;
                
                request.startIndex = 1;  // set current startIndex
                request.totalItems = 0;  // set current totalItems
                current.obj = 0;
                current.page = 1;
                workWithItems.shift = current.obj;

                byID('search_query_line').innerHTML = query;
                m.html('cur_cat', lang.cats[adv_search.categorys[current.as_cat.id].name]);

                m.html('as_query_users_video', '');
                m.html('as_query_playlists_search', '');

                getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(),'rebuildCurrentGlobalObj');
            break;
            case 1: // user's data
                loading.show();
                search.hide();
                current.globalObj = new Array();
                current.feed = adv_search.userType[search.as_data].url_pre + encodeURIComponent(query) + adv_search.userType[search.as_data].url_post;

                request.startIndex = 1;  // set current startIndex
                request.totalItems = 0;  // set current totalItems
                current.obj = 0;
                current.page = 1;
                workWithItems.shift = current.obj;

                byID('search_query_line').innerHTML = query;
                m.html('cur_cat', lang.cats[adv_search.categorys[0].name]);
                getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(),search.as_data==2?'search.playlistsResult':'rebuildCurrentGlobalObj');
            break;
            case 2:
                log('* * * * * * * * *\nplaylists, query:"' + query + '"\n\n* * * * * * * * *');
                // playlists

                loading.show();
                search.hide();
                current.globalObj = new Array();
                current.feed = google.playlists_search + "&q=" + encodeURIComponent(query);

                request.startIndex = 1;  // set current startIndex
                request.totalItems = 0;  // set current totalItems
                current.obj = 0;
                current.page = 1;
                workWithItems.shift = current.obj;

                byID('search_query_line').innerHTML = query;
                m.html('cur_cat', lang.cats[adv_search.categorys[0].name]);
                getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(), 'search.playlistsResult');
            break;
        }
    },
    "plTimer":null,
    "plTimeCounter":null,
    "plTimeCounterStep":250,
    "plTimeCounterLimit":5000,
    "playlistsResult":function(list){
        current.isPL = true;
        var tmp = empty(current.globalObj) ? new Array(0) : current.globalObj;
        var tmp_list = new Array();

        for(var i=0; i < list.data.items.length + tmp.length; i++) {
            if(i < tmp.length) {
                tmp_list[i] = tmp[i];
            } else {
                tmp_list[i] = {
                    "id":list.data.items[i - tmp.length].id,
                    "author":list.data.items[i - tmp.length].author,
                    "title":list.data.items[i - tmp.length].title,
                    "size":list.data.items[i - tmp.length].size,
                    "cover":"img/pls_def.png",
                    "category":"",
                    "feed":"http://gdata.youtube.com/feeds/api/playlists/" + list.data.items[i - tmp.length].id + "?v=2&alt=jsonc&start-index=1&max-results=5",
                    "img": new Image()
                };
                getData(tmp_list[i].feed, 'search.playlistCoverResult');
            }
        }

        current.globalObj = tmp_list; // set value of current.globalObj

        request.startIndex = list.data.startIndex;  // set current startIndex
        request.totalItems = list.data.totalItems;  // set current totalItems

        search.plTimer = setInterval(
            function(){
                if(search.plTimeCounter < search.plTimeCounterLimit) {
                    search.plTimeCounter += search.plTimeCounterStep;
                    log("search.plTimer");
                    for(var i = 0; i < current.globalObj.length; i++) {
                        if(current.globalObj[i].cover=="img/pls_def.png"){
                            return;
                        }
                    }
                }
                clearInterval(search.plTimer);
                search.plTimer = null;
                setTimeout('search.drawBoxesForPlaylists()', 500);
            }, search.plTimeCounterStep);
    },
    "playlistCoverResult":function(list){
        for(var i = request.startIndex - 1; i < current.globalObj.length; i++){
            if(current.globalObj[i].id == list.data.id){
                current.globalObj[i].cover = 'img/pls.png';
                current.globalObj[i].img.src = 'img/pls.png';
                current.globalObj[i].img.onload = function(){};
                try{
                    for(var j=0;j<list.data.items.length;j++){
                        try{
                            current.globalObj[i].category = list.data.items[j].video.category;
                            current.globalObj[i].cover = list.data.items[j].video.thumbnail.sqDefault;
                            current.globalObj[i].img.src = list.data.items[j].video.thumbnail.sqDefault;
                            current.globalObj[i].img.onload = function(){};
                            return;
                        }catch(e){}
                    }
                }catch(e){}
                return;
            }
        }
    },
    "drawBoxesForPlaylists":function() {
            byID("frame").innerHTML = '';   // clean parent bopx
            byID("arrow_left").style.display = ((current.page - 1) < 1) ? "none" : "block"; // set visibility of arrow to up
            log('from ' + ((current.page - 1) * items.atPage).toString() + ', to ' + (current.page * items.atPage).toString() + ', current.globalObj.length' + current.globalObj.length);
            for(var i=(current.page - 1) * items.atPage; i <  current.page * items.atPage; i++) {
                //while(!current.globalObj[i].img.complete){}
                if(!empty(current.globalObj[i])) {
                    log("current.globalObj[" + i + "].cover: " + current.globalObj[i].cover + "\ncurrent.globalObj[" + i + "].id: " + current.globalObj[i].id);
                    byID("frame").innerHTML += '' +
                        '<div class="box" id="' + i + '" title="' + current.globalObj[i].id + '">' +
                        '   <div class="inner">' +
                        '       <div class="img_cover">' +
                        '           <img src="' + current.globalObj[i].cover + '" class="prew" alt="" />' +
                        '       </div>' + "\n" +
                        '       <p class="title pl" style="">' + current.globalObj[i].title + '</p>' + //&hellip;((current.globalObj[i].title.length<=27)? current.globalObj[i].title : current.globalObj[i].title.substr(0, 27) + '…') + '</p>' +  "\n" +//&hellip;
                        '       <p>&nbsp;' + current.globalObj[i].category + '</p>' +
                        '   </div>' +
                        '</div>';
                }
            }
            byID("arrow_right").style.display = (current.page == Math.ceil(request.totalItems/items.atPage)) ? "none" : "block"; // set visibility of arrow to down
            workWithItems.focusMovie();
    },
    "colorButtonPress":function(color){ // (string)red|green|yellow
        switch(current.searchItem) {
            case 0:
                switch(color) {
                    case "red":
                        search.categoryes.show();
                    break;
                    case "green":
                        search.as_time = (search.as_time + 1) % adv_search.time.length;
                        m.html('as_time',lang.adv_search.time[adv_search.time[search.as_time].name]);
                    break;
                    case "yellow":
                        search.as_orderby = (search.as_orderby + 1) % adv_search.orderBy.length;
                        m.html('as_orderby',lang.adv_search.orderBy[adv_search.orderBy[search.as_orderby].name]);
                    break;
                }
            break;
            case 1:
                switch(color) {
                    case "red":
                        search.as_data = (search.as_data + 1) % adv_search.userType.length;
                        m.html('as_data',lang.adv_search.userType[adv_search.userType[search.as_data].name]);
                    break;
                }
            break;
            case 2:break;
        }
    },
    "categoryes":{
        "shift":0,
        "draw":function() {
            log("search.categoryes.draw");
            for(var i = 0; i < current.catItems; i++) {
                var index = search.categoryes.getOne(i + search.categoryes.shift + current.as_cat.trying);
                m.html('as_cat_' + i, lang.cats[adv_search.categorys[index].name]);
                if(i==1) {
                    current.as_cat.id = index;
                }
            }
            current.as_cat.trying += search.categoryes.shift;
        },
        "getOne":function(cur) {
            return cur % adv_search.categorys.length >= 0 ? cur % adv_search.categorys.length : adv_search.categorys.length + cur % adv_search.categorys.length;
        },
        "show":function(){
            document.getElementsByClassName('combo_drop')[0].style.display = 'block';
            current.layer = layers.AS_CATEGORY;
            byID('mode').focus();
            current.as_cat.trying = adv_search.categorys.length - 1;
            search.categoryes.draw();
        },
        "hide":function(){
            document.getElementsByClassName('combo_drop')[0].style.display = 'none';
            current.layer = layers.SEARCH;
            byID('yt_searchbox' + (current.searchItem + 1).toString()).getElementsByTagName('input')[0].focus();
        },
        "select":function(){
            search.as_cat = current.as_cat.id;
            m.html('as_cat', lang.cats[adv_search.categorys[current.as_cat.id].name]);
            search.categoryes.hide();
        }
    }
};

var m = {
    "show":function(elem){
        if(typeof elem == 'string'){
            byID(elem).style.display = "block";
        }
        if(typeof elem == 'object'){
            elem.style.display = "block";
        }
    },
    "hide":function (elem){
        if(typeof elem == 'string'){
            byID(elem).style.display = "none";
        }
        if(typeof elem == 'object'){
            elem.style.display = "none";
        }
    },
    "html":function(elem, text){
        if(typeof elem == 'string'){
            elem = byID(elem);
        }
        if(!text){
            return elem.innerHTML;
        } else {
            elem.innerHTML = text;
        }
    },
    "val":function(elem, text){
        if(typeof elem == 'string'){
            elem = byID(elem);
        }
        if(!text){
            return elem.value;
        } else {
            elem.value = text;
        }
    }
};