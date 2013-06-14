var CMODAL_IMG_PATH = '/home/web/public/img/' + screen.width;

/**
 * Class for modal windows and messages.
 * Default use case:
 *   - create
 *   - set title/content/footer
 *   - init (after the DOM is ready)
 *   - show
 *   - hide/destroy
 * @class CModal
 * @constructor
 * @author DarkPark
 */
function CModal ( parent ) {
	// parent constructor
	CPage.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModal";

	/**
	 * CSS class name associated with the component
	 * @type {String}
	 */
	this.baseClass = "cmodal-main";

	/**
	 * CSS "display" attribute value to make the component visible
	 * to hide the default value is "none"
	 * @type {String}
	 */
	this.showAttr = 'table';

	this.focusList = [];
	this.focusPos = 0;

	/**
	 * default icon images path
	 * @type {String}
	 */
	//this.imgPath = '/home/web/public/img/' + screen.width;
}

// extending
extend(CModal, CPage);


/**
 * Prepare html and all placeholders
 * @param {Node|String} body window content
 */
CModal.prototype.Init = function ( body ) {
	// parent call init with placeholder
	CPage.prototype.Init.call(this,
		element('div', {className: 'cmodal-main'},
			element('div', {className:'cmodal-cell'},
				body
	)));
	// get the node to append to
	(this.parent && this.parent.handle && this.parent.handle.nodeName ? this.parent.handle : document.body).appendChild(this.handle);
};


/**
 * Destroy the window and free resources
 */
CModal.prototype.Free = function () {
	// global or local clearing
	(this.parent ? this.parent.handle : document.body).removeChild(this.handle);
	elclear(this.handle);
};


/**
 * Manage the window visibility
 * also enable/disable parent window event handling
 * @param {Boolean} [visible=true] true - visible; false - hidden
 * @param {Boolean} [manageFocus=true] focus handling mode: true - set/remove focus accordingly, false - manual focus management
 */
CModal.prototype.Show = function ( visible, manageFocus ) {
	// parent call
	CBase.prototype.Show.call(this, visible, manageFocus !== false);

	if ( visible === false ) {  // hide
		currCPage = this.parent;
	} else {  // show
		currCPage = this;
	}
};


/**
 * Move focus to the previous element from the focusList set
 */
CModal.prototype.FocusPrev = function ( event, manageVK ) {
	if ( this.focusList.length > 0 ) {
		// cycling the index
		if ( --this.focusPos < 0 ) this.focusPos = this.focusList.length-1;
		// get the next html element in the list
		var el = this.focusList[this.focusPos];
		if ( manageVK !== false ) gSTB.HideVirtualKeyboard();
		// set focus
		el.focus();
		// skip looping select options elements
		if ( el.nodeName !== 'SELECT' ) {
			event.preventDefault();
			if ( manageVK !== false ) gSTB.ShowVirtualKeyboard();
		}
	}
};


/**
 * Move focus to the next element from the focusList set
 */
CModal.prototype.FocusNext = function ( event, manageVK ) {
	if ( this.focusList.length > 0 ) {
		// cycling the index
		if ( ++this.focusPos >= this.focusList.length ) this.focusPos = 0;
		// get the next html element in the list
		var el = this.focusList[this.focusPos];
		if ( manageVK !== false ) gSTB.HideVirtualKeyboard();
		// set focus
		el.focus();
		// skip looping select options elements
		if ( event && el.nodeName !== 'SELECT' ) {
			event.preventDefault();
			if ( manageVK !== false ) gSTB.ShowVirtualKeyboard();
		}
	}
};


///////////////////////////////////////////////////////////////////////////////


/**
 * Show small modal info panel which automatically hides in the given time
 * @class CModalBox
 * @constructor
 * @example
 *   var mb = new CModalBox();
 */
function CModalBox ( parent ) {
	// parent constructor
	CModal.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalBox";

	/**
	 * html element for window title
	 * @type {Node}
	 */
	this.header = element('div', {className:'cmodal-header'});

	/**
	 * html element for window main content
	 * @type {Node}
	 */
	this.content = element('div', {className:'cmodal-content'});

	/**
	 * html element for window bottom panel
	 * @type {Node}
	 */
	this.footer = element('div', {className:'cmodal-footer'});
}

// extending
extend(CModalBox, CModal);

/**
 * Internal method to update one of the placeholders
 * makes the inserted node visible
 * @param {Node} place placeholder
 * @param {Node|Array|String} data some data to set
 * @return {Node} updated placeholder
 */
