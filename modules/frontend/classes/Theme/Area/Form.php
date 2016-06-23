<?php

class Theme_Area_Form extends Theme_Area_Default {
	protected $replace_content_only = false;
	static $handlers = [];

	static function attach_handler($slug, $area_name , callable $handler) {
		self::$handlers[$slug.$area_name][] = $handler;
	}

	function render() {
		$form = new G2_FormMagic($this->area->html);
		
		if($form->is_posted() && $form->validate()){
			$handlers = $this->get_handlers($this->area->page->slug, $this->area->area_name);
			$replace = '';
			foreach($handlers as $handler){
				$replace_out = $handler($form->data());
				if($replace_out){
					$replace .= $replace_out;
				}
			}
			
		}
		
		if(isset($replace) && !empty($replace)){
			return $replace;
		} else {
			return $form->parse();
		}
		
	}
	
	function load(){
		return $this->area;
	}
	
	function get_handlers($slug, $area_name){
		if(isset(self::$handlers[$slug.$area_name])){
			return self::$handlers[$slug.$area_name];
		} else {
			return [function(){}];
		}
	}


}
