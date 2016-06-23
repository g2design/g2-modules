<?php
class Template_helper extends Mvc_Base{
	var $template = null;
	var $vars  = array();
	
//	public function loadView($name)
//	{
//		$view = new View($name);
//		return $view;
//	}
	
	function set_template($template){
		if(file_exists($this->get_package_dir()."views/templates/{$template}_template.php")){
			$this->template = "templates/{$template}_template";
		}
		
		
	}
	
	function set($key,$value){
		$this->vars[$key] = $value;
	}
	
	function render($page){
		/* @var $page View */
		$page = $this->loadView("pages/$page");
		$template = $this->loadView($this->template);
		
		foreach($this->vars as $key => $value){
			$page->set($key,$value);
			$template->set($key,$value);
		}
		
		$template->set('page', $page);
		$template->render();
	}
	
	
	function render_view(Mvc_View $page){
		$template = $this->loadView($this->template);
		
		foreach($this->vars as $key => $value){
			$page->set($key,$value);
			$template->set($key,$value);
		}
		
		$template->set('page', $page);
		$template->render();
	}
	
	function get_render_view(Mvc_View $page){
		$template = $this->loadView($this->template);
		
		foreach($this->vars as $key => $value){
			$page->set($key,$value);
			$template->set($key,$value);
		}
		
		$template->set('page', $page);
		return $template->get_render();
	}
}