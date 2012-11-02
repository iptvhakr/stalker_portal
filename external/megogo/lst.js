////////////
lst = function () {

    this.pos = 0;
    this.prevPos = 0;

}
lst.prototype.length = 0;
lst.prototype.page = 0;
lst.prototype.prevLst = 0;
lst.prototype.id = 0;
lst.prototype.direct = '';
//lst.prototype.leftSide=1;
//lst.prototype.level=0;
//lst.prototype.maxLevel=0;


lst.prototype.refreshPage = function () {
}
lst.prototype.next = function () {

    this.prevPos = this.pos;
    this.direct = 'next';

    if (this.pos < this.length - 1)
        this.pos++;
    else {
        this.page++;
        this.pos = 0;
        this.overflow();

    }

    this.onChange();
}

lst.prototype.prev = function () {

    this.direct = 'prev';

    this.prevPos = this.pos;

    if (this.pos)
        this.pos--;
    else {
        if (this.length)
            this.pos = this.length - 1;

        this.page--;
        this.overflow();

    }

    this.onChange();
}


lst.prototype.reset = function () {

    this.prevPos = this.pos;
    this.page = 0;
    this.pos = 0;
    this.onChange();
}

lst.prototype.overflow = function () {

}

lst.prototype.onChange = function () {
}
lst.prototype.onEnter = function () {

    /*if(this.level < this.maxLevel)
     this.level++;*/

    this.onChange();

}

//////
/*settingsLst = new lst();*/
likeLst = new lst();
catLst = new lst();
extSubCatLst = new lst();
confirmExitLst = new lst();
catLst.length = 8;
catLst.id = 7;
catLst.initialisated = -1;
catLst.name = 'catLst';

var timeoutID = 0;
var fl_timeoutID = 0;

catLst.onLeft = function () {
    this.leftFl = 1;
    currLst.onChange();
}

catLst.onRight = function () {

    fl_timeoutID = 0;
    clearTimeout(timeoutID);
    this.onEnter();
    //this.onChange();
    $('cat_' + this.pos).className = str_replace($('cat_' + this.pos).className, '_act', '');

}


catLst.onUp = function () {

    $('submenu_genres').style.display = 'none';
    $('submenu').style.display = 'none';

    if (!empty(timeoutID))
        clearTimeout(timeoutID);


    if (this.pos < 6) {

        timeoutID = setTimeout(function () {
                if (catLst.pos < 6)
                    if (currLst == catLst) {
                        fl_timeoutID = 1;
                        catLst.onEnter(); //currLst = catLst; currLst.onChange();
                    }
            },
            3000);
    }
    currLst.prev();

}

catLst.onDown = function () {

    $('submenu_genres').style.display = 'none';
    $('submenu').style.display = 'none';


    if (!empty(timeoutID))
        clearTimeout(timeoutID);

    if (this.pos < 6) {
        timeoutID = setTimeout(function () {
                if (currLst == catLst) {
                    fl_timeoutID = 1;
                    catLst.onEnter(); //currLst = catLst; currLst.onChange();
                }
            },
            3000);
    }
    currLst.next();
}

catLst.onChange = function () {

    if (!session) {
        $('cat_5').style.display = 'none';


        if (this.pos == 5) {

            if (this.prevPos == 4)
                this.pos = 6;
            else this.pos = 4;
        }
    }
    else $('cat_5').style.display = 'block';


    $('cat_' + this.prevPos).className = str_replace($('cat_' + this.prevPos).className, '_act', '');
    $('cat_' + this.pos).className = str_replace($('cat_' + this.pos).className, '_act', '');
    //if (!fl_timeoutID)
    $('cat_' + this.pos).className = $('cat_' + this.pos).className + '_act';
    fl_timeoutID = 0;


}


catLst.onExit = function () {
    $('cats_page').style.display = 'none';
    $('confirmExit').style.display = 'block';

    prevLst = currLst;
    currLst = confirmExitLst;
    currLst.onChange();
}


