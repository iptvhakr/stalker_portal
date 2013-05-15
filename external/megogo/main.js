
stb_emul_mode = 1;



function init(){

    back_location = back_location.replace(/\?referrer\=/, '');
	if(stb_emul_mode){

	}
    window.moveTo(0, 0);
    window.resizeTo(win.width, win.height);
    loadStyle(win.height+'.css');
    cont_page_max = vars[win.height].cont_page_x_max*vars[win.height].cont_page_y_max;
    var lang = getEnvironmentValue('language');
    loadScript(lang+'.js','scriptloaded()'); // add lang cheking here
    stb.InitPlayer();
    stb.SetTopWin(0);
    stb.EnableServiceButton(true);
    stb.EnableVKButton(true);
    vars.player_vars.volume = stb.GetVolume();
    vars.player_vars.mute = stb.GetMute();
    device = stb.RDir("Model");
    stb.SetPIG (1,0,0,0);
    stbEvent = {
        onEvent:eventFunc,
        event: 0
    };
    genreLst.maxLength = vars[win.height].seriesLen;
    log('init finished')

    //$('login_page').style.display = 'block';

    //sendreq(iviURL+'categories/', log)
    extSubCatLst.length = vars[win.height].ext_cont_page_x_max;
	extSubCatLst.maxLength = vars[win.height].ext_cont_page_x_max;
	if(win.height == 576){
	/*$('ext_video_layer_0').style.display = 'none';
	$('ext_video_layer_2').style.display = 'none';*/

	}
	else{
		/*extSubCatLst.length*=2;
		extSubCatLst.maxLength *=2;*/
		}

}

var accpass ={'login':'', 'pass':''};

function scriptloaded(){

    for(var i = 0;i<3;i++){
        document.getElementsByClassName('login_text')[i].innerHTML = login_text_arr[i];
    }
    document.getElementsByClassName('btn_div')[0].getElementsByTagName('input')[0].value = auth_btn_ok;
    document.getElementsByClassName('btn_div')[0].getElementsByTagName('input')[1].value = auth_btn_cansel;

    $('login').focus();
     accpass = stb.LoadUserData('megogofile');

    if(empty(accpass))
    accpass ={'login':'', 'pass':''};


    if(!empty(accpass.login)){
        sendreq(iviURL+'login?'+createSign({'login':accpass.login, 'pwd':accpass.pass}),drowheader);
    }else{

    	currLst = catLst;
         currLst.reset();
         $('auth').style.display = 'none';
         $('cats_page').style.display = 'block';

         currLst.onEnter();

    }
    init_pages('',0);
}



function init_pages(layer, count){


	var arr = $(layer+'video_layer');


	while(/*i*/arr.children.length)
	arr.removeChild(arr.children[0]);

	if(empty(count))
		count = cont_page_max+1;

	if(count>cont_page_max+1)
		count = cont_page_max;

    for(var y = 0;y<count;y++){
         var obj = {
            'tag':'div',
            'attrs':{
                'id':layer+'video_p'+y,
                'class':'submenu_item'
            },
            'child':[
            		{
	                    'tag':'div',
	                    'attrs':{
	                        'class':'submenu_cover',
	                    },
                    },

                    {
	                    'tag':'div',
	                    'attrs':{
	                        'class':'submenu_title',
	                    },
                    },

                    {
	                    'tag':'div',
	                    'attrs':{
	                        'class':'submenu_text',
	                    },
                    },

                    {
	                    'tag':'div',
	                    'attrs':{
	                        'class':'submenu_rating',
	                    },
                    }


            ]

 		}



        $(layer+'video_layer').appendChild(createHTMLTree(obj));


    }

     currLst.onChange();
}

var session = '';

submit = 0;



function drowheader(text){
    //console.log(text)
    var authData = JSON.parse(text);


    if(authData.error){
                currLst = authLst;
    		currLst.reset();
                newMyAlert('Ошибка авторизации.','temp');
    }
    else{

    	session = authData.session;
    	for(var i in authData.user.favorites)
			favorites[authData.user.favorites[i]] = 1;

		if(accpass.login == $('login').value)
  			stb.SaveUserData('megogofile','{"login":"'+$('login').value+'","pass":"'+utf8_to_b64($('password').value)+'"}')



         /*if(currLst != authLst)*/{
         	$('auth').style.display = 'none';
         $('cats_page').style.display = 'block';
         currLst = catLst;
         currLst.reset();
         	currLst.onEnter();
         }
         //log("1");
    }
}



