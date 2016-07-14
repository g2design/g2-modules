<?php

use Route\Router;

class Theme_Loader extends Mvc_Singleton {

	var $twig = null, $page = null, $theme = null, $pre_content = null, $meta;
	var $logged_in = false;
	var $loader = false;
	static $params = [];

	/**
	 * Functions prepares the twig enviroment
	 * @param string $theme relative location of
	 */
	function set_theme($theme) {
		$this->theme = $theme;
		$this->loader = new Twig_Loader_Filesystem($theme);
		$twig_cache = 'cache/frontend';
		$default_params = array(
			'cache' => $twig_cache,
			'auto_reload' => true,
			'autoescape' => false,
//			'debug' => true
		);

		$this->loader->addPath($this->get_package_dir(TRUE) . 'views/');

		$this->twig = new Twig_Environment($this->loader, $default_params);
	}

	/**
	 * Returns the twig loader filesystem
	 * 
	 * @return Twig_Loader_Filesystem
	 */
	function &getTwigLoader() {
		return $this->loader;
	}

	function render($slug) {
		if (isset($_GET['disable_cache'])) {
			$_SESSION['disable_cache'] = true;
		}

		//Prepare the correct page for this location
		//Load a cache object and check if site is page is cached
		/* if (empty($_POST) && !G()->logged_in() && !isset($_SESSION['disable_cache']) && ($page_rendered = $this->get_cached_data($slug))) {
		  echo $page_rendered;
		  return;
		  } */

		$this->run_theme_script();

		$this->page = new Theme_Page($slug, $this->twig);
		$this->page->logged_in = $this->logged_in;
		$layout = $this->load_layout(($this->page->data()->layout ? $this->page->data()->layout : 'layouts/default.twig'), $this->theme);

		$content = $this->twig->render($layout->file, ['this' => &$this, 'page_id' => $this->page->get('id'), 'layout_id' => $layout->getID(), 'page' => $this->page->data()]);

		$processor = new Theme_LayoutProcess($layout);
		$processor->logged_in = $this->logged_in;
		$processor->page = $this->page->data();

		$content = $processor->process($content);

		//Rather attach scripts and css here then in the template
		$c = Wa72\HtmlPageDom\HtmlPageCrawler::create($content);
		if (G()->logged_in()) {
			$this->attach_admin($c);
		}
		$this->replace_cdn_script_tags($c);
		$content = $c->saveHTML();


		if (!G()->logged_in() && empty($_POST)) {
			$this->cache_data($slug, $content);
		} else {
			$this->remove_cache($slug);
		}
		echo $content;
	}
	
	function render_content($content, $layout = 'layouts/default.twig', $meta = '') {
		if (isset($_GET['disable_cache'])) {
			$_SESSION['disable_cache'] = true;
		}

		
		$this->pre_content = $content;
		$this->meta =  $meta;
		$this->run_theme_script();

		$this->page->logged_in = $this->logged_in;
		$layout = $this->load_layout($layout, $this->theme);

		$content = $this->twig->render($layout->file, array_merge( self::$params ,['this' => &$this, 'layout_id' => $layout->getID()]));

		//Rather attach scripts and css here then in the template
		$c = Wa72\HtmlPageDom\HtmlPageCrawler::create($content);
		if (G()->logged_in()) {
			$this->attach_admin($c);
		}
//		$this->replace_cdn_script_tags($c);
		$content = $c->saveHTML();


		if (!G()->logged_in() && empty($_POST)) {
			$this->cache_data($slug, $content);
		} else {
			$this->remove_cache($slug);
		}
		echo $content;
	}

	function attach_admin(Wa72\HtmlPageDom\HtmlPageCrawler &$c) {
		$c->filter('head')->append($this->render_file('admin/css_links.twig'));
	}

	function replace_cdn_script_tags(Wa72\HtmlPageDom\HtmlPageCrawler &$c) {
		//Find all cdn scripts
		$nodes = $c->filter('script');

		$nodes->each(function(Wa72\HtmlPageDom\HtmlPageCrawler &$node) use ($c) {

			/* @var $node \Wa72\HtmlPageDom\HtmlPageCrawler */
			$href = $node->getAttribute('src');
			if (!file_exists(THEME . '/' . $href)) {

				//Check if this is an external link
				if (filter_var($href, FILTER_VALIDATE_URL) === false && Mvc_Functions::startsWith($href, '//')) {
					//Determine its download location by hashing the url
					$hash = md5($href);
					$filename = "static/js/downloaded/$hash.js";
					$file_loc = THEME . "/" . $filename;
					if (!file_exists($file_loc) && isset($_GET['downloadjs'])) {
						//Download the link
//						echo "$href <br>";
						if (Mvc_Functions::startsWith($href, '//'))
							$href = 'http:' . $href;

						$content = file_get_contents($href);
						debug($href);
						debug($content);
						if (empty($content)) {
							return;
						}
						if (!is_dir(dirname($file_loc))) {
							mkdir(dirname($file_loc));
						}
						file_put_contents($file_loc, $content);


						$c->filter('script[src="' . $href . '"]')->setAttribute('src', $filename);
					} else if (file_exists($file_loc)) {
						$c->filter('script[src="' . $href . '"]')->setAttribute('src', $filename);
					}
				}
			}
		});
	}

	function render_file($file, $params = []) {
		return $this->twig->render($file, array_merge($params, self::$params));
	}

	function load_layout($file, $theme) {
		$layout = current(R::findOrDispense('layout', 'file = :file AND theme =:theme', array_merge(self::$params,['file' => $file, 'theme' => $theme])));
		if (!$layout->getID()) {
			$layout->file = $file;
			$layout->theme = $theme;
			R::store($layout);
		}

		return $layout;
	}

