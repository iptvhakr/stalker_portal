/*jslint indent: 2*/
/*globals require: true*/

var getLines = function (file, cb, async) {
  var fs = require('fs'), splitLines, data;
  splitLines = function (data) {
    data = data.toString();
    var lines;
    if (data.indexOf('\r\n') > -1) {
      lines = data.split('\r\n');
    } else if (data.indexOf('\n') > -1) {
      lines = data.split('\n');
    } else if (data.indexOf('\r') > -1) {
      lines = data.split('\r');
    } else { // mostly it's only one line
      lines = [data];
    }
    return lines.filter(function (line) {
      return line !== '';
    });
  };
  if (async) {
    fs.readFile(file, function (err, data) {
      if (err) {
        throw err;
      }
      cb(splitLines(data));
    });
  } else {
    data = fs.readFileSync(file);
    return splitLines(data);
  }
};

/**
 * If a string is inside quotes, the quotes will be removed
 * Very simple and not foolproof yet. It doesn't care about
 * escaped/not escaped strings. So you can have thing like this;
 * "lorem ipsum" dolor sit"
 * and you will receive:
 * lorem ipsum" dolor sit
 *
 * FIXME
 * is it a bug?
 */
var fixQuoted = function (str) {
  if (
    (str[0] === '"' && str[str.length - 1] === '"') ||
    (str[0] === "'" && str[str.length - 1] === "'")
  ) {

    return str.substr(1, str.length - 2);
  }

  return str;

};

/**
 * Parses a .ini file and convert's it's content to a JS object
 * Parser regexps are from the Config::Simple Perl module
 * @class IniReader
 * @constructor
 */
var IniReader = function (cfg) {
  // backward compatibility
  // in first versions the first argument was the file name and the second was
  // the async flag
  if (typeof cfg === 'string') {
    cfg = {
      file: cfg
    };
    if (typeof arguments[1] === 'boolean') {
      cfg.async = arguments[1];
    }
  }
  cfg = cfg || {};
  this.async = !!cfg.async;
  this.file = cfg.file || null;
  this.values = {};
};
require('util').inherits(IniReader, require('events').EventEmitter);
/**
 * Regexp to get the group names
 */
IniReader.prototype.groupRex = /^\s*\[\s*([^\]]+)\s*\]$/;

/**
 * Regexp to get key/value pairs
 */
//IniReader.prototype.keyValueRex = /^\s*([^=]*\w)\s*=\s*(.*)\s*$/;
IniReader.prototype.keyValueRex = /^\s*([^=]*)\s*=\s*(.*)\s*$/;

IniReader.prototype.load = IniReader.prototype.init = function (file) {
  if (typeof file === 'string') {
    this.file = file;
  }
  if (!this.file) {
    throw new Error('No file name given');
  }
  if (this.async) {
    getLines(this.file, function (lines) {
      this.lines = lines;
      this.values = this.parseFile();
      this.emit('fileParse');
    }.bind(this), true);
  } else {
    this.lines = getLines(this.file);
    var values = this.parseFile();
    for (var prop in values){
        if (values.hasOwnProperty(prop)){
            this.values[prop] = values[prop];
        }
    }
    this.emit('fileParse');
  }
};

/**
  * Tries to find a group name in a line
  * @method parseSectionHead
  * @type {String|False}
  * @returns the group name if found or false
  */
IniReader.prototype.parseSectionHead = function (line) {
  var groupMatch = line.match(this.groupRex);
  return groupMatch ? groupMatch[1] : false;
};

/**
  * Tries to find a key/value pair in a line
  * @method keyValueMatch
  * @type {Object|False}
  * @returns the key value pair in an object ({key: 'key', value;'value'}) if found or false
  */
IniReader.prototype.keyValueMatch = function (line) {
  var keyValMatch = line.match(this.keyValueRex);
  return keyValMatch ? {key: keyValMatch[1], value: keyValMatch[2]} : false;
};

/**
  * Parses an init file, and extracts blocks with keys and values
  * @method parseFile
  * @returns the conf tree
  * @type Object
  */
