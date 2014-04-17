/**
 * Main STB objects declaration
 * not included directly anywhere
 * used for IDE autocompletion
 * also enables the desktop browser emulation
 * @author DarkPark
 */

var ENVIRONMENT = localStorage.getItem('ENVIRONMENT');
if (ENVIRONMENT) {
	ENVIRONMENT = JSON.parse(localStorage.getItem('ENVIRONMENT'));
} else {
	ENVIRONMENT = {
		"bootdelay"             : "1",
		"baudrate"              : "115200",
		"board"                 : "mag250",
		"monitor_base"          : "0xA0000000",
		"monitor_len"           : "0x00050000",
		"monitor_sec"           : "1:0-4",
		"loadaddr"              : "0x80000000",
		"unprot"                : "protect off $monitor_sec",
		"update"                : "erase $monitor_sec;cp.b $load_addr $monitor_base $monitor_len;protect on $monitor_sec",
		"mem"                   : "mem=160m bigphysarea=2048",
		"console"               : "ttyAS0",
		"ethinit"               : "nwhwconf=device:eth0",
		"autoconf"              : "off",
		"mtdparts"              : "mtdparts=stm-nand-flex.1:4M(Kernel),120M(RootFs),4M(Kernel2),120M(RootFs2),-(Userfs)",
		"mtdids"                : "nand0=stm-nand-flex.1",
		"partition"             : "nand0,0",
		"nfsargs"               : "setenv bootargs ${ethinit},hwaddr:${ethaddr} root=/dev/nfs nfsroot=${rootpath} ip=${ipaddr}::${gatewayip}:${netmask}:::${autoconf} ${mem}",
		"flashargs"             : "setenv bootargs ${ethinit},hwaddr:${ethaddr} root=/dev/mtdblock6 rootfstype=jffs2 ${mem}  ip=none",
		"flashargs2"            : "setenv bootargs ${ethinit},hwaddr:${ethaddr} root=/dev/mtdblock8 rootfstype=jffs2 ${mem}  ip=none",
		"addmisc"               : "setenv bootargs ${bootargs} ${mtdparts} console=${console},${baudrate} ",
		"kernel"                : "uImage",
		"flash_self"            : "run flashargs addmisc; mtdparts default; setenv partition nand0,0 ;fsload ${kernel}; bootm; run net ",
		"flash_self2"           : "run flashargs2 addmisc; mtdparts default; setenv partition nand0,2 ;fsload ${kernel}; bootm; run net ",
		"net"                   : "dhcp; run nfsargs addmisc; bootm; reset",
		"componentout"          : "YPrPb",
		"bootupgrade"           : "no",
		"do_factory_reset"      : "1",
		"serial#"               : "052012B031491",
		"Boot_Version"          : "008",
		"wifi_ssid"             : "default_ssid",
		"wifi_auth"             : "wpa2psk",
		"wifi_enc"              : "tkip",
		"wifi_int_ip"           : "0.0.0.0",
		"update_url"            : "igmp://224.50.0.51:9001",
		"bootstrap_url"         : "igmp://224.50.0.50:9000",
		"use_portal_dhcp"       : "true",
		"video_clock"           : "0",
		"ntpurl"                : "africa.pool.ntp.org",
		"settMaster"            : "1",
		"betaupdate_cond"       : "1",
		"timezone_conf_int"     : "plus_02_00_13",
		"ts_icon"               : "true",
		"ts_path"               : "/media/HDD-SATA-1",
		"ts_endType"            : "1",
		"upnp_conf"             : "lan",
		"front_panel"           : "0",
		"screen_clock"          : "0",
		"timezone_conf"         : "Europe/Kiev",
		"Ver_Forced"            : "no",
		"autoupdate_cond"       : "2",
		"audio_dyn_range_comp"  : "OFF",
		"audio_operational_mode": "RF_MODDE",
		"audio_stereo_out_mode" : "STEREO",
		"aspect_ratio"          : "default",
		"audio_initial_volume"  : "70",
		"ts_time"               : "900",
		"ts_exitType"           : "2",
		"ts_lag"                : "0",
		"lang_audiotracks"      : "0",
		"Image_Date"            : "Fri Jul 5 18:28:28 EEST 2013",
		"Image_Version"         : "216",
		"Image_Desc"            : "0.2.18-alpha3-250",
		"stdin"                 : "serial",
		"stdout"                : "serial",
		"stderr"                : "serial",
		"bootcmd"               : "run net",
		"ethaddr"               : "00:1a:79:04:c9:48",
		"debug_name"            : "bas",
		"ssaverDelay"           : "1800",
		"ssaverName"            : "abstract",
		"debug"                 : "1",
		"ts_on"                 : "true",
		"lang_subtitles"        : "1",
		"subtitles_on"          : "true",
		"tvsystem"              : "1080p-60",
		"graphicres"            : "1280",
		"language"              : "en"
	};
	localStorage.setItem('ENVIRONMENT', JSON.stringify(ENVIRONMENT));
}


/**
 * Main object gSTB methods declaration
 * TODO: expand description
 * @class gSTB
 */
