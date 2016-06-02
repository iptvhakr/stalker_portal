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