function drow_cats(responseCats){
cats_obj = {"result":"ok","category_list":[{"id":"16","title":"\u0424\u0438\u043b\u044c\u043c\u044b","total_num":"2829"},{"id":"4","title":"\u0421\u0435\u0440\u0438\u0430\u043b\u044b","total_num":"444"},{"id":"6","title":"\u041c\u0443\u043b\u044c\u0442\u0444\u0438\u043b\u044c\u043c\u044b","total_num":"398"},{"id":"9","title":"\u041f\u0435\u0440\u0435\u0434\u0430\u0447\u0438 \u0438 \u0448\u043e\u0443","total_num":"70"},{"id":"17","title":"\u041d\u043e\u0432\u043e\u0441\u0442\u0438","total_num":"96"}]};
    //cats_obj = JSON.parse(responseCats);
    byclass('cat_item_text')[0].innerHTML = "Рекомендуем";
    for(var i = 1;i < cats_obj.category_list.length;i++){
        byclass('cat_item_text')[i].innerHTML = cats_obj.category_list[i-1].title;
    }
    switchLayer(layer_cats);
    pop_layer = true;

    //sendreq(iviURL+'videos?'+createSign({'category':cats_obj.category_list[0]['id'], 'sort':'popular', 'session':session, 'offset':vars.content_page*cont_page_max, 'limit':cont_page_max+1}),init_contentlist);


}






function sesies_getdata(id){

    //CUR_LAYER = layer_info;
    urlVideo = {'video':id, 'session':session/*, 'bitrate':win.height*/}

    if(fileInfo.isSeries){
    	season = file.video[0].season_list[seasonLst.pos].id;
    	if(!empty(episodeLst.idLst[season][episodeLst.pos])){
    		urlVideo['episode'] = episodeLst.idLst[season][episodeLst.pos];
    		urlVideo['season'] = season;
    	}
    }

    if(seriesLst.bitrates[bitratesLst.pos] != undefined)
    	//if(seriesLst.bitrates[bitratesLst.pos] != '')
    	urlVideo['bitrate'] = seriesLst.bitrates[bitratesLst.pos].id;



     sendreq(iviURL+'info?'+createSign(urlVideo),start_playing1);

}

function initGenriesPage(cat, page){

	var  lst = genreList[cat];

	var arr = $('genres_id');

    while(arr.childElementCount>1)
    	arr.removeChild(arr.children[1]);

    genreLst.id = new Array();
    genreLst.length = 0;

    tmpLst = [];

    for(var i=page*genreLst.maxLength; i< (page+1)*genreLst.maxLength; i++){

    		if(i>=lst.length)
    			break;

    		tmpLst.push(lst[i].id);

	    	var obj = {
	            'tag':'div',

	            'attrs':
            	{
                	'class':'submenu_genres_item',
                	'id': 'genres_item_'+i/*(i-page*genreLst.maxLength)*/
            	},

	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'submenugenres_title',
		                	'html':lst[i].title
		                }

           			}


	            ]

	 		}

	 		$('genres_id').appendChild(createHTMLTree(obj));
	 		genreLst.id.push(i);
	 		genreLst.length++;
	 	}

	 	 //if(empty(episodeLst.idLst[season]))
	 	// episodeLst.idLst[file.video[0].season_list[seasonLst.pos].id] = tmpLst;


}

function init_genreLst(text){

//currLst = genreLst;
//currLst.onChange();

	genreListTmp =  JSON.parse(text);
	if(genreListTmp.genre_list != undefined)
		genreListTmp = genreListTmp.genre_list;

	cat = vars.catID[catLst.pos];

	genreList[cat] = genreListTmp;

	initGenriesPage(cat, 0);

	currLst = genreLst;
	currLst.reset();
	$('genres_item_'+genreLst.id[genreLst.pos]).className ="submenu_genres_item";

	if(fl_timeoutID){
		fl_timeoutID = 0;
		currLst.onExit();
	}
	else
		currLst.onChange();

	$('submenu_genres').style.display = 'block';


}


function getHTMLRating(ds){

	res = '';

	res+='<div class="stripes_horizontal_country">';


                    generalRate = 0;

                    if(ds.rating_kinopoisk)
                        generalRate = parseFloat(ds.rating_kinopoisk);
                    else
                    if(ds.rating_imdb)
                        generalRate = parseFloat(ds.rating_imdb);




                    generalRate = generalRate.toFixed(2);

                    if(win.height == 576)
                    	res += '<div class="submenu_genre_rating">';
                    else
                    	res += '<div class="submenu_rating">'

res += '<div class="stripes_genres_horizontal_rating"><div class="stripes_genres_horizontal_rating_act" style="width: '+(generalRate*10)+'px; "></div></div>';
res +='</div>';




	if(ds.year != undefined)
 		res += ds.year;

 	if(!empty(ds.country)){

                if(!empty(ds.year))
                    res +=', ';

                if(!empty(countries[ds.country]))
                    res += countries[ds.country];
                else res += ds.country;

            }

       res += '</div>';


					return res;
}