var gSTB = {

	GetSystemPaths:
		function () { return '{"result":{"root":"/home/web/","media":"/media/"}}'; },

	GetDefaultUpdateUrl:
		function () { return 'http://aurahd.infomir.com.ua/imageupdate'; },

	GetHashVersion1:
		function ( data ) {},

	CloseWebWindow:
		function () {},

	/**
	 * Continues playing (after Pause()) or begin anew (after Stop())
	 */
	Continue:
		function () {},

	/**
	 * Shows the contents of the string text in the stream of standard output in the format:
	 *     DEBUG: [time] text
	 * @param {String} text this string is shown in the stream of standard output
	 */
	Debug:
		function ( text ) {},

	/**
	 * De-initializes the player
	 */
	DeinitPlayer:
		function () {},


	/**
	 * Delete all cookie saved by the browser.
	 * This function is realized only for the browser based on WebKit.
	 */
	DeleteAllCookies:
		function () {},


	/**
	 * Assign new status for "App" button handler.
	 * @param {Boolean} pEnable true – application will take control under "App" button; false - "App" button will be handled as regular button.
	 */
	EnableAppButton:
		function ( pEnable ) {},


	EnableCustomNavigation:
		function ( boolVal ) {},


	/**
	 * Enable/disable Javascript Interrupt dialog, when Javascript code does not respond for some long time.
	 * Use this function only for debugging purpose.
	 * @param {Boolean} enable true – enable interrupt; false – disable interrupt.
	 */
	EnableJavaScriptInterrupt:
		function ( enable ) {},


	EnableMulticastProxy:
		function ( boolVal ) {},

	/**
	 * Enables or disables automatic start of Service Menu by pressing "SET" ("service" on old RC) button.
	 * If button "SET" ("service" on old RC) is already used by JavaScript code, there may be a conflict.
	 * To avoid this conflict JavaScript code should disable automatic start of Service Menu and call directly function gSTB.StartLocalCfg every time it is required.
	 * @param {Boolean} bEnable flag: true - enable automatic start; false - disable automatic start
	 */
	EnableServiceButton:
		function ( bEnable ) {},


	/**
	 * Allow or forbid to set cookie from given domain.
	 * By default any domain is allowed to set cookie.
	 * Each call of this function adds domain (bEnable==false) or removes it (bEnable==true) from the list of domains that are not allowed to set cookie.
	 * @param {String} domain if bEnable == true then any attempt to set cookie from given domain will be ignored
	 * @param {Boolean} bEnable false - forbid to set cookie from given domain; true - allow to set cookie from given domain
	 */
	EnableSetCookieFrom:
		function ( domain, bEnable ) {},


	/**
	 * Enable or disable 2D navigation (arrow navigation) on web pages.
	 * 2D navigation is disabled by default, but could be enabled on previous web page, so it is recommended to disable 2D navigation if current page does not use it.
	 * @param {Boolean} bEnable false – disable 2D navigation; true – enable 2D navigation.
	 */
	EnableSpatialNavigation:
		function ( bEnable ) {},


	/**
	 * Enables or disables automatic show/hide of virtual keyboard by pressing "KB" ("empty" on old RC) button.
	 * If button "KB" ("empty" on old RC) is already used by JavaScript code, there may be a conflict.
	 * To avoid this conflict JavaScript code should disable automatic start of virtual keyboard and call directly functions gSTB.ShowVirtualKeyboard or gSTB.HideVirtualKeyboard every time it is required.
	 * @param {Boolean} bEnable false - disable automatic show/hide; true - enable automatic show/hide
	 */
	EnableVKButton:
		function ( bEnable ) {},


	EnableTvButton:
		function ( bEnable ) {},


	/**
	 * Performs the script /home/default/action.sh with the parameters set
	 * @param {String} action contains parameters for the script
	 * @example
	 *   gSTB.ExecAction("param 23 s") calls shell command "/home/default/action.sh param 23 s"
	 */
	ExecAction:
		function ( action ) {},

	ExtProtocolCommand:
		function ( strVal1, strVal2, strVal3 ) {},


	/**
	 * Force HDMI output to DVI mode.
	 * @param {Number} ForceDVI 0 – auto detect HDMI mode; 1 - force HDMI to DVI mode.
	 * @constructor
	 */
	ForceHDMItoDVI:
		function ( ForceDVI ) {},


	Get3DConversionMode:
		function () {},


	/**
	 * Receive the video window alpha transparency level
	 * @return {Number} Returne the current value of alpha transparency for the video window
	 */
	GetAlphaLevel:
		function () { return 0; },


	/**
	 * Return the current video content format.
	 * @return {Number} Consists of 2 tetrads:
	 * | 7 6 5 4 | 3 2 1 0 |
	 * |  aspH   |  aspL   |
	 * ForMAG100 aspH is always equal to 0.
	 * aspL - the aspect ratio: 0 – automatic; 1 – 20:9; 2 –16:9; 3 – 4:3.
	 * aspH - conversion of video format:
	 *   0 – as it is, video is stretched for the whole screen;
	 *   1 – Letter box mode, video is proportionally enlarged to the size of the screen along the larger edge;
	 *   2 – Pan&Scan mode, video is proportionally enlarged to the screen size along the lesser edge;
	 *   3 – combined mode, intermediate between Letter Box Box and Pan&Scan.
	 *   4 – enlarged mode;
	 *   5 – optimal mode.
	 *   Only for MAG200
	 */
	GetAspect:
		function () { return 0; },


	/**
	 * Receive the number (PID) of the current audio track.
	 * The list of all audio tracks determined by the player can be received with gSTB.GetAudioPIDs.
	 * @return {Number} Current audio track number (0..0x1fff)
	 */
	GetAudioPID:
		function () { return 0; },


	/**
	 * The function returns the list of audio tracks in the stream with the description of the language.
	 * This stream can be easily converted into a structure array by calling the function eval().
	 * This function must be called after the event having the code 2 occurs (see description of events)
	 * @return {String} List of the audio tracks found in the following format:
	 *   [{pid:<PID1>, lang:[<lang1_1>, <lang2_1>]}, ... , {pid:<PIDn>, lang:[ <lang1_n>, <lang2_n>]}]
	 * where:
	 *   PIDn - PID of audio track with the number n.
	 *   lang1_n, lang2_n - First two descriptions of languages in audio track with the number n (3-symbol tags according to ISO 639).
	 * @example
	 *   the result in the form: [{pid:114, lang:["rus", "ru"]}, {pid:115, lang:["eng", ""]}]
	 *   means that 2 audio streams were found in the stream: Russian having PID=114 and English having PID=115
	 */
	GetAudioPIDs:
		function () { return ''; },

	GetAudioPIDsEx:
		function () {},


	/**
	 * Receive current brightness of video output in SD.
	 * This function is realized only for the browser based on WebKit.
	 * @return {Number} Brightness of video output in SD mode (1..254)
	 */
	GetBrightness:
		function () { return 0; },


	/**
	 * Get current buffer loading in percents.
	 * It makes sense to call this function only with the following solutions: ffmpeg, ffrt, ffrt2, ffrt3, fm, file
	 * after getting event 2 from List of the events used and before complete buffer filling or before getting event 4 from List of the events used.
	 * @return {Number} [0..100] Current buffer loading in percents.
	 */
	GetBufferLoad:
		function () { return 0; },


	/**
	 * Receive current contrast of video output in SD mode.
	 * This function is realized only for the browser based on WebKit.
	 * @return {Number} Contrast of video output in SD mode (-128..127)
	 */
	GetContrast:
		function () { return 0; },


	/**
	 * Get active bank of NAND.
	 * @return {String} the same as /home/default/rdir.cgi GetCurrentBank
	 */
	GetDeviceActiveBank:
		function () { return ""; },


	/**
	 * Get image description.
	 * @return {String} info
	 */
	GetDeviceImageDesc:
		function () { return ""; },


	/**
	 * Get image version.
	 * @return {String} info
	 */
	GetDeviceImageVersion:
		function () { return '218'; },


	/**
	 * Get current image version.
	 * @return {String} info
	 */
	GetDeviceImageVersionCurrent:
		function () { return '0.2.16-250 Tue Apr 9 18:10:19 EEST 2013'; },


	/**
	 * Get MAC address.
	 * @return {String} info
	 */
	GetDeviceMacAddress:
		function () { return '00:00:00:00:00:00'; },


	/**
	 * Get model of the device.
	 * @return {String} info
	 */
	GetDeviceModel:
		function () { return 'MAG250'; },


	/**
	 * STV model name
	 * @return {String}
	 */
	GetDeviceModelExt:
		function () { return 'AuraHD1' },


	/**
	 * Get serial number.
	 * @return {String} the number
	 */
	GetDeviceSerialNumber:
		function () { return '052012B031491'; },


	/**
	 * Get vendor information.
	 * @return {String} info
	 */
	GetDeviceVendor:
		function () { return ""; },


	/**
	 * Get hardware information.
	 * @return {String} info
	 */
	GetDeviceVersionHardware:
		function () { return ""; },


	/**
	 * Read specified boot loader’s variables.
	 * @param {String} strVal Read values for variables specified in the array.
	 *   Format: JSON object with pair named "varList" that has array of non empty strings as value.
	 *   Example: {"varList":["a", "b", "timezone_conf_int", "wifi_ssid", "update"]}
	 * @return {String} Value of "result" pair is JSON object.
	 *   Format: JSON object. Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 *     Pair "result" will hold result of operation. Object is holding only pairs. Each pair name is equal to variable name. And value of the pair representing value of variable in string notation.
	 *   Example:
	 *     {"result":{"a":"b","b":"","timezone_conf_int":"plus_02_00_13","wifi_ssid":"default_ssid","update":"erase $monitor_sec;cp.b $load_addr $monitor_base $monitor_len;protect on $monitor_sec"},"errMsg":""}
	 */
	GetEnv:
		function ( strVal ) {
			var request = JSON.parse(strVal),
				result  = {};
			request.varList.forEach(function(item){
				result[item] = ENVIRONMENT[item] || '';
			});
			return JSON.stringify({result:result});
		},


	GetExtProtocolList:
		function () {},


	/**
	 * Get link status of LAN network interface (eth0).
	 * @return {Boolean} true – link is active; false – no link connection.
	 */
	GetLanLinkStatus:
		function () { return true; },


	/**
	 * Gets the duration of the current content
	 * @return {Number} total duration of the current content in seconds
	 */
	GetMediaLen:
		function () { return 0; },


	/**
	 * Gets the duration of the current content in ms
	 * @return {Number} total duration of the current content in ms
	 */
	GetMediaLenEx:
		function () { return 0; },


	/**
	 * Get metadata information stored in current content. For example it can be data from ID3 tag from mp3 file.
	 * It makes sense to call this function after getting event 2 from List of the events used.
	 * @return {String} Metadata from current content in the following form:
	 *   {"album":"album_1", "album_artist":"artist_1", "artist":"artist_1", "comment":"", "composer":"", "copyright":"", "date":"2000", "disc":"", "encoder":"", "encoded_by":"", "filename":"", "genre":"", "language":"", "performer":"", "publisher":"publisher_1", "title":"track_9", "track":"9"}
	 */
	GetMetadataInfo:
		function () { return ""; },


    /**
     * Receive the current microphone volume level.
     * Platform : MAG100
     * @return {Number} Returns the current microphone volume level (0..100).
     */
	GetMicVolume:
		function () { return 0; },


    /**
     * Receive the muted state of audio output.
     * @return {Number} Returns whether audio output is muted (mute==1) or not (mute==0).
     */
	GetMute:
		function () { return 0; },

	GetNetworkGateways:
		function () {},

	GetNetworkNameServers:
		function () {},

	GetNetworkWifiMac:
		function () {},


	/**
	 * Receive the video window state
	 * @return {Boolean} The result specifies whether full screen mode is set for the video window:
	 * true – the content is displayed in a reduced window;
	 * false – the content is displayed in a full screen mode.
	 */
	GetPIG:
		function () { return true; },


	/**
	 * Gets the current position in percent
	 * @return {Number} The current position in percent of the whole duration of the content (0..100)
	 */
	GetPosPercent:
		function () { return 0; },


	/**
	 * Gets the current position in hundredth fractions of percent.
	 * @return {Number} The current position in percent of the whole duration of content (0..10000)
	 */
	GetPosPercentEx:
		function () { return 0; },


	/**
	 * Gets the current position in time
	 * @return {Number} the current position in second from the beginning of content
	 */
	GetPosTime:
		function () { return 0; },


	/**
	 * Gets the current position in time in ms
	 * @return {Number} the current position in ms from the beginning of content
	 */
	GetPosTimeEx:
		function () { return 0; },


	GetPppoeIp:
		function () {},

	GetPppoeLinkStatus:
		function () {},


	/**
	 * Receive current saturation of video output in SD mode.
	 * This function is realized only for the browser based on WebKit.
	 * @return {Number} Saturation of video output in SD mode (1..254)
	 */
	GetSaturation:
		function () { return 0; },


	/**
	 * Fetching available workgroups.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @return {String} JSON object with array of groups.
	 * @example
	 *   {"result":	["DUNE", "WORKGROUP"], "errMsg": ""}
	 */
	GetSmbGroups:
		function () { return ""; },


	/**
	 * Fetching available servers for given work group.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @param args pair "group" defines workgroup of interest, for example: {"group":"workgroup"}
	 * @return {String} JSON object with array of servers
	 * @example
	 *   {"result": ["ARCHIVE", "EDDY", "SANDBOX"], "errMsg": ""}
	 */
	GetSmbServers:
		function ( args ) { return ''; },


	/**
	 * Fetching available shares for given server.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @param {String} args Pair "server" defines server of interest, for example: {"server": "ALEX-PC"}
	 * @return {String} JSON object. Pair "shares" is an array of shares. Pair "serverIP" holds IP address of given server
	 * @example
	 *   {"result": {"shares": ["share", "photo"], "serverIP": "192.168.100.1"}, "errMsg": ""}
	 */
	GetSmbShares:
		function ( args ) { return ''; },


	/**
	 * Receive the current speed of display
	 * @return {Number} Current speed of display:
	 * 1 - normal; 2 - 2x; 3 - 4x; 4 - 8x; 5 - 16x; 6 - 1/2; 7 - 1/4;
	 * 8 - 12x; -1 - reverse; -2 - reverse 2x; -3 - reverse 4x;
	 * -4 - reverse 8x; -5 - reverse 16x; -8 - reverse 12x
	 */
	GetSpeed:
		function () { return 0 },


	/**
	 * Get all mount point info
	 * @param param
	 * @return {String}
	 */
	GetStorageInfo:
		function ( param ) { return '{}' },


	/**
	 * Receive the number (PID) of the current subtitles track.
	 * The list of all subtitles track determined by the player can be received with gSTB.GetSubtitlePIDs.
	 * @return {Number} Current subtitles track number (0..0x1fff)
	 */
	GetSubtitlePID:
		function () { return 0; },


	/**
	 * The function returns the list of subtitles track in the stream with the description of the language.
	 * This string can be easily converted into a structure array by calling the function eval().
	 * This function must be called after the event having the code 2 occurs (see description of events)
	 * @return {String} List of subtitles tracks found in the following format:
	 *   [{pid:<PID1>, lang:[<lang1_1>, <lang2_1>]}, ... , {pid:<PIDn>, lang:[ <lang1_n>, <lang2_n>]}]
	 * where:
	 *   PIDn - PID of subtitle track with the number n.
	 *   lang1_n, lang2_n - First two descriptions of languages in subtitle track with the number n (3-symbol tags according to ISO 639).
	 * @example
	 *   the result in the form: [{pid:114, lang:["rus", "ru"]}, {pid:115, lang:["eng", ""]}]
	 *   means that 2 subtitle streams were found in the stream: Russian having PID=114 and English having PID=115
	 */
	GetSubtitlePIDs:
		function () { return ''; },


	GetTeletextPID:
		function () {},

	GetTeletextPIDs:
		function () {},


	/**
	 * Returns the colour considered transparent at the moment
	 * @return {Number} The colour in RGB format considered transparent at the moment (0..0xffffff)
	 */
	GetTransparentColor:
		function () { return 0; },


	/**
	 * Get information about current video content.
	 * Function must be called after receiving event 7 from List of the events used.
	 * @return {String} Returns string in the following form:
	 *   {frameRate:25000,pictureWidth:704,pictureHeight:576,hPAR:12,vPAR:11},
	 *   where
	 *     frameRate – video frame rate.
	 *     pictureWidth – encoded video width.
	 *     pictureHeight – encoded video height.
	 *     hPAR and vPAR – pixel aspect ratio coefficients. In example above these params mean that movie aspect ratio is: (704*hPAR/vPAR)/576 = 1.333333333(3) = 4:3 for square pixels.
	 */
	GetVideoInfo:
		function () { return ""; },


    /**
     * Receive the volume level.
     * @return {Number} Returns the current volume level (0..100).
     */
	GetVolume:
		function () { return 0; },


	/**
	 * Return wep 128 bit keys for given passphrase.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @param {String} passPhrase passphrase, 1-32 symbols.
	 * @return {String} JSON object (contain 128 bit wep key):
	 *   {"errMsg": "",	"result": {"wep128-key1": "46f04863257ac8040905ea0002"}}
	 */
	GetWepKey128ByPassPhrase:
		function ( passPhrase ) { return ''; },


	/**
	 * Return wep 64 bit keys for given passphrase.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @param {String} passPhrase passphrase, 1-32 symbols.
	 * @return {String} JSON object (contain four 64 bit wep keys):
	 *   {"errMsg": "",	"result": {
	 *     "wep64-key1": "c6774663dd",
	 *     "wep64-key2": "af6bd13ecd",
	 *     "wep64-key3": "8e33fb2bf1",
	 *     "wep64-key4": "cf12611e1d"
	 *   }}
	 */
	GetWepKey64ByPassPhrase:
		function ( passPhrase ) { return ""; },


	/**
	 * Make scan and return list of found wireless groups (SSID).
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @return {String} an array of JSON objects. Each object representing wireless group and has following attributes:
	 *   "ssid" – name of group
	 *   "auth" – authentication method ("WPA", "WPA2", "WEPAUTO", "NONE")
	 *   "enc" – encoding ("CCMP", "TKIP", "NONE")
	 *   "signalInfo" – signal strength (numeric value)
	 *   "rfInfo" – string information about channel
	 * @example
	 *   {"errMsg": "", "result": [
	 *     {
	 *       "ssid": "dlink",
	 *       "auth": "WPA2",
	 *       "enc": "TKIP",
	 *       "signalInfo": "-47",
	 *       "rfInfo": "Frequency:2.412 GHz (Channel 1)"
	 *     },
	 *     {
	 *       "ssid": "linksys3E66",
	 *       "auth": "WEPAUTO",
	 *       "enc": "WEP",
	 *       "signalInfo": "-67",
	 *       "rfInfo": "Frequency:2.427 GHz (Channel 4)"
	 *     }
	 *   ]}
	 */
	GetWifiGroups:
		function () { return ""; },


	/**
	 * Get link status of WiFi network interface.
	 * @return {Boolean} true – link is active; false – no connection to wifi access point.
	 */
	GetWifiLinkStatus:
		function () { return true; },


	/**
	 * Receive the level of alpha transparency for the set window
	 * @param {Number} winNum Number of the window for which this function is used:
	 * 0 – graphic window;
	 * 1 – video window.
	 * @return {Number} Returns the current value of alpha transparency for video window (0..255)
	 */
	GetWinAlphaLevel:
		function ( winNum ) { return 0; },


	/**
	 * Hides the virtual keyboard from the screen
	 */
	HideVirtualKeyboard:
		function () {},


	HideVirtualKeyboardEx:
		function () {},


	/**
	 * Blocks or unblocks the screen browser upgrade
	 * @param {Boolean} bIgnore flag:
	 * true – after this call the graphic window stops upgrading till the next call with the parameter false;
	 * false – after this call the graphic window resumes upgrading – passing to normal mode.
	 */
	IgnoreUpdates:
		function ( bIgnore ) {},


	/**
	 * Initializes the player
	 * Should be called before the first use of the player
	 */
	InitPlayer:
		function () {},


	/**
	 * Test is file name point to existing file.
	 * @param {String} fileName absolute file path which will be tested
	 * @return {Boolean} true if file name is absolute path and it points to existing file
	 */
	IsFileExist:
		function ( fileName ) { return true; },


	/**
	 * Test is file name point to existing folder.
	 * @param {String} fileName absolute file path which will be tested
	 * @return {Boolean} true if file name is absolute path and it points to existing folder
	 */
	IsFolderExist:
		function ( fileName ) { return true; },

	/**
	 * Checks if the given file has UTF8 encoding
	 * @param {String} fileName
	 * @return {boolean}
	 */
	IsFileUTF8Encoded:
		function ( fileName ) { return true; },

	/**
	 * This function indicating that internal portal been started.
	 * @return {Boolean} true when internal portal has been started
	 */
	IsInternalPortalActive:
		function () { return true; },


	/**
	 * Receive the current state of display
	 * @return {Boolean} Current state of player: false – currently is not playing; true – currently is playing.
	 */
	IsPlaying:
		function () { return false; },


	IsVirtualKeyboardActive:
		function () {},

	IsVirtualKeyboardActiveEx:
		function () {},


	/**
	 * Returns the list of directories and files having the extension set with SetListFilesExt, located in the directory dirName.
	 * This function is realized only for the browser based on WebKit. For browsers based on FireFox such function can be realized using the function RDir with the parameter "rdir".
	 * @param {String} dirName Route to the directory the contents whereof must be received.
	 * @param {Boolean} lastModified Flag is the last modification time is necessary
	 * @return {String} The string in the following form is returned:
	 *   var dirs = ["dir1/", ..., "dirN/",	""]
	 *   var files = [{"name":"fileName1", "size":size1}, ..., {"name":"fileNameM", "size":sizeM}]
	 *   where dirN – the name of N-sub-directory, fileNameM and sizeM – name and size of M-file.
	 */
	ListDir:
		function ( dirName, lastModified ) {
			if ( dirName.indexOf('system/pages/screensaver') !== -1 ) {
				return 'var dirs = ["abstract/", "blank/", "clock/", ""]; var files = [{}]';
			}
			return 'var dirs = []; var files = [{}]';
		},


	/**
	 * Load CAS settings from the set file.
	 * See instruction on adjusting CAS Verimatrix in the supplement.
	 * The call of the function becomes effective only if made before gSTB.SetCASType.
	 * Platforms: MAG100, MAG200
	 * @param {String} iniFileName URL of the settings file in the root file system
	 */
	LoadCASIniFile:
		function ( iniFileName ) {},


	/**
	 * Load text subtitles from external subtitle file of srt, sub, ass formats.
	 * If subtitles are loaded successfully then external subtitle track will be added to subtitle track list with number(PID) 0x2000.
	 * If any error occurs while loading subtitles then JS API user will receive event with code 8 from List of the events used.
	 * @param {String} url URL which points to external subtitles. Can be a local URL: "/media/USB-.../subtitles.srt"
	 * and URL from http server: "http://192.168.1.1/subtitles.srt"
	 */
	LoadExternalSubtitles:
		function ( url ) {},


	LoadURL:
		function ( strVal ) {},

	/**
	 * Load selected configuration file from folder /home/web/system/config
 	 * @param {String} strVal file name
	 * @returns {String} text file content
	 * @example LoadWebconfigData('default.ini');
	 */
	LoadWebconfigData:	// TODO: useless function
		function ( strVal ){
			return localStorage.getItem(strVal) || '';
		},

	/**
	 * Load file from mnt/Userfs/data
	 * @param {String} strVal file name
	 * @returns {String} file content
	 */
	LoadUserData:
		function ( strVal ) {
			return localStorage.getItem(strVal) || '';
		},

	/**
	 * Pauses current playback.
	 * Continue() continues playing from the current position.
	 */
	Pause:
		function () {},


	/**
	 * Starts playing media content as specified in playStr
	 * Can use the given proxy server for http playback
	 * Proxy server settings are valid till the next call of gSTB.Play()
	 * @param {String} playStr in the format: "solution URL [atrack:anum] [vtrack:vnum] [strack:snum] [subURL:subtitleUrl]"
	 * <p>solution - Media content type. Depends on the IPTV-device type (see Appendix 2 for the table of supported formats and the description of media content types)</p>
	 * <p>URL - Address of the content to be started for playing. Depends on the type (see more detailed information in Appendix 2)</p>
	 * <p>atrack:anum - Sets the number(PID) of audio track (optional parameter)</p>
	 * <p>vtrack:vnum - Sets the number(PID) of audio track (optional parameter)</p>
	 * <p>strack:snum - Sets the number(PID) of subtitle track (optional parameter)</p>
	 * <p>subURL:subtitleURL - Sets the URL of external subtitles file. See gSTB.LoadExternalSubtitles (optional parameter)</p>
	 * @param {String} [proxy_params] in the format: "http://[username[:password]@]proxy_addr:proxy_port"
	 * <p>Proxy server settings are affect only http playback and valid till the next call of gSTB.Play().</p>
	 */
	Play:
		function ( playStr, proxy_params ) {},


	/**
	 * Play media content of the preset type (solution) from the preset URL.
	 * @param {String} Solution Corresponds to the parameter solution from the function gSTB.Play
	 * @param {String} URL Address of the content to be started for playing. Depends on the type. See more detailed information in supplement 2.
	 */
	PlaySolution:
		function ( Solution, URL ) {},

	/**
	 * Performs script /home/default/rdir.cgi with set parameters and return the standard output of this script.
	 * The rdir.cgi supplied with the root file system has several commands preset:
	 * 	 gSTB.RDir("SerialNumber",x) – x returns serial number of this device to x.
	 * 	 gSTB.RDir("MACAddress",x) - receive MAC address
	 * 	 gSTB.RDir("IPAddress",x) - receive IP address
	 * 	 gSTB.RDir("HardwareVersion",x) – receive hardware version
	 * 	 gSTB.RDir("Vendor",x) – receive the name of STB manufacturer
	 * 	 gSTB.RDir("Model ",x) – receive the name of STB pattern
	 * 	 gSTB.RDir("ImageVersion",x) – receive the version of the software flash image gSTB.RDir("ImageDescription",x) – receive the information on the image of the software flash
	 * 	 gSTB.RDir("ImageDate",x) – receive the date of creation of the flash software image.
	 * 	 gSTB.RDir("getenv v_name",x) – receive the value of environment variable with the name v_name. See detailed description of operations with environment variables in supplement 11.
	 * 	 gSTB.RDir("setenv v_name value") – set environment variable with the name v_name to the value value. See detailed description of operations with environment variables in supplement 11.
	 * 	 gSTB.RDir("ResolveIP hostname") – resolve hostname to IP address.
	 * @param {String} value contains parameters with which the script /home/default/rdi.cgi is started
	 * @return {String} standard output received by performing the script with parameters set
	 */
	RDir:
		function ( value ) { return ''; },


	/**
	 * Read the file of portal settings /etc/stb_params
	 * @return {String} Returns the contents of the file /etc/stb_params
	 */
	ReadCFG:
		function () { return ''; },


	ResetUserFs:
		function () {},


    /**
     * Rotate video.
     * @param {Number} Angle Rotates the video window contents by the preset angle relative to the initial position.
     * Allowed values: 0, 90, 180, 270.
     */
	Rotate:
		function ( Angle ) {},

	/**
	 * Save file at mnt/Userfs/data
	 * @param filename file name
	 * @param filedata data for saving
	 */
	SaveUserData:
		function ( filename, filedata ) {
			localStorage.setItem(filename, filedata);
		},


	/**
	 * Send an event to portal webkit window.
	 * You can handle the event by defining event handler: stbEvent.onPortalEvent(string).
	 * @param {String} pArgs This argument will be passed to event handler stbEvent.onPortalEvent(string).
	 */
	SendEventToPortal:
		function ( pArgs ) {},


	/**
	 * Execute control actions for background service.
	 * Pair "errMsg" will have empty string as value in case of success execution and non localized string that representing error condition.
	 * Pair "result" will hold result of operation.
	 * @param {String} serviceName Service name. For example: "network", "pppoe".
	 * @param {String} Action Action to execute for given service. For example: "start", "restart".
	 * @return {String} result JSON object contain status pair. Value is always "ok".
	 * @example
	 *   {"errMsg": "", "result": {"status": "ok"}}
	 */
	ServiceControl:
		function ( serviceName, Action ) { return ""; },


	Set3DConversionMode:
		function ( intVal ) {},


	/**
	 * Set additional CAS parameters.
	 * Call of the function becomes effective only if made before gSTB.SetCASType.
	 * Platforms: MAG200
	 * @param {String} paramName Additional parameter name
	 * @param {String} paramValue Additional parameter value
	 * @constructor
	 */
	SetAdditionalCasParam:
		function ( paramName, paramValue ) {},


	/**
	 * Sets alpha transparency of the video window
	 * @param {Number} Alpha Transpareny of the video window: 0 - completely transparent; 255 - completely opaque.
	 */
	SetAlphaLevel:
		function ( Alpha ) {},


    /**
     * Set video picture format.
     * MAG100 ignores aspH. MAG200 uses aspL only in windows mode, while aslH only in full screen mode, see. gSTB.SetPIG
     * @param {Number} Aspect Sets the video picture format. Consists of 2 tetrads:
     * | 7 6 5 4 | 3 2 1 0 |
     * |  aspH   |  aspL   |
     * aspL - Sets the aspect ratio: 0 – automatic; 1 – 20:9; 2 –16:9; 3 – 4:3.
     * aspH - Sets conversion of video format:
     *   0 – as it is, video is stretched for the whole screen;
     *   1 – Letter box mode, video is proportionally enlarged to the size of the screen along the larger edge;
     *   2 – Pan&Scan mode, video is proportionally enlarged to the screen size along the lesser edge;
     *   3 – combined mode, intermediate between Letter Box Box and Pan&Scan.
     *   4 – enlarged mode;
     *   5 – optimal mode.
     *   Only for MAG200
     */
	SetAspect:
		function ( Aspect ) {},


	/**
	 * Set languages of audio tracks to be automatically selected when receiving the information on the channel.
	 * Each param is a 3–symbol tags according to ISO 639, for example: "rus" or "eng".
	 * If the information of several audio tracks is present the player selects the track preset by the language priLang.
	 * If such track is not found, the track with the language secLang is selected.
	 * If this one is not found either the first track from the list is selected.
	 * @param {String} priLang primary language
	 * @param {String} secLang secondary language
	 */
	SetAudioLangs:
		function ( priLang, secLang ) {},


	/**
	 * Set Operational Mode for DolbyDigital audio.
	 * Affects only DolbyDigital audio.
	 * @param {Number} mode 0 – RF mode; 1 – Line mode; 2 – Custom0; 3 – Custom1.
	 */
	SetAudioOperationalMode:
		function ( mode ) {},


	/**
	 * Sets track number (PID) for audio
	 * @param {Number} Pid Sets the number or PID of the audio track to be played in the current content. If such track is absent the sound will be disabled.
	 */
	SetAudioPID:
		function ( Pid ) {},


	/**
	 * Set the current AutoFrameRate mode, which allows to automatically switch frame rate of HDMI video output according to a content frame rate.
	 * Auto frame rate switching works with ffmpeg, file, ffrt2, ffrt3 solutions if player has managed to detect frame rate of content.
	 * Auto frame rate switching works only on HDMI output in the following modes: 720p-50/60, 1080i-50/60 and 1080p-50/60. In 720p-50/60 modes player can switch only to 720p-50/60 modes.
	 * After stopping playback video output switches back to original frame rate.
	 * Warning: Not every TV supports 1080p-24 video mode.
	 * @param {Number} mode contains bit flags which specify a set of frame rates to which video output can automatically switch:
	 *   1 – can switch to 1080p-24 mode;
	 *   2 – can switch to 720p-50, 1080i-50, 1080p-50 modes;
	 *   4 – can switch to 720p-60, 1080i-60, 1080p-60.
	 *     for example:
	 *     gSTB.SetAutoFrameRate(0) disables auto frame rate switching.
	 *     gSTB.SetAutoFrameRate(7) enables switching to frame rates 24,50 and 60.
	 */
	SetAutoFrameRate:
		function ( mode ) {},


	/**
	 * Set the brightness of video output in SD mode.
	 * This function is realized only for the browsers based on WebKit.
	 * @param {Number} Bri Brightness in the SD mode (1..254)
	 */
	SetBrightness:
		function ( Bri ) {},


	/**
	 * Set input buffer size for buffering support.
	 * Buffering works only with the following solutions: ffmpeg, ffrt, ffrt2, ffrt3, fm, file.
	 * After start of playback with mentioned above solution the following stages take place:
	 *   - opening content for playback;
	 *   - determining content information (raised event 2 from List of the events used);
	 *   - input buffer filling stage;
	 *   - after complete buffer filling stream data go to decoding;
	 *   - decoded data go to outputs (raised event 4 from List of the events used).
	 * @param {Number} sizeInMs Buffer size in ms.
	 * @param {Number} maxSizeInBytes Maximum buffer size in bytes. Used ONLY to limit maximum amount of allocated memory but not as the primary buffer size.
	 */
	SetBufferSize:
		function ( sizeInMs, maxSizeInBytes ) {},


	/**
	 * Set hard or soft mode of descrambling.
	 * At present the use of the function is expedient only for CAS Verimatrix.
	 * Depending on the mode set, the player can descramble only the streams scrambled by the following algorithm:
	 *   Soft mode: RC4, AES;
	 *   Hard mode: AES, DVB-CSA.
	 * This mode is set only once after the start of the portal.
	 * The call of the function becomes effective only if it is called before gSTB.SetCASType.
	 * PLatforms: MAG100, MAG200
	 * @param {Number} isSoftware 0 –use hard descrambling; 1 – use soft descrambling.
	 * In the absence of this call soft descrambling is used. Only soft descrambling can be used for MAG100.
	 */
	SetCASDescrambling:
		function ( isSoftware ) {},


	/**
	 * Set CAS server parameters.
	 * Call of the function becomes effective only if made before gSTB.SetCASType.
	 * Platforms: MAG100, MAG200
	 * @param {String} serverAddr CAS server URL
	 * @param {Number} serverPort CAS server port
	 * @param {String} companyName Name of the company under which this operator is registered on CAS server
	 * @param {Number} opID Operator identifier used by STB. If opID is equal to -1, the value is not updated
	 * @param {Number} errorLevel Level of error. 0 – minimal level. If error Level equals -1 it is not updated
	 * @constructor
	 */
	SetCASParam:
		function ( serverAddr, serverPort, companyName, opID, errorLevel ) {},


	/**
	 * Set default access server type after each start of the portal.
	 * Set default server type once after each start of the portal.
	 * Platforms: MAG100, MAG200
	 * @param {Number} Type 0 – not set; 1 – Verimatrix; 2 – SecureMedia, 4-10 – custom CAS plugin with corresponding number.
	 */
	SetCASType:
		function ( Type ) {},


	SetCheckSSLCertificate:
		function ( intVal ) {},


    /**
     * Set the preset colour and mask for using as ChromaKey (the transparency of any colour on the whole window).
     * Any changes on the screen shall be visible only subject to switching on the regime ChromaKey by the functions gSTB.SetMode or gSTB.SetWinMode.
     * @param {Number} key Sets the colour in RGB. If ythe colour of a window pixel coincides with this colour after masking, the pixel is considered transparent.
     * @param {Number} mask Set the mask for key. If the mask is equal to 0xffffff, the colour set by the parameter key is considered transparent.
     * @constructor
     */
	SetChromaKey:
		function ( key, mask ) {},

	SetComponentMode:
		function ( intVal ) {},


	/**
	 * Set contrast of video output in SD mode.
	 * This function is realized only for the browser based on WebKit.
	 * @param {Number} Con Video output contrast in SD mode (-128..127)
	 */
	SetContrast:
		function ( Con ) {},


	SetCustomHeader:
		function ( strVal ) {},


	/**
	 * Turns on/off flicker-filter with the default parameters.
	 * Flicker filter on MAG200 is applicable only for graphic window, this is why it is recommended to set its only once and keep it switched.
	 * @param {Number} state Flicker-filter on/off: 0 - switch off the Flicker-filter; 1 - switch on the Flicker-filter. In this case default values for sharpness and flicker are set.
	 */
	SetDefaultFlicker:
		function ( state ) {},


	/**
	 * Set dynamic range compression for DolbyDigital audio.
	 * @param {Number} high Compression level for high range. 0 – DRC is off.
	 * @param {Number} low Compression level for low range. 0 – DRC is off.
	 */
	SetDRC:
		function ( high, low ) {},


	/**
	 * Setting up values of specified boot loader’s variables.
	 * @param {String} envList Each pair's name is referencing name of variable. And pair value will be used as new value of variable.
	 * If referencing variable does not exist it will be created. If new value is empty string then value will be deleted.
	 *   Format: JSON object, which holding only pairs. Each pair has string value. Each pair name is referencing variable name. And pair value will set new value.
	 *   Example: {"a34":"b34", "c34":"", "c34":"d34"}
	 * @return {Boolean} Result of operation
	 */
	SetEnv:
		function ( envList ) {
			if ( envList ) {
				envList = JSON.parse(envList);
				if ( envList ) for ( var name in envList ) {
					if ( envList[name] === '' ) {
						delete ENVIRONMENT[name];
					} else {
						ENVIRONMENT[name] = envList[name];
					}
				}
				localStorage.setItem('ENVIRONMENT', JSON.stringify(ENVIRONMENT));
			}
			return true;
		},


	/**
	 * Sets Flicker-filter parameters
	 * Flicker filter on MAG200 is applicable only for graphic window, therefore it is advised to set it only once during loading and not to switch it off
	 * flk and shp parameters are ignored for MAG 200
	 * @param {Number} State Flicker filter on/off: 0 - switch off the flicker-filter; 1 - switch on the flicker-filter
	 * @param {Number} Flk Flicker level
	 * @param {Number} Shp Sharpness level
	 */
	SetFlicker:
		function ( State, Flk, Shp ) {},


	/**
	 * Set HDMI audio format.
	 * @param {Number} type can be one of:
	 * 0 – HDMI transmits PCM audio;
	 * 1 – HDMI transmits SPdif audio (in that case SPDif output mode is set by gSTB.SetupSPdif)
	 */
	SetHDMIAudioOut:
		function ( type ) {},

	SetInternalPortalActive:
		function ( boolVal ) {},


	/**
	 * Set the list of file extensions for returning to the function gSTB.ListDir.
	 * This function is realized only for the browser based on WebKit.
	 * @param {String} fileExts List of files extensions followed by a space. For example: ".mkv .mov .mpg"
	 */
	SetListFilesExt:
		function ( fileExts ) {},


	/**
	 * Sets or cancels repeated playing
	 * @param {Number} Loop 0 - switch off repeated playing on the content; 1 - switch on repeated playing on the content
	 */
	SetLoop:
		function ( Loop ) {},


    /**
     * Set the microphone volume level.
     * Platform: MAG100
     * @param {Number} Micvol Set the microphone volume level (0..100): 0 – minimal volume; 100 – maximal volume.
     */
	SetMicVolume:
		function ( Micvol ) {},


    /**
     * Switch on (mode=1) or switch off (mode=0) the mode ChromaKey for the video window.
     * @param {Number} Mode ChromaKey mode for the video window: 0 – off; 1 – on.
     * The parameters set by gSTB.SetChromaKey gSTB.SetTransparentColor shall be valid if the on-mode is used.
     */
	SetMode:
		function ( Mode ) {},


	SetMulticastProxyURL:
		function ( strVal ) {},


    /**
     * Switch off or on the sound restoring the volume level.
     * After the cycle of switching off/on with this function is completed the volume level remains unchanged.
     * @param {Number} Mute Switches on/switches off the sound: 0 – on; 1 – off.
     */
	SetMute:
		function ( Mute ) {},

	/**
	 * True to apply new native string handling without utf8/utf16 encoding/decoding
	 * @param {Boolean} boolVal true - all string manipulations are with utf16 strings
	 */
	SetNativeStringMode:
		function ( boolVal ) {},

	SetObjectCacheCapacities:
		function ( intVal1, intVal2, intVal3 ) {},

	SetPCRModeEnabled:
		function ( boolVal ) {},


	/**
	 * Sets position and mode of video window
	 * @param {Number} State If state=1 show the video on full screen. If state=0 show the video in the specified rectangle.
	 * @param {Number} Scale The scale of the video window. The present multiplier of the video window size equals to scale/256.
	 * @param {Number} X Horizontal offset of the upper left corner of the video window from the screen edge.
	 * @param {Number} Y Vertical offset of the upper left corner of the video window from the screen edge.
	 */
	SetPIG:
		function ( State, Scale, X, Y ) {},


	SetPixmapCacheSize:
		function ( intVal ) {},


	/**
	 * Sets the current position in percent.
	 * @param {Number} Prc The position in percent (0..100) of the total duration of the content where playback should start.
	 */
	SetPosPercent:
		function ( Prc ) {},


	/**
	 * Sets the current position in hundredth fractions of percent.
	 * @param {Number} Prc Position in hundredth fractions of percent (0..10000) of the total duration of the content, from which the playback should start.
	 */
	SetPosPercentEx:
		function ( Prc ) {},


	/**
	 * Sets the new position of playback in time
	 * @param {Number} time the position in seconds (time >= 0) from the beginning of the content where the playback should start (positioning in the content)
	 */
	SetPosTime:
		function ( time ) {},


	/**
	 * Sets the current playback position in time (ms)
	 * @param {Number} time position in ms (time >= 0) from the beginning of the content where playback should start (positioning in the content)
	 */
	SetPosTimeEx:
		function ( time ) {},


	/**
	 * Set the saturation of video output in SD mode.
	 * This function is realized only for the browser based on WebKit.
	 * @param {Number} Sat Saturation of video output in SD mode (1..254)
	 */
	SetSaturation:
		function ( Sat ) {},


	/**
	 * Time to screensaver activation on idle
	 * @param {Number} timeSec 0 - disable SS, 1-59 value ceiling to 60
	 */
	SetScreenSaverTime:
		function ( timeSec ) {},


	/**
	 * Set screensaver window init parameters
	 * @param {Object} options parameters from windowInit function
	 */
	SetScreenSaverInitAttr:
		function ( options ) {},


	/**
	 * Set system settings init parameters
	 * @param {Object} options parameters from windowInit function
	 */
	SetSettingsInitAttr:
		function ( options ) {},


	/**
	 * Sets the rate of playing
	 * @param {Number} Speed Sets new playback speed (-8..8)
	 * 1 - normal; 2 - 2x; 3 - 4x; 4 - 8x; 5 - 16x; 6 - 1/2; 7 - 1/4;
     * 8 - 12x; -1 - reverse; -2 - reverse 2x; -3 - reverse 4x;
     * -4 - reverse 8x; -5 - reverse 16x; -8 - reverse 12x
	 */
	SetSpeed:
		function ( Speed ) {},


	/**
	 * Set stereo mode.
	 * Mono, Mono left and Mono right modes affect only Dual Mono DolbyDigital audio.
	 * @param {Number} mode [0..4] values:
	 *   0 – Stereo mode.
	 *   1 – Mono mode. Left and right channels are mixed and sent to both audio outputs.
	 *   2 – Mono left. Left channel audio are sent to both audio outputs.
	 *   3 – Mono right. Right channel audio are sent to both audio outputs.
	 *   4 – Lt/Rt mode
	 */
	SetStereoMode:
		function ( mode ) {},


	/**
	 * Set the languages of subtitles tracks to be automatically selected when receiving the information on the channel.
	 * Each param is a 3–symbol tags according to ISO 639, for example: "rus" or "eng".
	 * If the information of several audio tracks is present the player selects the track preset by the language priLang.
	 * If such track is not found, the track with the language secLang is selected.
	 * If this one is not found either the first track from the list is selected.
	 * @param {String} priLang primary language
	 * @param {String} secLang secondary language
	 */
	SetSubtitleLangs:
		function ( priLang, secLang ) {},


	/**
	 * Sets the number of track (PID) for subtitles
	 * @param {Number} pid Set the number or PID for the subtitles track to be played in the current content. Is such track is absent subtitles will be disabled.
	 */
	SetSubtitlePID:
		function ( pid ) {},


	/**
	 * Subtitle on/off.
	 * For MAG100 subtitles are displayed in full screen mode.
	 * @param {Boolean} Enable true – subtitles on; false – subtitles off.
	 */
	SetSubtitles:
		function ( Enable ) {},


	SetSubtitlesColor:
		function ( uintVal ) {},


	/**
	 * Set the encoding which will be used to display external subtitles.
	 * @param {String} encoding Encoding for external subtitles. E.i.:
	 * "utf-8", "cp1250", "cp1251", "cp1252", ..., "cp1258", "iso8859-1", ... , "iso8859-16".
	 */
	SetSubtitlesEncoding:
		function ( encoding ) {},


	/**
	 * Set the font for displaying text subtitles.
	 * Platforms: MAG100, MAG200.
	 * @param {String} font URL addressing the font file in the root file system. For example: "/home/default/arial.ttf"
	 */
	SetSubtitlesFont:
		function ( font ) {},


	/**
	 * Set the offset for displaying text subtitles.
	 * Platforms: MAG100, MAG200.
	 * @param {Number} offs Horizontal offset of subtitles.
	 */
	SetSubtitlesOffs:
		function ( offs ) {},


	/**
	 * Set the size of text subtitles – size in pixels.
	 * Platforms: MAG100, MAG200.
	 * @param {Number} size Set the size of text subtitles
	 */
	SetSubtitlesSize:
		function ( size ) {},

	SetSyncCorrection:
		function ( intVal1, intVal2 ) {},

	SetSyncOffsetCorrection:
		function ( intVal ) {},

	SetTeletext:
		function ( boolVal ) {},

	SetTeletextPID:
		function ( uintVal ) {},


	/**
	 * Sets the preset window over others
	 * @param {Number} winNum number of the window for which this function is used: 0 - graphic window; 1 - video window
	 */
	SetTopWin:
		function ( winNum ) {},


	/**
	 * Sets the colour considered transparent at the moment.
	 * The function is a special case of gSTB.SetChromaKey.
	 * Any changes on the screen are visible only provided the ChromaKey mode is switched on by functions gSTB.SetMode or gSTB.SetWinMode.
	 * @param {Number} Color Colour in RGB format that can be considered transparent (0..0xffffff)
	 * @constructor
	 */
	SetTransparentColor:
		function ( Color ) {},


	/**
	 * Set-client to STB
	 * @param {Number} Type Supported RTSP-server type:
	 *   <p>0 – RTSP server based on VLC;</p>
	 *   <p>1 – BitBand RTSP server;</p>
	 *   <p>2 – Kasenna RTSP server;</p>
	 *   <p>3 – ARRIS (C-COR) RTSP server;</p>
	 *   <p>4 – Live555 RTSP server;</p>
	 *   <p>5 – ZTE RTSP server;</p>
	 *   <p>6 – Netup RTSP server.</p>
	 * The server types 3,4,5,6 are supported only for MAG200.
	 * @param {Number} flags Control flags:
	 *   <p>1 – switch on the keep-alive mode;</p>
	 *   <p>2 – determination of the stream end by the field x-notice in the message ANNOUNCE from the server;</p>
	 *   <p>4 – determination of te stream end by the field x-notice in the answer to GET_PARAMETER;</p>
	 *   <p>8 – determination of the stream end after a period of time of the video stream from the server absence;</p>
	 *   <p>16 (0x10) – determination of the stream end by the field according to the field rtptime sent in the RTP heading of the package (Only for the mode of sending video under RTP);</p>
	 *   <p>32 (0x20) – Use UDP transport to send video.</p>
	 */
	SetupRTSP:
		function ( Type, flags ) {},


	/**
	 * Set the mode of sound output through SPdif
	 * @param {Number} flags Output mode through SPdif:
	 *   <p>0 – the sound is supplied only to analogue output;</p>
	 *   <p>1 – sound is supplied to analogue output and through SPdif in tne format 2- channel PCM;</p>
	 *   <p>2 – sound is supplied to SPdif without decoding (AC3 ...), if supported by codec, othjerwise through SPdif in te format of 2-channel PCM.</p>
	 */
	SetupSPdif:
		function ( flags ) {},


	/**
	 * Sets the control mode of Flicker-filter
	 * @param {Number} Mode Control mode of flicker-filter: 0 - API user controls flicker-filter himself; 1 - The player automatically switches on flicker-filter during pauses and stops and switches it off during playing
	 * @see SetFlicker
	 * @see SetDefaultFlicker
	 */
	SetUserFlickerControl:
		function ( Mode ) {},


    /**
     * Sets the video window control mode
     * @param {Number} Mode Control mode:
     * 0 – the device automatically switches on the video window at the beginning of playing and switches it off when stops,
     * 1 – API user uses gSTB.SetVideoState for instructing whether to show the video window or not.
     */
	SetVideoControl:
		function ( Mode ) {},


    /**
     * Switch on or switch off the video window.
     * Valid only if user control had been allowed with gSTB.SetVideoControl.
     * @param {Number} State Allow/prohibit video dicplay: 0 – video window is not dosplayed, 1 – video window is displayed if the stream is present.
     */
	SetVideoState:
		function ( State ) {},


	/**
	 * Set the location and size of the video window.
	 * Parameters xsize and ysize depends on the screen resolution.
	 * @param {Number} xsize Horizontal size of the video window (width).
	 * @param {Number} ysize Vertical size of the video window (height).
	 * @param {Number} x Left upper corner of the video window horizontal offset from the screen edge. Must not exceed the screen width in sum with xsize.
	 * @param {Number} y Left upper cornet of the video window vertical offset from the screen edge. Must not exceed the screen width in sum with ysize.
	 * @constructor
	 */
	SetViewport:
		function ( xsize, ysize, x, y ) {},


	/**
	 * Sets volume level
	 * @param {Number} Volume Volume level: 0 - no sound; 100 - maximal level
	 */
	SetVolume:
		function ( Volume ) {},


	SetWebMode:
		function ( boolVal, strVal ) {},


	/**
	 * Given proxy settings are only applied to http:// or https:// requests of the browser, but not applied to content playback from http server.
	 * For this purpose please use extended gSTB.Play using proxy server.
	 * @param {String} proxy_addr Proxy server address.
	 * @param {Number} proxy_port Proxy server port.
	 * @param {String} user_name Username for proxy server. Can be empty.
	 * @param {String} passwd Password for proxy server. Can be empty.
	 * @param {String} exlude_list Proxy exclude list delimited by spaces.
	 * Access to any entry in the list is performed directly, without proxy. E.i.: 'youtube.com .lenta.ru 192.168.1.1/24 192.168.1.*'
	 */
	SetWebProxy:
		function ( proxy_addr, proxy_port, user_name, passwd, exlude_list ) {},


    /**
     * Set alpha transparency of the preset window.
     * @param {Number} winNum Number of the window for which this function is used: 0 – graphic window; 1 – video window.
     * @param {Number} alpha Transparency of the preset window: 0 – completely transparent; 255 – completely opaque.
     */
	SetWinAlphaLevel:
		function ( winNum, alpha ) {},


    /**
     * Switch on or switch off the ChromaKey mode for the preset window
     * @param {Number} winNum The number of the window for which this function is used: 0 – graphic window; 1 – video window.
     * @param {Number} Mode ChromaKey mode for video window: 0 – off; 1 – on.
     * The parameters set by gSTB.SetChromaKey or gSTB.SetTransparentColor shall be active in the on-mode
     */
	SetWinMode:
		function ( winNum, Mode ) {},


	/**
	 * Show text string as a subtitle on screen.
	 * In case when start and end equal 0, text is shown on screen immediately until next gSTB.ShowSubtitle is called or 30 seconds elapsed.
	 * If this function was called then subtitles will work only via gSTB.ShowSubtitle until next call of gSTB.Play.
	 * @param {Number} start String presentation start time in ms from start of current media.
	 * @param {Number} end String presentation end time in ms from start of current media.
	 * @param {String} text This text will be shown on screen as a subtitle.
	 */
	ShowSubtitle:
		function ( start, end, text ) {},

	ShowVideoImmediately:
		function ( boolVal ) {},

	/**
	 * Shows the virtual keyboard on the screen.
	 * User can switch virtual keyboard from english symbols to symbols of the language that is set as the local language in Service Menu.
     * @param {Boolean} [boolVal] true - the keyboard should be warmed up; false - ignore (default)
	 */
	ShowVirtualKeyboard:
		function ( boolVal ) {},


	/**
	 * Enter or exit StandBy mode.
	 * When entering StandBy mode the following operations take place:
	 * 	 1. All video outputs switch off.
	 * 	 2. Content display, if it was on, stops.
	 * @param bStandby true – enter Standby mode; false – exit from Standby mode.
	 */
	StandBy:
		function ( bStandby ) {},


	/**
	 * Start local configuration menu (Service Menu).
	 * Result of this function is similar to pressing "SET" ("service" on old RC) button, if automatic appearance of Service Menu is disabled via gSTB.EnableServiceButton.
	 */
	StartLocalCfg:
		function () {
			//window.location = getScriptPath() + 'system/settings/index.html';
		},


	/**
	 * Display one next frame of video content.
	 * Platform: MAG100
	 */
	Step:
		function () {},

	/**
	 * Stops playing.
	 * Continue() shall begin playing from the beginning.
	 */
	Stop:
		function () {},


	/**
	 * Receive API version
	 * @return {String} The string in the form of:
	 * "JS API version: <JS_API version>; STB API version: <STB_API version>; Player Engine version: <Player version>"
	 *   <p>JS_API version – this API version number</p>
	 *   <p>STB_API version – player API version</p>
	 *   <p>Player version – version of the player used in API in HEX code form</p>
	 * @example
	 *   JS API version: 301; STB API version: 104; Player Engine version: 0x23
	 */
	Version:
		function () { return ''; },


	/**
	 * Write the file of portal settings /etc/stb_params.
	 * It must be kept in mind that the values PORTAL_IP, PORTAL_1, PORTAL_2 are used in the starting portal stored in /home/web of the root file system,
	 * therefore it is desirable to receive source values of these parameters via gSTB.ReadCFG before making the call and add them to the string cfg.
	 * @param {String} Cfg The data to be stored in the file
	 */
	WriteCFG:
		function ( Cfg ) {},


	/**
	 * Save the string as the browser set up (prefs.js).
	 * This function is not browser specific and it is used to set the right of access to the portal.
	 * This is performed in starting portal saved at /home/web of the root file system and it is recommended to avoid using it anywhere else.
	 * @param {String} prefs Data to be saved in the file of browser settings
	 */
	WritePrefs:
		function ( prefs ) {},


	/**
	 * Get current input language
	 */
	GetInputLang:
		function () {},


	/**
	 * Set new input language
	 * @param {String} langId Language id to be set as input language
	 */
	SetInputLang:
		function ( langId ) {},


	/**
	 * Set the user interface language
	 * @param {String} langId Attention! This function also causes SetInputLang()
	 */
	SetUiLang:
		function ( langId ) {}
};


