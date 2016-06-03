/**
 * MAGSDK basic implementation of downloads plugin.
 *
 * @author <bas.jsdev@gmail.com>
 */

'use strict';

/**
 * Base Events Emitter implementation.
 */
function Emitter () { this.events = {}; }

Emitter.prototype = {
    addListener: function ( name, callback ) {
        this.events[name] = this.events[name] || [];
        this.events[name].push(callback);
    },
    once: function ( name, callback ) {
        var self = this;

        this.events[name] = this.events[name] || [];
        this.events[name].push(function onceWrapper () {
            callback.apply(this, arguments);
            self.removeListener(name, onceWrapper);
        });
    },
    addListeners: function ( callbacks ) {
        var name;

        if ( typeof callbacks === 'object' ) {
            for ( name in callbacks ) { if ( callbacks.hasOwnProperty(name) ) { this.addListener(name, callbacks[name]); } }
        }
    },
    removeListener: function ( name, callback ) {
        if ( this.events[name] ) {
            this.events[name] = this.events[name].filter(function callbacksFilter ( fn ) { return fn !== callback; });
            if ( this.events[name].length === 0 ) { this.events[name] = undefined; }
        }
    },
    removeAllListeners: function ( name ) {
        if ( arguments.length === 0 ) { this.events = {}; } else if ( name ) { this.events[name] = undefined; }
    },
    emit: function ( name, data, callback ) {
        var event = this.events[name],
            i;

        if ( event ) {
            for ( i = 0; i < event.length; i++ ) { event[i].apply(this, Array.prototype.slice.call(arguments, 1)); }
        }
    }
};

// correct constructor name
Emitter.prototype.constructor = Emitter;

var dm = new Emitter(),
    downloads = [],
    hash = {},
    updateTime = 2000,
    timerId;


window.dm = dm;
window.dmhash = hash;


dm.errorCodes = {
    '-1': 'Wrong url.',
    '-2': 'Wrong fileName.',
    '-3': 'File already exist.',
    '-4': 'Cant add this task.',
    '-5': 'Wrong download object.',
    '-6': 'Wrong mount path.'
};

// example
// dm.add({url: 'http://fs194.www.ex.ua/load/92973019cf192ecd9b76edff7818a635/231637117/2343cccf16b7ac415ce82ff5289ed3ea.mp4',
// name: '/media/USB-94F9AM9X43RO31TW-1/1.mp4'}, function ( error, data ) {console.log(error); console.log(data);});
dm.add = function ( data, callback ) {
    if ( typeof callback !== 'function' ) {
        console.log('Wrong callback function.');
        return;
    }
    if ( !data.url ) {
        callback({code: '-1', message: dm.errorCodes['-1']});
        return;
    }
    if ( !data.name ) {
        callback({code: '-2', message: dm.errorCodes['-2']});
        return;
    }
    if ( gSTB.IsFileExist(data.name) ) {
        callback({code: '-3', message: dm.errorCodes['-3']});
        return;
    }
    if ( !stbDownloadManager.AddJob(data.url, data.name) ) {
        callback({code: '-4', message: dm.errorCodes['-4']});
        return;
    }
    callback(null, true);
};
// hide the source code of the function
dm.add.toString = function () { return 'function add() { [native code] }';};


dm.remove = function ( item, deleteFile, callback ) {
    if ( typeof callback !== 'function' ) {
        console.log('Wrong callback function.');
        return;
    }
    if ( !item || !(item instanceof Emitter) ) {
        callback({code: '-5', message: dm.errorCodes['-5']});
        return;
    }
    stbDownloadManager.DeleteJob(item.data.id, !!deleteFile);
    delete hash[item.data.id];
    downloads.splice(downloads.indexOf(item), 1);

    callback(null, true);

    if ( dm.events['remove'] ) {
        dm.emit('remove', {item: item, time: (new Date).getTime()});
    }
};
// hide the source code of the function
dm.remove.toString = function () { return 'function remove() { [native code] }';};


dm.restore = function ( path, callback ) {
    if ( typeof callback !== 'function' ) {
        console.log('Wrong callback function.');
        return;
    }
    if ( !path ) {
        callback({code: '-6', message: dm.errorCodes['-6']});
        return;
    }
    stbDownloadManager.RestoreJobs(path);

    callback(null, true);

    if ( dm.events['restore'] ) {
        dm.emit('restore', {time: (new Date).getTime()});
    }
};
// hide the source code of the function
dm.restore.toString = function () { return 'function restore() { [native code] }';};


Object.defineProperties(dm, {
    limit: {
        get: function () { return 1; },
        set: function () {}
    },
    list: {
        get: function () { return downloads; },
        set: function () {}
    },
    updateInterval: {
        get: function () { return updateTime; },
        set: function ( newTime ) {
            updateTime = newTime;
            clearInterval(timerId);
            timerId = setInterval(update, updateTime);
        }
    }
});

// check info and trigger events if something happened
function update () {
    var rawData, i, item, progress;

    try {
        rawData = JSON.parse(stbDownloadManager.GetQueueInfo());
    } catch ( error ) {
        rawData = []
    }

    for ( i = 0; i < rawData.length; i++ ) {
        // if new item, trigger event
        if ( !hash[rawData[i].id] ) {
            item = new Emitter();
            item.data = {
                name: rawData[i].filePath,
                id: rawData[i].id,
                path: rawData[i].mountPoint,
                priority: rawData[i].prio,
                progress: Math.ceil(rawData[i].progressPct >= 0 ? rawData[i].progressPct : 0),
                size: rawData[i].sizeTotal,
                state: Number(rawData[i].state),
                url: rawData[i].url,
                _progressPct: rawData[i].progressPct,
                _attempt: rawData[i].attempt,
                _prioLevel: rawData[i].prioLevel,
                _sizeDone: rawData[i].sizeDone,
                _stateStr: rawData[i].stateStr,
                _tempFile: rawData[i].tempFile,
                _timeWasted: rawData[i].timeWasted
            };
            item.pause = function () { stbDownloadManager.StopJob(this.data.id); };
            item.pause.toString = function () { return 'function pause() { [native code] }';};
            item.resume = function () { stbDownloadManager.StartJob(this.data.id); };
            item.resume.toString = function () { return 'function resume() { [native code] }';};
            hash[item.data.id] = item;
            downloads.push(item);
            if ( dm.events['add'] ) {
                dm.emit('add', {item: item, time: (new Date).getTime()});
            }
            console.log('add->id:' + item.data.id);
        } else {
            item = hash[rawData[i].id];
            // check if progress changed (every running task)
            if ( Number(rawData[i].state) === 2 ) {
                progress = Math.ceil(rawData[i].progressPct);
                if ( progress !== item.data.progress ) {
                    item.data.progress = progress;
                    downloads[downloads.indexOf(item)].data.progress = progress;
                    if ( dm.events['progress'] ) {
                        dm.emit('progress', {item: item, time: (new Date).getTime()});
                    }
                }
            }
            // check if state changed
            if ( Number(rawData[i].state) !== item.data.state ) {
                item.data.state = Number(rawData[i].state);
                downloads[downloads.indexOf(item)].data.state = item.data.state;
                if ( dm.events['state'] ) {
                    dm.emit('state', {item: item, time: (new Date).getTime()});
                }
            }
        }
    }
}

// first launch
update();
// check info and trigger events if something happened
timerId = setInterval(update, updateTime);

module.exports = function () {
    return dm;
};
