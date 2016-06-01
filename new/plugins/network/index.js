/**
 * MAGSDK basic implementation of network plugin.
 *
 * @author Yaroslav Surilov <y.surilov@infomir.com>
 */

'use strict';

/**
 * Async fetch available Samba work groups.
 *
 * @param {Function} callback
 */
function getGroups ( callback ) {
    var data = [],
        self = this,
        groups;

    try {
        groups = JSON.parse(gSTB.GetSmbGroups());
    } catch ( ex ) {
        throw new Error('[Network::getGroups()] parsing gSTB.GetSmbGroups() failed');
    }

    if ( groups && Array.isArray(groups.result) && groups.result.length > 0 ) {
        groups.result.forEach(function( group ) {
            if ( !group ) {
                return;
            }

            data.push({
                name: group,
                uri: group,
                type: self.types.SAMBA_GROUP
            });
        });

        callback(null, data);
    } else {
        callback({message: 'Invalid data'});
    }
}


/**
 * Async fetch available servers for given work group.
 *
 * @param {Object} config
 * @param {Function} callback method to invoke with a result of operation
 */
function getServers ( config, callback ) {
    var data,
        self,
        servers;

    if ( typeof callback !== 'function' ) {
        throw new Error('[Network::getServers()] callback must be a function');
    }

    data = [];
    self = this;

    try {
        servers = JSON.parse(gSTB.GetSmbServers(JSON.stringify({group: config.group})));
    } catch ( ex ) {
        throw new Error('[Network::getServers()] parsing gSTB.GetSmbServers() failed');
    }

    if ( servers && Array.isArray(servers.result) && servers.result.length > 0 ) {
        servers.result.forEach(function( server ){
            if ( !server ) {
                return;
            }

            data.push({
                name: server,
                uri: config.uri + '/' + server,
                type: self.types.SAMBA_HOST
            });
        });

        callback(null, data);
    } else {
        callback({message: 'Invalid data'});
    }
}


/**
 * Async fetch available Samba shares for the given server.
 *
 * @param {string} uri
 * @param {Function} callback method to invoke with a result of operation
 */
function getShares ( uri, callback ) {
    var data,
        self,
        shares;

    if ( typeof callback !== 'function' ) {
        throw new Error('[Network::getShares()] callback must be a function');
    }

    data = [];
    self = this;

    try {
        shares = JSON.parse(gSTB.GetSmbShares(JSON.stringify({server: uri})));
    } catch ( ex ) {
        throw new Error('[Network::getShares()] parsing gSTB.GetSmbShares() failed');
    }

    // reset
    // unmountSMB();

    if ( shares && shares.result && Array.isArray(shares.result.shares) && shares.result.shares.length > 0 ) {
        shares.result.shares.forEach(function( share ){
            if ( !share ) {
                return;
            }

            data.push({
                name: share,
                url: 'SMB_PATH',
                address: '//' + shares.result.serverIP + '/' + share,
                folder: share,
                type: self.types.SAMBA_SHARE
            });
        });

        callback(null, data);
    } else {
        callback({ message: 'Invalid data' });
    }
}


/**
 * Resolve own network wrapper for each application.
 *
 * @param {Object} config Application config
 * @return {Object} network wrapper instance
 */
module.exports = function ( config ) {
    return {
        config: config,
        types: {
            SAMBA_GROUP: 0,
            SAMBA_HOST: 1,
            SAMBA_SHARE: 2
        },
        getGroups: getGroups,
        getServers: getServers,
        getShares: getShares
    };
};
