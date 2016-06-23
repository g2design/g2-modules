<?php

use Route\Router;

class Index_Mvc_Controller extends Mvc_Controller {

	function __construct() {
		G2_User::init();
		//Determine the current theme location
		$config = ROOT_DIR . 'config/site.ini';
		if (file_exists($config)) {
			$config = new Zend_Config_Ini($config, APP_DEPLOY);
			$theme_location = $config->site->theme ? $config->site->theme : 'themes/default';
		} else {
			$theme_location = 'themes/default';
		}
		define("THEME", $theme_location);
	}

	public function __before() {
		parent::__before();

		if ( /*isset($_GET['load_pages']) && */ G()->logged_in() || true) {
			$_SESSION['load_pages'] = true;
		}

		if (isset($_SESSION['load_pages'])) {
			$theme = Theme_Loader::get_instance();
			$theme->set_theme(THEME);
			

			$theme->create_pages();
		}
		
		
	}
	
	function index($params) {
		
		$page = '';
		$page = implode('/', $params);
		if (!$page) {
			$page = 'index';
		}
		
		$router = new Router();
		
		if($router->routable($page)) {
			$router->route($page);
		} else if ($router->routable('404')) {
			$router->route('404');
			
		} else {
			parent::_404();
		}
	}

	function index_old($params) {
		//paste the slug together

		$page = '';
		$page = implode('/', $params);
		if (!$page) {
			$page = 'index';
		}

//		$theme_location = 'themes/default';
		//Load the front end instance loader
		$theme = Theme_Loader::get_instance();
		$theme->set_theme(THEME);
		G2_User::init();
		if (G()->logged_in()) {
			$theme->logged_in();
		}
		$_SESSION['theme'] = $theme;

		//Render the theme
		if ($theme->page_exists($page)) {
			$theme->render($page);
		} else {
			$this->_404();
		}
	}

	public function _404() {
		
		//Load the front end instance loader
		$theme = Theme_Loader::get_instance();
		$theme->set_theme(THEME);
		G2_User::init();
		if (G()->logged_in()) {
			$theme->logged_in();
		}
		$_SESSION['theme'] = $theme;

		if ($theme->page_exists('404')) {
			http_response_code(404);
			$theme->render('404');
		} else {
			parent::_404();
		}
	}
	
	
	/**
	 * Update titles of pages in site
	 * 
	 */
	function update_titles() {
		echo 'Load Config <br>';
		$config = new Zend_Config_Ini('meta.ini', APP_DEPLOY);
		
		$title = $config->meta->title->change;
		
		$pages_slugs = [];
		$this->filter($title,$pages_slugs);
		
		$pages = R::findAll('page');
		$meta = new Meta_Generator($config);
		
		foreach($pages as $page){
			$old = clone $page;
			if($page->slug == '404') continue;
			echo "$page->title ==== ".$meta->get_title($page->slug) .'<br>';
			echo "$page->description ==== ".$meta->get_description($page->slug) .'<br>';
			$page->title = $meta->get_title($page->slug);
			$page->description = $meta->get_description($page->slug);
			Audit::create($old, $page, "Script updated Meta Data");
			R::store($page);
		}
		
		
//		foreach($pages_slugs as $slug => $title){
//			$page = R::findOne('page','slug = :slug',['slug' => $slug]);
//			if($page){
//				echo "$page->title => $title ====   $page->slug<br>";
//			} else {
//				echo "$slug Page not Found <br>";
//			}
//		}
	}
	
	private function filter($config , &$data = [], $slug = false){
		foreach($config as $key => $value){
			if (is_string($value)) {
				if(!empty($slug) && $key == 'index'){
					$slugkey = $slug;
				} else {
					$slugkey = $slug.'/'.$key;
				}
				
				$slugkey = trim($slugkey, '/');
				$slugkey = str_replace('_', '-', $slugkey);
				
				$data[$slugkey] = $value;
			} else {
				echo "$key ==== $slug <br>";
				$slug .= "/$key";
				$this->filter($value,$data, $slug);
			}
		}
	}
//
//	function migrate_db() {
//		set_time_limit(360);
//		error_reporting(E_ALL);
//		R::addDatabase('mysql', 'mysql:host=localhost;dbname=G2site', 'root', '');
//		$beans = R::inspect();
//		$loaded = [];
//
//		foreach ($beans as $table) {
//			if (strpos($table, '_') !== false || $table == 'audit') {
//				continue;
//			}
//			$data = R::findAll($table);
//			foreach ($data as $bean) {
//				$loaded[] = R::dup($bean);
//			}
//		}
//
//		R::selectDatabase('mysql');
//		R::exec('SET foreign_key_checks = 0');
//		$tables = R::getCol(' show tables ');
//		R::debug(true);
//		foreach ($tables as $table) {
//			R::wipe($table);
//		}
//		foreach ($loaded as $bean) {
//
////			R::store($bean);
//		}
//	}

}
