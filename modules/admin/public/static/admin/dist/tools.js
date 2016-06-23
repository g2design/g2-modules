var fs   = require('fs'),
    path = require('path');
// ==========================================================================


exports.deleteFileIfExists = function (filename) {
  if (fs.existsSync(filename)) {
    fs.unlinkSync(filename);
  }
}
// --------------------------------------------------------------------------

exports.createDirectoryTree = function (dirpath) {
  var parts = path.normalize(dirpath).replace(/\\/g, '/').replace(/^\.?\s*\/+\s*/, "").replace(/\s*\/+\s*$/, "").split('/');
  var result_path = '.';
  for (var i = 0; i < parts.length; i++) {
    result_path = path.join(result_path, parts[i]);
    if (parts[i] == '..') {
      continue;
    }
    if (! fs.existsSync(result_path)) {
      fs.mkdirSync(result_path);
    }
  }
  return path.normalize(result_path);
}
// --------------------------------------------------------------------------

exports.getType = function(obj) {
  return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
}
// --------------------------------------------------------------------------

exports.getMode = function(args) {
  var mode = 'less';
  for (var i = 2; i < args.length; i++) {
    if (args[i] === '--sass') {
      mode = 'sass'; break;
    }
  }
  return mode;
}
