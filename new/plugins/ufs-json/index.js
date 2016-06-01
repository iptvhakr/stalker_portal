/**
 * MAGSDK basic implementation of UfsJson json plugin.
 *
 * @author Igor Zaporozhets <i.zaporozhets@infomir.com>
 */

'use strict';


var defaultFilename = 'config';


/**
 * core.module.UfsJson.load(function ( error, data) { ... });
 * core.module.UfsJson.save(, {qwe: 123});
 *
 * core.module.UfsJson.filename = 'config';
 *
 * core.module.UfsJson.loadSync();
 * core.module.UfsJson.saveSync({qwe: 123});
 *
 */


function UfsJson () {}

/**
 * Custom app file name suffix.
 *
 * @type {string}
 */
UfsJson.prototype.filename = defaultFilename;

/**
 * Asynchronously load file from /mnt/Userfs/data/%appName%.config.json.
 *
 * @param {function} [callback] - result callback
 *
 * @example
 * // load data from /mnt/Userfs/data/%appName%.config.json
 * core.plugins.UfsJson.load(function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 *
 * // load data from /mnt/Userfs/data/%appName%.meta.info.json'.
 * core.plugins.UfsJson.fileName = 'meta.info.json';
 *
 * core.plugins.UfsJson.load(function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 */
UfsJson.prototype.load = function ( callback ) {
    var self = this;

    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    setTimeout(function () {
        var data = gSTB.LoadUserData(self.appName + '.' + self.filename + '.json');

        try {
            data = JSON.parse(data);
            if ( typeof callback === 'function' ) {
                callback(false, data);
            }
        } catch ( error ) {
            data = null;
            if ( typeof callback === 'function' ) {
                callback(error, data);
            }
        }
    }, 0);
};

/**
 * Asynchronously save data to file in /mnt/Userfs/data/%appName%.config.json.
 * @param {Array|Object|number|string} data data to save
 * @param {function} [callback] - result callback
 *
 *
 * @example
 * // save data to /mnt/Userfs/data/%appName%.config.json'
 * core.plugins.UfsJson.save({userToken: 'sometext'}, function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 *
 * // save data to /mnt/Userfs/data/%appName%.meta.info.json'.
 * core.plugins.UfsJson.fileName = 'meta.info.json';
 *
 * core.plugins.UfsJson.save({userToken: 'sometext'}, function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 */
UfsJson.prototype.save = function ( data, callback) {
    var self = this;

    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    setTimeout(function () {
        try {
            data = JSON.stringify(data);
        } catch ( error ) {
            data = null;
            if ( typeof callback === 'function' ) {
                callback(error, data);
            }
            return;
        }

        gSTB.SaveUserData(self.appName + '.'  + self.filename + '.json', data);
        if ( typeof callback === 'function' ) {
            callback(false, data);
        }
    }, 0);
};

/**
 * Synchronously load file from /mnt/Userfs/data/%appName%*.
 *
 * @param {function} [callback] - result callback
 *
 * @return {string|data} data deserialize file content
 *
 * @example
 * // load data from /mnt/Userfs/data/%appName%.config.json'
 * console.log(core.plugins.UfsJson.loadSync());
 *
 * // load data from /mnt/Userfs/data/%appName%.meta.info.json'.
 * core.plugins.UfsJson.fileName = 'meta.info.json';
 *
 * console.log(core.plugins.UfsJson.loadSync());
 */
UfsJson.prototype.loadSync = function () {
    var data;

    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    data = gSTB.LoadUserData(this.appName + '.' + this.filename + '.json');

    try {
        data = JSON.parse(data);
    } catch ( error ) {
        data = null;
    }

    return data;
};

/**
 * Save data to file in /mnt/Userfs/data/%appName%.config.json*.
 *
 * @param {Array|Object|number|string} data data to save
 * @param {function} [callback] - result callback
 *
 * @return {Array|Object|number|string} data to save
 *
 * @example
 *
 * // save data to /mnt/Userfs/data/%appName%.config.json'
 * core.plugins.UfsJson.saveSync({userToken: 'sometext'});
 *
 * // save data to /mnt/Userfs/data/%appName%.meta.info.json'
 * core.plugins.UfsJson.fileName = 'meta.info.json';
 *
 * core.plugins.UfsJson.saveSync({userToken: 'sometext'});
 */
UfsJson.prototype.saveSync = function ( data ) {
    if ( !this.filename ) {
        this.filename = defaultFilename;
    }
    
    try {
        data = JSON.stringify(data);
        gSTB.SaveUserData(this.appName + '.' + this.filename + '.json', data);
        return true;
    } catch ( error ) {
        data = null;
        return false;
    }
};

/**
 * Resolve own UfsJson wrapper for each application.
 *
 * @param {Object} config application config
 *
 * @return {UfsJson} UfsJson wrapper instance
 */
module.exports = function ( config ) {
    var instanse = new UfsJson();

    // create read-only property for own app files
    Object.defineProperty(instanse, 'appName', {
        value: config.name,
        enumerable: false,
        configurable: false,
        writable: false
    });

    return instanse;
};
