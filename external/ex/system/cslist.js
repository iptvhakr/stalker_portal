/**
 * Item list navigation module
 * @class CScrollList
 * @constructor
 * @author DarkPark
 */
function CScrollList ( parent ) {
	// parent constructor
	CBase.call(this, parent);

	/**
	 * The component inner name
	 * @type {String}
	 */
	this.name = "CScrollList";

	/**
	 * CSS class name associated with the component
	 * @type {String}
	 */
	this.baseClass = "cslist-main";

	/**
	 * the current selected item
	 * @type {Node}
	 */
	this.activeItem = null;

	/**
	 * List of items for each state flag
	 * Example: {focused:[item], marked:[item,item]}
	 * @type {Object}
	 */
	this.states = {};

	/**
	 * default item attribute values
	 * used for an item initialization
	 * @type {Object}
	 */
        this.defaultParams = {
            hidden   : false,  // display or not
            marked   : false,  // initial checked state
            disabled : false,  // can be focused or not
            focused  : false,  // initial focused state
            href     : '#',    // empty link
            self     : this,   // back link to the component itself
            // right mouse click (suppress the context menu)
            oncontextmenu : function() { 
                if(old_system_type){
                    // activate item
                    this.self.Focused(this, true); 
                }
                return false;
            },
            // mouse click on the item or Ok/Enter key
            onclick : function() {
                // activate item
                this.self.Focused(this, true);
                return false;
            },
            onmouseover : function() {
                if(!old_system_type){
                    // activate item
                    this.self.Focused(this, true);
                }
                return false;
            }
        };

	/**
	 * default item filter values
	 * used for focus handling
	 * @type {Object}
	 */
	this.defaultFilter = {
		hidden   : false,  // visible
		disabled : false   // enabled
	};

	/**
	 * amount of items on a single page
	 * automatically calculates on the first page up/down event
	 * @type {Number}
	 */
	this.itemsPerPage = 0;

	/**
	 * item height if available
	 * automatically calculates on the first key up/down event
	 * @type {Number}
	 */
	this.itemHeight = 0;

	/**
	 * scrolling method on up/down arrows
	 * 0 - no special way (default browser shift to the center on focus)
	 * 1 - focus is always centered (convenient but redraw the whole container on each step)
	 * 2 - shift by pages (quick and low resources)
	 * @type {Number}
	 */
	this.scrollMode = 2;

}


// extending
//extend(CScrollList, CBase);
CScrollList.prototype = Object.create(CBase.prototype);
CScrollList.prototype.constructor = CScrollList;

/**
 * Create a new item and add it to the placeholder
 * visible/enabled/not focused and not checked by default
 * corresponding css classes (the same names as flags):
 *     hidden   - for invisible items
 *     marked   - for checked items
 *     disabled - for items that can't be focused or selected
 *     focused  - for a single item active at the moment
 * @param {String|Node|Array} body item content
 * @param {Object} [attrs] list of element attributes
 * @return {Node} created item element
 */
CScrollList.prototype.Add = function ( body, attrs ) {
	// check input
	attrs = attrs || {};
	// add to DOM container and return link to a new item
	var item = this.handleInner.appendChild(element('a', this.defaultParams, body));
	// apply flags and decoration
	if ( attrs.hidden   ) this.Hidden(item,   true);
	if ( attrs.marked   ) this.Marked(item,   true);
	if ( attrs.disabled ) this.Disabled(item, true);
	if ( attrs.focused  ) this.Focused(item,  true);
	// apply custom attributes with the current defaults
	for ( var name in attrs ) { item[name] = attrs[name]; }
	// result element
	return item;
};


/**
 * Reset and clear all items and options.
 * This will make the component ready for a new filling.
 */
CScrollList.prototype.Clear = function () {
	// cleaning all items
	//elclear(this.handleInner); can be slow on huge list
	this.handleInner.innerHTML = null;  // not a life-saver :/
	// vars
	this.activeItem = null;
	this.states = {};
};


/**
 * Reset only the given item to the default state
 * @param {Node} item the element to be processed
 */
CScrollList.prototype.Reset = function ( item ) {
	// valid html element given
	if ( item && item.nodeName ) {
		// apply flags and decoration
		this.Hidden(item,   this.defaultParams.hidden);
		this.Marked(item,   this.defaultParams.marked);
		this.Disabled(item, this.defaultParams.disabled);
		this.Focused(item,  this.defaultParams.focused);
		// clear focus pointer if necessary
		if ( item === this.activeItem && !item.focused ) this.activeItem = null;
	}
};


