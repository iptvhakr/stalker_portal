
app.classes={
    "list":{
        "act_row_id":"",
        "pages_count":0,
        "items_count":0,
        "items":{},
        "cur_page":0,
        "cur_exPage":0,
        "cur_exUrl":'',
        "cur_item":0,
        "cur_mode":"",
        "history":[],
        "deep":-1,
        "ctrls":{
            "show":function(mode,arr,before){
                //document.cookie="uper=100;";
                //uper = 100
                if(!before){					
                    app.classes.list.history.push(
                    {
                        "act_row_id":app.classes.list.act_row_id,
                        "pages_count":app.classes.list.pages_count,
                        "items_count":app.classes.list.items_count,
                        "items":app.classes.list.items,
                        "cur_page":app.classes.list.cur_page,
                        "cur_item":app.classes.list.cur_item,
                        "cur_mode":app.classes.list.cur_mode
                    }
                    );
                } else {
					
                }
                if(before && before==true && app.classes.list.deep>=0) {
                    this.hide(
                        function(){
                            app.classes.list.act_row_id = 	app.classes.list.history[app.classes.list.deep].act_row_id;
                            app.classes.list.pages_count = 	app.classes.list.history[app.classes.list.deep].pages_count;
                            app.classes.list.items_count = 	app.classes.list.history[app.classes.list.deep].items_count;
                            //app.classes.list.items = 		app.classes.list.history[app.classes.list.deep].items;
                            app.classes.list.cur_page = 	app.classes.list.history[app.classes.list.deep].cur_page;
                            app.classes.list.cur_item = 	app.classes.list.history[app.classes.list.deep].cur_item;
                            if(as.sublist){
                            app.classes.list.cur_mode = 	app.classes.list.history[app.classes.list.deep].cur_mode;as.sublist=false;}
                            app.classes.list.act_row_id = 'act_item_list_' + app.classes.list.history[app.classes.list.deep].cur_mode;
                            app.classes.list.views.init(app.classes.list.history[app.classes.list.deep].cur_mode, app.classes.list.history[app.classes.list.deep].items, app.classes.list.act_row_id);
                            app.classes.list.deep--;
                            app.classes.list.history.pop();
                        }
                        );
                } else {
                    this.hide(
                        function(){
                            app.classes.list.deep++;
                            app.classes.list.act_row_id = 'act_item_list_' + mode;
                            app.classes.list.views.init(mode, arr, app.classes.list.act_row_id);
                        }
                        );
                }
            },
            "hide":function(callback){
                var divs = $(as.list).getElementsByTagName('div');
                for(var i = divs.length-1;i>=0;i--){
                    try{
                        $(as.list).removeChild(divs[i]);
                    }catch(e){}
                }

            app.classes.list.act_row_id='';
            app.classes.list.pages_count=0;
            app.classes.list.items_count=0;
            app.classes.list.items={};
            app.classes.list.cur_page=0;
            app.classes.list.cur_item=0;
            app.classes.list.cur_mode='';
            if(callback){
                callback();
            }
        },
        "getCurItemInfo":function(){
            var ret = {};
            ret.url= $(as.list).getElementsByClassName('line')[app.classes.list.cur_item].getAttribute('rel');
            ret.name= $(as.list).getElementsByClassName('line')[app.classes.list.cur_item].html();
            ret.cat= $(as.list).getElementsByClassName('line')[app.classes.list.cur_item].getAttribute('cat');
            if(ret.cat == 'false' && branch == 'audio'){
                ret.cat = 'music';
            }
            log('getCurItemInfo: url: '+ret.url+' name: '+ret.name+' category: '+ret.cat);
            return ret;				
        },
        "move":function(val){
            switch (val) {
                case "begin":
                    app.classes.list.cur_item = 0;
                break;
                case "end":
                    app.classes.list.cur_item = as.sizes[as.actualSize].max_list_lines - 1;
                break;
                case "end_r":
                    app.classes.list.cur_item = Math.min((as.sizes[as.actualSize].max_list_lines - 1),(app.classes.list.items_count-app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines-1));
                break;
                case "up":
                    if(app.classes.list.cur_item>0){
                        app.classes.list.cur_item--;
                    } else {
                        if(app.classes.list.cur_item<=0 && app.classes.list.cur_page > 0) {
                            app.classes.list.cur_item=0;
                            app.classes.list.cur_page--;
                            app.classes.list.views.fill[app.classes.list.cur_mode+'Fill'](app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines,'end');
                            log('app.classes.list.cur_page--');
                        }else{
                            if(app.classes.list.cur_exPage>0){
                                if(as.layer==as.layers.SUB_CATS_LIST){
                                app.classes.list.cur_item = as.sizes[as.actualSize].max_list_lines - 1;
                                app.classes.list.cur_exPage--;
                                switch(branch){
                                    case "video":
                                            app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                    break;
                                    case "audio":
                                            app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                    break;
                                    case "images":
                                            app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                    break;
                                    case "search":
                                        app.ctrls.getContent(active_suggest != -1?as.feeds['search']+ $('sug_'+active_suggest).innerHTML:as.feeds['search']+ encodeURI($('search_input').value+'&p='+app.classes.list.cur_exPage), app.ctrls.getSubCatListFromHTML);
                                    break;
                                }
                            if(!as.sublist)as.subcount==0?as.subcount:as.subcount--;
                            else app.classes.list.deep++;
                                window.setTimeout(function(){
                                    app.classes.list.cur_page =as.actualSize==480?14:9;
                                    app.classes.list.views.fill[app.classes.list.cur_mode+'Fill'](app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines,'end_r');
                                },1000);
                                }
                                else {app.classes.list.ctrls.move("begin", true);}
                            }
                            else {app.classes.list.ctrls.move("begin", true);}
                        }
                    }
                break;
                case "down":
                     if(parseInt((app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines + app.classes.list.cur_item + 1),10)>= parseInt(app.classes.list.items_count,10)&&as.layer==as.layers.SUB_CATS_LIST) {
                            
                            //--------------------------------------
                            app.classes.list.cur_exPage++;
                            app.classes.list.cur_item=0;
                            switch(branch){
                                case "video":
                                        app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                break;
                                case "audio":
                                        app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                break;
                                case "images":
                                        app.ctrls.getContent(app.classes.list.cur_exUrl+'&p='+app.classes.list.cur_exPage, app.ctrls.getSubCatListFromHTML,false);
                                break;
                                case "search":
                                    app.ctrls.getContent(active_suggest != -1?as.feeds['search']+ $('sug_'+active_suggest).innerHTML:as.feeds['search']+ encodeURI($('search_input').value+'&p='+app.classes.list.cur_exPage), app.ctrls.getSubCatListFromHTML);
                                break;
                            }
                            if(!as.sublist)as.subcount++;
                            else app.classes.list.deep--;
                            //--------------------------------------
                            
                            return;
                    }
                   else if(parseInt((app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines + app.classes.list.cur_item + 1),10)>= parseInt(app.classes.list.items_count,10)&&as.layer==as.layers.CATS_LIST)
                       {
                          app.classes.list.ctrls.move("end_r", true);    
                       }
                   else if(app.classes.list.cur_item + 1 < as.sizes[as.actualSize].max_list_lines &&
                        app.classes.list.cur_page <app.classes.list.pages_count){							
                        app.classes.list.cur_item++;
							
                    } else {
                        if(app.classes.list.pages_count-1>app.classes.list.cur_page){
                            app.classes.list.cur_page++;
                            app.classes.list.views.fill[app.classes.list.cur_mode+'Fill'](app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines,'begin');
                        }
                    }
                break;
                case "page-":
                    app.classes.list.cur_item = -1;
                    app.classes.list.ctrls.move("up", true);
                break;
                case "page+":
                     app.classes.list.cur_item = as.sizes[as.actualSize].max_list_lines;
                     app.classes.list.ctrls.move("down", true);
                break;
                case 0:
                default:
						
                break;
            }
            switch(app.classes.list.cur_mode){
                case "simple":
                    $(app.classes.list.act_row_id).html($(as.list).getElementsByClassName('line')[app.classes.list.cur_item].html());						
                break;
                case "extended":
                    /*if(app.classes.list.cur_item>=as.sizes[as.actualSize].num_line_for_rotate_suggest){
                        $('item_suggect').className='line_act_suggest_vert';
                    }
                    else{
                        $('item_suggect').className='line_act_suggest';
                    }*/
                    $(app.classes.list.act_row_id).getElementsByClassName('line_act')[0].html($(as.list).getElementsByClassName('line')[app.classes.list.cur_item].html());
                    $('info_head').getElementsByClassName('h_img')[0].getElementsByTagName('img')[0].setAttribute('src',$(as.list).getElementsByClassName('line')[app.classes.list.cur_item].getAttribute('title'));
                    $('info_head').getElementsByClassName('h_text')[0].html($(as.list).getElementsByClassName('line')[app.classes.list.cur_item].html());
                    $('info_head').getElementsByClassName('h_text_n')[0].html(lang.add+' '+$(as.list).getElementsByClassName('line')[app.classes.list.cur_item].getAttribute('add'));
                break;
            }
            $(app.classes.list.act_row_id).style.marginTop = (app.classes.list.cur_item * as.sizes[as.actualSize].act_line_margin_top - as.sizes[as.actualSize].act_line_margin_top_first).toString() + "px";
        }
    },
    "views":{
        "init":function(mode, arr, act_row,no_reset){
            app.classes.list.cur_mode = mode;
            for(var i=0; i < as.sizes[as.actualSize].max_list_lines; i++) {
                itm = views.components.item;
                app.views.addElement(itm, $(as.list));
            }
            app.classes.list.views.fill[mode](arr, act_row,no_reset);
        },
        "fill":{
            "simple":function(arr, act_row,no_reset){
		log('app.classes.fill.simple');			
                var itm = views.components.item_act_simple;
                itm.attrs.id=act_row;
                itm.attrs.html='';
                app.views.addElement(itm, $(as.list));

                app.classes.list.pages_count = Math.ceil(arr.length / as.sizes[as.actualSize].max_list_lines);
                app.classes.list.items_count = arr.length;
                app.classes.list.items = arr;					
					 
                this.simpleFill(app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines);
            },
            "simpleFill":function(start,move){
                log('app.classes.fill.simpleFill');	
                $('list_page').html((app.classes.list.cur_page+1+app.classes.list.pages_count*app.classes.list.cur_exPage).toString());
                $('list_pages_count').html(app.classes.list.pages_count+app.classes.list.pages_count*app.classes.list.cur_exPage);
                $('list_items_count').html(app.classes.list.items_count+app.classes.list.items_count*app.classes.list.cur_exPage); 
					
					
                var items = $(as.list).getElementsByClassName('line');
                for(var i=0;i<items.length;i++){
                    if(app.classes.list.items[i + start] != undefined) {
                        items[i].setAttribute('rel', app.classes.list.items[i + start].url);//+'&per=100'
                        items[i].html(app.classes.list.items[i + start].name);
                    } else {
                        items[i].setAttribute('rel', 'false');
                        items[i].html(' ');
                    }
                }
                app.classes.list.ctrls.move(move, true);
            },
            "extended":function(arr, act_row,no_reset){
                log('app.classes.extended');
                var itm = views.components.item_act_extended;
                itm.attrs.id=act_row;
                itm.attrs.html='';
                app.views.addElement(itm, $('list_s'));
		app.classes.list.pages_count = Math.ceil(arr.length / as.sizes[as.actualSize].max_list_lines);
                app.classes.list.items_count = arr.length;
                app.classes.list.items = arr;					
                this.extendedFill(app.classes.list.cur_page*as.sizes[as.actualSize].max_list_lines);
            },
            "extendedFill":function(start,move){
                log('app.classes.extendedFill');
                $('list_page_s').html((app.classes.list.cur_page+1+app.classes.list.pages_count*app.classes.list.cur_exPage).toString());
                //$('list_pages_count_s').html(app.classes.list.pages_count+app.classes.list.pages_count*app.classes.list.cur_exPage);
                $('list_items_count_s').html(app.classes.list.items_count+app.classes.list.items_count*app.classes.list.cur_exPage);
                var items = $('list_s').getElementsByClassName('line');
                for(var i=0;i<items.length;i++){
                    if(app.classes.list.items[i + start] != undefined) {
                        items[i].setAttribute('rel', app.classes.list.items[i + start].url);//+'&per=100'
                        items[i].setAttribute('title', app.classes.list.items[i + start].img);
                        items[i].setAttribute('cat', app.classes.list.items[i + start].cat);
                        items[i].setAttribute('add', app.classes.list.items[i + start].add);
                        items[i].html(app.classes.list.items[i + start].name);
                    } else {
                        items[i].setAttribute('rel', 'false');
                        items[i].setAttribute('title', '');
                        items[i].html(' ');
                    }
                }
                //log($('page_main').innerHTML+'\n');
                //log($('page_main_s').innerHTML);
                app.classes.list.ctrls.move(move, true);
            }
        }
    },
    "keypress":function(code){
        switch(code){
            case keys.UP:
                app.classes.list.ctrls.move("up");
            break;
            case keys.DOWN:
                app.classes.list.ctrls.move("down");
            break;
            case keys.PAGE_NEXT:
                app.classes.list.ctrls.move("page+");
            break;
            case keys.PAGE_PREV:
                app.classes.list.ctrls.move("page-");
            break;
        }
    }
}
};