function initHorizontalList(list, offset){



	layer = 'ext_';

	/*var arr = $(layer+'video_layer');

	while(arr.children.length)
		arr.removeChild(arr.children[0]);


	arr = $(layer+'video_layer_0');

	while(arr.children.length)
		arr.removeChild(arr.children[0]);


	arr = $(layer+'video_layer_2');

	while(arr.children.length)
		arr.removeChild(arr.children[0]);*/




    var lst = extSubCatLst;
   // lst.length = 0;

    var c = extSubCatLst.maxLength;

    var ds = [];

    var i = 0;

    i = offset;//-c;


    while(ds.length < c*3){

    	if(i<0){
    	i++;
    		continue;
    		}

    	if(i >= list.length)
    	break;

    	if(ds.length >= list.length)
    		break;

    	ds.push(list[i++]);

    }

   // ds = list;

  i = 0;

    var tmpFields = '';
    tmpFields += '<div class="stripehorizontal_counter" id="stripehorizontal_counter">'+extSubCatLst.pos*extSubCatLst.page +'из'+ list.length+'<div class="stripehorizontal_counter_bottom"></div></div>'
       // if(offset>=c )
        {
    	for(var i = 0; i<c; i++){

    		if(i>=ds.length)
				break;

    		tmpFields += '<div class="stripes_horizontal_box" id="'+layer+'video_p'+i+'"><div class="stripes_cover">';

    		if(ds[i].poster != undefined)
    			tmpFields += '<img src="'+'http://megogo.net'+ds[i].poster+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'"/></div>';

    		tmpFields += '<div class="stripes_horizontal_title">'+ds[i].title+'</div>';


    		tmpFields+=getHTMLRating(ds[i]);

    		tmpFields += '</div>';

    	}
        }
    	$(layer+'video_layer_0').innerHTML = tmpFields;




    	tmpFields = '';

//    	tmpFields += '<div class="stripehorizontal_counter" id="stripehorizontal_counter">'+extSubCatLst.pos*extSubCatLst.page +'из'+ list.length+'<div class="stripehorizontal_counter_bottom"></div></div>'
    	tmpFields += '<div class="stripeshorizontal_left"></div>'

		//var i = 0;

		if(offset>=c )
			i = c;

		j=i;

    	for(;i<j+c;i++){

			if(i>=ds.length)
				break;

    		tmpFields += '<div class="stripes_horizontal_box" id="'+layer+'video_p'+i+'"><div class="stripes_cover">';
    		if(ds[i].poster != undefined)
    			tmpFields += '<img src="'+'http://megogo.net'+ds[i].poster+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'"/></div>';

    		tmpFields += '<div class="stripes_horizontal_title">'+ds[i].title+'</div>';


    		tmpFields+=getHTMLRating(ds[i]);

    		tmpFields += '</div>';



    	}

    	tmpFields += '<div class="stripeshorizontal_right"></div>';

    	$(layer+'video_layer').innerHTML = tmpFields;


     /*   tmpFields = '';

        i = c;

        if(offset>=c )
			i = c*2;

		j=i;

    	for(;i<j+c;i++){

    		if(i>=ds.length)
				break;


    		if(ds[i].poster != undefined){
    		tmpFields += '<div class="stripes_horizontal_box" id="'+layer+'video_p'+i+'"><div class="stripes_cover">';
    			tmpFields += '<img src="'+'http://megogo.net'+ds[i].poster+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'"/></div>';


    		tmpFields += '</div>';
    		}



    	}

    	$(layer+'video_layer_2').innerHTML = tmpFields;
    	$(layer+'video_layer_2').style.display = 'none';
*/

//$(layer+'video_layer_0').style.display = 'none';
$(layer+'video_layer_2').style.display = 'none';
    	currLst.onChange();

    	return 1;

}


