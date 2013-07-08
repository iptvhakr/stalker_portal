
function init(){
    window.moveTo(0, 0);
    window.resizeTo(win.width, win.height);
    loadStyle(win.height+'.css');
    lang = getEnvironmentValue('language');
    if(lang != 'en' || lang != 'ru'){
        lang = 'ru';
    }
    loadScript(lang+'.js','scriptloaded()'); // add lang cheking here
    stb.InitPlayer();
    stb.SetTopWin(0);
    stb.EnableServiceButton(true);
    stb.EnableVKButton(false);
    vars.player_vars.volume = stb.GetVolume();
    vars.player_vars.mute = stb.GetMute();
    device = stb.RDir("ModelExt");
    vars.sn = stb.RDir("SerialNumber");
    stb.SetPIG (1,0,0,0);

    var match = '';
    if (match = /referrer\=([^&]+)/.exec(window.location.search)){
        back_location = decodeURIComponent(match[1]);
    }else{
        back_location = '';
    }

    log('currentLocation: '+back_location);
    stbEvent = {
        onEvent:eventFunc,
        event: 0
    };
    log('init finished')
    //$('login_page').style.display = 'block';
    
    //sendreq(iviURL+'categories/', log)
}
function scriptloaded(){
//    for(var i = 0;i<3;i++){
//        document.getElementsByClassName('login_text')[i].innerHTML = login_text_arr[i];
//    }
//    document.getElementsByClassName('btn_div')[0].getElementsByTagName('input')[0].value = auth_btn_ok;
//    document.getElementsByClassName('btn_div')[0].getElementsByTagName('input')[1].value = auth_btn_cansel;
//    
//    window.setTimeout(function(){$('login').focus();},1)
//    var accpass = stb.LoadUserData('ivifile');
//    if(!empty(accpass)){
//        authentification(true);
//    }else{
//        $('login_page').style.display = 'block';
//    }
    init_pages();
     sendreq(iviURL+'categories/', drow_cats);
}

function init_pages(){
    for(var y = 0;y<(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max);y++){
        var obj = {
            'tag':'div',
            'attrs':{
                'id':'video_p'+y,
                'class':'poster_box'
            },
            'child':[
                {
                    'tag':'div',
                    'attrs':{
                        'class':'movie_desc',
                        'style':'display:none;'
                    },
                    'child':[
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'text_title'
                            }
                        },
                        {
                            'tag':'div',
                            'attrs':{
                                'class':'text2'
                            }
                        }
                    ]
                },
                {
                    'tag':'div',
                    'attrs':{
                        'class':'poster'
                    }
                }
            ]
            
        }
            
        $('video_layer').appendChild(createHTMLTree(obj));
    }
}

function authentification(auto){
    var login = '';
    var pass = '';
    if(auto){
        var accpass = JSON.parse(stb.LoadUserData('ivifile'));
        login = accpass.login;
        pass = b64_to_utf8(accpass.pass);
        autologin = true;
    }else{
        if($('login').value == '' || $('password').value == ''){
            newMyAlert(allert_empty_fld,'temp')
            return;
        }
        login = $('login').value;
        pass = $('password').value;
        autologin = false;
    }
    
    //console.log(iviURL+'login/?email='+login+'&password='+pass);
    sendreq(iviURL+'login/?email='+login+'&password='+pass,drowheader);
}
function drowheader(text){
    //console.log(text)
    var authData = JSON.parse(text);
    if(authData.error){
        newMyAlert(authData.error[0],'temp')
    }
    else{
        if(!autologin){
            if($('submit').checked){
                stb.SaveUserData('ivifile','{"login":"'+$('login').value+'","pass":"'+utf8_to_b64($('password').value)+'"}')
            }else{
                stb.SaveUserData('ivifile','')
            }
        }
        sendreq(iviURL+'categories/', drow_cats);
    }
}



function drow_cats(responseCats){
    cats_obj = JSON.parse(responseCats);
    //console.log(responseCats)
    byclass('cat_item_text')[0].innerHTML = "Популярное";
    for(var i = 1;i < 5;i++){
        byclass('cat_item_text')[i].innerHTML = cats_obj[i-1].title;
    }
    switchLayer(layer_cats);
    pop_layer = true;
    sendreq(iviURL+'catalogue/v2/?from=0&to=7&sort=pop', init_contentlist);
}

function drow_promo(text){
    dataset = JSON.parse(text);
/*    $('promo').style.background = 'url('+promo_obj[vars.cont_page_x].img_ipad.path+')';
    $('promo').getElementsByClassName('pop_title')[0].innerHTML = promo_obj[vars.cont_page_x].title;*/
    for(var i = 0;i<vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max;i++){
        $('promo_p'+i).getElementsByClassName('poster')[0].innerHTML = '<img src="'+dataset[i].thumbnails[0].path+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:5px;"/>';
        //(new Image()).src = promo_obj[i].img_ipad.path;
    }
    
    switchSubLayer(sub_layer_pop);
}

function count_filters(text){
    //console.log(text);
    var ganre_obj = JSON.parse(text);
    filters(ganre_obj[vars.curCatSel-1].genres);
}

