
////////////
lst =   function(){

this.pos=0;
this.prevPos=0;

}
lst.prototype.length=0;
lst.prototype.page=0;
lst.prototype.prevLst=0;
lst.prototype.id=0;
//lst.prototype.leftSide=1;
//lst.prototype.level=0;
//lst.prototype.maxLevel=0;




lst.prototype.refreshPage = function(){}
lst.prototype.next = function(){

	this.prevPos = this.pos;

	if(this.pos < this.length-1)
		this.pos++;
	else{
		this.page++;
		this.pos = 0;
		this.overflow();

	}

	this.onChange();
}

lst.prototype.prev = function(){

	this.prevPos = this.pos;

	if(this.pos )
		this.pos--;
	else{
		if(this.length)
			this.pos = this.length-1;

		this.page--;
		this.overflow();

	}

	this.onChange();
}


lst.prototype.reset = function(){

    this.prevPos = this.pos;
    this.page = 0;
	this.pos = 0;
	this.onChange();
}

lst.prototype.overflow = function(){

}

lst.prototype.onChange = function(){}
lst.prototype.onEnter = function(){

	/*if(this.level < this.maxLevel)
		this.level++;*/

	this.onChange();

}

//////
/*settingsLst = new lst();*/
likeLst = new lst();
catLst = new lst();
catLst.length = 8;
catLst.id = 7;
catLst.initialisated = -1;
catLst.onChange = function(){

	 $('cat_'+this.prevPos).className = str_replace($('cat_'+this.prevPos).className, '_act', '');
	 $('cat_'+this.pos).className = str_replace($('cat_'+this.pos).className, '_act', '');
	 $('cat_'+this.pos).className = $('cat_'+this.pos).className+ '_act';

}


catLst.onExit = function(){

	currLst.onChange();
}



catLst.onEnter = function(){

		switch(this.pos){
		case 0:
		case 1:
		case 2:
		case 3:
		case 4:
				//refreshContentList();
				if(this.initialisated != vars.catID[catLst.pos]){
                    subCatLst.reset();
					//sendreq(megogoURL+'p/videos?'+createSign({'category':vars.catID[catLst.pos], 'session':session, 'offset':0, 'limit':cont_page_max+1}), init_contentlist);
					if(!catLst.pos)
        				sendreq(megogoURL+'p/recommend?'+createSign({'session':session, 'offset':subCatLst.page*cont_page_max, 'limit':cont_page_max+1}), init_contentlist);
			        else
						sendreq(megogoURL+'p/videos?'+createSign({'category':vars.catID[catLst.pos], 'session':session, 'offset':subCatLst.page*cont_page_max, 'limit':cont_page_max+1}), init_contentlist);

  					subCatLst.initialisated = -1;

  				}
  				//else{
  				//this.initialisated = vars.catID[catLst.pos];
  				if(this.initialisated != -1){
  					currLst = subCatLst;
  					currLst.onChange();

  				}
  				this.initialisated = vars.catID[catLst.pos];
  				//}


    	break;
    	case 5:
    		sendreq(megogoURL+'p/favorites?'+createSign({'session':session, 'limit':100}), init_contentlist);
    		this.initialisated = vars.catID[catLst.pos];
    		currLst = subCatLst;
  				currLst.reset();
    		//sendreq(megogoURL+'p/lastview?'+createSign({'session':session}),init_contentlist);
    		//sendreq(iviURL+'lastview?'+createSign({'session':session}),init_contentlist);

        break;


		case 6:
		        currLst = searchLst;
		        currLst.pos = 0;
          		//init_search_page();
          		currLst.reset();

		break;

		case 7:
		        currLst = authLst;
		       /* currLst.pos = 0;
		        $('submenu').style.display = 'none';
		        $('submenu_settings').style.display = 'block';
		        $('cats_page').style.display = 'none';

		        $('submenu_settings').style.margin = '400px auto';*/
          		//init_search_page();
          		currLst.reset();

		break;


	}



}
//////////////
subCatLst = new lst();
subCatLst.length = 0;
subCatLst.maxLength = 3;
subCatLst.initialisated = -1;
subCatLst.layers = ['cats_page'];