function fill_contentlist(ds1, layer){

var lst = subCatLst;


    if(currLst == movieInfoLst)
    	lst = proposalMovieLst;

    lst.length = 0;
                     //currLst.dsPos = currLst.page*currLst.maxLength + currLst.pos;
    var start = (subCatLst.page-subCatLst.dsPos)*cont_page_max/*+1*/;
    var ds = ds1.slice(start, start+(cont_page_max+1));


 //   ds1+currLst.page*currLst.maxLength + currLst.pos-currLst.dsPos;
    for(var i = 0;i<=cont_page_max;i++){
        if(ds[i]){
        	lst.length++;
        	$(layer+'video_p'+i).style.display = 'block';//.innerHTML = '';
            if(ds[i].poster){
                $(layer+'video_p'+i).getElementsByClassName('submenu_cover')[0].innerHTML = '<img src="'+'http://megogo.net'+ds[i].poster+'" width="'+vars[win.height].poster_width+'" height="'+vars[win.height].poster_height+'" align="left"  style="margin-right:2px;"/>';
            }
            else $(layer+'video_p'+i).getElementsByClassName('submenu_cover')[0].innerHTML = '';


            $(layer+'video_p'+i).getElementsByClassName('submenu_title')[0].innerHTML = ds[i].title;

            if(ds[i].year != undefined){
                $(layer+'video_p'+i).getElementsByClassName('submenu_text')[0].innerHTML = ds[i].year;
            }


            if(!empty(ds[i].country)){

                if(!empty(ds[i].year))
                    $(layer+'video_p'+i).getElementsByClassName('submenu_text')[0].innerHTML +=', ';

                if(!empty(countries[ds[i].country]))
                    $(layer+'video_p'+i).getElementsByClassName('submenu_text')[0].innerHTML += countries[ds[i].country];
                else $(layer+'video_p'+i).getElementsByClassName('submenu_text')[0].innerHTML += ds[i].country;

            }

            try{
            	for(var y = 0;y<ds[i].genre_list.length;y++){
                            $(layer+'video_p'+i).getElementsByClassName('submenu_text')[0].innerHTML +=', '+ds[i].genre_list[y]['title']
             	}
      		} catch(e) {}



                    generalRate = 0;
                   	  if(ds[i].rating_kinopoisk)
                        generalRate = parseFloat(ds[i].rating_kinopoisk);
                    else
                    if(ds[i].rating_imdb)
                        generalRate = parseFloat(ds[i].rating_imdb);

                    generalRate = generalRate.toFixed(2);


            rate = '';
           /* for(j=0; j<generalRate; j++){

            	//rate+='<img src="./img/sptr.jpg" align="left"  style="margin-right:5px;"/>';

            }*/

            rate='<div class="stripes_horizontal_rating"><div class="stripes_horizontal_rating_act"></div>'+generalRate+'</div></div>';
            //$(layer+'video_p'+i).getElementsByClassName('submenu_rating')[0].getElementsByClassName('stripes_horizontal_rating_act')[0].style.width = generalRate*10+'px';

            var arr = $(layer+'video_p'+i).getElementsByClassName('submenu_rating')[0].innerHTML = rate;
             $(layer+'video_p'+i).getElementsByClassName('submenu_rating')[0].getElementsByClassName('stripes_horizontal_rating_act')[0].style.width = generalRate*10+'px';

        }else{

            $(layer+'video_p'+i).style.display = 'none';//.innerHTML = '';
        }
    }//}

    window.setTimeout(function(){switchSubLayer(sub_layer_video);},180);

    if(layer == 'alt_')
    	proposalMovieLst.dataset = ds;

    else dataset = ds;

    //if (lst == subCatLst && lst.length>3)
    //lst.length = 3;

   // ds.splice( 0, cont_page_max );
   // currLst.ds = ds;



}



function init_contentlist(text, layer){

	if (currLst ==extSubCatLst)
	       layer = 'ext_';

    //console.log(text);
    if(empty(layer))
    	layer = '';

    var ds;

    if(typeof(text) != 'object')
    	ds = JSON.parse(text);
    else ds = text;

	if(ds.video_list != undefined){
    	ds = ds.video_list;

    	if(empty(ds)&&catLst.pos == 5&&(currLst==subCatLst /*|| currLst==subCatLs*/)){
		    	if (currLst.page==0){
		    		$('video_layer').style.display = "none";
		    		currLst = catLst;
		    		if(currLst.prevPos<currLst.pos)
		    			currLst.prev();
					else currLst.next();
				}
				else currLst.prev();

    			return;
    	}
         if (currLst ==extSubCatLst){

         	if(empty(ds)){
         		currLst.onExit();
				return;
			}

         	var d = extSubCatLst.maxLength;
         	//if (ds.length > d*3)
         	//ds.splice(0,ds[ds.length-4], ds[ds.length-3], ds[ds.length-2], ds[ds.length-1]);

         	extSubCatLst.list = ds;
    		initHorizontalList(extSubCatLst.list, 0);
    		var k = extSubCatLst.houndres;

    		if(extSubCatLst.direct == 'prev')
    			k--;
    		else
    			if(extSubCatLst.direct == 'next')
    		k++;

    		extSubCatLst.reset();
    		extSubCatLst.houndres = k;
    		extSubCatLst.onChange();

    		return;
         }
    	 else init_pages(layer,ds.video_list);

    }

    subCatLst.dsPos = subCatLst.page;

    fill_contentlist(ds, layer);

    currLst.ds = ds;

}


