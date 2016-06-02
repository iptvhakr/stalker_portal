/*!
 * 
 * gettext parser workaround
 * gettext("EXPLORER");
 * gettext("Watch movies, listen to music or view the photos, saved on your removable storages or within the local network.");
 * 
 * 
 */
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/*!************************************!*\
  !*** ./app/js/stb/develop/main.js ***!
  \************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Main module to setup development environment.
			 *
			 * @module stb/develop/main
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var app     = __webpack_require__(/*! ../app */ 3),
			    metrics = __webpack_require__(/*! ../../../../config/metrics */ 9);
			
			
			window.localStorage = window.localStorage || window.stbStorage || (window.parent !== window ? window.parent.localStorage || window.parent.stbStorage : null);
			
			// set global mode
			app.data.debug = true;
			
			// STB device or emulation?
			app.data.host = !!(window.gSTB || (window.parent && (window.parent.gSTB || window.parent.host)));
			
			// platform?
			if ( app.data.host ) {
			    // web inspector
			    __webpack_require__(/*! ./weinre */ 28);
			}
			
			// inside frame/iframe
			if ( window.parent && window.parent.gSTB ) {
			    // link to the outer gSTB global object
			    // only for gSTB.Debug
			    window.gSTB = window.parent.gSTB;
			}
			
			// apply screen size, position, margins and styles
			if ( localStorage && localStorage.getItem('screen.height') ) {
			    app.setScreen(metrics[localStorage.getItem('screen.height')]);
			} else {
			    app.setScreen(
			        metrics[screen.height] ||
			        metrics[720]
			    );
			}
			
			
			// additional dev modules
			__webpack_require__(/*! ./shims */ 26);
			__webpack_require__(/*! ./static */ 27);
			__webpack_require__(/*! ./proxy */ 25);
			__webpack_require__(/*! ./events */ 22);
			__webpack_require__(/*! ./debug */ 21);
			__webpack_require__(/*! ./overrides */ 24);
			
			// the application itself
			__webpack_require__(/*! ../../main */ 15);


/***/ },
/* 1 */
/*!*********************************!*\
  !*** ./app/js/stb/component.js ***!
  \*********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/component
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Emitter = __webpack_require__(/*! ./emitter */ 5),
			    router  = __webpack_require__(/*! ./router */ 7),
			    counter = 0;
			
			
			/**
			 * Base component implementation.
			 *
			 * Visual element that can handle sub-components.
			 * Each component has a DOM element container $node with a set of classes:
			 * "component" and some specific component class names depending on the hierarchy, for example "page".
			 * Each component has a unique ID given either from $node.id or from data.id. If not given will generate automatically.
			 *
			 * @constructor
			 * @extends Emitter
			 *
			 * @param {Object} [config={}] init parameters
			 * @param {Element} [config.id] component unique identifier (generated if not set)
			 * @param {string} [config.className] space-separated list of classes for "className" property of this.$node
			 * @param {Element} [config.$node] DOM element/fragment to be a component outer container
			 * @param {Element} [config.$body] DOM element/fragment to be a component inner container (by default is the same as $node)
			 * @param {Component} [config.parent] link to the parent component which has this component as a child
			 * @param {Array.<Component>} [config.children=[]] list of components in this component
			 * @param {Object.<string, function>} [config.events={}] list of event callbacks
			 * @param {boolean} [config.visible=true] component initial visibility state flag
			 * @param {boolean} [config.focusable=true] component can accept focus or not
			 * @param {boolean} [config.propagate=false] allow to emit events to the parent component
			 *
			 * @fires module:stb/component~Component#click
			 *
			 * @example
			 * var component = new Component({
			 *     $node: document.getElementById(id),
			 *     className: 'bootstrap responsive',
			 *     events: {
			 *         click: function () { ... }
			 *     }
			 * });
			 * component.add( ... );
			 * component.focus();
			 */
			function Component ( config ) {
			    // current execution context
			    var self = this,
			        name;
			
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.id        && typeof config.id !== 'string'         ) { throw new Error(__filename + ': wrong or empty config.id'); }
			        if ( config.className && typeof config.className !== 'string'  ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.$node     && !(config.$node instanceof Element)    ) { throw new Error(__filename + ': wrong config.$node type'); }
			        if ( config.$body     && !(config.$body instanceof Element)    ) { throw new Error(__filename + ': wrong config.$body type'); }
			        if ( config.parent    && !(config.parent instanceof Component) ) { throw new Error(__filename + ': wrong config.parent type'); }
			        if ( config.children  && !Array.isArray(config.children)       ) { throw new Error(__filename + ': wrong config.children type'); }
			    }
			
			    /**
			     * Component visibility state flag.
			     *
			     * @readonly
			     * @type {boolean}
			     */
			    this.visible = true;
			
			    /**
			     * Component can accept focus or not.
			     *
			     * @type {boolean}
			     */
			    this.focusable = true;
			
			    /**
			     * DOM outer handle.
			     *
			     * @type {Element}
			     */
			    this.$node = null;
			
			    /**
			     * DOM inner handle.
			     * In simple cases is the same as $node.
			     *
			     * @type {Element}
			     */
			    this.$body = null;
			
			    /**
			     * Link to the parent component which has this component as a child.
			     *
			     * @type {Component}
			     */
			    this.parent = null;
			
			    /**
			     * List of all children components.
			     *
			     * @type {Component[]}
			     */
			    this.children = [];
			
			    /**
			     * allow to emit events to the parent component
			     *
			     * @readonly
			     * @type {boolean}
			     */
			    this.propagate = !!config.propagate;
			
			    // parent constructor call
			    Emitter.call(this, config.data);
			
			    // outer handle - empty div in case nothing is given
			    this.$node = config.$node || document.createElement('div');
			
			    // inner handle - the same as outer handler in case nothing is given
			    this.$body = config.$body || this.$node;
			
			    // set CSS class names
			    this.$node.className += ' component ' + (config.className || '');
			
			    // apply component id if given, generate otherwise
			    this.id = config.id || this.$node.id || 'cid' + counter++;
			
			    // apply hierarchy
			    if ( config.parent ) {
			        // add to parent component
			        config.parent.add(this);
			    }
			
			    // apply given visibility
			    if ( config.visible === false ) {
			        // default state is visible
			        this.hide();
			    }
			
			    // apply focus handling method
			    if ( config.focusable === false ) {
			        // can't accept focus
			        this.focusable = false;
			    }
			
			    // a descendant defined own events
			    if ( this.defaultEvents ) {
			        // sanitize
			        config.events = config.events || {};
			
			        if ( true ) {
			            if ( typeof config.events !== 'object' ) { throw new Error(__filename + ': wrong config.events type'); }
			            if ( typeof this.defaultEvents !== 'object' ) { throw new Error(__filename + ': wrong this.defaultEvents type'); }
			        }
			
			        for ( name in this.defaultEvents ) {
			            // overwrite default events with user-defined
			            config.events[name] = config.events[name] || this.defaultEvents[name];
			        }
			    }
			
			    // apply given events
			    if ( config.events ) {
			        // apply
			        this.addListeners(config.events);
			    }
			
			    // apply the given children components
			    if ( config.children ) {
			        // apply
			        this.add.apply(this, config.children);
			    }
			
			    // component activation by mouse
			    this.$node.addEventListener('click', function ( event ) {
			        // left mouse button
			        if ( event.button === 0 ) {
			            // activate if possible
			            self.focus();
			
			            // there are some listeners
			            if ( self.events['click'] ) {
			                /**
			                 * Mouse click event.
			                 *
			                 * @event module:stb/component~Component#click
			                 *
			                 * @type {Object}
			                 * @property {Event} event click event data
			                 */
			                self.emit('click', {event: event});
			            }
			        }
			
			        if ( true ) {
			            // middle mouse button
			            if ( event.button === 1 ) {
			                debug.inspect(self, 0);
			                debug.info('"window.link" or "' + self.id + '.component"', 'this component is now available in global scope');
			                window.link = self;
			                self.$node.classList.toggle('wired');
			            }
			        }
			
			        event.stopPropagation();
			    });
			
			    if ( true ) {
			        // expose inner ID to global scope
			        window[self.id] = self.$node;
			
			        // expose a link
			        this.$node.component = this.$body.component = this;
			        this.$node.title = 'component ' + this.constructor.name + '.' + this.id + ' (outer)';
			        this.$body.title = 'component ' + this.constructor.name + '.' + this.id + ' (inner)';
			    }
			}
			
			
			// inheritance
			Component.prototype = Object.create(Emitter.prototype);
			Component.prototype.constructor = Component;
			
			
			/**
			 * List of all default event callbacks.
			 *
			 * @type {Object.<string, function>}
			 */
			Component.prototype.defaultEvents = null;
			
			
			/**
			 * Add a new component as a child.
			 *
			 * @param {...Component} [child] variable number of elements to append
			 *
			 * @files Component#add
			 *
			 * @example
			 * panel.add(
			 *     new Button( ... ),
			 *     new Button( ... )
			 * );
			 */
			Component.prototype.add = function ( child ) {
			    var i;
			
			    // walk through all the given elements
			    for ( i = 0; i < arguments.length; i++ ) {
			        child = arguments[i];
			
			        if ( true ) {
			            if ( !(child instanceof Component) ) { throw new Error(__filename + ': wrong child type'); }
			        }
			
			        // apply
			        this.children.push(child);
			        child.parent = this;
			
			        // correct DOM parent/child connection if necessary
			        if ( child.$node && child.$node.parentNode === null ) {
			            this.$body.appendChild(child.$node);
			        }
			
			        // there are some listeners
			        if ( this.events['add'] ) {
			            /**
			             * A child component is added.
			             *
			             * @event module:stb/component~Component#add
			             *
			             * @type {Object}
			             * @property {Component} item new component added
			             */
			            this.emit('add', {item: child});
			        }
			
			        debug.log('component ' + this.constructor.name + '.' + this.id + ' new child: ' + child.constructor.name + '.' + child.id);
			    }
			};
			
			
			/* @todo: consider activation in future */
			///**
			// * Insert component into the specific position.
			// *
			// * @param {Component} child component instance to insert
			// * @param {number} index insertion position
			// */
			//Component.prototype.insert = function ( child, index ) {
			//    var prevIndex = this.children.indexOf(child);
			//
			//    if ( DEBUG ) {
			//        if ( arguments.length !== 2 ) { throw new Error(__filename + ': wrong arguments number'); }
			//        if ( !(child instanceof Component) ) { throw new Error(__filename + ': wrong child type'); }
			//    }
			//
			//    if ( prevIndex !== -1 ) {
			//        this.children.splice(prevIndex, 1);
			//        this.$body.removeChild(child.$node);
			//    }
			//
			//    if ( index === this.children.length ) {
			//        this.$body.appendChild(child.$node);
			//    } else {
			//        this.$body.insertBefore(child.$node, this.$body.children[index]);
			//    }
			//    this.children.splice(index, 0, child);
			//
			//    if ( !child.parent ) {
			//        child.parent = this;
			//    }
			//};
			
			
			/**
			 * Delete this component and clear all associated events.
			 *
			 * @fires module:stb/component~Component#remove
			 */
			Component.prototype.remove = function () {
			    // really inserted somewhere
			    if ( this.parent ) {
			        if ( true ) {
			            if ( !(this.parent instanceof Component) ) { throw new Error(__filename + ': wrong this.parent type'); }
			        }
			
			        // active at the moment
			        if ( router.current.activeComponent === this ) {
			            this.blur();
			            this.parent.focus();
			        }
			        this.parent.children.splice(this.parent.children.indexOf(this), 1);
			    }
			
			    // remove all children
			    this.children.forEach(function ( child ) {
			        if ( true ) {
			            if ( !(child instanceof Component) ) { throw new Error(__filename + ': wrong child type'); }
			        }
			
			        child.remove();
			    });
			
			    this.removeAllListeners();
			    this.$node.parentNode.removeChild(this.$node);
			
			    // there are some listeners
			    if ( this.events['remove'] ) {
			        /**
			         * Delete this component.
			         *
			         * @event module:stb/component~Component#remove
			         */
			        this.emit('remove');
			    }
			
			    debug.log('component ' + this.constructor.name + '.' + this.id + ' remove', 'red');
			};
			
			
			/**
			 * Activate the component.
			 * Notify the owner-page and apply CSS class.
			 *
			 * @param {Object} [data] custom data which passed into handlers
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/component~Component#focus
			 */
			Component.prototype.focus = function ( data ) {
			    var activePage = router.current,
			        activeItem = activePage.activeComponent;
			
			    // this is a visual component on a page
			    // not already focused and can accept focus
			    if ( this.focusable && this !== activeItem ) {
			        // notify the current active component
			        if ( activeItem ) { activeItem.blur(); }
			
			        /* eslint consistent-this: 0 */
			
			        // apply
			        activePage.activeComponent = activeItem = this;
			        activeItem.$node.classList.add('focus');
			
			        // there are some listeners
			        if ( activeItem.events['focus'] ) {
			            /**
			             * Make this component focused.
			             *
			             * @event module:stb/component~Component#focus
			             */
			            activeItem.emit('focus', data);
			        }
			
			        debug.log('component ' + this.constructor.name + '.' + this.id + ' focus');
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Remove focus.
			 * Change page.activeComponent and notify subscribers.
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/component~Component#blur
			 */
			Component.prototype.blur = function () {
			    var activePage = router.current,
			        activeItem = activePage.activeComponent;
			
			    // apply visuals anyway
			    this.$node.classList.remove('focus');
			
			    // this is the active component
			    if ( this === activeItem ) {
			        activePage.activeComponent = null;
			
			        // there are some listeners
			        if ( this.events['blur'] ) {
			            /**
			             * Remove focus from this component.
			             *
			             * @event module:stb/component~Component#blur
			             */
			            this.emit('blur');
			        }
			
			        debug.log('component ' + this.constructor.name + '.' + this.id + ' blur', 'grey');
			
			        return true;
			    }
			
			    debug.log('component ' + this.constructor.name + '.' + this.id + ' attempt to blur without link to a page', 'red');
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Make the component visible and notify subscribers.
			 *
			 * @param {Object} [data] custom data which passed into handlers
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/component~Component#show
			 */
			Component.prototype.show = function ( data ) {
			    // is it hidden
			    if ( !this.visible ) {
			        // correct style
			        this.$node.classList.remove('hidden');
			        // flag
			        this.visible = true;
			
			        // there are some listeners
			        if ( this.events['show'] ) {
			            /**
			             * Make the component visible.
			             *
			             * @event module:stb/component~Component#show
			             */
			            this.emit('show', data);
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return true;
			};
			
			
			/**
			 * Make the component hidden and notify subscribers.
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/component~Component#hide
			 */
			Component.prototype.hide = function () {
			    // is it visible
			    if ( this.visible ) {
			        // correct style
			        this.$node.classList.add('hidden');
			        // flag
			        this.visible = false;
			
			        // there are some listeners
			        if ( this.events['hide'] ) {
			            /**
			             * Make the component hidden.
			             *
			             * @event module:stb/component~Component#hide
			             */
			            this.emit('hide');
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return true;
			};
			
			
			// public
			module.exports = Component;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/component.js"))

/***/ },
/* 2 */
/*!****************************!*\
  !*** ./app/js/stb/keys.js ***!
  \****************************/
/***/ function(module, exports) {

			/**
			 * Global list of non-printable control key codes.
			 *
			 * WARNING!!! All codes in this file (exclude 'volumeUp', 'volumeDown')
			 * uses in window 'keydown' handler to prevent wrong 'keypress' firings.
			 * If u add code into this file, 'keypress' event with this code will never fires.
			 *
			 *  Value | Description
			 * -------|-------------
			 *  +1000 | shift key pressed
			 *  +2000 | alt key pressed
			 *
			 * @module stb/keys
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint quote-props: 0 */
			
			// public
			module.exports = {
			    back         : 8,    // Backspace
			    'delete'     : 46,
			    channelPrev  : 1009, // Shift+Tab
			    channelNext  : 9,    // Tab
			    ok           : 13,   // Enter
			    exit         : 27,   // Esc
			    up           : 38,   // UP ARROW
			    down         : 40,   // DOWN ARROW
			    left         : 37,   // LEFT ARROW
			    right        : 39,   // RIGHT ARROW
			    pageUp       : 33,   // Page Up
			    pageDown     : 34,   // Page Down
			    end          : 35,
			    home         : 36,
			    volumeUp     : 107,  // NUMPAD +
			    volumeDown   : 109,  // NUMPAD -
			    f1           : 112,  // F1
			    f2           : 113,  // F2
			    f3           : 114,  // F3
			    f4           : 115,  // F4
			    refresh      : 116,  // F5
			    frame        : 117,  // F6
			    phone        : 119,  // F8
			    set          : 120,  // F9
			    tv           : 121,  // F10
			    menu         : 122,  // F11
			    web          : 123,  // F12
			    mic          : 2032,
			    rewind       : 2066, // Alt+B
			    forward      : 2070, // Alt+F
			    app          : 2076, // Alt+L
			    usbMounted   : 2080, // Alt+P
			    usbUnmounted : 2081, // Alt+Q
			    playPause    : 2082, // Alt+R
			    stop         : 2083, // Alt+S
			    power        : 2085, // Alt+U
			    record       : 2087, // Alt+W
			    info         : 2089, // Alt+Y
			    mute         : 2192,
			    clock        : 2032,
			    audio        : 2071, // Alt+G
			    keyboard     : 2076  // Alt+L
			};


/***/ },
/* 3 */
/*!***************************!*\
  !*** ./app/js/stb/app.js ***!
  \***************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/app
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Model    = __webpack_require__(/*! ./model */ 30),
			    router   = __webpack_require__(/*! ./router */ 7),
			    keys     = __webpack_require__(/*! ./keys */ 2),
			    metrics  = __webpack_require__(/*! ../../../config/metrics */ 9),
			    keyCodes = {},
			    app, key, linkCSS, linkThemeCSS;
			
			
			__webpack_require__(/*! ./shims */ 32);
			
			window.core = window.parent.getCoreInstanse(window);
			window.core.once('load', function () {
			    console.log('core loaded');
			
			    if ( app.data.time.load ) {
			        app.defaultEvents.load({type: 'load'});
			    }
			});
			
			// app inside sandbox iframe
			window.localStorage = window.parent.localStorage || window.parent.stbStorage;
			
			// inside frame/iframe
			if ( window.parent && window.parent.gSTB ) {
			    // link to the outer global objects
			    window.dvbManager         = window.parent.dvbManager;
			    window.epgManager         = window.parent.epgManager;
			    window.gSTB               = window.parent.gSTB;
			    window.pvrManager         = window.parent.pvrManager;
			    window.stbDownloadManager = window.parent.stbDownloadManager;
			    window.stbStorage         = window.parent.stbStorage;
			    window.stbUpdate          = window.parent.stbUpdate;
			    window.stbUPnP            = window.parent.stbUPnP;
			    window.stbWebWindow       = window.parent.stbWebWindow;
			    window.stbWindowMgr       = window.parent.stbWindowMgr;
			    window.timeShift          = window.parent.timeShift;
			}
			
			
			/**
			 * @instance
			 * @type {Model}
			 */
			app = new Model({
			    /**
			     * Enable logging and debugging flag set by debug module at runtime.
			     *
			     * @type {boolean}
			     */
			    debug: false,
			
			    /**
			     * True if executed on the STB device, set by debug module at runtime.
			     *
			     * @type {boolean}
			     */
			    host: true,
			
			    /**
			     * Screen geometry and margins.
			     *
			     * @type {Object}
			     * @property {number} height Total screen height
			     * @property {number} width Total screen width
			     * @property {number} availTop top safe zone margin
			     * @property {number} availRight right safe zone margin
			     * @property {number} availBottom bottom safe zone margin
			     * @property {number} availLeft left safe zone margin
			     * @property {number} availHeight safe zone height
			     * @property {number} availWidth safe zone width
			     */
			    screen: null,
			
			    /**
			     * Timestamps data.
			     *
			     * @type {Object}
			     * @property {number} init application initialization time (right now)
			     * @property {number} load document onload event
			     * @property {number} done onload event sent and processed
			     */
			    time: {
			        init: +new Date(),
			        load: 0,
			        done: 0
			    }
			});
			
			
			/**
			 * Set crops, total, content size and link the corresponding CSS file.
			 *
			 * @param {Object} metrics screen params specific to resolution
			 *
			 * @return {boolean} operation status
			 */
			app.setScreen = function ( metrics ) {
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			    }
			
			    if ( metrics ) {
			        if ( true ) {
			            if ( typeof metrics !== 'object' ) { throw new Error(__filename + ': wrong metrics type'); }
			        }
			
			        // calculate and extend
			        metrics.availHeight = metrics.height - (metrics.availTop + metrics.availBottom);
			        metrics.availWidth  = metrics.width - (metrics.availLeft + metrics.availRight);
			
			        // set max browser window size
			        window.moveTo(0, 0);
			        window.resizeTo(metrics.width, metrics.height);
			
			
			        // already was initialized
			        if ( linkCSS && linkCSS instanceof HTMLLinkElement ) {
			            // remove all current CSS styles
			            document.head.removeChild(linkCSS);
			        }
			
			        // already was initialized
			        if ( linkThemeCSS && linkThemeCSS instanceof HTMLLinkElement ) {
			            // remove all current CSS styles
			            document.head.removeChild(linkThemeCSS);
			        }
			
			        linkThemeCSS = document.createElement('link');
			        linkThemeCSS.rel  = 'stylesheet';
			        linkThemeCSS.href = window.core.theme.path + metrics.height + '.css?' + +new Date();
			        document.head.appendChild(linkThemeCSS);
			
			        // load CSS file base on resolution
			        linkCSS = document.createElement('link');
			        linkCSS.rel  = 'stylesheet';
			        linkCSS.href = 'css/' + ( true ? 'develop.' : 'release.') + metrics.height + '.css?' + +new Date();
			        document.head.appendChild(linkCSS);
			
			        // provide global access
			        this.data.metrics = metrics;
			
			        return true;
			    }
			
			    // nothing has applied
			    return false;
			};
			
			// define events constants
			
			/**
			 * The player reached the end of the media content or detected a discontinuity of the stream
			 *
			 * @const {number} EVENT_END_OF_FILE
			 * @default 1
			 */
			app.EVENT_END_OF_FILE = 1;
			
			/**
			 * Information on audio and video tracks of the media content is received. It's now possible to call gSTB.GetAudioPIDs etc.
			 *
			 * @const {number} EVENT_GET_MEDIA_INFO
			 * @default 2
			 */
			app.EVENT_GET_MEDIA_INFO = 2;
			
			/**
			 * Video and/or audio playback has begun
			 *
			 * @const {number} EVENT_PLAYBACK_BEGIN
			 * @default 4
			 */
			app.EVENT_PLAYBACK_BEGIN = 4;
			
			/**
			 * Error when opening the content: content not found on the server or connection with the server was rejected
			 *
			 * @const {number} EVENT_CONTENT_ERROR
			 * @default 5
			 */
			app.EVENT_CONTENT_ERROR = 5;
			
			/**
			 * Detected DualMono AC-3 sound
			 *
			 * @const {number} EVENT_DUAL_MONO_DETECT
			 * @default 6
			 */
			app.EVENT_DUAL_MONO_DETECT = 6;
			
			/**
			 * The decoder has received info about the content and started to play. It's now possible to call gSTB.GetVideoInfo
			 *
			 * @const {number} EVENT_INFO_GET
			 * @default 7
			 */
			app.EVENT_INFO_GET = 7;
			
			/**
			 * Error occurred while loading external subtitles
			 *
			 * @const {number} EVENT_SUBTITLE_LOAD_ERROR
			 * @default 8
			 */
			app.EVENT_SUBTITLE_LOAD_ERROR = 8;
			
			/**
			 * Found new teletext subtitles in stream
			 *
			 * @const {number} EVENT_SUBTITLE_FIND
			 * @default 9
			 */
			app.EVENT_SUBTITLE_FIND = 9;
			
			/**
			 * HDMI device has been connected
			 *
			 * @const {number} EVENT_HDMI_CONNECT
			 * @default 32
			 */
			app.EVENT_HDMI_CONNECT = 32;
			
			/**
			 * HDMI device has been disconnected
			 *
			 * @const {number} EVENT_HDMI_DISCONNECT
			 * @default 33
			 */
			app.EVENT_HDMI_DISCONNECT = 33;
			
			/**
			 * Recording task has been finished successfully. See Appendix 13. JavaScript API for PVR subsystem
			 *
			 * @const {number} EVENT_RECORD_FINISH_SUCCESSFULL
			 * @default 34
			 */
			app.EVENT_RECORD_FINISH_SUCCESSFULL = 34;
			
			/**
			 * Recording task has been finished with error. See Appendix 13. JavaScript API for PVR subsystem
			 *
			 * @const {number} EVENT_RECORD_FINISH_ERROR
			 * @default 35
			 */
			app.EVENT_RECORD_FINISH_ERROR = 35;
			
			/**
			 * Scanning DVB Channel in progress
			 *
			 * @const {number} EVENT_DVB_SCANING
			 * @default 40
			 */
			app.EVENT_DVB_SCANING = 40;
			
			/**
			 * Scanning DVB Channel found
			 *
			 * @const {number} EVENT_DVB_FOUND
			 * @default 41
			 */
			app.EVENT_DVB_FOUND = 41;
			
			/**
			 * DVB Channel EPG update
			 *
			 * @const {number} EVENT_DVB_CHANELL_EPG_UPDATE
			 * @default 42
			 */
			app.EVENT_DVB_CHANELL_EPG_UPDATE = 42;
			
			/**
			 * DVB antenna power off
			 *
			 * @const {number} EVENT_DVB_ANTENNA_OFF
			 * @default 43
			 */
			app.EVENT_DVB_ANTENNA_OFF = 43;
			
			
			// apply screen size, position and margins
			app.setScreen(metrics[screen.height] || metrics[720]);
			
			
			// extract key codes
			for ( key in keys ) {
			    if ( key === 'volumeUp' || key === 'volumeDown' ) {
			        continue;
			    }
			    // no need to save key names
			    keyCodes[keys[key]] = true;
			}
			
			
			app.defaultEvents = {
			    /**
			     * The load event is fired when a resource and its dependent resources have finished loading.
			     *
			     * Control flow:
			     *   1. Global handler.
			     *   2. Each page handler.
			     *   3. Application DONE event.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/load
			     *
			     * @param {Event} event generated object with event data
			     */
			    load: function ( event ) {
			        debug.event(event);
			        debug.log('app loaded', 'green');
			
			        // time mark
			        app.data.time.load = event.timeStamp;
			        
			        if ( window.core.ready ) {
			            // global handler
			            // there are some listeners
			            if ( app.events[event.type] ) {
			                // notify listeners
			                app.emit(event.type, event);
			            }
			            // local handler on each page
			            router.pages.forEach(function forEachPages ( page ) {
			                debug.log('component ' + page.constructor.name + '.' + page.id + ' load', 'green');
			
			                // there are some listeners
			                if ( page.events[event.type] ) {
			                    // notify listeners
			                    page.emit(event.type, event);
			                }
			            });
			
			            // time mark
			            app.data.time.done = +new Date();
			            // everything is ready
			            // and there are some listeners
			            if ( app.events['done'] ) {
			                // notify listeners
			                app.emit('done', event);
			            }
			        }
			    },
			
			    /**
			     * The unload event is fired when the document or a child resource is being unloaded.
			     *
			     * Control flow:
			     *   1. Each page handler.
			     *   2. Global handler.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/unload
			     *
			     * @param {Event} event generated object with event data
			     */
			    unload: function ( event ) {
			        debug.event(event);
			
			        // global handler
			        // there are some listeners
			        if ( app.events[event.type] ) {
			            // notify listeners
			            app.emit(event.type, event);
			        }
			
			        // local handler on each page
			        router.pages.forEach(function forEachPages ( page ) {
			            debug.log('component ' + page.constructor.name + '.' + page.id + ' unload', 'red');
			
			            // there are some listeners
			            if ( page.events[event.type] ) {
			                // notify listeners
			                page.emit(event.type, event);
			            }
			        });
			    },
			
			    /**
			     * The error event is fired when a resource failed to load.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/error
			     *
			     * @param {Event} event generated object with event data
			     */
			    error: function ( event ) {
			        debug.event(event);
			    },
			
			    /**
			     * The keydown event is fired when a key is pressed down.
			     * Set event.stop to true in order to prevent bubbling.
			     *
			     * Control flow:
			     *   1. Current active component on the active page.
			     *   2. Current active page itself.
			     *   3. Application.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/keydown
			     *
			     * @param {Event} event generated object with event data
			     */
			    keydown: function ( event ) {
			        var page = router.current,
			            activeComponent,
			            eventData = { // native event won't work in desktop browser because of operations with code field
			                keyCode: event.keyCode,
			                stop: event.stop,
			                shiftKey: event.shiftKey,
			                altKey: event.altKey,
			                type: event.type,
			                native: event
			            };
			
			        if ( true ) {
			            if ( !page ) { throw new Error(__filename + ': app should have at least one page'); }
			        }
			
			        // filter phantoms
			        if ( eventData.keyCode === 0 ) { return; }
			
			        // combined key code
			        eventData.code = eventData.keyCode;
			
			        // apply key modifiers
			        if ( eventData.shiftKey ) { eventData.code += 1000; }
			        if ( eventData.altKey )   { eventData.code += 2000; }
			
			        debug.event(eventData);
			
			        // page.activeComponent can be set to null in event handles
			        activeComponent = page.activeComponent;
			
			        // current component handler
			        if ( activeComponent && activeComponent !== page ) {
			            // component is available and not page itself
			            if ( activeComponent.events[eventData.type] ) {
			                // there are some listeners
			                activeComponent.emit(eventData.type, eventData);
			            }
			
			            // bubbling
			            if (
			                !eventData.stop &&
			                activeComponent.propagate &&
			                activeComponent.parent &&
			                activeComponent.parent.events[eventData.type]
			            ) {
			                activeComponent.parent.emit(eventData.type, eventData);
			            }
			        }
			
			        // page handler
			        if ( !eventData.stop ) {
			            // not prevented
			            if ( page.events[eventData.type] ) {
			                // there are some listeners
			                page.emit(eventData.type, eventData);
			            }
			
			            // global app handler
			            if ( !eventData.stop ) {
			                // not prevented
			                if ( app.events[eventData.type] ) {
			                    // there are some listeners
			                    app.emit(eventData.type, eventData);
			                }
			            }
			        }
			
			        // suppress non-printable keys in stb device (not in your browser)
			        if ( app.data.host && keyCodes[eventData.code] ) {
			            event.preventDefault();
			        }
			    },
			
			    /**
			     * The keypress event is fired when press a printable character.
			     * Delivers the event only to activeComponent at active page.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/keypress
			     *
			     * @param {Event} event generated object with event data
			     * @param {string} event.char entered character
			     */
			    keypress: function ( event ) {
			        var page = router.current;
			
			        if ( true ) {
			            if ( page === null || page === undefined ) { throw new Error(__filename + ': app should have at least one page'); }
			        }
			
			        //debug.event(event);
			
			        // current component handler
			        if ( page.activeComponent && page.activeComponent !== page ) {
			            // component is available and not page itself
			            if ( page.activeComponent.events[event.type] ) {
			                // there are some listeners
			                page.activeComponent.emit(event.type, event);
			            }
			        }
			    },
			
			    /**
			     * The click event is fired when a pointing device button (usually a mouse button) is pressed and released on a single element.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/click
			     *
			     * @param {Event} event generated object with event data
			     */
			    click: function ( event ) {
			        debug.event(event);
			    },
			
			    /**
			     * The contextmenu event is fired when the right button of the mouse is clicked (before the context menu is displayed),
			     * or when the context menu key is pressed (in which case the context menu is displayed at the bottom left of the focused
			     * element, unless the element is a tree, in which case the context menu is displayed at the bottom left of the current row).
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/contextmenu
			     *
			     * @param {Event} event generated object with event data
			     */
			    contextmenu: function ( event ) {
			        //var kbEvent = {}; //Object.create(document.createEvent('KeyboardEvent'));
			
			        debug.event(event);
			
			        //kbEvent.type    = 'keydown';
			        //kbEvent.keyCode = 8;
			
			        //debug.log(kbEvent.type);
			
			        //globalEventListenerKeydown(kbEvent);
			        //var event = document.createEvent('KeyboardEvent');
			        //event.initEvent('keydown', true, true);
			
			        //document.dispatchEvent(kbEvent);
			
			        if ( false ) {
			            // disable right click in release mode
			            event.preventDefault();
			        }
			    },
			
			    /**
			     * The wheel event is fired when a wheel button of a pointing device (usually a mouse) is rotated.
			     *
			     * @see https://developer.mozilla.org/en-US/docs/Web/Reference/Events/wheel
			     *
			     * @param {Event} event generated object with event data
			     */
			    mousewheel: function ( event ) {
			        var page = router.current;
			
			        if ( true ) {
			            if ( page === null || page === undefined ) { throw new Error(__filename + ': app should have at least one page'); }
			        }
			
			        debug.event(event);
			
			        // current component handler
			        if ( page.activeComponent && page.activeComponent !== page ) {
			            // component is available and not page itself
			            if ( page.activeComponent.events[event.type] ) {
			                // there are some listeners
			                page.activeComponent.emit(event.type, event);
			            }
			        }
			
			        // page handler
			        if ( !event.stop ) {
			            // not prevented
			            if ( page.events[event.type] ) {
			                // there are some listeners
			                page.emit(event.type, event);
			            }
			        }
			    }
			};
			
			
			// apply events
			for ( key in app.defaultEvents ) {
			    window.addEventListener(key, app.defaultEvents[key]);
			}
			
			
			/**
			 * Show app.
			 */
			app.show = function () {
			    if ( this.events['show'] ) {
			        this.emit('show');
			    }
			
			    window.core.call('app:ready');
			};
			
			
			/**
			 * Exit from app.
			 * Destroy all application instance.
			 * If callback function provided, and callback returns boolean 'true', application will stay alive.
			 *
			 * @fires module:/stb/app#exit
			 *
			 * @param [callback] provide callback if u want to handle exit result, or cancel it
			 */
			app.exit = function ( callback ) {
			    var ModalMessage = __webpack_require__(/*! ./ui/modal.message */ 12),
			        LayoutList   = __webpack_require__(/*! ./ui/layout.list */ 11),
			        previousFocus = router.current.activeComponent,
			        exitModal;
			
			    router.current.add(exitModal = new ModalMessage({
			        title: gettext('Exit'),
			        events:{
			            show: function () {
			                this.children[0].focus();
			            },
			            hide: function () {
			                previousFocus.focus();
			            }
			        },
			        children:[
			            new LayoutList({
			                size:2,
			                focusIndex:0,
			                data:[
			                    {
			                        items: [
			                            {
			                                value: gettext('Exit')
			                            }
			                        ],
			                        click: function () {
			                            if ( typeof callback === 'function' ) {
			                                if ( callback(true) ) {
			                                    exitModal.hide();
			                                    exitModal.remove();
			                                    return;
			                                }
			                            }
			                            if ( app.events['exit'] ) {
			                                app.emit('exit');
			                            }
			
			                            exitModal.hide();
			                            exitModal.remove();
			                            core.call('exit');
			                        }
			                    },
			                    {
			                        items: [
			                            {
			                                value: gettext('Cancel')
			                            }
			                        ],
			                        click: function () {
			                            if ( typeof callback === 'function' ) {
			                                callback(false);
			                            }
			                            exitModal.hide();
			                            exitModal.remove();
			                        }
			                    }
			                ],
			                events: {
			                    keydown: function ( event ) {
			                        LayoutList.prototype.defaultEvents.keydown.call(this, event);
			                        if ( event.code === keys.back ) {
			                            event.stop = true;
			                            if ( typeof callback === 'function' ) {
			                                callback(false);
			                            }
			                            exitModal.hide();
			                            exitModal.remove();
			                        }
			                    }
			                }
			            })
			        ]
			    }));
			
			    exitModal.show();
			    exitModal.focus();
			};
			
			
			// Creating stbEvent instance
			window.stbEvent = {};
			
			
			/**
			 * Device media events.
			 *
			 * @event module:stb/app#media
			 * @type object
			 * @property {number} code of event
			 */
			
			
			/**
			 * Event on messages from a window.
			 *
			 * @event module:stb/app#message
			 * @type object
			 * @property {boolean} broadcast message flag
			 * @property {string} message received from window
			 * @property {object} data received from window
			 */
			
			
			/**
			 * Fires stb device media events.
			 *
			 * @param {number} event code
			 * @param {string} info associated data in **JSON** format
			 */
			window.stbEvent.onEvent = function ( event, info ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onEvent ) {
			            // proxy call
			            frame.stbEvent.onEvent(event, info);
			        }
			    });
			
			    // there are some listeners
			    if ( app.events['media'] ) {
			        // additional data
			        if ( info ) {
			            try {
			                info = JSON.parse(info);
			            } catch ( e ) {
			                debug.log(e);
			            }
			        }
			
			        // notify listeners
			        app.emit('media', {code: parseInt(event, 10), info: info});
			    }
			};
			
			
			/**
			 * Fires event on broadcast messages from a window.
			 *
			 * @param {number} windowId that sent message
			 * @param {string} message text
			 * @param {object} data in sent message
			 * @fires module:/stb/app#message
			 */
			window.stbEvent.onBroadcastMessage = function ( windowId, message, data ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onBroadcastMessage ) {
			            // proxy call
			            frame.stbEvent.onBroadcastMessage(windowId, message, data);
			        }
			    });
			
			    if ( app.events['message'] ) {
			        // notify listeners
			        app.emit('message', {
			            broadcast: true,
			            windowId: windowId,
			            message: message,
			            data: data
			        });
			    }
			};
			
			
			/**
			 * Fires event on messages from a window.
			 *
			 * @param {number} windowId that sent message
			 * @param {string} message text
			 * @param {object} data in sent message
			 * @fires module:/stb/app#message
			 */
			window.stbEvent.onMessage = function ( windowId, message, data ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onMessage ) {
			            // proxy call
			            frame.stbEvent.onMessage(windowId, message, data);
			        }
			    });
			
			    if ( app.events['message'] ) {
			        // notify listeners
			        app.emit('message', {
			            broadcast: false,
			            windowId: windowId,
			            message: message,
			            data: data
			        });
			    }
			};
			
			
			/**
			 * Event on device mount state.
			 *
			 * @event module:stb/app#mount
			 * @type object
			 * @property {boolean} state of mount device
			 */
			
			
			/**
			 * Fires device mount state event.
			 *
			 * @param {boolean} state of mount device
			 * @fires module:/stb/app#mount
			 */
			window.stbEvent.onMount = function ( state ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onMount ) {
			            // proxy call
			            frame.stbEvent.onMount(state);
			        }
			    });
			
			    if ( app.events['device:mount'] ) {
			        // notify listeners
			        app.emit('device:mount', {state: state});
			    }
			};
			
			
			/**
			 * Event on callback on internet browser link clicked.
			 *
			 * @event module:stb/app#media:available
			 */
			
			
			/**
			 * Fires event of callback on internet browser link clicked to ask user what to do with link: play or download.
			 *
			 * @param {string} mime file type
			 * @param {string} url resource link
			 *
			 * @fires module:/stb/app#media:available
			 */
			window.stbEvent.onMediaAvailable = function ( mime, url ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onMediaAvailable ) {
			            // proxy call
			            frame.stbEvent.onMediaAvailable(mime, url);
			        }
			    });
			
			    if ( app.events['media:available'] ) {
			        // notify listeners
			        app.emit('media:available', {mime: mime, url: url});
			    }
			};
			
			
			/**
			 * Event on internet connection state.
			 *
			 * @event module:stb/app#internet:state
			 * @type object
			 * @property {boolean} state of internet connection
			 */
			
			
			/**
			 * Fires new internet connection state event.
			 *
			 * @param {boolean} state of internet connection
			 * @fires module:/stb/app#internet:state
			 */
			window.stbEvent.onNetworkStateChange = function ( state ) {
			    if ( app.events['internet:state'] ) {
			        // notify listeners
			        app.emit('internet:state', {state: state});
			    }
			};
			
			
			/**
			 * Event on document loading progress changes.
			 *
			 * @event module:stb/app#browser:progress
			 * @type object
			 * @property {number} progress of document loading
			 */
			
			
			/**
			 * Fires document loading progress changes event.
			 *
			 * @param {number} progress of document loading
			 * fires module:/stb/app#browser:progress
			 */
			window.stbEvent.onWebBrowserProgress = function ( progress ) {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onWebBrowserProgress ) {
			            // proxy call
			            frame.stbEvent.onWebBrowserProgress(progress);
			        }
			    });
			
			    if ( app.events['browser:progress'] ) {
			        // notify listeners
			        app.emit('browser:progress', {progress: progress});
			    }
			};
			
			
			/**
			 * Event on browser web window activation event.
			 *
			 * @event module:stb/app#window:focus
			 */
			
			
			/**
			 * Fires browser web window activation event.
			 *
			 * fires module:/stb/app#window:focus
			 */
			window.stbEvent.onWindowActivated = function () {
			    // proxy to all frames
			    Array.prototype.forEach.call(window.frames, function ( frame ) {
			        // necessary global object is present
			        if ( frame.stbEvent && frame.stbEvent.onWindowActivated ) {
			            // proxy call
			            frame.stbEvent.onWindowActivated();
			        }
			    });
			
			    if ( app.events['window:focus'] ) {
			        // notify listeners
			        app.emit('window:focus');
			    }
			};
			
			
			// new way of string handling
			// all strings are in UTF-16
			// since stbapp 2.18
			if ( window.gSTB && gSTB.SetNativeStringMode ) {
			    /* eslint new-cap: 0 */
			
			    gSTB.SetNativeStringMode(true);
			}
			
			
			// public
			module.exports = app;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/app.js"))

/***/ },
/* 4 */
/*!***************************!*\
  !*** ./app/js/stb/dom.js ***!
  \***************************/
/***/ function(module, exports) {

			/**
			 * HTML elements low-level handling.
			 *
			 * @module stb/dom
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint no-unused-vars: 0 */
			
			/**
			 * DOM manipulation module
			 */
			var dom = {};
			
			
			/**
			 * Create a new HTML element.
			 *
			 * @param {string} tagName mandatory tag name
			 * @param {Object|null} [attrList] element attributes
			 * @param {...*} [content] element content (primitive value/values or other nodes)
			 * @return {Node|null} HTML element or null on failure
			 *
			 * @example
			 * dom.tag('table');
			 * dom.tag('div', {}, 'some text');
			 * dom.tag('div', {className:'top'}, dom.tag('span'), dom.tag('br'));
			 * dom.tag('link', {rel:'stylesheet', type:'text/css', href:'http://some.url/'});
			 */
			dom.tag = function ( tagName, attrList, content ) {
			    var node = null,
			        i, name;
			
			    // minimal param is given
			    if ( tagName ) {
			        // empty element
			        node = document.createElement(tagName);
			
			        // optional attribute list is given
			        if ( attrList && typeof attrList === 'object' ) {
			            for ( name in attrList ) {
			                // extend a new node with the given attributes
			                node[name] = attrList[name];
			            }
			        }
			
			        // content (arguments except the first two)
			        for ( i = 2; i < arguments.length; i++ ) {
			            // some data is given
			            if ( arguments[i] ) {
			                // regular HTML tag or plain data
			                node.appendChild(
			                    typeof arguments[i] === 'object' ?
			                    arguments[i] :
			                    document.createTextNode(arguments[i])
			                );
			            }
			        }
			
			    }
			
			    return node;
			};
			
			
			/**
			 * Create a new DocumentFragment filled with the given non-empty elements if any.
			 *
			 * @param {...*} [node] fragment content (primitive value/values or other nodes)
			 * @return {DocumentFragment} new placeholder
			 *
			 * @example
			 * // gives an empty fragment element
			 * dom.fragment();
			 * // gives a fragment element with 3 div element inside
			 * dom.fragment(dom.tag('div'), div2, div3);
			 * // mixed case
			 * dom.fragment('some text', 123, div3);
			 */
			dom.fragment = function ( node ) {
			    // prepare placeholder
			    var i, fragment = document.createDocumentFragment();
			
			    // walk through all the given elements
			    for ( i = 0; i < arguments.length; i++ ) {
			        node = arguments[i];
			        // some data is given
			        if ( node ) {
			            // regular HTML tag or plain data
			            fragment.appendChild(typeof node === 'object' ? node : document.createTextNode(node));
			        }
			    }
			
			    return fragment;
			};
			
			
			/**
			 * Add the given non-empty data (HTML element/text or list) to the destination element.
			 *
			 * @param {Node} tagDst element to receive children
			 * @param {...*} [content] element content (primitive value/values or other nodes)
			 * @return {Node|null} the destination element - owner of all added data
			 *
			 * @example
			 * // simple text value
			 * add(some_div, 'Hello world');
			 * // single DOM Element
			 * add(some_div, some_other_div);
			 * // DOM Element list
			 * add(some_div, div1, div2, div3);
			 * // mixed case
			 * add(some_div, div1, 'hello', 'world');
			 */
			dom.add = function ( tagDst, content ) {
			    var i;
			
			    // valid HTML tag as the destination
			    if ( tagDst instanceof Node ) {
			        // append all except the first one
			        for ( i = 1; i < arguments.length; i++ ) {
			            // some data is given
			            if ( arguments[i] ) {
			                // regular HTML tag or plain data
			                tagDst.appendChild(
			                    typeof arguments[i] === 'object' ?
			                    arguments[i] :
			                    document.createTextNode(arguments[i])
			                );
			            }
			        }
			        return tagDst;
			    }
			
			    return null;
			};
			
			
			/**
			 * Remove the given elements from the DOM.
			 *
			 * @param {...Node} [nodes] element to be removed
			 * @return {boolean} operation status (true - all given elements removed)
			 *
			 * @example
			 * dom.remove(document.querySelector('div.test'));
			 * dom.remove(div1, div2, div3);
			 */
			dom.remove = function ( nodes ) {
			    var count = 0,  // amount of successfully removed nodes
			        i;
			
			    // walk through all the given elements
			    for ( i = 0; i < arguments.length; i++ ) {
			        // valid non-empty tag
			        if ( arguments[i] && arguments[i].parentNode ) {
			            if ( arguments[i].parentNode.removeChild(arguments[i]) === arguments[i] ) {
			                count++;
			            }
			        }
			    }
			
			    return arguments.length > 0 && count === arguments.length;
			};
			
			
			// public
			module.exports = dom;


/***/ },
/* 5 */
/*!*******************************!*\
  !*** ./app/js/stb/emitter.js ***!
  \*******************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/emitter
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			
			/**
			 * Base Events Emitter implementation.
			 *
			 * @see http://nodejs.org/api/events.html
			 * @constructor
			 */
			function Emitter () {
			    if ( true ) {
			        if ( typeof this !== 'object' ) { throw new Error(__filename + ': must be constructed via new'); }
			    }
			
			    /**
			     * Inner hash table for event names and linked callbacks.
			     * Manual editing should be avoided.
			     *
			     * @member {Object.<string, function[]>}
			     *
			     * @example
			     * {
			     *     click: [
			     *         function click1 () { ... },
			     *         function click2 () { ... }
			     *     ],
			     *     keydown: [
			     *         function () { ... }
			     *     ]
			     * }
			     **/
			    this.events = {};
			}
			
			
			Emitter.prototype = {
			    /**
			     * Bind an event to the given callback function.
			     * The same callback function can be added multiple times for the same event name.
			     *
			     * @param {string} name event identifier
			     * @param {function} callback function to call on this event
			     *
			     * @example
			     * var obj = new Emitter();
			     * obj.addListener('click', function ( data ) { ... });
			     * // one more click handler
			     * obj.addListener('click', function ( data ) { ... });
			     */
			    addListener: function ( name, callback ) {
			        if ( true ) {
			            if ( arguments.length !== 2 ) { throw new Error(__filename + ': wrong arguments number'); }
			            if ( typeof name !== 'string' || name.length === 0 ) { throw new Error(__filename + ': wrong or empty name'); }
			            if ( typeof callback !== 'function' ) { throw new Error(__filename + ': wrong callback type'); }
			        }
			
			        // initialization may be required
			        this.events[name] = this.events[name] || [];
			        // append this new event to the list
			        this.events[name].push(callback);
			    },
			
			
			    /**
			     * Add a one time listener for the event.
			     * This listener is invoked only the next time the event is fired, after which it is removed.
			     *
			     * @param {string} name event identifier
			     * @param {function} callback function to call on this event
			     */
			    once: function ( name, callback ) {
			        // current execution context
			        var self = this;
			
			        if ( true ) {
			            if ( arguments.length !== 2 ) { throw new Error(__filename + ': wrong arguments number'); }
			            if ( typeof name !== 'string' || name.length === 0 ) { throw new Error(__filename + ': wrong or empty name'); }
			            if ( typeof callback !== 'function' ) { throw new Error(__filename + ': wrong callback type'); }
			        }
			
			        // initialization may be required
			        this.events[name] = this.events[name] || [];
			        // append this new event to the list
			        this.events[name].push(function onceWrapper (/*data*/) {
			            //callback(data);
			            callback.apply(this, arguments);
			            self.removeListener(name, onceWrapper);
			        });
			    },
			
			
			    /**
			     * Apply multiple listeners at once.
			     *
			     * @param {Object} callbacks event names with callbacks
			     *
			     * @example
			     * var obj = new Emitter();
			     * obj.addListeners({click: function ( data ) {}, close: function ( data ) {}});
			     */
			    addListeners: function ( callbacks ) {
			        var name;
			
			        if ( true ) {
			            if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			            if ( typeof callbacks !== 'object' ) { throw new Error(__filename + ': wrong callbacks type'); }
			            if ( Object.keys(callbacks).length === 0 ) { throw new Error(__filename + ': no callbacks given'); }
			        }
			
			        // valid input
			        if ( typeof callbacks === 'object' ) {
			            for ( name in callbacks ) {
			                if ( callbacks.hasOwnProperty(name) ) {
			                    this.addListener(name, callbacks[name]);
			                }
			            }
			        }
			    },
			
			
			    /**
			     * Remove all instances of the given callback.
			     *
			     * @param {string} name event identifier
			     * @param {function} callback function to remove
			     *
			     * @example
			     * obj.removeListener('click', func1);
			     */
			    removeListener: function ( name, callback ) {
			        if ( true ) {
			            if ( arguments.length !== 2 ) { throw new Error(__filename + ': wrong arguments number'); }
			            if ( typeof name !== 'string' || name.length === 0 ) { throw new Error(__filename + ': wrong or empty name'); }
			            if ( typeof callback !== 'function' ) { throw new Error(__filename + ': wrong callback type'); }
			            if ( this.events[name] && !Array.isArray(this.events[name]) ) { throw new Error(__filename + ': corrupted inner data'); }
			        }
			
			        // the event exists and should have some callbacks
			        if ( this.events[name] ) {
			            // rework the callback list to exclude the given one
			            this.events[name] = this.events[name].filter(function callbacksFilter ( fn ) { return fn !== callback; });
			            // event has no more callbacks so clean it
			            if ( this.events[name].length === 0 ) {
			                // as if there were no listeners at all
			                this.events[name] = undefined;
			            }
			        }
			    },
			
			
			    /**
			     * Remove all callbacks for the given event name.
			     * Without event name clears all events.
			     *
			     * @param {string} [name] event identifier
			     *
			     * @example
			     * obj.removeAllListeners('click');
			     * obj.removeAllListeners();
			     */
			    removeAllListeners: function ( name ) {
			        if ( true ) {
			            if ( arguments.length !== 0 && (typeof name !== 'string' || name.length === 0) ) { throw new Error(__filename + ': wrong or empty name'); }
			        }
			
			        // check input
			        if ( arguments.length === 0 ) {
			            // no arguments so remove everything
			            this.events = {};
			        } else if ( name ) {
			            if ( true ) {
			                if ( this.events[name] ) { throw new Error(__filename + ': event is not removed'); }
			            }
			
			            // only name is given so remove all callbacks for the given event
			            // but object structure modification should be avoided
			            this.events[name] = undefined;
			        }
			    },
			
			
			    /**
			     * Execute each of the listeners in the given order with the supplied arguments.
			     *
			     * @param {string} name event identifier
			     * @param {Object} [data] options to send
			     * @param {Function} [callback] callback to send
			     *
			     *
			     * @example
			     * obj.emit('init');
			     * obj.emit('click', {src:panel1, dst:panel2});
			     *
			     * // it's a good idea to emit event only when there are some listeners
			     * if ( this.events['click'] ) {
			     *     this.emit('click', {event: event});
			     * }
			     */
			    emit: function ( name, data, callback ) {
			        var event = this.events[name],
			            i;
			
			        if ( true ) {
			            if ( arguments.length < 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			            if ( typeof name !== 'string' || name.length === 0 ) { throw new Error(__filename + ': wrong or empty name'); }
			        }
			
			        // the event exists and should have some callbacks
			        if ( event ) {
			            if ( true ) {
			                if ( !Array.isArray(event) ) { throw new Error(__filename + ': wrong event type'); }
			            }
			
			            for ( i = 0; i < event.length; i++ ) {
			                if ( true ) {
			                    if ( typeof event[i] !== 'function' ) { throw new Error(__filename + ': wrong event callback type'); }
			                }
			
			                // invoke the callback with parameters
			                // http://jsperf.com/function-calls-direct-vs-apply-vs-call-vs-bind/6
			                //event[i].call(this, data);
			                event[i].apply(this, Array.prototype.slice.call(arguments, 1));
			            }
			        }
			    }
			};
			
			// correct constructor name
			Emitter.prototype.constructor = Emitter;
			
			
			// public
			module.exports = Emitter;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/emitter.js"))

/***/ },
/* 6 */
/*!************************!*\
  !*** ./~/util/util.js ***!
  \************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(process) {// Copyright Joyent, Inc. and other Node contributors.
			//
			// Permission is hereby granted, free of charge, to any person obtaining a
			// copy of this software and associated documentation files (the
			// "Software"), to deal in the Software without restriction, including
			// without limitation the rights to use, copy, modify, merge, publish,
			// distribute, sublicense, and/or sell copies of the Software, and to permit
			// persons to whom the Software is furnished to do so, subject to the
			// following conditions:
			//
			// The above copyright notice and this permission notice shall be included
			// in all copies or substantial portions of the Software.
			//
			// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
			// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
			// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
			// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
			// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
			// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
			// USE OR OTHER DEALINGS IN THE SOFTWARE.
			
			var formatRegExp = /%[sdj%]/g;
			exports.format = function(f) {
			  if (!isString(f)) {
			    var objects = [];
			    for (var i = 0; i < arguments.length; i++) {
			      objects.push(inspect(arguments[i]));
			    }
			    return objects.join(' ');
			  }
			
			  var i = 1;
			  var args = arguments;
			  var len = args.length;
			  var str = String(f).replace(formatRegExp, function(x) {
			    if (x === '%%') return '%';
			    if (i >= len) return x;
			    switch (x) {
			      case '%s': return String(args[i++]);
			      case '%d': return Number(args[i++]);
			      case '%j':
			        try {
			          return JSON.stringify(args[i++]);
			        } catch (_) {
			          return '[Circular]';
			        }
			      default:
			        return x;
			    }
			  });
			  for (var x = args[i]; i < len; x = args[++i]) {
			    if (isNull(x) || !isObject(x)) {
			      str += ' ' + x;
			    } else {
			      str += ' ' + inspect(x);
			    }
			  }
			  return str;
			};
			
			
			// Mark that a method should not be used.
			// Returns a modified function which warns once by default.
			// If --no-deprecation is set, then it is a no-op.
			exports.deprecate = function(fn, msg) {
			  // Allow for deprecating things in the process of starting up.
			  if (isUndefined(global.process)) {
			    return function() {
			      return exports.deprecate(fn, msg).apply(this, arguments);
			    };
			  }
			
			  if (process.noDeprecation === true) {
			    return fn;
			  }
			
			  var warned = false;
			  function deprecated() {
			    if (!warned) {
			      if (process.throwDeprecation) {
			        throw new Error(msg);
			      } else if (process.traceDeprecation) {
			        console.trace(msg);
			      } else {
			        console.error(msg);
			      }
			      warned = true;
			    }
			    return fn.apply(this, arguments);
			  }
			
			  return deprecated;
			};
			
			
			var debugs = {};
			var debugEnviron;
			exports.debuglog = function(set) {
			  if (isUndefined(debugEnviron))
			    debugEnviron = process.env.NODE_DEBUG || '';
			  set = set.toUpperCase();
			  if (!debugs[set]) {
			    if (new RegExp('\\b' + set + '\\b', 'i').test(debugEnviron)) {
			      var pid = process.pid;
			      debugs[set] = function() {
			        var msg = exports.format.apply(exports, arguments);
			        console.error('%s %d: %s', set, pid, msg);
			      };
			    } else {
			      debugs[set] = function() {};
			    }
			  }
			  return debugs[set];
			};
			
			
			/**
			 * Echos the value of a value. Trys to print the value out
			 * in the best way possible given the different types.
			 *
			 * @param {Object} obj The object to print out.
			 * @param {Object} opts Optional options object that alters the output.
			 */
			/* legacy: obj, showHidden, depth, colors*/
			function inspect(obj, opts) {
			  // default options
			  var ctx = {
			    seen: [],
			    stylize: stylizeNoColor
			  };
			  // legacy...
			  if (arguments.length >= 3) ctx.depth = arguments[2];
			  if (arguments.length >= 4) ctx.colors = arguments[3];
			  if (isBoolean(opts)) {
			    // legacy...
			    ctx.showHidden = opts;
			  } else if (opts) {
			    // got an "options" object
			    exports._extend(ctx, opts);
			  }
			  // set default options
			  if (isUndefined(ctx.showHidden)) ctx.showHidden = false;
			  if (isUndefined(ctx.depth)) ctx.depth = 2;
			  if (isUndefined(ctx.colors)) ctx.colors = false;
			  if (isUndefined(ctx.customInspect)) ctx.customInspect = true;
			  if (ctx.colors) ctx.stylize = stylizeWithColor;
			  return formatValue(ctx, obj, ctx.depth);
			}
			exports.inspect = inspect;
			
			
			// http://en.wikipedia.org/wiki/ANSI_escape_code#graphics
			inspect.colors = {
			  'bold' : [1, 22],
			  'italic' : [3, 23],
			  'underline' : [4, 24],
			  'inverse' : [7, 27],
			  'white' : [37, 39],
			  'grey' : [90, 39],
			  'black' : [30, 39],
			  'blue' : [34, 39],
			  'cyan' : [36, 39],
			  'green' : [32, 39],
			  'magenta' : [35, 39],
			  'red' : [31, 39],
			  'yellow' : [33, 39]
			};
			
			// Don't use 'blue' not visible on cmd.exe
			inspect.styles = {
			  'special': 'cyan',
			  'number': 'yellow',
			  'boolean': 'yellow',
			  'undefined': 'grey',
			  'null': 'bold',
			  'string': 'green',
			  'date': 'magenta',
			  // "name": intentionally not styling
			  'regexp': 'red'
			};
			
			
			function stylizeWithColor(str, styleType) {
			  var style = inspect.styles[styleType];
			
			  if (style) {
			    return '\u001b[' + inspect.colors[style][0] + 'm' + str +
			           '\u001b[' + inspect.colors[style][1] + 'm';
			  } else {
			    return str;
			  }
			}
			
			
			function stylizeNoColor(str, styleType) {
			  return str;
			}
			
			
			function arrayToHash(array) {
			  var hash = {};
			
			  array.forEach(function(val, idx) {
			    hash[val] = true;
			  });
			
			  return hash;
			}
			
			
			function formatValue(ctx, value, recurseTimes) {
			  // Provide a hook for user-specified inspect functions.
			  // Check that value is an object with an inspect function on it
			  if (ctx.customInspect &&
			      value &&
			      isFunction(value.inspect) &&
			      // Filter out the util module, it's inspect function is special
			      value.inspect !== exports.inspect &&
			      // Also filter out any prototype objects using the circular check.
			      !(value.constructor && value.constructor.prototype === value)) {
			    var ret = value.inspect(recurseTimes, ctx);
			    if (!isString(ret)) {
			      ret = formatValue(ctx, ret, recurseTimes);
			    }
			    return ret;
			  }
			
			  // Primitive types cannot have properties
			  var primitive = formatPrimitive(ctx, value);
			  if (primitive) {
			    return primitive;
			  }
			
			  // Look up the keys of the object.
			  var keys = Object.keys(value);
			  var visibleKeys = arrayToHash(keys);
			
			  if (ctx.showHidden) {
			    keys = Object.getOwnPropertyNames(value);
			  }
			
			  // IE doesn't make error fields non-enumerable
			  // http://msdn.microsoft.com/en-us/library/ie/dww52sbt(v=vs.94).aspx
			  if (isError(value)
			      && (keys.indexOf('message') >= 0 || keys.indexOf('description') >= 0)) {
			    return formatError(value);
			  }
			
			  // Some type of object without properties can be shortcutted.
			  if (keys.length === 0) {
			    if (isFunction(value)) {
			      var name = value.name ? ': ' + value.name : '';
			      return ctx.stylize('[Function' + name + ']', 'special');
			    }
			    if (isRegExp(value)) {
			      return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
			    }
			    if (isDate(value)) {
			      return ctx.stylize(Date.prototype.toString.call(value), 'date');
			    }
			    if (isError(value)) {
			      return formatError(value);
			    }
			  }
			
			  var base = '', array = false, braces = ['{', '}'];
			
			  // Make Array say that they are Array
			  if (isArray(value)) {
			    array = true;
			    braces = ['[', ']'];
			  }
			
			  // Make functions say that they are functions
			  if (isFunction(value)) {
			    var n = value.name ? ': ' + value.name : '';
			    base = ' [Function' + n + ']';
			  }
			
			  // Make RegExps say that they are RegExps
			  if (isRegExp(value)) {
			    base = ' ' + RegExp.prototype.toString.call(value);
			  }
			
			  // Make dates with properties first say the date
			  if (isDate(value)) {
			    base = ' ' + Date.prototype.toUTCString.call(value);
			  }
			
			  // Make error with message first say the error
			  if (isError(value)) {
			    base = ' ' + formatError(value);
			  }
			
			  if (keys.length === 0 && (!array || value.length == 0)) {
			    return braces[0] + base + braces[1];
			  }
			
			  if (recurseTimes < 0) {
			    if (isRegExp(value)) {
			      return ctx.stylize(RegExp.prototype.toString.call(value), 'regexp');
			    } else {
			      return ctx.stylize('[Object]', 'special');
			    }
			  }
			
			  ctx.seen.push(value);
			
			  var output;
			  if (array) {
			    output = formatArray(ctx, value, recurseTimes, visibleKeys, keys);
			  } else {
			    output = keys.map(function(key) {
			      return formatProperty(ctx, value, recurseTimes, visibleKeys, key, array);
			    });
			  }
			
			  ctx.seen.pop();
			
			  return reduceToSingleString(output, base, braces);
			}
			
			
			function formatPrimitive(ctx, value) {
			  if (isUndefined(value))
			    return ctx.stylize('undefined', 'undefined');
			  if (isString(value)) {
			    var simple = '\'' + JSON.stringify(value).replace(/^"|"$/g, '')
			                                             .replace(/'/g, "\\'")
			                                             .replace(/\\"/g, '"') + '\'';
			    return ctx.stylize(simple, 'string');
			  }
			  if (isNumber(value))
			    return ctx.stylize('' + value, 'number');
			  if (isBoolean(value))
			    return ctx.stylize('' + value, 'boolean');
			  // For some reason typeof null is "object", so special case here.
			  if (isNull(value))
			    return ctx.stylize('null', 'null');
			}
			
			
			function formatError(value) {
			  return '[' + Error.prototype.toString.call(value) + ']';
			}
			
			
			function formatArray(ctx, value, recurseTimes, visibleKeys, keys) {
			  var output = [];
			  for (var i = 0, l = value.length; i < l; ++i) {
			    if (hasOwnProperty(value, String(i))) {
			      output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
			          String(i), true));
			    } else {
			      output.push('');
			    }
			  }
			  keys.forEach(function(key) {
			    if (!key.match(/^\d+$/)) {
			      output.push(formatProperty(ctx, value, recurseTimes, visibleKeys,
			          key, true));
			    }
			  });
			  return output;
			}
			
			
			function formatProperty(ctx, value, recurseTimes, visibleKeys, key, array) {
			  var name, str, desc;
			  desc = Object.getOwnPropertyDescriptor(value, key) || { value: value[key] };
			  if (desc.get) {
			    if (desc.set) {
			      str = ctx.stylize('[Getter/Setter]', 'special');
			    } else {
			      str = ctx.stylize('[Getter]', 'special');
			    }
			  } else {
			    if (desc.set) {
			      str = ctx.stylize('[Setter]', 'special');
			    }
			  }
			  if (!hasOwnProperty(visibleKeys, key)) {
			    name = '[' + key + ']';
			  }
			  if (!str) {
			    if (ctx.seen.indexOf(desc.value) < 0) {
			      if (isNull(recurseTimes)) {
			        str = formatValue(ctx, desc.value, null);
			      } else {
			        str = formatValue(ctx, desc.value, recurseTimes - 1);
			      }
			      if (str.indexOf('\n') > -1) {
			        if (array) {
			          str = str.split('\n').map(function(line) {
			            return '  ' + line;
			          }).join('\n').substr(2);
			        } else {
			          str = '\n' + str.split('\n').map(function(line) {
			            return '   ' + line;
			          }).join('\n');
			        }
			      }
			    } else {
			      str = ctx.stylize('[Circular]', 'special');
			    }
			  }
			  if (isUndefined(name)) {
			    if (array && key.match(/^\d+$/)) {
			      return str;
			    }
			    name = JSON.stringify('' + key);
			    if (name.match(/^"([a-zA-Z_][a-zA-Z_0-9]*)"$/)) {
			      name = name.substr(1, name.length - 2);
			      name = ctx.stylize(name, 'name');
			    } else {
			      name = name.replace(/'/g, "\\'")
			                 .replace(/\\"/g, '"')
			                 .replace(/(^"|"$)/g, "'");
			      name = ctx.stylize(name, 'string');
			    }
			  }
			
			  return name + ': ' + str;
			}
			
			
			function reduceToSingleString(output, base, braces) {
			  var numLinesEst = 0;
			  var length = output.reduce(function(prev, cur) {
			    numLinesEst++;
			    if (cur.indexOf('\n') >= 0) numLinesEst++;
			    return prev + cur.replace(/\u001b\[\d\d?m/g, '').length + 1;
			  }, 0);
			
			  if (length > 60) {
			    return braces[0] +
			           (base === '' ? '' : base + '\n ') +
			           ' ' +
			           output.join(',\n  ') +
			           ' ' +
			           braces[1];
			  }
			
			  return braces[0] + base + ' ' + output.join(', ') + ' ' + braces[1];
			}
			
			
			// NOTE: These type checking functions intentionally don't use `instanceof`
			// because it is fragile and can be easily faked with `Object.create()`.
			function isArray(ar) {
			  return Array.isArray(ar);
			}
			exports.isArray = isArray;
			
			function isBoolean(arg) {
			  return typeof arg === 'boolean';
			}
			exports.isBoolean = isBoolean;
			
			function isNull(arg) {
			  return arg === null;
			}
			exports.isNull = isNull;
			
			function isNullOrUndefined(arg) {
			  return arg == null;
			}
			exports.isNullOrUndefined = isNullOrUndefined;
			
			function isNumber(arg) {
			  return typeof arg === 'number';
			}
			exports.isNumber = isNumber;
			
			function isString(arg) {
			  return typeof arg === 'string';
			}
			exports.isString = isString;
			
			function isSymbol(arg) {
			  return typeof arg === 'symbol';
			}
			exports.isSymbol = isSymbol;
			
			function isUndefined(arg) {
			  return arg === void 0;
			}
			exports.isUndefined = isUndefined;
			
			function isRegExp(re) {
			  return isObject(re) && objectToString(re) === '[object RegExp]';
			}
			exports.isRegExp = isRegExp;
			
			function isObject(arg) {
			  return typeof arg === 'object' && arg !== null;
			}
			exports.isObject = isObject;
			
			function isDate(d) {
			  return isObject(d) && objectToString(d) === '[object Date]';
			}
			exports.isDate = isDate;
			
			function isError(e) {
			  return isObject(e) &&
			      (objectToString(e) === '[object Error]' || e instanceof Error);
			}
			exports.isError = isError;
			
			function isFunction(arg) {
			  return typeof arg === 'function';
			}
			exports.isFunction = isFunction;
			
			function isPrimitive(arg) {
			  return arg === null ||
			         typeof arg === 'boolean' ||
			         typeof arg === 'number' ||
			         typeof arg === 'string' ||
			         typeof arg === 'symbol' ||  // ES6 symbol
			         typeof arg === 'undefined';
			}
			exports.isPrimitive = isPrimitive;
			
			exports.isBuffer = __webpack_require__(/*! ./support/isBuffer */ 54);
			
			function objectToString(o) {
			  return Object.prototype.toString.call(o);
			}
			
			
			function pad(n) {
			  return n < 10 ? '0' + n.toString(10) : n.toString(10);
			}
			
			
			var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
			              'Oct', 'Nov', 'Dec'];
			
			// 26 Feb 16:19:34
			function timestamp() {
			  var d = new Date();
			  var time = [pad(d.getHours()),
			              pad(d.getMinutes()),
			              pad(d.getSeconds())].join(':');
			  return [d.getDate(), months[d.getMonth()], time].join(' ');
			}
			
			
			// log is just a thin wrapper to console.log that prepends a timestamp
			exports.log = function() {
			  console.log('%s - %s', timestamp(), exports.format.apply(exports, arguments));
			};
			
			
			/**
			 * Inherit the prototype methods from one constructor into another.
			 *
			 * The Function.prototype.inherits from lang.js rewritten as a standalone
			 * function (not on Function.prototype). NOTE: If this file is to be loaded
			 * during bootstrapping this function needs to be rewritten using some native
			 * functions as prototype setup using normal JavaScript does not work as
			 * expected during bootstrapping (see mirror.js in r114903).
			 *
			 * @param {function} ctor Constructor function which needs to inherit the
			 *     prototype.
			 * @param {function} superCtor Constructor function to inherit prototype from.
			 */
			exports.inherits = __webpack_require__(/*! inherits */ 51);
			
			exports._extend = function(origin, add) {
			  // Don't do anything if add isn't an object
			  if (!add || !isObject(add)) return origin;
			
			  var keys = Object.keys(add);
			  var i = keys.length;
			  while (i--) {
			    origin[keys[i]] = add[keys[i]];
			  }
			  return origin;
			};
			
			function hasOwnProperty(obj, prop) {
			  return Object.prototype.hasOwnProperty.call(obj, prop);
			}
			
			/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(/*! ./~/process/browser.js */ 52)))

/***/ },
/* 7 */
/*!******************************!*\
  !*** ./app/js/stb/router.js ***!
  \******************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * Singleton for page navigation with history.
			 *
			 * All page modules should be in the directory `app/js/pages`.
			 * Page module name and the corresponding file name should be the same.
			 *
			 * Include module to start working:
			 *
			 * ```js
			 * var router = require('stb/router');
			 * ```
			 *
			 * Init with page modules:
			 *
			 * ```js
			 * router.data([
			 *     require('./pages/init'),
			 *     require('./pages/main'),
			 *     require('./pages/help')
			 * ]);
			 * ```
			 *
			 * Each page has its ID. The same ID should be used in HTML.
			 *
			 * Make some page active/visible by its ID:
			 *
			 * ```js
			 * router.navigate('pageMain');
			 * ```
			 *
			 * This will hide the current page, activate the `pageMain` page and put it in the tail of the history list.
			 *
			 * All subscribers of the current and `pageMain` page will be notified with `show/hide` events.
			 *
			 * Also the router emits `navigate` event to all subscribers.
			 *
			 *
			 * To get to the previous active page use:
			 *
			 * ```js
			 * router.back();
			 * ```
			 *
			 * The module also has methods to parse location hash address and serialize it back:
			 *
			 * ```js
			 * router.parse('#pageMain/some/additional/data');
			 * router.stringify('pageMain', ['some', 'additional', 'data']);
			 * ```
			 *
			 * Direct modification of the URL address should be avoided.
			 * The methods `router.navigate` and `router.back` should be used instead.
			 *
			 * @module stb/router
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Emitter = __webpack_require__(/*! ./emitter */ 5),
			    router;
			
			
			/**
			 * @instance
			 * @type {Emitter}
			 */
			router = new Emitter();
			
			
			/**
			 * Current active/visible page.
			 *
			 * @readonly
			 * @type {Page}
			 */
			router.current = null;
			
			
			/**
			 * List of all visited pages.
			 *
			 * @readonly
			 * @type {Page[]}
			 */
			router.history = [];
			
			
			/**
			 * List of all stored pages.
			 *
			 * @readonly
			 * @type {Page[]}
			 */
			router.pages = [];
			
			
			/**
			 * Hash table of all pages ids with links to pages.
			 *
			 * @readonly
			 * @type {Object.<string, Page>}
			 */
			router.ids = {};
			
			
			/**
			 * Set router data event.
			 *
			 * @event module:stb/router#init
			 *
			 * @type {Object}
			 * @property {Page[]} pages new page list
			 */
			
			/**
			 * Clear and fill the router with the given list of pages.
			 *
			 * @param {Page[]} pages list of pages to add
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/router#init
			 */
			router.init = function ( pages ) {
			    var i, l, item;
			
			    if ( pages ) {
			        if ( true ) {
			            if ( !Array.isArray(pages) ) { throw new Error(__filename + ': wrong pages type'); }
			        }
			
			        // reset page list
			        this.pages = [];
			
			        // apply list
			        this.pages = pages;
			
			        // extract ids
			        for ( i = 0, l = pages.length; i < l; i++ ) {
			            item = pages[i];
			            this.ids[item.id] = item;
			
			            // find the currently active page
			            if ( item.active ) {
			                this.current = item;
			            }
			        }
			
			        // there are some listeners
			        if ( this.events['init'] ) {
			            // notify listeners
			            this.emit('init', {pages: pages});
			        }
			
			        return true;
			    }
			
			    return false;
			};
			
			
			/**
			 * Extract the page name and data from url hash.
			 *
			 * @param {string} hash address hash part to parse
			 *
			 * @return {{name: string, data: string[]}} parsed data
			 *
			 * @example
			 * router.parse('#main/some/additional/data');
			 * // execution result
			 * {name: 'main', data: ['some', 'additional', 'data']}
			 */
			router.parse = function ( hash ) {
			    var page = {
			        name: '',
			        data: []
			    };
			
			    // get and decode all parts
			    page.data = hash.split('/').map(decodeURIComponent);
			    // the first part is a page id
			    // everything else is optional path
			    page.name = page.data.shift().slice(1);
			
			    return page;
			};
			
			
			/**
			 * Convert the given page name and its data to url hash.
			 *
			 * @param {string} name page name
			 * @param {string[]} [data=[]] page additional parameters
			 *
			 * @return {string} url hash
			 *
			 * @example
			 * router.stringify('main', ['some', 'additional', 'data']);
			 * // execution result
			 * '#main/some/additional/data'
			 */
			router.stringify = function ( name, data ) {
			    // validation
			    data = Array.isArray(data) ? data : [];
			
			    // encode all parts
			    name = encodeURIComponent(name);
			    data = data.map(encodeURIComponent);
			    // add encoded name to the beginning
			    data.unshift(name);
			
			    // build an uri
			    return data.join('/');
			};
			
			
			/**
			 * Make the given inactive/hidden page active/visible.
			 * Pass some data to the page and trigger the corresponding event.
			 *
			 * @param {Page} page item to show
			 * @param {*} [data] data to send to page
			 *
			 * @return {boolean} operation status
			 */
			router.show = function ( page, data ) {
			    // page available and can be hidden
			    if ( page && !page.active ) {
			        // apply visibility
			        page.$node.classList.add('active');
			        page.active  = true;
			        this.current = page;
			
			        // there are some listeners
			        if ( page.events['show'] ) {
			            // notify listeners
			            page.emit('show', {page: page, data: data});
			        }
			
			        debug.log('component ' + page.constructor.name + '.' + page.id + ' show', 'green');
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Make the given active/visible page inactive/hidden and trigger the corresponding event.
			 *
			 * @param {Page} page item to hide
			 *
			 * @return {boolean} operation status
			 */
			router.hide = function ( page ) {
			    // page available and can be hidden
			    if ( page && page.active ) {
			        // apply visibility
			        page.$node.classList.remove('active');
			        page.active  = false;
			        this.current = null;
			
			        // there are some listeners
			        if ( page.events['hide'] ) {
			            // notify listeners
			            page.emit('hide', {page: page});
			        }
			
			        debug.log('component ' + page.constructor.name + '.' + page.id + ' hide', 'grey');
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Browse to a page with the given name.
			 * Do nothing if the name is invalid. Otherwise hide the current, show new and update history.
			 *
			 * @param {string} name page id
			 * @param {*} [data] options to pass to the page on show
			 *
			 * @return {boolean} operation status
			 */
			router.navigate = function ( name, data ) {
			    var pageFrom = this.current,
			        pageTo   = this.ids[name];
			
			    if ( true ) {
			        if ( router.pages.length > 0 ) {
			            if ( !pageTo || typeof pageTo !== 'object' ) { throw new Error(__filename + ': wrong pageTo type'); }
			            if ( !('active' in pageTo) ) { throw new Error(__filename + ': missing field "active" in pageTo'); }
			        }
			    }
			
			    // valid not already active page
			    if ( pageTo && !pageTo.active ) {
			        debug.log('router.navigate: ' + name, pageTo === pageFrom ? 'grey' : 'green');
			
			        // update url
			        location.hash = this.stringify(name, data);
			
			        // apply visibility
			        this.hide(this.current);
			        this.show(pageTo, data);
			
			        // there are some listeners
			        if ( this.events['navigate'] ) {
			            // notify listeners
			            this.emit('navigate', {from: pageFrom, to: pageTo});
			        }
			
			        // store
			        this.history.push(pageTo);
			
			        return true;
			    }
			
			    debug.log('router.navigate: ' + name, 'red');
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Go back one step in the history.
			 * If there is no previous page, does nothing.
			 *
			 * @return {boolean} operation status
			 */
			router.back = function () {
			    var pageFrom, pageTo;
			
			    debug.log('router.back', this.history.length > 1 ? 'green' : 'red');
			
			    // there are some pages in the history
			    if ( this.history.length > 1 ) {
			        // remove the current
			        pageFrom = this.history.pop();
			
			        // new tail
			        pageTo = this.history[this.history.length - 1];
			
			        // valid not already active page
			        if ( pageTo && !pageTo.active ) {
			            // update url
			            location.hash = pageTo.id;
			
			            // apply visibility
			            this.hide(this.current);
			            this.show(pageTo);
			
			            // there are some listeners
			            if ( this.events['navigate'] ) {
			                // notify listeners
			                this.emit('navigate', {from: pageFrom, to: pageTo});
			            }
			
			            return true;
			        }
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			// public
			module.exports = router;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/router.js"))

/***/ },
/* 8 */
/*!*******************************!*\
  !*** ./app/js/stb/ui/list.js ***!
  \*******************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/list
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2);
			
			
			/**
			 * Mouse click event.
			 *
			 * @event module:stb/ui/list~List#click:item
			 *
			 * @type {Object}
			 * @property {Element} $item clicked HTML item
			 * @property {Event} event click event data
			 */
			
			
			/**
			 * Base list implementation.
			 *
			 * Each data item can be either a primitive value or an object with these fields:
			 *
			 *  Name    | Description
			 * ---------|-------------
			 *  value   | actual cell value to render
			 *  mark    | is it necessary or not to render this cell as marked
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object}   [config={}]            init parameters (all inherited from the parent)
			 * @param {Array}    [config.data=[]]       component data to visualize
			 * @param {function} [config.render]        method to build each grid cell content
			 * @param {function} [config.navigate]      method to move focus according to pressed keys
			 * @param {number}   [config.size=5]        amount of visible items on a page
			 * @param {number}   [config.viewIndex=0]   move view window to this position on init
			 * @param {number}   [config.focusIndex]    list item index to make item focused (move view window to this position)
			 * @param {boolean}  [config.cycle=true]    allow or not to jump to the opposite side of a list when there is nowhere to go next
			 * @param {boolean}  [config.scroll=null]   associated ScrollBar component link
			 * @param {object}   [config.provider]      data provider
			 *
			 * @fires module:stb/ui/list~List#click:item
			 */
			function List ( config ) {
			    // current execution context
			    //var self = this;
			
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.type      && Number(config.type) !== config.type  ) { throw new Error(__filename + ': config.type must be a number'); }
			    }
			
			    /**
			     * Link to the currently focused DOM element.
			     *
			     * @type {Element}
			     */
			    this.$focusItem = null;
			
			    /**
			     * Position of the visible window to render.
			     *
			     * @type {number}
			     */
			    this.viewIndex = null;
			
			    /**
			     * Component data to visualize.
			     *
			     * @type {Array}
			     */
			    this.data = [];
			
			    /**
			     * Component orientation.
			     *
			     * @type {number}
			     */
			    this.type = this.TYPE_VERTICAL;
			
			    /**
			     * Amount of visible items on a page.
			     *
			     * @type {number}
			     */
			    this.size = 5;
			
			    /**
			     * Allow or not to jump to the opposite side of a list when there is nowhere to go next.
			     *
			     * @type {boolean}
			     */
			    this.cycle = false;
			
			    /**
			     * Associated ScrollBar component link.
			     *
			     * @type {ScrollBar}
			     */
			    this.scroll = null;
			
			    // horizontal or vertical
			    if ( config.type ) {
			        // apply
			        this.type = config.type;
			    }
			
			    /**
			     * Associated data provider
			     *
			     * @type {Provider}
			     */
			    this.provider = null;
			
			
			    // set default className if classList property empty or undefined
			    config.className = 'list ' + (config.className || '');
			
			    if ( this.type === this.TYPE_HORIZONTAL ) {
			        config.className += ' horizontal';
			    }
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // component setup
			    this.init(config);
			
			    // custom navigation method
			    //if ( config.navigate ) {
			    //    if ( DEBUG ) {
			    //        if ( typeof config.navigate !== 'function' ) { throw new Error(__filename + ': wrong config.navigate type'); }
			    //    }
			    //    // apply
			    //    this.navigate = config.navigate;
			    //}
			
			    // navigation by keyboard
			    //this.addListener('keydown', this.navigate);
			
			    // navigation by mouse
			    //this.$body.addEventListener('mousewheel', function ( event ) {
			    //    // scrolling by Y axis
			    //    if ( self.type === self.TYPE_VERTICAL && event.wheelDeltaY ) {
			    //        self.move(event.wheelDeltaY > 0 ? keys.up : keys.down);
			    //    }
			    //
			    //    // scrolling by X axis
			    //    if ( self.type === self.TYPE_HORIZONTAL && event.wheelDeltaX ) {
			    //        self.move(event.wheelDeltaX > 0 ? keys.left : keys.right);
			    //    }
			    //});
			}
			
			
			// inheritance
			List.prototype = Object.create(Component.prototype);
			List.prototype.constructor = List;
			
			
			List.prototype.TYPE_VERTICAL   = 1;
			List.prototype.TYPE_HORIZONTAL = 2;
			
			
			/**
			 * Fill the given item with data.
			 *
			 * @param {Element} $item item DOM link
			 * @param {*} data associated with this item data
			 */
			List.prototype.renderItemDefault = function ( $item, data ) {
			    $item.innerText = data.value;
			};
			
			
			/**
			 * Method to build each list item content.
			 * Can be redefined to provide custom rendering.
			 *
			 * @type {function}
			 */
			List.prototype.renderItem = List.prototype.renderItemDefault;
			
			
			/**
			 * List of all default event callbacks.
			 *
			 * @type {Object.<string, function>}
			 */
			List.prototype.defaultEvents = {
			    /**
			     * Default method to handle mouse wheel events.
			     *
			     * @param {Event} event generated event
			     */
			    mousewheel: function ( event ) {
			        // scrolling by Y axis
			        if ( this.type === this.TYPE_VERTICAL && event.wheelDeltaY ) {
			            this.move(event.wheelDeltaY > 0 ? keys.up : keys.down);
			        }
			
			        // scrolling by X axis
			        if ( this.type === this.TYPE_HORIZONTAL && event.wheelDeltaX ) {
			            this.move(event.wheelDeltaX > 0 ? keys.left : keys.right);
			        }
			    },
			
			    /**
			     * Default method to handle keyboard keydown events.
			     *
			     * @param {Event} event generated event
			     */
			    keydown: function ( event ) {
			        switch ( event.code ) {
			            case keys.up:
			            case keys.down:
			            case keys.right:
			            case keys.left:
			            case keys.pageUp:
			            case keys.pageDown:
			            case keys.home:
			            case keys.end:
			                // cursor move only on arrow keys
			                this.move(event.code);
			                break;
			            case keys.ok:
			                // there are some listeners
			                if ( this.events['click:item'] && this.$focusItem ) {
			                    // notify listeners
			                    this.emit('click:item', {$item: this.$focusItem, event: event});
			                }
			                break;
			        }
			    }
			};
			
			
			/**
			 * Default method to move focus according to pressed keys.
			 *
			 * @param {Event} event generated event source of movement
			 */
			//List.prototype.navigateDefault = function ( event ) {
			//    switch ( event.code ) {
			//        case keys.up:
			//        case keys.down:
			//        case keys.right:
			//        case keys.left:
			//        case keys.pageUp:
			//        case keys.pageDown:
			//        case keys.home:
			//        case keys.end:
			//            // cursor move only on arrow keys
			//            this.move(event.code);
			//            break;
			//        case keys.ok:
			//            // there are some listeners
			//            if ( this.events['click:item'] ) {
			//                // notify listeners
			//                this.emit('click:item', {$item: this.$focusItem, event: event});
			//            }
			//            break;
			//    }
			//};
			
			
			/**
			 * Current active method to move focus according to pressed keys.
			 * Can be redefined to provide custom navigation.
			 *
			 * @type {function}
			 */
			//List.prototype.navigate = List.prototype.navigateDefault;
			
			
			/**
			 * Make all the data items identical.
			 * Wrap to objects if necessary.
			 *
			 * @param {Array} data incoming array
			 * @return {Array} reworked incoming data
			 */
			function normalize ( data ) {
			    var i, item;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( !Array.isArray(data) ) { throw new Error(__filename + ': wrong data type'); }
			    }
			
			    // rows
			    for ( i = 0; i < data.length; i++ ) {
			        // cell value
			        item = data[i];
			        // primitive value
			        if ( typeof item !== 'object' ) {
			            // wrap with defaults
			            item = data[i] = {
			                value: data[i]
			            };
			        }
			
			        if ( true ) {
			            //if ( !('value' in item) ) { throw new Error(__filename + ': field "value" is missing'); }
			            if ( ('mark' in item) && Boolean(item.mark) !== item.mark ) { throw new Error(__filename + ': item.mark must be boolean'); }
			        }
			    }
			
			    return data;
			}
			
			
			/**
			 * Init or re-init of the component inner structures and HTML.
			 *
			 * @param {Object} config init parameters (subset of constructor config params)
			 */
			List.prototype.init = function ( config ) {
			    var self     = this,
			        currSize = this.$body.children.length,
			        /**
			         * Item mouse click handler.
			         *
			         * @param {Event} event click event data
			         *
			         * @this Element
			         *
			         * @fires module:stb/ui/list~List#click:item
			         */
			        onClick = function ( event ) {
			            if ( this.data ) {
			                self.focusItem(this);
			
			                // there are some listeners
			                if ( self.events['click:item'] ) {
			                    // notify listeners
			                    self.emit('click:item', {$item: this, event: event});
			                }
			            }
			        },
			        item, i;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			    }
			
			    // apply cycle behaviour
			    if ( config.cycle !== undefined ) { this.cycle = config.cycle; }
			
			    // apply ScrollBar link
			    if ( config.scroll ) { this.scroll = config.scroll; }
			
			    // apply data provider
			    if ( config.provider ) { this.provider = config.provider; }
			
			
			    // custom render method
			    if ( config.render ) {
			        if ( true ) {
			            if ( typeof config.render !== 'function' ) { throw new Error(__filename + ': wrong config.render type'); }
			        }
			        // apply
			        this.renderItem = config.render;
			    }
			
			    // list items amount on page
			    if ( config.size ) {
			        if ( true ) {
			            if ( Number(config.size) !== config.size ) { throw new Error(__filename + ': config.size must be a number'); }
			            if ( config.size <= 0 ) { throw new Error(__filename + ': config.size should be positive'); }
			        }
			        // apply
			        this.size = config.size;
			    }
			
			    // geometry has changed or initial draw
			    if ( this.size !== currSize ) {
			        // non-empty list
			        if ( currSize > 0 ) {
			            // clear old items
			            this.$body.innerText = null;
			        }
			
			        // create new items
			        for ( i = 0; i < this.size; i++ ) {
			            item = document.createElement('div');
			            item.index = i;
			            item.className = 'item';
			
			            item.addEventListener('click', onClick);
			            this.$body.appendChild(item);
			        }
			    }
			
			    // view window position
			    if ( config.viewIndex !== undefined ) {
			        if ( true ) {
			            if ( Number(config.viewIndex) !== config.viewIndex ) { throw new Error(__filename + ': config.viewIndex must be a number'); }
			            if ( config.viewIndex < 0 ) { throw new Error(__filename + ': config.viewIndex should be positive'); }
			        }
			    }
			    // reset current view window position
			    this.viewIndex = null;
			
			    if ( this.provider ) {
			        this.provider.get( null, function ( error, data ) {
			            if ( error ) {
			                if ( self.events['data:error'] ) {
			                    /**
			                     * Provider get error while take new data
			                     *
			                     * @event module:stb/ui/list~List#data:error
			                     */
			                    self.emit('data:error', error);
			                }
			            } else {
			                if ( data ) {
			                    config.data = data;
			                    self.setData(config);
			                    if ( self.scroll ) {
			                        self.scroll.init({
			                            realSize: self.provider.maxCount,
			                            viewSize: self.provider.size,
			                            value: self.provider.head + self.provider.pos
			                        });
			                    }
			                }
			                if ( self.events['data:get'] ) {
			                    /**
			                     * Provider request new data
			                     *
			                     * @event module:stb/ui/list~List#data:get
			                     *
			                     * @type {Object}
			                     */
			                    self.emit('data:get');
			                }
			            }
			        });
			    } else if ( config.data ) {
			        this.setData(config);
			    }
			
			};
			
			/**
			 * Set data and render inner structures and HTML.
			 *
			 * @param {Object} config init parameters (subset of constructor config params)
			 */
			List.prototype.setData = function ( config ) {
			    // apply list of items
			
			    if ( config.data ) {
			        if ( true ) {
			            if ( !Array.isArray(config.data) ) { throw new Error(__filename + ': wrong config.data type'); }
			        }
			        // prepare user data
			        this.data = normalize(config.data);
			    }
			
			    // reset current view window position
			    this.viewIndex = null;
			    if ( this.$focusItem ) {
			        this.blurItem(this.$focusItem);
			    }
			
			    if ( this.scroll && !this.provider ) {
			        this.scroll.init({
			            realSize: this.data.length,
			            viewSize: this.size,
			            value: config.viewIndex || 0
			        });
			    }
			
			    // set focus item
			    if ( config.focusIndex !== undefined && this.data.length ) {
			        if ( true ) {
			            if ( Number(config.focusIndex) !== config.focusIndex ) { throw new Error(__filename + ': config.focusIndex must be a number'); }
			            if ( config.focusIndex < 0 ) { throw new Error(__filename + ': config.focusIndex should be positive'); }
			            //if ( config.focusIndex > this.data.length - 1 ) { throw new Error(__filename + ': config.focusIndex should be less than data size'); }
			        }
			
			        // jump to the necessary item
			        this.focusIndex(config.focusIndex);
			    } else {
			        // go to the first page
			        this.renderView(config.viewIndex || 0);
			    }
			};
			
			/**
			 * Shift the visible view window event.
			 *
			 * @event module:stb/ui/list~List#move:view
			 *
			 * @type {Object}
			 * @property {number} prevIndex previous view window position
			 * @property {number} currIndex current view window position
			 */
			
			
			/**
			 * Draw the visible window.
			 *
			 * @param {number} index start position to render
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/ui/list~List#move:view
			 */
			List.prototype.renderView = function ( index ) {
			    var $item, i, itemData, prevIndex, currIndex;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( Number(index) !== index ) { throw new Error(__filename + ': index must be a number'); }
			        if ( index < 0 ) { throw new Error(__filename + ': index should be more than zero'); }
			        //if ( index >= this.data.length ) { throw new Error(__filename + ': index should be less than data size'); }
			    }
			
			    // has the view window position changed
			    if ( this.viewIndex !== index ) {
			        // save for emit
			        prevIndex = this.viewIndex;
			        // sync global pointer
			        this.viewIndex = currIndex = index;
			
			        // rebuild all visible items
			        for ( i = 0; i < this.size; i++ ) {
			            // shortcuts
			            $item    = this.$body.children[i];
			            itemData = this.data[index];
			
			            // real item or stub
			            if ( itemData ) {
			                // correct inner data/index and render
			                $item.data  = itemData;
			                $item.index = index;
			                this.renderItem($item, itemData);
			
			                // apply CSS
			                if ( itemData.mark ) {
			                    $item.classList.add('mark');
			                } else {
			                    $item.classList.remove('mark');
			                }
			            } else {
			                // nothing to render
			                $item.data = $item.index = undefined;
			                $item.innerHTML = '&nbsp;';
			                $item.ready = false;
			            }
			            index++;
			        }
			
			        // there are some listeners
			        if ( this.events['move:view'] ) {
			            // notify listeners
			            this.emit('move:view', {prevIndex: prevIndex, currIndex: currIndex});
			        }
			
			        // there are some listeners
			        if ( this.events['select:item'] ) {
			            this.emit('select:item', {$item: $item});
			        }
			
			        // update a linked scroll component
			        if ( this.scroll ) {
			            this.scroll.scrollTo(this.provider? this.provider.head + this.provider.pos : this.viewIndex);
			        }
			
			        // full rebuild
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Jump to the opposite side.
			 *
			 * @event module:stb/ui/list~List#cycle
			 *
			 * @type {Object}
			 * @property {number} direction key code initiator of movement
			 */
			
			
			/**
			 * Attempt to go beyond the edge of the list.
			 *
			 * @event module:stb/ui/list~List#overflow
			 *
			 * @type {Object}
			 * @property {number} direction key code initiator of movement
			 */
			
			
			/**
			 * Move focus to the given direction.
			 *
			 * @param {number} direction arrow key code
			 *
			 * @fires module:stb/ui/list~List#cycle
			 * @fires module:stb/ui/list~List#overflow
			 */
			List.prototype.move = function ( direction ) {
			    var self = this,
			        force = false;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( Number(direction) !== direction ) { throw new Error(__filename + ': direction must be a number'); }
			    }
			    if ( !this.data.length ) {
			        return;
			    }
			    switch ( direction ) {
			        case keys.left:
			            if ( this.type === this.TYPE_HORIZONTAL ) {
			                force = true;
			            } else {
			                break;
			            }
			        case keys.up:
			            if ( force || this.type === this.TYPE_VERTICAL ) {
			                if ( this.$focusItem && this.$focusItem.index > 0 ) {
			                    if ( this.$focusItem === this.$body.firstChild ) {
			                        this.renderView(this.viewIndex - 1);
			                    } else {
			                        this.focusItem(this.$focusItem.previousSibling);
			                    }
			                } else {
			                    if ( this.provider ) {
			                        this.provider.get(direction, function ( error, data ) {
			                            if ( error ) {
			                                if ( self.events['data:error'] ) {
			                                    /**
			                                     * Provider get error while take new data
			                                     *
			                                     * @event module:stb/ui/list~List#data:error
			                                     */
			                                    self.emit('data:error', error);
			                                }
			                            } else {
			                                if ( data ) {
			                                    self.setData({data: data, focusIndex: self.$focusItem.index});
			                                }
			                            }
			                        });
			                    } else {
			                        // already at the beginning
			                        if ( this.cycle ) {
			                            // jump to the end of the list
			                            this.move(keys.end);
			                        }
			                        if ( this.events['overflow'] ) {
			                            // notify listeners
			                            this.emit('overflow', {direction: direction, cycle: this.cycle});
			                        }
			                    }
			                }
			            }
			            break;
			        case keys.right:
			            if ( this.type === this.TYPE_HORIZONTAL ) {
			                force = true;
			            } else {
			                break;
			            }
			        case keys.down:
			            if ( force || this.type === this.TYPE_VERTICAL ) {
			                if ( this.$focusItem && this.$focusItem.index < this.data.length - 1 ) {
			                    if ( this.$focusItem === this.$body.lastChild ) {
			                        this.renderView(this.viewIndex + 1);
			                    } else {
			                        this.focusItem(this.$focusItem.nextSibling);
			                    }
			                } else {
			                    if ( this.provider ) {
			                        this.provider.get(direction, function ( error, data ) {
			                            if ( error ) {
			                                if ( self.events['data:error'] ) {
			                                    /**
			                                     * Provider get error while take new data
			                                     *
			                                     * @event module:stb/ui/list~List#data:error
			                                     */
			                                    self.emit('data:error', error);
			                                }
			                            } else {
			                                if ( data ) {
			                                    self.setData({data: data, focusIndex: self.$focusItem.index});
			                                }
			                            }
			                        });
			                    } else {
			                        // already at the beginning
			                        if ( this.cycle ) {
			                            // jump to the beginning of the list
			                            this.move(keys.home);
			                        }
			                        if ( this.events['overflow'] ) {
			                            // notify listeners
			                            this.emit('overflow', {direction: direction, cycle: this.cycle});
			                        }
			                    }
			                }
			            }
			            break;
			        case keys.pageUp:
			            if ( this.provider ) {
			                this.provider.get(direction, function ( error, data ) {
			                    if ( error ) {
			                        if ( self.events['data:error'] ) {
			                            /**
			                             * Provider get error while take new data
			                             *
			                             * @event module:stb/ui/list~List#data:error
			                             */
			                            self.emit('data:error', error);
			                        }
			                    } else {
			                        if ( data ) {
			                            self.setData({data: data, focusIndex: 0});
			                        }
			                    }
			                });
			                return;
			            }
			            if ( this.viewIndex < this.size ) {
			                // first page
			                this.renderView(0);
			            } else {
			                // second page and further
			                this.renderView(this.viewIndex - this.size + 1);
			            }
			
			            this.focusItem(this.$body.firstChild);
			            break;
			        case keys.pageUp:
			            if ( this.provider ) {
			                this.provider.get(direction, function ( error, data ) {
			                    if ( error ) {
			                        if ( self.events['data:error'] ) {
			                            /**
			                             * Provider get error while take new data
			                             *
			                             * @event module:stb/ui/list~List#data:error
			                             */
			                            self.emit('data:error', error);
			                        }
			                    } else {
			                        if ( data ) {
			                            self.setData({data: data, focusIndex: 0});
			                        }
			                    }
			                });
			                break;
			            }
			            if ( this.viewIndex < this.size ) {
			                // first page
			                this.renderView(0);
			            } else {
			                // second page and further
			                this.renderView(this.viewIndex - this.size + 1);
			            }
			
			            this.focusItem(this.$body.firstChild);
			            break;
			        case keys.pageDown:
			            if ( this.provider ) {
			                this.provider.get(direction, function ( error, data ) {
			                    if ( error ) {
			                        if ( self.events['data:error'] ) {
			                            /**
			                             * Provider get error while take new data
			                             *
			                             * @event module:stb/ui/list~List#data:error
			                             */
			                            self.emit('data:error', error);
			                        }
			                    } else {
			                        if ( data ) {
			                            self.setData({data: data, focusIndex: data.length < self.size ?  data.length - 1 : self.size - 1});
			                        }
			                    }
			                });
			                break;
			            }
			            // data is bigger then one page
			            if ( this.data.length > this.size ) {
			                // determine jump size
			                if ( this.viewIndex > this.data.length - this.size * 2 ) {
			                    // last page
			                    this.renderView(this.data.length - this.size);
			                } else {
			                    // before the last page
			                    this.renderView(this.viewIndex + this.size - 1);
			                }
			                this.focusItem(this.$body.lastChild);
			            } else {
			                // not the last item on the page
			                this.focusItem(this.$body.children[this.data.length - 1]);
			            }
			            break;
			        case keys.home:
			            if ( this.provider ) {
			                this.provider.get(direction, function ( error, data ) {
			                    if ( error ) {
			                        if ( self.events['data:error'] ) {
			                            /**
			                             * Provider get error while take new data
			                             *
			                             * @event module:stb/ui/list~List#data:error
			                             */
			                            self.emit('data:error', error);
			                        }
			                    } else {
			                        if ( data ) {
			                            self.setData({data: data, focusIndex: 0});
			                        }
			                    }
			                });
			                break;
			            }
			            this.renderView(0);
			            this.focusItem(this.$body.firstChild);
			            break;
			        case keys.end:
			            if ( this.provider ) {
			                this.provider.get(direction, function ( error, data ) {
			                    if ( error ) {
			                        if ( self.events['data:error'] ) {
			                            /**
			                             * Provider get error while take new data
			                             *
			                             * @event module:stb/ui/list~List#data:error
			                             */
			                            self.emit('data:error', error);
			                        }
			                    } else {
			                        if ( data ) {
			                            self.setData({data: data, focusIndex: data.length < self.size ?  data.length - 1 : self.size - 1});
			                        }
			                    }
			                });
			                break;
			            }
			            if ( this.data.length > this.size ) {
			                this.renderView(this.data.length - this.size);
			                this.focusItem(this.$body.lastChild);
			            } else {
			                // not the last item on the page
			                this.focusItem(this.$body.children[this.data.length - 1]);
			            }
			            break;
			
			    }
			};
			
			
			/**
			 * Highlight the given DOM element as focused.
			 * Remove focus from the previously focused item and generate associated event.
			 *
			 * @param {Node|Element} $item element to focus
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/ui/list~List#focus:item
			 * @fires module:stb/ui/list~List#blur:item
			 */
			List.prototype.focusItem = function ( $item ) {
			    var $prev = this.$focusItem;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			    }
			
			    // different element
			    if ( $item && $prev !== $item ) {
			        if ( true ) {
			            if ( !($item instanceof Element) ) { throw new Error(__filename + ': wrong $item type'); }
			            if ( $item.parentNode !== this.$body ) { throw new Error(__filename + ': wrong $item parent element'); }
			        }
			
			        // some item is focused already
			        if ( $prev !== null ) {
			            if ( true ) {
			                if ( !($prev instanceof Element) ) { throw new Error(__filename + ': wrong $prev type'); }
			            }
			
			            // style
			            $prev.classList.remove('focus');
			
			            // there are some listeners
			            if ( this.events['blur:item'] ) {
			                /**
			                 * Remove focus from an element.
			                 *
			                 * @event module:stb/ui/list~List#blur:item
			                 *
			                 * @type {Object}
			                 * @property {Element} $item previously focused HTML element
			                 */
			                this.emit('blur:item', {$item: $prev});
			            }
			        }
			        // reassign
			        this.$focusItem = $item;
			
			        this.$focusItem.data = this.data[this.$focusItem.index];
			
			        // correct CSS
			        $item.classList.add('focus');
			
			        // there are some listeners
			        if ( this.events['focus:item'] ) {
			            /**
			             * Set focus to a DOM element.
			             *
			             * @event module:stb/ui/list~List#focus:item
			             *
			             * @type {Object}
			             * @property {Element} $prev old/previous focused HTML element
			             * @property {Element} $curr new/current focused HTML element
			             */
			            this.emit('focus:item', {$prev: $prev, $curr: $item});
			        }
			
			        // there are some listeners
			        if ( this.events['select:item'] ) {
			            /**
			             * Set focus to a list item.
			             *
			             * @event module:stb/ui/list~List#select:item
			             *
			             * @type {Object}
			             * @property {Element} $item new/current focused item
			             */
			            this.emit('select:item', {$item: $item});
			        }
			
			        return true;
			    }
			    // nothing was done
			    return false;
			};
			
			/**
			 * Highlight the given DOM element as blur.
			 * Remove focus from the item and generate associated event.
			 *
			 * @param {Node|Element} $item element to focus
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/ui/list~List#focus:item
			 * @fires module:stb/ui/list~List#blur:item
			 */
			List.prototype.blurItem = function ( $item ) {
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			    }
			
			    // different element
			    if ( $item ) {
			        if ( $item === this.$focusItem ) {
			            this.$focusItem = null;
			        }
			
			        $item.classList.remove('focus');
			
			        // there are some listeners
			        if ( this.events['blur:item'] ) {
			            /**
			             * Remove focus from an element.
			             *
			             * @event module:stb/ui/list~List#blur:item
			             *
			             * @type {Object}
			             * @property {Element} $item previously focused HTML element
			             */
			            this.emit('blur:item', {$item: $item});
			        }
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			/**
			 * Set the given item focused by item index.
			 *
			 * @param {number} index item data index
			 */
			List.prototype.focusIndex = function ( index ) {
			    var viewIndex = this.viewIndex || 0;
			
			    if ( true ) {
			        if ( Number(index) !== index ) { throw new Error(__filename + ': index must be a number'); }
			        if ( index < 0 ) { throw new Error(__filename + ': index should be positive'); }
			        //if ( index > this.data.length - 1 ) { throw new Error(__filename + ': index should be less than data size'); }
			    }
			
			    // determine direction
			    if ( index >= viewIndex + this.size ) {
			        // check range
			        index = index < this.data.length - 1 ? index : this.data.length - 1;
			        // move down
			        this.renderView(index - this.size + 1);
			        this.focusItem(this.$body.lastChild);
			    } else if ( index < viewIndex ) {
			        // check range
			        index = index > 0 ? index : 0;
			        // move up
			        this.renderView(index);
			        this.focusItem(this.$body.firstChild);
			    } else {
			        // no move
			        if ( this.viewIndex === null ) {
			            // first attempt
			            this.renderView(0);
			        }
			        this.focusItem(this.$body.children[index - viewIndex]);
			    }
			};
			
			
			/**
			 * Set item state and appearance as marked.
			 *
			 * @param {Node|Element} $item element to focus
			 * @param {boolean} state true - marked, false - not marked
			 */
			List.prototype.markItem = function ( $item, state ) {
			    if ( true ) {
			        if ( arguments.length !== 2 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( !($item instanceof Element) ) { throw new Error(__filename + ': wrong $item type'); }
			        if ( $item.parentNode !== this.$body ) { throw new Error(__filename + ': wrong $item parent element'); }
			        if ( Boolean(state) !== state ) { throw new Error(__filename + ': state must be boolean'); }
			    }
			
			    // correct CSS
			    if ( state ) {
			        $item.classList.add('mark');
			    } else {
			        $item.classList.remove('mark');
			    }
			
			    // apply flag
			    $item.data.mark = state;
			};
			
			
			// public
			module.exports = List;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/list.js"))

/***/ },
/* 9 */
/*!***************************!*\
  !*** ./config/metrics.js ***!
  \***************************/
/***/ function(module, exports) {

			/**
			 * Application geometry options for js/less.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    480: {
			        // screen base dimension
			        height: 480,
			        width:  720,
			        // safe zone margins
			        availTop:    24,
			        availBottom: 24,
			        availRight:  32,
			        availLeft:   48
			        // project-specific vars
			        // put here ...
			    },
			
			    576: {
			        // screen base dimension
			        height: 576,
			        width:  720,
			        // safe zone margins
			        availTop:    24,
			        availBottom: 24,
			        availRight:  26,
			        availLeft:   54
			        // project-specific vars
			        // put here ...
			    },
			
			    720: {
			        // screen base dimension
			        height: 720,
			        width:  1280,
			        // safe zone margins
			        availTop:    30,
			        availBottom: 30,
			        availRight:  40,
			        availLeft:   40
			        // project-specific vars
			        // put here ...
			    },
			
			    1080: {
			        // screen base dimension
			        height: 1080,
			        width:  1920,
			        // safe zone margins
			        availTop:    45,
			        availBottom: 45,
			        availRight:  60,
			        availLeft:   60
			        // project-specific vars
			        // put here ...
			    }
			};


/***/ },
/* 10 */
/*!************************************!*\
  !*** ./app/js/stb/ui/check.box.js ***!
  \************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module "stb/ui/check.box"
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2),
			    groups    = {};  // set of groups with linked components
			
			
			/**
			 * Base check box implementation.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {boolean} [config.value=false] initial state
			 * @param {string} [config.group] group name to work synchronously with other checkboxes
			 *
			 * @example
			 * var CheckBox = require('stb/ui/check.box'),
			 *     checkBox = new CheckBox({
			 *         value: true,
			 *         group: 'lang'
			 *     });
			 */
			function CheckBox ( config ) {
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.group     && typeof config.group     !== 'string' ) { throw new Error(__filename + ': wrong or empty config.group'); }
			    }
			
			    // set default className if classList property empty or undefined
			    config.className = 'checkBox ' + (config.className || '');
			
			    // state
			    this.value = !!config.value;
			
			    // correct init styles
			    if ( this.value ) {
			        config.className += ' checked';
			    }
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // group name to work synchronously with other checkboxes
			    this.group = null;
			
			    // apply hierarchy
			    if ( config.group ) {
			        // save
			        this.group = config.group;
			
			        // fill groups data
			        if ( groups[config.group] === undefined ) {
			            groups[config.group] = [this];
			        } else {
			            groups[config.group].push(this);
			        }
			    }
			}
			
			
			// inheritance
			CheckBox.prototype = Object.create(Component.prototype);
			CheckBox.prototype.constructor = CheckBox;
			
			
			/**
			 * List of all default event callbacks.
			 *
			 * @type {Object.<string, function>}
			 */
			CheckBox.prototype.defaultEvents = {
			    /**
			     * Default method to handle mouse click events.
			     */
			    click: function () {
			        // invert state
			        this.set(!this.value);
			    },
			
			    /**
			     * Default method to handle keyboard keydown events.
			     *
			     * @param {Event} event generated event
			     */
			    keydown: function ( event ) {
			        // emulate click
			        if ( event.code === keys.ok ) {
			            this.set(!this.value);
			        }
			    }
			};
			
			
			/**
			 * Set the given state.
			 * Does nothing in case the value is already as necessary.
			 *
			 * @param {boolean} value new value to set
			 * @return {boolean} operation status
			 *
			 * @fires module:"stb/ui/check.box~CheckBox#change"
			 */
			CheckBox.prototype.set = function ( value ) {
			    var i, l;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			    }
			
			    if ( this.value !== value ) {
			        // going to be turned on and assigned to some group
			        if ( !this.value && this.group !== null ) {
			            // unset all checkboxes in this group
			            for ( i = 0, l = groups[this.group].length; i < l; i++ ) {
			                groups[this.group][i].set(false);
			            }
			        }
			
			        // set new value
			        this.value = !this.value;
			
			        // set visible changes
			        this.$node.classList.toggle('checked');
			
			        // there are some listeners
			        if ( this.events['change'] ) {
			            /**
			             * Update progress value.
			             *
			             * @event module:stb/ui/check.box~CheckBox#change
			             *
			             * @type {Object}
			             * @property {boolean} value current check state
			             */
			            this.emit('change', {value: this.value});
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			// public
			module.exports = CheckBox;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/check.box.js"))

/***/ },
/* 11 */
/*!**************************************!*\
  !*** ./app/js/stb/ui/layout.list.js ***!
  \**************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/layout.list
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var List = __webpack_require__(/*! ./list */ 8),
			    Layout = __webpack_require__(/*! ./layout */ 38);
			
			/**
			 *  Layout list contains array of layout components
			 *
			 * @constructor
			 * @extends List
			 *
			 * @param {object} config object
			 * @param {Element} [config.noData=''] element or string to display if set empty data
			 *
			 * @example
			 * var CheckList = require('../stb/ui/layout.list'),
			 *     list = new LayoutList({
			 *         propagate: true,
			 *         size: 7,
			 *         focusIndex: 0,
			 *         noData: 'No channels'
			 *         data: [
			 *                 {
			 *                     items: [
			 *                         {
			 *                             className: 'star'
			 *                         },
			 *                         'Some text'
			 *                     ],
			 *                     click: function () {
			 *                         // do something
			 *                     }
			 *                 },
			 *                 {
			 *                     items: [
			 *                         'Hello world',
			 *                         {
			 *                             value: 'hi',
			 *                             className: 'status'
			 *                         }
			 *                     ],
			 *                     value:{
			 *                         uri: 'http://55.55.55.55/some'
			 *                     },
			 *                     click: someHandler
			 *                 },
			 *                 {
			 *                     items: [
			 *                         {
			 *                             className: 'big',
			 *                             value: ' Some'
			 *                         },
			 *                         {
			 *                             value: new Input()
			 *                         }
			 *                     ]
			 *                 },
			 *                 {
			 *                     items: [
			 *                         new Button({value: 'Ok'}),
			 *                         new Button({value: 'Cancel'}),
			 *                         new Button({value: 'Exit'})
			 *                     ]
			 *                 }
			 *             ]
			 * });
			 */
			function LayoutList ( config ) {
			    var self = this,
			        $wrap;
			
			    config = config || {};
			
			    /**
			     * Elements handlers
			     */
			    this.handlers = {};
			
			    /**
			     * No data placeholder
			     *
			     * @type {Element}
			     */
			    this.$noData = null;
			
			    config.className = 'layoutList ' + (config.className || '');
			
			    config.propagate = config.propagate || true;
			
			    /**
			     * Set data layout to be fixed to cache HTML elements
			     *
			     * @type {boolean|*}
			     */
			    this.fixedData = config.fixedData || false;
			
			    //config.$body = document.createElement('div');
			
			    config.$body = document.createElement('div');
			    config.$body.className = 'body';
			
			    this.$noData = document.createElement('div');
			    this.$noData.className = 'noData hidden';
			
			    List.call(this, config);
			
			    this.$node.appendChild(this.$body);
			
			
			    if ( config.noData ) {
			        if ( true ) {
			            if ( typeof config.noData !== 'string' && !(config.noData instanceof Element) ) { throw new Error(__filename + ': wrong config.$noData type'); }
			        }
			        if ( config.noData instanceof Element ) {
			            this.$noData.appendChild(config.noData);
			        } else if ( typeof config.noData === 'string' ) {
			            $wrap = document.createElement('div');
			            $wrap.innerText = config.noData;
			            this.$noData.appendChild($wrap);
			        }
			
			        this.$node.appendChild(this.$noData);
			    }
			
			    // add handler to focus inner layout
			    this.addListener('click:item', function ( event ) {
			        // focus inner layout of item
			        if ( event.$item.layout.children.length && !event.inner ) {
			            event.$item.layout.children[event.$item.layout.focusIndex].focus();
			        }
			
			        // only focus item if we click mouse
			        if ( event.inner ) {
			            self.focus();
			            self.focusItem(event.$item);
			        }
			        // do click callback if it present
			        if ( self.handlers[event.$item.index] ) {
			            self.handlers[event.$item.index](event.$item);
			        }
			    });
			}
			
			
			LayoutList.prototype = Object.create(List.prototype);
			LayoutList.prototype.constructor = LayoutList;
			
			/*eslint id-length:0*/
			/**
			 * Default render function
			 *
			 * @param {Element} $item in list
			 * @param {object} config to render layout element
			 */
			LayoutList.prototype.renderItemDefault = function ( $item, config ) {
			    var layout, i;
			
			    if ( $item.ready && this.fixedData && !$item.innerHTML.length ) {
			        for ( i = 0; i < config.items.length; i++ ) {
			            if ( typeof config.items[i].value === 'string' ) {
			                $item.layout.$node.childNodes[i].innerText = config.items[i].value;
			                $item.layout.$node.childNodes[i].className = config.items[i].className;
			            }
			        }
			    } else {
			        // clear inner content
			        while ( $item.firstChild ) {
			            $item.removeChild($item.firstChild);
			        }
			
			        layout = new Layout({
			            focusable:false,
			            data:config.items
			        });
			
			        $item.appendChild(layout.$node);
			        $item.layout = layout;
			        layout.parent = this;
			        layout.$parentItem = $item;
			
			        // focus layoutList if click on layout
			        layout.addListener('click', function () {
			            // add inner property to set that event comes from inner component
			            this.parent.emit('click:item', {$item:$item, inner:true});
			        });
			
			        if ( config.click ) {
			            this.handlers[$item.index] = config.click;
			        }
			        // item is rendered
			        $item.ready = true;
			    }
			    $item.value = config.value || {};
			
			};
			
			
			LayoutList.prototype.setData = function ( config ) {
			    List.prototype.setData.call(this, config);
			
			    if ( config.data && config.data.length ) {
			        this.$noData.classList.add('hidden');
			    } else {
			        this.$noData.classList.remove('hidden');
			    }
			};
			
			
			LayoutList.prototype.init = function ( config ) {
			    List.prototype.init.call(this, config);
			    if ( config.data && config.data.length ) {
			        this.$noData.classList.add('hidden');
			    } else {
			        this.$noData.classList.remove('hidden');
			    }
			};
			
			LayoutList.prototype.renderItem = LayoutList.prototype.renderItemDefault;
			
			
			module.exports = LayoutList;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/layout.list.js"))

/***/ },
/* 12 */
/*!****************************************!*\
  !*** ./app/js/stb/ui/modal.message.js ***!
  \****************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/modal.message
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1);
			
			
			/**
			 * Modal window implementation.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {Object} [config.title] message title
			 * @param {Object} [config.className] message classname
			 * @param {Object} [config.icon] icon at header
			 * @param {Object} [config.visible] visibility flag
			 * @param {Object} [config.children] content (inherited from the parent)
			 *
			 *
			 * page.modalMessage = new ModalMessage({
			 *        title: 'My Title',
			 *        icon: 'star',
			 *        children: [new Button({value: 'Create'})]
			 *    });
			 * page.add(page.modalMessage);
			 * page.modalMessage.show();
			 *
			 */
			function ModalMessage ( config ) {
			    var $overlay;
			
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.icon && typeof config.icon !== 'string' ) { throw new Error(__filename + ': wrong or empty config.icon'); }
			        if ( config.title && typeof config.title !== 'string' ) { throw new Error(__filename + ': wrong or empty config.title'); }
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.$body ) { throw new Error(__filename + ': config.$body should not be provided in ModalMessage manually'); }
			    }
			
			    // usually can't accept focus
			    config.focusable = config.focusable || false;
			    // set default className if classList property empty or undefined
			    config.className = 'modalMessage ' + (config.className || '');
			    // hide by default
			    config.visible = config.visible || false;
			    // create centered div
			    config.$body = document.createElement('div');
			    config.$body.className = 'body';
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // add table-cell wrappers
			    this.$node.appendChild(document.createElement('div'));
			    this.$node.firstChild.classList.add('alignBox');
			    this.$node.firstChild.appendChild(document.createElement('div'));
			
			    // add header div
			    this.$header = document.createElement('div');
			    this.$header.className = 'header';
			
			    // insert caption placeholder
			    this.$text = this.$header.appendChild(document.createElement('div'));
			    this.$text.classList.add('text');
			    this.$text.innerText = config.title || '';
			
			    // optional icon
			    if ( config.icon ) {
			        // insert icon
			        this.$icon = this.$header.appendChild(document.createElement('div'));
			        this.$icon.className = 'icon ' + config.icon;
			    }
			
			    $overlay = document.createElement('div');
			    $overlay.className = 'overlay';
			
			    // add to dom
			    this.$node.firstChild.firstChild.appendChild(this.$header);
			    this.$node.firstChild.firstChild.appendChild(this.$body);
			    this.$node.firstChild.firstChild.appendChild($overlay);
			}
			
			
			// inheritance
			ModalMessage.prototype = Object.create(Component.prototype);
			ModalMessage.prototype.constructor = ModalMessage;
			
			
			/**
			 * Redefine default component focus to set additional css
			 */
			ModalMessage.prototype.focus = function () {
			    this.$node.classList.add('active');
			    Component.prototype.focus.call(this);
			    if ( this.children[0] && this.children[0] instanceof Component ) {
			        this.children[0].focus();
			    }
			};
			
			/**
			 * Blur message
			 */
			ModalMessage.prototype.blur = function () {
			    this.$node.classList.remove('active');
			    Component.prototype.blur.call(this);
			};
			
			
			// public
			module.exports = ModalMessage;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/modal.message.js"))

/***/ },
/* 13 */
/*!*******************************!*\
  !*** ./app/js/stb/ui/page.js ***!
  \*******************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * Page is the main component to build user interface.
			 * Page is an area filling the whole screen.
			 * There can be only one active page visible at the same time.
			 *
			 * Active/visible state of a page is managed by the `router` module.
			 *
			 * A page can contain other components.
			 *
			 * @module stb/ui/page
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1);
			
			
			/**
			 * Base page implementation.
			 *
			 * A full-screen top-level layer that can operate as an independent separate entity.
			 * It is added to the document body on creation if not already linked.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 *
			 * @example
			 * var Page = require('stb/ui/page'),
			 *     page = new Page({
			 *         $node: document.getElementById(id)
			 *     });
			 *
			 * page.addListener('show', function show () {
			 *     // page is visible now
			 * });
			 */
			function Page ( config ) {
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			    }
			
			    /**
			     * Page visibility/active state flag.
			     *
			     * @readonly
			     * @type {boolean}
			     */
			    this.active = false;
			
			    /**
			     * Link to the currently active component with focus.
			     *
			     * @readonly
			     * @type {Component}
			     */
			    this.activeComponent = null;
			
			    // set default className if classList property empty or undefined
			    config.className = 'page ' + (config.className || '');
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // state flag
			    this.active = this.$node.classList.contains('active');
			
			    // correct DOM parent/child connection if necessary
			    if ( this.$node.parentNode === null ) {
			        document.body.appendChild(this.$node);
			    }
			
			    // always itself
			    this.page = this;
			}
			
			
			// inheritance
			Page.prototype = Object.create(Component.prototype);
			Page.prototype.constructor = Page;
			
			
			// public
			module.exports = Page;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/page.js"))

/***/ },
/* 14 */
/*!***********************!*\
  !*** ./config/app.js ***!
  \***********************/
/***/ function(module, exports) {

			/**
			 * Global application configuration.
			 * Should store run-time options, paths, flags and so on.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    //name: 'explorer',
			    mountPath: '/ram/mnt',
			    //NFS_PATH: '/ram/mnt/nfs',
			    /**
			     * List of all file types to display
			     * @type {string[]}
			     */
			    registerTypes: ['mpg', 'mpeg', 'mkv', 'avi', '3gp', 'ts', 'tspinf', 'm4a', 'mp3', 'mp4', 'ac3', 'mov', 'vob', 'wav', 'ape', 'mts', 'm2t', 'm2v', 'ogg', 'oga', 'divx', 'aiff', 'm2ts', 'wv', 'm2p', 'tp', 'flv', 'tta', 'mod', 'tod', 'asf', 'wma', 'wmv', 'flac', 'ape', 'cue', 'iso', 'aac', 'txt', 'srt', 'sub', 'ass', 'm4v', 'tts']
			};


/***/ },
/* 15 */
/*!************************!*\
  !*** ./app/js/main.js ***!
  \************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Main application entry point.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var app = __webpack_require__(/*! ./stb/app */ 3);
			
			app.addListeners({
			    load: function load () {
			        var parallel = __webpack_require__(/*! cjs-async/parallel */ 47),
			            gettext  = __webpack_require__(/*! ./stb/gettext */ 29),
			            router   = __webpack_require__(/*! ./stb/router */ 7),
			            config   = __webpack_require__(/*! ../../config/app */ 14),
			            network  = __webpack_require__(/*! ./modules/network */ 17);
			
			        app.network = network(config);
			
			        gettext.load({name: core.environment.language || 'en'}, function ( err ) {
			            if ( err ) {
			                debug.log('Gettext loading is failed...................', 'red');
			            }
			        });
			
			        router.init([
			            __webpack_require__(/*! ./pages/main */ 20)
			        ]);
			
			        if ( window.parent !== window ) {
			            app.show();
			        }
			
			        router.navigate('pageMain');
			    }
			});


/***/ },
/* 16 */
/*!******************************!*\
  !*** ./app/js/modules/fs.js ***!
  \******************************/
/***/ function(module, exports) {

			/**
			 * MAGSDK basic implementation of fs plugin.
			 *
			 * @author Yaroslav Surilov <y.surilov@infomir.com>
			 */
			
			// TODO: checking if file/folder exists before operation with it.
			
			var activeRegisterTypes;
			
			/**
			 * @constructor
			 */
			function Fs ( config ) {
			    'use strict';
			
			    var self = this;
			
			    this.types = config.registerTypes || ['mpg', 'mpeg', 'mkv', 'avi', '3gp', 'ts', 'tspinf', 'm4a', 'mp3', 'mp4', 'ac3', 'mov', 'vob', 'wav', 'ape', 'mts', 'm2t', 'm2v', 'ogg', 'oga', 'divx', 'aiff', 'm2ts', 'wv', 'm2p', 'tp', 'flv', 'tta', 'mod', 'tod', 'asf', 'wma', 'wmv', 'flac', 'ape', 'cue', 'm3u', 'm3u8', 'jpg', 'jpeg', 'png', 'bmp', 'tif', 'tiff', 'iso', 'aac', 'txt', 'srt', 'sub', 'ass', 'm4v', 'tts'];
			    this.mountPath = config.mountPath || '/ram/mnt';
			
			    Object.defineProperty(this, 'onMount', {
			        set: function ( fn ) {
			            window.parent.stbEvent.addListener('device:mount', function ( state ) {
			                //self.emit('device:mount', state);
			                fn ( state );
			            });
			        }
			    });
			}
			
			
			/**
			 * Asynchronously read the contents of a directory.
			 *
			 * @param {string} path path to the directory in file system
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.readDir = function ( path, callback ) {
			    var data;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Fs::readDir()] callback must be a function');
			    }
			
			    if ( this.types !== activeRegisterTypes ) {
			        gSTB.SetListFilesExt('.' + this.types.join(' .'));
			        activeRegisterTypes = this.types;
			    }
			
			    try {
			        // Two local variables occur: "dirs" and "files".
			        eval(gSTB.ListDir(path, true));
			
			        data = {
			            dirs: dirs.reduce(function ( acc, item ) {
			                return acc.concat(item !== '' ? { name: item.slice(0, -1), type: 3 } : []);
			            }, []),
			            files: files.reduce(function ( acc, item ) {
			                return item.name ? acc.concat(item) : acc;
			            }, [])
			        };
			    } catch ( ex ) {
			        callback({message: '[Fs::readDir()] invalid gSTB.listDir() data'});
			    }
			
			    setTimeout(function () {
			        callback(null, data);
			    }, 0);
			};
			
			
			/**
			 * Asynchronously read the file..
			 *
			 * @param {string} path path to the file in file system
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.readFile = function ( path, callback ) {
			    'use strict';
			
			    var result;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Fs::readFile()] callback must be a function');
			    }
			
			    result = gSTB.RDir('GetFile "' + path + '"');
			
			    setTimeout(function () {
			        callback(null, result);
			    }, 0);
			};
			
			
			/**
			 * Set new register file types dynamically.
			 *
			 * @param {Object[]} types new set of file types
			 */
			Fs.prototype.setTypes = function ( types ) {
			    'use strict';
			
			    this.types = types;
			};
			
			
			/**
			 * List of all mounted partitions.
			 *
			 * @return {Object} mount points in a form of a set of objects
			 */
			Fs.prototype.getMountPoints = function ( filter ) {
			    'use strict';
			
			    var snList = {}; // Set of all serial numbers with amount of partitions on each
			
			    // Get mount points
			    var info = JSON.parse(gSTB.GetStorageInfo('{}'));
			    // valid non-empty data
			    if ( Array.isArray(info.result) && info.errMsg === '' && info.result.length > 0 ) {
			        info.result.forEach(function ( item ) {
			            // SD card-reader support
			            item.mediaType = item.sn === '000022272228' ? 3 : item.mediaType;
			
			            item.label = item.label.trim();
			            if ( snList[item.sn] ) {
			                snList[item.sn]++;
			            } else {
			                snList[item.sn] = 1;
			            }
			        });
			
			        info.result.forEach(function ( item ) {
			            if ( !item.label ) {
			                item.label = item.vendor + ' ' + item.model.replace(/\//, '');
			                if ( snList[item.sn] > 1 ) {
			                    item.label += ' #' + item.partitionNum;
			                }
			            }
			        });
			
			        // Sort by mount path
			        info.result.sort(function ( a, b ) {
			            return a.mountPath < b.mountPath ? -1 : 1;
			        });
			
			        // Final list of all combined data
			        return info.result;
			    } else {
			        return [];
			    }
			};
			
			
			/**
			 * Get low-level information about mounted HDDs.
			 */
			Fs.prototype.getHDDInfo = function () {
			    'use strict';
			
			    try {
			        return JSON.parse(gSTB.RDir('get_hdd_info') || '[]');
			    } catch ( ex ) {
			        return [];
			    }
			};
			
			
			/**
			 * Delete particular folder.
			 *
			 * @param {string} path path to the file system object
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.removeDir = function ( path, callback ) {
			    'use strict';
			
			    var command;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Fs::removeDir()] callback must be a function');
			    }
			
			    command = 'RemoveDirFull "' + (path.charAt(path.length - 1) === '/' ? path.substr(0, path.length - 1) : path) + '"';
			
			    callback(null, gSTB.RDir(command) === 'Ok');
			};
			
			
			/**
			 * Delete particular file.
			 *
			 * @param {string} path path to the file system object
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.removeFile = function ( path, callback ) {
			    'use strict';
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Fs::removeFile()] callback must be a function');
			    }
			
			    callback(null, gSTB.RDir('RemoveFile "' + path + '"') === 'Ok');
			};
			
			
			/**
			 * Create directory by specified path.
			 *
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.createDir = function ( path, callback ) {
			    'use strict';
			
			    var self = this;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Fs::createDir()] callback must be a function');
			    }
			
			    gSTB.ExecAction('make_dir "' + path + '"');
			
			    setTimeout(function () {
			        self.isFolderExist(path, function ( err, res ) {
			            callback(null, res);
			        });
			    }, 0);
			};
			
			
			/**
			 * Check if the given file has UTF8 encoding.
			 *
			 * @param {string} path file to test
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.isFileUTF8Encoded = function ( path, callback ) {
			    'use strict';
			
			    var result = gSTB.IsFileUTF8Encoded(path);
			
			    setTimeout(function () {
			        callback(null, result);
			    }, 0);
			};
			
			
			/**
			 * Test is file name point to existing file.
			 *
			 * @param {string} path absolute file path which will be tested
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.isFileExist = function ( path, callback ) {
			    'use strict';
			
			    var result = gSTB.IsFolderExist(path);
			
			    setTimeout(function () {
			        callback(null, result);
			    }, 0);
			};
			
			
			/**
			 * Test is file name point to existing folder.
			 *
			 * @param {string} path absolute file path which will be tested
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.isFolderExist = function ( path, callback ) {
			    'use strict';
			
			    var result = gSTB.IsFolderExist(path);
			
			    setTimeout(function () {
			        callback(null, result);
			    }, 0);
			};
			
			
			/**
			 * Mount shared directory.
			 *
			 * @param {Object} config mount options
			 * @param {String} config.address ip address
			 * @param {String} config.login needed login
			 * @param {String} config.pass needed pass
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.mount = function ( config, callback ) {
			    'use strict';
			
			    var mountPath = (config && config.mountPath) || this.mountPath,
			        login,
			        pass,
			        command,
			        status;
			
			    if ( config && config.address ) {
			        config.address = config.address.charAt(config.address.length - 1) === '/' ? config.address.substr(0, config.address.length - 1) : config.address;
			        login = config.login || 'guest';
			        pass = config.pass ||  '';
			        command = 'mount cifs "' + config.address + '" "' + mountPath + '" username=' + login + ',password=' + pass + ',iocharset=utf8';
			        status = gSTB.RDir(command).trim();
			    }
			
			    setTimeout(function () {
			        callback(null, status === 'Ok');
			    }, 0);
			};
			
			
			/**
			 * Umount folder (for example nfs or smb).
			 *
			 * @param {Object} config umount options
			 * @param {boolean} config.force [force=false] flag to forcefully umount the resource
			 * @param {boolean} config.mounted is directory already mounted
			 * @param {boolean} config.path path for umount
			 * @param {Function} callback method to invoke with a result of operation
			 */
			Fs.prototype.umount = function ( config, callback ) {
			    'use strict';
			
			    if ( config.mounted || config.force ) {
			        gSTB.ExecAction('umount_dir ' + config.path);
			    }
			};
			
			
			/**
			 * Resolve own fs wrapper for each application.
			 *
			 * @param {Object} config application config
			 * @return {Fs} fs wrapper instance
			 */
			module.exports = function ( config ) {
			    'use strict';
			
			    return new Fs(config);
			};


/***/ },
/* 17 */
/*!***********************************!*\
  !*** ./app/js/modules/network.js ***!
  \***********************************/
/***/ function(module, exports) {

			/**
			 * MAGSDK basic implementation of network plugin.
			 *
			 * @author Yaroslav Surilov <y.surilov@infomir.com>
			 */
			
			'use strict';
			
			/**
			 * Async fetch available Samba work groups.
			 *
			 * @param {Function} callback
			 */
			function getGroups ( callback ) {
			    var data = [],
			        self = this,
			        groups;
			
			    try {
			        groups = JSON.parse(gSTB.GetSmbGroups());
			    } catch ( ex ) {
			        throw new Error('[Network]::getGroups()> parsing gSTB.GetSmbGroups() failed');
			    }
			
			    if ( groups && Array.isArray(groups.result) && groups.result.length > 0 ) {
			        groups.result.forEach(function( group ) {
			            if ( !group ) {
			                return;
			            }
			
			            data.push({
			                name: group,
			                uri: group,
			                type: self.types.SAMBA_GROUP
			            });
			        });
			
			        callback(null, data);
			    } else {
			        callback({message: 'Invalid data'});
			    }
			}
			
			
			/**
			 * Async fetch available servers for given work group.
			 *
			 * @param {Object} config
			 * @param {Function} callback method to invoke with a result of operation
			 */
			function getServers ( config, callback ) {
			    var data,
			        self,
			        servers;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Network]::getServers()> callback must be a function');
			    }
			
			    data = [];
			    self = this;
			
			    try {
			        servers = JSON.parse(gSTB.GetSmbServers(JSON.stringify({group: config.group})));
			    } catch ( ex ) {
			        throw new Error('[Network]::getServers()> parsing gSTB.GetSmbServers() failed');
			    }
			
			    if ( servers && Array.isArray(servers.result) && servers.result.length > 0 ) {
			        servers.result.forEach(function( server ){
			            if ( !server ) {
			                return;
			            }
			
			            data.push({
			                name: server,
			                uri: config.uri + '/' + server,
			                type: self.types.SAMBA_HOST
			            });
			        });
			
			        callback(null, data);
			    } else {
			        callback({message: 'Invalid data'});
			    }
			}
			
			
			/**
			 * Async fetch available Samba shares for the given server.
			 *
			 * @param {string} uri
			 * @param {Function} callback method to invoke with a result of operation
			 */
			function getShares ( uri, callback ) {
			    var data,
			        self,
			        shares;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[Network]::getShares()> callback must be a function');
			    }
			
			    data = [];
			    self = this;
			
			    try {
			        shares = JSON.parse(gSTB.GetSmbShares(JSON.stringify({server: uri})));
			    } catch ( ex ) {
			        throw new Error('[Network]::getShares()> parsing gSTB.GetSmbShares() failed');
			    }
			
			    // reset
			    // unmountSMB();
			
			    if ( shares && shares.result && Array.isArray(shares.result.shares) && shares.result.shares.length > 0 ) {
			        shares.result.shares.forEach(function ( share ) {
			            var server;
			
			            if ( !share ) {
			                return;
			            }
			
			            server = shares.result.serverIP;
			
			            data.push({
			                name: share,
			                address: '//' + server + '/' + share,
			                server: server,
			                type: self.types.SAMBA_SHARE
			            });
			        });
			
			        callback(null, data);
			    } else {
			        callback({ message: 'Invalid data' });
			    }
			}
			
			
			/**
			 * Resolve own network wrapper for each application.
			 *
			 * @param {Object} config Application config
			 * @return {Object} network wrapper instance
			 */
			module.exports = function ( config ) {
			    return {
			        config: config,
			        types: {
			            SAMBA_GROUP: 0,
			            SAMBA_HOST: 1,
			            SAMBA_SHARE: 2
			        },
			        getGroups: getGroups,
			        getServers: getServers,
			        getShares: getShares
			    };
			};


/***/ },
/* 18 */
/*!********************************!*\
  !*** ./app/js/modules/upnp.js ***!
  \********************************/
/***/ function(module, exports) {

			/**
			 * MAGSDK basic implementation of UPnP plugin.
			 *
			 * @author Yaroslav Surilov <y.surilov@infomir.com>
			 */
			
			'use strict';
			
			var s = window.stbUPnP,
			    isUPnPInit = false;
			
			if ( !isUPnPInit ) {
			    s.init();
			    isUPnPInit = true;
			}
			
			function UPnP() {}
			
			
			/**
			 * Enter the root UPnP item or open it
			 *
			 * @param {Object} [data] media item inner data
			 * @param {number} level hierarchy change flag: 0 - no change, 1 - go level deeper, -1 - go level up
			 * @param {Function} callback method to invoke with a result of operation
			 * @return
			 */
			UPnP.prototype.openRoot = function ( data, level, callback ) {
			    var servers, content;
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[UPnP::openRoot()] callback must be a function');
			    }
			
			    servers = s.getServerListSync();
			    content = [];
			
			    // some data received
			    if ( servers && Array.isArray(servers) && servers.length > 0 ) {
			        servers.forEach(function(server){
			            //debug.inspect(server);
			            content.push({name:server.FriendlyName, url: server.UDN, type: 7.2});
			        });
			        // go deeper
			        //inServer = false;
			        //levelChange = level;
			        //listLink.total = content.length;
			        setTimeout(function () {
			            callback( null, content );
			        }, 0);
			    } else {
			        setTimeout(function () {
			            callback( { message: 'Unable to get UPnP group servers' } )
			        }, 0);
			    }
			};
			
			UPnP.prototype.openServer = function ( path, callback ) {
			    var result;
			
			    debug.log('-----------------"""""""""""""""""""""""');
			
			    if ( typeof callback !== 'function' ) {
			        throw new Error('[UPnP::openHost()] callback must be a function');
			    }
			
			    s.onOpenServer = function ( data, error ) {
			        if ( error ) {
			            callback(error);
			        } else {
			            callback(null, data);
			        }
			    };
			
			    s.openServer(path);
			};
			
			
			/**
			 * Resolve own wrapper for each application.
			 *
			 * @param {Object} config application config
			 * @return {UPnP} UPnP wrapper instance
			 */
			module.exports = function ( config ) {
			    'use strict';
			
			    return new UPnP(config);
			};


/***/ },
/* 19 */
/*!*******************************************!*\
  !*** ./app/js/modules/utils/evalSpace.js ***!
  \*******************************************/
/***/ function(module, exports) {

			/**
			 * Calculate file size in human readable format.
			 */
			(function () {
			   'use strict';
			
			    function evalSpace ( size, freeSize ) {
			        function bytesToSize ( bytes, precision ) {
			            var kilobyte = 1024;
			            var megabyte = kilobyte * 1024;
			            var gigabyte = megabyte * 1024;
			            var terabyte = gigabyte * 1024;
			
			            if ( (bytes >= 0) && (bytes < kilobyte) ) {
			                return bytes + ' B';
			            } else if ( (bytes >= kilobyte) && (bytes < megabyte) ) {
			                return (bytes / kilobyte).toFixed(precision) + ' KB';
			            } else if ( (bytes >= megabyte) && (bytes < gigabyte) ) {
			                return (bytes / megabyte).toFixed(precision) + ' MB';
			            } else if ( (bytes >= gigabyte) && (bytes < terabyte) ) {
			                return (bytes / gigabyte).toFixed(precision) + ' GB';
			            } else if ( bytes >= terabyte ) {
			                return (bytes / terabyte).toFixed(precision) + ' TB';
			            } else {
			                return bytes + ' B';
			            }
			        }
			
			        // TODO: potentially transform into string.
			        return {
			            overview: bytesToSize(freeSize, 1) + ' / ' + bytesToSize(size, 1),
			            singleObject: bytesToSize(size, 1)
			        };
			    }
			
			    module.exports = evalSpace;
			}());


/***/ },
/* 20 */
/*!******************************!*\
  !*** ./app/js/pages/main.js ***!
  \******************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Media Browser / File Manager.
			 *
			 * @author Yaroslav Surilov <y.surilov@infomir.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var SMB_MOUNT_PATH       = '/ram/mnt/smb';
			
			var app                  = __webpack_require__(/*! ../stb/app */ 3),
			    keys                 = __webpack_require__(/*! ../stb/keys */ 2),
			    Panel                = __webpack_require__(/*! ../stb/ui/panel.ex */ 39),
			    PanelSet             = __webpack_require__(/*! ../stb/ui/panel.set */ 40),
			    LayoutList           = __webpack_require__(/*! ../stb/ui/layout.list */ 11),
			    ModalMessage         = __webpack_require__(/*! ../stb/ui/modal.message */ 12),
			    RadioList            = __webpack_require__(/*! ../stb/ui/radio.list */ 41),
			    CheckList            = __webpack_require__(/*! ../stb/ui/check.list */ 34),
			    Input                = __webpack_require__(/*! ../stb/ui/input */ 37),
			    Button               = __webpack_require__(/*! ../stb/ui/button */ 33),
			    config               = __webpack_require__(/*! ../../../config/app */ 14),
			    evalSpace            = __webpack_require__(/*! ../modules/utils/evalSpace */ 19),
			    ScrollBar            = __webpack_require__(/*! ../stb/ui/scroll.bar */ 42),
			    Footer               = __webpack_require__(/*! ../stb/ui/footer */ 36),
			    Clock                = __webpack_require__(/*! ../stb/ui/clock */ 35),
			    page                 = new (__webpack_require__(/*! ../stb/ui/page */ 13))({$node: window.pageMain}),
			    doc                  = document,
			    isModalShown         = false,
			    echo                 = core.notify,
			    noNameDirs           = 0,
			    /**
			     * Enable Fs system plugin.
			     */
			    //fs                   = core.plugins.fs,
			    fs                   = __webpack_require__(/*! ../modules/fs */ 16)(config),
			    upnp                 = __webpack_require__(/*! ../modules/upnp */ 18)(config),
			    /**
			     * Enable UfsJson system plugin.
			     */
			    ufsJson              = core.plugins.ufsJson,
			    //ufsJson              = require('../modules/ufs')(config),
			    // Storage information when application starts.
			    initUSBInfo          = fs.getMountPoints(),
			    historyStack         = [],
			    /**
			     * Correspondence of handlers with various types of content.
			     *
			     * @type {Object}
			     */
			    openers              = {},
			    /**
			     * Directory listing for future sorting, because only sorting "by name" can be applied to it,
			     * and in all other variants it remains unchanged.
			     *
			     * @type {Array}
			     */
			    actualDirList        = [],
			    /**
			     * File listing for future sorting, because really sorting is possible only for files.
			     *
			     * @type {Array}
			     */
			    actualFileList       = [],
			    /**
			     * Structure for storing app data.
			     *
			     * @type {Object}
			     */
			    appStorage     = {},
			    dirFilesByType       = {},
			    /**
			     * Structure for mapping file type details to their numbers.
			     *
			     * @type {Object}
			     */
			    typeBase             = {},
			    serversFavShares     = [],
			    mainPanelTitle,
			    menuFilteringItem,
			    searchRequest,
			    folderListView,
			    sharesListView,
			    originFolderListView,
			    mainPanelSet,
			    leftPanel,
			    mainPanel,
			    rightPanel,
			    leftPanelMenu,
			    mainPanelList,
			    rightPanelDetails,
			    ctxMenuList,
			    filterMenuList,
			    lastContext,
			    ext2mimeMap,
			    listScroll,
			    filterScroll,
			    fsTypes;
			
			/*
			-------------------------------------------------------------------
			
			        Global types
			
			-------------------------------------------------------------------
			*/
			
			/*
			 0 - init screen
			 1 - storage
			 2 - network
			 3 - folder
			 4 - file
			 // here are subtypes for type 'file'
			*/
			
			// Desired types, not yet implemented.
			/*
			 0  - init screen
			 1  - favorites
			 2  - network
			 3  - work groups
			 4  - servers
			 5  - shares | *
			 6  - permanent shares
			
			 7  - UPnP | *
			 7.1 - UPnP_ROOT | top level | *
			 7.2 - UPnP_HOST | server level | *
			 7.3 - UPnP_FOLDER | folder level | *
			
			 8  - storage | *
			 9  - folder | *
			 10 - file | *
			 11 - cue | *
			 // here are subtypes for type 'file'
			 */
			
			
			historyStack.type = 0; // 0 - init screen, 1 - storage, 2 - network
			
			/**
			 * Get last element of the list
			 *
			 * @returns {*} last element of the list
			 */
			historyStack.last = function () {
			    return this[this.length - 1];
			};
			
			fsTypes = {
			    0: 'Unknown',
			    1: 'FAT16',
			    2: 'FAT32',
			    3: 'EXT2',
			    4: 'EXT3',
			    5: 'NTFS',
			    6: 'EXT4'
			};
			
			typeBase = {
			    // VIDEO
			    5: {
			        ext: ['mpg', 'mpeg', 'mov', 'mp4', 'avi', 'mkv', '3gp', 'ts', 'vob', 'wmv', 'mts', 'm2t', 'm2v', 'divx', 'm2ts', 'm2p', 'tp', 'flv', 'mod', 'tod', 'asf', 'tts', 'm4v'],
			        ico: 'b'
			    },
			    // AUDIO
			    6: {
			        ext: ['mp3', 'wav', 'ac3', 'ogg', 'oga', 'aiff', 'wv', 'tta', 'wma', 'aac', 'dts', 'flac', 'ape', 'm4a'],
			        ico: 'a'
			    },
			    // IMAGE
			    7: {
			        ext: ['jpg', 'jpeg', 'png', 'bmp', 'tif', 'tiff'],
			        ico: 'e'
			    },
			    // TEXT
			    8: {
			        ext: ['txt', 'srt', 'sub', 'ass'],
			        ico: 'd'
			    },
			    // PLAYLIST
			    10: {
			        ext: ['m3u', 'm3u8'],
			        ico: 'Q'
			    },
			    // CUE
			    11: {
			        ext: ['cue'],
			        ico: 'a'
			    },
			    // ISO
			    12: {
			        ext: ['iso'],
			        ico: 'h'
			    },
			    // RECORDS
			    13: {
			        ext: ['tspinf'],
			        ico: 'g'
			    }
			};
			
			ext2mimeMap = {
			    5: 'content/video',
			    6: 'content/audio',
			    12:'content/video'
			};
			
			openers = {
			    9: openDir, // TODO: implement this as a handler for opening folders
			    5: openMedia,
			    6: openMedia,
			    7: openImage,
			    11: openCue,
			    12: openIso
			};
			
			
			// app.addListener('device:mount', function () {
			//     Z();
			//     Z();
			// });
			
			/*
			 ------------------------------------------------------------------------------
			
			    Openers
			
			 ------------------------------------------------------------------------------
			 */
			
			/**
			 * Open specified directory.
			 *
			 * @param {Object} config set of parameters associated with particular directory
			 * @param {Object} specificOpenerConfig
			 */
			function openDir ( config, specificOpenerConfig ) {
			    if ( specificOpenerConfig.type === 'dvd' ) {
			        var uri = config.path || getPathFromStack(historyStack.slice(historyStack.type === 2 ? 4 : 0));
			
			        core.intent({
			            action: 'play',
			            mime: 'content/video',
			            data: {
			                title: config.name,
			                uri: uri,
			                solution: 'extBDDVD'
			            },
			            events: {}
			        }, function ( error, context ) {
			            //debug.info(error, 'error1');
			            lastContext = context;
			        });
			    }
			}
			
			
			function openShare ( config, callback ) {
			    fs.mount(config, callback);
			}
			
			
			/**
			 * Open CUE
			 */
			function openCue ( item ) {
			    var uri = item.path || getPathFromStack(historyStack.slice(historyStack.type === 2 ? 4 : 0)) + item.name;
			
			    fs.readFile(uri, function ( err, res ) {
			        var cue = parseCue(res),
			            ext = cue.files[0].name.replace(/^[^.]+\./, ''),
			            cnt = [];
			
			        //debug.info(cue, 'raw');
			
			        // fill the list
			        cue.files.forEach(function( file ) {
			            file.tracks.forEach(function( track ) {
			                var info = {
			                    name: track.number + '. ' + track.title,
			                    type: track.type === 'AUDIO' ? 6 : 5,
			                    ext: ext,
			                    time: (track.index[track.index.length-1] || 0),
			                    url: uri + '/' + file.name
			                };
			                if ( cue.files.length > 1 ) {
			                    info.file = file.name;
			                }
			                if ( track.performer !== cue.performer && track.performer !== undefined ) {
			                    info.performer = track.performer;
			                }
			                cnt.push(info);
			            });
			        });
			
			        //debug.info(cnt, 'ready');
			
			        historyStack.push({
			            index: mainPanelList.$focusItem.index,
			            path: mainPanelList.$focusItem.data.value.name
			        });
			
			        reRenderList(mainPanelList, constructDataOut({dirs:[], files: cnt}, 1), 7, 7, listScroll, {focusIndex: 0});
			    });
			
			    //' position:' + this.obj.time
			}
			
			
			/**
			 * Handler for audio and video content.
			 *
			 * @param {Object} item item to play
			 */
			function openMedia ( item, mixin ) {
			    if ( !item ) {
			        return;
			    }
			
			    var uri = item.path || getPathFromStack(historyStack.slice(historyStack.type === 2 ? 4 : 0)) + item.name,
			        queue = dirFilesByType[item.type],
			        next, intent;
			
			    //debug.inspect();
			
			    var objData = {
			        action: 'play',
			        mime: item.mime,
			        data: {
			            title: item.name,
			            uri: uri
			        },
			        events: {
			            end: function () {
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                if ( next == undefined ) {
			                    next = queue.next = queue.indexOf(item) + 1;
			                } else {
			                    next = queue.next = next + 1;
			                }
			
			                if ( next === queue.length ) {
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length + next + 1);
			                    queue.next = null;
			                    return;
			                }
			
			                openMedia(queue[next], {context: lastContext});
			            },
			            prev: function () {
			                //debug.log('prev', 'yellow');
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                if ( next === 0 || (!next && queue.length === 1) ) {
			                    //debug.log('prev', 'yellow');
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length);
			                    queue.next = null;
			                    return;
			                } else {
			                    if ( next == undefined ) {
			                        next = queue.next = queue.indexOf(item) - 1;
			                    } else {
			                        next = queue.next = next - 1;
			                    }
			                }
			                openMedia(queue[next], {context: lastContext});
			            },
			            next: function () {
			                //debug.log('next', 'yellow');
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                // debug.inspect(queue);
			                // debug.log(queue.length, 'yellow');
			                // debug.log(next, 'yellow');
			
			                if ( next === queue.length - 1 || (!next && queue.length === 1) ) {
			                    //debug.log('next', 'yellow');
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length + next + 1);
			                    queue.next = null;
			                    return;
			                } else {
			                    if ( next == undefined ) {
			                        next = queue.next = queue.indexOf(item) + 1;
			                    } else {
			                        next = queue.next = next + 1;
			                    }
			                }
			                openMedia(queue[next], {context: lastContext});
			            },
			            close: function () {
			                mainPanelList.focusIndex(next ? actualDirList.length + next + 1 : mainPanelList.$focusItem.index);
			            },
			            error: function ( err ) {
			                //debug.info(err, 'error0');
			            }
			        }
			    };
			
			    if ( mixin ) {
			        Object.keys(mixin).forEach(function ( item ) {
			            objData[item] = mixin[item];
			        });
			    }
			
			    // Delete intent events depending on file position in queue^
			    // disable "prev" on the first one and "next" on the last
			    if ( !queue.indexOf(item) ) {
			        delete objData.events.prev;
			    }
			
			    if ( queue.indexOf(item) === queue.length - 1 ) {
			        delete objData.events.next;
			    }
			    //---------------------------
			
			    //debug.inspect(objData);
			
			    intent = core.intent(objData, function ( error, context ) {
			        //debug.info(error, 'error1');
			        lastContext = context;
			    });
			}
			
			
			/**
			 * Handler for images content.
			 *
			 * @param {Object} item item to open
			 */
			function openImage (item ) { // TODO: Realize it in the future
			}
			
			function openIso ( item ) {
			    if ( !item ) return;
			    var uri = item.path || getPathFromStack(historyStack.slice(historyStack.type === 2 ? 4 : 0)) + item.name,
			        queue = dirFilesByType[item.type],
			        next, intent;
			
			    var objData = {
			        action: 'play',
			        mime: item.mime,
			        data: {
			            title: item.name,
			            uri: uri
			        },
			        events: {
			            end: function () {
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                if ( next == undefined ) {
			                    next = queue.next = queue.indexOf(item) + 1;
			                } else {
			                    next = queue.next = next + 1;
			                }
			
			                if ( next === queue.length ) {
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length + next + 1);
			                    queue.next = null;
			                    return;
			                }
			
			                openMedia(queue[next], {context: lastContext});
			            },
			            prev: function () {
			                //debug.log('prev', 'yellow');
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                if ( next === 0 || (!next && queue.length === 1) ) {
			                    //debug.log('prev', 'yellow');
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length);
			                    queue.next = null;
			                    return;
			                } else {
			                    if ( next == undefined ) {
			                        next = queue.next = queue.indexOf(item) - 1;
			                    } else {
			                        next = queue.next = next - 1;
			                    }
			                }
			                openMedia(queue[next], {context: lastContext});
			            },
			            next: function () {
			                //debug.log('next', 'yellow');
			                var queue = dirFilesByType[item.type];
			                next = queue.next;
			
			                // debug.inspect(queue);
			                // debug.log(queue.length, 'yellow');
			                // debug.log(next, 'yellow');
			
			                if ( next === queue.length - 1 || (!next && queue.length === 1) ) {
			                    //debug.log('next', 'yellow');
			                    intent.close();
			                    mainPanelList.focusIndex(actualDirList.length + next + 1);
			                    queue.next = null;
			                    return;
			                } else {
			                    if ( next == undefined ) {
			                        next = queue.next = queue.indexOf(item) + 1;
			                    } else {
			                        next = queue.next = next + 1;
			                    }
			                }
			                openMedia(queue[next], {context: lastContext});
			            },
			            close: function () {
			                mainPanelList.focusIndex(next ? actualDirList.length + next + 1 : mainPanelList.$focusItem.index);
			            },
			            error: function ( err ) {
			                //debug.info(err, 'error0');
			            }
			        }
			    };
			
			    // Delete intent events depending on file position in queue^
			    // disable "prev" on the first one and "next" on the last
			    if ( !queue.indexOf(item) ) {
			        delete objData.events.prev;
			    }
			
			    if ( queue.indexOf(item) === queue.length - 1 ) {
			        delete objData.events.next;
			    }
			    //---------------------------
			
			    //debug.inspect(objData);
			
			    intent = core.intent(objData, function ( error, context ) {
			        //debug.info(error, 'error1');
			        lastContext = context;
			    });
			}
			
			
			/*--------------------------------- end openers ------------------------------*/
			
			
			/*
			 ------------------------------------------------------------------------------
			
			        Parsers
			
			 ------------------------------------------------------------------------------
			 */
			
			/**
			 * Parses the given CUE data
			 * @param {string} text data to parse
			 * @return {Object}
			 */
			function parseCue ( text ) {
			    var currFile = null, currTrack = null, data = {files:[]};
			    text.split('\n').forEach(function(line) {
			        line = line.trim();
			        var lineParts = line.split(' '),
			            lineCmd   = lineParts.shift().toLowerCase();
			        // strip command name
			        var value = line.slice(lineCmd.length).trim();
			        // check all
			        switch ( lineCmd ) {
			            case 'title':
			            case 'performer':
			            case 'songwriter':
			                // strip trailing quotes
			                if ( value.charAt(0) === '"' && value.charAt(value.length-1) === '"' ) {
			                    value = value.slice(1, value.length-1);
			                }
			                if ( currTrack !== null ) {
			                    currTrack[lineCmd] = value;
			                } else {
			                    data[lineCmd] = value;
			                }
			                break;
			            case 'file':
			                currFile = {tracks:[]};
			                var parts = value.split(' ');
			                // has additional info
			                if ( parts.length > 1 && value.charAt(value.length-1) !== '"' ) {
			                    currFile.type = parts.pop();
			                    // strip type part
			                    value = value.slice(0, -currFile.type.length).trim();
			                }
			                // strip trailing quotes
			                if ( value.charAt(0) === '"' && value.charAt(value.length-1) === '"' ) {
			                    value = value.slice(1, value.length-1);
			                }
			                currFile.name = value;
			                data.files.push(currFile);
			                break;
			            case 'track':
			                currTrack = {
			                    number : lineParts[0],
			                    type   : lineParts[1],
			                    index  : []
			                };
			                currFile.tracks.push(currTrack);
			                break;
			            case 'rem':
			                if ( ['GENRE', 'DATE', 'COMMENT', 'PLACE'].indexOf(lineParts[0]) !== -1 ) {
			                    value = value.slice(lineParts[0].length).trim();
			                    var field = lineParts[0].toLowerCase();
			                    // strip trailing quotes
			                    if ( value.charAt(0) === '"' && value.charAt(value.length-1) === '"' ) {
			                        value = value.slice(1, value.length-1);
			                    }
			                    if ( currTrack !== null ) {
			                        currTrack[field] = value;
			                    } else {
			                        data[field] = value;
			                    }
			                }
			                break;
			            case 'index':
			                if ( currTrack !== null ) {
			                    var time = (lineParts[1] || '00:00:00').split(':');
			                    currTrack.index.push(parseInt(time[0], 10) * 60 + parseInt(time[1], 10));
			                }
			                break;
			        }
			    });
			    return data;
			}
			
			/*--------------------------------- end parsers ------------------------------*/
			
			/*
			 ------------------------------------------------------------------------------
			
			         Utilities
			
			 ------------------------------------------------------------------------------
			 */
			
			function extractServersFromPermanentShares() {
			    var shares = appStorage.sharesStorage;
			
			    shares.forEach(function ( item ) {
			        serversFavShares.push(item.server);
			    });
			}
			
			smbTraverse.openShareAttempts = 0;
			
			/*--------------------------------- end utilities ------------------------------*/
			
			/**
			 * Traversing network resources.
			 *
			 * @param {string} resource resource to open
			 * @param {Object} data set of properties for opening shared folders
			 * @param {Object} cfg additional config for opened resource
			 */
			function smbTraverse ( resource, data, cfg ) {
			    var indexToSetFocus = typeof cfg.index !== 'undefined' ? cfg.index : 0;
			
			    if ( resource === 'getGroups' ) {
			        app.network.getGroups(function ( err, res ) {
			            reRenderList(mainPanelList, constructDataOut(res, 2, cfg), 7, 7, listScroll, {focusIndex: indexToSetFocus});
			        });
			    } else if ( resource === 'openShare' ) {
			        smbTraverse.openShareAttempts += 1;
			
			        openShare(data, function ( err, res ) {
			            if ( !res && smbTraverse.openShareAttempts === 1 ) {
			                echo({
			                    title: gettext("Authorization may be necessary. Specify login and password?"),
			                    icon: 'info',
			                    type: '',
			                    buttons: [
			                        {
			                            value: gettext('Cancel'),
			                            click: function () {
			                                smbTraverse.openShareAttempts = 0;
			                                return;
			                            }
			                        },
			                        {
			                            value: gettext('Ok'),
			                            click: function () {
			                                mainPanelSet.blur();
			                                page.modalMessageSambaAuthorize.show();
			                                page.modalMessageSambaAuthorize.focus();
			                            }
			                        }
			                    ]
			                });
			            } else if ( !res && smbTraverse.openShareAttempts > 1 ) {
			                //debug.log(smbTraverse.openShareAttempts, 'yellow');
			                echo({
			                    title: gettext('Credentials are incorrect'),
			                    icon: 'alert',
			                    type: 'warning'
			                });
			            } else {
			                closeModalWindow();
			                smbTraverse.openShareAttempts = 0;
			
			                if ( historyStack.currentDeviceType !== 5 ) {
			                    historyStack.push({
			                        index: mainPanelList.$focusItem.index,
			                        path: data,
			                        type: 2.3
			                    });
			                }
			
			                fs.readDir(data.mountPath, function ( err, res ) {
			                    historyStack.push({
			                        index: mainPanelList.$focusItem.index,
			                        path: data.mountPath
			                    });
			
			                    historyStack.isDisplayShares = true;
			
			                    reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: indexToSetFocus});
			                    reRenderList(rightPanelDetails, renderDetailsList('1000000', [res.dirs.length + res.files.length + '', null, null, null, null, null, null]), 5, 5, null, null);
			                });
			            }
			        });
			    } else {
			        app.network[resource](data, function ( err, res ) {
			            var idx;
			
			            if ( resource === 'getShares' ) {
			                sharesListView = res;
			                if ( res ) {
			                    if ( ~serversFavShares.indexOf(res[0].server) ) {
			                        cfg.favSharesPresent = true;
			                        cfg.idx = serversFavShares.indexOf(res[0].server);
			                    }
			                }
			            }
			            reRenderList(mainPanelList, constructDataOut(res, 2, cfg), 7, 7, listScroll, {focusIndex: indexToSetFocus});
			        });
			    }
			}
			
			
			/**
			 * Reset stack to default state except "last" method.
			 */
			function resetStack() {
			    var last = historyStack.last;
			
			    historyStack = [];
			    historyStack.last = last;
			    historyStack.type = 0;
			
			    folderListView = null;
			}
			
			/**
			 *
			 * @param files
			 */
			function updateDirFilesByType ( files ) {
			    dirFilesByType = {};
			
			    files.forEach(function ( item ) {
			        dirFilesByType[item.type] ? dirFilesByType[item.type].push(item) : (dirFilesByType[item.type] = [item]);
			    });
			}
			
			
			/**
			 * Handler for "Back" button.
			 */
			function goBack() {
			    var len, prevIdx;
			
			    if ( !historyStack.isDisplayFavorites || (historyStack.isDisplayFavorites && historyStack.length > 1) ) {
			        prevIdx = historyStack.last().index;
			        // Potential problem with index in stack
			        historyStack.pop();
			        len = historyStack.length;
			    }
			
			    if ( historyStack.isDisplayFavorites && !historyStack.length ) {
			        //Z();
			        reRenderList(mainPanelList, renderFilesListInitial(), 7, 7, listScroll, {focusIndex: historyStack.entryPointIndex});
			        reRenderList(rightPanelDetails, renderDetailsList('0000000', [null, null, null, null, null, null, null]), 5, 5, null, null);
			        // Use "resetStack" instead.
			        // historyStack.isDisplayFavorites = false;
			        // historyStack.type = 0;
			        // folderListView = null;
			        // historyStack.currentDirFilesCount = 0;
			        // historyStack.length = 0;
			        resetStack();
			        page.footer.hide();
			    } else if ( historyStack.type === 2 && len === 4 ) {
			        debug.log('Go out from network share (to the list of shares)', 'red');
			        folderListView = null;
			        // TODO: set constant
			        fs.umount({ path: SMB_MOUNT_PATH, force: true });
			        smbTraverse('getShares', historyStack[historyStack.length - 2].path, {icon: 'V', index: prevIdx});
			        reRenderList(rightPanelDetails, renderDetailsList('0000000', [null, null, null, null, null, null, null]), 5, 5, null, null);
			        historyStack.pop();
			    } else if ( historyStack.type === 2 && len > 4 ) {
			        debug.log('Go out from folder in network share', 'red');
			
			        fs.readDir(getPathFromStack(historyStack.slice(4)), function ( err, res ) {
			            historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			            reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: prevIdx});
			            reRenderList(rightPanelDetails,
			                renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			        });
			    } else if ( historyStack.type === 1 ) {
			        if ( historyStack.isDisplayFavorites && historyStack.length === 1 ) {
			            var favorites = appStorage.favoritesStorage;
			
			            historyStack.currentDirFilesCount = appStorage.favoritesStorage.length;
			
			            historyStack.pop();
			
			            reRenderList(mainPanelList,
			                constructDataOut({
			                    dirs: favorites.filter(function ( item ) { return item.type === 9; }),
			                    files: favorites.filter(function ( item ) { return item.type > 9; })
			                }, 1),
			                7, 7, listScroll, {focusIndex: 0});
			        } else {
			            //debug.log(getPathFromStack());
			            fs.readDir(getPathFromStack(), function ( err, res ) {
			                var name = '0',
			                    data;
			
			                historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			                if ( len ) {
			                    data = constructDataOut(res, 1);
			                } else {
			                    // historyStack.isDisplayShares = false;
			                    // historyStack.type = 0;
			                    // folderListView = null;
			                    resetStack();
			                    mainPanelTitle.innerText = gettext('files');
			                    data = renderFilesListInitial();
			
			                    page.footer.hide();
			
			                    // Construct share name.
			                    name = historyStack.currentDeviceType === 5 ? historyStack.currentDevice.address : null;
			                }
			
			                menuFilteringItem.innerText = gettext('all');
			
			                if ( historyStack.currentDeviceType === 5 ) {
			                    //Z();
			                    fs.umount({ path: SMB_MOUNT_PATH, force: true });
			
			                    reRenderList(rightPanelDetails,
			                        renderDetailsList('0100000', [null, name, null, null, null, null, null]), 5, 5, null, null);
			                } else {
			                    if ( historyStack.currentDevice ) {
			                        reRenderList(rightPanelDetails,
			                            renderDetailsList(+!!len + '000' + +!len + +!len + '0', [len ? historyStack.currentDirFilesCount + '' : null, null, null, null,
			                                len ? null : evalSpace(historyStack.currentDevice.size, historyStack.currentDevice.freeSize).overview, len ? null : fsTypes[historyStack.currentDevice.fsType], null]), 5, 5, null, null);
			                    }
			                }
			
			                historyStack.currentDeviceType = null;
			
			                reRenderList(mainPanelList, data, 7, 7, listScroll, {focusIndex: prevIdx});
			
			            });
			        }
			    } else if ( historyStack.type === 2 && len < 4 ) {
			        debug.log('Go out from network stage', 'red');
			
			        if ( historyStack.length === 3 ) {
			            smbTraverse('getShares', historyStack[historyStack.length - 1].path, {icon: 'V', index: prevIdx});
			            historyStack.isDisplayShares = false;
			            //Z();
			        } else if ( historyStack.length === 2 ) {
			            folderListView = null;
			            smbTraverse('getServers', historyStack[historyStack.length - 1].path, {icon: 'U', index: prevIdx});
			        } else if ( historyStack.length === 1) {
			            smbTraverse('getGroups', null, {icon: 'T', index: prevIdx});
			        } else {
			            // Delete special class for special rendering of long items in lists.
			            doc.querySelector('.panelEx.main').classList.remove('networkView');
			
			            historyStack.type = 0;
			            folderListView = null;
			
			            //resetStack();
			
			            reRenderList(mainPanelList, renderFilesListInitial(), 7, 7, listScroll, {focusIndex: historyStack.entryPointIndex});
			
			            page.footer.hide();
			        }
			    }
			}
			
			
			/**
			 * Construct appropriate render structure for lists from raw sources.
			 *
			 * @param {Object} data raw data
			 * @param {number} type type of data to render
			 * @return {Object[]} array of ready-to-use in LayoutList objects
			 */
			function constructDataOut ( data, type, cfg ) {
			    var itemBack, dirs, files;
			
			    itemBack = [
			        {
			            items: [
			                {
			                    value: 'S',
			                    className: 'icon iconFileManager'
			                },
			                '..'
			            ],
			            click: function () {
			                goBack();
			            }
			        }
			    ];
			
			    if ( type === 1 ) {
			        dirs = data.dirs;
			        files = data.files;
			
			        files = files.map(function (item ) {
			            var key;
			            item.ext = item.ext || item.name.split('.').pop().toLowerCase();
			
			            for ( key in typeBase ) {
			                if ( ~typeBase[key].ext.indexOf(item.ext) ) {
			                    item.type = key;
			                    break;
			                }
			            }
			
			            item.mime = ext2mimeMap[item.type];
			            return item;
			        });
			
			        // Create queues of files of particular types/separate files by type.
			        updateDirFilesByType(files);
			
			        dirs = dirs.map(function ( item ) {
			            var className = 'icon iconFileManager favoritesStar',
			                icoFav = 'B',
			                len = appStorage.favoritesStorage.length,
			                i = 0,
			                isFavorite = false;
			
			            for ( ; i < len; i += 1 ) {
			                if ( appStorage.favoritesStorage[i].path === (item.path || getPathFromStack() + item.name)) {
			                    icoFav = 'A';
			                    className += ' theme-favorite-icon-active';
			                    isFavorite = true;
			                    break;
			                }
			
			                if ( i + 1 === len ) {
			                    className += ' theme-favorite-icon';
			                }
			            }
			
			            return {
			                items: [
			                    {
			                        value: 'R',
			                        className: 'icon iconFileManager'
			                    },
			                    {
			                        value: item.name,
			                        className: 'listDirNameField'
			                    },
			                    !historyStack.isDisplayShares && {
			                        value: icoFav,
			                        className: className
			                    }
			                ],
			                value: {
			                    name: item.name,
			                    type: 9,
			                    item: item
			                },
			                click: function () {
			                    var stack;
			
			                    historyStack.push({
			                        index: mainPanelList.$focusItem.index,
			                        path: historyStack.isDisplayFavorites && historyStack.length === 0 ? item.path : item.name
			                    });
			
			                    //DS();
			                    stack = historyStack.type === 2 ? historyStack.slice(4) : historyStack;
			
			                    // check if unpacked dvd/Blu-ray iso folder
			                    if ( ~['VIDEO_TS', 'BDMV', 'video_ts', 'bdmv', 'video_ts.ifo', 'VIDEO_TS.IFO'].indexOf(item.name) ) {
			                        //Z();
			                        echo({
			                            title: gettext("Do you want to play folder's content?"),
			                            icon: 'info',
			                            type: '',
			                            buttons: [
			                                {
			                                    value: gettext('Cancel'),
			                                    click: function () { // FIXME: default execution of this branch.
			                                        alert();
			                                        //localReadDir();
			                                    }
			                                },
			                                {
			                                    value: gettext('Play'),
			                                    click: function () {
			                                        //openDir(item, {type: 'dvd'});
			                                    }
			                                }
			                            ]
			                        });
			                    } else {
			                        localReadDir();
			                    }
			
			                    // For DRY purpose, read directory
			                    function localReadDir() {
			                        fs.readDir(getPathFromStack(stack), function ( err, res ) {
			                            historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			
			                            reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: 0});
			                            reRenderList(rightPanelDetails, renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			                        });
			                    }
			                }
			            };
			        });
			
			        files = files.map(function ( item ) {
			            // Restore favorites status when construct file list.
			            var className = 'icon iconFileManager favoritesStar',
			                icoFav = 'B',
			                len = appStorage.favoritesStorage.length,
			                i = 0,
			                icoType,
			                isFavorite = false;
			
			            for ( ; i < len; i += 1 ) {
			                if ( appStorage.favoritesStorage[i].path === (item.path || getPathFromStack() + item.name)) {
			                    icoFav = 'A';
			                    className += ' theme-favorite-icon-active';
			                    isFavorite = true;
			                    break;
			                }
			
			                if ( i + 1 === len ) {
			                    className += ' theme-favorite-icon';
			                }
			            }
			
			            for ( i in typeBase ) {
			                if ( ~typeBase[i].ext.indexOf(item.ext) ) {
			                    icoType = typeBase[i].ico;
			                    break;
			                }
			            }
			
			            return {
			                items: [
			                    {
			                        value: icoType,
			                        className: 'icon iconFileManager' + (historyStack.type === 1 ? ' listDirFileIcon' : '')
			                    },
			                    {
			                        value: item.name,
			                        className: 'listDirNameField'
			                    },
			                    historyStack.type === 1 && !historyStack.isDisplayShares && {
			                        value: icoFav,
			                        className: className
			                    }
			                ],
			                value: {
			                    name: item.name,
			                    type: 10,
			                    item: item,
			                    isFavorite: isFavorite
			                },
			                click: function () {
			                    if ( openers[item.type] ) {
			                        openers[item.type](item);
			                    } else {
			                        echo({
			                            title: gettext('Can not open this kind of file'),
			                            icon: 'info'
			                        });
			                    }
			                }
			            };
			        });
			    } else if ( type === 2 ) {
			        data = (data || []).map(function ( item ) {
			            var additionalClass = '',
			                shareName = item.name,
			                sharesStorage = appStorage.sharesStorage,
			                iter, i;
			
			            if ( cfg && cfg.favSharesPresent ) {
			                iter = sharesStorage.slice(cfg.idx);
			                for ( i = iter.length; i--; ) {
			                    if ( iter[i].name === shareName ) {
			                        additionalClass = 'theme-favorite-icon-active';
			                        break;
			                    }
			                }
			            }
			
			            return {
			                items: [
			                    {
			                        value: (cfg && cfg.icon) || 'O',
			                        className: 'icon iconFileManager ' + additionalClass
			                    },
			                    item.name
			                ],
			                click: function () {
			                    var path;
			
			                    if ( historyStack.length === 1 ) {
			                        historyStack.push({
			                            index: mainPanelList.$focusItem.index,
			                            path: { group: item.name, uri: item.uri },
			                            type: 2.1
			                        });
			                        smbTraverse('getServers', historyStack.last().path, {icon: 'U'});
			                    } else if ( historyStack.length === 2 ) {
			                        historyStack.push({
			                            index: mainPanelList.$focusItem.index,
			                            path: item.name,
			                            type: 2.2
			                        });
			                        smbTraverse('getShares', historyStack.last().path, {icon: 'V'});
			
			                        page.footer.init([]);
			                        page.footer.show();
			
			                    } else if ( historyStack.length === 3 ) {
			                        smbTraverse('openShare', {address: item.address, folder: item.name, mountPath: SMB_MOUNT_PATH, login: item.login, password: item.password}, {});
			                    }
			                },
			                value: {
			                    item: item
			                }
			            };
			        });
			
			        return itemBack.concat(data);
			    }
			
			    actualDirList = dirs;
			    actualFileList = files;
			
			    // Default sorting by name.
			    folderListView = dirs.concat(files).sort(function ( a, b ) {
			        return a.value.name.toLowerCase().localeCompare(b.value.name.toLowerCase());
			    });
			
			    //DS();
			    //debug.inspect(folderListView);
			    return originFolderListView = (folderListView = itemBack.concat(folderListView)).slice();
			}
			
			
			/*
			 ------------------------------------------------------------------------------
			
			        UPnP
			
			 ------------------------------------------------------------------------------
			 */
			
			function constructUPnPDataOut ( data ) {
			    var itemBack, dirs, files;
			
			    itemBack = [
			        {
			            items: [
			                {
			                    value: 'S',
			                    className: 'icon iconFileManager'
			                },
			                '..'
			            ],
			            click: function () {
			                goBack();
			            }
			        }
			    ];
			
			    if ( data ) {
			        return itemBack.concat(data);
			    }
			}
			
			function prepareContent() {
			    var s = window.stbUPnP,
			        result = [],
			        item;
			
			    while ( (item = list.shift()) ) {
			        switch ( item.ContentType ) {
			            case s.TYPE_FOLDER:
			                item.type = 9;
			                item.url = item.ID;
			                break;
			            case s.TYPE_AUDIO:
			                item.type = 6;
			                break;
			            case s.TYPE_VIDEO:
			                item.type = 5;
			                break;
			            case s.TYPE_IMAGE:
			                item.type = 7;
			                break;
			            default :
			                item.type = 8;
			                break;
			        }
			
			        if ( item.type !== s.TYPE_FOLDER && item.Resources && item.Resources.length ) {
			            item.url = item.Resources[0].Value;
			            item.size = item.Resources[0].Size;
			        }
			        item.Resources = null;
			        item.readOnly = true;
			        item.name = item.Title;
			
			        result.push(item);
			    }
			
			    return result;
			}
			
			/*--------------------------------- end UPnP ------------------------------*/
			
			
			/**
			 * Re-render page on data change/some event.
			 *
			 * @param {Object} elem element that we want to re-render
			 * @param {Object[]} data new initialization data
			 * @param {number} size size of the list
			 * @param {number} sizeLimit minimum size for displaying scroll
			 * @param {Object} scrollType scroll element
			 */
			function reRenderList ( elem, data, size, sizeLimit, scrollType, mixin ) {
			    var tmpData = {
			            scroll: data.length <= sizeLimit ? null : scrollType,
			            size: size,
			            data: data
			        },
			        nameField, item, itemHeight;
			
			    if ( mixin ) {
			        Object.keys(mixin).forEach(function ( item ) {
			            tmpData[item] = mixin[item];
			        });
			    }
			
			
			    if ( scrollType ) {
			        if ( data.length > 7 ) {
			            if ( !elem.scroller ) {
			                elem.scroller = 1;
			                scrollType.init({
			                    realSize: data.length
			                });
			                scrollType.$node.style.display = '';
			            }
			        } else {
			            elem.scroller = 0;
			            //debug.inspect(scrollType);
			            scrollType.$node.style.display = 'none';
			        }
			    }
			
			    elem.setData(tmpData);
			
			    // Set styles to "name" field in details panel
			    if ( doc.getElementsByClassName('detailsNameField')[0] ) {
			        nameField = doc.querySelector('.detailsNameField').nextElementSibling;
			        item = nameField.parentNode.parentNode;
			        itemHeight = window.getComputedStyle(item).getPropertyValue('height');
			
			        if ( reRenderList.itemHeight ) {
			            itemHeight = reRenderList.itemHeight;
			        } else {
			            reRenderList.itemHeight = itemHeight = window.getComputedStyle(item).getPropertyValue('height');
			        }
			
			        nameField.style.cssText =
			            'white-space: inherit; text-overflow: initial; overflow-wrap: break-word; min-height: ' + parseInt(itemHeight) * 1.25 + 'px !important;';
			        item.style.cssText =
			            'min-height: ' + parseInt(itemHeight) * 1.25 + 'px !important; white-space: inherit;';
			
			    }
			}
			
			
			/*
			 ------------------------------------------------------------------------------
			
			    Device info
			
			 ------------------------------------------------------------------------------
			 */
			
			/**
			 * Construct comprehensive information about network share
			 *
			 * @param {Object} share particular share with information about itself
			 * @return {Object} constructed data
			 */
			function constructShareInfo ( share ) {
			    //debug.inspect(share);
			
			    var template = [
			        {
			            items: [
			                gettext('display name') + ':',
			                share.details.name // FIXME after appearing renaming for share
			            ]
			        },
			        {
			            items: [
			                gettext('real name') + ':',
			                share.details.name
			            ]
			        },
			        {
			            items: [
			                gettext('type') + ':',
			                gettext('SAMBA')
			            ]
			        },
			        {
			            items: [
			                gettext('server') + ':',
			                share.details.server
			            ]
			        },
			        {
			            items: [
			                gettext('full path') + ':',
			                share.details.address
			            ]
			        },
			        {
			            items: [
			                gettext('authorization') + ':',
			                gettext('anonymous entry')
			            ]
			        }
			    ];
			
			    try {
			        // Hide diagram and its staff.
			        doc.getElementsByClassName('infoStorageDiagram')[0].classList.add('hidden');
			        doc.getElementsByClassName('cntIndicators')[0].classList.add('hidden');
			    } catch ( ex ) {}
			
			    return template;
			}
			
			
			/**
			 * Construct comprehensive information about Favorites
			 *
			 * @return {Object} constructed data
			 */
			function constructFavoritesInfo () {
			    var favorites = appStorage.favoritesStorage;
			
			    try {
			        // Hide diagram and its staff.
			        doc.getElementsByClassName('infoStorageDiagram')[0].classList.add('hidden');
			        doc.getElementsByClassName('cntIndicators')[0].classList.add('hidden');
			    } catch ( ex ) {}
			
			    return [
			        {
			            items: [
			                gettext('total') + ':',
			                String(favorites.length)
			            ]
			        }
			    ];
			}
			
			/*--------------------------------- end device info --------------------------*/
			
			
			/**
			 * Render details list in the right panel accordingly to necessary set.
			 *
			 * @param {string} mask necessary set of list items.
			 */
			function renderDetailsList ( mask, data ) {
			    var fullSet = [
			            {
			                items: [
			                    gettext('elements') + ':'
			                ]
			            },
			            {
			                items: [
			                    {
			                        value: gettext('name') + ':',
			                        className: 'detailsNameField'
			                    }
			                ]
			            },
			            {
			                items: [
			                    gettext('creation date') + ':'
			                ]
			            },
			            {
			                items: [
			                    gettext('size') + ':'
			                ]
			            },
			            {
			                items: [
			                    gettext('free space') + ':'
			                ]
			            },
			            {
			                items: [
			                    gettext('file system') + ':'
			                ]
			            },
			            {
			                items: [
			                    gettext('unsaved changes') + ':'
			                ]
			            }
			        ],
			        maskLst = mask.split(''),
			        diagram = doc.getElementsByClassName('infoStorageDiagram')[0],
			        indicators = doc.getElementsByClassName('cntIndicators')[0];
			
			    if ( diagram ) {
			        diagram.classList[+maskLst[4] ? 'remove' : 'add']('hidden');
			    }
			
			    if ( indicators ) {
			        indicators.classList[+maskLst[4] ? 'remove' : 'add']('hidden');
			    }
			
			    // New realization
			    return fullSet.reduce(function ( acc, item, idx ) {
			        if ( +maskLst[idx] ) {
			            item.items.push(data[idx]);
			            acc.push(item);
			        }
			        return acc;
			    }, []);
			}
			
			
			/**
			 * Render context menu list accordingly to necessary set.
			 *
			 * @param {string} mask necessary set of list items.
			 */
			function renderCtxMenuList ( mask ) {
			    var data = [
			        {
			            items: [
			                {
			                    value: '',
			                    className: 'icon f3'
			                },
			                gettext('add to favorites')
			            ],
			            click: function () {
			                addEntityToFavorites();
			                page.modalMessageActions.hide();
			            }
			        },
			        {
			            items: [
			                {
			                    value: 'F',
			                    className: 'icon iconFileManager'
			                },
			                gettext('open')
			            ],
			            click: function () {
			                var target = mainPanelList.$focusItem;
			
			                if ( target.value.type === 9 ) {
			                    historyStack.push({
			                        index: mainPanelList.$focusItem.index,
			                        path: target.value.name
			                    });
			
			                    fs.readDir(getPathFromStack(), function ( err, res ) {
			                        historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			                        reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: 0});
			                        reRenderList(rightPanelDetails, renderDetailsList('1000100', [historyStack.currentDirFilesCount + '', null, null, null, evalSpace(historyStack.currentDevice.size, historyStack.currentDevice.freeSize).overview, null, null]), 5, 5, null, null);
			                    });
			                } else {
			                    openers[target.value.item.type](target.value.item);
			                }
			
			                page.modalMessageActions.hide();
			            }
			        },
			        {
			            items: [
			                {
			                    value: '',
			                    className: 'icon f2'
			                },
			                gettext('create folder')
			            ],
			            click: function () {
			                page.modalMessageActions.hide();
			                mainPanelSet.hide();
			                page.modalMessageCreateFolder.show();
			                page.modalMessageCreateFolder.focus();
			            }
			        },
			        {
			            items: [
			                {
			                    value: '',
			                    className: 'icon f1'
			                },
			                gettext('delete')
			            ],
			            click: function () {
			                removeFileSystemObject();
			            }
			        },
			        {
			            items: [
			                {
			                    value: 'W',
			                    className: 'icon iconFileManager'
			                },
			                gettext('create shortcut')
			            ],
			            click: function () {
			                addEntityToPermanentShares();
			                page.modalMessageActions.hide();
			            }
			        }
			    ];
			
			    return data.filter(function ( item, idx ) {
			        return +mask[idx];
			    });
			}
			
			
			function addEntityToPermanentShares () {
			    var target = mainPanelList.$focusItem;
			
			    target.children[0].children[0].classList.add('theme-favorite-icon-active');
			
			    //debug.inspect(target.data.value.item);
			    appStorage.sharesStorage.push(target.data.value.item);
			}
			
			
			function removeEntityFromPermanentShares ( shareItem ) {
			    var target = mainPanelList.$focusItem;
			
			    //debug.info(shareItem, 'shareItem');
			    //
			    // target.children[0].children[0].classList.remove('theme-favorite-icon-active');
			    //
			    // //debug.inspect(target.data.value.item);
			    // appStorage.sharesStorage.push(target.data.value.item);
			}
			
			
			/**
			 * Render files list on app init or on change set of plugged devices.
			 */
			function renderFilesListInitial () {
			    var lst = [],
			        permanentSharesList = appStorage.sharesStorage,
			        isUPnPEnabled = 0,
			        upnpPath,
			        itemsToRender,
			        persistentItems;
			
			    // check if upnp activated
			    if ( core.environment.upnp_conf === 'on' ) {
			        upnpPath = '/media/UPnP';
			
			        isUPnPEnabled = 1;
			    } else if ( window.stbUPnP && window.stbUPnP.getServerListSync ) {
			        isUPnPEnabled = 1;
			    }
			
			    debug.log(typeof window.stbUPnP, 'yellow');
			    debug.log(typeof window.top.stbUPnP, 'yellow');
			
			    persistentItems = [
			            {
			                items: [
			                    {
			                        value: 'A',
			                        className: 'icon iconFileManager'
			                    },
			                    {
			                        value: gettext('favorites'),
			                        className: 'persistentMainEntries'
			                    }
			                ],
			                value: {
			                    type: 1
			                },
			                click: function () {
			                    var favorites = appStorage.favoritesStorage;
			
			                    historyStack.type = 1;
			                    historyStack.isDisplayFavorites = true;
			                    historyStack.entryPointIndex = mainPanelList.$focusItem.index;
			                    historyStack.currentDirFilesCount = appStorage.favoritesStorage.length;
			
			                    reRenderList(mainPanelList,
			                        constructDataOut({
			                            dirs: favorites.filter(function ( item ) { return item.type === 9; }),
			                            files: favorites.filter(function ( item ) { return item.type > 9; })
			                        }, 1),
			                        7, 7, listScroll, {focusIndex: 0});
			
			                    //DS();
			                }
			            },
			            {
			                items: [
			                    {
			                        value: 'O',
			                        className: 'icon iconFileManager'
			                    },
			                    {
			                        value: gettext('network'),
			                        className: 'persistentMainEntries'
			                    }
			                ],
			                value: {
			                    type: 2
			                },
			                click: function () {
			                    // Set special class for special rendering of long items in lists.
			                    doc.querySelector('.panelEx.main').classList.add('networkView');
			
			                    historyStack.type = 2;
			                    historyStack.entryPointIndex = mainPanelList.$focusItem.index;
			                    historyStack.length = 0;
			                    historyStack.push({
			                        index: mainPanelList.$focusItem.index,
			                        path: 'SAMBA_GROUP'
			                    });
			                    historyStack.type = 2;
			                    smbTraverse('getGroups', null, {icon: 'T'});
			                }
			            },
			            {
			                items: [
			                    {
			                        value: 'P',
			                        className: 'icon iconFileManager'
			                    },
			                    'UPnP'
			                ],
			                value: {
			                    type: 7
			                },
			                click: function () {
			                    historyStack.type = 1;
			                    historyStack.currentDevice = null;
			                    historyStack.entryPointIndex = mainPanelList.$focusItem.index;
			                    historyStack.currentDeviceType = 7;
			                    historyStack.stageType = 7;
			
			                    if ( upnpPath ) {
			                        historyStack.push({
			                            index: mainPanelList.$focusItem.index,
			                            path: upnpPath
			                        });
			
			                        //debug.log(getPathFromStack(), 'yellow');
			
			                        fs.readDir(getPathFromStack(), function (err, res) {
			                            //debug.inspect(res);
			                            historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			
			                            // Read and display additional information on the right panel.
			                            //reRenderList(rightPanelDetails, renderDetailsList('1000100', [historyStack.currentDirFilesCount + '', null, null, null, evalSpace(item.size, item.freeSize).overview, null, null]), 5, 5, null, null);
			
			                            // Read this device
			                            reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: 0});
			
			                            page.footer.init([
			                                //f1: {title: gettext('delete'), action: function () {}},
			                                {type: 'f2', title: gettext('create folder'), action: function () {}},
			                                //f3: {title: gettext('add to favorites'), action: function () {}},
			                                {type: 'f4', title: gettext('search'), action: function () {}}
			                            ]);
			
			                            page.footer.show();
			                        });
			                    } else {
			                        upnp.openRoot( null, null, function ( error, result ) {
			                            var lst;
			
			                            if ( error ) {
			                                echo({
			                                    title: gettext( error.message ),
			                                    icon: 'alert',
			                                    type: 'warning'
			                                });
			                            } else {
			                                lst = [];
			
			                                result.forEach(function ( item ) {
			                                    lst.push({
			                                        items: [
			                                            {
			                                                value: 'V',
			                                                className: 'icon iconFileManager'
			                                            },
			                                            item.name
			                                        ],
			                                        click: function () {
			                                            upnp.openServer(item.url, function ( error, result ) {
			                                                debug.inspect(result);
			                                            });
			                                        },
			                                        value: item
			                                    });
			                                });
			
			                                historyStack.push({
			                                    index: mainPanelList.$focusItem.index,
			                                    path: upnpPath
			                                });
			
			                                // Read this device
			                                reRenderList(mainPanelList, constructUPnPDataOut(lst), 7, 7, listScroll, {focusIndex: 0});
			                            }
			                        });
			                    }
			                }
			            }
			        ];
			
			    !isUPnPEnabled && persistentItems.pop();
			
			    initUSBInfo.forEach(function ( item ) {
			        //debug.info(item, 'item');
			
			        lst.push({
			            items: [
			                {
			                    value: 'N',
			                    className: 'icon iconFileManager'
			                },
			                item.vendor || item.model
			            ],
			            value: {
			                sizeOverview: evalSpace(item.size, item.freeSize).overview,
			                freeSize: item.freeSize,
			                fullSize: item.size,
			                fsType: fsTypes[item.fsType],
			                deviceType: 8
			            },
			            click: function () {
			                // Set title (breadcrumbs)
			                mainPanelTitle.innerText = item.vendor || item.model;
			
			                historyStack.type = 1;
			                historyStack.currentDevice = item;
			                historyStack.entryPointIndex = mainPanelList.$focusItem.index;
			                historyStack.currentDeviceType = 8;
			
			                historyStack.stageType = 8;
			
			                historyStack.push({
			                    index: mainPanelList.$focusItem.index,
			                    path: item.mountPath
			                });
			
			                fs.readDir(getPathFromStack(), function (err, res) {
			                    historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			
			                    // Read and display additional information on the right panel.
			                    reRenderList(rightPanelDetails, renderDetailsList('1000100', [historyStack.currentDirFilesCount + '', null, null, null, evalSpace(item.size, item.freeSize).overview, null, null]), 5, 5, null, null);
			
			                    // Read this device
			                    reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, listScroll, {focusIndex: 0});
			
			                    page.footer.init([
			                        //f1: {title: gettext('delete'), action: function () {}},
			                        {type: 'f2', title: gettext('create folder'), action: function () {}},
			                        //f3: {title: gettext('add to favorites'), action: function () {}},
			                        {type: 'f4', title: gettext('search'), action: function () {}}
			                    ]);
			
			                    page.footer.show();
			                });
			            }
			        });
			    });
			
			    permanentSharesList.forEach(function ( item ) {
			        lst.push({
			            items: [
			                {
			                    value: 'V',
			                    className: 'icon iconFileManager'
			                },
			                item.name
			            ],
			            value: {
			                details: item,
			                deviceType: 5
			            },
			            click: function () {
			                historyStack.isDisplayShares = true;
			                historyStack.type = 1;
			                historyStack.currentDevice = item;
			                historyStack.currentDeviceType = 5;
			                historyStack.entryPointIndex = mainPanelList.$focusItem.index;
			                //historyStack.currentDirFilesCount = appStorage.favoritesStorage.length;
			
			                // Hide footer when go inside share
			                page.footer.hide();
			
			                smbTraverse('openShare', {
			                    address: item.address,
			                    folder: item.name,
			                    mountPath: SMB_MOUNT_PATH,
			                    login: item.login,
			                    password: item.password
			                }, {});
			            }
			        });
			    });
			
			    itemsToRender = persistentItems.concat(lst);
			    renderFilesListInitial.itemsLength = itemsToRender.length;
			    return itemsToRender;
			}
			
			
			/**
			 * Get actual resource path from stack navigation structure.
			 *
			 * @param {Object[]} stack custom stack structure instead of default
			 * @return {string} actual resource path
			 */
			function getPathFromStack ( stack ) {
			    return (stack || historyStack).reduce(function ( acc, item ) {
			        return acc + item.path + '/';
			    }, '');
			}
			
			// TODO: potentially its necessary to put focus on previously focused item
			function contentSort(type) {
			    var back = [folderListView[0]],
			        copyListFull = folderListView.slice(1),
			        listFiles;
			
			    //debug.inspect(copyListFull);
			
			    listFiles = copyListFull.filter(function ( item ) {
			        return item.value.type === 10;
			    });
			
			    switch ( type ) {
			        case 'NAME':
			            copyListFull.sort(function ( a, b ) {
			                return a.value.name.toLowerCase().localeCompare(b.value.name.toLowerCase());
			            });
			            reRenderList(mainPanelList, back.concat(copyListFull), 7, 7, listScroll, {focusIndex: 0});
			            break;
			        case 'DATE':
			            listFiles.sort(function ( a, b ) {
			                return a.value.item.last_modified - b.value.item.last_modified;
			            });
			            reRenderList(mainPanelList, back.concat(actualDirList, listFiles), 7, 7, listScroll, {focusIndex: 0});
			            break;
			        case 'TYPE':
			            listFiles.sort(function ( a, b ) {
			                return a.value.name.toLowerCase().localeCompare(b.value.name.toLowerCase());
			            });
			            reRenderList(mainPanelList, back.concat(actualDirList, listFiles), 7, 7, listScroll, {focusIndex: 0});
			            break;
			        case 'SIZE':
			            listFiles.sort(function ( a, b ) {
			                return a.value.item.size- b.value.item.size;
			            });
			            reRenderList(mainPanelList, back.concat(actualDirList, listFiles), 7, 7, listScroll, {focusIndex: 0});
			            break;
			    }
			}
			
			
			/**
			 * Search at current output by given string.
			 */
			function searchAtCurrentOutput () {
			    var currentStdout,
			        currentFilesRawSet,
			        back, re;
			
			    //debug.log(searchRequest, 'magenta');
			
			    if ( !searchRequest ) {
			        folderListView = originFolderListView.slice();
			        reRenderList(mainPanelList, folderListView, 7, 7, listScroll, {focusIndex: 0});
			
			        historyStack.currentDirFilesCount = folderListView.length - 1;
			        reRenderList(rightPanelDetails, renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			
			        return;
			    }
			
			    back = [folderListView[0]];
			    re = new RegExp(searchRequest, 'i');
			
			    // Searching is always by initial list.
			    // Save intermediate result.
			    currentStdout = originFolderListView.slice(1).filter(function ( item ) {
			        return re.test(item.value.name);
			    });
			
			    currentFilesRawSet = currentStdout.map(function ( item ) {
			        return item.value.item;
			    });
			
			    folderListView = back.concat(currentStdout);
			
			    reRenderList(mainPanelList, folderListView, 7, 7, listScroll, {focusIndex: 0});
			
			    historyStack.currentDirFilesCount = currentStdout.length;
			    reRenderList(rightPanelDetails, renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			
			    // Update files list with currently sorted (for valid files queue in player).
			    updateDirFilesByType(currentFilesRawSet);
			}
			
			
			/**
			 * Filter by types for current output.
			 *
			 * @param {Object[]} types
			 */
			function filterAtCurrentOutput ( types ) {
			    // FIXME: add filter option for network shares.
			    if ( !folderListView ) {
			        return;
			    }
			    var back = [folderListView[0]],
			        copyListFull = folderListView.slice(1),
			        currentFilesRawSet,
			        filtered;
			
			    //debug.inspect(copyListFull);
			
			    if ( !types.length ) {
			        searchAtCurrentOutput();
			    } else {
			        filtered = copyListFull.filter(function ( item ) {
			            return ~types.indexOf(+item.value.type) || ~types.indexOf(+item.value.item.type);
			        });
			
			        currentFilesRawSet = filtered.map(function ( item ) {
			            return item.value.item;
			        });
			
			        folderListView = back.concat(filtered);
			
			        reRenderList(mainPanelList, folderListView, 7, 7, listScroll, {focusIndex: 0});
			
			        // Update files list with currently sorted (for valid files queue in player).
			        updateDirFilesByType(currentFilesRawSet);
			
			        historyStack.currentDirFilesCount = filtered.length;
			        reRenderList(rightPanelDetails, renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			    }
			}
			
			
			/**
			 * Delete file objects.
			 */
			function removeFileSystemObject () {
			    var target = mainPanelList.$focusItem,
			        len = folderListView.length,
			        idx = target.index;
			
			    fs[target.value.type === 9 ? 'removeDir' : 'removeFile'](getPathFromStack() + mainPanelList.$focusItem.value.name, function ( err, res ) {
			        reRenderList(mainPanelList, (folderListView.splice(idx, 1), folderListView), 7, 7, listScroll, {focusIndex: len - 1 === idx ? idx - 1 : idx});
			        page.modalMessageActions.hide();
			    });
			}
			
			
			/**
			 * Render whole page (all panels + auxiliary nodes).
			 *
			 * @param {Object[]} data array of structured items for using in LayoutLists
			 */
			function renderPage( data ) {
			    var doc = document,
			        menuSortingItem,
			        inputFolderName,
			        inputSearchField,
			        filterItems,
			        btnCancel,
			        btnCreate,
			        smbAuthorizeModalList,
			        btnSambaAuthorize,
			        inputSMBShareLoginField,
			        inputSMBSharePasswdField;
			
			    filterItems = [
			        {state: false, title: gettext('folders'), value: 3},
			        {state: false, title: gettext('video'), value: 5},
			        {state: false, title: gettext('audio'), value: 6},
			        // {state: false, title: gettext('image'), value: 7},
			        {state: false, title: gettext('text'), value: 8},
			        {state: false, title: gettext('iso'), value: 9},
			        {state: false, title: gettext('playlists'), value: 10},
			        {state: false, title: gettext('cue'), value: 11},
			        {state: false, title: gettext('records'), value: 12}
			    ];
			
			    // FIXME: fix path for files in favorites
			    function addEntityToFavorites () {
			        //debug.inspect(Object.keys(mainPanelList.$focusItem));
			        var targetNode = mainPanelList.$focusItem,
			            favoriteItem = folderListView[targetNode.index],
			            idx = targetNode.index;
			
			        if ( favoriteItem.value.isFavorite ) {
			            // Remove item from session storage.
			            appStorage.favoritesStorage = appStorage.favoritesStorage.filter(function ( item ) {
			                return item.path !== getPathFromStack() + favoriteItem.value.name;
			            });
			
			            favoriteItem.items[2].value = 'B';
			            favoriteItem.items[2].className = 'icon iconFileManager favoritesStar theme-favorite-icon';
			            favoriteItem.value.isFavorite = false;
			        } else {
			            //debug.log(getPathFromStack() + favoriteItem.value.name, 'magenta');
			            // Add item to session storage.
			            appStorage.favoritesStorage.push({
			                path: getPathFromStack() + favoriteItem.value.name,
			                type: favoriteItem.value.type,
			                name: favoriteItem.value.name,
			                size: favoriteItem.value.item.size,
			                last_modified: favoriteItem.value.item.last_modified
			            });
			
			            favoriteItem.items[2].value = 'A';
			            favoriteItem.items[2].className += ' theme-favorite-icon-active';
			            favoriteItem.value.isFavorite = true;
			        }
			
			        reRenderList(mainPanelList, folderListView.slice(0, idx).concat(favoriteItem, folderListView.slice(idx + 1)), 7, 7, listScroll, {focusIndex: idx});
			    }
			
			    page.footer = new Footer({
			        parent: page,
			        data: []
			    });
			
			    listScroll = new ScrollBar({
			        value: 0,
			        viewSize: 7
			    });
			
			    filterScroll = new ScrollBar({
			        value: 0,
			        viewSize: 5,
			        realSize: 9
			    });
			
			    btnCancel = new Button({
			        value: gettext('cancel'),
			        className: 'cancelBtn inactiveBtn'
			    });
			
			    btnCreate = new Button({
			        value: gettext('create'),
			        className: 'createBtn'
			    });
			
			    btnSambaAuthorize = new Button({
			        value: gettext('authorize'),
			        className: 'authorizeBtn'
			    });
			
			    btnCreate.status = 1;
			    btnCancel.status = 0;
			
			    inputFolderName = new Input({
			        //placeholder: gettext('new folder'),
			        value: gettext('new folder'),
			        events: {
			            keydown: function ( event ) {
			                var caretStatusZero = !this.value;
			
			                Input.prototype.defaultEvents.keydown.call(this, event);
			
			                switch ( event.code ) {
			                    case keys.back:
			                        if ( caretStatusZero ) {
			                            closeModalWindow();
			                            mainPanelSet.show();
			                        }
			                        event.stop = true;
			
			                        break;
			                    case keys.ok:
			                        btnCreate.emit('click');
			                        break;
			                    case keys.down:
			                        inputFolderName.blur();
			                        (btnCreate.status ? btnCreate : btnCancel).focus();
			                }
			            }
			        }
			    });
			
			    inputSearchField = new Input({
			        placeholder: gettext('search'),
			        events: {
			            keydown: function ( event ) {
			                Input.prototype.defaultEvents.keydown.call(this, event);
			
			                switch ( event.code ) {
			                    case keys.back:
			                        event.stop = true;
			                        break;
			                    case keys.ok:
			                        this.parent.emit('click');
			
			                        if ( this.value.trim() === '' ) {
			                            this.setValue('');
			                        }
			
			                        // Do nothing if there are no items for searching.
			                        if ( !historyStack.type || historyStack.type === 2 ) {
			                            return;
			                        }
			
			                        // Set search request globally in shared variable.
			                        searchRequest = this.value.trim();
			                        searchAtCurrentOutput();
			                        break;
			                }
			            }
			        }
			    });
			
			    inputSMBShareLoginField = new Input({
			        placeholder: gettext('login'),
			        events: {
			            keydown: function ( event ) {
			                var caretStatusZero = !this.value;
			
			                Input.prototype.defaultEvents.keydown.call(this, event);
			
			                switch ( event.code ) {
			                    case keys.back:
			                        if ( caretStatusZero ) {
			                            closeModalWindow();
			                        }
			                        event.stop = true;
			
			                        break;
			                    case keys.ok:
			                        this.parent.emit('click');
			                        break;
			                    case keys.down:
			                        inputSMBShareLoginField.blur();
			                        inputSMBSharePasswdField.focus();
			                }
			            }
			        }
			    });
			
			    inputSMBSharePasswdField = new Input({
			        placeholder: gettext('password'),
			        events: {
			            keydown: function ( event ) {
			                var caretStatusZero = !this.value;
			
			                Input.prototype.defaultEvents.keydown.call(this, event);
			
			                switch ( event.code ) {
			                    case keys.back:
			                        if ( caretStatusZero ) {
			                            closeModalWindow();
			                        }
			                        event.stop = true;
			                        break;
			                    case keys.ok:
			                        this.parent.emit('click');
			                        break;
			                    case keys.up:
			                        inputSMBShareLoginField.focus();
			                        break;
			                    case keys.down:
			                        btnSambaAuthorize.focus();
			                }
			            }
			        }
			    });
			
			    // It seems it's only possible way for now to set arbitrary attribute to INPUT.
			    inputSearchField.$node.setAttribute('maxlength', 20);
			
			    page.modalMessageSambaAuthorize = new ModalMessage({
			        title: gettext('authorization'),
			        events: {
			            show: function () {
			                inputSMBShareLoginField.focus();
			                isModalShown = true;
			
			                page.footer.hide();
			            },
			            hide: function () {
			                page.footer.show();
			            }
			        },
			        children: [
			            smbAuthorizeModalList = new LayoutList({
			                cycle: true,
			                size: 2,
			                focusIndex: 0,
			                data: [
			                    {
			                        items: [
			                            inputSMBShareLoginField
			                        ]
			                    },
			                    {
			                        items: [
			                            inputSMBSharePasswdField
			                        ]
			                    }
			                ],
			                events: {
			                    overflow: function ( evt ) {
			                        if ( evt.direction === keys.down) {
			                            this.blurItem(this.$focusItem);
			                            btnSambaAuthorize.focus();
			                        }
			                    }
			                }
			            }),
			            btnSambaAuthorize
			        ]
			    });
			
			    page.modalMessageSort = new ModalMessage({
			        title: gettext('sorting'),
			        events: {
			            show: function () {
			                page.modalMessageSort.children[0].focus();
			                isModalShown = true;
			            }
			        },
			        children: [
			            new RadioList({
			                cycle: true,
			                focusIndex: 0,
			                size: 4,
			                events: {
			                    'click:item': function ( evt ) {
			                        var v = evt.$item.value;
			                        contentSort(v === 1 ? 'NAME' : v === 2 ? 'DATE' : v === 3 ? 'TYPE' : 'SIZE');
			                        //debug.log(evt.$item.data.title);
			                        menuSortingItem.innerText = evt.$item.data.title;
			                        page.modalMessageSort.hide();
			                        mainPanelSet.focus();
			                        leftPanelMenu.focus();
			                    }
			                },
			                data: [
			                    {
			                        state: true,
			                        title: gettext('by name'),
			                        value: 1
			                    },
			                    {
			                        state: false,
			                        title: gettext('by date'),
			                        value: 2
			                    },
			                    {
			                        state: false,
			                        title: gettext('by type'),
			                        value: 3
			                    },
			                    {
			                        state: false,
			                        title: gettext('by size'),
			                        value: 4
			                    }
			                ]
			            })
			        ]
			    });
			
			    page.modalMessageFilter = new ModalMessage({
			        title: gettext('filtering'),
			        events: {
			            show: function () {
			                page.modalMessageFilter.children[0].focus();
			                isModalShown = true;
			            }
			        },
			        children: [
			            filterMenuList = new CheckList({
			                    cycle: true,
			                    focusIndex: 0,
			                    size: 5,
			                    data: filterItems
			                    //scroll: filterScroll
			            })
			        ]
			    });
			
			    page.modalMessageActions = new ModalMessage({
			        title: gettext('actions'),
			        events: {
			            show: function () {
			                page.modalMessageActions.focus();
			                mainPanelSet.blur();
			                isModalShown = true;
			            },
			            hide: function () {
			                mainPanelSet.focus();
			                isModalShown = false;
			            }
			        },
			        children: [
			            ctxMenuList = new LayoutList({
			                cycle: true,
			                focusIndex: 0,
			                size: 0,
			                data: []
			            })
			        ]
			    });
			
			    page.modalMessageCreateFolder = new ModalMessage({
			        title: gettext('create folder'),
			        events: {
			            show: function () {
			                inputFolderName.focus();
			                isModalShown = true;
			
			                page.footer.hide();
			            },
			            hide: function () {
			                mainPanelSet.show();
			                page.footer.show();
			            }
			        },
			        children: [
			            inputFolderName,
			            btnCancel,
			            btnCreate
			        ]
			    });
			
			    page.add(page.footer);
			
			    page.add(page.modalMessageSort);
			    page.add(page.modalMessageFilter);
			    page.add(page.modalMessageActions);
			    page.add(page.modalMessageCreateFolder);
			    page.add(page.modalMessageSambaAuthorize);
			
			    page.add(new Clock());
			
			    page.add(
			        mainPanelSet = new PanelSet({
			            panels: [
			                leftPanel = new Panel({
			                    size: 1,
			                    title: gettext('menu'),
			                    children: [
			                        leftPanelMenu = new LayoutList({
			                            cycle: true,
			                            size: 7,
			                            focusIndex: 0,
			                            data: [
			                                {
			                                    items: [
			                                        {
			                                            value: 'J',
			                                            className: 'icon iconFileManager'
			                                        },
			                                        inputSearchField
			                                    ]
			                                },
			                                {
			                                    items: [
			                                        {
			                                            value: 'I',
			                                            className: 'icon iconFileManager'
			                                        },
			                                        gettext('by name') // Default state
			                                    ],
			                                    click: function () {
			                                        // Do nothing if there are no items for searching.
			                                        if ( !historyStack.type || historyStack.type === 2 ) {
			                                            return;
			                                        }
			
			                                        mainPanelSet.blur();
			                                        page.modalMessageSort.show();
			                                        page.modalMessageSort.focus();
			                                    }
			                                },
			                                {
			                                    items: [
			                                        {
			                                            value: 'K',
			                                            className: 'icon iconFileManager'
			                                        },
			                                        gettext('all')
			                                    ],
			                                    click: function () {
			                                        var rootElem, availTypes, currentTypes;
			
			                                        if ( !historyStack.type ) {
			                                            return;
			                                        }
			
			                                        // Show only available types in filter menu
			                                        availTypes = Object.keys(dirFilesByType);
			                                        currentTypes = filterItems.filter(function ( item ) {
			                                            return ~availTypes.indexOf(String(item.value));
			                                        });
			
			                                        if ( actualDirList.length ) {
			                                            currentTypes.unshift(filterItems[0]);
			                                        }
			
			                                        filterMenuList.init({
			                                            data: currentTypes,
			                                            size: currentTypes.length,
			                                            focusIndex: 0,
			                                            scroll: currentTypes.length < 6 ? null : filterScroll
			                                        });
			
			                                        mainPanelSet.blur();
			                                        page.modalMessageFilter.show();
			                                        page.modalMessageFilter.focus();
			                                    }
			                                }
			                            ]
			                        })
			                    ]
			                }),
			                mainPanel = new Panel({
			                    size: 1,
			                    title: gettext('files'),
			                    main: true,
			                    children: !page.loaded && [
			                        mainPanelList = new LayoutList({
			                            cycle: true,
			                            focusIndex: 0,
			                            size: 7,
			                            data: renderFilesListInitial(),
			                            scroll: listScroll,
			                            noData: ''
			                        })
			                    ]
			                }),
			                rightPanel = new Panel({
			                    focusable: true,
			                    size: 1,
			                    title: gettext('details'),
			                    children: [
			                        rightPanelDetails = new LayoutList({
			                            size: 5,
			                            focusable: false,
			                            // "1" --> switch on item, "0" --> switch off item
			                            data: renderDetailsList('0000000', [null, null, null, null, null, null, null])
			                        })
			                    ]
			                })
			            ]
			        })
			    );
			
			    menuSortingItem = leftPanelMenu.$body.children[1].children[0].children[1];
			    menuFilteringItem = leftPanelMenu.$body.children[2].children[0].children[1];
			
			    doc.querySelector('.panelEx.right').classList.add('denseRightDisplay');
			    mainPanel.$body.appendChild(listScroll.$node);
			    //filterMenuList.$node.appendChild(filterScroll.$node);
			    // FIXME: add scroll
			    //rightPanel.$body.appendChild(listScroll.$node);
			
			    if ( renderFilesListInitial.itemsLength <= 7 ) {
			        listScroll.$node.style.display = 'none';
			    }
			
			    mainPanelTitle = mainPanel.$title;
			
			    mainPanelSet.drawShadow();
			    mainPanel.focus();
			
			    reRenderList(rightPanelDetails, constructFavoritesInfo(), 6, 6, null, null);
			
			    /*
			    ------------------------------------------------------------------------------
			
			        Listeners
			
			    ------------------------------------------------------------------------------
			    */
			
			
			    filterMenuList.addListener('keydown', function ( evt ) {
			        var types = [],
			            title = [];
			
			        if ( evt.code === keys.back ) {
			            Array.prototype.forEach.call(filterMenuList.$body.children, function ( item ) {
			                if ( item.state ) {
			                    types.push(item.data.value);
			                    title.push(item.data.title);
			                }
			            });
			
			            // Apply filters
			            filterAtCurrentOutput ( types );
			
			            menuFilteringItem.innerText = title.length ? title.join(', ') : gettext('all');
			        }
			    });
			
			    // Handle "Menu" action for files/folders file system operations.
			    mainPanelList.addListener('keydown', function ( evt ) {
			        var targetNode, rootElem;
			
			        if ( historyStack.type ) {
			            targetNode = mainPanelList.$focusItem;
			            rootElem = ctxMenuList.$body;
			            if ( historyStack.type === 1 ) {
			                if ( evt.code === keys.menu ) {
			                    if ( !targetNode.data.value ) {
			                        if ( historyStack.isDisplayFavorites ) {
			                            return;
			                        }
			
			                        ctxMenuList.init({
			                            data: renderCtxMenuList('00100'),
			                            size: 1,
			                            focusIndex: 0
			                        });
			                    } else {
			                        ctxMenuList.init({
			                            data: renderCtxMenuList('11110'),
			                            size: 4,
			                            focusIndex: 0
			                        });
			
			                        // DS();
			                        // debug.info(targetNode.data, 'targetNode.data');
			
			                        if ( targetNode.data.value.type === 10 ) {
			                            rootElem.children[0].classList.remove('hidden');
			                            ctxMenuList.init({
			                                data: renderCtxMenuList('11110'),
			                                size: 4,
			                                focusIndex: 0
			                            });
			                        }
			
			                        if ( targetNode.data.value.isFavorite ) {
			                            rootElem.children[0].children[0].children[1].innerText = gettext('remove from favorites');
			                        } else {
			                            rootElem.children[0].children[0].children[1].innerText = gettext('add to favorites');
			                        }
			
			                        if ( historyStack.isDisplayFavorites ) {
			                            rootElem.children[0].children[0].children[1].innerText = gettext('remove from favorites');
			                            ctxMenuList.init({
			                                data: renderCtxMenuList('11000'),
			                                size: 2,
			                                focusIndex: 0
			                            });
			                        }
			                    }
			
			                    page.modalMessageActions.show();
			
			                    page.footer.hide();
			                } else if ( evt.code === keys.f1 ) {
			                    if ( historyStack.isDisplayFavorites || !targetNode.data.value ) {
			                        return;
			                    }
			
			                    removeFileSystemObject();
			                } else if ( evt.code === keys.f2 ) {
			                    if ( historyStack.isDisplayFavorites ) {
			                        return;
			                    }
			
			                    mainPanelSet.hide();
			                    page.modalMessageCreateFolder.show();
			                    page.modalMessageCreateFolder.focus();
			                } else if ( evt.code === keys.f3 ) {
			                    if ( !targetNode.data.value ) {
			                        return;
			                    } else if ( historyStack.isDisplayFavorites ) {
			                        // Remove item from session storage.
			                        appStorage.favoritesStorage = appStorage.favoritesStorage.filter(function ( item ) {
			                            return item.path !== targetNode.data.value.item.path;
			                        });
			                        historyStack.currentDirFilesCount = appStorage.favoritesStorage.length;
			                        reRenderList(mainPanelList, constructDataOut({dirs: [], files: appStorage.favoritesStorage}, 1), 7, 7, listScroll, {
			                            focusIndex: targetNode.index >= appStorage.favoritesStorage.length ? targetNode.index - 1 : targetNode.index
			                        });
			                    } else {
			                        if ( targetNode.data.value.type === 9 || targetNode.data.value.type === 10 ) {
			                            addEntityToFavorites();
			                        }
			                    }
			                } else if ( evt.code === keys.f4 ) {
			                    leftPanel.focus();
			                    inputSearchField.focus();
			                }
			            } else if ( historyStack.type === 2 && historyStack.last().type === 2.2 ) {
			                if ( evt.code === keys.menu ) {
			                    ctxMenuList.init({
			                        data: renderCtxMenuList('00001'),
			                        size: 1,
			                        focusIndex: 0
			
			                    });
			
			                    if ( mainPanelList.$focusItem.index ) {
			                        page.modalMessageActions.show();
			                    }
			                }
			            }
			        }
			    });
			
			    (function () {
			        var delay = core.environment.onFocusDebounceTimeout,
			            footerButtons = [],
			            timerId;
			
			            mainPanelList.addListener('focus:item', function () {
			                var target = mainPanelList.$focusItem,
			                    fileSystemObject;
			
			                if ( folderListView ) {
			                    fileSystemObject = folderListView[target.index];
			                    if ( fileSystemObject && fileSystemObject.value ) {
			                        clearTimeout(timerId);
			                        timerId = setTimeout(function () {
			                            if ( fileSystemObject.value.type === 10 ) {
			                                reRenderList(rightPanelDetails,
			                                    renderDetailsList('0111000', [null, fileSystemObject.value.name, new Date(fileSystemObject.value.item.last_modified).toLocaleDateString(), evalSpace(fileSystemObject.value.item.size).singleObject, null, null, null]), 5, 5, null, null);
			                                footerButtons = [
			                                    {type: 'f3', title: gettext('add to favorites'), action: function () {}},
			                                    {type: 'f4', title: gettext('search'), action: function () {}}
			                                ];
			
			                                !historyStack.isDisplayFavorites && footerButtons.unshift({type: 'f2', title: gettext('create folder'), action: function () {}});
			                                !historyStack.isDisplayFavorites && footerButtons.unshift({type: 'f1', title: gettext('delete'), action: function () {}});
			
			                                page.footer.init(footerButtons);
			                            } else {
			                                reRenderList(rightPanelDetails,
			                                    renderDetailsList('0100000', [null, fileSystemObject.value.name, null, null, null, null, null]), 5, 5, null, null);
			                                page.footer.init([
			                                    {type: 'f1', title: gettext('delete'), action: function () {}},
			                                    {type: 'f2', title: gettext('create folder'), action: function () {}},
			                                    {type: 'f4', title: gettext('search'), action: function () {}}
			                                ]);
			                            }
			                        }, delay);
			                    } else {
			                        reRenderList(rightPanelDetails,
			                            renderDetailsList('1000000', [historyStack.currentDirFilesCount + '', null, null, null, null, null, null]), 5, 5, null, null);
			
			                        footerButtons = [
			                            {type: 'f4', title: gettext('search'), action: function () {}}
			                        ];
			
			                        !historyStack.isDisplayFavorites && footerButtons.unshift({type: 'f2', title: gettext('create folder'), action: function () {}});
			
			                        page.footer.init(footerButtons);
			                    }
			                }
			
			                if ( !historyStack.type ) {
			                    clearTimeout(timerId);
			                    timerId = setTimeout(function () {
			                        if ( target.data.value && target.data.value.deviceType === 8 ) {
			                            reRenderList(rightPanelDetails,
			                                renderDetailsList('0000110', [null, null, null, null, target.data.value.sizeOverview, target.data.value.fsType, null]), 3, 3, null, null);
			
			                            drawStorageSpaceDiagram({size: target.data.value.fullSize, free: target.data.value.freeSize });
			
			                            page.footer.hide();
			                        } else if ( target.data.value && target.data.value.deviceType === 5 ) {
			                            reRenderList(rightPanelDetails, constructShareInfo(target.data.value), 6, 6, null, null);
			
			                            // Show footer when we have at least one share
			                            // Necessary for realize Delete and Rename options for shares
			                            page.footer.init([
			                                {type: 'f1', title: gettext('delete'), action: function () { removeEntityFromPermanentShares(target); }},
			                                {type: 'f4', title: gettext('rename'), action: function () {}}
			                            ]);
			                            page.footer.show();
			                        } else if ( target.data.value && target.data.value.type === 1 ) {
			                            reRenderList(rightPanelDetails, constructFavoritesInfo(), 6, 6, null, null);
			                        } else {
			                            reRenderList(rightPanelDetails,
			                                renderDetailsList('0000000', [null, null, null, null, null, null, null]), 5, 5, null, null);
			                        }
			                    }, delay);
			                }
			            });
			    }());
			
			    mainPanelList.addListener('move:view', function () {
			        mainPanelList && mainPanelList.$focusItem && mainPanelList.emit('focus:item');
			    });
			
			    btnCreate.addListener('click', function () {
			        var nameToSet = inputFolderName.value.slice(0, 255),
			            re = /^(?:[\w\d]+[\w\d ]*)+$/,
			            path;
			
			        if ( !nameToSet.trim() ) {
			            echo({
			                title: gettext('Folder name can not be empty'),
			                icon: 'info'
			            });
			        } else if ( !re.test(nameToSet) ) {
			            echo({
			                title: gettext('Sorry, could not set folder name to') + ' "' + nameToSet + '". ' + gettext('Unsupported symbols'),
			                icon: 'info'
			            });
			        } else {
			            path = getPathFromStack() + nameToSet;
			
			            fs.isFolderExist(path, function ( err, res ) {
			                if ( res ) {
			                    echo({
			                        title: gettext('Folder with this name already exists'),
			                        icon: 'info'
			                    });
			                } else {
			                    fs.createDir(path, function ( err, res ) {
			                        reRenderList(mainPanelList, (folderListView.splice(mainPanelList.$focusItem.index + 1, 0, {
			                            items: [
			                                {
			                                    value: 'R',
			                                    className: 'icon iconFileManager'
			                                },
			                                {
			                                    value: nameToSet,
			                                    className: 'listDirNameField'
			                                },
			                                {
			                                    value: 'B',
			                                    className: 'icon iconFileManager favoritesStar'
			                                }
			                            ],
			                            value: {
			                                name: nameToSet,
			                                type: 9
			                            },
			                            click: function () {
			                                historyStack.push({
			                                    index: mainPanelList.$focusItem.index,
			                                    path: nameToSet
			                                });
			
			                                fs.readDir(getPathFromStack(), function ( err, res ) {
			                                    historyStack.currentDirFilesCount = res.dirs.length + res.files.length;
			                                    reRenderList(mainPanelList, constructDataOut(res, 1), 7, 7, null, {focusIndex: 0});
			                                    reRenderList(rightPanelDetails, renderDetailsList('1000100', [historyStack.currentDirFilesCount + '', null, null, null, evalSpace(historyStack.currentDevice.size, historyStack.currentDevice.freeSize).overview, null, null]), 5, 5, null, null);
			                                });
			                            }
			                        }), folderListView), 7, 7, listScroll, {focusIndex: mainPanelList.$focusItem.index + 1});
			                    });
			
			                    closeModalWindow();
			
			                    // FIXME: details in the right panel fails.
			                    //DS();
			                }
			            });
			        }
			    });
			
			    btnCancel.addListener('click', function () {
			        closeModalWindow();
			    });
			
			    btnCreate.addListener('keydown', function ( evt ) {
			        if ( evt.code === keys.up ) {
			            inputFolderName.focus();
			        } else if ( evt.code === keys.left ) {
			            btnCancel.focus();
			            this.$body.classList.add('inactiveBtn');
			            btnCancel.$body.classList.remove('inactiveBtn');
			
			            btnCreate.status = 0;
			            btnCancel.status = 1;
			        }
			    });
			
			    btnCancel.addListener('keydown', function ( evt ) {
			        if ( evt.code === keys.up ) {
			            inputFolderName.focus();
			        } else if ( evt.code === keys.right ) {
			            btnCreate.focus();
			            this.$body.classList.add('inactiveBtn');
			            btnCreate.$body.classList.remove('inactiveBtn');
			
			            btnCreate.status = 1;
			            btnCancel.status = 0;
			        }
			    });
			
			    btnSambaAuthorize.addListener('click', function () {
			        //debug.inspect(mainPanelList.$focusItem.data);
			        var login = inputSMBShareLoginField.value,
			            passwd = inputSMBSharePasswdField.value,
			            targetItem = mainPanelList.$focusItem.data,
			            itemData = targetItem.value.details || targetItem.value.item,
			            path = {address: itemData.address, folder: itemData.name, login: login, password: passwd, mountPath: SMB_MOUNT_PATH};
			
			        //debug.info(path, 'btnSambaAuthorize CLICK');
			
			        itemData.login = login;
			        itemData.password = passwd;
			
			        //debug.inspect(targetItem);
			
			        smbTraverse('openShare', path, {});
			    });
			
			    btnSambaAuthorize.addListener('keydown', function ( evt ) {
			        if ( evt.code === keys.up ) {
			            smbAuthorizeModalList.focus();
			            smbAuthorizeModalList.focusIndex(1);
			        }
			    });
			
			    // FIXME: implement new events.
			    window.addEventListener('keydown', function ( evt ) {
			        if ( evt.keyCode === 80 ) {
			            initUSBInfo = fs.getMountPoints();
			
			            if ( !historyStack.type ) {
			                reRenderList(mainPanelList, renderFilesListInitial(), 7, 7, listScroll, mainPanelList.$focusItem ? {focusIndex: mainPanelList.$focusItem.index} : null);
			            }
			
			            echo({
			                title: gettext('New device is connected'),
			                icon: 'info'
			            });
			
			        } else if ( evt.keyCode === 81 ) {
			            initUSBInfo = fs.getMountPoints();
			
			            if ( !historyStack.type ) {
			                reRenderList(mainPanelList, renderFilesListInitial(), 7, 7, listScroll, {
			                    focusIndex: renderFilesListInitial.itemsLength === historyStack.entryPointIndex ? historyStack.entryPointIndex - 1 : historyStack.entryPointIndex
			                });
			            } else if ( historyStack.stageType === 8 ) {
			                fs.isFolderExist(historyStack.currentDevice.mountPath, function ( err, res ) {
			                    var idx, items;
			
			                    if ( !res ) {
			                        items = renderFilesListInitial();
			                        idx = historyStack.entryPointIndex;
			                        // last = historyStack.last;
			                        //
			                        // historyStack = [];
			                        // folderListView = null;
			                        // historyStack.type = 0;
			                        // historyStack.last = last;
			
			                        resetStack();
			
			                        page.footer.hide();
			
			                        reRenderList(mainPanelList, items, 7, 7, listScroll, {
			                            focusIndex: renderFilesListInitial.itemsLength === idx ? idx - 1 : idx
			                        });
			                    }
			                });
			            }
			
			            echo({
			                title: 'Device is disconnected',
			                icon: 'info'
			            });
			        }
			    });
			}
			
			
			/**
			 * Draw diagram for free/allocated space on disk, set computed styles.
			 *
			 * @param {Object} data
			 */
			function drawStorageSpaceDiagram ( data ) {
			    var cntCanvas = doc.createElement('div'),
			        canvasElem = doc.createElement('canvas'),
			        percentBlock = cntCanvas.cloneNode(false),
			        ctx = canvasElem.getContext('2d'),
			        colorsForDiagram = detectColorsForDiagram(),
			        radius = 0,
			        vals = [data.size - data.free, data.free],
			        myTotal = 0,
			        myColor = [colorsForDiagram.inUseSpace, colorsForDiagram.freeSpace],
			        i, len, el, txtAlloc, txtFree, cntIndicators, freeSpaceElem, allocSpaceElem;
			
			    function detectColorsForDiagram() {
			        var inUseFillElem = doc.querySelector('.component.list .item.focus'),
			            freeFillElem = doc.querySelector('.panelEx.main'),
			            inUseColor = window.getComputedStyle(inUseFillElem).getPropertyValue('background-color'),
			            freeColor = window.getComputedStyle(freeFillElem).getPropertyValue('background-color'),
			            text = window.getComputedStyle(freeFillElem).getPropertyValue('color');
			
			        freeColor = freeColor.replace(/(?:0\.)?\d+/g, function ( s ) { return ~~(s * 1.2); });
			
			        return {
			            inUseSpace: inUseColor,
			            freeSpace: freeColor,
			            text: text
			        };
			    }
			
			    if ( !drawStorageSpaceDiagram.indicators ) {
			        freeSpaceElem = cntCanvas.cloneNode(false);
			        allocSpaceElem = cntCanvas.cloneNode(false);
			        cntIndicators = cntCanvas.cloneNode(false);
			
			        txtAlloc = doc.createTextNode(gettext('in use'));
			        txtFree = doc.createTextNode(gettext('free'));
			
			        cntIndicators.className = 'cntIndicators';
			        allocSpaceElem.className = 'allocSpaceElem';
			        freeSpaceElem.className = 'freeSpaceElem';
			
			        allocSpaceElem.style.backgroundColor = myColor[0];
			        freeSpaceElem.style.backgroundColor = myColor[1];
			
			        cntIndicators.appendChild(allocSpaceElem);
			        cntIndicators.appendChild(txtAlloc);
			        cntIndicators.appendChild(freeSpaceElem);
			        cntIndicators.appendChild(txtFree);
			
			        rightPanel.$body.children[0].appendChild(cntIndicators);
			
			        drawStorageSpaceDiagram.indicators = true;
			    }
			
			    if ( drawStorageSpaceDiagram.diagram ) {
			        doc.querySelector('.infoStorageDiagram').parentNode.removeChild(drawStorageSpaceDiagram.diagram);
			        drawStorageSpaceDiagram.diagram = null;
			    }
			
			    cntCanvas.className = 'infoStorageDiagram';
			
			    percentBlock.innerText = ~~((data.size - data.free) * 100 / data.size) + '%';
			    percentBlock.style.color = colorsForDiagram.text;
			
			    cntCanvas.appendChild(canvasElem);
			    cntCanvas.appendChild(percentBlock);
			    rightPanel.$body.children[0].appendChild(cntCanvas);
			
			    for ( i = 0, len = vals.length; i < len; i += 1 ) {
			        myTotal += vals[i];
			    }
			
			    canvasElem.width = canvasElem.height = rightPanel.$node.offsetHeight / 2.4;
			
			    for ( i = 0, len = vals.length; i < len; i += 1 ) {
			        ctx.fillStyle = myColor[i];
			        ctx.beginPath();
			        ctx.moveTo(canvasElem.width / 2, canvasElem.height / 2);
			        ctx.arc(canvasElem.width / 2, canvasElem.height / 2, canvasElem.height / 2, radius, radius + (Math.PI * 2 * (vals[i] / myTotal)), false);
			        ctx.lineTo(canvasElem.width / 2, canvasElem.height / 2);
			        ctx.fill();
			        radius += Math.PI * 2 * (vals[i] / myTotal);
			    }
			
			    drawStorageSpaceDiagram.diagram = cntCanvas;
			}
			
			
			/**
			 * Helper function for closing modal window.
			 */
			function closeModalWindow () {
			    page.modalMessageSort.hide();
			    page.modalMessageFilter.hide();
			    page.modalMessageActions.hide();
			    page.modalMessageCreateFolder.hide();
			    page.modalMessageSambaAuthorize.hide();
			    mainPanelSet.focus();
			    isModalShown = false;
			    smbTraverse.openShareAttempts = 0;
			}
			
			page.addListener('show', function () {
			    if ( !page.loaded ) {
			        ufsJson.load(function ( state, data ) {
			            if ( data ) {
			                appStorage.favoritesStorage = data.favoritesStorage || [];
			                appStorage.sharesStorage = data.sharesStorage || [];
			
			                if ( appStorage.sharesStorage.length ) {
			                    extractServersFromPermanentShares();
			                }
			            } else {
			                appStorage.favoritesStorage = [];
			                appStorage.sharesStorage = [];
			            }
			        });
			
			        setTimeout(function () {
			            renderPage([]);
			            page.loaded = 1;
			        }, 0);
			    }
			});
			
			page.addListener('keydown', function ( event ) {
			    var evt;
			
			    switch ( event.code ) {
			        case keys.back:
			            if ( isModalShown ) {
			                closeModalWindow();
			            } else if ( historyStack.length || historyStack.isDisplayFavorites ) {
			                if ( historyStack.type === 2 && historyStack.length === 2 ) {
			                    historyStack.pop();
			                    smbTraverse('getGroups', null, {icon: 'T', index: historyStack.last().index});
			                } else {
			                    // evt = doc.createEvent('Event');
			                    // evt.initEvent('click', true, true);
			                    // mainPanelList.$body.children[0].dispatchEvent(evt);
			                    goBack();
			                }
			            } else {
			                mainPanelSet.blur();
			                app.exit(function ( result ) {
			                    if ( !result ) {
			                        mainPanelSet.focus();
			                    }
			                    ufsJson.saveSync(appStorage);
			                });
			            }
			            break;
			    }
			});
			
			module.exports = page;
			
			
			/*
			 ------------------------------------------------------------------------------
			
			        Debug helpers
			
			 ------------------------------------------------------------------------------
			 */
			
			// Debug stack
			function DS() {
			    debug.log('|----------------historyStack----------------|', 'yellow');
			    debug.inspect(historyStack);
			    debug.inspect(Object.keys(historyStack));
			    debug.log('----------------------------------------------', 'yellow');
			}
			
			// Mark
			function Z() {
			    debug.log('zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'cyan');
			    debug.log('zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz');
			    debug.log('zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'cyan');
			}


/***/ },
/* 21 */
/*!*************************************!*\
  !*** ./app/js/stb/develop/debug.js ***!
  \*************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * Logger.
			 *
			 * @module stb/develop/debug
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint new-cap: 0 */
			
			var host      = __webpack_require__(/*! ../app */ 3).data.host,
			    config    = __webpack_require__(/*! ../../../../config/logger */ 43),
			    util      = __webpack_require__(/*! util */ 6),
			    buffer    = [],
			    timeMarks = {},  // storage for timers (debug.time, debug.timeEnd)
			    socket;
			
			
			// enable colors in console
			__webpack_require__(/*! tty-colors */ 53);
			
			
			(function connect () {
			    if ( !config.active || !host ) {
			        return;
			    }
			
			    socket = new WebSocket('ws://' + location.hostname + ':' + config.port);
			
			    socket.onclose = function () {
			        setTimeout(function () {
			            connect();
			        }, 5000);
			    };
			})();
			
			
			/**
			 * Wrapper to dump message locally and remotely.
			 *
			 * @param {string} message data to output and send
			 */
			function log ( message ) {
			    gSTB.Debug(message);
			
			    if ( config.active ) {
			        buffer.push(message);
			        if ( socket && socket.readyState === socket.OPEN ) {
			            socket.send(JSON.stringify(buffer));
			            buffer = [];
			        }
			    }
			}
			
			
			/**
			 * Global object to output logs
			 * @namespace
			 * @global
			 */
			module.exports = window.debug = {
			
			    /**
			     * Check condition and warn if not match.
			     *
			     * @param {boolean} condition should be true if okay
			     * @param {string} title description of the problem
			     */
			    assert: function ( condition, title ) {
			        if ( !condition ) {
			            if ( host ) {
			                log(('Assertion failed: ' + title).red);
			            } else {
			                console.assert(condition, title);
			            }
			        }
			    },
			
			
			    /**
			     * Print a plain colored string.
			     *
			     * @param {*} message data to output
			     * @param {string} [color='black'] colour to set
			     */
			    log: function ( message, color ) {
			        message = (message + '') || '(empty message)';
			        if ( host ) {
			            log(message[color || 'white']);
			        } else {
			            console.log('%c%s', 'color:' + (color || 'black'), message);
			        }
			    },
			
			
			    /**
			     * Print the given var with caption.
			     *
			     * @param {*} data data to output
			     * @param {string} [title] optional caption
			     */
			    info: function ( data, title ) {
			        var type = Object.prototype.toString.call(data).match(/\s([a-zA-Z]+)/)[1].toLowerCase(),
			            args;
			
			        if ( host ) {
			            // prepare
			            if ( data instanceof Object || Array.isArray(data) ) {
			                // complex object
			                data = data.nodeName ? data.outerHTML : JSON.stringify(data, null, 4);
			            }
			            // combine all together and print result
			            log((type === 'error' ? type.red : type.green) + '\t' + (title ? title.bold + ':\t'.green : '') + data);
			        } else {
			            args = ['color:' + (type === 'error' ? 'red' : 'green'), type];
			            if ( title ) {
			                args.unshift('%c%s\t%c%s\t');
			                args.push('color:grey');
			                args.push(title);
			            } else {
			                args.unshift('%c%s\t');
			            }
			            args.push(data);
			            // output
			            console.log.apply(console, args);
			        }
			    },
			
			
			    /**
			     * Print the given complex var with level restriction.
			     *
			     * @param {*} data data to output
			     * @param {number} [depth=0] amount of sub-levels to print
			     */
			    inspect: function ( data, depth ) {
			        if ( host ) {
			            log('inspect:\n' + util.inspect(data, {depth: depth === undefined ? 3 : depth, colors: true}));
			        } else {
			            console.log(data);
			        }
			    },
			
			
			    /**
			     * Print the given event object in some special way.
			     *
			     * @param {Event} data event object
			     */
			    event: function ( data ) {
			        var type  = data.type.toUpperCase(),
			            color = type === 'ERROR' ? 'red' : 'green',
			            text  = ('Event ' + type[color]).bold;
			
			        if ( host ) {
			            switch ( type ) {
			                case 'KEYDOWN':
			                    text = text +
			                    '\tctrl' [data.ctrlKey  ? 'green' : 'grey'] +
			                    ' alt'  [data.altKey   ? 'green' : 'grey'] +
			                    ' shift'[data.shiftKey ? 'green' : 'grey'] +
			                    '\t' + data.keyCode + '\t' + data.code + '\t' + (data.keyIdentifier || '').green;
			                    break;
			                case 'KEYPRESS':
			                    text = text +
			                    '\tctrl' [data.ctrlKey  ? 'green' : 'grey'] +
			                    ' alt'  [data.altKey   ? 'green' : 'grey'] +
			                    ' shift'[data.shiftKey ? 'green' : 'grey'] +
			                    '\t' + data.keyCode + '\t' + (data.keyIdentifier || '').green + '\t' + String.fromCharCode(data.keyCode);
			                    break;
			                case 'MOUSEMOVE':
			                    text = text +
			                    '\tctrl' [data.ctrlKey  ? 'green' : 'grey'] +
			                    ' alt'  [data.altKey   ? 'green' : 'grey'] +
			                    ' shift'[data.shiftKey ? 'green' : 'grey'] +
			                    '\t' + data.x + ':' + data.y;
			                    break;
			                case 'CLICK':
			                    text = text +
			                    '\tctrl' [data.ctrlKey  ? 'green' : 'grey'] +
			                    ' alt'  [data.altKey   ? 'green' : 'grey'] +
			                    ' shift'[data.shiftKey ? 'green' : 'grey'] +
			                    '\t' + data.x + ':' + data.y;
			                    break;
			                case 'ERROR':
			                    text = text +
			                        '\t' + data.filename +
			                        ' (' + data.lineno + ':' + data.colno + ')' +
			                        ' ' + data.message;
			                    break;
			            }
			            log(text);
			        } else {
			            switch ( type ) {
			                case 'KEYDOWN':
			                case 'KEYPRESS':
			                    console.log('%o\t%c%s %c%s %c%s %c%s %c%s\t%s\t%c%s', data, 'color:' + color + ';font-weight:bold', type,
			                        'color:' + (data.ctrlKey  ? 'green' : 'lightgrey'), 'ctrl',
			                        'color:' + (data.altKey   ? 'green' : 'lightgrey'), 'alt',
			                        'color:' + (data.shiftKey ? 'green' : 'lightgrey'), 'shift',
			                        'color:black', data.keyCode, data.code || '', 'color:green', data.keyIdentifier
			                    );
			                    break;
			                default:
			                    console.log('%o\t%c%s', data, 'color:' + color + ';font-weight:bold', type);
			            }
			        }
			    },
			
			
			    /**
			     * Use to do some development-specific actions which are removed in release mode.
			     *
			     * @param {function} cb callback to execute
			     *
			     * @example
			     * debug.stub(function () {
			     *     alert('This is visible only in debug mode!');
			     * });
			     * // it's also possible to use simple expression:
			     * // link the current scope var with global
			     * // useful for dev only
			     * debug.stub(window.app = this);
			     */
			    stub: function ( cb ) {
			        if ( typeof cb === 'function' ) {
			            cb();
			        }
			    },
			
			
			    /**
			     * Start specific timer.
			     * Use to calculate time of some actions.
			     *
			     * @param {string} [name=''] timer group name
			     * @param {string} [title=''] timer individual mark caption
			     *
			     * @example
			     * debug.time('request');
			     * // some processing...
			     * debug.time('request');
			     * // prints 'time: +20ms'
			     * // some processing...
			     * debug.time('request', 'ready');
			     * // prints 'time (ready): +40ms'
			     * // some processing...
			     * debug.time('request', 'done');
			     * // prints 'time (done): +60ms'
			     */
			    time: function ( name, title ) {
			        var time = +new Date();
			
			        // sanitize
			        name  = name  || '';
			        title = title || '';
			
			        // is this mark exist
			        if ( timeMarks[name] ) {
			            // already set
			            debug.log((name || 'time') + (title ? ' (' + title + ')' : '') + ': +' + (time - timeMarks[name].last) + 'ms', 'blue');
			        } else {
			            // create a new mark
			            timeMarks[name] = {init: time};
			        }
			
			        // update with the current value
			        timeMarks[name].last = time;
			    },
			
			
			    /**
			     * End specific timer.
			     * Use to calculate time of some actions.
			     *
			     * @param {string} [name=''] timer name
			     * @param {string} [title='total'] timer mark caption
			     *
			     * @example
			     * debug.time();
			     * // some processing...
			     * debug.timeEnd();
			     * // prints 'time (total): 934ms'
			     *
			     * @example
			     * debug.time('request');
			     * // some processing...
			     * debug.timeEnd('request', 'done');
			     * // prints 'request (done): 934ms'
			     */
			    timeEnd: function ( name, title ) {
			        var time = +new Date();
			
			        // sanitize
			        name  = name  || '';
			        title = title || 'total';
			
			        // is this mark exist
			        if ( timeMarks[name] ) {
			            debug.log((name || 'time') + ' (' + title + '): ' + (time - timeMarks[name].init) + 'ms', 'blue');
			
			            delete timeMarks[name];
			        } else {
			            throw new Error(__filename + ': no started timer for "' + name + '"');
			        }
			    }
			
			};
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/develop/debug.js"))

/***/ },
/* 22 */
/*!**************************************!*\
  !*** ./app/js/stb/develop/events.js ***!
  \**************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Additional dev events.
			 *
			 * @module stb/develop/events
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint new-cap: 0 */
			
			var util    = __webpack_require__(/*! util */ 6),
			    app     = __webpack_require__(/*! ../app */ 3),
			    request = __webpack_require__(/*! ../request */ 31),
			    dom     = __webpack_require__(/*! ../dom */ 4),
			    grid    = __webpack_require__(/*! ./grid */ 23);
			
			
			// additional top-level key handler
			window.addEventListener('load', function developEventListenerLoad () {
			    // export to globals div for develop HTML elements
			    window.$develop = document.body.appendChild(document.createElement('div'));
			    window.$develop.className = 'develop';
			
			    // apply dev css
			    document.body.classList.add('develop');
			
			    grid.init();
			
			    if ( localStorage && localStorage.getItem('grid.active') ) {
			        grid.show();
			    }
			
			    // stress-testing
			    window.gremlins = __webpack_require__(/*! gremlins.js/gremlins.min.js */ 50);
			    window.horde    = window.gremlins.createHorde();
			});
			
			
			// additional top-level key handler
			window.addEventListener('keydown', function developEventListenerKeydown ( event ) {
			    switch ( event.keyCode ) {
			        // numpad 0
			        case 96:
			            debug.log('full document reload', 'red');
			            location.hash = '';
			            location.reload();
			            break;
			
			        // numpad 1
			        case 97:
			            // NTSC
			            changeScreenDimension(720, 480);
			            break;
			
			        // numpad 2
			        case 98:
			            // PAL
			            changeScreenDimension(720, 576);
			            break;
			
			        // numpad 3
			        case 99:
			            // 720p
			            changeScreenDimension(1280, 720);
			            break;
			
			        // numpad 4
			        case 100:
			            // 1080p
			            changeScreenDimension(1920, 1080);
			            break;
			
			        // numpad 5
			        case 101:
			            // debug grid
			            if ( grid.active ) {
			                grid.hide();
			            } else {
			                grid.show();
			            }
			            debug.log('show grid: ' + grid.active, 'red');
			            if ( localStorage ) {
			                localStorage.setItem('grid.active', grid.active || '');
			            }
			            break;
			
			        // numpad 6
			        case 102:
			            // stress-testing for emulation
			            window.horde.unleash({nb: 500});
			            break;
			
			        // numpad 7
			        case 103:
			            if ( !app.data.host ) {
			                debug.log('SpyJS in this mode is available only on STB devices.', 'red');
			            } else {
			                // SpyJS enable/disable
			                if ( localStorage ) {
			                    if ( !localStorage.getItem('spyjs.active') ) {
			                        // try to "ping" proxy server
			                        request.ajax(document.location.protocol + '//' + location.hostname + ':3546', {
			                            method: 'get',
			                            onload: function () {
			                                // proxy seems ready
			                                //isSpyJs = true;
			                                localStorage.setItem('spyjs.active', true);
			                                debug.log('SpyJS: enable', 'red');
			                                debug.log('SpyJS: set proxy to ' + location.hostname + ':' + 3546);
			
			                                gSTB.SetWebProxy(location.hostname, 3546, '', '', '');
			                                location.reload();
			                            },
			                            onerror: function () {
			                                debug.log('SpyJS: no connection (check SpyJS is started on the server)', 'red');
			                            }
			                        });
			                    } else {
			                        if ( localStorage ) {
			                            localStorage.setItem('spyjs.active', '');
			                        }
			                        gSTB.ResetWebProxy();
			                        debug.log('SpyJS: disable', 'red');
			                        location.reload();
			                    }
			                } else {
			                    // enable always if no localStorage (can't understand running or not)
			                    request.ajax(document.location.protocol + '//' + location.hostname + ':3546', {
			                        method: 'get',
			                        onload: function () {
			                            // proxy seems ready
			                            //isSpyJs = true;
			                            debug.log('SpyJS: enable', 'red');
			                            debug.log('SpyJS: set proxy to ' + location.hostname + ':' + 3546);
			
			                            gSTB.SetWebProxy(location.hostname, 3546, '', '', '');
			                            location.reload();
			                        },
			                        onerror: function () {
			                            debug.log('SpyJS: no connection (check SpyJS is started on the server)', 'red');
			                        }
			                    });
			                }
			            }
			            break;
			
			        // numpad 8
			        case 104:
			            // FireBug Lite
			            debug.log('firebug-lite activation', 'red');
			            document.head.appendChild(dom.tag('script', {
			                type: 'text/javascript',
			                src: 'http://getfirebug.com/firebug-lite.js#startOpened',
			                onload: function () {
			                    debug.log('firebug-lite ready ...', 'green');
			                },
			                onerror: function ( error ) {
			                    debug.inspect(error);
			                }
			            }));
			            break;
			
			        // numpad 9
			        case 105:
			            // outline components and inner structures
			            debug.log('toggle develop css layout', 'red');
			            document.body.classList.toggle('develop');
			            break;
			
			        // numpad .
			        case 110:
			            // CSS reload
			            debug.log('CSS reload', 'red');
			            // get through all css links
			            Array.prototype.slice.call(document.head.getElementsByTagName('link')).forEach(function forEachLink ( tag ) {
			                // get base name, modify and apply
			                tag.href = tag.href.split('?')[0] + '?' + (+new Date());
			            });
			            break;
			    }
			});
			
			
			/**
			 * Apply the given screen geometry and reload the page.
			 *
			 * @param {number} width screen param
			 * @param {number} height screen param
			 */
			function changeScreenDimension ( width, height ) {
			    // check if it's necessary
			    if ( localStorage ) {
			        if ( Number(localStorage.getItem('screen.height')) !== height ) {
			            // yes
			            debug.log(util.format('switch to %sx%s', width, height), 'red');
			
			            // save in case of document reload
			            localStorage.setItem('screen.height', height);
			            localStorage.setItem('screen.width',  width);
			
			            // hide content to avoid raw HTML blinking
			            document.body.style.display = 'none';
			
			            // apply new metrics
			            app.setScreen(__webpack_require__(/*! ../../../../config/metrics */ 9)[height]);
			
			            // restore visibility
			            document.body.style.display = '';
			        } else {
			            // not really
			            debug.log('no resolution change: new and current values are identical', 'red');
			        }
			    }
			}


/***/ },
/* 23 */
/*!************************************!*\
  !*** ./app/js/stb/develop/grid.js ***!
  \************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Visual grid with cursor.
			 *
			 * @module stb/develop/grid
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var data    = __webpack_require__(/*! ../app */ 3).data,
			    points = (window.localStorage && localStorage.getItem('grid.points'));
			
			if ( points ) {
			    try {
			        points = JSON.parse(points);
			    } catch ( error ) {
			        points = [];
			    }
			} else {
			    points = [];
			}
			
			
			// public
			module.exports = window.grid = {
			
			    /** @type {HTMLElement} */
			    $canvas: null,
			
			    /** @type {CanvasRenderingContext2D} */
			    ctx: null,
			
			    lineWidth: 0.9,
			
			    // content middle point
			    centerX: 0,
			    centerY: 0,
			
			    // last click point
			    lastX: 0,
			    lastY: 0,
			
			    // mouse pointer
			    cursorX: 0,
			    cursorY: 0,
			
			    // list of click points
			    points: points,
			
			    // points to snap
			    snaps: [],
			
			    // visible or not
			    active: false,
			
			
			    init: function () {
			        // current execution context
			        var self = this;
			
			        this.$canvas = window.$develop.appendChild(document.createElement('canvas'));
			        this.ctx = this.$canvas.getContext('2d');
			
			        // apply size
			        this.ctx.canvas.width  = data.metrics.width;
			        this.ctx.canvas.height = data.metrics.height;
			
			        // safe zone center
			        this.centerX = data.metrics.availWidth  / 2 + data.metrics.availLeft;
			        this.centerY = data.metrics.availHeight / 2 + data.metrics.availTop;
			
			        this.snaps.push({x: data.metrics.availLeft,  y: data.metrics.availTop});
			        this.snaps.push({x: data.metrics.width - data.metrics.availRight, y: data.metrics.height - data.metrics.availBottom});
			        this.snaps.push({x: this.centerX, y: this.centerY});
			
			        this.ctx.lineWidth = this.lineWidth;
			        this.ctx.font = '14px Ubuntu';
			
			        this.$canvas.addEventListener('contextmenu', function ( event ) {
			            event.preventDefault();
			        });
			
			        this.$canvas.addEventListener('mousedown', function ( event ) {
			            self.mousedown(event);
			        });
			
			        this.$canvas.addEventListener('mousemove', function ( event ) {
			            self.mousemove(event);
			        });
			    },
			
			
			    mousemove: function ( event ) {
			        // current execution context
			        var self = this;
			
			        this.cursorX = event.x;
			        this.cursorY = event.y;
			
			        this.repaint();
			
			        if ( event.shiftKey ) {
			            // snap to the point divisible by 10
			            this.cursorX = Math.round(event.x / 10) * 10;
			            this.cursorY = Math.round(event.y / 10) * 10;
			        } else if ( !event.ctrlKey ) {
			            // snap to the nearest line
			            this.points.concat(this.snaps).some(function ( point ) {
			                if ( Math.abs(point.x - self.cursorX) <= 10 ) {
			                    self.cursorX = point.x;
			                }
			                if ( Math.abs(point.y - self.cursorY) <= 10 ) {
			                    self.cursorY = point.y;
			                }
			            });
			        }
			
			        this.drawPointer();
			    },
			
			
			    mousedown: function ( event ) {
			        var matchPoint = null,
			            self       = this,  // current execution context
			            point;
			
			        // all clicked crosses
			        this.points.forEach(function ( point ) {
			            if ( self.cursorX === point.x && self.cursorY === point.y ) {
			                matchPoint = point;
			            }
			        });
			
			        if ( event.button === 0 ) {
			            // left mouse button
			            if ( matchPoint === null ) {
			                this.points.push({x: this.cursorX, y: this.cursorY});
			            }
			            this.lastX = this.cursorX;
			            this.lastY = this.cursorY;
			        } else if ( event.button === 1 ) {
			            // middle mouse button
			            this.points.pop();
			            point = this.points[this.points.length - 1];
			            if ( point ) {
			                this.lastX = point.x;
			                this.lastY = point.y;
			            } else {
			                this.lastX = 0;
			                this.lastY = 0;
			            }
			        } else if ( event.button === 2 ) {
			            // right mouse button
			            if ( matchPoint !== null ) {
			                this.points.splice(this.points.indexOf(matchPoint), 1);
			                point = this.points[this.points.length - 1];
			                if ( point ) {
			                    this.lastX = point.x;
			                    this.lastY = point.y;
			                } else {
			                    this.lastX = 0;
			                    this.lastY = 0;
			                }
			            } else {
			                this.lastX = 0;
			                this.lastY = 0;
			            }
			        }
			        this.repaint();
			        this.drawPointer();
			        if ( localStorage ) {
			            localStorage.setItem('grid.points', JSON.stringify(this.points));
			        }
			    },
			
			
			    show: function () {
			        this.active = true;
			        this.$canvas.classList.add('active');
			        this.repaint();
			    },
			
			
			    hide: function () {
			        this.active = false;
			        this.$canvas.classList.remove('active');
			    },
			
			
			    repaint: function () {
			        var ctx  = this.ctx,
			            self = this;  // current execution context
			
			        // remove all
			        ctx.clearRect(0, 0, data.metrics.width, data.metrics.height);
			
			        // safe zone center
			        this.drawCross({x: this.centerX, y: this.centerY}, {color: 'grey'});
			
			        // draw safe zone borders
			        ctx.strokeStyle = 'red';
			        ctx.strokeRect(data.metrics.availLeft, data.metrics.availTop, data.metrics.availWidth, data.metrics.availHeight);
			
			        // all clicked crosses
			        this.points.forEach(function ( point ) {
			            self.drawCross(point, {color:'green', mark: 5});
			        });
			    },
			
			
			    drawPointer: function () {
			        var ctx    = this.ctx,
			            height = 16,
			            width, dx, dy, angle, title;
			
			        title = this.cursorX + ' : ' + this.cursorY;
			
			        // there were some clicks
			        if ( this.lastX || this.lastY ) {
			            // distance by X and Y from last point
			            dx = this.cursorX - this.lastX;
			            dy = this.cursorY - this.lastY;
			            title = title + ' [' + (dx > 0 ? '+' : '') + dx + ', ' + (dy > 0 ? '+' : '') + dy + ']';
			
			            // angle of the line connecting the cursor and the last point
			            angle = Math.atan2(dy, dx) * 180 / Math.PI;
			            title = title + ' ' + angle.toFixed(2) + '°';
			
			            // not perpendicular
			            if ( dx && dy ) {
			                // distance between the cursor and the last point
			                title = title + ' len: ' + Math.sqrt(Math.pow(Math.abs(dx), 2) + Math.pow(Math.abs(dy), 2)).toFixed(2);
			            }
			
			            // angle line
			            ctx.beginPath();
			            // show by color if 45°
			            ctx.strokeStyle = [-135, 135, -45, 45].indexOf(angle) !== -1 ? 'yellow' : 'grey';
			            ctx.moveTo(this.lastX, this.lastY);
			            ctx.lineTo(this.cursorX, this.cursorY);
			            ctx.stroke();
			        }
			
			        // pointer itself
			        this.drawCross({x: this.cursorX, y: this.cursorY});
			
			        title = ' ' + title + ' ';
			        width = ctx.measureText(title).width;
			
			        // title background
			        ctx.fillStyle = 'yellow';
			        ctx.fillRect(
			            this.cursorX > this.centerX ? this.cursorX - width  : this.cursorX,
			            this.cursorY > this.centerY ? this.cursorY - height : this.cursorY,
			            width, height
			        );
			
			        // title itself
			        ctx.fillStyle    = 'black';
			        ctx.textBaseline = this.cursorY > this.centerY ? 'bottom' : 'top';
			        ctx.textAlign    = this.cursorX > this.centerX ? 'right'  : 'left';
			        ctx.fillText(title, this.cursorX, this.cursorY);
			    },
			
			
			    drawCross: function ( point, options ) {
			        var ctx = this.ctx;
			
			        // defaults
			        options = options || {};
			
			        // apply style options
			        ctx.lineWidth   = options.width || this.lineWidth;
			        ctx.strokeStyle = options.color || 'yellow';
			
			        ctx.beginPath();
			        // horizontal line
			        ctx.moveTo(0, point.y);
			        ctx.lineTo(data.metrics.width, point.y);
			        // vertical line
			        ctx.moveTo(point.x, 0);
			        ctx.lineTo(point.x, data.metrics.height);
			        // draw
			        ctx.stroke();
			
			        // center mark
			        if ( options.mark ) {
			            ctx.lineWidth = 1.5;
			            ctx.beginPath();
			            // horizontal line
			            ctx.moveTo(point.x - options.mark, point.y);
			            ctx.lineTo(point.x + options.mark, point.y);
			            // vertical line
			            ctx.moveTo(point.x, point.y - options.mark);
			            ctx.lineTo(point.x, point.y + options.mark);
			            // draw
			            ctx.stroke();
			            ctx.lineWidth = this.lineWidth;
			        }
			    }
			
			};


/***/ },
/* 24 */
/*!*****************************************!*\
  !*** ./app/js/stb/develop/overrides.js ***!
  \*****************************************/
/***/ function(module, exports) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * Develop enhancements.
			 *
			 * @author Igor Zaporozhets <deadbyelpy@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 * @see https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Function/bind
			 */
			
			'use strict';
			
			
			// links to the origin
			var getElementById = document.getElementById,
			    querySelector  = document.querySelector;
			
			
			// improved error output when working with selection elements by id
			document.getElementById = function ( id ) {
			    var el = getElementById.call(document, id);
			
			    if ( !el ) { throw new Error(__filename + ': no element with id ' + id); }
			
			    return el;
			};
			
			document.querySelector = function ( selector ) {
			    var el = querySelector.call(document, selector);
			
			    if ( !el ) { throw new Error(__filename + ': no element with selector: ' + selector); }
			
			    return el;
			};
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/develop/overrides.js"))

/***/ },
/* 25 */
/*!*************************************!*\
  !*** ./app/js/stb/develop/proxy.js ***!
  \*************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * STB calls relay.
			 *
			 * @module stb/develop/proxy
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint new-cap: 0 */
			
			var host   = __webpack_require__(/*! ../app */ 3).data.host,
			    util   = __webpack_require__(/*! util */ 6),
			    config = __webpack_require__(/*! ../../../../config/proxy */ 44);
			
			
			/**
			 * Proxy host activation
			 */
			function initHost () {
			    var ProxyHost = __webpack_require__(/*! code-proxy/client/host */ 49);
			
			    // init and export to globals
			    window.proxy = new ProxyHost({
			        name: config.name,
			        host: location.hostname,
			        port: config.portWs
			    });
			
			    // redefine logging
			    window.proxy.log = function log ( type, time, status, message, params ) {
			        gSTB.Debug(util.format('[%s]\t%s\t%s\t%s\t%s',
			            type.grey,
			            config.name.magenta,
			            time.toString().grey,
			            (status ? message.green : message.red),
			            (params ? JSON.stringify(params).grey : '')
			        ));
			    };
			}
			
			/**
			 * Proxy guest activation
			 */
			function initGuest () {
			    var ProxyGuest = __webpack_require__(/*! code-proxy/client/guest */ 48),
			        stbNames   = ['dvbManager', 'gSTB', 'pvrManager', 'stbDownloadManager', 'stbStorage', 'stbUpdate', 'stbWebWindow', 'stbWindowMgr', 'timeShift'],
			        skipKeys   = ['objectName', 'destroyed', 'deleteLater'];
			
			    // init and export to globals
			    window.proxy = new ProxyGuest({
			        name: config.name,
			        host: location.hostname,
			        port: config.portHttp
			    });
			
			    // create local stb objects
			    stbNames.forEach(function forEachStbNames ( stbObjName ) {
			        // prepare
			        var stbObj = window[stbObjName] = {},
			            // for each global stb object get all its properties
			            keysCode = util.format('Object.keys(%s)', stbObjName),
			            stbObjKeys;
			
			        // get data from cache if no connection
			        if ( !window.proxy.active && config.cache ) {
			            stbObjKeys = JSON.parse(localStorage.getItem('proxy:eval:' + keysCode));
			            console.log('proxy cache: ', keysCode);
			        } else {
			            stbObjKeys = window.proxy.eval(keysCode);
			            localStorage.setItem('proxy:eval:' + keysCode, JSON.stringify(stbObjKeys));
			        }
			
			        // valid list of keys
			        if ( stbObjKeys && Array.isArray(stbObjKeys) ) {
			
			            stbObjKeys.forEach(function forEachStbObjKeys ( stbObjKey ) {
			                // strip signature
			                stbObjKey = stbObjKey.split('(')[0];
			                // get rid of system fields
			                if ( skipKeys.indexOf(stbObjKey) === -1 ) {
			                    // wrap each method with proxy call
			                    stbObj[stbObjKey] = (function stbCallWrapper ( name, method ) {
			                        return function stbCallBody () {
			                            var code = name + '.' + method,
			                                data;
			
			                            // get data from cache if no connection
			                            if ( !window.proxy.active && config.cache ) {
			                                data = JSON.parse(localStorage.getItem('proxy:call:' + code));
			                                console.log('proxy cache: ', code);
			                            } else {
			                                data = window.proxy.call(code, Array.prototype.slice.call(arguments), name) || null;
			                                localStorage.setItem('proxy:call:' + code, JSON.stringify(data));
			                            }
			                            return data;
			                        };
			                    }(stbObjName, stbObjKey));
			                }
			            });
			        }
			    });
			}
			
			
			// init
			if ( config.active ) {
			    if ( host ) {
			        initHost();
			    } else {
			        initGuest();
			    }
			}


/***/ },
/* 26 */
/*!*************************************!*\
  !*** ./app/js/stb/develop/shims.js ***!
  \*************************************/
/***/ function(module, exports) {

			/**
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 * @see https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Function/bind
			 */
			
			'use strict';
			
			/* eslint-disable */
			
			if ( !Function.prototype.bind ) {
			    Function.prototype.bind = function ( oThis ) {
			        if ( typeof this !== 'function' ) {
			            // closest thing possible to the ECMAScript 5
			            // internal IsCallable function
			            throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
			        }
			
			        var aArgs = Array.prototype.slice.call(arguments, 1),
			            fToBind = this,
			            fNOP = function () {},
			            fBound = function () {
			                return fToBind.apply(this instanceof fNOP && oThis
			                        ? this
			                        : oThis,
			                    aArgs.concat(Array.prototype.slice.call(arguments)));
			            };
			
			        fNOP.prototype = this.prototype;
			        fBound.prototype = new fNOP();
			
			        return fBound;
			    };
			}
			
			
			if ( !window.requestAnimationFrame ) {
			    // shim layer with setTimeout fallback
			    window.requestAnimationFrame =
			        window.mozRequestAnimationFrame ||
			        window.webkitRequestAnimationFrame ||
			        window.msRequestAnimationFrame ||
			        function ( callback ) {
			            window.setTimeout(callback, 1000 / 60);
			        };
			}


/***/ },
/* 27 */
/*!**************************************!*\
  !*** ./app/js/stb/develop/static.js ***!
  \**************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Static files reload on change.
			 *
			 * @module stb/develop/static
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var dom    = __webpack_require__(/*! ../dom */ 4),
			    config = __webpack_require__(/*! ../../../../config/static */ 45);
			
			
			// livereload activation
			if ( config.livereload ) {
			    // load external script
			    document.head.appendChild(dom.tag('script', {
			        type: 'text/javascript',
			        src: '//' + location.hostname + ':' + (config.livereload === true ? 35729 : config.livereload) + '/livereload.js'
			    }));
			}


/***/ },
/* 28 */
/*!**************************************!*\
  !*** ./app/js/stb/develop/weinre.js ***!
  \**************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * Web Inspector Remote.
			 *
			 * @module stb/develop/weinre
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var dom     = __webpack_require__(/*! ../dom */ 4),
			    util    = __webpack_require__(/*! util */ 6),
			    config  = __webpack_require__(/*! ../../../../config/weinre */ 46);
			
			
			// web inspector is allowed only without SpyJS
			if ( config.active && (window.localStorage ? !localStorage.getItem('spyjs.active') : true ) ) {
			    // load external script
			    document.head.appendChild(dom.tag('script', {
			        type: 'text/javascript',
			        src: util.format('//%s:%s/target/target-script-min.js#%s', location.hostname, config.port, config.name)
			    }));
			}


/***/ },
/* 29 */
/*!*******************************!*\
  !*** ./app/js/stb/gettext.js ***!
  \*******************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/gettext
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Emitter = __webpack_require__(/*! ./emitter */ 5),
			    gettext = new Emitter(),
			    meta    = null,
			    data    = null;
			
			
			/**
			 * Simple gettext implementation.
			 *
			 * @param {Object} config options
			 * @param {string} config.path relative path to project root
			 * @param {string} config.name language name
			 * @param {string} config.ext language file extension
			 * @param {function} callback hook on ready
			 *
			 * @example
			 * gettext.load({name: 'ru'}, function ( error, data ) {
			 *     debug.log(error);
			 *     debug.inspect(data);
			 * });
			 */
			gettext.load = function ( config, callback ) {
			    var xhr = new XMLHttpRequest();
			
			    if ( true ) {
			        if ( !config.name || typeof config.name !== 'string' ) { throw new Error(__filename + ': config.name must be a nonempty string'); }
			        if ( typeof callback !== 'function' ) { throw new Error(__filename + ': wrong callback type'); }
			    }
			
			    // defaults
			    config.ext  = config.ext  || 'json';
			    config.path = config.path || 'lang';
			
			    /* todo: get rid of JSON.parse in future
			    xhr.overrideMimeType('application/json');
			    xhr.responseType = 'json';/**/
			
			    xhr.responseType = 'text';
			
			    xhr.onload = function () {
			        var json;
			
			        try {
			            json = JSON.parse(xhr.responseText);
			            meta = json.meta;
			            data = json.data;
			            callback(null, data);
			        } catch ( error ) {
			            meta = null;
			            data = null;
			            xhr.onerror(error);
			        }
			
			        // there are some listeners
			        if ( gettext.events['load'] ) {
			            // notify listeners
			            gettext.emit('load');
			        }
			    };
			
			    xhr.onerror = function ( error ) {
			        callback(error);
			
			        // there are some listeners
			        if ( gettext.events['error'] ) {
			            // notify listeners
			            gettext.emit('error');
			        }
			    };
			
			    xhr.open('GET', config.path + '/' + config.name + '.' + config.ext, true);
			    xhr.send(null);
			};
			
			
			/**
			 * Display the native language translation of a textual message.
			 *
			 * @param {string} msgId textual message
			 *
			 * @return {string} translated text
			 *
			 * @global
			 *
			 * @example
			 * console.log(gettext('some line to be localized'));
			 */
			window.gettext = function ( msgId ) {
			    return data && data[''][msgId] ? data[''][msgId] : msgId;
			};
			
			
			/**
			 * The "p" in "pgettext" stands for "particular": fetches a particular translation of the textual message.
			 *
			 * @param {string} context message context
			 * @param {string} msgId textual message
			 *
			 * @return {string} translated text
			 *
			 * @global
			 *
			 * @example
			 * console.log(pgettext('some context', 'some text'));
			 */
			window.pgettext = function ( context, msgId ) {
			    return data && data[context][msgId] ? data[context][msgId] : msgId;
			};
			
			
			/**
			 * Display the native language translation of a textual message whose grammatical form depends on a number.
			 *
			 * @param {string} msgId textual message in a singular form
			 * @param {string} plural textual message in a plural form
			 * @param {number} value message number
			 *
			 * @return {string} translated text
			 *
			 * @global
			 *
			 * @example
			 * console.log(ngettext('{0} cat', '{0} cats', 1));
			 */
			window.ngettext = function ( msgId, plural, value ) {
			    /* eslint no-eval: 0 */
			
			    if ( true ) {
			        if ( Number(value) !== value ) { throw new Error(__filename + ': value must be a number'); }
			    }
			
			    if ( data && meta ) {
			        // translation
			        return data[''][msgId][eval('var n = ' + value + '; ' + meta.plural)];
			    }
			
			    // english
			    return value === 1 ? msgId : plural;
			};
			
			
			// public
			module.exports = gettext;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/gettext.js"))

/***/ },
/* 30 */
/*!*****************************!*\
  !*** ./app/js/stb/model.js ***!
  \*****************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/model
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Emitter = __webpack_require__(/*! ./emitter */ 5);
			
			
			/**
			 * Base model implementation.
			 *
			 * Represents domain-specific data or information that an application will be working with.
			 * A typical example is a user account (e.g name, avatar, e-mail) or a music track (e.g title, year, album).
			 * Holds information, but don’t handle behaviour and don’t format information or influence how data appears.
			 *
			 * @constructor
			 * @extends Emitter
			 *
			 * @param {Object} [data={}] init attributes
			 */
			function Model ( data ) {
			    if ( true ) {
			        if ( typeof this !== 'object' ) { throw new Error(__filename + ': must be constructed via new'); }
			        if ( data && typeof data !== 'object' ) { throw new Error(__filename + ': wrong data type'); }
			    }
			
			    // parent constructor call
			    Emitter.call(this);
			
			    /**
			     * Model attributes with given data or empty hash table.
			     *
			     * @member {Object.<string, *>}
			     **/
			    this.data = data || {};
			}
			
			
			// inheritance
			Model.prototype = Object.create(Emitter.prototype);
			Model.prototype.constructor = Model;
			
			
			// which of data fields is primary
			Model.prototype.idName = 'id';
			
			
			/**
			 * Remove all attributes from the model event.
			 *
			 * @event module:stb/model~Model#clear
			 *
			 * @type {Object}
			 * @property {Object} data old model attributes
			 */
			
			
			/**
			 * Remove all attributes from the model.
			 *
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/model~Model#clear
			 */
			Model.prototype.clear = function () {
			    var data = this.data;
			
			    if ( true ) {
			        if ( typeof data !== 'object' ) { throw new Error(__filename + ': wrong data type'); }
			    }
			
			    // is there any data?
			    if ( Object.keys(data).length > 0 ) {
			        // reset
			        this.data = {};
			
			        // there are some listeners
			        if ( this.events['clear'] ) {
			            // notify listeners
			            this.emit('clear', {data: data});
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Set model data event.
			 *
			 * @event module:stb/model~Model#init
			 *
			 * @type {Object}
			 * @property {Object} data new model attributes
			 */
			
			
			/**
			 * Clear and set model data.
			 *
			 * @param {Object} data attributes
			 * @return {boolean} operation status
			 *
			 * @fires module:stb/model~Model#clear
			 * @fires module:stb/model~Model#init
			 */
			Model.prototype.init = function ( data ) {
			    if ( true ) {
			        if ( typeof data !== 'object' ) { throw new Error(__filename + ': wrong data type'); }
			    }
			
			    // valid input
			    if ( data ) {
			        // reset data
			        this.clear();
			
			        // init with given data
			        this.data = data;
			
			        // there are some listeners
			        if ( this.events['init'] ) {
			            // notify listeners
			            this.emit('init', {data: data});
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Check an attribute existence.
			 *
			 * @param {string} name attribute
			 *
			 * @return {boolean} attribute exists or not
			 */
			Model.prototype.has = function ( name ) {
			    if ( true ) {
			        if ( typeof this.data !== 'object' ) { throw new Error(__filename + ': wrong this.data type'); }
			    }
			
			    // hasOwnProperty method is not available directly in case of Object.create(null)
			    //return Object.hasOwnProperty.call(this.data, name);
			    return this.data.hasOwnProperty(name);
			};
			
			/**
			 * Get the model attribute by name.
			 *
			 * @param {string} name attribute
			 *
			 * @return {*} associated value
			 */
			Model.prototype.get = function ( name ) {
			    if ( true ) {
			        if ( typeof this.data !== 'object' ) { throw new Error(__filename + ': wrong this.data type'); }
			    }
			
			    return this.data[name];
			};
			
			
			/**
			 * Update or create a model attribute event.
			 *
			 * @event module:stb/model~Model#change
			 *
			 * @type {Object}
			 * @property {string} name attribute name
			 * @property {*} [prev] old/previous attribute value (can be absent on attribute creation)
			 * @property {*} [curr] new/current attribute value (can be absent on attribute removal)
			 */
			
			
			/**
			 * Update or create a model attribute.
			 *
			 * @param {string} name attribute
			 * @param {*} value associated value
			 * @return {boolean} operation status (true - attribute value was changed/created)
			 *
			 * @fires module:stb/model~Model#change
			 */
			Model.prototype.set = function ( name, value ) {
			    var isAttrSet = name in this.data,
			        emitData  = {name: name, curr: value};
			
			    if ( true ) {
			        if ( typeof this.data !== 'object' ) { throw new Error(__filename + ': wrong this.data type'); }
			    }
			
			    if ( isAttrSet ) {
			        // update
			        emitData.prev = this.data[name];
			        // only if values are different
			        if ( value !== emitData.prev ) {
			            this.data[name] = value;
			
			            // there are some listeners
			            if ( this.events['change'] ) {
			                // notify listeners
			                this.emit('change', emitData);
			            }
			
			            return true;
			        }
			    } else {
			        // create
			        this.data[name] = value;
			
			        // there are some listeners
			        if ( this.events['change'] ) {
			            // notify listeners
			            this.emit('change', emitData);
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			/**
			 * Delete the given attribute by name.
			 *
			 * @param {string} name attribute
			 * @return {boolean} operation status (true - attribute was deleted)
			 *
			 * @fires module:stb/model~Model#change
			 */
			Model.prototype.unset = function ( name ) {
			    var isAttrSet = name in this.data,
			        emitData;
			
			    if ( true ) {
			        if ( typeof this.data !== 'object' ) { throw new Error(__filename + ': wrong this.data type'); }
			    }
			
			    if ( isAttrSet ) {
			        emitData = {name: name, prev: this.data[name]};
			        delete this.data[name];
			
			        // there are some listeners
			        if ( this.events['change'] ) {
			            // notify listeners
			            this.emit('change', emitData);
			        }
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			///**
			// * Extends the model with the given attribute list
			// * @param {Object} data
			// */
			//Model.prototype.attributes = function ( data ) {
			//    var index   = 0,
			//        keyList = data && typeof data === 'object' ? Object.keys(data) : [];
			//    for ( ; index < keyList.length; index++ ) {
			//        this.set(keyList[index], data[keyList[index]]);
			//    }
			//};
			
			
			///**
			// * Prepare all data for sending to a server
			// * @return {Object}
			// */
			//Model.prototype.pack = function () {
			//    return this._data;
			//};
			
			
			///**
			// * Restores the received data from a server to a model data
			// * @param {Object} data
			// * @return {Object}
			// */
			//Model.prototype.unpack = function ( data ) {
			//    return data;
			//};
			
			
			///**
			// * Sync model to a server
			// */
			//Model.prototype.save = function () {
			//    var self = this;
			//    if ( this.url ) {
			//        // collect data
			//        io.ajax(this.url, {
			//            // request params
			//            method: self._data[self.idName] ? 'put' : 'post',
			//            data  : self.pack(),
			//            onload: function ( data ) {
			//                data = self.unpack(self.parse(data));
			//                self.attributes(data);
			//                console.log(data);
			//                self.emit('save', true);
			//            },
			//            // error handlers
			//            onerror:   this.saveFailure,
			//            ontimeout: this.saveFailure
			//        });
			//    }
			//};
			
			
			///**
			// * Error handler while model data fetch
			// */
			//Model.prototype.saveFailure = function () {
			//    this.emit('save', false);
			//};
			
			
			///**
			// * Converts received data from a server to a model attributes
			// * @param {string} response
			// * @return {Object}
			// */
			//Model.prototype.parse = function ( response ) {
			//    var data = {};
			//    try {
			//        data = JSON.parse(response).data;
			//    } catch(e){ console.log(e); }
			//    return data;
			//};
			
			
			// public
			module.exports = Model;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/model.js"))

/***/ },
/* 31 */
/*!*******************************!*\
  !*** ./app/js/stb/request.js ***!
  \*******************************/
/***/ function(module, exports) {

			/**
			 * Ajax request wrapper.
			 *
			 * @module stb/request
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var request = {},
			    defaults = {
			        method    : 'GET',  // HTTP method to use, such as "GET", "POST", "PUT", "DELETE", etc.
			        async     : true,   // whether or not to perform the operation asynchronously
			        headers   : {},     // list of HTTP request headers
			        type      : 'text', // "", "arraybuffer", "blob", "document", "json", "text"
			        data      : null,   // data to send (plain object)
			        timeout   : 30000,  // amount of milliseconds a request can take before being terminated
			        onload    : null,   // callback when the request has successfully completed
			        onerror   : null,   // callback when the request has failed
			        ontimeout : null    // callback when the author specified timeout has passed before the request could complete
			    },
			    defaultsKeys = Object.keys(defaults);
			
			
			/**
			 * Main method to send ajax requests.
			 *
			 * @param {string} url address
			 * @param {Object} options Plain object with call parameters
			 * @return {XMLHttpRequest|Boolean} false in case of wrong params
			 *
			 * @example
			 * TODO: add
			 */
			request.ajax = function ( url, options ) {
			    var i, headersKeys, client;
			
			    // init
			    options = options || {};
			    // valid non-empty string
			    if ( url && (typeof url === 'string' || url instanceof String) && url.length > 0 ) {
			        // plain object is given as param
			        if ( options && typeof options === 'object') {
			            // extend with default options
			            for ( i = 0; i < defaultsKeys.length; i++ ) {
			                // in case not redefined
			                if ( options[defaultsKeys[i]] === undefined ) {
			                    options[defaultsKeys[i]] = defaults[defaultsKeys[i]];
			                }
			            }
			        }
			
			        client = new XMLHttpRequest();
			        // init a request
			        client.open(options.method, url, options.async);
			
			        // apply the given headers
			        if ( options.headers && typeof options.headers === 'object') {
			            headersKeys = Object.keys(options.headers);
			            for ( i = 0; i < headersKeys.length; i++ ) {
			                client.setRequestHeader(headersKeys[i], options.headers[headersKeys[i]]);
			            }
			        }
			
			        // set response type and timeout
			        client.responseType = options.type;
			        client.timeout      = options.timeout;
			
			        // callbacks
			        if ( options.onload && typeof options.onload === 'function' ) {
			            client.onload = function onload () {
			                options.onload.call(this, this.response || this.responseText, this.status);
			            };
			        }
			        client.onerror   = options.onerror;
			        client.ontimeout = options.ontimeout;
			
			        // actual request
			        //client.send(this.encode(options.data));
			        client.send(options.data ? options.data : null);//JSON.stringify()
			
			        return client;
			    }
			    return false;
			};
			
			
			/**
			 * Serializes the given data for sending to the server via ajax call.
			 *
			 * @param {Object} data Plain object to serialize
			 * @return {string} null if no data to encode
			 *
			 * @example
			 * TODO: add
			 */
			request.encode = function ( data ) {
			    var result = [],
			        i, keys;
			
			    // input plain object validation
			    if ( data && typeof data === 'object') {
			        keys = Object.keys(data);
			        // apply encoding
			        for ( i = 0; i < keys.length; i++ ) {
			            result.push(encodeURIComponent(keys[i]) + '=' + encodeURIComponent(data[keys[i]]));
			        }
			        // build the list of params
			        if ( result.length > 0 ) {
			            return result.join('&');
			        }
			    }
			    return null;
			};
			
			
			// public
			module.exports = request;


/***/ },
/* 32 */
/*!*****************************!*\
  !*** ./app/js/stb/shims.js ***!
  \*****************************/
/***/ function(module, exports) {

			/**
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/* eslint-disable */
			
			if ( !('classList' in document.documentElement) ) {
			    var prototype = Array.prototype,
			        indexOf = prototype.indexOf,
			        slice = prototype.slice,
			        push = prototype.push,
			        splice = prototype.splice,
			        join = prototype.join;
			
			    window.DOMTokenList = function ( el ) {
			        this._element = el;
			        if (el.className !== this._classCache) {
			            this._classCache = el.className;
			            if (!this._classCache) { return; }
			            var classes = this._classCache.replace(/^\s+|\s+$/g,'').split(/\s+/),
			                i;
			            for (i = 0; i < classes.length; i++) {
			                push.call(this, classes[i]);
			            }
			        }
			    };
			    window.DOMTokenList.prototype = {
			        add: function ( token ) {
			            if(this.contains(token)) { return; }
			            push.call(this, token);
			            this._element.className = slice.call(this, 0).join(' ');
			        },
			        contains: function ( token ) {
			            return indexOf.call(this, token) !== -1;
			        },
			        item: function ( index ) {
			            return this[index] || null;
			        },
			        remove: function ( token ) {
			            var i = indexOf.call(this, token);
			            if (i === -1) {
			                return;
			            }
			            splice.call(this, i, 1);
			            this._element.className = slice.call(this, 0).join(' ');
			        },
			        toString: function () {
			            return join.call(this, ' ');
			        },
			        toggle: function ( token ) {
			            if (!this.contains(token)) {
			                this.add(token);
			            } else {
			                this.remove(token);
			            }
			            return this.contains(token);
			        }
			    };
			
			    Object.defineProperty(Element.prototype, 'classList',{
			        get: function () {
			            return new window.DOMTokenList(this);
			        }
			    });
			}


/***/ },
/* 33 */
/*!*********************************!*\
  !*** ./app/js/stb/ui/button.js ***!
  \*********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/button
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2);
			
			
			/**
			 * Base button implementation.
			 *
			 * Has global options:
			 *     Button.prototype.clickDuration - time to apply "click" class, does not apply if 0
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {string} [config.value] button caption text (generated if not set)
			 * @param {string} [config.icon] button icon name
			 *
			 * @example
			 * var Button = require('stb/ui/button'),
			 *     btnSimple, btnIcon, btnDetached;
			 *
			 * btnSimple = new Button({
			 *     $node: document.getElementById('btnSimple'),
			 *     value: 'Simple button'
			 * });
			 *
			 * btnIcon = new Button({
			 *     $node: document.getElementById('btnIcon'),
			 *     icon: 'menu'
			 *     value: 'Button with icon'
			 * });
			 *
			 * btnDetached = new Button({
			 *     value: 'Button not added to the page',
			 *     className: 'wide'
			 * });
			 */
			function Button ( config ) {
			    // current execution context
			    //var self = this;
			
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.icon      && typeof config.icon      !== 'string' ) { throw new Error(__filename + ': wrong or empty config.icon'); }
			        if ( config.value     && typeof config.value     !== 'string' ) { throw new Error(__filename + ': wrong or empty config.value'); }
			    }
			
			    // set default className if classList property empty or undefined
			    config.className = 'button ' + (config.className || '');
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // optional dom
			    if ( config.icon ) {
			        // insert icon
			        this.$icon = this.$body.appendChild(document.createElement('div'));
			        this.$icon.className = 'icon ' + config.icon;
			    }
			
			    // insert caption placeholder
			    this.$text = this.$body.appendChild(document.createElement('div'));
			    this.$text.classList.add('text');
			
			    if ( config.value ) {
			        // fill it
			        this.$text.innerText = config.value;
			    }
			}
			
			
			// inheritance
			Button.prototype = Object.create(Component.prototype);
			Button.prototype.constructor = Button;
			
			
			// time to apply "click" class, does not apply if 0
			Button.prototype.clickDuration = 200;
			
			
			/**
			 * List of all default event callbacks.
			 *
			 * @type {Object.<string, function>}
			 */
			Button.prototype.defaultEvents = {
			    /**
			     * Default method to handle mouse click events.
			     */
			    click: function () {
			        // current execution context
			        var self = this;
			
			        this.$node.classList.add('click');
			
			        setTimeout(function () {
			            self.$node.classList.remove('click');
			        }, this.clickDuration);
			    },
			
			    /**
			     * Default method to handle keyboard keydown events.
			     *
			     * @param {Event} event generated event
			     */
			    keydown: function ( event ) {
			        if ( event.code === keys.ok ) {
			            // emulate click
			            // there are some listeners
			            if ( this.events['click'] ) {
			                /**
			                 * Mouse click event emulation.
			                 *
			                 * @event module:stb/ui/button~Button#click
			                 *
			                 * @type {Object}
			                 * @property {Event} event click event data
			                 */
			                this.emit('click', {event: event});
			            }
			        }
			    }
			};
			
			
			// public
			module.exports = Button;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/button.js"))

/***/ },
/* 34 */
/*!*************************************!*\
  !*** ./app/js/stb/ui/check.list.js ***!
  \*************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 *@ module stb/ui/check.list
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var List = __webpack_require__(/*! ../ui/list */ 8),
			    CheckBox = __webpack_require__(/*! ../ui/check.box */ 10);
			
			
			/**
			 * Base check list implementation
			 *
			 * @constructor
			 * @extends List
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 *
			 * @example
			 * var CheckList = require('../stb/ui/check.list'),
			 *     new CheckList({
			 *         focusIndex:0,
			 *         data:[
			 *            {state:false, title:'Some title', value:'val1'},
			 *            {state:true, title:'Some title 1', value:'val2'},
			 *            {state:false, title:'Some title 2', value:'val3'}
			 *            ]
			 *     });
			 */
			function CheckList ( config ) {
			
			    config.className = 'checkList ' + (config.className || '');
			
			    List.call(this, config);
			
			    this.addListener('click:item', function ( event ) {
			        var item = event.$item;
			
			        item.checkBox.set(!item.checkBox.value);
			        item.state = item.checkBox.value;
			
			    });
			
			}
			
			
			CheckList.prototype = Object.create(List.prototype);
			CheckList.prototype.constructor = CheckList;
			
			/**
			 * Default render function
			 *
			 * @param {Element} $item in list
			 * @param {array} data to render layout element
			 * @param {string} [data.title] title of checkbox
			 * @param {boolean} [data.state] state of checkbox: checked or not
			 * @param {string} [data.value] special value of item
			 */
			CheckList.prototype.renderItemDefault = function ( $item, data ) {
			    var table = document.createElement('table'),
			        tr = document.createElement('tr'),
			        td = document.createElement('td'),
			        check = new CheckBox({
			            value: data.state || false
			        });
			
			    $item.innerHTML = '';
			
			    table.appendChild(tr);
			
			    td.appendChild(check.$node);
			    td.className = 'checkBoxWrapper';
			    tr.appendChild(td);
			
			    td = document.createElement('td');
			    td.className = 'title';
			    td.innerText = data.title || '';
			    tr.appendChild(td);
			
			    $item.checkBox = check;
			
			    $item.state = check.value;
			    $item.value = data.value;
			
			
			    $item.appendChild(table);
			
			};
			
			CheckList.prototype.renderItem = CheckList.prototype.renderItemDefault;
			
			
			module.exports = CheckList;
			


/***/ },
/* 35 */
/*!********************************!*\
  !*** ./app/js/stb/ui/clock.js ***!
  \********************************/
/***/ function(module, exports, __webpack_require__) {

			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1);
			
			function Clock ( config ) {
			    var body = document.createElement('div');
			
			    // sanitize
			    config = config || {};
			
			    // can't accept focus
			    config.focusable = false;
			    // set default className if classList property empty or undefined
			    config.className = 'clock ' + (config.className || '');
			    // hide by default
			    config.visible = config.visible || true;
			
			    // parent constructor call
			    Component.call(this, config);
			
			    function setTime () {
			        var time  = new Date(),
			            year  = time.getFullYear(),
			            month = time.getMonth() + 1,
			            date  = time.getDate(),
			            hours = time.getHours(),
			            mins  = time.getMinutes();
			
			        body.innerText = (hours > 9 ? hours : '0' + hours) + ':' + (mins > 9 ? mins : '0' + mins);
			        //pmDate.innerText = (date > 9 ? date : '0' + date) + '.' + (month > 9 ? month : '0' + month) + '.' + year;
			    }
			
			    setTime();
			    setInterval(setTime, 1000);
			
			    this.$node.appendChild(body);
			}
			
			// inheritance
			Clock.prototype = Object.create(Component.prototype);
			Clock.prototype.constructor = Clock;
			
			module.exports = Clock;


/***/ },
/* 36 */
/*!*********************************!*\
  !*** ./app/js/stb/ui/footer.js ***!
  \*********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/footer
			 // @author Fedotov <d.fedotov@infomir.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    Page = __webpack_require__(/*! ./page */ 13),
			    keys = __webpack_require__(/*! ../keys */ 2),
			    dom = __webpack_require__(/*! ../dom */ 4);
			
			
			/**
			 * Footer.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} config={} init parameters
			 * @param {Object} config.parent parent page
			 * @param {boolean} [config.visible] visibility flag
			 * @param {Array} [config.data] buttons config
			 * @param {Object} [config.data.type] button type
			 * @param {string} [config.data.title] button title
			 * @param {Function} [config.data.action] button press (click) action
			 *
			 * @example
			 * page.footer = new Footer({
			 *        parent: page,
			 *        data: [
			 *            {type: 'menu', action: function () {}},
			 *            {type: 'f1', title: 'stop', action: function () {}},
			 *            {type: 'f2', title: 'start', action: function () {}},
			 *            {type: 'f4', title: 'end', action: function () {}}
			 *        ]
			 *    });
			 * page.add(page.footer);
			 */
			function Footer ( config ) {
			    var self;
			
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( !config.parent || !(config.parent instanceof Page) ) { throw new Error(__filename + ': wrong or empty config.parent'); }
			    }
			
			    // can't accept focus
			    config.focusable = false;
			    // set default className if classList property empty or undefined
			    config.className = 'footer ' + (config.className || '');
			    // hide by default
			    config.visible = config.visible || false;
			    // create centered div
			    config.$body = document.createElement('div');
			    config.$body.className = 'body';
			
			    // parent constructor call
			    Component.call(this, config);
			
			    this.tabs = [];
			
			    this.tab = 0;
			
			    this.$node.appendChild(dom.tag('table', {},
			        dom.tag('tr', {},
			            dom.tag('td', {},
			                dom.tag('div', {className: 'icon menu'})
			            ),
			            dom.tag('td', {className: 'central'},
			                this.tabs[0] = dom.tag('div', {className: 'wrapper hidden'},
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'}))
			                ),
			                this.tabs[1] = dom.tag('div', {className: 'wrapper hidden'},
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'}))
			                ),
			                this.tabs[2] = dom.tag('div', {className: 'wrapper hidden'},
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'}))
			                ),
			                this.tabs[3] = dom.tag('div', {className: 'wrapper hidden'},
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'})),
			                    dom.tag('div', {className: 'button'}, dom.tag('div', {className: 'iconImg'}), dom.tag('div', {className: 'title'}))
			                )
			            ),
			            dom.tag('td', {},
			                this.$info = dom.tag('div', {className: 'icon info'})
			            )
			        )
			    ));
			
			    this.init(config.data);
			
			    self = this;
			
			    this.parent.addListener('keydown', function ( event ) {
			        var currTab = self.tabs[self.tab];
			
			        if ( self.visible ) {
			            switch ( event.code ) {
			                case keys.f1:
			                    if ( currTab.f1 && typeof currTab.f1.action === 'function' ) { currTab.f1.action(); }
			                    break;
			                case keys.f2:
			                    if ( currTab.f2 && typeof currTab.f2.action === 'function' ) { currTab.f2.action(); }
			                    break;
			                case keys.f3:
			                    if ( currTab.f3 && typeof currTab.f3.action === 'function' ) { currTab.f3.action(); }
			                    break;
			                case keys.f4:
			                    if ( currTab.f4 && typeof currTab.f4.action === 'function' ) { currTab.f4.action(); }
			                    break;
			                case keys.menu:
			                    if ( currTab.menu && typeof currTab.menu.action === 'function' ) { currTab.menu.action(); }
			                    break;
			                case keys.info:
			                    if ( self.$info.style.display !== 'none' ) {
			                        if ( currTab.classList.contains('hidden') ) {
			                            currTab.classList.remove('hidden');
			                        } else {
			                            currTab.classList.add('hidden');
			                        }
			                    }
			                    break;
			            }
			        }
			    });
			}
			
			
			// inheritance
			Footer.prototype = Object.create(Component.prototype);
			Footer.prototype.constructor = Footer;
			
			
			/**
			 * Redefine buttons
			 *
			 * @param {Array} [config] buttons config
			 * @param {Object} [config.type] f1 button config, if false button will be hidden
			 * @param {Object} [config.title] f1 button title
			 * @param {Object} [config.action] f1 button press (click) action
			 *
			 * @example
			 * page.Footer.init([
			 *            {type: 'menu', action: function () {}},
			 *            {type: 'f1', title: 'stop', action: function () {}},
			 *            {type: 'f2', title: 'start', action: function () {}},
			 *            {type: 'f4', title: 'end', action: function () {}}
			 *    ]);
			 */
			Footer.prototype.init = function ( config ) {
			    var visible = !this.tabs[this.tab].classList.contains('hidden'),
			        tab = 0,
			        i;
			
			    config = config || [];
			    this.tabs[this.tab].classList.add('hidden');
			    this.$info.style.display = 'none';
			    config.forEach(function ( item ) { if ( ['f1', 'f2', 'f3', 'f4'].indexOf(item.type) !== -1 ) { tab++; } });
			    this.tab = tab === 0 ? 0 : tab - 1;
			    tab = 0;
			
			    for ( i = 0; i < config.length; i++ ) {
			        if ( true ) {
			            if ( ['f1', 'f2', 'f3', 'f4', 'menu', 'info'].indexOf(config[i].type) === -1 ) {
			                throw new Error(__filename + ': allowed footer buttons are: f1, f2, f3, f4, menu, info');
			            }
			        }
			        this.tabs[this.tab][config[i].type] = {action: config[i].action};
			        if ( config[i].type === 'menu' ) { continue; } // menu button has only action
			        this.tabs[this.tab].children[tab].children[0].className = 'iconImg ' + config[i].type;
			        this.tabs[this.tab].children[tab].children[1].innerText = config[i].title;
			        tab++;
			    }
			    if ( tab ) { this.$info.style.display = 'block'; }
			    if ( visible && tab ) { this.tabs[this.tab].classList.remove('hidden'); }
			};
			
			
			// public
			module.exports = Footer;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/footer.js"))

/***/ },
/* 37 */
/*!********************************!*\
  !*** ./app/js/stb/ui/input.js ***!
  \********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/input
			 * @author Igor Zaporozhets <deadbyelpy@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2);
			
			
			/**
			 * Base input field implementation.
			 * Has two types: text and password.
			 * Password - replace real text with '*', but real text presents in the own property 'value'.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {string} [config.value='text'] input text value
			 * @param {string} [config.placeholder='password'] placeholder text value
			 * @param {string} [config.type=Input.TYPE_TEXT] input type
			 * @param {string} [config.direction='ltr'] symbol direction ('rtl' - right to left, 'ltr' - left to right)
			 *
			 * @example
			 * var Input = require('stb/ui/input'),
			 *     input = new Input({
			 *         placeholder: 'input password'
			 *         events: {
			 *             input: function ( event ) {
			 *                 debug.log(event.value);
			 *             }
			 *         }
			 *     });
			 */
			function Input ( config ) {
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string'   ) { throw new Error(__filename + ': wrong or empty config.className'); }
			    }
			
			    /**
			     * Text value of input.
			     *
			     * @type {string}
			     */
			    this.value = '';
			
			    /**
			     * Input type, now available only text and password.
			     * Different logic with different types.
			     * TYPE_TEXT - normal input.
			     * TYPE_PASSWORD - hidden input, all chars replaced with '*', but real value is located in 'this.value'.
			     *
			     * @type {number}
			     */
			    this.type = this.TYPE_TEXT;
			
			    // set default className if classList property empty or undefined
			    config.className = 'input ' + (config.className || '');
			
			    // Set maximum input length
			    config.maxlength = config.maxlength || 0;
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // insert text line
			    this.$line = this.$body.appendChild(document.createElement('div'));
			    // correct class
			    this.$line.className = 'line';
			
			    // element to show current cursor position
			    this.$caret = this.$line.appendChild(document.createElement('div'));
			    // correct class
			    this.$caret.className = 'caret';
			
			    // hint element with placeholder text
			    this.$placeholder = this.$line.appendChild(document.createElement('div'));
			    // correct class
			    this.$placeholder.className = 'placeholder';
			
			    // setup caret index
			    this.$caret.index = 0;
			
			    // component setup
			    this.init(config);
			}
			
			
			// inheritance
			Input.prototype = Object.create(Component.prototype);
			Input.prototype.constructor = Input;
			
			// input types
			Input.prototype.TYPE_TEXT     = 0;
			Input.prototype.TYPE_PASSWORD = 1;
			
			
			/**
			 * List of all default event callbacks.
			 *
			 * @type {Object.<string, function>}
			 */
			Input.prototype.defaultEvents = {
			    /**
			     * Default method to handle keyboard keypress events.
			     *
			     * @param {Event} event generated event
			     */
			    keypress: function ( event ) {
			        this.addChar(String.fromCharCode(event.keyCode), this.$caret.index);
			    },
			
			    /**
			     * Default method to handle keyboard keydown events.
			     *
			     * @param {Event} event generated event
			     */
			    keydown: function ( event ) {
			        switch ( event.code ) {
			            case keys['delete']:
			                this.removeChar(this.$caret.index);
			                break;
			
			            case keys.back:
			                this.removeChar(this.$caret.index - 1);
			                break;
			
			            case keys.left:
			                this.setCaretPosition(this.$caret.index - 1);
			                break;
			
			            case keys.right:
			                this.setCaretPosition(this.$caret.index + 1);
			                break;
			
			            case keys.end:
			            case keys.down:
			                this.setCaretPosition(this.value.length);
			                break;
			
			            case keys.home:
			            case keys.up:
			                this.setCaretPosition(0);
			                break;
			
			            default:
			                break;
			        }
			    }
			};
			
			
			/**
			 * Init or re-init of the component inner structures and HTML.
			 *
			 * @param {Object} config init parameters (subset of constructor config params)
			 */
			Input.prototype.init = function ( config ) {
			    if ( true ) {
			        if ( config.type && Number(config.type) !== config.type ) { throw new Error(__filename + ': config.type must be a number'); }
			        if ( config.type && config.type !== this.TYPE_TEXT && config.type !== this.TYPE_PASSWORD ) { throw new Error(__filename + ': config.type must be one of the TYPE_* constant'); }
			        if ( config.value && typeof config.value !== 'string' ) { throw new Error(__filename + ': config.value must be a string'); }
			        if ( config.placeholder && typeof config.placeholder !== 'string' ) { throw new Error(__filename + ': config.placeholder must be a string'); }
			        if ( config.direction && typeof config.direction !== 'string' ) { throw new Error(__filename + ': config.direction must be a string'); }
			        if ( config.direction && config.direction !== 'ltr' && config.direction !== 'rtl' ) { throw new Error(__filename + ': config.direction wrong value'); }
			    }
			
			    // type passed
			    if ( config.type ) {
			        // apply
			        this.type = config.type;
			    }
			
			    // default value passed
			    if ( config.value ) {
			        // apply
			        this.setValue(config.value);
			    }
			
			    // hint
			    if ( config.placeholder ) {
			        // apply
			        this.$placeholder.innerText = config.placeholder;
			    }
			
			    this.maxlength = config.maxlength;
			
			    // char direction
			    this.$line.dir = config.direction || 'ltr';
			};
			
			
			/**
			 * Add given char to given position.
			 * Also moving caret in every action.
			 * Do nothing if position is < 0, or if index more or equals to length add char to the end.
			 *
			 * @param {string} char symbol to add
			 * @param {number} [index=this.value.length] given position
			 *
			 * @fires module:stb/ui/input~Input#input
			 */
			Input.prototype.addChar = function ( char, index ) {
			    var $char = document.createElement('div');
			
			    index = (index === undefined) ? this.$caret.index : index;
			
			    if ( true ) {
			        if ( index < 0 ) { throw new Error(__filename + ': index must be more than 0 or equal to 0'); }
			        if ( typeof char !== 'string' ) { throw new Error(__filename + ': char must be a string'); }
			        if ( char.length !== 1 ) { throw new Error(__filename + ': char must be a string with length = 1'); }
			    }
			
			    // remove hint
			    if ( this.value.length === 0 ) {
			        this.$line.removeChild(this.$placeholder);
			    }
			
			    if ( !this.maxlength || index <= this.maxlength ) {
			        // settings class name for span which presents one symbol in virtual input
			        $char.className = 'char';
			
			        // insert char into value
			        this.value = this.value.substring(0, index) + char + this.value.substring(index, this.value.length);
			
			        // move caret
			        ++this.$caret.index;
			
			        if ( this.type === this.TYPE_PASSWORD ) {
			            $char.innerText = '*';
			        } else if ( char === ' ' ) {
			            $char.innerHTML = '&nbsp;';
			        } else {
			            $char.innerText = char;
			        }
			
			        if ( index >= this.value.length ) { // add char to the end, move caret to the end
			            this.$line.appendChild($char);
			            this.$line.appendChild(this.$caret);
			        } else { // move caret before index, append span before caret
			            this.$line.insertBefore(this.$caret, this.$line.children[index]);
			            this.$line.insertBefore($char, this.$caret);
			        }
			
			        // there are some listeners
			        if ( this.events['input'] ) {
			            // notify listeners
			            this.emit('input', {value: this.value});
			        }
			    }
			};
			
			
			/**
			 * Remove char from given position.
			 * Do nothing if index is out of the range (0, length).
			 *
			 * @param {number} [index=this.$caret.index - 1] index given position
			 *
			 * @fires module:stb/ui/input~Input#input
			 */
			Input.prototype.removeChar = function ( index ) {
			    var prevValue = this.value;
			
			    index = (index === undefined) ? this.$caret.index - 1 : index;
			    // non-empty string
			    if ( this.value.length > 0 ) {
			        if ( true ) {
			            if ( index < 0 ) { throw new Error(__filename + ': index must be a positive value'); }
			            if ( index > this.value.length ) { throw new Error(__filename + ': index must be a less than or equal to total length'); }
			        }
			
			        if ( this.$caret.index === index && index < this.value.length ) {
			            // remove char after caret
			            this.$line.removeChild(this.$line.children[index + 1]);
			        } else if ( this.$caret.index > index ) {
			            // remove char before caret
			            --this.$caret.index;
			            this.$line.removeChild(this.$line.children[index]);
			        }
			
			        // cut one char from the value
			        this.value = this.value.substring(0, index) + this.value.substring(index + 1, this.value.length);
			
			        // there are some listeners and value was changed
			        if ( this.events['input'] && prevValue !== this.value ) {
			            // notify listeners
			            this.emit('input', {value: this.value});
			        }
			    }
			
			    // only hint
			    if ( this.value.length === 0 ) {
			        this.$line.appendChild(this.$placeholder);
			    }
			};
			
			
			/**
			 * Move caret to the given position.
			 * Do nothing if index is out of the range (0, this.value.length).
			 *
			 * @param {number} index given position
			 */
			Input.prototype.setCaretPosition = function ( index ) {
			    // check boundaries and current position
			    if ( index >= 0 && index <= this.value.length && this.$caret.index !== index ) {
			        // extract caret
			        this.$line.removeChild(this.$caret);
			
			        // apply
			        if ( index === this.value.length ) {
			            // add to the end
			            this.$line.appendChild(this.$caret);
			        } else {
			            this.$line.insertBefore(this.$caret, this.$line.children[index]);
			        }
			
			        this.$caret.index = index;
			    }
			};
			
			
			/**
			 * Setting new text value of the input field.
			 *
			 * @param {string} value given string value
			 */
			Input.prototype.setValue = function ( value ) {
			    var oldLength = this.value.length,
			        newLength = value.length,
			        i = 0,
			        $char, diff;
			
			    if ( true ) {
			        if ( typeof value !== 'string' ) { throw new Error(__filename + ': value must be a string'); }
			    }
			
			    // return if no changes
			    if ( value === this.value ) {
			        return;
			    }
			
			    // non-empty string
			    if ( newLength > 0 ) {
			        // no hint
			        if ( this.$placeholder.parentNode === this.$line ) {
			            this.$line.removeChild(this.$placeholder);
			        }
			
			        // no cursor
			        this.$line.removeChild(this.$caret);
			
			        // value length has changed
			        if ( newLength !== oldLength ) {
			            diff = newLength - oldLength;
			
			            // need to correct char divs amount
			            if ( diff > 0 ) {
			                // add missing chars
			                for ( i = 0; i < diff; i++ ) {
			                    $char = this.$line.appendChild(document.createElement('div'));
			                    $char.className = 'char';
			                }
			            } else {
			                // remove unnecessary chars
			                for ( i = 0; i > diff; i-- ) {
			                    this.$line.removeChild(this.$line.lastChild);
			                }
			            }
			        }
			
			        // apply value
			        for ( i = 0; i < newLength; i++ ) {
			            $char = this.$line.children[i];
			
			            if ( this.type === this.TYPE_PASSWORD ) {
			                $char.innerHTML = '*';
			            } else if ( value[i] === ' ' ) {
			                $char.innerHTML = '&nbsp;';
			            } else {
			                $char.innerText = value[i];
			            }
			        }
			
			        this.value = value;
			        this.$caret.index = newLength;
			        this.$line.appendChild(this.$caret);
			    } else {
			        // empty string
			        this.value = '';
			        this.$line.innerText = '';
			        this.$line.appendChild(this.$caret);
			        this.$line.appendChild(this.$placeholder);
			        this.$caret.index = 0;
			    }
			
			    // there are some listeners
			    if ( this.events['input'] ) {
			        // notify listeners
			        this.emit('input', {value: this.value});
			    }
			};
			
			
			// public
			module.exports = Input;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/input.js"))

/***/ },
/* 38 */
/*!*********************************!*\
  !*** ./app/js/stb/ui/layout.js ***!
  \*********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/layout
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2);
			
			
			/**
			 * Layout component implementation
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object}   [config={}]          init parameters (all inherited from the parent)
			 * @param {Array} [config.data] array of items to add to layout
			 *
			 * @example
			 * var Layout = require ('../stb/ui/layout'),
			 *     layout = new Layout({
			 *         data:[
			 *             'Some text'
			 *             {
			 *                 className: 'icon star',
			 *             },
			 *             {
			 *                 value: new Input()
			 *             },
			 *             new Button({'value:'Ok'})
			 *             ]
			 *         });
			 */
			function Layout ( config ) {
			
			    // sanitize
			    config = config || {};
			
			    /**
			     * Index of focused child component
			     * @type {number}
			     */
			    this.focusIndex = 0;
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			    }
			
			    config.className = 'layout ' + (config.className || '');
			
			    /**
			     * Component data
			     * @type {Array}
			     */
			    this.data = [];
			
			    Component.call(this, config);
			
			    this.init(config);
			
			    // add listener to move focus between children
			    this.addListener('keydown', function ( event ) {
			        switch ( event.code ) {
			            case keys.right:
			                if ( this.children.length && this.focusIndex < this.children.length - 1 ) {
			                    this.children[++this.focusIndex].focus();
			                }
			                break;
			            case keys.left:
			                if ( this.children.length && this.focusIndex > 0 ) {
			                    this.children[--this.focusIndex].focus();
			                }
			                break;
			            case keys.back:
			                // focus parent
			                this.parent.focus();
			
			                // focus parent focused item if parent is layout list
			                if ( this.parent &&  this.$parentItem ) {
			                    this.parent.focusItem(this.$parentItem);
			                }
			                break;
			        }
			    });
			
			}
			
			
			Layout.prototype = Object.create(Component.prototype);
			Layout.prototype.constructor = Layout;
			
			
			/**
			 * Init or re-init of the component inner structures and HTML.
			 *
			 * @param {Object} config init parameters (subset of constructor config params)
			 */
			Layout.prototype.init = function ( config ) {
			    var self = this,
			        data = normalize(config.data),
			        item, $wrapper, i;
			
			    // clear element if reinit
			    while (this.$node.firstChild) {
			        this.$node.removeChild(this.$node.firstChild);
			    }
			
			    this.data = data;
			
			    for ( i = 0; i < data.length; i++ ) {
			        item = data[i];
			        // plain text
			        if ( typeof item.value === 'string' ) {
			            $wrapper = document.createElement('div');
			            $wrapper.textContent = item.value;
			            if ( item.className ) { $wrapper.className = item.className; }
			            this.$node.appendChild($wrapper);
			        } else if ( item.value instanceof HTMLElement ) {
			            // HTML Element
			
			            // if with wrapper
			            if ( item.wrap ) {
			                $wrapper = document.createElement('div');
			                if ( item.className ) { $wrapper.className = item.className; }
			                $wrapper.appendChild(item.value);
			                this.$node.appendChild($wrapper);
			            } else {
			                // without wrapper
			                this.$node.appendChild(item.value);
			            }
			        } else if ( item.value instanceof Component ) {
			            // component
			            // force propagate events
			            item.value.propagate = true;
			
			            // set index to current component
			            item.value.index = this.children.length;
			
			            // change layout focus index if click component
			            item.value.addListener('click', function () {
			                self.focusIndex = this.index;
			            });
			
			            // append component
			            if ( item.wrap ) {
			                // with wrapper
			                $wrapper = document.createElement('div');
			                if ( item.className ) { $wrapper.className = item.className; }
			                $wrapper.appendChild(item.value.$node);
			                this.$node.appendChild($wrapper);
			                this.children.push(item.value);
			                item.value.parent = this;
			            } else {
			                // without wrapper
			                this.add(item.value);
			            }
			        }
			    }
			};
			
			
			/**
			 * Make all the data items identical.
			 * Wrap to objects if necessary.
			 *
			 * @param {Array} data incoming array
			 * @return {Array} reworked incoming data
			 */
			function normalize ( data ) {
			    var i, item;
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( !Array.isArray(data) ) { throw new Error(__filename + ': wrong data type'); }
			    }
			
			    // rows
			    for ( i = 0; i < data.length; i++ ) {
			        // cell value
			        item = data[i];
			        // plain text
			        if ( typeof item !== 'object' ) {
			            // wrap with defaults
			            data[i] = {
			                value: data[i],
			                wrap : true
			            };
			        } else {
			            // HTML element or component
			            if ( item instanceof Component || item instanceof HTMLElement ) {
			                data[i] = {
			                    value: item,
			                    wrap : false
			                };
			            } else {
			                data[i].wrap = true;
			            }
			        }
			    }
			    return data;
			}
			
			
			module.exports = Layout;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/layout.js"))

/***/ },
/* 39 */
/*!***********************************!*\
  !*** ./app/js/stb/ui/panel.ex.js ***!
  \***********************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/panelEx
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1);
			
			/**
			 * Extended panel implementation
			
			 * @constructor
			 * @extends Component
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {boolean} [config.main=false] set panel as main
			 * @param {boolean} [config.size=1] size of panel width
			 *
			 * @example
			 *
			 * main = new Panel({
			 *            size: 1,
			 *             title:'Left Panel',
			 *             main:true,
			 *             children:[
			 *                 new List({
			 *                     data:['1 bla', '2 bro', '3 car', '4 hoho', 'Search'],
			 *                     size:5
			 *                 })
			 *             ]
			 *         });
			 */
			function PanelEx ( config ) {
			    var $overlay;
			
			    config = config || {};
			
			    /**
			     * Size of panel
			     *
			     * @type {number}
			     */
			    this.size = 1;
			
			    /**
			     * Set panel as main
			     *
			     * @type {boolean}
			     */
			    this.main = false;
			
			    /**
			     * Index in panel set
			     *
			     * @type {number}
			     */
			    this.index = 0;
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.title && typeof config.title !== 'string' ) { throw new Error(__filename + ': wrong  config.title'); }
			    }
			
			    // set default className if classList property empty or undefined
			    config.className = 'panelEx ' + (config.className || '');
			
			    config.$body = document.createElement('div');
			    config.$body.className = 'body';
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // add special class to set component size
			    if ( config.size ) {
			        this.size = config.size;
			        this.$node.classList.add('size' + config.size);
			    }
			
			    // add special class to set main panel
			    if ( config.main ) {
			        this.main = true;
			        this.$node.classList.add('main');
			    }
			
			    // create elements to set as component shadow
			    this.$shadow = {
			        left:document.createElement('div'),
			        right:document.createElement('div')
			    };
			
			    this.$shadow.left.className = 'shadow left';
			    this.$node.appendChild(this.$shadow.left);
			
			    this.$shadow.right.className = 'shadow right';
			    this.$node.appendChild(this.$shadow.right);
			
			    // add title to panel
			    if ( config.title ) {
			        this.$title = document.createElement('div');
			        this.$title.className = 'title';
			        this.$title.innerText = config.title;
			        this.$node.appendChild(this.$title);
			    }
			
			    this.$node.appendChild(this.$body);
			
			    $overlay = document.createElement('div');
			    $overlay.className = 'overlay';
			    this.$node.appendChild($overlay);
			}
			
			// inheritance
			PanelEx.prototype = Object.create(Component.prototype);
			PanelEx.prototype.constructor = PanelEx;
			
			
			/**
			 * Default events
			 *
			 * @type {{focus: Function}} try to focus first child component if it present
			 */
			PanelEx.prototype.defaultEvents = {
			    focus : function () {
			        if ( this.children.length ) {
			            this.children[0].focus();
			        }
			    }
			};
			
			
			/**
			 * Redefine default component focus to set panel as active even when give focus to children components
			 */
			PanelEx.prototype.focus = function () {
			    this.parent.panels[this.parent.focusIndex].$node.classList.remove('active');
			    this.parent.panels[this.parent.focusIndex].$node.classList.remove('top');
			    Component.prototype.focus.call(this);
			    this.parent.focusIndex = this.index;
			    this.$node.classList.add('active');
			    this.$node.classList.add('top');
			    if ( this.index === 0 && this.parent.panels[1] && this.parent.panels[1].main ) {
			        this.parent.panels[1].$node.classList.remove('position-left');
			        this.parent.panels[1].$node.classList.add('position-right');
			        if ( this.parent.panels[2] ) {
			            this.parent.panels[2].$node.classList.remove('expand');
			        }
			        this.$node.classList.add('expand');
			
			    } else if ( this.index === 2 && this.parent.panels[1].main ) {
			        this.parent.panels[1].$node.classList.remove('position-right');
			        this.parent.panels[1].$node.classList.add('position-left');
			        this.parent.panels[0].$node.classList.remove('expand');
			        this.$node.classList.add('expand');
			    }
			
			};
			
			/**
			 * Blur panel
			 */
			PanelEx.prototype.blur = function () {
			    this.parent.panels[this.parent.focusIndex].$node.classList.remove('active');
			    Component.prototype.blur.call(this);
			};
			
			if ( true ) {
			    // expose to the global scope
			    window.ComponentPanelEx = PanelEx;
			}
			
			
			// public
			module.exports = PanelEx;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/panel.ex.js"))

/***/ },
/* 40 */
/*!************************************!*\
  !*** ./app/js/stb/ui/panel.set.js ***!
  \************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/panel.set
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1),
			    keys      = __webpack_require__(/*! ../keys */ 2);
			
			/**
			 * Magsdk panel set implementation
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {array} [config.panels] array of panels to use
			 * @param {array} [config.focusIndex=0] focus panel index
			 * @constructor
			 * @extends Component
			 */
			function PanelSet ( config ) {
			    var self = this,
			        i;
			
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.panels && !Array.isArray(config.panels) || !config.panels.length       ) { throw new Error(__filename + ': wrong config.panels type'); }
			    }
			
			
			    // can't accept focus
			    config.focusable = config.focusable || false;
			
			    // set default className if classList property empty or undefined
			    config.className = 'panelSet ' + (config.className || '');
			
			    // parent constructor call
			    Component.call(this, config);
			
			    this.panels = config.panels;
			
			    /**
			     * Index of current active panel
			     *
			     * @type {number}
			     */
			    this.focusIndex = 0;
			
			    // set special panels classes
			    if ( config.panels && !config.panels[0].main ) {
			        config.panels[0].$node.classList.add('left');
			        // small resolution special class
			        config.panels[0].$node.classList.add('expand');
			    }
			
			    if (  config.panels && config.panels[1] && config.panels[1].main ) {
			        // small resolution special class
			        config.panels[1].$node.classList.add('position-right');
			    }
			
			    if ( config.panels && config.panels[2] && config.panels[1].main ) {
			        config.panels[2].$node.classList.add('right');
			    }
			
			    // add panels
			    if ( config.panels ) {
			        this.add.apply(this, config.panels);
			    }
			
			
			    // add special listener
			    for ( i = 0; i < this.panels.length; i++ ) {
			        this.panels[i].addListeners({
			            keydown: keydownHandler
			        });
			        // set panels indexes
			        this.panels[i].index = i;
			    }
			
			
			    // panel keydown handler to set focus panel
			    function keydownHandler ( e ) {
			        switch ( e.code ) {
			            case keys.left:
			                if ( self.focusIndex > 0 ) {
			                    self.panels[self.focusIndex - 1].focus();
			                }
			                break;
			            case keys.right:
			                if ( self.focusIndex < self.panels.length - 1 ) {
			                    self.panels[self.focusIndex + 1].focus();
			                }
			                break;
			        }
			    }
			
			    /*if ( config.focusIndex && config.focusIndex < this.panels.length ) {
			     this.panels[config.focusIndex].focus();
			     } else {
			     if ( config.panels.length > 1 ) {
			     this.panels[1].focus();
			     } else {
			     this.panels[0].focus();
			     }
			     }*/
			}
			
			PanelSet.prototype = Object.create(Component.prototype);
			PanelSet.prototype.constructor = PanelSet;
			
			
			/**
			 * Draw panels shadow after adding to DOM
			 */
			PanelSet.prototype.drawShadow = function  () {
			    var i, height, top;
			
			
			    for ( i = 0; i < this.panels.length; i++ ) {
			        if ( i > 0 ) {
			            this.panels[i].$node.classList.add('leftShadow');
			            height = this.panels[i - 1].$node.offsetHeight;
			            top = this.panels[i - 1].$node.offsetTop - this.panels[i].$node.offsetTop;
			            if ( this.panels[i].$node.offsetHeight > height ) {
			                this.panels[i].$shadow.left.style.height = this.panels[i - 1].$node.offsetHeight + 'px';
			                this.panels[i].$shadow.left.style.top = top + 'px';
			            } else {
			                this.panels[i].$shadow.left.style.height = this.panels[i].$node.offsetHeight + 'px';
			            }
			
			        }
			
			        if ( i < this.panels.length - 1 ) {
			            this.panels[i].$node.classList.add('rightShadow');
			
			            height = this.panels[i + 1].$node.offsetHeight;
			            top = this.panels[i + 1].$node.offsetTop - this.panels[i].$node.offsetTop;
			
			            if ( this.panels[i].$node.offsetHeight > height ) {
			                this.panels[i].$shadow.right.style.height = this.panels[i - 1].$node.offsetHeight + 'px';
			                this.panels[i].$shadow.right.style.top = top + 'px';
			            } else {
			                this.panels[i].$shadow.right.style.height = this.panels[i].$node.offsetHeight + 'px';
			            }
			        }
			    }
			};
			
			/**
			 *
			 */
			PanelSet.prototype.focus = function () {
			    Component.prototype.focus.call(this);
			    this.panels[this.focusIndex].focus();
			};
			
			/**
			 *
			 */
			PanelSet.prototype.blur = function () {
			    this.panels[this.focusIndex].blur();
			};
			
			module.exports = PanelSet;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/panel.set.js"))

/***/ },
/* 41 */
/*!*************************************!*\
  !*** ./app/js/stb/ui/radio.list.js ***!
  \*************************************/
/***/ function(module, exports, __webpack_require__) {

			/**
			 * @module stb/ui/radio.list
			 * @author Aleynikov Boris <alynikov.boris@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var List = __webpack_require__(/*! ../ui/list */ 8),
			    CheckBox = __webpack_require__(/*! ../ui/check.box */ 10),
			    counter = 0;
			
			
			/**
			 * Base radio list implementation
			 *
			 * @constructor
			 * @extends List
			 *
			 * @param {Object}   [config={}]          init parameters (all inherited from the parent)
			 *
			 * @example
			 * var RadioList = require('../stb/ui/radio.list'),
			 *     rList = new RadioList({
			 *         focusIndex:0,
			 *         data:[
			 *            {state:false, title:'Some title', value:'string'},
			 *            {state:true, title:'Some title 1', value:'number'},
			 *            {state:false, title:'Some title 2', value:'object'}
			 *         ]
			 *     });
			 */
			function RadioList ( config ) {
			    var self = this;
			
			    /**
			     * Checkbox group name
			     *
			     * @type {string}
			     */
			    this.group = 'group ' +  counter++;
			
			    /**
			     * Link to checked item
			     *
			     * @type {Element}
			     */
			    this.$checkedData = null;
			
			    config.className = 'radioList ' + (config.className || '');
			
			    List.call(this, config);
			
			    this.addListener('click:item', function ( event ) {
			        var item = event.$item;
			
			        item.checkBox.set(true);
			        item.state = item.checkBox.value;
			        item.data.state = item.checkBox.value;
			
			        if ( self.$checkedData !== item.data ) {
			            /**
			             * Select element from list.
			             *
			             * @event
			             *
			             * @type {Object}
			             * @property {Element} previous selected element
			             * @property {Element} current selected element
			             */
			            self.emit('select', {
			                $last: self.$checkedData,
			                $curr: item
			            });
			            self.$checkedData.state = false;
			            self.$checkedData = item.data;
			        }
			
			    });
			
			}
			
			
			RadioList.prototype = Object.create(List.prototype);
			RadioList.prototype.constructor = RadioList;
			
			RadioList.prototype.group = 0;
			
			/**
			 * Default render function
			 *
			 * @param {Element} $item in list
			 * @param {array} data to render layout element
			 * @param {string} [data.title] title of checkbox
			 * @param {boolean} [data.state] state of checkbox: checked or not
			 * @param {string} [data.value] special value of item
			 */
			RadioList.prototype.renderItemDefault = function ( $item, data ) {
			    var table = document.createElement('table'),
			        tr = document.createElement('tr'),
			        td = document.createElement('td'),
			        check = new CheckBox({
			            group: this.group
			        });
			
			    $item.innerHTML = '';
			
			    // set state with set function to prevent multiple true values
			    if ( data.state ) {
			        check.set(true);
			        // set link to checked item
			    }
			
			    table.appendChild(tr);
			
			    td.appendChild(check.$node);
			    td.className = 'checkBoxWrapper';
			    tr.appendChild(td);
			
			    td = document.createElement('td');
			    td.className = 'title';
			    td.innerText = data.title || '';
			    tr.appendChild(td);
			
			    $item.checkBox = check;
			
			    $item.state = check.value;
			    $item.value = data.value;
			
			
			    $item.appendChild(table);
			
			};
			
			RadioList.prototype.setData = function ( config ) {
			    var i;
			
			    List.prototype.setData.call(this, config);
			
			    for ( i = 0; i < this.data.length; i++ ) {
			        if ( this.data[i].state ) {
			            this.$checkedData = this.data[i];
			            break;
			        }
			    }
			
			};
			
			RadioList.prototype.renderItem = RadioList.prototype.renderItemDefault;
			
			
			module.exports = RadioList;


/***/ },
/* 42 */
/*!*************************************!*\
  !*** ./app/js/stb/ui/scroll.bar.js ***!
  \*************************************/
/***/ function(module, exports, __webpack_require__) {

			/* WEBPACK VAR INJECTION */(function(__filename) {/**
			 * @module stb/ui/scroll.bar
			 * @author Igor Zaporozhets <deadbyelpy@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var Component = __webpack_require__(/*! ../component */ 1);
			
			
			/**
			 * Base scroll bar implementation.
			 *
			 * @constructor
			 * @extends Component
			 *
			 * @param {Object} [config={}] init parameters (all inherited from the parent)
			 * @param {number} [config.value=0] initial thumb position
			 * @param {number} [config.realSize=100] actual scroll size
			 * @param {number} [config.viewSize=10] visible area size
			 * @param {number} [config.type] direction
			 *
			 * @example
			 * var ScrollBar = require('stb/ui/scroll.bar'),
			 *     scrollBar = new ScrollBar({
			 *         viewSize: 5,
			 *         realSize: 25,
			 *         events: {
			 *             done: function () {
			 *                 debug.log('ScrollBar: done');
			 *             },
			 *             change: function ( data ) {
			 *                 debug.log('ScrollBar: change to ' + data.curr + ' from ' + data.prev);
			 *             }
			 *         }
			 *     });
			 */
			function ScrollBar ( config ) {
			    // sanitize
			    config = config || {};
			
			    if ( true ) {
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			        // init parameters checks
			        if ( config.className && typeof config.className !== 'string' ) { throw new Error(__filename + ': wrong or empty config.className'); }
			        if ( config.type      && Number(config.type) !== config.type  ) { throw new Error(__filename + ': config.type must be a number'); }
			    }
			
			    /**
			     * Visible area size.
			     *
			     * @type {number}
			     */
			    this.viewSize = 10;
			
			    /**
			     * Scroll area actual height or width (if scroll is horizontal).
			     *
			     * @type {number}
			     */
			    this.realSize = 100;
			
			    /**
			     * Scroll thumb position.
			     *
			     * @type {number}
			     */
			    this.value = 0;
			
			    /**
			     * Component orientation.
			     *
			     * @type {number}
			     */
			    this.type = this.TYPE_VERTICAL;
			
			    /**
			     * Geometry of the scroll thumb element.
			     *
			     * @type {ClientRect}
			     */
			    this.thumbRect = null;
			
			    /**
			     * Geometry of the scroll track element.
			     *
			     * @type {ClientRect}
			     */
			    this.trackRect = null;
			
			    // can't accept focus
			    config.focusable = config.focusable || false;
			
			    // set default className if classList property empty or undefined
			    config.className = 'scrollBar ' + (config.className || '');
			
			    // horizontal or vertical
			    if ( config.type ) {
			        // apply
			        this.type = config.type;
			    }
			
			    if ( this.type === this.TYPE_HORIZONTAL ) {
			        config.className += ' horizontal';
			    }
			
			    // parent constructor call
			    Component.call(this, config);
			
			    // insert thumb line
			    this.$thumb = this.$body.appendChild(document.createElement('div'));
			
			    // correct CSS class name
			    this.$thumb.className = 'thumb';
			
			    // component setup
			    this.init(config);
			}
			
			
			// inheritance
			ScrollBar.prototype = Object.create(Component.prototype);
			ScrollBar.prototype.constructor = ScrollBar;
			
			
			ScrollBar.prototype.TYPE_VERTICAL   = 1;
			ScrollBar.prototype.TYPE_HORIZONTAL = 2;
			
			
			/**
			 * Init or re-init realSize/viewSize/value parameters.
			 *
			 * @param {Object} config init parameters (subset of constructor config params)
			 */
			ScrollBar.prototype.init = function ( config ) {
			    config = config || {};
			
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( typeof config !== 'object' ) { throw new Error(__filename + ': wrong config type'); }
			    }
			
			    // set actual scroll size
			    if ( config.realSize !== undefined ) {
			        if ( true ) {
			            if ( Number(config.realSize) !== config.realSize ) { throw new Error(__filename + ': config.realSize value must be a number'); }
			        }
			        // apply
			        this.realSize = config.realSize;
			    }
			
			    // set visible area size
			    if ( config.viewSize !== undefined ) {
			        if ( true ) {
			            if ( Number(config.viewSize) !== config.viewSize ) { throw new Error(__filename + ': config.viewSize value must be a number'); }
			            if ( config.viewSize < 0 ) { throw new Error(__filename + ': config.viewSize value must be greater than 0'); }
			        }
			        // apply
			        this.viewSize = config.viewSize;
			    }
			
			    // show or hide thumb
			    if ( this.viewSize >= this.realSize ) {
			        this.$thumb.classList.add('hidden');
			    } else {
			        this.$thumb.classList.remove('hidden');
			    }
			
			    // set thumb position
			    if ( config.value !== undefined ) {
			        // apply
			        this.scrollTo(config.value);
			    }
			
			    // set thumb size
			    if ( this.type === this.TYPE_VERTICAL ) {
			        this.$thumb.style.height = (this.viewSize / this.realSize * 100) + '%';
			    } else {
			        this.$thumb.style.width = (this.viewSize / this.realSize * 100) + '%';
			    }
			
			    // geometry
			    this.thumbRect = this.$thumb.getBoundingClientRect();
			    this.trackRect = this.$node.getBoundingClientRect();
			};
			
			
			/**
			 * Set position of the given value.
			 * Does nothing in case when scroll is in the end and passed value is more than scroll bar length.
			 *
			 * @param {number} value new value to set
			 * @return {boolean} operation result
			 *
			 * @fires module:stb/ui/scroll.bar~ScrollBar#done
			 * @fires module:stb/ui/scroll.bar~ScrollBar#change
			 */
			ScrollBar.prototype.scrollTo = function ( value ) {
			    if ( true ) {
			        if ( arguments.length !== 1 ) { throw new Error(__filename + ': wrong arguments number'); }
			        if ( Number(value) !== value ) { throw new Error(__filename + ': value must be a number'); }
			        if ( this.realSize > this.viewSize && value > this.realSize - this.viewSize ) { throw new Error(__filename + ': value is greater than this.realSize-this.viewSize'); }
			        if ( value < 0 ) { throw new Error(__filename + ': value is less then 0'); }
			    }
			
			    // value has changed
			    if ( this.value !== value ) {
			        // track and thumb geometry was not set
			        if ( this.thumbRect.height === 0 || this.thumbRect.width === 0 ) {
			            // apply
			            this.trackRect = this.$node.getBoundingClientRect();
			            this.thumbRect = this.$thumb.getBoundingClientRect();
			        }
			
			        // set scroll bar width
			        if ( this.type === this.TYPE_VERTICAL ) {
			            this.$thumb.style.marginTop = ((this.trackRect.height - this.thumbRect.height) * value / (this.realSize - this.viewSize)) + 'px';
			        } else {
			            this.$thumb.style.marginLeft = ((this.trackRect.width - this.thumbRect.width) * value / (this.realSize - this.viewSize)) + 'px';
			        }
			
			        // there are some listeners
			        if ( this.events['change'] ) {
			            /**
			             * Update scroll value.
			             *
			             * @event module:stb/ui/scroll.bar~ScrollBar#change
			             *
			             * @type {Object}
			             * @property {number} prev old/previous scroll value
			             * @property {number} curr new/current scroll value
			             */
			            this.emit('change', {curr: value, prev: this.value});
			        }
			
			        // is it the end?
			        if ( value >= this.realSize ) {
			            value = this.realSize;
			
			            // there are some listeners
			            if ( this.events['done'] ) {
			                /**
			                 * Set scroll to its maximum value.
			                 *
			                 * @event module:stb/ui/scroll.bar~ScrollBar#done
			                 */
			                this.emit('done');
			            }
			        }
			
			        // set new value
			        this.value = value;
			
			        return true;
			    }
			
			    // nothing was done
			    return false;
			};
			
			
			// public
			module.exports = ScrollBar;
			
			/* WEBPACK VAR INJECTION */}.call(exports, "app/js/stb/ui/scroll.bar.js"))

/***/ },
/* 43 */
/*!**************************!*\
  !*** ./config/logger.js ***!
  \**************************/
/***/ function(module, exports) {

			/**
			 * WebSocket logging server configuration.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    // turn on/off server
			    active: false,
			
			    // listening port
			    port: 8010
			};


/***/ },
/* 44 */
/*!*************************!*\
  !*** ./config/proxy.js ***!
  \*************************/
/***/ function(module, exports) {

			/**
			 * Code-proxy server configuration.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    // turn on/off server
			    active: true,
			
			    // listening HTTP port to serve proxy files
			    portHttp: 8800,
			
			    // listening WebSocket port to serve requests
			    portWs: 8900,
			
			    // time between connection/sending attempts (in ms)
			    retryDelay: 100,
			
			    // amount of connection/sending attempts before give up
			    retryLimit: 30,
			
			    // full logging
			    logging: false,
			
			    // session name
			    name: 'anonymous',
			
			    // use localStorage to get/save requests data
			    cache: true
			};


/***/ },
/* 45 */
/*!**************************!*\
  !*** ./config/static.js ***!
  \**************************/
/***/ function(module, exports) {

			/**
			 * HTTP static server configuration.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    // turn on/off server
			    active: true,
			
			    // listening HTTP port to serve project files
			    port: 8000,
			
			    // static file server cache activation
			    // false to disable of amount of seconds to cache
			    cache: false,
			
			    // full logging
			    logging: true,
			
			    // enable automatic reload on file changes mode
			    // set boolean value "true" to work on the default port 35729
			    // or specify some custom port value
			    livereload: true
			};


/***/ },
/* 46 */
/*!**************************!*\
  !*** ./config/weinre.js ***!
  \**************************/
/***/ function(module, exports) {

			/**
			 * WEb INspector REmote debugger server configuration.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			// public
			module.exports = {
			    // turn on/off server
			    active: true,
			
			    // listening HTTP port to provide client interface
			    port: 8080,
			
			    // address to listen
			    host: '-all-',
			
			    // full logging
			    logging: false,
			
			    // debug servers session id
			    name: 'anonymous'
			};


/***/ },
/* 47 */
/*!*********************************!*\
  !*** ./~/cjs-async/parallel.js ***!
  \*********************************/
/***/ function(module, exports) {

			/**
			 * @author Stanislav Kalashnik <darkpark.main@gmail.com>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/**
			 * Parallel tasks execution.
			 *
			 * @param {function[]} tasks set of tasks to execute
			 * @param {function} [callback] optional callback to run once all the tasks have completed
			 */
			module.exports = function ( tasks, callback ) {
			    var isError = false,
			        counter = 0,
			        outList = [],
			        outHash = {};
			
			    function handler ( task, index ) {
			        var done = function ( error, result ) {
			            if ( error ) {
			                // exit this task
			                // and prevent other to callback
			                isError = true;
			
			                if ( typeof callback === 'function' ) {
			                    callback(error);
			                }
			
			                return;
			            }
			
			            // fill results
			            outList[index] = result;
			            if ( task.name ) {
			                outHash[task.name] = result;
			            }
			
			            counter++;
			
			            // all tasks are processed
			            if ( counter >= tasks.length && typeof callback === 'function' ) {
			                callback(null, outList, outHash);
			            }
			        };
			
			        // error happened in some other task
			        if ( isError ) {
			            // callback was already used
			            return;
			        }
			
			        // actual call condition
			        if ( task.length === 0 ) {
			            done(null, task());
			        } else {
			            task(done);
			        }
			    }
			
			    // sanitize
			    tasks = Array.isArray(tasks) ? tasks : [];
			
			    // no tasks were given
			    if ( tasks.length === 0 ) {
			        if ( typeof callback === 'function' ) {
			            // empty result
			            callback(null, outList, outHash);
			        }
			    } else {
			        // run all tasks
			        tasks.forEach(handler);
			    }
			};


/***/ },
/* 48 */
/*!**************************************!*\
  !*** ./~/code-proxy/client/guest.js ***!
  \**************************************/
/***/ function(module, exports) {

			/**
			 * Client-side guest part.
			 *
			 * @author DarkPark
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/**
			 * @constructor
			 *
			 * @param {Object} [options] set of initialization parameters (host, port, name)
			 */
			function ProxyGuest ( options ) {
				// prepare
				var name;
			
				// connection with server
				this.active = false;
			
				/**
				 * proxy instance configuration
				 * @namespace
				 */
				this.config = {
					// node.js server address
					host: '127.0.0.1',
			
					// http server port
					port: 8800,
			
					// session name
					name: 'anonymous',
			
					// cached url for posting requests
					urlPost: '',
			
					// cached url for info collecting
					urlInfo: ''
				};
			
				// single ajax object for performance
				this.xhr = new XMLHttpRequest();
			
				// validate and iterate input
				if ( options && typeof options === 'object' ) {
					for ( name in options ) {
						// rewrite defaults
						if ( options.hasOwnProperty(name) ) {
							this.config[name] = options[name];
						}
					}
				}
			
				// there may be some special chars
				name = encodeURIComponent(this.config.name);
			
				// cache final request urls
				this.config.urlPost = 'http://' + this.config.host + ':' + this.config.port + '/' + name;
				this.config.urlInfo = 'http://' + this.config.host + ':' + this.config.port + '/info/' + name;
			
				// check initial connection status
				this.active = this.info().active;
			
				console.log('%c[core]\t%c%s\t%c0\t%cconnection to the host %c(%s:%s): %c%s',
					'color:grey',
					'color:purple', this.config.name,
					'color:grey',
					'color:black',
					'color:grey', this.config.host, this.config.port,
					'color:' + (this.active ? 'green' : 'red'), this.active ? 'available' : 'not available'
				);
			}
			
			
			/**
			 * Sends a synchronous request to the host system.
			 *
			 * @param {Object} request JSON data to send
			 * @return {*} execution result from the host
			 */
			ProxyGuest.prototype.send = function ( request ) {
				// prepare
				var time = +new Date(),
					response;
			
				// mandatory init check
				if ( !this.config.urlPost ) {
					return false;
				}
			
				// make request
				this.xhr.open('post', this.config.urlPost, false);
				this.xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
				this.xhr.send(JSON.stringify(request));
			
				// proceed the result
				try {
					response = JSON.parse(this.xhr.responseText);
				} catch ( e ) {
					response = {error: e};
				}
			
				// update connection status
				this.active = !response.error;
			
				// detailed report
				console.groupCollapsed('%c[%s]\t%c%s\t%c%s\t%c%s',
					'color:grey;font-weight:normal', request.type,
					'color:purple;font-weight:normal', this.config.name,
					'color:grey;font-weight:normal', +new Date() - time,
					'color:' + (response.error ? 'red' : 'green'), request.method || request.code
				);
				if ( request.params !== undefined ) { console.log('%c%s:\t', 'font-weight:bold', 'Params', request.params); }
				if ( response.data  !== undefined ) { console.log('%c%s:\t', 'font-weight:bold', 'Result', response.data); }
				if ( response.error !== undefined ) { console.error(response.error); }
				console.groupEnd();
			
				// ready
				return response.data;
			};
			
			
			/**
			 * Wrapper to send a line of js code to eval on the host.
			 *
			 * @param {String} code javascript source code to execute on the device
			 * @return {*} execution result from the host
			 */
			ProxyGuest.prototype.eval = function ( code ) {
				return this.send({
					type: 'eval',
					code: code
				});
			};
			
			
			/**
			 * Wrapper to send one function of js code with arguments to eval on the host.
			 *
			 * @param {String} method javascript function name (like "encodeURIComponent")
			 * @param {Array} params list of the function arguments
			 * @param {String} [context=window] remote call context
			 * @return {*} execution result from the host
			 */
			ProxyGuest.prototype.call = function ( method, params, context ) {
				return this.send({
					type:    'call',
					method:  method,
					params:  params,
					context: context
				});
			};
			
			
			/**
			 * Wrapper to send a var name to get json.
			 *
			 * @param {String} name javascript var name to serialize
			 * @return {*} execution result from the host
			 */
			ProxyGuest.prototype.json = function ( name ) {
				var data = this.send({
					type: 'json',
					code: name
				});
			
				return data ? JSON.parse(data) : null;
			};
			
			
			/**
			 * Wrapper to send a var name to get json.
			 *
			 * @param {String} name javascript var name to serialize
			 * @return {*} execution result from the host
			 */
			ProxyGuest.prototype.hook = function ( name ) {
				var data = this.send({
					type: 'hook',
					name: name
				});
			
				return data ? JSON.parse(data) : null;
			};
			
			
			/**
			 * Gets the detailed info about the current connection.
			 *
			 * @return {{active:Boolean, count:Number}|{active:Boolean}|Boolean} info
			 */
			ProxyGuest.prototype.info = function () {
				// mandatory init check
				if ( !this.config.urlInfo ) {
					return false;
				}
			
				// make request
				this.xhr.open('get', this.config.urlInfo, false);
				this.xhr.send();
			
				return JSON.parse(this.xhr.responseText || false);
			};
			
			
			// CommonJS modules support
			if ( typeof module !== 'undefined' && module.exports ) {
				module.exports = ProxyGuest;
			}


/***/ },
/* 49 */
/*!*************************************!*\
  !*** ./~/code-proxy/client/host.js ***!
  \*************************************/
/***/ function(module, exports) {

			/**
			 * Client-side host part.
			 *
			 * @author DarkPark
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			/**
			 * @constructor
			 *
			 * @param {Object} [options] set of initialization parameters (host, port, name)
			 */
			function ProxyHost ( options ) {
				// prepare
				var name;
			
				// connection with server
				this.active = false;
			
				/**
				 * proxy instance configuration
				 * @namespace
				 */
				this.config = {
					/** proxy server address */
					host : '127.0.0.1',
			
					/** proxy server websocket port */
					port : 8900,
			
					/** session name */
					name : 'anonymous',
			
					/** automatically try to restore connection on disconnect */
					reconnect : true,
			
					/** time between connection attempts (5s) */
					reconnectInterval : 5000
				};
			
				/**
				 * @type {WebSocket}
				 */
				this.socket = null;
			
				// validate and iterate input
				if ( options && typeof options === 'object' ) {
					for ( name in options ) {
						// rewrite defaults
						if ( options.hasOwnProperty(name) ) { this.config[name] = options[name]; }
					}
				}
			
				// try to establish connection
				this.connect();
			}
			
			
			/**
			 * Connect to the proxy server
			 */
			ProxyHost.prototype.connect = function () {
				// prepare
				var self = this;
			
				// establish the connection
				// there may be some special chars in name
				this.socket = new WebSocket('ws://' + this.config.host + ':' + this.config.port + '/' + encodeURIComponent(this.config.name));
			
				/**
				 * event hook
				 * @callback
				 */
				this.socket.onopen = function () {
					self.log('core', 0, true, 'connection established');
			
					self.active = true;
				};
			
				/**
				 * event hook
				 * @callback
				 */
				this.socket.onclose = function () {
					self.log('core', 0, false, 'no connection');
			
					self.active = false;
			
					if ( self.config.reconnect ) {
						setTimeout(function () {
							self.connect();
						}, self.config.reconnectInterval);
					}
				};
			
				/**
				 * Message from a desktop browser.
				 *
				 * @callback
				 */
				this.socket.onmessage = function ( message ) {
					// prepare
					var response = {time:+new Date()},
						request, context;
			
					// proceed the message
					try {
						request = JSON.parse(message.data || false);
						switch ( request.type ) {
							case 'call':
								context = request.context ? eval(request.context) : window;
								response.data = eval(request.method).apply(context, request.params);
								break;
							case 'eval':
								response.data = eval(request.code);
								break;
							case 'json':
								response.data = JSON.stringify(eval(request.code));
								break;
							default:
								response.error = 'invalid incoming request';
						}
					} catch ( e ) {
						response.error = e.toString();
					}
			
					// time taken
					response.time = +new Date() - response.time;
					// wrap and send back
					this.send(JSON.stringify(response));
			
					// detailed report
					self.log(request.type, response.time, !response.error, request.method || request.code, request.params);
				};
			};
			
			
			/**
			 * Finish the connection and strop reconnection if any.
			 */
			ProxyHost.prototype.disconnect = function () {
				// stop auto connection
				this.config.reconnect = false;
				this.socket.close();
			};
			
			
			/**
			 * Logging wrapper.
			 *
			 * @param {String} type
			 * @param {Number} time
			 * @param {Boolean} status
			 * @param {String} message
			 * @param {*} [params]
			 */
			ProxyHost.prototype.log = function ( type, time, status, message, params ) {
				console.log('%c[%s]\t%c%s\t%c%s\t%c%s\t',
					'color:grey', type,
					'color:purple', this.config.name,
					'color:grey', time,
					'color:' + (status ? 'green' : 'red'), message,
					params || ''
				);
			};
			
			
			// CommonJS modules support
			if ( typeof module !== 'undefined' && module.exports ) {
				module.exports = ProxyHost;
			}


/***/ },
/* 50 */
/*!***************************************!*\
  !*** ./~/gremlins.js/gremlins.min.js ***!
  \***************************************/
/***/ function(module, exports, __webpack_require__) {

			var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;/**
			 * This file is generated. Don't modify it directly. (c)
			 */
			
			/**
			 * almond 0.2.6 Copyright (c) 2011-2012, The Dojo Foundation All Rights Reserved.
			 * Available via the MIT or new BSD license.
			 * see: http://github.com/jrburke/almond for details
			 */
			
			//  Chance.js 0.5.4
			//  http://chancejs.com
			//  (c) 2013 Victor Quinn
			//  Chance may be freely distributed or modified under the MIT license.
			
			/**
			 * gremlins.js 0.1.0 Copyright (c) 2014, marmelab
			 * Available via the MIT license.
			 * see: http://github.com/marmelab/gremlins.js for details
			 */
			
			(function(e,t){ true?!(__WEBPACK_AMD_DEFINE_FACTORY__ = (t), __WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ? (__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) : __WEBPACK_AMD_DEFINE_FACTORY__), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)):typeof exports=="object"?module.exports=t():e.gremlins=t()})(this,function(){var e,t,n;return function(r){function d(e,t){return h.call(e,t)}function v(e,t){var n,r,i,s,o,u,a,f,c,h,p=t&&t.split("/"),d=l.map,v=d&&d["*"]||{};if(e&&e.charAt(0)===".")if(t){p=p.slice(0,p.length-1),e=p.concat(e.split("/"));for(f=0;f<e.length;f+=1){h=e[f];if(h===".")e.splice(f,1),f-=1;else if(h===".."){if(f===1&&(e[2]===".."||e[0]===".."))break;f>0&&(e.splice(f-1,2),f-=2)}}e=e.join("/")}else e.indexOf("./")===0&&(e=e.substring(2));if((p||v)&&d){n=e.split("/");for(f=n.length;f>0;f-=1){r=n.slice(0,f).join("/");if(p)for(c=p.length;c>0;c-=1){i=d[p.slice(0,c).join("/")];if(i){i=i[r];if(i){s=i,o=f;break}}}if(s)break;!u&&v&&v[r]&&(u=v[r],a=f)}!s&&u&&(s=u,o=a),s&&(n.splice(0,o,s),e=n.join("/"))}return e}function m(e,t){return function(){return s.apply(r,p.call(arguments,0).concat([e,t]))}}function g(e){return function(t){return v(t,e)}}function y(e){return function(t){a[e]=t}}function b(e){if(d(f,e)){var t=f[e];delete f[e],c[e]=!0,i.apply(r,t)}if(!d(a,e)&&!d(c,e))throw new Error("No "+e);return a[e]}function w(e){var t,n=e?e.indexOf("!"):-1;return n>-1&&(t=e.substring(0,n),e=e.substring(n+1,e.length)),[t,e]}function E(e){return function(){return l&&l.config&&l.config[e]||{}}}var i,s,o,u,a={},f={},l={},c={},h=Object.prototype.hasOwnProperty,p=[].slice;o=function(e,t){var n,r=w(e),i=r[0];return e=r[1],i&&(i=v(i,t),n=b(i)),i?n&&n.normalize?e=n.normalize(e,g(t)):e=v(e,t):(e=v(e,t),r=w(e),i=r[0],e=r[1],i&&(n=b(i))),{f:i?i+"!"+e:e,n:e,pr:i,p:n}},u={require:function(e){return m(e)},exports:function(e){var t=a[e];return typeof t!="undefined"?t:a[e]={}},module:function(e){return{id:e,uri:"",exports:a[e],config:E(e)}}},i=function(e,t,n,i){var s,l,h,p,v,g=[],w;i=i||e;if(typeof n=="function"){t=!t.length&&n.length?["require","exports","module"]:t;for(v=0;v<t.length;v+=1){p=o(t[v],i),l=p.f;if(l==="require")g[v]=u.require(e);else if(l==="exports")g[v]=u.exports(e),w=!0;else if(l==="module")s=g[v]=u.module(e);else if(d(a,l)||d(f,l)||d(c,l))g[v]=b(l);else{if(!p.p)throw new Error(e+" missing "+l);p.p.load(p.n,m(i,!0),y(l),{}),g[v]=a[l]}}h=n.apply(a[e],g);if(e)if(s&&s.exports!==r&&s.exports!==a[e])a[e]=s.exports;else if(h!==r||!w)a[e]=h}else e&&(a[e]=n)},e=t=s=function(e,t,n,a,f){return typeof e=="string"?u[e]?u[e](t):b(o(e,t).f):(e.splice||(l=e,t.splice?(e=t,t=n,n=null):e=r),t=t||function(){},typeof n=="function"&&(n=a,a=f),a?i(r,e,t,n):setTimeout(function(){i(r,e,t,n)},4),s)},s.config=function(e){return l=e,l.deps&&s(l.deps,l.callback),s},e._defined=a,n=function(e,t,n){t.splice||(n=t,t=[]),!d(a,e)&&!d(f,e)&&(f[e]=[e,t,n])},n.amd={jQuery:!0}}(),n("../src/vendor/almond.js",function(){}),function(){function a(e,t){e||(e={});if(!t)return e;for(var n in t)typeof e[n]=="undefined"&&(e[n]=t[n]);return e}function f(e,t){if(e)throw new RangeError(t)}var e=9007199254740992,t=-e,r="0123456789",i="abcdefghijklmnopqrstuvwxyz",s=i.toUpperCase(),o=r+"abcdef",u=function(e){e!==undefined&&(typeof e=="function"?this.random=e:this.seed=e),typeof this.random=="undefined"&&(this.mt=this.mersenne_twister(e),this.random=function(){return this.mt.random(this.seed)})};u.prototype.bool=function(e){return e=a(e,{likelihood:50}),f(e.likelihood<0||e.likelihood>100,"Chance: Likelihood accepts values from 0 to 100."),this.random()*100<e.likelihood},u.prototype.character=function(e){e=a(e);var t="!@#$%^&*()[]",n,o;return f(e.alpha&&e.symbols,"Chance: Cannot specify both alpha and symbols."),e.casing==="lower"?n=i:e.casing==="upper"?n=s:n=i+s,e.pool?o=e.pool:e.alpha?o=n:e.symbols?o=t:o=n+r+t,o.charAt(this.natural({max:o.length-1}))},u.prototype.floating=function(t){var n,r;t=a(t,{fixed:4});var i=Math.pow(10,t.fixed);f(t.fixed&&t.precision,"Chance: Cannot specify both fixed and precision.");var s=e/i,o=-s;f(t.min&&t.fixed&&t.min<o,"Chance: Min specified is out of range with fixed. Min should be, at least, "+o),f(t.max&&t.fixed&&t.max>s,"Chance: Max specified is out of range with fixed. Max should be, at most, "+s),t=a(t,{min:o,max:s}),n=this.integer({min:t.min*i,max:t.max*i});var u=(n/i).toFixed(t.fixed);return parseFloat(u)},u.prototype.integer=function(n){var r,i;n=a(n,{min:t,max:e}),i=Math.max(Math.abs(n.min),Math.abs(n.max));do r=this.natural({max:i}),r=this.bool()?r:r*-1;while(r<n.min||r>n.max);return r},u.prototype.natural=function(t){return t=a(t,{min:0,max:e}),f(t.min>t.max,"Chance: Min cannot be greater than Max."),Math.floor(this.random()*(t.max-t.min+1)+t.min)},u.prototype.normal=function(e){e=a(e,{mean:0,dev:1});var t,n,r,i,s=e.mean,o=e.dev;do n=this.random()*2-1,r=this.random()*2-1,t=n*n+r*r;while(t>=1);return i=n*Math.sqrt(-2*Math.log(t)/t),o*i+s},u.prototype.string=function(e){e=a(e);var t=e.length||this.natural({min:5,max:20}),n="",r=e.pool;for(var i=0;i<t;i++)n+=this.character({pool:r});return n},u.prototype.capitalize=function(e){return e.charAt(0).toUpperCase()+e.substr(1)},u.prototype.mixin=function(e){var t=this;for(var n in e)u.prototype[n]=e[n];return this},u.prototype.pick=function(e,t){return!t||t===1?e[this.natural({max:e.length-1})]:this.shuffle(e).slice(0,t)},u.prototype.shuffle=function(e){var t=e.slice(0),n=[],r=0,i=Number(t.length);for(var s=0;s<i;s++)r=this.natural({max:t.length-1}),n[s]=t[r],t.splice(r,1);return n},u.prototype.paragraph=function(e){e=a(e);var t=e.sentences||this.natural({min:3,max:7}),n=[];for(var r=0;r<t;r++)n.push(this.sentence());return n.join(" ")},u.prototype.sentence=function(e){e=a(e);var t=e.words||this.natural({min:12,max:18}),n,r=[];for(var i=0;i<t;i++)r.push(this.word());return n=r.join(" "),n=this.capitalize(n)+".",n},u.prototype.syllable=function(e){e=a(e);var t=e.length||this.natural({min:2,max:3}),n="bcdfghjklmnprstvwz",r="aeiou",i=n+r,s="",o;for(var u=0;u<t;u++)u===0?o=this.character({pool:i}):n.indexOf(o)===-1?o=this.character({pool:n}):o=this.character({pool:r}),s+=o;return s},u.prototype.word=function(e){e=a(e),f(e.syllables&&e.length,"Chance: Cannot specify both syllables AND length.");var t=e.syllables||this.natural({min:1,max:3}),n="";if(e.length){do n+=this.syllable();while(n.length<e.length);n=n.substring(0,e.length)}else for(var r=0;r<t;r++)n+=this.syllable();return n},u.prototype.age=function(e){e=a(e);var t;switch(e.type){case"child":t=this.natural({min:1,max:12});break;case"teen":t=this.natural({min:13,max:19});break;case"adult":t=this.natural({min:18,max:120});break;case"senior":t=this.natural({min:65,max:120});break;default:t=this.natural({min:1,max:120})}return t},u.prototype.birthday=function(e){return e=a(e,{year:(new Date).getFullYear()-this.age(e)}),this.date(e)};var l=["Sophia","Emma","Isabella","Jacob","Mason","Ethan","Noah","Olivia","William","Liam","Jayden","Michael","Ava","Alexander","Aiden","Daniel","Matthew","Elijah","Emily","James","Anthony","Benjamin","Abigail","Joshua","Andrew","David","Joseph","Logan","Jackson","Mia","Christopher","Gabriel","Madison","Samuel","Ryan","Lucas","John","Nathan","Isaac","Dylan","Caleb","Elizabeth","Chloe","Christian","Landon","Jonathan","Carter","Ella","Luke","Owen","Brayden","Avery","Gavin","Wyatt","Addison","Isaiah","Aubrey","Henry","Eli","Hunter","Lily","Jack","Natalie","Evan","Sofia","Jordan","Nicholas","Tyler","Aaron","Charlotte","Zoey","Jeremiah","Julian","Cameron","Grace","Hannah","Amelia","Harper","Levi","Lillian","Brandon","Angel","Austin","Connor","Adrian","Robert","Samantha","Charles","Evelyn","Victoria","Thomas","Brooklyn","Sebastian","Zoe","Colton","Jaxon","Layla","Kevin","Zachary","Ayden","Dominic","Blake","Jose","Hailey","Oliver","Justin","Bentley","Leah","Jason","Chase","Ian","Kaylee","Anna","Aaliyah","Gabriella","Josiah","Allison","Parker","Xavier","Nevaeh","Alexis","Adam","Audrey","Cooper","Savannah","Sarah","Alyssa","Claire","Taylor","Riley","Camila","Nathaniel","Arianna","Ashley","Grayson","Jace","Brianna","Carson","Sophie","Peyton","Nolan","Tristan","Luis","Brody","Bella","Khloe","Genesis","Alexa","Juan","Hudson","Serenity","Kylie","Aubree","Scarlett","Bryson","Carlos","Stella","Maya","Easton","Katherine","Julia","Damian","Alex","Kayden","Ryder","Lucy","Madelyn","Jesus","Cole","Autumn","Makayla","Kayla","Mackenzie","Micah","Vincent","Max","Lauren","Jaxson","Gianna","Eric","Ariana","Asher","Hayden","Faith","Alexandra","Melanie","Sydney","Bailey","Caroline","Naomi","Morgan","Kennedy","Ellie","Jasmine","Eva","Skylar","Diego","Kimberly","Violet","Molly","Miles","Steven","Aria","Ivan","Jocelyn","Trinity","Elias","Aidan","Maxwell","London","Bryce","Lydia","Madeline","Antonio","Giovanni","Reagan","Timothy","Bryan","Piper","Andrea","Santiago","Annabelle","Maria","Colin","Richard","Braxton","Kaleb","Brooke","Kyle","Kaden","Preston","Payton","Miguel","Jonah","Paisley","Paige","Lincoln","Ruby","Nora","Riley","Mariah","Leo","Victor","Brady","Jeremy","Mateo","Brian","Jaden","Ashton","Patrick","Rylee","Declan","Lilly","Brielle","Sean","Joel","Gael","Sawyer","Alejandro","Jade","Marcus","Destiny","Leonardo","Jesse","Caden","Jake","Kaiden","Nicole","Mila","Wesley","Kendall","Liliana","Camden","Kaitlyn","Natalia","Sadie","Edward","Brantley","Jordyn","Roman","Vanessa","Mary","Mya","Penelope","Isabelle","Alice","Axel","Silas","Jude","Grant","Reese","Gabrielle","Hadley","Katelyn","Angelina","Rachel","Isabel","Eleanor","Cayden","Emmanuel","George","Clara","Brooklynn","Jessica","Maddox","Malachi","Bradley","Alan","Weston","Elena","Gage","Aliyah","Vivian","Laila","Sara","Amy","Devin","Eliana","Greyson","Lyla","Juliana","Kenneth","Mark","Oscar","Tanner","Rylan","Valeria","Adriana","Nicolas","Makenzie","Harrison","Elise","Mckenzie","Derek","Quinn","Delilah","Peyton","Ezra","Cora","Kylee","Tucker","Emmett","Avery","Cody","Rebecca","Gracie","Izabella","Calvin","Andres","Jorge","Abel","Paul","Abraham","Kai","Josephine","Alaina","Michelle","Jennifer","Collin","Theodore","Ezekiel","Eden","Omar","Jayce","Valentina","Conner","Bennett","Aurora","Catherine","Stephanie","Trevor","Valerie","Eduardo","Peter","Maximus","Jayla","Jaiden","Willow","Jameson","Seth","Daisy","Alana","Melody","Hazel","Kingston","Summer","Melissa","Javier","Margaret","Travis","Kinsley","Kinley","Garrett","Everett","Ariel","Lila","Graham","Giselle","Ryleigh","Xander","Haley","Julianna","Ivy","Alivia","Cristian","Brynn","Damien","Ryker","Griffin","Keira","Daniela","Aniyah","Angela","Kate","Londyn","Corbin","Myles","Hayden","Harmony","Adalyn","Luca","Zane","Francisco","Ricardo","Alexis","Stephen","Zayden","Megan","Allie","Gabriela","Iker","Drake","Alayna","Lukas","Presley","Charlie","Spencer","Zion","Erick","Jenna","Josue","Alexandria","Ashlyn","Adrianna","Jada","Jeffrey","Trenton","Fiona","Chance","Norah","Paxton","Elliot","Emery","Fernando","Maci","Miranda","Keegan","Landen","Ximena","Amaya","Manuel","Amir","Shane","Cecilia","Raymond","Andre","Ana","Shelby","Katie","Hope","Callie","Jordan","Luna","Leilani","Eliza","Mckenna","Angel","Genevieve","Makenna","Isla","Lola","Danielle","Chelsea","Leila","Tessa","Adelyn","Camille","Mikayla","Adeline","Adalynn","Sienna","Esther","Jacqueline","Emerson","Arabella","Maggie","Athena","Lucia","Lexi","Ayla"];u.prototype.first=function(){return this.pick(l)},u.prototype.gender=function(){return this.pick(["Male","Female"])};var c=["Smith","Johnson","Williams","Jones","Brown","Davis","Miller","Wilson","Moore","Taylor","Anderson","Thomas","Jackson","White","Harris","Martin","Thompson","Garcia","Martinez","Robinson","Clark","Rodriguez","Lewis","Lee","Walker","Hall","Allen","Young","Hernandez","King","Wright","Lopez","Hill","Scott","Green","Adams","Baker","Gonzalez","Nelson","Carter","Mitchell","Perez","Roberts","Turner","Phillips","Campbell","Parker","Evans","Edwards","Collins","Stewart","Sanchez","Morris","Rogers","Reed","Cook","Morgan","Bell","Murphy","Bailey","Rivera","Cooper","Richardson","Cox","Howard","Ward","Torres","Peterson","Gray","Ramirez","James","Watson","Brooks","Kelly","Sanders","Price","Bennett","Wood","Barnes","Ross","Henderson","Coleman","Jenkins","Perry","Powell","Long","Patterson","Hughes","Flores","Washington","Butler","Simmons","Foster","Gonzales","Bryant","Alexander","Russell","Griffin","Diaz","Hayes","Myers","Ford","Hamilton","Graham","Sullivan","Wallace","Woods","Cole","West","Jordan","Owens","Reynolds","Fisher","Ellis","Harrison","Gibson","McDonald","Cruz","Marshall","Ortiz","Gomez","Murray","Freeman","Wells","Webb","Simpson","Stevens","Tucker","Porter","Hunter","Hicks","Crawford","Henry","Boyd","Mason","Morales","Kennedy","Warren","Dixon","Ramos","Reyes","Burns","Gordon","Shaw","Holmes","Rice","Robertson","Hunt","Black","Daniels","Palmer","Mills","Nichols","Grant","Knight","Ferguson","Rose","Stone","Hawkins","Dunn","Perkins","Hudson","Spencer","Gardner","Stephens","Payne","Pierce","Berry","Matthews","Arnold","Wagner","Willis","Ray","Watkins","Olson","Carroll","Duncan","Snyder","Hart","Cunningham","Bradley","Lane","Andrews","Ruiz","Harper","Fox","Riley","Armstrong","Carpenter","Weaver","Greene","Lawrence","Elliott","Chavez","Sims","Austin","Peters","Kelley","Franklin","Lawson","Fields","Gutierrez","Ryan","Schmidt","Carr","Vasquez","Castillo","Wheeler","Chapman","Oliver","Montgomery","Richards","Williamson","Johnston","Banks","Meyer","Bishop","McCoy","Howell","Alvarez","Morrison","Hansen","Fernandez","Garza","Harvey","Little","Burton","Stanley","Nguyen","George","Jacobs","Reid","Kim","Fuller","Lynch","Dean","Gilbert","Garrett","Romero","Welch","Larson","Frazier","Burke","Hanson","Day","Mendoza","Moreno","Bowman","Medina","Fowler","Brewer","Hoffman","Carlson","Silva","Pearson","Holland","Douglas","Fleming","Jensen","Vargas","Byrd","Davidson","Hopkins","May","Terry","Herrera","Wade","Soto","Walters","Curtis","Neal","Caldwell","Lowe","Jennings","Barnett","Graves","Jimenez","Horton","Shelton","Barrett","Obrien","Castro","Sutton","Gregory","McKinney","Lucas","Miles","Craig","Rodriquez","Chambers","Holt","Lambert","Fletcher","Watts","Bates","Hale","Rhodes","Pena","Beck","Newman","Haynes","McDaniel","Mendez","Bush","Vaughn","Parks","Dawson","Santiago","Norris","Hardy","Love","Steele","Curry","Powers","Schultz","Barker","Guzman","Page","Munoz","Ball","Keller","Chandler","Weber","Leonard","Walsh","Lyons","Ramsey","Wolfe","Schneider","Mullins","Benson","Sharp","Bowen","Daniel","Barber","Cummings","Hines","Baldwin","Griffith","Valdez","Hubbard","Salazar","Reeves","Warner","Stevenson","Burgess","Santos","Tate","Cross","Garner","Mann","Mack","Moss","Thornton","Dennis","McGee","Farmer","Delgado","Aguilar","Vega","Glover","Manning","Cohen","Harmon","Rodgers","Robbins","Newton","Todd","Blair","Higgins","Ingram","Reese","Cannon","Strickland","Townsend","Potter","Goodwin","Walton","Rowe","Hampton","Ortega","Patton","Swanson","Joseph","Francis","Goodman","Maldonado","Yates","Becker","Erickson","Hodges","Rios","Conner","Adkins","Webster","Norman","Malone","Hammond","Flowers","Cobb","Moody","Quinn","Blake","Maxwell","Pope","Floyd","Osborne","Paul","McCarthy","Guerrero","Lindsey","Estrada","Sandoval","Gibbs","Tyler","Gross","Fitzgerald","Stokes","Doyle","Sherman","Saunders","Wise","Colon","Gill","Alvarado","Greer","Padilla","Simon","Waters","Nunez","Ballard","Schwartz","McBride","Houston","Christensen","Klein","Pratt","Briggs","Parsons","McLaughlin","Zimmerman","French","Buchanan","Moran","Copeland","Roy","Pittman","Brady","McCormick","Holloway","Brock","Poole","Frank","Logan","Owen","Bass","Marsh","Drake","Wong","Jefferson","Park","Morton","Abbott","Sparks","Patrick","Norton","Huff","Clayton","Massey","Lloyd","Figueroa","Carson","Bowers","Roberson","Barton","Tran","Lamb","Harrington","Casey","Boone","Cortez","Clarke","Mathis","Singleton","Wilkins","Cain","Bryan","Underwood","Hogan","McKenzie","Collier","Luna","Phelps","McGuire","Allison","Bridges","Wilkerson","Nash","Summers","Atkins"];u.prototype.last=function(){return this.pick(c)},u.prototype.name=function(e){e=a(e);var t=this.first(),n=this.last(),r;return e.middle?r=t+" "+this.first()+" "+n:e.middle_initial?r=t+" "+this.character({alpha:!0,casing:"upper"})+". "+n:r=t+" "+n,e.prefix&&(r=this.prefix()+" "+r),r},u.prototype.name_prefixes=function(){return[{name:"Doctor",abbreviation:"Dr."},{name:"Miss",abbreviation:"Miss"},{name:"Misses",abbreviation:"Mrs."},{name:"Mister",abbreviation:"Mr."}]},u.prototype.prefix=function(e){return this.name_prefix(e)},u.prototype.name_prefix=function(e){return e=a(e),e.full?this.pick(this.name_prefixes()).name:this.pick(this.name_prefixes()).abbreviation},u.prototype.color=function(e){function t(e,t){return[e,e,e].join(t||"")}e=a(e,{format:this.pick(["hex","shorthex","rgb"]),grayscale:!1});var n=e.grayscale;if(e.format==="hex")return"#"+(n?t(this.hash({length:2})):this.hash({length:6}));if(e.format==="shorthex")return"#"+(n?t(this.hash({length:1})):this.hash({length:3}));if(e.format==="rgb")return n?"rgb("+t(this.natural({max:255}),",")+")":"rgb("+this.natural({max:255})+","+this.natural({max:255})+","+this.natural({max:255})+")";throw new Error('Invalid format provided. Please provide one of "hex", "shorthex", or "rgb"')},u.prototype.domain=function(e){return e=a(e),this.word()+"."+(e.tld||this.tld())},u.prototype.email=function(e){return e=a(e),this.word()+"@"+(e.domain||this.domain())},u.prototype.fbid=function(){return parseInt("10000"+this.natural({max:1e11}),10)},u.prototype.hashtag=function(){return"#"+this.word()},u.prototype.ip=function(){return this.natural({max:255})+"."+this.natural({max:255})+"."+this.natural({max:255})+"."+this.natural({max:255})},u.prototype.ipv6=function(){var e="";for(var t=0;t<8;t++)e+=this.hash({length:4})+":";return e.substr(0,e.length-1)},u.prototype.klout=function(){return this.natural({min:1,max:99})},u.prototype.tlds=function(){return["com","org","edu","gov","co.uk","net","io"]},u.prototype.tld=function(){return this.pick(this.tlds())},u.prototype.twitter=function(){return"@"+this.word()},u.prototype.address=function(e){return e=a(e),this.natural({min:5,max:2e3})+" "+this.street(e)},u.prototype.areacode=function(e){e=a(e,{parens:!0});var t=this.natural({min:2,max:9}).toString()+this.natural({min:0,max:8}).toString()+this.natural({min:0,max:9}).toString();return e.parens?"("+t+")":t},u.prototype.city=function(){return this.capitalize(this.word({syllables:3}))},u.prototype.coordinates=function(e){return e=a(e),this.latitude(e)+", "+this.longitude(e)},u.prototype.latitude=function(e){return e=a(e,{fixed:5}),this.floating({min:-90,max:90,fixed:e.fixed})},u.prototype.longitude=function(e){return e=a(e,{fixed:5}),this.floating({min:0,max:180,fixed:e.fixed})},u.prototype.phone=function(e){e=a(e,{formatted:!0}),e.formatted||(e.parens=!1);var t=this.areacode(e).toString(),n=this.natural({min:2,max:9}).toString()+this.natural({min:0,max:9}).toString()+this.natural({min:0,max:9}).toString(),r=this.natural({min:1e3,max:9999}).toString();return e.formatted?t+" "+n+"-"+r:t+n+r},u.prototype.postal=function(){var e=this.character({pool:"XVTSRPNKLMHJGECBA"}),t=e+this.natural({max:9})+this.character({alpha:!0,casing:"upper"}),n=this.natural({max:9})+this.character({alpha:!0,casing:"upper"})+this.natural({max:9});return t+" "+n},u.prototype.provinces=function(){return[{name:"Alberta",abbreviation:"AB"},{name:"British Columbia",abbreviation:"BC"},{name:"Manitoba",abbreviation:"MB"},{name:"New Brunswick",abbreviation:"NB"},{name:"Newfoundland and Labrador",abbreviation:"NL"},{name:"Nova Scotia",abbreviation:"NS"},{name:"Ontario",abbreviation:"ON"},{name:"Prince Edward Island",abbreviation:"PE"},{name:"Quebec",abbreviation:"QC"},{name:"Saskatchewan",abbreviation:"SK"},{name:"Northwest Territories",abbreviation:"NT"},{name:"Nunavut",abbreviation:"NU"},{name:"Yukon",abbreviation:"YT"}]},u.prototype.province=function(e){return e&&e.full?this.pick(this.provinces()).name:this.pick(this.provinces()).abbreviation},u.prototype.radio=function(e){e=a(e,{side:"?"});var t="";switch(e.side.toLowerCase()){case"east":case"e":t="W";break;case"west":case"w":t="K";break;default:t=this.character({pool:"KW"})}return t+this.character({alpha:!0,casing:"upper"})+this.character({alpha:!0,casing:"upper"})+this.character({alpha:!0,casing:"upper"})},u.prototype.state=function(e){return e&&e.full?this.pick(this.states()).name:this.pick(this.states()).abbreviation},u.prototype.states=function(){return[{name:"Alabama",abbreviation:"AL"},{name:"Alaska",abbreviation:"AK"},{name:"American Samoa",abbreviation:"AS"},{name:"Arizona",abbreviation:"AZ"},{name:"Arkansas",abbreviation:"AR"},{name:"Armed Forces Europe",abbreviation:"AE"},{name:"Armed Forces Pacific",abbreviation:"AP"},{name:"Armed Forces the Americas",abbreviation:"AA"},{name:"California",abbreviation:"CA"},{name:"Colorado",abbreviation:"CO"},{name:"Connecticut",abbreviation:"CT"},{name:"Delaware",abbreviation:"DE"},{name:"District of Columbia",abbreviation:"DC"},{name:"Federated States of Micronesia",abbreviation:"FM"},{name:"Florida",abbreviation:"FL"},{name:"Georgia",abbreviation:"GA"},{name:"Guam",abbreviation:"GU"},{name:"Hawaii",abbreviation:"HI"},{name:"Idaho",abbreviation:"ID"},{name:"Illinois",abbreviation:"IL"},{name:"Indiana",abbreviation:"IN"},{name:"Iowa",abbreviation:"IA"},{name:"Kansas",abbreviation:"KS"},{name:"Kentucky",abbreviation:"KY"},{name:"Louisiana",abbreviation:"LA"},{name:"Maine",abbreviation:"ME"},{name:"Marshall Islands",abbreviation:"MH"},{name:"Maryland",abbreviation:"MD"},{name:"Massachusetts",abbreviation:"MA"},{name:"Michigan",abbreviation:"MI"},{name:"Minnesota",abbreviation:"MN"},{name:"Mississippi",abbreviation:"MS"},{name:"Missouri",abbreviation:"MO"},{name:"Montana",abbreviation:"MT"},{name:"Nebraska",abbreviation:"NE"},{name:"Nevada",abbreviation:"NV"},{name:"New Hampshire",abbreviation:"NH"},{name:"New Jersey",abbreviation:"NJ"},{name:"New Mexico",abbreviation:"NM"},{name:"New York",abbreviation:"NY"},{name:"North Carolina",abbreviation:"NC"},{name:"North Dakota",abbreviation:"ND"},{name:"Northern Mariana Islands",abbreviation:"MP"},{name:"Ohio",abbreviation:"OH"},{name:"Oklahoma",abbreviation:"OK"},{name:"Oregon",abbreviation:"OR"},{name:"Pennsylvania",abbreviation:"PA"},{name:"Puerto Rico",abbreviation:"PR"},{name:"Rhode Island",abbreviation:"RI"},{name:"South Carolina",abbreviation:"SC"},{name:"South Dakota",abbreviation:"SD"},{name:"Tennessee",abbreviation:"TN"},{name:"Texas",abbreviation:"TX"},{name:"Utah",abbreviation:"UT"},{name:"Vermont",abbreviation:"VT"},{name:"Virgin Islands, U.S.",abbreviation:"VI"},{name:"Virginia",abbreviation:"VA"},{name:"Washington",abbreviation:"WA"},{name:"West Virginia",abbreviation:"WV"},{name:"Wisconsin",abbreviation:"WI"},{name:"Wyoming",abbreviation:"WY"}]},u.prototype.street=function(e){e=a(e);var t=this.word({syllables:2});return t=this.capitalize(t),t+=" ",t+=e.short_suffix?this.street_suffix().abbreviation:this.street_suffix().name,t},u.prototype.street_suffix=function(){return this.pick(this.street_suffixes())},u.prototype.street_suffixes=function(){return[{name:"Avenue",abbreviation:"Ave"},{name:"Boulevard",abbreviation:"Blvd"},{name:"Center",abbreviation:"Ctr"},{name:"Circle",abbreviation:"Cir"},{name:"Court",abbreviation:"Ct"},{name:"Drive",abbreviation:"Dr"},{name:"Extension",abbreviation:"Ext"},{name:"Glen",abbreviation:"Gln"},{name:"Grove",abbreviation:"Grv"},{name:"Heights",abbreviation:"Hts"},{name:"Highway",abbreviation:"Hwy"},{name:"Junction",abbreviation:"Jct"},{name:"Key",abbreviation:"Key"},{name:"Lane",abbreviation:"Ln"},{name:"Loop",abbreviation:"Loop"},{name:"Manor",abbreviation:"Mnr"},{name:"Mill",abbreviation:"Mill"},{name:"Park",abbreviation:"Park"},{name:"Parkway",abbreviation:"Pkwy"},{name:"Pass",abbreviation:"Pass"},{name:"Path",abbreviation:"Path"},{name:"Pike",abbreviation:"Pike"},{name:"Place",abbreviation:"Pl"},{name:"Plaza",abbreviation:"Plz"},{name:"Point",abbreviation:"Pt"},{name:"Ridge",abbreviation:"Rdg"},{name:"River",abbreviation:"Riv"},{name:"Road",abbreviation:"Rd"},{name:"Square",abbreviation:"Sq"},{name:"Street",abbreviation:"St"},{name:"Terrace",abbreviation:"Ter"},{name:"Trail",abbreviation:"Trl"},{name:"Turnpike",abbreviation:"Tpke"},{name:"View",abbreviation:"Vw"},{name:"Way",abbreviation:"Way"}]},u.prototype.tv=function(e){return this.radio(e)},u.prototype.zip=function(e){var t="";for(var n=0;n<5;n++)t+=this.natural({max:9}).toString();if(e&&e.plusfour===!0){t+="-";for(n=0;n<4;n++)t+=this.natural({max:9}).toString()}return t},u.prototype.ampm=function(){return this.bool()?"am":"pm"},u.prototype.date=function(e){var t=this.month({raw:!0}),n;e=a(e,{year:parseInt(this.year(),10),month:t.numeric-1,day:this.natural({min:1,max:t.days}),hour:this.hour(),minute:this.minute(),second:this.second(),millisecond:this.millisecond(),american:!0,string:!1});var r=new Date(e.year,e.month,e.day,e.hour,e.minute,e.second,e.millisecond);return e.american?n=r.getMonth()+1+"/"+r.getDate()+"/"+r.getFullYear():n=r.getDate()+"/"+(r.getMonth()+1)+"/"+r.getFullYear(),e.string?n:r},u.prototype.hammertime=function(e){return this.date(e).getTime()},u.prototype.hour=function(e){e=a(e);var t=e.twentyfour?24:12;return this.natural({min:1,max:t})},u.prototype.millisecond=function(){return this.natural({max:999})},u.prototype.minute=u.prototype.second=function(){return this.natural({max:59})},u.prototype.month=function(e){e=a(e);var t=this.pick(this.months());return e.raw?t:t.name},u.prototype.months=function(){return[{name:"January",short_name:"Jan",numeric:"01",days:31},{name:"February",short_name:"Feb",numeric:"02",days:28},{name:"March",short_name:"Mar",numeric:"03",days:31},{name:"April",short_name:"Apr",numeric:"04",days:30},{name:"May",short_name:"May",numeric:"05",days:31},{name:"June",short_name:"Jun",numeric:"06",days:30},{name:"July",short_name:"Jul",numeric:"07",days:31},{name:"August",short_name:"Aug",numeric:"08",days:31},{name:"September",short_name:"Sep",numeric:"09",days:30},{name:"October",short_name:"Oct",numeric:"10",days:31},{name:"November",short_name:"Nov",numeric:"11",days:30},{name:"December",short_name:"Dec",numeric:"12",days:31}]},u.prototype.second=function(){return this.natural({max:59})},u.prototype.timestamp=function(){return this.natural({min:1,max:parseInt((new Date).getTime()/1e3,10)})},u.prototype.year=function(e){return e=a(e,{min:(new Date).getFullYear()}),e.max=typeof e.max!="undefined"?e.max:e.min+100,this.natural(e).toString()},u.prototype.cc=function(e){e=a(e);var t,n,r,i;t=e.type?this.cc_type({name:e.type,raw:!0}):this.cc_type({raw:!0}),n=t.prefix.split(""),r=t.length-t.prefix.length-1;for(var s=0;s<r;s++)n.push(this.integer({min:0,max:9}));return n.push(this.luhn_calculate(n.join(""))),n.join("")},u.prototype.cc_types=function(){return[{name:"American Express",short_name:"amex",prefix:"34",length:15},{name:"Bankcard",short_name:"bankcard",prefix:"5610",length:16},{name:"China UnionPay",short_name:"chinaunion",prefix:"62",length:16},{name:"Diners Club Carte Blanche",short_name:"dccarte",prefix:"300",length:14},{name:"Diners Club enRoute",short_name:"dcenroute",prefix:"2014",length:15},{name:"Diners Club International",short_name:"dcintl",prefix:"36",length:14},{name:"Diners Club United States & Canada",short_name:"dcusc",prefix:"54",length:16},{name:"Discover Card",short_name:"discover",prefix:"6011",length:16},{name:"InstaPayment",short_name:"instapay",prefix:"637",length:16},{name:"JCB",short_name:"jcb",prefix:"3528",length:16},{name:"Laser",short_name:"laser",prefix:"6304",length:16},{name:"Maestro",short_name:"maestro",prefix:"5018",length:16},{name:"Mastercard",short_name:"mc",prefix:"51",length:16},{name:"Solo",short_name:"solo",prefix:"6334",length:16},{name:"Switch",short_name:"switch",prefix:"4903",length:16},{name:"Visa",short_name:"visa",prefix:"4",length:16},{name:"Visa Electron",short_name:"electron",prefix:"4026",length:16}]},u.prototype.cc_type=function(e){e=a(e);var t=this.cc_types(),n=null;if(e.name){for(var r=0;r<t.length;r++)if(t[r].name===e.name||t[r].short_name===e.name){n=t[r];break}if(n===null)throw new Error("Credit card type '"+e.name+"'' is not suppoted")}else n=this.pick(t);return e.raw?n:n.name},u.prototype.dollar=function(e){e=a(e,{max:1e4,min:0});var t=this.floating({min:e.min,max:e.max,fixed:2}).toString(),n=t.split(".")[1];return n===undefined?t+=".00":n.length<2&&(t+="0"),t<0?"-$"+t.replace("-",""):"$"+t},u.prototype.exp=function(e){e=a(e);var t={};return t.year=this.exp_year(),t.year===(new Date).getFullYear()?t.month=this.exp_month({future:!0}):t.month=this.exp_month(),e.raw?t:t.month+"/"+t.year},u.prototype.exp_month=function(e){e=a(e);var t,n;if(e.future){do t=this.month({raw:!0}).numeric,n=parseInt(t,10);while(n<(new Date).getMonth())}else t=this.month({raw:!0}).numeric;return t},u.prototype.exp_year=function(){return this.year({max:(new Date).getFullYear()+10})},u.prototype.d4=function(){return this.natural({min:1,max:4})},u.prototype.d6=function(){return this.natural({min:1,max:6})},u.prototype.d8=function(){return this.natural({min:1,max:8})},u.prototype.d10=function(){return this.natural({min:1,max:10})},u.prototype.d12=function(){return this.natural({min:1,max:12})},u.prototype.d20=function(){return this.natural({min:1,max:20})},u.prototype.d30=function(){return this.natural({min:1,max:30})},u.prototype.d100=function(){return this.natural({min:1,max:100})},u.prototype.rpg=function(e,t){t=a(t);if(e===null)throw new Error("A type of die roll must be included");var n=e.toLowerCase().split("d"),r=[];if(n.length!==2||!parseInt(n[0],10)||!parseInt(n[1],10))throw new Error("Invalid format provided. Please provide #d# where the first # is the number of dice to roll, the second # is the max of each die");for(var i=n[0];i>0;i--)r[i-1]=this.natural({min:1,max:n[1]});return typeof t.sum!="undefined"&&t.sum?r.reduce(function(e,t){return e+t}):r},u.prototype.guid=function(e){e=e||{version:5};var t="ABCDEF1234567890",n="AB89",r=this.string({pool:t,length:8})+"-"+this.string({pool:t,length:4})+"-"+e.version+this.string({pool:t,length:3})+"-"+this.string({pool:n,length:1})+this.string({pool:t,length:3})+"-"+this.string({pool:t,length:12});return r},u.prototype.hash=function(e){e=a(e,{length:40,casing:"lower"});var t=e.casing==="upper"?o.toUpperCase():o;return this.string({pool:t,length:e.length})},u.prototype.luhn_check=function(e){var t=e.toString(),n=+t.substring(t.length-1);return n===this.luhn_calculate(+t.substring(0,t.length-1))},u.prototype.luhn_calculate=function(e){var t=e.toString().split("").reverse(),n=0;for(var r=0,i=t.length;i>r;++r){var s=+t[r];r%2===0&&(s*=2,s>9&&(s-=9)),n+=s}return n*9%10},u.prototype.mersenne_twister=function(e){return new h(e)},u.prototype.VERSION="0.5.4";var h=function(e){e===undefined&&(e=(new Date).getTime()),this.N=624,this.M=397,this.MATRIX_A=2567483615,this.UPPER_MASK=2147483648,this.LOWER_MASK=2147483647,this.mt=new Array(this.N),this.mti=this.N+1,this.init_genrand(e)};h.prototype.init_genrand=function(e){this.mt[0]=e>>>0;for(this.mti=1;this.mti<this.N;this.mti++)e=this.mt[this.mti-1]^this.mt[this.mti-1]>>>30,this.mt[this.mti]=(((e&4294901760)>>>16)*1812433253<<16)+(e&65535)*1812433253+this.mti,this.mt[this.mti]>>>=0},h.prototype.init_by_array=function(e,t){var n=1,r=0,i,s;this.init_genrand(19650218),i=this.N>t?this.N:t;for(;i;i--)s=this.mt[n-1]^this.mt[n-1]>>>30,this.mt[n]=(this.mt[n]^(((s&4294901760)>>>16)*1664525<<16)+(s&65535)*1664525)+e[r]+r,this.mt[n]>>>=0,n++,r++,n>=this.N&&(this.mt[0]=this.mt[this.N-1],n=1),r>=t&&(r=0);for(i=this.N-1;i;i--)s=this.mt[n-1]^this.mt[n-1]>>>30,this.mt[n]=(this.mt[n]^(((s&4294901760)>>>16)*1566083941<<16)+(s&65535)*1566083941)-n,this.mt[n]>>>=0,n++,n>=this.N&&(this.mt[0]=this.mt[this.N-1],n=1);this.mt[0]=2147483648},h.prototype.genrand_int32=function(){var e,t=new Array(0,this.MATRIX_A);if(this.mti>=this.N){var n;this.mti===this.N+1&&this.init_genrand(5489);for(n=0;n<this.N-this.M;n++)e=this.mt[n]&this.UPPER_MASK|this.mt[n+1]&this.LOWER_MASK,this.mt[n]=this.mt[n+this.M]^e>>>1^t[e&1];for(;n<this.N-1;n++)e=this.mt[n]&this.UPPER_MASK|this.mt[n+1]&this.LOWER_MASK,this.mt[n]=this.mt[n+(this.M-this.N)]^e>>>1^t[e&1];e=this.mt[this.N-1]&this.UPPER_MASK|this.mt[0]&this.LOWER_MASK,this.mt[this.N-1]=this.mt[this.M-1]^e>>>1^t[e&1],this.mti=0}return e=this.mt[this.mti++],e^=e>>>11,e^=e<<7&2636928640,e^=e<<15&4022730752,e^=e>>>18,e>>>0},h.prototype.genrand_int31=function(){return this.genrand_int32()>>>1},h.prototype.genrand_real1=function(){return this.genrand_int32()*(1/4294967295)},h.prototype.random=function(){return this.genrand_int32()*(1/4294967296)},h.prototype.genrand_real3=function(){return(this.genrand_int32()+.5)*(1/4294967296)},h.prototype.genrand_res53=function(){var e=this.genrand_int32()>>>5,t=this.genrand_int32()>>>6;return(e*67108864+t)*(1/9007199254740992)},typeof exports!="undefined"&&(typeof module!="undefined"&&module.exports&&(exports=module.exports=u),exports.Chance=u),typeof n=="function"&&n.amd&&n("vendor/chance",[],function(){return u}),typeof window=="object"&&typeof window.document=="object"&&(window.Chance=u,window.chance=new u)}(),n("utils/configurable",["require"],function(e){function t(e,t){for(var n in t)(function(n){e[n]=function(r){return arguments.length?(t[n]=r,e):t[n]}})(n)}return t}),n("species/clicker",["require","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/configurable"),n=e("../vendor/chance");return function(){function f(){var t,n,r,i,s=0;do{t=a.positionSelector(),n=t[0],r=t[1],i=e.elementFromPoint(n,r),s++;if(s>a.maxNbTries)return!1}while(!i||!a.canClick(i));var o=e.createEvent("MouseEvents"),u=a.randomizer.pick(a.clickTypes);o.initMouseEvent(u,!0,!0,window,0,0,0,0,0,!1,!1,!1,!1,0,null),i.dispatchEvent(o),typeof a.showAction=="function"&&a.showAction(n,r,u),typeof a.logger.log=="function"&&a.logger.log("gremlin","clicker   ",u,"at",n,r)}var e=window.document,r=e.body,i=["click","click","click","click","click","click","dblclick","dblclick","mousedown","mouseup","mouseover","mouseover","mouseover","mousemove","mouseout"],s=function(){return[a.randomizer.natural({max:e.documentElement.clientWidth-1}),a.randomizer.natural({max:e.documentElement.clientHeight-1})]},o=function(t,n){var i=e.createElement("div");i.style.border="3px solid red",i.style["border-radius"]="50%",i.style.width="40px",i.style.height="40px",i.style["box-sizing"]="border-box",i.style.position="absolute",i.style.webkitTransition="opacity 1s ease-out",i.style.mozTransition="opacity 1s ease-out",i.style.transition="opacity 1s ease-out",i.style.left=t-20+"px",i.style.top=n-20+"px";var s=r.appendChild(i);setTimeout(function(){r.removeChild(s)},1e3),setTimeout(function(){s.style.opacity=0},50)},u=function(){return!0},a={clickTypes:i,positionSelector:s,showAction:o,canClick:u,maxNbTries:10,logger:{},randomizer:new n};return t(f,a),f}}),n("species/formFiller",["require","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/configurable"),n=e("../vendor/chance");return function(){function u(){var t=[],n=d();for(var r in o.elementMapTypes)o.elementMapTypes.hasOwnProperty(r)&&t.push(r);var i,s=0;do{var u=e.querySelectorAll(t.join(","));if(u.length===0)return!1;i=o.randomizer.pick(u),s++;if(s>o.maxNbTries)return!1}while(!i||!o.canFillElement(i));var a=null;for(var f in o.elementMapTypes)if(i[n](f)){a=f;break}var l=o.elementMapTypes[a](i);typeof o.showAction=="function"&&o.showAction(i),typeof o.logger.log=="function"&&o.logger.log("gremlin","formFiller","input",l,"in",i)}function a(e){var t=o.randomizer.character();return e.value+=t,t}function f(e){var t=o.randomizer.character({pool:"0123456789"});return e.value+=t,t}function l(e){var t=e.querySelectorAll("option"),n=o.randomizer.pick(t);for(var r=0,i=t.length;r<i;r++){var s=t[r];s.selected=s.value==n.value}return n.value}function c(t){var n=e.createEvent("MouseEvents");return n.initMouseEvent("click",!0,!0,window,0,0,0,0,0,!1,!1,!1,!1,0,null),t.dispatchEvent(n),t.value}function h(t){var n=e.createEvent("MouseEvents");return n.initMouseEvent("click",!0,!0,window,0,0,0,0,0,!1,!1,!1,!1,0,null),t.dispatchEvent(n),t.value}function p(e){var t=o.randomizer.email();return e.value=t,t}function d(){var t=e.querySelector("body");return(t.mozMatchesSelector||t.msMatchesSelector||t.oMatchesSelector||t.webkitMatchesSelector).name}var e=window.document,r={'input[type="text"]':a,'input[type="password"]':a,'input[type="number"]':f,select:l,'input[type="radio"]':c,'input[type="checkbox"]':h,'input[type="email"]':p,"input:not([type])":a},i=function(e){typeof e.attributes["data-old-border"]=="undefined"&&(e.attributes["data-old-border"]=e.style.border);var t=e.attributes["data-old-border"];e.style.border="1px solid red",setTimeout(function(){e.style.border=t},500)},s=function(){return!0},o={elementMapTypes:r,showAction:i,canFillElement:s,maxNbTries:10,logger:{},randomizer:new n};return t(u,o),u}}),n("species/scroller",["require","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/configurable"),n=e("../vendor/chance");return function(){function a(){var e=u.positionSelector(),t=e[0],n=e[1];window.scrollTo(t,n),typeof u.showAction=="function"&&u.showAction(t,n),typeof u.logger.log=="function"&&u.logger.log("gremlin","scroller  ","scroll to",t,n)}var e=window.document,r=e.documentElement,i=e.body,s=function(){var e=Math.max(i.scrollWidth,i.offsetWidth,r.scrollWidth,r.offsetWidth,r.clientWidth),t=Math.max(i.scrollHeight,i.offsetHeight,r.scrollHeight,r.offsetHeight,r.clientHeight);return[u.randomizer.natural({max:e-r.clientWidth}),u.randomizer.natural({max:t-r.clientHeight})]},o=function(t,n){var s=e.createElement("div");s.style.border="3px solid red",s.style.width=r.clientWidth-25+"px",s.style.height=r.clientHeight-25+"px",s.style.position="absolute",s.style.webkitTransition="opacity 1s ease-out",s.style.mozTransition="opacity 1s ease-out",s.style.transition="opacity 1s ease-out",s.style.left=t+10+"px",s.style.top=n+10+"px";var o=i.appendChild(s);setTimeout(function(){i.removeChild(o)},1e3),setTimeout(function(){o.style.opacity=0},50)},u={positionSelector:s,showAction:o,logger:{},randomizer:new n};return t(a,u),a}}),n("species/typer",["require","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/configurable"),n=e("../vendor/chance");return function(){function a(){var t=Math.max(i.scrollWidth,i.offsetWidth,r.scrollWidth,r.offsetWidth,r.clientWidth),n=Math.max(i.scrollHeight,i.offsetHeight,r.scrollHeight,r.offsetHeight,r.clientHeight),s=e.createEvent("KeyboardEvent"),o=typeof s.initKeyboardEvent!="undefined"?"initKeyboardEvent":"initKeyEvent",a=u.randomizer.natural({max:360}),f=u.randomizer.natural({max:r.clientWidth-1}),l=u.randomizer.natural({max:r.clientHeight-1}),c=e.elementFromPoint(f,l);s[o](u.randomizer.pick(u.eventTypes),!0,!0,c,!1,!1,!1,!1,a,0),c.dispatchEvent(s),typeof u.showAction=="function"&&u.showAction(c,f,l,a),typeof u.logger.log=="function"&&u.logger.log("gremlin","typer       type",a,"at",f,l)}var e=window.document,r=e.documentElement,i=e.body,s=["keypress","keyup","keydown"],o=function(t,n,r,s){var o=e.createElement("div");o.style.border="3px solid orange",o.style["border-radius"]="50%",o.style.width="40px",o.style.height="40px",o.style["box-sizing"]="border-box",o.style.position="absolute",o.style.webkitTransition="opacity 1s ease-out",o.style.mozTransition="opacity 1s ease-out",o.style.transition="opacity 1s ease-out",o.style.left=n+"px",o.style.top=r+"px",o.style.textAlign="center",o.style.paddingTop="7px",o.innerHTML=String.fromCharCode(s);var u=i.appendChild(o);setTimeout(function(){i.removeChild(u)},1e3),setTimeout(function(){u.style.opacity=0},50)},u={eventTypes:s,showAction:o,logger:{},randomizer:new n};return t(a,u),a}}),n("mogwais/alert",["require","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/configurable"),n=e("../vendor/chance");return function(){function l(){o.watchEvents.indexOf("alert")!==-1&&(window.alert=function(e){o.logger.warn("mogwai ","alert     ",e,"alert")}),o.watchEvents.indexOf("confirm")!==-1&&(window.confirm=function(e){o.confirmResponse(),o.logger.warn("mogwai ","alert     ",e,"confirm")}),o.watchEvents.indexOf("prompt")!==-1&&(window.prompt=function(e){o.promptResponse(),o.logger.warn("mogwai ","alert     ",e,"prompt")})}var e=["alert","confirm","prompt"],r=function(){return o.randomizer.bool()},i=function(){return o.randomizer.sentence()},s={warn:function(){}},o={watchEvents:e,confirmResponse:r,promptResponse:i,logger:s,randomizer:new n},u=window.alert,a=window.confirm,f=window.prompt;return l.cleanUp=function(){return window.alert=u,window.confirm=a,window.prompt=f,l},t(l,o),l}}),n("mogwais/fps",["require","../utils/configurable"],function(e){var t=e("../utils/configurable");return function(){function o(e){e-i>r.delay&&(u(e),i=e);if(!s)return;window.requestAnimationFrame(o)}function u(){function t(t){e=t,window.requestAnimationFrame(n)}function n(t){var n=t-e<16?60:1e3/(t-e),i=r.levelSelector(n);r.logger[i]("mogwai ","fps       ",n)}var e;window.requestAnimationFrame(t)}function a(){s=!0,window.requestAnimationFrame(o)}var e={log:function(){},warn:function(){},error:function(){}},n=function(e){return e<10?"error":e<20?"warn":"log"},r={delay:500,levelSelector:n,logger:e},i=-Infinity,s;return a.cleanUp=function(){return s=!1,a},t(a,r),a}}),n("mogwais/gizmo",["require","../utils/configurable"],function(e){var t=e("../utils/configurable");return function(){function s(){function s(){e++,e==n.maxErrors&&(t.stop(),window.setTimeout(function(){n.logger.warn("mogwai ","gizmo     ","stopped test execution after ",n.maxErrors,"errors")},4))}var e=0,t=this;r=window.onerror,window.onerror=function(e,t,n){return s(),r?r(e,t,n):!1},i=n.logger.error,n.logger.error=function(){s(),i.apply(n.logger,arguments)}}var e={warn:function(){}},n={maxErrors:10,logger:e},r,i;return s.cleanUp=function(){return window.onerror=r,n.logger.error=i.bind(n.logger),s},t(s,n),s}}),n("utils/executeInSeries",["require"],function(e){function t(e,t,n,r){var i=t.length;e=e.slice(0);var s=function(e,t){if(!e.length)return typeof r=="function"?r():!0;var o=e.shift();o.apply(n,t),o.length===i&&s(e,t,r)};t.push(function(){s(e,t,r)}),s(e,t,r)}return t}),n("strategies/allTogether",["require","../utils/executeInSeries","../utils/configurable"],function(e){var t=e("../utils/executeInSeries"),n=e("../utils/configurable");return function(){function s(n,s,u){function l(e){t(n,[],f,e)}function c(t){if(r)return;if(t>=a)return o();l(function(){setTimeout(function(){c(++t)},e.delay)})}var a=s&&s.nb?s.nb:e.nb,f=this;r=!1,i=u,c(0)}function o(){typeof i=="function"&&i(),i=null}var e={delay:10,nb:100},r,i;return s.stop=function(){r=!0,setTimeout(o,4)},n(s,e),s}}),n("strategies/bySpecies",["require","../utils/executeInSeries","../utils/configurable"],function(e){var t=e("../utils/executeInSeries"),n=e("../utils/configurable");return function(){function s(n,s,u){function l(n,i,s){if(r)return;if(i>=a)return s();t([n],[],f,function(){setTimeout(function(){l(n,++i,s)},e.delay)})}function c(){if(r)return;if(n.length===0)return o();l(n.shift(),0,c)}var a=s&&s.nb?s.nb:e.nb,n=n.slice(0),f=this;r=!1,i=u,c()}function o(){typeof i=="function"&&i(),i=null}var e={delay:10,nb:100},r,i;return s.stop=function(){r=!0,setTimeout(o,4)},n(s,e),s}}),n("strategies/distribution",["require","../utils/executeInSeries","../utils/configurable","../vendor/chance"],function(e){var t=e("../utils/executeInSeries"),n=e("../utils/configurable"),r=e("../vendor/chance");return function(){function o(n,r,o){function p(r,s,o){if(i)return;if(s>=l)return f();t([r],[],h,function(){setTimeout(function(){p(a(n,c),++s,o)},e.delay)})}var l=r&&r.nb?r.nb:e.nb,n=n.slice(0),c=e.distribution.length===0?u(n):e.distribution,h=this;if(l===0)return o();i=!1,s=o,p(a(n,c),0,p)}function u(e){var t=e.length;if(t===0)return[];var n=[],r=1/t;for(var i=0;i<t;i++)n.push(r);return n}function a(t,n){var r=0,i=e.randomizer.floating({min:0,max:1});for(var s=0,o=t.length;s<o;s++){r+=n[s];if(i<=r)return t[s]}return function(){}}function f(){typeof s=="function"&&s(),s=null}var e={distribution:[],delay:10,nb:100,randomizer:new r},i,s;return o.stop=function(){i=!0,setTimeout(f,4)},n(o,e),o}}),n("main",["require","./vendor/chance","./species/clicker","./species/formFiller","./species/scroller","./species/typer","./mogwais/alert","./mogwais/fps","./mogwais/gizmo","./strategies/allTogether","./strategies/bySpecies","./strategies/distribution","./utils/executeInSeries"],function(e){function s(e,t){for(var n=0,r=t.length;n<r;n++)for(var i in e)typeof t[n][i]=="function"&&t[n][i](e[i])}var t=e("./vendor/chance"),n={species:{clicker:e("./species/clicker"),formFiller:e("./species/formFiller"),scroller:e("./species/scroller"),typer:e("./species/typer")},mogwais:{alert:e("./mogwais/alert"),fps:e("./mogwais/fps"),gizmo:e("./mogwais/gizmo")},strategies:{allTogether:e("./strategies/allTogether"),bySpecies:e("./strategies/bySpecies"),distribution:e("./strategies/distribution")}},r=e("./utils/executeInSeries"),i=function(){this._gremlins=[],this._mogwais=[],this._strategies=[],this._beforeCallbacks=[],this._afterCallbacks=[],this._logger=console,this._randomizer=new t};return i.prototype.gremlin=function(e){return this._gremlins.push(e),this},i.prototype.allGremlins=function(){for(var e in n.species)this.gremlin(n.species[e]());return this},i.prototype.mogwai=function(e){return this._mogwais.push(e),this},i.prototype.allMogwais=function(){for(var e in n.mogwais)this.mogwai(n.mogwais[e]());return this},i.prototype.strategy=function(e){return this._strategies.push(e),this},i.prototype.before=function(e){return this._beforeCallbacks.push(e),this},i.prototype.after=function(e){return this._afterCallbacks.push(e),this},i.prototype.logger=function(e){return arguments.length?(this._logger=e,this):this._logger},i.prototype.log=function(e){this._logger.log(e)},i.prototype.randomizer=function(e){return arguments.length?(this._randomizer=e,this):this._randomizer},i.prototype.seed=function(e){return this._randomizer=new t(e),this},i.prototype.unleash=function(e,t){this._gremlins.length===0&&this.allGremlins(),this._mogwais.length===0&&this.allMogwais(),this._strategies.length===0&&this.strategy(n.strategies.distribution());var i=[].concat(this._gremlins,this._mogwais),o=i.concat(this._strategies,this._beforeCallbacks,this._afterCallbacks);s({logger:this._logger,randomizer:this._randomizer},o);var u=this._beforeCallbacks;u=u.concat(this._mogwais);var a=this._afterCallbacks;for(var f=0,l=i.length;f<l;f++)typeof i[f].cleanUp=="function"&&a.push(i[f].cleanUp);var c=this;r(u,[],c,function(){r(c._strategies,[c._gremlins,e],c,function(){r(a,[],c,function(){typeof t=="function"&&t()})})})},i.prototype.stop=function(){var e=this._strategies;for(var t=0,n=e.length;t<n;t++)e[t].stop()},n.createHorde=function(){return new i},n}),t(["main"]),t("main")});

/***/ },
/* 51 */
/*!****************************************!*\
  !*** ./~/inherits/inherits_browser.js ***!
  \****************************************/
/***/ function(module, exports) {

			if (typeof Object.create === 'function') {
			  // implementation from standard node.js 'util' module
			  module.exports = function inherits(ctor, superCtor) {
			    ctor.super_ = superCtor
			    ctor.prototype = Object.create(superCtor.prototype, {
			      constructor: {
			        value: ctor,
			        enumerable: false,
			        writable: true,
			        configurable: true
			      }
			    });
			  };
			} else {
			  // old school shim for old browsers
			  module.exports = function inherits(ctor, superCtor) {
			    ctor.super_ = superCtor
			    var TempCtor = function () {}
			    TempCtor.prototype = superCtor.prototype
			    ctor.prototype = new TempCtor()
			    ctor.prototype.constructor = ctor
			  }
			}


/***/ },
/* 52 */
/*!******************************!*\
  !*** ./~/process/browser.js ***!
  \******************************/
/***/ function(module, exports) {

			// shim for using process in browser
			
			var process = module.exports = {};
			var queue = [];
			var draining = false;
			var currentQueue;
			var queueIndex = -1;
			
			function cleanUpNextTick() {
			    if (!draining || !currentQueue) {
			        return;
			    }
			    draining = false;
			    if (currentQueue.length) {
			        queue = currentQueue.concat(queue);
			    } else {
			        queueIndex = -1;
			    }
			    if (queue.length) {
			        drainQueue();
			    }
			}
			
			function drainQueue() {
			    if (draining) {
			        return;
			    }
			    var timeout = setTimeout(cleanUpNextTick);
			    draining = true;
			
			    var len = queue.length;
			    while(len) {
			        currentQueue = queue;
			        queue = [];
			        while (++queueIndex < len) {
			            if (currentQueue) {
			                currentQueue[queueIndex].run();
			            }
			        }
			        queueIndex = -1;
			        len = queue.length;
			    }
			    currentQueue = null;
			    draining = false;
			    clearTimeout(timeout);
			}
			
			process.nextTick = function (fun) {
			    var args = new Array(arguments.length - 1);
			    if (arguments.length > 1) {
			        for (var i = 1; i < arguments.length; i++) {
			            args[i - 1] = arguments[i];
			        }
			    }
			    queue.push(new Item(fun, args));
			    if (queue.length === 1 && !draining) {
			        setTimeout(drainQueue, 0);
			    }
			};
			
			// v8 likes predictible objects
			function Item(fun, array) {
			    this.fun = fun;
			    this.array = array;
			}
			Item.prototype.run = function () {
			    this.fun.apply(null, this.array);
			};
			process.title = 'browser';
			process.browser = true;
			process.env = {};
			process.argv = [];
			process.version = ''; // empty string to avoid regexp issues
			process.versions = {};
			
			function noop() {}
			
			process.on = noop;
			process.addListener = noop;
			process.once = noop;
			process.off = noop;
			process.removeListener = noop;
			process.removeAllListeners = noop;
			process.emit = noop;
			
			process.binding = function (name) {
			    throw new Error('process.binding is not supported');
			};
			
			process.cwd = function () { return '/' };
			process.chdir = function (dir) {
			    throw new Error('process.chdir is not supported');
			};
			process.umask = function() { return 0; };


/***/ },
/* 53 */
/*!*******************************!*\
  !*** ./~/tty-colors/index.js ***!
  \*******************************/
/***/ function(module, exports) {

			/**
			 * Extend strings with ANSI escape codes for styling strings in the terminal.
			 *
			 * @author Stanislav Kalashnik <sk@infomir.eu>
			 * @license GNU GENERAL PUBLIC LICENSE Version 3
			 */
			
			'use strict';
			
			var styles = {
					reset:     [0,   0],
					bold:      [1,  22],
					dim:       [2,  22],
					italic:    [3,  23],
					underline: [4,  24],
					inverse:   [7,  27],
					hidden:    [8,  28],
					strike:    [9,  29],
					black:     [30, 39],
					red:       [31, 39],
					green:     [32, 39],
					yellow:    [33, 39],
					blue:      [34, 39],
					magenta:   [35, 39],
					cyan:      [36, 39],
					white:     [37, 39],
					grey:      [90, 39],
					bgBlack:   [40, 49],
					bgRed:     [41, 49],
					bgGreen:   [42, 49],
					bgYellow:  [43, 49],
					bgBlue:    [44, 49],
					bgMagenta: [45, 49],
					bgCyan:    [46, 49],
					bgWhite:   [47, 49]
				};
			
			
			// apply all styles to String prototype
			Object.keys(styles).forEach(function ( name ) {
				// rework values to avoid unnecessary concatenations
				styles[name][0] = '\u001b[' + styles[name][0] + 'm';
				styles[name][1] = '\u001b[' + styles[name][1] + 'm';
			
				// add getter by style name
				Object.defineProperty(String.prototype, name, {
					get: function () {
						return styles[name][0] + this + styles[name][1];
					},
					// hide from iteration
					enumerable: false,
					// allow to change or remove this property
					configurable: true
				});
			});


/***/ },
/* 54 */
/*!*******************************************!*\
  !*** ./~/util/support/isBufferBrowser.js ***!
  \*******************************************/
/***/ function(module, exports) {

			module.exports = function isBuffer(arg) {
			  return arg && typeof arg === 'object'
			    && typeof arg.copy === 'function'
			    && typeof arg.fill === 'function'
			    && typeof arg.readUInt8 === 'function';
			}

/***/ }
/******/ ]);
//# sourceMappingURL=develop.js.map