	function get_layouts() {
		//Check in the theme folder fot all layouts
		$layouts = Mvc_Functions::directoryToArray($this->theme . '/layouts', true);
		$layout_beans = [];
		foreach ($layouts as $layout_f) {
			if (!is_dir($layout_f)) {
				$layout_beans[] = $this->load_layout(str_replace($this->theme . '/', '', $layout_f), $this->theme);
			}
		}

		return $layout_beans;
	}

	function get_templates() {
		//Check in the theme folder fot all layouts
		$templates = Mvc_Functions::directoryToArray($this->theme . '/templates', true);
		foreach ($templates as $key => &$temp) {
			if (Mvc_Functions::get_extension($temp) == 'twig') {
				$temp = str_replace($this->theme . '/', '', $temp);
			} else {
				unset($templates[$key]);
			}
		}
		return $templates;
	}

	function page_exists($slug) {
		$page = Theme_Page::page_exists($slug);
		if (!$page) {
			return false;
		}

		if (file_exists($this->theme . '/' . $page->layout)) {
			return true;
		} else
			return false;
	}

	function meta() {
		$site_meta = '';
		return $this->meta !== null ? $this->meta : $site_meta . $this->page->meta();
	}

	function header() {
		return '<header>asaas</header>';
	}

	function content() {
		return $this->pre_content != null ? $this->pre_content : $this->page->content();
	}

	function logged_in() {
		$this->logged_in = true;
	}

	function run_theme_script() {
		$theme_script = $this->theme . '/setup.php';
		if (file_exists($theme_script)) {
			include $theme_script;
		}
	}

	/**
	 * 
	 * @return Zend_Cache_Core
	 */
	public function get_cache_object() {
		$frontendOptions = array(
			'lifeTime' => 14400, // cache lifetime of 15 minutes
			'automatic_serialization' => true
		);
		$backendOptions = array(
			'cache_dir' => ROOT_DIR . '/cache/page_cache', // where to put the cache files
		);
		if (!is_dir($backendOptions['cache_dir'])) {
			mkdir($backendOptions['cache_dir'], 0777, true);
		}
		// Create an instance of Zend_Cache_Core
		return $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	}

	private function get_cached_data($id) {
		$id = md5($id);
		$cache = $this->get_cache_object();
		$data = $cache->load($id);
		if (!$data) {
			return false;
		} else {
			return $data;
		}
	}

	private function cache_data($id, $data) {
		$id = md5($id);
		$cache = $this->get_cache_object();
		$cache->remove($id);
		$cache->save($data, $id);
//		exit;
		return true;
	}

	private function remove_cache($id) {
		$id = md5($id);
		$cache = $this->get_cache_object();
		$cache->remove($id);
	}
	
	public function get_routes(){
		return R::findAll('route');
	}

	public function create_pages() {
		//Load Page files
		$files = Mvc_Functions::directoryToArray($this->theme . "/pages", true);
//		var_dump($files);exit;
		//Loop throught the files and read theme seperately
		foreach ($files as $file) {
			if (is_dir($file)) {
				continue;
			}

			$id = $file . "-" . BASE_URL;
			//Check file last modified before loading
			$file_mod_time = current(R::findOrDispense('filem', 'filename = :file', [ 'file' => $id]));
			if (!$file_mod_time->getID()) {
				$file_mod_time->filename = $id;
				$file_mod_time->last_modified = filemtime($file);

				R::store($file_mod_time);
			} else {
				
//				debug($file . ' === ' . filemtime($file) . ' ' . $file_mod_time->last_modified);
				if (filemtime($file) > $file_mod_time->last_modified) {
					$file_mod_time->last_modified = filemtime($file);
					R::store($file_mod_time);
				} else {
					continue;
				}
			}


			list($config, $template) = explode("==", file_get_contents($file));
			list($config, $template) = preg_split('/(==\n)|(==\r)/', file_get_contents($file));
//						parent::$lines = preg_split('/\r\n|\n|\r/', trim(file_get_contents('file.txt')));
			$config = new App_Config_Ini($config);


			if ($config->type) {
				$classname = "Theme_Type_" . ucfirst($config->type);

				if (class_exists($classname)) {
					$class = new $classname($this);
					if ($class instanceof Theme_Type) {

						$class->create($file, $config, $template, filemtime($file));
					}
				}
			} else {
				$class = new Theme_Type_Page($this);

				$class->create($file, $config, $template, filemtime($file));
			}
		}

		//Remove all pages saved in database that does not exist inside the pages folder

		$this->remove_old_pages();
	}

	/**
	 * Create a generated template from a file saved in database
	 * @param type $id
	 * @param type $template
	 * @param type $mtime
	 * @return type
	 */
	function generate_template($id, $template, $mtime) {
		//Create a filename to connect to this page
		$filename = $this->get_gen_id_filename($id);
		$directory = "generated-templates";
		$correct_theme_location = "$directory/$filename";
		$file_location = "$this->theme/$correct_theme_location";

		/** Check if the file is already loaded and up to date * */
		if (file_exists($file_location) && $mtime <= filemtime($file_location)) {
			return $correct_theme_location;
		}

		if (!is_dir(dirname($file_location))) {
			mkdir(dirname($file_location), 0777, true);
		}

		file_put_contents($file_location, $template);

		return $correct_theme_location;
	}

	private function get_gen_id_filename($id) {
		return $filename = sha1($id) . ".twig";
	}

	private function remove_old_pages() {

		$pages = R::findAll('page', "gen_filename IS NOT NULL");


		foreach ($pages as $page) {
			if (!file_exists($page->gen_filename)) {
				R::trash($page);
			}
		}
	}

}