/**
 * Main object stbWebWindow methods declaration
 * @class stbWebWindow
 */
var stbWebWindow = {

	/**
	 * Get the window id
	 * @return {Number}
	 */
	windowId:
		function () { return 0; },

	/**
	 * Sends a message to the given window
	 * @param {Number} windowId
	 * @param {String} message
	 * @param {String} data
	 */
	messageSend:
		function ( windowId, message, data ) {},

	/**
	 * Sends a message to all windows
	 * @param {String} message
	 * @param {String} data
	 */
	messageBroadcast:
		function ( message, data ) {},

	close:
		function () {
			window.location = PATH_ROOT + 'services.html';
		},

	SendVirtualKeypress:
		function ( text, id ) {},

	SetGeometry:
		function ( x, y, w, h ) {},

	/**
	 * Apply the given mode to the web window
	 * @param {Boolean} fullScreen mode (true - maximize)
	 */
	SetFullScreenMode:
		function ( fullScreen ) {},

	/**
	 * Sets the zoom for the current window
	 * works for all web windows including the service ones
	 * @param {Number} intVal zoom persent: 10...1000
	 */
	SetZoomFactor:
		function ( intVal ) {},

	/**
	 * Switches between modes
	 */
	ToggleFullScreenMode:
		function () {},

	/**
	 * Retrieves the current web window address
	 * @return {String} the current web window address
	 */
	getCurrentUrl:
		function () { return ''; },

	/**
	 * Goes back in the navigation history
	 */
	NavigateBack:
		function () {},

	/**
	 * Goes forth in the navigation history
	 */
	NavigateForward:
		function () {},

	/**
	 * Reloads the current web address in the browser
	 */
	ReloadDocument:
		function () {},

	/**
	 * Stops the current web address loading
	 */
	StopLoading:
		function () {},

	/**
	 * Gives the focus to the top web browser frame
	 */
	FocusTopWindow:
		function () {},

	/**
	 * Gives the focus to the main web browser frame
	 */
	FocusMiddleWindow:
		function () {}
};