function filters(arr,category){
     switch(category){
        default:
            document.getElementsByClassName('modal_title')[0].innerHTML = select_genre;
            for(var i = 0; i < arr.length;i++){
                var obj = document.createElement('input');
                obj.type = 'button';
                obj.className = 'genrebtn';
                obj.value = arr[i].title;
                obj.name = arr[i].id;
                document.getElementsByClassName('modal_list')[0].appendChild(obj);
                document.getElementsByClassName('genrebtn')[i].setAttribute('onclick','document.getElementsByClassName("modal")[0].style.display = "none";setmod("genre","'+arr[i].id+'","'+arr[i].title+'");document.getElementsByClassName("modal_list")[0].innerHTML = ""');
            }
            log(typeof(layer_indexes.active[layer_genre]));
            var obj = document.createElement('input');
            obj.type = 'button';
            obj.className = 'genrebtn';
            obj.value = 'Любой жанр';
            obj.id = 'all_cats';
            document.getElementsByClassName('modal_list')[0].appendChild(obj);
            $('all_cats').setAttribute('onclick','document.getElementsByClassName("modal")[0].style.display = "none";setmod("genre",null,"Любой жанр");document.getElementsByClassName("modal_list")[0].innerHTML = ""');
            window.setTimeout(function(){document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus()},1)
        break;
        case "year":
            document.getElementsByClassName('modal_title')[0].innerHTML = select_year;
            var obj_year = {
                'tag':'div',
                'attrs':{
                    'class':'year_form'
                },
                'child':[
                    {
                        'tag':'span',
                        'attrs':{
                            'html':'C ',
                            'class':'year_span'
                        }
                    },
                    {
                        'tag':'input',
                        'attrs':{
                            'id':'year_0',
                            'class':'year_input',
                            'type':'text'
                        }
                    },
                    {
                        'tag':'span',
                        'attrs':{
                            'html':' по ',
                            'class':'year_span',
                            'type':'text'
                        }
                    },
                    {
                        'tag':'input',
                        'attrs':{
                            'id':'year_1',
                            'class':'year_input'
                        }
                    }
                ]
            }
            
            document.getElementsByClassName('modal_list')[0].appendChild(createHTMLTree(obj_year));
            window.setTimeout(function(){$('year_0').focus();}, 1);
            if(vars.cats[vars.curCatSel-1][2].year_from == null){
                $('year_0').value = 1900
            }else{
                $('year_0').value = vars.cats[vars.curCatSel-1][2].year_from;
            }
            if(vars.cats[vars.curCatSel-1][2].year_to == null){
                var date = new Date();
                $('year_1').value = date.getFullYear();
            }else{
                $('year_1').value = vars.cats[vars.curCatSel-1][2].year_to;
            }
            year_focus = 0;
			var okbtn = document.createElement('input');
			okbtn.type = 'button';
			okbtn.value = 'Применить';
			okbtn.id = 'genre_okbtn';
			okbtn.className = 'genrebtn';
			//okbtn.onclick = 'function(){document.getElementsByClassName("close_btn")[0].removeChild($("genre_okbtn")); subcatsPage_keyhandler({"keyCode":13,"which":13});}';
			document.getElementsByClassName('close_btn')[0].appendChild(okbtn);
        break;
        case "country":
            document.getElementsByClassName('modal_title')[0].innerHTML = select_countries;
            var y = 0;
            for(var i in countries){
                if(y<17){
                    var obj = document.createElement('input');
                    obj.type = 'button';
                    obj.className = 'genrebtn';
                    obj.value = countries[i];
                    obj.name = i;
                    //obj.onclick = 'document.getElementsByClassName("modal")[0].style.display = "none";vars.cats[vars.curCatSel-1][(vars.catSel-vars.curCatSel)-1].id = '+i+';vars.cats[vars.curCatSel-1][(vars.catSel-vars.curCatSel)-1].title = '+countries[i]+';setmod("country","'+i+'","'+countries[i]+'")';
                    document.getElementsByClassName('modal_list')[0].appendChild(obj);
                    document.getElementsByClassName('genrebtn')[y].setAttribute('onclick','document.getElementsByClassName("modal")[0].style.display = "none";setmod("country","'+i+'","'+countries[i]+'");document.getElementsByClassName("modal_list")[0].innerHTML = ""');
                    y++;
                }else{
                    break;
                }
            }
            var obj = document.createElement('input');
            obj.type = 'button';
            obj.className = 'genrebtn';
            obj.value = 'Любая страна';
            obj.id = 'all_cats';
            document.getElementsByClassName('modal_list')[0].appendChild(obj);
            $('all_cats').setAttribute('onclick','document.getElementsByClassName("modal")[0].style.display = "none";setmod("country",null,"Любая страна");document.getElementsByClassName("modal_list")[0].innerHTML = ""');
            window.setTimeout(function(){document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus()},1)
        break;
        case "series":
            document.getElementsByClassName('modal_title')[0].innerHTML = select_series;
            subcatsArr = arr;
            for(var i = 0; i < arr.length ;i++){
                //console.log(JSON.stringify(arr[i]));
                var obj = document.createElement('input');
                obj.type = 'button';
                obj.className = 'genrebtn';
                obj.value = arr[i].title;
                obj.name = arr[i].id;
                document.getElementsByClassName('modal_list')[0].appendChild(obj);
                document.getElementsByClassName('genrebtn')[i].setAttribute('onclick','current_series = '+i+';document.getElementsByClassName("modal")[0].style.display = "none";sesies_getdata('+arr[i].id+');document.getElementsByClassName("modal_list")[0].innerHTML = "";');
                log(document.getElementsByClassName('genrebtn')[i].onclick);
            }
            window.setTimeout(function(){document.getElementsByClassName('genrebtn')[layer_indexes.active[layer_genre]].focus()},1)
        break;
    }
    document.getElementsByClassName('modal')[0].style.display = 'block';
    PREV_LAYER = CUR_LAYER;
    CUR_LAYER = layer_genre;
}

