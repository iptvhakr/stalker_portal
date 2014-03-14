/**
 * `byID` is function for return element object
 *
 * @function
 * @name byID
 * @param {function(id):*}
 * @example
 * byID('frame');
 * @return [object]
 */
function byID(id) {return document.getElementById(id);}

/**
 * `random` is function for return random value between 0 and @value
 *
 * @function
 * @name random
 * @param {function(max):*}
 * @example
 * random(9999);
 * @return int
 */
function random(max) {return Math.floor(Math.random() * (parseInt(max) + 1));}

/**
 * `log` is function for write log
 *
 * @function
 * @name log
 * @param {log(line):*}
 * @example
 * log('request from YouTube API : from 1 to 24, total items 97');
 * @return void
 */
function log(line) {
    if(debug && !emulate) {
        gSTB.Debug(line);   // print to consol (ssh)
    }
}

/**
 * `string_replace` is function for replace parts of string
 *
 * @function
 * @name string_replace
 * @param {string_replace(search, replace, subject, count):*}
 * @example
 * string_replace('&amp;', '&', 'string &amp; me');
 * @return string
 */
function string_replace (search, replace, subject, count) {
    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
            f = [].concat(search),
            r = [].concat(replace),
            s = subject,
            ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;}
        }
    }
    return sa ? s : s[0];
}

/**
 * `print_r` is function for create string from object|array
 *
 * @function
 * @name print_r
 * @param {print_r(object|array):*}
 * @example
 * print_r(eval('{"main":{"one":1,"0":"zero"}}'));
 * @return string
 */
function print_r(arr, level) {
    var print_red_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0; j<level+1; j++) level_padding += "    ";
    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
            if(typeof(value) == 'object') {
                print_red_text += level_padding + "'" + item + "' :\n";
                print_red_text += print_r(value,level+1);
        }
            else
                print_red_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        }
    }

    else  print_red_text = "===>"+arr+"<===("+typeof(arr)+")";
    return print_red_text;
}

/**
 * `is_numeric` is function checking on int value
 *
 * @function
 * @name is_numeric
 * @param {is_numeric(value):*}
 * @example
 * is_numeric('999');
 * @return bool
 */
function is_numeric(mixed_var) {
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}

/**
 * `empty` is function checking on exits value
 *
 * @function
 * @name empty
 * @param {empty(value):*}
 * @example
 * empty('');
 * @return bool
 */
function empty (mixed_var) {
    if (mixed_var === "" ||
        mixed_var === 0 ||
        mixed_var === "0" ||
        mixed_var === null ||
        mixed_var === false ||
        typeof mixed_var === 'undefined' ||
        typeof mixed_var === 'NaN'
    ) {
        return true;
    }
    if (typeof mixed_var == 'object') {
        for (var key in mixed_var) {
            return false;
        }
        return true;
    }
    return false;
}

/**
 * `separate` is function checking on exits value
 *
 * @function
 * @name separate
 * @param {separate(value):*}
 * @example
 * separate(95466725);
 * @return string
 */
function separate(number) {
    number = parseInt(number);
    var n_s = number.toString();
    if(number<100000) {
        return number;
    } else {
        if (number < 999999) {
            return n_s.substr(0, n_s.length - 3) + lang.count.thousands;
        } else {
            return n_s.substr(0, n_s.length - 6) + lang.count.millions;
        }
    }
}

/**
 * `isset` is function checking on exits var
 *
 * @function
 * @name isset
 * @param {isset(object):*}
 * @example
 * isset(s);
 * @return bool
 */
function isset () {
    var a = arguments, l = a.length, i = 0, undef;
    if (l === 0) {
        throw new Error('Empty isset');
    }
    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}
/**
 * `getXmlHttp` is function for create correctly XMLHttpRequest object for ajax
 *
 * @function
 * @name getXmlHttp
 * @param {function():*}
 * @example
 * getXmlHttp();
 * @return XMLHttpRequest
 */
function getXmlHttp() {
  if (typeof XMLHttpRequest === 'undefined') {
    XMLHttpRequest = function() {
      try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
        catch(e) {}
      try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
        catch(e) {}
      try { return new ActiveXObject("Msxml2.XMLHTTP"); }
        catch(e) {}
      try { return new ActiveXObject("Microsoft.XMLHTTP"); }
        catch(e) {}
      throw new Error("This browser does not support XMLHttpRequest.");
    };
  }
  return new XMLHttpRequest();
}
/**
 * `getHtmlByUrl` is function for get html construct from @url
 *
 * @function
 * @name getHtmlByUrl
 * @param {function(url):*}
 * @example
 * getXmlHttp('http://www.youtube.com/watch?v=7F9xRVTdOOo');
 * @return void
 */
function getHtmlByUrl(url) {
    try {
        stb.EnableSetCookieFrom(".youtube.com", false); // disabled cookie receiving from domain '.youtube.com'
        var request = getXmlHttp();

        request.open('GET', url, true);
        request.setRequestHeader("Content-Type", "text/xml");
        request.setRequestHeader("charset", "utf-8");
        if (request.overrideMimeType) {
            request.overrideMimeType('text/html');
        }
        request.onreadystatechange = function ()
        {
            if (request.readyState == 4 && request.status == 200) {
                log("Url " + url + " get done");
                parseYoutubePage_new(request.responseText);     // call to function parse YouTube html
                //parseVideoInfo(request.responseText);
                setTimeout(function(){stb.EnableSetCookieFrom(".youtube.com", true);}, 500);//
                // enabled cookie receiving from domain '.youtube.com'
            }
        };
        request.send(null); // send object
    } catch (e) {
        log("catch (e): \"" + e + "\"");
        return;
    }
}
/**
 * `setEnvironmentValue` is function for set var, value to devise
 *
 * @function
 * @name setEnvironmentValue
 * @param {function():*}
 * @example
 * setEnvironmentValue();
 * @return void
 */
