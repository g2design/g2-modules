<?php
class Twig_Mvc_Controller extends G2_TwigController{
	function __construct() {
		
		parent::__construct();
		$css =  new G2_Template_Css();
		$css->set_base_folder($this->get_package_dir().'skins/default/stylesheets');
		$css->add_external_url('//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
		$css->add_base_file('main.css');
		$css->set_output_dir('static/css');
		$this->template->css = $css;
		$this->template->title = 'Twig Page Title';
		$this->template->desc = "This is a page built with Stephan Wessels Mvc Framework and integration with Twig";
	}
	
	
	function index() {
		$view = new G2_TwigView('test');
		$view->set('title', 'asasssss');
		$view->render();
	}
}
