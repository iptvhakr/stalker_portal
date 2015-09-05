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
			element('div', {className: 'cmodal-cell'},
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
		if ( --this.focusPos < 0 ) {this.focusPos = this.focusList.length - 1;}
		// get the next html element in the list
		var el = this.focusList[this.focusPos];
		if ( manageVK !== false ) {gSTB.HideVirtualKeyboard();}
		// set focus
		el.focus();
		// skip looping select options elements
		if ( el.nodeName !== 'SELECT' ) {
			event.preventDefault();
			if ( manageVK !== false ) {gSTB.ShowVirtualKeyboard();}
		}
	}
};


/**
 * Move focus to the next element from the focusList set
 */
CModal.prototype.FocusNext = function ( event, manageVK ) {
	if ( this.focusList.length > 0 ) {
		// cycling the index
		if ( ++this.focusPos >= this.focusList.length ) {this.focusPos = 0;}
		// get the next html element in the list
		var el = this.focusList[this.focusPos];
		if ( manageVK !== false ) {gSTB.HideVirtualKeyboard();}
		// set focus
		el.focus();
		// skip looping select options elements
		if ( event && el.nodeName !== 'SELECT' ) {
			event.preventDefault();
			if ( manageVK !== false ) {gSTB.ShowVirtualKeyboard();}
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
	this.header = element('div', {className: 'cmodal-header'});

	/**
	 * html element for window main content
	 * @type {Node}
	 */
	this.content = element('div', {className: 'cmodal-content'});

	/**
	 * html element for window bottom panel
	 * @type {Node}
	 */
	this.footer = element('div', {className: 'cmodal-footer'});
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
	if ( data && data.nodeName ) {data.style.display = 'block';}
	// show if there is some data
	place.style.display = data ? 'block' : 'none';
	return place;
};


/**
 * Set window title (alias for SetData)
 * @param {Node|Array|String} [data] some data to set
 * @return {Node} updated placeholder
 */
CModalBox.prototype.SetHeader = function ( data ) { return this.SetData(this.header, data); };


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
CModalBox.prototype.SetFooter = function ( data ) { return this.SetData(this.footer, data); };


/**
 * Prepare html and all placeholders
 */
CModalBox.prototype.Init = function () {
	// parent call init with placeholder
	CModal.prototype.Init.call(this,
		element('div', {className: 'cmodal-body'}, [
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
		this.name = 'CModalHint';

		// for limited scopes
		var self = this;

		// filling
		this.SetHeader();
		this.SetContent(data);
		this.SetFooter();

		// free resources on hide
		this.onHide = function () {
			self.Free();
		};

		// build and display
		this.Init();
		this.handle.className = this.handle.className + ' cModalHint';
		this.Show(true);

		if ( time ) {
			// hide in some time
			this.timer = setTimeout(function () {
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
		this.bpanel.Add(KEYS.EXIT, useNewIcons ? 'new/exit2.png' : 'ico_exit.png', btnExitTitle || "", function () {
			if ( btnExitClick instanceof Function ) btnExitClick.call(self);
			// hide and destroy
			self.Show(false);
		});

		// filling
		this.SetHeader(title);
		this.SetContent(data);
		this.SetFooter(this.bpanel.handle);

		// free resources on hide
		this.onHide = function () {
			elclear(self.bpanel.handle);
			delete self.bpanel;
			self.Free();
		};

		// forward events to button panel
		this.EventHandler = function ( e ) {
			if ( !eventPrepare(e, true, 'CModalAlert') ) {return;}
			self.bpanel.EventHandler(e);
		};

		// build and display
		this.Init();
		this.Show(true);
	}
}

// extending
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
	this.bpanel.Add(KEYS.OK, 'ico_ok.png', btnF2Title, function () {
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
 * Show dialog bow for a lang change
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {Object} data current data
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
	for ( var i = 0; i < languages.length; i++ ) {
		echo('languages[i].langVal === currentData.contentLang/currentData.interfaceLang=>' + languages[i].langVal + ' === ' + currentData.contentLang + ' / ' + currentData.interfaceLang);
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
			if ( ++this.focusPos >= this.focusList.length ) {this.focusPos = 0;}
			// get the next html element in the list
			var el = this.focusList[this.focusPos][0];
			// set focus
			el.focus();
		}
	};

	this.FocusPrev = function ( event, manageVK ) {
		if ( this.focusList.length > 0 ) {
			// cycling the index
			if ( --this.focusPos < 0 ) {this.focusPos = this.focusList.length - 1;}
			// get the next html element in the list
			var el = this.focusList[this.focusPos][0];
			// set focus
			el.focus();
		}
	};


	this.saveNewLang = function () {
		echo('saveNewLang');
		var dataForSaving = {
			contentLang  : self.$contentLang.GetSelected().langVal,
			interfaceLang: self.$interfaceLang.GetSelected().langVal
		};
		gSTB.SaveUserData('ex.ua.data.json', JSON.stringify(dataForSaving));
		window.location.reload();
	};

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, useNewIcons ? 'new/exit2.png' : 'ico_exit.png', lang.cancel, function () {
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
		if ( !eventPrepare(event, true, 'CModalAlert') ) {return;}
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

///////////////////////////////////////////////////////////////////////////////


/**
 * Show dialog bow for download add
 * @param {CPage|CBase} [parent] object owner (document.body if not set)
 * @param {Object} data current data
 * @class CModalBox
 * @constructor
 */
function CModalAddDownload ( parent, data ) {
	var self               = this, // for limited scopes
		prepareSize        = function ( size ) {
			if ( size > (1024 * 1024 * 1024) ) {return (Math.floor(size / 1024 / 1024 / 1024 * 100) / 100) + ' ' + ('GB');}
			if ( size > (1024 * 1024) ) {return (Math.floor(size / 1024 / 1024 * 100) / 100) + ' ' + ('MB');}
			if ( size > (1024) ) {return (Math.floor(size / 1024 * 100) / 100) + ' ' + ('KB');}

			return size + ' ' + ('B');
		},
		refreshStorageInfo = function () {
			var usb, i;

			getStorageInfo();
			for ( i = 0; i < STORAGE_INFO.length; i++ ) {
				usb = STORAGE_INFO[i];
				if ( usb.isReadOnly === 0 ) {
					usb.freeSizeStr = prepareSize(usb.freeSize);
					usb.sizeStr = prepareSize(usb.size);
					usb.id = i;
					usb.sizeIndicator = lang.freeSpace + ' ' + usb.freeSizeStr + lang.of + usb.sizeStr;
					allStorages.push(usb);
				}
			}
		},
		allStorages        = [],
		html;

	// parent constructor
	CModalBox.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = 'CModalAddDownload';

	refreshStorageInfo();
	allStorages = allStorages.length > 0 ? allStorages : [{id: -1, label: lang.noStorage}];
	this.$deviceSelect = new CSelectBox(this, {
		data     : allStorages,
		nameField: 'label',
		style    : 'cselect-box-wider',
		events   : {
			onChange: function () {
				self.$sizeIndicator.innerText = this.GetSelected().sizeIndicator;
			}
		}
	});

	html = element('table', {className: 'main maxw'}, [
		element('tr', {}, [
			element('td', {className: 'name'}, lang.downloadIn),
			element('td', {className: 'data'}, this.$deviceSelect.parentNode)
		]),
		element('tr', {}, [
			element('td', {className: 'name'}),
			this.$sizeIndicator = element('td', {className: 'data'}, self.$deviceSelect.GetSelected().sizeIndicator || lang.freeSpace + ' 0')
		])
	]);

	this.saveNewLang = function () {
		var error = false;

		data.forEach(function ( item ) {
			var status = stbDownloadManager.AddJob(item.data.url, self.$deviceSelect.GetSelected().mountPath + '/' + item.data.name);
			echo('add new record');
			echo('url:   ' + item.data.url);
			echo('path:  ' + self.$deviceSelect.GetSelected().mountPath);
			echo('status:' + status);
			if ( !status ) { error = true; }
		});
		self.Show(false);
		new CModalHint(currCPage, error ? lang.mediaDefaultError : lang.added, 3000);
	};

	this.bpanel = new CButtonPanel();
	this.bpanel.Init(CMODAL_IMG_PATH);
	this.bpanel.Add(KEYS.EXIT, useNewIcons ? 'new/exit2.png' : 'ico_exit.png', lang.cancel, function () { self.Show(false); });
	this.bpanel.Add(KEYS.OK, 'ico_ok.png', lang.download, function () { self.saveNewLang(); });

	// filling
	this.SetHeader(lang.download + ' ' + data.length + ' ' + lang.files);
	this.SetContent(html);
	this.SetFooter(this.bpanel.handle);

	this.onShow = function () { self.$deviceSelect.focus(); };

	// free resources on hide
	this.onHide = function () {
		elclear(self.bpanel.handle);
		delete self.bpanel;
		self.Free();
	};

	// forward events to button panel
	this.EventHandler = function ( event ) {
		echo('modal eventhandler');
		if ( !eventPrepare(event, true, 'CModalAlert') ) {return;}
		switch ( event.code ) {
			case KEYS.CHANNEL_NEXT: // channel+
			case KEYS.CHANNEL_PREV: // channel-
				event.preventDefault(); // to suppress tabbing
				break;
			case KEYS.UP:
			case KEYS.DOWN:
				break;
			case KEYS.LEFT:
			case KEYS.RIGHT:
				this.$deviceSelect.EventHandler(event);
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
CModalAddDownload.prototype = Object.create(CModalBox.prototype);
CModalAddDownload.prototype.constructor = CModalAddDownload;
