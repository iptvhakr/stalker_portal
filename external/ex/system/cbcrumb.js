/**
 * Class for breadcrumb with images.
 * @class CBreadCrumb
 * @constructor
 * @author DarkPark
 */
function CBreadCrumb ( parent ) {
	// parent constructor
	CBase.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CBreadCrumb";

	/**
	 * CSS class name associated with the component
	 * @type {String}
	 */
	this.baseClass = "cbcrumb-main";

	/**
	 * directory with icons
	 * depends on screen resolution
	 * @type {String}
	 */
	this.path = "";

	//this.length = 16;
	this.litems = 3;
	this.ritems = 2;
}

// extending
//extend(CBreadCrumb, CBase);
CBreadCrumb.prototype = Object.create(CBase.prototype);
CBreadCrumb.prototype.constructor = CBreadCrumb;

/**
 * Component initialization with image path set.
 * @param {String} path image path dependant on resolution
 * @param {Node} [handle] component placeholder
 */
CBreadCrumb.prototype.Init = function ( path, handle, litems, ritems ) {
	// global vars
	this.path = path;

	// parent call init with placeholder
	CBase.prototype.Init.call(this,handle);

	this.litems = litems || this.litems;
	this.ritems = ritems || this.ritems;
};


/**
 * Append a new button
 * @param {String} path
 * @param {String} [icon] file of the button icon
 * @param {String} [text] button title
 * @returns {Node}
 */
CBreadCrumb.prototype.Push = function ( path, icon, text ) {
	var last = this.handleInner.lastChild;
	if ( last ) last.className = 'cbcrumb-item';
//	if ( text ) {
//		if ( text.length > this.length ) text = text.slice(0, this.length-3) + '...';
//	}
	// build item
	var item = element('div', {className:'cbcrumb-item active', onclick:null, path:path}, [
		icon ? element('img', {className:'cbcrumb-icon', onclick:null, src:this.path + '/' + icon}) : null,
		text ? element('div', {className:'cbcrumb-text', onclick:null}, text) : null
	]);
	// add divider
	if ( this.handleInner.children.length === this.litems ) {
		elchild(this.handleInner, element('div', {className:'cbcrumb-item divider'}, '. . .'));
	}
	// add to component container
	elchild(this.handleInner, item);
	// show ... and hide item
	if ( this.handleInner.children.length > this.litems+this.ritems+1 ) {
		this.handleInner.children[this.litems].style.display = 'table-cell';
		this.handleInner.children[this.handleInner.children.length-this.ritems-1].style.display = 'none';
	}
	return item;
};


CBreadCrumb.prototype.Pop = function () {
	var item = this.handleInner.lastChild;
	if ( item ) {
		this.handleInner.removeChild(item);

		// remove divider
		if ( this.handleInner.children.length === this.litems+1 ) {
			this.handleInner.removeChild(this.handleInner.lastChild);
		} else if ( this.handleInner.children.length > this.litems+this.ritems ) {
			//this.handleInner.children[2].style.display = 'table-cell';
			//this.handleInner.children[this.handleInner.children.length-this.ritems].style.display = 'none';
			this.handleInner.children[this.handleInner.children.length-this.ritems].style.display = 'table-cell';
		}
		if ( this.handleInner.children.length === this.litems+this.ritems+1 ) {
			this.handleInner.children[this.litems].style.display = 'none';
		}

		if ( this.handleInner.lastChild ) this.handleInner.lastChild.className = 'cbcrumb-item active';
	}
	return item;
};


CBreadCrumb.prototype.Reset = function () {
	elclear(this.handleInner);
};


CBreadCrumb.prototype.onClick = function () {
	//this.Activate(true);
	return false;
};