function setmod(cat,state,title){
    switch(cat){
        case 'genre':
            vars.cats[vars.curCatSel-1][0].id = state;
            vars.cats[vars.curCatSel-1][0].title = title;
            var url = '';
            url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
            if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
            }
            if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
            }
            if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
            }
            if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                url+='&country='+vars.cats[vars.curCatSel-1][1].id;
            }
            if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                url+='&category='+vars.catID[vars.curCatSel]
            }
            log(url)
            sendreq(url, next_cont_page);
            document.getElementsByClassName('item_act')[0].innerHTML = title;
            //vars.cats[(vars.curCatSel-1)][(vars.catSel-vars.curCatSel)-1].id = state;
            //vars.cats[(vars.curCatSel-1)][(vars.catSel-vars.curCatSel)-1].title = title;
        break;
        case 'country':
            vars.cats[vars.curCatSel-1][1].id = state;
            vars.cats[vars.curCatSel-1][1].title = title;
            var url = '';
            url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
            if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
            }
            if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                url+='&country='+vars.cats[vars.curCatSel-1][1].id;
            }
            if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
            }
            if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
            }
            if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                url+='&category='+vars.catID[vars.curCatSel]
            }
            log(url)
            sendreq(url, next_cont_page)
            document.getElementsByClassName('item_act')[0].innerHTML = title;
        break;
        case 'year':
        break;
    }
    CUR_LAYER = layer_cats;
}

function sesies_getdata(id){
    CUR_LAYER = layer_info;
    var time = new Date();
    sendreq_post(
        'http://api.digitalaccess.ru/api/json/',
        get_video_data,
        '{"method":"da.content.get","params":['+id+',{"contentid":'+id+',"watchid":"'+id+''+time.getTime()+'","site":"s127"}]}'//
    );
    //sendreq(iviURL+'videofullinfo/?id='+id,get_video_data);
}

function init_subcats(cat_no){
    if(byclass('block_big')[0].childNodes.length<6){
        for(var i = 0;i<11;i++){
            var subcatobj = 
            {
                'tag':'div',
                'attrs':{
                    'id':'genre_row'+i,
                    'class':'genre_row elements',
                },
                'child':[
                    {
                        'tag':'div',
                        'attrs':{
                            'class':'genre_name text'
                        }
                    }
                ]
            }
            byclass('block_big')[0].appendChild(createHTMLTree(subcatobj));
        }
    }
    vars.subcats_page = 0;
    fillsubcats(cat_no,vars.subcats_page);
    window.setTimeout(function(){vars.subcats_item = 0;
        $('subcat_list_selector').style.top = $('genre_row0').offsetTop+'px';
        byclass('genre_name_act')[0].innerHTML = cats_obj[cat_no].genres[vars.subcats_item].title;
    },1);
}

function fillsubcats(cat_no,subcats_page){
    for(var i = 0;i<vars[win.height].subcats_onpage;i++){
        if(cats_obj[cat_no].genres.length > i + vars[win.height].subcats_onpage*subcats_page){
            byclass('genre_name')[i].innerHTML = cats_obj[cat_no].genres[i + vars[win.height].subcats_onpage*subcats_page].title;
            byclass('genre_row')[i].style.visibility = 'visible';
        }else{
            byclass('genre_row')[i].style.visibility = 'hidden';
        }
    }
    
}

function subcats_move(dir){
    var item_onpage = vars.subcats_item - vars.subcats_page*vars[win.height].subcats_onpage;
    switch(dir){
        case 38:
            if(vars.subcats_item>0){
                if(item_onpage>0){
                    vars.subcats_item--;
                    item_onpage--;
                    $('subcat_list_selector').style.top = $('genre_row'+item_onpage).offsetTop+'px';
                    byclass('genre_name_act')[0].innerHTML = cats_obj[vars.catSel].genres[vars.subcats_item].title;
                }else{
                    vars.subcats_item--;
                    item_onpage=vars[win.height].subcats_onpage-1;
                    vars.subcats_page--;
                    fillsubcats(vars.catSel,vars.subcats_page);
                    $('subcat_list_selector').style.top = $('genre_row'+item_onpage).offsetTop+'px';
                    byclass('genre_name_act')[0].innerHTML = cats_obj[vars.catSel].genres[vars.subcats_item].title;
                }
            }
        break;
        case 40:
            if(vars.subcats_item<cats_obj[vars.catSel].genres.length-1){
                if(item_onpage<vars[win.height].subcats_onpage-1){
                    vars.subcats_item++;
                    item_onpage++;
                    $('subcat_list_selector').style.top = $('genre_row'+item_onpage).offsetTop+'px';
                    byclass('genre_name_act')[0].innerHTML = cats_obj[vars.catSel].genres[vars.subcats_item].title;
                }else{
                    vars.subcats_item++;
                    item_onpage=0;
                    vars.subcats_page++;
                    fillsubcats(vars.catSel,vars.subcats_page);
                    $('subcat_list_selector').style.top = $('genre_row'+item_onpage).offsetTop+'px';
                    byclass('genre_name_act')[0].innerHTML = cats_obj[vars.catSel].genres[vars.subcats_item].title;
                }
            }
        break;
        case 37:
        break;
        case 39:
        break;
    }
    log(item_onpage)
}

