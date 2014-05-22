//Footer 
var timer = null,
pictX = 0,
pictY = 0,
realwidth,
realheight,
aspectChange,
aspTimer,
hideDelay;

function getkeyup(e) {
	_debug('getkeyup() keyCode:'+e.keyCode+'; which:'+e.which+' curent page = '+curPage);
	ec = e.keyCode;
	ew = e.which;
	es = e.shiftKey;

	if (curPage === 0) {
		var shift;

		switch (win.width) {
			case 1920:
				shift = 930;
				break;
			case 1280:
				shift = 713;
				break;
			default:
				shift = 396;
				break;
		}

		switch (ec) {
			case 38:
				document.getElementById('pc_authbox'+curAction).style.width = '0px';
				if (curAction) {
					curAction = 0;
				} else {
					curAction = 1;
				}
				document.getElementById('pc_authbox'+curAction).style.width = shift+'px';
				document.getElementById('mainPage_holder_'+curAction).focus();
				break;
			case 40:
				document.getElementById('pc_authbox'+curAction).style.width = '0px';
				if (curAction) {
					curAction = 0;
				} else {
					curAction = 1;
				}
				document.getElementById('pc_authbox'+curAction).style.width = shift+'px';
				document.getElementById('mainPage_holder_'+curAction).focus();
				break;
		}
	}
}

