<?php
namespace Admin;
use G2;
use Theme_Loader;

class indexController extends G2\Controller {
	
	/*
	 * @var $theme Theme_Loader;
	 */
	var $theme = null;
	
	function __before() {
		parent::__before();
		
		$theme_location = $this->get_package_dir().'themes/admin';
		//Load the front end instance loader
		$theme = Theme_Loader::get_instance();
		$theme->set_theme($theme_location);
		
		$this->theme = $theme;
		ob_start();
	}
	
	function index(){
		echo "Hello";
	}
	
	public function __after() {
		parent::__after();
		
		$content = ob_get_clean();
		echo $this->theme->render_content($content);
	}
}