function init_contentlist(text){
    //console.log(text);
    dataset = JSON.parse(text);
    for(var i = 0;i<(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max);i++){
        if(dataset[i]){
            if(!empty(dataset[i].thumbnails)){
                $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '<img src="'+dataset[i].thumbnails[0].path+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:5px;"/>';
            }else{
                $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '<img src="img/'+win.height+'/video-preview.png" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:5px;"/>';
            }
            $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text_title')[0].innerHTML = dataset[i].title;
            if(dataset[i].years != undefined){
                $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML = dataset[i].years.length<4?dataset[i].years:(dataset[i].years[0]+'...');
            }
            if(countries[dataset[i].country] != undefined){
                if(dataset[i].years != undefined){
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += '<br>';
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += countries[dataset[i].country];
                }else{
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML = countries[dataset[i].country];
                }
            }
            if(dataset[i].genres != undefined){
                $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += '<br>';
                for(var y = 0;y < dataset[i].genres.length;y++){
                    if(genres[dataset[i].genres[y]] != undefined){
                        if(y < dataset[i].genres.length-1){
                            $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += genres[dataset[i].genres[y]]+', ';
                        }else{
                            $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += genres[dataset[i].genres[y]];
                        }
                    }
                }
            }
        }else{
            $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '';
            $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text_title')[0].innerHTML = '';
        }
    }
    window.setTimeout(function(){switchSubLayer(sub_layer_video);},180);
}

function content_move(dir){
    $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box';
    $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'none';
    switch(dir){
        case 38:
            if(vars.cont_page_y == 1){
                vars.cont_page_y = 0;
            }else{
                    if(vars.content_page>0){
                    vars.cont_page_y = 1;
                    vars.content_page--;
                    if(pop_layer){
                        var url = '';
                        url +=iviURL+'catalogue/v2/?sort=pop&from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))
                    }else{
                        var url = '';
                        url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
                        if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                            url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                            url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                            url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                            url+='&country='+vars.cats[vars.curCatSel-1][1].id;
                        }
                        if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                            url+='&category='+vars.catID[vars.curCatSel]
                        }
                    }
                    log(url)
                    sendreq(url, next_cont_page)                    
                    return;
                }
            }
        break;
        case 40:
            if(vars.cont_page_y == 0){
                if($('video_p'+(vars.cont_page_x+(1*vars[win.height].cont_page_x_max))).getElementsByClassName('poster')[0].innerHTML!=''){
                    vars.cont_page_y = 1;
                }
            }else{
                if($('video_p'+((vars[win.height].cont_page_x_max-1)+(1*vars[win.height].cont_page_x_max))).getElementsByClassName('poster')[0].innerHTML!=''){
                    vars.cont_page_y = 0;
                    vars.content_page++;
                    if(pop_layer){
                        var url = '';
                        url +=iviURL+'catalogue/v2/?sort=pop&from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))
                    }else{
                        var url = '';
                        url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
                        if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                            url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                            url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                            url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
                        }
                        if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                            url+='&country='+vars.cats[vars.curCatSel-1][1].id;
                        }
                        if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                            url+='&category='+vars.catID[vars.curCatSel]
                        }
                    }
                    log(url)
                    sendreq(url, next_cont_page)                    
                    return;
                }
            }
        break;
        case 37:
            if(vars.cont_page_x>0){
                vars.cont_page_x--;
            }else{
                if(vars.cont_page_y == 1){
                    if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                        $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                    }else{
                        $(vars.catsArr[vars.catSel].id).className = 'item_act';
                    }
                    vars.cont_page_x = 0;
                    vars.cont_page_y = 0;
                    left_side = true;
                    return;
                }else{
                    if($(vars.catsArr[vars.catSel].id).title == 'topmenu'){
                        $(vars.catsArr[vars.catSel].id).className = 'menu_act cat_item_text';
                    }else{
                        $(vars.catsArr[vars.catSel].id).className = 'item_act';
                    }
                    vars.cont_page_x = 0;
                    vars.cont_page_y = 0;
                    left_side = true;
                    return;
                }
            }
        break;
        case 39:
            if(vars.cont_page_x<vars[win.height].cont_page_x_max-1){
                if($('video_p'+((vars.cont_page_x+1)+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('poster')[0].innerHTML!=''){
                    vars.cont_page_x++;
                }
            }else{
                if(vars.cont_page_y == 0){
                    
                }else{
                    
                }
            }
        break;
    }
    $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box_act';
    $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'block';
}

