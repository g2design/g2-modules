var tools = require('./tools.js'),
    fs    = require('fs'),
    exec  = require('child_process').exec,
    path  = require('path');

exports.js = function (source_dir, output_dir, output) {
  var streamOptions = {
    flags: 'w+',
    encoding: 'utf-8',
    mode: 0644
  };

  var streamClose = function (output_file, minified_file, output_path, minified_path) {
    var build_file         = './dist/build/' + (output_file || minified_file),
        output_file_path   = (output_file)   ? path.join(output_path,   output_file) : null,
        minified_file_path = (minified_file) ? path.join(minified_path, minified_file) : null;
  
    // Copy uncompressed file
    if (output_file_path) {
      tools.deleteFileIfExists(output_file_path);
      fs.writeFileSync(output_file_path, fs.readFileSync(build_file, 'utf-8'));
      console.log('File "' + output_file_path + '" has been compiled successfully.');
    }
    
    // Compress file
    if (minified_file_path) {
      tools.deleteFileIfExists(minified_file_path);
      exec('java -jar ./dist/yuicompressor-2.4.8.jar ' + build_file + ' -o ' + minified_file_path,  function (error, stdout, stderr) {
        if (stderr || error) {
          console.log('Error: ' + (stderr || error));
        } else {
          fs.unlinkSync(build_file);
          console.log('File "' + minified_file_path + '" has been compiled successfully.');
        }
      });
    } else {
      fs.unlinkSync(build_file);
    }
  };

  if (output.length === 0) {
    console.log('No files specified to compile.');
  
  } else {
    output.forEach(function(output_obj) {
      var output_file   = (! output_obj['output_file'])          ? null : path.basename(output_obj['output_file']),
          minified_file = (! output_obj['minified_output_file']) ? null : path.basename(output_obj['minified_output_file']);
  
      if ( ! output_file && ! minified_file ) {
        return;
      }
  
      var output_path = null, minified_path = null;
  
      if ( output_file ) {
        output_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['output_file'])));
      }
  
      if ( minified_file ) {
        minified_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['minified_output_file'])));
      }
  
      var build_file = './dist/build/' + (output_file || minified_file);
  
      tools.deleteFileIfExists(build_file);
      (function (_output_obj, _build_file, _output_file, _minified_file, _output_path, _minified_path) {
        var file_stream = fs.createWriteStream(_build_file, streamOptions);
  
        file_stream.on('close', function () {
          streamClose(_output_file, _minified_file, _output_path, _minified_path);
        });
  
        for (var i = 0, file = _output_obj['files'][0]; i < _output_obj['files'].length; file = _output_obj['files'][++i]) {
  
          var filename = (tools.getType(file) === 'object') ? file['file'] : file,
              data     = fs.readFileSync(path.normalize(path.join(source_dir, filename)), 'utf-8');
      
          if (tools.getType(file) === 'object') {
            if (tools.getType(file['replace']) === 'array') {
              for (var j = 0; j < file['replace'].length; j++) {
                data = data.replace(file['replace'][j][0], file['replace'][j][1]);
              }
            }
          }
  
          // Write data
          file_stream.write('/* ' + path.normalize(path.join(source_dir, filename)) + ' */\n\r' + data + '\n\r\n\r');
        }
        file_stream.end();
  
      })(output_obj, build_file, output_file, minified_file, output_path, minified_path);
  
    });
  }
};


exports.less = function (source_dir, output_dir, output) {
  if (output.length === 0) {
    console.log('No files specified to compile.');
  
  } else {
    output.forEach(function(output_obj) {
      var output_file   = (! output_obj['output_file'])          ? null : path.basename(output_obj['output_file']),
          minified_file = (! output_obj['minified_output_file']) ? null : path.basename(output_obj['minified_output_file']);
  
      if ( ! output_file && ! minified_file ) {
        return;
      }
  
      var output_path = null, minified_path = null;
  
      if ( output_file ) {
        output_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['output_file'])));
        tools.deleteFileIfExists(path.join(output_path, output_file));
      }
  
      if ( minified_file ) {
        minified_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['minified_output_file'])));
        tools.deleteFileIfExists(path.join(minified_path, minified_file));
      }
  
      var source_file = path.normalize(path.join(source_dir, output_obj['source_file']));
  
      (function(_source_file, _output_file, _minified_file, _output_path, _minified_path) {
        var output_file_path   = (_output_file)   ? path.join(_output_path, _output_file) : null,
            minified_file_path = (_minified_file) ? path.join(_minified_path, _minified_file) : null;
  
        var compiledCallback = function (error, stdout, stderr) {
          if (stderr || error) {
            console.log('Error: ' + (stderr || error));
          } else {
            console.log('File "' + output_file_path + '" has been compiled successfully.');
            if (_minified_file) {
              exec('lessc ' + _source_file + ' > ' + minified_file_path + ' -x', minifiedCallback);
            }
          }
        };
  
        var minifiedCallback = function (error, stdout, stderr) {
          if (stderr || error) {
            console.log('Error: ' + (stderr || error));
          } else {
            console.log('File "' + minified_file_path + '" has been compiled successfully.');
          }
        };
    
        if (_output_file) {
          exec('lessc ' + _source_file + ' > ' + output_file_path, compiledCallback);
        } else {
          exec('lessc ' + _source_file + ' > ' + minified_file_path + ' -x', minifiedCallback);
        }
      })(source_file, output_file, minified_file, output_path, minified_path);
    });
  }
};


exports.sass = function (source_dir, output_dir, output) {
  if (output.length === 0) {
    console.log('No files specified to compile.');
  
  } else {
    output.forEach(function(output_obj) {
      var output_file   = (! output_obj['output_file'])          ? null : path.basename(output_obj['output_file']),
          minified_file = (! output_obj['minified_output_file']) ? null : path.basename(output_obj['minified_output_file']);
  
      if ( ! output_file && ! minified_file ) {
        return;
      }
  
      var output_path = null, minified_path = null;
  
      if ( output_file ) {
        output_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['output_file'])));
        tools.deleteFileIfExists(path.join(output_path, output_file));
      }
  
      if ( minified_file ) {
        minified_path = tools.createDirectoryTree(path.dirname(path.join(output_dir, output_obj['minified_output_file'])));
        tools.deleteFileIfExists(path.join(minified_path, minified_file));
      }
  
      var source_file = path.normalize(path.join(source_dir, output_obj['source_file']));
  
      (function(_source_file, _output_file, _minified_file, _output_path, _minified_path) {
        var output_file_path   = (_output_file)   ? path.join(_output_path, _output_file) : null,
            minified_file_path = (_minified_file) ? path.join(_minified_path, _minified_file) : null;
  
        var compiledCallback = function (error, stdout, stderr) {
          if (error) {
            console.log(error);
          } else {
            console.log('File "' + output_file_path + '" has been compiled successfully.');
            if (_minified_file) {
              exec('node-sass --output-style compressed ' + _source_file + ' ' + minified_file_path, minifiedCallback);
            }
          }
        };
  
        var minifiedCallback = function (error, stdout, stderr) {
          if (error) {
            console.log('Error: ' + (error));
          } else {
            console.log('File "' + minified_file_path + '" has been compiled successfully.');
          }
        };
    
        if (_output_file) {
          exec('node-sass ' + _source_file + ' ' + output_file_path, compiledCallback);
        } else {
          exec('node-sass --output-style compressed ' + _source_file + ' ' + minified_file_path, minifiedCallback);
        }
      })(source_file, output_file, minified_file, output_path, minified_path);
    });
  }
};