subCatLst.onChange = function(){
     if(this.length > this.maxLength)
		this.length = this.maxLength;

	if(!this.page)
		$('submenu').getElementsByClassName('submenu_shadow_top')[0].style.display = 'none';
	else
		$('submenu').getElementsByClassName('submenu_shadow_top')[0].style.display = 'block';

	 $('video_p'+this.prevPos).className = 'submenu_item';
	 if(currLst == this)
	 	$('video_p'+this.pos).className = 'submenu_item_act_'+vars.menu_items[catLst.pos];

}


subCatLst.overflow = function(){


    var tmp = 0;


		 if(/*this.page*3>=dataset.length*/this.length<3 && this.prevPos>this.pos){

			    this.page--;
			    tmp=1;
			    }


		if(this.page <0){
		this.page = 0;
		   tmp=1;
		}

		if(!tmp)
		if(!catLst.pos)
        	sendreq(megogoURL+'p/recommend?'+createSign({'session':session, 'offset':this.page*cont_page_max, 'limit':cont_page_max+1}), init_contentlist);
        else
        if(catLst.pos == 5)
        sendreq(megogoURL+'p/favorites?'+createSign({'session':session, 'offset':this.page*cont_page_max, 'limit':cont_page_max+1}), init_contentlist);

        else
			sendreq(megogoURL+'p/videos?'+createSign({'category':vars.catID[catLst.pos], 'session':session, 'offset':this.page*cont_page_max, 'limit':cont_page_max+1}), init_contentlist);

}

subCatLst.onExit = function(){

	$('video_p'+this.pos).className = 'submenu_item';
	currLst = catLst;
	currLst.onChange();
}





subCatLst.onEnter = function(){


    $('cats_page').style.display = 'none';
	$('info_page').style.display = 'block';
	subCatLst.offset =subCatLst.pos;
	subCatLst.dataset =dataset;

	if(subCatLst.initialisated != subCatLst.offset){

  		subCatLst.initialisated = subCatLst.offset;
		switchMovieInfo(subCatLst);

	}
	currLst = movieInfoLst;
	prevLst = subCatLst;
	movieInfoLst.color = vars.menu_items[catLst.pos];
	currLst.reset();
	currLst.onChange();


}

/*subCatLst.reset = function(){

    this.prevPos = this.pos;
    this.page = 0;
	this.pos = 0;
	this.onChange();
}*/





//////////

function getOffsetRect(elem) {
    // (1)
    var box = elem.getBoundingClientRect()

    // (2)
    var body = document.body
    var docElem = document.documentElement

    // (3)
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

    // (4)
    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0

    // (5)
    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft

    return { top: Math.round(top), left: Math.round(left) }
}

var pageCount=0;
var pages = new Array();

function pageSeparating(elem, page){

	 for(i=0; i<elem.children.length; i++)
	 	elem.children[i].style.display = "block";

	 lowLimit = elem.clientHeight*page;
	 hiLimit = elem.clientHeight*(page+1)-1-20;

     tmp = new Array();
	 for(i=0; i<elem.children.length; i++){
	 	offset = getOffsetRect(elem.children[i]).top;
	 	if(offset<lowLimit || offset>hiLimit)
	 		tmp.push(i);
	 }

	 for(i=0; i<tmp.length; i++)
	 	elem.children[tmp[i]].style.display = "none";

}


movieInfoLst = new lst();
movieInfoLst.color = 'blue';
movieInfoLst.id = ['info_btn_0', 'info_1', 'info_2', 'info_3', 'info_4', 'info_5'];
movieInfoLst.layers = ['movieinfo_general_id',  'movieinfo_actors_id', 'alt_submenu', '', 'movieinfo_comments_id', 'movieinfo_submenu'];
movieInfoLst.length = movieInfoLst.id.length;




movieInfoLst.reset = function(){
this.pos = this.prevPos = 0;

  $('alt_submenu').style.marginLeft = "360px";
text= new Array();
if(!empty(file.video[0].alt_video)){
       text['video_list'] = file.video[0].alt_video;
       init_contentlist(text, 'alt_'); }

       $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
	//$('alt_video_layer').style.display = "none";

	for(i = 0; i<this.length;i++)
	$(this.id[i]).className ="movieinfo_menuitem";

	movieInfoLst.onChange();
}


