<?php

use Leafo\ScssPhp\Server;

class Static_Mvc_Controller extends Mvc_Controller {

	private $theme_location;

	function __construct() {
		//Determine the current theme location
		$config = ROOT_DIR . 'config/site.ini';
		if (file_exists($config)) {
			$config = new Zend_Config_Ini($config, APP_DEPLOY);
			$theme_location = $config->site->theme ? $config->site->theme : 'themes/default';
		} else {
			$theme_location = 'themes/default';
		}

		$this->theme_location = $theme_location;
	}

	/**
	 * Handles all resource request that are not js or css
	 * 
	 * @param type $args
	 * @throws Exception
	 */
	function index($args) {
		//Determine the current theme location
		$theme_location = $this->theme_location;
		$file_location = $theme_location . '/static/' . implode('/', $args);

		if (file_exists($file_location)) {
			$extension = Mvc_Functions::get_extension($file_location);
			$func = "{$extension}_mime";
			if (method_exists($this, $func)) {
				$this->$func($file_location);
			} else {
				$mimepath = '/usr/share/magic'; // may differ depending on your machine
				// try /usr/share/file/magic if it doesn't work
				$mime = finfo_open(FILEINFO_MIME);

				if ($mime === FALSE) {
					throw new Exception('Unable to open finfo');
				}
				$filetype = finfo_file($mime, $file_location);
				finfo_close($mime);
				if ($filetype === FALSE) {
					throw new Exception('Unable to recognise filetype');
				}


				header("Content-Type: $filetype");
//			header("X-Content-Type-Options: nosniff");
				header("Access-Control-Allow-Origin:*");
				header('Cache-Control:public, max-age=30672000');
			}

			echo file_get_contents($file_location);
			die();
		} else {
			$this->_404();
			die();
		}
	}

	/**
	 * Handles css requests. Also compiles less if a file exists
	 * 
	 */
	function css($args) {
		//Determine the current theme location
		$theme_location = $this->theme_location;
		$file_location = $theme_location . '/static/css/' . implode('/', $args);
		// Maybe check for a less file to compile and compile it
		$this->less_check($file_location);


		if (file_exists($file_location)) {
			header("Content-Type: text/css");
			header("X-Content-Type-Options: nosniff");
			header("Access-Control-Allow-Origin:*");
			header('Cache-Control:public, max-age=30672000');

			echo file_get_contents($file_location);
			die();
		} else {
			$this->_404();
			die();
		}
	}

	/**
	 * Handles requests to scss files
	 */
	function scss($args) {
		$server = new Server($this->theme_location . '/static/scss');
		$file_location = $this->theme_location . '/static/scss/' . implode('/', $args);
		$cache_location = ROOT_DIR . '/cache/scss/' . basename($file_location) . '_cache';

		if (!is_dir(dirname($cache_location)))
			mkdir(dirname($cache_location), 0777, true);
		header("Content-Type: text/css");
		header("X-Content-Type-Options: nosniff");
		header("Access-Control-Allow-Origin:*");
		header('Cache-Control:public, max-age=30672000');
		echo $server->checkedCachedCompile($file_location, $cache_location);
		die;
	}

	/**
	 * Handles request to js files
	 * 
	 * @param type $args
	 */
	function js($args) {
		//Determine the current theme location
		$theme_location = $this->theme_location;
		$file_location = $theme_location . '/static/js/' . implode('/', $args);
		// Maybe check for a less file to compile and compile it
//		$this->less_check($file_location);


		if (file_exists($file_location)) {
			header("Content-Type: text/javascript");
			header("X-Content-Type-Options: nosniff");

			header("Access-Control-Allow-Origin:*");
			header('Cache-Control:public, max-age=30672000');

			echo file_get_contents($file_location);
			die();
		} else {
			$this->_404();
			die();
		}
	}

	private function less_check($file) {
		$to_path = $file;
		$file = str_replace('/css/', '/less/', $file);
		$file = str_replace('.css', '.less', $file);
		if (file_exists($file)) { // A less file exists
			$less = new lessc();

			$less->setFormatter("compressed");

			if (!is_dir(dirname($to_path))) {
				mkdir(dirname($to_path), 0777, true);
			}
			$less->compileFile($file, $to_path);
		}
	}

	// MIME TYPES MANUAL

	function css_mime($file) {
		header("Content-Type: text/css");
		header("X-Content-Type-Options: nosniff");
		header("Access-Control-Allow-Origin:*");
		header('Cache-Control:public, max-age=30672000');
	}

}