CModalBox.prototype.SetData = function ( place, data ) {
	// clear
	elclear(place);
	// and append
	if ( data instanceof Node || data instanceof Array ) {
		elchild(place, data);
	} else {
		// simple string
		place.innerHTML = data;
	}
	// make sure it visible
	if ( data && data.nodeName ) data.style.display = 'block';
	// show if there is some data
	place.style.display = data ? 'block' : 'none';
	return place;
};


/**
 * Set window title (alias for SetData)
 * @param {Node|Array|String} [data] some data to set
 * @return {Node} updated placeholder
 */
CModalBox.prototype.SetHeader  = function ( data ) { return this.SetData(this.header, data); };


/**
 * Set window body (alias for SetData)
 * @param {Node|Array|String} [data] some data to set
 * @return {Node} updated placeholder
 */
CModalBox.prototype.SetContent = function ( data ) { return this.SetData(this.content, data); };


/**
 * Set window footer (alias for SetData)
 * @param {Node|Array|String} [data] some data to set
 * @return {Node} updated placeholder
 */
CModalBox.prototype.SetFooter  = function ( data ) { return this.SetData(this.footer, data); };


/**
 * Prepare html and all placeholders
 */
CModalBox.prototype.Init = function ( ) {
	// parent call init with placeholder
	CModal.prototype.Init.call(this,
		element('div', {className:'cmodal-body'}, [
			this.header,
			this.content,
			this.footer
		]
	));
};


///////////////////////////////////////////////////////////////////////////////


/**
 * Show small modal info panel which automatically hides in the given time
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {String} data info text
 * @param {Number} time milliseconds before hiding (not set - manual hide)
 * @class CModalHint
 * @constructor
 * @example
 *   new CModalHint(CurrentPage, 'some test short info');
 */
function CModalHint ( parent, data, time ) {
	// check input
	if ( data ) {
		// parent constructor
		CModalBox.call(this, parent);

		/**
		 * The component inner name
		 * @type {String}
		 */
		this.name = "CModalHint";

		// for limited scopes
		var self = this;

		// filling
		this.SetHeader();
		this.SetContent(data);
		this.SetFooter();

		// free resources on hide
		this.onHide = function(){
			self.Free();
		};

		// build and display
		this.Init();
		this.Show(true);

		if ( time ) {
			// hide in some time
			this.timer = setTimeout(function(){
				self.Show(false);
			}, time || 5000);
		}
	}
}

// extending
extend(CModalHint, CModalBox);


///////////////////////////////////////////////////////////////////////////////


/**
 * Show modal message box with single button Exit
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {String} title modal message box caption
 * @param {String} data modal message box text
 * @param {String} btnExitTitle exit button caption
 * @param {Function} btnExitClick callback on exit button click
 * @class CModalAlert
 * @constructor
 * @example
 *   new CModalAlert(CurrentPage, 'Some title', 'Some long or short message text', 'Close', function(){alert('exit')});
 */
function CModalAlert ( parent, title, data, btnExitTitle, btnExitClick ) {
	// check input
	if ( data ) {
		// parent constructor
		CModalBox.call(this, parent);

		/**
		 * The component inner name
		 * @type {String}
		 */
		this.name = "CModalAlert";

		// for limited scopes
		var self = this;

		this.bpanel = new CButtonPanel();
		this.bpanel.Init(CMODAL_IMG_PATH);
		this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', btnExitTitle || "", function(){
			if ( btnExitClick instanceof Function ) btnExitClick.call(self);
			// hide and destroy
			self.Show(false);
		});

		// filling
		this.SetHeader(title);
		this.SetContent(data);
		this.SetFooter(this.bpanel.handle);

		// free resources on hide
		this.onHide = function(){
			elclear(self.bpanel.handle);
			delete self.bpanel;
			//self.Dispatch(false);
			self.Free();
		};

		// forward events to button panel
		this.EventHandler = function ( e ) {
			if ( !eventPrepare(e, true, 'CModalAlert') ) return;

			self.bpanel.EventHandler(e);
			//e.preventDefault();
		};

		// build and display
		this.Init();
		this.Show(true);
	}
}

// extending
extend(CModalAlert, CModalBox);


///////////////////////////////////////////////////////////////////////////////


