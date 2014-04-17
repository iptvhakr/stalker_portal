/**
 * Debug instruments
 * @author igork
 */

//'use strict';

/**
 * Dumps the given data (json format), its type and optional title to console
 * @param data mixed value to be printed
 * @param {String} [title] optional string for additional info
 */
var echo = function ( data, title ) {
	if ( EMULATION ) { console.log(title ? title : 'log:', data); }
	// console colors
	var red   = '\033[31m',
		bold  = '\033[1m',
		cyan  = '\033[36m',
		green = '\033[32m',
		reset = '\033[0m';
	// info of the var type
	var type = Object.prototype.toString.call(data).match(/\s([a-z|A-Z]+)/)[1];
	// add custom colors (red for errors)
	if ( type === 'Error' ) {
		type = red + type + reset;
	} else {
		type = green + type + reset;
	}
	// prepare
	if ( data instanceof Object || Array.isArray(data) ) {
		// complex object
		data = data.nodeName ? data.outerHTML : JSON.stringify(data, null, 4);
	}
	title = title || '';
	// combine all together and print result
	gSTB.Debug('[' + type + ']\t' + (title ? bold + title + green + ':\t' + reset : '') + data);
	// ok
	return data;
};


/**
 * Reloads all css files found on the current page
 * uses timestamp to make links unique
 */
function reloadStyles () {
	var time = +new Date();
	// get through all css links
	Array.prototype.slice.call(document.head.getElementsByTagName('link')).forEach(function(tag){
		// get base name, modify and apply
		tag.href = tag.href.split('?_')[0] + '?_=' + time;
	});
}


/**
 * Reload all scripts on page
 * @param {Array} [filter] Array of strings with parts of the urls scripts which should be exclude from reload
 */
function reloadAllScripts ( filter ) {
	var scripts = document.head.getElementsByTagName('script'),
		filtered = false,
		script = null,
		src = '';
	for ( var i = 0; i < scripts.length; i++ ) {
		script = scripts[i];
		filtered = false;
		src = undefined;
		if ( script.type === 'text/javascript' && script.attributes.src !== undefined ) {
//            if (filter !== undefined && Array.isArray(filter)){
//                filter.forEach(function(f){
//                    if (script.src.indexOf(f) !== -1) filtered = true;
//                })
//            }
			if ( !filtered ) {
				src = script.src;
				script.parentNode.removeChild(script);
				if ( src.indexOf('?_') !== -1 ) {
					src = src.substr(0, src.indexOf('?_'));
				}
				loadScript(src);
			}
		}
	}
}


/**
 * Reload scripts
 * @param {String} src Part of the script url
 */
function reloadScripts ( src ) {
	var scripts = document.head.getElementsByTagName('script'),
		curr_time = (new Date()).getTime();
	for ( var i = 0, script = null, src = ''; i < scripts.length; i++ ) {
		script = scripts[i];
		if ( script.type === 'text/javascript' && script.src.indexOf(src) !== -1 ) {
			src = script.src + curr_time;
			script.parentNode.removeChild(script);
			loadScript(src);
		}
	}
}


/**
 *  Reload page with cache ignore
 */
function reloadPage () {
	gSTB.Stop();
	window.location.reload(true);
}


/**
 * Impose image to the screen
 * @param {String} image full url of image
 * @param {Number} [opacity=0.5] opacity level of impose image
 */
function imposeImageToScreen(image, opacity){
	// prepare image
	imposeImageToScreen.image = element('div');
	imposeImageToScreen.image.style.width      = WINDOW_WIDTH + 'px';
	imposeImageToScreen.image.style.height     = WINDOW_HEIGHT + 'px';
	imposeImageToScreen.image.style.position   = 'absolute';
	imposeImageToScreen.image.style.background = 'url("' + image + '") no-repeat';
	imposeImageToScreen.image.style.opacity    = opacity || 1;
	imposeImageToScreen.image.style.zIndex     = 1000;
	// add to DOM
	document.body.appendChild(imposeImageToScreen.image);
}


/**
 * Monitors all focus changes and dumps focused elements
 */
function focusTracker () {
	// state inversion
	focusTracker.state = !focusTracker.state;

	if ( focusTracker.state ) {
		// start
		console.log('focus tracking: started');
		// backup
		focusTracker.focus  = Element.prototype.focus;
		focusTracker.blur   = Element.prototype.blur;
		focusTracker.select = HTMLInputElement.prototype.select;
		// rewrite
		Element.prototype.focus = function(){
			console.log('focus', this);
			// invoke the old native one
			focusTracker.focus.call(this);
		};
		Element.prototype.blur = function(){
			console.log('blur', this);
			// invoke the old native one
			focusTracker.blur.call(this);
		};
		HTMLInputElement.prototype.select = function(){
			console.log('select', this);
			// invoke the old native one
			focusTracker.select.call(this);
		};
	} else {
		// stop
		console.log('focus tracking: stopped');
		//clearInterval(focusTracker.timer);
		// restore
		Element.prototype.focus = focusTracker.focus;
		Element.prototype.blur  = focusTracker.blur;
		HTMLInputElement.prototype.select = focusTracker.select;
	}
}


/**
 * Set shortcuts for some debug tools
 * Numpad 1 - reload page ignore caching
 * Numpad 2 - reload styles
 * Numpad 3 - reload all scripts on page
 */
(function(){
	window.addEventListener('keydown', function ( event ) {
		//eventPrepare(event, false, '123');
		switch ( event.code ) {
			case 97:  // numpad 1
				echo('reload page');
				reloadPage();
				break;
			case 98:  // numpad 2
				echo('reload CSS');
				reloadStyles();
				break;
			case 99:  // numpad 3
				reloadAllScripts(['target-script-min.js']);
				break;
			case 100: // numpad 4
				echo('toggle grid');
				// toggle visibility
				if ( imposeImageToScreen.image ) {
					// clear
					document.body.removeChild(imposeImageToScreen.image);
					delete imposeImageToScreen.image;
				} else {
					// add
					imposeImageToScreen(PATH_SYSTEM + 'grid.' + WINDOW_HEIGHT + '.png');
				}
				break;
			case 103: // numpad 7
				// toggle tracking
				focusTracker();
				break;
			case 220: // Print Screen
				echo('\n\n\n<html><head>\n' + document.head.innerHTML + '\n</head>\n<body>\n' + document.body.innerHTML + '\n</body>\n</html>\n');
				break;
		}
	});
})();