/**
 * Removes the given elements and reposition the focus
 * @param {[Node]} items list of elements to be processed
 */
CScrollList.prototype.DeleteAll = function ( items ) {
	var self   = this,
		curPos = null;
	// collect affected items
	//var items = this.ActiveItems();
	// there are some
	if ( items instanceof Array && items.length > 0 ) {
		// clear focus (for future refocus)
		if ( document.activeElement !== null && document.activeElement.parentNode === this.handleInner ) document.activeElement.blur();
		// cursor position
		if ( items.indexOf(this.Current()) === -1 ) {
			// not intersect
			curPos = this.Current();
		} else {
			// get the next good (scan down)
			curPos = this.Next({marked:false, hidden:false, disabled:false});
			// not found or the last in the list
			if ( curPos === null || curPos === this.Current() ) {
				// scan up
				curPos = this.Next({marked:false, hidden:false, disabled:false}, true);
			}
		}
		// apply
		items.forEach(function ( item ) {
			self.Delete(item);
		});
		// the nearest available item
		if ( curPos !== null ) this.Focused(curPos, true);
	}
};


/**
 * Remove the given item and clear inner states if necessary
 * @param {Node} item the element to be processed
 */
CScrollList.prototype.Delete = function ( item ) {
	// valid html element given
	if ( item && item.nodeName && item.parentNode === this.handleInner ) {
		// clear states
		for ( var name in this.states ) {
			// find
			var index = this.states[name].indexOf(item);
			// remove
			if ( index !== -1 ) this.states[name].splice(index, 1);
		}
		// clear focus pointer if necessary
		if ( item === this.activeItem ) this.activeItem = null;
		// delete dom element
		this.handleInner.removeChild(item);
	}
};


/**
 * Getter for currently focused element
 * @return {Node} or null if there is no such item
 */
CScrollList.prototype.Current = function () {
	return this.activeItem;
};


/**
 * Getter for element total number
 * @return {Number}
 */
CScrollList.prototype.Length = function () {
	return this.handleInner.children.length;
};


/**
 * Set scroll position relatively some list element
 * @param {Object} item
 * @param {Boolean} [makeFocused] - apply all attributes and corresponding actions
 * @param {Boolean} [manageFocus] - set actual focus
 */
CScrollList.prototype.SetPosition = function ( item, makeFocused, manageFocus ) {
	var index, page;
	if ( makeFocused ) {
		if ( manageFocus === undefined ) { manageFocus = this.manageFocus; }
		this.Focused(item || this.FindOne(), true, manageFocus);
	}
	if ( this.activeItem !== null ) {
		index = this.activeItem.offsetTop / this.itemHeight;
		page = Math.floor(index / this.itemsPerPage);
		// different methods to scroll
		if ( this.scrollMode === 1 ) {
			this.handleInner.scrollTop = (index - Math.floor((this.itemsPerPage - 1) / 2)) * this.itemHeight;
		} else if ( this.scrollMode === 2 ) {
			this.handleInner.scrollTop = (this.itemsPerPage * this.itemHeight * page);
		}
	}
};

/**
 * Set inner item flags and decoration
 * @param {Node} item the element to be processed
 * @param {String} option item inner flag name
 * @param {Boolean} state flag of the operation (true if change is made)
 * @return {Boolean} operation status
 */
CScrollList.prototype.SetState = function ( item, option, state ) {
	state = Boolean(state);
	// current and new states are different
	if ( item[option] !== state ) {
		// check if exist
		if ( !this.states[option] ) this.states[option] = [];
		var index = this.states[option].indexOf(item);
		// update internal list
		if ( state ) {
			// add to the list
			if ( index === -1 ) this.states[option].push(item);
		} else {
			// remove
			if ( index !== -1 ) this.states[option].splice(index, 1);
		}
		var oldVal = item[option];
		// flag
		item[option] = state;
		// decoration
		if ( state ) {
			// add the corresponding class
			item.className += ' ' + option + ' ';
		} else {
			// remove the corresponding class
			item.className = item.className.replace(' ' + option + ' ', '');
		}
		// call user hook
		if ( this.onStateChange instanceof Function ) this.onStateChange(item, option, oldVal, state);
		return true;
	}
	// nothing has changed
	return false;
};