/**
 * Main object stbWindowMgr methods declaration
 * @class stbWindowMgr
 */
var stbWindowMgr = {

	/**
	 * Returns the created window numeric id, -1 if window can't be created
	 * can immediately show window and set geometry
	 * @param {String} options json data
	 *     {url:'', x:0, y:0, width:screen.width, heigh:screen.heigh, "transparent":true, "visible":true, "backgroundColor":'#fff', "api":{"init":inherit, "include":["stbTimeShift"], "exclude":["stbRecordManager"]}}
	 *     api.init = all|none|inherit (default:inherit)
	 * @return {Number}
	 */
	windowInit:
		function ( options ) { return 0; },

	/**
	 * Apply window options
	 * @param {Number} windowId
	 * @param {String} options json data
	 * @return {Boolean} operation status
	 */
	windowAttr:
		function ( windowId, options ) { return true; },

	/**
	 * Gets all available window data
	 * @param {Number} windowId
	 * @return {String} json data like "{url:'', state:'', api ...}"
	 */
	windowInfo:
		function ( windowId ) { return ''; },

	/**
	 * Alias for windowAttr(id, '{"visible":true}')
	 * @param {Number} windowId
	 * @return {Boolean} operation status
	 */
	windowShow:
		function ( windowId ) { return true; },

	/**
	 * alias for windowAttr(id, '{"visible":false}')
	 * @param {Number} windowId
	 * @return {Boolean} operation status
	 */
	windowHide:
		function ( windowId ) { return true; },

	/**
	 * Closes the given window
	 * @param {Number} windowId
	 * @return {Boolean} operation status
	 */
	windowClose:
		function ( windowId ) { return true; },

	/**
	 * Reloads or loads a new address
	 * alias for windowAttr(id, '{"url":'some address ...'}')
	 * @param {Number} windowId
	 * @param {String} url
	 * @return {Boolean} operation status
	 */
	windowLoad:
		function ( windowId, url ) { return true; },

	/**
	 * Gets the current active window id
	 * @return {Number}
	 */
	windowActive:
		function () { return 0; },

	/**
	 * All window ids
	 * @return {String} json data like "[1,2,3]"
	 */
	windowList:
		function () { return ''; },

	setBookmarkImplUrl:
		function ( url ) {},

	openDownloadManager:
		function ( url ) {
			window.location = url;
		},

	GetFocusedInputInfo:
		function () {},

	/**
	 * Adds the current page as a bookmark to the bookmark manager
	 */
	addBrowserBookmark:
		function () {},

	closeWebWindow:
		function () {},

	closeWindow:
		function ( handle ) {},

	getCurrWebUrl:
		function () {},

	getCurrentTitle:
		function () {},

	/**
	 * Init web browser window without displaying
	 * @param {String} urlTop full file path to the top frame (default: file:///home/web/public/app/bookmarks/header.html)
	 * @param {String} urlBottom full file path to the bottom frame (default: file:///home/web/public/app/bookmarks/footer.html)
	 */
	InitWebWindow:
		function ( urlTop, urlBottom ) {},


	/**
	 * Set internet browser main window init parameters
	 * @param {Object} options parameters from windowInit function
	 */
	setWebFaceInitAttr:
		function ( options ) {},

	/**
	 * New web browser window initialization
	 * @param url
	 */
	initWebWindow:
		function ( url ) {},

	IsWebVkWindowExist:
		function () {},


	/**
	 * This function indicating that Web Window is exists.
	 * @return {Boolean} true when "wild web window" exist in the window stack
	 */
	IsWebWindowExist:
		function () { return true; },


	/**
	 * In case web browser window initialized loads the given url
	 * @param {String} url web address (if empty string loads file:///home/pages/blank/index.html)
	 */
	LoadUrl:
		function ( url ) {},

	openMediaPlayer:
		function ( strVal1, strVal2 ) {},

	openWebFavorites:
		function ( strVal, boolVal ) {},

	openWebWindow:
		function ( strVal ) {},

	openWebFace:
		function ( url ) {},

	/**
	 * Send the new web browser content window to top
	 */
	raiseWebWindow:
		function () {},

	/**
	 * Send the new web browser control window to top
	 */
	raiseWebFaceWindow:
		function () {},

	resizeWebWindow:
		function ( x, y, w, h ) {},

	/**
	 * Sets the "manual" position VK, which override the behavior GetFocusedInputInfo ()
	 * @param {String} strVal defines the hint for positioning. Valid values: "none", "topleft", "topright", "top", "bottomleft", "bottomright", "bottom"
	 * @param {Number} [intVal1] coordinates X sets topleft VK. If the coordinates are equal to -1 "manual" setting mode is canceled coordinates (GetFocusedInputInfo () will give the coordinates of the active element)
	 * @param {Number} [intVal2] coordinates Y sets topleft VK. If the coordinates are equal to -1 "manual" setting mode is canceled coordinates (GetFocusedInputInfo () will give the coordinates of the active element)
	 */
	SetVirtualKeyboardCoord:
		function ( strVal, intVal1, intVal2 ) {},

	/**
	 * Shows the bookmark manager
	 */
	showBrowserBookmarks:
		function () {},

	/**
	 * Deactivates the current web browser window and displays the main portal window
	 */
	showPortalWindow:
		function () {},

	VkSetFocus:
		function ( boolVal ) {},


	/**
	 * Set VK window init parameters
	 * @param {Object} options parameters from windowInit function
	 */
	setVirtualKeyboardInitAttr:
		function ( options ) {}
};