catLst.onEnter = function () {
    this.leftFl = 1;
    switch (this.pos) {
        case 0:
            //case 2:
            if (this.initialisated != vars.catID[catLst.pos]) {
                subCatLst.reset();

                $('submenu_genres').style.display = 'none';
                $('submenu').style.display = 'block';

                if (!catLst.pos)
                    sendreq(megogoURL + 'p/recommend?' + createSign({'session':session, 'offset':subCatLst.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);
                else
                    sendreq(megogoURL + 'p/videos?' + createSign({'category':vars.catID[catLst.pos], 'session':session, 'offset':subCatLst.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);

                subCatLst.initialisated = -1;

            }
            //else{
            //this.initialisated = vars.catID[catLst.pos];
            if (this.initialisated != -1) {
                currLst = subCatLst;
                currLst.onChange();

            }
            this.initialisated = vars.catID[catLst.pos];
            //}

            break;
        case 1:
        case 2:
        case 3:
        case 4:


            if (this.initialisated != vars.catID[catLst.pos]) {

                if (!catLst.pos) {

                    sendreq(megogoURL + 'p/recommend?' + createSign({'session':session, 'offset':subCatLst.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);
                }
                else

                    sendreq(megogoURL + 'p/genres?' + createSign({'session':session, 'category':vars.catID[this.pos]}), init_genreLst);


            }

            if (this.initialisated != -1) {
                this.initialisated = vars.catID[catLst.pos];
                //$('cat_'+this.pos).className = str_replace($('cat_'+this.pos).className, '_act', '');
                currLst = genreLst;//currLst = subCatLst;
                currLst.onChange();

            }
            this.initialisated = vars.catID[catLst.pos];
            //}


            break;
        case 5:
            $('submenu_genres').style.display = 'none';
            var arr = $('video_layer');
            while (/*i*/arr.children.length)
                arr.removeChild(arr.children[0]);

            currLst = subCatLst;
            currLst.reset();
            sendreq(megogoURL + 'p/favorites?' + createSign({'session':session, 'limit':100}), init_contentlist);
            this.initialisated = vars.catID[catLst.pos];


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
subCatLst.offsetPage = subCatLst.page;
subCatLst.name = 'subCatLst';


subCatLst.onChange = function () {

    if (fl_timeoutID) {
        fl_timeoutID = 0;
        this.onExit();
    }


    $('submenu').style.display = 'block';

    if (this.length > this.maxLength)
        this.length = this.maxLength;

    if (this.pos == this.maxLength)
        this.pos--;

    if (!this.page)
        $('submenu').getElementsByClassName('submenu_shadow_top')[0].style.display = 'none';
    else
        $('submenu').getElementsByClassName('submenu_shadow_top')[0].style.display = 'block';

    $('video_p' + this.prevPos).className = 'submenu_item';
    if (currLst == this)
    //	 if (!fl_timeoutID)
        $('video_p' + this.pos).className = 'submenu_item_act_' + vars.menu_items[catLst.pos];


    if (fl_timeoutID) {
        fl_timeoutID = 0;
        currLst.onExit();
    }


}


subCatLst.overflow = function () {



    /*  if(this.length==0&&this.pos==0&&this.prevPos==0&&this.page==0&&catLst.pos == 5){
     this.onExit();
     return;
     video_list
     }*/
    var tmp = 0;


    if (/*this.page*3>=dataset.length*/this.length < 3 && this.direct == 'next') {

        this.page--;
        if (this.length)
            this.pos = this.length - 1;
        tmp = 1;
    }


    if (this.page < 0) {
        this.page = this.pos = this.prevPos = 0;
        tmp = 1;
    }

    if (!tmp)
        if (!catLst.pos)
            sendreq(megogoURL + 'p/recommend?' + createSign({'session':session, 'offset':this.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);
        else if (catLst.pos == 5) {
            if (this.length == cont_page_max || this.direct == 'prev')
                sendreq(megogoURL + 'p/favorites?' + createSign({'session':session, 'offset':this.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);
            else  this.page--;
        }

        else
            sendreq(megogoURL + 'p/videos?' + createSign({'category':vars.catID[catLst.pos], 'session':session, 'offset':this.page * cont_page_max, 'limit':cont_page_max + 1}), init_contentlist);

}

subCatLst.onExit = function () {

    $('video_p' + this.pos).className = 'submenu_item';
    currLst = catLst;
    currLst.onChange();
}


subCatLst.onEnter = function () {


    $('cats_page').style.display = 'none';
    $('info_page').style.display = 'block';
    subCatLst.offset = subCatLst.pos;
    subCatLst.dataset = dataset;

    if (subCatLst.initialisated != subCatLst.offset || this.offsetPage != this.page) {

        this.offsetPage = this.page;

        subCatLst.initialisated = subCatLst.offset;
        switchMovieInfo(subCatLst);

    }
    currLst = movieInfoLst;
    prevLst = subCatLst;
    movieInfoLst.color = vars.menu_items[catLst.pos];
    currLst.reset();
    currLst.onChange();


}

subCatLst.reset = function () {

    this.prevPos = 0;
    this.page = 0;
    this.pos = 0;
    $('submenu').style.display = 'block';


}


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
    var top = box.top + scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft

    return { top:Math.round(top), left:Math.round(left) }
}

var pageCount = 0;
var pages = new Array();

function pageSeparating(elem, page) {

    for (i = 0; i < elem.children.length; i++)
        elem.children[i].style.display = "block";

    lowLimit = (elem.clientHeight - 70) * page;
    /*$('footer').style.height*/
    hiLimit = (elem.clientHeight - 70) * (page + 1) - 1/*-20*/;

    tmp = new Array();
    for (i = 0; i < elem.children.length; i++) {
        offset = getOffsetRect(elem.children[i]).top;
        if (offset < lowLimit || offset > hiLimit)
            tmp.push(i);
    }

    if (tmp.length != elem.children.length)

        for (i = 0; i < tmp.length; i++)
            elem.children[tmp[i]].style.display = "none";

}


movieInfoLst = new lst();
movieInfoLst.color = 'blue';
movieInfoLst.id = ['info_btn_0', 'info_1', 'info_2', 'info_3', 'info_4', 'info_5'];
movieInfoLst.layers = ['movieinfo_general_id', 'movieinfo_actors_id', 'alt_submenu', '', 'movieinfo_comments_id', 'movieinfo_submenu'];
movieInfoLst.length = movieInfoLst.id.length;
movieInfoLst.descrPage = 0;
movieInfoLst.name = 'movieInfoLst';


movieInfoLst.reset = function () {

    this.pos = this.prevPos = 0;

    text = new Array();
    if (file != undefined)
        if (!empty(file.video[0].alt_video)) {
            text['video_list'] = file.video[0].alt_video;
            init_contentlist(text, 'alt_');
        }

    try {

        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
    }
    catch (err) {
    }
    $('info_page').children[0].className = "movieinfo_back_" + movieInfoLst.color;

    for (i = 0; i < this.length; i++)
        $(this.id[i]).className = "movieinfo_menuitem";

    currLst = descriptionMovieLst;
    descriptionMovieLst.length = 3;
    currLst.onChange();
    currLst = movieInfoLst;

    movieInfoLst.onChange();


}


movieInfoLst.onChange = function () {

    if (!session) {
        $('info_3').style.display = 'none';
        $('info_5').style.display = 'none';

        if (this.pos == 5) {

            if (this.prevPos == 4)
                this.pos = 0;
            else this.pos = 4;
        }

        if (this.pos == 3) {

            if (this.prevPos == 2)
                this.pos = 4;
            else this.pos = 2;
        }
    }
    else {
        $('info_3').style.display = 'block';
        $('info_5').style.display = 'block';
    }


    if (this.pos == 0)
        $('footer_page').style.display = 'block';
    else $('footer_page').style.display = 'none';


    $(this.id[this.prevPos]).className = "movieinfo_menuitem";
    $(this.id[this.pos]).className = "movieinfo_menuitem_" + movieInfoLst.color;


    for (var i in this.layers) {
        if (this.layers[i] != '')
            $(this.layers[i]).style.display = 'none';
    }
    if (this.layers[i] != '')
        $(this.layers[this.pos]).style.display = 'block';


}


movieInfoLst.onPageDown = function () {
    // log('onPageDown');
    currLst = descriptionMovieLst;

    currLst.next();
    currLst = movieInfoLst;
    currLst.onChange();

}

movieInfoLst.onPageUp = function () {
    // log('onPageUp');
    currLst = descriptionMovieLst;
    currLst.prev();
    currLst = movieInfoLst;
    currLst.onChange();

}


movieInfoLst.onEnter = function () {

    switch (this.pos) {
        case 0:


            if (empty(fileInfo.isSeries)) {


                sesies_getdata(fileInfo.id);
                $('info_page').style.display = 'none';

            }
            else {

                currLst = seriesLst;
                $('info_page').style.display = 'none';
                $('menu_series').style.display = 'block';
                currLst.reset();
            }


            break;
        case 1:
            if (!$('movieinfo_actors_id').children.length)
                break;
            currLst = actorsMovieLst;
            $(this.id[this.pos]).className = "movieinfo_menuitem";
            currLst.reset();

            break;
        case 2:
            currLst = proposalMovieLst;

            //margin-left 360

            currLst.onChange();

            break;
        case 3:
            if (!session) {
                show_waiting1('Авторизируйтесь');
                break;
            }

            if ($('info_3').innerHTML == 'Из избранного')
                sendreq(iviURL + 'removefavorite?' + createSign({'video':fileInfo['id'], 'session':session}), setFavorits);
            else
                sendreq(iviURL + 'addfavorite?' + createSign({'video':fileInfo['id'], 'session':session}), setFavorits);

            break;
        case 4:
            if (!$('movieinfo_comments_id').children.length)
                break;
            currLst = commentsMovieLst;
            $(this.id[this.pos]).className = "movieinfo_menuitem";
            currLst.reset();


            break;
        case 5:
            if (!session) {
                show_waiting1('Авторизируйтесь');
                break;
            }
            currLst = likeLst;
            currLst.onChange();
            break;


    }

}

movieInfoLst.onExit = function () {

    //currLst = movieInfoLst;
    //currLst.onChange();
    //switchLayer(layer_cats);

    if (flFavorUpdate) {
        flFavorUpdate = 0;
        if (catLst.pos == 5) {

            sendreq(megogoURL + 'p/favorites?' + createSign({'session':session, 'limit':100}), init_contentlist);
            currLst = subCatLst;
            currLst.initialisated = vars.catID[catLst.pos];
            currLst.reset();
        }


    }

    currLst = prevLst;
    currLst.onChange();

    $('info_page').style.display = 'none';
    $(this.id[this.pos]).className = "movieinfo_menuitem";

    for (i = 0; i < currLst.layers.length; i++)
        $(currLst.layers[i]).style.display = 'block';

    $('footer_page').style.display = 'none';

}

///////////////


commentsMovieLst = new lst();
commentsMovieLst.length = 1;
commentsMovieLst.maxPage = 0;
commentsMovieLst.name = 'commentsMovieLst';
commentsMovieLst.reset = function () {

    this.__proto__.reset();


    elem = $('movieinfo_comments_id');
    for (i = 0; i < elem.children.length; i++)
        elem.children[i].style.display = "block";

    tmp = (getOffsetRect(elem.children[elem.children.length - 1]).top) / elem.clientHeight;

    commentsMovieLst.maxPage = tmp - (tmp % 1);


    if (tmp % 1)
        commentsMovieLst.maxPage++;

    //commentsMovieLst.length =  commentsMovieLst.maxPage+1;

    for (i = 0; i < tmp.length; i++)
        elem.children[tmp[i]].style.display = "none";

    proposalMovieLst.reset();


    this.onChange();


}


commentsMovieLst.overflow = function () {

    if (this.page == -1)
        this.page = 0;
    if (this.page >= this.maxPage)
        this.page--;


}


commentsMovieLst.onExit = function () {
    try {
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
    }
    catch (err) {
    }
    currLst = movieInfoLst;
    currLst.onChange();

}

commentsMovieLst.onChange = function () {

    pageSeparating($('movieinfo_comments_id'), this.page);
    $('movieinfo_comments_id').getElementsByClassName('movieinfo_menuitem_shadow')[0].style.display = "block";
    $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "block";
    $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "block";

    if (this.page < this.maxPage - 1)
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className = 'arrow_bottom_blue';
    else
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className = 'arrow_bottom_gray';


    if (this.page)
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className = 'arrow_top_blue';
    else
        $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className = 'arrow_top_gray';


}


actorsMovieLst = new lst();
actorsMovieLst.length = 1;
actorsMovieLst.name = 'actorsMovieLst';
//actorsMovieLst.maxPage = 2;
actorsMovieLst.reset = function () {

    this.__proto__.reset();

    this.length = 1;
    elem = $('movieinfo_actors_id');
    for (i = 0; i < elem.children.length; i++)
        elem.children[i].style.display = "block";

    tmp = (getOffsetRect(elem.children[elem.children.length - 1]).top/*+40*/) / elem.clientHeight;
    actorsMovieLst.maxPage = tmp - (tmp % 1);

    if (tmp % 1)
        this.maxPage++;

    //elem.children.length

    for (i = 0; i < tmp.length; i++)
        elem.children[tmp[i]].style.display = "none";


    this.onChange();


}


actorsMovieLst.overflow = function () {

    if (this.page == -1)
        this.page = 0;
    if (this.page > this.length/* this.maxPage*/)
        this.page--;


}


actorsMovieLst.onExit = function () {

    $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
    $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
    currLst = movieInfoLst;
    currLst.onChange();

}

actorsMovieLst.onChange = function () {

    pageSeparating($('movieinfo_actors_id'), this.page);
    $('movieinfo_actors_id').getElementsByClassName('movieinfo_menuitem_shadow')[0].style.display = "block";
    $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "block";
    $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "block";

    if (this.page < this.maxPage - 1)
        $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className = 'arrow_bottom_blue';
    else
        $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_bottom')[0].children[0].className = 'arrow_bottom_gray';


    if (this.page)
        $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className = 'arrow_top_blue';
    else
        $('movieinfo_actors_id').getElementsByClassName('movieinfo_gradient_top')[0].children[0].className = 'arrow_top_gray';


}


descriptionMovieLst = new lst();
descriptionMovieLst.name = 'descriptionMovieLst';
descriptionMovieLst.length = 3;
//descriptionMovieLst.maxPage = 3;
descriptionMovieLst.reset = function () {

    this.__proto__.reset();

    this.onChange();


}


descriptionMovieLst.next = function () {
    this.pos++;
    if (this.pos >= this.length)
        this.pos--;
    descriptionMovieLst.onChange();
}

descriptionMovieLst.prev = function () {
    this.pos--;
    if (this.pos < 0)
        this.pos = 0;
    descriptionMovieLst.onChange();
}


descriptionMovieLst.onExit = function () {

    $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_top')[0].style.display = "none";
    $('movieinfo_comments_id').getElementsByClassName('movieinfo_gradient_bottom')[0].style.display = "none";
    currLst = movieInfoLst;
    currLst.onChange();

}


descriptionMovieLst.onChange = function () {

    text = '<br>' + fileInfo.description;
    var arr = text.split('.');

    descriptionMovieLst.maxPage = 0;

    $('descr').innerHTML = '';
    var pos = -1;

    var tmp;
    var pos1 = -1;

    for (var i = 0; i < arr.length; i++) {

        $('descr').innerHTML += arr[i];

        tmp = $('descr').clientHeight / (getOffsetRect($('descr')).top - 10);

        if (tmp >= this.pos && pos1 == -1)
            pos1 = i;


        if (tmp >= this.pos + 1) {
            pos = i;
            break;
        }
    }

    if (pos == -1) {
        pos = arr.length;
        this.length = this.pos + 1;
    }

    $('descr').innerHTML = '';

    var tmp2 = '';
    for (var i = pos1; i < pos; i++)
        tmp2 += arr[i] + '.';
    $('descr').innerHTML = tmp2;

}


proposalMovieLst = new lst();
proposalMovieLst.length = 3;
proposalMovieLst.onChange = function () {

    if (this.length > 3)
        this.length = 3;


    var arr = $('alt_video_layer');

    arr.children[this.prevPos].className = 'submenu_item';
    arr.children[this.pos].className = 'submenu_item_act_' + vars.menu_items[catLst.pos];


}

proposalMovieLst.refreshPage = function () {

    if (this.page >= file.video[0].alt_video.length / cont_page_max)
        this.page = 0;

    if (this.page < 0) {
        this.page = file.video[0].alt_video.length / cont_page_max;
        //this.pos = 0;
    }

    text = new Array();      //file.video[0].alt_video.slice(this.pos)

    tmp = file.video[0].alt_video;
    tmp1 = tmp.concat([]);
    tmp1.reverse();

    text['video_list'] = tmp.concat(tmp1);
    text['video_list'] = text['video_list'].slice(this.page * cont_page_max);


    init_contentlist(text, 'alt_');

}

proposalMovieLst.onExit = function () {

    var arr = $('alt_video_layer');
    arr.children[this.pos].className = 'submenu_item';


    currLst = movieInfoLst;
    currLst.onChange();

}

proposalMovieLst.onEnter = function () {

    if (catLst.pos == 1 || catLst.pos == 3 || catLst.pos == 4)
        prevLst = extSubCatLst;
    else
        prevLst = subCatLst;

    subCatLst.initialisated = -1;
    $('info_page').style.display = 'block';
    var arr = $('alt_video_layer');
    arr.children[this.pos].className = 'submenu_item';
    this.offset = this.pos;

    switchMovieInfo(this);

    currLst = movieInfoLst;
    movieInfoLst.color = 'blue';
    currLst.reset();
    currLst.onChange();

}


proposalMovieLst.overflow = function () {
    var text = new Array();
    if (this.page == -1) {
        this.page = 0;
        this.pos = 0;
    }

    if (this.page * 3 >= file.video[0].alt_video.length) {
        this.page = file.video[0].alt_video.length / 3 - 1;
        this.pos = this.prevPos;
    }
    else if (!empty(file.video[0].alt_video.length)) {
        this.length = 0;
        l = 3;
        text['video_list'] = new Array();
        for (i = l * this.page, j = 0; i < l * (this.page + 1); i++, j++) {
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


seriesLst.reset = function () {

    if (!empty(fileInfo.isSeries)) {
        $('series_1').innerHTML = $('episode_item_' + episodeLst.pos).children[0].innerHTML;
        $('series_1').style.display = 'block';
    }
    else
        $('series_1').style.display = 'none';
    currLst.onChange();
}


seriesLst.onChange = function () {
    $('footer_page').style.display = 'none';
    if (!empty(fileInfo.isSeries)) {
        $('series_1').innerHTML = $('episode_item_' + episodeLst.pos).children[0].innerHTML;
        $('series_1').style.display = 'block';
    }
    else
        $('series_1').style.display = 'none';

    $(this.id[this.prevPos]).className = "menuseries_item";
    $(this.id[this.pos]).className = "menuseries_item_act";

    if (this.pos == 1 && empty(fileInfo.isSeries))
        if (!this.prevPos)
            this.next();
        else this.prev();

    if (fileInfo.isSeries)
        $('series_1').innerHTML = $('episode_item_' + episodeLst.pos).children[0].innerHTML;


}

seriesLst.onEnter = function () {

// /p/addfavorite?video_id=<video_id>&session=<session_id>&sign=<sign>

    switch (this.pos) {

        case 0:

            sesies_getdata(fileInfo.id);
            $('menu_series').style.display = 'none';

            break;


        case 1:

            if (empty($('season_item_1'))) {

                currLst = episodeLst;

                tmp = $('menu_series').children;

                if (tmp[1].className == 'submenu_series') {

                    $('submenu_series').className = 'submenu_series2';
                    $('submenu_series2').className = 'submenu_series';

                }

            }
            else {
                $('submenu_series').className = 'submenu_series';
                $('submenu_series2').className = 'submenu_series2';
                currLst = seasonLst;
            }
            break;

        case 2:
            tmp = 0;
            currLst = bitratesLst;
            if (!empty(seriesLst.bitrates))
                tmp = currLst.pos;

            currLst.reset();
            currLst.pos = tmp;

            break;

        case 3:

            episodeLst.pos = 0;
            try {

                season.pos = 0;
            }
            catch (err) {
            }
            ;

            sesies_getdata(fileInfo.id);
            $('menu_series').style.display = 'none';


            break;


    }
    currLst.onChange();

}

seriesLst.onExit = function () {

    $('menu_series').style.display = 'none';
    $('info_page').style.display = 'block';
    currLst = movieInfoLst;
    currLst.onChange();

}
/////////////////

/////////////////

bitratesLst = new lst();
//bitratesLst.length = episodeLst.id.length;
bitratesLst.idLst = [
    {}
];
bitratesLst.val = '';

bitratesLst.reset = function () {

    /*if(empty(seriesLst.bitrates))*/
    {
        //currLst = seriesLst;

        //seriesLst.bitrates = [{}];
        //bitratesLst.idLst = [{}];

        var url = {'video':fileInfo.id, 'session':session}

        if (fileInfo.isSeries) {
            season = file.video[0].season_list[seasonLst.pos].id;
            if (!empty(episodeLst.idLst[season][episodeLst.pos])) {
                url['episode'] = episodeLst.idLst[season][episodeLst.pos];
                url['season'] = season;
            }
        }

        sendreq(iviURL + 'info?' + createSign(/*{'video':id, 'session':session}*/url), getStreamInfo);


    }

    this.pos = this.prevPos = this.val = 0;
    $('submenu_bitrates').style.display = 'block';
    bitratesLst.length = seriesLst.bitrates.length;

}


bitratesLst.onChange = function () {

    $('bitrates_item_' + this.prevPos).className = "submenu_series_item";
    $('bitrates_item_' + this.pos).className = "submenu_series_item_act";

}


bitratesLst.onEnter = function () {

    this.val = seriesLst.bitrates[this.pos].name;
    currLst.onExit();

}

bitratesLst.onExit = function () {

    $('bitrates_item_' + this.pos).className = "submenu_series_item";
    $('submenu_bitrates').style.display = 'none';

    currLst = seriesLst;
    currLst.onChange();

}


//seriesLst.nextLst = seasonLst;/*movieInfoLst.enter5 = function(){}*/

//////////////

running_string = [];
running_string.str = '';
running_string.offset = 0;
running_string.maxLength = 0;
running_string.div = '';
running_string.timer = 0;

running_string.play = function () {

    var str1 = '';
    var l = 0;
    var stuff = '';

    for (var i = 0; i < vars[win.height].seasonTextLen; i++)
        stuff = stuff + ' ';

    str2 = stuff + this.str + stuff;

    // str2 =   this.str;

    while (l < this.maxLength) {

        if (this.offset + l >= str2.length)
            this.offset = 0;

        str1 = str1 + str2[this.offset + l++];

        //l++;

    }

    running_string.div.innerHTML = str1;

    this.offset++;

}


running_string.stop = function () {

    clearInterval(running_string.timer);
    running_string.div.innerHTML = running_string.str.slice(0, vars[win.height].seasonTextLen) + '...'
    running_string.timer = 0;
    running_string.offset = 0;
    running_string.str = '';

}


seasonLst = new lst();
seasonLst.length = seasonLst.id.length;

seasonLst.reset = function () {

    this.pos = this.prevPos = 0;

}


seasonLst.onChange = function () {

    if (running_string.timer)
        running_string.stop();

    if (file.video[0].season_list[this.pos].title.length > vars[win.height].seasonTextLen) {
        running_string.maxLength = vars[win.height].seasonTextLen;
        running_string.str = file.video[0].season_list[this.pos].title;
        running_string.div = $('season_item_' + this.id[this.pos]).getElementsByClassName('submenuseries_title')[0];
        running_string.timer = window.setInterval('running_string.play()', 100);

    }


    if ($('submenu_series').style.display == 'none')
        $('submenu_series').style.display = 'block';


    //$('series_1').innerHTML = $('season_item_'+seasonLst.pos).children[0].innerHTML +','+  $('episode_item_'+episodeLst.pos).children[0].innerHTML;

    $('season_item_' + this.id[this.prevPos]).className = "submenu_series_item";
    $('season_item_' + this.id[this.pos]).className = "submenu_series_item_act";

}


seasonLst.onEnter = function () {

    if (running_string.timer)
        running_string.stop();

    currLst = episodeLst;
    initSeriesLst(seasonLst.pos, 0);
    if (currLst.pos >= currLst.length)
        currLst.pos = 0;
    currLst.onChange();

}

seasonLst.onExit = function () {

    if (running_string.timer)
        running_string.stop();

    $('submenu_series').style.display = 'none';
    currLst = seriesLst;
    currLst.onChange();

}


/*seasonLst.overflow = function(){

 if(file.video[0].season_list[seasonLst.pos].episode_list[this.pos+this.page*this.maxLength]== undefined){
 this.pos =  this.prevPos;
 if (this.page>0)
 this.page--;
 else {this.page=0;this.pos=0;}
 }


 initSeriesLst(seasonLst.pos, this.page);
 currLst.onChange();

 }*/


/////////////////

episodeLst = new lst();
episodeLst.length = episodeLst.id.length;

episodeLst.idLst = [
    {}
];

episodeLst.reset = function () {
    this.pos = this.prevPos = 0;

}


episodeLst.onChange = function () {

    if (running_string.timer)
        running_string.stop();

    if (file.video[0].season_list[seasonLst.pos].episode_list[this.pos].title.length > vars[win.height].seasonTextLen) {
        running_string.maxLength = vars[win.height].seasonTextLen;

        running_string.str = file.video[0].season_list[seasonLst.pos].episode_list[this.pos].title;
        running_string.div = $('episode_item_' + this.id[this.pos]).getElementsByClassName('submenuseries_title')[0];
        running_string.timer = window.setInterval('running_string.play()', 100);

    }


    if ($('submenu_series2').style.display == 'none')
        $('submenu_series2').style.display = 'block';

    $('series_1').innerHTML = $('episode_item_' + episodeLst.pos).children[0].innerHTML;

    $('episode_item_' + this.prevPos/*this.id[this.prevPos]*/).className = "submenu_series_item";
    $('episode_item_' + this.pos/*this.id[this.pos]*/).className = "submenu_series_item_act";

}


episodeLst.onEnter = function () {


    currLst.onExit();

}

episodeLst.overflow = function () {

    //if ($('episode_item_'+this.id[this.pos]) == undefined)
    //	this.id[this.pos] =  this.prevPos;
    if (file.video[0].season_list[seasonLst.pos].episode_list[this.pos + this.page * this.maxLength] == undefined) {
        this.pos = this.prevPos;
        if (this.page > 0)
            this.page--;
        else {
            this.page = 0;
            this.pos = 0;
        }
    }


    initSeriesLst(seasonLst.pos, this.page);
    currLst.onChange();

}

episodeLst.onExit = function () {

    $('submenu_series2').style.display = 'none';
    if (!empty($('season_item_1')))
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


searchLst.reset = function () {

    $('cats_page').style.display = 'none';

    $('modal_search').style.display = 'block';
    $('modal_search1').style.display = 'block';


//searchLst.length = $('search_modal_box').children.length;
    $('search_line').value = '1';
    if (sugg == 0)
        get_suggest();
    sugg = 1;
    $('search_line').value = '';

    currLst.onChange();

}

searchLst.onLeft = function () {

    currLst.prev();
}


searchLst.onRight = function () {

    currLst.next();
}


searchLst.onExit = function () {


    $('modal_search').style.display = 'none';
    $('modal_search1').style.display = 'none';
    $('cats_page').style.display = 'block';

    currLst = catLst;
    currLst.onChange();
    $('search_line').blur();

}

searchLst.onRefresh = function () {
}


searchLst.onEnter = function (res) {
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
    if (empty(res)) {
        searchResult = ''
        get_suggest();
    }
    else {
        searchLst.res = res;
        searchResultLst.pos = 0;
        searchResultLst.prevPos = 0;
        searchResultLst.page = 0;
        searchResultLst.initialisated = -1;
        searchResultLst.showRes(res);

    }

}

searchLst.onChange = function () {


    switch (this.pos) {

        case 0:
            $('search_line').focus();
            stb.ShowVirtualKeyboard();
            console.log('ShowVirtualKeyboard');

            break;
        case 1:
            var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
            if (arr[0] == undefined)
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

searchResultLst.onChange = function () {

    var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');

    arr[this.prevPos].children[0].className = 'stripes_cover';
    arr[this.pos].children[0].className = 'stripes_cover_act';

    $('modal_search').style.display = 'block';
    $('modal_search1').style.display = 'block';
    $('info_page').style.display = 'none';

}

searchResultLst.onLeft = function () {

    currLst.prev();
}


searchResultLst.onRight = function () {

    currLst.next();
}

searchResultLst.onExit = function () {
//searchResultLst.onDown();
    searchLst.onExit();
}

searchResultLst.onDown = function () {

    var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
    arr[this.pos].children[0].className = 'stripes_cover';
    currLst = searchLst;
    currLst.onChange();
}

searchResultLst.onEnter = function () {

    /*var arr = $('modal_search').getElementsByClassName('stripes_horizontal_box');
     arr[this.pos].children[0].className = 'stripes_cover';
     currLst = searchLst;
     currLst.onChange();*/
    prevLst = currLst;
    $('modal_search').style.display = 'none';
    $('modal_search1').style.display = 'none';
    $('info_page').style.display = 'block';
    searchResultLst.offset = this.pos + this.page * this.length;
    searchResultLst.dataset = searchLst.res.video_list;

    if (searchResultLst.initialisated != searchResultLst.offset) {

        searchResultLst.initialisated = searchResultLst.offset;
        switchMovieInfo(searchResultLst);
    }
    currLst = movieInfoLst;
    movieInfoLst.color = 'blue';
    currLst.onChange();
}

searchResultLst.showRes = function (res) {

    if (win.height == 576)
        searchResultLst.length = 3;

    searchResult = searchLst.res.video_list;

    str = '<div class="stripeshorizontal_left"></div>'
    /*searchResult.length*/

    var arr = $('modal_search').getElementsByClassName('stripes_horizontal_act');


    while (arr[0].childElementCount)
        arr[0].removeChild(arr[0].children[0]);

    var fileInfo = '';

    for (i = searchResultLst.page * searchResultLst.length; i < searchResultLst.page * searchResultLst.length + searchResultLst.length; i++) {
        try {
            fileInfo = searchResult[i];

            str += '<div class="stripes_horizontal_box"><div class="stripes_cover"><img width="' + vars[win.height].poster_width + '" height="' + vars[win.height].poster_height + '" src="http://megogo.net' + fileInfo.poster + '" /></div>';
            str += '<div class="stripes_horizontal_title">' + fileInfo.title + '</div>';

            var genre_str = '';
            if (!empty(fileInfo.year)) {
                genre_str += fileInfo.year;
            }

            if (!empty(fileInfo.country)) {
                if (!empty(fileInfo.year))
                    genre_str += ', ';

                if (!empty(countries[fileInfo.country]))
                    genre_str += countries[fileInfo.country];
                else genre_str += fileInfo.country;

            }


            str += '<div class="stripes_horizontal_country">' + genre_str + '</div></div>';
        }
        catch (err) {
        }

    }
    str += '<div class="stripeshorizontal_right"></div>';
    $('modal_search').getElementsByClassName('stripes_horizontal_act')[0].innerHTML = str;

}

searchResultLst.overflow = function () {

    if (this.page == -1)
        this.page = 0;

    if (this.page * this.length >= searchLst.res.video_list.length) {
        //this.pos = 0;
        this.page--;
    }

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


likeLst.onChange = function () {

    $('movieinfo_submenu').children[this.prevPos].className = "movieinfo_menuitem";
    $('movieinfo_submenu').children[this.pos].className = "movieinfo_menuitem_" + vars.menu_items[catLst.pos];
    ;

}


likeLst.onEnter = function () {
    var like = 0;

    if (this.pos) like = 1;

    sendreq(megogoURL + 'p/addvote?' + createSign({'video':fileInfo.id, 'like':like, 'session':session}), setLike);

    $('movieinfo_submenu').children[this.prevPos].className = "movieinfo_menuitem";
    $('movieinfo_submenu').children[this.pos].className = "movieinfo_menuitem";


    currLst = movieInfoLst;
    currLst.onChange();
    $('movieinfo_submenu').style.display = 'none';


}


likeLst.onExit = function () {


    $('movieinfo_submenu').children[this.prevPos].className = "movieinfo_menuitem";
    $('movieinfo_submenu').children[this.pos].className = "movieinfo_menuitem";
    currLst = movieInfoLst;
    currLst.onChange();
    $('movieinfo_submenu').style.display = 'none';


}


//////////////
authLst = new lst();


authLst.id = ['login', 'password', 'auth_ok', 'auth_cancel'];
authLst.length = authLst.id.length;
authLst.res = '';


authLst.reset = function () {

    $('cats_page').style.display = 'none';
    $('auth').style.display = 'block';

    currLst.onChange();

}


authLst.onLeft = function () {
    if (this.pos > 1)
        currLst.prev();

}

authLst.onRight = function () {
    if (this.pos > 1)
        currLst.next();

}

authLst.onRefresh = function () {
}


authLst.onExit = function () {

    //window.location="http://freetv.infomir.com.ua/inet-services/public_html/";
    $('cats_page').style.display = 'block';
    $('auth').style.display = 'none';
    currLst = catLst;
    currLst.onChange();


}


authLst.onEnter = function (res) {

    if (this.id[this.pos] == 'auth_ok') {
        //$('auth').style.display = 'none';
        show_waiting();
        session = '';
        sendreq(iviURL + 'login?' + createSign({'login':$('login').value, 'pwd':$('password').value}), drowheader);


    }
    else
    //if (session != '')
    if (this.id[this.pos] == 'auth_cancel') {


        currLst = catLst;
        currLst.reset();
        $('auth').style.display = 'none';
        $('cats_page').style.display = 'block';

        currLst.onEnter();

    }


    if (this.pos < 2)
        stb.ShowVirtualKeyboard();


    currLst.onChange();

}

authLst.id = ['login', 'password', 'auth_cancel', 'auth_ok'];
authLst.virtKeyb = 0;
authLst.onChange = function () {

    if (this.pos == 0 && this.prevPos == 3)
        this.pos = 3;

    if (this.pos == 3 && this.prevPos == 0)
        this.pos = 0;

    $(this.id[this.pos]).focus();

}

///////////////

//////////////
confirmExitLst.id = ['conf_ok', 'conf_cancel', ];
confirmExitLst.length = confirmExitLst.id.length;


confirmExitLst.onRight = function (res) {

    this.next();
}

confirmExitLst.onLeft = function (res) {

    this.prev();
}


confirmExitLst.onEnter = function (res) {
//back_location = decodeURIComponent(back_location);
    log('referrer:' + _GET['referrer']);

    if (this.pos == 0)
        window.location = _GET['referrer'] || "";       // "http://freetv.infomir.com.ua/inet-services/public_html/";
    else {
        currLst = prevLst;
        $('cats_page').style.display = 'block';
        $('confirmExit').style.display = 'none';
    }

    currLst.onChange();

}


confirmExitLst.onChange = function () {

    $(this.id[this.pos]).focus();

}


//////////////

genreLst = new lst();
genreLst.length = genreLst.id.length;
genreLst.maxLength = 0;


genreLst.reset = function () {

    this.pos = this.prevPos = this.page = 0;
    $('submenu').style.display = 'none';
    this.onChange();


}


genreLst.onChange = function () {
    $('cat_' + catLst.pos).className = str_replace($('cat_' + catLst.pos).className, '_act', '');
    cat = vars.catID[catLst.pos];

    if ($('submenu_genres').style.display == 'none')
        $('submenu_genres').style.display = 'block';

    $('genres_item_' + this.id[this.prevPos]).className = "submenu_genres_item";
    $('genres_item_' + this.id[this.pos]).className = "submenu_genres_item_act_" + vars.menu_items[catLst.pos];

}


genreLst.onEnter = function () {

    prevLst = currLst;
    currLst = extSubCatLst;
    $('footer_sort').style.display = 'block';
    sendreq(megogoURL + 'p/videos?' + createSign({'category':vars.catID[catLst.pos], 'genre':genreList[cat][this.pos + this.page * this.maxLength]['id'], 'sort':sort, 'session':session/*, 'offset':subCatLst.page*cont_page_max, 'limit':cont_page_max+1*/}), init_contentlist);

    currLst.reset();

}

genreLst.onExit = function () {

    $('submenu_genres').style.display = 'none';
    currLst = catLst;
    currLst.onChange();

}

genreLst.overflow = function () {

    $('genres_item_' + this.id[this.prevPos]).className = "submenu_genres_item";

    cat = vars.catID[catLst.pos];

    if (genreList[cat][this.pos + this.page * this.maxLength] == undefined) {
        this.pos = this.prevPos;
        if (this.page > 0)
            this.page--;
        else {
            this.page = 0;
            this.pos = 0;
        }
    }


    initGenriesPage(cat, this.page);
    this.prevPos = this.pos;
    currLst.onChange();

}

///////////////

//////////////


extSubCatLst.initialisated = -1;
extSubCatLst.layers = ['cats_page'];
extSubCatLst.offsetPage = extSubCatLst.page;
extSubCatLst.list = [];
extSubCatLst.offset = 0;
extSubCatLst.layers = ['extsubmenu'];
extSubCatLst.houndres = 0;
extSubCatLst.name = 'extSubCatLst';
extSubCatLst.row = 'ext_video_layer_0';

extSubCatLst.reset = function () {

    $('footer_sort').style.display = 'block';
    this.pos = 0;
    this.prevPos = 0;
    this.page = 0;

    $('cats_page').style.display = 'none';
    $('extsubmenu').style.display = 'block';
    extSubCatLst.houndres = 0;
    this.direct = '';
    extSubCatLst.row = 'ext_video_layer_0';
    //this.onChange();

}

extSubCatLst.onChange = function () {

    if ($('extsubmenu').style.display == 'none') {
        $('extsubmenu').style.display = 'block';
        $('footer_sort').style.display = 'block';
    }


    var arr = $('ext_video_layer').getElementsByClassName('stripes_horizontal_box');

    if (arr[this.prevPos] != undefined)
        arr[this.prevPos].children[0].className = 'stripes_cover';

    if (arr[this.pos] != undefined)
        arr[this.pos].children[0].className = 'stripes_cover';


    arr = $('ext_video_layer_0').getElementsByClassName('stripes_horizontal_box');

    if (arr[this.prevPos] != undefined)
        arr[this.prevPos].children[0].className = 'stripes_cover';

    if (arr[this.pos] != undefined)
        arr[this.pos].children[0].className = 'stripes_cover';


    arr = $(extSubCatLst.row).getElementsByClassName('stripes_horizontal_box');

    if (arr[this.pos] != undefined)
        arr[this.pos].children[0].className = 'stripes_cover_act';
    else this.prev();

    $('stripehorizontal_counter').innerHTML = (this.pos + this.page * this.maxLength + 1) + ' из ' + (this.list.length);
    this.offset = this.page * this.maxLength + this.pos;


}

extSubCatLst.onLeft = function () {

    //this.offset--;
    this.prev();
}

extSubCatLst.onRight = function () {

    /*this.offset++;
     currLst.onChange();*/
    this.next();
}

extSubCatLst.onUp = function () {

    this.page--;
    this.direct = 'prev';
    this.overflow();

}

extSubCatLst.onDown = function () {

    this.direct = 'next';
    this.page++;
    this.overflow();
}


extSubCatLst.onExit = function () {

//prevLst = this;
//	currLst = movieInfoLst;
    $('footer_sort').style.display = 'none';
    $('cats_page').style.display = 'block';
    $('extsubmenu').style.display = 'none';
    currLst = genreLst;
    currLst.onChange();
}

extSubCatLst.overflow = function () {

    var tmp = 0;


    if (this.page < 0) {

        this.page = 0;


        if (this.direct == 'prev' && extSubCatLst.houndres)
            sendreq(megogoURL + 'p/videos?' + createSign({'category':vars.catID[catLst.pos], 'genre':genreList[cat][genreLst.pos]['id'], 'sort':sort, 'session':session, 'offset':(extSubCatLst.houndres - 1) * 100 }), init_contentlist);

        return;

    }

    this.offset = this.page * this.maxLength + this.pos;
    if (this.offset >= this.list.length) {
        if (this.list.length < 100)
            this.page--;
        else {

            var k = extSubCatLst.houndres;


            if (this.direct == 'next')
                if (this.list.length == 100) {
                    currLst.onChange();
                    sendreq(megogoURL + 'p/videos?' + createSign({'category':vars.catID[catLst.pos], 'genre':genreList[cat][genreLst.pos]['id'], 'sort':sort, 'session':session, 'offset':(extSubCatLst.houndres + 1) * 100 }), init_contentlist);
                }
        }

    }
    //this.offset =  this.page*5+this.pos;
    else {

        if (!(this.page % 2)) {

            extSubCatLst.row = 'ext_video_layer_0';

            currLst.onChange();
            if (this.direct == 'prev')
                return;
        }
        else {

            extSubCatLst.row = 'ext_video_layer';

            currLst.onChange();
            if (this.direct == 'next')
                return;

        }

        initHorizontalList(this.list, (this.page - this.page % 2) * this.length);
    }


    currLst.onChange();

}


extSubCatLst.onEnter = function () {

    $('extsubmenu').style.display = 'none';
    $('info_page').style.display = 'block';
    $('footer_sort').style.display = 'none';

    //this.offset =subCatLst.pos;
    this.dataset = this.list;

    //if(subCatLst.initialisated != subCatLst.offset || this.offsetPage != this.page){

    //this.offsetPage = this.page;

    //subCatLst.initialisated = subCatLst.offset;
    this.offset = this.page * this.maxLength + this.pos;
    switchMovieInfo(this);
    this.offset = this.page * this.maxLength + this.pos;

//	}
    prevLst = this;
    currLst = movieInfoLst;

    movieInfoLst.color = vars.menu_items[catLst.pos];
    currLst.reset();
    currLst.onChange();


}


//////////////