/**
 * Handle visibility state for the given item
 * also correct check/focus state if hiding
 * @param {Node} item the element to be processed
 * @param {Boolean} state flag of the state
 * @return {Boolean} operation status
 */
CScrollList.prototype.Hidden = function ( item, state ) {
	state = Boolean(state);
	// valid html element given
	if ( item && item.nodeName ) {
		// flag and decoration
		var changed = this.SetState(item, 'hidden', state);
		// operation ok and the item is hidden
		if ( changed && state ) {
			// clear internal cursor if necessary
			if ( item.focused ) this.activeItem = null;
			// uncheck and remove focus
			this.SetState(item, 'marked', false);
			this.SetState(item, 'focused', false);
		}
		// operation status
		return changed;
	}
	// failure
	return false;
};


/**
 * Handle checked state for the given item
 * @param {Node} item the element to be processed
 * @param {Boolean} state flag of the state
 * @return {Boolean} operation status
 */
CScrollList.prototype.Marked = function ( item, state ) {
	state = Boolean(state);
	// valid html element given, enabled and visible
	if ( item && item.nodeName && !item.disabled && !item.hidden ) {
		// operation status
		return this.SetState(item, 'marked', state);
	}
	// failure
	return false;
};


/**
 * Handle enable/disable state for the given item
 * @param {Node} item the element to be processed
 * @param {Boolean} state flag of the state
 * @return {Boolean} operation status
 */
CScrollList.prototype.Disabled = function ( item, state ) {
	state = Boolean(state);
	// valid html element given
	if ( item && item.nodeName ) {
		// flag and decoration
		var changed = this.SetState(item, 'disabled', state);
		// operation ok and the item is disabled
		if ( changed && state ) {
			// clear internal cursor if necessary
			if ( item.focused ) this.activeItem = null;
			// uncheck and remove focus
			this.SetState(item, 'marked', false);
			this.SetState(item, 'focused', false);
		}
		// operation status
		return changed;
	}
	// failure
	return false;
};


/**
 * Handle focus state for the given item
 * also removes the focus from the previously focused item
 * @param {Node} item the element to be processed
 * @param {Boolean} [state=true] flag of the state
 * @param {Boolean} [manageFocus=true] flag to manage focus handling
 * @return {Boolean} operation status
 */
CScrollList.prototype.Focused = function ( item, state, manageFocus ) {
	var changed = false,
		prevent = false;
		state   = state !== false;
	// valid html element given, enabled and visible
	if ( item && item.nodeName && !item.disabled && !item.hidden ) {
		// states differ
		if ( state !== item.focused ) {
			if ( state ) {
				// different items (not currently active item)
				if ( item !== this.activeItem ) {
					// call user hook which can prevent further processing
					if ( this.onFocus instanceof Function ) prevent = this.onFocus(item, this.activeItem);
					// block or not
					if ( !prevent ) {
						// flag and decoration
						changed = this.SetState(item, 'focused', state);
						// clear the previously focused item
						this.Focused(this.activeItem, false, manageFocus);
						// global flag
						this.activeItem = item;
					}
				}
				// set actual focus if necessary
				if ( manageFocus !== false ) this.activeItem.focus();
			} else {
				// flag and decoration
				changed = this.SetState(item, 'focused', state);
				// focus removed if necessary
				if ( manageFocus !== false ) this.activeItem.blur();
				this.activeItem = null;
			}
		}
	}
	// operation status
	return changed;
};


/**
 * Make the whole component active, set focused item and give actual focus
 * give a focus to the appropriate item (last focused or the first one)
 * @param {Boolean} [state=true] set active or deactivate
 * @param {Boolean} [manageFocus=true] focus handling mode: true - set/remove focus accordingly, false - manual focus management
 * @return {Boolean} operation status
 */
CScrollList.prototype.Activate = function ( state, manageFocus ) {
	// parent call
	CBase.prototype.Activate.call(this, state);
	if ( this.isActive ) {
		// get the first good one
		this.activeItem = this.activeItem || this.FindOne();
		// still no active item
		if ( this.activeItem === null ) return false;
		// flag and decoration
		this.SetState(this.activeItem, 'focused', true);
		// make it focused
		if ( manageFocus !== false ) this.activeItem.focus();
		// all is ok
		return true;
	} else {
		// remove focus if there is an element
		if ( this.activeItem ) this.activeItem.blur();
	}
};