function getkeydown(e) {
	_debug('getkeydown() keyCode:'+e.keyCode+'; which:'+e.which+' curent page = '+curPage);
	
	ec = e.keyCode
	ew = e.which
	es = e.shiftKey
	
	pat = /^(\S+)_(\S+)/
	
	// NOTE!!! Этот код нужен для того, чтобы различать коды, генерируемые пультом ДУ и клавиатуры из-за
	//		 несовместимости обработки клавишных событий различными браузерами.
	// Принимается соглашение:
	//		 Ctrl = 1, Alt = 0, keyCode = 32 (Space) : Это ENTER на клавиатуре и OK на ПДУ
	//		 Ctrl = 0, Alt = 1, keyCode = 32 (Space) : Это SPACE на клавиатуре и MIC на ПДУ
	if (ec == 32 && e.ctrlKey && !e.altKey) {
	ec = 13;
	ew = 13;
	}
	altCtrl = e.altKey ;//&& e.ctrlKey;
	if(altCtrl){
	ec = 0;
	} else {
	if(e.ctrlKey){
	  ew=0;
	} else {
	  if(ec > 90 && ew != 0){
		ec = 0;
	  }
	}
	}
	if (keyEnabled) {

	if (curPage == Main_page) {
			var shift;
		switch(ec){
			case 13: //OK
							if(curAction == 0){
								if(document.getElementById('mainPage_holder_0').value){
									document.getElementById('mainPage_holder_'+curAction).disabled='disabled';
									document.getElementById('mainPage_holder_'+curAction).blur();
									document.getElementById('mainPage').style.display = 'none';
									getData();
									document.getElementById('pc_main').style.display = 'block';
									document.getElementById('pageIndicator').style.display = 'block';
									drowFooter(1);
								}
								else{
									stb.ShowVirtualKeyboard();
								}

							}
							else{
								if(curAction == 1){
									document.getElementById('mainPage_holder_'+curAction).disabled='disabled';
									document.getElementById('mainPage_holder_'+curAction).blur();
									document.getElementById('mainPage').style.display = 'none';
									getSearch(searchPage);
									//document.getElementById('selectionSearch').style.left = '78px';
									//document.getElementById('selectionSearch').style.top = '48px';
									document.getElementById('subscriptS_00').className = 'text_title t16b';
									document.getElementById('subscriptS_20').className = 'text_title t16b';
									document.getElementById('search_form').style.position = 'inherit';
									document.getElementById('search_form').style.display = 'block';
									drowFooter(4);
									return;
								}
								else{
									if(startEdit == 2 && curAction == 2){
										alert('in_progress...');
									}
									else{
										stb.ShowVirtualKeyboard();
										startEdit++;
									}
								}
							}
			break;
						case 27:
							if(curAction != 2){
								if(startEdit == 0){
									if(document.referrer.length > 4) {
										window.location = document.referrer;
									} else {
										window.location = back;
									}
									return;
								}
								document.getElementById('mainPage_holder_'+curAction).blur();
								startEdit = 0;
								_debug('here');
							}
							else{
								if(startEdit == 2){
									startEdit--;
									document.getElementById('mainPage_holder_'+(curAction+startEdit)).blur();
									document.getElementById('mainPage_holder_'+curAction).focus();
								}
								else{
									_debug('here2');
									startEdit = 0;
									document.getElementById('mainPage_holder_'+(curAction+startEdit)).blur();
								}
							}
						break;
						case 112: //RED
			break;
						case 113: //GREEN
						break;
						case 114: //YELLOW
			break;
		}
	}

	if (curPage == Album_page) {
		//alert('test 1');
		switch(ec){
						case 8:
								exitToMain();
								drowFooter(0);
								document.getElementById('mainPage').style.display = 'block';
						break;
			case 27: //EXIT
				exitToMain();
								drowFooter(0);
								document.getElementById('mainPage').style.display = 'block';
			break;
			case 13: //OK
				tempAlbNo = curAlbumPage*albumsAtLine + curAlbumIdx;
				var albumId = album.id[tempAlbNo];
				_debug(''+albumId+'   '+curSelectedItem);
				goInAlbum(userId,albumId);
								drowFooter(2);
								document.getElementById('pageIndicator').style.display = 'none';
			break;
			case 33: //PAGE_UP
				if(curAlbumPage > 0){
									document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
									curAlbumPage--;
									fillPage(curAlbumPage);
									unselectItem(curAlbumIdx,curAlbumPage);
									curSelectedItem = selectItem(0,curAlbumPage);
				}
			break;
			case 34: //PAGE_DOWN
								if(curAlbumPage * albumsAtLine + albumsAtLine < albumCount){
									document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
									curAlbumPage++;
									fillPage(curAlbumPage);
									unselectItem(curAlbumIdx,curAlbumPage);
									curSelectedItem = selectItem(0,curAlbumPage);
								}

			break;
			case 37: //LEFT
				if(curAlbumIdx>0){
									unselectItem(curAlbumIdx,curAlbumPage);
									curAlbumIdx--;
									curSelectedItem = selectItem(curAlbumIdx,curAlbumPage);
				}
								else{
									if(curAlbumPage > 0){
										document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
					curAlbumPage--;
					fillPage(curAlbumPage);
										unselectItem(curAlbumIdx,curAlbumPage);
										curSelectedItem = selectItem(0,curAlbumPage);
									}
								}
			break;
			case 38: //UP
							if(curAlbumPage > 0){
								document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
								curAlbumPage--;
								fillPage(curAlbumPage);
								unselectItem(curAlbumIdx,curAlbumPage);
								curSelectedItem = selectItem(0,curAlbumPage);
							}
			break;
			case 39: //RIGHT
				if(curAlbumIdx<(albumsAtLine - 1)){
									_debug(' '+albumCount+' > '+albumsAtLine+' * '+curAlbumPage+' + '+curAlbumIdx);
									if(albumCount > (albumsAtLine*curAlbumPage + curAlbumIdx + 1)){
					unselectItem(curAlbumIdx,curAlbumPage);
										curAlbumIdx++;
										curSelectedItem = selectItem(curAlbumIdx,curAlbumPage);
									}
				}
								else{
									if(curAlbumPage * albumsAtLine + albumsAtLine < albumCount){
									document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
									curAlbumPage++;
									fillPage(curAlbumPage);
									unselectItem(curAlbumIdx,curAlbumPage);
									curSelectedItem = selectItem(0,curAlbumPage);
									}
								}
			break;
			case 40: //DOWN						  
							if(curAlbumPage * albumsAtLine + albumsAtLine < albumCount){
								document.getElementById('pageInd_'+curAlbumPage).className = 'pc_pagecount_inact';
								curAlbumPage++;
								fillPage(curAlbumPage);
								unselectItem(curAlbumIdx,curAlbumPage);
								curSelectedItem = selectItem(0,curAlbumPage);
							}
			break;
		}
	}

	if (curPage == Pictures_page) {
		//alert('test 2');
		switch(ec){
			case 8: //BACK
				backToAlbums();
								pictX = 0;
								pictY = 0;
								drowFooter(1);
								document.getElementById('pageIndicator').style.display = 'block';
								for(var i = 0;i<4;i++){
									for(var j = 0;j<2;j++){
										document.getElementById('subscript_'+j+''+i).className = 't16w';
									}
								}
								
			break;
			case 27: //EXIT
				backToAlbums();
								pictX = 0;
								pictY = 0;
								drowFooter(1);
								document.getElementById('pageIndicator').style.display = 'block';
								for(var i = 0;i<4;i++){
									for(var j = 0;j<2;j++){
										document.getElementById('subscript_'+j+''+i).className = 't16w';
									}
								}
			break;
			case 13: //OK
								pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
								pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
								_debug('position (x,y) = ('+pictX+','+pictY+')');
								document.getElementById('blackScreen').style.display = 'block';
								goToPict(pictX,pictY);
								return;

			break;
			case 33: //PAGE_UP
				if(curPictPage > 0){
					curPictPage--;
					drowPicture(curPictPage*maxPictureColum*maxPictureLines);
				}
			break;
			case 34: //PAGE_DOWN
				var tempPage = album.pictCount[curSelectedItem+curAlbumPage*albumsAtLine]/(maxPictureLines*maxPictureColum);
								_debug('tempPage'+tempPage%1);
				if(tempPage%1 == 0){
									tempPage = tempPage - tempPage%1 - 1;
								}
								else{
									tempPage--;
								}
								_debug('tempPage = '+tempPage+' > curPictPage = '+curPictPage);
				if(curPictPage < tempPage){
					curPictPage++;
										if(curPictPage == tempPage || curPictPage > tempPage){
											posPictSelector = makeSelectionPict('0');
										}
					drowPicture(curPictPage*maxPictureColum*maxPictureLines);
				}
			break;
			case 37: //LEFT
								if(pictX == 0) {
									if(curPictPage > 0){
									curPictPage--;
									try{
										document.getElementById('subscript_'+pictY+''+pictX).className = ' t16w';
									}catch(e){}
									drowPicture(curPictPage*maxPictureColum*maxPictureLines);
									if(pictY){
										posPictSelector = makeSelectionPict('31');
									}
									else{
										posPictSelector = makeSelectionPict('30');
									}
									pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
									pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
									document.getElementById('subscript_'+pictY+''+pictX).className = ' t16b';
									}
								}
								else{
									posPictSelector = makeSelectionPict('left');
									pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
									pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
									_debug('position (x,y) = ('+pictX+','+pictY+')');
									document.getElementById('subscript_'+pictY+''+pictX).className = ' t16b';
									if(pictX < (maxPictureColum - 1)){
										document.getElementById('subscript_'+pictY+''+(pictX + 1)).className = ' t16w';
									}
								}
								_debug(pictX+' '+curPictPage);
			break;
			case 38: //UP
								
								if(pictY == 0) {
									if(curPictPage > 0){
										document.getElementById('subscript_'+pictY+''+pictX).className = 't16w';
										curPictPage--;
										drowPicture(curPictPage*maxPictureColum*maxPictureLines);
										if(!pictX){
											posPictSelector = makeSelectionPict('01');
										}
										else{
											if(pictX == 1){
												posPictSelector = makeSelectionPict('11');
											}
											else{
												if(pictX == 2){
													posPictSelector = makeSelectionPict('21');
												}
												else{
													posPictSelector = makeSelectionPict('31');
												}
											}
										}
										pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
										pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
										document.getElementById('subscript_'+pictY+''+pictX).className = 't16b';
									}
								}
								else{
									posPictSelector = makeSelectionPict('up');
									pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
									pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
									_debug('position (x,y) = ('+pictX+','+pictY+')');
									document.getElementById('subscript_'+pictY+''+pictX).className = 't16b';
									if(pictY < maxPictureLines - 1){
										document.getElementById('subscript_'+(pictY + 1) +''+pictX).className = 't16w';
									}
								}
			break;
			case 39: //RIGHT
								posPictSelector = makeSelectionPict('right');
								if(pictX == (maxPictureColum - 1)) {
									document.getElementById('subscript_'+pictY+''+pictX).className = 't16w';
									var tempPage = album.pictCount[curSelectedItem+curAlbumPage*albumsAtLine]/(maxPictureLines*maxPictureColum);
									if(tempPage%1 == 0){
										tempPage = tempPage - tempPage%1 - 1;
									}
									else{
										tempPage--;
									}
									if(curPictPage < tempPage){
										if(curPictPage == (tempPage - tempPage%1) || curPictPage > (tempPage - tempPage%1)){
												posPictSelector = makeSelectionPict('0');
											}
											else{
												if(pictY){
													posPictSelector = makeSelectionPict('01');
												}
												else{
													posPictSelector = makeSelectionPict('00');
												}
											}
										curPictPage++;
										drowPicture(curPictPage*maxPictureColum*maxPictureLines);
										pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
										pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
										document.getElementById('subscript_'+pictY+''+pictX).className = ' t16b';
									}


								}
								pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
								pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
								_debug('position (x,y) = ('+pictX+','+pictY+')');
								document.getElementById('subscript_'+pictY+''+pictX).className = 't16b';
								if(pictX){
									document.getElementById('subscript_'+pictY+''+(pictX - 1)).className = 't16w';
								}
								
			break;
			case 40: //DOWN
								try{
								_debug(pictY);
								}catch(e){}
								posPictSelector = makeSelectionPict('down');
								if(!empty(pictY)){
									if(pictY == (maxPictureLines - 1)){
										document.getElementById('subscript_'+pictY+''+pictX).className = 't16w';
										var tempPage = album.pictCount[curSelectedItem+curAlbumPage*albumsAtLine]/(maxPictureLines*maxPictureColum);
										_debug('tempPage'+tempPage%1);
										if((tempPage%1) == 0){
											tempPage = tempPage - tempPage%1 - 1;
										}
										else{
											tempPage--;
										}
										_debug(curPictPage+'|'+tempPage);
										if(curPictPage < tempPage){
											if(curPictPage == (tempPage - tempPage%1) || curPictPage > (tempPage - tempPage%1)){
												posPictSelector = makeSelectionPict('0');
											}
											else{
												if(!pictX){
													posPictSelector = makeSelectionPict('00');
												}
												else{
													if(pictX == 1){
														posPictSelector = makeSelectionPict('10');
													}
													else{
														if(pictX == 2){
															posPictSelector = makeSelectionPict('20');
														}
														else{
															posPictSelector = makeSelectionPict('30');
														}
													}
												}
											}
											curPictPage++;
											drowPicture(curPictPage*maxPictureColum*maxPictureLines);
											pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
											pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
											document.getElementById('subscript_'+pictY+''+pictX).className = 't16b';
										}
									}
								}
								else{
									pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
									pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
									_debug('position (x,y) = ('+pictX+','+pictY+')');
									document.getElementById('subscript_'+pictY+''+pictX).className = 't16b';
									if(pictY){
										document.getElementById('subscript_'+(pictY - 1) +''+pictX).className = '16w';
									}
								}

			break;
						case 112: //RED
								pictX = (posPictSelector[0] / horShift) - (posPictSelector[0] / horShift)%1;
								pictY = (posPictSelector[1] / verShift) - (posPictSelector[1] / verShift)%1;
								_debug('position (x,y) = ('+pictX+','+pictY+')');
								document.getElementById('blackScreen').style.display = 'block';
								goToPict(pictX,pictY);

								timer = setInterval(function (){
								if(!slideshowType){
									pictureChange('right');
								}
								else{
									pictureChange('shuffle');
								}
								},slDelayArr[slideshowDelay]);
								return;
						break;
						case 113:  //GREEN
							if(slideshowDelay < 7){
								slideshowDelay++;
							}
							else{
								slideshowDelay = 0;
							}
							if(slideshowDelay<4){
								document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/1000)+ ' ' + sec;
							}
							else{
								document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/60000)+ ' ' + min;
							}
						break;
						case 114:  //YELLOW
							if(slideshowType < 1){
								slideshowType++;
							}
							else{
								slideshowType = 0;
							}
							_debug(slideshowType);
							switch(win.width){
								case 1920:
									document.getElementById('yellow').innerHTML = '<img style="margin-left:15px; margin-top:-9px; width:40px; height:50px;" src="img/footer_menu_ico'+slideshowType+'_picasa.png" alt=""/>';
								break;
								default:
									document.getElementById('yellow').innerHTML = '<img style="margin-left:5px; margin-top:-7px;" src="img/footer_menu_ico'+slideshowType+'_picasa.png" alt=""/>';
								break;
							}
							

						break;
						case 117:
							if(aspect<2){
								aspect++;
							}
							else{
								aspect = 0;
							}
							document.getElementById('aspectInd').innerHTML = '<img src="img/en/aspect_'+aspectInd[aspect]+'.png" alt=""/>'
							document.getElementById('aspectInd').style.display = 'block';
							hideDelay = setTimeout(function (){
								_debug('delay passed');
								_debug(document.getElementById('aspectInd').innerHTML);
								document.getElementById('aspectInd').style.display = 'none';
							}
								, 3000);
						break;
						
		}
	}

	if (curPage == SinglePicture_page) {
		switch(ec){
			case 8: //BACK
								pictureRealSize.height = null;
								pictureRealSize.width = null;
								realwidth = 0;
								realheight = 0;
								if(!empty(timer)) {
									clearInterval(timer);
									timer = null;
								}
				goBack();
								document.getElementById('footer').style.visibility = 'visible';
								document.getElementById('footer').style.display = 'block';
								document.getElementById('blackScreen').style.display = 'none';
								document.getElementById('inform').style.display = 'none';
								showInfo = 0;
								document.getElementById('pictureBig_0').innerHTML = '';
			break;
						case 13:
							if(showInfo){
								_debug('disable info');
								document.getElementById('inform').style.display = 'none';
								showInfo = 0;
							}
							else{
								_debug('enable info');
								document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
								showInfo = 1;
							}
						break;
			case 27: //EXIT
								pictureRealSize.height = null;
								pictureRealSize.width = null;
								realwidth = 0;
								realheight = 0;
								if(!empty(timer)) {
									clearInterval(timer);
									timer = null;
								}
				goBack();
								document.getElementById('footer').style.visibility = 'visible';
								document.getElementById('footer').style.display = 'block';
								document.getElementById('blackScreen').style.display = 'none';
								document.getElementById('inform').style.display = 'none';
								showInfo = 0;
								document.getElementById('pictureBig_0').innerHTML = '';
			break;
			case 33: //PAGE_UP
								realwidth = 0;
								realheight = 0;
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								clearInterval(timer);
								pictureChange('left');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
						case 37: //LEFT
								realwidth = 0;
								realheight = 0;
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								clearInterval(timer);
								pictureChange('left');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
						case 38: //UP
								realwidth = 0;
								realheight = 0;
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								clearInterval(timer);
								pictureChange('left');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
						case 39: //RIGHT
								realwidth = 0;
								realheight = 0;
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								clearInterval(timer);
								pictureChange('right');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
						case 40: //DOWN
								realwidth = 0;
								realheight = 0;  //
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								clearInterval(timer);
								pictureChange('right');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
			case 34: //PAGE_DOWN
								//pictureRealSize.height = null;
								//pictureRealSize.width = null;
								realwidth = 0;
								realheight = 0;
								clearInterval(timer);
								pictureChange('right');
								//document.getElementById('inform').style.display = 'block';
								document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
			break;
						case 9:
							if(es){
								clearInterval(timer);
								pictureChange('left');
							}
							else{
								clearInterval(timer);
								pictureChange('right');
							}
							//document.getElementById('inform').style.display = 'block';
							document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';
						break;
						case 112:
							if(!empty(timer)) {
								clearInterval(timer);
								timer = null;
								document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_pause.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
								setTimeout(function (){
									document.getElementById('paused').innerHTML ='';
								},2000);
								
							}
							else{
								timer = setInterval(function (){
									if(!slideshowType){
										pictureChange('right');
										document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';//tut
									}
									else{
										pictureChange('shuffle');
										document.getElementById('inform').innerHTML = picture.names[tempPictNo]+'\n';//tut
									}
										},slDelayArr[slideshowDelay]);
										document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_play.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
										setTimeout(function (){
											document.getElementById('paused').innerHTML ='';
										},2000);
							}
						break;
						case 116:
							switch(pictureAngle){
								case 0:
									_debug('---- 0 deg ----');
									document.getElementById('pictureBig').className = 'deg0';
									if(!empty(realwidth)){
										document.getElementById('pictureBig').style.width = realwidth+'px';
									}
									else{
										realwidth = (document.getElementById('pictureBig').style.width).match(/\d*/);
									}
									if(!empty(realheight)){
										document.getElementById('pictureBig').style.height = realheight+'px';
									}
									else{
										realheight = document.getElementById('pictureBig').style.height.match(/\d*/);
									}
									document.getElementById('pictureBig').style.marginLeft = (((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)-((win.width)/70))+'px';
									document.getElementById('pictureBig').style.marginTop = (((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2)+10) +'px';
									_debug('realheight '+realheight);
									_debug('realwidth '+realwidth);
									pictureAngle = 1;
								break;

								case 1:
									_debug('---- 90 deg ----');
									document.getElementById('pictureBig').className = 'deg90';
									if(empty(realheight)){
										realheight = document.getElementById('pictureBig').style.height.match(/\d*/);
									}
									if(empty(realwidth)){
										realwidth = document.getElementById('pictureBig').style.width.match(/\d*/);
									}
									_debug('realheight '+realheight);
									_debug('realwidth '+realwidth);
									if(aspect == 1){
										//document.getElementById('pictureBig').style.marginLeft = (((win.width - document.getElementById('pictureBig').style.height.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
										if((document.getElementById('pictureBig').style.width).match(/\d*/) > win.height ){
											_debug('picture is wure 4em vusota okna');
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.height = (((((document.getElementById('pictureBig').style.height.match(/\d*/))/realwidth)*win.height)/182)*100)+'px';
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}
										else{
											_debug('++++++++++++++++++picture ne wure 4em vusota okna');
											var coef = win.height/document.getElementById('pictureBig').style.width.match(/\d*/);
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.marginLeft = (((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)-(win.width/70))+'px';
											document.getElementById('pictureBig').style.height = ((((document.getElementById('pictureBig').style.height.match(/\d*/))/182)*100)*coef)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}

									}
									else{
										if((document.getElementById('pictureBig').style.width).match(/\d*/) > win.height ){
											_debug('picture is wure 4em vusota okna');
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.height = (((document.getElementById('pictureBig').style.height.match(/\d*/))/realwidth)*win.height)+'px';
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';

										}
										else{
											_debug('picture ne wure 4em vusota okna');
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}
									}
									_debug('marginTop'+document.getElementById('pictureBig').style.marginTop);
									_debug('marginLeft'+document.getElementById('pictureBig').style.marginLeft);
									_debug('Left'+document.getElementById('pictureBig').style.left);
									_debug('document.getElementById("pictureBig").style.width --- '+document.getElementById('pictureBig').style.width+'\n document.getElementById("pictureBig").style.height --- '+ document.getElementById('pictureBig').style.height);
									pictureAngle = 2;
								break;

								case 2:
									_debug('---- 180 deg ----');
									_debug('realheight '+realheight);
									_debug('realwidth '+realwidth);
									if(!empty(realheight)){
										document.getElementById('pictureBig').style.height = realheight+'px';
									}
									else{
										realheight = document.getElementById('pictureBig').style.height.match(/\d*/);
									}
									if(empty(realwidth)){
										realwidth = document.getElementById('pictureBig').style.width.match(/\d*/);
									}
									else{
										document.getElementById('pictureBig').style.width = realwidth+'px';
									}
									_debug('win.width'+win.width);
									_debug('document.getElementById("pictureBig").style.width.match(/\d*/)'+document.getElementById('pictureBig').style.width.match(/\d*/));
									document.getElementById('pictureBig').style.marginTop = '0px';
									if(aspect == 1){
										_debug('aspect is 1');
										 document.getElementById('pictureBig').style.marginLeft = ((((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)-((win.width)/70))/*+(win.width/5)*/)+'px';
										 document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
									}
									else{
										_debug('aspect not 1');
										_debug('marginLeft'+document.getElementById('pictureBig').style.marginLeft);
										document.getElementById('pictureBig').style.marginLeft = ((((win.width - (document.getElementById('pictureBig').style.width.match(/\d*/)))/2)-((win.width)/70))/*+(win.width/5)*/)+'px';
										_debug('marginLeft'+document.getElementById('pictureBig').style.marginLeft);
										document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
									}

									//document.getElementById('pictureBig').style.left = ((win.width - (document.getElementById('pictureBig').style.width).match(/\d*/))/2)+'px'
									document.getElementById('pictureBig').className = 'deg180';
									document.getElementById('pictureBig').style.width = realwidth+'px';

									_debug('marginLeft'+document.getElementById('pictureBig').style.marginLeft);
									_debug('Left'+document.getElementById('pictureBig').style.left);
									_debug('document.getElementById("pictureBig").style.width --- '+document.getElementById('pictureBig').style.width);
									_debug('document.getElementById("pictureBig").style.height --- '+ document.getElementById('pictureBig').style.height);
									pictureAngle = 3;
								break;

								case 3:
									_debug('---- 270 deg ----');
									document.getElementById('pictureBig').className = 'deg270';
									if(empty(realheight)){
										realheight = document.getElementById('pictureBig').style.height.match(/\d*/);
									}
									else{
										document.getElementById('pictureBig').style.height = realheight+'px';
									}
									if(empty(realwidth)){
										realwidth = document.getElementById('pictureBig').style.width.match(/\d*/);
									}
									else{
										document.getElementById('pictureBig').style.width = realwidth+'px';
									}
									if(aspect == 1){
										//document.getElementById('pictureBig').style.marginLeft = (((win.width - document.getElementById('pictureBig').style.height.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
										if((document.getElementById('pictureBig').style.width).match(/\d*/) > win.height ){
											_debug('picture is wure 4em vusota okna');
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.height = (((((document.getElementById('pictureBig').style.height.match(/\d*/))/realwidth)*win.height)/182)*100)+'px';
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}
										else{
											_debug('++++++++++++++++++picture ne wure 4em vusota okna');
											var coef = win.height/document.getElementById('pictureBig').style.width.match(/\d*/);
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.marginLeft = (((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)-(win.width/70))+'px';
											document.getElementById('pictureBig').style.height = ((((document.getElementById('pictureBig').style.height.match(/\d*/))/182)*100)*coef)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}

									}
									else{
										if((document.getElementById('pictureBig').style.width).match(/\d*/) > win.height ){
											_debug('picture is wure 4em vusota okna');
											document.getElementById('pictureBig').style.width = win.height+'px';
											document.getElementById('pictureBig').style.height = (((document.getElementById('pictureBig').style.height.match(/\d*/))/realwidth)*win.height)+'px';
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';

										}
										else{
											_debug('picture ne wure 4em vusota okna');
											document.getElementById('pictureBig').style.marginLeft = ((win.width - document.getElementById('pictureBig').style.width.match(/\d*/))/2)+'px';
											document.getElementById('pictureBig').style.marginTop = ((win.height - document.getElementById('pictureBig').style.height.match(/\d*/))/2) +'px';
										}
									}
									_debug('marginTop'+document.getElementById('pictureBig').style.marginTop);
									_debug('marginLeft'+document.getElementById('pictureBig').style.marginLeft);
									_debug('Left'+document.getElementById('pictureBig').style.left);
									_debug('document.getElementById("pictureBig").style.width --- '+document.getElementById('pictureBig').style.width+'\n document.getElementById("pictureBig").style.height --- '+ document.getElementById('pictureBig').style.height);
									_debug('realheight '+realheight);
									_debug('realwidth '+realwidth);
									pictureAngle = 0;
								break;
							}
						break;
						case 117:
							if(!empty(aspTimer)) {
								clearInterval(aspTimer);
								aspTimer = null;
							}
							aspTimer = setTimeout(function(){
							aspectChange = 0;
							}, 3000);
							if(aspectChange){
								if(aspect<4){
									aspect++;
								}
								else{
									aspect = 0;
								}
							}
							document.getElementById('aspectInd').innerHTML = '<img src="img/en/aspect_'+aspectInd[aspect]+'.png" alt=""/>'
							document.getElementById('aspectInd').style.display = 'block';
							if(aspectChange){
								doIt();
							}
							if(!empty(hideDelay)) {
								clearInterval(hideDelay);
								hideDelay = null;
							}
							hideDelay = setTimeout(function (){
								_debug('delay passed');
								_debug(document.getElementById('aspectInd').innerHTML);
								document.getElementById('aspectInd').style.display = 'none';
							}
								, 3000);
							aspectChange = 1;
						break;

		}
				if(ew == 114){
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
						document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_pause.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
						setTimeout(function (){
							document.getElementById('paused').innerHTML ='';
						},2000);
						
					}
					else{
						timer = setInterval(function (){
							if(!slideshowType){
								pictureChange('right');
							}
							else{
								pictureChange('shuffle');
							}
								},slDelayArr[slideshowDelay]);
								document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_play.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
								setTimeout(function (){
									document.getElementById('paused').innerHTML ='';
								},2000);
					}

				}
				if(ew == 115){
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					goBack();
					document.getElementById('footer').style.visibility = 'visible';
					document.getElementById('footer').style.display = 'block';
					document.getElementById('blackScreen').style.display = 'none';
					document.getElementById('pictureBig_0').innerHTML = '';
					document.getElementById('inform').style.display = 'none';
					showInfo = 0;

				}
	}

	if (curPage === Search_page) {
		_debug('**** Search_page ****');

		switch (ec) {
			case 8: //BACK
				exitToMain();
				drowFooter(0);
				document.getElementById('mainPage').style.display = 'block';
				for(var i = 0;i<4;i++){
					for(var j = 0;j<4;j++){
						document.getElementById('subscriptS_'+j+''+i).className = 'text_title t16w';
					}
				}
				break;
			case 27: //EXIT
				exitToMain();
				drowFooter(0);
				document.getElementById('mainPage').style.display = 'block';
				for(var i = 0;i<4;i++){
					for(var j = 0;j<4;j++){
						document.getElementById('subscriptS_'+j+''+i).className = 'text_title t16w';
					}
				}
				break;
			case 13: //OK
				var posX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
				var posY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
				_debug('position (x,y) = ('+posX+','+posY+')');
				document.getElementById('red').style.visibility = 'hidden';
				showCurPict(posX,posY);
				drowFooter(5);
				document.getElementById('mb_header_pc').style.visibility = 'hidden';
				break;
			case 33:  //Page Up
				if (searchPage) {
					searchPage--;
					getSearch(searchPage)
				}
				break;
			case 34:  //PageDown
				_debug('(searchPage+1)*maxPictureLines*maxPictureColum '+((searchPage+1)*maxPictureLines*maxPictureColum)+' searchCount[12] '+searchCount[12]);
				if ((searchPage+1)*maxPictureLines*maxPictureColum < parseInt(searchCount[12])) {
					searchPage++;
					getSearch(searchPage);
				}
				break;
			case 37: //LEFT
				if (empty(pictX)) {
					if (searchPage) {
						searchPage--;
						getSearch(searchPage);
						document.getElementById('subscriptS_'+pictY+''+(pictX)).className = 'text_title t16w';
						document.getElementById('subscriptS_'+(pictY+2)+''+(pictX)).className = 'text_title t16w';
						if (pictY) {
							posPictSelector = makeSelectionPict('31');
						} else {
							posPictSelector = makeSelectionPict('30');
						}
						pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
						pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
						document.getElementById('subscriptS_'+pictY+''+(pictX)).className = 'text_title t16b';
						document.getElementById('subscriptS_'+(pictY+2)+''+(pictX)).className = 'text_title t16b';
					}
				} else {
					posPictSelector = makeSelectionPict('left');
					pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
					pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
					_debug('position (x,y) = ('+pictX+','+pictY+')');
					if (pictX > 0) {
						document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
						document.getElementById('subscriptS_'+(pictY+2)+''+pictX).className = 'text_title t16b';
						document.getElementById('subscriptS_'+pictY+''+(pictX+1)).className = 'text_title t16w';
						document.getElementById('subscriptS_'+(pictY+2)+''+(pictX+1)).className = 'text_title t16w';
					} else {
						document.getElementById('subscriptS_'+pictY+'0').className = 'text_title t16b';
						document.getElementById('subscriptS_'+(pictY+2)+'0').className = 'text_title t16b';
						document.getElementById('subscriptS_'+pictY+'1').className = 'text_title t16w';
						document.getElementById('subscriptS_'+(pictY+2)+'1').className = 'text_title t16w';
					}
				}
				break;
			case 38: //UP
				if (posPictSelector[1]) {
					pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
					if (pictY == 0) {
						if (searchPage) {
							searchPage--;
							getSearch(searchPage);
							document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16w';
							if (!pictX) {
								posPictSelector = makeSelectionPict('01');
							} else {
								if (pictX == 1) {
									posPictSelector = makeSelectionPict('11');
								} else {
									if (pictX == 2) {
										posPictSelector = makeSelectionPict('21');
									} else {
										posPictSelector = makeSelectionPict('31');
									}
								}
							}
							pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
							pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
							document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16b';
						}
					} else {
						posPictSelector = makeSelectionPict('up');
						pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
						pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
						_debug('position (x,y) = ('+pictX+','+pictY+')');
						if (pictY > 0) {
							document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY + 1)+''+pictX).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(pictY + 3)+''+pictX).className = 'text_title t16w';
						} else {
							document.getElementById('subscriptS_0'+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_2'+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_1'+pictX).className = 'text_title t16w';
							document.getElementById('subscriptS_3'+pictX).className = 'text_title t16w';
						}
					}
				}
				break;
			case 39: //RIGHT
				if (pictX == (maxPictureColum - 1)) {
					if ((searchPage+1)*maxPictureLines*maxPictureColum < parseInt(searchCount[12])) {
						searchPage++;
						getSearch(searchPage);
						document.getElementById('subscriptS_'+pictY+''+(pictX)).className = 'text_title t16w';
						document.getElementById('subscriptS_'+(pictY+2)+''+(pictX)).className = 'text_title t16w';
						if (pictY) {
							posPictSelector = makeSelectionPict('01');
						} else {
							posPictSelector = makeSelectionPict('00');
						}
						pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
						pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
						document.getElementById('subscriptS_'+pictY+''+(pictX)).className = 'text_title t16b';
						document.getElementById('subscriptS_'+(pictY+2)+''+(pictX)).className = 'text_title t16b';
					}
				} else {
					posPictSelector = makeSelectionPict('right');
					pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
					pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
					if ((searchCount[12] - (searchPage*maxPictureLines*maxPictureColum)) > (pictX + (pictY*maxPictureColum))) {
						if (pictX < (maxPictureColum - 1)) {
							document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY+2)+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+pictY+''+(pictX-1)).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(pictY + 2)+''+(pictX-1)).className = 'text_title t16w';
						} else {
							document.getElementById('subscriptS_'+pictY+''+(maxPictureColum-1)).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY+2)+''+(maxPictureColum-1)).className = 'text_title t16b';
							document.getElementById('subscriptS_'+pictY+''+(maxPictureColum-2)).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(pictY+2)+''+(maxPictureColum-2)).className = 'text_title t16w';
						}
					}
				}
				break;
			case 40: //DOWN
				pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
				if (pictY == (maxPictureLines - 1)) {
					if ((searchPage+1)*maxPictureLines*maxPictureColum < parseInt(searchCount[12])) {
						searchPage++;
						getSearch(searchPage);
						document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16w';
						document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16w';
						if (!pictX) {
							posPictSelector = makeSelectionPict('00');
						} else {
							if (pictX == 1) {
								posPictSelector = makeSelectionPict('10');
							} else {
								if (pictX == 2) {
									posPictSelector = makeSelectionPict('20');
								} else {
									posPictSelector = makeSelectionPict('30');
								}
							}
						}
						pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
						pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
						document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
						document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16b';
					}
				} else {
					posPictSelector = makeSelectionPict('down');
					pictX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
					pictY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
					_debug('position (x,y) = ('+pictX+','+pictY+')');
					if ((searchCount[12] - (searchPage*maxPictureLines*maxPictureColum)) > maxPictureColum) {
						if (pictY < (maxPictureLines - 1)) {
							document.getElementById('subscriptS_'+pictY+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY + 2)+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(pictY - 1)+''+pictX).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(pictY + 1)+''+pictX).className = 'text_title t16w';
						} else {
							document.getElementById('subscriptS_'+(maxPictureLines-1)+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(maxPictureLines+1)+''+pictX).className = 'text_title t16b';
							document.getElementById('subscriptS_'+(maxPictureLines-2)+''+pictX).className = 'text_title t16w';
							document.getElementById('subscriptS_'+(maxPictureLines)+''+pictX).className = 'text_title t16w';
						}
					}
				}
				break;
			case 112:
				var posX = (posPictSelector[0] / horShiftSear) - (posPictSelector[0] / horShiftSear)%1;
				var posY = (posPictSelector[1] / verShiftSear) - (posPictSelector[1] / verShiftSear)%1;
				_debug('position (x,y) = ('+posX+','+posY+')');
				document.getElementById('red').style.visibility = 'hidden';
				showCurPict(posX,posY);
				timer = setInterval(function() {
					changePict('right');
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				}, slDelayArr[slideshowDelay]);
				drowFooter(5);
				document.getElementById('mb_header_pc').style.visibility = 'hidden';
				return;
				break;
				case 113:
				if (slideshowDelay < 7) {
					slideshowDelay++;
				} else {
					slideshowDelay = 0;
				}
				if (slideshowDelay==4||slideshowDelay==5||slideshowDelay==6) {
					document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/60000)+ ' ' + min;
				} else {
					if (slideshowDelay==0) {
						document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/1000)+ ' ' + sec;
					} else {
						if (slideshowDelay==1||slideshowDelay==2||slideshowDelay==3) {
							document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/1000)+ ' ' + sec;
						} else {
							document.getElementById('green').innerText = sl_delay + ' ' +(slDelayArr[slideshowDelay]/60000)+ ' ' + min;
						}
					}
				}
				break;
			case 117:
				if(aspect<2){
					aspect++;
				} else {
					aspect = 0;
				}
				document.getElementById('aspectInd').innerHTML = '<img src="img/en/aspect_'+aspectInd[aspect]+'.png" alt=""/>'
				document.getElementById('aspectInd').style.display = 'block';
				hideDelay = setTimeout(function() {
					_debug('delay passed');
					_debug(document.getElementById('aspectInd').innerHTML);
					document.getElementById('aspectInd').style.display = 'none';
				}, 3000);
				break;
		}
	}

	if (curPage == Searched_pict) {
			switch(ec){
				case 8: //BACK
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					drowFooter(4);
					document.getElementById('search_form').style.display = 'block';
					document.getElementById('blackScreen').style.display = 'none';
					document.getElementById('pictureBig_0').style.display = 'none';
					document.getElementById('pictureBig_1').style.display = 'none';
					document.getElementById('inform').style.display = 'none';
					showInfo = 0;
					document.getElementById('red').style.visibility = 'visible';
					document.getElementById('mb_header_pc').style.visibility = 'visible';
					pictureRealSize.height = null;
					curPage = Search_page;
				break;
				case 13:
					if(showInfo){
						_debug('disable info');
						document.getElementById('inform').style.display = 'none';
						showInfo = 0;
					}
					else{
						_debug('enable info');
						document.getElementById('inform').style.display = 'block';
						document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
						document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
						showInfo = 1;
					}
				break;
				case 27: //EXIT
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					drowFooter(4);
					document.getElementById('search_form').style.display = 'block';
					document.getElementById('blackScreen').style.display = 'none';
					document.getElementById('pictureBig_0').style.display = 'none';
					document.getElementById('pictureBig_1').style.display = 'none';
					document.getElementById('inform').style.display = 'none';
					showInfo = 0;
					document.getElementById('red').style.visibility = 'visible';
					document.getElementById('mb_header_pc').style.visibility = 'visible';
					pictureRealSize.height = null;
					curPage = Search_page;
				break;
				case 37:
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					changePict('left');
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				break;
				case 39:
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					changePict('right');
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				break;
				case 38:
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					changePict('left');
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				break;
				case 40:
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					changePict('right');
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				break;
				case 9:
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					if(es){
						changePict('left');
					}
					else{
						changePict('right');
					}
					document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
					document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
				break;
				case 112:
					if(!empty(timer)) {
							clearInterval(timer);
							timer = null;
							document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_pause.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
							setTimeout(function (){
								document.getElementById('paused').innerHTML ='';
							},2000);
					}
					else{
						timer = setInterval(function (){
								changePict('right');
								document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
								document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
								},slDelayArr[slideshowDelay]);
								document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_play.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
								setTimeout(function (){
									document.getElementById('paused').innerHTML ='';
								},2000);
					}
				break;
				case 114:
					if(aspect<2){
						aspect++;
					}
					else{
						aspect = 0;
					}
					resize();
				break;
				case 116:
					switch(pictureAngle){
						case 0:
							_debug('---- 0 deg ----');
							if(aspect != 3){
								document.getElementById('BigPict').className = 'deg0';
							}
							else{
								document.getElementById('BigPict').className = 'deg0 enlarge1';
							}
							if(!empty(realwidth)){
								document.getElementById('BigPict').style.width = realwidth+'px';
							}
							else{
								realwidth = (document.getElementById('BigPict').style.width).match(/\d*/);
							}
							if(!empty(realheight)){
								document.getElementById('BigPict').style.height = realheight+'px';
							}
							else{
								realheight = document.getElementById('BigPict').style.height.match(/\d*/);
							}
							if(aspect == 1 && win.width==720){
								 document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
							}
							else{
								 document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
							}
							document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
							_debug('realheight '+realheight);
							_debug('realwidth '+realwidth);
							pictureAngle = 1;
						break;

						case 1:
							_debug('---- 90 deg ----');
							if(aspect != 3){
								document.getElementById('BigPict').className = 'deg90';
							}
							else{
								document.getElementById('BigPict').className = 'deg90 enlarge1';
							}
							
							if(empty(realheight)){
								realheight = document.getElementById('BigPict').style.height.match(/\d*/);
							}
							if(empty(realwidth)){
								realwidth = document.getElementById('BigPict').style.width.match(/\d*/);
							}
							_debug('realheight '+realheight);
							_debug('realwidth '+realwidth);
							if(aspect == 1 && win.width==720){
								//document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.height.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
								if((document.getElementById('BigPict').style.width).match(/\d*/) > win.height ){
									_debug('picture is wure 4em vusota okna');
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.height = (((((document.getElementById('BigPict').style.height.match(/\d*/))/realwidth)*win.height)/182)*100)+'px';
									document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)-(win.width/70))+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
								else{
									_debug('++++++++++++++++++picture ne wure 4em vusota okna');
									var coef = win.height/document.getElementById('BigPict').style.width.match(/\d*/);
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.height = ((((document.getElementById('BigPict').style.height.match(/\d*/))/182)*100)*coef)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}

							}
							else{
								if((document.getElementById('BigPict').style.width).match(/\d*/) > win.height ){
									_debug('picture is wure 4em vusota okna');
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.height = (((document.getElementById('BigPict').style.height.match(/\d*/))/realwidth)*win.height)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
								else{
									_debug('picture ne wure 4em vusota okna');
									//document.getElementById('BigPict').style.height = (((document.getElementById('BigPict').style.height.match(/\d*/))/142)*100)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
							}
							_debug('marginTop'+document.getElementById('BigPict').style.marginTop);
							_debug('marginLeft'+document.getElementById('BigPict').style.marginLeft);
							_debug('Left'+document.getElementById('BigPict').style.left);
							_debug('document.getElementById("BigPict").style.width --- '+document.getElementById('BigPict').style.width+'\n document.getElementById("BigPict").style.height --- '+ document.getElementById('BigPict').style.height);
							pictureAngle = 2;
						break;

						case 2:
							_debug('---- 180 deg ----');
							_debug('realheight '+realheight);
							_debug('realwidth '+realwidth);
							if(!empty(realheight)){
								document.getElementById('BigPict').style.height = realheight+'px';
							}
							else{
								realheight = document.getElementById('BigPict').style.height.match(/\d*/);
							}
							if(empty(realwidth)){
								realwidth = document.getElementById('BigPict').style.width.match(/\d*/);
							}
							else{
								document.getElementById('BigPict').style.width = realwidth+'px';
							}
							_debug('win.width'+win.width);
							_debug('document.getElementById("BigPict").style.width.match(/\d*/)'+document.getElementById('BigPict').style.width.match(/\d*/));
							document.getElementById('BigPict').style.marginTop = '0px';
							if(aspect == 1 && win.width==720){
								_debug('aspect is 1');
								 document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
							}
							else{
								_debug('aspect not 1');
								_debug('marginLeft'+document.getElementById('BigPict').style.marginLeft);
								document.getElementById('BigPict').style.marginLeft = (((win.width - (document.getElementById('BigPict').style.width.match(/\d*/)))/2)/*+(win.width/5)*/)+'px';
								_debug('marginLeft'+document.getElementById('BigPict').style.marginLeft);
							}
							
							//document.getElementById('BigPict').style.left = ((win.width - (document.getElementById('BigPict').style.width).match(/\d*/))/2)+'px'
							if(aspect != 3){
								document.getElementById('BigPict').className = 'deg180';
							}
							else{
								document.getElementById('BigPict').className = 'deg180 enlarge1';
							}
							document.getElementById('BigPict').style.width = realwidth+'px';
							document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
							_debug('marginLeft'+document.getElementById('BigPict').style.marginLeft);
							_debug('Left'+document.getElementById('BigPict').style.left);
							_debug('document.getElementById("BigPict").style.width --- '+document.getElementById('BigPict').style.width);
							_debug('document.getElementById("BigPict").style.height --- '+ document.getElementById('BigPict').style.height);
							pictureAngle = 3;
						break;

						case 3:
							_debug('---- 270 deg ----');
							if(aspect != 3){
								document.getElementById('BigPict').className = 'deg270';
							}
							else{
								document.getElementById('BigPict').className = 'deg270 enlarge1';
							}
							if(empty(realheight)){
								realheight = document.getElementById('BigPict').style.height.match(/\d*/);
							}
							else{
								document.getElementById('BigPict').style.height = realheight+'px';
							}
							if(empty(realwidth)){
								realwidth = document.getElementById('BigPict').style.width.match(/\d*/);
							}
							else{
								document.getElementById('BigPict').style.width = realwidth+'px';
							}
							if(aspect == 1 && win.width==720){
								//document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.height.match(/\d*/))/2)/*+(win.width/5)*/)+'px';
								if((document.getElementById('BigPict').style.width).match(/\d*/) > win.height ){
									_debug('picture is wure 4em vusota okna');
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.height = (((((document.getElementById('BigPict').style.height.match(/\d*/))/realwidth)*win.height)/182)*100)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
								else{
									_debug('++++++++++++++++++picture ne wure 4em vusota okna');
									var coef = win.height/document.getElementById('BigPict').style.width.match(/\d*/);
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.marginLeft = (((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)-(win.width/70))+'px';
									document.getElementById('BigPict').style.height = ((((document.getElementById('BigPict').style.height.match(/\d*/))/182)*100)*coef)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}

							}
							else{
								if((document.getElementById('BigPict').style.width).match(/\d*/) > win.height ){
									_debug('picture is wure 4em vusota okna');
									document.getElementById('BigPict').style.width = win.height+'px';
									document.getElementById('BigPict').style.height = (((document.getElementById('BigPict').style.height.match(/\d*/))/realwidth)*win.height)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
								else{
									_debug('picture ne wure 4em vusota okna');
									//document.getElementById('BigPict').style.height = (((document.getElementById('BigPict').style.height.match(/\d*/))/142)*100)+'px';
									document.getElementById('BigPict').style.marginLeft = ((win.width - document.getElementById('BigPict').style.width.match(/\d*/))/2)+'px';
									document.getElementById('BigPict').style.marginTop = ((win.height - document.getElementById('BigPict').style.height.match(/\d*/))/2) +'px';
								}
							}
							_debug('marginTop'+document.getElementById('BigPict').style.marginTop);
							_debug('marginLeft'+document.getElementById('BigPict').style.marginLeft);
							_debug('Left'+document.getElementById('BigPict').style.left);
							_debug('document.getElementById("BigPict").style.width --- '+document.getElementById('BigPict').style.width+'\n document.getElementById("BigPict").style.height --- '+ document.getElementById('BigPict').style.height);
							_debug('realheight '+realheight);
							_debug('realwidth '+realwidth);
							pictureAngle = 0;
						break;
					}
				break;
				case 117:
					if(!empty(aspTimer)) {
						clearInterval(aspTimer);
						aspTimer = null;
					}
					aspTimer = setTimeout(function(){
						aspectChange = 0;
						}, 3000);
					if(aspectChange){
						if(aspect<4){
							aspect++;
						}
						else{
							aspect = 0;
						}
						realwidth = 0;
						realheight = 0;
					}
					document.getElementById('aspectInd').innerHTML = '<img src="img/en/aspect_'+aspectInd[aspect]+'.png" alt=""/>'
					document.getElementById('aspectInd').style.display = 'block';
					if(aspectChange){
						resize();
					}
					if(!empty(hideDelay)) {
						clearInterval(hideDelay);
						hideDelay = null;
					}
					hideDelay = setTimeout(function (){
						_debug('delay passed');
						_debug(document.getElementById('aspectInd').innerHTML);
						document.getElementById('aspectInd').style.display = 'none';
					}
						, 3000);
					aspectChange = 1;
				break;
					}
			
			if(ew == 114){
				if(!empty(timer)) {
							clearInterval(timer);
							timer = null;
							document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_pause.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
							setTimeout(function (){
								document.getElementById('paused').innerHTML ='';
							},2000);
				}
				else{
					timer = setInterval(function (){
							changePict('right');
							document.getElementById('inform').innerText = document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1))+''+(curPictS%4)).innerText;
							document.getElementById('inform').innerText += '\n'+document.getElementById('subscriptS_'+((curPictS/4)-((curPictS/4)%1)+2)+''+(curPictS%4)).innerText;
							},slDelayArr[slideshowDelay]);
							document.getElementById('paused').innerHTML = '<img id="pause" src="img/osd_play.png" style="position: absolute; right: 80px; top: 80px; z-index:999;">';
							setTimeout(function (){
								document.getElementById('paused').innerHTML ='';
							},2000);
				}
			}
			if(ew == 115){
				if(!empty(timer)) {
						clearInterval(timer);
						timer = null;
					}
					pictureRealSize.height = null;
					pictureRealSize.width = null;
					pictureAngle = 0;
					realwidth = 0;
					realheight = 0;
					drowFooter(4);
					document.getElementById('search_form').style.display = 'block';
					document.getElementById('blackScreen').style.display = 'none';
					document.getElementById('pictureBig_0').style.display = 'none';
					document.getElementById('pictureBig_1').style.display = 'none';
					document.getElementById('inform').style.display = 'none';
					showInfo = 0;
					document.getElementById('red').style.visibility = 'visible';
					document.getElementById('mb_header_pc').style.visibility = 'visible';
					pictureRealSize.height = null;
					curPage = Search_page;
			}
			
		}

	} else {
		_debug('receiving transmission');
	}
	if (curPage == 7) {
		switch (ec) {
			case 13:
				document.getElementById('alert').style.display = 'none';
				exitToMain();
				drowFooter(0);
				document.getElementById('mainPage').style.display = 'block';
				break;
			case 27:
				document.getElementById('alert').style.display = 'none';
				exitToMain();
				drowFooter(0);
				document.getElementById('mainPage').style.display = 'block';
				break;
		}
	}
	if (ec == 115 && reload_enabled) {
		window.location.reload();
		return;
	}
}
	