/**
 * The object provides API to download manager.
 * Download manager allows adding and scheduling download tasks, which will try to download and store remote file into local storage.
 * stbDownloadManager object itself does not require any additional initialization. It is always accessible from JavaScript context.
 * @class stbDownloadManager
 */
var stbDownloadManager = {

	/**
	 * Will add the job for file downloading using URL urlToDownload.
	 * In case of success local file will be stored in filePath. By the time of the operation local file should not exist.
	 * @param {String} urlToDownload URL in form of the string. Remote file will be download using URL.
	 * @param {String} filePath Path to local storage pointing to non-existed file. Dowloaded file will be stored using this path to the local storage.
	 * @return {Boolean} true if job were added
	 */
	AddJob:
		function ( urlToDownload, filePath ) { return true; },

	/**
	 * Similar to «AddJob». Create a special job, that will download given file without saving the result to file storage.
	 * This job is using as connection test facility. You can calculate download speed (once job is finished) using «timeWasted» и «sizeDone» attributes.
	 * You can only create one such connection at the moment. So, you have to delete it each time you want to create next one.
	 * @param {String} urlToDownload URL must point to remote file.
	 * @return {Boolean} result of the operation
	 */
	AddMeasureJob:
		function ( urlToDownload ) { return true; },

	/**
	 * Change priority of given job.
	 * Priority can either be increased or decreased.
	 * @param {Number} id [-1, 0-4294967295] ID of a job assigned for this operation.
	 * @param {Boolean} rise true – increase priority; false – decrease priority
	 */
	AdjustJobPriority:
		function ( id, rise ) {},

	/**
	 * Delete given job.
	 * @param {Number} id [-1, 0-4294967295] ID of a job assigned for this operation.
	 * @param {Boolean} deleteFile true – delete associated local file; false – just delete the job, keep local file
	 * @return {Boolean} result of the operation
	 */
	DeleteJob:
		function ( id, deleteFile ) { return true; },

	/**
	 * Similar to GetQueueInfo.
	 * Get information about special (test) job. If test job does not exist then return nothing.
	 * @return {String} See GetQueueInfo
	 */
	GetMeasureInfo:
		function () { return ""; },

	/**
	 * Get info about queue of jobs.
	 * @param {String} [idList] If list is not empty information for given jobs will be returned. Whole queue will be returned in other case.
	 *   @example "1.0, 2.0, 3.0"
	 * @return {String} Contain JavaScript array of objects. Size of the array depends on operation’s result.
	 * Each object should have these fields:
	 *   id - numeric (0-4294967295) ID of the job
	 *   state - state number (0 – Stopped, 1 – WaitingQueue, 2 – Running, 3 – Completed, 4 – TemporaryError, 5 - PermanentError)
	 *   stateStr - state string (localization is supported for this string resource)
	 *   url - URL of remote file
	 *   filePath - path to local storage
	 *   progressPct - progress of downloading process (expressed in percents: 0-100)
	 *   sizeDone - size of already downloaded data
	 *   sizeTotal - total file size (value -1 if undefined)
	 *   prio - priority of the job
	 *   attempt - number of the download attempt
	 */
	GetQueueInfo:
		function ( idList ) { return '[]'; },

	InvalidateCatalog:
		function ( strVal ) {},

	/**
	 * Will play given job in dedicated «media player» window of the internal portal.
	 * This effectively generate stbEvent.onMediaAvailable(...) event
	 * @param {Number} id [-1, 0-4294967295] ID of a job assigned for this operation.
	 * @constructor
	 */
	PlayDownloadedMedia:
		function ( id ) {},

	RestoreJobs:
		function ( strVal ) {},

	/**
	 * Change state of given job to "waiting for queue".
	 * This will cause job to start downloading process once queue will be ready to schedule the job.
	 * @param {Number} id [-1, 0-4294967295] ID of a job assigned for this operation.
	 * @return {Boolean} result of the operation
	 */
	StartJob:
		function ( id ) { return true; },

	/**
	 * Change state of given job to "stopped".
	 * This state will cause the job will never be selected by scheduler for downloading.
	 * @param {Number} id [-1, 0-4294967295] ID of a job assigned for this operation.
	 * @return {Boolean} result of the operation
	 */
	StopJob:
		function ( id ) { return true; }
};


