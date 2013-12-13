/**
 * `keyProcessing` is function for catching keys down events and call functions appropriate each event
 *
 * @function
 * @name keyProcessing
 * @param {function(event):*}
 * @example
 * keyProcessing(some.event);
 * @return void
 */
function keyProcessing(e) {
    
    var code = e.keyCode || e.which;
    if (stb && stb.key_lock === true && code != key.FRAME) {return;}
    if (e.shiftKey) {code += 1000;}
    if (e.altKey) {code += 2000;}
    
    if(!current.buttonsStatus) {
        if(code == keys.EXIT) {
            player.stop();
            loading.hide();
        }
        return;
    }
    switch(current.layer) {
        case layers.BASE: // main layer
            switch(code) {
                case keys.EXIT:
                    if(current.loading == true) {
                        player.stop();
                        loading.hide();
                    }
                    if(document.referrer.length > 4) {
                        window.location = document.referrer;
                    } else {
                        window.location = pages.back;
                    }
                    return;
                break;
                case keys.LEFT:
                    if(current.obj - (current.page - 1) * items.atPage == 0) {
                        if(current.obj!=0) {
                            current.page--;
                            workWithItems.shift = -1;
                            if(current.isPL==true){
                                search.drawBoxesForPlaylists();
                            } else {
                                workWithItems.drawBoxes();
                            }
                        }
                    } else {
                        workWithItems.shift = -1;
                        workWithItems.focusMovie();
                    }
                break;
                case keys.UP:
                    if(current.obj - (current.page - 1) * items.atPage >= items.atLine) {
                        workWithItems.shift = -items.atLine;
                        workWithItems.focusMovie();
                    } else {
                        if(current.obj - (current.page - 1) * items.atPage < items.atLine && current.obj>items.atLine) {
                            current.page--;
                            workWithItems.shift = -items.atLine;
                            if(current.isPL==true){
                                search.drawBoxesForPlaylists();
                            } else {
                                workWithItems.drawBoxes();
                            }
                        }
                    }
                break;
                case keys.RIGHT:
                    if(current.obj + 1 == current.page * items.atPage) {
                        if(current.obj + 1 == current.globalObj.length &&
                           !(request.totalItems == current.globalObj.length - 1)) {
                            log("event to upload next");
                            current.page++;
                            workWithItems.shift = 1;
                            loading.show();
                            getData(current.feed+'&start-index='+current.globalObj.length.toString()+'&max-results='+request.itemsPerRequest.toString(), current.isPL==true ? 'search.playlistsResult' : 'rebuildCurrentGlobalObj');
                        } else {
                            if((current.globalObj.length%36)!=0 && current.obj+1>=current.globalObj.length){return;}
                            current.page++;
                            workWithItems.shift = 1;
                            if(current.isPL==true){
                                search.drawBoxesForPlaylists();
                            } else { 
                                workWithItems.drawBoxes();
                            }

                        }
                    } else {
                        workWithItems.shift = 1;
                        workWithItems.focusMovie();
                    }
                break;
                case keys.DOWN:
                    log('data: '+current.globalObj.length+' : '+request.totalItems)
                    if((current.obj - (current.page - 1) * items.atPage < items.atLine) || (request.totalItems < items.atPage) ) {
                        if(current.obj+items.atLine<current.globalObj.length){
                            workWithItems.shift = items.atLine;
                            workWithItems.focusMovie();
                        }
                    } else {
                        if(current.globalObj.length >= (current.page + 1) * items.atPage ||(request.totalItems == current.globalObj.length - 1)) {
                            if((current.globalObj.length%36)!=0 && (current.obj-(current.obj%items.atPage)+items.atPage)+1>=current.globalObj.length){return;}
                                current.page++;
                                workWithItems.shift = items.atLine;
                                if(current.isPL==true){
                                    search.drawBoxesForPlaylists();
                                } else {
                                    workWithItems.drawBoxes();
                                }
                            
                        } else {
                            if(request.totalItems == current.globalObj.length - 1) {return;}
                            log("event to upload next");
                            current.page++;
                            workWithItems.shift = items.atLine;
                            loading.show();
                            getData(current.feed+'&start-index='+current.globalObj.length.toString()+'&max-results='+request.itemsPerRequest.toString(), current.isPL==true ? 'search.playlistsResult' : 'rebuildCurrentGlobalObj');
                        }
                    }
                break;
                case keys.PAGE_NEXT:
                    if((current.page + 1)*items.atPage <=current.globalObj.length || (request.totalItems == current.globalObj.length - 1)) {
                        if(current.page == Math.ceil(request.totalItems/items.atPage)) return;
                        current.page++;
                        workWithItems.shift = items.atPage;
                        if(current.isPL==true){
                            search.drawBoxesForPlaylists();
                        } else {
                            workWithItems.drawBoxes();
                        }
                    } else {
                        log("event to upload next");
                        current.page++;
                        workWithItems.shift = items.atPage;
                        loading.show();
                        getData(current.feed+'&start-index='+current.globalObj.length.toString()+'&max-results='+request.itemsPerRequest.toString(), current.isPL==true ? 'search.playlistsResult' : 'rebuildCurrentGlobalObj');
                    }
                break;
                case keys.PAGE_PREV:
                    if(current.page>1) {
                        current.page--;
                        workWithItems.shift = -items.atPage;
                        if(current.isPL==true){
                            search.drawBoxesForPlaylists();
                        } else {
                            workWithItems.drawBoxes();
                        }
                    }
                break;
                case keys.OK:
                    loading.show();
                    var id = byID('frame').getElementsByClassName('active')[0].title;
                    if(current.isPL==true){
                        for(var i in current.globalObj){
                            if(current.globalObj[i].id == id) {
                                byID('search_query_line').innerHTML = current.globalObj[i].title;
                                break;
                            }
                        }

                        current.globalObj = null;
                        current.globalObj = new Array();
                        current.feed = google.playlists_show_pre+id+google.playlists_show_post;

                        request.startIndex = 1;  // set current startIndex
                        request.totalItems = 0;  // set current totalItems
                        current.obj = 0;
                        current.page = 1;
                        workWithItems.shift = current.obj;
                        m.html('cur_cat', lang.cats['playlist']);
                        getData(current.feed + '&start-index=' + request.startIndex.toString() + '&max-results=' + request.itemsPerRequest.toString(), 'rebuildCurrentGlobalObj');
                    } else {
                        for(var j in current.globalObj){
                            if(current.globalObj[j].id == id) {
                                log('Found id: ' + id);
                                player.playingPreparation(current.globalObj[j]); //play move
                                break;
                            }
                        }
                    }
                break;
                case keys.RED:
                //case keys.NUM1:
                    settings.cats_show();
                break;
                case keys.GREEN:
                //case keys.NUM2:
                    if(current.isPL!=true){settings.video_like.show();}
                break;
                case keys.YELLOW:
                //case keys.NUM3:
                    search.show();
                break;
                case keys.BLUE:
                //case keys.NUM4:
                    settings.options.show();
                break;
				case keys.VOL_DOWN:
					player.pressVolume(-1);
					break;
				case keys.VOL_UP:
					player.pressVolume(1);
					break;
            }
        break;
        case layers.PLAYER: // playing layer
            switch(code) {
                case keys.EXIT:
                case keys.STOP: // stop
                    player.stop();
                    byID('shell').style.display = 'block';
                break;
                case keys.PAUSE: // play/pause
                    if(player.condition==0){player.condition=1;} else {player.condition=0;}
                    player.playOrPause();
                break;
                case keys.REW:
                case keys.LEFT:  // move interval -step
                    player.rewind(-1);
                break;
                case keys.FFWD:
                case keys.RIGHT: // move interval +step
                    player.rewind(1);
                break;
                case keys.UP:    // interval enlarge
                    player.changeStep("up");
                break;
                case keys.DOWN: // interval reduce
                    player.changeStep("down");
                break;
                case keys.CHANNEL_NEXT:
                    var id;
                    player.stop_pre_play();
                    if(current.obj + 1 == current.page * items.atPage) {
                        if(current.obj + 1 == current.globalObj.length &&
                           !(request.totalItems == current.globalObj.length - 1)) {
                            log("event to upload next");
                            current.page = 0;
                            workWithItems.shift = 0;
                            workWithItems.drawBoxes();
                            loading.show(true);

                            id = byID('frame').getElementsByClassName('active')[0].title;
                            for(var i in current.globalObj) {
                                if(current.globalObj[i].id == id) {
                                    log('Set action id : ' + id);
                                    player.playingPreparation(current.globalObj[i]); //play move
                                    break;
                                }
                            }
                        } else {
                            current.page++;
                            workWithItems.shift = 1;
                            workWithItems.drawBoxes();
                            loading.show(true);

                            setTimeout(
                                function() {
                                    var id = byID('frame').getElementsByClassName('active')[0].title;
                                    for(var i in current.globalObj)
                                    {
                                        if(current.globalObj[i].id == id)
                                        {
                                            log('Set action id : ' + id);
                                            player.playingPreparation(current.globalObj[i]); //play move
                                            break;
                                        }
                                    }
                                },
                                350
                            );
                        }
                    } else {
                        workWithItems.shift = 1;
                        workWithItems.focusMovie();
                        loading.show(true);

                        id = byID('frame').getElementsByClassName('active')[0].title;
                        for(var i in current.globalObj) {
                            if(current.globalObj[i].id == id) {
                                log('Set action id : ' + id);
                                player.playingPreparation(current.globalObj[i]); //play move
                                break;
                            }
                        }
                    }
                break;
                case keys.CHANNEL_PREV:
                    var id;
                    player.stop_pre_play();
                    if(current.obj - (current.page - 1) * items.atPage == 0) {
                        if(current.obj!=0) {
                            current.page--;
                            workWithItems.shift = -1;
                            workWithItems.drawBoxes();
                            loading.show(true);

                            setTimeout(
                                function() {
                                    var id = byID('frame').getElementsByClassName('active')[0].title;
                                    for(var i in current.globalObj)
                                    {
                                        if(current.globalObj[i].id == id)
                                        {
                                            log('Set action id : ' + id);
                                            player.playingPreparation(current.globalObj[i]); //play move
                                            break;
                                        }
                                    }
                                },
                                350
                            );
                        }
                    } else {
                        workWithItems.shift = -1;
                        workWithItems.focusMovie();
                        loading.show(true);

                        id = byID('frame').getElementsByClassName('active')[0].title;
                        for(var i in current.globalObj) {
                            if(current.globalObj[i].id == id) {
                                log('Set action id : ' + id);
                                player.playingPreparation(current.globalObj[i]); //play move
                                break;
                            }
                        }
                    }
                break;
                case keys.NUM0:
                case keys.NUM1:
                case keys.NUM2:
                case keys.NUM3:
                case keys.NUM4:
                case keys.NUM5:
                case keys.NUM6:
                case keys.NUM7:
                case keys.NUM8:
                case keys.NUM9:
                case keys.BACK:
                    var num;
                    switch (code) {
                        case keys.NUM0:num = 0;break;
                        case keys.NUM1:num = 1;break;
                        case keys.NUM2:num = 2;break;
                        case keys.NUM3:num = 3;break;
                        case keys.NUM4:num = 4;break;
                        case keys.NUM5:num = 5;break;
                        case keys.NUM6:num = 6;break;
                        case keys.NUM7:num = 7;break;
                        case keys.NUM8:num = 8;break;
                        case keys.NUM9:num = 9;break;
                        case keys.BACK:num = -1;break;
                    }
                    player.pressNums(num);
                break;
                case keys.OK:
                    player.pressOK();
                break;
                case keys.VOL_DOWN:
                    player.pressVolume(-1);
                break;
                case keys.VOL_UP:
                    player.pressVolume(1);
                break;
                case keys.MUTE:
                    player.pressMute();
                break;
                case keys.YELLOW:
                case keys.FRAME:
                    player.displayModeChange();
                break;
                case keys.BLUE:
                    settings.changePlayMode();
                break;
            }
        break;
        case layers.SEARCH: // search layer
            switch(code) {
                case keys.EXIT:
                    search.pressEXIT();
                break;
                case keys.UP:
                    search.focusMove("up");
                break;
                case keys.DOWN:
                    search.focusMove("down");
                break;
                case keys.OK:
                    search.pressOK();
                break;
                case keys.APP:
                    //search.pressVKB();
                break;
                case keys.RED:
                case keys.GREEN:
                case keys.YELLOW:
                    var color = code == keys.RED ? "red": code == keys.GREEN ? "green": "yellow";
                    search.colorButtonPress(color);
                break;
            }
        break;
        case layers.CATEGORY:
            switch(code) {
                case keys.UP:
                    categoryes.shift = -1;
                    categoryes.draw();
                break;
                case keys.DOWN:
                    categoryes.shift = 1;
                    categoryes.draw();
                break;
                case keys.PAGE_NEXT:
                    categoryes.shift = current.catItems;
                    categoryes.draw();
                break;
                case keys.PAGE_PREV:
                    categoryes.shift = -current.catItems;
                    categoryes.draw();
                break;
                case keys.BACK:
                case keys.EXIT:
                    settings.cats_hide();
                break;
                case keys.OK: // start search
                    settings.cats_start();
                break;
            }
        break;
        case layers.AS_CATEGORY:
            switch(code) {
                case keys.UP:
                    search.categoryes.shift = -1;
                    search.categoryes.draw();
                break;
                case keys.DOWN:
                    search.categoryes.shift = 1;
                    search.categoryes.draw();
                break;
                case keys.PAGE_NEXT:
                    search.categoryes.shift = current.catItems;
                    search.categoryes.draw();
                break;
                case keys.PAGE_PREV:
                    search.categoryes.shift = -current.catItems;
                    search.categoryes.draw();
                break;
                case keys.BACK:
                case keys.EXIT:
                    search.categoryes.hide();
                break;
                case keys.OK:
                    search.categoryes.select();
                break;
            }
        break;
        case layers.SETTINGS:
            settings.options.handler(code);
        break;
        case layers.VIDEO_LIKE:
            settings.video_like.handler(code);
        break;
    }
    if(current.mode.show_all_keydowns){
        log("key-code: " + code.toString() + ", " + "cur-lay: " + current.layer.toString() + ", " + "cur-obj:" + current.obj.toString() + ", " + "cur-page:" + current.page.toString() );
    }
}