function setEnvironmentValue(name, value){
    var strout = 'Saving ' + name + ' = ';
    strout += value;
    stb.RDir('setenv ' + name + ' ' + value);
    log(strout);
}
/**
 * `getEnvironmentValue` is function for get var value from devise
 *
 * @function
 * @name getEnvironmentValue
 * @param {function(name):*}
 * @example
 * getEnvironmentValue('quality');
 * @return string
 */
function getEnvironmentValue(name){
    log('readed value: ' + name);
    return stb.RDir('getenv ' + name);
}
/**
 * `parseYoutubePage` is function for parse YouTube content page
 * and optional call player action
 *
 * @function
 * @name parseYoutubePage
 * @param {function(html, playNow):*}
 * @example
 * parseYoutubePage('...html...', true);
 * @return void
 */
function parseYoutubePage(html, playNow) {
    var s = /amp\;url_encoded_fmt_stream_map=(.*?)amp\;/.exec(html);
    log('\n\n'+s.length+'\n\n');
    log('\n\n'+s[1]+'\n\n');
    var str = '({';
    var r = s[1].split('%2C');
    for(var i=0;i<r.length;i++){
        //r[i] = r[i].replace('url%3D', '');
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        r[i] = unescape(r[i]);
        r[i] = unescape(r[i]);
        r[i] = unescape(r[i]);
		var sig = /sig=(([^&])*)/igm.exec(r[i])[1];
		r[i]=r[i].replace(/&sig=(([^&])*)/igm,"")
		var params = r[i].split("&");
		var json_object = {};
		var new_params;
		for(var y = 0;y<params.length;y++){
			//console.log("\n"+params[y]);
			new_params = params[y].split("=");
			new_params[1] = new_params[1].replace(/;.*/ig,"");
			json_object[new_params[0]] = new_params[1];
			//console.log("\n"+new_params[0]+" : "+new_params[1]);
			//log("json_object["+new_params[0]+"]="+json_object[new_params[0]]);
		}
        var m = /itag\=(\d{1,})/.exec(r[i]);
        r[i] = r[i].substr(r[i].indexOf('http://'));
		
        var splited = r[i].split(';');
        var link = splited[0];
		console.log(link);
        str+=m[1]+':\''+link+'&signature='+sig+'\',';
    }
    str =  str.substr(0, str.length - 1) + '})';
    //console.log(str);
    if(!playNow || playNow == true) {
        player.play(eval(str));  // call player
    }
}

function parseYoutubePage_new(html, playNow) {
    var s = /"url_encoded_fmt_stream_map": "(.*?)"/.exec(html);

    if (!s || Array.isArray(s) && !s[1]){
        log('\n\ns is null\n\n');
        player.stop();
        loading.hide();
        toast.show(!s ? lang.video_not_available : lang.video_not_available_on_device);
        return;
    }

    log('\n\n'+s.length+'\n\n');
    log('\n\n'+s[1]+'\n\n');
    var str = '({';
    var r = s[1].split(',');
    for(var i=0;i<r.length;i++){
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        r[i] = unescape(r[i]);
        r[i] = unescape(r[i]);
        r[i] = unescape(r[i]);

        try{

            var url = /url=([^\\]*)/igm.exec(r[i])[1];
            log('\n\n'+url+'\n\n');

            if (!url){
                throw new Error("Empty url");
            }

        }catch(e){
            player.stop();
            loading.hide();

            if (player.obj.restricted && player.obj.restricted == 'limitedSyndication'){
                toast.show(lang.video_not_available_on_device);
            }else{
                toast.show(lang.video_not_available);
            }

            return;
        }

        var m = /itag\=(\d{1,})/.exec(url);
        str+=m[1]+':\''+url;

        str+='\',';
    }
    str =  str.substr(0, str.length - 1) + '})';
    if(!playNow || playNow == true) {
        player.play(eval(str));  // call player
    }
}

function parseVideoInfo(html, playNow){
    var s = /url_encoded_fmt_stream_map=(.*)/.exec(html);
    log('\n\n'+s.length+'\n\n');
    log('\n\n'+s[1]+'\n\n');
    var str = '({';
    var r = s[1].split('%2C');
    for(var i=0;i<r.length;i++){
        r[i] = r[i].replace('url%3D', '');
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        r[i] = decodeURIComponent(r[i]);
        var m = /itag\=(\d{1,})/.exec(r[i]);
        if (!m){
            continue;
        }
        log('\n\nm='+m+'\n\n');
        str+=m[1]+':\''+r[i].split(';')[0]+'\',';
    }
    str =  str.substr(0, str.length - 1) + '})';
    if(!playNow || playNow == true) {
        player.play(eval(str));  // call player
    }
}

function trimLeft(str) {
  return str.replace(/^\s+/, '');
}

function trimRight(str) {
  return str.replace(/\s+$/, '');
}

function trim(str) {
  return trimRight(trimLeft(str));
}

function trimSpaces(str) {
  return str.replace(/\s{2,}/g, ' ');
}
function get_params(){
    var get = new String(window.location);
    var x = get.indexOf('?');
    if (x!=-1){
        var l = get.length;
        get = get.substr(x+1, l-x);
        l = get.split('&');
        x = 0;
        for(i in l){
            if (l.hasOwnProperty(i)){
                get = l[i].split('=');
                _GET[get[0]] = get[1];
                x++;
            }
        }
    }
} 