function open_submenu(){
    var arr = document.getElementsByClassName('submenu');
    for(var i = 0;i<arr.length;i++){
        arr[i].parentNode.removeChild(arr[i]);
    }
    for(var y = vars.catsArr.length-1;y>-1;y--){
        if(vars.catsArr[y].normal == 'item'){
            vars.catsArr.splice(y, 1);
        }
    }
    var newobj = {
        "tag":"div",
        "attrs":{
            "class":"submenu"
        },
        "child":[
            {
                "tag":"div",
                "attrs":{
                    "title":"submenu",
                    "class":"item",
                    "id":"item_0",
                    "html":"Любой жанр"
                },
            },
            {
                "tag":"div",
                "attrs":{
                    "title":"submenu",
                    "class":"item",
                    "id":"item_1",
                    "html":"Любая страна"
                },
            },
            {
                "tag":"div",
                "attrs":{
                    "title":"submenu",
                    "class":"item",
                    "id":"item_2",
                    "html":"Любой год"
                },
            }
        ]
    }
    $(vars.catsArr[vars.catSel].id).appendChild(createHTMLTree(newobj));
    vars.catsArr.splice(vars.curCatSel+1, 0, {"id":"item_0","normal":"item","focus":"item_focus","click":"item_act","func":"list"},{"id":"item_1","normal":"item","focus":"item_focus","click":"item_act","func":"list"},{"id":"item_2","normal":"item","focus":"item_focus","click":"item_act","func":"list"})
    $(vars.catsArr[vars.catSel].id).className = 'menu cat_item_text';
    vars.catSel++;
    $(vars.catsArr[vars.catSel].id).className = 'item_act';
}

function next_cont_page(text){
    //console.log(text);
    dataset = JSON.parse(text);
    for(var i = 0;i<(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max);i++){
        if(!empty(dataset[i])){
            if(dataset[i].thumbnails){
                $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '<img src="'+dataset[i].thumbnails[0].path+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:5px;"/>';
            }else{
                $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '<img src="img/'+win.height+'/video-preview.png" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:5px;"/>';
            }
            $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text_title')[0].innerHTML = dataset[i].title;
            if(dataset[i].years != undefined){
                $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML = dataset[i].years.length<4?dataset[i].years:(dataset[i].years[0]+'...');
            }
            if(countries[dataset[i].country] != undefined){
                if(dataset[i].years != undefined){
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += '<br>';
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += countries[dataset[i].country];
                }else{
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML = countries[dataset[i].country];
                }
            }
            if(dataset[i].genres != undefined){
                $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += '<br>';
                for(var y = 0;y < dataset[i].genres.length;y++){
                    $('video_p'+i).getElementsByClassName('movie_desc')[0].getElementsByClassName('text2')[0].innerHTML += genres[dataset[i].genres[y]]+', ';
                }
            }
        }else{
            $('video_p'+i).getElementsByClassName('poster')[0].innerHTML = '';
        }
    }
    //window.setTimeout(function(){
    if(!empty(dataset[(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))])){
        if(vars.cont_page_x == 0 && vars.cont_page_y == 0 && vars.content_page ==0){
            
        }else{
            $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box_act';
            $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'block';
        }
    }else{
        vars.cont_page_x = 0;
        vars.cont_page_y = 0;
        if(!empty(dataset[(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))])){
            $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'poster_box_act';
            $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'block';
        }else{
            if(vars.content_page>0){
                newMyAlert("Дальше записей нет", 'temp')
                vars.content_page--;
                if(pop_layer){
                    var url = '';
                    url +=iviURL+'catalogue/v2/?sort=pop&from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))
                }else{
                    var url = '';
                    url = iviURL+'catalogue/v2/?from='+(vars.content_page*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&to='+((vars.content_page+1)*(vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max))+'&sort=new';
                    if(!empty(vars.cats[vars.curCatSel-1][0].id)){
                        url+='&genre='+vars.cats[vars.curCatSel-1][0].id;
                    }
                    if(!empty(vars.cats[vars.curCatSel-1][2].year_from)){
                        url+='&year_from='+vars.cats[vars.curCatSel-1][2].year_from;
                    }
                    if(!empty(vars.cats[vars.curCatSel-1][2].year_to)){
                        url+='&year_to='+vars.cats[vars.curCatSel-1][2].year_to;
                    }
                    if(!empty(vars.cats[vars.curCatSel-1][1].id)){
                        url+='&country='+vars.cats[vars.curCatSel-1][1].id;
                    }
                    if(empty(vars.cats[vars.curCatSel-1][0].id) && empty(vars.cats[vars.curCatSel-1][1].id)){
                        url+='&category='+vars.catID[vars.curCatSel]
                    }
                }
                sendreq(url, next_cont_page)                    
                return;
            }
        }
    }
    //},180);
}