/**
 * Show modal message box with single button Exit
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {String} title modal message box caption
 * @param {String} data modal message box text
 * @param {String} btnExitTitle exit button caption
 * @param {Function} btnExitClick callback on exit button click
 * @param {String} btnF2Title f2 button caption
 * @param {Function} btnF2Click callback on f2 button click
 * @class CModalConfirm
 * @constructor
 * @example
 *   new CModalConfirm(CurrentPage, 'Some title', 'Some long or short message text', 'Close', function(){alert('exit')}, 'Ok', function(){alert('f2');});
 */
function CModalConfirm ( parent, title, data, btnExitTitle, btnExitClick, btnF2Title, btnF2Click ) {
	// parent constructor
	CModalAlert.call(this, parent, title, data, btnExitTitle, btnExitClick);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalConfirm";

	// for limited scopes
	var self = this;

	// additional button
	this.bpanel.Add(KEYS.OK, 'ico_ok.png', btnF2Title, function(){
		// hide and destroy
		self.Show(false);

		if ( btnF2Click instanceof Function ) {
			btnF2Click.call(self);
			// prevent double invoke
			btnF2Click = null;
		}
	});
}

// extending
extend(CModalConfirm, CModalAlert);


///////////////////////////////////////////////////////////////////////////////


/**
 * Show modal message box with single button Exit
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {String} title modal message box caption
 * @param {String} lblUser modal message box text for user name label
 * @param {String} lblPass modal message box text for user pass label
 * @param {String} btnExitTitle exit button caption
 * @param {Function} btnExitClick callback on exit button click
 * @param {String} btnF2Title f2 button caption
 * @param {Function} btnF2Click callback on f2 button click
 * @class CModalConfirm
 * @constructor
 */
function CModalAuth ( parent, title, lblUser, lblPass, btnExitTitle, btnExitClick, btnF2Title, btnF2Click ) {
	// for limited scopes
	var self = this;

	var html = element('table', {className:'main maxw'}, [
		element('tr', {}, [
			element('td', {className:'name'}, lblUser),
			element('td', {className:'data'}, this.user = element('input', {type:'text'}))
		]),
		element('tr', {}, [
			element('td', {className:'name'}, lblPass),
			element('td', {className:'data'}, this.pass = element('input', {type:'text'}))
		])
	]);

	this.user.onkeydown = this.pass.onkeydown = function ( event ) {
		// get real key code or exit
		if ( !eventPrepare(event, false, 'CModalAuth') ) return;
		echo('onkeydown');
		//echo(event.code);
		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			case KEYS.UP: // up
				self.FocusPrev(event);
				break;
			case KEYS.DOWN: // down
				self.FocusNext(event);
				break;
			case KEYS.OK: // enter
				if ( self.focusPos === 0 ) self.FocusNext(event); else btnF2.data.onclick();
				break;
			default:
				// forward events to button panel
				//this.bpanel.EventHandler(event);
		}
	};

	this.onShow = function(){
		setTimeout(function(){
			self.user.focus();
			gSTB.ShowVirtualKeyboard();
			//self.FocusPrev();
		}, 5);
	};

	// parent constructor
	CModalAlert.call(this, parent, title, html, btnExitTitle, btnExitClick);

	this.focusList.push(this.user);
	this.focusList.push(this.pass);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalAuth";

	// additional button
	var btnF2 = this.bpanel.Add(KEYS.F2, 'ico_f2.png', btnF2Title, function(){
		if ( btnF2Click instanceof Function ) {
			gSTB.HideVirtualKeyboard();
			if ( btnF2Click.call(self, self.user.value, self.pass.value) ) {
				self.Show(false);
			}
		}
	});
}

// extending
extend(CModalAuth, CModalAlert);


///////////////////////////////////////////////////////////////////////////////


