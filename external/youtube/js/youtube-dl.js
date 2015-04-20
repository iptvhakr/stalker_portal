/**
 * Created by Harry on 26.03.2015.
 */


//'use strict';

/*eslint-disable camelcase*/

var YoutubeDL = (function () {
	var urlDefObj = {
			error: null,
			format: {},
			formatId: 0,
			needSig: false,
			sig: null,
			url: '',
			getUrl: getUrlFromObj
		},
		getInfoCallback = null,
		getUrlCallback = null,
		//in python library it is _formats array
		formats = {
			5: {ext: 'flv', width: 400, height: 240},
			6: {ext: 'flv', width: 450, height: 20},
			13: {ext: '3gp'},
			17: {ext: '3gp', width: 176, height: 144},
			18: {ext: 'mp4', width: 640, height: 360},
			22: {ext: 'mp4', width: 1280, height: 720},
			34: {ext: 'flv', width: 640, height: 360},
			35: {ext: 'flv', width: 854, height: 480},
			36: {ext: '3gp', width: 320, height: 240},
			37: {ext: 'mp4', width: 1920, height: 1080},
			38: {ext: 'mp4', width: 4096, height: 3072},
			43: {ext: 'webm', width: 640, height: 360},
			44: {ext: 'webm', width: 854, height: 480},
			45: {ext: 'webm', width: 1280, height: 720},
			46: {ext: 'webm', width: 1920, height: 1080},


			//3d videos
			82: {ext: 'mp4', height: 360, formatNote: '3D', preference: -20},
			83: {ext: 'mp4', height: 480, formatNote: '3D', preference: -20},
			84: {ext: 'mp4', height: 720, formatNote: '3D', preference: -20},
			85: {ext: 'mp4', height: 1080, formatNote: '3D', preference: -20},
			100: {ext: 'webm', height: 360, formatNote: '3D', preference: -20},
			101: {ext: 'webm', height: 480, formatNote: '3D', preference: -20},
			102: {ext: 'webm', height: 720, formatNote: '3D', preference: -20},

			// Apple HTTP Live Streaming
			92: {ext: 'mp4', height: 240, formatNote: 'HLS', preference: -10},
			93: {ext: 'mp4', height: 360, formatNote: 'HLS', preference: -10},
			94: {ext: 'mp4', height: 480, formatNote: 'HLS', preference: -10},
			95: {ext: 'mp4', height: 720, formatNote: 'HLS', preference: -10},
			96: {ext: 'mp4', height: 1080, formatNote: 'HLS', preference: -10},
			132: {ext: 'mp4', height: 240, formatNote: 'HLS', preference: -10},
			151: {ext: 'mp4', height: 72, formatNote: 'HLS', preference: -10},

//			//DASH mp4 video
//			'133': {'ext': 'mp4', 'height': 240, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'134': {'ext': 'mp4', 'height': 360, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'135': {'ext': 'mp4', 'height': 480, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'136': {'ext': 'mp4', 'height': 720, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'137': {'ext': 'mp4', 'height': 1080, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'138': {'ext': 'mp4', 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40}, // # Height can vary (https://github.com/rg3/youtube-dl/issues/4559)
//			'160': {'ext': 'mp4', 'height': 144, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'264': {'ext': 'mp4', 'height': 1440, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'298': {'ext': 'mp4', 'height': 720, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'h264'},
//			'299': {'ext': 'mp4', 'height': 1080, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'h264'},
//			'266': {'ext': 'mp4', 'height': 2160, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'vcodec': 'h264'},
//
//		 	//Dash mp4 audio
//			'139': {'ext': 'm4a', 'format_note': 'DASH audio', 'acodec': 'aac', 'vcodec': 'none', 'abr': 48, 'preference': -50, 'container': 'm4a_dash'},
//			'140': {'ext': 'm4a', 'format_note': 'DASH audio', 'acodec': 'aac', 'vcodec': 'none', 'abr': 128, 'preference': -50, 'container': 'm4a_dash'},
//			'141': {'ext': 'm4a', 'format_note': 'DASH audio', 'acodec': 'aac', 'vcodec': 'none', 'abr': 256, 'preference': -50, 'container': 'm4a_dash'},
//
//			//Dash webm
//			'167': {'ext': 'webm', 'height': 360, 'width': 640, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'168': {'ext': 'webm', 'height': 480, 'width': 854, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'169': {'ext': 'webm', 'height': 720, 'width': 1280, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'170': {'ext': 'webm', 'height': 1080, 'width': 1920, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'218': {'ext': 'webm', 'height': 480, 'width': 854, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'219': {'ext': 'webm', 'height': 480, 'width': 854, 'format_note': 'DASH video', 'acodec': 'none', 'container': 'webm', 'vcodec': 'VP8', 'preference': -40},
//			'278': {'ext': 'webm', 'height': 144, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'container': 'webm', 'vcodec': 'VP9'},
//			'242': {'ext': 'webm', 'height': 240, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'243': {'ext': 'webm', 'height': 360, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'244': {'ext': 'webm', 'height': 480, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'245': {'ext': 'webm', 'height': 480, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'246': {'ext': 'webm', 'height': 480, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'247': {'ext': 'webm', 'height': 720, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'248': {'ext': 'webm', 'height': 1080, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'271': {'ext': 'webm', 'height': 1440, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'272': {'ext': 'webm', 'height': 2160, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40},
//			'302': {'ext': 'webm', 'height': 720, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'VP9'},
//			'303': {'ext': 'webm', 'height': 1080, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'VP9'},
//			'308': {'ext': 'webm', 'height': 1440, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'VP9'},
//			'313': {'ext': 'webm', 'height': 2160, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'vcodec': 'VP9'},
//			'315': {'ext': 'webm', 'height': 2160, 'format_note': 'DASH video', 'acodec': 'none', 'preference': -40, 'fps': 60, 'vcodec': 'VP9'},
//
//			//Dash webm audio
//			'171': {'ext': 'webm', 'vcodec': 'none', 'format_note': 'DASH audio', 'abr': 128, 'preference': -50},
//			'172': {'ext': 'webm', 'vcodec': 'none', 'format_note': 'DASH audio', 'abr': 256, 'preference': -50},
//
//			//Dash webm audio with opus inside
//			'249': {'ext': 'webm', 'vcodec': 'none', 'format_note': 'DASH audio', 'acodec': 'opus', 'abr': 50, 'preference': -50},
//			'250': {'ext': 'webm', 'vcodec': 'none', 'format_note': 'DASH audio', 'acodec': 'opus', 'abr': 70, 'preference': -50},
//			'251': {'ext': 'webm', 'vcodec': 'none', 'format_note': 'DASH audio', 'acodec': 'opus', 'abr': 160, 'preference': -50},

			//conn (unnamed)
			conn: {protocol: 'conn'},

			//RTMP (unnamed)
			rtmp: {protocol: 'rtmp'}
		},
		hashUrl = {}, info = {}, container = {};

	/**
	 * getting valid url for urlMap object
	 * it's need if needSig = true
	 * @param {function}  callback ( error, url )
	 * @return {string} url to play
	 */
	function getUrlFromObj ( callback ) {
		getUrlCallback = callback || null;
		if ( this.needSig ) {
			getSig(this);
			return null;
		}
		onGetUrlDone(this.error, this);
		return this.url;
	}

	/**
	 * parse url parse to object
	 * @param {string}  url address from parse
	 * @return {Object} result of parsing
	 */
	function urlToObj ( url ) {
		var results = {}, get, getVar, i,
			rx = new RegExp('^(([^:/\\?#]+):)?(//(([^:/\\?#]*)(?::([^/\\?#]*))?))?([^\\?#]*)(\\?([^#]*))?(#(.*))?$'),
			parts = rx.exec(url);

		get = parts[8].substring(1).split('&');
		for ( i = 0; i < get.length; i++ ) {
			getVar = get[i].split('=');
			results[getVar[0]] = getVar[1] || '';
		}
		return results;
	}

	/**
	 * Ajax request
	 * @param {string} method "post", "get" or "head"
	 * @param {string} url address
	 * @param {Function} callback on
	 * @param {Object} [headers] list of optional headers like "charset", "Content-Type" and so on
	 * @param {string} [type=text] data parsing mode: plain text (default), xml, json
	 * @return {XMLHttpRequest} request object in case response headers are necessary
	 * @example
	 *   ajax('get', 'https://google.com/', function(data, status){console.info(data, status);}, {charset:'utf-8'})
	 */
	function ajax ( method, url, callback, headers, type ) {
		var hname,
			jdata = null,
			timeout = null,
			xhr = new XMLHttpRequest();

		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {
				clearTimeout(timeout);
				if (type === 'json') {
					try {
						jdata = JSON.parse(xhr.responseText);
					} catch (e) {
						jdata = null;
					}
				} else {
					jdata = xhr.responseText;
				}
				if ( typeof callback === 'function' ) {
					callback(type === 'xml' ? xhr.responseXML : jdata, xhr.status);
				}
			}
		};
		xhr.open(method, url, true);
		if ( headers ) {
			for ( hname in headers ) {
				if ( headers.hasOwnProperty(hname) ) {
					xhr.setRequestHeader(hname, headers[hname]);
				}
			}
		}
		xhr.send();
		// abort after some time (30s)
		timeout = setTimeout(function () {
			xhr.abort();
		}, 60000);
		return xhr;
	}

	/**
	 * callback get information about video
	 * @param {Object} e error
	 * @param {Object} i information
	 * @param {Object} [urlObj] best quality from urlMap
	 */
	function onGetMapDone ( e, i, urlObj ) {
		if ( typeof getInfoCallback === 'function' ) {
			getInfoCallback(e, i, urlObj);
		}
	}

	/**
	 * callback get information about video
	 * @param {Object} e error
	 * @param {Object} urlObj from urlMap
	 */
	function onGetUrlDone ( e, urlObj ) {
		if ( typeof getUrlCallback === 'function' ) {
			urlObj = urlObj || {};
			getUrlCallback(e, urlObj.url);
		}
	}

	/**
	 * set default values
	 */
	function clear () {
		hashUrl = {};
		info = {
			ageGate: false,
			author: '',
			description: '',
			lengthSeconds: 0,
			playerUrl: null,
			thumb: null,
			title: '',
			uploadDate: '',
			uploaderId: '',
			url: '', //recomended to play highest resolution
			urlMap: [],
			urlMapBad: [], //no supported formats
			videoId: null,
			viewCount: '',
			ypc_video_rental_bar_text: null,
			error: null
		};
	}

	/**
	 * start getting urls map
	 * @param {string} id video id
	 */
	function getUrls ( id ) {
		var url;

		clear();
		info.videoId = id;
		url = 'https://www.youtube.com/watch?v=' + info.videoId + '&gl=US&hl=en&has_verified=1&bpctr=9999999999';
		ajax('GET', url, parseUrls, null, null);
	}

	/**
	 * start parsing urls map
	 * @param {String} responseText answer result
	 */
	function parseUrls ( responseText ) {
		var regExp;

		info.playerUrl = null;
		regExp = /swfConfig.*?"(https?:\/\/.*?watch.*?-.*?\.swf)"/;
		if ( responseText.search(regExp) !== -1 ) {
			info.error = {};
			info.error.message = 'Flash player is not supported';
			info.error.videoId = info.videoId;
			onGetMapDone(info.error, info, null);
			return;
		}

		regExp = /player-age-gate-content">/;

		if ( responseText.search(regExp) !== -1 ) {
			info.ageGate = true;
			info.error = {};
			info.error.message = 'Need age gate. Now it\'s not supported';
			info.error.videoId = info.videoId;
			onGetMapDone(info.error, info);
			return;
		}
		info.ageGate = false;
		parseInfo(responseText);
	}

	/**
	 * getting urls and info from response
	 * @param {string} responseText answer result
	 */
	function parseInfo ( responseText )  {
		var encodedUrlMap, tempStr, hashes,
			hash, tempUrl, format, regExp,
			matchObject, ytplayerConfig, jsplayerUrlJson = null,
			videoInfo, encodedUrlMapArray, tempObj,
			j, i;

		regExp = /;ytplayer\.config\s*=\s*({.*?});/;
		matchObject = regExp.exec(responseText);
		ytplayerConfig = JSON.parse(matchObject[1]);
		videoInfo = ytplayerConfig.args;

		getInfo(responseText, videoInfo);

		if ( !videoInfo.token ) {
			if ( videoInfo.reason ) {
				info.error = {};
				info.error.message = 'YouTube said: ' + videoInfo.reason;
				info.error.videoId = info.videoId;
			} else {
				info.error = {};
				info.error.message = '"token" parameter not in video info for unknown reason';
				info.error.videoId = info.videoId;
			}
			onGetMapDone(info.error, info);
			return;
		}

		if ( 'ypc_video_rental_bar_text' in videoInfo && !info.author ) {
			info.error = {};
			info.error.message = '"rental" videos not supported';
			info.error.videoId = info.videoId;
			onGetMapDone(info.error, info);
			return;
		}


		if ( videoInfo.conn && videoInfo.conn[0] ) {
			info.url = videoInfo.conn[0];
			info.urlMap = [
				Object.create(urlDefObj)
			];
			info.urlMap[0].url = info.url;
			info.urlMap.format = formats.conn;
			onGetMapDone(info.error, info, info.urlMap[0]);
			return;
		}

		info.urlMap = [];

		if ( 'rtmpe%3Dyes' in videoInfo ) {
			info.error = {};
			info.error.message = 'rtmpe downloads are not supported';
			info.error.videoId = info.videoId;
			onGetMapDone(info.error, info);
			return;
		}

		if ( videoInfo.url_encoded_fmt_stream_map || videoInfo.adaptive_fmts ) {
			encodedUrlMap = (videoInfo.url_encoded_fmt_stream_map || '') + ',' + (videoInfo.adaptive_fmts || '');
			encodedUrlMapArray = encodedUrlMap.split(',');

			for ( i in encodedUrlMapArray ) {
				tempUrl = '';
				format = {};
				if ( !encodedUrlMapArray.hasOwnProperty(i) ) {
					continue;
				}
				tempStr = encodedUrlMapArray[i];
				hashes = tempStr.slice(tempStr.indexOf('?') + 1).split('&');
				tempStr = [];
				for ( j = 0; j < hashes.length; j++ ) {
					hash = hashes[j].split('=');
					tempStr.push(hash[0]);
					tempStr[hash[0]] = decodeURI(hash[1]);
				}
				if ( 'itag' in tempStr && 'url' in tempStr ) {
					format = tempStr.itag;
					tempUrl = decodeURI(decodeURI(decodeURIComponent(tempStr.url.replace(/\+/g, '%20'))));
					if ( format in formats ) {

						if ( 'ratebypass' in tempStr ) {
							tempUrl += '&ratebypass=yes';
						}

						if ( 'sig' in  tempStr ) {
							tempUrl += '&signature=' + tempStr.sig;
						} else {
							if ( 's' in tempStr ) {
								regExp = /"assets":.+?"js":\s*("[^"]+")/;
								matchObject = regExp.exec(responseText);
								jsplayerUrlJson = matchObject[1];
								regExp = /"(.*)"/;
								matchObject = regExp.exec(jsplayerUrlJson);
								if ( matchObject && matchObject[1] ) {
									jsplayerUrlJson = matchObject[1];
								}
								if ( !jsplayerUrlJson && !info.ageGate ) {
									return;
								}
								tempObj = Object.create(urlDefObj, {
									format: {value: formats[format]},
									formatId: {value: parseInt(format, 10)},
									sig: {value: tempStr.s},
									playerUrl: {value: jsplayerUrlJson}
								});
								tempObj.url = tempUrl;
								tempObj.needSig = true;
								info.urlMap.push(tempObj);
								continue;
							}
						}
						tempObj = Object.create(urlDefObj, {
							format: {value: formats[format]},
							formatId: {value: parseInt(format, 10)},
							sig: {value: tempStr.s},
							playerUrl: {value: jsplayerUrlJson}
						});
						tempObj.url = tempUrl;
						tempObj.needSig = false;
						info.urlMap.push(tempObj);

					} else {
						tempObj = Object.create(urlDefObj, {
							format: {value: formats[format]},
							formatId: {value: parseInt(format, 10)},
							sig: {value: tempStr.s},
							playerUrl: {value: jsplayerUrlJson}
						});
						tempObj.url = tempUrl;
						tempObj.needSig = false;
						info.urlMapBad.push(tempObj);
					}
				}
			}
			if ( info.urlMap.length ) {
				info.urlMap.sort(function (a, b) {
					if (a.formatId < b.formatId) {
						return -1;
					}
					if (a.formatId > b.formatId) {
						return 1;
					}
					return 0;
				});

				info.urlMap.sort(function (a, b) {
					if (a.format.height > b.format.height) {
						return -1;
					}
					if (a.format.height < b.format.height) {
						return 1;
					}
					return 0;
				});
				info.url = info.urlMap[0].needSig ? null : info.urlMap[0].url;
			}
			onGetMapDone(info.error, info, info.urlMap[0]);
			return;

		}
		onGetMapDone(info.error, info);
//		if ( responseText.search('eow-description') !== -1 ) {
//			console.log('eow-description');
//		}
//
//		if ( responseText.search(/swfConfig.*?\"(https?:\/\/.*?watch.*?-.*?\.swf)\"/) !== -1 ) {
//			console.log(/swfConfig.*?\"(https?:\/\/.*?watch.*?-.*?\.swf)\"/.exec(responseText));
//		}
	}

	/**
	 * getting info from response
	 * @param {string} responseText answer result
	 * @param {string} videoInfo parsed info
	 */
	function getInfo ( responseText, videoInfo ) {
		var regExp, matchObject;

		if ( videoInfo.view_count ) {
			info.viewCount = videoInfo.view_count;
		} else {
			info.viewCount = 0;
		}

		if ( videoInfo.author ) {
			info.author = videoInfo.author;
		} else {
			info.author = '';
		}

		regExp = /<link itemprop="url" href="http:\/\/www.youtube.com\/(?:user|channel)\/([^"]+)">/;
		matchObject = regExp.exec(responseText);
		if ( matchObject ) {
			info.uploaderId = matchObject[1];
		} else {
			info.uploaderId = null;
		}

		if ( videoInfo.title ) {
			info.title = videoInfo.title;
		} else {
			info.title = '';
		}

		regExp = /<span itemprop="thumbnail".*?href="(.*?)">/;
		matchObject = regExp.exec(responseText);
		if ( matchObject ) {
			info.thumb = matchObject[1];
		} else {
			if ( videoInfo.thumbnail_url ) {
				info.thumb = videoInfo.thumbnail_url;
			} else {
				info.thumb = null;
			}
		}

		regExp = /id="eow-date.*?>(.*?)<\/span>/;
		matchObject = regExp.exec(responseText);
		if ( matchObject ) {
			info.uploadDate = matchObject[1];
		} else {
			regExp = /id="watch-uploader-info".*?>.*?(?:Published|Uploaded|Streamed live) on (.*?)<\/strong>/;
			matchObject = regExp.exec(responseText);
			if (matchObject) {
				info.uploadDate = matchObject[1];
			} else {
				info.uploadDate = '';
			}
		}

		info.category = '';
		regExp = /<h4[^>]*>\s*Category\s*<\/h4>\s*<ul[^>]*>\s*(.*?)\s*<\/ul>/;
		matchObject = regExp.exec(responseText);
		if ( matchObject ) {
			regExp = /<a[^<]+>(.*?)<\/a>/g;
			matchObject = regExp.exec(matchObject[1]);
			if (matchObject[1]) {
				info.category = matchObject[1];
			}
		}


		regExp = /id="eow-description"[^>]*>(.*)?<\/p>/;
		matchObject = regExp.exec(responseText);
		if ( matchObject ) {
			info.description = matchObject[1];
		} else {
			regExp = /<meta name="description" content="([^"]+)"/;
			matchObject = regExp.exec(responseText);
			if (matchObject) {
				info.description = matchObject[1];
			} else {
				info.description = '';
			}
		}

		if ( videoInfo.length_seconds ) {
			info.lengthSeconds = parseInt(videoInfo.length_seconds, 10);
		} else {
			info.lengthSeconds = 0;
		}


		if ( videoInfo.ypc_video_rental_bar_text ) {
			info.ypc_video_rental_bar_text = videoInfo.ypc_video_rental_bar_text;
		} else {
			info.ypc_video_rental_bar_text = null;
		}
	}

	/**
	 * get signature from urlMap object
	 * @param {Object} obj from urlMap
	 * @return {boolean} no errors
	 */
	function getSig ( obj ) {
		var encryptedSig, regExp, matchObject, playerUrl, hashes, hash, i;

		encryptedSig = obj.sig;
		playerUrl = obj.playerUrl;
		if (!playerUrl) {
			obj.error = {};
			obj.error.videoId = info.videoId;
			obj.error.message = 'Cannot decrypt signature without player_url';
			obj.error.playerUrl = obj.playerUrl;
			onGetUrlDone(obj.error, obj);
			return false;
		}
		playerUrl = playerUrl.replace(/\\\//g, '/');
		if (playerUrl.indexOf('//') === 0) {
			playerUrl = 'https:' + playerUrl;
		}
		hashes = encryptedSig.split('.');
		hash = [];
		for ( i in hashes ) {
			hash.push(hashes[i].length);
		}
		hash = hash.join('.');
		hash = playerUrl + '_' + hash;
		if (!(hash in hashUrl)) {
			regExp = /.*?-([a-zA-Z0-9_-]+)(?:\/watch_as3|\/html5player)?\.([a-z]+)$/;
			matchObject = regExp.exec(playerUrl);
			if (!matchObject || !matchObject[2]) {
				obj.error = {};
				obj.error.videoId = info.videoId;
				obj.error.message = 'Cannot identify player';
				obj.error.playerUrl = obj.playerUrl;
				onGetUrlDone(obj.error, obj);
				return false;
			}
			if (matchObject[2] === 'js') {
				ajax('GET', playerUrl, getSigParseJS);
				return true;
			}
			if (matchObject[2] === 'swf') {
				obj.error = {};
				obj.error.videoId = info.videoId;
				obj.error.message = 'Flash player is not supported';
				obj.error.playerUrl = obj.playerUrl;
				onGetUrlDone(obj.error, obj);
				return false;
			}
			obj.error = {};
			obj.error.videoId = info.videoId;
			obj.error.playerUrl = obj.playerUrl;
			obj.error.message = 'Player type is not detected';
			onGetUrlDone(obj.error, obj);
		} else {
			obj.url += '&signature=' + hashUrl[hash];
			onGetUrlDone(obj.error, obj);
		}

		function getSigParseJS ( responseText ) {
			var jsplayer;

			regExp = /\.sig\|\|([a-zA-Z0-9$]+)\(/;
			matchObject = regExp.exec(responseText);
			//responseText = responseText.replace('navigator', 'window.navigator');
			responseText = responseText.replace(/}\)\(\);$/m, '');
			try {
				//responseText = '(function(){' + responseText;
				responseText += 'return ' + matchObject[1] + '("' + obj.sig + '");})()';
				//console.log(responseText);
				/*eslint-disable no-eval */
				//var navigator = window.navigator;navigator.platform = "Linux sh4";
				jsplayer = eval(responseText);
				/*eslint-enable no-eval */
			} catch (e) {
				obj.error = {};
				obj.error.videoId = info.videoId;
				obj.error.playerUrl = obj.playerUrl;
				obj.error.message = e.message;
				onGetUrlDone(obj.error, obj);
				return;
			}
			hashUrl[hash] = jsplayer;
			obj.url += '&signature=' + jsplayer;
			obj.needSig = false;
			onGetUrlDone(obj.error, obj);
		}

		return true;
	}

	/**
	 * enter point to start getting and parsing information about video
	 * @param {string} str link to video or video id
	 * @param {function} callback ( error, information, bestFormat object )
	 * @return {boolean} no any errors
	 */
	container.getInfo = function ( str, callback ) {
		var urlObj = {};

		getInfoCallback = callback || null;
		if ( str && str.indexOf('http') === 0 ) {
			urlObj = urlToObj(str);
			if (!urlObj.v) {
				info.error = {};
				info.error.videoId = info.videoId;
				info.error.message = 'URL has not video id';
				return false;
			}
		} else {
			urlObj.v = str;
		}

		if ( info.videoId === urlObj.v ) {
			onGetMapDone(info.error, info, info.urlMap[0]);
			return true;
		}
		getUrls(urlObj.v);
		return true;
	};

	clear();
	return container;
})();

