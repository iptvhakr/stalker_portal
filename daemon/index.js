require.paths.unshift(__dirname + '/lib');

var RESTServer = require('./lib/restserver').RESTServer;

RESTServer.start();

require('recordertask').sync();