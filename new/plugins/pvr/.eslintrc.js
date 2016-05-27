/**
 * @author Igor Zaporozhets <i.zaporozhets@infomir.com>
 */

'use strict';

// public
module.exports = {
    // base rules
    extends: require.resolve('spa-eslint-config/.eslintrc.js'),

    globals: {
        gSTB: false
    },
    
    rules: {
        'new-cap': false,
        'no-empty-function': false
    }
};