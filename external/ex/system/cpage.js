/**
 * Class for main pages of the portal.
 * Each page should be created from this class.
 * @class CPage
 * @constructor
 * @author DarkPark
 */
function CPage ( parent ) {
	// parent constructor
	CBase.call(this, parent || null);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CPage";

	/**
	 * the object that will become visible after this one is hidden
	 * @type {CPage|CBase}
	 */
	this.previous = null;

	/**
	 * default visibility state
	 * @type {Boolean}
	 */
	this.isVisible = false;

}

// extending
CPage.prototype = Object.create(CBase.prototype);
CPage.prototype.constructor = CPage;


/**
 * Manage the page visibility
 * @param {Boolean} [visible=true] component visibility: true - visible, false - hidden
 * @param {CPage|CBase} [previous=null] the page to return to on this page hiding
 */
CPage.prototype.Show = function ( visible, previous ) {
	// custom action
	if ( visible === false ) { // hide
		// turn off events
		if ( this.activeChild ) this.activeChild.Activate(false);
		// hide this
		CBase.prototype.Show.call(this, false, true, true);

		// go to home if not set
		currCPage = this.previous || ServiceMenu;
		this.previous = null;

		// if set and not itself
		if ( currCPage && currCPage !== this ) {
			// show it
			CBase.prototype.Show.call(currCPage, true, true, true);
		}
	} else { // show
		// if set and not itself
		if ( currCPage && currCPage !== this ) {
			if ( currCPage.activeChild ) currCPage.activeChild.Activate(false);
			// hide it
			CBase.prototype.Show.call(currCPage, false, true, true);
		}

		// show this
		CBase.prototype.Show.call(this, true, true, true);

		// set back route
		this.previous = previous || null;
		currCPage = this;
	}
};


CPage.prototype.onClick = function () {
	//this.Activate(true);
	return false;
};