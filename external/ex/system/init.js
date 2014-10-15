/**
 * Global init procedure
 * should be called in all html files
 * if no gSTB object found switches to emulation mode
 * if environment variable debug = 1 add some debug tools
 * if set environment variable debug_name then add weinre to all pages
 * @author DarkPark
 */

'use strict';

// global flags
var DEBUG         = true,
	DEBUG_NAME    = '',  // primary name and primary address
	DEBUG_SERVER  = '',  // in case they are not given then env vars and local storage will be used
	EMULATION     = false,

	PATH_MODE_WEB = false,  // portal is starting from web by http/https or locally
	PATH_ROOT     = '/home/web/',
	PATH_SYSTEM   = PATH_ROOT + 'system/',
	PATH_MEDIA    = '/media/',

	WINDOW_WIDTH  = screen.width,
	WINDOW_HEIGHT = screen.height,
	URL_PARAMS    = {},  // query params
	proxy;


// check run mode
if ( ['http:', 'https:'].indexOf(document.location.protocol) >= 0 ) {
	PATH_MODE_WEB = true;
	// find the system dir path
	Array.prototype.slice.call(document.getElementsByTagName('script')).forEach(function(tag){
		if ( tag.src ) {
			// get path and script name
			var path = tag.src.split('/'),
				head = path.pop();
			if ( head.indexOf('init.js') === 0 ) {
				PATH_SYSTEM = path.join('/') + '/';
				path.pop();
				PATH_ROOT = path.join('/') + '/';
			}
		}
	});
}

// any query params
if ( document.location.search ) {
	// parse and fill the global list
	document.location.search.substring(1).split('&').forEach(function(part){
		part = part.split('=');
		// valid number on params
		if ( part.length === 2 ) { URL_PARAMS[part[0]] = decodeURIComponent(part[1]); }
	});
}


// native or emulation
if ( window.gSTB === undefined ) {

	DEBUG = EMULATION = true;

	// load stored vars
	DEBUG_SERVER = DEBUG_SERVER || localStorage.getItem('DEBUG_SERVER');
	DEBUG_NAME   = DEBUG_NAME   || localStorage.getItem('DEBUG_NAME');

	// resolution correction
	WINDOW_WIDTH  = parseInt(URL_PARAMS.width,  10) || window.outerWidth;
	WINDOW_HEIGHT = parseInt(URL_PARAMS.height, 10) || window.outerHeight;

	// stb global object is not present so emulation
	document.write('<script type="text/javascript" src="' + PATH_SYSTEM + 'gstb.js"></script>\n');
	document.write('<script type="text/javascript" src="' + PATH_SYSTEM + 'debug.js"></script>\n');
	document.write('<script type="text/javascript" src="http://' + DEBUG_SERVER + ':8800/file/client.js"></script>\n');
	document.write('<script type="text/javascript">' +
		'if ( window.proxyClient ) {' +
		'proxy = new proxyClient();' +
		'if ( DEBUG_NAME && DEBUG_SERVER ) proxy.init({name:"' + DEBUG_NAME + '", host:"' + DEBUG_SERVER + '"}); }' +
		'</script>\n');

	// page loading finished
	window.addEventListener('load', function(){
		document.body.style.backgroundColor = 'black';
	});

} else {
	if ( typeof gSTB.SetNativeStringMode === 'function' ) {
		// new way of string handling
		// all strings are in UTF-16
		gSTB.SetNativeStringMode(true);
	}

	// check mode
	(function(){
		var vars = JSON.parse(gSTB.GetEnv(JSON.stringify({varList:['debug', 'debug_name', 'debug_server']})));
		if ( !vars.errMsg && vars.result ) {
			DEBUG = vars.result.debug === '1';
			// no errors and valid string
			if ( DEBUG && !URL_PARAMS.nodebug ) {
				// general tools
				document.write('<script type="text/javascript" src="' + PATH_SYSTEM + 'debug.js"></script>\n');
				// rewrite default
				DEBUG_NAME   = DEBUG_NAME   || vars.result.debug_name;
				DEBUG_SERVER = DEBUG_SERVER || vars.result.debug_server;
				if ( DEBUG_NAME && DEBUG_SERVER ) {
					// web inspector
					document.write('<script type="text/javascript" src="http://' + DEBUG_SERVER + ':8000/target/target-script-min.js#' + DEBUG_NAME + '"></script>\n');
				}
			}
		}
	})();

}

// additional path to image
var PATH_IMG_SYSTEM = PATH_SYSTEM + 'img/' + WINDOW_WIDTH + '/',
	PATH_IMG_PUBLIC = PATH_ROOT + 'public/img/' + WINDOW_WIDTH + '/';
