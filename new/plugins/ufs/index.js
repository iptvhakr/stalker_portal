/**
 * MAGSDK basic implementation of ufs plugin.
 *
 * @author Igor Zaporozhets <i.zaporozhets@infomir.com>
 */

'use strict';


var defaultFilename = 'config.text';


/**
 * core.module.ufs.load(function ( error, data) { ... });
 * core.module.ufs.save(, {qwe: 123});
 *
 * core.module.ufs.filename = 'config';
 *
 * core.module.ufs.loadSync();
 * core.module.ufs.saveSync({qwe: 123});
 *
 */
function Ufs () {}

/**
 * Custom app file name suffix.
 *
 * @type {string}
 */
Ufs.prototype.filename = defaultFilename;

/**
 * Asynchronously load file from /mnt/Userfs/data/%appName%.config.text.
 *
 * @param {function} [callback] - result callback
 *
 * @example
 * // load data from /mnt/Userfs/data/%appName%.config.text
 * core.plugins.ufs.load(function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 *
 * // load data from /mnt/Userfs/data/%appName%.meta.info.ini
 * core.plugins.ufs.fileName = 'meta.info.ini';
 *
 * core.plugins.ufs.load(function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 */
Ufs.prototype.load = function ( callback ) {
    var self = this;

    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    setTimeout(function () {
        var data = gSTB.LoadUserData(self.appName + '.' + self.filename);

        if ( typeof callback === 'function' ) {
            callback(false, data);
        }
    }, 0);
};

/**
 * Asynchronously save data to file in /mnt/Userfs/data/%appName%.config.text.
 * @param {Array|Object|number|string} data data to save
 * @param {function} [callback] - result callback
 *
 *
 * @example
 * // save data to /mnt/Userfs/data/%appName%.config.text
 * core.plugins.ufs.save('sometext', function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 *
 * // save data to /mnt/Userfs/data/%appName%.meta.info.ini'.
 * core.plugins.ufs.fileName = 'meta.info';
 *
 * core.plugins.ufs.save('sometext', function ( error, data ) {
 *     if ( error ) {
 *         throw error;
 *     }
 *     console.log(data);
 * });
 */
Ufs.prototype.save = function ( data, callback) {
    var self = this;

    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    setTimeout(function () {
        gSTB.SaveUserData(self.appName + '.' + self.filename, data);

        if ( typeof callback === 'function' ) {
            callback(false);
        }
    }, 0);
};

/**
 * Synchronously load file from /mnt/Userfs/data/%appName%.config.text.
 *
 * @return {string|null} data file content string
 *
 * @example
 * // load data from /mnt/Userfs/data/%appName%.config.text
 * console.log(core.plugins.ufs.loadSync());
 *
 * // save data to /mnt/Userfs/data/%appName%.meta.info.ini'.
 * core.plugins.ufs.fileName = 'meta.info';
 *
 * console.log(core.plugins.ufs.loadSync());
 */
Ufs.prototype.loadSync = function () {
    if ( !this.filename ) {
        this.filename = defaultFilename;
    }

    return gSTB.LoadUserData(this.appName + '.' + this.filename);
};

/**
 * Synchronously save data to file in /mnt/Userfs/data/%appName%.config.text.
 *
 * @param {Array|Object|number|string} data data to save
 *
 * @example
 *
 * // save data to /mnt/Userfs/data/%appName%.config.text
 * core.plugins.ufs.saveSync('sometext');
 *
 * // save data to /mnt/Userfs/data/%appName%.meta.info.ini'.
 * core.plugins.ufs.fileName = 'meta.info';
 *
 * core.plugins.ufs.saveSync('sometext');
 */
Ufs.prototype.saveSync = function ( data ) {
    if ( !this.filename ) {
        this.filename = defaultFilename;
    }
    
    return gSTB.SaveUserData(this.appName + '.' + this.filename, data);
};

/**
 * Resolve own ufs wrapper for each application.
 *
 * @param {Object} config application config
 *
 * @return {Ufs} ufs wrapper instance
 */
module.exports = function ( config ) {
    var instanse = new Ufs();

    // create read-only property for own app files
    Object.defineProperty(instanse, 'appName', {
        value: config.name,
        enumerable: false,
        configurable: false,
        writable: false
    });

    return instanse;
};
