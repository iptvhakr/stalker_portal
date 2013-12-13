/**
 * Class for button with images panel.
 * @class CButtonPanel
 * @constructor
 * @author DarkPark
 */
function CButtonPanel ( parent ) {
	// parent constructor
	CBase.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CButtonPanel";

	/**
	 * CSS class name associated with the component
	 * @type {String}
	 */
	this.baseClass = "cbpanel-main";

	/**
	 * directory with icons
	 * depends on screen resolution
	 * @type {String}
	 */
	this.path = "";

	/**
	 * Flag to indicate the component state
	 * @type {Boolean}
	 */
	this.isActive = true;
}

// extending
//extend(CButtonPanel, CBase);
CButtonPanel.prototype = Object.create(CBase.prototype);
CButtonPanel.prototype.constructor = CButtonPanel;

/**
 * Component initialization with image path set.
 * Should be called once before use just after constructor invoke and all hooks preparation.
 * @param {String} path image path dependant on resolution
 * @param {Node} [handle] component placeholder
 */
CButtonPanel.prototype.Init = function ( path, handle ) {
	// global image path
	this.path = path;

	if ( handle && handle.nodeName ) {
		// parent call init with placeholder
		CBase.prototype.Init.call(this, handle);
	} else {
		// parent call init with placeholder
		CBase.prototype.Init.call(this, element('div', {className:'cbpanel-main'}));
	}
};


/**
 * Append a new button
 * @param {Number} code keydown code
 * @param {String} icon file of the button icon
 * @param {String} text button title
 * @param {Function} callback click/keyboard handler
 * @param {Boolean} [hidden=false] is button visible
 * @returns {Node}
 */
CButtonPanel.prototype.Add = function ( code, icon, text, callback, hidden ) {
	// prepare text
	var html = null,
		self = this,
		func = function(){
			if ( self.isActive && callback instanceof Function ) callback(code);
		};
	if ( text ) {
		html = element('div', {className:'cbpanel-text', onclick:func});
		html.innerHTML = text;
	}
	// build button item
	var item = element('div', {className:'cbpanel-item', data:{code:code, onclick:func}}, [
		element('img', {className:'cbpanel-icon', onclick:func, src:this.path + '/' + icon}),
		html
	]);
	// apply visibility option
	this.Hidden(item, hidden || false);
	// add to component container
	elchild(this.handleInner, item);
	return item;
};


/**
 * Manage the given item visibility
 * @param {Node} item the group element to alter
 * @param {Boolean} state true - set hidden; false - set visible
 */
CButtonPanel.prototype.Hidden = function ( item, state ) {
	// valid group object and states are different
	if ( item && item.nodeName && item.data.hidden !== state ) {
		// set inner attribute
		item.data.hidden = state;
		// actual show/hide
		item.style.display = state ? 'none' : 'table-cell';
	}
};


/**
 * Makes the component active or disable it to start/stop event handling
 * @param {Boolean} [active=true]
 */
CButtonPanel.prototype.Activate = function ( active ) {
	this.isActive = active !== false;
};


/**
 * Handle external events
 * @param {Event} event global event object
 */
CButtonPanel.prototype.EventHandler = function ( event ) {
	if ( this.isActive ) {
		// iterate all items
		for ( var i = 0, data, items = this.handleInner.children, length = items.length; i < length; i++ ) {
			data = items[i].data;
			// check data, visibility and validate id
			if ( !data.hidden && data.code === event.code && data.onclick instanceof Function ) {
				data.onclick();
				// stop event spreading
				event.preventDefault();
				return;
			}
		}
	}
};

CButtonPanel.prototype.onClick = function () {
	//this.Activate(true);
	return false;
};