function CModalMount ( parent ) {
	// parent constructor
	CModalBox.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalMount";

	// for limited scopes
	var self = this;

	var html = element('table', {className:'main maxw'}, [
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_ADDR),
			element('td', {className:'data'}, this.url = element('input', {type:'text'}))
		]),
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_SHARE),
			element('td', {className:'data'}, this.folder = element('input', {type:'text'}))
		]),
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_LOCAL),
			element('td', {className:'data'}, this.local = element('input', {type:'text'}))
		]),
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_TYPE),
			element('td', {className:'data'}, this.type = element('select', {}, [
				element('option', {value:'smb'}, 'SMB'),
				element('option', {value:'nfs'}, 'NFS')]))
		]),
		this.row_user = element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_USER),
			element('td', {className:'data'}, this.login = element('input', {type:'text'}))
		]),
		this.row_pass = element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_PASS),
			element('td', {className:'data'}, this.pass = element('input', {type:'text'}))
		])
	]);

	this.type.onchange = function () {
		echo(this.value);
		if ( this.value === 'smb' ) {
			self.row_user.className = self.row_pass.className = '';
			self.focusList = [self.url, self.folder, self.local, self.type, self.login, self.pass];
			self.login.disabled = false;
			self.pass.disabled = false;

		} else {
			self.row_user.className = self.row_pass.className = 'inactive';
			self.login.value = self.pass.value = "";
			self.focusList = [self.url, self.folder, self.local, self.type];
			self.login.disabled = true;
			self.pass.disabled = true;
		}
	};
	this.type.onchange();
	this.type.onkeyup = this.type.onchange;

	this.prepare = function(){
		self.url.value    = self.url.value.trim();
		self.folder.value = self.folder.value.trim();
		self.local.value  = self.local.value.trim();
		self.login.value  = self.login.value.trim();
		self.pass.value   = self.pass.value.trim();

		// work with slashes
		if ( self.folder.value ) {
			if ( self.type.value === 'nfs' && self.folder.value.charAt(0) !== "/" ) self.folder.value = "/" + self.folder.value;
			if ( self.type.value === 'smb' && self.folder.value.charAt(0) === "/" ) self.folder.value = self.folder.value.slice(1);
			if ( self.folder.value.charAt(self.folder.value.length-1) === "/" ) self.folder.value = self.folder.value.slice(0, -1);
		}

		if ( !self.local.value && self.folder.value ) {
			var parts = self.folder.value.split("/");
			self.local.value = parts[parts.length-1];
		}
	};

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});
	this.bpanel.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_MOUNT_DLG_OK, function(){
		var addr;

		gSTB.HideVirtualKeyboard();

		self.prepare();

		// no params
		if ( !self.url.value || !self.folder.value || !self.local.value ) {
			// try again
			new CModalHint(self, LANG_MEDIA_MOUNT_DLG_WRONG, 4000);
		} else {
			// check dupes
			var data, exist, dupl = false;
			// type dependant
			smb_array.forEach(function(item){ if ( item.local === self.local.value ) dupl = true; });
			nfs_array.forEach(function(item){ if ( item.local === self.local.value ) dupl = true; });
			// found
			if ( dupl ) {
				// try again
				new CModalHint(self, LANG_MEDIA_MOUNT_DLG_DUPL, 4000);
			} else {
				// good
				if ( self.type.value === 'smb' ) {
					addr = '//' + self.url.value + '/' + self.folder.value;
					// clear
					MediaBrowser.UnmountSMB();
					// try to mount
					if ( MediaBrowser.MountSMB({
						url  : addr,
						login: self.login.value,
						pass : self.pass.value}) )
					{
						// prepare
						data = {
							url   : self.url.value,
							folder: self.folder.value,
							local : self.local.value,
							login : self.login.value || "guest",
							pass  : self.pass.value
						};
						// check duplicates
						exist = false;
						smb_array.forEach(function(item){
							var same = true;
							if ( !exist ) {
								for ( var name in data ) same = same && data[name] === item[name];
								if ( same ) exist = true;
							}
						});
						// only if not a duplicate
						if ( !exist ) {
							setTimeout(function(){
								// resave smb data
								smb_array.push(data);
								gSTB.SaveUserData('smb_data', JSON.stringify(smb_array));
								MediaBrowser.Reset();
								MediaBrowser.FileList.Open({
									path  : smb_path,
									url   : addr,
									folder: self.folder.value,
									type  : MEDIA_TYPE_SAMBA_SHARE,
									//type  : MEDIA_TYPE_NET_FOLDER,
									//smb   : true,
									name  : self.local.value
								});
								echo(smb_array, 'gSTB.SaveUserData');
							}, 5);
							self.Show(false);
						} else {
							new CModalHint(self, LANG_MEDIA_MOUNT_DLG_DONE, 4000);
						}
					} else {
						new CModalHint(self, LANG_MEDIA_MOUNT_DLG_FAIL, 4000);
					}
				} else {
					// NFS
					addr = self.url.value + ':' + self.folder.value;
					// clear
					MediaBrowser.UnmountNFS();
					// try to mount
					if ( MediaBrowser.MountNFS({url:addr}) ) {
						// prepare
						data = {
							url   : self.url.value,
							folder: self.folder.value,
							local : self.local.value
						};
						// check duplicates
						exist = false;
						nfs_array.forEach(function(item){
							var same = true;
							if ( !exist ) {
								for ( var name in data ) same = same && data[name] === item[name];
								if ( same ) exist = true;
							}
						});
						// only if not a duplicate
						if ( !exist ) {
							setTimeout(function(){
								// resave nfs data
								nfs_array.push(data);
								gSTB.SaveUserData('nfs_data', JSON.stringify(nfs_array));
								MediaBrowser.Reset();
								MediaBrowser.FileList.Open({
									path  : nfs_path,
									url   : addr,
									folder: self.folder.value,
									type  : MEDIA_TYPE_NFS_SHARE,
									//type  : MEDIA_TYPE_NET_FOLDER,
									//nfs   : true,
									name  : self.local.value
								});
								echo(nfs_array, 'gSTB.SaveUserData');
							}, 5);
							// hide and destroy
							self.Show(false);
						} else {
							new CModalHint(self, LANG_MEDIA_MOUNT_DLG_DONE, 4000);
						}
					} else {
						new CModalHint(self, LANG_MEDIA_MOUNT_DLG_FAIL, 4000);
					}
				}
			}
		}
	});

	// filling
	this.SetHeader(LANG_MEDIA_MOUNT_DLG_ON);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function(){
		setTimeout(function(){
			self.url.focus();
			gSTB.ShowVirtualKeyboard();
		}, 100);
	};

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			case KEYS.UP:
				self.prepare();
				self.FocusPrev(event, false);
				break;
			case KEYS.DOWN:
				self.prepare();
				self.FocusNext(event, self.focusList[self.focusPos] !== self.folder);
				break;
			case KEYS.RIGHT:
			case KEYS.LEFT:
				//if ( this.focusList[this.focusPos] === self.type ) event.preventDefault();
				break;
			case KEYS.OK: // enter
				if ( self.focusList[self.focusPos] === self.folder ) self.prepare();
				if ( this.focusList[this.focusPos] === self.local  ) event.preventDefault();
				if ( this.focusList[this.focusPos] !== self.type   ) self.FocusNext(event);
				break;
			default:
				// forward events to button panel
				self.bpanel.EventHandler(event);
		}
	};

	// build and display
	this.Init();
	this.Show(true);
}