function next_cont_page(text){

	init_contentlist(text);
    $('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).className = 'submenu_item_act_'+vars.menu_items[ vars.catSel];
    //$('video_p'+(vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max))).getElementsByClassName('movie_desc')[0].style.display = 'block';

}

function initBitratesLst(bitrates){

var arr = $('bitrates_id');

while(arr.childElementCount>1)
arr.removeChild(arr.children[1]);
bitratesLst.length = 0;


if(!empty(seriesLst.bitrates)){

	for(var i in seriesLst.bitrates){

            seriesLst.bitrates[i].id = seriesLst.bitrates[i].name.substr(strpos(seriesLst.bitrates[i].name,'(',0)+1);
            seriesLst.bitrates[i].id = str_replace(seriesLst.bitrates[i].id,')','');


	    	var obj = {
	            'tag':'div',

	            'attrs':
            	{
                	'class':'submenu_series_item',
                	'id': 'bitrates_item_'+i
            	},

	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'submenuseries_title',
		                	'html':seriesLst.bitrates[i].name
		                }

           			}


	            ]

	 		}

	 		$('bitrates_id').appendChild(createHTMLTree(obj));
	 		bitratesLst.length++;

	}

}



}



function initSeriesLst(season, page){

if (page == undefined)
page =0;
 var arr = $('series_id');
   log('121');
 episodeLst.maxLength = vars[win.height].seriesLen;


    	while(arr.childElementCount>1)
    		arr.removeChild(arr.children[1]);

    	episodeLst.id = new Array();
    	episodeLst.length = 0;

    	tmpLst = [];
       log('122');
        log(page*episodeLst.maxLength);
        log(file.video[0].season_list[season].episode_list.length);
    	for(var i=page*episodeLst.maxLength; i< file.video[0].season_list[season].episode_list.length; i++){
              log('123');
            if(i==(page+1)*episodeLst.maxLength)
            	break;
            //	episodeLst.idLst[season][0] = file.video[0].season_list[season].episode_list[i].id;
    		//else episodeLst.idLst[season].push(file.video[0].season_list[season].episode_list[i].id);

    		title = file.video[0].season_list[season].episode_list[i].title;

    		if(file.video[0].season_list[season].episode_list[i].title.length>vars[win.height].seasonTextLen)
    			title = file.video[0].season_list[season].episode_list[i].title.slice(0,vars[win.height].seasonTextLen)+'...';



    		tmpLst.push(file.video[0].season_list[season].episode_list[i].id);

	    	var obj = {
	            'tag':'div',

	            'attrs':
            	{
                	'class':'submenu_series_item',
                	'id': 'episode_item_'+(i-page*episodeLst.maxLength)
            	},

	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'submenuseries_title',
		                	'html':title
		                }

           			}


	            ]

	 		}

	 		$('series_id').appendChild(createHTMLTree(obj));
	 		episodeLst.id.push(i);
	 		episodeLst.length++;
	 	}
         log('124');
	 	 //if(empty(episodeLst.idLst[season]))
	 	 episodeLst.idLst[file.video[0].season_list[seasonLst.pos].id] = tmpLst;
}


function initSeasonLst(page){

if (page == undefined)
page =0;
 var arr = $('season_id');

 seasonLst.maxLength = vars[win.height].seriesLen;


    	while(arr.childElementCount>1)
    		arr.removeChild(arr.children[1]);

    	seasonLst.id = new Array();
    	seasonLst.length = 0;

    	tmpLst = [];

    	for(var i=page*seasonLst.maxLength; i< file.video[0].season_list.length; i++){

            if(i==(page+1)*seasonLst.maxLength)
            	break;



    		tmpLst.push(file.video[0].season_list[i].id);

	    	var obj = {
	            'tag':'div',

	            'attrs':
            	{
                	'class':'submenu_series_item',
                	'id': 'season_item_'+(i-page*seasonLst.maxLength)
            	},

	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'submenuseries_title',
		                	'html':file.video[0].season_list[i].title
		                }

           			}


	            ]

	 		}

	 		$('season_id').appendChild(createHTMLTree(obj));
	 		seasonLst.id.push(i);
	 		seasonLst.length++;
	 	}

	 	 //if(empty(episodeLst.idLst[season]))
	 	 season_list.idLst[file.video[0].season_list[seasonLst.pos].id] = tmpLst;
}


