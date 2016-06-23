<?php

class Banner_Loader extends Mvc_Base {
	var $config;
	
	/**
	 * 
	 * @param Zend_Config_Ini $config.
	 */
	function __construct($config) {
		$this->config = $config;
	}
	
	function load_banners($page, $classes = ''){
	
		$page = $this->find_page($page);
		
		$images = $page->images;
		$view = new G2_TwigView('widgets/banner');
		foreach($images as $im){
			if(isset($im->content)){
				$view->set('is_banner', 'true');
				break;
			}
		}
		
//		if(isset($page->content)){
//			$content = new G2_TwigView("banner-content/$page->content");
//			if($content->exists()){
//				$view->set('content',$content->get_render());
//			}
//		}
		
		$view->set('images', $images);
		$view->set('classes',$classes);
		
		return $view->get_render();
	}
	
	private function find_page($page){
//		var_dump($page);
		foreach($this->config->banners as $name => $banner_object){
			if(!isset($first)){
				$first = $name;
			}
			$key = $name;
			foreach($banner_object as $attr => $value){
				if($attr == 'page' && $value == $page){
					//We have found the page
					return $this->config->banners->$key;
				}
			}
		}
//		reset($this->config->banners);
		return $this->config->banners->$first;
//		return current($this->config->banners); 
	}
	
}