// extending
extend(CModalMount, CModalBox);


///////////////////////////////////////////////////////////////////////////////


function CModalUnmount ( parent ) {
	// parent constructor
	CModalBox.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalMount";

	// for limited scopes
	var self = this;

	var html = element('table', {className:'main maxw'}, [
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_MOUNT_DLG_LOCAL)
		]),
		element('tr', {}, [
			element('td', {className:'data'}, this.local = element('select', {className:'wide'}))
		]),
		element('tr', {}, [
			element('td', {className:'data'}, this.hint = element('div', {className:'hint'}))
		])
	]);

	smb_array.forEach(function(item){
		// select the current one
		elchild(self.local, element('option', {smb:true, data:item/*, selected:(item === smb_data)*/}, item.local));
	});
	nfs_array.forEach(function(item){
		// select the current one
		elchild(self.local, element('option', {nfs:true, data:item/*, selected:(item === smb_data)*/}, item.local));
	});

	this.local.onchange = function () {
		var option = this.options[this.selectedIndex];
		if ( option.smb ) self.hint.innerHTML = 'smb://' + option.data.url + '/' + option.data.folder;
		if ( option.nfs ) self.hint.innerHTML = 'nfs://' + option.data.url + option.data.folder;
	};

	this.local.onchange();
	this.local.onkeyup = this.local.onchange;

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});
	this.bpanel.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_MOUNT_DLG_UM, function(){
		var pos, option = self.local.options[self.local.selectedIndex];
		if ( option.smb ) {
			// SMB
			MediaBrowser.UnmountSMB();
			pos = smb_array.indexOf(option.data);
			if ( pos !== -1 ) smb_array.splice(pos, 1);
			echo(smb_array, 'smb_array saving');
			gSTB.SaveUserData('smb_data', JSON.stringify(smb_array));
		} else if ( option.nfs ) {
			// NFS
			MediaBrowser.UnmountNFS();
			pos = nfs_array.indexOf(option.data);
			if ( pos !== -1 ) nfs_array.splice(pos, 1);
			echo(nfs_array, 'nfs_array saving');
			gSTB.SaveUserData('nfs_data', JSON.stringify(nfs_array));
		}
		// refresh only root
		//if ( MediaBrowser.FileList.parentItem.type === MEDIA_TYPE_ROOT ) { MediaBrowser.FileList.Refresh(); }
		MediaBrowser.Reset();
		// hide and destroy
		self.Show(false);
	});

	// filling
	this.SetHeader(LANG_MEDIA_MOUNT_DLG_OFF);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function(){
		self.local.focus();
	};

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		if ( !eventPrepare(event, true, 'CModalAlert') ) return;

		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			default:
				// forward events to button panel
				self.bpanel.EventHandler(event);
		}
	};

	// build and display
	this.Init();
	this.Show(true);
}