function switchMovieInfo(lst){

  $('movie_info_poster').src = 'http://megogo.net'+lst.dataset[lst.offset].poster;
  $('movie_info_poster').setAttribute('width',vars[win.height].infoposter_width);
  $('movie_info_poster').setAttribute('height',vars[win.height].infoposter_height);

    seriesLst.bitrates = [];




	//bitratesLst.idLst = [{}];

	fileInfo = lst.dataset[lst.offset];


  $('info_title').innerHTML = fileInfo.title;

  var genre_str = '';

  if(!empty(fileInfo.year))
    genre_str += fileInfo.year;


                    if(!empty(fileInfo.country)){
                        if(!empty(fileInfo.year))
                            genre_str +=', ';

                        if(!empty(countries[fileInfo.country]))
                            genre_str += countries[fileInfo.country];
                        else genre_str += fileInfo.country;

                    }

                    byclass('movieinfo_block')[0].innerHTML = genre_str;

                    genre_str = '';

                    if(!empty(fileInfo.genre_list)){
                        for(var y = 0;y<fileInfo.genre_list.length;y++){
                        	if(!empty(genre_str))
                        		genre_str+=', ';
                            genre_str +=fileInfo.genre_list[y]['title'];
                        }
                    }


                              // $('director_id').innerHTML = 'Режиссер: <div class="movieinfo_cast_actor">'+file.video[0].people[i].title+'</div>';
                      if(!empty(fileInfo.duration)){   duration =fileInfo.duration/60; duration = duration - (duration%1);
            $('movieinfo_block').innerHTML +=  '<span class="movieinfo_time">'+duration+'мин.</span>';
            }

                    $('movie_info_genre_id').innerHTML = genre_str;


                    obj = $('descr');

                   	tmp = new Array();


                    if(fileInfo.rating_imdb){
                    	tmp.push('');
                        tmp.push('IMDB: '+fileInfo.rating_imdb);

                    }

                    if(fileInfo.votes_imdb != undefined && !empty(fileInfo.votes_imdb)){
                        tmp.push(' ('+fileInfo.votes_imdb+')');
                    }

                    if(fileInfo.rating_kinopoisk)
                        tmp.push('<br>Кинопоиск: '+fileInfo.rating_kinopoisk);

                    if(fileInfo.votes_kinopoisk)
                        tmp.push(' ('+fileInfo.votes_kinopoisk+')');

					rates = arrToSpecStr(tmp, '', '', '');

					if(!empty(rates))
						$('movieinfo_cast_rating').innerHTML = rates;

					if(!empty(fileInfo.additional_info)){
						$('movie_info_actor_id').style.display = "block";
						$('movie_info_actor_id').children[0].innerHTML = fileInfo.additional_info ;
					}
					else
						$('movie_info_actor_id').style.display = "none";


                    obj.innerHTML = '<br>'+fileInfo.description;


                    if(fileInfo.isSeries == 0){
                        $('info_btn_0').innerHTML = 'Воспроизвести';
                        sendreq(iviURL+'video?'+createSign({'video':/*lst.dataset[vars.cont_page_x+(vars.cont_page_y*vars[win.height].cont_page_x_max)].id*/lst.dataset[lst.offset].id, 'session':session}),init_info);
                    }else{
                        $('info_btn_0').innerHTML = 'Список серий';
                        sendreq(iviURL+'video?'+createSign({'video':lst.dataset[lst.offset].id, 'session':session}),init_info);
                    }
                //}
                //console.log($('info_page').innerHTML);

                $('info_5').innerHTML = 'Оценить';

}