/**
 *
 * @class stbWildWebWindow
 */
var stbWildWebWindow = {

	GetZoomFactor:
		function(){},

	IsFullScreenMode:
		function(){}

};


/**
 * Main object stbEvent methods declaration.
 * Event model in JavaScript assumes the possibility for API user to receive the events indicating some changes of the player playback state.
 * After the initialization of the player (see appendix 1) call initEvents() function.
 * The code of the last event is also stored in the stbEvent.event.
 * @class stbEvent
 */
var stbEvent = {

	/**
	 * Receive a message from a window
	 * @param {Number} windowId
	 * @param {String} message
	 * @param {String} data
	 */
	onMessage:
		function ( windowId, message, data ) {},

	/**
	 * Receive a broadcast message from a window
	 *		storage.mount
	 *		storage.unmount
	 * @param {Number} windowId
	 * @param {String} message
	 * @param {String} data
	 */
	onBroadcastMessage:
		function ( windowId, message, data ) {},

	/**
	 * The function to be called when getting the player event.
	 * It is used for processing the events in the portal with the event code as the parameter.
	 */
	onEvent:
		function () {},

	/**
	 * The code of the last event.
	 * The following events are defined:
	 *   1 - The player reached the end of the media content or detected a discontinuity of the stream.
	 *   2 - Information on audio and video tracks of the media content is received.
	 *   4 - Video and/or audio playback has begun.
	 *   5 - Error when opening the content: content not found on the server or connection with the server was rejected.
	 *   6 - Detected DualMono AC-3 sound.
	 *   7 - Detected information about video content.
	 *   8 - Error occurred while loading external subtitles.
	 *   0x20 - HDMI connected.
	 *   0x21 - HDMI disconnected.
	 *   0x22 - Recording task has been finished successfully. See Appendix 13. JavaScript API for PVR subsystem.
	 *   0x23 - Recording task has been finished with error. See Appendix 13. JavaScript API for PVR subsystem.
	 *   0x81 - When playing RTP-stream the numbering of RTP-packets was broken.
	 * @type {Number}
	 */
	event: 0,

	/**
	 * USB device mount/unmount
	 * @param {Number} state 0 - mount off, 1 - mount on
	 */
	onMount:
		function ( state ) {},

	/**
	 * Callback on current web document loading
	 * triggers every time the document loading progress changes
	 * @param {Number} progress loading stage value (0-100)
	 */
	onWebBrowserProgress:
		function ( progress ) {},

	/**
	 * Callback on browser web window activation
	 */
	onWindowActivated:
		function () {},

	/**
	 * Callback on internet browser link clicked to ask user what to do with link: play or download
	 * It is also used to start playing a downloaded item
	 */
	onMediaAvailable:
		function () {},

	/**
	 * !!! deprecated
	 */
	onScreenSaverOverride:
		function () {},

	/**
	 * Callback on screensaver activation/deactivation
	 * @param {Boolean} state true - activation, false - deactivation
	 */
	onScreenSaverActivation:
		function ( state ) {},

	/**
	 * Callback fired on lost/restore local network connection
	 * @param {Boolean} status new network state
	 */
	onNetworkStateChange:
		function ( status ) {},

	/**
	 * Callback fired on lost/restore internet connection
	 * @param {Boolean} status new network state
	 */
	onInternetStateChange:
		function ( status ) {}
};


