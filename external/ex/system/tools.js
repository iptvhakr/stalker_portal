/**
 * Set of base functionality tools
 * @author DarkPark
 */

// debug print blank placeholder for release builds
if ( window.echo === undefined ) { window.echo = function ( data, title ) {}; }


/**
 * Assigns a list of attribute values to the given object
 * @param {Node} obj html element
 * @param {Object} attr list of attributes with values
 * @return {Node} the same as the given one
 * @example
 *   elattr(myimg, {src:'face.png', className:'main'});
 */
function elattr ( obj, attr ) {
	// check if Node and valid attr list
	if ( obj && obj.nodeType && attr && attr instanceof Object ) {
		// assign attributes
		for ( var akey in attr ) obj[akey] = attr[akey];
	}
	return obj;
}


/**
 * Creates a DOM element with given options
 * @param {String} name html element name (a, img, div, ...)
 * @param {Object} [attr] list of attributes with values
 * @param {Node|Array|String} [data] inner html value
 * @return {Node}
 * @example
 *   element('div', {}, 'some text');
 *   element('div', {}, [element('span'), element('br')]);
 *   element('link', {rel:'stylesheet', type:'text/css', href:'http://some.url/'});
 */
function element ( name, attr, data ) {
	var tag = document.createElement(name);
	// set attributes
	elattr(tag, attr);
	// add to the dom
	elchild(tag, data);
	// Node is ready
	return tag;
}


/**
 * Adds the given value to the obj as a child recursively
 * @param {Node} obj element to be appended
 * @param value data to add (simple text values, Nodes, array of Nodes)
 * @return {Node} owner element of all added data
 * @example
 *   elchild(mydiv, 'Hello world'); // simple text value
 *   elchild(mydiv, someotherdiv); // Node
 *   elchild(mydiv, [div1, div2, div3]); // Node list
 *   elchild(mydiv, [div1, 'hello', 'world']); // combined case
 */
function elchild ( obj, value ) {
	// check input
	if ( obj && value ) {
		// Node
		if ( value.nodeType ) obj.appendChild(value);
		// array of Nodes of simple values
		else if ( value instanceof Array ) {
			for ( var i = 0; i < value.length; i++ ) elchild(obj, value[i]);
		}
		// simple values
		else obj.appendChild(document.createTextNode(value));
	}
	return obj;
}


/**
 * Removes all child elements from the given object
 * @param {Node} obj html element to be updated
 * @return {Node} cleared element
 */
function elclear ( obj ) {
	// check input
	if ( obj !== null && obj.nodeName && obj.hasChildNodes() ) {
		// clearing
		while ( obj.hasChildNodes() ) obj.removeChild(obj.firstChild);
	}
	return obj;
}


/**
 * Loads the given stylesheet file dynamically
 * head injection method is used
 * @param {String} src file to be loaded
 * @param {Function} [onload] optional handler
 */
function loadStyle ( src, onload ) {
	// make style link
	document.head.appendChild(element('link', {rel:'stylesheet', type:'text/css', href:src}));
	//elchild(document.head, element('link', {rel:'stylesheet', type:'text/css', href:src}));
	// run callback if given with dumb image
	if ( onload ) element('img', {onerror:function(){onload();}, src:'***'});
}


/**
 * Loads the given JavaScript file dynamically
 * head injection method is used
 * @param {String} src file to be loaded
 * @param {Function} [onload] optional handler
 */
function loadScript ( src, onload ) {
	// Node init
	var elem = element('script', {type:'text/javascript', src:src});
	// set handler if given
	if ( onload instanceof Function ) elem.onload = onload;
	// push to dom
	document.head.appendChild(elem);
	//elchild(document.head, elem);
}


/**
 * Returns the active language
 * @return {String} lowercase lang name
 */
function getCurrentLanguage () {
	return gSTB.RDir('getenv language').toLowerCase() || 'en';
}


/**
 * Prepare global event object - add the real key code
 * should be called only once at the beginning of events chain
 * with shift key pressed +1000
 * with alt key pressed +2000
 * @param {Event} event object to be altered
 * @param {Boolean} [stopBubble=true] stops all propagation of the event in the bubbling phase
 * @param {String} [label] optional text info for debug
 * @returns {Boolean} true - valid event; false - phantom and should be skipped
 */