function init_info(text){
    //console.log(text);
    file = JSON.parse(text);
    movieInfoLst.reset();
    if(file.isSeries){
    seasonLst.reset();
    initSeriesLst(seasonLst.pos, 0);
    episodeLst.reset();
    }
    //seriesLst.reset();
    //proposalMovieLst.reset();



    if(!empty(favorites[fileInfo.id]))
    	$('info_3').innerHTML = 'Из избранного';
    else $('info_3').innerHTML = 'В избранное';



    if(!empty(file.video[0].people[0])){

		var arr = $('movieinfo_actors_id');


    	while(arr.childElementCount>3)
    		arr.removeChild(arr.children[3]);


      var actors = new Array();
    	for(var i in file.video[0].people){

        if(empty(actors[file.video[0].people[i].type_title]))
          actors[file.video[0].people[i].type_title] =  file.video[0].people[i].title + ',';
        else
          actors[file.video[0].people[i].type_title] +=  file.video[0].people[i].title + ',';



	    if(file.video[0].people[i].type == 'DIRECTOR')
	      $('director_id').getElementsByClassName('movieinfo_cast_actor')[0].innerHTML = file.video[0].people[i].title;
	      }

	      for(var i in actors){
	      	actors[i] = actors[i].substring(0,actors[i].length-1);
	      	if(strpos( actors[i], ',', 0)!= false)
	      	j = i+'ы';
	      else j = i;

	       // if(actors[i)
		      $('movieinfo_actors_id').innerHTML +='<div class="movieactors">'+j+'<div class="movieactors_cast">'+actors[i]+'</div></div>';
		    }

	}




	if(!empty(file.video[0].comment_list[0])){

		var arr = $('movieinfo_comments_id');

    	while(arr.childElementCount>3)
    		arr.removeChild(arr.children[3]);

        var tmp = '<div class="movieinfo_menuitem_shadow"></div><div class="movieinfo_gradient_top"><div class="arrow_top_gray"></div></div>';
        tmp += '<div class="movieinfo_gradient_bottom"><div class="arrow_bottom_blue"></div></div>';

    	for(var i in file.video[0].comment_list){

    		tmp += '<div><div class = "moveinfo_avatar"><img src = "'+'http://megogo.net'+file.video[0].comment_list[i].user_avatar+'"></div><div class = "movieinfo_comment"><div class="movieinfo_username">'+file.video[0].comment_list[i].user_name+'</div>'+file.video[0].comment_list[i].text+'</div></div>';

	    	/*var obj = {
	            'tag':'div',
	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'movieinfo_comment',
		                	'html':file.video[0].comment_list[i].text
		            	},
		            	'child':
		            	[
			            	{
				            	'tag':'div',
				            	'attrs':
				            	{
				                	'class':'movieinfo_username',
				                	'html':file.video[0].comment_list[i].user_name
				                }
			            	}

		            	]
           			},

           			{
	            		'tag':'div',
		            	'attrs':
		            	{
		                	'class':'movieinfo_avatar'
		            	},
		            	'child':
		            	[
		            		{
		            			'tag':'img',
				            	'attrs':
				            	{
				                	'src': 'http://megogo.net'+file.video[0].comment_list[i].user_avatar
				            	}
		            		}

		            	]


	            	}


	            ]

	 		}
*/


	       // $('movieinfo_comments_id').appendChild(createHTMLTree(obj));
	    }
	}

	 $('movieinfo_comments_id').innerHTML = tmp;


 	var arr = $('season_id');

    while(arr.childElementCount>1)
    	arr.removeChild(arr.children[1]);

    seasonLst.id = new Array();
    seasonLst.length = 0;

    var title;

 	if(!empty(file.video[0].season_list[0])){

    	for(var i/*=0;i<5;i++*/ in file.video[0].season_list){

    		title = file.video[0].season_list[i].title;

    		if(file.video[0].season_list[i].title.length>vars[win.height].seasonTextLen)
    			title = file.video[0].season_list[i].title.slice(0,vars[win.height].seasonTextLen)+'...';



	    	var obj = {
	            'tag':'div',

	            'attrs':
            	{
                	'class':'submenu_series_item',
                	'id': 'season_item_'+i
            	},


	            'child':
	            [

           			{
            			'tag':'div',
		            	'attrs':
		            	{
		                	'class':'submenuseries_title',
				            'html':   title

				            //file.video[0].season_list[season].title = file.video[0].season_list[season].title.slice(0,vars[win.height].episodeTexLen);
		            	},
           			}


	            ]

	 		}

	 		$('season_id').appendChild(createHTMLTree(obj));
	 		seasonLst.id.push(i);
	 		seasonLst.length++;
	 	}

        initSeriesLst(0);
	}



      //initSeasonLst(0);

    //lst.prototype.prevLst=NULL;

        currLst = movieInfoLst;
	currLst.onChange();
    //switchLayer(layer_info);
}


function getStreamInfo(text){
	file1 = JSON.parse(text);
	seriesLst.bitrates = file1.bitrates;
	initBitratesLst(seriesLst.bitrates);
	currLst.onChange();

}

function start_playing1(text){
	playlist = JSON.parse(text);
	seriesLst.bitrates = playlist.bitrates;
	initBitratesLst(seriesLst.bitrates);
    $('video_title').innerHTML = playlist.title;
    switchLayer(layer_player);
    $('footer').style.display = 'none';
    if(stb_emul_mode == 1){
        CUR_LAYER = 1; PREV_LAYER = 5;
        currLst = seriesLst;
        $('info_page').style.display = 'none';
        $('menu_series').style.display = 'block';
        currLst.reset();
    }
    else {
        if (_GET.hasOwnProperty('proxy')){
            stb.Play('ffmpeg ' + playlist.src, _GET['proxy']);
        }else{
            stb.Play('ffmpeg ' + playlist.src);
        }
        $('player_page').style.display = 'block';
        show_waiting();
    }

}