/**
 * The object provides an interface to the update manager (handles software updates subsystem operations).
 * Update Manager allows you to initiate and display the status of the software upgrade process.
 * Before any software update operation you must stop every single process of media content accessing and displaying.
 * Update manager is a finite state machine. State is accessible via getStatus method. Initial state – «Idle» (value «21»).
 * Any active operation upon the update system is allowed only in «Idle» state.
 * Right after starting of an operation state machine leaves «Idle» state and must be considered as busy until «Idle» state turned back.
 * So, after every start of operation that been committed user should wait for «Idle» state back.
 * @class stbUpdate
 */
var stbUpdate = {

	/**
	 * Returns total number of flash banks.
	 * @return {Number} there is always one bank exist.
	 */
	GetFlashBankCount:
		function () { return 1; },


	eventProgress:
		function () {},

	eventStateChanged:
		function () {},


	/**
	 * Returns memory bank number, which was used for current software loading.
	 * @return {Number} 0 – first memory bank; 1 – second memory bank; -1 – memory bank is undefined (it could be possible if device was booted from network storage. For examples, NFS)
	 */
	getActiveBank:
		function () { return 0; },


	/**
	 * Returns date of then Image creation.
	 * @return {String} Returns date of the Image, which was assigned upon image creation procedure
	 */
	getImageDateStr:
		function () { return ""; },


	/**
	 * Returns description of the Image.
	 * @return {String} Returns description of the Image, which was assigned upon image creation procedure.
	 */
	getImageDescStr:
		function () { return ""; },


	/**
	 * Returns version of the Image.
	 * @return {String} version of the Image, which was assigned upon image creation procedure.
	 */
	getImageVersionStr:
		function () { return ""; },


	/**
	 * Returns progress indicator value expressed in percents.
	 * @return {Number} [0..100] value in percents.
	 */
	getPercents:
		function () { return 0; },


	/**
	 * Returns status of update subsystem as code.
	 * @return {Number} status code:
	 *   -1: not defined
	 *   1: Signature init error (final state error)
	 *   2: Wrong device model
	 *   3: Section size exceeds partition size on FLASH
	 *   4: Required FLASH section not found. Aborting update
	 *   5: Updating kernel
	 *   6: Updating image
	 *   7: Internal error (final state error)
	 *   8: Inspecting firmware
	 *   9: Updating environment variables
	 *   10: Updating Bootstrap section
	 *   11: Skipping Bootstrap section
	 *   12: Updating User FS section
	 *   13: Skipping User FS section
	 *   14: Updating second boot
	 *   15: Updating logotype
	 *   16: Update finished OK (final state OK)
	 *   17: Wrong signature (final state OK)
	 *   18: Erasing flash section
	 *   19: Flash write error (final state error)
	 *   20: File write error (final state error)
	 *   21: Idle (final state OK)
	 *   22: Invalid file header (final state error)
	 *   23: Inspecting update file
	 *   23: File check finished
	 *   24: File check finished (final state OK)
	 *   25: File not found (final state error)
	 *   26: Initialising
	 *   27: Read error (final state error)
	 */
	getStatus:
		function () { return 0; },


	/**
	 * Returns status of update subsystem as string.
	 * @return {String} Localized string describing current operation status.
	 * Localization done according to the settings of internal configuration portal.
	 */
	getStatusStr:
		function () { return ""; },


	/**
	 * Initiating automatic software update procedure from given update file.
	 * Memory bank will be selected automatically. During update procedure there is dedicated user interface form will be displayed.
	 * @param {String} image software update procedure will be started using given update file
	 * Can be one of:
	 *   - URL pointing to update file using HTTP scheme (for example, http://test.com/imageupdate)
	 *   - file path to update file (for example, /media/usbdisk/mag200/imageupdate)
	 * @param {Boolean} checkVersion true – update procedure will be committed only if current version of software older than available version; false – do not do version check
	 */
	startAutoUpdate:
		function ( image, checkVersion ) {},


	/**
	 * Initiate update file check operation.
	 * Operation should be started only from "Idle" state.
	 * @param {String} image file will be verified and available data will be read
	 * Can be one of:
	 *   - URL pointing to update file using HTTP scheme (for example, http://test.com/imageupdate)
	 *   - file path to update file (for example, /media/usbdisk/mag200/imageupdate)
	 */
	startCheck:
		function ( image ) {},


	/**
	 * Initiating software update procedure from given update file to given memory bank.
	 * Operation should be started only from "Idle" state.
	 * @param {Number} bank 0 – update using the first memory bank; 1 - update using the second memory bank
	 * @param {String} image software update procedure will be started using given update file
	 * Can be one of:
	 *   - URL pointing to update file using HTTP scheme (for example, http://test.com/imageupdate)
	 *   - file path to update file (for example, /media/usbdisk/mag200/imageupdate)
	 */
	startUpdate:
		function ( bank, image ) {}

};