/**
 * Go through all the items
 * @param {Function} callback iteration callback function
 */
CScrollList.prototype.Each = function ( callback ) {
	Array.prototype.forEach.call(this.handleInner.children, callback);
};


/**
 * Get item list according to the given filter conditions and amount limitation
 * @param {Object} [filter=this.defaultFilter] list of attributes for searching
 * @param {Number} [limit=0] amount of items to get (0 - all possible)
 * @param {Boolean} [reverse=false] to invert search direction (true - search backwards, false - from first to last)
 * @return {Node[]} found items
 */
CScrollList.prototype.Find = function ( filter, limit, reverse ) {
	// preparing
	var match,                              // flag for items comparison
		found = [],                         // result item list
		items = this.handleInner.children,  // all list items
		itlen = items.length,               // item list amount
		citem = null;                       // current item pointer
	// use default if not set
	filter = filter || this.defaultFilter;
	// iterate all items till all items are found
	for ( var i = 0; i < itlen; i++ ) {
		// floating pointer depends on direction
		citem = items[reverse ? itlen-i-1 : i];
		// init state
		match = true;
		// check all the filter attributes (all should match)
		for ( var attr in filter ) match = match && (citem[attr] === filter[attr]);
		// matched item
		if ( match ) {
			// add to the result list
			found.push(citem);
			// check limit and exit if set and enough
			if ( limit && found.length >= limit ) break;
		}
	}
	return found;
};


/**
 * Get the first item matching the given filter conditions
 * @param {Object} [filter=this.defaultFilter] list of attributes for searching
 * @param {Boolean} [reverse=false] to invert search direction (true - search backwards, false - from first to last)
 * @return {Node|null} found item or null
 */
CScrollList.prototype.FindOne = function ( filter, reverse ) {
	return this.Find(filter, 1, reverse).pop() || null;
};


/**
 * Get the next/previous item from the current focused item
 * according to the given filter and search direction
 * searching for a closest next item by default
 * can go to the next/previous page with nskip = items-per-page
 * @param {Object} [filter=this.defaultFilter] list of attributes for searching
 * @param {Boolean} [reverse=false] to invert search direction (true - return previous, false - next)
 * @param {Number} [nskip=0] amount of items to skip
 * @return {Node|null} found item or null if there are no suitable ones
 */
CScrollList.prototype.Next = function ( filter, reverse, nskip ) {
	// preparing
	var match,                        // flag for items comparison
		suitable  = this.activeItem,  // the last found matching item (starting from the current)
		pointer   = this.activeItem,  // the floating current item for processing
		skipcount = 0;                // counter of found items per page
	// amount of items to skip
	nskip = nskip || 0;
	// there is a starting item
	if ( pointer ) {
		// use default if not set
		filter = filter || this.defaultFilter;
		// iterate from the current position till the edge of the list
		while ( (pointer = (reverse ? pointer.previousSibling : pointer.nextSibling)) ) {
			// suitable by default
			match = true;
			// check all the filter attributes (all should match)
			for ( var attr in filter ) match = match && (pointer[attr] === filter[attr]);
			// count only visible items
			if ( !pointer.hidden ) skipcount++;
			// suitable item is found
			if ( match ) {
				// matching becomes the current
				suitable = pointer;
				// skip item correction if necessary
				if ( nskip === 0 || (nskip > 0 && skipcount >= nskip) ) return suitable;
			}
		}
	}
	return suitable;
};


/**
 * Handle external events
 * @param {Event} event global event object
 * @param {Object} [filter=this.defaultFilter] list of attributes for searching
 */
