<?php

use Route\Router;

class Package_Admin extends Mvc_Package {

	public function __construct() {
		MVC_Router::getInstance()->add_library($this->get_package_dir() . 'classes');

		define('ADMIN_URL', $this->get_package_uri(true));
		
		Mvc_Fileserver::get_instance()->add_location(__DIR__ . '/public');
	}

	public function dispatching($slug) {
		
		if ($slug != 'static') {
			$router = new \Route\Router();
			$_this = $this;
			
			$router->create_route('new-admin', function($slug, $params) use ($_this) {
				$p_router = new Mvc_Package_Router($_this);
				
				echo $p_router->route($params);
			});
		}
	}

	public function get_label() {

		return str_replace('_', ' ', strtolower(__CLASS__));
	}

	public function get_action() {
		return strtolower(__CLASS__);
	}

}