// extending
extend(CModalUnmount, CModalBox);


///////////////////////////////////////////////////////////////////////////////


function CModalFormat ( parent ) {
	// parent constructor
	CModalBox.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalFormat";

	// for limited scopes
	var self = this;

	var html = element('table', {className:'main maxw'}, [
		element('tr', {}, [
			element('td', {colSpan:2, className:'warn', innerHTML:LANG_MEDIA_DISK_FORMAT_WARN})
		]),
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_DISK_FORMAT_TYPE),
			element('td', {className:'data'}, this.type = element('select', {className:'wide'}, [
				element('option', {value:'ext3'},  'EXT3'),
				element('option', {value:'ext2'},  'EXT2'),
				element('option', {value:'ntfs'},  'NTFS')
			]))
		]),
		element('tr', {}, [
			element('td', {className:'name'}, LANG_MEDIA_DISK_FORMAT_PART),
			element('td', {className:'data'}, this.part = element('select', {className:'wide'}))
		]),
		element('tr', {}, [
			element('td', {colSpan:2}, element('div', {}, element('div', {className:'progressbar'}, element('div', {className:'progressbar_bg maxh'}, [
				this.line = element('div', {className:'progressbar_line maxh'}),
				this.text = element('div', {className:'progressbar_digit'})
			]))))
		])
	]);

	self.focusList = [self.type, self.part];

	// fill the select list
	if ( HDD_INFO.length > 0 ) {
		for ( var i = 0; i < HDD_INFO.length; i++ ) {
			// not single partitions (only the hdd itself)
			if ( HDD_INFO[i].partitionNum === "" ) {
				this.part.add(new Option(
					HDD_INFO[i].vendor + ' ' + HDD_INFO[i].model.replace(/\//, '') + '' + (HDD_INFO[i].size != '' ? ' (' + (Math.floor(HDD_INFO[i].size / 1073741824 * 100) / 100) + ' Gb)' : ''),
					'allHDD|' + (HDD_INFO[i].size != '' ? (Math.floor(HDD_INFO[i].size / 1073741824 * 100) / 100) : 0)));
			}
		}
	}

	this.finish = function(){
		// reset progress bar and label
		this.line.style.width = this.text.innerHTML = "";
		// restore event handling
		document.addEventListener('keydown', mainEventListener);
		// enable select boxes
		this.type.disabled = this.part.disabled = false;
		// return focus
		this.type.focus();
		// clear buttons suppression
		this.bpanel.Activate(true);
	};

	this.check = function(){
		var read_status = {};
		try {
			eval('read_status=' + gSTB.RDir('tempfile read hdd_progress'));
		} catch ( e ) {
			echo(e, 'tempfile read hdd_progress');
		}
		echo(read_status, 'read_status');
		// beginning
		if ( read_status.state == 'undefined' ) {
			// init progress bar and label
			this.line.style.width = this.text.innerHTML = "0%";
			// call later (in 5 sec)
			return window.setTimeout(function(){ self.check() }, 5000);
		}
		// done
		if ( read_status.state === 'complete' ) {
			this.finish();
			// update global list
			//getStorageInfo(); // no need
			// congratulations
			return new CModalAlert(this, LANG_MEDIA_DISK_FORMAT_NAME, LANG_MEDIA_DISK_FORMAT_DONE, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
		}
		// failure
		if ( read_status.state == 'error' ) {
			this.finish();
			switch ( read_status.stage ) {
				case "16":
					new CModalAlert(this, LANG_MEDIA_DISK_FORMAT_FAIL, LANG_MEDIA_DISK_FORMAT_BUSY, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
					break;
				case "2":
				case "22":
					new CModalAlert(this, LANG_MEDIA_DISK_FORMAT_FAIL, LANG_MEDIA_DISK_FORMAT_WRN1, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
					break;
				default :
					new CModalAlert(this, LANG_MEDIA_DISK_FORMAT_NAME, LANG_MEDIA_DISK_FORMAT_FAIL, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
			}
			return;
		}
		// update progress bar and label
		this.line.style.width = this.text.innerHTML = read_status.percent + "%";
		// call later (in 5 sec)
		return window.setTimeout(function(){ self.check() }, 5000);
	};

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});
	this.bpanel.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_DISK_FORMAT_OK, function(){
		var part = self.part.value.split('|');
		if ( part[0] ) {
			if ( self.type.value === "fat32" && parseInt(part[1], 10) > 320 ) {
				return new CModalAlert(self, LANG_MEDIA_DISK_FORMAT_FAIL, LANG_MEDIA_DISK_FORMAT_WRN2, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
			}

			new CModalConfirm(self, LANG_MEDIA_DISK_FORMAT_NAME, LANG_MEDIA_DISK_FORMAT_WARN,
				LANG_MEDIA_DEFAULT_ABORT, function(){},
				LANG_MEDIA_DISK_FORMAT_OK, function(){
					// exit from the hdd to root level if necessary
					if ( MediaBrowser.FileList.mode === MEDIA_TYPE_STORAGE_SATA ) {
						// go to root
						MediaBrowser.Reset();
					}

					// disable main event handling
					document.removeEventListener('keydown', mainEventListener);
					// disable select boxes
					self.type.disabled = self.part.disabled = true;
					// suppress all bottom buttons
					self.bpanel.Activate(false);
					// format command
					var cmd = 'hdd_format ' + self.type.value + (part[0] !== 'allHDD' ?  ' '+part[0] : '');
					echo(cmd, 'formatting command');
					gSTB.RDir(cmd);
					// start periodical check
					self.check();
				}
			);
		} else {
			return new CModalAlert(self, LANG_MEDIA_DISK_FORMAT_FAIL, LANG_MEDIA_DISK_FORMAT_WRN1, LANG_MEDIA_DEFAULT_CLOSE, function(){ echo('exit') });
		}
	});

	// filling
	this.SetHeader(LANG_MEDIA_DISK_FORMAT_NAME);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function(){
		self.type.focus();
	};

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			case KEYS.UP:
				self.FocusPrev(event, false);
				event.preventDefault();
				break;
			case KEYS.DOWN:
				self.FocusNext(event, false);
				event.preventDefault();
				break;
			default:
				// forward events to button panel
				self.bpanel.EventHandler(event);
		}
	};

	// build and display
	this.Init();
	this.Show(true);
}