CScrollList.prototype.EventHandler = function ( event, filter ) {
	// check if there is page items amount available, calculate otherwise
	if ( this.itemsPerPage === 0 && this.handleInner.firstChild !== null ) this.itemsPerPage = Math.round(this.handleInner.offsetHeight / this.handleInner.firstChild.offsetHeight);
	// check if there is page items amount available, calculate otherwise
	if ( this.itemHeight === 0 && this.handleInner.firstChild !== null ) this.itemHeight = this.handleInner.firstChild.offsetHeight;
	//echo(this.handleInner.offsetHeight, 'this.handleInner.offsetHeight');
	//echo(this.itemsPerPage, 'this.itemsPerPage');
	//echo(this.itemHeight, 'this.itemHeight');

	var found = null;
	// moving direction
	switch ( event.code ) {
		case KEYS.PAGE_UP:
		case KEYS.PAGE_DOWN:
			// jump to the next/previous item skiping page amount of items
			found = this.Next(filter, event.code !== KEYS.PAGE_DOWN, this.itemsPerPage);
			// correct visible view
			this.handleInner.scrollTop = this.handleInner.scrollTop + (event.code === KEYS.PAGE_UP ? -1 : 1 ) * this.itemsPerPage * this.itemHeight;
//			if ( event.code === KEYS.PAGE_UP ) {
//				//this.handleInner.scrollTop = this.activeItem.offsetTop - this.itemsPerPage * this.itemHeight;
//				this.handleInner.scrollTop = this.handleInner.scrollTop + (event.code === KEYS.PAGE_UP ? -1 : 1 ) * this.itemsPerPage * this.itemHeight;
//			} else {
//				//this.handleInner.scrollTop = this.activeItem.offsetTop + this.itemsPerPage * this.itemHeight;
//				this.handleInner.scrollTop = this.handleInner.scrollTop + this.itemsPerPage * this.itemHeight;
//			}
			// different methods to scroll
			/*if ( this.scrollMode === 0 ) {

			} else if ( this.scrollMode === 1 ) {
				// focus is always centered (convenient but redraw the whole container on each step)

			} else if ( this.scrollMode === 2 ) {
				// shift by pages (quick and low resources)

			}*/
			break;
		case KEYS.LEFT:
		case KEYS.RIGHT:
			// look for a singlie item from the beginning or end of the list
			found = this.FindOne(filter, event.code === KEYS.RIGHT);
			break;
		case KEYS.UP:
		case KEYS.DOWN:
			// jump to the next/previous item
			found = this.Next(filter, event.code === KEYS.UP);
			echo(this.handleInner.scrollTop, 'this.handleInner.scrollTop');
			echo(this.activeItem.offsetTop, 'this.activeItem.offsetTop');
			// different methods to scroll
			if ( this.scrollMode === 1 ) {
				// focus is always centered (convenient but redraw the whole container on each step)
				this.handleInner.scrollTop = this.activeItem.offsetTop - (this.itemsPerPage-1)/2 * this.itemHeight + (event.code === KEYS.UP ? -1 : 1)*this.itemHeight;
			} else if ( this.scrollMode === 2 ) {
				// shift by pages (quick and low resources)
				if ( event.code === KEYS.UP ) {
					if ( this.activeItem.offsetTop === this.handleInner.scrollTop ) this.handleInner.scrollTop = this.handleInner.scrollTop - this.itemsPerPage * this.itemHeight;
				} else {
					if ( this.activeItem.offsetTop - this.handleInner.scrollTop === (this.itemsPerPage-1) * this.itemHeight ) this.handleInner.scrollTop = this.handleInner.scrollTop + this.itemsPerPage * this.itemHeight;
				}
			}
			echo(this.handleInner.scrollTop, 'this.handleInner.scrollTop');
			break;
		case KEYS.OK:
			// blank but necessary to prevent suppression
			// !exit!
			if ( this.activeItem ) this.activeItem.onclick();  // commented to prevent double invoke
			event.preventDefault();
			return;
		default:
			// suppress everything else and exit
			//event.preventDefault();
			return;
	}
	event.preventDefault();
	// make focused the first item if not found
	this.Focused(found || this.FindOne(filter), true);
};


/**
 * Hook method on focus item change
 * shold be declared in child to invoke
 * @param {Node} item the new focused item
 * @param {Node} previous the old focused item
 * @return {Boolean} true - prevent focus from changing, false|undefined - usual behaviour
 */
CScrollList.prototype.onFocus = null;


/**
 * Hook method on item internal states change
 * shold be declared in child to invoke
 * @param {Node} item the new focused item
 * @param {String} option affected item state name
 * @param {String|Boolean} oldVal previous state value
 * @param {String|Boolean} newVal new state value
 */
CScrollList.prototype.onStateChange = null;