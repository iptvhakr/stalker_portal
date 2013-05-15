//require.paths.unshift(__dirname + '/lib');

var RESTServer = require('restserver').RESTServer;

RESTServer.start();

require('recordertask').sync();