// extending
extend(CModalFormat, CModalBox);


///////////////////////////////////////////////////////////////////////////////


function CModalPlayListOpen ( parent ) {
	// parent constructor
	CModalBox.call(this, parent);

	this.content.className = this.content.className + ' cmodal-pls';
	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalPlayListOpen";

	this.utf8 = true;

	// for limited scopes
	var self = this;
	// callback on F-button click
	var func = function ( code ) {
		setTimeout(function(){
			var data = self.parent.FileList.Current().data;
			data.code = code;
			data.utf8 = self.utf8;
			self.parent.FileList.Open(data);
		},5);
		self.Show(false);
	};

	this.bpanelMain = new CButtonPanel();
	this.bpanelMain.Init(CMODAL_IMG_PATH);
	this.bpanelMain.Add(KEYS.F1, 'ico_f1.png', LANG_MEDIA_PLS_OPEN_F1, func);
	this.bpanelMain.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_PLS_OPEN_F2, func);
	this.bpanelMain.Add(KEYS.F3, 'ico_f3.png', LANG_MEDIA_PLS_OPEN_F3, func);

	this.bpanelBottom = new CButtonPanel();
	this.bpanelBottom.Init(CMODAL_IMG_PATH);
	this.bpanelBottom.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});

	// filling
	this.SetHeader(LANG_MEDIA_PLS_OPEN_NAME);
	this.SetContent([
		element('div', {className:'block'}, [this.swdiv = element('a', {className:'switch on', onclick:function(){
			self.Switch();
		}}), LANG_MEDIA_PLS_OPEN_UTF8]),
		this.bpanelMain.handle
	]);
	this.SetFooter(this.bpanelBottom.handle);

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanelMain.handle);
		elclear(self.bpanelBottom.handle);
		delete self.bpanelMain;
		delete self.bpanelBottom;
		//self.Dispatch(false);
		self.Free();
	};

	// invert flag
	this.Switch = function(){
		this.utf8 = !this.utf8;
		this.swdiv.className = this.utf8 ? 'switch on' : 'switch';
	};

	// forward events to button panel
	this.EventHandler = function ( e ) {
		if ( e.code === KEYS.OK ) this.Switch();

		if ( self.bpanelMain ) self.bpanelMain.EventHandler(e);
		if ( self.bpanelBottom ) self.bpanelBottom.EventHandler(e);
	};

	// build and display
	this.Init();
	this.Show(true);
}