function eventPrepare ( event, stopBubble, label ) {
	// prevent double invoke
	if ( event.code ) return true;
	// determine real key code
	event.code = event.keyCode || event.which;
	// filter phantoms
	if ( event.code === 0 ) return false;
	// apply key modifiers
	if ( event.shiftKey ) event.code += 1000;
	if ( event.altKey ) event.code += 2000;
	// stop bubbling if necessary
	if ( stopBubble !== false ) event.stopPropagation();
	// debug
	echo(event.code + "\t" + event.shiftKey + "\t" + event.ctrlKey + "\t" + event.altKey + "\t" + event.srcElement.id + "\t" + event.target.id + "\t" + (label || ""),
		'keyDown [code/shift/ctrl/alt/src/target]');/**/
	return true;
}


///**
// * Simple way to implement class inheritance
// * @param {Object} Child new class
// * @param {Object} Parent base class
// */
//function extend ( Child, Parent ) {
//	var F = function(){};
//	F.prototype = Parent.prototype;
//	Child.prototype = new F();
//	Child.prototype.constructor = Child;
//	Child.parent = Parent.prototype;
//}

/**
 * Combines two objects and write result to target object
 * @param {Object} target object to which the data will be transferred
 * @param {Object} source object from which the data will be transferred
 * @param [override = true] if set to false target object not rewriting, result of combining returns from function
 * @returns {Object} combined object
 */
function extend (target, source, override) {
	var _target = (override === false ? extend({}, target) : target || {});
	for (var prop in source) {
		if ( typeof _target[prop] === 'object' && typeof source[prop] === 'object' && !Array.isArray(_target[prop]) && !Array.isArray(source[prop]) ) {
			_target[prop] = extend(_target[prop], source[prop], override);
		} else {
			_target[prop] = source[prop];
		}
	}
	return _target;
}
/**
 * Ajax request
 * @param {String} method "post", "get" or "head"
 * @param {String} url address
 * @param {Function} callback on
 * @param {Object} [headers] list of optional headers like "charset", "Content-Type" and so on
 * @param {String} [type] data parsing mode: plain text (default), xml, json
 * @return {XMLHttpRequest} request object in case response headers are necessary
 * @example
 *   ajax('get', 'https://google.com/', function(data, status){console.info(data, status);}, {charset:'utf-8'})
 */
function ajax ( method, url, callback, headers, type ) {
	var hname, xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if ( xhr.readyState === 4 ) {
			callback(type === 'xml' ? xhr.responseXML : (type === 'json' ? JSON.parse(xhr.responseText) : xhr.responseText), xhr.status);
		}
	};
	xhr.open(method, url, true);
	// set headers if present
	if ( headers ) for ( hname in headers ) {
		xhr.setRequestHeader(hname, headers[hname]);
	}
	xhr.send();
	return xhr;
}

/**
 * Object represents simple event model
 * @type {{ bind: Function, trigger: Function, inject: Function}}
 */
var Events = {
	/**
	 * Assign new event to the current object
	 * @param {String|Object} event Event name or Object where the key is event name and value is handler
	 * @param {Function} callback Function that will be executed when event was triggered
	 */
	bind: function ( event, callback ) {
		this._events || (this._events = {});
		if ( typeof event === 'object' ) {
			for ( var name in event ) {
				this.bind(name, event[name]);
			}
		} else if ( typeof event === 'string' && typeof callback === 'function' ) {
			if ( this._events[event] === undefined ) {
				this._events[event] = [];
			}
			this._events[event].push(callback);
		}
	},

	/**
	 * Trigger some event
	 * @param {String} event Name of events which will be triggered
	 */
	trigger: function ( event, data ) {
		var result, results = [], self = this;
		if ( event !== undefined && this._events !== undefined && this._events[event] !== undefined ) {
			this._events[event].forEach(function ( ev ) {
					result = ev.call(self, data);
					if ( result != undefined ) { results.push(result); }
				}
			);
		}
		return results;
	},

	/**
	 * Remove event handlers for specified event
	 * @param {String} event Name of removed event
	 */
	unbind: function(event){
		delete this._events[event];
	},

	/**
	 * Inject current functionality to another object or function
	 * @param {Object|Function} obj Object which is embedded functionality
	 */
	inject: function( obj ){
		if (typeof obj === 'function'){
			extend(obj.prototype, this);
		}else if (typeof obj === 'object'){
			extend(obj, this);
		}
	}
};