function get_video_data(text){
    file = JSON.parse(text);
    $('video_title').innerHTML = file.result.title;

    switchLayer(layer_player);
    stb.Play('ffmpeg '+file.result.files[0].url);
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



function get_suggest(){ // /p/searchsuggest?text=<text>&[category=<cat_id>&genre=<genre_id>&session=<session_id>]&sign=<sign>
	if(empty($('search_line').value))
		return;

 url = {'text':$('search_line').value};


 sendreq(megogoURL+'p/search?'+createSign({'text':$('search_line').value}), build_suggest);  return;

    var url = iviURL+'autocomplete/v2/?query=';
        url+=$('search_line').value;

    sendreq(url,build_suggest,true);
}

function build_suggest(text){
    var sug_obj = JSON.parse(text);
    log(text);
    searchLst.onEnter(sug_obj);return;

}

function send_search_req(){
/*    var url = iviURL+'search/v2/?query=';
    url+=$('search_line').value;
    if($('search_cats').value!=0){
        url+='&category='+$('search_cats').value
    }
    if($('search_country').value!=0){
        url+='&country='+$('search_country').value
    }
    sendreq(url,search_answer,true);*/
}

function search_answer(text){
 /*   console.log(text);
    search_obj = JSON.parse(text);
    if(search_obj.length){
        exit_search();
        init_contentlist(text)
    }else{
        newMyAlert("Не найдено", 'temp')
    }*/
}

function exit_search(){
/*    document.getElementsByClassName('close_btn')[0].innerHTML = '<input type="button" class="genrebtn" value="Закрыть" onclick="exit_modal()"/>';
    suggest_active = false;
    suggest_count = 0;
    suggest_focus = -1;
    CUR_LAYER = layer_cats;
    document.getElementsByClassName('modal')[0].style.display = 'none';
    $('search_layer').innerHTML = '';*/
}

function exit_modal(){

    document.getElementsByClassName('modal')[0].style.display = 'none';
    document.getElementsByClassName('modal_list')[0].innerHTML = '';
     //log('+++exit_modal+++'+CUR_LAYER);
    CUR_LAYER = PREV_LAYER;
    //log('+++exit_modal+++'+CUR_LAYER);
    layer_indexes.active[layer_genre]=0;
}

function show_waiting(){
    log('===== show_waiting ======');
    newAlert_on = true;
    var arr = document.getElementsByClassName('modal_box');

    while(arr[0].childElementCount)
    	arr[0].removeChild(arr[0].children[0]);

    var obj = document.createElement('div');
	obj.className = 'modal_login';
	obj.innerHTML = 'Loading...';

	document.getElementsByClassName('modal_box')[0].appendChild(obj);
	document.getElementsByClassName('modal')[0].style.display = 'block';

	window.setTimeout(function(){hide_waiting()}, 5000);

}

function show_waiting1(str){
    log('==== show_waiting1 ====');
    newAlert_on = true;
    var arr = document.getElementsByClassName('modal_box');

    while(arr[0].childElementCount)
    	arr[0].removeChild(arr[0].children[0]);

    var obj = document.createElement('div');
	obj.className = 'modal_login';
	obj.innerHTML = str;

	document.getElementsByClassName('modal_box')[0].appendChild(obj);
	document.getElementsByClassName('modal')[0].style.display = 'block';

	window.setTimeout(function(){hide_waiting()}, 5000);

}

function hide_waiting(){
    log('==== hide_waiting ====');
    newAlert_on = false;
    document.getElementsByClassName('modal')[0].style.display = 'none';
    // log('+++hide_waiting+++'+CUR_LAYER);
     if(CUR_LAYER != 5)
    CUR_LAYER = 1;
   //  log('+++hide_waiting+++'+CUR_LAYER);
}

function switchLayer(layer){
    log('Switch layer from <'+layers[CUR_LAYER]+'> to <'+layers[layer]+'>');
    switch(CUR_LAYER){
        default:

        break;
        case layer_auth:

        break;
        case layer_player:
            //clearInterval(runner_timer);
            //clearTimeout(pos_timer);
            //clearTimeout(setpos_timer);
        break;
    }
    $(layers[CUR_LAYER]).style.display = 'none';
    $(layers[layer]).style.display = 'block';
    PREV_LAYER = CUR_LAYER;
    CUR_LAYER = layer;
    switch(layer){
        default:
           // document.body.style.background = 'url(img/'+win.height+'/bg.jpg)';
        break;
        case layer_auth:
            $('login').focus();
            //document.body.style.background = 'url(img/'+win.height+'/bg.jpg)';
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

function setFavorits(text){

	res = JSON.parse(text);
	var d =  fileInfo['id'];
	if(res.result == 'ok')
			if(!empty(favorites[d]))
			{
				$('info_3').innerHTML = 'В избранное';
				favorites.splice(d,1);
				//if(!empty(favorites[file.video[0].id]))
			}
			else{
				$('info_3').innerHTML = 'Из избранного';
				favorites[d] = 1;
			}

	flFavorUpdate = 1;
	//sendreq(megogoURL+'p/favorites?'+createSign({'session':session, 'limit':100}), init_contentlist);
    //catLst.initialisated = vars.catID[catLst.pos];
  	//subCatLst.reset();

	sendreq(iviURL+'favorites?'+createSign({'session':session}),getFavorits);
	//file = JSON.parse(text);
}

function getFavorits(text){

	var file = JSON.parse(text);
	favorites = [];
	for(var i=0; i< file.video_list.length; i++)
		favorites[file.video_list[i].id] = 1;
}


function setLike(text){
 res = JSON.parse(text);
	if(res.result == 'ok')
	$('info_5').innerHTML = 'Оценки: +'+res.like+'/-'+res.dislike;//$('movieinfo_submenu').children[likeLst.pos].innerHTML;

}

function finish(text){
log(text);
 res = JSON.parse(text);


}