movieInfoLst.onChange = function(){



	$(this.id[this.prevPos]).className ="movieinfo_menuitem";
	$(this.id[this.pos]).className ="movieinfo_menuitem_"+movieInfoLst.color;


    for(var i in this.layers){
        if(this.layers[i] != '')
		$(this.layers[i]).style.display = 'none';
	}
    if(this.layers[i] != '')
	$(this.layers[this.pos]).style.display = 'block';


}


movieInfoLst.onEnter = function(){

	switch(this.pos){
		case 0:


		   	if(empty(fileInfo.isSeries)){


		 				sesies_getdata(fileInfo.id);
		 				$('info_page').style.display = 'none';

 			}
 			else{

 			   currLst = seriesLst;
			$('info_page').style.display = 'none';
 			$('menu_series').style.display = 'block';
		   	currLst.reset();
 			}

		break;
		case 1:

		break;
		case 2:
				currLst = proposalMovieLst;

				//margin-left 360

		   		currLst.onChange();

		break;
		case 3:
			if($('info_3').innerHTML == 'Из избранного')
				sendreq(iviURL+'removefavorite?'+createSign({'video':file.video[0]['id'], 'session':session}),setFavorits);
		   else
			sendreq(iviURL+'addfavorite?'+createSign({'video':file.video[0]['id'], 'session':session}),setFavorits);

		break;
		case 4:
				if (!$('movieinfo_comments_id').children.length)
					break;
             currLst = commentsMovieLst ;
             $(this.id[this.pos]).className ="movieinfo_menuitem";
             currLst.reset();


    	break;
		case 5:
		currLst = likeLst;
         currLst.onChange();
		break;
	}



}

movieInfoLst.onExit = function(){

	//currLst = movieInfoLst;
	//currLst.onChange();
	//switchLayer(layer_cats);

	currLst = prevLst;
	currLst.onChange();

	$('info_page').style.display = 'none';
	$(this.id[this.pos]).className ="movieinfo_menuitem";

	for(i=0; i<currLst.layers.length; i++)
		$(currLst.layers[i]).style.display = 'block';




}

///////////////



commentsMovieLst = new lst();
commentsMovieLst.length = 1;
commentsMovieLst.maxPage = 0;
commentsMovieLst.reset = function(){

	this.__proto__.reset();


	elem =  $('movieinfo_comments_id');
 	for(i=0; i<elem.children.length; i++)
	 	elem.children[i].style.display = "block";

	tmp =  getOffsetRect(elem.children[elem.children.length-1]).top/elem.clientHeight;
	commentsMovieLst.maxPage = tmp - (tmp%1);

	if(tmp%1)
		commentsMovieLst.maxPage++;

	 for(i=0; i<tmp.length; i++)
	 	elem.children[tmp[i]].style.display = "none";

	 proposalMovieLst.reset();


	 this.onChange();


}


commentsMovieLst.overflow = function(){

	if(this.page == -1)
		this.page = 0;
	if(this.page >= this.maxPage)
		this.page--;


}


commentsMovieLst.onExit = function(){

	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
	currLst = movieInfoLst;
	currLst.onChange();

}