function init_info(text){
    //console.log(text);
    file = JSON.parse(text);
    var time = new Date();
    var month = '';
    var day = '';
    var hour = '';
    var new_elem = true;
    var re = '';
    if(!empty(file.result)){
        var watch_id = file.result.id+'.'+time.getTime();
        var advwatch_id = file.result.midroll[0]+'.'+time.getTime();
        var ivifile = stb.LoadUserData('ivifile');
        log(ivifile);
        if(empty(ivifile)){
            ivifile = [];
        }else{
            ivifile = JSON.parse(ivifile);
        }
        var adv={"month":[],"day":[],"hour":[]}
        for(var i=0;i<ivifile.length;i++){
            ivifile[i].time = new Date(ivifile[i].time);
            if(ivifile[i].time.getMonth() == time.getMonth()){
                if(ivifile[i].time.getDay() == time.getDay()){
                    if(ivifile[i].time.getHours() == time.getHours()){
                        adv.hour.splice(adv.hour.length, 0,ivifile[i].id)
                    }else{
                        adv.day.splice(adv.day.length, 0,ivifile[i].id)
                    }
                }else{
                    adv.month.splice(adv.month.length, 0,ivifile[i].id)
                }
            }
        }
        for(var i = 0;i<adv.month.length;i++){
            new_elem = true;
            re = new RegExp(adv.month[i]+'"\\:\\d*');
            log(re)
            if(re.test(month)){
                new_elem = false;
                var find = month.match(re);
                var count = find[0].match(/":\d*/)[0].replace('":','');
                month = month.replace(re,(adv.month[i]+'":'+(parseInt(count)+1)));
            }
            if(new_elem){
//                if(i==adv.month.length-1){
//                    month+=('"'+adv.month[i]+'":1');
//                }else{
                month+=('"'+adv.month[i]+'":1,');
//                }
            }
            if(i==adv.month.length-1){
                if(month[month.length-1] == ','){
                    month=month.substr(0, month.length-1);
                }
            }
        }
        for(var i = 0;i<adv.day.length;i++){
            new_elem = true;
            re = new RegExp(adv.day[i]+'"\\:\\d*');
            if(re.test(day)){
                new_elem = false;
                var find = day.match(re);
                var count = find[0].match(/":\d*/)[0].replace('":','');
                day = day.replace(re,adv.day[i]+'":'+(parseInt(count)+1));
            }
            if(new_elem){
//                if(i==adv.day.length-1){
//                    day+=('"'+adv.day[i]+'":1');
//                }else{
                day+=('"'+adv.day[i]+'":1,');
//                }
            }
            if(i==adv.day.length-1){
                if(day[day.length-1] == ','){
                    day=day.substr(0, day.length-1);
                }
            }
        }
        for(var i = 0;i<adv.hour.length;i++){
            new_elem = true;
            re = new RegExp(adv.hour[i]+'"\\:\\d*');
            if(re.test(hour)){
                new_elem = false;
                var find = hour.match(re);
                var count = find[0].match(/":\d*/)[0].replace('":','');
                hour = hour.replace(re,adv.hour[i]+'":'+(parseInt(count)+1));
            }
            if(new_elem){
                //if(i==adv.hour.length-1){
                //    hour+=('"'+adv.hour[i]+'":1');
                //}else{
                hour+=('"'+adv.hour[i]+'":1,');
                //}
            }
            if(i==adv.hour.length-1){
                if(hour[hour.length-1] == ','){
                    hour=hour.substr(0, hour.length-1);
                }
            }
        }
        sendreq_post(
                    'http://api.digitalaccess.ru/api/json/',
                    set_reclama,
                    //'{"method":"da.adv.get","params":['+file.result.id+',{"content_id":"'+file.result.id+'","watch_id":"'+watch_id+'","uid":"'+time.getTime()+'","site":"s127"}]}'//midroll[0]
                    '{"method":"da.adv.get","params":['+file.result.id+',{"content_id":"'+file.result.id+'","watch_id":"'+watch_id+'","advid":"'+file.result.midroll[0]+'","advwatchid":"'+advwatch_id+'","last_adv":"","previous_videos":{"month":{'+month+'},"hour":{'+hour+'},"day":{'+day+'},"week":{}},"shown_video_ids":[],"site":"s127"}]}'//
                );
                log('{"method":"da.adv.get","params":['+file.result.id+',{"content_id":"'+file.result.id+'","watch_id":"'+watch_id+'","advid":"'+file.result.midroll[0]+'","advwatchid":"'+advwatch_id+'","last_adv":"","previous_videos":{"month":{'+month+'},"hour":{'+hour+'},"day":{'+day+'},"week":{}},"shown_video_ids":[],"site":"s127"}]}')
    }

    switchLayer(layer_info);
}

function set_reclama(text){
    //console.log(text);
    reclama = JSON.parse(text);
}

function init_series(text){
    seasons = JSON.parse(text);
    //console.log(text);
    filters(seasons, 'series');
}


var aspectTimer = 0;
function changeAspect(){
    if(aspectTimer) {clearTimeout(aspectTimer);}
    aspectTimer = null;
    vars.player_vars.aspect_current = (vars.player_vars.aspect_current + 1) % 4;
    stb.SetAspect(vars.player_vars.aspects[vars.player_vars.aspect_current].mode);
    $('screenAspect').style.backgroundImage = 'url(' + vars.player_vars.aspects[vars.player_vars.aspect_current].img + ')';
    $('screenAspect').style.display = 'block';
    aspectTimer = setTimeout(function() {$('screenAspect').style.display = 'none';}, 3000);
}

function init_search_page(){
    document.getElementsByClassName('modal_title')[0].innerHTML = select_search;
    layer_indexes.active[CUR_LAYER]=0
    var input = document.createElement('input');
        input.id = 'search_line';
        input.type = 'text';
        input.name = 'name';
    $('search_layer').appendChild(input);
    $('search_line').oninput = get_suggest;
    var div = {
        'tag':'div',
        'attrs':{
            'class':'sub_select'
        },
        'child':[
            {
                'tag':'select',
                'attrs':{
                    'id':'search_cats',
                    'class':'select'
                },
                'child':[
                    {
                        'tag':'option',
                        'attrs':{
                            'value':0,
                            'html':'Любая категория'
                        },
                        'child':[

                        ]
                    },
                    {
                        'tag':'option',
                        'attrs':{
                            'value':14,
                            'html':'Кино'
                        },
                        'child':[

                        ]
                    },
                    {
                        'tag':'option',
                        'attrs':{
                            'value':15,
                            'html':'Сериалы'
                        },
                        'child':[

                        ]
                    },
                    {
                        'tag':'option',
                        'attrs':{
                            'value':17,
                            'html':'Мультфильмы'
                        },
                        'child':[

                        ]
                    },
                    {
                        'tag':'option',
                        'attrs':{
                            'value':16,
                            'html':'Программы'
                        },
                        'child':[

                        ]
                    }
                ]
            }
        ]
    }
    $('search_layer').appendChild(createHTMLTree(div));
    div = {
        'tag':'div',
        'attrs':{
            'class':'sub_select'
        },
        'child':[
            {
                'tag':'select',
                'attrs':{
                    'id':'search_country',
                    'class':'select'
                },
                'child':[
                    {
                        'tag':'option',
                        'attrs':{
                            'value':0,
                            'html':'Любая страна'
                        }
                    }
                ]
            }
        ]
    }
    
    for(var i in countries){
/*        
        var option = document.createElement('option');
            option.value = i;
            option.innerHTML = countries[i];
        $('search_country').appendChild(option);
*/
        var obj = {
            'tag':'option',
            'attrs':{
                'value':i,
                'html':countries[i]
            }
        }
        div.child[0].child.splice(div.child[0].child.length,0,obj)
    }
    $('search_layer').appendChild(createHTMLTree(div));
    document.getElementsByClassName('modal')[0].style.display = 'block';
    document.getElementsByClassName('close_btn')[0].innerHTML = '<div class="search_btn"><input type="button" id="search_cancel" onclick="exit_search()" value="Отмена" /></div><div class="search_btn"><input id="search_ok" type="button" value="Найти" onclick="send_search_req()" /></div>'
    window.setTimeout(function(){$('search_line').focus();},0)
         
}

function get_suggest(){
    //$('search_genre').value
    var url = iviURL+'autocomplete/v2/?query='//'search/v2/?query=';
        url+=$('search_line').value;
/*    if($('search_cats').value!=0){
        url+='&category='+$('search_cats').value
    }
    if($('search_country').value!=0){
        url+='&country='+$('search_country').value
    }*/
    sendreq(url,build_suggest,true);
}

function build_suggest(text){
    var sug_obj = JSON.parse(text);
    if($('sug_list')){
        $('search_layer').removeChild($('sug_list'));        
    }
    if(sug_obj.length>0){
        var sug_list = {
            'tag':'div',
            'attrs':{
                'id':'sug_list'
            },
            'child':[]
        }
        for(var i = 0;i<sug_obj.length && i<5;i++){
            var obj = {
                'tag':'div',
                'attrs':{
                    'id':'sug_item'+i,
                    'class':'sug_item',
                    'html':sug_obj[i].title
                }
            }
            sug_list.child.splice(sug_list.child.length,0,obj)
        }
        $('search_layer').appendChild(createHTMLTree(sug_list));
        suggest_active = true;
        suggest_count = sug_obj.length<5?sug_obj.length:5;
        suggest_focus = -1;
    }else{
        suggest_active = false;
        suggest_count = 0;
        suggest_focus = -1;
    }
}

function send_search_req(){
    var url = iviURL+'search/v2/?query=';
    url+=$('search_line').value;
    if($('search_cats').value!=0){
        url+='&category='+$('search_cats').value
    }
    if($('search_country').value!=0){
        url+='&country='+$('search_country').value
    }
    sendreq(url,search_answer,true);
}

function search_answer(text){
    //console.log(text);
    search_obj = JSON.parse(text);
    if(search_obj.length){
        exit_search();
        init_contentlist(text)
    }else{
        newMyAlert("Не найдено", 'temp')
    }
}

function exit_search(){
    document.getElementsByClassName('close_btn')[0].innerHTML = '<input type="button" class="genrebtn" value="Закрыть" onclick="exit_modal()"/>';
    suggest_active = false;
    suggest_count = 0;
    suggest_focus = -1;
    CUR_LAYER = layer_cats;
    document.getElementsByClassName('modal')[0].style.display = 'none';
    $('search_layer').innerHTML = '';
}

function exit_modal(){
    document.getElementsByClassName('modal')[0].style.display = 'none'; 
    document.getElementsByClassName('modal_list')[0].innerHTML = '';
    CUR_LAYER = PREV_LAYER;
    layer_indexes.active[layer_genre]=0;
    $('modal_exit').blur();
}

function show_waiting(){
    newAlert_on = true;
/*    if($('black_bg')){
        document.body.removeChild($('black_bg'));
    }
    //window.setTimeout(function(){newAlert_on = true;}, 10);
    var black_bg = document.createElement('div');
    black_bg.id = 'black_bg';
    document.body.appendChild(black_bg)
    var div = document.createElement('div');
    //div.id = 'waiting';
    div.className = 'waiting_form';
    div.innerHTML = 'Загрузка...';
    div.style.paddingBottom='20px';
    $('black_bg').appendChild(div);*/
    $('black_bg').style.display = 'block';
    
}

function hide_waiting(){
    newAlert_on = false;
    $('black_bg').style.display = 'none';
}

function show_qestion(onyes,onno){
    document.getElementsByClassName('waiting_form')[0].innerHTML = 'Вам уже есть 18 лет?<br><div id="modal_btn"><input id="alert_btn_ok" name="btn" type="button" onclick="'+onyes+'" value="Да" /><input id="alert_btn_cancel" name="btn" type="button" onclick="'+onno+'" value="Нет" /></div>'
    var elem = document.getElementsByClassName('waiting_form')[0];
    elem.style.width = '350px';
    elem.style.marginLeft = (win.width - 350)/2+'px';
    elem.style.marginTop = (win.height - 50)/2+'px';
    $('black_bg').style.display = 'block';
    CUR_LAYER = layer_conf;
    window.setTimeout(function(){$('alert_btn_cancel').focus();},1)
} 

function switchLayer(layer){
    log('Switch layer from <'+layers[CUR_LAYER]+'> to <'+layers[layer]+'>');
    switch(CUR_LAYER){
        default:
            
        break;
        case layer_auth:
            
        break;
        case layer_player:
            clearInterval(runner_timer);
            clearTimeout(pos_timer);
            setpos_timer = false;
            clearTimeout(setpos_timer);
            clearInterval(adv_timer)
        break;
    }
    $(layers[CUR_LAYER]).style.display = 'none';
    $(layers[layer]).style.display = 'block';
    PREV_LAYER = CUR_LAYER;
    CUR_LAYER = layer;
    switch(layer){
        default:
            document.body.style.background = 'url(img/'+win.height+'/bg.jpg)';
        break;
        case layer_auth:
            $('login').focus();
            document.body.style.background = 'url(img/'+win.height+'/bg.jpg)';
        break;
        case layer_player:
            document.body.style.background = 'none';
        break;
    }
}
 
function switchSubLayer(layer){
    switch(SUB_CUR_LAYER){
        case sub_layer_pop:
            
        break;
        case sub_layer_video:

        break;
    }
    $(sub_layers[SUB_CUR_LAYER]).style.display = 'none';
    $(sub_layers[layer]).style.display = 'block';
    SUB_CUR_LAYER = layer;
    switch(SUB_CUR_LAYER){
        case sub_layer_pop:
            
        break;
        case sub_layer_video:

        break;
    }
}

function markTNS(type, _tnsId){
    if(_tnsId==null || _tnsId==undefined || _tnsId=="NaN" || _tnsId=="") return;
    var url = new String();
    switch(type){
        case 'player':
            url = 'http://www.tns-counter.ru/V13a****ivi_ru/ru/CP1251/tmsec='+_tnsId+'_player/'
        break;
        case 'playstart':
            url = 'http://www.tns-counter.ru/V13a****ivi_ru/ru/CP1251/tmsec='+_tnsId+'_playstart/';
        break;
        case 'playend':
            url = 'http://www.tns-counter.ru/V13a****ivi_ru/ru/CP1251/tmsec='+_tnsId+'_playend/'
        break;
    }
    if (url) {
        sendreq(url,log,true)
    }
}

function ga(type,ga_id,watch_id){
    var url = '';
    var time = new Date();
    watch_id+=('_'+time.getTime())
    url = 'http://www.google-analytics.com/__utm.gif?utmwv=4.3as&utmn='+time.getTime()+'&utmhn=localhost&utmt=event';
    url+= '&utme=5(Video*'+type+')';
    url+= '&utmcs=UTF-8&utmcs=UTF-8&utmsr='+screen.width+''+screen.height+'&utmsc=24-bit&utmul='+lang+'&utmje=&utmfl=&utmdt=&utmhid='+time.getTime()+'&utmr=-&utmp=&utmac='+ga_id+'&utmcc=__utma%3D';
    url+= (watch_id+'.'+time.getTime()+'.29%3B%2B__utmz%3D'+watch_id+'.'+time.getTime()+'.1.1.');
    url+= 'utmcsr%3D(direct)%7Cutmccn%3D(direct)%7Cutmcmd%3D(none)%3B'
    if(!empty(url)){
        sendreq(url,log,true);
    }
}
/**/

//url = 'http://www.google-analytics.com/__utm.gif
//?utmwv=4.3as //version
//&utmn=1143993741 //random ID
//&utmhn=localhost //host name
//&utmt=event //request type
//&utme=5(Video*Start) //Extensible parameter (custom)
//&utmcs=UTF-8 //charset
//&utmsr=1920x1080 //screen resolution
//&utmsc=24-bit //Screen colour depth
//&utmul=ru //user language
//&utmje=0 //java embeded 0->false 1->true
//&utmfl=10.2%20r153 //flash version
//&utmdt=player //Page title
//&utmhid=371137194 //Hit ID, random number
//&utmr=- //Full referral URL
//&utmp= //Page path
//&utmac=UA-123­456-1 //Account ID
//&utmcc=__utma%3D111872281.1897449158157389800.1308726137.1308741887.1308746675.29%3B%2B__utmz%3D111872281.1308726137.1.1.utmcsr%3D(direct)%7Cutmccn%3D(direct)%7Cutmcmd%3D(none)%3B'

//---Cookie string values
//__utma //visitior cookie
//__utmz //ga cookie
//utmcsr //Campaign source 
//utmcmd //Campaign medium