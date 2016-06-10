/**
 * MAGSDK basic implementation of pvr plugin.
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


var pvr = new Emitter(),
    limit = 5,
    records = [],
    hash = {},
    timerId,
    updateTime = 2000;


pvr.errorCodes = {
    '-1': 'Bad argument.',
    '-2': 'Not enough memory.',
    '-3': 'Wrong recording range (start or end time). e.i. recording duration must be less or equal than 24 hours.',
    '-4': 'Task with specified ID was not found.',
    '-5': 'Wrong file name. Folder where you want to save recording must exist and begin with /media/USB- or /ram/media/USB-.',
    '-6': 'Duplicate tasks. Recording with that file name already exists.',
    '-7': 'Error opening stream URL.',
    '-8': 'Error opening output file.',
    '-9': 'Maximum number of simultaneous recording is exceeded. It does not mean task number but number of simultaneous recording. See also SetMaxRecordingCnt.',
    '-10': 'Manager got end of stream and recording has finished earlier keeping the recorded file.',
    '-11': 'Error writing output file. E.i. disk is full or has been disconnected during recording.',
    '-12': 'Wrong url.',
    '-13': 'Wrong fileName.',
    '-14': 'Wrong startTime.',
    '-15': 'Wrong endTime.',
    '-16': 'Wrong download object.'
};

// should be inited before pvr use
gSTB.InitPlayer();


// example
//pvr.add({
//        name: '/media/USB-94F9AM9X43RO31TW-1/records/EurosportLive/2016-03-16/00-00-01.ts',
//        channel: 'rtp://239.1.1.1:1234',
//        startTime: Math.ceil((new Date()).getTime() / 1000 + 10),
//        endTime: Math.ceil((new Date()).getTime() / 1000 + 500)
//    }, function ( error, data ) {console.log(error);console.log(data);});
/**
 *
 * Error codes table:
 *   0 Operation successful.
 * Task state table:
 *   1 Waiting for a start of actual recording.
 *   2 Recording.
 *   3 Error occurred. Recording is stopped.
 *   4 Recording completed.
 */
pvr.add = function ( data, callback ) {
    var state;

    if ( typeof callback !== 'function' ) {
        console.log('Wrong callback function.');
        return;
    }
    if ( !data.channel ) {
        callback({code: '-12', message: pvr.errorCodes['-12']});
        return;
    }
    if ( !data.name ) {
        callback({code: '-13', message: pvr.errorCodes['-13']});
        return;
    }
    if ( !data.startTime ) {
        callback({code: '-14', message: pvr.errorCodes['-14']});
        return;
    }
    if ( !data.endTime ) {
        callback({code: '-15', message: pvr.errorCodes['-15']});
        return;
    }

    state = pvrManager.CreateTask(data.channel, data.name, data.startTime, data.endTime);

    if ( pvr.errorCodes[state] ) {
        callback({code: state, message: pvr.errorCodes[state]});
        return;
    }

    callback(null, true);
};
// hide the source code of the function
pvr.add.toString = function () { return 'function add() { [native code] }';};


pvr.remove = function ( item, deleteFile, callback ) {
    if ( typeof callback !== 'function' ) {
        console.log('Wrong callback function.');
        return;
    }
    if ( !item || !(item instanceof Emitter) ) {
        callback({code: '-16', message: pvr.errorCodes['-16']});
        return;
    }
    // 0     | do not remove any files
    // 1     | if temporary file exists, rename it into resulting file
    // 2     | remove only temporary file, if it exists
    // 3     | remove both temporary and resulting files
    pvrManager.RemoveTask(item.data.id, deleteFile ? 3 : 0);
    delete hash[item.data.id];
    records.splice(records.indexOf(item), 1);

    callback(null, true);

    //console.log('remove->id:' + item.data.id);
    if ( pvr.events['remove'] ) {
        pvr.emit('remove', {item: item, time: (new Date).getTime()});
    }
};
// hide the source code of the function
pvr.remove.toString = function () { return 'function remove() { [native code] }';};


pvr.reset = function () {
    records = [];
    hash = {};
};
// hide the source code of the function
pvr.reset.toString = function () { return 'function reset() { [native code] }';};


Object.defineProperties(pvr, {
    limit: {
        get: function () { return limit; },
        set: function ( value ) {
            limit = value;
            pvrManager.SetMaxRecordingCnt(value);
        }
    },
    list: {
        get: function () { return records; },
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

function update () {
    var rawData, i, item, progress;

    try {
        rawData = JSON.parse(pvrManager.GetAllTasks());
    } catch ( error ) {
        rawData = []
    }

    for ( i = 0; i < rawData.length; i++ ) {
        // if new item, trigger event
        if ( !hash[rawData[i].id] ) {
            item = new Emitter();
            item.data = {
                id: rawData[i].id,
                state: rawData[i].state,
                url: rawData[i].url,
                path: rawData[i].fileName,
                channel: rawData[i].fileName.split('records/')[1].split('/')[0],
                name: rawData[i].fileName.split('/').pop(),
                startTime: rawData[i].startTime,
                endTime: rawData[i].endTime,
                progress: rawData[i].state === 4 ? 100 : 0, // all completed have 100%
                server: false,
                _errorCode: rawData[i].errorCode
            };
            hash[item.data.id] = item;
            records.push(item);
            if ( pvr.events['add'] ) {
                pvr.emit('add', {item: item, time: (new Date).getTime()});
                console.log('add->id:' + item.data.id);
            }
        } else {
            item = hash[rawData[i].id];
            // check if progress changed (every running task)
            if ( rawData[i].state === 2 ) {
                progress = Math.ceil((((new Date()).getTime() / 1000 - rawData[i].startTime) / (rawData[i].endTime - rawData[i].startTime)) * 100);
                progress = progress < 0 ? 0 : progress;
                progress = progress > 100 ? 100 : progress;
                if ( progress !== item.data.progress ) {
                    item.data.progress = progress;
                    records[records.indexOf(item)].data.progress = progress;
                    if ( pvr.events['progress'] ) {
                        pvr.emit('progress', {item: item, time: (new Date).getTime()});
                        console.log('progress->id:' + rawData[i].id);
                    }
                }
            }
            // check if state changed
            if ( rawData[i].state !== item.data.state ) {
                item.data.state = rawData[i].state;
                records[records.indexOf(item)].data.state = rawData[i].state;
                if ( pvr.events['state'] ) {
                    pvr.emit('state', {item: item, time: (new Date).getTime()});
                    console.log('state->id:' + rawData[i].id);
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
    return pvr;
};