// extending
extend(CModalPlayListOpen, CModalBox);


///////////////////////////////////////////////////////////////////////////////


function CModalFileSelect ( parent, options ) {
	// parent constructor
	CModalBox.call(this, parent);

	/*this.content.className = this.content.className + ' cmodal-pls';

	this.name = "CModalFileSelect";
	this.utf8 = true;

	// for limited scopes
	var self = this;
	// callback on F-button click
	var func = function ( code ) {
		setTimeout(function(){
			var data = self.parent.FileList.Current().data;
			data.code = code;
			data.utf8 = self.utf8;
			self.parent.FileList.Open(data);
		},5);
		self.Show(false);
	};

	this.bpanelMain = new CButtonPanel();
	this.bpanelMain.Init(CMODAL_IMG_PATH);
	this.bpanelMain.Add(KEYS.F1, 'ico_f1.png', LANG_MEDIA_PLS_OPEN_F1, func);
	this.bpanelMain.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_PLS_OPEN_F2, func);
	this.bpanelMain.Add(KEYS.F3, 'ico_f3.png', LANG_MEDIA_PLS_OPEN_F3, func);

	this.bpanelBottom = new CButtonPanel();
	this.bpanelBottom.Init(CMODAL_IMG_PATH);
	this.bpanelBottom.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});

	// filling
	this.SetHeader(LANG_MEDIA_PLS_OPEN_NAME);
	this.SetContent([
		element('div', {className:'block'}, [this.swdiv = element('a', {className:'switch on', onclick:function(){
			self.Switch();
		}}), LANG_MEDIA_PLS_OPEN_UTF8]),
		this.bpanelMain.handle
	]);
	this.SetFooter(this.bpanelBottom.handle);

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanelMain.handle);
		elclear(self.bpanelBottom.handle);
		delete self.bpanelMain;
		delete self.bpanelBottom;
		//self.Dispatch(false);
		self.Free();
	};

	// invert flag
	this.Switch = function(){
		this.utf8 = !this.utf8;
		this.swdiv.className = this.utf8 ? 'switch on' : 'switch';
	};

	// forward events to button panel
	this.EventHandler = function ( e ) {
		if ( e.code === KEYS.OK ) this.Switch();

		if ( self.bpanelMain ) self.bpanelMain.EventHandler(e);
		if ( self.bpanelBottom ) self.bpanelBottom.EventHandler(e);
	};

	// build and display
	this.Init();
	this.Show(true);*/
}

// extending
extend(CModalFileSelect, CModalBox);


///////////////////////////////////////////////////////////////////////////////


function CModalCreateGroup( parent, label, text, data,toDelete) {
	// parent constructor
	CModalBox.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalCreateGroup";

	// for limited scopes
	var self = this;

	var html = element('table', {className:'main maxw'}, [
		element('tr', {}, [
			element('td', {className:'name'}, text)
		]),
		element('tr', {}, [
			element('td', {className:'data'}, this.local = element('input', {className:'wide',type : "text"}))
		])
	]);

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', LANG_MEDIA_DEFAULT_ABORT, function(){
		// hide and destroy
		self.Show(false);
	});
	this.bpanel.Add(KEYS.F2, 'ico_f2.png', LANG_MEDIA_SAVE_FAVORITES_CONFIRM_OK, function(){
		if(self.local.value !== ""){
		self.Show(false);
                    self.parent.TVList.createGroup(self.local.value,data, toDelete);
                };
	});

	// filling
	this.SetHeader(label);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function(){
		self.local.focus();
	};

	// free resources on hide
	this.onHide = function(){
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		if ( !eventPrepare(event, true, 'CModalAlert') ) return;

		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			default:
				// forward events to button panel
				self.bpanel.EventHandler(event);
		}
	};

	// build and display
	this.Init();
	this.Show(true);
}

// extending
extend(CModalCreateGroup, CModalBox);