IniReader.prototype.parseFile = function () {

  var output, lines, skipLineRex, chompRex, trimRex, nonWhitespaceRex,
      groupName, keyVal, line, currentSection, lineNumber;

  output = {};
  lines = this.lines;

  // regular expressions to clear, validate and get the values
  //skipLineRex = /^\s*(\n|\#|;)/;
  skipLineRex = /^\W*(\n|\#|;)/;
  chompRex = /(?:\n|\r)$/;
  trimRex = /^\s+|\s+$/g;
  nonWhitespaceRex = /\S/;

  lineNumber = 0;

  while (line = lines.shift()) {

    lineNumber += 1;

    // skip comments and empty lines
    if (skipLineRex.test(line) || !nonWhitespaceRex.test(line)) {
      continue;
    }

    line = line.replace(chompRex, '');
    line = line.replace(trimRex, '');

    // block name
    groupName = this.parseSectionHead(line);

    if (groupName) {
      currentSection = groupName;
      if (!output[currentSection]) {
        output[currentSection] = {};
      }
      continue;
    }

    // key/value pairs
    keyVal = this.keyValueMatch(line);

    if (keyVal) {
      if (currentSection) {
        output[currentSection][keyVal.key.replace(trimRex, '')] = fixQuoted(keyVal.value);
      }
      output[keyVal.key.replace(trimRex, '')] = fixQuoted(keyVal.value);
      continue;
    }

    // if we came this far, the syntax couldn't be validated
    throw new Error("syntax error in line " + lineNumber);

  }
  return output;
};
/**
  * @method getBlock
  * @returns A block of the conf tree
  * @type Object
  */
IniReader.prototype.getBlock = function (block) {
  return typeof block === 'string' ?
          this.values[block] :
          this.values;
};
/**
  * @method getValue
  * @returns the value of the key
  * @param String block The name of the block where the key should be defined
  * @param String key The name of the key which value should be returned
  * @deprecated
  */
IniReader.prototype.getValue = function (block, key) {
  return this.getParam(block + '.' + key);
};
/**
  * @method getValue
  * @returns the value of the key
  * @param String param The name of the block where the key should be defined
  */
IniReader.prototype.getParam = function (param) {
  param = param.split('.');
  var block = param[0],
      key = param[1],
      output;

  if (typeof block !== 'string') {
    throw new Error('block is not a string');
  }

  output = this.values[block];

  if (typeof key === 'string') {
    output = output[key];
  }
  return output;
};
IniReader.prototype.setParam = function (prop, value) {
  if (typeof this.values !== 'object') {
    this.values = {};
  }
  var propKeys = prop.split('.'),
    propKeysLen = propKeys.length,
    ref = this.values;
  if (propKeysLen > 0) {
    propKeys.forEach(function (key, index) {
      if (!ref[key]) {
        ref[key] = {};
      }
      if (index < propKeysLen - 1) {
        ref = ref[key];
      } else {
        ref[key] = value;
      }
    }, this);
  }
};
IniReader.prototype.param = function (prop, value) {
  if (typeof value === 'undefined') {
    return this.getParam(prop);
  } else {
    return this.setParam(prop, value);
  }
};
IniReader.prototype.getLe = function (le) {
  return typeof le === 'string' && (le === '\n' || le === '\r\n' || le === '\r') ? le : '\n';
}
IniReader.prototype.serialize = function (le) {
  var output = '',
    group, ws = /\s+/;

  le = this.getLe(le);

  Object.keys(this.values).forEach(function (group) {
    output += le + '[' + group + ']' + le;
    Object.keys(this.values[group]).forEach(function (key) {
      var value = this.values[group][key];
      if (ws.test(value)) {
        if (value.indexOf('"') > -1) {
          value = "'" + value + "'";
        } else {
          value = '"' + value + '"';
        }
      }
      output += key + '=' + value + le;
    }, this);
  }, this);
  return output;
};
IniReader.prototype.write = function (file, le) {
  if (!file) {
    file = this.file;
  }

  le = this.getLe(le);

  var now = new Date(),
    output = '; IniReader' + le + '; ' + now.getFullYear() + '-' +
      (now.getMonth() + 1) + '-' + now.getDate() + le,
    fs = require('fs'),
    group, item, value, ws = /\s+/;

  output += this.serialize(le);

  if (this.async) {
    fs.writeFile(file, output, function (err) {
      if (err) {
        throw err;
      }
      this.emit('fileWritten', file);
    }.bind(this));
  } else {
    fs.writeFileSync(file, output);
    this.emit('fileWritten', file);
  }
};
exports.IniReader = IniReader;
