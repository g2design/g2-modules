<?php

class Theme_Page extends Mvc_Base {
	private $data = null, $twig = null;
	var $logged_in = false, $id = null;
	static $params = [];
	
	function __construct($slug,  Twig_Environment &$twig) {
		$this->data = $this->load_data($slug);
		$this->id = $this->data->getID();
		$this->twig = $twig;
	}
	
	static function &attach_object($name, $value) {
		self::$params[$name] = &$value;
	}
	
	function get($name){
		return $this->data->$name;
	}
	
	/**
	 * 
	 * @return RedBeanPHP\OODBBean
	 */
	function &data(){
		return $data = &$this->data;
	}
	
	function content(){
		//Load the template
//		echo $this->data->template;
		$content = $this->twig->render($this->data->template, array_merge(self::$params,['this'=> $this]));
		$processor = new Theme_Process($this);
		$processor->logged_in = $this->logged_in;
		
		$content = $processor->process($content);
		return $content;
	}
	
	function meta(){
		return "<title>{$this->data->title}</title><meta name=\"description\" content=\"{$this->data->description}\" >";
	}
	
	static function page_exists($slug){
		$page = R::findOne('page','slug = :slug',['slug' => $slug]);
		
		if(empty($page)){
			return false;
		} else return $page;
	}
	
	function load_data($slug){
		
		$page = R::findOne('page','slug = :slug',['slug' => $slug]);
		
		if(empty($page)){
			$page = $this->create_data($slug);
		}
		
		return $page;
	}
	
	function create_data($slug){
		$page = R::dispense('page');
		$page->slug = $slug;
		
		//Load the default page template
		$page->template = 'templates/index.twig';
		
		R::store($page);
		
		return $page;
	}
}
