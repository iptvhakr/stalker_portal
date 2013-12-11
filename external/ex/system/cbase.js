/**
 * Base class for any visual component.
 * Always has one html placeholder.
 * @class CBase
 * @constructor
 * @author DarkPark
 */
function CBase ( parent ) {
	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CBase";

	/**
	 * Component placeholder
	 * should be never used for append nodes inside the component methods
	 * use this.handleInner instead
	 * @type {Node}
	 */
	this.handle = null;

	/**
	 * Object owner of the component
	 * @type {CBase}
	 */
	this.parent = null;

	/**
	 * Component main body placeholder (should be the same as this.handle)
	 * can be different only in case when the given placeholder is invalid (don't have the necessary class)
	 * so the valid wrapper with this.baseClass should be created instead
	 * @type {Node}
	 */
	this.handleInner = null;

	/**
	 * CSS "display" attribute value to make the component visible
	 * to hide the default value is "none"
	 * @type {String}
	 */
	this.showAttr = 'block';

	/**
	 * CSS class name associated with the component
	 * it is checking on initialization in the placeholder
	 * if not found a wrapper is created
	 * @type {String}
	 */
	this.baseClass = "";

	/**
	 * The previous DOM element that had focus
	 * (used for focus management on show/hide operations)
	 * @type {Node}
	 */
	this.prevFocus = null;

	/**
	 * Flag to indicate the component state
	 * using for event handling and activate/deactivate hooks
	 * @type {Boolean}
	 */
	this.isActive = false;

	/**
	 * Current active child element
	 * @type {CBase}
	 */
	this.activeChild = null;

	/**
	 * default visibility state
	 * @type {Boolean}
	 */
	this.isVisible = true;

	// apply hierarchy
	this.SetParent(parent);

}


/**
 * Set the parent object owner of this component
 * @param {CBase} parent object owner
 */
CBase.prototype.SetParent = function ( parent ) {
	// check input
	if ( parent instanceof CBase ) {
		// store here as well
		this.parent = parent;
	}
};


/**
 * Component initialization with its placeholder.
 * Should be called once before use just after constructor invoke and all hooks preparation.
 * @param {Node} handle component placeholder (it should be an empty div element)
 */
CBase.prototype.Init = function ( handle ) {
	// input validation
	if ( handle && handle.nodeName ) {
		//echo('Init', this.name);

		// global store
		this.handle = handle;

		// the given placeholder is invalid (don't have the necessary base class)
		if ( this.baseClass && handle.className.indexOf(this.baseClass) === -1 ) {
			// add wrapper
			this.handleInner = this.handle.appendChild(element('div', {className:this.baseClass}));
		} else {
			// the component body pointer
			this.handleInner = this.handle;
		}

		var self = this;
		this.handle.addEventListener('click', function(event){
			if ( self.onClick instanceof Function && self.onClick() === false ) {
				// prevents further propagation of the current event
				event.stopPropagation();
			}
		});

		// run callback hook
		if ( this.onInit instanceof Function ) this.onInit();
	}
};


/**
 * Makes the component active or disable it
 * process the activation/deactivation hooks for this component and its parent
 * @param {Boolean} [active=true]
 */
CBase.prototype.Activate = function ( active ) {
	this.isActive = active !== false;
	if ( this.isActive ) {
		//echo('Activate', this.name);
		// run this component activation callback hook
		if ( this.onActivate instanceof Function ) this.onActivate();
		// has parent
		if ( this.parent ) {
			// run the previous active component deactivation (if not itself)
			if ( this.parent.activeChild && this.parent.activeChild !== this ) this.parent.activeChild.Activate(false);
			// set link in the parent
			this.parent.activeChild = this;
		}
	} else {
		//echo('Deactivate', this.name);
		// run this component deactivation callback hook
		if ( this.onDeactivate instanceof Function ) this.onDeactivate();
		// has parent
		if ( this.parent ) {
			// set link in the parent
			this.parent.activeChild = null;
		}
	}
};


/**
 * Manage the component visibility, global focus and exec show/hide hooks
 * @param {Boolean} [visible=true] component visibility: true - visible, false - hidden
 * @param {Boolean} [manageFocus=true] focus handling mode: true - set/remove focus accordingly, false - manual focus management
 * @return {Boolean} status: true - operation was successful (mode changed), false - operation was skipped
 */
CBase.prototype.Show = function ( visible, manageFocus ) {
	var success = false, self = this;
	// placeholder validation
	if ( this.handle ) {
		this.isVisible = visible !== false;
		// show
		if ( this.isVisible ) {
			// prevent double invoke
			if ( this.handle.style.display !== this.showAttr ) {
				//echo('Show', this.name);
				// save the previous focused element
				this.prevFocus = document.activeElement;
				// remove focus if necessary and set
				if ( manageFocus !== false && document.activeElement ) document.activeElement.blur();
				// show this component
				this.handle.style.display = this.showAttr;
				// set focus if necessary
				if ( manageFocus !== false ) this.handle.focus();
				// invoke callback hook
				if ( this.onShow instanceof Function ) this.onShow();
				success = true;
			}
		// hide
		} else {
			// prevent double invoke
			if ( this.handle.style.display !== 'none' ) {
				//echo('Hide', this.name);
				// remove focus if necessary and set
				if ( manageFocus !== false && document.activeElement ) document.activeElement.blur();
				// hide this component
				this.handle.style.display = 'none';
				// return focus to the previous element if necessary and set
				if ( manageFocus !== false && this.prevFocus ) this.prevFocus.focus();
				// invoke callback hook
				if ( this.onHide instanceof Function ) this.onHide();
				// deactivate this component if necessary
				if ( this.isActive ) this.Activate(false);
				success = true;
			}
		}
	}
	return success;
};


/**
 * Events handler entry point.
 * Should be recreated if necessary in each child object to handle parent events.
 * @type {Function}
 */
CBase.prototype.EventHandler = null;

// hooks to redefine
CBase.prototype.onInit = null;
CBase.prototype.onShow = null;
CBase.prototype.onHide = null;
CBase.prototype.onActivate = null;
CBase.prototype.onDeactivate = null;


/**
 * Method to activate a componeny by mouse click on it
 * @returns {Boolean}
 */
CBase.prototype.onClick = function () {
	this.Activate(true);
	return true;
};