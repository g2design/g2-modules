<?php

use Route\Router;

class Theme_Type_Page extends Theme_Type {
	static $meta_config = null;
	
	public function create($filename, \App_Config_Ini $config, $template, $mtime) {
		
		//Add the meta config to this config
		$meta_config = $this->get_meta_config();
		
		//First load the page that matches the slug
		$existing = current(R::findOrDispense('page', 'slug = :slug', ['slug' => $config->page->slug]));
		if ($existing->getID() && $existing->gen_filename != $filename) {
			throw new Exception("A page already exists inside the database with the same slug but built with the onscreen page creator");
			return false;
		}
		$page = current(R::findOrDispense('page', 'gen_filename = :file', ['file' => $filename])); /* @var $page \RedBeanPHP\OODBBean */

		/**
		 * Creates the page database object
		 */
		if (!$page->getID()) { // The Page does not exist
			$page->gen_filename = $filename;
			$page->title = $config->page->title;
			$page->description = $config->page->description;
		} else {
			
			$meta = new Meta_Generator($meta_config);
			$page->title = !empty($config->page->title) ? $config->page->title : $meta->get_title($page->slug);
			$page->description = !empty($config->page->description) ? $config->page->description :  $meta->get_description($page->slug);
		}
		
		
		$page->slug = $config->page->slug;
		
		$page->layout = $this->theme->load_layout($config->page->layout ? $config->page->layout : 'layouts.default.twig', $this->theme->theme)->file;


		/*
		 * Create the template file in the theme
		 */
		$page->template = $this->theme->generate_template($filename, $template, $mtime);

		R::store($page);

		// Create a route for this page for loading

		$r_func = function($slug) use ($page) {
			//Load the front end instance loader
			$theme = Theme_Loader::get_instance();
			$theme->set_theme(THEME);
			G2_User::init();
			if (G()->logged_in()) {
				$theme->logged_in();
			}
			$_SESSION['theme'] = $theme;

			//Render the theme
			if ($theme->page_exists($page->slug)) {
				$theme->render($page->slug);
			}
		};
		
		$route = new Router();
		$route->create_route($page->slug, $r_func);
	}
	
	
	private function get_meta_config() {
		if(isset(self::$meta_config)) {
			return self::$meta_config;
		} else {
			$config = $this->get_package_instance()->get_config('meta.ini');
			if(file_exists('meta.ini') && is_readable('meta.ini')) {
				$config->merge(new Zend_Config_Ini('meta.ini', APP_DEPLOY));
			}
			
			return self::$meta_config = $config;
		}
	}
}