commentsMovieLst.onChange = function(){

	 pageSeparating($('movieinfo_comments_id'),this.page);
	 $('movieinfo_comments_id').getElementsByClassName('movieinfo_menuitem_shadow')[0].style.display = "block";
	 $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "block";
	 $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "block";

	 if(this.page<this.maxPage-1)
	 	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className =  'arrow_bottom_blue';
	 else
	 	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className =  'arrow_bottom_gray';


	 if(this.page)
	 	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className =  'arrow_top_blue';
	 else
	 	$('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className =  'arrow_top_gray';




}






proposalMovieLst = new lst();
proposalMovieLst.length = 3;
proposalMovieLst.onChange = function(){

	if(this.length>3)
		this.length = 3;


	var arr = $('alt_video_layer');

    arr.children[this.prevPos].className = 'submenu_item';
    arr.children[this.pos].className = 'submenu_item_act_blue';



}

proposalMovieLst.refreshPage=function(){

     if (this.page >= file.video[0].alt_video.length/cont_page_max)
       	this.page = 0;

     if (this.page < 0)
       	this.page = file.video[0].alt_video.length/cont_page_max;

	 text= new Array();      //file.video[0].alt_video.slice(this.pos)

 	 tmp = file.video[0].alt_video;
 	 tmp1 = tmp.concat([]);
 	 tmp1.reverse();

       text['video_list'] = tmp.concat(tmp1);
       text['video_list'] = text['video_list'].slice(this.page*cont_page_max);


       init_contentlist(text, 'alt_');

}

proposalMovieLst.onExit = function(){

	var arr = $('alt_video_layer');
	arr.children[this.pos].className = 'submenu_item';


	currLst = movieInfoLst;
	currLst.onChange();

}

proposalMovieLst.onEnter = function(){


    prevLst = subCatLst;
    subCatLst.initialisated = -1;
	$('info_page').style.display = 'block';
 	this.offset =this.pos;

	switchMovieInfo(this);

	currLst = movieInfoLst;
	movieInfoLst.color = 'blue';
	currLst.reset();
	currLst.onChange();

}


 proposalMovieLst.overflow = function(){

	if(this.page == -1)
		this.page = 0;

			if(this.page*3>=file.video[0].alt_video.length)
			   this.page = file.video[0].alt_video.length/3-1;
			else
		 if(!empty(file.video[0].alt_video.length)){
		    this.length = 0;
		 	l = 3;
		 	text['video_list'] = new Array();
			for(i = l*this.page, j=0; i<l*(this.page+1); i++,j++){
       		text['video_list'][j] = file.video[0].alt_video[i];
       		this.length++;
       		}
       		init_contentlist(text, 'alt_');
       	}

	//this.refreshPage();

}



////////////////////
seriesLst = new lst();
seriesLst.id = ['series_0', 'series_1', 'series_2', 'series_3'];
seriesLst.layers = ['', '', '', '', ''];
seriesLst.length = seriesLst.id.length;
seriesLst.bitrates = new Array();


seriesLst.reset = function(){

if(!empty(fileInfo.isSeries)){
		$('series_1').innerHTML = $('episode_item_'+episodeLst.pos).children[0].innerHTML;
		$('series_1').style.display = 'block';
		}
else
$('series_1').style.display = 'none';
currLst.onChange();
}



seriesLst.onChange = function(){

	if(!empty(fileInfo.isSeries)){
		$('series_1').innerHTML = $('episode_item_'+episodeLst.pos).children[0].innerHTML;
		$('series_1').style.display = 'block';
	}
	else
		$('series_1').style.display = 'none';

		$(this.id[this.prevPos]).className ="menuseries_item";
		$(this.id[this.pos]).className ="menuseries_item_act";

		if(this.pos == 1 && empty(fileInfo.isSeries))
			if (!this.prevPos)
				this.next();
			else this.prev();

		if(fileInfo.isSeries)
		$('series_1').innerHTML = $('episode_item_'+episodeLst.pos).children[0].innerHTML;




}

seriesLst.onEnter = function(){

// /p/addfavorite?video_id=<video_id>&session=<session_id>&sign=<sign>

 	switch(this.pos){

 		case 0:


 				sesies_getdata(fileInfo.id);
 				$('menu_series').style.display = 'none';

 			break;


 		case 1:

		    if(empty($('season_item_1'))){

				currLst = episodeLst;

				tmp = $('menu_series').children;

				if(tmp[1].className == 'submenu_series'){

					$('submenu_series').className = 'submenu_series2';
					$('submenu_series2').className = 'submenu_series';

				}

			}
			else{
				$('submenu_series').className = 'submenu_series';
				$('submenu_series2').className = 'submenu_series2';
				currLst = seasonLst;
			}
		break;

		case 2:

                currLst = bitratesLst;
 				currLst.reset();

 			break;


	}
	currLst.onChange();

}

seriesLst.onExit = function(){

    $('menu_series').style.display = 'none';
    $('info_page').style.display = 'block';
	currLst = movieInfoLst;
	currLst.onChange();

}
/////////////////

/////////////////

bitratesLst = new lst();
//bitratesLst.length = episodeLst.id.length;
bitratesLst.idLst = [{}];
bitratesLst.val = '';

bitratesLst.reset = function(){

	if(empty(seriesLst.bitrates)){
		//currLst = seriesLst;

		   var url = {'video':fileInfo.id, 'session':session}

		    if(fileInfo.isSeries){
		    	season = file.video[0].season_list[seasonLst.pos].id;
		    	if(!empty(episodeLst.idLst[season][episodeLst.pos])){
		    		url['episode'] = episodeLst.idLst[season][episodeLst.pos];
		    		url['season'] = season;
		    	}
		    }

     	sendreq(iviURL+'info?'+createSign(/*{'video':id, 'session':session}*/url),getStreamInfo);


	}

    this.pos = this.prevPos = this.val = 0;
    $('submenu_bitrates').style.display = 'block';
    bitratesLst.length = seriesLst.bitrates.length;

}


bitratesLst.onChange = function(){

$('bitrates_item_'+this.prevPos).className ="submenu_series_item";
	$('bitrates_item_'+this.pos).className ="submenu_series_item_act";

}


bitratesLst.onEnter = function(){

    this.val = seriesLst.bitrates[this.pos].name;
	currLst.onExit();

}

bitratesLst.onExit = function(){

    $('bitrates_item_'+this.pos).className ="submenu_series_item";
    $('submenu_bitrates').style.display = 'none';
	currLst = seriesLst;
	currLst.onChange();

}


//seriesLst.nextLst = seasonLst;/*movieInfoLst.enter5 = function(){}*/

//////////////

seasonLst = new lst();
seasonLst.length = seasonLst.id.length;

seasonLst.reset = function(){

    this.pos = this.prevPos = 0;

}


seasonLst.onChange = function(){

 	if ($('submenu_series').style.display == 'none')
 		$('submenu_series').style.display = 'block';

     $('series_1').innerHTML = $('season_item_'+seasonLst.pos).children[0].innerHTML +','+  $('episode_item_'+episodeLst.pos).children[0].innerHTML;

	$('season_item_'+this.id[this.prevPos]).className ="submenu_series_item";
	$('season_item_'+this.id[this.pos]).className ="submenu_series_item_act";

}


seasonLst.onEnter = function(){

	currLst = episodeLst;
	initSeriesLst(seasonLst.pos);
	currLst.onChange();

}

seasonLst.onExit = function(){

    $('submenu_series').style.display = 'none';
	currLst = seriesLst;
	currLst.onChange();

}


/////////////////

episodeLst = new lst();
episodeLst.length = episodeLst.id.length;
episodeLst.idLst = [{}];

episodeLst.reset = function(){

    this.pos = this.prevPos = 0;

}


episodeLst.onChange = function(){

 	if ($('submenu_series2').style.display == 'none')
 		$('submenu_series2').style.display = 'block';

    $('series_1').innerHTML = $('episode_item_'+episodeLst.pos).children[0].innerHTML;

	$('episode_item_'+this.id[this.prevPos]).className ="submenu_series_item";
	$('episode_item_'+this.id[this.pos]).className ="submenu_series_item_act";

}


episodeLst.onEnter = function(){


	currLst.onExit();

}

episodeLst.onExit = function(){

    $('submenu_series2').style.display = 'none';
    if(!empty($('season_item_1')))
		currLst = seasonLst;
	else
		currLst = seriesLst;
	currLst.onChange();

}


//seriesLst.nextLst = seasonLst;/*movieInfoLst.enter5 = function(){}*/

//////////////
searchLst = new lst();



searchLst.id = ['search_line', 'search_line'/*'search_cats', 'search_country', *//*'search_ok', 'search_cancel'*/];
searchLst.length = searchLst.id.length;
searchLst.res = '';

var sugg = 0;


searchLst.reset = function(){

$('cats_page').style.display = 'none';

$('modal_search').style.display = 'block';
 		$('modal_search1').style.display = 'block';




//searchLst.length = $('search_modal_box').children.length;
$('search_line').value = '1';
if(sugg==0)
     get_suggest();
     sugg=1;
$('search_line').value = '';

	currLst.onChange();

}

 searchLst.onLeft = function(){

	currLst.prev();
}


searchLst.onRight = function(){

	currLst.next();
}




searchLst.onExit = function(){


	$('modal_search').style.display = 'none';
 	$('modal_search1').style.display = 'none';
 	$('cats_page').style.display = 'block';

 	currLst = catLst;
	currLst.onChange();
	$('search_line').blur();

}                               /* RB650768667UA*/





searchLst.onEnter = function(res){
	//if(/*searchLst.id[this.pos] == 'search_ok'*/this.pos == 1){
	//         currLst = searchResultLst;
	         //$('search_ok').blur();
	//         currLst.onChange();
	//}
   // else
    	/*if(searchLst.id[this.pos] == 'search_cancel'){

	         currLst.onExit();
		}
        else*/
			if(empty(res)){
				searchResult = ''
			    get_suggest();
			}
			else{
				searchLst.res = res;
				searchResultLst.pos = 0;
				searchResultLst.prevPos = 0;
				searchResultLst.page = 0;
				searchResultLst.initialisated = -1;
				searchResultLst.showRes();

			}

}

searchLst.onChange = function(){


	switch(this.pos){

		case 0:
			$('search_line').focus();
			stb.ShowVirtualKeyboard();
			console.log('ShowVirtualKeyboard');

		break;
		case 1:
				    var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
    		if(arr[0]==undefined)
    		break;

			this.pos = this.prevPos = 0;
			currLst = searchResultLst;
			$('search_line').blur();

			currLst.onChange();
			//$('search_cats').value= this.pos;
			//$('search_cats').focus();
		break;
		case 2:
	/*		//$('search_genre').value= this.pos;
			$('search_genre').focus();
		break;*/
		/*case 2:
			//$('search_ok').value= this.pos;
			$('search_ok').focus();
		break;
		case 3:

			//$('search_cancel').value= this.pos;
			$('search_cancel').focus();
		break;


		case 4:

			//$('search_cancel').value= this.pos;
			$('search_cancel').focus();
		break;*/

	}
}

////////////////
searchResultLst = new lst();
searchResultLst.length = 5;
searchResultLst.layers = ['modal_search1', 'modal_search1'];

searchResultLst.onChange = function(){

	var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');

    arr[this.prevPos].children[0].className = 'stripes_cover';
    arr[this.pos].children[0].className = 'stripes_cover_act';

}

searchResultLst.onLeft = function(){

	currLst.prev();
}


searchResultLst.onRight = function(){

	currLst.next();
}

searchResultLst.onExit = function(){
searchResultLst.onDown();
}

searchResultLst.onDown = function(){

    var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
	arr[this.pos].children[0].className = 'stripes_cover';
	currLst = searchLst;
	currLst.onChange();
}

searchResultLst.onEnter = function(){

    /*var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
	arr[this.pos].children[0].className = 'stripes_cover';
	currLst = searchLst;
	currLst.onChange();*/
	prevLst = currLst;
 	$('modal_search').style.display = 'none';
 	$('modal_search1').style.display = 'none';
	$('info_page').style.display = 'block';
	searchResultLst.offset =searchResultLst.pos;
	searchResultLst.dataset =searchLst.res.video_list;

	if(searchResultLst.initialisated != searchResultLst.offset){

  		searchResultLst.initialisated = searchResultLst.offset;
		switchMovieInfo(searchResultLst);
	}
	currLst = movieInfoLst;
	movieInfoLst.color = 'blue';
	currLst.onChange();
}

searchResultLst.showRes = function(res){

searchResult = searchLst.res.video_list;
		str = '<div class="stripeshorizontal_left"></div>'           /*searchResult.length*/

		var arr = document.getElementsByClassName('stripes_horizontal_act');


	    while(arr[0].childElementCount)
	    		arr[0].removeChild(arr[0].children[0]);


		for(i=searchResultLst.page*5; i<searchResultLst.page*5+5; i++){

			var fileInfo = searchResult[i];

			str +=	'<div class="stripes_horizontal_box"><div class="stripes_cover"><img width="110px" height="149px" src="http://megogo.net'+fileInfo.poster+'" /></div>';
			str +=	'<div class="stripes_horizontal_title">'+ fileInfo.title +'</div>';

			 var genre_str = '';
		     if(!empty(fileInfo.year)){
		         genre_str += fileInfo.year;
		     }

		     if(!empty(fileInfo.country)){
		         if(!empty(fileInfo.year))
		             genre_str +=', ';

		         if(!empty(countries[fileInfo.country]))
		             genre_str += countries[fileInfo.country];
		         else genre_str += fileInfo.country;

		     }


			str +='<div class="stripes_horizontal_country">'+genre_str+'</div></div>';






		}
	    str +='<div class="stripeshorizontal_right"></div>';
		document.getElementsByClassName('stripes_horizontal_act')[0].innerHTML = str;

}

searchResultLst.overflow = function(){

	if(this.page == -1)
		this.page = 0;

	searchResultLst.showRes();

}




/////////////////


/*settingsLst.length = 2;


settingsLst.onChange = function(){


    // $('settings_1').innerHTML = $('season_item_'+seasonLst.pos).children[0].innerHTML +','+  $('episode_item_'+episodeLst.pos).children[0].innerHTML;

	$('settings_item_'+this.prevPos).className ="submenu_series_item";
	$('settings_item_'+this.pos).className ="submenu_series_item_act";

}


settingsLst.onEnter = function(){

	if(this.pos){
		if(!childFree)
			  $('settings_item_1').innerHTML = 'Защита от детей включена';
		else
			$('settings_item_1').innerHTML = 'Защита от детей выключена';
		childFree = !childFree;
	}
	else
		 $('auth').style.display = 'block';

	currLst.onChange();

}

settingsLst.onExit = function(){

    $('submenu_settings').style.display = 'none';
    $('submenu').style.display = 'block';
    $('cats_page').style.display = 'block';
	currLst = catLst;
	currLst.onChange();

}
*/

/////////////////



likeLst.length = 2;


likeLst.onChange = function(){



     $('movieinfo_submenu').children[this.prevPos].className ="movieinfo_menuitem";
     $('movieinfo_submenu').children[this.pos].className ="movieinfo_menuitem_rate";

}


likeLst.onEnter = function(){



     sendreq(megogoURL+'p/addvote?'+createSign({'video':fileInfo.id, 'like':this.pos, 'session':session}), setLike);

       $('movieinfo_submenu').children[this.prevPos].className ="movieinfo_menuitem";
     $('movieinfo_submenu').children[this.pos].className ="movieinfo_menuitem";


	currLst = movieInfoLst;
	currLst.onChange();
	 $('movieinfo_submenu').style.display = 'none';


}


likeLst.onExit = function(){



    currLst = movieInfoLst;
	currLst.onChange();
	 $('movieinfo_submenu').style.display = 'none';

}




//////////////
authLst = new lst();


authLst.id = ['login', 'password', 'auth_ok', 'auth_cancel'];
authLst.length = authLst.id.length;
authLst.res = '';


authLst.reset = function(){

	$('cats_page').style.display = 'none';
 	$('auth').style.display = 'block';

	currLst.onChange();

}






authLst.onExit = function(){

	//if ($('submenu_series2').style.display == 'none')
 	//$('submenu_settings').style.display = 'block;';
 	 $('cats_page').style.display = 'block';
 	$('auth').style.display = 'none;';

 	currLst = settingsLst;

	currLst.onChange();

}





authLst.onEnter = function(res){

	if(this.id[this.pos] == 'auth_ok' ){
        show_waiting();
		authentification(0);
	    currLst.onChange();
	}
    else
    	if(this.id[this.pos] == 'auth_cancel'){

	         currLst.onExit();
		}
        else
			currLst.onChange();

}

 authLst.id = ['login', 'password', 'auth_cancel', 'auth_ok'];
authLst.onChange = function(){

$(this.id[this.pos]).focus();

	switch(this.pos){

		case 0:
			//$('login').focus();
			stb.ShowVirtualKeyboard();
			//console.log('ShowVirtualKeyboard');

		break;
		case 1:
			//$('password').focus();
            stb.ShowVirtualKeyboard();
			//console.log('ShowVirtualKeyboard');
			//currLst.onChange();
		break;
		case 2:
			//$('auth_cancel').focus();
			//currLst.onExit();


		break;
		case 3:
			//$('auth_ok').focus();
			//authentification(0);
		break;


	}
}
