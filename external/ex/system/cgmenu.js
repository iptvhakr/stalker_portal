/**
 * Menu panel with groups module
 * General use case:
 *   - create CGroupMenu object
 *   - add group/groups
 *   - fill group/groups with items
 *   - switch to the default group
 * @class CGroupMenu
 * @constructor
 * @author DarkPark
 */
function CGroupMenu ( parent ) {
	// parent constructor
	CBase.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CGroupMenu";

	/**
	 * CSS class name associated with the component
	 * @type {String}
	 */
	this.baseClass = "cgmenu-main";

	/**
	 * current active group
	 * @type {Node}
	 */
	this.activeGroup = null;

	/**
	 * is the component has focus at the moment
	 * @type {Boolean}
	 */
	this.active = false;

	//this.onEvent(this.EventHandler);
	//this.events.keydown = this.EventHandler;
}


// extending
//CGroupMenu.prototype = Object.create(CBase.prototype);
extend(CGroupMenu, CBase);


/**
 * Finds and returns the group data object
 * @param {String|Number} gid unique name of the group
 * @return {Object|Boolean} found group data object or false
 */
CGroupMenu.prototype.GetGroup = function ( gid ) {
	// iterate all items
	for ( var i = 0, items = this.handleInner.children, length = items.length; i < length; i++ ) {
		// check data and validate id
		if ( items[i].gid === gid ) return items[i];
	}
	return false;
};


/**
 * Add a new group with items
 * @param {String|Number} gid unique name of the group
 * @param {String} title group visible title
 * @param {Object} [options] list of the group attributes (onclick and hidden)
 * @return {Node|Boolean} group data object
 */
CGroupMenu.prototype.AddGroup = function ( gid, title, options ) {
	// valid gid and not already exist
	if ( gid && !this.GetGroup(gid) ) {
		// CScrollList placeholder
		var hlist = element('div', {className:'cslist-main'});
		// create html
		var group = element('table', {
			gid     : gid,
			slist   : new CScrollList(),
			options : options || {}
		}, [
			// title row
			element('tr', {className:'title'}, [
				element('td', {className:'side'}, element('p')), // arrow left
				element('td', null, title),                      // group title
				element('td', {className:'side'}, element('p'))  // arrow right
			]),
			// group items row
			element('tr', {className:'ilist'}, element('td', {colSpan:3}, hlist))
		]);
		// correct group attributes if necessary
		group.options.hidden = group.options.hidden ? true : false;
		// create scroll list
		group.slist.Init(hlist);
		//group.slist.Activate(true, false);
		// add to dom
		elchild(this.handleInner, group);
		// ok
		return group;
	}
	return false;
};


/**
 * Manage the given group visibility
 * @param {Node} group the group element to alter
 * @param {Boolean} state true - set hidden; false - set visible
 */
CGroupMenu.prototype.Hidden = function ( group, state ) {
	// valid group object
	if ( group && group.nodeName ) {
		// set inner attribute
		group.options.hidden = state;
	}
};


/**
 * Fill the given group with specified item
 * @param {Object} group group data object
 * @param {String|Number} iid unique name of the group item
 * @param {String|Node|Array} body group item title or complex content
 * @param {Object} [options] group item additional attributes (hidden, marked, focused, disabled)
 * @return {Node} created and added group item
 */
CGroupMenu.prototype.AddItem = function ( group, iid, body, options ) {
	// prepare attributes list
	options = options || {};
	// item id
	options.iid = iid;
	// default handler for each item if not overwritten
	if ( group.options.onclick && !options.onclick ) options.onclick = group.options.onclick;
	// add item to the group list
	return group.slist.Add(body, options);
};


/**
 * Switch to the given group (hides the current one)
 * @param {Object} group new active group
 * @param {Boolean} [activate] flag: true - make new visible group focused; false - skip activation (default)
 * @return {Boolean} operation status: true - successfully switched
 */
CGroupMenu.prototype.Switch = function ( group, activate ) {
	// validate group object
	if ( group && group.gid && group.nodeName ) {
		// hide the previous one
		if ( this.activeGroup ) {
			this.activeGroup.slist.Activate(false);
			this.activeGroup.style.display = 'none';
		}
		// set global active flag
		this.activeGroup = group;
		// show it
		this.activeGroup.style.display = 'table';
		// set focus and active state if necessary
		if ( activate ) this.activeGroup.slist.Activate();
		// ok
		return true;
	}
	return false;
};


/**
 * Set active group
 * @param {Boolean} [active=true] set active or deactivate
 * @param {Boolean} [setFocus=true] focus handling mode: true - set/remove focus accordingly, false - manual focus management
 * @return {Boolean} operation status
 */
CGroupMenu.prototype.Activate = function ( active, setFocus ) {
	var status = false;
	if ( this.activeGroup !== null ) status = this.activeGroup.slist.Activate(active, setFocus);
	return status;
};


/**
 * Get the next/previous item from the current item
 * according to the given filter and search direction
 * searching for a closest next item by default
 * @param {Object} [filter=this.defaultFilter] list of attributes for searching
 * @param {Boolean} [reverse=false] to invert search direction (true - return previous, false - next)
 * @return {Node|null} found item or null if there are no suitable ones
 */
CGroupMenu.prototype.Next = function ( filter, reverse ) {
	// preparing
	var match,                       // flag for items comparison
		pointer = this.activeGroup;  // the floating current item for processing
	// there is a starting item
	if ( pointer ) {
		// iterate from the current position till the edge of the list
		while ( (pointer = (reverse ? pointer.previousSibling : pointer.nextSibling)) ) {
			// suitable by default
			match = true;
			// check all the filter attributes (all should match)
			for ( var attr in filter ) match = match && (pointer.options[attr] === filter[attr]);
			// suitable item is found
			if ( match ) return pointer;
		}
	}
	return null;
};


/**
 * Handle external events
 * @param {Event} event global event object
 */
CGroupMenu.prototype.EventHandler = function ( event ) {
	// moving directions
	switch ( event.code ) {
		case KEYS.LEFT:
			this.Switch(this.Next({hidden:false}, true), true);
			break;
		case KEYS.RIGHT:
			this.Switch(this.Next({hidden:false}), true);
			break;
		default:
			// delegate everything else to scroll list
			this.activeGroup.slist.EventHandler(event);
	}
};
