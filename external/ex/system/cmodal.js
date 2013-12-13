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
//extend(CModal, CPage);
CModal.prototype = Object.create(CPage.prototype);
CModal.prototype.constructor = CModal;

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
//extend(CModalBox, CModal);
CModalBox.prototype = Object.create(CModal.prototype);
CModalBox.prototype.constructor = CModalBox;

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
//extend(CModalHint, CModalBox);
CModalHint.prototype = Object.create(CModalBox.prototype);
CModalHint.prototype.constructor = CModalHint;

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
//extend(CModalAlert, CModalBox);
CModalAlert.prototype = Object.create(CModalBox.prototype);
CModalAlert.prototype.constructor = CModalAlert;

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
//extend(CModalConfirm, CModalAlert);
CModalConfirm.prototype = Object.create(CModalAlert.prototype);
CModalConfirm.prototype.constructor = CModalConfirm;

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
//extend(CModalAuth, CModalAlert);
CModalAuth.prototype = Object.create(CModalAlert.prototype);
CModalAuth.prototype.constructor = CModalAuth;

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
//extend(CModalFileSelect, CModalBox);
CModalFileSelect.prototype = Object.create(CModalBox.prototype);
CModalFileSelect.prototype.constructor = CModalFileSelect;

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
//extend(CModalCreateGroup, CModalBox);
CModalCreateGroup.prototype = Object.create(CModalBox.prototype);
CModalCreateGroup.prototype.constructor = CModalCreateGroup;
////////////////////////////////////////////////////////////////////

/**
 * Show dialog bow for a PVR record edit
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {Object} data current PVR record data
 * @param {String} label modal message box text
 * @param {String} text modal message box text
 * @class CModalBox
 * @constructor
 */
function CModalSelectLang ( parent, label, text, data ) {
	// parent constructor
	CModalBox.call(this, parent);
	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CModalSelectLang";

	// for limited scopes
	var self = this;

	this.$interfaceLang = new CSelectBox(this,
		{
			data     : languages,
			nameField: "label",
			style    : 'cselect-box-wide',
			events   : {}
		});

	this.$contentLang = new CSelectBox(this,
		{
			data     : languages,
			nameField: "label",
			style    : 'cselect-box-wide',
			events   : {}
		});

	var html = element('table', {className: 'main maxw'}, [
		element('tr', {}, [
			element('td', {className: 'name'}, lang.interfaceLang),
			element('td', {className: 'data'}, this.$interfaceLang.parentNode)
		]),
		element('tr', {}, [
			element('td', {className: 'name'}, lang.contentLang),
			element('td', {className: 'data'}, this.$contentLang.parentNode)
		])
	]);

	var currentData = gSTB.LoadUserData('ex.ua.data.json');
	try {
		currentData = JSON.parse(currentData);
	} catch ( err ) {
		echo('JSON.parse(LoadUserData("ex.ua.data.json")); -> ERROR ->' + err);
	}
	echo(currentData, 'data JSON.parse(LoadUserData("ex.ua.data.json"))');

	// set current lang as default
	for (var i = 0; i < languages.length; i++) {
		echo('languages[i].langVal === currentData.contentLang/currentData.interfaceLang=>'+languages[i].langVal + ' === ' + currentData.contentLang + ' / ' + currentData.interfaceLang );
		if ( languages[i].langVal === currentData.contentLang ) { this.$contentLang.SetIndex(i); }
		if ( languages[i].langVal === currentData.interfaceLang ) { this.$interfaceLang.SetIndex(i); }
	}

	this.focusList = [
		[this.$interfaceLang],
		[this.$contentLang]
	];

	this.focusPos = 0;
	this.FocusNext = function ( event, manageVK ) {
		if ( this.focusList.length > 0 ) {
			// cycling the index
			if ( ++this.focusPos >= this.focusList.length ) this.focusPos = 0;
			// get the next html element in the list
			var el = this.focusList[this.focusPos][0];
			// set focus
			el.focus();
		}
	};

	this.FocusPrev = function ( event, manageVK ) {
		if ( this.focusList.length > 0 ) {
			// cycling the index
			if ( --this.focusPos < 0 ) this.focusPos = this.focusList.length - 1;
			// get the next html element in the list
			var el = this.focusList[this.focusPos][0];
			// set focus
			el.focus();
		}
	};


	this.saveNewLang = function () {
		echo('saveNewLang');
		var dataForSaving = {
			contentLang: self.$contentLang.GetSelected().langVal,
			interfaceLang: self.$interfaceLang.GetSelected().langVal
		};
		gSTB.SaveUserData('ex.ua.data.json', JSON.stringify(dataForSaving));
		//self.Show(false);
		window.location.reload();
	};

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, 'ico_exit.png', lang.cancel, function () {
		self.Show(false);
	});
	this.bpanel.Add(KEYS.OK, 'ico_ok.png', lang.apply, function () {
		self.saveNewLang();
	});

	// filling
	this.SetHeader(label);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function () {
		self.focusList[self.focusPos][0].focus();
	};

	// free resources on hide
	this.onHide = function () {
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		echo('modal eventhandler');
		if ( !eventPrepare(event, true, 'CModalAlert') ) return;
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
			case KEYS.LEFT:
			case KEYS.RIGHT:
				this.focusList[this.focusPos][0].EventHandler(event);
				break;
			case KEYS.VOLUME_DOWN:
			case KEYS.VOLUME_UP:
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
CModalSelectLang.prototype = Object.create(CModalBox.prototype);
CModalSelectLang.prototype.constructor = CModalSelectLang;