/**
 * This object provides API for channel recording manager (PVR).
 * Recording manager allows to schedule a task, which will record specified channel(stream) into local storage during the specified time range.
 * pvrManager object does not need any additional initialization. It is always accessible from JavaScript context.
 * It is allowed to record only channels that contain mpeg-ts stream. It can be multicast stream or stream from http server.
 * Error codes table:
 *   0 Operation successful.
 *   -1 Bad argument.
 *   -2 Not enough memory.
 *   -3 Wrong recording range (start or end time). e.i. recording duration must be less or equal than 24 hours.
 *   -4 Task with specified ID was not found.
 *   -5 Wrong file name. Folder where you want to save recording must exist and begin with /media/USB- or /ram/media/USB-.
 *   -6 Duplicate tasks. Recording with that file name already exists.
 *   -7 Error opening stream URL.
 *   -8 Error opening output file.
 *   -9 Maximum number of simultaneous recording is exceeded. It does not mean task number but number of simultaneous recording. See also SetMaxRecordingCnt.
 *   -10 Manager got end of stream and recording has finished earlier keeping the recorded file.
 *   -11 Error writing output file. E.i. disk is full or has been disconnected during recording.
 *
 * Task state table:
 *   1 Waiting for a start of actual recording.
 *   2 Recording.
 *   3 Error occurred. Recording is stopped.
 *   4 Recording completed.
 *
 * @class pvrManager
 */
var pvrManager = {

	/**
	 * Change recording end time.
	 * @param {String} id Task identifier.
	 * @param {String} endTime New recording end time (see CreateTask).
	 * @return {Number} See Error codes table.
	 */
	ChangeEndTime:
		function ( id, endTime ) { return 0; },

	/**
	 * Schedule channel recording task.
	 * @param {String} url URL of the stream, that will be recorded (http://..., rtp://..., udp://...).
	 * @param {String} fileName Full file name of recording (/media/USB-... or /ram/media/USB-...).
	 * @param {String} startTime Recording start time. UTC time in "YYYYMMDDThhmmss" format or number of seconds since Epoch (1970/01/01 UTC).
	 * @param {String} endTime Recording end time. UTC time in "YYYYMMDDThhmmss" format or number of seconds since Epoch (1970/01/01 UTC).
	 * @return {String} Unique task identifier or error code.
	 * Unique task identifier if operation was successful, otherwise return value is a string representing error code (<0) from Error codes table
	 * Number of seconds since Epoch can be obtained via Date object: var date = new Date(); var startTime = date.getTime()/1000;
	 */
	CreateTask:
		function ( url, fileName, startTime, endTime ) { return ""; },

	/**
	 * Get the list of all tasks.
	 * @return {String} List of all recording tasks in form of JSON array.
	 * [task_1,...,task_n]
	 * where task_n has the following form:
	 *   {"id":1,"state":0,"errorCode":0,"filename":"/media/USB-1/1.ts", "url":"http://192.168.1.1/mpegts", "startTime":"3452344145","endTime":"3452345345"}
	 *   where:
	 *     id – unique task identifier.
	 *     state – current task state. See Task state table.
	 *     errorCode – error code. See Error codes table.
	 *     fileName – requested recording file name.
	 *     url – URL of recorded stream.
	 *     startTime and endTime – start and end recording time.
	 *     See CreateTask.
	 */
	GetAllTasks:
		function () { return '[]'; },

	/**
	 * Get task list by identifier list.
	 * @param {String} idList List of task identifiers in form of JSON array (string in form: [id_1, ..., id_n]).
	 * @return {String} List of all matched recording tasks in form of JSON array.
	 * [task_1,...,task_n]
	 * where task_n has the following form:
	 *   {"id":1,"state":0,"errorCode":0,"filename":"/media/USB-1/1.ts", "url":"http://192.168.1.1/mpegts", "startTime":"3452344145","endTime":"3452345345"}
	 *   where:
	 *     id – unique task identifier.
	 *     state – current task state. See Task state table.
	 *     errorCode – error code. See Error codes table.
	 *     fileName – requested recording file name.
	 *     url – URL of recorded stream.
	 *     startTime and endTime – start and end recording time.
	 *     See CreateTask.
	 */
	GetTasksByIDs:
		function ( idList ) { return ""; },

	/**
	 * Get recording task by its identifier.
	 * @param {String} id Task identifier.
	 * @return {String} Return value in the following form:
	 *   {"id":1,"state":0,"errorCode":0, "filename":"/media/USB-1/1.ts", "url":"http://192.168.1.1/mpegts", "startTime":"3452344145", "endTime":"3452345345"}
	 *   For more details see GetAllTasks. Return value will be the string '{}' if no task found.
	 */
	GetTaskByID:
		function ( id ) { return ""; },

	/**
	 * Remove recording task by its identifier.
	 * @param {String} id Task identifier.
	 * @param {Number} removeType types:
	 *   0 – do not remove any files.
	 *   1 – if temporary file exists, rename it into resulting file.
	 *   2 – remove only temporary file, if it exists.
	 *   3 – remove both temporary and resulting files.
	 */
	RemoveTask:
		function ( id, removeType ) {},

	/**
	 * Set maximum number of simultaneous recording.
	 * @param {Number} maxCnt Maximum number of simultaneous recording.
	 */
	SetMaxRecordingCnt:
		function ( maxCnt ) {}

};


/**
 * Main time shift object
 * @class timeShift
 */
var timeShift = {
	SetTimeShiftFolder:
		function ( folderPath ) {},

	SetMaxDuration:
		function ( duration ) {},

	EnterTimeShift:
		function () {},

	ExitTimeShift:
		function () {},

	ExitTimeShiftAndSave:
		function ( name ) {},

	ExitTimeShiftAndSaveDuration:
		function ( name, duration ) {},

	SetSlidingMode:
		function ( OnOff ) {}
};


/**
 * Global key-value storage
 * @class stbStorage
 */
var stbStorage = {

	/**
	 * Remove all the keys
	 */
	clear:
		function () {},

	/**
	 * Get the total number of keys
	 * @returns {Number}
	 */
	count:
		function () { return 0; },

	/**
	 * Get the given key value by its name
	 * @param {String} keyName
	 * @return {string} key value
	 */
	get:
		function ( keyName ) { return ''; },

	/**
	 * Check the given key existence
	 * @param {String} keyName
	 * @return {Boolean} true if present
	 */
	has:
		function ( keyName ) { return true; },

	/**
	 * Get the list of all keys as a JSON string in this format:
	 * { "errCode" : 0, "errMsg" : "", "result" : ["key1", "key2"] }
	 * @return {String}
	 */
	keys:
		function () { return ''; },

	/**
	 * Create or update the given key
	 * @param {String} keyName
	 * @param {String|Number} keyValue
	 */
	set:
		function ( keyName, keyValue ) {},

	/**
	 * Remove the given key
	 * @param {String} keyName
	 * @return {Boolean} operation status - true on successful deletion
	 */
	unset:
		function ( keyName ) { return true; }

};


if ( DEBUG_NAME && DEBUG_SERVER ) {
	['gSTB', 'stbWebWindow', 'stbWindowMgr', 'stbDownloadManager', 'stbWildWebWindow', 'stbUpdate', 'pvrManager', 'timeShift', 'stbStorage'].forEach(function(name){
		var obj = window[name];
		for ( var method in obj ) {
			if ( obj.hasOwnProperty(method) && typeof obj[method] === 'function' ) {
				obj[method] = function(name, method){
					return function(){
						//console.log(method);
						return proxy.call(name + '.' + method, Array.prototype.slice.call(arguments));
					}
				}(name, method);
			}
		}
	});
}
