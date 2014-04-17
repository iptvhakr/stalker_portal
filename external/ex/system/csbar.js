/**
 * Search bar module
 * contains a text input and icon/button
 * @param {Node} parent component
 * @class CSearchBar
 * @constructor
 * @author DarkPark
 */
function CSearchBar ( parent ) {
	// parent constructor
	CBase.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CSearchBar";

	/**
	 * CSS class name associated with the component
	 * it is checking on initialization in the placeholder
	 * if not found a wrapper is created
	 * @type {String}
	 */
	this.baseClass = 'csbar-main';

	/**
	 * text data for filtering
	 * @type {String}
	 */
	this.filter = '';

	/**
	 * directory with icons
	 * depends on screen resolution
	 * @type {String}
	 */
	this.path = "";

	/**
	 * list of inner nodes
	 * @type {Object}
	 */
	this.items = {};

	/**
	 * numerical ID of the timeout
	 * @type {Number}
	 */
	this.timer = 0;

	/**
	 * grey hint text line
	 * @type {String}
	 */
	this.hint = 0;
}

// extending
CSearchBar.prototype = Object.create(CBase.prototype);
CSearchBar.prototype.constructor = CSearchBar;

/**
 * Component initialization with image path set.
 * Should be called once before use just after constructor invoke and all hooks preparation.
 * @param {String} path image path dependant on resolution
 * @param {Node} [handle] component placeholder
 * @param {String} hint default hint text
 */
CSearchBar.prototype.Init = function ( path, handle, hint ) {
	// global vars
	this.path = path;
	this.hint = hint || "";

	var self = this;
	var func = function () {
		self.Activate(!self.isActive);
		event.stopPropagation();
	};

	// parent call init with placeholder
	CBase.prototype.Init.call(this, handle);

	this.items.hint   = element('input', {type: 'text', className: 'hint maxw', value:this.hint});
	this.items.text   = element('input', {type: 'text', className: 'text maxw', onkeydown:function(event){ self.EventHandler(event); }});
	this.items.span   = element('div', {className: 'csbar-item span'});
	this.items.icon   = element('div', {className: 'csbar-item icon', onclick: func}, element('img', {className: '', src: this.path + '/ico_search2.png'}));
	this.items.button = element('div', {className: 'csbar-item icon', onclick: func}, element('img', {className: '', src: this.path + '/ico_f2.png'}));

	// add to component container
	this.items.input = element('div', {className: 'csbar-item stext'}, [this.items.hint, this.items.text]);
	elchild(this.handleInner, [this.items.input, this.items.span, this.items.icon, this.items.button]);
};


/**
 * Manage the component search input field visibility
 * @param {Boolean} [visible=true] component visibility: true - visible, false - hidden
 * @return {Boolean} status: true - operation was successful (mode changed), false - operation was skipped
 * @param {Boolean} [manageFocus=true] flag to manage focus handling
 */
CSearchBar.prototype.ShowInput = function ( visible, manageFocus ) {
	visible = visible !== false;

	if ( !visible ) {
		this.items.text.blur();
		gSTB.HideVirtualKeyboard();
		// return focus to the previous element if necessary and set
		if ( manageFocus !== false && this.prevFocus ) this.prevFocus.focus();
	}

	this.items.input.style.display = visible ? 'table-cell' : 'none';
	this.items.span.style.display  = visible ? 'none' : 'table-cell';

	if ( visible ) {
		// save the previous focused element
		this.prevFocus = document.activeElement;
		// set focus to input
		this.items.text.focus();
		gSTB.ShowVirtualKeyboard();
	}
};


/**
 * Apply default parameters
 *
 */
CSearchBar.prototype.Reset = function () {
	this.Activate(false);
	this.items.text.value = this.items.hint.value = this.hint = this.filter = "";
	this.items.icon.lastChild.src = this.path + '/ico_search2.png';
};


/**
 * Search callback
 * @param {String} value search value
 */
CSearchBar.prototype.onSearch = null;


/**
 * Get hint if search value was changed
 * @param {String} value search value
 */
CSearchBar.prototype.onHint = null;


/**
 * Apply the hint value to the input element
 * @param {String} hint search suggest text
 */
CSearchBar.prototype.SetHint = function ( hint ) {
	hint = hint || this.items.text.value || this.hint;
	// apply if there is a hint
	if ( hint ) this.items.hint.value = hint;
};


/**
 * Handle external events
 * @param {Event} event global event object
 */
CSearchBar.prototype.EventHandler = function ( event ) {
	echo('CSearchBar.prototype.EventHandler event.keyCode=' + event.keyCode + ' event.code' + event.code);
	switch ( event.keyCode ) {
		case KEYS.LEFT:
		case KEYS.RIGHT:
		case KEYS.UP:
		case KEYS.DOWN:
//			event.stopPropagation();
			break;
		case KEYS.EXIT:
			this.Activate(false);
			event.stopPropagation();
			break;
		case KEYS.F2:
			this.Activate(!this.isActive);
			event.stopPropagation();
			break;
		case KEYS.OK:
			this.Activate(false);
			if ( this.onSearch instanceof Function ) this.onSearch(this.items.text.value);	// hook
			this.items.icon.firstChild.src = this.items.text.value ? this.path + '/ico_filter.png' : this.path + '/ico_search2.png';	// set icon
			event.stopPropagation();
			break;
		default:
			echo('prototype.default');
			this.items.hint.value = this.items.text.value === '' ? this.hint : "";// hide or show default hint state
			// hint callback set
			if ( this.onHint instanceof Function ) {
				if ( this.items.hint.value == this.hint ) {
					this.SetHint(' ');
				}
				if ( this.timer ) {
					clearTimeout(this.timer);
				}
				var self = this;
				this.timer = setTimeout(function () {
					self.onHint.call(self, self.items.text.value);
				}, 300);
			}
			break;
	}
};


/**
 * Set active state of the component
 * @param {Boolean} active state
 * @param {Boolean} [manageFocus=true] flag to manage focus handling
 */
CSearchBar.prototype.Activate = function ( active, manageFocus ) {
	if ( !active ) this.ShowInput(false, manageFocus);

	// parent call init with placeholder
	CBase.prototype.Activate.call(this, active);

	if ( active ) this.ShowInput(true, manageFocus);
};